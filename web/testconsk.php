<?php
include_once dirname(__FILE__) . "/../socket/socket.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge los datos enviados por AJAX
    $ip = $_POST['ip'];
    $puerto = $_POST['puerto'];

    // Realiza aquí tu lógica para probar la conexión
    // Por ejemplo, podrías intentar hacer un ping o una conexión al puerto indicado
    // y luego devolver 'true' o 'false' en función del resultado.
    $socket = new Socket($ip, $puerto);
    $conexionExitosa = $socket->testconn();
    if ($conexionExitosa) {
        echo 'true';
    } else {
        echo 'false';
    }
}
?>
