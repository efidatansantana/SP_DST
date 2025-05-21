<?php
    include_once 'inc/funciones.php';
    $funciones = new Funciones();

    //si el metodo es GET
    if($_SERVER['REQUEST_METHOD'] == 'GET') {
        if(!isset($_GET['codigo_eess']) || !isset($_GET['codigo_empresa']) || !isset($_GET['codigo_producto_ca']) || !isset($_GET['precio']) || !isset($_GET['fecha_ins'])) {
            Header("Location: envios.php?error=1");
        }

        // Guardamos los datos del formulario
        $codigo_eess = $_GET['codigo_eess'];
        $codigo_empresa = $_GET['codigo_empresa'];
        $codigo_producto_ca = $_GET['codigo_producto_ca'];
        $precio = $_GET['precio'];
        $fecha_ins = $_GET['fecha_ins'];

        if($codigo_eess  == '' || $codigo_empresa == '' || $codigo_producto_ca == '' || $precio == '' || $fecha_ins == '') {
            Header("Location: envios.php?error=1");
        }else{
            //Eliminamos el envio
            if($funciones->eliminar_envio($codigo_eess, $codigo_empresa, $codigo_producto_ca, $precio, $fecha_ins)){
                Header("Location: envios.php?success=1");
            }else{
                Header("Location: envios.php?error=1");
            }
        }
    }else{
        Header("Location: envios.php?error=1");
    }
?>