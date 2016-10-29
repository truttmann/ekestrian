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

class LotController extends InitController
{

    public function listAction(){

        parent::initListJs();
        $enchere_id = $this->params()->fromRoute('enchere_id');
        
        $this->setPageTitle('Liste des lots');

        $this->addLinkToBreadcrumb('encheres', 'Liste des enchères')
            ->addLinkToBreadcrumb('lots', 'Liste des lots', array("enchere_id" => $enchere_id));

        $list = $this->getList("lots_list");

        $list->setTitle('Lots');

        $items = $this->getServiceLocator()->get('lotTable');
        
        $filters = $list->getFilters();
        $order = $list->getOrder();
		
		$list->addLinks(
            array(
                array(
                    'label' => 'Nouveau',
                    'route' => "lots/edit",
                    'params'    =>  array(
                        'enchere_id' => $enchere_id
                    )
                )
            )
        );

        $items = $items->fetchAllPaginate($filters['filters'], $order, $enchere_id);
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
                    'width'         =>  '50',
                    'placeholder'   =>  'Titre',
                ),
                array(
                    'code'          =>  'min_price',
                    'clickable'     =>  true,
                    'route'         => 'lots/edit',
                    'matching_params'   => array("enchere_id", "lot_id"),
                    'label'         =>  'Prix dép.',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '15',
                    'placeholder'   =>  'Prix de départ',

                ),
                array(
                    'code'          =>  'number',
                    'sortable'      =>  true,
                    'label'         =>  'Pos.',
                    'type'          =>  'text',
                    'width'         =>  '10',
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
                    'width'         =>  '15',
                    'items'         =>  array(
                        array(
                            'route'     =>  'lots/edit',
                            'label'     =>  'Editer',
                            'matching_params'    =>  array(
                                'lot_id',
                                'enchere_id'
                            )
                        ),
                        array(
                            'route'     =>  'lots/encheres',
                            'label'     =>  'Voir les enchères',
                            'matching_params'    =>  array(
                                'lot_id',
                                'enchere_id'
                            )
                        ),
                        array(
                            'route'     =>  'lots/delete',
                            'label'     =>  'Supprimer',
                            'matching_params'    =>  array(
                                'lot_id',
                                'enchere_id'
                            )
                        )
                    )
                ),
            )
        );

        return $list->toHtml();
    }
    
    public function enchereAction(){

        parent::initListJs();
        $lot_id = $this->params()->fromRoute('lot_id');
        $enchere_id = $this->params()->fromRoute('enchere_id');
        
        $this->setPageTitle('Liste des enchères');

        $this->addLinkToBreadcrumb('encheres', 'Liste des enchères')
            ->addLinkToBreadcrumb('lots', 'Liste des lots', array("enchere_id" => $enchere_id));

        $list = $this->getList("lots_enchere_list");

        $list->setTitle('Enchères');

        $fac = $this->getServiceLocator()->get('clientAuctionTable');

        $filters = $list->getFilters();
        $order = $list->getOrder();

        $items = $fac->fetchAllPaginate($filters['filters'], $order, array("client_auction.lot_id" => $lot_id));
        $items->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
        $nbr = (isset($filters['nbr_results_per_page']))?$filters['nbr_results_per_page']:20;
        $items->setItemCountPerPage($nbr);
        $list->getPaginator()->setPaginatorObject($items);

        $list->setItems($items);


        $list->addCols(
            array(
                array(
                    'code'          =>  'lot_name',
                    'label'         =>  'Titre',
                    'searchable'    =>  true,
                    'sortable'      =>  true,
                    'type'          =>  'text',
                    'width'         =>  '2',
                    'placeholder'   =>  'Titre',
                ),
                array(
                    'code'          =>  'creation_date',
                    'label'         =>  'Date de création',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '2',
                    'placeholder'   =>  'Date de création',

                ),
                array(
                    'code'          =>  'value',
                    'label'         =>  'Prix',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '1',
                    'placeholder'   =>  'Prix',

                ),
                array(
                    'code'          =>  'status',
                    'label'         =>  'Statut',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '1',
                    'placeholder'   =>  'statut',

                ),
                array(
                    'code'          =>  'firstname',
                    'label'         =>  'Nom',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '1',
                    'placeholder'   =>  'nom',

                ),                
                array(
                    'code'          =>  'firstname',
                    'label'         =>  'Prénom',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '1',
                    'placeholder'   =>  'prenom',
                ),
                array(
                    'code'          =>  'email',
                    'label'         =>  'Email',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '1',
                    'placeholder'   =>  'email',

                ),                
                array(
                    'code'          =>  'phone',
                    'label'         =>  'Tél.',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '1',
                    'placeholder'   =>  '#',
                ),
                array(
                    'code'          =>  'libelle',
                    'label'         =>  'Pays',
                    'searchable'    =>  true,
                    'type'          =>  'text',
                    'width'         =>  '1',
                    'placeholder'   =>  'pays',
                ),
                array(
                    'code'          =>  'action',
                    'label'         =>  'Actions',
                    'searchable'    =>  false,
                    'type'          =>  'link',
                    'width'         =>  '1',
                    'items'         =>  array(
                    )
                ),
            )
        );

        return $list->toHtml();
    }
    
    public function editAction(){

        $this->setPageTitle('Edition d\'un lot');

        $this->initFormJs();

        $this->addLinkToBreadcrumb('encheres', 'Liste des enchères')
            ->addLinkToBreadcrumb('lots', 'Liste des lots', array("enchere_id" => $this->params()->fromRoute('enchere_id')))
            ->addLinkToBreadcrumb('lots/edit', 'Edition d\'un lot', array("enchere_id" => $this->params()->fromRoute('enchere_id'), "lot_id" => $this->params()->fromRoute('lot_id')));

        $data = array();

        if ($id = $this->params()->fromRoute('lot_id')) {
            $items = $this->getServiceLocator()->get('lotTable');
            $data = $items->fetchOne($id)->toArray();
            $mainViewModel = new \Application\Model\ViewForm($data['name'], 'lots', 'lots/save', array("enchere_id" => $this->params()->fromRoute('enchere_id')));
        } else {
            $mainViewModel = new \Application\Model\ViewForm('Lots', 'lots', 'lots/save', array("enchere_id" => $this->params()->fromRoute('enchere_id')));
        }
        
        $this->addMainButton('Retour', 'lots', array(), 'btn blue-madison', array("enchere_id" => $this->params()->fromRoute('enchere_id')));
        $this->addMainButton('Sauvegarder', null, null, 'btn red-intense save');

        $form = $this->getServiceLocator()->get('FormElementManager')->get("lots_edit");
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
        $FormViewModel->setTemplate('application/form/lots');
        $FormViewModel->setVariable('form', $form);
        $FormViewModel->setVariable('lots_id', $id);
        $FormViewModel->setVariable('lot', $data);
        $FormViewModel->setVariable('form_title', 'Informations de le lot');

        $this->mainView->setVariable('action_route', $this->url()->fromRoute('lots/save', array("enchere_id"=>$this->params()->fromRoute('enchere_id'), "lot_id" => $this->params()->fromRoute('lot_id'))));
        $this->mainView->addChild($FormViewModel, 'form');
        $this->mainView->setTemplate('application/page/form_container');
        
        
        /* ajout du téléchagement d'image uniquement en creation */
        if(!empty($data)) {
            $viewMediaManagerList = new ViewModel();
            $viewMediaManagerList->setTemplate('application/media_manager/info');
            $this->mainView->addChild($viewMediaManagerList, null, true);
            
            $viewMediaManagerList = new ViewModel();
            $viewMediaManagerList->setTemplate('application/media_manager/form');
            $this->mainView->addChild($viewMediaManagerList, null, true);

            $engineMediaManager = $this->getEngineMediaManager();
            $engineMediaManager->setVariable('lot', $data);
            $engineMediaManager->setVariable('listRoute', 'lots/media_list');
            $engineMediaManager->setVariable('listRouteParams', array("enchere_id" => $this->params()->fromRoute('enchere_id')));
            $engineMediaManager->setVariable('uploadRoute', 'lots/media_upload');
            $engineMediaManager->setVariable('uploadRouteParams', array("enchere_id" => $this->params()->fromRoute('enchere_id')));
            $this->mainView->addChild($engineMediaManager, null, true);
        }

        return $this->mainView;
    }
    
    public function deleteAction(){
        $id_enchere = $this->params()->fromRoute('enchere_id');
        $id = $this->params()->fromRoute('lot_id');
        
        if ($id && $id_enchere) {
            try{
                $items = $this->getServiceLocator()->get('lotTable');
                $c = $items->fetchOne($id);
                $items->delete($c);
                $this->addSuccess('La suppression a été effectuée avec succès');
            }catch(\Exception $e) {
                $this->addError('La sauvegarde a échouée, veuillez vérifier qu\'il n\'y a plus de données rattachés(paiement...)');
            }  
        } 
        return $this->redirect()->toRoute('lots', array(
            'enchere_id' => $id_enchere,
            'lot_id' => $id,
        ));
    }
    
    public function saveAction() {
        $id_enchere = $this->params()->fromRoute('enchere_id');
        $id = $this->params()->fromRoute('lot_id');
        
        if($this->getRequest()->getPost()){
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if (empty($data['image_url']) || empty($data['image_url']['tmp_name'])) {
                unset($data['image_url']);
            }
            
            $data['min_price'] = str_replace(".", ",", $data['min_price']);
            $data['estimated_price'] = str_replace(".", ",", $data['estimated_price']);
            $data['reserve_price'] = str_replace(".", ",", $data['reserve_price']);
            $data['ist_price'] = str_replace(".", ",", $data['ist_price']);
            $data['pre_auth_price'] = str_replace(".", ",", $data['pre_auth_price']);
            
            $obj = new \Application\Model\Lot();
            
            $form = $this->getServiceLocator()->get('FormElementManager')->get("lots_edit");
            $form->setServiceLocator($this->getServiceLocator());
            $form->initForm();
            $form->setInputFilter($obj->getInputFilter());
            $form->setData($data);
            if ($form->isValid()) {
                try {
                    // sauvegarde de l'heure de mea
                    $lotModel = $this->getServiceLocator()->get('lotTable');
                    if(!empty($data['lot_id'])) {
                        $obj = $lotModel->fetchOne($data['lot_id']);
                    }
                    
                    $obj->exchangeArray($data);
                    
                    if (!empty($data['image_url']) && !empty($data['image_url']['tmp_name'])) {
                        move_uploaded_file($data['image_url']['tmp_name'], __DIR__.'/../../../../../public/upload/lot/'.$data['image_url']['name']);
                        $obj->image_url = '/upload/lot/'.$data['image_url']['name'];
                    }
                    
                    $obj->enchere_id = (int)$id_enchere;
                    if($obj->vendeur_id == 0) {
                        $obj->vendeur_id = null;
                    }
                    if($obj->cheval_id == 0) {
                        $obj->cheval_id = null;
                    }
                    
                    $obj = $lotModel->save($obj);
                    $id = $obj->lot_id;
                    $this->addSuccess('La sauvegarde a été effectuée avec succès');
                } catch (Exception $e) {
                    echo 'Exception reçue : ',  $e->getMessage(), "\n";exit;
                    throw $e;
                }
            }else{
                $this->addError('La sauvegarde a échouée');
                if($messages = $form->getMessages()){
                    foreach($messages as $message){
                        $this->addError($message);
                    }
                }
                $this->getServiceLocator()->get('user_service')->setInfoForm(array($data, $form->getMessages()));
                return $this->redirect()->toRoute('lots/edit', array(
                    'enchere_id' => $id_enchere,
                    'lot_id' => $id,
                ));
            }
        }else{
            throw new Exception('Aucune donnée envoyée.');
        }
        return $this->redirect()->toRoute('lots/edit', array(
            'enchere_id' => $id_enchere,
            'lot_id' => $id,
        ));
    }
    
    public function mediaListAction(){
        $id_enchere = $this->params()->fromRoute('enchere_id');
        $id_lot = $this->params()->fromRoute('lot_id');
        $return = array();
        
        if($id_lot != 0){
            $res = $this->getServiceLocator()->get('imageTable')->fetchAll(array("lot_id" => $id_lot));
            foreach ($res as $l) {
                $downloadedFiles[] = array(
                    'id' => $l->image_id,
                    'name' => pathinfo($l->filename, PATHINFO_BASENAME),
                );
            }
            
            $scriptUrl = 'lots/media_upload';
            $updateUrl = 'lots/media_update';
            $deleteUrl = 'lots/media_delete';
            $uploadDir = __DIR__.'/../../../../../public/upload/lots/';
            $uploadUrl = $this->getBasePath().'/upload/lots/';
            $minWidth = 1;
            $minHeight = 1;

            $options = array(
                'script_url' => $this->url()->fromRoute($scriptUrl, array("enchere_id" => $id_enchere, "lot_id" => $id_lot)),
                'upload_dir' => $uploadDir,
                'upload_url' => $uploadUrl,
                'update_url' => $this->url()->fromRoute($updateUrl, array("enchere_id" => $id_enchere, "lot_id" => $id_lot)),
                'delete_url' => $this->url()->fromRoute($deleteUrl, array("enchere_id" => $id_enchere, "lot_id" => $id_lot)),
                'min_width' => $minWidth,
                'min_height' => $minHeight
            );

            $uploadHandler = new \Application\Helper\UploadHandler($options, false);
            $return = $uploadHandler->get(false, $downloadedFiles);
        }
        
        return new \Zend\View\Model\JsonModel($return);
    }
    
    public function mediaUploadAction(){
        $id_enchere = $this->params()->fromRoute('enchere_id');
        $id_lot = $this->params()->fromRoute('lot_id');
        
        $scriptUrl = 'lots/media_upload';
        $updateUrl = 'lots/media_update';
        $deleteUrl = 'lots/media_delete';
        $uploadDir = __DIR__.'/../../../../../public/upload/lots/';
        $uploadUrl = $this->getBasePath().'/upload/lots/';
        $minWidth = 1;
        $minHeight = 1;
        
        $options = array(
            'script_url' => $this->url()->fromRoute($scriptUrl, array("enchere_id" => $id_enchere, "lot_id" => $id_lot)),
            'upload_dir' => $uploadDir,
            'upload_url' => $uploadUrl,
            'update_url' => $this->url()->fromRoute($updateUrl, array("enchere_id" => $id_enchere, "lot_id" => $id_lot)),
            'delete_url' => $this->url()->fromRoute($deleteUrl, array("enchere_id" => $id_enchere, "lot_id" => $id_lot)),
            'min_width' => $minWidth,
            'min_height' => $minHeight
        );
        $uploadHandler = new \Application\Helper\UploadHandler($options, false);

        $data = $uploadHandler->post(false);
        if (isset($data['files'][0])) {
            $this->uploadedFile = $data['files'][0];
        }

        $img = new \Application\Model\Image();
        $img->image_id = null;
        $img->lot_id = $id_lot;
        $img->filename = $this->uploadedFile->url;
        
        $this->getServiceLocator()->get('imageTable')->save($img);
        
        return new \Zend\View\Model\JsonModel($data);
    }
    
    public function mediaUpdateAction()
    {

    }

    public function mediaDeleteAction()
    {
        $id_enchere = $this->params()->fromRoute('enchere_id');
        $id_lot = $this->params()->fromRoute('lot_id');
        
        $scriptUrl = 'lots/media_upload';
        $updateUrl = 'lots/media_update';
        $deleteUrl = 'lots/media_delete';
        $uploadDir = __DIR__.'/../../../../../public/upload/lots/';
        $uploadUrl = $this->getBasePath().'/upload/lots/';
        $minWidth = 1;
        $minHeight = 1;
        
        $options = array(
            'script_url' => $this->url()->fromRoute($scriptUrl, array("enchere_id" => $id_enchere, "lot_id" => $id_lot)),
            'upload_dir' => $uploadDir,
            'upload_url' => $uploadUrl,
            'update_url' => $this->url()->fromRoute($updateUrl, array("enchere_id" => $id_enchere, "lot_id" => $id_lot)),
            'delete_url' => $this->url()->fromRoute($deleteUrl, array("enchere_id" => $id_enchere, "lot_id" => $id_lot)),
            'min_width' => $minWidth,
            'min_height' => $minHeight
        );
        
        $id = $this->getRequest()->getQuery()->id;
        $i = $this->getServiceLocator()->get('imageTable')->fetchOne($id);
        $_REQUEST['file'] = pathinfo($i->filename, PATHINFO_BASENAME);
        
        $uploadHandler = new \Application\Helper\UploadHandler($options, false);
        $data = $uploadHandler->delete(false);
        
        $this->getServiceLocator()->get('imageTable')->delete($i);

        return new \Zend\View\Model\JsonModel($data);
    }
}
