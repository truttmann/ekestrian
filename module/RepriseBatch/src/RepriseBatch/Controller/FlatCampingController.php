<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 *
 * Construit la table a plat
 * la remplie
 */

namespace RepriseBatch\Controller;

use RepriseBatch\Model\FlatCampingTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Console\Request as ConsoleRequest;

class FlatCampingController extends AbstractActionController
{
    private $logger;

    private $adapter;

    /**
     * @var FlatCampingTable
     */
    private $flatCamping;

    public function __construct()
    {
        $writer = new Stream(__DIR__ .'/../../../log/'.date( 'Y-m-d' ).'-info.log');
        $this->logger = new Logger();
        $this->logger->addWriter($writer);
        set_time_limit(0);
    }

    public function loadAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $forced = $request->getParam('forced');

        $this->adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $this->flatCamping = new FlatCampingTable($this->adapter, $this->getServiceLocator());

        if ($this->flatCamping->isOutdatedTable() || $forced) {
            $this->reloadTable();
        } else {
            $this->updateTable();
        }
    }

    public function reloadTable()
    {
        try {
            echo " [proc] Création de la table temporaire de Flat Camping                                   \r";
            $flatCampingTmp = new FlatCampingTable($this->adapter, $this->getServiceLocator(), $this->flatCamping->getTableName() . '_tmp');
            $flatCampingTmp->dropTable();
            $flatCampingTmp->createTable();
            echo " [ ok ] Création de la table temporaire de Flat Camping                                   \r\n";

            echo " [proc] Chargement des données dans la table temporaire...                                   \r";
            $nbAffectedRows = $flatCampingTmp->insertDatas();
            echo " [ ok ] Chargement des données dans la table temporaire... $nbAffectedRows données de campings insérées              \r\n";

            echo " [proc] Mise à jour des données de filtres...                                                                 \r";
            $nbAffectedRows = $flatCampingTmp->updateFiltersDatas();
            echo " [ ok ] Mise à jour des données de filtres... $nbAffectedRows données de filtres mis à jour                          \r\n";

            echo " [proc] Mise à jour des données de logos chaine...                                                                 \r";
            $nbAffectedRows = $flatCampingTmp->updateCampingLogoChaine();
            echo " [ ok ] Mise à jour des données de logos chaine... $nbAffectedRows données de logos chaine mis à jour                          \r\n";

            echo " [proc] Suppression de la table actuelle et remplacement par la table temporaire                                             \r";
            $this->flatCamping->dropTable();
            $flatCampingTmp->renameTable($this->flatCamping->getTableName());
            echo " [ ok ] Suppression de la table actuelle et remplacement par la table temporaire                                             \r\n";
        } catch (\Exception $e) {
            $this->logger->log(1,   $e->getMessage(), array("controller" => "FlatCampingController", "action" => "reloadTable"));
        }
    }

    public function updateTable()
    {
        try {
            echo " [proc] Suppression des campings expirés...                                                    \r";
            $nbAffectedRows = $this->flatCamping->deleteOutdatedDatas();
            echo " [ ok ] Suppression des campings expirés... $nbAffectedRows données de campings supprimé                 \r\n";

            echo " [proc] Insertion des données inexistantes...                                                    \r";
            $nbAffectedRows = $this->flatCamping->insertDatas();
            echo " [ ok ] Insertion des données inexistantes... $nbAffectedRows données de campings insérées               \r\n";

            echo " [proc] Mise à jour des données de campings...                                                                 \r";
            $nbAffectedRows = $this->flatCamping->updateCampingsData();
            echo " [ ok ] Mise à jour des données de campings... $nbAffectedRows données de campings mis à jour                          \r\n";

            echo " [proc] Mise à jour des données de filtres...                                                                 \r";
            $nbAffectedRows = $this->flatCamping->updateFiltersDatas();
            echo " [ ok ] Mise à jour des données de filtres... $nbAffectedRows données de filtres mis à jour                          \r\n";

            echo " [proc] Mise à jour des données de logos chaine...                                                                 \r";
            $nbAffectedRows = $this->flatCamping->updateCampingLogoChaine();
            echo " [ ok ] Mise à jour des données de logos chaine... $nbAffectedRows données de logos chaine mis à jour                          \r\n";
        } catch (\Exception $e) {
            $this->logger->log(1,   $e->getMessage(), array("controller" => "FlatCampingController", "action" => "updateTable"));
        }
    }
}
