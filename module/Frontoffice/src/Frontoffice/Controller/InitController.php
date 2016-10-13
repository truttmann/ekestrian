<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Frontoffice\Controller;

use Application\Model\CustomList;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Zend\Validator\File\Size;

class InitController extends AbstractActionController
{
    protected $lang_id = 1;
    protected $_service_locator  = null;
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

        $router = $this->_service_locator->get('router');
        $request = $this->_service_locator->get('request');
        $routeMatch = $router->match($request);
        switch($routeMatch->getParam('lang')) {
            case 'fr': 
                $this->lang_id = 1;
                break;
            case 'en':
                $this->lang_id = 2;
                break;
        }
        
        $this->viewBreadcrumb = new ViewModel();
        $this->viewBreadcrumb->setTemplate('application/breadcrumb/items');
        $this->viewBreadcrumb->setVariable('items', array(array(
            'route' =>  'home',
            'label' =>  'Accueil'
        )));
        $this->mainView = new ViewModel();
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
            ->appendFile('/js/main.js')
            ->appendFile('/js/knockout.js')
            ->appendFile('/js/common.js')
            ->appendFile('/js/newsletters.js')
            ->appendFile('/js/jquery.elevateZoom.min.js')
            ->appendFile('/js/light/jquery.mousewheel.min.js')
            ->appendFile('/js/jquery.blueimp-gallery.min.js');
    }

    protected function initListJs(){
    }



    protected function initFormJs(){


        $this
            ->getServiceLocator()
            ->get('viewhelpermanager')
            ->get('HeadScript');
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
        $languageTable = $this->_service_locator->get('Application\Language\Model\LanguageTable');
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
        /*$aclHelper = new Acl($this->_service_locator);
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
        $this->layout()->setVariable('page_title', $this->pageTitle);

        $this->layout()->setVariable('message_success',self::MESSAGE_SUCCESS_TYPE);
        $this->layout()->setVariable('message_error',self::MESSAGE_ERROR_TYPE);
        $this->layout()->setVariable('message_warning',self::MESSAGE_WARNING_TYPE);

        $this->layout()->setVariable('buttons', $this->mainButtons);

        if ($this->viewBreadcrumb instanceof ViewModel) {
            $this->layout()->addChild($this->viewBreadcrumb, 'breadcrumb');
        }

        $header = new ViewModel();
        $header->setTemplate('frontoffice/page/header');
        $header->setVariable("lang_id", $this->lang_id);
        $this->layout()->addChild($header, 'header');

        $navbar = new ViewModel();
        $navbar->setTemplate('frontoffice/page/navbar');
        $navbar->setVariable("lang_id", $this->lang_id);
        $this->layout()->addChild($navbar, 'navbar');
        
        $newsletter = new ViewModel();
        $newsletter->setTemplate('frontoffice/page/newsletter');
        $newsletter->setVariable("lang_id", $this->lang_id);
        $this->layout()->addChild($newsletter, 'newsletter');

        $footer = new ViewModel();
        $footer->setTemplate('frontoffice/page/footer');
        $footer->setVariable("lang_id", $this->lang_id);
        $this->layout()->addChild($footer, 'footer');

        
        $this->layout()->setVariable("lang_id", $this->lang_id);

        $em = $e->getApplication()->getEventManager();
        
        if(!empty($this->_messages))
            $this->flashMessenger()->addMessage($this->_messages);
        
  /*      $temp = $this->_service_locator->get('user_service')->getMessages();
        if(!empty($temp)) {
            $this->flashMessenger()->addMessage($temp);
            $this->_service_locator->get('user_service')->setMessages(null);
        }
        
        $em->attach(\Zend\Mvc\MvcEvent::EVENT_RENDER, function($e) {
            $messenger = new \Zend\Mvc\Controller\Plugin\FlashMessenger();
            if ($messenger->hasMessages()) {
                $messages = $messenger->getMessages();
                $e->getViewModel()->setVariable('flashMessages', $messages);
            }
        });

    */
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
        $viewRender = $this->_service_locator->get('ViewRenderer');
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
        
        $this->_service_locator->get('user_service')->setMessages($this->_messages);
        return $this;
    }

    public function getBasePath($path = null){
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        return $renderer->basePath($path);
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
        $this->_service_locator
            ->get('viewhelpermanager')
            ->get('HeadScript')
            ->appendFile('/plugins/jquery-file-upload/js/jquery.fileupload-ui.js');

        $viewMediaManagerList = new ViewModel();
        $viewMediaManagerList->setTemplate('application/media_manager/list');
        return $viewMediaManagerList;
    }
}
