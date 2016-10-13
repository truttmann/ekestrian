<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Model;

use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Application\Model\CustomPaginator;
use Application\Core\Helper\Csv;


class CustomList extends InitModel{

    protected $_columns = array();
    protected $_links = array();
    protected $_actions = array();
    protected $_template = 'application/page/list';
    protected $_items = array();
    protected $_filters = array();
    protected $_title = 'default list title';
    protected $_session = null;
    protected $_paginator = null;
    protected $_pagination_available = true;
    protected $_filters_available = true;
    protected $_mass_update_available = false;
    protected $_extra_fields = array();
    protected $_title_items_only = 'enregistrement';
    protected $_title_items_plural = 'enregistrements';
    protected $_id_list = null;
    protected $_added_to_view = array();



    public function __construct($namespace, $request){

        // $namespace = $namespace de la session pour les filtres
        $this->_initSession($namespace);
        $this->_initFilters($request);
        $this->_initPaginator();

    }

    private function _initSession($namsepace){
        if($this->_session === null){
            $this->_session = new \Zend\Session\Container($namsepace);
        }
        return $this;
    }

    private function _initPaginator(){
        if($this->_paginator === null){
            $this->_paginator = new CustomPaginator();
        }
        return $this;
    }

    public function toHtml(){

        $viewModel = new ViewModel();
        $viewModel->setTemplate($this->_template);

        $title = $this->getTitle();
        $viewModel->setVariables(array('title' => $title));

        $cols = $this->getCols();
        $viewModel->setVariables(array('cols' => $cols));

        $links = $this->getLinks();
        $viewModel->setVariables(array('links' => $links));

        $items = $this->getItems();
        $viewModel->setVariables(array('items' => $items));

        $filters = $this->getFilters();
        $viewModel->setVariables(array('filters' => $filters));

        $pagination_available = $this->getIsPaginationAvailable();
        $viewModel->setVariables(array('pagination_available' => $pagination_available));

        if($pagination_available){
            $_title_items_plural = $this->getTitleItemsPlural();
            $_title_items_only = $this->getTitleItemsOnly();



            $paginator = $this->getPaginator();
            $paginator->setTitleItemsOnly($_title_items_only);
            $paginator->setTitleItemsPlural($_title_items_plural);

            $paginator->setVariable('title_items_only', $_title_items_only);
            $paginator->setVariable('title_items_plural', $_title_items_plural);
            $viewModel->addChild($paginator->toHtml(), 'paginator');
        }

        $filters_available = $this->getIsFiltersAvailable();
        $viewModel->setVariables(array('filters_available' => $filters_available));

        $id_list = $this->getIdList();
        $viewModel->setVariables(array('id_list' => $id_list));

        $mass_update_available = $this->getIsMassUpdateAvailable();
        $viewModel->setVariables(array('mass_update_available' => $mass_update_available));

        $actions = $this->getActions();
        $viewModel->setVariables(array('actions' => $actions));

        $current_url = $this->getCurrentUrlWithoutParams();
        $viewModel->setVariables(array('current_url' => $current_url));

        $extra_fields = $this->getExtraFields();
        $viewModel->setVariables(array('extra_fields' => $extra_fields));

        $added_to_view = $this->getAddedToView();
        $viewModel->setVariables(array('added_to_view' => $added_to_view));


        return $viewModel;
    }


    public function addToView($key, $value) {
        $this->_added_to_view[$key] = $value;
        return $this;
    }

    public function getAddedToView() {
        return $this->_added_to_view;
    }

    public function getIdList(){
        return $this->_id_list;

    }

    public function setIdList($id){
        $this->_id_list = $id;
        return $this;

    }

    public function getExtraFields(){
        return $this->_extra_fields;

    }

    public function addExtraFields($field){
        $this->_extra_fields[] = $field;

    }

    public function setTemplate($template){
        $this->_template = $template;
        return $this;
    }

    public function getTemplate(){
        return $this->_template;
    }

    public function setIsPaginationAvailable($boolean){
        $this->_pagination_available = $boolean;
        return $this;
    }

    public function setIsFiltersAvailable($boolean){
        $this->_filters_available = $boolean;
        return $this;
    }

    public function getTitleItemsOnly(){
        return $this->_title_items_only;
    }

    public function setTitleItemsOnly($boolean){
        $this->_title_items_only = $boolean;
        return $this;
    }

    public function getTitleItemsPlural(){
        return $this->_title_items_plural;
    }

    public function setTitleItemsPlural($boolean){
        $this->_title_items_plural = $boolean;
        return $this;
    }

    public function getIsFiltersAvailable(){
        return $this->_filters_available;
    }

    public function setIsMassUpdateAvailable($boolean){
        $this->_mass_update_available = $boolean;
        return $this;
    }

    public function getIsMassUpdateAvailable(){
        return $this->_mass_update_available;
    }

    public function getIsPaginationAvailable(){
        return $this->_pagination_available;
    }

    public function addCol(array $item){
        $this->_columns[] = $item;
        return $this;
    }

    public function addCols(array $items){
        foreach($items as $item)
            $this->addCol($item);

        return $this;
    }

    public function addAction(array $item){
        $this->_actions[] = $item;
        return $this;
    }

    public function getActions(){
        return $this->_actions;
    }

    public function addActions(array $items){
        foreach($items as $item)
            $this->addAction($item);

        return $this;
    }

    public function getPaginator(){
        return $this->_paginator;
    }

    public function getCols(){
        return $this->_columns;
    }

    public function getItems(){
        return $this->_items;
    }

    public function setItems($item){
        $this->_items = $item;

        // gestion de l'export CSV si export-csv posteé et vrai
        $this->_exportCsv();

        return $this;
    }

    private function _exportCsv(){

        if(isset($_POST['export-csv']) && $_POST['export-csv'] == 1){
            if($items = $this->getItems()){
                $csv = new Csv();
                $csv->setItems($items);
                $csv->setTitle($this->getTitle());
                $csv->build();
                $csv->download();
            }
        }
    }

    public function setTitle($item){
        $this->_title = $item;
        return $this;
    }

    public function getTitle(){
        return $this->_title;
    }

    public function addLink(array $item){
        $this->_links[] = $item;
        return $this;
    }

    public function addLinks(array $items){
        foreach($items as $item)
            $this->addLink($item);

        return $this;
    }

    public function getLinks(){
        return $this->_links;
    }

    public function getOrder(){
      return (isset($_GET['order']) && $_GET['order'] && isset($_GET['direction']) && $_GET['direction'])?$_GET['order'] . ' ' . $_GET['direction']:null;
    }

    public function getFilters(){
        $session = $this->_getSession();
        return $session->filters;
    }

    public function addFilter($key, $val){
        $session = $this->_getSession();
        $session->filters['filters'][$key] = $val;
        return $this;
    }

    public function removeFilter($key){
        $session = $this->_getSession();

        if(isset($session->filters['filters'][$key])){
            unset($session->filters['filters'][$key]);
        }

        if(isset($_POST['filters'][$key])){
            unset($_POST['filters'][$key]);
        }

        return $this;
    }

    private function _initFilters($request){

        if($request->isPost()){

            $filters = $request->getPost()->toArray();
            $session = $this->_getSession();
            $session->filters = array();
            $session->filters['filters'] = array();

            if((!isset($filters['reset']) || !$filters['reset']) && isset($filters['filters'])){
                foreach($filters['filters'] as $key => $v){
                    $session->filters['filters'][$key] = $v;
                }
            }

            $session->filters['nbr_results_per_page'] = (isset($filters['nbr_results_per_page']))?$filters['nbr_results_per_page']:20;
            $session->filters['current_page'] = (isset($_GET['page']) && $_GET['page'] > 0)?$_GET['page']:1;

        }
        return $this;
    }

    private function _getSession(){
        return $this->_session;
    }
}
