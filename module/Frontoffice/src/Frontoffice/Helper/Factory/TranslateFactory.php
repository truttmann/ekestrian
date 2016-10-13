<?php

namespace Frontoffice\Helper\Factory;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TranslateFactory extends \Zend\View\Helper\AbstractHelper {
    protected $count = 0;

    public function __invoke($code, $langue_id = 1)
    {
        $sm = $this->getView()->getHelperPluginManager()->getServiceLocator();
        $res = null;
        
        try {
            $res = $sm->get('translateTable')->fetchOne($code, $langue_id);
            
        } catch(\Exception $e) {
            $obj = new \Application\Model\Translate();
            $obj->code = $code;
            $obj->langue_id = $langue_id;
            $sm->get('translateTable')->save($obj);
        }
        return (($res == null || empty($res->value))?$code:$res->value);
        
    }
}
