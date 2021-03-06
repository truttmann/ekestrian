<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;


use Application\Form\ClientForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\InitController;
use Application\MediaManager\Model\ViewMediaManager;

class ClientController extends InitController
{

    public function listAction(){

        parent::initListJs();

        $this->setPageTitle('Liste des Clients');

        $this->addLinkToBreadcrumb('clients', 'Liste des clients');

        $list = $this->getList("clients_list");

        $list->setTitle('Clients');

        $items = $this->getServiceLocator()->get('clientTable');
        
        $filters = $list->getFilters();
        $order = $list->getOrder();

        $list->addLinks(
            array(
                array(
                    'label' => 'Nouveau',
                    'route' => "clients/edit"
                )
            )
        );

        $items = $items->fetchAllPaginate($filters['filters'], $order);
        $items->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
        $nbr = (isset($filters['nbr_results_per_page']))?$filters['nbr_results_per_page']:20;
        $items->setItemCountPerPage($nbr);
        $list->getPaginator()->setPaginatorObject($items);

        $list->setItems($items);

        $list->setTitleItemsOnly('camping en base');
        $list->setTitleItemsPlural('campings en base');

        $list->addCols(
            array(
                array(
                    'code'          =>  'societe',
                    'label'         =>  'Société',
                    'searchable'    =>  true,
                    'sortable'      =>  true,
                    'type'          =>  'text',
                    'width'         =>  '15',
                    'placeholder'         =>  '#',
                ),
                array(
                    'code'          =>  'email',
                    'clickable'     =>  true,
                    'route'         => 'clients/edit',
                    'matching_params'   => array("client_id"),
                    'label'         =>  'Email',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '15',
                    'placeholder'   =>  'Email',

                ),
                array(
                    'code'          =>  'lastname',
                    'label'         =>  'Prénom',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '20',
                    'placeholder'   =>  'Prénom',
                ),
                array(
                    'code'          =>  'firstname',
                    'label'         =>  'Nom',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '15',
                    'placeholder'   =>  'Nom',
                ),
                array(
                    'code'          =>  'status',
                    'label'         =>  'Actif',
                    'type'          =>  'boolean',
                    'width'         =>  '5',
                ),

                array(
                    'code'          =>  'action',
                    'label'         =>  'Actions',
                    'searchable'    =>  false,
                    'type'          =>  'link',
                    'width'         =>  '15',
                    'items'         =>  array(
                        array(
                            'route'     =>  'clients/edit',
                            'label'     =>  'Editer',
                            'matching_params'    =>  array(
                                'client_id'
                            )
                        ),
                        array(
                            'route'     =>  'clients/delete',
                            'label'     =>  'Supprimer',
                            'matching_params'    =>  array(
                                'client_id'
                            )
                        )
                    )
                ),
            )
        );

        return $list->toHtml();
    }
    
    public function editAction(){

        $this->setPageTitle('Edition d\'un client');

        $this->initFormJs();

        $this->addLinkToBreadcrumb('clients', 'Liste des clients')
            ->addLinkToBreadcrumb('clients/edit', 'Edition d\'un client', array("client_id" => $this->params()->fromRoute('client_id')));

        $data = array();

        if ($id = $this->params()->fromRoute('client_id')) {
            $items = $this->getServiceLocator()->get('clientTable');
            $data = $items->fetchOne($id)->toArray();
            $mainViewModel = new \Application\Model\ViewForm($data['lastname'], 'clients', 'clients/save', array());
        } else {
            $mainViewModel = new \Application\Model\ViewForm('Clients', 'clients', 'clients/save');
        }
        
        $this->addMainButton('Retour', 'clients', array(), 'btn blue-madison');
        $this->addMainButton('Sauvegarder', null, null, 'btn red-intense save');

        $form = $this->getServiceLocator()->get('FormElementManager')->get("client_edit");
        $form->setServiceLocator($this->getServiceLocator());
        $form->initForm();
        
        $t = $this->getServiceLocator()->get('user_service')->getInfoForm();
        if(!empty($t)) {
            $form->setData($t[0]);
            $form->setMessages($t[1]);
            $this->getServiceLocator()->get('user_service')->setInfoForm(null);
        } else {
            $form->setData($data);
        }

        $FormViewModel = new ViewModel();
        $FormViewModel->setTemplate('application/form/clients');
        $FormViewModel->setVariable('form', $form);
        $FormViewModel->setVariable('client_id', $id);
        

        $FormViewModel->setVariable('form_title', 'Informations du client');

        $this->mainView->setVariable('action_route', $this->url()->fromRoute('clients/save', array("client_id"=>$id)));
        $this->mainView->addChild($FormViewModel, 'form');
        $this->mainView->setTemplate('application/page/form_container');


        return $this->mainView;
    }
    
    public function deleteAction(){
        if ($id = $this->params()->fromRoute('client_id')) {
            $items = $this->getServiceLocator()->get('clientTable');
            $c = $items->fetchOne($id);
            try{
		    $items->delete($c);
		    $this->addSuccess('La suppression a été effectuée avec succès');
	    }catch(\Exception $e) {
		    $this->addError('La suppression est impossible, ce menbre a déjà emis des enchères');
	    }
            
        } 
        return $this->redirect()->toRoute('clients', array());
    }
    
    public function saveAction() {
        $clientModel = $this->getServiceLocator()->get('clientTable');
                    
        $id = $this->params()->fromRoute('client_id');
        if($data = $this->getRequest()->getPost()){
            $data['condition_vente'] = 1;
            $data['reglement'] = 1;
            $data['confidence'] = 1;
            
            if(empty($data['password'])) {
                if(!empty($data['client_id'])) {
                    $obj = $clientModel->fetchOne($data['client_id']);
                }
                $data['password'] = $data['password_confirm'] = $obj->password;
            }
            
            $obj = new \Application\Model\Client();
            
            $form = $this->getServiceLocator()->get('FormElementManager')->get("client_edit");
            $form->setServiceLocator($this->getServiceLocator());
            $form->initForm();
            $form->setInputFilter($obj->getInputFilter());
            $form->setData($data);
            
            /* Verificaton que le mot de passe et sa confirmation sont bien identique */
            if(!empty($data['password']) && $data['password'] != $data['password_confirm']) {
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
            if($data['type'] == "pro" && (!isset($data['societe']) || empty($data['societe']))) {
                $form->setMessages(array(
                    'societe' => array(
                         'Le nom de la société est obligatoire pour les professionnels'
                    )
                ));
            }
            
            if ($form->isValid() && empty($form->getMessages())) {
                try {
                    // sauvegarde de l'heure de mea
                    if(!empty($data['client_id'])) {
                        $obj = $clientModel->fetchOne($data['client_id']);
                    }
                    $obj->exchangeArray($data->toArray());
                    $obj = $clientModel->save($obj);
                    $id = $obj->client_id;
                    $this->addSuccess('La sauvegarde a été effectuée avec succès');
                } catch (Exception $e) {
                    echo 'Exception reçue : ',  $e->getMessage(), "\n";
                }
            }else{
                $this->addError('La sauvegarde a échouée');
                if($messages = $form->getMessages()){
                    foreach($messages as $key => $message){
                        $this->addError($key." : ".(is_array($message)?current($message):$message));
                    }
                }
                $this->getServiceLocator()->get('user_service')->setInfoForm(array($data, $form->getMessages()));
                return $this->redirect()->toRoute('clients/edit', array(
                    'client_id' => $id,
                ));
            }
        }else{
            throw new Exception('Aucune donnée envoyée.');
        }
        return $this->redirect()->toRoute('clients/edit', array(
            'client_id' => $id,
        ));
    }
}
