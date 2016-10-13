<?php

namespace RepriseBatch\Service;

use Zend\ServiceManager\ServiceManager;

class Batch {
    protected $nombatch = "";
    protected $serviceLocator = null;
    protected $forced = null;

    public function __construct($nomBatch, ServiceManager $serviceLocator, $forced = false) {
        $this->nombatch = $nomBatch;
        $this->serviceLocator = $serviceLocator;
        $this->forced = $forced;
        set_time_limit(0);
        $this->init();
    }

    private function init() {
        $this->serviceLocator->get('logger')->logApp("Demande de batch : ".$this->nombatch, "batch_".date('Y-m-d').".log");

        // verification de la dispo du batch
        if(! $this->verifDispo()) {
            throw new \Exception("Missing batch lock file information in conf");
        }


        $this->serviceLocator->get('logger')->logApp("Debut du batch : ".$this->nombatch);

        //lancement des batch
        $this->lancerBatch();
    }   

    /**
    * function witch check the avability of the batch
    */
    private function verifDispo() {
        try {
            require  __DIR__."/../utils/BrcLock.class.php";

            $config = $this->serviceLocator->get('Config');
            if(! array_key_exists("application_config", $config) || ! array_key_exists("batch", $config['application_config']) || ! array_key_exists("ds", $config['application_config']['batch']) || ! array_key_exists("repository_for_lockfile", $config['application_config']['batch'])) {
                throw new \Exception("Missing batch lock file information in conf");
            }
            define('DS', $config['application_config']['batch']['ds']);
            define('TMP_DIR', $config['application_config']['batch']['repository_for_lockfile']);
            
            // test de non lancement en parrallele
            if (! \BrcLock::get($this->nombatch)) {
                $this->serviceLocator->get('logger')->logApp("batch (".$this->nombatch.") deja en cours");
                exit(1);
            }
            return true;   
        } catch(\Exception $e) {
            $this->serviceLocator->get('logger')->logApp("batch (".$this->nombatch.") verifDispo erreur: ".$e->getMessage());
            return false;
        }
    }

    /**
    * function witch get batch property and lanch it
    */
    protected function lancerBatch() {
        $this->serviceLocator->get('logger')->logApp("Chargement de la classe (batch : ".$this->nombatch.")");
        $cls = "RepriseBatch\utils\Batch".$this->nombatch;
        $batch = null;
        if($this->forced) {
            $batch = new $cls($this->serviceLocator, true);            
        } else {
            $batch = new $cls($this->serviceLocator);            
        }


        $batch->init();
        $this->serviceLocator->get('logger')->logApp("Fin du batch : ".$this->nombatch);
    }

}