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

class ChevauxController extends InitController
{

    public function listAction(){

        parent::initListJs();

        $this->setPageTitle('Liste des Chevaux');

        $this->addLinkToBreadcrumb('chevaux', 'Liste des chevaux');

        $list = $this->getList("chevaux_list");

        $list->setTitle('Chevaux');

        $items = $this->getServiceLocator()->get('chevalTable');

        $filters = $list->getFilters();
        $order = $list->getOrder();

        $list->addLinks(
            array(
                array(
                    'label' => 'Nouveau',
                    'route' => "chevaux/edit"
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
                    'code'          =>  'name',
                    'label'         =>  'Nom',
                    'searchable'    =>  true,
                    'sortable'      =>  true,
                    'type'          =>  'text',
                    'width'         =>  '20',
                    'placeholder'         =>  'Nom',
                ),
                array(
                    'code'          =>  'race',
                    'clickable'     =>  true,
                    'route'         => 'chevaux/edit',
                    'matching_params'   => array("cheval_id"),
                    'label'         =>  'Race',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '20',
                    'placeholder'   =>  'Race',

                ),
                array(
                    'code'          =>  'birthday',
                    'label'         =>  'Date de naissance',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '25',
                    'placeholder'   =>  'Date de naissance',
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
                            'route'     =>  'chevaux/edit',
                            'label'     =>  'Editer',
                            'matching_params'    =>  array(
                                'cheval_id'
                            )
                        ),
                        array(
                            'route'     =>  'chevaux/delete',
                            'label'     =>  'Supprimer',
                            'matching_params'    =>  array(
                                'cheval_id'
                            )
                        )
                    )
                ),
            )
        );

        return $list->toHtml();
    }
    
    public function editAction(){

        $this->setPageTitle('Edition d\'un cheval');

        $this->initFormJs();

        $this->addLinkToBreadcrumb('chevaux', 'Liste des chevaux')
            ->addLinkToBreadcrumb('chevaux/edit', 'Edition d\'un cheval', array("cheval_id" => $this->params()->fromRoute('cheval_id')));

        $data = array();

        if ($id = $this->params()->fromRoute('cheval_id')) {

            $items = $this->getServiceLocator()->get('chevalTable');
            $data = $items->fetchOne($id)->toArray();

            $mainViewModel = new \Application\Model\ViewForm($data['name'], 'chevaux', 'chevaux/save', array());
        } else {
            $mainViewModel = new \Application\Model\ViewForm('Chevaux', 'chevaux', 'chevaux/save');
        }
        
        $this->addMainButton('Retour', 'chevaux', array(), 'btn blue-madison');
        $this->addMainButton('Sauvegarder', null, null, 'btn red-intense save');

        $form = $this->getServiceLocator()->get('FormElementManager')->get("chevaux_edit");
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
        $FormViewModel->setTemplate('application/form/chevaux');
        $FormViewModel->setVariable('form', $form);
        $FormViewModel->setVariable('cheval_id', $id);
        $FormViewModel->setVariable('cheval', $data);
        

        $FormViewModel->setVariable('form_title', 'Informations du cheval');

        $this->mainView->setVariable('action_route', $this->url()->fromRoute('chevaux/save', array("cheval_id"=>$id)));
        $this->mainView->addChild($FormViewModel, 'form');
        $this->mainView->setTemplate('application/page/form_container');


        return $this->mainView;
    }
    
    public function deleteAction(){
        if ($id = $this->params()->fromRoute('cheval_id')) {
            try{
                $items = $this->getServiceLocator()->get('chevalTable');
                $c = $items->fetchOne($id);
                $items->delete($c);
                $this->addSuccess('La suppression a été effectuée avec succès');
            }catch(\Exception $e) {
                $this->addError('La sauvegarde a échouée, veuillez vérifier qu\'il n\'y a plus de lots rattachés');
            }
        } 
        return $this->redirect()->toRoute('chevaux', array());
    }
    
    public function saveAction() {

        //var_dump($this->getRequest()->getPost());die;
        $id = $this->params()->fromRoute('cheval_id');
        
        if($this->getRequest()->getPost()){
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if (empty($data['image_url']) || empty($data['image_url']['tmp_name'])) {
                unset($data['image_url']);
            }

               
            $obj = new \Application\Model\Cheval();
            
            $form = $this->getServiceLocator()->get('FormElementManager')->get("chevaux_edit");
            $form->setServiceLocator($this->getServiceLocator());
            $form->initForm();
            $form->setInputFilter($obj->getInputFilter());
            $form->setData($data);
            if ($form->isValid()) {
                try {
                    // sauvegarde de l'heure de mea
                    $chevalModel = $this->getServiceLocator()->get('chevalTable');
                    if(!empty($data['cheval_id'])) {
                        $obj = $chevalModel->fetchOne($data['cheval_id']);
                    }
                    
                    $obj->exchangeArray($data);
                    
                    if (!empty($data['image_url']) && !empty($data['image_url']['tmp_name'])) {
                        move_uploaded_file($data['image_url']['tmp_name'], __DIR__.'/../../../../../public/upload/cheval/'.$data['image_url']['name']);
                        $obj->image_url = '/upload/cheval/'.$data['image_url']['name'];
                    }
                    
                    $obj = $chevalModel->save($obj);
                    $id = $obj->cheval_id;
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
                return $this->redirect()->toRoute('chevaux/edit', array(
                    'cheval_id' => $id,
                ));
            }
        }else{
            throw new Exception('Aucune donnée envoyée.');
        }
        return $this->redirect()->toRoute('chevaux/edit', array(
            'cheval_id' => $id,
        ));
    }
}
