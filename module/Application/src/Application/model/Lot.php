<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Lot  implements InputFilterAwareInterface    
{
    public $action;
    /**
     * @var
     */
    public $name;
    /**
     * @var
     */
    public $race;
    /**
     * @var
     */
    public $birthday;
    
    public $inputFilter;

    public function __construct(){}

    public function exchangeArray($data)
    {
        $ks = array_keys($data);
        foreach($ks as $k) {
            $this->$k = $data[$k];
        }
    }
    
    public function toArray(){
        return get_object_vars($this);
    }
    
    /**
     * Retourne les données des attributs en Non CamelCase
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->attributesNames[$name])) {
            if ($this->{$this->attributesNames[$name]} instanceof \DateTime) {
                $attrDetail = $this->getAttributeDetails($this->attributesNames[$name]);
                $format = 'd/m/Y à H:i:s';
                if ($attrDetail->hasTag('Date')) {
                    $format = $attrDetail->getTag('Date')->getContent();
                }
                return $this->{$this->attributesNames[$name]}->format($format);
            }
            return $this->{$this->attributesNames[$name]};
        }
        return null;
    }

    /**
     * @param $attrName
     * @return false|Reflection\DocBlockReflection
     * @throws \Exception S'il n'y a pas d'informations concernant l'attribut.
     */
    public function getAttributeDetails($attrName)
    {
        $propertyReflection = new Reflection\PropertyReflection($this, $attrName);
        if ($propertyReflection->getDocBlock() === false) {
            throw new \Exception("Missing doc informations on attribute '$attrName'");
        }
        return $propertyReflection->getDocBlock();
    }

    public function getAttributesNames()
    {
        return $this->attributesNames;
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
 
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
 
            
            
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'title',
                'required' => TRUE,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => '2',
                            'max' => '255',
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'title_en',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => '2',
                            'max' => '255',
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'description',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => '2',
                            'max' => '1024',
                        ),
                    ),
                ),
            )));
        
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'description_en',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => '2',
                            'max' => '1024',
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'estimated_price',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'Float',
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'min_price',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'Float',
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'reserve_price',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'Float',
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'ist_price',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'Float',
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'pre_auth_price',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'Float',
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'number',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'Int',
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'video_link',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => '2',
                            'max' => '255',
                        ),
                    )/*,
                    array(
                        'name' => "Uri"
                    )*/
                ),
            )));
            
            $inputFilter->add(
                array(
                    'name' => 'image_url',
                    'required' => false,
                    'validators' => array(
                       /* array(
                            'name' => 'Zend\Validator\File\Size',
                            'options' => array(
                                'min' => 120,
                                'max' => 5000000,
                            ),
                        ),*/
                        array(
                            'name' => 'Zend\Validator\File\Extension',
                            'options' => array(
                                'extension' => array('png','jpg', 'jpeg')
                            ),
                        ),
                    ),
                )
            );
            
            $this->inputFilter = $inputFilter;
        }
 
        return $this->inputFilter;
    }
}

    
    
    
    
    
