<?php
    include_once 'inc/funciones.php';
    if (isset($_GET['bsht'])) {

        //decodificamos el base64
        $base64_encoded = $_GET['bsht'];
        $decoded_data = base64_decode($base64_encoded);
        $decoded_data = json_decode($decoded_data, true);

        //municipio i/o provincia seleccionada
        $prev_municipio = isset($_GET['m']) ? $_GET['m'] : null;
        $prev_provincia = isset($_GET['p']) ? $_GET['p'] : null;
            
        //instanciamos la clase funciones
        $funciones = new Funciones();

        //comprobamos que exista el parametro IDEESS y el parametro IDMunicipio
        if($decoded_data['IDEESS'] == null || $decoded_data['IDMunicipio'] == null){
            Header("Location: mityc.php?error=1&bsht=" . $_GET['bsht']."&municipio=".$prev_municipio."&provincia=".$prev_provincia);
        }

        //comprobamos que exista el establecimiento y el municipio
        $exist_establecimiento = $funciones->ExistEstablecimiento($decoded_data['IDEESS']);
        $exist_municipio = $funciones->ExistMunicipio($decoded_data['IDMunicipio']);

        //si existe el establecimiento obtenemos los productos y la empresa
        if($exist_establecimiento){
            $productos_establecimiento = $funciones->getProductosEstablecimiento($decoded_data['IDEESS']);
            $empresa_establecimiento = $funciones->getEmpresaEstablecimiento($decoded_data['IDEESS']);
            $codigo_terminal = $funciones->getCodigoTerminal($decoded_data['IDEESS']);
        }

        $estado = $funciones->GetEstadoEESS($decoded_data['IDEESS']) ?? 1;

        //delete establecimiento
        if(isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['id'])){
            if($funciones->deleteEstablecimiento($_GET['id'])){
                Header("Location: dash.php?success=1");
            }else{
                Header("Location: estacion.php?error=1");
            }
        }

    } else {
        Location("Location: ../dash.php?error=0");
    }

    include_once 'inc/header.php';
?>
<div class="row">
    <div class="col-lg-12 p-4">
    <h2>Estación (Rótulo): <b><?php echo $decoded_data['Rótulo']; ?></b></h2>
    <hr />
  </div>
</div>

<div class="container">
    <form action="agregar-estacion.php" method="POST">
        <div class="row">
            <input type="hidden" name="bsht" value="<?php echo $_GET['bsht']; ?>" />
            <input type="hidden" name="rotulo" value="<?php echo $decoded_data['Rótulo']; ?>" />

            <div class="col">
                <label for="codestacion" class="form-label">Código Estación <?php echo $exist_establecimiento ? "<i class='bi bi-check-circle-fill text-success' title='La estación ya existe en la base de datos'></i>" : ""; ?></label>
                <input type="text" class="form-control" id="codestacion" placeholder="Código Estación" value="<?php echo $decoded_data['IDEESS']; ?>" disabled/>
                <input type="hidden" name="IDEESS" value="<?php echo $decoded_data['IDEESS']; ?>" />
            </div>
            <div class="col">
                <label for="codmunicipio" class="form-label">Código Municipio <?php echo $exist_municipio ? "<i class='bi bi-check-circle-fill text-success' title='El municipio ya existe en la base de datos'></i>" : ""; ?></label>
                <input type="text" class="form-control" id="codmunicipio" placeholder="Código Municipio" value="<?php echo $decoded_data['IDMunicipio']; ?>" disabled/>
                <input type="hidden" name="Municipio" value="<?php echo $decoded_data['Municipio']; ?>" />
            </div>
            <div class="col">
                <label for="municipio" class="form-label">Municipio   <?php echo $exist_municipio ? "<i class='bi bi-check-circle-fill text-success' title='El municipio ya existe en la base de datos'></i>" : ""; ?></label>
                <input type="text" class="form-control" id="municipio" placeholder="Municipio" value="<?php echo $funciones->orderName($decoded_data['Municipio']); ?>" disabled/>
                <input type="hidden" name="IDMunicipio" value="<?php echo $decoded_data['IDMunicipio']; ?>" />
            </div>
            <div class="col">
                <label for="provincia" class="form-label">Provincia</label>
                <input type="text" class="form-control" id="provincia" placeholder="Provincia" value="<?php echo $decoded_data['Provincia']; ?>" disabled/>
            </div>
        </div>
        <div class="row pt-2">
            <div class="col">
                <label for="nombre" class="form-label">(*)Nombre estación</label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre de la estación" value="<?php echo $decoded_data['Rótulo']; ?>" required/>
                <small class="text-muted">Nombre de la estación o rótulo.</small>
            </div>
            <div class="col">
                <label for="rotulo" class="form-label">(*)Código Terminal</label>
                <input type="text" class="form-control" id="terminal" name="terminal" placeholder="Código del terminal de la estación" pattern="[a-zA-Z0-9]*" value="<?php echo isset($codigo_terminal) ? $codigo_terminal : ""; ?>" required/>
                <small class="text-muted">Código del terminal o número de serie del datáfono.</small>
            </div>
            <div class="col">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" placeholder="Dirección" value="<?php echo $decoded_data['Dirección']; ?>" disabled/>
            </div>
            <div class="col">
                <label for="margen" class="form-label">Margen</label>
                <input type="text" class="form-control" id="margen" placeholder="Margen" value="<?php echo $decoded_data['Margen']; ?>" disabled/>
            </div>
        </div>
        <div class="row pt-2">
            <div class="col">
                <label for="productos" class="form-label">(*)Productos</label>
                <select class="selector-est" name="productos[]" multiple id="selected_producto" required>
                    <?php
                        $productos = $funciones->getProductos();

                        foreach ($decoded_data as $key => $value) {
                            if (strpos($key, 'Precio') !== false && $value != null) {
                                $selected = '';

                                if (isset($productos_establecimiento)) {
                                    foreach ($productos_establecimiento as $producto_establecimiento) {
                                        if ($producto_establecimiento['CODIGO_PRODUCTO'] == $key) {
                                            $selected = 'selected';
                                            break; // Ya encontramos el producto seleccionado, no es necesario seguir buscando.
                                        }
                                    }
                                }

                                echo "<option value='" . $key . "' " . $selected . ">" . str_replace("Precio", "", $key) . "</option>";
                            }
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
                        $establecimientos_empresa = $funciones->getEstablecimientoEmpresa($decoded_data['IDEESS']);

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

            <div class="col">
                <label for="estado" class="form-label">(*)Estado</label>
                <select class="form-select" name="estado"  required>
                    <option value="1" <?php echo $estado == '1' ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo $estado == '0' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
                <small class="text-muted">Estado de la estación, solo se enviaran los precios de las estaciones activas (Por defecto Activo).</small>
            </div>
        </div>
        <a href="mityc.php?provincia=<?php echo $prev_provincia."&municipio=".$prev_municipio; ?>" class="btn btn-warning mt-3"><i class="bi bi-arrow-left-circle-fill"></i> Volver atrás</a>
        <button class="btn btn-primary mt-3" type="button" data-bs-toggle="modal" data-bs-target="#precioModal"><i class="bi bi-currency-exchange"></i> Ver precios</button>
        <?php
            if(isset($exist_establecimiento) && $exist_establecimiento){
                echo "<a href='#' data-bs-toggle='modal' data-bs-target='#modalEliminarEst' class='btn btn-danger mt-3'><i class='bi bi-trash'></i> Eliminar estación</a>";
            }
        ?>
        <button type="submit" class="btn btn-success mt-3" id="sav"><i class="bi bi-check-circle-fill"></i> <?php if(isset($exist_establecimiento) && $exist_establecimiento){echo "Guardar cambios";}else{echo "Añadir estación";} ?></button>
    </form>

    <!-- Modal eliminar-->
    <div class="modal fade" id="modalEliminarEst" tabindex="-1" aria-labelledby="modalEliminarEstLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <form action="<?php echo $_SERVER['PHP_SELF']."?action=delete&id=" . $decoded_data['IDEESS']."&bsht=" . $_GET['bsht']; ?>" method="POST">
                    <input type="hidden" name="IDEESS" value="<?php echo $decoded_data['IDEESS']; ?>" />
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEliminarEstLabel">Eliminar estación</h5>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que quieres eliminar la estación?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                        <button type="submit" class="btn btn-danger">Si</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Precios Modal -->
    <div class="modal fade" id="precioModal" tabindex="-1" aria-labelledby="precioModal" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="precioModal"><?php echo $decoded_data['Rótulo']; ?> - Precios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table id="precios" class="table table-striped table-bordered" style="width:100%">
                        <thead class="text-center">
                            <tr>
                                <th>Producto</th>
                                <th>Precio €/L</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($decoded_data as $key => $value) {
                                    if (strpos($key, 'Precio') !== false && $value != null) {
                                        echo "<tr>";
                                        echo "<td>" . str_replace("Precio", "", $key) . "</td>";
                                        echo "<td>" . $value . "€</td>";
                                        echo "</tr>";
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector("input#terminal").addEventListener("input", function(){
        const allowedCharacters="0123456789azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBNz"; // You can add any other character in the same way
        
        this.value = this.value.split('').filter(char => allowedCharacters.includes(char)).join('')
    });


    //si success es igual a 1 en la url desabilita el boton de añadir estacion
    if (window.location.href.indexOf("success=1") > -1) {
        document.getElementById("sav").disabled = true;
    }else{
        document.getElementById("sav").disabled = false;
    }

</script>

<?php include 'inc/footer.php'; ?>