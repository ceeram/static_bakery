A Component to help creating SOAP services
==========================================

by char101 on June 05, 2009

A component providing automatic WSDL generation using jool.nl
Webservice Helper library, CakePHP caching of generated WSDL, and
automatic handling of SOAP calls.


Concepts
````````

+ SOAP methods will be implemented as model class methods. Models that
  will handle SOAP calls will have suffix Service, for example
  BookService
+ The Soap component will provide function to generate WSDL from the
  model class definition and to handle SOAP calls to the model class
  methods.
+ A controller will act as a SOAP proxy to the service models using
  the SOAP component



Requirements
````````````

+ PHP Soap extension
+ CakePHP 1.2 (haven't tested it in CakePHP 1.1)



Webservice Handler library
``````````````````````````
Download Webservice Handler library from jool.nl site and extract the
contents into app/vendors/wshelper.


Create the SOAP component
`````````````````````````
Save the code below into app/controllers/components/soap.php.
The generated WSDL will be cached into app/tmp/cache directory. If
DEBUG configuration
is greater than 0, the cache file modified time will be compared the
model file
modified time and updated appropriately.


Component Class:
````````````````

::

    <?php 
    vendor('wshelper/lib/soap/IPReflectionClass.class');
    vendor('wshelper/lib/soap/IPReflectionCommentParser.class');
    vendor('wshelper/lib/soap/IPXMLSchema.class');
    vendor('wshelper/lib/soap/IPReflectionMethod.class');
    vendor('wshelper/lib/soap/WSDLStruct.class');
    vendor('wshelper/lib/soap/WSDLException.class');
    
    /**
     * Class SoapComponent
     *
     * Generate WSDL and handle SOAP calls
     */
    class SoapComponent extends Component
    {
    	var $params = array();
    
    	function initialize(&$controller)
    	{
    		$this->params = $controller->params;
    	}
    	
    	/**
    	 * Get WSDL for specified model.
    	 *
    	 * @param string $modelClass : model name in camel case
    	 * @param string $serviceMethod : method of the controller that will handle SOAP calls
    	 */
    	function getWSDL($modelId, $serviceMethod = 'call')
    	{
    		$modelClass = $this->__getModelClass($modelId);
    		$expireTime = '+1 year';
    		$cachePath = $modelClass . '.wsdl';
    		
    		// Check cache if exist
    		$wsdl = cache($cachePath, null, $expireTime);
    
    		// If DEBUG > 0, compare cache modified time to model file modified time
    		if ((Configure::read() > 0) && (! is_null($wsdl))) {
    
    			$cacheFile = CACHE . $cachePath;
    			if (is_file($cacheFile)) {
    				$modelMtime = filemtime($this->__getModelFile($modelId));
    				$cacheMtime = filemtime(CACHE . $cachePath);
    				if ($modelMtime > $cacheMtime) {
    					$wsdl = null;
    				}
    			}
    
    		}
    		
    		// Generate WSDL if not cached
    		if (is_null($wsdl)) {
    		
    			$refl = new IPReflectionClass($modelClass);
    			
    			$controllerName = $this->params['controller'];
    			$serviceURL = Router::url("/$controllerName/$serviceMethod", true);
    
    			$wsdlStruct = new WSDLStruct('http://schema.example.com', 
    					                     $serviceURL . '/' . $modelId, 
    										 SOAP_RPC, 
    										 SOAP_LITERAL);
    			$wsdlStruct->setService($refl);
    			try {
    				$wsdl = $wsdlStruct->generateDocument();
    				// cache($cachePath, $wsdl, $expireTime);
    			} catch (WSDLException $exception) {
    				if (Configure::read() > 0) {
    					$exception->Display();
    					exit();
    				} else {
    					return null;
    				}
    			}
    		}
    
    		return $wsdl;
    	}
    
    	/**
    	 * Handle SOAP service call
    	 *
    	 * @param string $modelId : underscore notation of the called model
    	 *                          without _service ending
    	 * @param string $wsdlMethod : method of the controller that will generate the WSDL
    	 */
    	function handle($modelId, $wsdlMethod = 'wsdl')
    	{
    		$modelClass = $this->__getModelClass($modelId);
    		$wsdlCacheFile = CACHE . $modelClass . '.wsdl';
    
    		// Try to create cache file if not exists
    		if (! is_file($wsdlCacheFile)) {
    			$this->getWSDL($modelId);
    		}
    
    		if (is_file($wsdlCacheFile)) {
    			$server = new SoapServer($wsdlCacheFile);
    		} else {
    			$controllerName = $this->params['controller'];
    			$wsdlURL = Router::url("/$controllerName/$wsdlMethod", true);
    			$server = new SoapServer($wsdlURL . '/' . $modelId);
    		}
    		$server->setClass($modelClass);
    		$server->handle();
    	}
    
    	/**
    	 * Get model class for specified model id
    	 *
    	 * @access private
    	 * @return string : the model id
    	 */
    	function __getModelClass($modelId)
    	{
    		$inflector = new Inflector;
    		return ($inflector->camelize($modelId) . 'Service');
    	}
    
    	/**
    	 * Get model id for specified model class
    	 *
    	 * @access private
    	 * @return string : the model id
    	 */
    	function __getModelId($modelClass)
    	{
    		$inflector = new Inflector;
    		return $inflector->underscore(substr($class, 0, -7));
    	}
    
    	/**
    	 * Get model file for specified model id
    	 *
    	 * @access private
    	 * @return string : the filename
    	 */
    	function __getModelFile($modelId)
    	{
    		$modelDir = dirname(dirname(dirname(__FILE__))) . DS . 'models';
    		return $modelDir . DS . $modelId . '_service.php';
    	}
    }
    ?>



Create the controller that will handle SOAP calls
`````````````````````````````````````````````````
This is an example controller. You can change the method name
that will handle SOAP calls and provide WSDL definition as you wish.
But don't forget to change the arguments to the handle and
getWSDL methods.
Save the file into app/controllers/service_controller.php

Controller Class:
`````````````````

::

    <?php 
    class ServiceController extends AppController
    {
    	public $name = 'Service';
    	public $uses = array('TestService');
    	public $helpers = array();
    	public $components = array('Soap');
    
    	/**
    	 * Handle SOAP calls
    	 */
    	function call($model)
    	{
    		$this->autoRender = FALSE;
    		$this->Soap->handle($model, 'wsdl');
    	}
    
    	/**
    	 * Provide WSDL for a model
    	 */
    	function wsdl($model)
    	{
    		$this->autoRender = FALSE;
    		header('Content-Type: text/xml'); // Add encoding if this doesn't work e.g. header('Content-Type: text/xml; charset=UTF-8'); 
    		echo $this->Soap->getWSDL($model, 'call');
    	}
    }
    ?>



Create the service model
````````````````````````
This is a test model. Save it into app/models/test_service.php. Note
that the webservice handler library parses the method comments to
create the WSDL, so you'll need to make sure that all the function
parameters and return value are documented in the function docblock.
Make sure that you specify the type of each parameters and make sure
the ordering matches the order of the parameters in the function.
(Thanks to Brett Nemeroff for pointing this).


Model Class:
````````````

::

    <?php 
    class TestService extends AppModel
    {
    	var $name = 'TestService';
    	var $useTable = false;
    
    	/**
    	 * Divide two numbers
    	 *
    	 * @param float $a
    	 * @param float $b
    	 * @return float
    	 */
    	function divide($a, $b)
    	{
    		if ($b != 0) {
    			return $a / $b;
    		}
    		return 0;
    	}
    }
    ?>



Testing the service
```````````````````
My favorite tool for testing SOAP services is SoapUI. You can use it
or your
favorite tool to test the service. To access the WSDL, direct your
tool to
http://yourhost/service/wsdl/test. The SOAPAction URL will be
http://yourhost/service/call/test.



.. author:: char101
.. categories:: articles, components
.. tags:: soap,component,webservice,Components

