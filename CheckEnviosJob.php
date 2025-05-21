
<?php
include_once 'CheckEnvios.php';
class CheckEnviosJob{

    public function __construct()
    {
        $this->checkEnvios = new CheckEnvios();
    }

    public function run()
    {
        $this->checkEnvios->run();
    }
}

//RUN JOB
$job = new CheckEnviosJob();
?>