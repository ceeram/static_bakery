HTTP Client class
=================

by rossoft on September 22, 2006

Http Client Class Uses Pear or Curl for a HTTP/HTTPS GET/POST request.
Handles cookies and HTTP auth test


Component Class:
````````````````

::

    <?php 
    /**
     * Http Client Class
     *
     * Uses Pear or Curl for a HTTP/HTTPS GET/POST request
     * (preferred engine: Curl)
     *
     * Handles Cookies & HTTP Auth
     *
     * @author RosSoft
     * @version 0.23
     * @license MIT
     *
     * If you need cookies or HTTP Auth, don't use the
     * function wrappers http_client_get() or http_client_post()
     * Use the class instead. Example:
     * $client=& new HttpClient();
     * $client->user='username'; //the request requires basic http auth
     * $client->password='xxxx';
     * $client->post('http://example.com/login',array('admin'=>'1')); //this will remember the cookie set by that request
     * $client->get('http://example.com/admin/index'); //the cookie is used
     *
     * You can send HTTP Headers with
     * $client->request_headers=array('Content-Type'=>'application/x-www-form-urlencoded; charset=utf-8');
     *
     */
    
    /**
     * Gets the content of an HTTP/HTTPS GET request
     * @param string $url Destination URL
     * @param array $params Associative array of params (don't need to urlencode them)
     * @param string $engine Can be 'Pear','Curl','Auto' ('Auto' selects the best available)
     * @return string The content
     *
     */
    function http_client_get($url,$params=array(),$engine='Auto')
    {
    	$client=& new HttpClient($engine);
    	return $client->get($url,$params);
    }
    
    /**
     * Gets the content of an HTTP/HTTPS POST request
     * @param string $url Destination URL
     * @param array $params Associative array of params (don't need to urlencode them)
     * @param string $engine Can be 'Pear','Curl','Auto' ('Auto' selects the best available)
     * @return string The content
     */
    function http_client_post($url,$params=array(),$engine='Auto')
    {
    	$client=& new HttpClient($engine);
    	return $client->post($url,$params);
    }
    
    /**
     * Gets the content of an HTTP/HTTPS HEAD request
     * @param string $url Destination URL
     * @param array $params Associative array of params (don't need to urlencode them)
     * @param string $engine Can be 'Pear','Curl','Auto' ('Auto' selects the best available)
     * @return string The content
     */
    function http_client_head($url,$params=array(),$engine='Auto')
    {
    	$client=& new HttpClient($engine);
    	return $client->head($url);
    }
    
    /**
     * Main class
     * You can use indirectly through http_client_get
     * or http_client_post wrappers or directly by:
     *
     * $client=HttpClient();
     * $document=$client->get('http://www.google.es/search',array('hl'=>'en', 'q'=>'cakephp'));
     *
     * Is exactly the same as:
     * $client=& new HttpClient();
     * $document=$client->get('http://www.google.es/search?hl=en&q=cakephp'));
     *
     */
    
    class HttpClient extends Object
    {
    	/**
    	 * @var integer Http Connection timeout in seconds
    	 */
    	var $timeout=30;
    
    	/**
    	 * @var integer Maximum number of redirections (avoid loops)
    	 */
    	var $max_redirections=10;
    
    
    	/**
    	 * @var string Http Basic Auth username
    
    	 */
    	var $user=null;
    
    	/**
    	 * @var string Http Basic Auth password
    	 */
    
    	var $password=null;
    
    	/**
    	 * @var array HTTP Request Headers to be sent
    	 */
    	var $request_headers=array();
    
    
    	/**
    	 * @access protected Instance of HttpClient[Engine]
    	 * Engines: 'Pear','Curl','Auto'
    	 * 'Auto' tries Curl, if not loaded then Pear (default)
    	 */
    	var $_engine;
    
    	function HttpClient($engine='Auto')
    	{
    		if (!in_array($engine,array('Pear','Curl','Auto')))
    		{
    			$message="HttpClient: unknown engine $engine";
    			$this->log($message,LOG_ERROR);
    			die($message);
    		}
    		if ($engine=='Auto')
    		{
    			if (function_exists('curl_init'))
    			{
    				$engine='Curl';
    			}
    			else
    			{
    				$engine='Pear';
    			}
    		}
    		$engine='HttpClient' . $engine;
    		$this->_engine=& new $engine($this);
    	}
    
    	/**
    	 * Gets the content of an HTTP/HTTPS GET request
    	 * @param string $url Destination URL
    	 * @param array $params Associative array of params (don't need to urlencode them)
    	 * @return string The content
    	 */
    
    	function get($url,$params=array())
    	{
    		return $this->_engine->get($url,$params);
    	}
    
    	/**
    	 * Gets the content of an HTTP/HTTPS POST request
    	 * @param string $url Destination URL
    	 * @param array $params Associative array of params (don't need to urlencode them)
    	 * @return string The content
    	 */
    	function post($url,$params=array())
    	{
    		return $this->_engine->post($url,$params);
    	}
    
    	/**
    	 * Gets the content of an HTTP/HTTPS HEAD request
    	 * @param string $url Destination URL
    	 * @param array $params Associative array of params (don't need to urlencode them)
    	 * @return string The content
    	 */
    	function head($url,$params=array())
    	{
    		return $this->_engine->head($url,$params);
    	}
    
    	/**
    	 * Returns information of last response
    	 * (the content of the array is engine dependant)
    	 * @return array
    	 */
    	function response()
    	{
    		return $this->_engine->response();
    	}
    
    	/**
    	 * Returns the last response http code
    	 * @return integer
    	 */
    	function response_code()
    	{
    		return $this->_engine->response_code();
    	}
    
    	/**
    	 * Returns headers of last response
    	 * @return array
    	 */
    	function headers()
    	{
    		return $this->_engine->headers();
    	}
    
    
    	function _convert_params($params)
    	{
    	  	$array= array();
    		foreach ($params as $name=>$value)
    		{
    		  	$array[] = "$name=".urlencode($value);
    		}
    		return implode("&", $array);
    	}
    
    }
    
    /**
     * Engine Client: Pear
     */
    class HttpClientPear extends Object
    {
    	var $_client;
    	var $_response;
    	var $_parent;
    
    	function __construct($parent)
    	{
    		$this->_parent=& $parent;
    
    		/*
    		 * vendors/pear/init.php content: <?php ini_set('include_path',ini_get('include_path').PATH_SEPARATOR . dirname(__FILE__)); ? >
    		 */
    		vendor('pear/init');
    		vendor('pear/HTTP/Client');
    
    		$this->_client = new HTTP_Client();
    	}
    
    	function get($url,$params=array())
    	{
    		$this->_init();
    		$this->_client->get($url, $params);
    		return $this->_execute();
    	}
    
    	function head($url,$params=array())
    	{
    		$this->_init();
    		if ($params)
    		{
    			$url=$url . '?' . $this->_parent->_convert_params($params);
    		}
    		$this->_client->head($url);
    		return $this->_execute();
    	}
    
    	function _init()
    	{
    		$this->_client->setDefaultHeader($this->_parent->request_headers);
    		$this->_client->setMaxRedirects($this->_parent->max_redirections);
    		$params=array('timeout'=>$this->_parent->timeout);
    		if ($this->_parent->user!==null)
    		{
    			$params['user']=$this->_parent->user;
    			$params['password']=$this->_parent->password;
    		}
    		$this->_client->setRequestParameter($params);
    	}
    
    	function _execute()
    	{
    		$this->_response=$this->_client->currentResponse();
        	return $this->_response['body'];
    	}
    
    	function post($url,$params)
    	{
    		$this->_client->setDefaultHeader($this->_parent->request_headers);
            $this->_client->post($url, $params);
            return $this->_execute();
    	}
    
    	function response()
    	{
    		return $this->_response;
    	}
    
    	function response_code()
    	{
    		return $this->_response['code'];
    	}
    
    	function headers()
    	{
    		return $this->_response['headers'];
    	}
    
    
    
    }
    
    /**
     * Engine Client: Curl
     */
    define('HTTP_CLIENT_CURL_COOKIES',CACHE . 'http_curl_cookies.txt');
    class HttpClientCurl extends Object
    {
    	var $_client;
    	var $_response;
    	var $_headers;
    	var $_body;
    	var $_parsing;
    	var $_parent;
    
    	function __construct($parent)
    	{
    		$this->_parent=& $parent;
    		file_put_contents(HTTP_CLIENT_CURL_COOKIES,'');
    
    	}
    
    	function get($url,$params)
    	{
    		$this->_init();
    		if (count($params))
    		{
    			$url=$url . '?' . $this->_parent->_convert_params($params);
    		}
    		curl_setopt($this->_client, CURLOPT_POST, 0);
    		curl_setopt($this->_client, CURLOPT_URL, $url);
    		return $this->_execute();
    	}
    
    	function post($url,$params)
    	{
    		$this->_init();
    		curl_setopt($this->_client, CURLOPT_POST, 1);
    		curl_setopt($this->_client, CURLOPT_POSTFIELDS, $this->_parent->_convert_params($params));
    		curl_setopt($this->_client, CURLOPT_URL, $url);
    		$result=$this->_execute();
    		return $result;
    	}
    
    	function head($url,$params)
    	{
    		if ($params)
    		{
    			$url=$url . '?' . $this->_parent->_convert_params($params);
    		}
    		$this->_init();
    		curl_setopt($this->_client, CURLOPT_NOBODY, 1);
    		curl_setopt($this->_client, CURLOPT_URL, $url);
    		$result=$this->_execute();
    		return $result;
    	}
    
    
    
    	function response()
    	{
    		return $this->_response;
    	}
    
    	function response_code()
    	{
    		return $this->_response['http_code'];
    	}
    
    
    	function _execute()
    	{
    		$this->_parsing=0; //start
    		$this->_headers=array();
    		$this->_body='';
    		curl_exec($this->_client);
    		$this->_response=curl_getinfo($this->_client);
    		curl_close($this->_client);
    		return $this->_body;
    	}
    
    	function _parse_content($ch,$string)
    	{
    		switch ($this->_parsing)
    		{
    			case 0: //start. skip http status code
    				$this->_parsing=1; //header
    				break;
    			case 1: //header
    				if (strpos($string,':'))
    				{
    					$header=split(': ',$string);
    					$key=$header[0];
    					$value=$header[1];
    					while ((strpos($value,"\n")==strlen($value) - 1)
    						  || (strpos($value,"\r")==strlen($value) - 1))
    					{
    						$value=substr($value,0,strlen($value) - 1); //remove \n
    					}
    					$this->_headers[$key]=$value;
    					$this->_headers[low($key)]=$value;
    				}
    				else //end of header
    				{
    					$this->_parsing=2;
    				}
    				break;
    			case 2: //body
    				$this->_body.=$string;
    				break;
    		}
    		return strlen($string);
    	}
    
    	function _init()
    	{
    		$this->_client= curl_init();
    		if (! $this->_client)
    		{
    			die('HttpClientCurl: curl_init() fails');
    		}
    		curl_setopt($this->_client, CURLOPT_HEADER,1);
    		curl_setopt($this->_client, CURLOPT_WRITEFUNCTION, array($this,'_parse_content'));
    		curl_setopt($this->_client, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($this->_client, CURLOPT_TIMEOUT,$this->_parent->timeout);
           	curl_setopt($this->_client, CURLOPT_COOKIEFILE,HTTP_CLIENT_CURL_COOKIES);
           	curl_setopt($this->_client, CURLOPT_COOKIEJAR,HTTP_CLIENT_CURL_COOKIES);
           	curl_setopt($this->_client, CURLOPT_HTTPHEADER,$this->_parent->request_headers);
    		curl_setopt($this->_client, CURLOPT_FOLLOWLOCATION, true);
    		curl_setopt($this->_client, CURLOPT_MAXREDIRS, $this->_parent->max_redirections);
    
           	if ($this->_parent->user !== null)
           	{
           		curl_setopt($this->_client, CURLOPT_USERPWD, "{$this->_parent->user}:{$this->_parent->password}");
           	}
    	}
    
    	function headers()
    	{
    		return $this->_headers;
    	}
    }
    ?>


.. meta::
    :title: HTTP Client class
    :description: CakePHP Article related to Cookies,HTTP,Components
    :keywords: Cookies,HTTP,Components
    :copyright: Copyright 2006 rossoft
    :category: components

