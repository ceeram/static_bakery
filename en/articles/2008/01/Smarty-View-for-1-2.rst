

Smarty View for 1.2
===================

by %s on January 14, 2008

This expands on the Smarty View code from Version 1.1. While its only
a beginning, I am hoping someone else will pick up from what we had in
version 1.1

In version 1.1 of Cakephp I used the Smarty View and thought it was a
great additional. In my opinion Smarty and Cake work so well together
because it allows a web design to stick to the Smarty side of things,
and the developer to use Cake.

With Smarty Caching, I never found any performance issues using
Smarty. It really does go well with Cake... In saying that, I have
started a plugin for Smarty for Cake 1.2 which expands on Smarty View
from 1.1 `http://bakery.cakephp.org/articles/view/how-to-use-smarty-
with-cake-smartyview`_

Note: At this point I have only found the HTML helpers work, not the
new form functions which Cake has. I am hoping someone can expand on
what I have got and will write those!

Installation

Step 1: Make the file /app/view/smarty.php

::

    
    <?php
    /* SVN FILE: $Id: view.php 6311 2008-01-02 06:33:52Z phpnut $ */
    
    /**
     * Methods for displaying presentation data in the view.
     *
     * PHP versions 4 and 5
     *
     * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
     * Copyright 2005-2008, Cake Software Foundation, Inc.
     *								1785 E. Sahara Avenue, Suite 490-204
     *								Las Vegas, Nevada 89104
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @filesource
     * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
     * @link			http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
     * @package			cake
     * @subpackage		cake.cake.libs.view
     * @since			CakePHP(tm) v 0.10.0.1076
     * @version			$Revision: 6311 $
     * @modifiedby		$LastChangedBy: phpnut $
     * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    
    /**
     * Include Smarty. By default expects it at ( VENDORS.'smarty'.DS.'Smarty.class.php' )
     */
    vendor('smarty'.DS.'Smarty.class');
    
    /**
     * CakePHP Smarty view class
     *
     * This class will allow using Smarty with CakePHP
     *
     * @version      1.1.0.0
     * @package      cake
     * @subpackage   cake.app.views
     * @since        CakePHP v 1.2
     */
    class SmartyView extends View
    {
    /**
     * SmartyView constructor
     *
     * @param  $controller instance of calling controller
     */
    	function __construct (&$controller)
    	{
    		parent::__construct($controller);
    		$this->Smarty = &new Smarty();
    		// requires views be in a 'smarty' subdirectory, you can remove this limitation if you aren't using other inherited views that use .tpl as the extension
    		$this->subDir = 'smarty'.DS;
    		$this->ext= '.tpl';
    		$this->Smarty->plugins_dir[] = VIEWS.'smarty_plugins'.DS;
    		$this->Smarty->compile_dir = TMP.'smarty'.DS.'compile'.DS;
    		$this->Smarty->cache_dir = TMP.'smarty'.DS.'cache'.DS;
    		$this->Smarty->error_reporting = 'E_ALL & ~E_NOTICE';
    		$this->Smarty->debugging = true;
    	}
    
    /**
     * Overrides the View::_render()
     * Sets variables used in CakePHP to Smarty variables
     *
     * @param string $___viewFn
     * @param string $___data_for_view
     * @param string $___play_safe
     * @param string $loadHelpers
     * @return rendered views
     */
    	function _render($___viewFn, $___data_for_view, $___play_safe = true, $loadHelpers = true)
    	{
    		if ($this->helpers != false && $loadHelpers === true)
    		{
    			$loadedHelpers =  array();
    			$loadedHelpers = $this->_loadHelpers($loadedHelpers, $this->helpers);
    
    			foreach(array_keys($loadedHelpers) as $helper)
    			{
    				$replace = strtolower(substr($helper, 0, 1));
    				$camelBackedHelper = preg_replace('/\\w/', $replace, $helper, 1);
    
    				${$camelBackedHelper} =& $loadedHelpers[$helper];
    				if(isset(${$camelBackedHelper}->helpers) && is_array(${$camelBackedHelper}->helpers))
    				{
    					foreach(${$camelBackedHelper}->helpers as $subHelper)
    					{
    						${$camelBackedHelper}->{$subHelper} =& $loadedHelpers[$subHelper];
    					}
    				}
    				$this->loaded[$camelBackedHelper] = (${$camelBackedHelper});
    				$this->Smarty->assign_by_ref($camelBackedHelper, ${$camelBackedHelper});
    			}
    		}
    
    		$this->register_functions();
    
    		foreach($___data_for_view as $data => $value)
    		{
    			if(!is_object($data))
    			{
    				$this->Smarty->assign($data, $value);
    			}
    		}
    		$this->Smarty->assign_by_ref('view', $this);
    		return $this->Smarty->fetch($___viewFn);
    	}
    	
    /**
     * Returns layout filename for this template as a string.
     *
     * @return string Filename for layout file (.ctp).
     * @access private
     */
    	function _getLayoutFileName() {
    		if (isset($this->webservices) && !is_null($this->webservices)) {
    			$type = strtolower($this->webservices) . DS;
    		} else {
    			$type = null;
    		}
    
    		if (isset($this->plugin) && !is_null($this->plugin)) {
    			if (file_exists(APP . 'plugins' . DS . $this->plugin . DS . 'views' . DS . 'layouts' . DS . $this->layout . $this->ext)) {
    				$layoutFileName = APP . 'plugins' . DS . $this->plugin . DS . 'views' . DS . 'layouts' . DS . $this->layout . $this->ext;
    				return $layoutFileName;
    			}
    		}
    		$paths = Configure::getInstance();
    
    		foreach($paths->viewPaths as $path) {
    			if (file_exists($path . 'layouts' . DS . $this->subDir . $type . $this->layout . $this->ext)) {
    				$layoutFileName = $path . 'layouts' . DS . $this->subDir . $type . $this->layout . $this->ext;
    				return $layoutFileName;
    			}
    		}
    
    		// added for .ctp viewPath fallback
    		foreach($paths->viewPaths as $path) {
    			if (file_exists($path . 'layouts' . DS  . $type . $this->layout . '.ctp')) {
    				$layoutFileName = $path . 'layouts' . DS . $type . $this->layout . '.ctp';
    				return $layoutFileName;
    			}
    		}
    
    		if($layoutFileName = fileExistsInPath(LIBS . 'view' . DS . 'templates' . DS . 'layouts' . DS . $type . $this->layout . '.ctp')) {
    		} else {
    			$layoutFileName = LAYOUTS . $type . $this->layout.$this->ext;
    		}
    		return $layoutFileName;
    	}
    	
    	
    /**
     * Returns filename of given action's template file (.tpl) as a string.
     * CamelCased action names will be under_scored! This means that you can have
     * LongActionNames that refer to long_action_names.ctp views.
     *
     * @param string $action Controller action to find template filename for
     * @return string Template filename
     * @access protected
     */
    	function _getViewFileName($name = null) {
    		$subDir = null;
    
    		if (!is_null($this->webservices)) {
    			$subDir = strtolower($this->webservices) . DS;
    		}
    		if (!is_null($this->subDir)) {
    			$subDir = $this->subDir . DS;
    		}
    
    		if ($name === null) {
    			$name = $this->action;
    		}
    
    		if (strpos($name, '/') === false && strpos($name, '..') === false) {
    			$name = $this->viewPath . DS . $subDir . Inflector::underscore($name);
    		} elseif (strpos($name, '/') !== false) {
    			if ($name{0} === '/') {
    				if (is_file($name)) {
    					return $name;
    				}
    				$name = trim($name, '/');
    			} else {
    				$name = $this->viewPath . DS . $subDir . $name;
    			}
    			if (DS !== '/') {
    				$name = implode(DS, explode('/', $name));
    			}
    		} elseif (strpos($name, '..') !== false) {
    			$name = explode('/', $name);
    			$i = array_search('..', $name);
    			unset($name[$i - 1]);
    			unset($name[$i]);
    			$name = '..' . DS . implode(DS, $name);
    		}
    
    		$paths = $this->_paths($this->plugin);
    		foreach ($paths as $path) {
    			if (file_exists($path . $name . $this->ext)) {
    				return $path . $name . $this->ext;
    			} elseif (file_exists($path . $name . '.ctp')) {
    				return $path . $name . '.ctp';
    			} elseif (file_exists($path . $name . '.thtml')) {
    				return $path . $name . '.thtml';
    			}
    		}
    
    		return $this->_missingView($paths[0] . $name . $this->ext, 'missingView');
    	}	
    
    	/**
    	 * checks for existence of special method on loaded helpers, invoking it if it exists
    	 * this allows helpers to register smarty functions, modifiers, blocks, etc.
    	 */
    	function register_functions() {
    		foreach(array_keys($this->loaded) as $helper) {
    			if (method_exists($this->loaded[$helper], '_register_smarty_functions')) {
    				$this->loaded[$helper]->_register_smarty_functions(&$this->Smarty);
    			}
    		}
    	}
    }
    ?>


Comment out this line so that Smarty files don't have to go in
/app/views/pages/smarty/home.tpl but /app/views/pages/home.tpl instead

::

    
    $this->subDir = 'smarty'.DS;


Step 2:

Download Smarty[br]`http://smarty.php.net/download.php`_[br] Extract
tarball so Smarty.class.php sits at:[br]
/vendor/smarty/Smarty.class.php

Step 3:

Create the folders /app/tmp/smarty[br]
/app/tmp/smarty/compile (chmod 777)[br]
/app/tmp/smarty/cache (chmod 777)


Step 4:

Include in your controller[br] (in app_controller.php to do it app-
wide)

::

    
    var $view = 'Smarty';



.. _http://smarty.php.net/download.php: http://smarty.php.net/download.php
.. _http://bakery.cakephp.org/articles/view/how-to-use-smarty-with-cake-smartyview: http://bakery.cakephp.org/articles/view/how-to-use-smarty-with-cake-smartyview
.. meta::
    :title: Smarty View for 1.2
    :description: CakePHP Article related to smartyview,smarty,Template,Helpers
    :keywords: smartyview,smarty,Template,Helpers
    :copyright: Copyright 2008 
    :category: helpers

