<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RepriseBatch\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use RepriseBatch\utils\InitialiseIndexSearch;
use RepriseBatch\Service\Batch;

class BatchController extends AbstractActionController
{
    private $logger;

    public function __construct(){
        
    }

    /**
    * Batch pour le remplissage de la table _index_search
    */
    public function remplissageIndexSearchAction()
    {   
        try{
            new Batch("IndexSearch", $this->getServiceLocator());
        }catch(\PDOException $e){
            $this->getServiceLocator()->get('logger')->log(1,   "PDO EXCEPTION : ".$e->getMessage().'- TRACE : '.$e->getTraceAsString()." - Line :".$e->getLine(), array("controller" => "ImportXmlController", "action" => "debutImportAction"));
            exit(1);
        }catch(\Exception $e){
            $this->getServiceLocator()->get('logger')->log(1,   $e->getMessage(), array("controller" => "BatchController", "action" => "remplissageIndexSearch"));
            exit(2);
        }
    }

    /**
    * batch pour l import xml des campings
    */
    public function debutImportAction()
    {
        try{
            $request = $this->getRequest();
            $forced = $request->getParam('forced');
            new Batch("ImportXml", $this->getServiceLocator(), $forced);
        }catch(\PDOException $e){
            $this->getServiceLocator()->get('logger')->logApp("PDO EXCEPTION : ".$e->getMessage().'- TRACE : '.$e->getTraceAsString()." - Line :".$e->getLine());
            exit(1);
        }catch(\Exception $e){
            $this->getServiceLocator()->get('logger')->logApp($e->getMessage());
            exit(2);
        }
    }

    /**
    * Batch pour les contracts
    */
    public function contractAction()
    {
        try{
            new Batch("Contract", $this->getServiceLocator());
        }catch(\PDOException $e){
            $this->getServiceLocator()->get('logger')->logApp("PDO EXCEPTION : ".$e->getMessage().'- TRACE : '.$e->getTraceAsString()." - Line :".$e->getLine());
            exit(1);
        }catch(\Exception $e){
            $this->getServiceLocator()->get('logger')->logApp($e->getMessage());
            exit(2);
        }
    }

    /**
    * Batch pour le csv logopartenaire
    */
    public function logoPartenaireAction()
    {
        try{
            new Batch("LogoPartenaire", $this->getServiceLocator());
        }catch(\PDOException $e){
            $this->getServiceLocator()->get('logger')->logApp("PDO EXCEPTION : ".$e->getMessage().'- TRACE : '.$e->getTraceAsString()." - Line :".$e->getLine());
            exit(1);
        }catch(\Exception $e){
            $this->getServiceLocator()->get('logger')->logApp($e->getMessage());
            exit(2);
        }
    }

    /**
    * Batch pour le csv logopartenaire
    */
    public function lienReservationAction()
    {
        try{
            new Batch("LienReservation", $this->getServiceLocator());
        }catch(\PDOException $e){
            $this->getServiceLocator()->get('logger')->logApp("PDO EXCEPTION : ".$e->getMessage().'- TRACE : '.$e->getTraceAsString()." - Line :".$e->getLine());
            exit(1);
        }catch(\Exception $e){
            $this->getServiceLocator()->get('logger')->logApp($e->getMessage());
            exit(2);
        }
    }

    /**
    * Batch pour le csv pays acceuil
    */
    public function paysAcceuilAction()
    {
        try{
            new Batch("SiteTrouristique", $this->getServiceLocator());
        }catch(\PDOException $e){
            $this->getServiceLocator()->get('logger')->logApp("PDO EXCEPTION : ".$e->getMessage().'- TRACE : '.$e->getTraceAsString()." - Line :".$e->getLine());
            exit(1);
        }catch(\Exception $e){
            $this->getServiceLocator()->get('logger')->logApp($e->getMessage());
            exit(2);
        }
    }
}
