<?php

namespace Application\Adapter;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Application\User\Model\UserTable;
use Zend\ServiceManager\ServiceManager;
use Application\Acl\Model\RoleTable;


class AuthAdapter implements AdapterInterface
{
    private $login = "";
    private $password = "";
    private $serviceManager = null;

    /**
     * Sets username and password for authentication
     *
     * @return void
     */
    public function __construct($username, $password, ServiceManager $serviceManager)
    {
        if(empty($username) || empty($password)) {
            throw new \Exception("Invalid login or password");    
        }
        $this->login = $username;
        $this->password = $password;
        $this->serviceManager = $serviceManager;
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
     *               If authentication cannot be performed
     */
    public function authenticate()
    {
        $code = Result::FAILURE;
        $reason = array();
        $identite = $this->login;

        try{
            $userT = $this->serviceManager->get('Application\User\Model\UserTable');
            $obj = $userT->getByLoginPass($this->login, $this->password);
            if(!is_object($obj) || get_class($obj) != "Application\User\Model\User") {
                throw new \Exception("undefined user");                
            }

           // print_r($obj);die;

            if(is_array($obj->roleIds) && count($obj->roleIds) == 0) {
                $code = Result::FAILURE_UNCATEGORIZED;
                $reason[] = "User didn't have rights";
            } else {
                $code = Result::SUCCESS;
                $roleT = $this->serviceManager->get('Application\Acl\Model\RoleTable');
                $identite = array(
                    "user" => $obj,
                    "role" => $roleT->fetchOne($obj->roleIds[0])
                );
            }
        }catch(\Exception $e) {
            $code = Result::FAILURE_IDENTITY_NOT_FOUND;
            $reason[] = $e->getMessage();
        }

        return new Result($code, $identite, $reason);
    }
}