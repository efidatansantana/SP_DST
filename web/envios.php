<?php
include 'inc/header.php';
include_once 'inc/funciones.php';

$funciones = new Funciones();

/* PAGINACION */
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Current page
$itemsPerPage = 11;
$offset = ($page - 1) * $itemsPerPage;

//comprobamos que las variables esten definidas, si no lo estan les asignamos un valor por defecto en este caso null
$estacion = isset($_GET['estacion']) ? $_GET['estacion'] : "";
$estado = isset($_GET['estado']) ? $_GET['estado'] : "";
$desde =  isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : "";
$hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : "";
$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : "";

// si todas estan vacias, no se ha filtrado nada
if ($estacion == "" && $estado == "" && $desde == "" && $hasta == "" && $empresa == "") {
    $isFilter = false;
} else {
    $isFilter = true;

    //si esta filtrado no tendremos un limite de items por pagina
    $itemsPerPage = -1;
}

// Obtenemos los precios filtrados
$precios = $funciones->getPreciosFiltrados($estacion, $estado, $desde, $hasta, $empresa, $offset, $itemsPerPage);

// Obtenemos el total de precios sin filtros para la paginacion
$totalItems = $funciones->getTotalPreciosCount($estacion, $estado, $desde, $hasta, $empresa); // Get the total count of items based on filters

// Calculate total pages
$totalPages = ceil($totalItems / $itemsPerPage); // Calculate total pages

$count = $funciones->GetEnviosNoEnviados();

?>

<div class="row">
    <div class="col-lg-12 p-4 ps-0">
        <?php
        if ($count > 0) {
            echo '<div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <b>' . $count . '</b> precios que no han podido ser enviados en los últimos 7 dias.
            </div>';
        }
        ?>
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-outline pe-4">
                <h2><i class="bi bi-arrow-left"></i></h2>
            </a>
            <h2><i class="bi bi-clock-history pe-2"></i>Historial Envios</h2>
            <hr />
        </div>
        <hr />
    </div>
</div>

<div class="filtros mb-4">
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="GET">
        <div class="row">
            <div class="col-lg-2 p-1">
                <div>
                    <span class="fw-bold" id="basic-addon1">Empresa</span>
                    <select class="form-select custom-select" aria-label="Default select example" id="empresa" name="empresa">
                        <option value="" <?php echo isset($_GET['empresa']) && $_GET['empresa'] == "" ? "selected" : "" ?>>Selecciona una empresa</option>
                        <?php
                        $empresas = $funciones->getEmpresas();
                        foreach ($empresas as $empresa) {
                            echo "<option value='" . $empresa['CODIGO'] . "' " . (isset($_GET['empresa']) && $_GET['empresa'] == $empresa['CODIGO'] ? "selected" : "") . ">" . $empresa['EMPRESA'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-lg-4 p-1">
                <div>
                    <span class="fw-bold" id="basic-addon1">Estación</span>
                    <select class="form-select custom-select" aria-label="Default select example" id="estacion" name="estacion">
                        <option value="" <?php echo isset($_GET['estacion']) && $_GET['estacion'] == "" ? "selected" : "" ?>>Selecciona una estación</option>
                        <?php
                        $estaciones = $funciones->getEstaciones();

                        foreach ($estaciones as $estacion) {
                            echo "<option value='" . $estacion['ID_EESS_MITYC'] . "' " . (isset($_GET['estacion']) && $_GET['estacion'] == $estacion['ID_EESS_MITYC'] ? "selected" : "") . ">" . $estacion['NOMBRE_ESTABLECIMIENTO'] . " | " . $estacion['MUNICIPIO'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-lg-2 p-1">
                <div>
                    <span class="fw-bold" id="basic-addon1">Estado</span>
                    <select class="form-select" aria-label="Default select example" id="estado" name="estado">
                        <!-- Por defecto esta seleccionado el 0, pero cambia si se ha enviado el estado por GET -->
                        <option value="" <?php echo isset($_GET['estado']) && $_GET['estado'] == "" ? "selected" : "" ?>>Selecciona un estado</option>
                        <option value="1" <?php echo isset($_GET['estado']) && $_GET['estado'] == "1" ? "selected" : "" ?>>Enviado</option>
                        <option value="0" <?php echo isset($_GET['estado']) && $_GET['estado'] == "0" ? "selected" : "" ?>>No enviado</option>
                        <option value="2" <?php echo isset($_GET['estado']) && $_GET['estado'] == "2" ? "selected" : "" ?>>Pendiente envio</option>
                        <!--<option value="0" <?php echo isset($_GET['estado']) && $_GET['estado'] == "0" || !isset($_GET['estado']) ? "selected" : "" ?>>No enviado</option>-->
                    </select>
                </div>
            </div>

            <div class="col-lg-1 p-1">
                <div>
                    <span class="fw-bold" id="basic-addon1">Desde</span>
                    <input type="date" class="form-control" placeholder="Desde" aria-label="Desde" aria-describedby="basic-addon1" id="fecha_desde" name="fecha_desde" value="<?php echo isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : "" ?>" max="<?php echo date("Y-m-d") ?>" min="<?php echo date("Y-m-d", strtotime("-5 month")) ?>">
                </div>
            </div>

            <div class="col-lg-1 p-1">
                <div>
                    <span class="fw-bold" id="basic-addon1">Hasta</span>
                    <input type="date" class="form-control" placeholder="Hasta" aria-label="Hasta" aria-describedby="basic-addon1" id="fecha_hasta" name="fecha_hasta" value="<?php echo isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : "" ?>" max="<?php echo strtotime(date("Y-m-d")) ?>" min="<?php echo strtotime(date("Y-m-d", strtotime("-5 month"))) ?>">
                </div>
            </div>


            <div class="col-lg-2 p-1 mt-4">
                <button type="button" class="btn btn-secondary" id="clear" name="clear">Limpiar filtros</button>
                <button type="submit" class="btn btn-success">Filtrar</button>
            </div>
        </div>
    </form>
</div>
<div class="table-responsive">
    <?php if ($isFilter) : ?>
        <div class="d-flex justify-content-between">
            <p>Se han encontado <b><?php echo $totalItems ?></b> registros</p>
        </div>
    <?php endif; ?>
    <table class="table table-striped sortable">
        <thead class="table-dark text-center">
            <tr>
                <th scope="col">Empresa</th>
                <th scope="col">Cód. Estación</th>
                <th scope="col">Estación</th>
                <th scope="col">Municipio</th>
                <th scope="col">Producto</th>
                <th scope="col">Precio</th>
                <th scope="col">Estado</th>
                <th scope="col">Fecha inserción</th>
                <th scope="col">Fecha envío</th>
                <th scope="col" title="Intentos de envío">Int. envío</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody id="table">
            <?php
            if (count($precios) == 0) {
                echo "<tr><td colspan='10' class='text-center'>No se han encontrado resultados</td></tr>";
            } else {
                foreach ($precios as $precio) {
                    if ($precio['ESTADO'] == 0) {
                        $status = "<i class='bi bi-x-circle-fill text-danger' title='No enviado'></i> No enviado";
                    } else if ($precio['ESTADO'] == 1) {
                        $status = "<i class='bi bi-check-circle-fill text-success' title='Enviado'></i> Enviado";
                    } else if ($precio['ESTADO'] == 2) {
                        $status = "<i class='bi bi-clock-fill text-warning' title='Pendiente envio'></i> Pendiente";
                    }

                    if ($precio['ANULADO'] == 1) {
                        $status = "<i class='bi bi-x-square-fill text-danger' title='Envío anulado'></i> No enviado (Anulado)";
                    }

                    echo "<tr";
                    if ($precio['ANULADO'] == 1) {
                        echo " class='s'";
                    }
                    echo ">";
                    echo "<td>" . $precio['NOMBRE_EMPRESA'] . "</td>";
                    echo "<td>" . $precio['CODIGO_EESS'] . "</td>";
                    echo "<td>" . $precio['ESTABLECIMIENTO'] . "</td>";
                    echo "<td>" . $precio['MUNICIPIO'] . "</td>";
                    echo "<td>" . $precio['NOMBRE'] . "</td>";

                    if ($precio['PRECIO'] == null || $precio['PRECIO'] == "") {
                        echo "<td>0,000€</td>";
                    } else {
                        echo "<td>" . $precio['PRECIO'] . "€ </td>";
                    }

                    echo "<td>" . $status . "</td>";
                    echo "<td>" . $precio['FECHA_INS'] ?? " - " . "</td>";
                    echo "<td>" . $precio['FECHA_ENVIO'] ?? " - " . "</td>";
                    echo "<td>" . ($precio['INTENTOS_ENVIO'] == 0 ? "-" : "<span class='text-danger fw-bold'>" . $precio['INTENTOS_ENVIO'] . "</span>") . "</td>";
                    echo "<td>";
                    if ($precio['FECHA_ENVIO'] == 'N/A') {
                        echo "<a href='eliminar_envio.php?codigo_eess=" . $precio['CODIGO_EESS'] . "&codigo_empresa=" . $precio['CODIGO_EMPRESA'] . "&codigo_producto_ca=" . $precio['CODIGO_PRODUCTO_CA'] . "&precio=" . $precio['PRECIO'] . "&fecha_ins=" . $precio['FECHA_INS'] . "' class='btn btn-danger btn-sm' title='Eliminar envío'><i class='bi bi-trash'></i></a>";
                    } else {
                        echo "<a href='#' class='btn btn-danger btn-sm disabled' title='Eliminar envío'><i class='bi bi-trash-fill'></i></a>";
                    }
                    echo "</tr>";
                }
            }
            ?>
    </table>
    <!--<div class="d-flex justify-content-center mt-4">
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <?php
                if ($totalPages > 1) {
                    for ($i = 1; $i <= $totalPages; $i++) {
                        $activeClass = ($i === $page) ? 'active' : '';
                        echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . $_SERVER['PHP_SELF'] . '?page=' . $i . '">' . $i . '</a></li>';
                    }
                }
                ?>
            </ul>
        </nav>
    </div>-->

    <!-- solo se ve si la variable noFilter es true -->
    <?php if (!$isFilter) : ?>
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <?php
                    if ($totalPages > 1) {
                        $startPage = max($page - 2, 1);
                        $endPage = min($page + 2, $totalPages);

                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . $_SERVER['PHP_SELF'] . '?page=1">1</a></li>';
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }

                        for ($i = $startPage; $i <= $endPage; $i++) {
                            $activeClass = ($i === $page) ? 'active' : '';
                            echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . $_SERVER['PHP_SELF'] . '?page=' . $i . '">' . $i . '</a></li>';
                        }

                        if ($endPage < $totalPages) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            echo '<li class="page-item"><a class="page-link" href="' . $_SERVER['PHP_SELF'] . '?page=' . $totalPages . '">' . $totalPages . '</a></li>';
                        }
                    }
                    ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
<script>
    $(document).ready(function() {
        $("#clear").click(function() {
            $("#empresa").val("");
            $("#estacion").val("");
            $("#estado").val("");
            $("#fecha_desde").val("");
            $("#fecha_hasta").val("");

            // Submit form
            $("form").submit();
        });

    });
</script>
<?php include 'inc/footer.php'; ?>