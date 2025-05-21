
<?php

class WriteLog
{
    private $log;
    private $data_name;
    private $logDirectory;

    public function __construct()
    {
        $this->logDirectory = "/var/www/Envio_Precios/log/";

        //log name is the current date
        $this->data_name = "log-".date('Ymd').".log";

        //delete the log file of the previous day if it is empty
        $last_log = "log-".date('Ymd', strtotime("-29 days")).".log";
        if(file_exists($this->logDirectory.$last_log)){
            unlink($this->logDirectory.$last_log);
        }

        if (!is_dir($this->logDirectory)) {
            mkdir($this->logDirectory, 0777, true); // Create directory if it doesn't exist
        }
        
        if (!file_exists($this->logDirectory . $this->data_name)) {
            $this->log = fopen($this->logDirectory . $this->data_name, 'w');
            chmod($this->logDirectory . $this->data_name, 0777);
        }else{
            $this->log = fopen($this->logDirectory . $this->data_name, 'a');
        }
    }

    public function write($data){

        $file = fopen($this->logDirectory.$this->data_name, 'a');
        if($file === false){
            echo "Error opening file".PHP_EOL;
        }

        if(fwrite($file, "=== ".date('d/m/Y H:i:s').': '.$data." ===".PHP_EOL)){
            echo "=== ".date('d/m/Y H:i:s').': '.$data." ===".PHP_EOL;
        }

        /*$this->log = fopen($this->logDirectory.$this->data_name, 'a');
        fwrite($this->log, "=== ".date('d/m/Y H:i:s').': '.$data." ===".PHP_EOL);*/
    }


    public function close()
    {
        if($this->log != null){
            fclose($this->log);
        }
    }
}
?>