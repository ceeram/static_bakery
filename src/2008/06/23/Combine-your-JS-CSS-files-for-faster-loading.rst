Combine your JS & CSS files for faster loading
==============================================

by morrislaptop on June 23, 2008

With the relatively recent publication of YSlow, it brought to my
attention that while seperating your CSS and JS files into seperate
files for a more structured application - it can greatly reduce the
load time for your users. This solution aims to solve that by keeping
your files seperate, and implementing the advantages of cake so
ultimately your users will only have to download one aggregated JS and
CSS file.
What this basically does is render the views as normal and keeps a
track of all the JS and CSS files that have been requested during
rendering views. When the layout is rendered we generate a URL which
tells our CSS.php or our JS.php what files need to be put together and
presented to the user for download.

Let's get to the code..


Put the helper in your controllers
----------------------------------

::

    
    <?php
    class TestsController extends AppController {
    
    	var $name = 'Tests';
    	var $helpers = array('NiceHead');
    	
    	function admin_index()
    	{ .....



Requesting the files you need in your views and layouts...
----------------------------------------------------------

In your layout file...

::

    
    $niceHead->js('jquery');

In your view files...

::

    
    $niceHead->js('jquery.autocomplete');



Call the helper to output tags for the URLs
-------------------------------------------

::

    
    <head>
    ...
    <?php if(isset($niceHead)) $niceHead->flush();?>
    ...
    </head>



Next...
-------

Now that we have some pretty URL's which look like they will grab a
bunch of different files and put them together in one nice big file
which the user has to only download once..

This URL doesn't work with the default css.php and js.php that is
shipped with CakePHP. Unfortunately we have to modify css.php and
js.php to support this new URL format. The best thing about this is
that it will still be backwards compatible!

This is a pretty rough draft at the moment, I thought I would just put
it forward to the community so they know the next step. I will update
this post if there is lots of confusion out there...


Modified app/webroot/js.php
---------------------------

::

    
    <?php
    /* SVN FILE: $Id: css.php 4852 2007-04-12 08:49:49Z phpnut $ */
    /**
     * Short description for file.
     *
     * Long description for file
     *
     * PHP versions 4 and 5
     *
     * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
     * Copyright 2005-2007, Cake Software Foundation, Inc.
     *								1785 E. Sahara Avenue, Suite 490-204
     *								Las Vegas, Nevada 89104
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @filesource
     * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
     * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
     * @package			cake
     * @subpackage		cake.app.webroot
     * @since			CakePHP(tm) v 0.2.9
     * @version			$Revision: 4852 $
     * @modifiedby		$LastChangedBy: phpnut $
     * @lastmodified	$Date: 2007-04-12 03:49:49 -0500 (Thu, 12 Apr 2007) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    if (!defined('CAKE_CORE_INCLUDE_PATH')) {
    	header('HTTP/1.1 404 Not Found');
    	exit('File Not Found');
    }
    /**
     * Enter description here...
     */
    	uses('file');
    /**
     * Enter description here...
     *
     * @param unknown_type $path
     * @param unknown_type $name
     * @return unknown
     */
     
    	function make_clean_js($path, $name) {
    		require(VENDORS . 'class.JavaScriptPacker.php');
    		$data = file_get_contents($path);
    		
    		#$csspp = new csspp();
    		#$output = $csspp->compress($data);
    		$packer = new JavaScriptPacker($data, 'Normal', true, false);
    		$packed = $packer->pack();
    		
    		$ratio = 100 - (round(strlen($packed) / strlen($data), 3) * 100);
    		$packed = " /* file: $name, ratio: $ratio% */ " . $packed;
    		return $packed;
    	}
    /**
     * Enter description here...
     *
     * @param unknown_type $path
     * @param unknown_type $content
     * @return unknown
     */
    	function write_js_cache($path, $content) {
    		if (!is_dir(dirname($path))) {
    			mkdir(dirname($path));
    		}
    		$cache = new File($path);
    		return $cache->write($content);
    	}
    	
    	$urls = explode(',', $url);
    	$output = '';
    	$templateModified = null;
    	foreach ($urls as $url)
    	{
    		if (preg_match('|\.\.|', $url) || !preg_match('|^cjs/(.+)$|i', $url, $regs)) {
    			$regs = array(1 => $url);
    		}
    		if ( '.js' !== substr($regs[1], -3) ) {
    			$regs[1] .= '.js';
    		}
    	
    		$filename = 'js/' . $regs[1];
    		$filepath = JS . $regs[1];
    		$cachepath = CACHE . 'js' . DS . str_replace(array('/','\\'), '-', $regs[1]);
    	
    		if (!file_exists($filepath)) {
    			die('Wrong file path: ' . $filepath);
    		}
    		
    		if ( !Configure::read('Asset.compress.js') ) {
    			$file = file_get_contents($filepath);
    			$output .= $file;
    			$templateModified = max($templateModified, filemtime($filepath));
    			continue;
    		}
    	
    		if (file_exists($cachepath)) {
    			$templateModified = filemtime($filepath);
    			$cacheModified = filemtime($cachepath);
    	
    			if ($templateModified > $cacheModified) {
    				$file = make_clean_js($filepath, $filename);
    				write_js_cache($cachepath, $file);
    			} else {
    				$file = file_get_contents($cachepath);
    			}
    		} else {
    			$file = make_clean_js($filepath, $filename);
    			write_js_cache($cachepath, $file);
    			$templateModified = time();
    		}
    		$output .= $file;
    	}
    
    	header("Date: " . date("D, j M Y G:i:s ", $templateModified) . 'GMT');
    	header("Content-Type: text/css");
    	header("Expires: " . gmdate("D, j M Y H:i:s", time() + DAY) . " GMT");
    	header("Cache-Control: cache"); // HTTP/1.1
    	header("Pragma: cache");        // HTTP/1.0
    	print $output;
    ?>



Modified version of css.php
---------------------------

::

    
    <?php
    /* SVN FILE: $Id: css.php 4853 2007-04-12 08:59:09Z phpnut $ */
    /**
     * Short description for file.
     *
     * Long description for file
     *
     * PHP versions 4 and 5
     *
     * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
     * Copyright 2005-2007, Cake Software Foundation, Inc.
     *								1785 E. Sahara Avenue, Suite 490-204
     *								Las Vegas, Nevada 89104
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @filesource
     * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
     * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
     * @package			cake
     * @subpackage		cake.app.webroot
     * @since			CakePHP(tm) v 0.2.9
     * @version			$Revision: 4853 $
     * @modifiedby		$LastChangedBy: phpnut $
     * @lastmodified	$Date: 2007-04-12 03:59:09 -0500 (Thu, 12 Apr 2007) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    if (!defined('CAKE_CORE_INCLUDE_PATH')) {
    	header('HTTP/1.1 404 Not Found');
    	exit('File Not Found');
    }
    /**
     * Enter description here...
     */
    	uses('file');
    /**
     * Enter description here...
     *
     * @param unknown_type $path
     * @param unknown_type $name
     * @return unknown
     */
    	function make_clean_css($path, $name) {
    		require(VENDORS . 'csspp' . DS . 'csspp.php');
    		$data = file_get_contents($path);
    		$csspp = new csspp();
    		$output = $csspp->compress($data);
    		$ratio = 100 - (round(strlen($output) / strlen($data), 3) * 100);
    		$output = " /* file: $name, ratio: $ratio% */ " . $output;
    		return $output;
    	}
    /**
     * Enter description here...
     *
     * @param unknown_type $path
     * @param unknown_type $content
     * @return unknown
     */
    	function write_css_cache($path, $content) {
    		if (!is_dir(dirname($path))) {
    			mkdir(dirname($path));
    		}
    		$cache = new File($path);
    		return $cache->write($content);
    	}
    	
    	$urls = explode(',', $url);
    	$output = '';
    	$templateModified = null;
    	foreach ($urls as $url)
    	{
    		if (preg_match('|\.\.|', $url) || !preg_match('|^ccss/(.+)$|i', $url, $regs)) {
    			$regs = array(1 => $url);
    		}
    		if ( '.css' !== substr($regs[1], -4) ) {
    			$regs[1] .= '.css';
    		}
    	
    		$filename = 'css/' . $regs[1];
    		$filepath = CSS . $regs[1];
    		$cachepath = CACHE . 'css' . DS . str_replace(array('/','\\'), '-', $regs[1]);
    	
    		if (!file_exists($filepath)) {
    			die('Wrong file path: ' . $filepath);
    		}
    		
    		if ( !Configure::read('Asset.compress.css') ) {
    			$file = file_get_contents($filepath);
    			$output .= $file;
    			$templateModified = max($templateModified, filemtime($filepath));
    			continue;
    		}
    	
    		if (file_exists($cachepath)) {
    			$templateModified = filemtime($filepath);
    			$cacheModified = filemtime($cachepath);
    	
    			if ($templateModified > $cacheModified) {
    				$file = make_clean_css($filepath, $filename);
    				write_css_cache($cachepath, $file);
    			} else {
    				$file = file_get_contents($cachepath);
    			}
    		} else {
    			$file = make_clean_css($filepath, $filename);
    			write_css_cache($cachepath, $file);
    			$templateModified = time();
    		}
    		$output .= $file;
    	}
    
    	header("Date: " . date("D, j M Y G:i:s ", $templateModified) . 'GMT');
    	header("Content-Type: text/css");
    	header("Expires: " . gmdate("D, j M Y H:i:s", time() + DAY) . " GMT");
    	header("Cache-Control: cache"); // HTTP/1.1
    	header("Pragma: cache");        // HTTP/1.0
    	print $output;
    ?>



Modified version of NiceHead
----------------------------


Helper Class:
`````````````

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
    	var $autoloadCss = false;
    	var $autoloadJs = false;
    	
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
    	function jsOnReady($input, $prepend = false)
    	{
    		$this->_register($input, 'jsOnReady', $prepend);
    	}
    	
    	/**
    	 * Includes a block of javascript on window load
    	 *
    	 * @param string $input
    	 */
    	function jsOnLoad($input, $prepend = false)
    	{
    		$this->_register($input, 'jsOnLoad', $prepend);
    	}
    	
    	/**
    	 * Includes an external javascript file
    	 *
    	 * @param string $input
    	 */
    	function js($input, $prepend = false)
    	{
    		$this->_register($input, 'js', $prepend);
    	}
    	
    	/**
    	 * Includes a block of javascript
    	 *
    	 * @param string $input
    	 */
    	function jsBlock($input, $prepend = false)
    	{
    		$this->_register($input, 'jsBlock', $prepend);
    	}
    	
    	/**
    	 * Includes an external stylesheet
    	 *
    	 * @param string $input
    	 */
    	function css($input, $prepend = false)
    	{
    		$this->_register($input, 'css', $prepend);
    	}
    	
    	/**
    	 * Includes a block of styles
    	 *
    	 * @param string $input
    	 */
    	function cssBlock($input, $prepend = false)
    	{
    		$this->_register($input, 'cssBlock', $prepend);
    	}
    	
    	function raw($input, $prepend = false)
    	{
    		$this->_register($input, 'raw', $prepend);
    	}
    	
    	/**
    	 * Internal function used to register items
    	 *
    	 * @param string $item
    	 * @param string $type
    	 */
        function _register($item, $type, $prepend = false)
        {
        	if(!array_key_exists($type, $this->_registered))
        	{
        		$this->_registered[$type] = array();
        	}
        	
        	if(!in_array($item, $this->_registered[$type]))
            {
            	if ( $prepend ) {
                	array_unshift($this->_registered[$type], $item);
                }
                else {
                	$this->_registered[$type][] = $item;
                }
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
    						e($this->Html->css(implode(',', $items)));
    	    				break;
    	    			case 'js':
    	    				e($this->Javascript->link(implode(',', $items)));
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



.. author:: morrislaptop
.. categories:: articles, helpers
.. tags:: CSS,js,Helpers

