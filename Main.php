
<?php
require_once 'MitycFetch.php';

class Main
{
    public function __construct()
    {
        /*
        * Ejecuta el proceso de consulta y actualizacion de precios del MITYC
        * una vez finalizado, se ejecuta el proceso de envio de precios, que es ejecutado desde el destructor de la clase MitycFetch
        */
        $mityc = new MitycFetch();
        $mityc->run();
    }
}

$main = new Main();
?>