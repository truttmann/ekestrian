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

class EncheresController extends InitController
{

    public function listAction(){

        parent::initListJs();

        $this->setPageTitle('Liste des Enchères');

        $this->addLinkToBreadcrumb('encheres', 'Liste des enchères');

        $list = $this->getList("encheres_list");

        $list->setTitle('Enchères');

        $items = $this->getServiceLocator()->get('enchereTable');

        $filters = $list->getFilters();
        $order = $list->getOrder();

        $list->addLinks(
            array(
                array(
                    'label' => 'Nouveau',
                    'route' => "encheres/edit"
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
                    'code'          =>  'title',
                    'label'         =>  'Titre',
                    'searchable'    =>  true,
                    'sortable'      =>  true,
                    'type'          =>  'text',
                    'width'         =>  '20',
                    'placeholder'   =>  'Titre',
                ),
                array(
                    'code'          =>  'start_date',
                    'clickable'     =>  true,
                    'route'         => 'encheres/edit',
                    'matching_params'   => array("enchere_id"),
                    'label'         =>  'Début de la vente',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '20',
                    'placeholder'   =>  'Date de début',

                ),
                array(
                    'code'          =>  'end_date',
                    'label'         =>  'Fin de la vente',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '20',
                    'placeholder'   =>  'Date de fin',
                ),
                array(
                    'code'          =>  'status',
                    'label'         =>  'Actif',
                    'type'          =>  'boolean',
                    'width'         =>  '10',
                ),

                array(
                    'code'          =>  'action',
                    'label'         =>  'Actions',
                    'searchable'    =>  false,
                    'type'          =>  'link',
                    'width'         =>  '20',
                    'items'         =>  array(
                        array(
                            'route'     =>  'encheres/edit',
                            'label'     =>  'Editer',
                            'matching_params'    =>  array(
                                'enchere_id'
                            )
                        ),
                        array(
                            'route'     =>  'lots',
                            'label'     =>  'Gérer les lots',
                            'matching_params'    =>  array(
                                'enchere_id'
                            )
                        ),
                        array(
                            'route'     =>  'encheres/delete',
                            'label'     =>  'Supprimer',
                            'matching_params'    =>  array(
                                'enchere_id'
                            )
                        )
                    )
                ),
            )
        );

        return $list->toHtml();
    }
    
    public function editAction(){

        $this->setPageTitle('Edition d\'une enchère');

        $this->initFormJs();

        $this->addLinkToBreadcrumb('encheres', 'Liste des enchères')
            ->addLinkToBreadcrumb('encheres/edit', 'Edition d\'une enchère', array("enchere_id" => $this->params()->fromRoute('enchere_id')));

        $data = array();

        if ($id = $this->params()->fromRoute('enchere_id')) {

            $items = $this->getServiceLocator()->get('enchereTable');
            $data = $items->fetchOne($id)->toArray();

            $mainViewModel = new \Application\Model\ViewForm($data['name'], 'encheres', 'encheres/save', array());
        } else {
            $mainViewModel = new \Application\Model\ViewForm('Enchères', 'encheres', 'encheres/save');
        }
        
        $this->addMainButton('Retour', 'encheres', array(), 'btn blue-madison');
        $this->addMainButton('Sauvegarder', null, null, 'btn red-intense save');

        $form = $this->getServiceLocator()->get('FormElementManager')->get("encheres_edit");
        $form->setServiceLocator($this->getServiceLocator());
        $form->initForm();
        
        $data['start_date'] = ((isset($data['start_date']))?str_replace(" ", "T", $data['start_date']):"");
        $data['end_date'] = ((isset($data['end_date']))?str_replace(" ", "T", $data['end_date']):"");
        
        $t = $this->getServiceLocator()->get('user_service')->getInfoForm();
        if(!empty($t)) {
            $t[0]['start_date'] = ((isset($t[0]['start_date']))?str_replace("+02:00", "", $t[0]['start_date']):"");
            $t[0]['end_date'] = ((isset($t[0]['end_date']))?str_replace("+02:00", "", $t[0]['end_date']):"");
            $form->setData($t[0]);
            $form->setMessages($t[1]);
            $this->getServiceLocator()->get('user_service')->setInfoForm(null);
        } else {
            $form->setData($data);
        }

        $FormViewModel = new ViewModel();
        $FormViewModel->setTemplate('application/form/encheres');
        $FormViewModel->setVariable('form', $form);
        $FormViewModel->setVariable('enchere_id', $id);
        $FormViewModel->setVariable('enchere', $data);
        

        $FormViewModel->setVariable('form_title', 'Informations de l\'enchère');

        $this->mainView->setVariable('action_route', $this->url()->fromRoute('encheres/save', array("enchere_id"=>$id)));
        $this->mainView->addChild($FormViewModel, 'form');
        $this->mainView->setTemplate('application/page/form_container');


        return $this->mainView;
    }
    
    public function deleteAction(){

        
        if ($id = $this->params()->fromRoute('enchere_id')) {
            try{
                $items = $this->getServiceLocator()->get('enchereTable');
                $c = $items->fetchOne($id);
                $items->delete($c);
                $this->addSuccess('La suppression a été effectuée avec succès');
            }catch(\Exception $e) {
                $this->addError('La sauvegarde a échouée, veuillez vérifier qu\'il n\'y a plus de lots rattachés');
            }
        }
        return $this->redirect()->toRoute('encheres', array());
    }
    
    public function saveAction() {

        //var_dump($this->getRequest()->getPost());die;
        $id = $this->params()->fromRoute('enchere_id');
        
        if($this->getRequest()->getPost()){
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if (empty($data['image_url']) || empty($data['image_url']['tmp_name'])) {
                unset($data['image_url']);
            }
            
            
            $data['start_date'] = ((strlen($data['start_date']) == 19)?substr($data['start_date'], 0, -3):$data['start_date'])."+02:00";
            $data['end_date'] = ((strlen($data['end_date']) == 19)?substr($data['end_date'], 0, -3):$data['end_date'])."+02:00";
            
               
            $obj = new \Application\Model\Enchere();
            
            $form = $this->getServiceLocator()->get('FormElementManager')->get("encheres_edit");
            $form->setServiceLocator($this->getServiceLocator());
            $form->initForm();
            $form->setInputFilter($obj->getInputFilter());
            $form->setData($data);
            
            if ($form->isValid()) {
                try {
                    // sauvegarde de l'heure de mea
                    $enchereModel = $this->getServiceLocator()->get('enchereTable');
                    if(!empty($data['enchere_id'])) {
                        $obj = $enchereModel->fetchOne($data['enchere_id']);
                    }
                    
                    $obj->exchangeArray($data);
                    
                    if (!empty($data['image_url']) && !empty($data['image_url']['tmp_name'])) {
                        move_uploaded_file($data['image_url']['tmp_name'], __DIR__.'/../../../../../public/upload/encheres/'.$data['image_url']['name']);
                        $obj->image_url = '/upload/encheres/'.$data['image_url']['name'];
                    }
                    
                    $obj = $enchereModel->save($obj);
                    $id = $obj->enchere_id;
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
                return $this->redirect()->toRoute('encheres/edit', array(
                    'enchere_id' => $id,
                ));
            }
        }else{
            throw new Exception('Aucune donnée envoyée.');
        }
        return $this->redirect()->toRoute('encheres/edit', array(
            'enchere_id' => $id,
        ));
    }
}
