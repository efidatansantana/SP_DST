
<?php
    class Conex {
        public static function conectar(){
            try {
                $db = new PDO("mysql:host=localhost;dbname=ENVIO_PRECIOS_MITYC", "efidata", "Ef1data00");
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->exec("SET CHARACTER SET UTF8");
            }
            catch(Exception $e){
                die("Error de conexión:" . $e->getMessage());
                $this->log = fopen('log/log.txt', 'a');
                fwrite($this->log, "Error de conexión BBDD:" . $e->getMessage().PHP_EOL);
                fclose($this->log);
            }
            return $db;
        }

        public static function desconectar($db){
            $db = null;
        }
    }
?>
