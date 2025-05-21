<?php 
    include 'inc/header.php'; 
    include_once 'inc/funciones.php';

    $funciones = new Funciones();

    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            if ($action == "edit") {
                //recogemos los datos
                $id = $_POST['id'];
                $nombre = $_POST['companyName'];
                $ip = $_POST['Ip'];
                $puerto = $_POST['Puerto'];

                //comprobamos que el municipio exista en la base de datos, si no existe lo añadimos
                if($funciones->editEmpresa($id, $nombre, $ip, $puerto)){
                    header("Location: empresas.php");
                }else{
                    echo "Error al editar la empresa";
                } 
            } else if ($action == "delete") {
                //elimina la empresa
                if($funciones->deleteEmpresa($id)){
                    header("Location: empresas.php");   
                }else{
                    echo "Error al eliminar la empresa";
                }
            }
        }

        if ($action == "add") {
            //recogemos los datos
            $nombre = $_POST['companyName'];
            $ip = $_POST['Ip'];
            $puerto = $_POST['Puerto'];

            //añadimos la empresa
            if($funciones->addEmpresa($nombre, $ip, $puerto)){
                header("Location: empresas.php");
            }else{
                echo "Error al añadir la empresa";
            } 
        }
    }

    $empresas = $funciones->getEmpresas();
?>
<div class="row">
    <div class="col-lg-12 p-4 ps-0">
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-outline pe-4"><h2><i class="bi bi-arrow-left"></i></h2></a>
            <h2 class="ml-3"><i class="bi bi-briefcase pe-2"></i>Empresas</h2>
        </div>
        <hr />
    </div>
</div>

<div class="text-right mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddEst"><i class="bi bi-plus-circle"></i> Añadir Empresa</button>
</div>

<div class="table-responsive">
    <table class="table table-striped sortable">
        <thead class="table-dark text-center">
            <tr>
                <th scope="col">Empresa</th>
                <th scope="col">IP/DNS</th>
                <th scope="col">PUERTO</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if (count($empresas) == 0) {
                    echo "<tr><td colspan='6' class='text-center'>No se han encontrado resultados</td></tr>";
                }else{
                    foreach ($empresas as $empresa) {
                        echo "<tr>";
                        echo "<td>" . $empresa['EMPRESA'] . "</td>";
                        echo "<td>" . $empresa['IP'] . " <span id='connectionStatus-" . $empresa['CODIGO'] . "'></span></td>";
                        echo "<td>" . $empresa['PUERTO'] . "</td>";
                        echo "<td>
                            <a href='#' data-bs-toggle='modal' data-bs-target='#modalEdit' class='ps-3' data-bs-id='" . $empresa['CODIGO'] . "' data-bs-name='" . $empresa['EMPRESA'] . "' data-bs-ip='" . $empresa['IP'] . "' data-bs-puerto='" . $empresa['PUERTO'] . "'><i class='bi bi-pencil-square  text-primary' title='Editar'></i></a>
                            <a type='button' class='ps-3 text-success' href='download_excel.php?data=empr_est&id_empresa=" . $empresa['CODIGO'] . "' title='Genera un archivo excel con todas las estaciones de la empresa.'><i class='bi bi-file-earmark-excel'></i></a>
                            <a type='button' class='ps-3' title='Probar conexión' onclick='testconsk(\"" . $empresa['IP'] . "\", \"" . $empresa['PUERTO'] . "\", \"" . $empresa['CODIGO'] . "\")'><i class='bi bi-wifi text-info'></i></a>
                            <a href='#' data-bs-toggle='modal' data-bs-target='#modalEliminar' class='ps-3'><i class='bi bi-trash text-danger' title='Eliminar'></i></a>
                        </td>";
                        echo "</tr>";
                    }
                }
            ?>
    </table>
</div>

<!-- Modal editar-->
<div class="modal fade" id="modalEdit"   data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditLabel">Editar Empresa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="<?php echo $_SERVER['PHP_SELF']."?action=edit&id=" . $empresa['CODIGO'] ?>" method="POST">
            <input type="hidden" name="id" id="id" value="">
            <div class="mb-3">
                <label for="companyName" class="form-label">Nombre de la Empresa</label>
                <input type="text" class="form-control" id="companyName" name="companyName">
            </div>

            <div class="mb-3">
                <label for="Ip" class="form-label">IP/DNS</label>
                <input type="text" class="form-control" id="Ip" name="Ip">
            </div>

            <div class="mb-3">
                <label for="Puerto" class="form-label">Puerto</label>
                <input type="number" class="form-control" id="Puerto" name="Puerto">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Guardar Cambios</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- modal add -->
<div class="modal fade" id="modalAddEst"  data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalAddEstLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAddEstLabel">Agregar Empresa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="<?php echo $_SERVER['PHP_SELF']."?action=add" ?>" method="POST">
            <div class="mb-3">
                <label for="companyName" class="form-label">Nombre de la Empresa</label>
                <input type="text" class="form-control" id="companyName" name="companyName">
            </div>

            <div class="mb-3">
                <label for="Ip" class="form-label">IP/DNS</label>
                <input type="text" class="form-control" id="Ip" name="Ip">
            </div>

            <div class="mb-3">
                <label for="Puerto" class="form-label">Puerto</label>
                <input type="number" class="form-control" id="Puerto" name="Puerto">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal eliminar-->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
  <div class="modal-dialog ">
    <div class="modal-content">
        <form action="<?php echo $_SERVER['PHP_SELF']."?action=delete&id=" . $empresa['CODIGO'] ?>" method="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEliminarLabel">Eliminar empresa</h5>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que quieres eliminar la empresa?
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
        var editarModal = document.getElementById('modalEdit');
        editarModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            // Extract info from data-bs-* attributes
            var id = button.getAttribute('data-bs-id')
            var name = button.getAttribute('data-bs-name')
            var ip = button.getAttribute('data-bs-ip')
            var puerto = button.getAttribute('data-bs-puerto')
            // Update the modal's content.
            var modalTitle = editarModal.querySelector('.modal-title')
            var modalBodyInput = editarModal.querySelector('.modal-body input')

            modalTitle.textContent = 'Editar Empresa'
            modalBodyInput.value = name

            document.getElementById("id").value = id;
            document.getElementById("companyName").value = name;
            document.getElementById("Ip").value = ip;
            document.getElementById("Puerto").value = puerto;
        });

        var addModal = document.getElementById('modalAddEst');
        addModal.addEventListener('show.bs.modal', function (event) {
            document.getElementById("companyName").value = "";
            document.getElementById("Ip").value = "";
            document.getElementById("Puerto").value = "";
        });
    });

    function testconsk(ip, puerto, codigo) {
        $('#connectionStatus-' + codigo).html('<i class="bi bi-clock-fill text-warning" title="Comprobando conexión por favor espera"></i>');

        $.ajax({
            url: 'testconsk.php', // Path to your testconsk.php file
            type: 'POST',
            data: { ip: ip, puerto: puerto },
            success: function(response) {
                //quitar espacios en blanco
                response = response.trim();

                if (response === 'true') {
                    $('#connectionStatus-' + codigo).html('<i class="bi bi-check-circle-fill text-success"></i>');
                } else {
                    $('#connectionStatus-' + codigo).html('<i class="bi bi-x-circle-fill text-danger"></i>');
                }
            },
            error: function() {
                alert('Error en la solicitud AJAX');
            }
        });
    }
</script>
<?php include 'inc/footer.php'; ?>