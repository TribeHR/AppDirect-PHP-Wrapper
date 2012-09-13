<?php
class AppDirectException extends Exception {}

class AppDirectConnectionException extends AppDirectException {}

class AppDirectValidationException extends AppDirectException {
	var $errors;
	var $http_code;
	
	public function AppDirectValidationException($http_code, $error) {
		$this->http_code = $http_code;		

		$message = '';
		$this->errors = array();
		foreach ($error as $key=>$value) {
			if ($key == 'error') {
				$this->errors[] = $value;
				$message .= $value . ' ';
			}
		}

		parent::__construct($message, intval($http_code));
	}
}

class AppDirectNotFoundException extends AppDirectException {
	var $errors;
	var $http_code;
	
	public function AppDirectNotFoundException($http_code, $error) {
		$this->http_code = $http_code;		

		$message = '';
		$this->errors = array();
		foreach ($error as $key=>$value) {
			if ($key == 'error') {
				$this->errors[] = $value;
				$message .= $value . ' ';
			}
		}

		parent::__construct($message, intval($http_code));
	}	
}

class AppDirectError
{
	var $field;
	var $message;
}

?>