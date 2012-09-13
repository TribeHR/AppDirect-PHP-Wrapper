<?php

/**
 * Define the different event types and classifications
 */
define('SUBSCRIPTION_ORDER', 'SUBSCRIPTION_ORDER');
define('SUBSCRIPTION_CHANGE', 'SUBSCRIPTION_CHANGE');
define('SUBSCRIPTION_CANCEL', 'SUBSCRIPTION_CANCEL');
define('SUBSCRIPTION_NOTICE', 'SUBSCRIPTION_NOTICE');
define('USER_ASSIGNMENT', 'USER_ASSIGNMENT');
define('USER_UNASSIGNMENT', 'USER_UNASSIGNMENT');
define('ACCOUNT_SYNC', 'ACCOUNT_SYNC');
define('ACCOUNT_UNSYNC', 'ACCOUNT_UNSYNC');
define('USER_SYNC', 'USER_SYNC');
define('USER_LIST_CHANGE', 'USER_LIST_CHANGE');

class AppDirectEvent extends AppDirectBase
{	
	var $type;
	var $payload;
	var $creator;
	var $flag;
	var $returnUrl;
	
	private $connector;

	public function __construct(SimpleXMLElement $xml = null)
	{
		$this->connector = new AppDirectConnector();
		parent::__construct($xml);
	}
	
	/**
	 * Return the name of this object - the name is used in the XML tags
	*/
	protected function getName()
	{
		return 'event';
	}
	
	public function getByToken($token = null)
	{
		$path = 'events/'.$token;
		$xmlObject = $this->connector->get($path);			
		return new AppDirectEvent($xmlObject);
	}
	
	public function postUserListChange($accountIdentifier)
	{
		$endpoint = 'events/';
		
		$xmlData = new SimpleXMLElement('<event></event>');
		$xmlData->addChild('type', USER_LIST_CHANGE);
		$xmlData->addChild('payload');
		$xmlData->payload->addChild('account');
		$xmlData->payload->account->addChild('accountIdentifier', $accountIdentifier);

		$xmlResult = $this->connector->post($endpoint, $xmlData->asXML());
		return $xmlResult;
	}
	
	public function signReturnUrl($params = array())
	{
		$url = $this->returnUrl;
		foreach($params as $key => $value)
		{
			$url .= '&'.$key.'='.$value;
		}
		return $this->connector->getSignedUrl($url);
	}
	
	public function xmlResponse($success, $code, $message)
	{
		$xmlResult = new SimpleXMLElement('<result></result>');
		$xmlResult->addChild('success', ($success ? 'true' : 'false') );
		if(!$success)
			$xmlResult->addChild('errorCode', $code);
		$xmlResult->addChild('message', $message);
		
		return $xmlResult->asXML();		
	}
}

?>