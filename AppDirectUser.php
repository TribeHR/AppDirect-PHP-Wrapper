<?php

class AppDirectUser extends AppDirectBase
{
	var $email;
	var $firstName;
	var $lastName;
	var $openId;

	public function __construct(SimpleXMLElement $xml = null)
	{
		$this->connector = new AppDirectConnector();
		if ($xml) {
		    //Load object dynamically and convert SimpleXMLElements into strings
		    foreach($xml as $key => $element)
			{ 
				$this->$key = (string)$element; 
		    }
		}
	}	
}

?>