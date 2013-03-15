

cPanel API component
====================

by %s on July 15, 2008

A simple CakePHP component for cPanel XMLAPI calls (
http://www.cpanel.net/plugins/xmlapi/ ). Requires cURL support.
Update: added WHM accessKey support
The cPanel API component requires cURL to connect to the cPanel
server.
The cURL part can be replaced with CakePHP's internal HttpSocket
class, but at the time of this writing there is no SSL support in the
HttpSocket class, so that cURL is used in this example.


Component Class:
````````````````

::

    <?php 
    /**
     * The cPanel API component provides easy controller integration with cPanel API calls.
     * Requires cURL support. 
     * 
     * @author Hendrik Daldrup <hendrik@jinarigo.ca>
     */
    class CpanelApiComponent extends Object {
    
    	var $disableStartup = true;
    	
    	/**
    	 * WHM domain name
    	 *
    	 * @var string
    	 * @access private
    	 */
    	var $__domain = '';
    	
    	/**
    	 * WHM port
    	 * default http port 2086
    	 * default https port 2087
    	 * 
    	 * @var integer
    	 * @access private
    	 */
    	var $__port = 2086;
    	
    	/**
    	 * WHM username
    	 *
    	 * @var string
    	 * @access private
    	 */
    	var $__user = '';
    	
    	/**
    	 * WHM password
    	 *
    	 * @var string
    	 * @access private
    	 */
    	var $__pass = '';
    	
    	/**
    	 * WHM accessKey
    	 *
    	 * @var string
    	 * @access private
    	 */
    	var $__accessKey = '';
    	
    	/**
    	 * Connect via https
    	 *
    	 * @var boolean
    	 * @access private
    	 */
    	var $__ssl = false;
    	
    	/**
    	 * cPanel URL string
    	 *
    	 * @var string
    	 * @access private
    	 */
    	var $__url = '';
    	
    	/**
    	 * Generate the cPanel URL, returns true on success.
    	 *
    	 * @param array $params Must include domain, user and pass. Port and SSL optional.
    	 * @return boolean
    	 */
    	function init($params = array()) {
    		
    		if (isset($params['domain']) && $params['domain'] != '') $this->__domain = $params['domain'];
    		else return false;
    		
    		if (isset($params['user']) && $params['user'] != '') $this->__user = $params['user'];
    		else return false;
    		
    		if (isset($params['pass']) && $params['pass'] != '') { 
    			$this->__pass = $params['pass'];
    		} else if (isset($params['accessKey']) && $params['accessKey'] != '') { 
    			$this->__accessKey = $params['accessKey'];
    		} else { 
    			return false;
    		}
    		
    		if (isset($params['port']) && $params['port'] != '') $this->__port = $params['port'];
    		if (isset($params['ssl']) && $params['ssl'] != '') $this->__ssl = $params['ssl'];
    
    		if ($this->__ssl) {
    			$this->__url = 'https';
    		} else {
    			$this->__url = 'http';
    		}
    		$this->__url .= '://'.$this->__domain.':'.$this->__port;
    		
    		return true;
    	}
    	
    	/**
    	 * Sends a cPanel API query and returns the result 
    	 *
    	 * @param string $query cPanel API query to send, e.g.: '/xml-api/applist'
    	 * @return string
    	 */
    	function query($query = null) {
    		if ($query) {
    			$ch = curl_init();
    			curl_setopt($ch, CURLOPT_URL, $this->__url.$query);
    			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    			if (isset($this->__accessKey) && $this->__accessKey != '') {
    				curl_setopt($ch, CURLOPT_HEADER, 0);
                	$customHeader[0] = "Authorization: WHM ".$this->__user.':'.$this->__accessKey;
                	curl_setopt($ch,CURLOPT_HTTPHEADER, $customHeader);
    			} else {
    			  	curl_setopt($ch, CURLOPT_USERPWD, $this->__user.':'.$this->__pass);
    				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    			}
    			$result = curl_exec($ch);
    			curl_close($ch);
    
    			return $result;
    		}
    		return false;
    	}
    	
    }	
    ?>



Controller Class:
`````````````````

::

    <?php 
    class CpanelController extends AppController 
    {
    	var $name = 'Cpanel';
    	var $components = array('CpanelApi');
    	
    	function cpanelTest() {
    		if ($this->CpanelApi->init(array(
    			'domain' => 'WhmDomainName',
    			'user' => 'WhmUsername',
    			'pass' => 'WhmPassword',
    			//'accessKey' => 'WhmAccessKey',
    			'port' => 2086,
    			'ssl' => false))) 
    		{
    			$cpanelData = $this->CpanelApi->query('/xml-api/applist');
    			$this->set('cpanelData', $cpanelData);
    		} else {
    			$this->Session->setFlash('Error in CpanelApiComponent init()');
    		}
    	}
    }
    ?>

Replace the WhmDomainName, WhmUsername and WhmPassword with the
correct values of your WHM account.
The port and ssl values are optional, just make sure to change both,
if you wish to use SSL support.
You can use the result to extract the data as needed. In this case it
simply sends the result to the view.

It's also possible to use your WHM access key now. To do so, simply
uncomment the 'accessKey' line in above controller example and remove
the 'pass' line. Make sure to enter your access key as a single line,
without any additional characters.

I hope this is usefull to someone and I will add a HttpSocket example,
once SSL support is available.


.. meta::
    :title: cPanel API component
    :description: CakePHP Article related to api,xml,component,curl,cpanel,whm,api component curl x,Components
    :keywords: api,xml,component,curl,cpanel,whm,api component curl x,Components
    :copyright: Copyright 2008 
    :category: components

