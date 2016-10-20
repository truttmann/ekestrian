<?php
namespace Application\Service;

use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Crypt\BlockCipher;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mime\Part;

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
		if ( $userContainer->offsetExists('logged_member') && $userContainer->logged_member != null) {
			$temp = $userContainer->logged_member;
			return $temp;
		} else {
			return false;
		}
    }
    
    public function setMembreConnecte($login) {
        $userContainer = new Container(NAME_SESSION_USER);
		$userContainer->logged_member = $login;
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
    
    public function setCarteRegistration($mes) {
        $userContainer = new Container(NAME_SESSION_USER);
		$userContainer->carte_registration = $mes;
	}
    
    public function getCarteRegistration() {
		$userContainer = new Container(NAME_SESSION_USER);
		if ( $userContainer->offsetExists('carte_registration')) {
			$temp = $userContainer->carte_registration;
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
    
    public function sendMailUserCreation(\Application\Model\Client $client) {
        $client->token = hash('SHA1', md5(date('Y-m-d H:i:s').$client->client_id));
        $this->sm->get('clientTable')->save($client);
        
        $vhm = $this->sm->get('viewhelpermanager');
        $url = $vhm->get('url');

        
        /* envoi du mail */
        $htmlMarkup = "Bonjour ".$client->lastname." ".$client->firstname.",<br/><br/>".
        "Nous vous remercions de vous être inscrit sur Elite Auctions.<br/>".
        "Afin de confirmer votre inscription, merci de cliquer sur le lien suivant : <a href='"."http".(isset($_SERVER['HTTPS']) ? "s" : null)."://".$_SERVER["HTTP_HOST"].$url('home/validation_membre', array('token'=>$client->token))."'>ACTIVATION</a><br/>".
        "Pour toute question vous pouvez nous contacter par email support@eliteauction.com<br/><br/>".
        "Bien cordialement ";

        $html = new Part($htmlMarkup);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->setParts(array(/*$text, */$html));

        $message = new Message();
        $message->addFrom("contact@eliteauction.com", "Elite Auction")
            ->addTo($client->email)
            ->addReplyTo("no-replay@eliteauction.com", "Elite Auction")
            ->setSubject("Elite Auction - Création de compte")
            ->setBody($body)
            ->setEncoding("UTF-8");

        $transport = new SendmailTransport();
        $transport->send($message);

        return "ok";
    }
    
    public function sendMailBienvenu(\Application\Model\Client $client) {
        $vhm = $this->sm->get('viewhelpermanager');
        $url = $vhm->get('url');

        
        /* envoi du mail */
        $htmlMarkup = "Bonjour ".$client->lastname." ".$client->firstname.",<br/><br/>".
        "Nous sommes ravi de vous compter parmi nos membres d'Elite Auctions.<br/>".
        "Vous pourrez accéder à toutes les enchère sur la page suivante : <a href='"."http".(isset($_SERVER['HTTPS']) ? "s" : null)."://".$_SERVER["HTTP_HOST"].$url('home', array('token'=>$client->token))."'>SITE</a><br/>".
        "Vous pourrez également modifier vos informations à tous moment en allant sur votre compte: <a href='"."http".(isset($_SERVER['HTTPS']) ? "s" : null)."://".$_SERVER["HTTP_HOST"].$url('home/membre/edit', array('membre_id'=>$client->client_id))."'>MON COMPTE</a> <br/><br/>".
        "Pour toute question vous pouvez nous contacter par email support@eliteauction.com<br/><br/>".
        "Bien cordialement ";

        $html = new Part($htmlMarkup);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->setParts(array(/*$text, */$html));

        $message = new Message();
        $message->addFrom("contact@eliteauction.com", "Elite Auction")
            ->addTo($client->email)
            ->addReplyTo("no-replay@eliteauction.com", "Elite Auction")
            ->setSubject("Elite Auction - Bienvenu")
            ->setBody($body)
            ->setEncoding("UTF-8");

        $transport = new SendmailTransport();
        $transport->send($message);

        return "ok";
    }
    
    
    
    public function sendMailUserPassword(\Application\Model\Client $client) {
        
        $client->password = substr(str_replace(" ", "", hash('SHA1', md5(date('Y-m-d H:i:s').$client->client_id))), 0, 8);
        $this->sm->get('clientTable')->save($client);
        
        $vhm = $this->sm->get('viewhelpermanager');
        $url = $vhm->get('url');
        
        /* envoi du mail */
        $htmlMarkup = "Bonjour ".$client->lastname." ".$client->firstname.",<br/><br/>".
        "Vous avez demander un nouveau mot de passe pour accéder au site web ELite Auction.<br/>".
        "Voici votre nouveau mot de passe : ".$client->password."<br/>".
        "Vous pourrez également modifier vos informations à tous moment en allant sur votre compte: <a href='"."http".(isset($_SERVER['HTTPS']) ? "s" : null)."://".$_SERVER["HTTP_HOST"].$url('home/membre/edit', array('membre_id'=>$client->client_id))."'>MON COMPTE</a> <br/><br/>".
        "Pour toute question vous pouvez nous contacter par email support@eliteauction.com<br/><br/>".
        "Bien cordialement ";

        $html = new Part($htmlMarkup);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->setParts(array(/*$text, */$html));

        $message = new Message();
        $message->addFrom("contact@eliteauction.com", "Elite Auction")
            ->addTo($client->email)
            ->addReplyTo("no-replay@eliteauction.com", "Elite Auction")
            ->setSubject("Elite Auction - Mot de passe oublié")
            ->setBody($body)
            ->setEncoding("UTF-8");

        $transport = new SendmailTransport();
        $transport->send($message);

        return "ok";
    }
    
    public function sendMailUserNewEnchere(\Application\Model\ClientAuction $client) {
        
        $vhm = $this->sm->get('viewhelpermanager');
        $url = $vhm->get('url');
        
        $c = $this->sm->get('clientTable')->fetchOne($client->client_id);
        
        $o = "";
        try{
        $t = $this->sm->get('lotTable')->fetchOne($client->lot_id);
        $o = $t->title;
        }catch(\Exception $e){}
        
        /* envoi du mail */
        $htmlMarkup = "Bonjour ".$c->lastname." ".$c->firstname.",<br/><br/>".
        "Nous vous confirmons la prise en compte de votre enchère sur le site ELite Auction, pour le lot ".$o." et pour la somme de ".$client->value."€.<br/>".
        "Pour toute question vous pouvez nous contacter par email support@eliteauction.com<br/><br/>".
        "Bien cordialement ";

        $html = new Part($htmlMarkup);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->setParts(array(/*$text, */$html));

        $message = new Message();
        $message->addFrom("contact@eliteauction.com", "Elite Auction")
            ->addTo($c->email)
            ->addReplyTo("no-replay@eliteauction.com", "Elite Auction")
            ->setSubject("Elite Auction - Nouvelle enchère")
            ->setBody($body)
            ->setEncoding("UTF-8");

        $transport = new SendmailTransport();
        $transport->send($message);

        return "ok";
    }

}