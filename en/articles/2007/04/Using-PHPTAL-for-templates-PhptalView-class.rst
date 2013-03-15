

Using PHPTAL for templates: PhptalView class
============================================

by %s on April 24, 2007

I use PHPTAL quite a lot (believe it or not, I like XML ;) and I
wanted to use it in Cake so I wrote a little PhptalView class
extending Cake's View.
OK folks, it's pretty straightforward.


+ Make sure you use PHP 5. PHPTAL is working only with PHP 5 (well,
  there are some really old versions that work with PHP 4, but as the
  author of PHPTAL has said: "Please forget PHP4" :-)
+ Download PHPTAL from `http://phptal.motion-twin.com/`_ and unpack it
  in /app/vendors . My class will look for PHPTAL-1.1.8 directory, as
  this is the last version of PHPTAL. It's easy to customize the dirname
  by changing the vendor() statement. It should be possible to use
  PHPTAL from PEAR too. Try installing PHPTAL into your PEAR and load it
  wit plain old require().
+ Download the snippet from CakeForge or from the second page of this
  article: `http://cakeforge.org/snippet/detail.php?type=snippet=180`_
+ Place it in your /app/views directory
+ In your controller, set view to Phptal: or whatever PHPTAL syntax
  you use for printing out variables. Otherwise PHPTAL will escape your
  View's rendering output.
+ Assign variables as usual, through Cake's set() method.
+ Enjoy the immense elegance of PHPTAL :-)

You are encouraged to improve the class any way you want. This is just
what I have baked for my needs.



Component Class:
````````````````

::

    <?php 
    <?php
    /**
     * Methods for displaying presentation data using PHPTAL (http://phptal.motion-twin.com/)
     *
     *
     * PHP 5 ONLY !!!!
     *
     * Copyright (c) 2007, Daniel KvasniÄ?ka
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @filesource
     * @copyright		Copyright (c) 2007, Daniel KvasniÄ?ka
     * @link			
     * @package		cake
     * @subpackage	cake.app.views
     * @version		0.1
     * @lastmodified	$Date: 2006-12-03 23:29:12 +0100 $
     * @license		http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    /**
     * Included libraries.
     */
    vendor('PHPTAL-1.1.8'.DS.'PHPTAL');
    
    /**
     * PHPTALView, the V in the MVC triad, made the PHPTAL way.
     *
     * Class holding methods for displaying presentation data.
     *
     * @package			cake
     * @subpackage		cake.app.views
     */
    class PhptalView extends View {
    
    	/**
    	 * PHPTALView constructor
    	 *
    	 * @param  $controller instance of calling controller
    	 */
    	function __construct(&$controller) {
    		parent::__construct($controller);
    		
    		$this->ext = ".zpt";
    		$this->template = new PHPTAL();
    	}
    
    /**
     * Renders and returns PHPTAL template for given view with its array of data.
     *
     * @param string $___viewFn Filename of the view
     * @param array $___dataForView Data to include in rendered view
     * @return string Rendered output
     * @access protected
     */
    	function _render($___viewFn, $___dataForView, $loadHelpers = true, $cached = false) {
    		if ($this->helpers != false && $loadHelpers === true) {
    			$loadedHelpers = array();
    			$loadedHelpers = $this->_loadHelpers($loadedHelpers, $this->helpers);
    
    			foreach(array_keys($loadedHelpers) as $helper) {
    				$replace = strtolower(substr($helper, 0, 1));
    				$camelBackedHelper = preg_replace('/\\w/', $replace, $helper, 1);
    
    				${$camelBackedHelper} =& $loadedHelpers[$helper];
    
    				if (isset(${$camelBackedHelper}->helpers) && is_array(${$camelBackedHelper}->helpers)) {
    					foreach(${$camelBackedHelper}->helpers as $subHelper) {
    						${$camelBackedHelper}->{$subHelper} =& $loadedHelpers[$subHelper];
    					}
    				}
    				$this->loaded[$camelBackedHelper] = (${$camelBackedHelper});
    			}
    		}
    		
    		$this->template->setTemplate( $___viewFn );
    		
    		foreach($___dataForView as $data => $value)
    		{
    			$this->template->set($data, $value);
    		}		
    		
    		try {
    		    return $this->template->execute();
    		} catch (Exception $e){
    		    return "<pre>".$e->__toString()."</pre>";
    		}
    	}
    }
    ?>
    ?>

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _Page 1: :///articles/view/4caea0de-e2f8-4aa0-87a1-435882f0cb67/lang:eng#page-1
.. _=180: http://cakeforge.org/snippet/detail.php?type=snippet&id=180
.. _Page 2: :///articles/view/4caea0de-e2f8-4aa0-87a1-435882f0cb67/lang:eng#page-2
.. _http://phptal.motion-twin.com/: http://phptal.motion-twin.com/
.. meta::
    :title: Using PHPTAL for templates: PhptalView class
    :description: CakePHP Article related to php5,xml,Template,phptal,Components
    :keywords: php5,xml,Template,phptal,Components
    :copyright: Copyright 2007 
    :category: components

