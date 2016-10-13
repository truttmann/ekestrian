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

class IndexController extends InitController
{

    public function indexAction(){
        parent::initListJs();
        $user = $this->getServiceLocator()->get('user_service')->getLoggedUser();


        //

        if ($user->name) {
            $this->setPageTitle('Bienvenue, ' . $user->name);
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('application/dashboard');
/*
        $isRight = false;

        $viewModel->setVariable("info_user_accueil", $this->_getCurrentRoleInfo());

        if ($this->getIsGestionnaire() === false && $this->getIsPartner() == false) {
            // nbr campings
            $mCampings = $this->getServiceLocator()->get('Application\Camping\Model\CampingTable')->fetchAll();
            $viewModel->setVariable('nbr_campings', $mCampings->getTotalItemCount());

            // nbr annonceurs
            $mPartners = $this->getServiceLocator()->get('Application\Partner\Model\PartnerTable')->fetchAll();
            $viewModel->setVariable('nbr_partners', $mPartners->getTotalItemCount());

            // nbr membres
            $mMembers = $this->getServiceLocator()->get('Application\Member\Model\MemberTable')->fetchAll();
            $viewModel->setVariable('nbr_members', $mMembers->getTotalItemCount());

            $isRight = true;

            $viewModel->setVariable('isRight', $isRight);
            $viewModel->setVariable('languages', $this->getLanguages());

            
            $list->setTitle('Move Publishing');


                
            $list->setTitle('Contrats');

            $list->setIsPaginationAvailable(true);

            $contractTable = $this->getServiceLocator()->get('Application\Contract\Model\ContractTable');

            $aclPlugin = new Aclplugin($this->getServiceLocator());
            $session = $aclPlugin->getSessContainer();
            $role = $session->role_connexion;


            $viewModel->addChild($list->toHtml(), 'partner');
        }
*/
        return $viewModel;
    }
}
