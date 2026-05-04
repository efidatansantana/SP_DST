
<?php
include_once '../WriteLog.php';
class Conex
{
    private $log;

    public function __construct()
    {
        $this->log = new WriteLog();
    }

    public static function conectar()
    {
        try {
            $db = new PDO("mysql:host=localhost;dbname=ENVIO_PRECIOS_MITYC", "root", "");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->exec("SET CHARACTER SET UTF8");
        } catch (Exception $e) {
            self::$log = fopen('log/log.txt', 'a');
            fwrite(self::$log, "Error de conexión BBDD:" . $e->getMessage() . PHP_EOL);
            fclose(self::$log);
            die("Error de conexión:" . $e->getMessage());
        }
        return $db;
    }

    public static function desconectar($db)
    {
        $db = null;
    }
}
?>
