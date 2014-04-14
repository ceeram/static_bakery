Remote Content Parser tool using CUrl
=====================================

by sky_l3ppard on June 20, 2009

This tool gives you friendly functions to retrieve HTML or whatever
from remotely or locally accessible files. And also friendly functions
to parse this content. You can either use session or not then
connected, use POST data in the forms and download, upload files. No
special libraries are required, only built in PHP CUrl library.


Steps:
``````

+ Download the component
+ Prepare your controller
+ HTML parsing example
+ Advanced example with open session and POST data



Download the component
~~~~~~~~~~~~~~~~~~~~~~

First you have to download RemoteParserComponent and save it in your
app/controllers/components/ directory as file named content_parser.php


Component Class:
````````````````

::

    <?php 
    // File -> app/controllers/components/remote_parser.php
    
    /** 
     * RemoteParser Component
     * 
     * @author sky_l3ppard
     * @version 1.0
     * @license MIT
     * @category Components
     */
    
    define('RP_DEBUG_LEVEL_NONE', 0);
    define('RP_DEBUG_LEVEL_ERRORS', 1);
    define('RP_DEBUG_LEVEL_FULL', 2);
    
    class RemoteParserComponent extends Object {
    	/**
    	 * Reference to controller
    	 * 
    	 * @var Object
    	 * @access Private
    	 */
    	var $__controller = null;
    	/**
    	 * Content from remote location
    	 * 
    	 * @var String
    	 * @access Private
    	 */
    	var $__content = null;
    	/**
    	 * Offset in remote content
    	 * 
    	 * @var Integer
    	 * @access Public
    	 */
    	var $offset = null;
    	/**
    	 * Length of remote content in bytes
    	 * 
    	 * @var Integer
    	 * @access Public
    	 */
    	var $length = null;
    	/**
    	 * Component debug level:
    	 * 		0 - no debug output at all
    	 * 		1 - logging only errors
    	 * 		2 - full debug 
    	 * 
    	 * @var Integer
    	 * @access Public
    	 */
    	var $debug_level = RP_DEBUG_LEVEL_FULL;
    	/**
    	 * Resource handle returned by "curl_init" function more information can be
    	 * found at "http://www.php.net/manual/en/function.curl-init.php"
    	 * 
    	 * @var Resource
    	 * @access Private
    	 */
    	var $__connection = null;
    	/**
    	 * Result of the last executed operation, more information about
    	 * this resultset can be found at "http://www.php.net/manual/en/function.curl-getinfo.php"
    	 * 
    	 * @var Array 
    	 * @access Private
    	 */
    	var $__connection_execution_info = null;
    	/**
    	 * True to keep the CURL connection openned, required then web site is
    	 * using session for remote browsing
    	 * 
    	 * @var Boolean 
    	 * @access Private
    	 */
    	var $__connection_keep_open = false;
    	/**
    	 * User agent to show then connected to remote site
    	 * 
    	 * @var String
    	 * @access Public
    	 */
    	var $user_agent = 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.3) Gecko/2008092510 Ubuntu/8.04 (hardy) Firefox/3.0.3';
    	/**
    	 * Number of seconds then connection times out
    	 * 
    	 * @var Integer
    	 * @access Public
    	 */
    	var $connection_timeout = 30;
    	/**
    	 * True to fallow redirects then connecting to remote site
    	 * 
    	 * @var Boolean
    	 * @access Public
    	 */
    	var $redirects_fallow = true;
    	/**
    	 * Number of maximum redirects on remote site
    	 * 
    	 * @var integer
    	 * @access public
    	 */
    	var $redirects_max = 5;
    	
    	
    	/**
    	 * Initializes this component
    	 * 
    	 * @param Object $controller - reference to controller
    	 * @access Public
    	 */
    	function initialize(&$controller) {
    		$this->__controller = &$controller;
    	}
    	
    	/**
    	 * Initializes Session for a CUrl connection
    	 * 
    	 * @access Private
    	 */
    	function __initializeSession() {
    		if (empty($this->__connection)) {
    			$this->__initializeConnection();
    		}
    		
    		$this->debug_log("Using session for open connection");
    		curl_setopt($this->__connection, CURLOPT_COOKIESESSION, true);
    		curl_setopt($this->__connection, CURLOPT_COOKIEFILE, "remote_connection_parser");
    		curl_setopt($this->__connection, CURLOPT_COOKIEJAR, "remote_connection_parser");
    		curl_setopt($this->__connection, CURLOPT_COOKIE, session_name() . '=' . session_id());
    	}
    	
    	/**
    	 * Initializes CUrl connection handle
    	 * 
    	 * @access Private
    	 */
    	function __initializeConnection() {
    		$this->debug_log("Initializing CURL connection");
    		$this->__connection = curl_init();
    		
    		curl_setopt($this->__connection, CURLOPT_CONNECTTIMEOUT, $this->connection_timeout);
    		curl_setopt($this->__connection, CURLOPT_USERAGENT, $this->user_agent);
    		curl_setopt($this->__connection, CURLOPT_FOLLOWLOCATION, $this->redirects_fallow);
    		curl_setopt($this->__connection, CURLOPT_MAXREDIRS, $this->redirects_max);
    	}
    
    	/**
    	 * Logs component operations
    	 * 
    	 * @param String $msg - message to log
    	 * @param Integer $level - level of debug:
    	 * 		0 - no debug output at all
    	 * 		1 - logging only errors
    	 * 		2 - full debug 
    	 * @access Public
    	 */
    	function debug_log($msg, $level = RP_DEBUG_LEVEL_FULL) {
    		if ($this->debug_level == RP_DEBUG_LEVEL_NONE) {
    			return;
    		}
    
    		if ($this->debug_level == RP_DEBUG_LEVEL_ERRORS && $level != RP_DEBUG_LEVEL_ERRORS) {
    			return;
    		}
    		
    		if ($this->debug_level == RP_DEBUG_LEVEL_ERRORS) {
    			$this->log($msg);
    		}
    		else {
    			$this->log($msg, LOG_DEBUG);
    		}
    	}
    	
    	/**
    	 * Sets CUrl connection to be open, and initializes session
    	 * 
    	 * @access Public
    	 */
    	function setOpenConnection() {
    		$this->__connection_keep_open = true;
    		$this->__initializeSession();
    	}
    	
    	/**
    	 * Adds option to the CUrl connection handle, do not forget to reset it
    	 * if using diferent method later. More information can be found at
    	 * "http://www.php.net/manual/en/function.curl-setopt.php"
    	 * 
    	 * @param Integer $code - CUrl option code
    	 * @param Mixed $value - CUrl option value, type depends on option
    	 * @access Public 
    	 */
    	function setOption($code, $value) {
    		if (!$this->__connection_keep_open) {
    			return;
    		}
    		
    		if (empty($this->__connection)) {
    			return;
    		}
    		$this->debug_log("Adding option code[$code], value[$value]");
    		curl_setopt($this->__connection, $code, $value);
    	}
    	/**
    	 * Called before execution of Remote operation, by default
    	 * initializes CUrl connection it not set to open
    	 * 
    	 * @param String $url - reference to the passed url
    	 * @access Private
    	 */
    	function __beforeExecution(&$url) {
    		$url = str_replace(' ', '%20', $url);
    		if ($this->__connection_keep_open) {
    			return;
    		}
    		$this->__initializeConnection();
    	}
    	
    	/**
    	 * Called after execution of Remote operation, by default
    	 * closes CUrl connection it not set to open
    	 * 
    	 * @param Integer $result - reference to the result
    	 * @access Private
    	 */
    	function __afterExecution(&$result) {
    		if ($this->__connection_keep_open) {
    			return;
    		}
    		$this->close(); 
    	}
    	
    	/**
    	 * Gets the result of execution as an array or one option. Read more
    	 * "http://www.php.net/manual/en/function.curl-getinfo.php"
    	 * 
    	 * @param String $opt - Name of option in result
    	 * @access Public
    	 * @return Array of result indicators, or one option
    	 */
    	function getExecutionInfo($opt = false) {
    		$this->debug_log("Retrieving execution info by option [$opt]");
    		return ($opt) ? $this->__connection_execution_info[$opt] : $this->__connection_execution_info;
    	}
    	
    	/**
    	 * Closes opened CUrl connection
    	 * Notice: if you are using open connection do not forget to close it
    	 * 
    	 * @access Public
    	 */
    	function close() {
    		if (empty($this->__connection)) {
    			return;
    		}
    		$this->debug_log("Closing CURL connection");
    		curl_close($this->__connection);
    		unset($this->__connection);
    	}
    	
    	/**
    	 * Uploads a file on usualy FTP location $url
    	 * 
    	 * @param String $url - location to upload file
    	 * @param Array $options - possible values:
    	 * 		file - a path of the file to upload; Mandatory
    	 * 		username - username used then logging in; Default - anonymous
    	 * 		username - password used then logging in; Default - anonymous
    	 * @access Public
    	 * @return Boolean - true on success, false on failure
    	 */
    	function uploadRemoteFile($url, $options) {
    		$this->debug_log("Uploading Remote File to location [$url]");
    		$this->__beforeExecution($url);
    		
    		if (!array_key_exists('file', $options)) {
    			$this->debug_log("FAILED to upload file, no file was specified in options", RCP_DEBUG_LEVEL_ERRORS);
    		}
    		
    		$result = false;
    		if (file_exists($options['file']) && (($file = fopen($options['file'], "r")) !== false)) {
    			//set additional curl options
    			curl_setopt($this->__connection, CURLOPT_INFILE, $file);
     			curl_setopt($this->__connection, CURLOPT_INFILESIZE, filesize($options['file']));
     			curl_setopt($this->__connection, CURLOPT_UPLOAD, true);
     			$credentials = empty($options['username']) ? 'anonymous' : $options['username'];
     			$credentials .= ':';
     			$credentials .= empty($options['password']) ? 'anonymous' : $options['password'];
    			curl_setopt($this->__connection, CURLOPT_USERPWD, $credentials);
    			
    			curl_exec($this->__connection);
    			$this->__connection_execution_info = curl_getinfo($this->__connection);
    			curl_close($this->__connection);
    			fclose($file);
    			
    			$kbs = sprintf("%01.2f KB", $this->getExecutionInfo('size_upload') / 1024);
    			$this->debug_log("Uploading file[$kbs], HTTP_CODE [".$this->getExecutionInfo('http_code')."]");
    			$result = $this->getExecutionInfo('http_code') == 200 || $this->getExecutionInfo('http_code') == 301;
    		}
    		else {
    			$this->debug_log("FAILED to upload file, could not open [{$options['file']}]", RCP_DEBUG_LEVEL_ERRORS);
    		}
    		$this->__afterExecution($result);
    		return $result;
    	}
    	
    	/**
    	 * Downloads file from remote location
    	 * 
    	 * @param String $url - URL of file to download
    	 * @param String $location - path of the file to save downloaded
    	 * @access Public
    	 * @return Boolean - true on success, false on failure
    	 */
    	function downloadRemoteFile($url, $location) {
    		$this->debug_log("Downloading Remote File from location [$url]");
    		$this->__beforeExecution($url);
    		
    		curl_setopt($this->__connection, CURLOPT_HEADER, 0);
    		curl_setopt($this->__connection, CURLOPT_ENCODING, ''); //accepts all types of encoded content
    		curl_setopt($this->__connection, CURLOPT_RETURNTRANSFER, 0);
    		curl_setopt($this->__connection, CURLOPT_URL, $url);
    		
    		$result = false;
    		if (($file = fopen($location, "w")) !== false) {
    			//set additional curl options
    			curl_setopt($this->__connection, CURLOPT_FILE, $file);
    			curl_exec($this->__connection);
    			$this->__connection_execution_info = curl_getinfo($this->__connection);
    			fclose($file);
    			
    			$kbs = sprintf("%01.2f KB", $this->getExecutionInfo('size_download') / 1024);
    			$this->debug_log("Downloading file [$kbs], HTTP_CODE [".$this->getExecutionInfo('http_code')."]");
    			$result = $this->getExecutionInfo('http_code') == 200 || $this->getExecutionInfo('http_code') == 301;
    		}
    		else {
    			$this->debug_log("FAILED to open file [$location] for writing", RCP_DEBUG_LEVEL_ERRORS);
    		}
    		
    		$this->__afterExecution($result);
    		return $result;
    	}
    	
    	/**
    	 * Downloads remote content using POST data
    	 * 
    	 * @param String $url - URL of the page to download
    	 * @param Mixed $post - POST data can be passed as:
    	 * 		Array - pairs of key/values (e.g.: array('username' => 'gosu', 'password' => 'hard_to_guess')
    	 * 		String - sometimes POST data must be sent as string (e.g.: username=gosu&password=hard_to_guess)
    	 * @param String $referer - link of which we are referring to this $url
    	 * @access Public
    	 * @return Boolean - true on success, false on failure
    	 */
    	function openPostRemoteFile($url, $post = array(), $referer = '') {
    		$this->debug_log("Opening Remote File with Post data on location [$url]");
    		$this->__beforeExecution($url);
    		
    		curl_setopt($this->__connection, CURLOPT_SSL_VERIFYPEER, true);
    		if (ereg('^(https)', $url)) {
        		curl_setopt($this->__connection, CURLOPT_SSL_VERIFYPEER, false);
    		}
    		
    		curl_setopt($this->__connection, CURLOPT_REFERER, $referer);
    		curl_setopt($this->__connection, CURLOPT_POST, 1);
    		curl_setopt($this->__connection, CURLOPT_ENCODING, ''); //accepts all types of encoded content
    		curl_setopt($this->__connection, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($this->__connection, CURLOPT_POSTFIELDS, $post);
    		curl_setopt($this->__connection, CURLOPT_URL, $url);
    		
    	    $content = curl_exec($this->__connection);
    	    $this->setContent($content);
    	    $this->__connection_execution_info = curl_getinfo($this->__connection);
    		
    		$kbs = sprintf("%01.2f KB", $this->getExecutionInfo('size_download') / 1024);
    		$this->debug_log("Got remote content size [$kbs], HTTP_CODE [".$this->getExecutionInfo('http_code')."]");
    		
    		$result = $this->getExecutionInfo('http_code') == 200 || $this->getExecutionInfo('http_code') == 301;
    		$this->__afterExecution($result);
    		return $result;
    	}
    	
    	/**
    	 * Downloads content from web site
    	 * 
    	 * @param String $url - URL of the page to download
    	 * @param String $referer - link of which we are referring to this $url
    	 * @access Public
    	 * @return Boolean - true on success, false on failure
    	 */
    	function openRemoteFile($url, $referer = '') {
    		$this->debug_log("Opening Remote File on location [$url]");
    		$this->__beforeExecution($url);
    		
    		curl_setopt($this->__connection, CURLOPT_SSL_VERIFYPEER, true);
    		if (ereg('^(https)', $url)) {
        		curl_setopt($this->__connection, CURLOPT_SSL_VERIFYPEER, false);
    		}
    		
    		curl_setopt($this->__connection, CURLOPT_REFERER, $referer);
    		curl_setopt($this->__connection, CURLOPT_POST, 0);
    		curl_setopt($this->__connection, CURLOPT_ENCODING, ''); //accepts all types of encoded content
    		curl_setopt($this->__connection, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($this->__connection, CURLOPT_URL, $url);
    		
    		$content = curl_exec($this->__connection);
    	    $this->setContent($content);
    	    $this->__connection_execution_info = curl_getinfo($this->__connection);
    		
    		$kbs = sprintf("%01.2f KB", $this->getExecutionInfo('size_download') / 1024);
    		$this->debug_log("Got remote content size [$kbs], HTTP_CODE [".$this->getExecutionInfo('http_code')."]");
    		
    		$result = $this->getExecutionInfo('http_code') == 200 || $this->getExecutionInfo('http_code') == 301;
    		$this->__afterExecution($result);
    		return $result;
    	}
    	
    	/**
    	 * Sets the content
    	 * 
    	 * @param String $content - content
    	 * @access Public
    	 */
    	function setContent($content) {
    		unset($this->__content);
    		$this->__content = $content;
    		$this->offset = 0;
    		$this->length = strlen($this->__content);
    		$this->debug_log("setting new content, length [$this->length]");
    	}
    	
    	/**
    	 * Gets the reference to content
    	 * 
    	 * @access Public
    	 * @return String - reference to content
    	 */
    	function &getContent() {
    		return $this->__content;
    	}
    	
    	/**
    	 * Reads content from offset till the first occurrence of given $pattern
    	 * and sets the offset at the ending of the pattern
    	 * 
    	 * @param String $pattern - pattern to search for
    	 * @access Public
    	 * @return String - content till given pattern on success
    	 * 		boolean false on failure, must be checked like (e.g.: $result === false)
    	 */
    	function readTo($pattern) {
    		$result = false;
    		if (empty($this->__content)) {
    			return $result;
    		}
    		
    		$this->debug_log("Reading content till PATTERN[$pattern], OFFSET[$this->offset]");
    		$posTo = strpos($this->__content, $pattern, $this->offset);
    		if ($posTo !== false) {
    			$length = $posTo - $this->offset;
    			$result = substr($this->__content, $this->offset, $length);
    			$this->offset = $posTo + strlen($pattern); 
    		}
    		return $result;
    	}
    	
    	/**
    	 * Reads block of content delimited by $from and $to patterns
    	 * 
    	 * @param String $from - begining of block
    	 * @param String $to - ending of block
    	 * @access Public
    	 * @return String - content block on success
    	 * 		boolean false on failure, must be checked like (e.g.: $result === false)
    	 */
    	function readFromTo($from, $to) {
    		$result = false;
    		if (empty($this->__content)) {
    			return $result;
    		}
    		
    		$this->debug_log("Reading content FROM PATTERN[$from] TO PATTERN [$to], OFFSET[$this->offset]");
    		$posFrom = strpos($this->__content, $from, $this->offset);
    		if ($posFrom !== false) {
    			$posFrom += strlen($from);
    			$posTo = strpos($this->__content, $to, $posFrom);
    			if ($posTo !== false) {
    				$length = $posTo - $posFrom;
    				$result = substr($this->__content, $posFrom, $length);
    				$this->offset = $posTo + strlen($to);
    			}
    		}
    		return $result;
    	}
    }
    ?>



Prepare your controller
~~~~~~~~~~~~~~~~~~~~~~~

After we downloaded the component we must include it in the
controller. And maybe override some default settings if needed

Including component to your controller
``````````````````````````````````````

Controller Class:
`````````````````

::

    <?php 
    // File -> app/controllers/your_controller.php
    
    class YourController extends AppController {
        var $name = 'ControllerName';
        var $components = array('RemoteParser');
    }
    ?>


Overriding default settings if needed
`````````````````````````````````````

Controller Class:
`````````````````

::

    <?php 
    // File -> app/controllers/your_controller.php
    
    class YourController extends AppController {
        var $name = 'ControllerName';
        var $components = array('RemoteParser');
        
        function beforeFilter() {
        	//set debug to log only errors mode: value - 1
        	$this->RemoteParser->debug_level = RP_DEBUG_LEVEL_ERRORS;
        	//connection timout in seconds, default - 30
        	$this->RemoteParser->connection_timeout = 15;
        }
    }
    ?>



HTML parsing example
~~~~~~~~~~~~~~~~~~~~

In this example we will connect to the CakePHP bakery and parse all
article names and links, add the following function into your
controller

::

    <?php
    function parsing_example() {
        //lets look what articles are at the bakery today
        if (!$this->RemoteParser->openRemoteFile('http://bakery.cakephp.org/categories/view/5')) {
        	$err_str = 'oops lets check our internet connection, HTTP_CODE: ';
        	$err_str .= $this->RemoteParser->getExecutionInfo('http_code');
        	die($err_str);
        }
        
        $article_list = array();
        //now we will parse HTML for articles
        while(($article = $this->RemoteParser->readFromTo('class="published"', '<h4>')) !== false) {
        	preg_match('@href="([^"]*)">([^<]*)@smi', $article, $matches);
        	$article_list[$matches[1]] = $matches[2];
        }
        //we got our article list in pairs : article_link/article_title 
        debug($article_list);
        $this->autoRender = false;
    }
    ?>



Advanced example with open session and POST data
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


This is the list of steps which we will do in this example:
```````````````````````````````````````````````````````````

+ We will try to login in bloglines website
+ If login fails with our user, we try to register
+ After we are logged in, we will retrieve some data

[p]Notice: CakePHP has a good security component which won't let these
operations to process, so we test it on a site which has security
holes. No offense to this website, just showing a missing security
issue and encouraging every user to use Security component.
[p]Add the following function to your controller:

::

    <?php
    function login_example() {
        //we need to keep session open
        $this->RemoteParser->setOpenConnection();
        //lets first try to login
        $our_email = 'bloger@mailinator.com';
        $password = 'you_will_never_guess';
        $login_link = 'http://www.bloglines.com/login';
        
        //post data used in login form
        $POST_data = array(
        	'email' => $our_email,
        	'password' => $password
        );
        
        //lets try to login
        if (!$this->RemoteParser->openPostRemoteFile($login_link, $POST_data)) {
        	die('Failed open, code: '.$this->RemoteParser->getExecutionInfo('http_code'));
        }
        
        //check if login is successful
        if ($this->RemoteParser->getExecutionInfo('redirect_count') == 0) {
        	//we have to register first
        	$reg_link = 'http://www.bloglines.com/register';
        	//post data used in registration form
        	$POST_data = array(
        		'email' => $our_email,
        		'password1' => $password,
        		'password2' => $password,
        		'tzcode' => 1,
        		'language' => 1
        	);
        	//lets register new account
        	if (!$this->RemoteParser->openPostRemoteFile($reg_link, $POST_data)) {
        		die('Failed open, code: '.$this->RemoteParser->getExecutionInfo('http_code'));
        	}
        	//registration is done
        }
        
        //lets read some stuff from our account
        if (!$this->RemoteParser->openRemoteFile('http://www.bloglines.com/profile?mode=5')) {
        	die('Failed open, code: '.$this->RemoteParser->getExecutionInfo('http_code'));
        }
        //this is some kind 'Search API Access Key' from our account
        $key = $this->RemoteParser->readFromTo('<td class="description">', '<');
        echo $key;
        
        //we must close our connection then using open session
        $this->RemoteParser->close();
        $this->autoRender = false;
    }
    ?>

That's it, enjoy..

.. meta::
    :title: Remote Content Parser tool using CUrl
    :description: CakePHP Article related to post,curl,parser,sky leppard,http client,web client,open session,html parser,Components
    :keywords: post,curl,parser,sky leppard,http client,web client,open session,html parser,Components
    :copyright: Copyright 2009 sky_l3ppard
    :category: components

