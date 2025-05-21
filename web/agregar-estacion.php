<?php
if(isset($_POST['bsht'])){
    if(isset($_POST['IDEESS']) && isset($_POST['nombre']) && isset($_POST['IDMunicipio']) && isset($_POST['Municipio']) && isset($_POST['productos']) && isset($_POST['empresas'])) {
        require 'inc/funciones.php';

        //como no, instanciamos la clase funciones
        $funciones = new Funciones();

        //recogemos los datos
        $IDEESS = $_POST['IDEESS'];
        $rotulo = $_POST['nombre'];
        $IDMunicipio = $_POST['IDMunicipio'];
        $Municipio = $_POST['Municipio'];
        $productos = $_POST['productos'];
        $empresas = $_POST['empresas'];
        $terminal = $_POST['terminal'];
        $estado = $_POST['estado'] ?? 1;


        //comprobamos que el municipio exista en la base de datos
        if(!$funciones->ExistMunicipio($IDMunicipio)){
            //si no existe lo añadimos
            if(!$funciones->addMunicipio($IDMunicipio, $Municipio)){
                Header("Location: ../estacion.php?error=1&bsht=" . $_POST['bsht']);
            }
        }

        //comprobamos si existe el establecimiento
        if(!$funciones->ExistEstablecimiento($IDEESS)){
            //si no existe lo añadimos
            if(!$funciones->addEstablecimiento($IDEESS, $IDMunicipio, $empresas, $rotulo, $terminal, $estado)){
                Header("Location: ../estacion.php?error=1&bsht=" . $_POST['bsht']);
            }
        }

        //comprobamos si el establecimiento ya existe en la tabla de productos_establecimiento
        if(!$funciones->AlreadyAdded($IDEESS)){
            //si no existe lo añadimos
            if($funciones->addPrefenrenciaEstablecimiento($IDEESS, $productos, $terminal, $empresas)){
                Header("Location: ../listado-estaciones.php?success=1");
            }else{
                Header("Location: ../estacion.php?error=1&bsht=" . $_POST['bsht']);
            }
        }else{
            //si existe lo actualizamos
            if($funciones->updatePrefenrenciaEstablecimiento($IDEESS, $rotulo, $productos, $terminal, $empresas, $estado)){
                Header("Location: ../listado-estaciones.php?success=1");
            }else{
                Header("Location: ../estacion.php?error=1&bsht=" . $_POST['bsht']);
            }
        }
    }else{
       Header("Location: ../estacion.php?error=1&bsht=" . $_POST['bsht']);
    }
}else{
    Header("Location: ../dash.php?error=0");
}
?>