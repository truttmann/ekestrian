<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Model;


use Application\Model\InitModel;
use Zend\View\Model\ViewModel;


class CustomPaginator extends InitModel
{

    protected $_template = 'application/page/paginator';
    protected $_paginator = null;
    protected $_title_items_only = 'enregistrement';
    protected $_title_items_plural = 'enregistrements';

    public function toHtml(){
        $viewModel = new ViewModel();
        $viewModel->setTemplate($this->_template);

        // nombre de page
        $viewModel->setVariables(array('nbr_page' => $this->getPaginatorObject()->count()));

        // nbr items
        $viewModel->setVariables(array('nbr_items' => $this->getPaginatorObject()->getTotalItemCount()));

        $viewModel->setVariables(array('current_page' => $this->getPaginatorObject()->getCurrentPageNumber()));

        $viewModel->setVariables(array('link_previous' => $this->getPrevious()));

        $viewModel->setVariables(array('link_next' => $this->getNext()));

        $viewModel->setVariables(array('nbr_items_per_page' => $this->getPaginatorObject()->getItemCountPerPage()));

        $viewModel->setVariables(array('current_url' => $this->getCurrentUrlWithoutParams()));

        $viewModel->setVariables(array('title_items_only' => $this->_title_items_only));

        $viewModel->setVariables(array('title_items_plural' => $this->_title_items_plural));

        return $viewModel;
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

    public function setPaginatorObject($item){
        $this->_paginator = $item;
        return $this;
    }

    public function getPaginatorObject(){
        return $this->_paginator;
    }

    public function getPrevious(){
        return ($this->getPaginatorObject()->getCurrentPageNumber() > 2)?$this->getPaginatorObject()->getCurrentPageNumber() - 1:1;
    }

    public function getNext(){
        return ($this->getPaginatorObject()->getCurrentPageNumber() < $this->getPaginatorObject()->count())?$this->getPaginatorObject()->getCurrentPageNumber() + 1:$this->getPaginatorObject()->count();
    }




}
