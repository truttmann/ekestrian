<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Model\CustomList;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Zend\Validator\File\Size;

class InitController extends AbstractActionController
{

    protected $_view_model  = null;
    protected $_list        = null;
    protected $_breadcrumb  = null;
    protected $_page_title  = null;
    protected $_messages      = array();
    protected $_history = null;
    const MIN_FILE_SIZE = 200;



    protected $pageTitle;
    protected $viewBreadcrumb;
    protected $mainButtons   = array();

    /**
     * Vue principale après le layout.
     * @var ViewModel
     */
    protected $mainView;

    const MESSAGE_SUCCESS_TYPE = 'SUCCESS_MESSAGE';
    const MESSAGE_ERROR_TYPE = 'ERROR_MESSAGE';
    const MESSAGE_WARNING_TYPE = 'WARNING_MESSAGE';


/**
     * check si l'utilisateur est connecté
     * check si l'utilisateur a les droits
     */
    public function __construct(){


        $this->viewBreadcrumb = new ViewModel();
        $this->viewBreadcrumb->setTemplate('application/breadcrumb/items');
        $this->viewBreadcrumb->setVariable('items', array(array(
            'route' =>  'home_back',
            'label' =>  'Accueil'
        )));
        $this->mainView = new ViewModel();
    }

    protected function deleteItem($module, $entity, $route_return = 'home_back'){
        if ($id = $this->params()->fromRoute('id')){

            $model = $this->getServiceLocator()->get('Application\\'.$module.'\\Model\\'.$entity.'Table');
            $item = $model->fetchOne($id);

            if($item){
                $model->delete($item);
            }
        }
        return $this->redirect()->toRoute($route_return);
    }

    protected function getEnv(){
        if(preg_match('/^((m\.)?localhost|127\.0\.0\.1|192\.168\.12?\.[0-9]+$/', $_SERVER['HTTP_HOST'])){
            return 'dev';
        }else{
            return 'prod';
        }
    }

    protected function fileUpload($resource, $dest, $_min_file_size = self::MIN_FILE_SIZE){

        $error = array();

        $size = new Size(array('min'=>$_min_file_size));
        $adapter = new \Zend\File\Transfer\Adapter\Http();

        $adapter->setValidators(array($size), $resource['name']);

        if (!$adapter->isValid()){
            $dataError = $adapter->getMessages();

            foreach($dataError as $key=>$row)
            {
                $error[] = $row;
            }

        } else {
            $adapter->setDestination($dest);
            $adapter->receive($resource['name']);
        }

        return $error;
    }

    protected function initCoreJs(){

        $this
            ->getServiceLocator()
            ->get('viewhelpermanager')
            ->get('HeadScript')
            ->appendFile('/plugins/jquery-1.10.2.min.js')
            ->appendFile('/plugins/jquery-migrate-1.2.1.min.js')
            ->appendFile('/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js')
            ->appendFile('/plugins/bootstrap/js/bootstrap.min.js')
            ->appendFile('/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js')
            ->appendFile('/plugins/jquery-slimscroll/jquery.slimscroll.min.js')
            ->appendFile('/plugins/jquery.blockui.min.js')
            ->appendFile('/plugins/jquery.cokie.min.js')
            ->appendFile('/plugins/uniform/jquery.uniform.min.js')
            ->appendFile('/js/ekestrian/global.js')
            ->appendFile('/js/ckeditor/ckeditor.js')


            ->appendFile('/plugins/bootstrap-modal/js/bootstrap-modalmanager.js')
            ->appendFile('/plugins/bootstrap-modal/js/bootstrap-modal.js')

            ->appendFile('/scripts/custom/ui-extended-modals.js');
    }

    protected function initListJs(){

        $this
            ->getServiceLocator()
            ->get('viewhelpermanager')
            ->get('HeadScript')
            ->appendFile('/plugins/select2/select2.min.js')
            ->appendFile('/plugins/data-tables/jquery.dataTables.js')
            ->appendFile('/plugins/data-tables/DT_bootstrap.js')
            ->appendFile('/plugins/bootstrap/js/bootstrap.min.js')
            ->appendFile('/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')
            ->appendFile('/scripts/core/app.js')
            ->appendFile('/scripts/core/datatable.js')
            ->appendFile('/scripts/custom/ecommerce-products.js')
            ->appendFile('/plugins/bootbox/bootbox.min.js')
            ->appendFile('/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js')
            ->appendFile('/js/ekestrian/list.js');
    }



    protected function initFormJs(){


        $this
            ->getServiceLocator()
            ->get('viewhelpermanager')
            ->get('HeadScript')

            ->appendFile('/plugins/select2/select2.min.js')
            ->appendFile('/plugins/data-tables/jquery.dataTables.js')

            ->appendFile('/plugins/data-tables/DT_bootstrap.js')

            ->appendFile('/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')
            ->appendFile('/plugins/bootstrap-switch/js/bootstrap-switch.min.js')
            // casse le menu de gauche
            //->appendFile('/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')

            ->appendFile('/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js')
            ->appendFile('/plugins/bootstrap-touchspin/bootstrap.touchspin.js')
            ->appendFile('/plugins/bootstrap-select/bootstrap-select.js')
            ->appendFile('/plugins/bootbox/bootbox.min.js')
            ->appendFile('/plugins/fancybox/source/jquery.fancybox.pack.js')
            ->appendFile('/plugins/plupload/js/plupload.full.min.js')
            ->appendFile('/plugins/jquery-validation/dist/jquery.validate.min.js')
            ->appendFile('/plugins/jquery-validation/localization/messages_fr.js')
            ->appendFile('/plugins/jquery-validation/dist/additional-methods.min.js')
            ->appendFile('/plugins/bootstrap-wysihtml5/wysihtml5-0.3.0.js')
            ->appendFile('/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.js')
            ->appendFile('/plugins/ckeditor/ckeditor.js')
            ->appendFile('/plugins/bootstrap-markdown/js/bootstrap-markdown.js')
            ->appendFile('/plugins/bootstrap-markdown/lib/markdown.js')

            ->appendFile('/scripts/core/app.js')
            ->appendFile('/scripts/core/datatable.js')
            ->appendFile('/scripts/custom/ecommerce-products-edit.js')

            ->appendFile('/js/ekestrian/scripts/custom/form-validation.js')

            ->appendFile('/js/ekestrian/autocompletion.js')
            ->appendFile('/js/ekestrian/form.js')
            ->appendFile('/plugins/bootstrap-toastr/toastr.min.js');
    }

    protected function getBaseUrl(){
        return $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost();
    }

    /**
     * principal view model objet
     */
    public function getViewModel(){
        if ($this->_view_model === null)
            $this->_view_model = new ViewModel();

        return $this->_view_model;
    }

    /**
     * principal view model objet
     * @param string $namespace [optional]
     * @return CustomList
     */
    public function getList($namespace = 'default'){
        if (!isset($this->_list[$namespace])){
            $request = $this->getRequest();
            $this->_list[$namespace] = new CustomList($namespace, $request);
        }
        return $this->_list[$namespace];
    }

    public function getLanguages()
    {
        $languages = array();
        $languageTable = $this->getServiceLocator()->get('Application\Language\Model\LanguageTable');
        foreach ($languageTable->fetchAll(null, 'ordering') as $language) {
            $languages[] = $language;
        }
        return $languages;
    }


    /**
     * Ajoute un nouveau lien au Breadcrumb déclaré dans le constructeur.
     * @param $route string
     * @param $label string
     * @return $this InitController
     */
    public function addLinkToBreadcrumb($route, $label, $params = array())
    {
        $items = $this->viewBreadcrumb->getVariable('items');
        $newItem = array(
            'route' => $route,
            'label' => $label,
            'params' => $params,
        );
        if ($items !== null) {
            $items[] = $newItem;
        } else {
            $items = array($newItem);
        }
        if (!isset($items['params'])) {
            $items[0]['params'] = array();
        }
        $this->viewBreadcrumb->setVariable('items', $items);
        return $this;
    }

    /**
     * Ajout un bouton en haut à droite de la page.
     * @param string $label Libellé du bouton
     * @param string $url Route ou url. Si c'est une url, elle doit commencé par 'http://' afin de bien
     *                    la discerner d'une route.
     * @param array $items Tableau pour une liste déroulante.
     * @param null $class Class CSS
     * @return $this
     */
    public function addMainButton($label, $url, $items = array(), $class = null, $params = array(), $attributes = "")
    {
        if ($class === null) {
            $class = "btn default";
        }
        $this->mainButtons[] = array(
            'label' => $label,
            'url' => $url,
            'items' => $items,
            'class' => $class,
            'params' => $params,
            'attributes' => $attributes,
        );
        return $this;
    }

    /**
     * Attache les évènements
     * @see \Zend\Mvc\Controller\AbstractController::attachDefaultListeners()
     */
    protected function attachDefaultListeners(){
        parent::attachDefaultListeners();

        $events = $this->getEventManager();
        $events->attach('dispatch', array($this, 'preDispatch'), 100);
        $events->attach('dispatch', array($this, 'postDispatch'), -100);
    }

    /**
     * Avant l'action
     * @param MvcEvent $e
     */
    public function preDispatch (MvcEvent $e){
        //$item = $this->getBreadcrumb();
        //$this->getViewModel()->setVariables(array('breadcrumb' => $item) );

        $this->initCoreJs();

        // recuperation du helper acl
        /*$aclHelper = new Acl($this->getServiceLocator());
        $this->layout()->setOption('aclHelper', $aclHelper);

        // recuperation du helper de vue
        $dataHelper = new Data();
        $this->layout()->setOption('dataHelper', $dataHelper);*/
    }

    /**
     * Après l'action
     * @param MvcEvent $e
     */
    public function postDispatch (MvcEvent $e){
        $this->layout()->setTemplate("layout/layout_back");
        $this->layout()->setVariable('page_title', $this->pageTitle);

        $this->layout()->setVariable('message_success',self::MESSAGE_SUCCESS_TYPE);
        $this->layout()->setVariable('message_error',self::MESSAGE_ERROR_TYPE);
        $this->layout()->setVariable('message_warning',self::MESSAGE_WARNING_TYPE);

        $this->layout()->setVariable('buttons', $this->mainButtons);

        if ($this->viewBreadcrumb instanceof ViewModel) {
            $this->layout()->addChild($this->viewBreadcrumb, 'breadcrumb');
        }

        $header = new ViewModel();
        $header->setTemplate('application/page/header');
        $this->layout()->addChild($header, 'header');

        $menu = new ViewModel();
        $menu->setTemplate('application/menu/items');
        $route = $this->getCurrentRoute();
        $menu->setVariable('current_route', $route);
        $this->layout()->addChild($menu, 'menu');

        $footer = new ViewModel();
        $footer->setTemplate('application/page/footer');
        $this->layout()->addChild($footer, 'footer');

        

        $em = $e->getApplication()->getEventManager();
        
        if(!empty($this->_messages))
            $this->flashMessenger()->addMessage($this->_messages);
        
        $temp = $this->getServiceLocator()->get('user_service')->getMessages();
        if(!empty($temp)) {
            $this->flashMessenger()->addMessage($temp);
            $this->getServiceLocator()->get('user_service')->setMessages(null);
        }
        
        $em->attach(\Zend\Mvc\MvcEvent::EVENT_RENDER, function($e) {
            $messenger = new \Zend\Mvc\Controller\Plugin\FlashMessenger();
            if ($messenger->hasMessages()) {
                $messages = $messenger->getMessages();
                $e->getViewModel()->setVariable('flashMessages', $messages);
            }
        });

        

    }

    protected function getCurrentRoute(){
        return $this->getEvent()->getRouteMatch()->getMatchedRouteName();
    }

    public function getCurrentAction(){
        return $this->getEvent()->getRouteMatch()->getParam('action', 'index');
    }

    /**
     * set le tiytr de la page
     */
    public function setPageTitle( $title){
        $this->pageTitle = $title;
        return $this;
    }

    /**
     * @param $viewModel
     * @return mixed
     * attend
     */
    public function renderViewModel($viewModel){
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        return $viewRender->render($viewModel);
    }

    public function addError($message){
        return $this->setMessage(self::MESSAGE_ERROR_TYPE, $message);
    }

    public function addSuccess($message){
        return $this->setMessage(self::MESSAGE_SUCCESS_TYPE, $message);
    }

    public function addWarning($message){
        return $this->setMessage(self::MESSAGE_WARNING_TYPE, $message);
    }

    public function setMessage($type, $messages){

        if(!isset($this->_messages[$type]))
            $this->_messages[$type] = array();

        if(!is_array($messages))
            $messages = array($messages);

        foreach($messages as $message){
            $this->_messages[$type][] = $message;
        }
        
        $this->getServiceLocator()->get('user_service')->setMessages($this->_messages);
        return $this;
    }

    public function getBasePath($path = null){
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        return $renderer->basePath($path);
    }

    /**
     * @param $option
     * @return ViewForm
     * $option =>
     * page_title ('Edition d\'un pays')
     * entity ('country')
     * previous_page_title ('Liste des pays')
     * form_title ('Test')
     */
    public function getMultiFormEdit($option){
        $this->initFormJs();
        $this->setPageTitle($option['page_title']);

        $ucFirstEntity = ucfirst($option['entity']);


        $this->addLinkToBreadcrumb($option['entity'] . '_list', $option['previous_page_title'])
             ->addLinkToBreadcrumb($option['entity'] . '_edit', $option['page_title']);

        $history_info = array();

        $languages = $this->getLanguages();

        $regionTable = $this->getServiceLocator()->get('Application\\'. $ucFirstEntity.'\Model\\'.$ucFirstEntity.'Table');

        $regionList = array();
        $regionData = array();
        if ($id = $this->params()->fromRoute('id')){

            // historisation
            $this->saveReadHistory(array('table_name'=> $option['entity'], 'data_id'=>$id));

            $history_info = $this->getFormHistoryInfo(array('table_name' => $option['entity'], 'data_id'=>$id));


            $regionData = $regionTable->fetchOne($id)->toArray();

            /* nous allons rechercher les caractéristiques qui sont liées a cette thématique */
            if(strtolower($ucFirstEntity) == "thematic") {
                $fac = $this->getServiceLocator()->get('Application\\'. $ucFirstEntity.'\Model\\ThematicCaracteristicTable');
                $res = $fac->getByThematic($id);
                $regionData["caracteristic_id"] = [];
                foreach ($res as $itemres) {
                    $regionData["caracteristic_id"][] = $itemres->caracteristic_id;
                }

            }

            $regionList = $regionTable->fetchOneI18n($id);

        }

        $this->addMainButton('Retour', $option['entity'].'_list', null, 'btn blue-madison');
        $this->addMainButton('Sauvegarder', null, null, 'btn red-intense save');

        $this->mainView->setVariable('action_route', $option['entity'].'_save');
        $this->mainView->setVariable('history_info', $history_info);

        $class = '\Application\\' . $ucFirstEntity . '\Form\\' . $ucFirstEntity . 'Form';
        $form = new $class();

        if(isset($option['to_form'])){
            foreach($option['to_form'] as $k => $element){
                $form->add($element);
            }
        }



        $form->setServiceLocator($this->getServiceLocator());
        $form->initForm();
        $form->setData($regionData);

        // formulaire information general
        $GeneralFormViewModel = new ViewModel();
        $GeneralFormViewModel->setTemplate('application/page/form/fields');
        $GeneralFormViewModel->setVariable('form', $form);
        $GeneralFormViewModel->setVariable('form_title', $option['form_title']);

        $languagesTab = new ViewModel();
        $languagesTab->setTemplate('application/page/tab');

        $items = array();
        foreach($languages as $key => $language) {

            $region = array();
            if (isset($regionList[$language->code])) {
                foreach ($regionList[$language->code]->toArray() as $field => $value) {
                    $fieldName = 'languages['.$language->code.']['.$field.']';
                    $region[$fieldName] = $value;
                }
            }


            $formClass = '\Application\\' . $ucFirstEntity . '\Form\\' . $ucFirstEntity . 'LanguageForm';
            $form = new $formClass();
            $form->setServiceLocator($this->getServiceLocator());
            $form->setNamePrefix('languages[' . $language->code . ']');
            $form->initForm();
            $form->setData($region);

            $formFields = new ViewModel();
            $formFields->setTemplate('application/form/multi/fields');
            $formFields->setVariable('form', $form);
            $formFields->setVariables(array(
                'code' => 'country_' . $language->code,
                'class' => (!$key) ? 'in active' : ''
            ));

            $items[] = array(
                'code'     => 'country_' . $language->code,
                'label'     => $language->code,
                'image_path'     => URL_WS_IMG . $language->imagePath,
            );
            $languagesTab->setVariable('items', $items);
            $languagesTab->addChild($formFields, 'tabs', true);
        }

        $this->mainView->setTemplate('application/page/form_container');
        $this->mainView->addChild($GeneralFormViewModel, 'form');
        $this->mainView->addChild($languagesTab, 'form', true);

        return $this->mainView;
    }

    public function saveMultiFormAction($option, $redirect = true){

        $ucFirstEntity = ucfirst($option['entity']);

        $id = null;
        if($data = $this->getRequest()->getPost()){
            if(array_key_exists('group_caracteristics_id', $data) && empty($data['group_caracteristics_id'])){$data['group_caracteristics_id'] = null;}
            try {

                $model = $this->getServiceLocator()->get('Application\\'.$ucFirstEntity.'\Model\\'.$ucFirstEntity.'Table');
                $class = '\Application\\' . $ucFirstEntity . '\Model\\' . $ucFirstEntity;

                $object = new $class();
                $data = $data->toArray();
                foreach($data['languages'] as $language_code => $item){

                    if($object->id){
                        unset($data['id']);
                    }

                    $object->exchangeArray($data);
                    $object->exchangeArray($item);
                    $object->code = $language_code;
                    $model->save($object);


                    if ($object->id) {
                        $id = $object->id;
                    }
                }

                if(isset($data['id']) && $data['id'] > 0){
                    $this->saveUpdateHistory(array('table_name'=>$option['entity'], 'data_id'=>$data['id']));
                }else{
                    $this->saveCreateHistory(array('table_name'=>$option['entity'],'data_id'=>$object->id));
                }

                $this->addSuccess('La sauvegarde a été effectuée avec succès');

            } catch (Exception $e) {
                echo 'Exception reçue : ',  $e->getMessage(), "\n";
            }

        }else{
            throw new Exception('Aucune donnée envoyée.');
        }

        if($redirect == true) {
            $this->redirect()->toRoute($option['entity'].'_edit', array(
                'id' => $id,
            ));
        }else{
            return $id;
        }


    }

    public function getViewMediaManager($dataId, $code = 'fr')
    {
        $viewMediaManagerForm = new ViewModel();
        $viewMediaManagerForm->setTemplate('application/media_manager/form');
        $viewMediaManagerForm->setVariable('dataId', $dataId);
        $viewMediaManagerForm->setVariable('code', $code);
        return $viewMediaManagerForm;
    }

    public function getEngineMediaManager()
    {
        $this->getServiceLocator()
            ->get('viewhelpermanager')
            ->get('HeadScript')
            ->appendFile('/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js')
            ->appendFile('/plugins/jquery-file-upload/js/vendor/tmpl.min.js')
            ->appendFile('/plugins/jquery-file-upload/js/vendor/load-image.min.js')
            ->appendFile('/plugins/jquery-file-upload/js/vendor/canvas-to-blob.min.js')
            //->appendFile('/plugins/jquery-file-upload/blueimp-gallery/jquery.blueimp-gallery.min.js')
            ->appendFile('/plugins/jquery-file-upload/js/jquery.fileupload.js')
            ->appendFile('/plugins/jquery-file-upload/js/jquery.fileupload-process.js')
            ->appendFile('/plugins/jquery-file-upload/js/jquery.fileupload-image.js')
            ->appendFile('/plugins/jquery-file-upload/js/jquery.fileupload-audio.js')
            ->appendFile('/plugins/jquery-file-upload/js/jquery.fileupload-video.js')
            ->appendFile('/plugins/jquery-file-upload/js/jquery.fileupload-validate.js')
            ->appendFile('/plugins/jquery-file-upload/js/jquery.fileupload-ui.js');

        $viewMediaManagerList = new ViewModel();
        $viewMediaManagerList->setTemplate('application/media_manager/list');
        return $viewMediaManagerList;
    }
}
