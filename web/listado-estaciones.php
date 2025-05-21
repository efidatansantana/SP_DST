<?php
    include 'inc/header.php'; 
    include_once 'inc/funciones.php'; 

    $funciones = new Funciones();

    /* PAGINACION */
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Current page
    $itemsPerPage = 13;
    $offset = ($page - 1) * $itemsPerPage;

    //obtencion de filtros
    $empresa = isset($_GET['empresa']) ? $_GET['empresa'] : null;
    $estacion = isset($_GET['estacion']) ? $_GET['estacion'] : null;
    $municipio = isset($_GET['municipio']) ? $_GET['municipio'] : null;
    $estado = isset($_GET['estado']) ? $_GET['estado'] : null;

    // Obtenemos los precios filtrados
    $estaciones = $funciones->getEstacionesFiltradas($offset, $itemsPerPage, $estacion, $municipio, $estado, $empresa);

    // Obtenemos el total de precios sin filtros para la paginacion
    if(count($estaciones) > 1){
        $totalItems = $funciones->getTotalEstacionesCount();
        $totalPages = ceil($totalItems / $itemsPerPage); // Calculate total pages
    }else{
        $totalPages = 1;
        $totalItems = 1;
    }



    if(isset($_GET['action'])) {
        if($_GET['action'] == 'delete') {
            if(isset($_POST['id_eess'])){
                // Guardamos los datos del formulario
                $id_eess = $_POST['id_eess'];
                if($id_eess  == '') {
                    Header("Location: listado-estaciones.php?error=".$_GET['id_eess']);
                }else{
                    if($funciones->deleteEstablecimiento($id_eess )) {
                        Header("Location: listado-estaciones.php?success=1");
                    } else {
                        Header("Location: listado-estaciones.php?error=1");
                    }
                }
            }else{
                Header("Location: listado-estaciones.php?error=1");
            }
        }
    }
?>

<div class="row">
    <div class="col-lg-12 p-4 ps-0">
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-outline"><h2><i class="bi bi-arrow-left"></i></h2></a>
            <h2><i class="bi bi-fuel-pump-fill pe-2"></i></i>Listado de estaciones existentes</h2>
        </div>
        <hr />
    </div>
</div>

<div class="text-right mb-3">
    <a type="button" class="btn btn-primary" href="mityc.php"><i class="bi bi-plus-circle"></i> Añadir Estación</a>
    <a type="button" class="btn btn-success text-right " href="download_excel.php?data=est_pr" title="Genera un archivo excel con todas las estaciones y los productos relacionados."><i class="bi bi-file-earmark-excel-fill"></i> Excel Estación/Producto (todas)</a>
    <a type="button" class="btn btn-warning text-right " href="download_excel.php?data=listado_estaciones&estacion=<?php echo $estacion; ?>&municipio=<?php echo $municipio; ?>&estado=<?php echo $estado; ?>&empresa=<?php echo $empresa; ?>" title="Genera un archivo excel sobre los datos que se están visualizando en la tabla"><i class="bi bi-file-earmark-excel-fill"></i> Listado Estaciones</a>
</div>

<!-- filtro por estacion, municipio, terminal, empresa, estado -->
<div class="filtros">
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="GET" id="form-filtros">
        <div class="row mb-3">
                <div class="col-lg-2">
                    <div class="fw-bold mb-3">
                        <span class="fw-bold" id="basic-addon1">Empresa</span>
                        <select class="form-select custom-select" name="empresa" id="empresa">
                            <option value="" <?php echo isset($_GET['empresa']) && $_GET['empresa'] == "" ? "selected" : "" ?>>Todas</option>
                            <?php
                                $empresas = $funciones->getEmpresas();
                                foreach ($empresas as $empresa) {
                                    echo "<option value='" . $empresa['CODIGO'] . "' " . (isset($_GET['empresa']) && $_GET['empresa'] == $empresa['CODIGO'] ? "selected" : "") . ">" . $empresa['EMPRESA'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="">
                        <span class="fw-bold">Estación</span>
                        <select class="form-select custom-select" name="estacion" id="estacion">
                            <option value="" <?php echo isset($_GET['estacion']) && $_GET['estacion'] == "" ? "selected" : "" ?>>Todas</option>
                            <?php
                                $estaciones_list = $funciones->getEstaciones();
                                
                                foreach ($estaciones_list as $estacion) {
                                    echo "<option value='" . $estacion['ID_EESS_MITYC'] . "' " . (isset($_GET['estacion']) && $_GET['estacion'] == $estacion['ID_EESS_MITYC'] ? "selected" : "") . ">" . $estacion['NOMBRE_ESTABLECIMIENTO']." | ".$estacion['MUNICIPIO'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="fw-bold mb-3">
                        <span class="fw-bold" id="basic-addon1">Municipio</span>
                        <select class="form-select custom-select" name="municipio" id="municipio">
                            <option value="" <?php echo isset($_GET['municipio']) && $_GET['municipio'] == "" ? "selected" : "" ?>>Todos</option>
                            <?php
                                $municipios = $funciones->getMunicipios();
                                foreach ($municipios as $municipio) {
                                    echo "<option value='" . $municipio['CODIGO'] . "' " . (isset($_GET['municipio']) && $_GET['municipio'] == $municipio['CODIGO'] ? "selected" : "") . ">" . $municipio['NOMBRE'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="fw-bold mb-3">
                        <span class="fw-bold" id="basic-addon1">Estado</span>
                        <select class="form-select" name="estado" id="estado">
                            <option value="" <?php echo isset($_GET['estado']) && $_GET['estado'] == "" ? "selected" : "" ?>>Todos</option>
                            <option value="1" <?php echo isset($_GET['estado']) && $_GET['estado'] == "1" ? "selected" : "" ?>>Activo</option>
                            <option value="0" <?php echo isset($_GET['estado']) && $_GET['estado'] == "0" ? "selected" : "" ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="fw-bold mt-4">
                        <div class="form-group">
                            <button type="button" class="btn btn-secondary pe-3" id="clear" name="clear">Limpiar filtros</button>
                            <button type="submit" class="btn btn-success"><i class="bi bi-search"></i> Buscar</button>  
                        </div>
                    </div>
                </div>
        </div>
    </form>
</div>


<div class="table-responsive" style="--bs-gutter-x: 1.1rem; !important;">
    <table class="table table-strMunicipioed sortable">
        <thead class="table-dark text-center">
            <tr>
                <th scope="col">Cód. Estación</th>
                <th scope="col">Estación</th>
                <th scope="col">Municipio</th>
                <th scope="col">Cód. Terminal</th>
                <th scope="col">Empresa</th>
                <th scope="col">Estado</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($estaciones as $estacion) {
                    $establecimientos_empresa = $funciones->getEstablecimientoEmpresa($estacion['ID_EESS_MITYC']);

                    echo "<tr>";
                    echo "<td>" . $estacion['ID_EESS_MITYC'] . "</td>";
                    echo "<td>" . $estacion['NOMBRE_ESTABLECIMIENTO'] . "</td>";
                    echo "<td>" . $estacion['MUNICIPIO'] . "</td>";
                    echo "<td>" . $estacion['CODIGO_TERMINAL'] . "</td>";
                    if (count($establecimientos_empresa) == 1){
                        echo "<td>" . $establecimientos_empresa[0]['NOMBRE_EMPRESA'] . "</td>";
                    }else{
                        echo "<td>";
                        foreach ($establecimientos_empresa as $establecimiento_empresa) {
                            echo "<badge class='badge bg-secondary'>" . $establecimiento_empresa['NOMBRE_EMPRESA'] . "</badge> ";
                        }
                        echo "</td>";

                    }
                    echo "
                        <td class='text-center'>
                            <badge class='badge bg-".($estacion['ESTADO'] == '1' ? 'success' : 'danger')."'>" . ($estacion['ESTADO'] == '1' ? 'Activo' : 'Inactivo') . "</badge>
                        </td>
                        <td class='text-center'>
                            <a href='#' class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#modalEliminar' data-bs-ide='".$estacion['ID_EESS_MITYC']."'>
                                <i class='bi bi-trash'></i>
                            </a>

                            <a href='editar-estacion.php?id_eess=".$estacion['ID_EESS_MITYC']."' class='btn btn-primary btn-sm'>
                                <i class='bi bi-pencil'></i>
                            </a>
                        ";
                    echo "
                        <a href='enviar_precios_estacion.php?id_eess=".$estacion['ID_EESS_MITYC']."' class='btn btn-success btn-sm' title='Reenvio de precios' ";
                        if ($estacion['ESTADO'] == '0') {
                            echo "style='pointer-events: none; opacity: 0.5; cursor: default;'";
                        }
                        echo ">
                            <i class='bi bi-send'></i>
                        </a>
                    </td>";
               
                    echo "</tr>";
                }

                //si no hay resultados
                if (count($estaciones) == 0) {
                    echo "<tr><td colspan='7' class='text-center'>No se han encontrado resultados</td></tr>";
                }
            ?>
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        <p class="text-muted">Mostrando <b><?php echo $offset + 1; ?></b> a <b><?php echo $offset + count($estaciones); ?></b> de <b><?php echo $totalItems; ?></b> registros</p>
    </div>

    <div class="d-flex justify-content-center mt-2">
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <?php
                    if ($totalPages > 1) {
                        $queryString = $_SERVER['QUERY_STRING'];

                        for ($i = 1; $i <= $totalPages; $i++) {
                            $activeClass = ($i === $page) ? 'active' : '';
                            $pageLink = $_SERVER['PHP_SELF'] . '?page=' . $i;

                            // Append existing query parameters to the pagination link, excluding 'page'
                            $filteredQueryParams = array_filter(explode('&', $queryString), function ($param) {
                                return !startsWith($param, 'page=');
                            });

                            // If there are other parameters, append them to the pagination link
                            if (!empty($filteredQueryParams)) {
                                $pageLink .= '&' . implode('&', $filteredQueryParams);
                            }

                            echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . $pageLink . '">' . $i . '</a></li>';
                        }
                    }

                    function startsWith($haystack, $needle) {
                        $length = strlen($needle);
                        return (substr($haystack, 0, $length) === $needle);
                    }
                ?>
            </ul>
        </nav>
    </div>
</div>

<!-- Modal eliminar-->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
  <div class="modal-dialog ">
    <div class="modal-content">
        <form action="<?php echo $_SERVER['PHP_SELF']."?action=delete"; ?>" method="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEliminarLabel">Eliminar Establecimiento</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_eess" id="id_eess" value="">

                ¿Estás seguro de que quieres eliminar el establecimiento?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="submit" class="btn btn-danger">Si</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#modalEliminar').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var id_eess = button.data('bs-ide') // Extract info from data-bs-* attributes

            var modal = $(this)
            modal.find('.modal-body #id_eess').val(id_eess)
        });


        $("#clear").click(function() {
            // Clear all inputs inside the form
            $("#empresa").val("");
            $("#estacion").val("");
            $("#municipio").val("");
            $("#estado").val("");

            // Submit form
            $("#form-filtros").submit();
        });

    });
</script>

<?php include 'inc/footer.php'; ?>


