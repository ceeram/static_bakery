NiceHead helper with autoloading of javascript and css
======================================================

by biesbjerg on May 14, 2007

Like cake isn't already doing enough magic... Here is a helper to make
it even better! Injects CSS/JS into the head tag in your layout and
autoloads css/js files for you based on current controller and action.


The setup
~~~~~~~~~

Credits:

Kudos to `http://rossoft.wordpress.com`_ and `jirikupiainen.com`_ for
inspiration!

Include NiceHead in your helpers array like this:

::

    
    <?php
    class TestsController extends AppController {
    
    	var $name = 'Tests';
    	var $helpers = array('NiceHead');
    	
    	function admin_index()
    	{ .....

Put this in your layout between the head tags :

::

    
    <head>
    ...
    <?php if(isset($niceHead)) $niceHead->flush();?>
    ...
    </head>

Now you can do this something like this in your views:


::

    
    <?php
    // Inject css file in head
    $niceHead->css('css_file');
    
    // Inject javascript file in head
    $niceHead->js('js_file');
    
    // Inject javascript block in head
    $niceHead->jsBlock('alert("hello world!");');
    
    // Inject css block
    $niceHead->cssBlock('.class{ background:blue; }');
    
    // Inject Raw code
    $niceHead->cssBlock('I dont know what this text is doing in the head!');
    ?>



Get funky with prototype
~~~~~~~~~~~~~~~~~~~~~~~~

Additionally I included some methods that requires prototype and Dan
Webb's DomReady.

Get prototype: `http://www.prototypejs.org`_
Get onDOMReady.js: `http://smoothoperatah.com/files/onDOMReady.js`_
Put the files in webroot/js and include them in your head before doing
$niceHead->flush(); :

::

    
    <?php e($javascript->link('prototype', 'onDOMReady'));?>


Now you should be able to use the extra methods:

::

    
    <?php
    // Inject javascript block in head to load on window load
    $niceHead->jsOnLoad('alert("This will execute when all contents has loaded');
    
    // Inject javascript block in head to load on DOM ready
    $niceHead->jsOnReady('alert("This will execute when DOM has loaded');
    ?>



Autoload magic
~~~~~~~~~~~~~~

By default NiceHead will try to autoload JS and CSS for you.

::

    
    For this url: www.domain.com/admin/users/login

NiceHead will first check if these two files exists:

::

    
    webroot/themed/current_theme/css/users/users.css
    webroot/themed/current_theme/css/users/users_admin_login.css

If not it'll look for:

::

    
    webroot/css/users/users.css
    webroot/css/users/users_admin_login.css

If files exist in any of above places it will auto inject them into
the head of the layout.

It does exactly the same for javascript files.

If you for some reason don't want this feature it can be disabled in
the helper:

::

    
    class NiceHeadHelper extends Helper
    {
    	/**
    	 * Autoload configuration
    	 * 
    	 * Put files in your CSS/JS
    	 * /app/webroot/css|js/controller/controller.css|controller_action.css
    	 * /app/webroot/themed/theme/css|js/controller/controller.css|controller_action.css
    	 * 
    	 */
    	var $autoloadCss = true;
    	var $autoloadJs = true;
    ......



That's all folks!
~~~~~~~~~~~~~~~~~

Now, before I forget, here is the actual helper:

::

    
    <?php
    /**
     *	NiceHead helper
     *	@author Kim Biesbjerg
     * 	@desc 	This helper can inject CSS/JS into the head of your layout
     * 			and autoload CSS/JS based on current controller/action
     * 
     * 			Requires PrototypeJS and Dan Webb's DomReady to function properly.
     * 			Prototype: www.prototypejs.org
     * 			DomReady: http://smoothoperatah.com/files/onDOMReady.js
     * 	@version 19. april, 2007 
     */
    class NiceHeadHelper extends Helper
    {
    	/**
    	 * Autoload configuration
    	 * 
    	 * Put files in your CSS/JS
    	 * /app/webroot/css|js/controller/controller.css|controller_action.css
    	 * /app/webroot/themed/theme/css|js/controller/controller.css|controller_action.css
    	 * 
    	 */
    	var $autoloadCss = true;
    	var $autoloadJs = true;
    	
    	/**
    	 * We use Cake's own Html/Javascript helpers
    	 * to generate tags to wrap around registered items
    	 *
    	 * @var array
    	 */
    	var $helpers = array('Html', 'Javascript');
    
    	/**
    	 * Order to flush registered items in <head>
    	 *
    	 * @var array
    	 */
    	var $priority = array('js', 'css', 'jsOnReady', 'jsOnLoad', 'jsBlock', 'cssBlock', 'raw');
    	
    	/**
    	 * Holds our registered items
    	 *
    	 * @var array
    	 */
    	var $_registered = array();
    	
    	function __construct()
    	{
    		   static $library = array();
    		   $this->_registered =& $library;
    	}
    
    	function beforeRender()
    	{
    		$this->_autoload();
    	}
    	
    	/**
    	 * Function to check if file exists and autoload
    	 * if $autloadCss/$autoloadJs is set to true
    	 */
    	function _autoload()
    	{
    		/**
    		 * Get current controller and action
    		 */
    		$controller = $this->params['controller'];
    		$action = $this->params['action'];
    		
    		/**
    		 * Check if we are supposed to autoload controller/action css
    		 */
    		if($this->autoloadCss)
    		{
    			/**
    			 * CSS base paths
    			 */
    			$themedCssPath = WWW_ROOT . $this->themeWeb . CSS_URL . $controller . DS;
    			$commonCssPath = WWW_ROOT . CSS_URL . $controller . DS;
    
    			/**
    			 * Check if CSS file for current controller exists
    			 */
    			if(file_exists($themedCssPath . $controller . '.css') || file_exists($commonCssPath . $controller . '.css'))
    			{
    				$this->css($controller . DS . $controller);
    			}
    			
    			/**
    			 * Check if CSS file for current action exists
    			 */
    			if(file_exists($themedCssPath . $controller . '_' . $action . '.css') || file_exists($commonCssPath . $controller . '_' . $action . '.css'))
    			{
    				$this->css($controller . DS . $controller . '_' . $action);
    			}
    		}
    		
    		/**
    		 * Check if we are supposed to autoload controller/action js
    		 */
    		if($this->autoloadJs)
    		{		
    			/**
    			 * JS base paths
    			 */
    			$themedJSPath = WWW_ROOT . $this->themeWeb . JS_URL . $controller . DS;
    			$commonJSPath = WWW_ROOT . JS_URL . $controller . DS;
    			
    			/**
    			 * Check if JS file for current controller exists
    			 */
    			if(file_exists($themedJSPath . $controller . '.JS') || file_exists($commonJSPath . $controller . '.JS'))
    			{
    				$this->js($controller . DS . $controller);
    			}
    			
    			/**
    			 * Check if JS file for current action exists
    			 */
    			if(file_exists($themedJSPath . $controller . '_' . $action . '.js') || file_exists($commonJSPath . $controller . '_' . $action . '.js'))
    			{
    				$this->js($controller . DS . $controller . '_' . $action);
    			}
    		}
    	}
    	
    	/**
    	 * Includes a block of javascript on dom load
    	 *
    	 * @param string $input
    	 */
    	function jsOnReady($input)
    	{
    		$this->_register($input, 'jsOnReady');
    	}
    	
    	/**
    	 * Includes a block of javascript on window load
    	 *
    	 * @param string $input
    	 */
    	function jsOnLoad($input)
    	{
    		$this->_register($input, 'jsOnLoad');
    	}
    	
    	/**
    	 * Includes an external javascript file
    	 *
    	 * @param string $input
    	 */
    	function js($input)
    	{
    		$this->_register($input, 'js');
    	}
    	
    	/**
    	 * Includes a block of javascript
    	 *
    	 * @param string $input
    	 */
    	function jsBlock($input)
    	{
    		$this->_register($input, 'jsBlock');
    	}
    	
    	/**
    	 * Includes an external stylesheet
    	 *
    	 * @param string $input
    	 */
    	function css($input)
    	{
    		$this->_register($input, 'css');
    	}
    	
    	/**
    	 * Includes a block of styles
    	 *
    	 * @param string $input
    	 */
    	function cssBlock($input)
    	{
    		$this->_register($input, 'cssBlock');
    	}
    	
    	function raw($input)
    	{
    		$this->_register($input, 'raw');
    	}
    	
    	/**
    	 * Internal function used to register items
    	 *
    	 * @param string $item
    	 * @param string $type
    	 */
        function _register($item, $type)
        {
        	if(!array_key_exists($type, $this->_registered))
        	{
        		$this->_registered[$type] = array();
        	}
        	
        	if(!in_array($item, $this->_registered[$type]))
            {
                $this->_registered[$type][] = $item;
            }                   
        }                                          
    
    	/**
    	 * Output the registered items
    	 *
    	 */
        function flush()
        {
        	foreach($this->priority as $type)
        	{
        		if(array_key_exists($type, $this->_registered))
        		{
        			$items = $this->_registered[$type];
    	    		
        			switch($type)
    	    		{
    					case 'css':
    	    				foreach($items as $item)
    	    				{
    	    					e($this->Html->css($item));
    	    				}
    	    				break;
    	    			case 'js':
    	    				foreach($items as $item)
    	    				{
    	    					e($this->Javascript->link($item));
    	    				}
    	    				break;
    	    			case 'raw':
    	    				foreach($items as $item)
    	    				{
    	    					e($item);
    	    				}
    	    				break;    				
    	    			case 'jsOnReady':
    						$output  = "Event.onDOMReady(function(){";
    						$output .= join($items);
    						$output .= "});";
    						e($this->Javascript->codeBlock($output));
    						break;
    	    			case 'jsOnLoad':
    						$output  = "Event.observe(window, 'load', function(){";
    						$output .= join($items);
    						$output .= "});";
    						e($this->Javascript->codeBlock($output));
    						break;
    	    			case 'jsBlock':
    						$output = join($items);
    						e($this->Javascript->codeBlock($output));
    						break;
    	    			case 'cssBlock':
    						$output = join($items);
    						e($this->Html->css($output));
    						break;
    	    			default:
    	    				die("Internal error. Unknown type: '{$type}'");
    	    		}    				
        		}
        		
        	}
        }
    }
    ?>


Cheers, biesbjerg

.. _jirikupiainen.com: http://www.jirikupiainen.com
.. _http://smoothoperatah.com/files/onDOMReady.js: http://smoothoperatah.com/files/onDOMReady.js
.. _http://rossoft.wordpress.com: http://rossoft.wordpress.com/
.. _http://www.prototypejs.org: http://www.prototypejs.org/
.. meta::
    :title: NiceHead helper with autoloading of javascript and css
    :description: CakePHP Article related to auto,js,head,load,insert,Helpers
    :keywords: auto,js,head,load,insert,Helpers
    :copyright: Copyright 2007 biesbjerg
    :category: helpers

