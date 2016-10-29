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

    public function validationCreationAction(){
        parent::initListJs();
        $this->mainView->setVariable("lang_id", $this->lang_id);
        $this->mainView->setTemplate('frontoffice/member/validation_creation');
        return $this->mainView;
    }
    
    public function forgotpasswordAction(){
        parent::initListJs();
        if($data = $this->getRequest()->getPost() && isset($_POST['email'])) {
            $obj = $this->_service_locator->get('clientTable')->fetchOneByEmail($_POST['email']);
            if(is_object($obj)) {
                $this->_service_locator->get('user_service')->sendMailUserPassword($obj, $this->lang_id);
            }
            $this->addSuccess("Si vous êtes bien membre, un email vient de vous être envoyé");
        }
        
        $this->mainView->setVariable("lang_id", $this->lang_id);
        $this->mainView->setTemplate('frontoffice/member/forgotpassword');
        return $this->mainView;
    }
    
    public function validationAccountAction(){
        try {
            $token = $this->params()->fromRoute('token');
            
            $obj = $this->_service_locator->get('clientTable')->fetchOneByToken($token);
            $obj->token = null;
            $obj->status = 1;
            $obj = $this->_service_locator->get('clientTable')->save($obj);
            
            $this->_service_locator->get('user_service')->sendMailBienvenu($obj, $obj->langue);
            
            $carte_registration = $this->createUserMangopay($obj, $obj->client_id);
            
            $this->_service_locator->get('user_service')->setMembreConnecte($obj->id);
            $this->addSuccess("Bienvenue ".$obj->lastname." ".$obj->firstname." sur votre espace membre de la plate-forme Elite Auction");
        } catch (Exception $ex) {
           $this->addError($e->getMessage());
        }
        return $this->redirect()->toRoute("home/membre/edit_cart", array('lang' => $this->lang_id, "membre_id" => $obj->client_id));
    }
    
    public function authentificationAction(){
        parent::initListJs();
        
        /* si l'utilisateur est déja connecté on le redirige vers la home */
        if($this->_service_locator->get('user_service')->isMembreConnecte()) {
            return $this->redirect()->toRoute('home', array('lang' => $this->lang_id));
        }
        
        $this->mainView->setVariable("lang_id", $this->lang_id);
        $this->mainView->setTemplate('frontoffice/member/login');
        return $this->mainView;
    }
    
    public function logoutAction(){
        $this->_service_locator->get('user_service')->setMembreConnecte(null);
        return $this->redirect()->toRoute("home", array('lang' => $this->lang_id));
    }
    
    public function listenchereAction(){
        /* si l'utilisateur est déja connecté on le redirige vers la home */
        $membre = $this->_service_locator->get('user_service')->isMembreConnecte();
        if($membre == false) {
            return $this->redirect()->toRoute('home', array('lang' => $this->lang_id));
        }
        
        if($membre->client_id != $this->params()->fromRoute('membre_id')) {
            $this->_service_locator->get('user_service')->setMembreConnecte(null);
            return $this->redirect()->toRoute('home', array('lang' => $this->lang_id));
        }
        
        parent::initListJs();
        
        $this->mainView->setVariable("lang_id", $this->lang_id);
        $this->mainView->setTemplate('frontoffice/lots');
        
        /* recuperation de l'id enchere */
        $id = $this->params()->fromRoute('enchere_id');

        /* récupération des lots */
        $e = array();
        $l = $this->_service_locator->get('lotTable')->fetchAllAuction(array("client_id" => $membre->client_id, "lot.status" => 1),  'number');
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
            $i->pere = $p;
            $i->mere = $m;
            $i->pere_mere = $pm;
            
            $e[] = $i;
        }
        $this->mainView->setVariable("lots", $e);
        
        return $this->mainView;
    }
    
    public function editAction(){
        /* modification accessible que si nous sommes en mode connecté */
        if($this->params()->fromRoute('membre_id') != null && !$this->_service_locator->get('user_service')->isMembreConnecte()){
            return $this->redirect()->toRoute("home/membre", array('lang' => $this->lang_id));
        }
        /* Si nous tentons de modifier quelqu'un d'autre que nous */
        if($this->params()->fromRoute('membre_id') != null){
            $t = $this->_service_locator->get('user_service')->isMembreConnecte();
            if($t->client_id != $this->params()->fromRoute('membre_id')) {
                return $this->redirect()->toRoute("home", array('lang' => $this->lang_id));
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
        $form->setLang($this->lang_id);
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
        $FormViewModel->setVariable('lang_id', $this->lang_id);	
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
			
			/* remise au format de la date */
			$save_date = $data['birthday'];
			$t = explode('/', $data['birthday']);
			if(count($t) == 3) {
				$data['birthday'] = $t[2].'-'.$t[1].'-'.$t[0];
			}
            
            /* modification accessible que si nous sommes en mode connecté */
            if($id != null && !$this->_service_locator->get('user_service')->isMembreConnecte()){
                return $this->redirect()->toRoute("home/membre", array('lang' => $this->lang_id));
            }
            /* Si nous tentons de modifier quelqu'un d'autre que nous */
            if($id != null){
                $t = $this->_service_locator->get('user_service')->isMembreConnecte();
                if($t->client_id != $id) {
                    return $this->redirect()->toRoute("home", array('lang' => $this->lang_id));
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
			if(count(explode('-', $data['birthday'])) != 3) {
                $form->setMessages(array(
                    'birthday' => array(
                        'La date de naissance est mal formée (dd/mm/yyy)'
                    )
                ));
            }
            if($data['type'] == "pro" && (!isset($data['societe']) ||empty($data['societe']))) {
                $form->setMessages(array(
                    'societe' => array(
                         'Le nom de la société est obligatoire pour les professionnels'
                    )
                ));
            }
            
            /* TODO : verification du non doublon d'email */
            $t = $this->_service_locator->get('user_service')->isMembreConnecte();
            if($id == null || $t->email != $data['email']) {
                try{
                    $this->_service_locator->get('clientTable')->fetchOneByEmail($data['email']);
                    
                    /* si nous somme toujours ici, cela signifie que la fonction a trouvée un client avec cet email */
                    $form->setMessages(array(
                        'email' => array(
                             'L\'adresse mail saisie est déjà utilisée, veuillez en saisir une autre'
                        )
                    ));
                }catch(\Exception $e) {}
            }
            
            
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
                    
                    $this->_service_locator->get('user_service')->setMembreConnecte($obj->client_id);
                    
                    $carte_registration = $this->createUserMangopay($obj, $id);
                    
                    if(empty($id)){
                        $this->getServiceLocator()->get('user_service')->sendMailUserCreation($obj, $obj->langue);
                        $data_url = array(
                            'lang' => $this->lang_id
                        );
                        return $this->redirect()->toRoute('home/validation_creation', $data_url);
                    }
                    
                    $id = $obj->client_id;
                    $this->addSuccess('La sauvegarde a été effectuée avec succès');
                    $data_url = array(
                        'membre_id' => $id,
						'lang' => $this->lang_id
                    );
                    return $this->redirect()->toRoute('home/membre/edit_cart', $data_url);
                } catch (Exception $e) {
                    $data['birthday'] = $save_date;
					if(empty($id) && is_object($obj)) {
                        $clientModel = $this->getServiceLocator()->get('clientTable');
                        $clientModel->delete($obj);
                    }
                    
                    $this->addError('La sauvegarde a échouée');
                    $this->addError($e->getMessage());
                    $this->getServiceLocator()->get('user_service')->setInfoFormMembre(array($data, $e->getMessage()));
                    $data_url = array(
                        'membre_id' => $id,
						'lang' => $this->lang_id
                    );
                    return $this->redirect()->toRoute('home/membre/edit', $data_url);
                }
            }else{
                $this->addError('La sauvegarde a échouée');
                if($messages = $form->getMessages()){
                    foreach($messages as $key => $message){
                        $this->addError($key." : ".(is_array($message)?current($message):$message));
                    }
                }
                $this->getServiceLocator()->get('user_service')->setInfoFormMembre(array($data, $form->getMessages()));
                if(!empty($id)) {
					$data_url = array(
                        'membre_id' => $id,
						'lang' => $this->lang_id
                    );
                    return $this->redirect()->toRoute('home/membre/edit', $data_url);
				} else {
                    $data_url = array(
                        'lang' => $this->lang_id
                    );
					return $this->redirect()->toRoute('home/membre/edit', $data_url);
				}
            }
        }else{
            throw new Exception('Aucune donnée envoyée.');
        }
        $data_url = array(
            'membre_id' => $id,
            'lang' => $this->lang_id
        );
        return $this->redirect()->toRoute('home/membre/edit', $data_url);
    }
    
    public function carteAction(){
        /* modification accessible que si nous sommes en mode connecté */
        if($this->params()->fromRoute('membre_id') != null && !$this->_service_locator->get('user_service')->isMembreConnecte()){
            return $this->redirect()->toRoute("home/membre", array('lang' => $this->lang_id));
        }
        /* Si nous tentons de modifier quelqu'un d'autre que nous */
        if($this->params()->fromRoute('membre_id') != null){
            $t = $this->_service_locator->get('user_service')->isMembreConnecte();
            if($t->client_id != $this->params()->fromRoute('membre_id')) {
                return $this->redirect()->toRoute("home", array('lang' => $this->lang_id));
            }
        }
        
        $clientModel = $this->getServiceLocator()->get('clientTable');
        $obj = $clientModel->fetchOne($this->params()->fromRoute('membre_id'));
        
        $carte_reg = $this->_service_locator->get('user_service')->getCarteRegistration();
        if(empty($carte_reg)) {
            $this->addError("No carte registration en cours");
            return $this->redirect()->toRoute('home/membre/edit', array(
                'membre_id' => $this->params()->fromRoute('membre_id'),
   				'lang' => $this->lang_id
            ));
        }
            
        $this->mainView->setTemplate('frontoffice/member/carte');
        $this->mainView->setVariable('carte_reg', $carte_reg);
        $this->mainView->setVariable('membre_id', $this->params()->fromRoute('membre_id'));
        return $this->mainView;
    }
    
    public function carteRetourPreAuthAction(){exit;}
    
    public function carteRegisterAction(){
        /*$clientModel = $this->getServiceLocator()->get('clientTable');
        $obj = $clientModel->fetchOne($this->params()->fromRoute('membre_id'));
        if(is_object($obj)) {
            $obj->carte_numero = $_POST['cardNumber'];
            $obj->carte_date = $_POST['cardExpirationDate'];
            $obj->carte_cle = $_POST['cardCvx'];
            $clientModel->save($obj);
        }
        echo "OK";
        exit;*/
    }
    
    public function carteRetourAction(){
        /* modification accessible que si nous sommes en mode connecté */
        if($this->params()->fromRoute('membre_id') != null && !$this->_service_locator->get('user_service')->isMembreConnecte()){
            return $this->redirect()->toRoute("home/membre", array('lang' => $this->lang_id));
        }
        /* Si nous tentons de modifier quelqu'un d'autre que nous */
        if($this->params()->fromRoute('membre_id') != null){
            $t = $this->_service_locator->get('user_service')->isMembreConnecte();
            if($t->client_id != $this->params()->fromRoute('membre_id')) {
                return $this->redirect()->toRoute("home", array('lang' => $this->lang_id));
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
            
            /* creation de la pré-authentification */
            if(empty($obj->mangopay_id) || empty($obj->mangopay_wallet_id) || empty($obj->mangopay_carte_id) || empty($obj->mangopay_card_id)) {
                throw new \Exception("Vous n'avez pas fini la création de votre compte, vos informations bancaires sont erronées.");
            }

            $res = $this->_service_locator->get('mangopay_service')->makePreAuth($obj);
            if($res->Status != "SUCCEEDED") {
                throw new \Exception($res->ResultMessage);
            }
            $obj->mangopay_autorisation_id = $res->Id;
            $clientModel->save($obj);
            
            
            $this->addSuccess('L\'édition du compte membre est finie.');
            return $this->redirect()->toRoute("home", array('lang' => $this->lang_id));
        }catch(\Exception $e) {
            $this->addError($e->getMessage());
            return $this->redirect()->toRoute('home/membre/edit', array(
                'membre_id' => $this->params()->fromRoute('membre_id'),
				'lang' => $this->lang_id
            ));
        }
    }
    
    public function loginAction() {
        if(!isset($_POST['email']) || !isset($_POST['password']) 
            || empty($_POST['email']) || empty($_POST['password'])) {
            $this->addError('Le formulaire n\'est pas valide');
            return $this->redirect()->toRoute('home/membre', array('lang' => $this->lang_id));
        }
        
        $clientModel = $this->getServiceLocator()->get('clientTable');
        $obj = null;
        try {
            $obj = $clientModel->fetchOneByEmail($_POST['email']);
        }catch(\Exception $e) {}
        
        if(! is_object($obj)){
            $this->addError('Login ou mot de passe invalide');
            return $this->redirect()->toRoute('home/membre', array('lang' => $this->lang_id));
        }
        
        if($obj->password != $_POST['password']) {
            $this->addError('Login ou mot de passe invalide');
            return $this->redirect()->toRoute('home/membre', array('lang' => $this->lang_id));
        }
        
        /* On vide toute les données concernant la pré-auth */
        $obj->mangopay_autorisation_id = null;
        $clientModel->save($obj);
        
        $this->addSuccess('Connexion réussie');
        $this->_service_locator->get('user_service')->setMembreConnecte($obj->id);
        
        /* verification de la carte */
        try{
           $res = $this->_service_locator->get('mangopay_service')->makePreAuth($obj);
            if($res->Status != "SUCCEEDED") {
                throw new \Exception($res->ResultMessage);
            }
            $obj->mangopay_autorisation_id = $res->Id;
            $clientModel->save($obj);
            
        }catch(\Exception $e){
            $this->addError('Erreur lors de la vérification de votre carte : '.$e->getMessage());
        }       
        
        return $this->redirect()->toRoute('home', array('lang' => $this->lang_id));
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
            $obj->mangopay_id = $mangopay_user->Id;
            $obj = $clientModel->save($obj);
        }

        /* creation du wallet user*/
        $result2 = $this->_service_locator->get('mangopay_service')->createWallet($mangopay_user);
        if(!is_object($result2)) {
            throw new \Exception("Membre updated, but error with mangopay : ".$result2);
        }
        $obj->mangopay_wallet_id = $result2->Id;
        $obj = $clientModel->save($obj);

        /* creation de la carte */
        $result3 = $this->_service_locator->get('mangopay_service')->cardRegistration($mangopay_user);
        if(!is_object($result3)) {
            throw new \Exception("Membre updated, but error with mangopay : ".$result3);
        }
        /* sauvegarde de l'id de la carte */
        $obj->mangopay_carte_id = $result3->Id;
        $obj->carte_url = $result3->CardRegistrationURL;
        $obj->carte_data = $result3->PreregistrationData;
        $obj->carte_accesskeyref = $result3->AccessKey;
        $clientModel->save($obj);
        $this->_service_locator->get('user_service')->setCarteRegistration($result3);
        
        return $result3;
    }
}
