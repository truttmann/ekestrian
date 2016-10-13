<?php

namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Stdlib\SplPriorityQueue;

class LoggerApp extends Logger {
    protected static $instance; // Contiendra l'instance de notre classe.
    protected $filepath = "./log";
    protected $filename = "";

    /*public function __construct($nomBatch,ServiceManager $serviceLocator) {
        $serviceLocator->get('logger')->crit("test");
    }

      
    */
    public function __construct($options = array()){
        parent::__construct();
        $this->filename = date('Y-m-d')."-error.log";
        if(! file_exists($this->filepath)) {
            if(! mkdir($this->filepath, 0777, true)) {
                throw new Exception("Unable to create log directory");                
            }
        }
        $writer = new Stream($this->filepath.'/'.$this->filename);             
        $this->addWriter($writer); 
    }


    public static function getInstance()
    {
        if (!isset(self::$instance)) {// Si on n'a pas encore instancié notre classe.
            self::$instance = new self; // On s'instancie nous-mêmes. :)
        }

        return self::$instance;
    }

    public function logApp($txt, $filename = "", $filePath = "") {
        if($filename != '' && $filename != $this->filename) {
            unset($this->writers);
            $this->writers = new SplPriorityQueue();
            $writer = new Stream($this->filepath.'/'.$filename);             
            $this->addWriter($writer); 
        }
        $this->log(1, $txt);
    }
}