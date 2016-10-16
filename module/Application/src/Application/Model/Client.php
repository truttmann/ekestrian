<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Client implements InputFilterAwareInterface    
{
    public $action;
    /**
     * @var
     */
    public $lastname;
    /**
     * @var
     */
    public $societe;
    /**
     * @var
     */
    public $email;
    /**
     * @var
     */
    public $firstname;

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
 
            password
            password_confirm
            condition_vente
            reglement
            confidence
            submit
            
            
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'firstname',
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
                'name' => $this->_name_prefix . 'lastname',
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
                'name' => $this->_name_prefix . 'societe',
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
                            'max' => '1024',
                        ),
                    ),
                ),
            )));
        
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'phone',
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
                            'max' => '15',
                        ),
                    ),
                ),
            )));
            
            
        
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'birthday',
                'required' => TRUE,
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
            
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'email',
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
                    array(
                        'name' => 'EmailAddress'
                    )
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => $this->_name_prefix . 'password',
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
            $this->inputFilter = $inputFilter;
        }
 
        return $this->inputFilter;
    }
}

    
    
    
    
    
