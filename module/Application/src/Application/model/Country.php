<?php
namespace Application\Model;

class Country
{
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
}

    
    
    
    
    
