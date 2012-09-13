<?php

class AppDirectConnector
{	
	var $key = APPDIRECT_CONSUMER_KEY;
	var $secret = APPDIRECT_CONSUMER_SECRET;
	var $endpoint = 'https://www.appdirect.com/rest/api/';
	
	var $consumer = false;
	
	function __construct($key = null, $secret = null)
	{
		if($key !== null)
			$this->key = $key;			
		if($secret !== null)
			$this->secret = $secret;			
		
		// Create our OAuth Consumer
		$this->consumer = new OAuthConsumer($this->key, $this->secret);
		
	}
	
	function getSignedUrl($url)
	{
		$request = OAuthRequest::from_consumer_and_token($this->consumer, NULL, 'GET', $url, NULL);
		$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->consumer, NULL);
		$url = $request->to_url();
		return $url;	
	}
	
	function get($path, $data = array())
	{
		// Create a signed request for this GET
		$url = $this->endpoint . $path;		
		$request = OAuthRequest::from_consumer_and_token($this->consumer, NULL, 'GET', $url, NULL);
		$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->consumer, NULL);
		$auth_header = $request->to_header();
		
		// Setup curl
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FAILONERROR, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header));

		// Fetch the data
		$response = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		// Do some verification on it
		$curl_error = ($code > 0 ? null : curl_error($curl) . ' (' . curl_errno($curl) . ')');

        curl_close($curl);
        
        if ($curl_error) // Connection Error?
		{
            throw new AppDirectConnectionException('An error occurred while connecting to AppDirect: ' . $curl_error);
			return;
        }
		elseif ($code == 200) // Created Successfully
		{
			$result = simplexml_load_string($response);
	        return $result;  	
		}
		elseif ($code == 422) { // Unprocessible Entity
			$errors = new SimpleXMLElement($response);
			throw new AppDirectValidationException($code, $errors);  		
			return;
		}
	}

	function post($path, $data = array())
	{
		// Create a signed request for this POST
		$url = $this->endpoint . $path;		
		$request = OAuthRequest::from_consumer_and_token($this->consumer, NULL, 'POST', $url, null);
		$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->consumer, NULL);
		$auth_header = $request->to_header();
		
		// Setup curl
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FAILONERROR, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header, 'Content-type: application/xml'));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		// Fetch the data
		$response = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);


		// Do some verification on it
		$curl_error = ($code > 0 ? null : curl_error($curl) . ' (' . curl_errno($curl) . ')');
        curl_close($curl);
        
        if ($curl_error) // Connection Error?
		{
            throw new AppDirectConnectionException('An error occurred while connecting to AppDirect: ' . $curl_error);
			return;
        }
		elseif ($code == 200) // Created Successfully
		{
			$result = simplexml_load_string($response);
	        return $result;  	
		}
		elseif ($code == 422) { // Unprocessible Entity
			$errors = new SimpleXMLElement($response);
			throw new AppDirectValidationException($code, $errors);  		
			return;
		}
	}
	
}

?>