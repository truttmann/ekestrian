<?php

namespace RepriseBatch\Controller;

use Zend\Mvc\Application;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\I18n\Translator\Translator;
use Application\City\Model\CityTable;
use Application\Department\Model\DepartmentTable;
use Application\Region\Model\RegionTable;
use Application\Thematic\Model\ThematicTable;
use Application\Camping\Model\CampingTable;
use Application\Caracteristic\Model\CaracteristicTable;
use RepriseBatch\Utils;
use Zend\View\HelperPluginManager;

require_once __DIR__ . '/../utils/Util.php';

class CampingController extends AbstractActionController
{
    /**
     * @var CityTable
     */
    public $languageTable;

    /**
     * @var CityTable
     */
    public $cityTable;

    /**
     * @var DepartmentTable
     */
    public $departmentTable;

    /**
     * @var RegionTable
     */
    public $regionTable;

    /**
     * @var ThematicTable
     */
    public $thematicTable;

    /**
     * @var CampingTable
     */
    public $campingTable;

    /**
     * @var CaracteristicTable
     */
    public $caracteristicTable;

    /**
     * @var Translator
     */
    public $translator;

    public function init()
    {
        $this->languageTable = $this->getServiceLocator()->get('Application\Language\Model\LanguageTable');
        $this->cityTable = $this->getServiceLocator()->get('Application\City\Model\CityTable');
        $this->departmentTable = $this->getServiceLocator()->get('Application\Department\Model\DepartmentTable');
        $this->regionTable = $this->getServiceLocator()->get('Application\Region\Model\RegionTable');
        $this->thematicTable = $this->getServiceLocator()->get('Application\Thematic\Model\ThematicTable');
        $this->campingTable = $this->getServiceLocator()->get('Application\Camping\Model\CampingTable');
        $this->caracteristicTable = $this->getServiceLocator()->get('Application\Caracteristic\Model\CaracteristicTable');
        $this->translator = $this->getServiceLocator()->get('translator');
        $this->translator->addTranslationFile('phparray', './module/RepriseBatch/language/en_UK.campings.php', null, 'uk');
        $this->translator->addTranslationFile('phparray', './module/RepriseBatch/language/es_ES.campings.php', null, 'es');
        $this->translator->addTranslationFile('phparray', './module/RepriseBatch/language/nl_NL.campings.php', null, 'nl');
        $this->translator->addTranslationFile('phparray', './module/RepriseBatch/language/de_DE.campings.php', null, 'de');
    }

    public function loadAction()
    {
        header('Content-Type: text/html; charset=utf-8');
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $this->init();

        $forced = $request->getParam('forced');

        /*if ($forced) {
            echo " [  \033[0;36m0%\033[0m] Flushing descriptions fields\r";
            $this->cityTable->flushAllDescriptions();
        }
        */

        $languagesList = array();
        foreach ($this->languageTable->fetchAll() as $language) {
            $languagesList[] = $language->code;
        }
        $campingPaginator = $this->campingTable->fetchAllToGenerateDescription();
        $nbrCampings = $campingPaginator->getTotalItemCount();
        $campingPaginator->setItemCountPerPage($nbrCampings);
        $i = 0;
        $campings = array();
        foreach ($campingPaginator as $camping) {
            foreach ($languagesList as $language) {
                    $campings[] = $this->generateCampingDescription(clone $camping, $language);
                }
            if (count($campings) > 200) {
                $this->campingTable->massUpdate($campings);
                $campings = array();
            }

            $i++;
            $percent = str_pad((int)(($i * 100 )/ $nbrCampings), 3, ' ', STR_PAD_LEFT);
            echo " [\033[0;36m$percent%\033[0m][$i] Generating description of the camping {$camping->name}                                              \r";
        }
        $this->campingTable->massUpdate($campings);
        echo "[ \033[0;32mok\033[0m ] Campings description generation completed                                              \r\n";
    }

    public function generateCampingDescription($camping, $languageCode = 'fr')
    {
        $this->translator->setLocale($languageCode);

        $city = $this->cityTable->fetchOne($camping->cityId, $languageCode);
        $department = $this->departmentTable->fetchOne($city->departmentId, $languageCode);
        $region =  $this->regionTable->fetchOne($department->regionId, $languageCode);

        $touristPlace = $this->thematicTable->getTouristPlaceByCamping($camping, $languageCode);
        if ($touristPlace === false) {
            $geoLocSentence = $region->shortDescription;
        } else {
            $geoLocSentence = $touristPlace->description;
        }

        $caracteristicsList = $this->caracteristicTable->fetchAllByCamping($camping->id, $languageCode);

        $caracGeo = array();
        foreach ($this->caracteristicTable->fetchAllByGroupCaracteristic(10, $languageCode) as $caracteristic) {
            $caracGeo[$caracteristic->xmlTag] = $caracteristic;
        }

        $caracs = array();
        $isAccessBeach = false;
        $nbLocations = null;
        $nbEmplacements = null;
        $isPlaces = false;
        $typeLocations = array();
        $typePoolsNonAD = array();
        $typePools = array();
        $typeSoins = array();
        $typeSports = array();
        $typeSportsProx = array();
        $nbBassins = null;
        $nbBassins2 = null;
        $hasToboggan = false;
        $hasSpasoins = false;
        $actiSports = array();
        $actiSportsProx = array();
        $anim = array();
        $animChild = array();
        $campingCar = array();
        $borne = null;
        $isCCPassage = false;
        $isAnimalsOK = false;
        $hasDates = false;
        foreach ($caracteristicsList as $caracteristic) {
            if (isset($caracGeo[$caracteristic->xmlTag]) && !empty($caracGeo[$caracteristic->xmlTag]->prefixLocation)) {
                $carac = null;
                if ($caracteristic->isAccessDirect != true && $caracteristic->distance != null) {
                    $carac = vksprintf($this->translator->translate('à %distance$s', null), array('distance' => $caracteristic->distance)) . ' ';
                } else if ($caracteristic->isAccessDirect != true && $caracteristic->distance == null) {
                    $carac = $this->translator->translate('à quelques kilomètres', null) .' ';
                }
                $carac .= "{$caracGeo[$caracteristic->xmlTag]->prefixLocation}" . "{$caracteristic->label}";
                if (!empty($caracteristic->nom)) {
                    $carac .= ' ' . $caracteristic->nom;
                }
                $caracs[] = $carac;
            }
            if ($caracteristic->xmlTag == 'PLAGE' && $caracteristic->isAccessDirect == true) {
                $isAccessBeach = true;
            }
            if ($caracteristic->xmlTag == 'NBLOCTOT') {
                $nbLocations = $caracteristic->val;
            }
            if($caracteristic->xmlTag == 'NBTOTEMPL') {
                $nbEmplacements = $caracteristic->val;
            }
            if ($caracteristic->xmlTag == 'NBEMPLNUS') {
                $isPlaces = true;
            }
            if (in_array($caracteristic->xmlTag, array('LOCMOBIL', 'LOCCHALET', 'LOCBUN', 'LOCBUNTOIL', 'LOCTENTE', 'LOCAUTRE'))) {
                $typeLocations[] = $caracteristic->labelSup;
            }
            if (in_array($caracteristic->xmlTag, array('PISCCHAUF', 'PISCCOUV', 'PISCDECOUV', 'PISPLENAIR'))) {
                if($caracteristic->ad == true) {
                    $typePools[] = $caracteristic->label;
                } else {
                    $typePoolsNonAD[] = [$caracteristic->label, $caracteristic->distance];
                }
            }
            if ($caracteristic->xmlTag == 'NBBASSINS2') {
                $nbBassins2 = $caracteristic->remarque;
            }
            if ($caracteristic->xmlTag == 'NBBASSINS') {
                $nbBassins = $caracteristic->remarque;
            }
            if ($caracteristic->xmlTag == 'TOBOGAQUA') {
                $hasToboggan = $caracteristic->isAccessDirect;
            }
            if (in_array($caracteristic->xmlTag, array('SOLARIUM', 'HAMMAM', 'SAUNA', 'JACUZZI'))) {
                $typeSoins[] = $caracteristic->prefixDe . $caracteristic->label;
            }
            if ($caracteristic->xmlTag == 'SPASOINS') {
                $hasSpasoins = true;
            }
            if (in_array($caracteristic->xmlTag, array('SOUSMARINE', 'SPORTSVIVE', 'JETSKI', 'KAYAK', 'PEDALOS', 'PLAVOILE', 'RAFTING', 'SKINAUTIQUE', 'EQUITATION', 'RANDPEDEST', 'CAVALIERR', 'PARAPENTE')) && $caracteristic->isAccessDirect) {
                $typeSports[] = $caracteristic->prefixDe . $caracteristic->label;
            } else if (in_array($caracteristic->xmlTag, array('SOUSMARINE', 'SPORTSVIVE', 'JETSKI', 'KAYAK', 'PEDALOS', 'PLAVOILE', 'RAFTING', 'SKINAUTIQUE', 'EQUITATION', 'RANDPEDEST', 'CAVALIERR', 'PARAPENTE')) && $caracteristic->distance) {
                $typeSportsProx[] = "{$caracteristic->prefixDe}{$caracteristic->label} ({$caracteristic->distance})";
            }
            if (in_array($caracteristic->xmlTag, array('GOLF', 'MINIGOLF', 'TENNIS', 'BASKET', 'VOLLEY', 'TIRALARC', 'FITNESS', 'HALFCOURT', 'PINGPONG', 'VTT', 'RANDEQUEST')) && $caracteristic->isAccessDirect) {
                $actiSports[] = $caracteristic->prefixDe . $caracteristic->label;
            } else if (in_array($caracteristic->xmlTag, array('GOLF', 'MINIGOLF', 'TENNIS', 'BASKET', 'VOLLEY', 'TIRALARC', 'FITNESS', 'HALFCOURT', 'PINGPONG', 'VTT', 'RANDEQUEST')) && $caracteristic->distance) {
                $actiSportsProx[] = $caracteristic->prefixDe . $caracteristic->label;
            }
            if (in_array($caracteristic->xmlTag, array('ANIM', 'SOIRDANSE', 'SOIREANIM', 'SPECTACLE', 'DISCO')) && $caracteristic->isAccessDirect) {
                $anim[] = $caracteristic->prefixDe . $caracteristic->label;
            }
            if (in_array($caracteristic->xmlTag, array('CLUBENF', 'ANIMENF', 'GARDERIE')) && $caracteristic->isAccessDirect) {
                $animChild[] = $caracteristic->prefixDe . $caracteristic->label;
            }
            if (in_array($caracteristic->xmlTag, array('AIREEXT', 'AIREINTER')) && $camping->rating < 4) {
                $campingCar[] = $caracteristic->labelSup;
            }
            if (in_array($caracteristic->xmlTag, array('EURORELAIS', 'ARTISAN', 'FLOTBLEU', 'PIV', 'SANISTATIO', 'URBAFLUX')) && $camping->rating < 4) {
                $borne = $caracteristic->label;
            }
            if ($caracteristic->xmlTag == 'CCPASSAGE') {
                $isCCPassage = true;
            }
            if (in_array($caracteristic->xmlTag, array('ANIMALEMP', 'ANIMALLOC')) && $camping->rating < 3) {
                $isAnimalsOK = true;
            }
            if (in_array($caracteristic->xmlTag, array('OUVCAMP', 'TARANCEMP', 'TARNOUVEMP', 'TARANCLOC', 'TARNOUVLOC', 'TARANCSUP', 'TARNOUVSUP')) && $camping->rating < 3) {
                $hasDates = true;
            }
        }

        $mainText = $this->translator->translate('Le camping %camping_label$s est situé', null) . ' %city_prefix_a$s%city_label$s ' .
            '%department_prefix_dans_le$s%department_label$s, %region_prefix_en$s%region_label$s, ' .
            '%geoloc$s%carac_location$s%beach$s' .
            '%rating$s' .
            '%pool$s' .
            '%sport$s' .
            '%anim$s' .
            '%borne$s' .
            '%animal$s';

        $metaText = $this->translator->translate('Descriptif complet du camping %camping_label$s %region_prefix_en$s%region_label$s : équipements, tarifs, services, loisirs.', null) . ' %beach$s' .
            '%rating$s' .
            '%pool$s' .
            '%sport$s' .
            '%anim$s' .
            '%borne$s' .
            '%animal$s';

        $caracLocation = '';
        if (count($caracs)) {
            $caracLocation = ' ' . $this->translator->translate('Il est situé', null) . ' ' . transformArrayToSentence($caracs);
        }

        $textBeach = null;
        if ($isAccessBeach && !empty($caracLocation)) {
            $textBeach = ' ' . $this->translator->translate('avec un accès direct à la plage', null) . '.';
        } else if ($isAccessBeach) {
            if (count($caracs)) {
                $textBeach = '. ';
            }
            $textBeach .= $this->translator->translate("Il dispose d'un accès direct à la plage", null);
        } else if (count($caracs)) {
            $textBeach = '. ';
        }

        $textRating = null;
        if ($camping->rating > 0) {
            $textRating = $this->translator->translate('Pour vos week-ends ou vacances en campings en France, découvrez ce camping', null) . ' ' . $camping->rating . ' ' . $this->translator->translatePlural('étoile', 'étoiles', $camping->rating, null);
            if ($nbLocations || $nbEmplacements) {
                $textRating .= ', ' . $this->translator->translate('qui vous propose', null) . ' %places$s%nb_locations$s%type_locations$s';
                $textRating = vksprintf($textRating, array(
                    'places' => ($nbEmplacements) ? $nbEmplacements.' '.$this->translator->translate('emplacements ou', null) . ' ' : '',
                    'nb_locations' => $nbLocations . ' ' . $this->translator->translate('locations', null),
                    'type_locations' => ''
                ));
            }
        } else if (strpos(strtolower($camping->name), 'aire naturelle') !== false) {
            $textRating = $this->translator->translate("Découvrez cette aire naturelle : ces terrains ont vocation à être implantés dans les espaces naturels notamment agricoles.\r\nLa superficie de l'aire ne peut pas excéder un hectare, il a été fixé 25 pour son nombre maximum d'emplacements. Elles sont équipées au minimum de points d'eau, de WC, de bacs à laver et de poubelles", null);
        } else if (strpos(strtolower(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $camping->name)), 'camping  la ferme') !== false) {
            $textRating = $this->translator->translate("Découvrez ce camping à la ferme : la capacité d'accueil de ces terrains ruraux / à la ferme est en général de 6 abris de camping ou 20 campeurs. Leur équipement varie du très simple au très confortable. Tous ces terrains, qu'ils soient gérés par des agriculteurs ou autres, font l'objet d’une déclaration en mairie qui fixe les dispositions prises pour l'entretien du terrain", null);
        }

        $textPools = null;
        if (count($typePools) > 0) {
            $textPools = $this->translator->translate('Pour vos loisirs aquatiques, vous bénéficiez sur place', null) . ' ';
            if ($nbBassins2 > 1) {
                $textPools .= $this->translator->translate('d\'un complexe aquatique avec', null) . ' %nb_bassins$s ' . $this->translator->translatePlural('bassin', 'bassins', $nbBassins2, null) . ' (%type_pools$s)%toboaqua$s';
            } else {
                $textPools .= $this->translator->translate('d\'une', null) . ' ' . strtolower($typePools[0]) . '%toboaqua$s';
            }
            $textTobo = null;
            if ($hasToboggan) {
                $textTobo = ' ' . $this->translator->translate('et vous amuserez dans les toboggans aquatiques du camping', null);
            }

            $textPools .= '.';
            if (count($typeSoins) == 0 && $hasSpasoins) {
                $textPools .= ' ' . $this->translator->translate("Profitez de l'espace bien-être du SPA.", null);
            } else if ($typeSoins) {
                $textPools .= ' ' . $this->translator->translate('Profitez', null) . ' %type_soins$s';
                if ($hasSpasoins) {
                    $textPools .= ' ' . $this->translator->translate("ainsi que de l'espace bien-être du SPA", null);
                }
                $textPools .= '.';
            }
            $textPools = vksprintf($textPools, array(
                'nb_bassins' => $nbBassins2,
                'type_pools' => transformArrayToSentence($typePools),
                'type_soins' => transformArrayToSentence($typeSoins),
                'toboaqua' => $textTobo
            ));
        }

        if (count($typePoolsNonAD) > 0) {
            $textPools = $this->translator->translate('Pour vos loisirs aquatiques, vous bénéficiez ', null) . ' ';
            if ($nbBassins2 > 1) {
                $textPools .= $this->translator->translate('d\'un complexe aquatique avec', null) . ' %nb_bassins$s ' . $this->translator->translatePlural('bassin', 'bassins', $nbBassins2, null) . ' (%type_pools$s)%toboaqua$s';
            } else {
                $textPools .= $this->translator->translate('d\'une', null) . ' ' . strtolower($typePoolsNonAD[0][0]) . '%toboaqua$s';
                $textPools .= ' à '.$typePoolsNonAD[0][1];
            }
            $textTobo = null;
            if ($hasToboggan) {
                $textTobo = ' ' . $this->translator->translate('et vous amuserez dans les toboggans aquatiques du camping', null);
            }
            $textPools .= '.';

            if (count($typeSoins) == 0 && $hasSpasoins) {
                $textPools .= ' ' . $this->translator->translate("Profitez de l'espace bien-être du SPA.", null);
            } else if ($typeSoins) {
                $textPools .= ' ' . $this->translator->translate('Profitez', null) . ' %type_soins$s';
                if ($hasSpasoins) {
                    $textPools .= ' ' . $this->translator->translate("ainsi que de l'espace bien-être du SPA", null);
                }
                $textPools .= '.';
            }

            /* recuperation des libelles des types de piscine concaténés aux distances */
            $tabTemp = [];
            foreach($typePoolsNonAD as $itemTemp) {
                $tabTemp[] = implode(' à ',$itemTemp);
            }

            $textPools = vksprintf($textPools, array(
                'nb_bassins' => $nbBassins2,
                'type_pools' => transformArrayToSentence($tabTemp),
                'type_soins' => transformArrayToSentence($typeSoins),
                'toboaqua' => $textTobo
            ));
        }

        $textSports = null;
        if ($typeSports) {
            $textSports .= $this->translator->translate('Côté sport, le camping propose %sport$s au sein du camping', null);
            if ($typeSportsProx) {
                $textSports.= ', ' . $this->translator->translate('de même que', null) . ' %sport_prox$s';
            }
        } else if ($typeSportsProx) {
            $textSports .= $this->translator->translate('Côté sport, le camping propose', null) . ' %sport_prox$s';
        }
        if (!$textSports && ($actiSports || $actiSportsProx)) {
            $textSports .= $this->translator->translate('Côté sport, le camping propose diverses activités', null) . ' %location$s : %acti_sports$s';
        } else if ($textSports && ($actiSports || $actiSportsProx)) {
            $textSports .= '. ' . $this->translator->translate('Le camping propose également diverses activités', null) . ' %location$s : %acti_sports$s';
        }
        $textSports = vksprintf($textSports, array(
            'sport' => transformArrayToSentence($typeSports, ', ', ' ' . $this->translator->translate('ou', null) . ' '),
            'sport_prox' => transformArrayToSentence($typeSportsProx, ', ', ' ' . $this->translator->translate('ou', null) . ' '),
            'location' => ($actiSports) ? $this->translator->translate('sur place', null) : $this->translator->translate('à proximité', null),
            'acti_sports' => ($actiSports) ? transformArrayToSentence($actiSports) : transformArrayToSentence($actiSportsProx)
        ));

        $textAnim = null;
        if ($anim) {
            $textAnim .= $this->translator->translate('Le camping propose', null) . ' %anim$s';
            if ($animChild) {
                $textAnim .= ' ' . $this->translator->translate('ainsi que %anim_child$s pour vos enfants', null) . '.';
            } else {
                $textAnim .= '.';
            }
        } else if ($animChild) {
            $textAnim .= $this->translator->translate('Le camping propose', null) . ' %anim_child$s ' . $this->translator->translate('pour vos enfants', null) . '.';
        }
        $textAnim = vksprintf($textAnim, array(
            'anim' => transformArrayToSentence($anim),
            'anim_child' => transformArrayToSentence($animChild)
        ));

        $textBorn = null;
        if ($borne) {
            $textBorn .= $this->translator->translate('Camping-caristes : le camping dispose d\'une borne', null) . ' %borne$s';
            if ($campingCar) {
                $textBorn .= ' située %aire$s';
            }
            $textBorn .= '.';
            if ($isCCPassage) {
                $textBorn .= ' ' . $this->translator->translate('Les camping-cars de passage sont acceptés.', null);
            } else {
                $textBorn .= ' ' . $this->translator->translate('Une nuit minimum est requise.', null);
            }
        }
        $textBorn = vksprintf($textBorn, array(
            'borne' => $borne,
            'aire' => transformArrayToSentence($campingCar)
        ));

        $textAnimal = null;
        if ($isAnimalsOK) {
            $textAnimal .= $this->translator->translate('Les animaux sont autorisés sur les emplacements.', null);
        }
        if ($hasDates) {
            if ($isAnimalsOK) {
                $textAnimal .= ' ';
            }
            $textAnimal .= $this->translator->translate('Consultez les dates d’ouvertures du camping ainsi que ses tarifs.', null);
        }

        $params = array(
            'camping_label' => $camping->name,
            'city_label'    => $this->constructSpecialLink($city->label, 'city', $city->id),
            'city_prefix_a' => $city->prefixA,
            'department_prefix_dans_le' => $department->prefixDansLe,
            'department_label' => $this->constructSpecialLink($department->label, 'department', $department->id),
            'region_prefix_en' => $region->prefixEn,
            'region_label'  => $this->constructSpecialLink($region->label, 'region', $region->id),
            'geoloc' => $geoLocSentence,
            'carac_location' => $caracLocation,
            'beach' => $textBeach,
            'rating' => ($textRating) ? ("\r\n\r\n" . $textRating . '.') : '',
            'pool' => ($textPools) ? ("\r\n\r\n" . $textPools) : '',
            'sport' => ($textSports) ? ("\r\n\r\n" . $textSports . '.') : '',
            'anim' => ($textAnim) ? ("\r\n\r\n" . $textAnim) : '',
            'borne' => ($textBorn) ? ("\r\n\r\n" . $textBorn) : '',
            'animal' => ($textAnimal) ? ("\r\n\r\n" . $textAnimal) : ''
        );

        $params2 = array(
            'camping_label' => $camping->name,
            'city_label'    => $city->label,
            'city_prefix_a' => $city->prefixA,
            'department_prefix_dans_le' => $department->prefixDansLe,
            'department_label' => $department->label,
            'region_prefix_en' => $region->prefixEn,
            'region_label'  => $region->label,
            'geoloc' => $geoLocSentence,
            'carac_location' => $caracLocation,
            'beach' => $textBeach,
            'rating' => ($textRating) ? ("\r\n\r\n" . $textRating . '.') : '',
            'pool' => ($textPools) ? ("\r\n\r\n" . $textPools) : '',
            'sport' => ($textSports) ? ("\r\n\r\n" . $textSports . '.') : '',
            'anim' => ($textAnim) ? ("\r\n\r\n" . $textAnim) : '',
            'borne' => ($textBorn) ? ("\r\n\r\n" . $textBorn) : '',
            'animal' => ($textAnimal) ? ("\r\n\r\n" . $textAnimal) : ''
        );

        $camping->code = $languageCode;
        $camping->longDescription = vksprintf($mainText, $params);
        $tf = new \TfTruncate();
        $camping->metaDescription = $tf->truncateText(vksprintf($metaText, $params2), 150);
        return $camping;
    }

    public function generateCampingDescriptionById($campingId)
    {
        $camping = $this->generateCampingDescription($this->campingTable->fetchOne($campingId));
        return $camping->longDescription;
    }

    public static function constructSpecialLink($label, $type, $id = null)
    {
        if ($id !== null) {
            $id = "=$id";
        }
        return '{' . $type . $id . '}' . $label . '{/' . $type . '}';
    }
}
