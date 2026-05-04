<?php

if (isset($_POST['bsht'])) {
    if (isset($_POST['IDEESS']) && isset($_POST['nombre']) && isset($_POST['IDMunicipio']) && isset($_POST['Municipio']) && isset($_POST['productos']) && isset($_POST['empresa'])) {
        require 'inc/funciones.php';
        $funciones = new Funciones();

        $IDEESS      = $_POST['IDEESS'];
        $rotulo      = $_POST['nombre'];
        $IDMunicipio = $_POST['IDMunicipio'];
        $Municipio   = $_POST['Municipio'];
        $productos   = $_POST['productos'];
        $id_empresa    = $_POST['empresa'];
        $terminal  = $_POST['terminal'] ?? [];  // array: [cod_terminal => terminal]
        $estado      = $_POST['estado'] ?? 1;

        // Validar que tiene la empresa terminal
        if (empty($terminal)) {
            Header("Location: ../estacion.php?error=1&bsht=" . $_POST['bsht']);
            exit;
        }


        // Comprobar y añadir municipio si no existe
        if (!$funciones->ExistMunicipio($IDMunicipio)) {
            if (!$funciones->addMunicipio($IDMunicipio, $Municipio)) {
                Header("Location: ../estacion.php?error=1&bsht=" . $_POST['bsht']);
                exit;
            }
        }

        if (!$funciones->addEstablecimiento($IDEESS, $IDMunicipio, $id_empresa, $rotulo, $terminal, $estado)) {
            Header("Location: ../estacion.php?error=3&bsht=" . $_POST['bsht']);
            exit;
        }


        // Añadir o actualizar preferencias
        if (!$funciones->AlreadyAdded($IDEESS, $id_empresa)) {
            if ($funciones->addPreferenciaEstablecimiento($IDEESS, $productos, $terminal, $id_empresa, $estado)) {
                Header("Location: ../listado-estaciones.php?success=1&estacion=" . $IDEESS);
            } else {
                Header("Location: ../estacion.php?error=4&bsht=" . $_POST['bsht']);
            }
        } else {
            if ($funciones->updatePrefenrenciaEstablecimiento($IDEESS, $rotulo, $productos, $terminal, $id_empresa, $estado)) {
                Header("Location: ../listado-estaciones.php?success=1&estacion=" . $IDEESS);
            } else {
                Header("Location: ../estacion.php?error=5&bsht=" . $_POST['bsht']);
            }
        }
    } else {
        Header("Location: ../estacion.php?error=6&bsht=" . $_POST['bsht']);
    }
} else {
    Header("Location: ../dash.php?error=0");
}
