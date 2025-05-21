<?php
    include_once 'inc/funciones.php';
    $funciones = new Funciones();
    
    //si el metodo es post
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Guardamos los datos del formulario
        $id_eess = $_POST['IDEESS'];
        $terminal = $_POST['terminal'];
        $productos = $_POST['productos'];
        $empresas = $_POST['empresas'];
        $nombre_eess = $_POST['nombre'];
        $estado = $_POST['estado'] ?? 1;

        if($id_eess  == '' || $terminal == '' || $productos == '' || $empresas == '' || $nombre_eess == '') {
            Header("Location: editar-estacion.php?error=1&id_eess=".$id_eess);
        }else{
            if($funciones->updatePrefenrenciaEstablecimiento($id_eess, $nombre_eess, $productos, $terminal, $empresas, $estado)) {
                Header("Location: listado-estaciones.php?success=1");
            } else {
                Header("Location: editar-estacion.php?error=1&id_eess=".$id_eess);
            }
        }
    }else{
        if(isset($_GET['id_eess'])) {
            // Guardamos los datos del formulario
            $id_eess = $_GET['id_eess'];
    
            $estacion = $funciones->getEstacion($id_eess);
            if($estacion == null) {
                Header("Location: listado-estaciones.php?error=1");
            }

            $codigo_terminal = $estacion['CODIGO_TERMINAL'] ?? null;
    
        }else{
            Header("Location: listado-estaciones.php?error=1");
        }
    }

    include_once 'inc/header.php';
?>
<?php if(isset($_GET['id_eess'])): ?>
<div class="row">
    <div class="col-lg-12 p-4">
    <h2>Editando estación: <b><?php echo $estacion['NOMBRE_ESTABLECIMIENTO']; ?></b></h2>
    <hr />
  </div>
</div>

<div class="container">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="form-estacion">
        <div class="row">
            <div class="col">
                <label for="nombre" class="form-label">(*)Nombre estación</label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre de la estación" value="<?php echo $estacion['NOMBRE_ESTABLECIMIENTO']; ?>" required/>
            </div>
            <div class="col">
                <label for="codestacion" class="form-label">Código Estación </label>
                <input type="text" class="form-control" id="codestacion" placeholder="Código Estación" value="<?php echo $estacion['ID_EESS_MITYC']; ?>" disabled/>
                <input type="hidden" name="IDEESS" value="<?php echo $estacion['ID_EESS_MITYC']; ?>" />
            </div>
        </div>
        <div class="row pt-2">
            <div class="col">
                <label for="rotulo" class="form-label">(*)Código Terminal</label>
                <input type="text" class="form-control" id="terminal" name="terminal" placeholder="Código del terminal de la estación" pattern="[a-zA-Z0-9]*" value="<?php echo isset($codigo_terminal) ? $codigo_terminal : ""; ?>" required/>
                <small class="text-muted">Código del terminal o número de serie del datáfono.</small>
            </div>
            <div class="col">
                <label for="codmunicipio" class="form-label">Código Municipio</label>
                <input type="text" class="form-control" id="codmunicipio" placeholder="Código Municipio" value="<?php echo $estacion['CODIGO_MUNICIPIO']; ?>" disabled/>
                <input type="hidden" name="Municipio" value="<?php echo $estacion['CODIGO_MUNICIPIO']; ?>" />
            </div>
        </div>
        <div class="row pt-2">
            <div class="col">
                <label for="productos" class="form-label">(*)Productos</label>
                <select class="selector-est" name="productos[]" multiple id="selected_producto" required>
                    <?php
                        $productos = $funciones->getProductos();
                        $productos_establecimiento = $funciones->getProductosEstablecimiento($id_eess);

                        foreach ($productos as $producto) {
                            foreach ($productos_establecimiento as $producto_establecimiento) {
                                if ($producto['CODIGO'] === $producto_establecimiento['CODIGO_PRODUCTO']) {
                                    $selected = 'selected';
                                    break;
                                }else{
                                    $selected = '';
                                }
                            }

                            echo '<option value="' . $producto['CODIGO'] . '" ' . $selected . '>' . $producto['NOMBRE'] . '</option>';
                        }
                    ?>
                </select>
                <small class="text-muted">Selecciona los productos que desea que se envien los precios.</small>
            </div>
            <div class="col">
                <label for="empresa" class="form-label">(*)Empresa</label>
                <select class="selector-est" name="empresas[]" multiple id="selected_empresa" required>
                    <?php
                        $selected = '';
                        $empresas = $funciones->getEmpresas();
                        $establecimientos_empresa = $funciones->getEstablecimientoEmpresa($id_eess);

                        foreach ($empresas as $empresa) {
                            foreach ($establecimientos_empresa as $establecimiento_empresa) {
                                if ($empresa['CODIGO'] === $establecimiento_empresa['CODIGO_EMPRESA']) {
                                    $selected = 'selected';
                                    break;
                                }else{
                                    $selected = '';
                                }
                            }

                            echo '<option value="' . $empresa['CODIGO'] . '" ' . $selected . '>' . $empresa['EMPRESA'] . '</option>';
                        }
                    ?>
                </select>
                <small class="text-muted">Selecciona la empresa a la que pertenece la estación, las empresas se añaden desde la sección <a href="empresas.php" target="_blank">empresas</a>.</small>
            </div>
        </div>

        <div class="row pt-2">
            <div class="col">
                <label for="estado" class="form-label">(*)Estado</label>
                <select class="form-select" name="estado"  required>
                    <option value="1" <?php echo $estacion['ESTADO'] == '1' ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo $estacion['ESTADO'] == '0' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
                <small class="text-muted">Estado de la estación, solo se enviaran los precios de las estaciones activas.</small>
            </div>
            <div class="col">

            </div>
        </div>
        <a href="listado-estaciones.php" class="btn btn-warning mt-3"><i class="bi bi-arrow-left-circle-fill"></i> Volver atrás</a>
        <button type="submit" class="btn btn-success mt-3" id="sav"><i class="bi bi-check-circle-fill"></i> Guardar cambios</button>
    </form>
</div>
<?php endif; ?>
<?php include 'inc/footer.php'; ?>