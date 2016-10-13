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

/**
 * Class Onglet
 * @package Application\Core\Modelcettte classe gere l'affichage en onglet
 */
class InitModel extends ViewModel{

    protected function getCurrentUrlWithoutParams(){
        return preg_replace('#([-a-z:/0-9_]+)\?(.*)#', '$1', "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
    }



}
