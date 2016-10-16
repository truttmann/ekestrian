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

    public function authentificationAction(){
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
                    
                    $this->_service_locator->get('user_service')->setMembreConnecte($obj);
                    
                    $carte_registration = $this->createUserMangopay($obj, $id);
                    $this->_service_locator->get('user_service')->setCarteRegistration($carte_registration);
                    
                    $id = $obj->client_id;
                    $this->addSuccess('La sauvegarde a été effectuée avec succès');
                } catch (Exception $e) {
                    
                    if(empty($id) && is_object($obj)) {
                        $clientModel = $this->getServiceLocator()->get('clientTable');
                        $clientModel->delete($obj);
                    }
                    
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
                        $this->addError($message);
                    }
                }
                $this->getServiceLocator()->get('user_service')->setInfoFormMembre(array($data, $form->getMessages()));
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
    
    public function carteAction(){
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
        
        $clientModel = $this->getServiceLocator()->get('clientTable');
        $obj = $clientModel->fetchOne($this->params()->fromRoute('membre_id'));
        
        $carte_reg = $this->_service_locator->get('user_service')->getCarteRegistration();
        if(empty($carte_reg)) {
            $this->addError("No carte registration en cours");
            return $this->redirect()->toRoute('home/membre/edit', array(
                'membre_id' => $this->params()->fromRoute('membre_id'),
            ));
        }
        
        /* sauvegarde de l'id de la carte */
        $obj->mangopay_carte_id = $carte_reg->Id;
        $clientModel->save($obj);
            
            
        $this->mainView->setTemplate('frontoffice/member/carte');
        $this->mainView->setVariable('carte_reg', $carte_reg);
        $this->mainView->setVariable('membre_id', $this->params()->fromRoute('membre_id'));
        return $this->mainView;
    }
    
    public function carteRegisterAction(){
        $clientModel = $this->getServiceLocator()->get('clientTable');
        $obj = $clientModel->fetchOne($this->params()->fromRoute('membre_id'));
        if(is_object($obj)) {
            $obj->carte_numero = $_POST['cardNumber'];
            $obj->carte_date = $_POST['cardExpirationDate'];
            $obj->carte_cle = $_POST['cardCvx'];
            $clientModel->save($obj);
        }
        echo "OK";
        exit;
    }
    
    public function carteRetourAction(){
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
        
        try{
            $clientModel = $this->getServiceLocator()->get('clientTable');
            $obj = $clientModel->fetchOne($this->params()->fromRoute('membre_id'));

            $carte_reg_f = $this->_service_locator->get('mangopay_service')->cardRegistrationFinish($obj, @$_GET["data"], @$_GET['errorCode']);
            /* sauvegarde de l'id de la carte */
            $obj->mangopay_card_id = $carte_reg_f->CardId;
            $obj->status = 1;
            $clientModel->save($obj);

            $this->addSuccess('L\'édition du compte membre est finie.');
            return $this->redirect()->toRoute("home");
        }catch(\Exception $e) {
            $this->addError($e->getMessage());
            return $this->redirect()->toRoute('home/membre/edit', array(
                'membre_id' => $this->params()->fromRoute('membre_id'),
            ));
        }
    }
    
    public function loginAction() {
        if(!isset($_POST['email']) || !isset($_POST['password']) 
            || empty($_POST['email']) || empty($_POST['password'])) {
            $this->addError('Le formulaire n\'est pas valide');
            return $this->redirect()->toRoute('home/membre', array());
        }
        
        $clientModel = $this->getServiceLocator()->get('clientTable');
        $obj = null;
        try {
            $obj = $clientModel->fetchOneByEmail($_POST['email']);
        }catch(\Exception $e) {}
        
        if(! is_object($obj)){
            $this->addError('Login ou mot de passe invalide');
            return $this->redirect()->toRoute('home/membre', array());
        }
        
        if($obj->password != $_POST['password']) {
            $this->addError('Login ou mot de passe invalide');
            return $this->redirect()->toRoute('home/membre', array());
        }
        
        $this->_service_locator->get('user_service')->setMembreConnecte($obj);
        
        $this->addSuccess('Connexion réussie');
        return $this->redirect()->toRoute('home');
    }
    
    private function createUserMangopay($obj, $id) {
        $clientModel = $this->getServiceLocator()->get('clientTable');
        /* si c'est une creation, nous ajoutons l'utilisateur à mango pay */
        if($obj->mangopay_id == null) {
           $mangopay_user = $this->_service_locator->get('mangopay_service')->createUser($obj);
            if(empty($mangopay_user) || !is_object($mangopay_user) || !is_numeric($mangopay_user->Id)) {
                throw new \Exception("Membre created, but error with mangopay : ".$mangopay_user);
            }
            $obj->mangopay_id = $mangopay_user->Id;
            $obj = $clientModel->save($obj);
        } else {
            $mangopay_user = $this->_service_locator->get('mangopay_service')->updateUser($obj);
            if(!is_object($mangopay_user)) {
                throw new \Exception("Membre updated, but error with mangopay : ".$mangopay_user);
            }
        }

        /* creation du wallet user*/
        if($obj->mangopay_wallet_id == null){
            $result2 = $this->_service_locator->get('mangopay_service')->createWallet($mangopay_user);
            if(!is_object($result2)) {
                throw new \Exception("Membre updated, but error with mangopay : ".$result2);
            }
            $obj->mangopay_wallet_id = $result2->Id;
            $obj = $clientModel->save($obj);
        }

        /* creation de la carte */
        $result3 = $this->_service_locator->get('mangopay_service')->cardRegistration($mangopay_user);
        if(!is_object($result3)) {
            throw new \Exception("Membre updated, but error with mangopay : ".$result3);
        }
        return $result3;
    }
}
