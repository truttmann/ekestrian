<?php
    namespace RepriseBatch\Model;

    use Zend\Db\TableGateway\TableGateway;
    use Zend\Db\Sql\Expression;
    use Zend\Db\Sql\Select;

    class CampingTable
    {
        protected $tableGateway;

        public function __construct(TableGateway $tableGateway)
        {
            $this->tableGateway = $tableGateway;
        }

        /**
        * list all object
        * @return resultSet
        */
        public function fetchAll()
        {
            $resultSet = $this->tableGateway->select();
            return $resultSet;
        }

        /**
        * count all object
        * @return resultSet
        */
        public function count()
        {
            $rowset = $this->tableGateway->select(function (Select $select) {
                $select->columns(array('requestCount' => new Expression("count('')")));
            });
            /*if (!$row) {
                throw new \Exception("Could not find row $id");
            }*/
            $res = $rowset->getDataSource()->next();
            if(! is_array($res) || count($res) !=1 || !array_key_exists('requestCount', $res)) {
                throw new \Exception("Unable to count data");            
            }

            return $res['requestCount'];
        }



        /**
        * find pays by ref
        * @param int $ref reference which is used for search
        * @return resultSet
        */
        public function getByRef($ref)
        {
            $resultSet = $this->tableGateway->select(array('reference' => $ref));
            return $resultSet;
        }

        /**
        *   find Camping by Id
        *   @param int id id of searched object
        */
        public function getCamping($id){
            $id  = (int) $id;
            $rowset = $this->tableGateway->select(array('id' => $id));
            $row = $rowset->current();
            if (!$row) {
                throw new \Exception("Could not find row $id");
            }
            return $row;
        }

        /**
        * list object using limit
        * @param  int       $start
        * @param  int       $end
        * @return resultSet
        */
        public function fetchLimit($offset, $start=null)
        {
            if($start === null || !is_int($start)) {
                throw new \Exception("Invalid start value: $start");            
            }
            $resultSet = $this->tableGateway->select(function (Select $select) use ($start,$offset) {
                //$select->where->like('name', 'Brit%');
                //$select->order('name ASC')->limit(2);
                if($offset != null) {
                    $select->order('id')->limit($start)->offset($offset);
                } else {
                    $select->order('id')->limit($start);
                }
            });
            return $resultSet;
        }

        /**
        * list object translate for insert into index_search using limit
        * @param  int       $start
        * @param  int       $end
        * @return resultSet
        */
        public function getDataForIndexSeachLimit($offset, $start=null, $asArray = false)
        {
            $return = null;
            if($start === null || !is_int($start)) {
                throw new \Exception("Invalid start value: $start");            
            }
            $resultSet = $this->tableGateway->select(function (Select $select) use ($start,$offset) {
                //$select->where->like('name', 'Brit%');
                //$select->order('name ASC')->limit(2);
                $select->join('camping_i18n', 'camping.id = camping_i18n.id', 'label', 'left');
                $select->join('language', 'language.code = camping_i18n.code', 'code', 'inner');
                if($offset != null) {
                    $select->order('id')->limit($start)->offset($offset);
                } else {
                    $select->order('id')->limit($start);
                }
            });
            if($asArray == true){
                foreach ($resultSet->getDataSource() as $key => $value) {
                    $return[] = $value;
                }
            } else {
                $return = $resultSet;
            }
            return $return;
        }

        /**
        * function to save object in database
        * @param  Camping $camping object to save
        * @return Camping
        * @throws \Exception 
        */
        public function save(Camping $camping)
        {
            $data = array(
                "reference" => $camping->reference,
                "name" => $camping->name,
                "access" => $camping->access,
                "address" => $camping->address,
                "address2" => $camping->address2,
                // "url_default" => $camping->url_default,
                "media_id" => $camping->media_id,
                "nbr_places" => $camping->nbr_places,
                "is_logos_accepted" => $camping->is_logos_accepted,
                "_is_favorite" => $camping->_is_favorite,
                "_is_our_selection" => $camping->_is_our_selection,
                "_priority" => $camping->_priority,
                "zip_code" => $camping->zip_code,
                // "phone_number" => $camping->phone_number,
                // "phone_number2" => $camping->phone_number2,
                // "fax" => $camping->fax,
                "email" => $camping->email,
                // "price_list_year" => ($camping->price_list_year != null ) ? $camping->price_list_year : date('Y'),
                "rating" => $camping->rating,
                "coord_latitude" => $camping->coord_latitude,
                "coord_longitude" => $camping->coord_longitude,
                "city_id" => $camping->city_id,
                "status" => $camping->status,
            );

            $id = (int)$camping->id;
            if ($id == 0) {
                $data["created_at"] = date('Y-m-d H:i:s');
                $this->tableGateway->insert($data);
                $camping->id = $this->tableGateway->getLastInsertValue();
            } else {
                if ($this->getCamping($id)) {
                    $data["updated_at"] = date('Y-m-d H:i:s');
                    $this->tableGateway->update($data, array('id' => $id));
                } else {
                    throw new \Exception('Form id does not exist');
                }
            }
            return $camping;
        }

        /**
        * function to check if the camping is correctly link to thematic
        * @param  int $camping_id  id of searched object
        * @param  int $thematic_id id of thematic
        * @return void
        */
        public function saveCampingThematicContractLink($camping_id, $thematic_id) 
        {
            return $this->tableGateway->getAdapter()
                    ->createStatement('INSERT INTO camping_thematic(camping_id, thematic_id, is_contract, is_manual) VALUES (?, ?, 1, 0) ON DUPLICATE KEY UPDATE is_contract=1')
                    ->execute(array($camping_id, $thematic_id));
        }

        /**
        * function to check if the camping is correctly link to thematic
        * @param  int   $camping_id    id of searched object
        * @param  array $arrayThematic list of id of thematic
        * @return void
        */
        public function removeCampingThematicContractLink($camping_id, array $arrayThematic) 
        {
            $adapter = $this->tableGateway->getAdapter(); 
            $stmt = $adapter->createStatement(); 
            if(count($arrayThematic) > 0) {
                $stmt->prepare('UPDATE camping_thematic set is_contract = 0 WHERE camping_id = ? AND thematic_id NOT IN ('.implode(',', $arrayThematic).')');
                $stmt->execute(array($camping_id));
            } else {
                $stmt->prepare('UPDATE camping_thematic set is_contract = 0 WHERE camping_id = ?');
                $stmt->execute(array($camping_id));
            }
            $stmt = $adapter->createStatement(); 
            $stmt->prepare('DELETE FROM camping_thematic WHERE is_contract = 0 AND is_manual = 0 AND camping_id = ?');
            $stmt->execute(array($camping_id));
        }

        /**
        * function to check if the camping is correctly link to thematic
        * @return void
        */
        public function checkThematicLink() 
        {
            $adapter = $this->tableGateway->getAdapter(); 
            $stmt = $adapter->createStatement(); 
            $stmt->prepare('CALL compute_camping_thematic;');
            $stmt->execute(); 
        }

        /**
        * function witch retrieve camping i18n information 
        * @param  int $campingId id of searched object
        * @return resultSet
        */
        public function getCampingI18nInfo($campingId) {
            $adapter = $this->tableGateway->getAdapter(); 
            $Campingi18n = new TableGateway('camping_i18n', $adapter); 
            $resultSet = $Campingi18n->select(array("id" => $campingId));
            return $resultSet;
        }

        /**
        * function witch delete all camping_i18n information for this camping
        * @param  int    $campingId camping identifier
        * @param  string $langue    language used
        * @return void
        */
        public function deletei18nInformation($campingId, $langue) {
            $adapter = $this->tableGateway->getAdapter(); 
            $Campingi18n = new TableGateway('camping_i18n', $adapter); 
            $Campingi18n->delete('id = '.$campingId.(($langue != null)?(' AND LOWER(code) = \''.$langue).'\'':''));
        }

        /**
        * function witch update all camping_i18n information for this camping
        * @param  int    $campingId camping identifier
        * @param  object $object    camping_i18n object
        * @return void
        */
        public function updatei18nInformation($campingId, $object) {
            $adapter = $this->tableGateway->getAdapter(); 
            $Campingi18n = new TableGateway('camping_i18n', $adapter); 
            $data = array(
                "_short_description" => $object->_short_description,
                "_long_description" => $object->_long_description,
                "_meta_description" => $object->_meta_description,
                "_url_reservation" => $object->_url_reservation,
                "_urls_data" => $object->_urls_data,
                "_logos_data" => $object->_logos_data,
                "_url_favorite" => $object->_url_favorite,
                "_url_selection" => $object->_url_selection,
            );
            $Campingi18n->update($data, array("id" => $object->id,"code" => $object->code ));
        }

        /**
        * function witch update all camping_i18n information for this camping
        * @param  int    $campingId camping identifier
        * @param  object $object    camping_i18n object
        * @return void
        */
        public function inserti18nInformation($campingId, $object) {
            $adapter = $this->tableGateway->getAdapter(); 
            $Campingi18n = new TableGateway('camping_i18n', $adapter);
            $data = array(
                "id" => $object->id,
                "code" => $object->code,
                "_short_description" => $object->_short_description,
                "_long_description" => $object->_long_description,
                "_meta_description" => (isset($object->_meta_description)) ? $object->_meta_description : null,
                "_url_reservation" => $object->_url_reservation,
                "_urls_data" => $object->_urls_data,
                "_logos_data" => $object->_logos_data,
                "_url_favorite" => $object->_url_favorite,
                "_url_selection" => $object->_url_selection,
            );
            $Campingi18n->insert($data);
        } 

        /**
        *   fucntion witch retrieve logo information for this camping
        * @param  int    $campingId camping identifier
        * @return resultSet
        */
        public function getCampingLogoActifInformation($campingId) {
            $adapter = $this->tableGateway->getAdapter(); 
            $logo = new TableGateway('logo', $adapter); 
            $resultSet = $logo->select(function (Select $select) use($campingId){
                $select->join("logo_i18n", "logo.id = logo_i18n.id", array("path", "teaser_long", "teaser_small", "code"), "left");
                $select->join("logo_url", "logo.id = logo_url.id", array("data", "url_default"), "left");
                $select->where(" (logo_url.blacklist = 0 OR logo_url.blacklist IS NULL) AND logo_url.camping_id = ".$campingId." AND logo.status = 1");
            });
            return $resultSet;
        } 

        /**
        * function to active a photo
        * @param  int  $campingId camping identifier
        * @param  int  $nbr_photo list of camping identifier
        * @return void
        */
        public function activeDesactivePhoto($campingId, $nbr_photo) {
            $adapter = $this->tableGateway->getAdapter(); 
            $media = new TableGateway('media', $adapter); 

            $where = " camping_id = $campingId AND type_media_id = ".TYPE_MEDIA_PHOTO;

            // desactivation de toutes les photos du camping
            $media->update(array("status" => 0), $where);

            if($nbr_photo != NULL && is_numeric($nbr_photo)) {
                $where .= " ORDER BY ordering LIMIT $nbr_photo";
                $media->update(array("status" => 1), $where);
            }
        }

        /**
         * function to update camping main photo
         * @param  int  $campingId
         * @return void
         */
        public function updateMainCampingPhoto($campingId){
            $adapter = $this->tableGateway->getAdapter();
            $media = new TableGateway('media', $adapter);
            $rowset = $media->select(function (Select $select) use($campingId){
                $select->where("media.camping_id = ".$campingId." AND media.status = 1 AND media.path IS NOT NULL AND media.path != ''");
                $select->order('ordering')->limit(1);
            });
            $row = $rowset->current();
            if (!$row) {
                $this->tableGateway->update(array("media_id"=>null), array('id' => $campingId));
            } else {
                $this->tableGateway->update(array("media_id"=>$row->id), array('id' => $campingId));
            }
            return;
        }

        /**
        * function to active a video
        * @param  int   $campingId camping identifier
        * @param  bool $hasVideo  array of videos'ids
        * @return void
        */
        public function activeDesactiveVideo($campingId, $hasVideo) {
            $adapter = $this->tableGateway->getAdapter(); 
            $media = new TableGateway('media', $adapter); 

            $where = " camping_id = $campingId AND type_media_id = ".TYPE_MEDIA_VIDEO;

            if($hasVideo != true){
                // desactivation de toutes les photos du camping
                $media->update(array("status" => 0), $where);
            } else {
                // activation de toutes les photos du camping
                $media->update(array("status" => 1), $where);
            }
        }

        /**
        * function witch update all camping priority
        * @return void
        */
        public function updatePriority($campingId = null){
            $adapter = $this->tableGateway->getAdapter(); 
            
            $sql = "
                update camping set _priority =
                    (
                        select if(count(*)>0, 1, 3)
                        from contract
                        inner join package_products pkg on pkg.id=contract.package_products_id
                        inner join package_products_product pp on pp.package_products_id=pkg.id
                        where contract.camping_id=camping.id
                        and CURRENT_TIMESTAMP BETWEEN contract.date_start AND contract.date_end
                        and pp.produit_id=16 -- produit 'Premier dans la liste'
                    )
                    +
                    (
                        select if(count(*)>0, 1, 0)
                        from camping_caracteristic cc
                        where cc.camping_id=camping.id and cc.caracteristic_id=378 -- RURAL
                    )
            ";
            if ($campingId) {
                $sql .= ' WHERE camping.id=' . $campingId;
            }
            $statement = $adapter->query($sql);
            $statement->execute();

            $sql = "update _index_search set _priority =  (select _priority FROM camping WHERE id = data_id) WHERE table_name = 'camping';";
            $statement = $adapter->query($sql);
            $statement->execute();
        }

        public function updateMainMedia($campingId)
        {
            $adapter = $this->tableGateway->getAdapter();
            $sql = "UPDATE camping SET media_id=
                    (SELECT id FROM media WHERE camping_id=$campingId AND media.type_media_id=2 ORDER BY ordering LIMIT 1)
                    WHERE camping.id=$campingId";
            $statement = $adapter->query($sql);
            $statement->execute();
        }

        /**
        * find filter for this camping
        *
        */
        public function findFilter() {

        }

        /**
        * fonction qui va mettre à jour la table _opening_date
        * @param  int   $campingId camping identifier
        * @return void
        */
        public function updateOpeningDate($campingId) {
            $adapter = $this->tableGateway->getAdapter(); 
            $media = new TableGateway('_opening_date', $adapter); 

            $sql = "
                DELETE
                FROM _opening_date
                ".(($campingId != "" && $campingId != null)?(" WHERE camping_id = ".$campingId):"").";";
            $statement = $adapter->query($sql);
            $statement->execute();

            $sql = "  
                INSERT INTO _opening_date(camping_id, start_at, end_at)
                SELECT 
                    camping_id, 
                    CONCAT(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv1, '/', 1)), '.', -1) , IF(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv1, '/', 1)), '.', 1) < 10, CONCAT('0',SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv1, '/', 1)), '.', 1)),SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv1, '/', 1)), '.', 1))) as date_debut, 
                    CONCAT(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv1, '/', -1)),'.' , -1) , IF(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv1, '/', -1)), '.', 1) < 10, CONCAT('0',SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv1, '/', -1)), '.', 1)),SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv1, '/', -1)), '.', 1))) as date_fin
                FROM camping_caracteristic
                INNER JOIN caracteristic ON camping_caracteristic.caracteristic_id = caracteristic.id
                WHERE xml_tag IN ('OUVAIRE','OUVCAMP','OUVLOC')
                AND ouv1 != '' AND ouv1 IS NOT NULL
                AND (ouv1  REGEXP '^[ ]{0,1}[0-9]{1,2}\.[0-9]{1,2}[ ]{0,1}\/[ ]{0,1}[0-9]{1,2}\.[0-9]{1,2}[ ]{0,1}$') = 1
                ".(($campingId != "" && $campingId != null)?(" AND camping_id = ".$campingId):"")."

                UNION 

                SELECT 
                    camping_id, 
                    CONCAT(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv2, '/', 1)), '.', -1) , IF(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv2, '/', 1)), '.', 1) < 10, CONCAT('0',SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv2, '/', 1)), '.', 1)),SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv2, '/', 1)), '.', 1))) as date_debut, 
                    CONCAT(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv2, '/', -1)),'.' , -1) , IF(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv2, '/', -1)), '.', 1) < 10, CONCAT('0',SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv2, '/', -1)), '.', 1)),SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv2, '/', -1)), '.', 1))) as date_fin
                FROM camping_caracteristic
                INNER JOIN caracteristic ON camping_caracteristic.caracteristic_id = caracteristic.id
                WHERE xml_tag IN ('OUVAIRE','OUVCAMP','OUVLOC')
                AND ouv2 != '' AND ouv2 IS NOT NULL
                AND (ouv2  REGEXP '^[ ]{0,1}[0-9]{1,2}\.[0-9]{1,2}[ ]{0,1}\/[ ]{0,1}[0-9]{1,2}\.[0-9]{1,2}[ ]{0,1}$') = 1
                ".(($campingId != "" && $campingId != null)?(" AND camping_id = ".$campingId):"")."

                UNION 

                SELECT 
                    camping_id, 
                    CONCAT(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv3, '/', 1)), '.', -1) , IF(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv3, '/', 1)), '.', 1) < 10, CONCAT('0',SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv3, '/', 1)), '.', 1)),SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv3, '/', 1)), '.', 1))) as date_debut, 
                    CONCAT(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv3, '/', -1)),'.' , -1) , IF(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv3, '/', -1)), '.', 1) < 10, CONCAT('0',SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv3, '/', -1)), '.', 1)),SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv3, '/', -1)), '.', 1))) as date_fin
                FROM camping_caracteristic
                INNER JOIN caracteristic ON camping_caracteristic.caracteristic_id = caracteristic.id
                WHERE xml_tag IN ('OUVAIRE','OUVCAMP','OUVLOC')
                AND ouv3 != '' AND ouv3 IS NOT NULL
                AND (ouv3  REGEXP '^[ ]{0,1}[0-9]{1,2}\.[0-9]{1,2}[ ]{0,1}\/[ ]{0,1}[0-9]{1,2}\.[0-9]{1,2}[ ]{0,1}$') = 1
                ".(($campingId != "" && $campingId != null)?(" AND camping_id = ".$campingId):"")."

                UNION 

                SELECT 
                    camping_id, 
                    CONCAT(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv4, '/', 1)), '.', -1) , IF(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv4, '/', 1)), '.', 1) < 10, CONCAT('0',SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv4, '/', 1)), '.', 1)),SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv4, '/', 1)), '.', 1))) as date_debut, 
                    CONCAT(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv4, '/', -1)),'.' , -1) , IF(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv4, '/', -1)), '.', 1) < 10, CONCAT('0',SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv4, '/', -1)), '.', 1)),SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(ouv4, '/', -1)), '.', 1))) as date_fin
                FROM camping_caracteristic
                INNER JOIN caracteristic ON camping_caracteristic.caracteristic_id = caracteristic.id
                WHERE xml_tag IN ('OUVAIRE','OUVCAMP','OUVLOC')
                AND ouv4 != '' AND ouv4 IS NOT NULL
                AND (ouv4  REGEXP '^[ ]{0,1}[0-9]{1,2}\.[0-9]{1,2}[ ]{0,1}\/[ ]{0,1}[0-9]{1,2}\.[0-9]{1,2}[ ]{0,1}$') = 1
                ".(($campingId != "" && $campingId != null)?(" AND camping_id = ".$campingId):"").";";

            $statement = $adapter->query($sql);
            $statement->execute();
        }


        /**
         * Utilisation exclusivement le champ EMAIL1 pour mettre a jour le champs email de camping
         * @param  int  $campingId
         * @return void
         * @throw  \Exception
         */
        public function updateCampingMail($campingId) {
            $current_camping = $this->getCamping($campingId);
            $adapter = $this->tableGateway->getAdapter();
            $camping_cara = new TableGateway('camping_caracteristic', $adapter);
            $resultSet = $camping_cara->select(function (Select $select) use($campingId){
                $select->join("caracteristic", "caracteristic.id = camping_caracteristic.caracteristic_id", array(), "inner");
                $select->where(" caracteristic.xml_tag = 'EMAIL1' AND camping_caracteristic.camping_id = ".$campingId);
            });
            $tab = $resultSet->toArray();
            if(is_array($tab) && count($tab) > 0) {
                $current_camping->email = $tab[0]['val'];
            } else {
                $current_camping->email = null;
            }
            $this->save($current_camping);
            return;
        }
    }
