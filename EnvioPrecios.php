
<?php
include_once 'socket/socket.php';
include_once 'WriteLog.php';

class EnvioPrecios
{
    private $socket;
    private $log;

    public function __construct($host, $port)
    {
        //conexion con el socket
        $this->socket = new Socket($host, $port);
        $this->log = new WriteLog();
    }

    public function __destruct()
    {
        $this->socket->close();
        $this->log->close();
        $this->db = null;
    }

    /**
     * Ejecuta el proceso
     */
    public function run($datos)
    {
        try{
            $this->socket->connect();
            if($this->socket !== null){
                echo "=== Iniciando envio de precios... ===".PHP_EOL;
                //construcción de la trama
                $stx = chr(2);
                $etx = chr(3);
                $cr = chr(10);
                $codigo_terminal = trim($datos['codigoTerminal']); //SE OBTIENE DE LA BASE DE DATOS
                $id_comando = "23";
                $codigo_producto = Str_pad(trim($datos['codigoProducto']), 3, '0', STR_PAD_LEFT); //SE OBTIENE DE LA BASE DE DATOS
                $precio = str_pad(trim($datos['precio']) * 1000, 8, '0', STR_PAD_LEFT); //SE OBTIENE DE LA BASE DE DATOS
                $trama = $stx.$codigo_terminal.$id_comando.$codigo_producto.$precio;
                $checksum = $this->CheckSum($trama);

                //Construcción de la trama completa
                $trama = $trama.$checksum.$etx.$cr;

                $this->socket->write($trama);
                $data = $this->socket->read();
                
                $this->log->write("<< Trama enviada: ".$trama);
                $this->log->write(">> Respuesta servidor: ".$data);
                
                if(strpos($data, 'Error') !== false){
                    throw new Exception('El servidor ha respondido con un error, Err: '.$data);
                }else{
                    $comando_recibido = substr($data, 1, 2);
                    $estado = substr($data, 3, 2);
                    if($estado == '01' && $comando_recibido == $id_comando){
                        $this->log->write("El precio se ha enviado correctamente");
                        return true;
                    }else{
                        $this->log->write("### Error al enviar el precio la respuesta ha sido negativa o el comando es incorrecto, Datos: ".json_encode($datos)." COMANDO: ".$comando_recibido." RESPUESTA: ".$estado." ###");
                        return false;
                    }
                }
            }else{
                throw new Exception('No se ha podido conectar con el socket, empresa: '.$datos['empresa']);
            }
        }catch(Exception $e){
            $this->log->write($e->getMessage());
            return false;
        }
    }

    /**
     * Calcula el checksum
     */
    private function CheckSum($cadena){
        $tmp = 0;
        for($i=0;$i<strlen($cadena);$i++){
            $tmp = $tmp ^ ord($cadena[$i]);
        }

        //uppercase
        return strtoupper(str_pad(dechex($tmp), 2, '0', STR_PAD_LEFT));
    }
}
?>