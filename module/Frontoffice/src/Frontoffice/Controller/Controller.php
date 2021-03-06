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
        
        $viewModel = new ViewModel();
        $viewModel->setTemplate('frontoffice/lot');
        
        /* recuperation de l'id enchere */
        $id_e = $this->params()->fromRoute('enchere_id');

        /* verification de l'existance de l'enchère, sinon redirection */
        $en = null;
        try {
            $en = $this->_service_locator->get('enchereTable')->fetchOne($id_e);
        } catch(\Exception $e) {}
        
        if(! is_object($en)) {
            return $this->redirect()->toRoute('home');
        }
        $viewModel->setVariable("enchere", $en);
        
        /* recuperation de l'id lot */
        $id = $this->params()->fromRoute('lot_id');

        /* verification de l'existance du lot, sinon redirection */
        $t = null;
        try {
            $t = $this->_service_locator->get('lotTable')->fetchOne($id);
        } catch(\Exception $e) {}
        
        if(! is_object($t)) {
            return $this->redirect()->toRoute('home');
        }

        /* recuperation des images du lot*/
        $lim = $this->_service_locator->get("imageTable")->fetchAll(array("lot_id" => $id));
        $tab = array();
        foreach ($lim as $im) {
            $tab[] = $im->filename;
        }
        $t->list_image = $tab;
        
        $viewModel->setVariable("lot", $t);
        
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
                $pm = $this->_service_locator->get('chevalTable')->fetchOne($m->mother_id);
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
        
        $viewModel->setVariable("cheval", $che);
        
        return $viewModel;
    }
}
