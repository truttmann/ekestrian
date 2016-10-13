<?php

namespace RepriseBatch\Controller;

use Zend\Mvc\Application;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\I18n\Translator\Translator;
use Application\City\Model\CityTable;
use Application\Department\Model\DepartmentTable;
use Application\Thematic\Model\ThematicTable;
use Application\Camping\Model\CampingTable;
use RepriseBatch\Utils;

require_once __DIR__ . '/../utils/Util.php';

class CityController extends AbstractActionController
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
     * @var ThematicTable
     */
    public $thematicTable;

    /**
     * @var CampingTable
     */
    public $campingTable;

    /**
     * @var Translator
     */
    public $translator;

    public function loadAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $this->languageTable = $this->getServiceLocator()->get('Application\Language\Model\LanguageTable');
        $this->cityTable = $this->getServiceLocator()->get('Application\City\Model\CityTable');
        $this->departmentTable = $this->getServiceLocator()->get('Application\Department\Model\DepartmentTable');
        $this->thematicTable = $this->getServiceLocator()->get('Application\Thematic\Model\ThematicTable');
        $this->campingTable = $this->getServiceLocator()->get('Application\Camping\Model\CampingTable');
        $this->translator = $this->getServiceLocator()->get('translator');
        $this->translator->addTranslationFile('phparray', './module/RepriseBatch/language/en_UK.cities.php', null, 'uk');
        $this->translator->addTranslationFile('phparray', './module/RepriseBatch/language/es_ES.cities.php', null, 'es');
        $this->translator->addTranslationFile('phparray', './module/RepriseBatch/language/nl_NL.cities.php', null, 'nl');
        $this->translator->addTranslationFile('phparray', './module/RepriseBatch/language/de_DE.cities.php', null, 'de');

        $forced = $request->getParam('forced');

        $idCity = $request->getParam('id');

        $languageCode = $request->getParam('lang');

        if ($forced) {
            echo " [  \033[0;36m0%\033[0m] Flushing descriptions fields\r";
            $this->cityTable->flushAllDescriptions();
        }

        if ($idCity) {
            $city = $this->cityTable->fetchOne($idCity, $languageCode);
            $this->generateCityDescriptionById($city, $languageCode);
            if (!$request->getParam('display')) {
                $this->cityTable->save($city);
            } else {
                echo $city->description;
            }
        } else {
            $languagesList = array();
            foreach ($this->languageTable->fetchAll() as $language) {
                $languagesList[] = $language->code;
            }
            $citiesList = $this->cityTable->fetchAll();
            $nbrCities = $citiesList->getTotalItemCount() * count($languagesList);
            $i = $nbrCities - $this->cityTable->fetchAllCitiesToGenerate()->count();

            $cities = array();
            foreach ($languagesList as $language) {
                foreach ($this->cityTable->fetchAllCitiesToGenerate($language) as $city) {
                    $cities[] = $this->generateCityDescriptionById($city, $language);

                    if (count($cities) > 200) {
                        $this->cityTable->massUpdate($cities);
                        $cities = array();
                    }

                    $i++;
                    $percent = str_pad((int)(($i * 100 )/ $nbrCities), 3, ' ', STR_PAD_LEFT);
                    echo " [\033[0;36m$percent%\033[0m] Generating description of the city {$city->labelOrigin}                                              \r";
                }
            }
            if (!empty($cities)) {
                $this->cityTable->massUpdate($cities);
            }
            echo "[ \033[0;32mok\033[0m ] Cities description generation completed                                              \r\n";
        }
    }

    public function generateCityDescriptionById($city, $languageCode = 'fr')
    {
        $this->translator->setLocale($languageCode);

        if (empty($city->label)) {
            $city->label = $city->labelOrigin;
        }

        $department = $this->departmentTable->fetchOne($city->departmentId, $languageCode);
        $touristPlace = $this->thematicTable->getTouristPlaceByCity($city->id, $languageCode);

        $campingsResultSet = $this->campingTable->fetchCampingsByCity($city->id, '_priority ASC');
        $nbrCampings = $campingsResultSet->count();

        $citiesProx = array();
        $i = 0;
        $nbrCampingsProxCities = 0;
        foreach ($this->campingTable->nbrCampingsByCityProx($city->coordLatitude, $city->coordLongitude, 15, $city->id, $languageCode) as $data) {
            if ($i < 3) {
                $citiesProx[] = $data['nbr_campings'] . ' ' . $this->translator->translatePlural('camping', 'campings', $data['nbr_campings'], null) . ' ' . $this->translator->translate('à', null) . ' ' . $this->constructSpecialLink($data['prefix_le'] . $data['label'], 'city', $data['id']);
                $i++;
            }
            $nbrCampingsProxCities += $data['nbr_campings'];
        }

        $nbrCampingsHumanSize = 0;
        $nbrCampingsBest = 0;
        if ($nbrCampings) {
            $nbrCampingsHumanSize = $this->campingTable->countNbrCampingsHumanSize($city->id);
            $nbrCampingsBest = $this->campingTable->countNbrCampingsBest($city->id);
        }

        $campingsProx = $this->campingTable->fetchByCoordinates($city->id, $city->coordLatitude, $city->coordLongitude, 15, $languageCode);
        $nbrCampingsProx = 0;
        if ($campingsProx) {
            $nbrCampingsProx = $campingsProx->count();
        }

        $campings = array();
        $i = 0;
        foreach ($campingsResultSet as $row) {
            if ($i < 3) {
                $campings[] = $this->constructSpecialLink($row->name, 'camping', $row->id);
                $i++;
            }
        }

        if ($nbrCampings < 3 && $campingsProx) {
            for ($i = 3 - $nbrCampings; $i; $i--) {
                $row = $campingsProx->next();
                if ($row) {
                    $campings[] = $this->constructSpecialLink($row['name'], 'camping', $row['id']) . ', ' . $this->translator->translate('situé', null) . ' ' . $row['city_prefix_a'] . $this->constructSpecialLink($row['city_label'], 'city', $row['city_id']);
                }
            }
        }

        $textListCampings = transformArrayToSentence($campings, ', ', ' ' . $this->translator->translate('ou', null) . ' ');
        $textListCitiesProx = transformArrayToSentence($citiesProx, ', ', ' ' . $this->translator->translate('ou', null) . ' ');

        $infosPeople = '';
        $infosTmp = array();
        if ($city->nbrPeople) {
            $infosTmp[] = "{$city->nbrPeople} " . $this->translator->translate('habitants', null);
        }
        if ($city->situationOrigin) {
            $infosTmp[] = $city->situationOrigin;
        }
        if (count($infosTmp)) {
            $infosPeople .= '(' . implode('-', $infosTmp) . ') ';
        }

        $params = array(
            'department_prefix_dans_le' => $department->prefixDansLe,
            'department_label' => $this->constructSpecialLink($department->label, 'department', $department->id),
            'department_description' => $department->shortDescription,
            'city_prefix_a' => $city->prefixA,
            'city_prefix_de' => $city->prefixDe,
            'city_label' => $this->constructSpecialLink($city->label, 'city', $city->id),
            'city_label_no_link' => $city->label,
            'infos_people' => $infosPeople,
            'tourist_place' => ($touristPlace) ? $touristPlace->shortDescription : '',
            'places_rule1' => ($nbrCampingsHumanSize > 0) ? $this->translator->translate('dans un terrain à taille humaine', null) : '',
            'places_rule2' => ($nbrCampingsHumanSize > 0 && $nbrCampingsBest > 0) ? ', ' . $this->translator->translate('ou', null) . ' ' : '',
            'places_rule3' => ($nbrCampingsBest > 0) ? $this->translator->translate('dans un beau camping 4 ou 5 étoiles', null) : '',
            'nbr_campings' => $nbrCampings,
            'campings' => $this->translator->translatePlural('camping', 'campings', $nbrCampings, null),
            'campings_prox' => $this->translator->translatePlural('camping', 'campings', $nbrCampingsProx, null),
            'campings_prox_cities' => $this->translator->translatePlural('camping', 'campings', $nbrCampingsProxCities, null),
            'nbr_campings_prox' => $nbrCampingsProx,
            'nbr_campings_prox_cities' => $nbrCampingsProxCities,
            'campings_list' => $textListCampings,
            'cities_prox' => $textListCitiesProx
        );

        $mainText = $this->translator->translate('Vous avez choisi des vacances en camping %department_prefix_dans_le$s%department_label$s ou plus particulièrement un camping %city_prefix_a$s%city_label$s %infos_people$s? %tourist_place$s', null);

        if ($department->shortDescription !== null) {
            $mainText .= "\r\n\r\n" . '%department_description$s';
        }
        $textInfoCampings = "\r\n\r\n" . $this->translator->translate('Vous souhaitez un séjour en tente, une location mobil-home', null) . ' %city_prefix_a$s%city_label_no_link$s, %places_rule1$s%places_rule2$s%places_rule3$s ? ' .
                    $this->translator->translate('Vous trouverez', null) . ' %nbr_campings$d %campings$s %city_prefix_a$s%city_label_no_link$s';
        if ($nbrCampingsProx) {
            $textInfoCampings .= ' ' . $this->translator->translate('et', null) . ' %nbr_campings_prox$d %campings_prox$s ' . $this->translator->translate('à proximité', null);
        }
        $textInfoCampings .= '. ' . $this->translator->translate('Découvrez', null) . ' %campings_list$s.';
        $textNoCampings = "\r\n\r\n" . $this->translator->translate('Aucun camping ne se trouve directement', null) . ' %city_prefix_a$s%city_label_no_link$s.';
        if ($nbrCampingsProxCities) {
            $textNoCampings .= ' ' . $this->translator->translate('En revanche, CampingFrance.com vous aide à trouver un des', null) . ' %nbr_campings_prox_cities$d %campings_prox_cities$s ' . $this->translator->translate('à proximité', null) . ' %city_prefix_de$s%city_label_no_link$s : %cities_prox$s.';
        }

        if ($nbrCampings > 0) {
            $mainText .= $textInfoCampings;
        } else {
            $mainText .= $textNoCampings;
        }

        $city->code = $languageCode;
        $city->description = vksprintf($mainText, $params);
        return $city;
    }

    public static function constructSpecialLink($label, $type, $id = null)
    {
        if ($id !== null) {
            $id = "=$id";
        }
        return '{' . $type . $id . '}' . $label . '{/' . $type . '}';
    }
}