<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ClientAuction  implements InputFilterAwareInterface    
{
    public $action;
    /**
     * @var
     */
    public $lot_name;
    /**
     * @var
     */
    public $creation_date;
    /**
     * @var
     */
    public $value;
    
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
                'name' => $this->_name_prefix . 'name',
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
                'name' => $this->_name_prefix . 'description',
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
                'name' => $this->_name_prefix . 'quality',
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
                'name' => $this->_name_prefix . 'race',
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
                'name' => $this->_name_prefix . 'birthday',
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'date',
                        'options' => array(
                            'format' => 'Y-m-d',
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add(
                array(
                    'name' => 'image_url',
                    'required' => false,
                    'validators' => array(
                        array(
                            'name' => 'Zend\Validator\File\Size',
                            'options' => array(
                                'min' => 120,
                                'max' => 200000,
                            ),
                        ),
                        array(
                            'name' => 'Zend\Validator\File\Extension',
                            'options' => array(
                                'extension' => 'png',
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

    
    
    
    
    
