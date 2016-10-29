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

class LotController extends InitController
{
    public function __construct($service)
    {
        $this->_service_locator = $service;
        parent::__construct();
    }

    public function indexAction(){
        parent::initListJs();
        
        $this->mainView->setTemplate('frontoffice/lot');
        $this->mainView->setVariable('enchere_id', $this->params()->fromRoute('enchere_id'));
        $this->mainView->setVariable('lot_id', $this->params()->fromRoute('lot_id'));
        
        /* recuperation de l'id enchere */
        $id_e = $this->params()->fromRoute('enchere_id');

        /* verification de l'existance de l'enchère, sinon redirection */
        $en = null;
        try {
            $en = $this->_service_locator->get('enchereTable')->fetchOne($id_e);
        } catch(\Exception $e) {}
        
        if(! is_object($en) || $en->status !=1) {
	    	$this->addError('Enchère non valide');
            return $this->redirect()->toRoute('home');
        }
        $this->mainView->setVariable("enchere", $en);
        
        /* recuperation de l'id lot */
        $id = $this->params()->fromRoute('lot_id');

        /* verification de l'existance du lot, sinon redirection */
        $t = null;
        try {
            $t = $this->_service_locator->get('lotTable')->fetchOne($id);
        } catch(\Exception $e) {}
        
        if(! is_object($t) || $t->status !=1) {
	    	$this->addError('Enchère non valide');
            return $this->redirect()->toRoute('home');
        }

        /* recuperation des images du lot*/
        $lim = $this->_service_locator->get("imageTable")->fetchAll(array("lot_id" => $id));
        $tab = array();
        foreach ($lim as $im) {
            $tab[] = $im->filename;
        }
        $t->list_image = $tab;
        
        /* recuperation de l'enchère maximal */
        $res = $this->_service_locator->get('clientAuctionTable')->getLastEnchere($t);
        $t->actual_cost = ((!empty($res))?$res->value:$t->min_price);
        
        $this->mainView->setVariable("lot", $t);
        
        /* recuperation du cheval en question */
        $che = $this->_service_locator->get('chevalTable')->fetchOne($t->cheval_id);
        if($che->birthday != ""){
            $date=  \DateTime::createFromFormat("Y-m-d",$che->birthday);
            if($date != false) {
                $che->birthday = $date->format("d/m/Y");
            } else {
                $che->birthday = "";
            }
        }
        
        
        
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
            if(is_object($che) && $che->father_id != null){
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
        /* récupération de la mère de la mère */
        $mm = null;
        try{
            if(is_object($m) && $m->mother_id != null){
                $mm = $this->_service_locator->get('chevalTable')->fetchOne($m->mother_id);
            }
        } catch (Exception $ex) {}
        /* récupération du père du père */
        $pp = null;
        try{
            if(is_object($p) && $p->father_id != null){
                $pp = $this->_service_locator->get('chevalTable')->fetchOne($p->father_id);
            }
        } catch (Exception $ex) {}
        /* récupération de la mère du père */
        $mp = null;
        try{
            if(is_object($p) && $p->mother_id != null){
                $mp = $this->_service_locator->get('chevalTable')->fetchOne($p->mother_id);
            }
        } catch (Exception $ex) {}
        
        
        /* récupération du père de la mère */
        $pmm = null;
        try{
            if(is_object($m) && is_object($mm) && $mm->father_id != null){
                $pmm = $this->_service_locator->get('chevalTable')->fetchOne($mm->father_id);
            }
        } catch (Exception $ex) {}
        /* récupération de la mère de la mère */
        $mmm = null;
        try{
            if(is_object($m) && is_object($mm) && $mm->mother_id != null){
                $mmm = $this->_service_locator->get('chevalTable')->fetchOne($mm->mother_id);
            }
        } catch (Exception $ex) {}
        /* récupération du père du père */
        $ppm = null;
        try{
            if(is_object($m) && is_object($pm) && $pm->father_id != null){
                $ppm = $this->_service_locator->get('chevalTable')->fetchOne($pm->father_id);
            }
        } catch (Exception $ex) {}
        /* récupération de la mère du père */
        $mpm = null;
        try{
            if(is_object($m) && is_object($pmm) && $pm->mother_id != null){
                $mpm = $this->_service_locator->get('chevalTable')->fetchOne($pm->mother_id);
            }
        } catch (Exception $ex) {}/* récupération du père de la mère */
        $pmp = null;
        try{
            if(is_object($p) && is_object($mp) && $mp->father_id != null){
                $pmp = $this->_service_locator->get('chevalTable')->fetchOne($mp->father_id);
            }
        } catch (Exception $ex) {}
        /* récupération de la mère de la mère */
        $mmp = null;
        try{
            if(is_object($p) && is_object($mp) && $mp->mother_id != null){
                $mmp = $this->_service_locator->get('chevalTable')->fetchOne($mp->mother_id);
            }
        } catch (Exception $ex) {}
        /* récupération du père du père */
        $ppp = null;
        try{
            if(is_object($p) && is_object($pp) && $pp->father_id != null){
                $ppp = $this->_service_locator->get('chevalTable')->fetchOne($pp->father_id);
            }
        } catch (Exception $ex) {}
        /* récupération de la mère du père */
        $mpp = null;
        try{
            if(is_object($p) && is_object($pp) && $pp->mother_id != null){
                $mpp = $this->_service_locator->get('chevalTable')->fetchOne($pp->mother_id);
            }
        } catch (Exception $ex) {}
         /* récupération de la mère de la mère de la mère de la mère */
        $mmmm = null;
        try{
            if(is_object($m) && is_object($mm) && is_object($mmm) && $mmm->mother_id != null){
                $mmmm = $this->_service_locator->get('chevalTable')->fetchOne($mmm->mother_id);
            }
        } catch (Exception $ex) {}
        
        
        $che->pere = $p;
        $che->mere = $m;
        $che->pere_mere = $pm;
        $che->mere_mere = $mm;
        $che->pere_pere = $pp;
        $che->mere_pere = $mp;
        
        $che->mere_pere_mere = $mpm;
        $che->pere_pere_mere = $ppm;
        $che->mere_mere_mere = $mmm;
        $che->pere_mere_mere = $pmm;
        $che->mere_pere_pere = $mpp;
        $che->pere_pere_pere = $ppp;
        $che->mere_mere_pere = $mmp;
        $che->pere_mere_pere = $pmp;
        $che->mere_mere_mere_mere = $mmmm;
        
        $this->mainView->setVariable("cheval", $che);
        
        return $this->mainView;
    }
    
    public function reloadCostAction(){
        $return = array("data" => "", "status" => 0);
        try {
            /* recuperation de l'id enchere */
            $id_e = $this->params()->fromRoute('enchere_id');

            /* verification de l'existance de l'enchère, sinon redirection */
            $en = $this->_service_locator->get('enchereTable')->fetchOne($id_e);
            if(! is_object($en)) {
                throw new \Exception("unable to find enchere");
            }

            /* recuperation de l'id lot */
            $id = $this->params()->fromRoute('lot_id');
            $t = $this->_service_locator->get('lotTable')->fetchOne($id);
            if(! is_object($t)) {
                throw new \Exception("unable to find lot");
            }

            /* recuperation de l'enchère maximal */
            $res = $this->_service_locator->get('clientAuctionTable')->getLastEnchere($t);
            
            $return['status'] = 1;
            $return['data'] = array('amount' => ((!empty($res))?$res->value:$t->min_price));
        }catch(\Exception $e) {
            $return['status'] = 0;
            $return['data'] = $e->getMessage();
        }
        
        return new \Zend\View\Model\JsonModel($return);
    }
    
    public function validationAction(){
        $return = array('data'=>"", "status" => 0);
        try {
            /* verification de l'existance de l'enchere */
            $id_e = $this->params()->fromRoute('enchere_id');
            $en = $this->_service_locator->get('enchereTable')->fetchOne($id_e);

		    if(!is_object($en) || $en->status !=1) {
				throw new \Exception('Enchère non valide');
		    }

            /* vérification de l'existance du lot */
            $id = $this->params()->fromRoute('lot_id');
            $t = $this->_service_locator->get('lotTable')->fetchOne($id);

            if(!is_object($t) || $t->status != 1) {
				throw new \Exception('Enchère non valide');
		    }

            /* verification de la connection du membre */
            $membre = $this->_service_locator->get('user_service')->isMembreConnecte();
            if($membre ==  false) {
                throw new \Exception("Vous n'êtes pas connecté, veuillez d'abord vous connecter.");
            }

            /* verification des informations du membre (card_id) => sinon message pour redirection modificaiton du compte */
            $obj_member = $this->_service_locator->get('clientTable')->fetchOne($membre->client_id);
            if(!is_object($obj_member)) {
                $this->_service_locator->get('user_service')->setMembreConnecte(null);
                throw new \Exception("Vous n'êtes pas connecté, veuillez d'abord vous connecter.");
            }
            
            if(empty($obj_member->mangopay_autorisation_id )){
                throw new \Exception("Vous n'avez pas d'autorisation de paiement, vérifier vos données bancaires.");
            }
            
            /* recuperation de l'eventuelle ancienne plus grosse enchère */
            $ca = $this->_service_locator->get('clientauctionTable')->getLastEnchere($t);
            
            /* sauvegarde en bdd */
            $obj = new \Application\Model\ClientAuction();
            $obj->lot_id = $id;
            $obj->client_id = $membre->client_id;
            $obj->value = $_REQUEST['value'];
            $obj->authorization_id = $obj_member->mangopay_autorisation_id;
            $obj->card_id = $obj_member->mangopay_card_id;
            $obj = $this->_service_locator->get('clientAuctionTable')->save($obj);
            
            /* Envoi du mail */
            $this->_service_locator->get('user_service')->sendMailUserNewEnchere($obj, $membre->langue);
           
            if($ca != null) {
				$temp_membre = $this->_service_locator->get('clientTable')->fetchOne($ca->client_id);
                $this->_service_locator->get('user_service')->sendMailUserLooseEnchere($ca, $temp_membre->langue);
            }
            
            
            /* récupération de la dernière enchère du lot */
            $return['data']["lot"] = $t;
			$return['data']['email'] = $membre->email;
            $return['data']["enchere"] = $en;
            $return['data']["auction"] = $this->_service_locator->get('clientAuctionTable')->getLastEnchere($t);
            $return["status"] = 1;
        
        /* return */
        } catch (\Exception $e) {
            $return['status'] = 0;
            $return['data'] = $e->getMessage();
        }
        
        return new \Zend\View\Model\JsonModel($return);
    }
    
    public function informationAction(){
        $return = array('data'=>"", "status" => 0);
        try {
            /* verification de l'existance de l'enchere */
            $id_e = $this->params()->fromRoute('enchere_id');
            $en = $this->_service_locator->get('enchereTable')->fetchOne($id_e);

		    if(!is_object($en) || $en->status != 1) {
				throw new \Exception('Enchère non valide');
		    }

            /* vérification de l'existance du lot */
            $id = $this->params()->fromRoute('lot_id');
            $t = $this->_service_locator->get('lotTable')->fetchOne($id);

	 	    if(!is_object($t) || $t->status != 1) {
				throw new \Exception('Enchère non valide');
		    }

            /* verification de la connection du membre */
            $membre = $this->_service_locator->get('user_service')->isMembreConnecte();
            if($membre ==  false) {
                throw new \Exception("Vous n'êtes pas connecté, veuillez d'abord vous connecter.");
            }

            /* verification des informations du membre (card_id) => sinon message pour redirection modificaiton du compte */
            $obj_member = $this->_service_locator->get('clientTable')->fetchOne($membre->client_id);
            if(!is_object($obj_member)) {
                $this->_service_locator->get('user_service')->setMembreConnecte(null);
                throw new \Exception("Vous n'êtes pas connecté, veuillez d'abord vous connecter.");
            }

            /* récupération de la dernière enchère du lot */
            $return['data']["lot"] = $t;
            $return['data']["enchere"] = $en;
            $return['data']["auction"] = $this->_service_locator->get('clientAuctionTable')->getLastEnchere($t);
            $return["status"] = 1;
        
        /* return */
        } catch (\Exception $e) {
            $return['status'] = 0;
            $return['data'] = $e->getMesage();
        }
        
        return new \Zend\View\Model\JsonModel($return);
    }
}
	