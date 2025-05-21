
<?php
include_once 'db/Conex.php';
include_once 'CheckEnvios.php';
include_once 'WriteLog.php';

class MitycFetch {
    private $db;
    private $log;

    public function __construct()
    {
        $this->db = Conex::conectar();
        $this->log = new WriteLog();
        $this->log->write("Se ha iniciado el proceso de consulta de precios con el MITYC");
        //$this->run();
    }

    /**
     * Ejecuta el proceso
     */
    public function run()
    {
        $this->GetMunicipios();
    }

    /**
     * Consulta los precios de todos los municipios
     */
    private function GetMunicipios()
    {
        $sql = "SELECT * FROM MITYC_MUNICIPIOS";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($municipios as $municipio) {
            $this->consultarPreciosMunicipio($municipio['CODIGO']);
        }   
    }

    /**
     * Consulta los precios de un municipio
     */
    private function consultarPreciosMunicipio($codigoMunicipio)
    {
        $api_url = 'https://sedeaplicaciones.minetur.gob.es/ServiciosRESTCarburantes/PreciosCarburantes/EstacionesTerrestres/FiltroMunicipio/'.$codigoMunicipio;

        //iniciamos curl
        $curl = curl_init();

        //configuramos las opciones de curl para realizar la peticion
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => false,
        ));

        //aqui se ejecuta la peticion y se obtienen los datos
        $response = curl_exec($curl);

        if(!$response){
            //si hay algun error en la peticion curl se muestra el error
            $error_msg = "No info";
            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
            }

            $this->log->write("Error al consultar los precios del municipio ".$codigoMunicipio." Err: ".$error_msg);
        }else{
            $this->procesarPrecios($response, $codigoMunicipio);
        }

        //aqui se cierra la conexion curl
        curl_close($curl);
    }

    /**
     * Obtiene los establecimientos de un municipio
     */
    private function GetEstablecimientos($codigoMunicipio)
    {
        $sql = "SELECT * FROM MITYC_ESTABLECIMIENTOS WHERE CODIGO_MUNICIPIO = :codigoMunicipio AND ESTADO = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigoMunicipio', $codigoMunicipio);
        $stmt->execute();
        $establecimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $establecimientos;
    }


    /**
     * Procesa los precios de un municipio
     */
    private function procesarPrecios($response, $codigoMunicipio)
    {
        $precios = json_decode($response, true);
        $precios = $precios['ListaEESSPrecio'];

        $establecimientos = $this->GetEstablecimientos($codigoMunicipio);

        foreach ($establecimientos as $establecimiento) {
            $this->procesarPrecio($precios, $establecimiento);
        }
    }

    /**
     * Procesa los precios de un establecimiento
     */
    private function procesarPrecio($precios, $establecimiento)
    {
        foreach ($precios as $precio) {
            if($precio['IDEESS'] == $establecimiento['ID_EESS_MITYC']){
                // Se consiguen los productos que se quieren insertar de ese establecimiento
                $sql = "SELECT * FROM PRODUCTOS_ESTABLECIMIENTOS WHERE CODIGO_EESS = :codigoEESS";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':codigoEESS', $establecimiento['ID_EESS_MITYC']);
                $stmt->execute();

                $productosEstablecimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($productosEstablecimiento as $productoEstablecimiento) {
                    $this->insertarPrecio($precio, $establecimiento, $productoEstablecimiento['CODIGO_PRODUCTO']);
                }
            }
        }
    }


    /**
     * Inserta un precio en la base de datos  PACO
     */
    private function insertarPrecio($precio, $establecimiento, $id_producto)
    {
        //check if the price is already in the database
        $sql = "SELECT * FROM MITYC_PRECIOS 
            WHERE CODIGO_EESS = :codigoEESS 
            AND CODIGO_PRODUCTO = :codigoProducto 
            AND ID_MUNICIPIO = :idMunicipio";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigoEESS', $establecimiento['ID_EESS_MITYC']);
        $stmt->bindParam(':codigoProducto', $id_producto);
        $stmt->bindParam(':idMunicipio', $establecimiento['CODIGO_MUNICIPIO']);
        $stmt->execute();
        $precioActual = $stmt->fetch(PDO::FETCH_ASSOC);

        if($precioActual){
            if($precioActual['PRECIO'] != $precio[$id_producto]){
                $this->actualizarPrecio($precio, $establecimiento, $id_producto, $precioActual['CODIGO_PRODUCTO_CA']);
            }else{
                $this->actualizarFetchDate($precio, $establecimiento, $id_producto);
            }
        }else{
            $this->insertpreciotbl($precio, $establecimiento, $id_producto);
        }
    }

    /**
     * Obtiene las empresas //NU
     */
    private function GetEmpresaEstablecimiento($establecimiento){
        $sql = "SELECT CODIGO_EMPRESA FROM ESTABLECIMIENTOS_EMPRESA WHERE CODIGO_EESS = :idEESS";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idEESS', $establecimiento['ID_EESS_MITYC']);
        $stmt->execute();
        $empresa = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $empresa;
    }

    /**
     * Obtiene los establecimientos de una empresa TABLA: ESTABLECIMIENTOS_EMPRESA
     */
    private function GetEstablecimientosEmpresa($codigoEmpresa){
        $sql = "SELECT ESTABLECIMIENTOS_EMPRESA.*,MITYC_ESTABLECIMIENTOS.NOMBRE_ESTABLECIMIENTO FROM ESTABLECIMIENTOS_EMPRESA 
            INNER JOIN MITYC_ESTABLECIMIENTOS ON ESTABLECIMIENTOS_EMPRESA.CODIGO_EESS = MITYC_ESTABLECIMIENTOS.ID_EESS_MITYC
            WHERE CODIGO_EMPRESA = :codigoEmpresa";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigoEmpresa', $codigoEmpresa);
        $stmt->execute();
        $establecimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $establecimientos;
    }


    /**
     * Inserta un precio en la base de datos
     */
    private function insertpreciotbl($precio, $establecimiento, $id_producto)
    {
        $date = date('Y-m-d H:i:s');
        $codigo_ca = $this->GetCA_CodProducto($id_producto);

        //empresa del establecimiento
        $empresa = $this->GetEmpresaEstablecimiento($establecimiento);

        //se inserta el precio en la tabla de precios del mityc
        $sql = "INSERT INTO MITYC_PRECIOS (CODIGO_EESS, ID_MUNICIPIO, CODIGO_PRODUCTO, CODIGO_PRODUCTO_CA, PRECIO, FECHA_UPD, FETCH_DATE) 
            VALUES (:codigoEESS, :idMunicipio, :codigoProducto, :codigoProductoCA, :precio, :fechaUpd, :fetchDate)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigoEESS', $establecimiento['ID_EESS_MITYC']);
        $stmt->bindParam(':idMunicipio', $establecimiento['CODIGO_MUNICIPIO']);
        $stmt->bindParam(':codigoProducto', $id_producto);
        $stmt->bindParam(':codigoProductoCA', $codigo_ca);
        $stmt->bindParam(':precio', $precio[$id_producto]);
        $stmt->bindParam(':fechaUpd', $date);
        $stmt->bindParam(':fetchDate', $date);

        if($stmt->execute()){
            foreach ($empresa as $emp) {
                $establecimientos = $this->GetEstablecimientosEmpresa($emp['CODIGO_EMPRESA']);
                foreach ($establecimientos as $est) {
                    if($est['CODIGO_EESS'] == $establecimiento['ID_EESS_MITYC']){
                        //se inserta el nuevo precio en la tabla de envios
                        $this->insertPrecioEnvio($emp['CODIGO_EMPRESA'], $est['CODIGO_EESS'], $codigo_ca, $precio[$id_producto], $date);
                    }
                }
            }

            $this->log->write("Se ha insertado el precio del producto '".$id_producto."' del establecimiento '".$precio['Rótulo']." - ".$establecimiento['ID_EESS_MITYC']."' con el precio '".$precio[$id_producto]."€/L'");
            return true;
        }else{
            $this->log->write("Error al insertar el precio del establecimiento ".$precio['Rótulo']);
            return false;
        }
    }

    /**
     * Actualiza un precio en la base de datos [Actualmente no se usa] --
     */
    private function actualizarPrecio($precio, $establecimiento, $id_producto, $codigo_ca){
        $date = date('Y-m-d H:i:s');
        $empresas = $this->GetEmpresaEstablecimiento($establecimiento);

        //se actualiza el precio en la tabla de precios del mityc
        $sql = "UPDATE MITYC_PRECIOS SET PRECIO = :precio, FECHA_UPD = :fechaUpd, FETCH_DATE = :fetchdate 
            WHERE CODIGO_EESS = :codigoEESS 
            AND CODIGO_PRODUCTO = :codigoProducto 
            AND ID_MUNICIPIO = :idMunicipio";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigoEESS', $establecimiento['ID_EESS_MITYC']);
        $stmt->bindParam(':idMunicipio', $establecimiento['CODIGO_MUNICIPIO']);
        $stmt->bindParam(':codigoProducto', $id_producto);
        $stmt->bindParam(':precio', $precio[$id_producto]);
        $stmt->bindParam(':fechaUpd', $date);
        $stmt->bindParam(':fetchdate', $date);

        if($stmt->execute()){
            foreach ($empresas as $emp) {
                $establecimientos = $this->GetEstablecimientosEmpresa($emp['CODIGO_EMPRESA']);
                foreach ($establecimientos as $est) {
                    if($est['CODIGO_EESS'] == $establecimiento['ID_EESS_MITYC']){
                        //se inserta el nuevo precio en la tabla de envios
                        $this->insertPrecioEnvio($emp['CODIGO_EMPRESA'], $est['CODIGO_EESS'], $codigo_ca, $precio[$id_producto], $date);
                    }
                }
            }
            $this->log->write("Se ha actualizado el precio del establecimiento '".$precio['Rótulo']." - ".$establecimiento['ID_EESS_MITYC']."' con el producto '".$id_producto. "' y el precio '".$precio[$id_producto]."€'");
            return true;
        }else{
            $this->log->write("Error al actualizar el precio del establecimiento ".$precio['Rótulo']);
            return false;
        }
    }

    /**
     * Inserta el precio en MITYC_PRECIOS_ENVIO
     */
    private function insertPrecioEnvio($codigo_empresa, $codigo_eess, $codigo_producto_ca, $precio, $fecha){
        //comprobar que no existen los mismos datos en la tabla
        $sql = "SELECT * FROM MITYC_PRECIOS_ENVIO 
            WHERE CODIGO_EESS = :codigo_eess 
            AND CODIGO_EMPRESA = :codigoEmpresa
            AND CODIGO_PRODUCTO_CA = :codigoProductoCa 
            AND PRECIO = :precio 
            AND FECHA_INS = :fechaIns";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigo_eess', $codigo_eess);
        $stmt->bindParam(':codigoEmpresa', $codigo_empresa);
        $stmt->bindParam(':codigoProductoCa', $codigo_producto_ca);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':fechaIns', $fecha);
        $stmt->execute();

        if($stmt->rowCount() == 0){
            $sql = "INSERT INTO MITYC_PRECIOS_ENVIO (CODIGO_EESS,CODIGO_EMPRESA, CODIGO_PRODUCTO_CA, PRECIO, FECHA_INS) 
                VALUES (:codigo_eess, :codigoEmpresa, :codigoProductoCA, :precio, :fechaIns)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':codigo_eess', $codigo_eess);
            $stmt->bindParam(':codigoEmpresa', $codigo_empresa);
            $stmt->bindParam(':codigoProductoCA', $codigo_producto_ca);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':fechaIns', $fecha);
            $stmt->execute();           
        
        }else{
            $this->log->write("El precio del establecimiento '".$codigo_eess."' con el producto '".$codigo_producto_ca."' y el precio '".$precio."' ya existe en la tabla MITYC_PRECIOS_ENVIO");
        }
    }

    /**
     * Actualiza el campo FETCH_DATE de un precio en la base de datos
     */
    private function actualizarFetchDate($precio, $establecimiento, $id_producto){
        $date = date('Y-m-d H:i:s');

        $sql = "UPDATE MITYC_PRECIOS SET FETCH_DATE = :fetchDate 
            WHERE CODIGO_EESS = :codigoEESS 
            AND CODIGO_PRODUCTO = :codigoProducto 
            AND ID_MUNICIPIO = :idMunicipio";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigoEESS', $establecimiento['ID_EESS_MITYC']);
        $stmt->bindParam(':idMunicipio', $establecimiento['CODIGO_MUNICIPIO']);
        $stmt->bindParam(':codigoProducto', $id_producto);
        $stmt->bindParam(':fetchDate', $date);
        
        if($stmt->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Obtiene el codigo CA de un producto
     */
    private function GetCA_CodProducto($codigoProducto)
    {
        $sql = "SELECT CODIGO_CA FROM MITYC_PRODUCTOS WHERE CODIGO = :codigoProducto";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigoProducto', $codigoProducto);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $producto['CODIGO_CA'];
    }

    /**
     * Destructor
     */
    public function __destruct()
    {

        $this->log->write("Consulta de precios con el MITYC finalizada \n");
        $this->db = Conex::desconectar($this->db);
        $this->log->close();
        $this->db = null;
    }
}

/*
// Ejecutar
$mitycFetch = new MitycFetch();
$mitycFetch->run();*/
?>