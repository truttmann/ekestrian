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

class NewsletterController extends InitController
{

    public function listAction(){

        parent::initListJs();

        $this->setPageTitle('Liste des inscrits à la newsletter');

        $this->addLinkToBreadcrumb('newsletter', 'Liste des inscrits à la newsletter');

        $list = $this->getList("newsleter_list");

        $list->setTitle('Newslettre');

        $items = $this->getServiceLocator()->get('newsletterTable');

        $filters = $list->getFilters();
        $order = $list->getOrder();

        $list->addLinks(
            array(
                array(
                    'label' => 'Nouveau',
                    'route' => "newsletter/edit"
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
                    'code'          =>  'newsletter_id',
                    'label'         =>  'Id',
                    'searchable'    =>  true,
                    'sortable'      =>  true,
                    'type'          =>  'text',
                    'width'         =>  '20',
                    'placeholder'   =>  '#',
                ),
                array(
                    'code'          =>  'email',
                    'label'         =>  'email',
                    'searchable'    =>  true,
                    'sortable'      =>  true,
                    'type'          =>  'text',
                    'width'         =>  '60',
                    'placeholder'   =>  'email',

                ),

                array(
                    'code'          =>  'action',
                    'label'         =>  'Actions',
                    'searchable'    =>  false,
                    'type'          =>  'link',
                    'width'         =>  '20',
                    'items'         =>  array(
                        array(
                            'route'     =>  'newsletter/delete',
                            'label'     =>  'Supprimer',
                            'matching_params'    =>  array(
                                'newsletter_id'
                            )
                        )
                    )
                ),
            )
        );

        return $list->toHtml();
    }
    
    public function editAction(){

        $this->setPageTitle('Edition d\'un inscrit à la newsletter');

        $this->initFormJs();

        $this->addLinkToBreadcrumb('newsletter', 'Liste des inscrits à la newsletter')
            ->addLinkToBreadcrumb('newsletter/edit', 'Edition d\'un inscrit à la newsletter');

        $data = array();
        
        $mainViewModel = new \Application\Model\ViewForm('Newsletter', 'newsletter', 'newsletter/save');
        
        $this->addMainButton('Retour', 'newsletter', array(), 'btn blue-madison');
        $this->addMainButton('Sauvegarder', null, null, 'btn red-intense save');

        $form = $this->getServiceLocator()->get('FormElementManager')->get("newsletter_edit");
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
        $FormViewModel->setTemplate('application/form/newsletter');
        $FormViewModel->setVariable('form', $form);
        

        $FormViewModel->setVariable('form_title', 'Informations de l\'inscrit à la newsletter');

        $this->mainView->setVariable('action_route', $this->url()->fromRoute('newsletter/save'));
        $this->mainView->addChild($FormViewModel, 'form');
        $this->mainView->setTemplate('application/page/form_container');


        return $this->mainView;
    }
    
    public function deleteAction(){

        
        if ($id = $this->params()->fromRoute('newsletter_id')) {

            $items = $this->getServiceLocator()->get('newsletterTable');
            $c = $items->getNewsletter($id);

            $items->delete($c);
            $this->addSuccess('La suppression a été effectuée avec succès');
        } 
        $this->redirect()->toRoute('newsletter', array());
    }
    
    public function saveAction() {
        if($this->getRequest()->getPost()){
            $data = $this->getRequest()->getPost()->toArray();
            
            $obj = new \Application\Model\Newsletter();
            
            $form = $this->getServiceLocator()->get('FormElementManager')->get("newsletter_edit");
            $form->setServiceLocator($this->getServiceLocator());
            $form->initForm();
            $form->setInputFilter($obj->getInputFilter());
            $form->setData($data);
            
            if ($form->isValid()) {
                try {
                    // sauvegarde de l'heure de mea
                    $newsletterModel = $this->getServiceLocator()->get('newsletterTable');
                    $obj = new \Application\Model\Newsletter();
                    $obj->exchangeArray($data);
                    
                    $obj = $newsletterModel->save($obj);
                    $this->addSuccess('La sauvegarde a été effectuée avec succès');
                } catch (Exception $e) {
                    echo 'Exception reçue : ',  $e->getMessage(), "\n";
                }
            }else{
                if($messages = $form->getMessages()){
                    $this->addError('La sauvegarde a échouée');
                    foreach($messages as $message){
                        $this->addError($message);
                    }
                }
                $this->getServiceLocator()->get('user_service')->setInfoForm(array($data, $form->getMessages()));
                return $this->redirect()->toRoute('newsletter/edit');
            }
        }else{
            throw new Exception('Aucune donnée envoyée.');
        }
        $this->redirect()->toRoute('newsletter');
    }
}
