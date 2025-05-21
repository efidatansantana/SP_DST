<?php
    include_once 'inc/funciones.php';
    $funciones = new Funciones();

    //si el metodo es GET
    if($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Guardamos los datos del formulario
        $data = $_GET['data'];
        
        if($data  == '') {
            Header("Location: listado-estaciones.php?error=1");
        }else{
            // Exportamos los datos
            if($data == 'est_pr') {
                $funciones->exportarEstacionesProductos();
            } else if($data == 'empr_est' && isset($_GET['id_empresa'])) {
                $id_empresa = $_GET['id_empresa'];
                $funciones->exportarEstacionesEmpresa($id_empresa);
            } else if($data == 'listado_estaciones') {
                if(isset($_GET['estacion']) && isset($_GET['municipio']) && isset($_GET['estado']) && isset($_GET['empresa'])){
                    $codigo_estacion = $_GET['estacion'] ?? null;
                    $codigo_municipio = $_GET['municipio'] ?? null;
                    $estado = $_GET['estado'] ?? null;
                    $empresa = $_GET['empresa'] ?? null;

                    $funciones->exportarEstaciones($codigo_estacion, $codigo_municipio, $estado, $empresa);
                }else{
                    Header("Location: listado-estaciones.php?error=1");
                }
            }else {
                Header("Location: listado-estaciones.php?error=1");
            }
        }
    }else{
        Header("Location: listado-estaciones.php?error=1");
    }
?>