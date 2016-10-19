<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;


use Zend\View\Model\ViewModel;
use Application\Controller\InitController;

class VendeurController extends InitController
{

    public function listAction(){

        parent::initListJs();

        $this->setPageTitle('Liste des Vendeurs');

        $this->addLinkToBreadcrumb('sellers', 'Liste des vendeurs');

        $list = $this->getList("sellers_list");

        $list->setTitle('Vendeurs');

        $items = $this->getServiceLocator()->get('vendeurTable');

        $filters = $list->getFilters();
        $order = $list->getOrder();

        $list->addLinks(
            array(
                array(
                    'label' => 'Nouveau',
                    'route' => "sellers/edit"
                )
            )
        );

        $items = $items->fetchAllPaginate($filters['filters'], $order);
        $items->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
        $nbr = (isset($filters['nbr_results_per_page']))?$filters['nbr_results_per_page']:20;
        $items->setItemCountPerPage($nbr);
        $list->getPaginator()->setPaginatorObject($items);

        $list->setItems($items);

        $list->addCols(
            array(
                array(
                    'code'          =>  'lastname',
                    'label'         =>  'Prénom',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '25',
                    'placeholder'   =>  'Prénom',
                ),
                array(
                    'code'          =>  'firstname',
                    'label'         =>  'Nom',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '25',
                    'placeholder'   =>  'Nom',
                ),
                array(
                    'code'          =>  'email',
                    'clickable'     =>  true,
                    'route'         => 'sellers/edit',
                    'matching_params'   => array("vendeur_id"),
                    'label'         =>  'Email',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '25',
                    'placeholder'   =>  'Email',
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
                    'width'         =>  '20',
                    'items'         =>  array(
                        array(
                            'route'     =>  'sellers/edit',
                            'label'     =>  'Editer',
                            'matching_params'    =>  array(
                                'vendeur_id'
                            )
                        ),
                        array(
                            'route'     =>  'sellers/delete',
                            'label'     =>  'Supprimer',
                            'matching_params'    =>  array(
                                'vendeur_id'
                            )
                        )
                    )
                ),
            )
        );

        return $list->toHtml();
    }
    
    public function editAction(){

        $this->setPageTitle('Edition d\'un vendeur');

        $this->initFormJs();

        $this->addLinkToBreadcrumb('sellers', 'Liste des vendeurs')
            ->addLinkToBreadcrumb('sellers/edit', 'Edition d\'un vendeur', array("vendeur_id" => $this->params()->fromRoute('vendeur_id')));

        $data = array();

        if ($id = $this->params()->fromRoute('vendeur_id')) {
            $items = $this->getServiceLocator()->get('vendeurTable');
            $data = $items->fetchOne($id)->toArray();
            $mainViewModel = new \Application\Model\ViewForm($data['lastname'], 'sellers', 'sellers/save', array());
        } else {
            $mainViewModel = new \Application\Model\ViewForm('Vendeur', 'sellers', 'sellers/save');
        }
        
        $this->addMainButton('Retour', 'sellers', array(), 'btn blue-madison');
        $this->addMainButton('Sauvegarder', null, null, 'btn red-intense save');

        $form = $form = $this->getServiceLocator()->get('FormElementManager')->get("vendeur_edit");
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
        $FormViewModel->setTemplate('application/form/vendeur');
        $FormViewModel->setVariable('form', $form);
        $FormViewModel->setVariable('vendeur_id', $id);
        

        $FormViewModel->setVariable('form_title', 'Informations du vendeur');

        $this->mainView->setVariable('action_route', $this->url()->fromRoute('sellers/save', array("vendeur_id"=>$id)));
        $this->mainView->addChild($FormViewModel, 'form');
        $this->mainView->setTemplate('application/page/form_container');


        return $this->mainView;
    }
    
    public function deleteAction(){
        if ($id = $this->params()->fromRoute('vendeur_id')) {
            try{
                $items = $this->getServiceLocator()->get('vendeurTable');
                $c = $items->fetchOne($id);
                $items->delete($c);
                $this->addSuccess('La suppression a été effectuée avec succès');
            }catch(\Exception $e) {
                $this->addError('La sauvegarde a échouée, veuillez vérifier qu\'il n\'y a plus de lots rattachés');
            }
        } 
        
        return $this->redirect()->toRoute('sellers', array());
    }
    
    public function saveAction() {
        $id = $this->params()->fromRoute('vendeur_id');
        if($data = $this->getRequest()->getPost()){
            $obj = new \Application\Model\Vendeur();
            
            $form = $this->getServiceLocator()->get('FormElementManager')->get("vendeur_edit");
            $form->setServiceLocator($this->getServiceLocator());
            $form->initForm();
            $form->setInputFilter($obj->getInputFilter());
            $form->setData($data);
            if ($form->isValid()) {
                try {
                    // sauvegarde de l'heure de mea
                    $vendeurModel = $this->getServiceLocator()->get('vendeurTable');
                    if(!empty($data['vendeur_id'])) {
                        $obj = $vendeurModel->fetchOne($data['vendeur_id']);
                    }
                    $obj->exchangeArray($data->toArray());
                    $obj = $vendeurModel->save($obj);
                    $id = $obj->vendeur_id;
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
                return $this->redirect()->toRoute('sellers/edit', array(
                    'vendeur_id' => $id,
                ));
            }
        }else{
            throw new Exception('Aucune donnée envoyée.');
        }
        return $this->redirect()->toRoute('sellers/edit', array(
            'vendeur_id' => $id,
        ));
    }
}
