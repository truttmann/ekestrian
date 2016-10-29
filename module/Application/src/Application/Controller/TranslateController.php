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

class TranslateController extends InitController
{

    public function listAction(){

        parent::initListJs();

        $this->setPageTitle('Liste des traductions');

        $this->addLinkToBreadcrumb('translate', 'Liste des traductions');

        $list = $this->getList("translate_list");

        $list->setTitle('Traduction');

        $items = $this->getServiceLocator()->get('translateTable');
        
        $filters = $list->getFilters();
        $order = $list->getOrder();

        /*$list->addLinks(
            array(
                array(
                    'label' => 'Nouveau',
                    'route' => "clients/edit"
                )
            )
        );*/

        $items = $items->fetchAllPaginate($filters['filters'], $order);
        $items->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
        $nbr = (isset($filters['nbr_results_per_page']))?$filters['nbr_results_per_page']:20;
        $items->setItemCountPerPage($nbr);
        $list->getPaginator()->setPaginatorObject($items);

        $list->setItems($items);


        $list->addCols(
            array(
                array(
                    'code'          =>  'code',
                    'label'         =>  'Code',
                    'searchable'    =>  true,
                    'sortable'      =>  true,
                    'type'          =>  'text',
                    'width'         =>  '20',
                    'placeholder'         =>  '#',
                ),
                array(
                    'code'          =>  'value',
                    'clickable'     =>  true,
                    'route'         => 'translate/edit',
                    'matching_params'   => array("translate_id"),
                    'label'         =>  'Valeur',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '30',
                    'placeholder'   =>  'Email',

                ),
                array(
                    'code'          =>  'libelle',
                    'label'         =>  'Langue',
                    'searchable'    =>  false,
                    'type'          =>  'text',
                    'width'         =>  '20',
                    'placeholder'   =>  'Nom',
                ),
                array(
                    'code'          =>  'action',
                    'label'         =>  'Actions',
                    'searchable'    =>  false,
                    'type'          =>  'link',
                    'width'         =>  '15',
                    'items'         =>  array(
                        array(
                            'route'     =>  'translate/edit',
                            'label'     =>  'Editer',
                            'matching_params'    =>  array(
                                'translate_id'
                            )
                        ),
                        array(
                            'route'     =>  'translate/delete',
                            'label'     =>  'Supprimer',
                            'matching_params'    =>  array(
                                'translate_id'
                            )
                        )
                    )
                ),
            )
        );

        return $list->toHtml();
    }
    
    public function editAction(){

        $this->setPageTitle('Edition d\'une traduction');

        $this->initFormJs();

        $this->addLinkToBreadcrumb('translate', 'Liste des traduction')
            ->addLinkToBreadcrumb('translate/edit', 'Edition d\'une tranduction', array("translate_id" => $this->params()->fromRoute('translate_id')));

        $data = array();

        if ($id = $this->params()->fromRoute('translate_id')) {
            $items = $this->getServiceLocator()->get('translateTable');
            $data = $items->fetchOneById($id)->toArray();
            $mainViewModel = new \Application\Model\ViewForm($data['code'], 'translate', 'translate/save', array());
        } else {
            $mainViewModel = new \Application\Model\ViewForm('Traduction', 'translate', 'translate/save');
        }
        
        $this->addMainButton('Retour', 'translate', array(), 'btn blue-madison');
        $this->addMainButton('Sauvegarder', null, null, 'btn red-intense save');

        $form = $this->getServiceLocator()->get('FormElementManager')->get("translate_edit");
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
        $FormViewModel->setTemplate('application/form/translate');
        $FormViewModel->setVariable('form', $form);
        $FormViewModel->setVariable('translate_id', $id);
        

        $FormViewModel->setVariable('form_title', 'Informations du therme de traduit');

        $this->mainView->setVariable('action_route', $this->url()->fromRoute('translate/save', array("translate_id"=>$id)));
        $this->mainView->addChild($FormViewModel, 'form');
        $this->mainView->setTemplate('application/page/form_container');


        return $this->mainView;
    }
    
    public function deleteAction(){
        if ($id = $this->params()->fromRoute('translate_id')) {
            $items = $this->getServiceLocator()->get('translateTable');
            $c = $items->fetchOneById($id);
            $items->delete($c);
            $this->addSuccess('La suppression a été effectuée avec succès');
        } 
        return $this->redirect()->toRoute('translate', array());
    }
    
    public function saveAction() {
        $id = $this->params()->fromRoute('translate_id');
        if($data = $this->getRequest()->getPost()){
            $obj = new \Application\Model\Translate();
            
            $form = $this->getServiceLocator()->get('FormElementManager')->get("translate_edit");
            $form->setServiceLocator($this->getServiceLocator());
            $form->initForm();
            $form->setInputFilter($obj->getInputFilter());
            $form->setData($data);
            if ($form->isValid()) {
                try {
                    // sauvegarde de l'heure de mea
                    $clientModel = $this->getServiceLocator()->get('translateTable');
                    if(!empty($data['translate_id'])) {
                        $obj = $clientModel->fetchOneById($data['translate_id']);
                    }
                    $e = $data->toArray();
                    $obj->value = $e["value"];
                    
                    $obj = $clientModel->save($obj);
                    $id = $obj->translate_id;
                    $this->addSuccess('La sauvegarde a été effectuée avec succès');
                } catch (Exception $e) {
                    echo 'Exception reçue : ',  $e->getMessage(), "\n";
                }
            }else{
                $this->addError('La sauvegarde a échouée');
                if($messages = $form->getMessages()){
                    foreach($messages as $message){
                        $this->addError($message);
                    }
                }
                $this->getServiceLocator()->get('user_service')->setInfoForm(array($data, $form->getMessages()));
                return $this->redirect()->toRoute('translate/edit', array(
                    'translate_id' => $id,
                ));
            }
        }else{
            throw new Exception('Aucune donnée envoyée.');
        }
        return $this->redirect()->toRoute('translate/edit', array(
            'translate_id' => $id,
        ));
    }
}
