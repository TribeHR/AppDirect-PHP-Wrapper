# AppDirect PHP Wrapper

This client library simplifies connecting to the AppDirect Network API. All calls will raise an exception on error, so make sure to wrap calls in a try...catch block. For more information on the AppDirect Network API, visit http://info.appdirect.com/developer/docs/getting-started/introduction

This wrapper makes use of the (open source) PHP Library for OAuth by Andy Smith - you'll need to download that library separately, and you can do so here: (open source) [PHP Library for OAuth](http://oauth.googlecode.com/svn/code/php) by [Andy Smith](http://term.ie/blog/): 

### Using the PHP Wrapper

First, make sure you have created your product in the AppDirect marketplace, as you'll need your OAuth key and secret in order to use this library.

1. Include your `OAuth` class files
2. Define your default `APPDIRECT_CONSUMER_KEY` and `APPDIRECT_CONSUMER_SECRET` in `AppDirectConnect.php`
3. Include the library class files by including `AppDirect.php`
4. Create handlers for each AppDirect event endpoint

The following is a simplified example to illustrate catching an AppDirect event

```
// Require the necessary library files
require_once('./path_to_oauth/OAuth.php');
require_once('./path_to_appdirect/AppDirect.php');

// Create a new event, assuming that you're catching the AppDirect
// eventUrl parameter as eventUrl
$event = new AppDirectEvent();

try {
	$event = $event->getEvent($_GET['eventUrl']);
}
catch(AppDirectException $cve) {
	// Do something intelligent with the error, knowing you can access several attributes
	// of the exception object: $cve->http_code, $cve->message, and $cve->errors
}

// Act on the event, perhaps checking any of the following attributes of $event:
// $event->type, $event->flag, $event->payload, $event->creator and $event->returnUrl

// Respond to AppDirect with xmlResponse($successful, $code, $message); The following
// is a sample failed response.
echo $event->xmlResponse(false, 'ACCOUNT_NOT_FOUND', 'Account could not be found');
```

### Credits
Written by the clever folks at [TribeHR](http://tribehr.com)