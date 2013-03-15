FlashHelper - a wrapper for the SwfObject js class
==================================================

by %s on November 25, 2008

After having had small issues every time I try to embed flash in my
apps, and always resorting to using the javascript SwfObject, I
decided it was time to just make a wrapper helper for this helpful
vendor. So, if you need a fast, easy and reliable way of adding flash
to your projects, look now further, the FlashHelper is here to help.


What it does
~~~~~~~~~~~~

It gives you a oneliner to embed flash into your views as easy as the
HtmlHelper::image lets you embed pictures. It does this using a
javascript class called SwfObject [1]


Requirements
~~~~~~~~~~~~

+ This helper in ur /app/views/helpers folder (it's found on next
  page)
+ The helper added to your controller's helpers property
+ The javascript file in ur /app/webroot/js folder (found [2])



How to use it
~~~~~~~~~~~~~

The helper has only two public functions and one of them is optional.


init($options)
``````````````

The optional init function has the job of linking the document to the
required javascript,
but as long as the helper is able to use the layouts
$scripts_for_layout , calling this method
multiple times is not a problem.

The other feature of this method is taking in options that subsequent
calls of renderSwf will use, ie for reusing a set of options for
multiple flash embeds, set them here. Note, that if you
do not call this method, the renderSwf will.


renderSwf($swfFile,$width,$height,$divDomId,$options)
`````````````````````````````````````````````````````

This is used once per flash file you wish to embed. Think of it as the
flash equalent of $html->image. The parameters are pretty straight
forward, but let me explain the few interesting aspects.

The first parameter is a string of the swf files name, relative to the
webroot.

The width and height may be set through the options array of the init
function, if you wish.

The divDomId parameter defaults to false, which will make the helper
not look for an existing div, but render one at the place the helper
is called. If you wish the helper to use a div of your choosing,
supply the dom id.

Note : The div is replaced by the flash, make another wrapper div for
styling

If you wish to have alternate content that is displayed if the flash
may not be embedded or javascript is disabled, put that content in the
div with the supplied dom id.


The options array is a nested array with 3 first level keys that it
looks for;
``````````

+ flashvars
+ params
+ attributes

The documentation for what valid keys and values these three accepts
may be found at [3]


Examples
~~~~~~~~

::

    <div id="flashy"><p>No flash loaded</p></div>
    <?php echo $flash->renderSwf('test.swf',400,200,'flashy');?>

::

    <?php 
    $flash->init(array('width'=>200,'height'=>100));
    echo $flash->renderSwf('test1.swf');
    echo $flash->renderSwf('test12swf');
    ?>

::

    <?php 
    echo $flash->renderSwf('fl_countdown_v3_3.swf?mo=1&da=24&snd=off&co=AA3333',800,250,false,array('params' => array('movie'=>'?mo=1&da=24&snd=off&co=AA3333')));
    ?>



Feedback
~~~~~~~~

Please leave a comment or report any issue / improvements to me [4]
and I will try to keep the helper updated


Links
`````

#. `http://code.google.com/p/swfobject/`_
#. `http://code.google.com/p/swfobject/downloads/detail?name=swfobject
   _2_1.zip`_
#. `http://code.google.com/p/swfobject/wiki/documentation`_
#. `http://code.google.com/p/alkemann/issues/entry`_



Component Class:
````````````````

::

    <?php 
    /**
     * A helper for embedding flash into your site using Javascript.
     * This helper is simply a wrapper for the javascript SwfObject vendor found here:
     * 
     *   http://code.google.com/p/swfobject/
     * 
     * It has simply two functions, one is optional. If you wish to embed several
     * flash files into your view, you can initialize the helper (include the javascript
     * library) once and also set default options for all your subsequent calls.
     *
     * Example 1 :
     * 
     * echo $flash->renderSwf('test.swf',400,200,'flashy');
     * echo '<div id="flashy"></div>';
     * 
     * Example 2 :
     * 
     * $flash->init(array('width'=>200,'height'=>100));
     * echo $flash->renderSwf('test1.swf');
     * echo $flash->renderSwf('test2swf');
     * 
     * Example 3 :
     * 
     * echo $flash->renderSwf('flashfiles/fl_countdown_v3_3.swf?mo=1&da=24&snd=off&co=AA3333',800,250,false,
     * 		array('params' => array('movie'=>'?mo=1&da=24&snd=off&co=AA3333')));
     * 
     * @author Alexander Morland
     * @license MIT
     * @version 1.2
     * @modified 19. nov. 2008
     */
    class FlashHelper extends AppHelper {	
    	var $helpers = array('Javascript');
    	/**
    	 * Used for remembering options from init() to each renderSwf
    	 *
    	 * @var array
    	 */
    	var $options = array(
    		'width' => 100,
    		'height' => 100
    	);
    
    	/**
    	 * Used by renderSwf to set a flash version requirement
    	 *
    	 * @var string
    	 */
    	var $defaultVersionRequirement = '9.0.0';
    	
    	/**
    	 * Used by renderSwf to only call init if it hasnt been done, either
    	 * manually or automatically by a former renderSwf()
    	 *
    	 * @var boolean
    	 */
    	var $initialized = false;
    	
    	/**
    	 * Optional initializing for setting default parameters and also includes the
    	 * swf library. Should be called once, but if using several groups of flashes,
    	 * MAY be called several times, once before each group.
    	 *
    	 * @example echo $flash->init();
    	 * @example $flash->init(array('width'=>200,'height'=>100);
    	 * @return mixed String if it was not able to add the script to the view, true if it was
    	 */
    	function init($options = array()) {
    		if (!empty($options)) {
    			$this->options = am($this->options, $options);
    		}
    		$this->initialized = true;
            $view =& ClassRegistry::getObject('view'); 
            if (is_object($view)) { 
                $view->addScript($this->Javascript->link('swfobject')); 
                return true;
            } else {
            	return $this->Javascript->link('swfobject');
            }
    	}
    	
    	/**
    	 * Wrapper for the SwfObject::embedSWF method in the vendor. This method will write a javascript code
    	 * block that calls that javascript method. If given a dom id as fourth parameter the flash will 
    	 * replace that dom object. If false is given, a div will be placed at the point in the 
    	 * page that this method is echo'ed. The last parameter is mainly used for sending in extra settings to
    	 * the embedding code, like parameters and attributes. It may also send in flashvars to the flash. 
    	 * 
    	 * For doucumentation on what options can be sent, look here:
    	 * http://code.google.com/p/swfobject/wiki/documentation
    	 *
    	 * @example echo $flash->renderSwf('counter.swf'); // size set with init();
    	 * @example echo $flash->renderSwf('flash/ad.swf',100,20);
    	 * @example echo $flash->renderSwf('swf/banner.swf',800,200,'banner_ad',array('params'=>array('wmode'=>'opaque')));
    	 * @param string $swfFile Filename (with paths relative to webroot)
    	 * @param int $width if null, will use width set by FlashHelper::init()
    	 * @param int $height if null, will use height set by FlashHelper::init()
    	 * @param mixed $divDomId false or string : dom id
    	 * @param array $options array('flashvars'=>array(),'params'=>array('wmode'=>'opaque'),'attributes'=>array());
    	 * 		See SwfObject documentation for valid options
    	 * @return string
    	 */
    	function renderSwf($swfFile, $width = null, $height = null, $divDomId = false, $options = array()) {
    		$options = am ($this->options, $options);		
    		if (is_null($width)) {
    			$width = $options['width'];
    		}
    		if (is_null($height)) {
    			$height = $options['height'];
    		}
    		$ret = '';
    		if (!$this->initialized) {
    			$init = $this->init($options);
    			if (is_string($init)) {
    				$ret = $init;
    			}
    			$this->initialized = TRUE;
    		}		
    		$flashvars = '{}';
    		$params =  '{wmode : "opaque"}';
    		$attributes = '{}';
    		if (isset($options['flashvars'])) {
    			$flashvars = $this->Javascript->object($options['flashvars']);
    		}
    		if (isset($options['params'])) {
    			$params = $this->Javascript->object($options['params']);
    		}
    		if (isset($options['attributes'])) {
    			$attributes = $this->Javascript->object($options['attributes']);
    		}
    	
    		if ($divDomId === false) {
    			$divDomId = uniqid('c_');
    			$ret .= '<div id="'.$divDomId.'"></div>';
    		}
    		if (isset($options['version'])) {
    			$version = $options['version'];
    		} else {
    			$version = $this->defaultVersionRequirement;			
    		}
    		if (isset($options['install'])) {
    			$install = $options['install'];
    		} else {
    			$install =  '';			
    		}
    		
    		$swfLocation = $this->webroot.$swfFile;
    		$ret .= $this->Javascript->codeBlock(
    			'swfobject.embedSWF("'.$swfLocation.'", "'.$divDomId.'", "'.$width.'", "'.$height.'", "'.$version.'","'.$install.'", '.$flashvars.', '.$params.', '.$attributes.');');
    	
    		return $ret;
    	}
    }?>

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _Page 1: :///articles/view/4caea0e3-c568-4290-bd5a-4e8782f0cb67/lang:eng#page-1
.. _Page 2: :///articles/view/4caea0e3-c568-4290-bd5a-4e8782f0cb67/lang:eng#page-2
.. _http://code.google.com/p/swfobject/wiki/documentation: http://code.google.com/p/swfobject/wiki/documentation
.. _http://code.google.com/p/alkemann/issues/entry: http://code.google.com/p/alkemann/issues/entry
.. _http://code.google.com/p/swfobject/downloads/detail?name=swfobject_2_1.zip: http://code.google.com/p/swfobject/downloads/detail?name=swfobject_2_1.zip
.. _http://code.google.com/p/swfobject/: http://code.google.com/p/swfobject/
.. meta::
    :title: FlashHelper  -  a wrapper for the SwfObject js class
    :description: CakePHP Article related to flash,alkemann,swf,swfobject,Helpers
    :keywords: flash,alkemann,swf,swfobject,Helpers
    :copyright: Copyright 2008 
    :category: helpers

