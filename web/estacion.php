<?php
include_once 'inc/funciones.php';
if (isset($_GET['bsht'])) {

    $base64_encoded = $_GET['bsht'];
    $decoded_data   = base64_decode($base64_encoded);
    $decoded_data   = json_decode($decoded_data, true);

    $prev_municipio = isset($_GET['m']) ? $_GET['m'] : null;
    $prev_provincia = isset($_GET['p']) ? $_GET['p'] : null;

    $funciones = new Funciones();

    if ($decoded_data['IDEESS'] == null || $decoded_data['IDMunicipio'] == null) {
        Header("Location: mityc.php?error=1&bsht=" . $_GET['bsht'] . "&municipio=" . $prev_municipio . "&provincia=" . $prev_provincia);
    }

    $exist_municipio       = $funciones->ExistMunicipio($decoded_data['IDMunicipio']);
    $exist_establecimiento = $funciones->ExistEstablecimiento($decoded_data['IDEESS']);
    /*$codigo_terminal  = '';
    $empresa_asignada = '';

    if ($exist_establecimiento) {
        $establecimientos_empresa  = $funciones->getEstablecimientoEmpresa($decoded_data['IDEESS']);

        if (!empty($establecimientos_empresa)) {
            $empresa_asignada = $establecimientos_empresa[0]['CODIGO_EMPRESA'];
            $codigo_terminal  = $establecimientos_empresa[0]['CODIGO_TERMINAL'] ?? '';
        }
        $productos_establecimiento = $funciones->getProductosEstablecimiento($decoded_data['IDEESS'], $empresa_asignada);
    }*/

    $estado = $funciones->GetEstadoEESS($decoded_data['IDEESS']) ?? 1;

    if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['id'])) {
        if ($funciones->deleteEstablecimiento($_GET['id'], $empresa_asignada)) {
            Header("Location: dash.php?success=1");
        } else {
            Header("Location: estacion.php?error=1");
        }
    }
} else {
    Header("Location: ../dash.php?error=0");
}

include_once 'inc/header.php';
?>

<div class="row">
    <div class="col-lg-12 p-4">
        <h2>Estación (Rótulo): <b><?php echo htmlspecialchars($decoded_data['Rótulo']); ?></b></h2>
        <hr />
    </div>
</div>

<div class="container">
    <form action="agregar-estacion.php" method="POST">
        <input type="hidden" name="bsht" value="<?php echo $_GET['bsht']; ?>" />
        <input type="hidden" name="rotulo" value="<?php echo htmlspecialchars($decoded_data['Rótulo']); ?>" />

        <!-- Fila 1: Código estación, municipio, nombre municipio, provincia -->
        <div class="row">
            <div class="col">
                <label class="form-label">
                    Código Estación
                    <?php echo $exist_establecimiento ? "<i class='bi bi-check-circle-fill text-success' title='La estación ya existe en la base de datos'></i>" : ""; ?>
                </label>
                <input type="text" class="form-control" value="<?php echo $decoded_data['IDEESS']; ?>" disabled />
                <input type="hidden" name="IDEESS" value="<?php echo $decoded_data['IDEESS']; ?>" />
            </div>
            <div class="col">
                <label class="form-label">
                    Código Municipio
                    <?php echo $exist_municipio ? "<i class='bi bi-check-circle-fill text-success' title='El municipio ya existe en la base de datos'></i>" : ""; ?>
                </label>
                <input type="text" class="form-control" value="<?php echo $decoded_data['IDMunicipio']; ?>" disabled />
                <input type="hidden" name="Municipio" value="<?php echo $decoded_data['Municipio']; ?>" />
            </div>
            <div class="col">
                <label class="form-label">
                    Municipio
                    <?php echo $exist_municipio ? "<i class='bi bi-check-circle-fill text-success' title='El municipio ya existe en la base de datos'></i>" : ""; ?>
                </label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($funciones->orderName($decoded_data['Municipio'])); ?>" disabled />
                <input type="hidden" name="IDMunicipio" value="<?php echo $decoded_data['IDMunicipio']; ?>" />
            </div>
            <div class="col">
                <label class="form-label">Provincia</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($decoded_data['Provincia']); ?>" disabled />
            </div>
        </div>

        <!-- Fila 2: Nombre estación, dirección, margen -->
        <div class="row pt-2">
            <div class="col">
                <label class="form-label">(*)Nombre estación</label>
                <input type="text" class="form-control" id="nombre" name="nombre"
                    placeholder="Nombre de la estación"
                    value="<?php echo htmlspecialchars($decoded_data['Rótulo']); ?>" required />
                <small class="text-muted">Nombre de la estación o rótulo.</small>
            </div>
            <div class="col">
                <label class="form-label">Dirección</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($decoded_data['Dirección']); ?>" disabled />
            </div>
            <div class="col">
                <label class="form-label">Margen</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($decoded_data['Margen']); ?>" disabled />
            </div>
        </div>

        <!-- Fila 3: Productos, Empresa, Terminal, Estado -->
        <div class="row pt-2">
            <!-- Productos -->
            <div class="col">
                <label class="form-label">(*)Productos</label>
                <select class="selector-est" name="productos[]" multiple id="selected_producto" required>
                    <?php
                    $productos = $funciones->getProductos();
                    foreach ($decoded_data as $key => $value) {
                        if (strpos($key, 'Precio') !== false && $value != null) {
                            $selected = '';
                            if (isset($productos_establecimiento)) {
                                foreach ($productos_establecimiento as $pe) {
                                    if ($pe['CODIGO_PRODUCTO'] == $key) {
                                        $selected = 'selected';
                                        break;
                                    }
                                }
                            }
                            echo "<option value='" . $key . "' " . $selected . ">" . str_replace("Precio", "", $key) . "</option>";
                        }
                    }
                    ?>
                </select>
                <small class="text-muted">Selecciona los productos cuyos precios se enviarán.</small>
            </div>

            <!-- Empresa (1 sola) -->
            <div class="col">
                <label class="form-label">(*)Empresa</label>
                <select class="selector-est" name="empresa" required>
                    <?php
                    $todas_empresas = $funciones->getEmpresas();
                    echo '<option value="">-- Selecciona una empresa --</option>';
                    foreach ($todas_empresas as $empresa) {
                        $disabled = $empresa['CODIGO'] == $empresa_asignada ? 'disabled' : '';
                        echo '<option value="' . $empresa['CODIGO'] . '" ' . $disabled . '>' . htmlspecialchars($empresa['EMPRESA']) . '</option>';
                    }
                    ?>
                </select>
                <small class="text-muted">Las empresas se gestionan en <a href="empresas.php" target="_blank">empresas</a>.</small>
            </div>

            <!-- Terminal -->
            <div class="col">
                <label class="form-label">(*)Código Terminal</label>
                <input type="text" class="form-control" name="terminal"
                    placeholder="Código del terminal"
                    pattern="[a-zA-Z0-9]*"
                    value=""
                    required />
                <small class="text-muted">Número de serie del datáfono.</small>
            </div>

            <!-- Estado -->
            <div class="col">
                <label class="form-label">(*)Estado</label>
                <select class="form-select" name="estado" required>
                    <option value="1" <?php echo $estado == '1' ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo $estado == '0' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
                <small class="text-muted">Solo se enviarán los precios de las estaciones activas.</small>
            </div>
        </div>

        <!-- Botones -->
        <div class="row mt-3">
            <div class="col">
                <a href="mityc.php?provincia=<?php echo $prev_provincia . "&municipio=" . $prev_municipio; ?>" class="btn btn-warning">
                    <i class="bi bi-arrow-left-circle-fill"></i> Volver atrás
                </a>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#precioModal">
                    <i class="bi bi-currency-exchange"></i> Ver precios
                </button>
                <button type="submit" class="btn btn-success" id="sav">
                    <i class="bi bi-check-circle-fill"></i>
                    <?php echo (isset($exist_establecimiento) && $exist_establecimiento) ? "Guardar cambios" : "Añadir estación"; ?>
                </button>
            </div>
        </div>
    </form>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="modalEliminarEst" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo $_SERVER['PHP_SELF'] . "?action=delete&id=" . $decoded_data['IDEESS'] . "&bsht=" . $_GET['bsht']; ?>" method="POST">
                    <input type="hidden" name="IDEESS" value="<?php echo $decoded_data['IDEESS']; ?>" />
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar estación</h5>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que quieres eliminar la estación y todas sus relaciones con empresas?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                        <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Precios -->
    <div class="modal fade" id="precioModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo htmlspecialchars($decoded_data['Rótulo']); ?> - Precios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered" style="width:100%">
                        <thead class="text-center">
                            <tr>
                                <th>Producto</th>
                                <th>Precio €/L</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($decoded_data as $key => $value): ?>
                                <?php if (strpos($key, 'Precio') !== false && $value != null): ?>
                                    <tr>
                                        <td><?php echo str_replace("Precio", "", $key); ?></td>
                                        <td><?php echo $value; ?>€</td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
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
    // Filtro de caracteres permitidos en el campo terminal
    document.querySelector('input[name="terminal"]').addEventListener('input', function() {
        const allowed = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        this.value = this.value.split('').filter(c => allowed.includes(c)).join('');
    });

    if (window.location.href.indexOf("success=1") > -1) {
        document.getElementById("sav").disabled = true;
    } else {
        document.getElementById("sav").disabled = false;
    }
</script>

<?php include 'inc/footer.php'; ?>