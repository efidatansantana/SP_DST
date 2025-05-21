<?php
    include 'inc/header.php'; 
    include_once 'inc/funciones.php'; 

    $funciones = new Funciones();
    $datos_eess = array();

    //if request is post
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        //set id_eess in header
        $id_eess = $_POST['CODIGO_EESS'];
        $datos_eess = $funciones->GetPreciosExistentes($id_eess);

        $post_data = array();
        $post_data['CODIGO_EESS'] = $_POST['CODIGO_EESS'];
        $post_data['CODIGO_MUNICIPIO'] = $_POST['CODIGO_MUNICIPIO'];
        $post_data['CODIGO_EMPRESA'] = $_POST['CODIGO_EMPRESA'];
        $post_data['PRODUCTOS'] = $_POST['productos'];

        foreach($post_data['PRODUCTOS'] as $p){
            $post_data['PRECIOS'] [] = $funciones->GetPreciosEESS($post_data['CODIGO_EESS'], $post_data['CODIGO_MUNICIPIO'], $p);
        }

        if($funciones->addPrecios($post_data)){
            Header("Location: envios.php?success=1");
        }else{
            Header("Location: listado-estaciones.php?error=1");
        }
    }else{
        //get id_eess from url
        if(isset($_GET['id_eess'])){
            $id_eess = $_GET['id_eess'];

            $datos_eess = $funciones->GetPreciosExistentes($id_eess);
            if(!$datos_eess){
                Header("Location: listado-estaciones.php?error=1");
            }
        }else{
            Header("Location: listado-estaciones.php?error=1");
        }
    }
?>

<div class="row">
    <div class="col-lg-12 p-4 ps-0">
        <div class="d-flex align-items-center">
            <a href="listado-estaciones.php" class="btn btn-outline"><h2><i class="bi bi-arrow-left"></i></h2></a>
            <h2><i class="bi bi-fuel-pump-fill pe-2"></i></i>Reenvio de precios</h2>
        </div>
        <hr />
    </div>
</div>

<div class="container">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="form-estacion">
        <div class="row">
            <div class="col">
                <label for="nombre" class="form-label">(*)Nombre estación</label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre de la estación" value="<?php echo $datos_eess[0]['NOMBRE_ESTABLECIMIENTO']; ?>" disabled/>
            </div>
            <div class="col">
                <label for="codestacion" class="form-label">Código Estación </label>
                <input type="text" class="form-control" id="codestacion" placeholder="Código Estación" value="<?php echo $datos_eess[0]['CODIGO_EESS']; ?>" disabled/>
                <input type="hidden" name="CODIGO_EESS" value="<?php echo $datos_eess[0]['CODIGO_EESS']; ?>" />
            </div>
        </div>
        <div class="row pt-2">
            <div class="col">
                <label for="rotulo" class="form-label">(*)Código Terminal</label>
                <input type="text" class="form-control" id="terminal" name="terminal" placeholder="Código del terminal de la estación" pattern="[a-zA-Z0-9]*" value="<?php echo $datos_eess[0]['CODIGO_TERMINAL']; ?>" disabled/>
            </div>
            <div class="col">
                <label for="codmunicipio" class="form-label">Código Municipio</label>
                <input type="text" class="form-control" id="codmunicipio" placeholder="Código Municipio" value="<?php echo $datos_eess[0]['CODIGO_MUNICIPIO']; ?>" disabled/>
                <input type="hidden" name="CODIGO_MUNICIPIO" value="<?php echo $datos_eess[0]['CODIGO_MUNICIPIO']; ?>" />
            </div>
        </div>

        <div class="row pt-2">
            <div class="col">
                <label for="productos" class="form-label">(*)Productos</label>
                <select class="selector-est" name="productos[]" multiple id="selected_producto" required>
                    <?php
                        foreach($datos_eess as $de){
                            echo '<option value="'.$de['CODIGO_PRODUCTO_CA'].'">'.str_replace("Precio ", "", $de['CODIGO_PRODUCTO']).' - '.$de['PRECIO'].'€'.'</option>';
                        }
                    ?>
                </select>
                <small class="text-muted">Lista productos que seleccionaste al añadir la estación.</small>
            </div>
            <div class="col">
                <label for="codmunicipio" class="form-label">Empresa</label>
                <input type="text" class="form-control" id="codEmpresa" placeholder="Empresa" value="<?php echo $datos_eess[0]['EMPRESA']; ?>" disabled/>
                <input type="hidden" name="CODIGO_EMPRESA" value="<?php echo $datos_eess[0]['CODIGO_EMPRESA']; ?>" />
            </div>
        </div>

        <a href="listado-estaciones.php" class="btn btn-warning mt-3"><i class="bi bi-arrow-left-circle-fill"></i> Volver atrás</a>
        <button type="submit" class="btn btn-success mt-3" id="sav"><i class="bi bi-check-circle-fill"></i> Enviar precios</button>
    </form>
</div>

<?php include 'inc/footer.php'; ?>


