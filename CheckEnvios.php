
<?php
include_once 'db/Conex.php';
include_once 'EnvioPrecios.php';
include_once 'WriteLog.php';

class CheckEnvios {
    private $db;
    private $socket;
    private $empresa;
    private $log;

    public function __construct()
    {
        $this->db = Conex::conectar();
        $this->log = new WriteLog();
        $this->empresa = $this->GetEmpresa();
        $this->PreciosPendientes();
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        //$this->log->write("Ejecución finalizada");
        //$this->log->write("========================================================");
        $this->log->close();
        $this->db = null;
    }

    /**
     * Obtiene los precios pendientes de enviar
     */
    private function PreciosPendientes(){
        try{
            foreach($this->empresa as $emp){
                $sql = "SELECT mpe.*, mp.NOMBRE, e.EMPRESA NOMBRE_EMPRESA, me.CODIGO_TERMINAL FROM MITYC_PRECIOS_ENVIO mpe
                    INNER JOIN MITYC_PRODUCTOS mp 
                        ON mp.CODIGO_CA = mpe.CODIGO_PRODUCTO_CA
                    INNER JOIN EMPRESAS e 
                        ON e.CODIGO = mpe.CODIGO_EMPRESA
                    INNER JOIN MITYC_ESTABLECIMIENTOS me 
                        ON me.ID_EESS_MITYC = mpe.CODIGO_EESS
                    WHERE mpe.ESTADO 
                        IN (0, 2)
                    AND mpe.CODIGO_EMPRESA = :codigoEmpresa
                    AND mpe.ANULADO = 0
                        ORDER BY mpe.CODIGO_PRODUCTO_CA, mpe.FECHA_INS DESC";
                    
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':codigoEmpresa', $emp['CODIGO']);
                $stmt->execute();
                $precios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(count($precios) > 0){
                    foreach($precios as $precio){
                        //Se comprueba si el precio es 0, null o vacio
                        if($precio['PRECIO'] != 0 || $precio['PRECIO'] != null || $precio['PRECIO'] != ''){ //si el precio es 0, null o vacio
                            if($precio['INTENTOS_ENVIO'] <= 120){ //a los cuantos numeros de envio quieres que deje de intentar enviar
                                if($this->ComprobarPrecioAnterior($precio['CODIGO_EESS'], $precio['CODIGO_PRODUCTO_CA'], $precio['CODIGO_EMPRESA'], $precio['FECHA_INS'])){
                                    // 1 segundo de espera
                                    sleep(1);
                                    $this->EnviarPreciosSocket($precio, $emp);                        
                                }
                            }
                        }else{
                            $this->log->write("## IMPORTANTE ## - El precio del establecimiento '".$precio['CODIGO_EESS']."' con el producto '".$precio['CODIGO_PRODUCTO_CA']."' es 0, null o vacio, se anulará el envío precio");
                            try{
                                $sql = "UPDATE MITYC_PRECIOS_ENVIO SET ANULADO = 1 WHERE CODIGO_EESS = :codigoEESS AND CODIGO_PRODUCTO_CA = :codigoProductoCa AND CODIGO_EMPRESA = :codigoEmpresa AND PRECIO = :precio AND FECHA_INS = :fechains";
                                $stmt = $this->db->prepare($sql);
                                $stmt->bindParam(':codigoEESS', $precio['CODIGO_EESS']);
                                $stmt->bindParam(':codigoProductoCa', $precio['CODIGO_PRODUCTO_CA']);
                                $stmt->bindParam(':codigoEmpresa', $precio['CODIGO_EMPRESA']);
                                $stmt->bindParam(':precio', $precio['PRECIO']);
                                $stmt->bindParam(':fechains', $precio['FECHA_INS']);
                                $stmt->execute();

                                $this->log->write("## IMPORTANTE ## - Se anuló el precio del establecimiento '".$precio['CODIGO_EESS']."' con el producto '".$precio['CODIGO_PRODUCTO_CA']."' en la tabla MITYC_PRECIOS_ENVIO");
                            }catch(Exception $e){
                                $this->log->write("## IMPORTANTE ## - Error al anular el precio del establecimiento '".$precio['CODIGO_EESS']."' con el producto '".$precio['CODIGO_PRODUCTO_CA']."' en la tabla MITYC_PRECIOS_ENVIO");
                            }
                            
                        }

                    }
                }
            }
        }catch(Exception $e){
            $this->log->write("Error al obtener los precios pendientes de enviar inf: ".$e->getMessage());
        }
    }

    /**
     * Se comprueba si existe un precio anterior y si es asi se anula
     */
    private function ComprobarPrecioAnterior($codigo_eess, $codigo_ca, $codigo_empresa, $nueva_fecha)
    {
        try{
            $sql = "SELECT * FROM MITYC_PRECIOS_ENVIO 
                WHERE 
                    CODIGO_EESS = :codigoEESS 
                AND 
                    CODIGO_EMPRESA = :codigoEmpresa
                AND 
                    CODIGO_PRODUCTO_CA = :codigoProductoCa
                AND 
                    ESTADO = 0 
                AND 
                    ANULADO = 0
                AND 
                    FECHA_INS < :nuevaFecha
                ORDER BY FECHA_INS 
                DESC LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':codigoEESS', $codigo_eess);
            $stmt->bindParam(':nuevaFecha', $nueva_fecha);
            $stmt->bindParam(':codigoProductoCa', $codigo_ca);
            $stmt->bindParam(':codigoEmpresa', $codigo_empresa);
            $stmt->execute();
            $precioAnterior = $stmt->fetch(PDO::FETCH_ASSOC);

            if($precioAnterior){
                $sql = "UPDATE MITYC_PRECIOS_ENVIO 
                    SET ANULADO = 1 
                    WHERE CODIGO_EESS = :codigoEESS 
                    AND CODIGO_PRODUCTO_CA = :codigoProductoCa 
                    AND CODIGO_EMPRESA = :codigoEmpresa 
                    AND PRECIO = :precio 
                    AND FECHA_INS = :fechains";

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':codigoEESS', $precioAnterior['CODIGO_EESS']);
                $stmt->bindParam(':codigoProductoCa', $precioAnterior['CODIGO_PRODUCTO_CA']);
                $stmt->bindParam(':codigoEmpresa', $precioAnterior['CODIGO_EMPRESA']);
                $stmt->bindParam(':precio', $precioAnterior['PRECIO']);
                $stmt->bindParam(':fechains', $precioAnterior['FECHA_INS']);
                $stmt->execute();

                return true;
            }else{
                return true;
            }
        }catch(Exception $e){
            $this->log->write("Error al comprobar el precio anterior del establecimiento '".$codigo_eess."' con el producto '".$codigo_ca."' y el precio '".$precio."' en la tabla MITYC_PRECIOS_ENVIO");
            return false;
        }
    }

    /**
     * Envia los precios por socket
     */
    private function EnviarPreciosSocket($precio, $emp){
        //eliminar ultimos 6 meses
        $this->deleteOlds();

        $date = date('Y-m-d H:i:s');
        $envioPrecios = new EnvioPrecios($emp['IP'], $emp['PUERTO']);

        $datos = array(
            'empresa' => $emp['EMPRESA'],
            'codigoTerminal' => $precio['CODIGO_TERMINAL'],
            'codigoProducto' => $precio['CODIGO_PRODUCTO_CA'],
            'precio' => floatval(str_replace(',', '.', $precio['PRECIO'])),
        );

        if($envioPrecios->run($datos)){
            $sql = "UPDATE MITYC_PRECIOS_ENVIO SET ESTADO = 1, FECHA_ENVIO = '$date' 
                WHERE CODIGO_EESS = :codigoEESS 
                AND CODIGO_EMPRESA = :codigoEmpresa
                AND CODIGO_PRODUCTO_CA = :codigoProductoCa 
                AND PRECIO = :precio 
                AND FECHA_INS = :fechains
                AND ANULADO = 0"
            ;

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':codigoEESS', $precio['CODIGO_EESS']);
            $stmt->bindParam(':codigoEmpresa', $emp['CODIGO']);
            $stmt->bindParam(':codigoProductoCa', $precio['CODIGO_PRODUCTO_CA']);
            $stmt->bindParam(':precio', $precio['PRECIO']);
            $stmt->bindParam(':fechains', $precio['FECHA_INS']);
            $stmt->execute();
        }else{
            $sql_intentos =  $precio['INTENTOS_ENVIO'] + 1;
            $sql = "UPDATE MITYC_PRECIOS_ENVIO SET ESTADO = 0, INTENTOS_ENVIO = '$sql_intentos' 
                WHERE CODIGO_EESS = :codigoEESS 
                AND CODIGO_EMPRESA = :codigoEmpresa
                AND CODIGO_PRODUCTO_CA = :codigoProductoCa 
                AND PRECIO = :precio 
                AND FECHA_INS = :fechains
                AND ANULADO = 0"
            ;
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':codigoEESS', $precio['CODIGO_EESS']);
            $stmt->bindParam(':codigoEmpresa', $emp['CODIGO']);
            $stmt->bindParam(':codigoProductoCa', $precio['CODIGO_PRODUCTO_CA']);
            $stmt->bindParam(':precio', $precio['PRECIO']);
            $stmt->bindParam(':fechains', $precio['FECHA_INS']);
           $stmt->execute();
        }
    }

    /**
     * Elimina los precios el cual la fecha de envio sea mayor a 6 meses
     */
    private function deleteOlds(){
        $sql = "DELETE FROM MITYC_PRECIOS_ENVIO WHERE FECHA_INS < DATE_SUB(NOW(), INTERVAL 6 MONTH)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    /**
     * Obtiene las empresas
     */
    private function GetEmpresa(){
        $sql = "SELECT * FROM EMPRESAS";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $empresa = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $empresa;
    }
}

//$checkEnvios = new CheckEnvios();
?>