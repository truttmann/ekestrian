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

class MembreController extends InitController
{
    public function __construct($service)
    {
        $this->_service_locator = $service;
        parent::__construct();
    }

    public function loginAction(){
        parent::initListJs();
        
        /* si l'utilisateur est déja connecté on le redirige vers la home */
        if($this->_service_locator->get('user_service')->isMembreConnecte()) {
            return $this->redirect()->toRoute('home');
        }
        
        $viewModel = new ViewModel();
        $viewModel->setVariable("lang_id", $this->lang_id);
        $viewModel->setTemplate('frontoffice/member/login');
        return $viewModel;
    }
    
    public function editAction(){
        /* modification accessible que si nous sommes en mode connecté */
        if($this->params()->fromRoute('membre_id') != null && !$this->_service_locator->get('user_service')->isMembreConnecte()){
            return $this->redirect()->toRoute("home/membre");
        }
        /* Si nous tentons de modifier quelqu'un d'autre que nous */
        if($this->params()->fromRoute('membre_id') != null){
            $t = $this->_service_locator->get('user_service')->isMembreConnecte();
            if($t->client_id != $this->params()->fromRoute('membre_id')) {
                return $this->redirect()->toRoute("home");
            }
        }
        parent::initListJs();
        $this->setPageTitle('Création d\'un compte Membre');

        $this->initFormJs();

        $this->addLinkToBreadcrumb('home', 'Accueil');

        $data = array();

        if ($id = $this->params()->fromRoute('membre_id')) {
            $items = $this->getServiceLocator()->get('clientTable');
            $data = $items->fetchOne($id)->toArray();
            $mainViewModel = new \Application\Model\ViewForm($data['lastname'], 'membre_account', 'home/membre/save', array());
        } else {
            $mainViewModel = new \Application\Model\ViewForm('membre_account', 'membre_account', 'home/membre/save', array());
        }
        
        $this->addMainButton('Retour', 'home', array(), 'btn blue-madison', array());
        $this->addMainButton('Sauvegarder', null, null, 'btn red-intense save');

        $form = $this->getServiceLocator()->get('FormElementManager')->get("membre_edit");
        $form->setServiceLocator($this->getServiceLocator());
        $form->initForm();
        
        $t = $this->getServiceLocator()->get('user_service')->getInfoFormMembre();
        if(!empty($t)) {
            $form->setData($t[0]);
            $form->setMessages($t[1]);
            $this->getServiceLocator()->get('user_service')->setInfoFormMembre(null);
        } else {
            $form->setData($data);
        }

        $FormViewModel = new ViewModel();
        $FormViewModel->setTemplate('frontoffice/form/membre');
        $FormViewModel->setVariable('form', $form);
        $FormViewModel->setVariable('edition', ((is_numeric($id))?true:false));
        $FormViewModel->setVariable('form_title', 'Création compte Membre');

        $this->mainView->setVariable('action_route', $this->url()->fromRoute('home/membre/save', array()));
        $this->mainView->addChild($FormViewModel, 'form');
        $this->mainView->setTemplate('application/page/form_container');
        
        return $this->mainView;
    }
    
    public function saveAction() {
        if($data = $this->getRequest()->getPost()){
            $id = $data['client_id'];
            
            /* modification accessible que si nous sommes en mode connecté */
            if($id != null && !$this->_service_locator->get('user_service')->isMembreConnecte()){
                return $this->redirect()->toRoute("home/membre");
            }
            /* Si nous tentons de modifier quelqu'un d'autre que nous */
            if($id != null){
                $t = $this->_service_locator->get('user_service')->isMembreConnecte();
                if($t->client_id != $id) {
                    return $this->redirect()->toRoute("home");
                }
            }
            
            if($id !== null) {
                $data['condition_vente'] = $data['reglement'] = $data['confidence'] = 1;
            }
            
            $obj = new \Application\Model\Client();
            
            
            $form = $this->getServiceLocator()->get('FormElementManager')->get("membre_edit");
            $form->setServiceLocator($this->getServiceLocator());
            $form->initForm();
            $form->setInputFilter($obj->getInputFilter());
            $form->setData($data);
            
            /* Verificaton que le mot de passe et sa confirmation sont bien identique */
            if($data['password'] != $data['password_confirm']) {
                $form->setMessages(array(
                    'password_confirm' => array(
                         'La validation de votre mot de passe n\'est pas conforme'
                    )
                ));
            }
            if(!isset($data['condition_vente'])) {
                $form->setMessages(array(
                    'condition_vente' => array(
                        'Vous devez cocher la case'
                    )
                ));
            }
            if(!isset($data['reglement'])) {
                $form->setMessages(array(
                    'reglement' => array(
                        'Vous devez cocher la case'
                    )
                ));
            }
            if(!isset($data['confidence'])) {
                $form->setMessages(array(
                    'confidence' => array(
                        'Vous devez cocher la case'
                    )
                ));
            }
            /* TODO : verification du non doublon d'email */
            if ($form->isValid() && empty($form->getMessages())) {
                try {
                    // sauvegarde de l'heure de mea
                    $clientModel = $this->getServiceLocator()->get('clientTable');
                    if(!empty($data['client_id'])) {
                        $obj = $clientModel->fetchOne($data['client_id']);
                    }
                    $obj->exchangeArray($data->toArray());
                    $obj->status = 0;
                    $obj = $clientModel->save($obj);
                    
                    /* si c'est une creation, nous ajoutons l'utilisateur à mango pay */
                    if(empty($id)){
                        $mangopay_user = $this->_service_locator->get('mangopay_service')->createUser($obj);
                        if(!empty($mangopay_user) && is_object($mangopay_user) && is_numeric($mangopay_user->Id)) {
                            $obj->mangopay_id = $mangopay_user->Id;
                            $obj = $clientModel->save($obj);
                            
                            /* creation du wallet user*/
                            $result2 = $this->_service_locator->get('mangopay_service')->createWallet($mangopay_user);
                            if(!is_object($result2)) {
                                $obj->mangopay_wallet_id = $result2->Id;
                                $obj = $clientModel->save($obj);
                            }
                        } else {
                            $obj = $clientModel->delete($obj);
                            throw new \Exception($mangopay_user);
                        }
                    } else {
                        if($obj->mangopay_id == null) {
                           $mangopay_user = $this->_service_locator->get('mangopay_service')->createUser($obj);
                            if(!empty($mangopay_user) && is_object($mangopay_user) && is_numeric($mangopay_user->Id)) {
                                $obj->mangopay_id = $mangopay_user->Id;
                                $obj = $clientModel->save($obj);

                                /* creation du wallet user*/
                                $result2 = $this->_service_locator->get('mangopay_service')->createWallet($mangopay_user);
                                if(!is_object($result2)) {
                                    $obj->mangopay_wallet_id = $result2->Id;
                                    $obj = $clientModel->save($obj);
                                }
                            } else {
                                throw new \Exception($mangopay_user);
                            } 
                        } else {
                            $mangopay_user = $this->_service_locator->get('mangopay_service')->updateUser($obj);
                            if(!is_object($mangopay_user)) {
                                throw new \Exception($mangopay_user);
                            }
                            
                            /* creation du wallet user*/
                            if($obj->mangopay_wallet_id == null){
                                $result2 = $this->_service_locator->get('mangopay_service')->createWallet($mangopay_user);
                                if(!is_object($result2)) {
                                    $obj->mangopay_wallet_id = $result2->Id;
                                    $obj = $clientModel->save($obj);
                                }
                            }
                        }
                    }
                    
                    $id = $obj->client_id;
                    $this->addSuccess('La sauvegarde a été effectuée avec succès');
                } catch (Exception $e) {
                    $this->addError('La sauvegarde a échouée');
                    $this->addError($e->getMessage());
                    $this->getServiceLocator()->get('user_service')->setInfoFormMembre(array($data, $e->getMessage()));
                    return $this->redirect()->toRoute('home/membre/edit', array(
                        'membre_id' => $id,
                    ));
                }
            }else{
                $this->addError('La sauvegarde a échouée');
                if($messages = $form->getMessages()){
                    foreach($messages as $message){
                        var_dump($message);
                        $this->addError($message);
                    }
                }
                $this->getServiceLocator()->get('user_service')->setInfoFormMembre(array($data, $form->getMessages()));
                var_dump("id = ".$id);exit;
                return $this->redirect()->toRoute('home/membre/edit', array(
                    'membre_id' => $id,
                ));
            }
        }else{
            throw new Exception('Aucune donnée envoyée.');
        }
        return $this->redirect()->toRoute('home/membre/edit_cart', array(
            'membre_id' => $id,
        ));
    }
}
