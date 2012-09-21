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

	// Method behaviour flags
	const TRAP_STATELESS = true;
	const ALLOW_STATELESS = false;

	// Event definition/action constants
	const FLAG_STATELESS = 'STATELESS';
	const FLAG_DEVELOPMENT = 'DEVELOPMENT';

	const NOTICE_DEACTIVATED = 'DEACTIVATED';
	const NOTICE_REACTIVATED = 'REACTIVATED';
	const NOTICE_UPCOMING_INVOICE = 'UPCOMING_INVOICE';
	
	const ACCOUNT_STATUS_TRIAL_EXPIRED = 'FREE_TRIAL_EXPIRED';
	const ACCOUNT_STATUS_ACTIVE = 'ACTIVE';

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
	// @statelessAction:
	//  - TRAP_STATELESS:  getEvent will directly respond to the AppDirect request with a generic error (default)
	//  - ALLOW_STATELESS: getEvent will pass the event to the requester, trusting the dummy data will be handled properly
	public function getEvent($eventUrl, $statelessAction = self::TRAP_STATELESS)
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
		$event = new AppDirectEvent($this->connector->get($eventUrl));

		// If STATELESS events are to be trapped, detect them here and return to AppDirect appropriately.
		// Do the negative condition check so that if anything invalid is passed, the default action is taken
		if ($statelessAction != self::ALLOW_STATELESS)
		{
			// Check if the event has the STATELESS flag set. If so, handle the return and abort.
			if (isset($event->flag) && $event->flag == self::FLAG_STATELESS)
				die($event->xmlResponse(false, 'OPERATION_CANCELED', 'The STATELESS event was acknowledged and canceled'));
		}

		// Return the fully-built event definition
		return $event;
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
	
	public function xmlResponse($success, $code, $message, $extraData = array())
	{
		$xmlResult = new SimpleXMLElement('<result></result>');
		$xmlResult->addChild('success', ($success ? 'true' : 'false') );
		if(!$success)
			$xmlResult->addChild('errorCode', $code);
		$xmlResult->addChild('message', $message);

		foreach($extraData as $key => $value) {
			$xmlResult->addChild($key, $value);
		}
		
		return $xmlResult->asXML();		
	}
}

?>
