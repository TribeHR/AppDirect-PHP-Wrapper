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
	
	// This exists only as backward compatibility with existing code
	public function getByToken($token = null)
	{
		return $this->getEvent($token);
	}

	// Get the Event data from AppDirect, either by Token or EventUrl
	public function getEvent($eventUrl)
	{
		// Verify the OAuth signature of the call
		if(!$this->connector->verifySignature())
		{
			$error = array('error' => 'The request did not validate using AppDirect OAuth signatures');
			throw new AppDirectValidationException('401', $error);
		}
		
		// The given $eventUrl could, in legacy code, actually be a token instead
		if (!$this->connector->isEventUrl($eventUrl))
		{
			// This is an old-style token. Properly path it.
			$eventUrl = 'events/'. $eventUrl;
		}
		else
		{
			// The Event is using the new distributed API, and we're given an EventUrl
			$eventUrl = urldecode($eventUrl);
		}

		// GET the event from the provided $eventUrl using a OAuth-signed request
		return new AppDirectEvent($this->connector->get($eventUrl));
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