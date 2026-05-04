
<?php
include_once dirname(__FILE__) . "/../WriteLog.php";

class Socket_Client
{
    private $skt;
    private $log;
    private $host;
    private $port;

    public function __construct($host, $port)
    {
        $this->log = new WriteLog();
        $this->host = $host;
        $this->port = $port;
    }

    public function connect()
    {
        if ($this->host === null || $this->port === null) {
            return false;
        }
        try {
            $this->skt = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_set_option($this->skt, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 20, "usec" => 0));
            if ($this->skt === false) {
                throw new Exception("socket_create() falló" . PHP_EOL);
            }
            echo "### Conectando con el socket... ###" . PHP_EOL;
            if (socket_connect($this->skt, $this->host, $this->port)) {
                echo "### Conexión establecida ###" . PHP_EOL;
                return true;
            } else {
                $this->skt = null;
                throw new Exception("socket_connect() falló: razón: " . socket_strerror(socket_last_error($this->skt)) . PHP_EOL);
                return false;
            }
        } catch (Exception $e) {
            echo "### Error: " . $e->getMessage() . " ###" . PHP_EOL;
            $this->log->write($e->getMessage());
            return false;
        }
    }

    public function testconn()
    {
        try {
            $this->skt = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_set_option($this->skt, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 20, "usec" => 0));
            if ($this->skt === false) {
                throw new Exception("socket_create() falló" . PHP_EOL);
            }

            $sk = socket_connect($this->skt, $this->host, $this->port);
            socket_set_option($this->skt, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 15, "usec" => 0));

            if ($sk) {
                $this->close();
                return true;
            } else {
                $this->skt = null;
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function isConnected()
    {
        return $this->skt !== null;
    }

    public function write($data)
    {
        socket_write($this->skt, $data, strlen($data));
    }

    public function read()
    {
        $data = socket_read($this->skt, 1024);
        return $data;
    }

    public function close()
    {
        if ($this->skt !== null) {
            socket_close($this->skt);
            //echo "### Conexión cerrada ###".PHP_EOL;
            $this->log->close();
            $this->skt = null;
        }
    }
}
?>