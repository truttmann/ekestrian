<?php

namespace RepriseBatch\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Ddl;
use Zend\Db\Sql\Ddl\Column;
use RepriseBatch\utils\sql\dbColumn;
use Application\Filter\Model;

class FlatCampingTable
{
    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    protected $filterIds;
    protected $filtersWithNoCondition;
    protected $filtersWithCondition;

    protected $thematicIds;

    protected $languageList;

    protected $updateQueries;

    public function __construct(Adapter $adapter, $sm, $tableName = '_flat_camping')
    {
        $this->adapter = $adapter;
        $this->sql = new Sql($adapter);
        $this->tableName = $tableName;
        $this->sm = $sm;
        $this->updateQueries = null;

        $this->filterIds = array();
        $filterTable = $this->sm->get('Application\Filter\Model\FilterTable');
        $filtersList = $filterTable->fetchAll();

        $this->filtersWithCondition = array();
        $this->filtersWithNoCondition = array();
        $filtersList->setDefaultItemCountPerPage($filtersList->getTotalItemCount());
        foreach ($filtersList as $filter) {
            if (!empty($filter->condition)) {
                $this->filtersWithCondition[] = $filter;
            } else {
                $this->filtersWithNoCondition[] = $filter;
            }
            $this->filterIds[] = $filter->id;
        }

        $this->thematicIds = array();
        $thematicTable = $this->sm->get('Application\Thematic\Model\ThematicTable');
        $thematicsList = $thematicTable->fetchAll();
        $thematicsList->setDefaultItemCountPerPage($thematicsList->getTotalItemCount());
        foreach ($thematicsList as $thematic) {
            $this->thematicIds[] = $thematic->id;
        }

        $languageTable = $this->sm->get('Application\Language\Model\LanguageTable');
        $this->languageList = array();
        foreach ($languageTable->fetchAll(null, 'ordering') as $language) {
            $this->languageList[] = $language->code;
        }
    }

    /**
     * Renvoi le nom de la table
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return bool
     */
    public function isExist()
    {
        $sql = "SHOW TABLES LIKE '{$this->tableName}'";
        $resultSet = $this->adapter->query($sql)
                                   ->execute();
        if ($resultSet->count()) {
            return true;
        }
        return false;
    }

    /**
     * Supprime la table si elle existe
     */
    public function dropTable()
    {
        $sql = "DROP TABLE IF EXISTS {$this->tableName}";
        $this->adapter->query($sql)
                      ->execute();
    }

    /**
     * Créer la table
     */
    public function createTable()
    {
        $table = new Ddl\CreateTable($this->tableName);

        $table->addColumn(new Column\Integer('camping_id'));
        $table->addColumn(new Column\Varchar('logo_chaine_id', 255));

        $isOurSelection = new Column\Boolean('_is_our_selection');
        $isOurSelection->setNullable(false)
                       ->setDefault(false);

        $table->addColumn($isOurSelection);

        $isFavorite = new Column\Boolean('_is_favorite');
        $isFavorite->setNullable(false)
                   ->setDefault(false);

        $table->addColumn($isFavorite);

        $priority = new dbColumn\TinyInteger('_priority');
        $priority->setNullable(false)
                 ->setDefault(99);
        $table->addColumn($priority);

        foreach ($this->languageList as $language) {
            $geoOrder = new Column\Varchar('geo_order_' . $language, 255);
            $table->addColumn($geoOrder);
        }

        $coordLatitude = new Column\Decimal('coord_latitude', 10, 7);
        $coordLatitude->setNullable(true);
        $table->addColumn($coordLatitude);

        $coordLongitude = new Column\Decimal('coord_longitude', 10, 7);
        $coordLongitude->setNullable(true);
        $table->addColumn($coordLongitude);

        $cityCoordLatitude = new Column\Decimal('city_coord_latitude', 10, 7);
        $cityCoordLatitude->setNullable(true);
        $table->addColumn($cityCoordLatitude);

        $cityCoordLongitude = new Column\Decimal('city_coord_longitude', 10, 7);
        $cityCoordLongitude->setNullable(true);
        $table->addColumn($cityCoordLongitude);

        $table->addColumn(new Column\Integer('city_id'));
        $table->addColumn(new Column\Integer('department_id'));
        $table->addColumn(new Column\Integer('region_id'));

        foreach ($this->filterIds as $filterId) {
            $filterColumn = new dbColumn\TinyInteger('filter_' . $filterId);
            $filterColumn->setNullable(true)
                         ->setDefault(null);
            $table->addColumn($filterColumn);
        }

        foreach ($this->thematicIds as $thematicId) {
            $thematicColumn = new dbColumn\TinyInteger('thematic_' . $thematicId);
            $thematicColumn->setNullable(true)
                           ->setDefault(null);
            $table->addColumn($thematicColumn);
        }

        $tableConstraint = new Ddl\Constraint\PrimaryKey();
        $tableConstraint->addColumn('camping_id');
        $table->addConstraint($tableConstraint);

        $this->executeStatement($table);

        $sql = "ALTER TABLE {$this->tableName}
                ADD INDEX _is_our_selection (_is_our_selection),
                ADD INDEX _is_favorite (_is_favorite),
                ADD INDEX coord (coord_latitude, coord_longitude),
                ADD INDEX city_coord (city_coord_latitude, city_coord_longitude),
                ADD INDEX city_id (city_id),
                ADD INDEX department_id (department_id),";
        foreach ($this->languageList as $language) {
            $sql .= "ADD INDEX order_key_$language (_priority, geo_order_$language),";
        }
        $sql .= "ADD INDEX region_id (region_id)";
        $this->adapter->query($sql)
                      ->execute();
    }

    /**
     * Insert les données des campings qui n'existent pas.
     * @return int Le nombre de ligne affectées
     */
    public function insertDatas()
    {
        $languages = array();
        $languagesConcat = array();
        $languagesJoin = array();
        foreach ($this->languageList as $lang) {
            $geoOrderLang = 'geo_order_' . $lang;
            $languages[] = $geoOrderLang;
            $languagesConcat[] = "CONCAT(IFNULL(region_$lang.label, region_fr.label), IFNULL(department_$lang.label, department_fr.label), IF(city_$lang.label IS NOT NULL, city_$lang.prefix_le, city_fr.prefix_le), IFNULL(city_$lang.label, city_fr.label), camping.name) as $geoOrderLang";
            $languagesJoin[] = "LEFT JOIN city_i18n AS city_$lang ON city.id=city_$lang.id AND city_$lang.code='$lang'
                                LEFT JOIN department_i18n AS department_$lang ON department.id=department_$lang.id AND department_$lang.code='$lang'
                                LEFT JOIN region_i18n AS region_$lang ON region.id=region_$lang.id AND region_$lang.code='$lang'";
        }
        $sql = "INSERT INTO {$this->tableName}(
                    camping_id,
                    _is_our_selection,
                    _is_favorite,
                    _priority,
                    coord_longitude,
                    coord_latitude,
                    city_coord_longitude,
                    city_coord_latitude,
                    city_id,
                    department_id,
                    region_id,
                    " . implode(', ', $languages) . ")
                SELECT
                    camping.id AS camping_id,
                    camping._is_our_selection,
                    camping._is_favorite,
                    camping._priority,
                    camping.coord_longitude,
                    camping.coord_latitude,
                    city.coord_longitude AS city_coord_longitude,
                    city.coord_latitude AS city_coord_latitude,
                    camping.city_id,
                    city.department_id,
                    department.region_id,
                    " . implode(', ', $languagesConcat) . "
                FROM camping
                INNER JOIN city
                    ON city.id=camping.city_id
                INNER JOIN department
                    ON department.id=city.department_id
                INNER JOIN region
                    ON region.id=department.region_id
                " . implode(' ', $languagesJoin) . "
                LEFT JOIN {$this->tableName} fca
                    ON fca.camping_id=camping.id
                WHERE camping.status=1 AND fca.camping_id IS NULL";
        $resultSet = $this->adapter->query($sql)
                                   ->execute();
        return $resultSet->count();
    }

    /**
     * Mets à jour les données de base des campings
     * @return int Le nombre de ligne mis à jour
     */
    public function updateCampingsData()
    {
        $languages = array();
        $languagesJoin = array();
        $languagesSelect = array();
        $geoOrderCheck = array();
        foreach ($this->languageList as $lang) {
            $geoOrderLang = 'geo_order_' . $lang;
            $languages[] = $geoOrderLang;
            $languagesSelect[] = "CONCAT(IFNULL(region_$lang.label, region_fr.label), IFNULL(department_$lang.label, department_fr.label), IFNULL(city_$lang.label, city_fr.prefix_le), IFNULL(city_$lang.label, city_fr.label), camping.name) AS geo_order_$lang";
            $languagesJoin[] = "LEFT JOIN city_i18n AS city_$lang ON city.id=city_$lang.id AND city_$lang.code='$lang'
                                LEFT JOIN department_i18n AS department_$lang ON department.id=department_$lang.id AND department_$lang.code='$lang'
                                LEFT JOIN region_i18n AS region_$lang ON region.id=region_$lang.id AND region_$lang.code='$lang'";
            $geoOrderCheck[] = "CONCAT(IFNULL(region_$lang.label, region_fr.label), IFNULL(department_$lang.label, department_fr.label), IFNULL(city_$lang.label, city_fr.prefix_le), IFNULL(city_$lang.label, city_fr.label), camping.name)<>fc.$geoOrderLang";
        }
        $sql = "REPLACE INTO {$this->tableName}(camping_id, _is_our_selection, _is_favorite, _priority, coord_latitude, coord_longitude, city_coord_latitude, city_coord_longitude, city_id, department_id, region_id, " . implode(', ', $languages) . ")
                SELECT
                    camping.id AS camping_id,
                    camping._is_our_selection,
                    camping._is_favorite,
                    camping._priority,
                    camping.coord_latitude,
                    camping.coord_longitude,
                    city.coord_latitude,
                    city.coord_longitude,
                    camping.city_id,
                    city.department_id,
                    department.region_id,
                    " . implode(', ', $languagesSelect) . "
                FROM camping
                INNER JOIN city
                    ON city.id=camping.city_id
                INNER JOIN department
                    ON department.id=city.department_id
                INNER JOIN region
                    ON region.id=department.region_id
                " . implode(' ', $languagesJoin) . "
                INNER JOIN {$this->tableName} fc
                    ON fc.camping_id=camping.id
                WHERE (
                        camping._is_our_selection<>fc._is_our_selection
                        OR camping._priority<>fc._priority
                        OR camping.city_id<>fc.city_id
                        OR camping.coord_latitude<>fc.coord_latitude
                        OR camping.coord_longitude<>fc.coord_longitude
                        OR city.coord_latitude<>fc.city_coord_latitude
                        OR city.coord_longitude<>fc.city_coord_longitude
                        OR " . implode(' OR ', $geoOrderCheck) . "
                      ) OR (
                          (
                            camping._is_our_selection<>fc._is_our_selection
                            OR camping._priority<>fc._priority
                            OR camping.city_id<>fc.city_id
                          )
                        AND camping.coord_latitude IS NULL
                        AND fc.coord_latitude IS NULL
                        AND camping.coord_longitude IS NULL
                        AND fc.coord_longitude IS NULL
                        AND city.coord_latitude IS NULL
                        AND fc.city_coord_latitude IS NULL
                        AND city.coord_longitude IS NULL
                        AND fc.city_coord_longitude IS NULL
                      )";
        $resultSet = $this->adapter->query($sql)
                                   ->execute();
        return $resultSet->count() / 2;
    }

    /**
    * fonction qui va mettre a jour le champ logo_chaine_id
    */
    public function updateCampingLogoChaine() 
    {
        $sql = "
            SELECT GROUP_CONCAT( distinct logo.id SEPARATOR  '|' ) as chaine, logo_url.camping_id
            FROM logo
            INNER JOIN logo_url ON logo_url.id = logo.id 
            INNER JOIN {$this->tableName} ON {$this->tableName}.camping_id = logo_url.camping_id
            WHERE logo.status =1
            AND chaine =1
            GROUP BY logo_url.camping_id";
        $resultSet = $this->adapter->query($sql)->execute();

        foreach ($resultSet as $data) {
            $this->adapter->query("UPDATE {$this->tableName} set logo_chaine_id = CONCAT('|', '".$data["chaine"]."','|') where camping_id = ".$data["camping_id"].";")->execute();
        }

        return $resultSet->count() / 2;
    }

    /**
     * Mets à jour les filtres
     * La requête principale récupère les infos de chaque camping avec leurs filtres et thématiques actuel et de la table à plat.
     * Ensuite nous comparons les résultats de façon à savoir s'il y a une différence ou non.
     * S'il y a une différence, nous updatons la table à plat.
     * @param int $campingId
     * @return int Le nombre de ligne mis à jour
     */
    public function updateFiltersDatas($campingId = null)
    {
        $tmpClauseFilters = array();
        $tmpClauseFiltersFca = array();
        $tmpClauseAggregate = array();
        $tmpClauseAggregateFca = array();

        $languages = array();
        foreach ($this->languageList as $lang) {
            $languages[] = "fca.geo_order_$lang";
        }

        // Construction de la requête pour récupérer les valeurs de chaques filtres non aggregate (Condition AND) de la table à plat qui ne sont pas nul
        foreach ($this->filtersWithNoCondition as $filter) {
            $tmpClauseFiltersFca[] = "IF(fca.filter_{$filter->id} IS NULL, '', CONCAT('{$filter->id}=', fca.filter_{$filter->id}))";
        }

        // Construction de la requête pour récupérer les valeurs de chaques filtres aggregate (Condition OR) de la table à plat et des valeurs actuelles qui ne sont pas nul
        foreach ($this->filtersWithCondition as $filter) {
            $tmpClauseAggregate[] = "WHEN fc.filter_id={$filter->id} AND {$filter->condition} THEN '{$filter->id}=1'";
            $tmpClauseAggregateFca[] = "IF(fca.filter_{$filter->id} IS NULL, '', CONCAT('{$filter->id}=', fca.filter_{$filter->id}))";
        }

        // Construction de la requête pour récupérer les valeurs de chaques thématiques de la table à plat qui ne sont pas nul
        $tmpClauseThematics = array();
        foreach ($this->thematicIds as $thematicId) {
            $tmpClauseThematics[] = "IF(fca.thematic_$thematicId IS NULL, '', CONCAT('$thematicId=', fca.thematic_$thematicId))";
        }

        $sql = "SELECT
                    camping.id AS camping_id,
                    fca._is_our_selection,
                    fca._is_favorite,
                    fca._priority,
                    " . implode(', ', $languages) . ",
                    fca.coord_latitude,
                    fca.coord_longitude,
                    fca.city_coord_latitude,
                    fca.city_coord_longitude,
                    fca.city_id,
                    fca.department_id,
                    fca.region_id,
                    TRIM(GROUP_CONCAT(DISTINCT IF(filter.condition='', CONCAT(fc.filter_id, '=', IF(fc.filter_id IS NULL, 0, 1)), '') ORDER BY fc.filter_id SEPARATOR ' ')) AS filters,
                    TRIM(CONCAT(" . implode(", ' ', ", $tmpClauseFiltersFca) . ")) AS filters_fca,
                    GROUP_CONCAT(DISTINCT CONCAT(ct.thematic_id, '=', IF(ct.thematic_id IS NULL, 0, IF(ct.is_contract=1, 2, IF(ct.is_contract=0 AND ct.is_manual=0, 0, 1)))) ORDER BY ct.thematic_id) AS thematics,
                    TRIM(CONCAT(" . implode(", ' ', ", $tmpClauseThematics) . ")) AS thematics_fca,
                    TRIM(GROUP_CONCAT(DISTINCT CASE " . implode(' ', $tmpClauseAggregate) . " END ORDER BY fc.filter_id SEPARATOR ' ')) AS filters_aggregate,
                    TRIM(CONCAT(" . implode(", ' ', ", $tmpClauseAggregateFca) . ")) AS filters_aggregate_fca
                FROM camping
                INNER JOIN {$this->tableName} fca
                    ON fca.camping_id=camping.id
                LEFT JOIN camping_caracteristic cc
                    ON cc.camping_id=camping.id
                LEFT JOIN filter_caracteristic fc
                    ON fc.caracteristic_id=cc.caracteristic_id
                INNER JOIN filter
                    ON filter.id=fc.filter_id
                LEFT JOIN camping_thematic ct
                    ON ct.camping_id=camping.id";
        if ($campingId) {
            $sql .= " WHERE camping.id=$campingId";
        }
        $sql .= " GROUP BY camping.id";

        $resultSet = $this->adapter->query($sql)
                                   ->execute();


        // On boucle sur les résultats
        foreach ($resultSet as $data) {

            // On format les données pour avoir quelques choses de propre comme : '12=1,89=1,...' (id et valeur)
            $filters = preg_replace('/\s+/', ',', $data['filters']);
            $filtersAggregate = preg_replace('/\s+/', ',', $data['filters_aggregate']);
            $thematics = preg_replace('/\s+/', ',', $data['thematics']);

            // On vérifie s'il y a une différence entre les valeurs actuelles et ceux générés précédement dans la table à plat
            if ($filters != preg_replace('/\s+/', ',', $data['filters_fca'])
                || $filtersAggregate != preg_replace('/\s+/', ',', $data['filters_aggregate_fca'])
                || $thematics != preg_replace('/\s+/', ',', $data['thematics_fca'])) {
                $flatCamping = new FlatCamping();

                // On le stock dans notre objet FlatCamping
                $flatCamping->exchangeArray($data);

                // On parse les données pour avoir un tableau des filtres :
                if (!empty($filters)) {
                    foreach (explode(',', $filters) as $filter) {
                        list($name, $value) = explode('=', $filter);
                        $flatCamping->filters["filter_$name"] = $value;
                    }
                }
                if (!empty($filtersAggregate)) {
                    foreach (explode(',', $filtersAggregate) as $filter) {
                        list($name, $value) = explode('=', $filter);
                        $flatCamping->filters["filter_$name"] = $value;
                    }
                }

                // Même chose pour les thématiques que pour les filtres
                if (!empty($thematics)) {
                    foreach (explode(',', $thematics) as $thematic) {
                        list($name, $value) = explode('=', $thematic);
                        $flatCamping->thematics["thematic_$name"] = $value;
                    }
                }

                // On prepare la requête pour minimiser le temps d'update
                $this->prepareUpdate($flatCamping);
            }
        }
        // Exécution de la requête à coup de REPLACE
        return $this->executeUpdate();
    }

    /**
     * Supprime les campings inexistants ou non publiés de la table à plat
     * @return int Le nombre de ligne supprimé
     */
    public function deleteOutdatedDatas()
    {
        $sql = "SELECT fc.camping_id
                FROM {$this->tableName} fc
                LEFT JOIN camping
                    ON fc.camping_id=camping.id
                WHERE camping.id IS NULL
                    OR camping.status=0";
        $resultSet = $this->adapter->query($sql)
                                   ->execute();
        foreach ($resultSet as $data) {
            $this->delete($data['camping_id']);
        }
        return $resultSet->count();
    }

    /**
     * Supprime une ligne en fonction du Camping ID
     * @param integer $campingId
     */
    public function delete($campingId)
    {
        $sql = "DELETE FROM {$this->tableName} WHERE camping_id=$campingId";
        $resultSet = $this->adapter->query($sql)
                                   ->execute();
    }

    /**
     * Renomme la table
     * @param string $newTableName
     */
    public function renameTable($newTableName)
    {
        $sql = "RENAME TABLE {$this->tableName} TO $newTableName";
        $this->adapter->query($sql)
             ->execute();
    }

    /**
     * Vérifie si la structure de la table est synchronisée en fonction des filtres ID et des thématiques ID
     * @return bool Renvoi TRUE si la table n'est pas à jour ou inexistante, FALSE sinon.
     */
    public function isOutdatedTable()
    {
        if ($this->isExist() === false) {
            return true;
        }
        $sql = "DESCRIBE {$this->tableName}";
        $columnList = $this->adapter->query($sql)->execute();
        $filterIds = array();
        $thematicIds = array();
        foreach ($columnList as $column) {
            if (stristr($column['Field'], 'filter_')) {
                $filterIds[] = substr($column['Field'], 7);
            } else if (stristr($column['Field'], 'thematic_')) {
                $thematicIds[] = substr($column['Field'], 9);
            }
        }
        $filtersDiff = array_diff($this->filterIds, $filterIds);
        $thematicsDiff = array_diff($this->thematicIds, $thematicIds);
        if (empty($filtersDiff) && empty($thematicsDiff))
            return false;
        return true;
    }

    /**
     * Prépare la requête pour l'insertion de données via REPLACE
     * @param FlatCamping $flatCamping
     */
    public function prepareUpdate(FlatCamping $flatCamping)
    {
        $fieldValues = array(
            $flatCamping->campingId,
            $flatCamping->isOurSelection,
            $flatCamping->isFavorite,
            $flatCamping->priority,
            is_null($flatCamping->coordLatitude) ? 'NULL' : $flatCamping->coordLatitude,
            is_null($flatCamping->coordLongitude) ? 'NULL' : $flatCamping->coordLongitude,
            is_null($flatCamping->cityCoordLatitude) ? 'NULL' : $flatCamping->cityCoordLatitude,
            is_null($flatCamping->cityCoordLongitude) ? 'NULL' : $flatCamping->cityCoordLongitude,
            $flatCamping->cityId,
            $flatCamping->departmentId,
            $flatCamping->regionId
        );
        foreach ($this->languageList as $lang) {
            $name = "geo_order_$lang";
            $fieldValues[] = (empty($flatCamping->$name)) ? "''" : "'" . str_replace("'", "\'", $flatCamping->$name) . "'";
        }

        foreach ($this->filterIds as $key => $filterId) {
            if (!empty($flatCamping->filters["filter_$filterId"])) {
                $fieldValues[] = $flatCamping->filters["filter_$filterId"];
            } else {
                $fieldValues[] = 'NULL';
            }
        }

        foreach ($this->thematicIds as $thematicId) {
            if (!empty($flatCamping->thematics["thematic_$thematicId"])) {
                $fieldValues[] = $flatCamping->thematics["thematic_$thematicId"];
            } else {
                $fieldValues[] = 'NULL';
            }
        }

        $this->updateQueries[] = '(' . implode(', ', $fieldValues) . ')';

    }

    /**
     * Exécute la requête précédément créée. Insert des données par tranche de 200
     * @param integer $chunk [optional] Le nombre de ligne à insérer par requête
     * @return int
     */
    public function executeUpdate($chunk = 2)
    {
        if (empty($this->updateQueries)) {
            return 0;
        }
        $fields = array(
            'camping_id',
            '_is_our_selection',
            '_is_favorite',
            '_priority',
            'coord_latitude',
            'coord_longitude',
            'city_coord_latitude',
            'city_coord_longitude',
            'city_id',
            'department_id',
            'region_id'
        );
        foreach ($this->languageList as $lang) {
            $fields[] = "geo_order_$lang";
        }

        foreach ($this->filterIds as $filterId) {
            $fields[] = "filter_$filterId";
        }
        foreach ($this->thematicIds as $thematicId) {
            $fields[] = "thematic_$thematicId";
        }
        $nbAffectedRows = 0;
        $headerSql = "REPLACE INTO {$this->tableName}(" . implode(', ', $fields) . ") VALUES ";
        foreach (array_chunk($this->updateQueries, $chunk) as $subArray) {
            $sql = $headerSql . implode(', ', $subArray);
            $resultSet = $this->adapter->query($sql)
                ->execute();
            $nbAffectedRows += $resultSet->count();
        }
        // On divise par 2 car le count renvoie le double du nombre de ligne traité.
        // C'est peut être le REPLACE qui fais un double traitement, va savoir..., à chercher pour les curieux
        // Moi j'ai la flemme...
        return $nbAffectedRows / 2;
    }

    /**
     * Exécute une requête DDL
     * @param \Zend\Db\Sql\Ddl $ddl
     */
    private function executeStatement($ddl)
    {
        $adapter = $this->adapter;
        $this->adapter->query(
            $this->sql->getSqlStringForSqlObject($ddl),
            $adapter::QUERY_MODE_EXECUTE
        );
    }

    public function updateBasicInfos($campingId)
    {
        $sql = "UPDATE _flat_camping fc
                SET fc._priority=(
                  SELECT _priority FROM camping WHERE id=$campingId
                ),
                fc._is_our_selection=(
                  SELECT _is_our_selection FROM camping WHERE id=$campingId
                ),
                fc._is_favorite=(
                  SELECT _is_favorite FROM camping WHERE id=$campingId
                )
                WHERE fc.camping_id=$campingId";
        $this->adapter->query($sql)
            ->execute();
    }
}