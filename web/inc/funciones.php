<?php
include_once '../db/Conex.php';
include_once '../WriteLog.php';
require_once 'PhpXlsxGenerator.php'; 
class Funciones
{
    private $conex;
    private $log;

    public function __construct()
    {
        $this->log = new WriteLog();
        $this->db = Conex::conectar();
    }


    //COMPROBACIONES
    public function ExistMunicipio($municipio)
    {
        $sql = "SELECT * FROM MITYC_MUNICIPIOS WHERE CODIGO = '$municipio'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($municipios) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Comprobar si el establecimineto ya existe en la base de datos
     */
    public function ExistEstablecimiento($establecimiento)
    {
        $sql = "SELECT * FROM MITYC_ESTABLECIMIENTOS WHERE ID_EESS_MITYC = '$establecimiento'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $establecimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($establecimientos) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Comprobar si el establecimiento ya tiene productos en la tabla de productos_establecimientos
     */
    public function AlreadyAdded($establecimiento)
    {
        $sql = "SELECT * FROM PRODUCTOS_ESTABLECIMIENTOS WHERE CODIGO_EESS = '$establecimiento'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $productosEstablecimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($productosEstablecimiento) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Comprobar si el producto y la empresa existe en la tabla de productos_establecimientos
     */
    public function ExistProductoEst($ideess, $producto)
    {
        $sql = "SELECT * FROM PRODUCTOS_ESTABLECIMIENTOS WHERE CODIGO_EESS = '$ideess' AND CODIGO_PRODUCTO = '$producto'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $productosEstablecimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($productosEstablecimiento) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Comprueba que en la tabla de ESTABLECIMIENTOS_EMPRESA no exista ya la empresa y el establecimiento
     */
    public function ExistEmpresaEst($ideess, $empresa)
    {
        $sql = "SELECT * FROM ESTABLECIMIENTOS_EMPRESA WHERE CODIGO_EESS = '$ideess' AND CODIGO_EMPRESA = '$empresa'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $empresasEstablecimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($empresasEstablecimiento) > 0) {
            return true;
        } else {
            return false;
        }
    }
    /* END COMPROBACIONES */

    
    /* --------------------------------- GETTERS ------------------------------------------------------*/

    /**
     * Devuelve un array precios de establecimientos filtrados
     */
    public function getPreciosFiltrados($estacion, $estado, $desde, $hasta, $empresa, $offset, $itemsPerPage)
    {
        $sql = "SELECT mpe.*, mp.NOMBRE, e.EMPRESA NOMBRE_EMPRESA, me.NOMBRE_ESTABLECIMIENTO ESTABLECIMIENTO, m.NOMBRE MUNICIPIO FROM MITYC_PRECIOS_ENVIO mpe
            INNER JOIN MITYC_PRODUCTOS mp 
                ON mp.CODIGO_CA = mpe.CODIGO_PRODUCTO_CA
            INNER JOIN EMPRESAS e 
                ON e.CODIGO = mpe.CODIGO_EMPRESA
            INNER JOIN MITYC_ESTABLECIMIENTOS me
                ON me.ID_EESS_MITYC = mpe.CODIGO_EESS
            INNER JOIN MITYC_MUNICIPIOS m
                ON m.CODIGO = me.CODIGO_MUNICIPIO
            WHERE 1=1";

        if ($estacion != "") {
            $sql .= " AND mpe.CODIGO_EESS = '$estacion'";
        }

        if ($estado != "") {
            $sql .= " AND mpe.ESTADO = '$estado'";
        }

        if ($empresa != "") {
            $sql .= " AND mpe.CODIGO_EMPRESA = '$empresa'";
        }

        if ($desde != "") {
            $formattedDesde = date("Y-m-d", strtotime($desde));
            $sql .= " AND DATE(mpe.FECHA_ENVIO) >= '$formattedDesde'";
        }
        
        if ($hasta != "") {
            $formattedHasta = date("Y-m-d", strtotime($hasta));
            $sql .= " AND DATE(mpe.FECHA_ENVIO) <= '$formattedHasta'";
        }
        
        $sql .= " ORDER BY mpe.FECHA_INS DESC, mpe.CODIGO_EESS ASC, mpe.ESTADO ASC";
        //$sql .= " ORDER BY mpe.FECHA_INS DESC, mpe.CODIGO_EESS DESC, mpe.CODIGO_PRODUCTO_CA ASC, mpe.ESTADO ASC";

        if($itemsPerPage != -1){
            $sql .= " LIMIT $offset, $itemsPerPage";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $precios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($precios as $key => $precio) {
            $precios[$key]['FECHA_ENVIO'] = $precio['FECHA_ENVIO'] != "" ? date("d/m/Y H:i:s", strtotime($precio['FECHA_ENVIO'])) : "N/A";
            $precios[$key]['FECHA_INS'] = $precio['FECHA_INS'] != "" ? date("d/m/Y H:i:s", strtotime($precio['FECHA_INS'])) : "-";
        }

        return $precios;
    }


    /**
     * Consigue los precios existentes de la estacion
     * @param $id_eess id de la estacion
     */
    public function GetPreciosExistentes($id_eess){
        $sql = "SELECT mp.CODIGO_EESS, me.NOMBRE_ESTABLECIMIENTO, me.CODIGO_TERMINAL, me.CODIGO_MUNICIPIO, ee.CODIGO_EMPRESA, e.EMPRESA ,mp.CODIGO_PRODUCTO, mp.CODIGO_PRODUCTO_CA, mp.PRECIO, mp.FECHA_UPD FROM MITYC_PRECIOS mp
                INNER JOIN ESTABLECIMIENTOS_EMPRESA ee
                    ON ee.CODIGO_EESS = mp.CODIGO_EESS
                INNER JOIN EMPRESAS e 
                    ON e.CODIGO = ee.CODIGO_EMPRESA
                INNER JOIN MITYC_ESTABLECIMIENTOS me
                    ON me.ID_EESS_MITYC = mp.CODIGO_EESS
                
                WHERE mp.CODIGO_EESS = :id_eess";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_eess', $id_eess);
        $stmt->execute();
        $precios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $precios;
    }

    /**
     * Devuelve el estado de una estacion
     * @param $id_eess id de la estacion
     * @return estado de la estacion
     */
    public function GetEstadoEESS($id_eess){
        try{
            $sql = "SELECT ESTADO FROM MITYC_ESTABLECIMIENTOS WHERE ID_EESS_MITYC = :id_eess";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_eess', $id_eess);
            $stmt->execute();
            $estado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $estado[0]['ESTADO'] ?? 1;
        }catch(Exception $e){
            return 1;
        }
    }

    /**
     * Devuelve un array con el precio de un producto de una estacion
     * @param $codigo_eess id de la estacion
     * @param $id_municipio id del municipio
     * @param $codigo_producto codigo del producto
     */
    public function GetPreciosEESS($codigo_eess, $id_municipio, $codigo_producto){
        $sql = "SELECT PRECIO, CODIGO_PRODUCTO_CA, FECHA_UPD FROM MITYC_PRECIOS WHERE CODIGO_EESS = :codigo_eess AND CODIGO_PRODUCTO_CA = :codigo_producto AND ID_MUNICIPIO = :id_municipio";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigo_eess', $codigo_eess);
        $stmt->bindParam(':id_municipio', $id_municipio);
        $stmt->bindParam(':codigo_producto', $codigo_producto);
        $stmt->execute();
        $precios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $precios;
    }


    /**
     * Devuelve un total de precios de establecimientos filtrados o no
     */
    public function getTotalPreciosCount($estacion, $estado, $desde, $hasta, $empresa){
        $sql = "SELECT COUNT(*) AS TOTAL FROM MITYC_PRECIOS_ENVIO WHERE 1=1";

        if ($estacion != "") {
            $sql .= " AND MITYC_PRECIOS_ENVIO.CODIGO_EESS = '$estacion'";
        }
        if ($estado != "") {
            $sql .= " AND MITYC_PRECIOS_ENVIO.ESTADO = '$estado'";
        }

        if ($empresa != "") {
            $sql .= " AND MITYC_PRECIOS_ENVIO.CODIGO_EMPRESA = '$empresa'";
        }
        
        if ($desde != "") {
            $formattedDesde = date("Y-m-d", strtotime($desde));
            $sql .= " AND DATE(MITYC_PRECIOS_ENVIO.FECHA_ENVIO) >= '$formattedDesde'";
        }
        if ($hasta != "") {
            $formattedHasta = date("Y-m-d", strtotime($hasta));
            $sql .= " AND DATE(MITYC_PRECIOS_ENVIO.FECHA_ENVIO) <= '$formattedHasta'";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $total = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $total[0]['TOTAL'];
    }

    /**
     * Devuelve un array con todas las estaciones
     */
    public function getEstaciones()
    {
        $sql = "SELECT me.*, mm.NOMBRE MUNICIPIO FROM MITYC_ESTABLECIMIENTOS me 
            INNER JOIN MITYC_MUNICIPIOS mm 
                ON mm.CODIGO = me.CODIGO_MUNICIPIO";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $estaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $estaciones;
    }

    /**
     * Devuelve un array con los datos de la estacion
     */
    public function getEstacion($estacion)
    {
        $sql = "SELECT * FROM MITYC_ESTABLECIMIENTOS WHERE ID_EESS_MITYC = '$estacion'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $estacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $estacion[0];
    }

    /**
     * Devuelve un array con los municipios
     */
    public function getMunicipios(){
        $sql = "SELECT CODIGO, NOMBRE FROM MITYC_MUNICIPIOS ORDER BY NOMBRE ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $municipios;
    }

    /**
     * Para la paginacion de estaciones
     * @param $offset desde que registro empieza
     * @param $itemsPerPage numero de registros por pagina
     * @param $estacion nombre de la estacion
     * @param $municipio nombre del municipio
     * @param $estado estado de la estacion
     * @return array con las estaciones
     */
    public function getEstacionesFiltradas($offset, $itemsPerPage, $codigo_estacion = null, $codigo_municipio = null, $estado = null, $empresa = null) {
        $sql = "SELECT me.*, mm.NOMBRE AS MUNICIPIO FROM MITYC_ESTABLECIMIENTOS me 
                INNER JOIN MITYC_MUNICIPIOS mm ON mm.CODIGO = me.CODIGO_MUNICIPIO
                INNER JOIN ESTABLECIMIENTOS_EMPRESA ee ON ee.CODIGO_EESS = me.ID_EESS_MITYC
                INNER JOIN EMPRESAS e ON e.CODIGO = ee.CODIGO_EMPRESA";
    
        $conditions = [];
    
        if ($codigo_estacion !== null && $codigo_estacion !== "") {
            $conditions[] = "me.ID_EESS_MITYC = '$codigo_estacion'";
        }
    
        if ($codigo_municipio !== null && $codigo_municipio !== "") {
            $conditions[] = "mm.CODIGO = '$codigo_municipio'";
        }
    
        if ($estado !== null && $estado !== "") {
            $conditions[] = "me.ESTADO = '$estado'";
        }

        if ($empresa !== null && $empresa !== "") {
            $conditions[] = "e.CODIGO = '$empresa'";
        }
    
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
    
        $sql .= " ORDER BY me.NOMBRE_ESTABLECIMIENTO ASC 
                  LIMIT $offset, $itemsPerPage";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $estaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $estaciones;
    }    

    /**
     * Para la paginacion de estaciones
     */
    public function getTotalEstacionesCount(){
        $sql = "SELECT COUNT(*) AS TOTAL FROM MITYC_ESTABLECIMIENTOS";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $total = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $total[0]['TOTAL'];
    }

    /**
     * Devuelve un array con todas las provincias
     */
    public function getEmpresas()
    {
        $sql = "SELECT * FROM EMPRESAS";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $empresas;
    }

    /**
     * Devuelve un array con todos los productos
     */
    public function getProductos()
    {
        $sql = "SELECT * FROM MITYC_PRODUCTOS";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $productos;
    }

    /*
    * Devuelve un array con todos los productos de una estacion
    */
    public function getProductosEstablecimiento($establecimiento)
    {
         $sql = "SELECT * FROM PRODUCTOS_ESTABLECIMIENTOS WHERE CODIGO_EESS = '$establecimiento'";
         $stmt = $this->db->prepare($sql);
         $stmt->execute();
         $productosEstablecimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
         return $productosEstablecimiento;
    }

    /**
     * Obtiene el numero de temrinal de una estacion
     */
    public function getCodigoTerminal($establecimiento)
    {
        $sql = "SELECT CODIGO_TERMINAL FROM MITYC_ESTABLECIMIENTOS WHERE ID_EESS_MITYC = '$establecimiento'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $codigoTerminal = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
        return $codigoTerminal[0]['CODIGO_TERMINAL'];
    }


    /**
     * Devulve un conteo de los precios no enviados
     */
    public function GetEnviosNoEnviados(){
        $sql = "SELECT COUNT(*) AS TOTAL
                FROM MITYC_PRECIOS_ENVIO
                INNER JOIN MITYC_ESTABLECIMIENTOS 
                    ON MITYC_ESTABLECIMIENTOS.ID_EESS_MITYC = MITYC_PRECIOS_ENVIO.CODIGO_EESS
                WHERE MITYC_PRECIOS_ENVIO.ESTADO = 0
                AND MITYC_PRECIOS_ENVIO.ANULADO = 0
                AND MITYC_ESTABLECIMIENTOS.ESTADO = 1
                AND MITYC_PRECIOS_ENVIO.FECHA_INS >= (NOW() - INTERVAL 7 DAY);";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $total = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $total[0]['TOTAL'];
    }

    /**
     * Devuelve un array con todos el codigo de empresa de una estacion
     */
    public function getEmpresaEstablecimiento($establecimiento)
    {
        $sql = "SELECT ESTABLECIMIENTOS_EMPRESA.CODIGO_EMPRESA 
            FROM MITYC_ESTABLECIMIENTOS 
            INNER JOIN ESTABLECIMIENTOS_EMPRESA 
                ON ESTABLECIMIENTOS_EMPRESA.CODIGO_EESS = MITYC_ESTABLECIMIENTOS.ID_EESS_MITYC 
            WHERE ID_EESS_MITYC = '$establecimiento'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $empresaEstablecimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
        return $empresaEstablecimiento[0] ?? [];
    }

    /**
     * Consigue los establecimientos de una empresa
     */
    public function getEstablecimientoEmpresa($id_estacion)
    {
        $sql = "SELECT *, EMPRESAS.EMPRESA NOMBRE_EMPRESA 
            FROM ESTABLECIMIENTOS_EMPRESA 
            INNER JOIN EMPRESAS 
                ON EMPRESAS.CODIGO = ESTABLECIMIENTOS_EMPRESA.CODIGO_EMPRESA 
            WHERE CODIGO_EESS = '$id_estacion'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $establecimientoEmpresa = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
        return $establecimientoEmpresa;
    }

    /**
     * Devuelve un array con todos los productos de todas las estaciones
     */
    public function getProductosEstablecimiento_general(){
        $sql = "SELECT MITYC_ESTABLECIMIENTOS.ID_EESS_MITYC CODIGO_EESS, MITYC_ESTABLECIMIENTOS.NOMBRE_ESTABLECIMIENTO, MITYC_MUNICIPIOS.NOMBRE MUNICIPIO, MITYC_ESTABLECIMIENTOS.CODIGO_TERMINAL, MITYC_PRODUCTOS.NOMBRE PRODUCTO 
            FROM PRODUCTOS_ESTABLECIMIENTOS 
                LEFT JOIN MITYC_ESTABLECIMIENTOS 
                    ON MITYC_ESTABLECIMIENTOS.ID_EESS_MITYC = PRODUCTOS_ESTABLECIMIENTOS.CODIGO_EESS
                LEFT JOIN MITYC_MUNICIPIOS 
                    ON MITYC_MUNICIPIOS.CODIGO = MITYC_ESTABLECIMIENTOS.CODIGO_MUNICIPIO
                LEFT JOIN MITYC_PRODUCTOS 
                    ON MITYC_PRODUCTOS.CODIGO = PRODUCTOS_ESTABLECIMIENTOS.CODIGO_PRODUCTO
            ORDER BY MITYC_ESTABLECIMIENTOS.NOMBRE_ESTABLECIMIENTO ASC, MITYC_PRODUCTOS.NOMBRE ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $productosEstablecimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $productosEstablecimiento;
    }

    /**
     * Devuelve un array con todos los establecimientos de una empresa
     */
    public function getEstablecimientosEmpresa_general($empresa){
        $sql = "SELECT EMPRESAS.EMPRESA NOMBRE_EMPRESA, MITYC_MUNICIPIOS.NOMBRE NOMBRE_MUNICIPIO, MITYC_ESTABLECIMIENTOS.NOMBRE_ESTABLECIMIENTO, MITYC_ESTABLECIMIENTOS.CODIGO_TERMINAL 
            FROM MITYC_ESTABLECIMIENTOS 
                INNER JOIN ESTABLECIMIENTOS_EMPRESA 
                    ON ESTABLECIMIENTOS_EMPRESA.CODIGO_EESS = MITYC_ESTABLECIMIENTOS.ID_EESS_MITYC
                INNER JOIN EMPRESAS 
                    ON EMPRESAS.CODIGO = ESTABLECIMIENTOS_EMPRESA.CODIGO_EMPRESA
                INNER JOIN MITYC_MUNICIPIOS 
                    ON MITYC_MUNICIPIOS.CODIGO = MITYC_ESTABLECIMIENTOS.CODIGO_MUNICIPIO
                WHERE CODIGO_EMPRESA = :empresa
            ORDER BY MITYC_ESTABLECIMIENTOS.NOMBRE_ESTABLECIMIENTO ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':empresa', $empresa);
        $stmt->execute();

        $establecimientosEmpresa = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $establecimientosEmpresa;
    }
    /* -----------------------------------------------------------------------------------------------------*/


    /* --------------------------------- SETTERS ------------------------------------------------------*/

    /**
     * Añade una empresa a la base de datos
     */
    public function addEmpresa($nombre, $ip, $puerto)
    {
        $sql = "INSERT INTO EMPRESAS (EMPRESA, IP, PUERTO) VALUES (:empresa, :ip, :puerto)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':empresa', $nombre);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':puerto', $puerto);

        if($stmt->execute()){
            $this->log->write("Se ha añadido la empresa '".$nombre."', de la tabla EMPRESAS, con los datos: IP: '".$ip."', Puerto: '".$puerto."'");
            $this->log->close();
            return true;
        }else{
            $this->log->write("Error al añadir la empresa '".$nombre."', de la tabla EMPRESAS, con los datos: IP: '".$ip."', Puerto: '".$puerto."'");
            $this->log->close();
            return false;
        }
    }

    /**
     * Añade un municipio a la base de datos
     */
    public function addMunicipio($id, $nombre)
    {
        $sql = "INSERT INTO MITYC_MUNICIPIOS (CODIGO, NOMBRE) VALUES (:codigo, :nombre)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigo', $id);
        $stmt->bindParam(':nombre', $nombre);

        if($stmt->execute()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Añade un establecimiento a la base de datos
     */
    public function addEstablecimiento($IDEESS, $IDMunicipio, $empresas,$rotulo, $termminal, $estado)
    {
        $result = false;

        $sql = "INSERT INTO MITYC_ESTABLECIMIENTOS (ID_EESS_MITYC, CODIGO_MUNICIPIO, NOMBRE_ESTABLECIMIENTO, CODIGO_TERMINAL, ESTADO) 
            VALUES (:ideess, :codmunicipio, :nombre_establecimiento, :codigo_terminal, :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':ideess', $IDEESS);
        $stmt->bindParam(':codmunicipio', $IDMunicipio);
        $stmt->bindParam(':nombre_establecimiento', $rotulo);
        $stmt->bindParam(':codigo_terminal', $termminal);
        $stmt->bindParam(':estado', $estado);

        if($stmt->execute()){
            $this->log->write("Se ha añadido el establecimiento '".$IDEESS."', de la tabla MITYC_ESTABLECIMIENTOS, con los datos: Municipio: '".$IDMunicipio."', Nombre: '".$rotulo."', Terminal: '".$termminal."'");
            $this->log->close();
            $result = true;
        }else{
            $this->log->write("Error al añadir el establecimiento '".$IDEESS."', de la tabla MITYC_ESTABLECIMIENTOS, con los datos: Municipio: '".$IDMunicipio."', Nombre: '".$rotulo."', Terminal: '".$termminal."'");
            $this->log->close();
            $result = false;
        }

        return $result;
    }

    /**
     * Añade los precios a la tabla de envios
     * @param $data array con los datos de los precios
     */
    public function addPrecios($data){
        $return = false;
        $sql = "INSERT INTO MITYC_PRECIOS_ENVIO (CODIGO_EESS, CODIGO_EMPRESA, CODIGO_PRODUCTO_CA, PRECIO, FECHA_INS) VALUES (:codigo_eess, :codigo_empresa, :codigo_producto_ca, :precio, :fecha_ins)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigo_eess', $data['CODIGO_EESS']);
        $stmt->bindParam(':codigo_empresa', $data['CODIGO_EMPRESA']);

        $fecha_actual = date("Y-m-d H:i:s");
    
        foreach ($data['PRECIOS'] as $key => $precio) {
            for($i = 0; $i < count($precio); $i++){
                $stmt->bindParam(':codigo_producto_ca', $precio[$i]['CODIGO_PRODUCTO_CA']);
                $stmt->bindParam(':precio', $precio[$i]['PRECIO']);
                $stmt->bindParam(':fecha_ins', $fecha_actual);
                if($stmt->execute()){
                    $this->log->write("Se ha añadido el envio precios de la estacion '".$data['CODIGO_EESS']."' con el producto '".$precio[$i]['CODIGO_PRODUCTO_CA']."' y el precio '".$precio[$i]['PRECIO']."', de la tabla MITYC_PRECIOS_ENVIO");
                    $this->log->close();
                    $return = true;
                }else{
                    $return = false;
                }
            }
        }

        return $return;
    }

    /* -----------------------------------------------------------------------------------------------------*/


    /* --------------------------------- DELETE ------------------------------------------------------*/

    /**
     * Elimina una empresa de la base de datos
     */
    public function deleteEmpresa($id)
    {
        $sql = "DELETE FROM EMPRESAS WHERE CODIGO = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        if($stmt->execute()){
            $this->log->write("Se ha eliminado la empresa '".$id."', de la tabla EMPRESAS");
            $this->log->close();
            return true;
        }else{
            $this->log->write("Error al eliminar la empresa '".$id."', de la tabla EMPRESAS");
            $this->log->close();
            return false;
        }
    }

    /**
     * Eliminar envio
     */
    public function eliminar_envio($codigo_eess, $codigo_empresa, $codigo_producto_ca, $precio, $fecha_ins){
        //format date
        $date1 = strtr($fecha_ins, '/', '-');
        $fecha_ins = date('Y-m-d H:i:s', strtotime($date1));

        $sql = "DELETE FROM MITYC_PRECIOS_ENVIO WHERE CODIGO_EESS = :codigo_eess AND CODIGO_EMPRESA = :codigo_empresa AND CODIGO_PRODUCTO_CA = :codigo_producto_ca AND PRECIO = :precio AND FECHA_INS = :fecha_ins";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigo_eess', $codigo_eess);
        $stmt->bindParam(':codigo_empresa', $codigo_empresa);
        $stmt->bindParam(':codigo_producto_ca', $codigo_producto_ca);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':fecha_ins', $fecha_ins);

        if($stmt->execute()){
            $this->log->write("Se ha eliminado el envio de la estacion '".$codigo_eess."' con el producto '".$codigo_producto_ca."' y el precio '".$precio."' insertado el dia '".$fecha_ins."', de la tabla MITYC_PRECIOS_ENVIO");
            $this->log->close();
            return true;
        }else{
            $this->log->write("Error al eliminar el envio de la estacion '".$codigo_eess."' con el producto '".$codigo_producto_ca."' y el precio '".$precio."' insertado el dia '".$fecha_ins."', de la tabla MITYC_PRECIOS_ENVIO");
            $this->log->close();
            return false;
        }
    }

    /**
     * Elimina un establecimiento de la base de datos
     */
    public function deleteEstablecimiento($id)
    {
        $sql = "DELETE FROM MITYC_ESTABLECIMIENTOS WHERE ID_EESS_MITYC = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        if($stmt->execute()){
            $this->log->write("Se ha eliminado el establecimiento '".$id."', de la tabla MITYC_ESTABLECIMIENTOS");
            $this->log->close();
            return true;
        }else{
            $this->log->write("Error al eliminar el establecimiento '".$id."', de la tabla MITYC_ESTABLECIMIENTOS");
            $this->log->close();
            return false;
        }
    }
    /* -----------------------------------------------------------------------------------------------------*/



    /* --------------------------------- UPDATE ------------------------------------------------------*/

    /**
     * Edita una empresa de la base de datos
     */
    public function editEmpresa($id, $nombre, $ip, $puerto)
    {
        $sql = "UPDATE EMPRESAS SET EMPRESA = :empresa, IP = :ip, PUERTO = :puerto WHERE CODIGO = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':empresa', $nombre);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':puerto', $puerto);

        if($stmt->execute()){
            $this->log->write("Se ha editado la empresa '".$id."', de la tabla EMPRESAS, con los datos: Nombre: '".$nombre."', IP: '".$ip."', Puerto: '".$puerto."'");
            $this->log->close();
            return true;
        }else{
            $this->log->write("Error al editar la empresa '".$id."', de la tabla EMPRESAS, con los datos: Nombre: '".$nombre."', IP: '".$ip."', Puerto: '".$puerto."'");
            $this->log->close();
            return false;
        }
    }

    /**
     * Esta funcion se encarga de actualizar los productos de una estacion en la tabla de productos_establecimientos
     */
    public function updatePrefenrenciaEstablecimiento($codigo_eess, $nombre_eess, $productos, $cod_terminal, $empresas, $estado)
    {
        $result = false;

        $update_terminal = "UPDATE MITYC_ESTABLECIMIENTOS SET CODIGO_TERMINAL = :codigo_terminal, NOMBRE_ESTABLECIMIENTO = :nombre_establecimiento, ESTADO = :estado WHERE ID_EESS_MITYC = :codigo_eess";
        $stmt = $this->db->prepare($update_terminal);
        $stmt->bindParam(':codigo_eess', $codigo_eess);
        $stmt->bindParam(':codigo_terminal', $cod_terminal);
        $stmt->bindParam(':nombre_establecimiento', $nombre_eess);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute() ? $result = true : $result = false;


        // eliminar todos los productos de la estacion que no esten en el array
        $sql = "DELETE FROM PRODUCTOS_ESTABLECIMIENTOS WHERE CODIGO_EESS = :codigo_eess AND CODIGO_PRODUCTO NOT IN (";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigo_eess', $codigo_eess);
        $codigo_producto = "";
        foreach ($productos as $key => $value) {
            $codigo_producto .= "'$value',";
        }
        $codigo_producto = substr($codigo_producto, 0, -1);
        $sql .= $codigo_producto . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigo_eess', $codigo_eess);
        if($stmt->execute()){
            // añadir si no existen
            $sql = "INSERT INTO PRODUCTOS_ESTABLECIMIENTOS (CODIGO_EESS, CODIGO_PRODUCTO) VALUES (:codigo_eess, :codigo_producto)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':codigo_eess', $codigo_eess);
            $stmt->bindParam(':codigo_producto', $codigo_producto);
    
            foreach ($productos as $key => $value) {
                $codigo_producto = $value;
                if(!$this->ExistProductoEst($codigo_eess, $codigo_producto)){
                    if(!$stmt->execute()){
                        $result = false;
                    }
                }
            }
        }
        $this->addEstablecimientoEmpresa($codigo_eess, $empresas) ? $result = true : $result = false;

        return $result;
    }

    /*
    * Añade las estaciones a la tabla de productos establecimientos para luego poder consultar los precios y enviarlos
    */
   public function addPrefenrenciaEstablecimiento($codigo_eess, $productos, $terminal, $empresas)
   {
       $result = false;
       
       //insert terminal
       $sql = "UPDATE MITYC_ESTABLECIMIENTOS SET CODIGO_TERMINAL = :codigo_terminal WHERE ID_EESS_MITYC = :codigo_eess";
       $stmt = $this->db->prepare($sql);
       $stmt->bindParam(':codigo_eess', $codigo_eess);
       $stmt->bindParam(':codigo_terminal', $terminal);
       $stmt->execute() ? $result = true : $result = false;


       $sql = "INSERT INTO PRODUCTOS_ESTABLECIMIENTOS (CODIGO_EESS, CODIGO_PRODUCTO) VALUES (:codigo_eess, :codigo_producto)";
       $stmt = $this->db->prepare($sql);
       $stmt->bindParam(':codigo_eess', $codigo_eess);
       $stmt->bindParam(':codigo_producto', $codigo_producto);

       foreach ($productos as $key => $value) {
           $codigo_producto = $value;
           if(!$stmt->execute()){
               $result = false;
           }
       }
       $this->addEstablecimientoEmpresa($codigo_eess, $empresas) ? $result = true : $result = false;

       return $result;
   }


    public function addEstablecimientoEmpresa($establecimiento, $empresas){
        $result = false;
        if(count($empresas) == 0){
            return false;
        }
        if($establecimiento == ""){
            return false;
        }

        // Empresas es un array y se insertan en la tabla ESTABLECIMIENTOS_EMPRESA solo si no existen y se borran las que no esten en el array
        $sql2 = "INSERT INTO ESTABLECIMIENTOS_EMPRESA (CODIGO_EESS, CODIGO_EMPRESA) VALUES (:establecimiento, :empresa)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->bindParam(':establecimiento', $establecimiento);

        $actuales = $this->getEstablecimientoEmpresa($establecimiento);
        $actuales = array_column($actuales, 'CODIGO_EMPRESA');

        foreach ($empresas as $key => $value) {
            if(!in_array($value, $actuales)){
                $stmt2->bindParam(':empresa', $value);
                $stmt2->execute() ? $result = true : $result = false;
            }else{
                $result = true;
            }
        }

        // Eliminar las empresas que no esten en el array
        $sql3 = "DELETE FROM ESTABLECIMIENTOS_EMPRESA WHERE CODIGO_EESS = :establecimiento AND CODIGO_EMPRESA NOT IN (";
        $stmt3 = $this->db->prepare($sql3);
        $stmt3->bindParam(':establecimiento', $establecimiento);
        $empresas = implode("','", $empresas);
        $sql3 .= "'$empresas')";
        $stmt3 = $this->db->prepare($sql3);
        $stmt3->bindParam(':establecimiento', $establecimiento);
        $stmt3->execute() ? $result = true : $result = false;

        return $result;
    }

    /* -----------------------------------------------------------------------------------------------------*/





    /* --------------------------------- ETC ------------------------------------------------------*/
    /**
     * Login
     */
    public function Login($usuario, $pwd){
        $sql = "SELECT * FROM USUARIOS WHERE USUARIO = :usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($usuario) > 0){
            if(password_verify($pwd, $usuario[0]['PASSWORD'])){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Arreglo de los pronombres de las provincias y municipios asignados por el MITYC
     */
    public function orderName($nombre) {
        //lista de pronombres
        $pronombres = array("A", "EL", "LA", "LOS", "LAS", "DE", "DEL", "AL", "ILLES", "El", "La", "Los", "Las", "De", "Del", "Al", "Illes", "la", "l'", "L'", "els", "Els", "el", "El", "Es", "Sa", "Ses", "Ses", "Les", "les", "O", "As","Os");
        $name = $nombre;
        foreach ($pronombres as $pronombre) {
            if (preg_match("/\s\($pronombre\)$/", $name)) {
                // Eliminar el pronombre del final y moverlo al principio
                $name = $pronombre . " " . substr($name, 0, strlen($name) - strlen($pronombre) - 3);
                break; // Terminar el bucle una vez que se encuentra el pronombre adecuado
            }
        }
        return $name;
    }

    /**
     * Genera un archivo excel con los productos de las estaciones
     */
    public function exportarEstacionesProductos(){
        // Excel file name for download 
        $fileName = "Estaciones_productos_" . date('Ymd') . ".xlsx";

        // Column names
        $excelData[] = array('Código estación', 'Estación', 'Municipio', 'Terminal', 'Producto');

        //get data
        $data = $this->getProductosEstablecimiento_general();
        if(count($data) > 0){
            foreach($data as $row){
                $excelData[] = array($row['CODIGO_EESS'], $row['NOMBRE_ESTABLECIMIENTO'], $row['MUNICIPIO'], $row['CODIGO_TERMINAL'], $row['PRODUCTO']);
            }
        }else{
            $excelData[] = array('No se encontraron datos');
        }

        // Export data to excel and download as xlsx file 
        $xlsx = CodexWorld\PhpXlsxGenerator::fromArray($excelData); 
        $xlsx->setAuthor('soporte@efidata.es');
        $xlsx->setCompany('Efidata S.L');
        $xlsx->setManager('Néstor Santana');
        $xlsx->setApplication('EfiFuelTracking');
        $xlsx->downloadAs($fileName);
    }


    /**
     * Genera un archivo excel con las estaciones filtradas o no
     */
    public function exportarEstaciones($codigo_estacion = null, $codigo_municipio = null, $estado = null, $empresa = null){
        $fileName = "Listado_estaciones_" . date('Ymd') . ".xlsx";

        // Column names
        $excelData[] = array('Código estación', 'Estación', 'Municipio', 'Terminal', 'Empresa', 'Estado');

        //get data
        $data = $this->getEstacionesFiltradas(0, 100000, $codigo_estacion, $codigo_municipio, $estado, $empresa);


        if(count($data) > 0){
            foreach($data as $row){
                $empresa = $this->getEstablecimientoEmpresa($row['ID_EESS_MITYC']);

                if(count($empresa) > 1){
                    $empresa = implode(", ", array_column($empresa, 'NOMBRE_EMPRESA'));
                }else{
                    $empresa = $empresa[0]['NOMBRE_EMPRESA'];
                }

                $excelData[] = array($row['ID_EESS_MITYC'], $row['NOMBRE_ESTABLECIMIENTO'], $row['MUNICIPIO'], $row['CODIGO_TERMINAL'], $empresa, $row['ESTADO']);
            }
        }else{
            $excelData[] = array('No se encontraron datos');
        }


        // Export data to excel and download as xlsx file
        $xlsx = CodexWorld\PhpXlsxGenerator::fromArray($excelData);
        $xlsx->setAuthor('soporte@efidata.es');
        $xlsx->setCompany('Efidata S.L');
        $xlsx->setManager('Néstor Santana');
        $xlsx->setApplication('EfiFuelTracking');
        $xlsx->downloadAs($fileName);
    }

    /**
     * Genera un archivo excel con las estaciones de una empresa
     */
    public function exportarEstacionesEmpresa($empresa){
        // Excel file name for download 
        $fileName = "Listado_estaciones_empresa_" . date('Ymd') . ".xlsx";

        // Column names
        $excelData[] = array('NOMBRE_EMPRESA', "NOMBRE_MUNICIPIO", "NOMBRE_ESTABLECIMIENTO", "CODIGO_TERMINAL");

        //get data
        $data = $this->getEstablecimientosEmpresa_general($empresa);
        if(count($data) > 0){
            foreach($data as $row){
                $excelData[] = array($row['NOMBRE_EMPRESA'], $row['NOMBRE_MUNICIPIO'], $row['NOMBRE_ESTABLECIMIENTO'], $row['CODIGO_TERMINAL']);
            }
        }else{
            $excelData[] = array('No se encontraron datos');
        }

        // Export data to excel and download as xlsx file 
        $xlsx = CodexWorld\PhpXlsxGenerator::fromArray($excelData); 
        $xlsx->setAuthor('soporte@efidata.es');
        $xlsx->setCompany('Efidata S.L');
        $xlsx->setManager('Néstor Santana');
        $xlsx->setApplication('EfiFuelTracking');
        $xlsx->downloadAs($fileName);
    }


    public function GetFiles(){
        //get files from ../log
        $files = scandir("../log");
        $files = array_diff($files, array('.', '..'));
        $files = array_values($files);

        $array_data = array();
        foreach ($files as $key => $value) {
            //save name file, date and size
            $array_data[$key]['name'] = $value;
            $array_data[$key]['date'] = date("d/m/Y H:i:s", filemtime("../log/$value"));
            $array_data[$key]['size'] = number_format(filesize("../log/$value") / 1024, 2) . "KB";

            $content = file_get_contents("../log/$value");
            $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

            //convent STX and ETX to STX and ETX
            $content = str_replace(chr(2), "<b class='text-danger s'>STX</b>", $content);
            $content = str_replace(chr(3), "<b class='text-danger s'>ETX</b>", $content);

            $content = base64_encode($content);
            $array_data[$key]['content'] = $content;
        }
        $files = array_reverse($array_data);

        return $files;
    }
    /* -----------------------------------------------------------------------------------------------------*/

}
