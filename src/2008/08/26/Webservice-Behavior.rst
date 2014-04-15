Webservice Behavior
===================

by markstory on August 26, 2008

The webservice behavior allows you to easily make requests to
webservices and remote addresses, via GET POST and XMLRPC.

While the CakePHP core makes providing webservices easy. Connecting to
webservices is still a task. When first building my personal site I
looked for an already built solution and found a partial solution in
Felix GeisendÃ¶rferâ€™s WebModel. However, I wanted to remove its
dependancy on cURL as the extension is not always available. I also
wanted to transform it into a behavior, making it easy to reuse and
more conforming to CakePHP standards. The transformation into a
behavior was an easy process. Removing curl and manually writing all
the Socket code was not an appealing prospect. Thankfully, CakePHP 1.2
has the new CakeSocket class which eases the creation of socket
connections. My end result is a behavior that has no extension
dependancies and is a behavior for easy reuse. This is a PHP5 class,
so if you are on PHP4 you need to hack out all the visibility
keywords.

Using the Webservice Behavior
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using the webservice behavior is quite easy. Simply add it to the
$actsAs array for your model

::

    $actsAs = array('webservice');

Is the most simple use of it. There are a number of setup options that
you can set as well. They are mostly related to the socket connection,
and in most cases donâ€™t need much fiddling.

Configuration
`````````````

+ timeout The timeout for the requests defaults to 30 seconds.
+ persistent Should the connection be persistent, opened with pfsock.
+ defaultUrl The default URL to be used.
+ port The port for the connection defaults to 80.
+ protocol The protocol for connection defaults to tcp.
+ followRedirect Should redirects be followed. Defaults to false

Above is the list of options that can be set in the Behavior actsAs
array and a description of what they will do.

Making Requests
~~~~~~~~~~~~~~~

Requests are made with the request() method. A simple use would look
like

::

    $this->Model->request('get');


This would make a request against the defaultUrl set in the model
settings and return the content of that request. The request() method
connects to the defaultUrl if no url is supplied. Several request
types are supported with â€˜getâ€™, â€˜postâ€™ and â€˜xmlâ€™ being the
completed types. I have plans to add SOAP as well, as soon as I can
wrap my head around the documentation. A second argument allows you
set additional headers, data, url, and options for the connection.

::

    $this->Model->request('get', array('data' => array('q' => 'mark story'), 'url' => 'www.google.ca/search')));

The above would do a search on google for â€˜mark storyâ€™.

Debugging your Requests
```````````````````````

There is built-in capabilities to introspect on what is going on in
your request calls. Using getRequestInfo() will return an array of
information pertaining to the last made request. Headers for both the
request and response, as well as cookies, data and connection options
will be returned. I found this to be very handy in my own development,
and I hope you will as well.

Bonus Round XMLRPC
~~~~~~~~~~~~~~~~~~

As a bonus when downloading the WebserviceBehavior you get an
XmlRpcMessage class as well. This is a very simple class to enable the
transmission of XMLRPC requests. I havenâ€™t done any testing with
complicated payloads. But for simple requests it works quite well.
When making requests with the type of xml, supplied data is
automatically converted into an XMLRPC message and sent for you. The
one caveat is that you need to supply a methodName as well.

::

    
    $data = array(
        'methodName' => 'testFunc',
        'data' => array(
            'foo', 'bar', 1
        )
    );
    $result = $this->Model->request('xml', array('data' => $vars));

This will format up an XML message and send it. You can also you the
XMLRPC class on its own of course. It is a full class with a usable
interface. But that is another day and another article. Included with
the class are some tests, they cover the typical use cases that I have
come across so far, but will be expanded as I use it more, so check
back for updates to the classes and tests.

As always Iâ€™d love to hear any feedback you have for this, and I
hope you find them useful.

Behavior Code
~~~~~~~~~~~~~

::

    
    <?php
    /**
     * A simple WebService Behavior class that eases POST & GET requests to foreign pages
     * Entirely PHP based, does not require and modules or cURL.
     * Also has ability to create and send XMLRPC requests.
     * 
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     * 
     * Based on the WebserviceModel authored by Felix GeisendÃ¶rfer (http://debuggable.com)
     *
     * @author Mark Story (mark-story.com)
     * @revision $Revision: 62 $ 
     */
    App::import('Core', 'Socket');
    
    class WebserviceBehavior extends ModelBehavior {
       
    /**
     * User Agent to use for Requests
     *
     * @var string
     **/
    	var $userAgent = 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.8.1.14) Gecko/20080404 Firefox/2.0.0.14';
    	
    /**
     * String of Content Types accepted.
     *
     * @var string
     **/
    	var $acceptTypes = 'text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
    	
    /**
     * Accept-Language Header
     *
     * @var string
     **/
    	var $acceptLanguage = 'en-us,en;q=0.5';
    /**
     * Cookies that come from requests
     *
     * @var array
     */	
    	var $cookies = array();
    	
    /**
     * Contain settings indexed by model name.
     *
     * @var array
     */
    	var $__settings = array();
    	
    /**
     * The valid request types for the behaviour
     * @var array
     */
    	var $_validRequests = array('get', 'post', 'xml');
    	
    /**
     * Information about the last made Request, useful for debugging.
     *
     * @var array
     **/
    	var $_lastInfo = array();
    	
    /**
     * Formatted Data to be sent.
     *
     * @var string
     **/
    	var $_data = null;
    	
    /**
     * Instance of CakeSocket
     *
     * @var Object
     **/
    	var $Socket = null;
    
    /**
     * Settings can be set with the following:
     *
     * timeout   - 	The time to wait before Timing out on a connection.
     *				defaults to 30 sec.
     *
     * persistent - Keep the connection alive between calls.
     *				
     * defaultUrl - The default URL to use for requests. Useful if you have a webservice with only
     *				one URL.  
     *
     * port -       The remote port to use if not 80				
     * 
     */	
    	var $__defaults = array(
    		'timeout' => 30,
    		'persistent' => false,
    		'defaultUrl' => null,
    		'port' => 80,
    		'protocol' => 'tcp',
    		'followRedirect' => true
    	);
        
    	function setup(&$Model, $settings = array()) {
    		$options = am($this->__defaults, $settings);
    	
    		$this->__settings[$Model->name] = $options;
    		
    		if ($options['persistent']) {
    			$this->serviceConnect($options['defaultUrl'], $options);
    		}
    	}
    /**
     * Request
     *
     * Make/Send Requests.  Supports GET, POST and XMLRPC.
     *
     * @param string $type The type of request to make get, post, xml are valid options.
     * @param Array $params Array of Options see below.
     * @return mixed Resulting Page if successful request or false if time out or connection failure.
     *
     * Options:
     *	data    - mixed  - Array of data to send in the request, will be serialized to the correct type. 
     *	url     - string - An alternate URL to use for this request if different from the defaultUrl
     *	headers - array  - Optional Additional Headers you may wish to set.  'headername' => 'value'
     *	options - array  - Additional Connection options to use for this request
     **/
    	function request(&$Model, $type = 'get', $params = array()) {
    		if (!in_array($type, $this->_validRequests)) {
    			return false;
    		}
    		
    		$this->_lastInfo = array();
    		
    		$defaults = array('data' => array(), 'url' => null, 'headers' => array(), 'options' => array());
    		$params = array_merge($defaults, $params);
    				
    		switch ($type) {
    			case 'get':
    			case 'post':
    				$this->_formatUrlData($params['data']);
    				break;
    			case 'xml':
    				$this->_formatXmlData($params['data']);
    				break;
    		}
    		
    		//switch url if necessary
    		if (!empty($params['url'])) {
    			$this->serviceConnect($Model, $params['url'], $params['options']);
    		} elseif (!empty($this->__settings[$Model->name]['defaultUrl'])){
    			$this->serviceConnect($Model, $this->__settings[$Model->name]['defaultUrl'], $params['options']);
    		} else {
    			return false;
    		}
    		
    		//make request.
    		$out = $this->{'_'.$type}($Model, $params);
    		$this->resetService();
    		return $out;
    	}
    	
    /**
     * Connect the Behavior to a new URL
     *
     * @param string $url The URL to connect to.
     * @param array $options Options Array for the new connection. 
     * @return bool success
     **/
    	function serviceConnect(&$Model, $url, $options = array()) {
    		$options = array_merge($this->__settings[$Model->name], $options);
    		$path = $this->_setPath($url);			
    		$options['host'] = $path['host'];
    		
    		if ($this->Socket === null) {
    			$this->Socket = new CakeSocket($options);	
    		} else {
    			if ($this->Socket->connected && $this->__settings[$Model->name]['persistent'] == false) {
    				$this->serviceDisconnect($Model);
    			}
    			$this->Socket->config = $options;
    		}
    		$this->__setInfo(array('connection' => $options, 'host' => $path['host'], 'path' => $path['path']));
    		return $this->Socket->connect();
    	}
    	
    /**
     * Disconnect / Reset the Webservice Socket.
     *
     * @return boolean
     **/
    	function serviceDisconnect(&$Model) {
    		if ($this->Socket !== null) {
    			$this->Socket->disconnect();
    			$this->Socket->reset();
    		}	
    	}
    
    /**
     * Reset the WebService Behavior
     *
     * @return void
     **/
    	function resetService() {
    		$this->_headers = array();
    		$this->_data = null;
    		$this->_rawCookies = null;
    		$this->cookies = null;
    	}
    
    /**
     * Get the last requests' information, good for debugging.
     *
     * @return array
     **/
    	function getRequestInfo() {
    		return $this->_lastInfo;
    	}
    	
    /**
     * Set Cookie data to the Webservice
     *
     * @param string $cookieData Raw cookie Strings. 
     * @return bool
     */
    	function setCookie($cookieData) {
    		$parts = explode('; ', $cookieData);
    		foreach ($parts as $part) {
    			list($name, $value) = explode('=', $part);
    			$cookie[$name] = $value;
    		}
    		$this->cookies[] = $cookie; 		
    		$this->__setInfo('cookie', $cookie);
    		return true;
    	}
    	
    /**
     * GET Request
     *
     * @return Mixed data retrieved from Request
     **/
    	function _get(&$Model, $params = array()) {
    		if (!empty($this->_data)) {
    			$addr = $this->_path . '?' . $this->_data;
     		} else {
    			$addr = $this->_path;
    		}
    		$params['headers']['Host'] = $this->_host;
    		$params['headers']['Connection'] = 'Close';
    		
    		$this->_formatHeaders($params['headers']);
    						
    		$request = "GET {$addr} HTTP/1.0\r\n";
    		$request .= $this->_headers;
    		$request .= "\r\n\r\n";
    		
    		$this->__setInfo('requestHeaders', $request);
    		
    		$this->Socket->write($request);		
    		$response = '';		
    		while ($data = $this->Socket->read()) {
    			$response .= $data;
    		}
    		$this->_parseResponse($response);
    		
    		if ($this->__settings[$Model->name]['followRedirect'] && array_key_exists('Location', $this->response['headers'])) {
    			$this->serviceConnect($Model, $this->response['headers']['Location'], $params);
    			$this->_get($Model);
    		}
    		return $this->response['body'];
    	}
    	
    /**
     * POST Request
     *
     * @return Mixed data retrieved from Request
     **/
    	function _post(&$Model, $params = array()) {				
    		$postHeaders = array(
    			'Host' => $this->_host,
    			'Connection' => 'Close',
    			'Content-Length' => strlen($this->_data),
    		);
    		$params['headers'] = array_merge($params['headers'], $postHeaders);
    		if (!isset($params['headers']['Content-Type'])) {
    			$params['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
    		}
    		
    		$this->_formatHeaders($params['headers']);
    		
    		$request = "POST {$this->_path} HTTP/1.0\r\n";
    		$request .= $this->_headers . "\r\n";
    		$request .= "\r\n";
    		$request .= $this->_data;
    		
    		$this->__setInfo('requestHeaders', $request);
    		
    		$this->Socket->write($request);		
    		$response = '';		
    		while ($data = $this->Socket->read()) {
    			$response .= $data;
    		}
    		$this->_parseResponse($response);
    		
    		if ($this->__settings[$Model->name]['followRedirect'] && array_key_exists('Location', $this->response['headers'])) {
    			$this->serviceConnect($Model, $this->response['headers']['Location'], $params);
    			$this->_data = null;
    			$this->_get($Model, $params);
    		}
    		return $this->response['body'];
    	}	
    	
    /**
     * XMLRPC Request
     *
     * @return Mixed data retrieved from Request
     **/
    	function _xml(&$Model, $params = array()) {
    		$additionalHeaders = array(
    			'Content-Type' => 'text/xml'
    		);
    		$params['headers'] = array_merge($params['headers'], $additionalHeaders);
    		
    		return $this->_post($Model, $params);
    	}
    	
    /**
     * Parse the Reponse from the request, separating the headers from the content.
     *
     * @return void
     **/
    	function _parseResponse($response) {
    		$headers = substr($response, 0, strpos($response, "\r\n\r\n"));
    		$body = substr($response, strlen($headers));
    		
    		//split up the headers
    		$parts = preg_split("/\r?\n/", $headers, -1, PREG_SPLIT_NO_EMPTY);
    		$heads = array();
    		for ($i = 1, $total = sizeof($parts); $i < $total; $i++ ) {
    			list($name, $value) = explode(': ', $parts[$i]);
    			$heads[$name] = $value;
    		}
    		if (array_key_exists('Set-Cookie', $heads)) {
    			$this->setCookie($heads['Set-Cookie']);
    		}
    		$this->__setInfo('responseHeaders', $heads);
    		
    		$this->response['headers'] = $heads;
    		$this->response['body'] = trim($body);		
    	}
    	
    /**
     * Set the host and path for the webservice.
     * @param string $url The complete url you want to connect to.
     * @return array Host & Path
     **/
    	function _setPath($url) {
    		$port = 80;
    		if (preg_match('/^https?:\/\//', $url)) {
    			$url = substr($url, strpos($url, '://') + 3);			
    		}
    		if (strpos($url, '/') === false) {
    			$host = $url;
    			$path = '/';
    		} else {
    			$host = substr($url, 0, strpos($url, '/'));
    			$path = substr($url, strlen($host));
    		}
    		if ($path == '') {
    			$path = '/';
    		}
    		$this->_host = $host;
    		$this->_path = $path;
    		return array('host' => $host, 'path' => $path, 'port' => $port);
    	}
    		
    /**
     * Formats Additional Request Headers 
     *
     * @return void
     **/
    	function _formatHeaders($headers = array()) {
    		$headers['User-Agent'] = $this->userAgent;
    		$headers['Accept'] = $this->acceptTypes;
    		$headers['Accept-Language'] = $this->acceptLanguage;
    
    		if (!empty($this->cookies)) {
    			foreach ($this->cookies as $cookie) {
    				reset($cookie);
    				$key = key($cookie);
    				$value = $cookie[$key];
    				$cooks[] = "$key=$value";
    			}
    			$headers['Cookie'] = implode('; ', $cooks);
    		}
    		
    		foreach ($headers as $name => $value) {
    			$tmp[] = "$name: $value";
    		}		
    		$header = implode("\r\n", $tmp);
    		$this->__setInfo('requestHeaders', $header);
    		$this->_headers = $header;
    	}
    	
    /**
     * Format data for HTTP get/post requests
     *
     * @return void
     **/
    	function _formatUrlData($params) {
    		$postData = array();
            
            foreach ($params as $key => $val) {
               $postData[] = urlencode($key).'='.urlencode($val);
            }
            $this->_data = join('&', $postData);
    		$this->__setInfo('data', $this->_data);
    	}
    	
    /**
     * Format data for XmlRpc requests.
     *
     * XMLRPC Serialization is performed here. Params for XMLRPC are a bit different than simple post/get.
     * be sure to specify a methodName in $params.  The data will be auto-typed based on the Data type in PHP
     * If arrays have any non-numeric keys they will become <structs> If you wish to force a type you can do so by changing
     * the element to an array. See the example below.
     *
     * usage. $this->request('xml', array('data' => $bigArray, 'methodName' => 'getImages'));
     *
     * Data array Sample:
     *
     * $bigArray = array(
     *		'simpleString' => 'sample',	
     *		'integerVal' => 1,
     *		'doubleVal' => 3.145,
     * 		'forcedInt' => array('value' => '1', 'type' => 'int'),
     *		'arrayType' => array('value' => array(2, 3, 4), 'type' => 'array'),
     *	);
     *
     * Keep in mind that when coercing types bad things can happen, if you are incorrect in your assumptions.
     *
     * @return void
     **/
    	function _formatXmlData($params) {
    		if (!class_exists('Xml')) {
    			App::import('Core', 'Xml');
    		}
    		$defaults = array('methodName' => '', 'data' => array());
    		$params = array_merge($defaults, $params);
    		
    		$message =& new XmlRpcMessage();
    		$message->methodName = $params['methodName'];
    		$message->setData($params['data']);
    		$result = $message->toString();
    	
    		$this->_data = $result;	
    	}
    	
    /**
     * Add into the lastInfo array.  Works like Controller::set();
     *
     * @return void
     **/
    	function __setInfo($one, $two = null) {
    		$data = array();
    
    		if (is_array($one)) {
    			if (is_array($two)) {
    				$data = array_combine($one, $two);
    			} else {
    				$data = $one;
    			}
    		} else {
    			$data = array($one => $two);
    		}
    		$this->_lastInfo = array_merge($this->_lastInfo, $data);
    	}
    
    /**
     * Destructor, used to disconnect from current connection.
     *
     */
    	function __destruct() {
    		$Model = null;
    		$this->serviceDisconnect($Model);
    	}
    }
    
    
    /**
     * XmlRpcMessage
     *
     * A Simple Class that creates a wrapper for formatting and creating XMLRPC requests
     *
     * @package webservice.behavior
     * @author Mark Story
     **/
    class XmlRpcMessage extends Object {
    /**
     * Instance of XML object
     *
     * @var object
     **/
    	var $_xml = null;
    /**
     * Request Method Name
     *
     * @var string
     **/
    	var $methodName = '';
    /**
     * Data the payload of the XMLRPC message
     *
     * @var mixed
     **/
    	var $_data = array();
    
    /**
     * Data Types that can be used
     *
     * @var array
     */
    	var $_dataTypes = array(
    		 'double', 'int', 'date', 'string', 'array', 'struct' 
    	);
    /**
     * Constructor
     *
     **/
    	function __construct() {
    		$this->_xml =& new Xml(null, array('format' => 'tags'));
    	}
    	
    /**
     * Convert Message to XML string
     *
     * @return string of Parsed XMLRPC message
     **/
    	function toString() {
    		$this->_createXml();
    		return $this->_xml->toString(array('cdata' => false, 'header' => true));
    	}
    	
    /**
     * Set the Data array, clears and sets the data internal data structure
     * Checks for type casting and auto type casts if necessary 
     *
     * Data array Sample:
     *
     * $bigArray = array(
     *		'simpleString' => 'sample',	
     *		'integerVal' => 1,
     *		'doubleVal' => 3.145,
     * 		'forcedInt' => array('value' => '1', 'type' => 'int'),
     *		'arrayType' => array('value' => array(2, 3, 4), 'type' => 'array'),
     *	);
     *
     * Keep in mind that when coercing types bad things can happen, if you are incorrect in your assumptions.
     *
     * @return bool
     **/
    	function setData($data) {
    		if (!is_array($data)) {
    			$data = (array)$data;
    		}
    		foreach ($data as $param) {
    			if (is_array($param) && isset($param['type']) && isset($param['value']) && count($param) == 2) {
    				$this->addParam($param['value'], $param['type']);				
    			} else {
    				$this->addParam($param);
    			}
    		}
    		return true;
    	}
    	
    /**
     * Add a parameter to the Internal Data array
     * Data array Sample:
     *
     * Keep in mind that when coercing types bad things can happen, if you are incorrect in your assumptions.
     *
     * @param string $value 
     * @param string $type 
     * @return bool
     */
    	function addParam($value, $type = null) {
    		if (is_null($type)) {
    			$type = $this->_typecast($value);
    		}
    		if (is_array($value)) {
    			foreach ($value as $k => $v) {
    				$t = $this->_typecast($v);
    				$value[$k] = array('value' => $v, 'type' => $t);
    			}
    		}
    		$this->_data[] = array('type' => $type, 'value' => $value);
    	}
    /**
     * Get the data inside the XmlRpcMessage
     *
     * @return mixed
     */
    	function getData() {
    		return $this->_data;
    	}
    	
    /**
     * Reset the Message and start over
     *
     * @return void
     */
    	function reset() {
    		$this->methodName = null;
    		$this->_data = array();
    		$this->_xml =& new Xml(null, array('format' => 'tags'));
    	}
    /**
     * Typecast a value
     * Retrieve the proper XMLRPC data type for a value
     *
     * @param string $value 
     * @return string Type identifier
     */
    	function _typecast($value) {
    		$type = null;		
    
    		if (is_string($value)) {
    			$type = 'string';
    		}
    		if (is_int($value)) {
    			$type = 'int';
    		}
    		if (is_float($value)) {
    			$type = 'double';
    		}
    		if (is_bool($value)) {
    			$type = 'boolean';
    		}
    		if (is_array($value)) {
    			$type = 'array';
    			
    			$valueKeys = array_keys($value);
    			foreach($valueKeys as $vk) {
    				if (!is_numeric($vk)) {
    					$type = 'struct';
    					break;
    				}
    			}
    		}
    		return $type;
    	}
    /**
     * Convert internal data to Xml
     *
     * @return void
     **/
    	function _createXml() {
    		$methodCall =& $this->_xml->createElement('methodCall', null);
    		$methodCall->createElement('methodName', $this->methodName);	
    		$this->_paramsEl =& $methodCall->createElement('params', null);	
    		
    		$this->__parseData($this->_data, $this->_paramsEl, false);
    	}
    
    /**
     * Parse internal data structure into XML data structures.
     * Auto type casts data and checks for forcing.
     *
     * @return Array of xmlobjects
     **/
    	function __parseData($data, $parent, $inner = false) {
    		$out = array();
    		foreach ($data as $param) {
    			extract($param);
    			
    			$valueElement =& $parent->createElement('value', null);
    			
    			switch ($type) {
    				case 'array':
    					$arrayEl =& $valueElement->createElement('array', null);
    					$dataEl =& $arrayEl->createElement('data', null);				
    					$this->__parseData($value, $dataEl, true);
    					break;
    				case 'struct':
    					$structEl =& $valueElement->createElement('struct', null);
    					foreach ($value as $memberKey => $memberValue) {
    						$memberEl =& $structEl->createElement('member', null);
    						$memberEl->createElement('name', $memberKey);
    						$this->__parseData(array($memberValue), $memberEl, true);
    					}
    					break;
    				case 'date':
    					$valueElement->createElement('dateTime.iso8601', date('Ymd\TH:i:s', strtotime($value) ));
    					break;
    				case 'base64':
    				case 'string':
    				case 'int':
    				case 'double':
    					$valueElement->createElement($type, $value);
    					break;
    				case 'boolean':
    					$bool = (boolean)$value ? '1' : '0';
    					$valueElement->createElement('boolean', $bool);
    				break;				
    			}
    
    			if ($inner == false) {
    				$paramElement =& $parent->createElement('param', null);
    				$valueElement->setParent($paramElement);
    			} else {
    				$paramElement =& $valueElement;
    			}
    			$out[] = $paramElement;
    		}
    		return $out;		
    	}
    	
    } // END class XmlRpcMessage extends Object
    ?>



Test Case
~~~~~~~~~

::

    
    <?php
    
    
    App::import('Behavior', 'Webservice');
    
    class TestWebserviceBehavior extends WebserviceBehavior {
    	
    	function testXML(&$model, $input) {
    		$this->_formatXmlData($input);
    		$result = str_replace(array("\t", "\n"), array('', ''), $this->_data);
    		return $result;
    	}
    }
    
    /**
     * Base model that to load Webservice behavior on every test model.
     *
     * @package app.tests
     * @subpackage app.tests.cases.behaviors
     */
    class WebserviceTestModel extends CakeTestModel
    {
    	/**
    	 * Behaviors for this model
    	 *
    	 * @var array
    	 * @access public
    	 */
    	var $actsAs = array('TestWebservice' => array('defaultUrl' => 'www.cakephp.org'));
    	
    	var $useTable = false;
    }
    
    /**
     * Model used in test case.
     *
     * @package	app.tests
     * @subpackage app.tests.cases.behaviors
     */
    class Service extends WebserviceTestModel {
    	/**
    	 * Name for this model
    	 *
    	 * @var string
    	 * @access public
    	 */
    	var $name = 'Service';
    }
    
    class WebserviceTestCase extends CakeTestCase {
    /**
     * Method executed before each test
     *
     * @access public
     */
    	function startTest() {
    		$this->Service =& new Service();
    	}
    	
    	function testHeaderFormatting() {
    		$this->Service->request('get', array('headers' => array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest')));
    		$info = $this->Service->getRequestInfo();
    		$this->assertPattern("/HTTP_X_REQUESTED_WITH: XMLHttpRequest\r\n/", $info['requestHeaders']);
    		$this->assertPattern("/User-Agent: /", $info['requestHeaders']);
    		$this->assertPattern("/Accept: /", $info['requestHeaders']);
    		
    		$this->Service->Behaviors->TestWebservice->userAgent = 'CakePHP WebService';
    		$this->Service->Behaviors->TestWebservice->acceptTypes = 'text/html';
    		$this->Service->request();
    		$result = $this->Service->getRequestInfo();
    		$this->assertPattern("/User-Agent: CakePHP WebService\r\n/", $result['requestHeaders']);
    		$this->assertPattern("/Accept: text\/html/", $result['requestHeaders']);
    	}
    	
    	function testGetRequest() {		
    		$result = $this->Service->request();
    		$this->assertPattern('/<html/', $result);
    		$this->assertPattern('/CakePHP/', $result);
    		$this->assertPattern('/<\/html>/', $result);
    		
    		$result = $this->Service->request('get');
    		$this->assertPattern('/<html/', $result);
    		$this->assertPattern('/CakePHP/', $result);
    		$this->assertPattern('/<\/html>/', $result);
    		
    		$result = $this->Service->request('get', array('url' => 'www.google.com'));	
    		$this->assertPattern('/<html/', $result);
    		$this->assertPattern('/Google/', $result);
    		$this->assertPattern('/<\/html>/', $result);
    	
    		$data = array('q' => 'cakePHP');
    		$result = $this->Service->request('get', array('url' => 'www.google.com/search', 'data' => $data));
    		$this->assertPattern('/<html/', $result);
    		$this->assertPattern('/Google/', $result);
    		$this->assertPattern('/http:\/\/www.cakephp.org/', $result);
    		$this->assertPattern('/<\/html>/', $result);
    	}
    	
    	function testPostRequest() {				
    		$vars = array('data[User][username]' => 'test-account', 'data[User][psword]' => 'totally-wrong-password', 'data[User][redirect]' => '', '_method' => 'POST');
    		$result = $this->Service->request('post', array('data' => $vars, 'url' => 'book.cakephp.org/users/login/'));
    		$this->assertPattern('/<html/', $result);
    		$this->assertPattern('/CakePHP/', $result);
    		$this->assertPattern('/<\/html>/', $result);
    		$this->assertPattern('/Login failed. Invalid username or password/', $result);
    			
    		$vars = array('param' => 'val ue', 'foo' => 'b>r');
    		$this->Service->request('post', array('data' => $vars));				
    		$info = $this->Service->getRequestInfo();
    		$expected = 'param=val+ue&foo=b%3Er';
    		$this->assertEqual($info['data'], $expected);
    	}
    	
    	function testXmlRpcRequest() {
    		//string and int types
    		$vars = array(
    			'methodName' => 'testFunc',
    			'data' => array(
    				'foo', 'bar', 1
    			)
    		);
    		$result = $this->Service->testXml($vars);
    		
    		$expected = '<?xml version="1.0" encoding="UTF-8" ?><methodCall><methodName>testFunc</methodName><params><param><value><string>foo</string></value></param><param><value><string>bar</string></value></param><param><value><int>1</int></value></param></params></methodCall>';
    		$this->assertEqual($result, $expected);
    		
    		//array
    		$input = array(
    			'methodName' => 'testFunc',
    			'data' => array(
    				array(6, 9, 4)
    			)
    		);
    		$result = $this->Service->testXml($input);
    		$expected = '<?xml version="1.0" encoding="UTF-8" ?><methodCall><methodName>testFunc</methodName><params><param><value><array><data><value><int>6</int></value><value><int>9</int></value><value><int>4</int></value></data></array></value></param></params></methodCall>';
    		$this->assertEqual($result, $expected);
    
    		// struct
    		$input = array(
    			'methodName' => 'testFunc',
    			'data' => array(
    				array('foo' => 'bar', 'two' => 9)
    			)
    		);
    		$result = $this->Service->testXml($input);
    		$expected = '<?xml version="1.0" encoding="UTF-8" ?><methodCall><methodName>testFunc</methodName><params><param><value><struct><member><name>foo</name><value><string>bar</string></value></member><member><name>two</name><value><int>9</int></value></member></struct></value></param></params></methodCall>';
    		$this->assertEqual($result, $expected);
    		
    		// date
    		$input = array(
    			'methodName' => 'testFunc',
    			'data' => array(
    				array('type' => 'date', 'value' => '2005-06-12 12:30:30')
    			)
    		);
    		$result = $this->Service->testXml($input);
    		$expected = '<?xml version="1.0" encoding="UTF-8" ?><methodCall><methodName>testFunc</methodName><params><param><value><dateTime.iso8601>20050612T12:30:30</dateTime.iso8601></value></param></params></methodCall>';
    		$this->assertEqual($result, $expected);	
    		
    	}
    }



.. author:: markstory
.. categories:: articles, behaviors
.. tags:: behavior,webservice,Behaviors

