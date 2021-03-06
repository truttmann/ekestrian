<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Frontoffice\Controller;


use Zend\View\Model\ViewModel;
use Frontoffice\Controller\InitController;

class LotsController extends InitController
{
    public function __construct($service)
    {
        $this->_service_locator = $service;
        parent::__construct();
    }

    public function indexAction(){
        parent::initListJs();
        
        $this->mainView->setVariable("lang_id", $this->lang_id);
        $this->mainView->setTemplate('frontoffice/lots');
        
        /* recuperation de l'id enchere */
        $id = $this->params()->fromRoute('enchere_id');

        /* verification de l'existance de l'enchère, sinon redirection */
        $t = null;
        try {
            $t = $this->_service_locator->get('enchereTable')->fetchOne($id);
        } catch(\Exception $e) {}
        
        if(! is_object($t) || $t->status != 1) {
	    	$this->addError('Enchère non valide');
            return $this->redirect()->toRoute('home');
        }
        $this->mainView->setVariable("enchere", $t);
        
        /* verification que l'enchere est démarrée */
        $d = new \DateTime();
        $d2 = \DateTime::createFromFormat("Y-m-d H:i:s", $t->start_date);
        $display = false;
        if($d2 != false && $d > $d2){
            $display = true;
        }
        $this->mainView->setVariable("can_enchere", $display);
        
        /* récupération des lots */
        $e = array();
        $l = $this->_service_locator->get('lotTable')->fetchAll(array("enchere_id" => $id, "status" => 1),  'number');
        foreach ($l as $i) {
            /* recuperation du cheval en question */
            $che = $this->_service_locator->get('chevalTable')->fetchOne($i->cheval_id);
            
            /* récupération du père */
            $p = null;
            try{
                if(is_object($che) && $che->father_id != null){
                    $p = $this->_service_locator->get('chevalTable')->fetchOne($che->father_id);
                }
            } catch (Exception $ex) {}
            
            /* récupération de la mère */
            $m = null;
            try{
                if(is_object($che) && $che->mother_id != null){
                    $m = $this->_service_locator->get('chevalTable')->fetchOne($che->mother_id);
                }
            } catch (Exception $ex) {}
            
            /* récupération du père de la mère */
            $pm = null;
            try{
                if(is_object($m) && $m->father_id != null){
                    $pm = $this->_service_locator->get('chevalTable')->fetchOne($m->father_id);
                }
            } catch (Exception $ex) {}
            $i->pere = $p;
            $i->mere = $m;
            $i->pere_mere = $pm;
            
            $e[] = $i;
        }
        $this->mainView->setVariable("lots", $e); 
        
        return $this->mainView;
    }
}
