<?php
namespace Application\Service;

use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Crypt\BlockCipher;
use Application\Model\Client;

require_once dirname(dirname(dirname(__DIR__))).'/config/constantes.php';

class MangopayService
{
	private $sm = null;
    private $api = null;

	/**
	* Constructor
	*/
	public function __construct($sm) {
		$this->sm = $sm;
        $this->api = new \MangoPay\MangoPayApi();

        // configuration
        $this->api->Config->ClientId = 'sdk-unit-tests';
        $this->api->Config->ClientPassword = 'cqFfFrWfCcb7UadHNxx2C9Lo6Djw8ZduLi7J9USTmu8bhxxpju';
        $this->api->Config->TemporaryFolder = __DIR__.'/../temp_mangopay/';
        //$api->Config->BaseUrl = 'https://api.mangopay.com';//uncomment this to use the production environment

        //uncomment any of the following to use a custom value (these are all entirely optional)
        //$api->Config->CurlResponseTimeout = 20;//The cURL response timeout in seconds (its 30 by default)
        //$api->Config->CurlConnectionTimeout = 60;//The cURL connection timeout in seconds (its 80 by default)
        //$api->Config->CertificatesFilePath = ''; //Absolute path to file holding one or more certificates to verify the peer with (if empty, there won't be any verification of the peer's certificate)
	}
    
    public function getAllUsers() {
        return $this->api->Users->GetAll();
    }
    
    public function createUser(Client $client) {
        $User = new \MangoPay\UserNatural();
        $User->Email = $client->email;
        $User->FirstName = $client->firstname;
        $User->LastName = $client->lastname;
        
        $date = \DateTime::createFromFormat("Y-m-d", $client->birthday);
        $User->Birthday = ((is_object($date))?$date->getTimestamp():121274);
        
        
        /* recuperation du trigramme du pays */
        $t = null;
        try{
            $t = $this->sm->get('countryTable')->fetchOne($client->country_id);
        }catch(\Exception $e) {}
        $User->Nationality = ((is_object($t) && $t->iso != null)?$t->iso:"FR");
        $User->CountryOfResidence = ((is_object($t) && $t->iso != null)?$t->iso:"FR");
        return $this->api->Users->Create($User);
    }
    
    public function createWallet(\MangoPay\UserNatural $user) {
        $Wallet = new \MangoPay\Wallet();
        $Wallet->Owners = array($user->Id);
        $Wallet->Description = "Demo wallet for User ".$user->Id;
        $Wallet->Currency = "EUR";
        return $this->api->Wallets->Create($Wallet);
    }
    
    public function updateUser(Client $client) {
        $t = null;
        try{
            $t = $this->sm->get('countryTable')->fetchOne($client->country_id);
        }catch(\Exception $e) {}
        
        try{
            $User = new \MangoPay\UserNatural();
            $User->Id = $client->mangopay_id;
            $User->Email = $client->email;
            $User->FirstName = $client->firstname;
            $User->LastName = $client->lastname;

            $date = \DateTime::createFromFormat("Y-m-d", $client->birthday);
            $User->Birthday = ((is_object($date))?$date->getTimestamp():121274);


            /* recuperation du trigramme du pays */

            $User->Nationality = ((is_object($t) && $t->iso != null)?$t->iso:"FR");
            $User->CountryOfResidence = ((is_object($t) && $t->iso != null)?$t->iso:"FR");
            return $this->api->Users->Create($User);
        } catch(\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function cardRegistration(\MangoPay\UserNatural $user) {
        $cardRegister = new \MangoPay\CardRegistration();
        $cardRegister->UserId = $user->Id;
        $cardRegister->Currency = "EUR";
        return $this->api->CardRegistrations->Create($cardRegister);
    }
    
    public function cardRegistrationFinish(Client $client, $data, $error) {
        $cardRegisterPut = $this->api->CardRegistrations->Get($client->mangopay_carte_id);
        $cardRegisterPut->RegistrationData = ((!empty($data)) ? 'data=' . $data : 'errorCode=' . $error);
       return $this->api->CardRegistrations->Update($cardRegisterPut);
    }
}