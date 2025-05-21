<?php include 'inc/header.php'; ?>

<div class="row">
    <div class="col-lg-12 p-4 ps-0">
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-outline"><h2><i class="bi bi-arrow-left"></i></h2></a>
            <h2><img class="mityc_logo" src="https://geoportalgasolineras.es/geoportal-instalaciones/assets/imagenes/im-open-data.png" alt="" width="50" class="img-responsive"> AGREGAR ESTACIÓN</h2>
        </div>
        <hr />
    </div>
</div>

<div class="mb-3">
    <a type="button" class="btn btn-primary text-right " href="listado-estaciones.php"><i class="bi bi-list"></i> Estaciones existentes</a>
</div>

<div class="textAlignCenter">
    <div id="loading" style="margin: 0px; padding: 0px; position: fixed; right: 0px; top: 0px; width: 100%; height: 100%; background-color: rgb(102, 102, 102); z-index: 30001; opacity: 0.8;">
        <p style="position: absolute; color: White; top: 30%; left: 40%;">
            <img  src="media/loading.gif" />
        </p>
    </div>
    <div id="viewContent">
        <div class="home_select">
            <label for="id_label_single" class="fw-bold">
                Selecciona la provincia
            </label>
            <select class="form-select js-example-basic-single js-states form-control" id="provincia_select">
                <option value="Select">Seleccionar provincia</option>
            </select>
        </div>
        <div class="home_select">
            <button id="mirar_por_pronvincias" type="button" class="btn btn-dark"><i class="bi bi-search pe-2"></i>Consultar por provincia</button>
        </div>

        <div class="home_select">
            <label for="id_label_single" class="fw-bold">
                Selecciona la Municipio
            </label>
            <select class="form-select js-example-basic-single js-states form-control" id="localidad_select">
                <option value="Select">Seleccionar Municipio</option>
            </select>
        </div>

        <div class="input-group input-group-sm">
            <input type="text"  id="search" placeholder="Escribe aquí el nombre de una estación" class="form-control">
        </div>

        <div id="estaciones" class="table-responsive"></div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>