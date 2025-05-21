<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

echo '
<!DOCTYPE html>
<html lang="es">
<head>
    <title>EfiFT</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css" />
    <link href="node_modules/select2/dist/css/select2.min.css" rel="stylesheet" />
    <script src="node_modules/sorttable/sorttable.js"></script>
    <link rel="icon" type="image/x-icon" href="media/favicon.ico">
    <link rel="stylesheet" href="../css/style.css?v=1.1" />
    <link rel="stylesheet" href="css/jquery.multiselect.css?v=1.1" />
    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <link href="node_modules/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container-fluid">
        <nav class="nav bg-purple justify-content-center">
            <a class="nav-link text-white" href="index.php"><i class="bi bi-house-door"></i> Inicio</a>
            <a class="nav-link text-white" style="pointer-events: none;user-select: none;" href="#"><i class="bi bi-calendar-event"></i> <span id="reloj"></span></a>
            <a class="nav-link text-white" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
        </nav>
    
        <div class="container-fluid py-1">
';

if(isset($_GET['error'])){
    if($_GET['error'] == 0){
      echo "<div class='alert alert-danger alert-dismissible fade show mb-0' role='alert'>
      <i class='bi bi-exclamation-triangle-fill'></i><strong> ¡Error!</strong> Ha ocurrido un error inesperado.
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
    }else if($_GET['error'] == 1){
      echo "<div class='alert alert-danger alert-dismissible fade show mb-0' role='alert'>
        <i class='bi bi-exclamation-triangle-fill'></i><strong> ¡Error!</strong> No se ha podido realizar la acción solicitada.
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
    }
}

if(isset($_GET['success'])){
    if($_GET['success'] == 0){
      echo "<div class='alert alert-success alert-dismissible fade show mb-0' role='alert'>
      <i class='bi bi-check-circle-fill'></i><strong> ¡Éxito!</strong> Operación realizada correctamente!.
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
    }else if($_GET['success'] == 1){
      echo "<div class='alert alert-success alert-dismissible fade show mb-0' role='alert'>
        <i class='bi bi-check-circle-fill'></i><strong> ¡Éxito!</strong> Operación realizada correctamente!.
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
    }
}
?>