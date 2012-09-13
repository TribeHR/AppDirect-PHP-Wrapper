<?php 
//Base class for AppDirect Data Objects.
class AppDirectBase {
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		if ($xml) {
		    //Load object dynamically and convert SimpleXMLElements into strings
		    foreach($xml as $key => $element)
			{ 
				if(count($element) > 0)
					$this->$key = new AppDirectBase($element);
				else
					$this->$key = (string)$element; 
		    }
		}
	}
		
	public function getXMLObject(&$xml = null) {
	  	if ($xml === null) {
			$xml = simplexml_load_string(sprintf("<?xml version='1.0' encoding='utf-8'?><%s></%s>", $this->getName(), $this->getName()));
	  	}
	  	foreach (get_object_vars($this) as $key=>$val) {
	  		if ($key != 'connector') {
		  		if (is_object($val) && method_exists($val, "getXMLObject")) {
		  			$node = $xml->addChild($key);
		  			$val->getXMLObject($node);	  			
		  		} elseif ($val !== null) {
		  			$xml->addChild($key,htmlentities($val, ENT_QUOTES));
		  		}
	  		}
	  	}
	  	return $xml;	  	
	}
	
	public function getXML() {
		$xml = $this->getXMLObject();
		return $xml->asXML();
	}
	
}

?>