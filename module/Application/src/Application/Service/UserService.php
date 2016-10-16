<?php
namespace Application\Service;

use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Crypt\BlockCipher;

require_once dirname(dirname(dirname(__DIR__))).'/config/constantes.php';

class UserService
{
	private $sm = null;

	/**
	* Constructor
	*/
	public function __construct($sm) {
		$this->sm = $sm;
	}

	public function getLoggedUser() {
		$userContainer = new Container(NAME_SESSION_USER);
		if ( $userContainer->offsetExists('logged_user')) {
			$temp = $userContainer->logged_user;
			return $temp;
		} else {
			return null;
		}
	}

	public function setLoggedUser($login) {
        $userContainer = new Container(NAME_SESSION_USER);
		$userContainer->logged_user = $login;
	}

    public function isMembreConnecte(){
        $userContainer = new Container(NAME_SESSION_USER);
		if ( $userContainer->offsetExists('logged_member')) {
			$temp = $userContainer->logged_member;
			return $temp;
		} else {
			return false;
		}
    }
    
    public function getInfoForm() {
		$userContainer = new Container(NAME_SESSION_USER);
		if ( $userContainer->offsetExists('info_form')) {
			$temp = $userContainer->info_form;
			return $temp;
		} else {
			return null;
		}
	}
    public function setMessages($mes) {
        $userContainer = new Container(NAME_SESSION_USER);
		$userContainer->messages = $mes;
	}
    
    public function getMessages() {
		$userContainer = new Container(NAME_SESSION_USER);
		if ( $userContainer->offsetExists('messages')) {
			$temp = $userContainer->messages;
			return $temp;
		} else {
			return null;
		}
	}

	public function setInfoForm($info) {
        $userContainer = new Container(NAME_SESSION_USER);
		$userContainer->info_form = $info;
	}
    
    public function getInfoFormMembre() {
		$userContainer = new Container(NAME_SESSION_USER);
		if ( $userContainer->offsetExists('info_form_membre')) {
			$temp = $userContainer->info_form_membre;
			return $temp;
		} else {
			return null;
		}
	}
    public function setInfoFormMembre($info) {
        $userContainer = new Container(NAME_SESSION_USER);
		$userContainer->info_form_membre = $info;
	}
    
	/**
	* function to check if user is logged
	* @return bool
	*/
	public function isLoggedUser() {
		$userContainer = new Container(NAME_SESSION_USER);
		if ( $userContainer->offsetExists('logged_user')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * retroune la liste des attributs d'un user
	 * @return stdClass Object
	 *
	 * */
	public function getInfosLoggedUser() {
		return $this->_infosUser;
	}


	/**
	* function qui va mettre a jour l'utilisateur courant
	* @param mixed $user
	* @return void
	*/
	public function updateUser($user) {
		/* mise a jour de la base */
		$this->sm->get('userModel')->update($user);

		/* mise a jour de la session */
		$temp = $this->getLoggedUser();
		$temp['user'] = $user;
		$this->setLoggedUser(serialize($temp));
	}

	/**
	* function qui retourne si l'utilisateur courant est super admin
	* @return bool
	*/
	public function isUserSA() {
		$return = false;
		$obj = $this->getLoggedUser();
		if(isset($obj['user']) && $obj['user']->ID_PROFIL == ID_PROFIL_SANS_FILTRE ) {
			$return = true;
		}
		return $return;
	}

	/**
	 * retroune le nouveau mot de passe généré
	 * @param  User   $user
	 * @return string
	 *
	 * */
	public function generePassword($user) {
		$newPwd = $this->generatePassword();
		$this->sm->get('userModel')->updatePassword($user , $newPwd);
		return $newPwd;
	}

	public function clearSession() {
		$userContainer = new Container(NAME_SESSION_USER);
		$sessionManager   = $userContainer->getManager();
		$sessionManager->destroy();
	}

	public  function hasUserAccess() {
		/* Controllers List of authorized logged off access */
    	//$authorizedControllers = require(__DIR__.'/../../../config/autorized_route.php');

        $userContainer = new Container(NAME_SESSION_USER);

        /* If !logged --> Go controller login */
        $router = $this->sm->get('router');
        $request = $this->sm->get('request');
        $routeMatch = $router->match($request);

       /* if(is_object($routeMatch)) {
	        $controllerName = $routeMatch->getParam('controller');*/
            if($userContainer->offsetExists('logged_user')
                && ($routeMatch->getParam('action') == 'logout' || $routeMatch->getParam('action') == 'login')) {
                return true;
            } else if (!$userContainer->offsetExists('logged_user')
            && $routeMatch->getParam('action') != 'logout' && $routeMatch->getParam('action') != 'login') {
                return false;
            }

	        /*if (!$userContainer->offsetExists('logged_user')) {
	            if(!in_array($controllerName, $authorizedControllers)) {

                    return false;
	            }
	        }*/
        /*} else {
        	return false;
        }*/
        return true;
	}


	private function generatePassword() {
        $alpha = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789";
        $numeric = "23456789";
        $special = ".-+=_,!@$#*§£%&";

        $pw = str_shuffle($alpha);
        $pw = mb_substr($pw, 0 ,8);
        $t = $t2 = rand(0, 7);

        $pwn = str_shuffle($numeric);
        $pwn = mb_substr($pwn, 0 ,1);
        $pw = utf8_encode(substr_replace(utf8_decode($pw), utf8_decode($pwn), $t, 1));

        while($t2 ==  $t) {
            $t2 = rand(0, 7);
        }

        $pws = str_shuffle($special);
        $pws = mb_substr($pws, 0 ,1);
        $pw = utf8_encode(substr_replace(utf8_decode($pw), utf8_decode($pws), $t2, 1));

        return $pw;
    }

	


    /**
     * function qui va permettre d'encypter une chaine pour la creation d'un cookie
     * @param string $chaine
     * @return string
     */
    public function cryptUserForCookie($chaine) {
        $blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
        $blockCipher->setKey(CRYPT_COOKIE_KEY);
        return $blockCipher->encrypt($chaine);
    }

    /**
     * * function qui va permettre de decypter une chaine pour la creation d'un cookie
     * @param string $chaine
     * @return mixed
     */
    public function decryptUserForCookie($chaine) {
        $blockCipher2 = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
        $blockCipher2->setKey(CRYPT_COOKIE_KEY);
        return $blockCipher2->decrypt($chaine);
    }

}