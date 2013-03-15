

Menu Helper
===========

by %s on June 11, 2008

An easy to use helper capitalizing on the awesome tree helper
(http://bakery.cakephp.org/articles/view/tree-helper-1) to make menus
and menu logic a piece of cake


Helper Class:
`````````````

::

    <?php 
    /* SVN FILE: $Id: menu.php 131 2008-06-23 12:02:48Z ad7six $ */
    /**
     * Short description for menu.php
     * 
     * Long description for menu.php
     * 
     * PHP versions 4 and 5
     * 
     * Copyright (c) 2008, Andy Dawson
     * 
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     * 
     * @filesource
     * @copyright            Copyright (c) 2008, Andy Dawson
     * @link                 www.ad7six.com
     * @package              cake-base
     * @subpackage           cake-base.app.views.helpers
     * @since                v 1.0
     * @version              $Revision: 131 $
     * @modifiedBy           $LastChangedBy: ad7six $
     * @lastModified         $Date: 2008-06-23 14:02:48 +0200 (Mon, 23 Jun 2008) $
     * @license              http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    /**
     * MenuHelper class
     * 
     * @uses                 AppHelper
     * @package              cake-base
     * @subpackage           cake-base.app.views.helpers
     */
    class MenuHelper extends AppHelper {
    /**
     * helpers property
     * 
     * @var array
     * @access public
     */
    	var $helpers = array('Html', 'Tree');
    /**
     * defaultSettings property
     * 
     * @var array
     * @access private
     */
    	var $__defaultSettings = array(
    		'genericElement' => 'menu/generic',
    		'hereMode' => 'active', // active // text // false[do nothing]
    		'activeMode' => 'url', // url // controller[name] // action[and controller name] // false [do nothing]
    		'uniqueKey' => 'title',
    		'onlyActiveChildren' => false,
    		'showWarnings' => true
    	);	
    /**
     * settings property
     * 
     * @var array
     * @access public
     */
    	var $settings = array();	
    /**
     * data property
     *
     * Holds the menu data as they get built. References flatData.
     * 
     * @var array
     * @access private
     */
    	var $__data = array();
    /**
     * flatData property
     *
     * A flat list of menu data
     * 
     * @var array
     * @access private
     */
    	var $__flatData = array();
    /**
     * here property
     *
     * Place holder for router normalized "here"
     * 
     * @var string ''
     * @access private
     */
    	var $__here = '';
    /**
     * beforeRender method
     *
     * If genericElement is set, 'render' the named element. This can be used to prevent repeating menu logic if
     * for example there are some menu items which don't change based on the specific view file
     * 
     * @access public
     * @return void
     */
    	function beforeRender() {
    		if ($this->__defaultSettings['genericElement']) {
    			$view =& ClassRegistry:: getObject('view');
    			if ($view) {	
    				echo $view->element($this->__defaultSettings['genericElement']);
    			}
    		}
    		return true;
    	}	
    /**
     * settings method
     *
     * Define "here" and initialize or change settings
     * 
     * @param string $section 
     * @param array $settings 
     * @access public
     * @return void
     */
    	function settings($section = 'main', $settings = array()) {
    		if (!$this->__here) {
    			$this->__here = Router::normalize('/' . $this->params['url']['url']);	
    		}
    		if (!isset($this->settings[$section])) {
    			foreach ($settings as $key => $_) {
    				if (!isset($this->__defaultSettings[$key])) {
    					unset ($settings[$key]);	
    				}	
    			}
    			$settings = array_merge($this->__defaultSettings, $settings);
    			$this->settings[$section] = $settings;
    		} elseif ($settings) {
    			$this->settings[$section] = array_merge($this->settings[$section], $settings);
    		}
    	       return $this->settings[$section];	
    	}	
    /**
     * addm method
     *
     * Add Multiple menu items at once - use array syntax
     * 
     * @param string $section 
     * @param array $data 
     * @access public
     * @return void
     */
    	function addm ($section = 'main', $data = array()) {
    		if (is_array($section)) {
    			$section = 'main';
    			$data = $section;
    		}
    		foreach ($data as $row) {
    			$this->add(array_merge(array('section' => $section), $row));
    		}
    	}	
    /**
     * Add a menu item.
     *
     * Add a menu item syntax examples:
     * 	$menu->add($title, $url); adds an entry with $title and $url to the menu named "main"
     * 	$menu->add('main', $title, $url); as above but explicit
     * 	$menu->add('context', $title, $url); add an entry with $title and $url to the menu named "context"
     * 	$menu->add('context', $title, $url, 'subSection'); add an entry with $title and $url to subsection "subSection for the menu named "context"
     * 	$menu->add(array('url' => $url, 'title' => $title, 'options' => array('escapeTitle' => false))); array syntax, not escaping title
     * 	$menu->add(array('url' => $url, 'title' => $title, 'options' => array('htmlAttributes' => array('id' => 'foo'))); array syntax, setting id for link
     * 
     * @param string $section 
     * @param mixed $title 
     * @param mixed $url 
     * @param mixed $under 
     * @param array $options 
     * @param array $settings 
     * @access public
     * @return void
     */
    	function add($section = 'main', $title = null, $url = null, $under = null, $options = array(), $settings = array()) {
    		$here = $inPath = $activeChild = $sibling = false;
    		if (is_array($section)) {
    			$settings = $section;
    			extract(array_merge(array('section' => 'main'), $section));	
    		} elseif (($section && $url !== false) || (is_string ($url) && $url[0] != 'h' && $url[0] != '/'&& $url[0] != '#') || is_array($under)) {
    			if ($under) {
    				$options = $under;	
    			}
    			$settings = array();
    			$options = $under;
    			$under = $url;
    			$url = $title;
    			$title = $section;
    			$section = 'main';	
    		}
    		if (!isset($this->settings[$section])) {
    			$this->settings($section, $settings);	
    		}	
    		extract(array_merge($this->settings[$section], $settings));
    		if (isset($$uniqueKey)) {
    			if (is_array($$uniqueKey)) {
    				$key = serialize($$uniqueKey);
    			} else {
    				$key = $$uniqueKey;
    			}
    		} else {
    			$key = $title;	
    		}
    		if (is_array($under)) {
    			$under = serialize($under);	
    		}
    		list($here, $markActive, $url) = $this->__setHere($section, $url, $key, $activeMode, $hereMode);
    		if ($under) {
    			if (!isset($this->__flatData[$section][$under])) {
    				$this->__flatData[$section][$under] = array('title' => $under, 'url' => false, 'options' => array(), 'here' => false, 'under' => false, 'inPath' => false, 'activeChild' => false, 'sibling' => false, 'markActive' => false);
    				$this->__data[$section][$under] =& $this->__flatData[$section][$under];
    			}
    			$this->__flatData[$section][$key] = compact('title', 'url', 'options', 'under', 'here', 'inPath', 'activeChild', 'sibling', 'markActive');
    			$this->__flatData[$section][$under]['children'][$key] =& $this->__flatData[$section][$key];
    		} elseif (!isset($this->__flatData[$section][$key])) {
    			$this->__flatData[$section][$key] = compact('title', 'url', 'options', 'under', 'here', 'inPath', 'activeChild', 'sibling', 'markActive');
    			$this->__data[$section][$key] =& $this->__flatData[$section][$key];
    		} elseif ($showWarnings)  {
    			$altKey = $uniqueKey == 'title'?'url':'title';
    			trigger_error ('MenuHelper::add<br /> Duplicate menu item detected for item "' . $title . '" in menu ' . $section . '.' .
    				'<br />You can change the field used to detect duplicates which is currently set to ' . $uniqueKey . ',' . 
    			      	' can be changed to ' . $altKey . '.');
    		}
    		if ($hereMode == 'text' && $here == true) {
    			$this->__flatData[$section][$key]['url'] = false;
    		}
    	}
    /**
     * sections method
     *
     * Return the names of all sections currently stored by the helper
     * 
     * @access public
     * @return void
     */
    	function sections () {
    		return array_keys($this->__data);
    	}
    /**
     * generate menu method
     *
     * generate menu syntax examples:
     * 	echo $menu->generate(); echo the main menu
     * 	echo $menu->generate('menu'); as above but explicit
     * 	echo $menu->generate('menu', array('element' => 'menus/item'); use an element for each item's content
     * 	echo $menu->generate('menu', array('callback' => 'menuItem'); use loose method menuItem for each item's content
     * 	echo $menu->generate('menu', array('callback' => array(&$object, 'method'); call $object->method($data) for each item's content
     * 
     * @param string $section 
     * @param array $settings to be passed to the tree helper
     * @param bool $createEmpty 
     * @access public
     * @return void
     */
    	function generate ($section = 'main', $settings = array(), $createEmpty = true) {
    		if (is_array($section)) {
    			extract(array_merge(array('section' => 'main'), $data));	
    		}
    		if (!isset($this->settings[$section])) {
    			return false;	
    		}
    		$settings = array_merge($this->settings[$section], $settings);	
    		$settings = array_merge(array('callback' => array(&$this, '_menuItem'), 'model' => false, 'class' => 'menu'), $settings);	
    		extract ($settings);
    		if (isset($this->__data[$section])) {
    			if ($onlyActiveChildren) {
    				$pkey = false;
    				if (isset($this->__flatData[$section]['__hereKey'])) {
    					$key = $this->__flatData[$section]['__hereKey'];
    					$pkey = $this->__flatData[$section][$key]['under'];
    					unset($this->__flatData[$section]['__hereKey']);
    					if (isset($this->__flatData[$section][$key]['children'])) {	
    						foreach ($this->__flatData[$section][$key]['children'] as $i => $_i) {
    							$this->__flatData[$section][$key]['children'][$i]['activeChild'] = true;
    						}
    					}
    					$under = $this->__flatData[$section][$key]['under'];
    					while ($under) {
    						$this->__flatData[$section][$under]['inPath'] = true;
    						$under = $this->__flatData[$section][$under]['under'];
    					}
    				}
    				foreach ($this->__flatData[$section] as $i => $row) {
    					if (!$row['under'] && !$row['here']) {
    						$this->__flatData[$section][$i]['sibling'] = true;	
    					} elseif ($row['under'] == $pkey && !$row['activeChild'] && !$row['here']) {
    						$this->__flatData[$section][$i]['sibling'] = true;	
    					} elseif (!($row['here'] || $row['inPath']|| $row['activeChild'] || $row['sibling'])) {
    						unset($this->__flatData[$section][$i]);	
    					}
    				}
    				$this->__cleanData($this->__data[$section], $section);
    			}
    			$data = $this->__data[$section];
    			$flatData = $this->__flatData[$section];
    			unset ($this->__data[$section]);
    			unset ($this->__flatData[$section]);
    		} elseif ($createEmpty) {
    			return '<ul><!-- Empty menu --></ul>';
    		} else {
    			return false;	
    		}
    		$return = $this->Tree->generate($data, $settings);	
    		return $return;
    	}
    /**
     * internal callback
     *
     * Used to return the output from the html helper using the parameters for this menu option
     * 
     * @param array $data 
     * @access protected
     * @return void
     */
    	function _menuItem($data = array()) {
    		$htmlAttributes = array();
    		$markActive = false;
    		$confirmMessage = false;
    		$escapeTitle = true;
    		extract ($data);
    		extract ($data);
    		if ($options) {
    			extract ($options);
    		}
    		if ($markActive) {
    			$this->Tree->addItemAttribute('class', 'active');
    			if (isset ($htmlAttributes['class'])) {
    				$htmlAttributes['class'] .= ' active';
    			} else {
    				$htmlAttributes['class'] = 'active';
    			}
    		}
    		if ($url === false) {
    			return $title;
    		} else {
    			return $this->Html->link($title, $url, $htmlAttributes, $confirmMessage, $escapeTitle);
    		}
    	}
    /**
     * setHere method
     *
     * Used internally to detect whether the current menu item links to the page currently
     * being rendered and modify the url if appropriate
     * 
     * @param mixed $section 
     * @param mixed $url 
     * @param mixed $activeMode 
     * @param mixed $hereMode 
     * @access private
     * @return array($here, $markActive, $url)
     */
    	function __setHere($section, $url, $key, $activeMode, $hereMode) {
    		$view =& ClassRegistry:: getObject('view');
    		if (isset($this->__flatData[$section]['__hereKey']) || !$view) {
    			return array(false, false, $url);
    		}
    		$here = $markActive = false;
    		if ($activeMode == 'url' && Router::normalize($url) == $this->__here) {
    			$here = true;
    		} elseif (is_array($url) && 
    			(!isset($url['controller']) || $url['controller'] == Inflector::underscore($view->name)) 
    		) {
    			if ($activeMode == 'controller') {
    				$here = true;
    			} elseif ($activeMode == 'action' && 
    				(!isset($url['action']) || $url['action'] == Inflector::underscore($view->action))) {
    				$here = true;
    			}	
    		}
    		if ($here) {
    			$this->__flatData[$section]['__hereKey'] = $key;
    			if ($hereMode == 'text') {
    				$url = false;	
    			} elseif ($hereMode == 'active') {
    				$markActive = true;	
    			}
    		}
    		if ($here && $hereMode == 'active') {
    			$this->Tree->addItemAttribute('class', 'active');
    			if (isset ($htmlAttributes['class'])) {
    				$htmlAttributes['class'] .= ' active';
    			} else {
    				$htmlAttributes['class'] = 'active';
    			}
    		}
    
    		return array($here, $markActive, $url);
    	}
    /**
     * cleanData method
     *
     * Shouldn't really be necessary. Ensures that any item(s) which have been suppressed by the "only show active"
     * logic are removed
     * 
     * @param mixed $array 
     * @param mixed $section 
     * @access private
     * @return void
     */
    	function __cleanData(&$array, $section) {
    		foreach ($array as $key => $row) {
    			if (!isset($this->__flatData[$section][$key])) {
    				unset ($array[$key]);	
    			} elseif (isset($row['children']) && $row['children']) {
    				$this->__cleanData($array[$key]['children'], $section);	
    			}
    		}	
    	}
    }
    ?>


.. meta::
    :title: Menu Helper
    :description: CakePHP Article related to ,Helpers
    :keywords: ,Helpers
    :copyright: Copyright 2008 
    :category: helpers

