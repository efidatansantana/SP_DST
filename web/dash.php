<?php
 include 'inc/header.php'; 
 include 'inc/funciones.php';

 $funciones = new Funciones();
 $count = $funciones->GetEnviosNoEnviados();
?>
<div class="row">
  <div class="col-lg-12 p-5 pb-3">
    <h1><i class="bi bi-speedometer"></i> EfiFuelTracking | Dashboard</h1>
    <hr />
  </div>

  <?php
    if($count > 0){
      echo '<div class="alert alert-danger" role="alert">
      <i class="bi bi-exclamation-triangle-fill"></i> <b>'.$count.'</b> precios que no han podido ser enviados.
      </div>';
    }
  ?>
</div>
<div class="row">
  <div class="col-xs-6 col-sm-6 col-md-3 col-lg-2 p-2">
    <a class="text-decoration-none" href="empresas.php">
      <div class="card p-3 shadow bg-purple text-center border-0">
        <div class="card-body">
          <i class="bi bi-building"></i>
          <hr />
          <p class="card-title lead">Empresas</p>
        </div>
      </div>
    </a>
  </div>

  <div class="col-xs-6 col-sm-6 col-md-3 col-lg-2 p-2">
    <a class="text-decoration-none" href="mityc.php">
      <div class="card p-3 shadow bg-purple text-center border-0">
        <div class="card-body">
          <i class="bi bi-fuel-pump"></i>
          <hr />
          <p class="card-title lead">Estaciones</p>
        </div>
      </div>
    </a>
  </div>
  

  <div class="col-xs-6 col-sm-6 col-md-3 col-lg-2 p-2">
    <a class="text-decoration-none" href="envios.php">
      <div class="card p-3 shadow bg-purple text-center border-0">
        <div class="card-body">
          <i class="bi bi-send-check-fill"></i>
          <hr />
          <p class="card-title lead">Envios</p>
        </div>
      </div>
    </a>
  </div>

  <div class="col-xs-6 col-sm-6 col-md-3 col-lg-2 p-2">
    <a class="text-decoration-none" type="button" data-bs-toggle='modal' data-bs-target='#modalupd' title="Forzar la actualizacón de precios.">
      <div class="card p-3 shadow bg-purple text-center border-0">
        <div class="card-body">
          <i class="bi bi-cloud-arrow-down-fill"></i>
          <hr />
          <p class="card-title lead">Actualizar precios <span style="font-weight: bold;">MITYC</span></p>
        </div>
      </div>
    </a>
  </div>

  <div class="col-xs-6 col-sm-6 col-md-3 col-lg-2 p-2">
    <a class="text-decoration-none" href="weblog.php">
      <div class="card p-3 shadow bg-purple text-center border-0">
        <div class="card-body">
          <i class="bi bi-file-earmark-text"></i>
          <hr />
          <p class="card-title lead">Logs</p>
        </div>
      </div>
    </a>
  </div>

</div>

<!-- Modal -->
<div class="modal fade" id="modalupd" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <form action="update_prices.php" method="GET" id="form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Forzar actualización de precios</h5>
                </div>
                <div class="modal-body" id="modal-b">
                    ¿Estás seguro de que deseas forzar la consulta de precios con MITYC?
                </div>
                <div class="modal-footer">
                    <p class="text-danger fw-bold" style="visibility: hidden;" id="txt">⏱️Esta acción puede tardar varios minutos.</p>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="no">No</button>
                    <button type="submit" class="btn btn-success" id="yes">Si</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
  //si presiona yes, se desabilitan los botones de yes se, desabilitan los botones de si y no y se añade el texto de "Actualizando...", sin jquery no se puede hacer
  document.getElementById("yes").addEventListener("click", function() {
    document.getElementById("yes").style.visibility = "hidden";
    document.getElementById("no").style.visibility = "hidden";
    document.getElementById("txt").style.visibility = "visible";
    document.getElementById("modal-b").innerHTML = "Forzando actualización de precios...";
  });
</script>
<?php include 'inc/footer.php'; ?>