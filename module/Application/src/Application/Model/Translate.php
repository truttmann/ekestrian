<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Translate  implements InputFilterAwareInterface    
{
    public $action;
    /**
     * @var
     */
    public $code;
    /**
     * @var
     */
    public $langue_id;
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
                'name' => $this->_name_prefix . 'value',
                'required' => TRUE,
                'filters' => array(
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                        ),
                    ),
                ),
            )));
            
            
            $this->inputFilter = $inputFilter;
        }
 
        return $this->inputFilter;
    }
}

    
    
    
    
    
