<?php

namespace RepriseBatch\Service;

use Zend\ServiceManager\ServiceManager;

class BatchCSV {
    protected $filecsv;
    protected $config;
    protected $serviceloc;
    protected $carac_separateur;
    protected $line_depart;
    protected $erreur_url;

     /**
    * function qui va instancier le traitement du fichiers csv
    * elle va verifier que le chemin fournit est un fichier, si c"est un repertoire, elle va tourner en boucle sur celui-ci pour prendre chacuns des fichiers par ordre de DATE de modification ASC, 
    * et elle archive les fichiers
    */
    public function init() {
        if(is_dir($this->filecsv)) {
            $tab = array();
            $listFile = scandir($this->filecsv);
            foreach ($listFile as $key => $value) {
                if(is_file($this->filecsv.'/'.$value) && substr($value, -4) == ".csv") {
                    $tab[date("Y/m/d - H:i:s",filemtime($this->filecsv.'/'.$value))] = $value;
                }
            }
            ksort($tab);

            foreach ($tab as $key => $value) {
                $this->handlingFile($this->filecsv.'/'.$value);    
            }
        } else {
            $this->handlingFile($this->filecsv);
        }
        
    }

    /**
    * function qui va peremttre le traitement d'un fichier csv
    * avec archivage de celui ci
    * @param string $filename nom du fichier
    * @return void
    */
    private function handlingFile($filename) {
        // deplacement vers le fichier d'archive
        $file = $this->archive_url.basename($filename);
        rename($filename, $file);

        $this->serviceloc->get('logger')->logApp("batch, debut traitement du fichier '$file'");

        // traitement du fichier
        $this->traitementBatch($file);

        // log pour indiquer la fin du triatment du fichier
        $this->serviceloc->get('logger')->logApp("batch, Fin traitement du fichier  '$file'");
    }   
}
