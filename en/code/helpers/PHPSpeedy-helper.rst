

PHPSpeedy helper
================

by %s on February 10, 2009

So I needed a nice package that minifies/gzips all assets like
js/css/html and combines them into as less http requests possible.
CakePHP1.2 does have an option called 'asset.filter.css' for example
in the core.php, but all it does is minify the files, not combining
them. PHPSpeedy does, and I've created a helper to automagicly do the
work.
PHPSpeedy reads the HTML, and replaces all < link src="" /> and <
script src="" /> with only one call to a cached, minified version for
javascript, and one for css. This reduces the numer of HTTP requests
the browser has to do for each page request. Also, it can gzip the
html so download times of html will also decrease. And the best part:
it notices changes in the javascript and css files! So cache is
automaticly rebuilded when needed.

Step 1, download PHPSpeedy
It can be found here: `http://aciddrop.com/php-speedy/`_ Open the zip,
and extract the contents of the libs folder to your vendors folder.
You should now have a php_speedy folder there.

Step 2, Configuring PHPSpeedy
We need to tell PHPSpeedy where the cached versions of the javascript
and css should be stored. This can be done in the
app/vendors/php_speedy/config.php
You can skip the username and password fields, they won't be used.
This is what I filled in for the path info:

::

    
    ## Path info
    $compress_options['document_root'] = WWW_ROOT;
    $compress_options['javascript_cachedir'] = WWW_ROOT . "js" . DS . "cache";
    $compress_options['css_cachedir'] = WWW_ROOT . "css" . DS . "cache";

Please note that the document_root setting isn't there by default! So
please add it to the config.php

As you can see, I set the cache folders as a subfolder of the js and
css folders, so they can be accessed by the browser.

Remember to set the correct permissions on the cache folders else, the
won't be writeable by PHPSpeedy.

Step 3, making small changes to the PHPSpeedy core files. This will
make PHPSpeedy compatible with CakePHP
Open app/vendors/php_speedy/php_speedy.php

FIND

::

    
    $jsmin = new JSMin($contents);

REPLACE WITH

::

    
    $jsmin = new JSMin(isset($contents) ? $contents : null);

FIND

::

    
    $compressor = new compressor(array('view'=>$view,

REPLACE WITH

::

    
    global $compressor;
    $compressor = new compressor(array('view'=>$view,

Step 4, download the code of the PHPSpeedy helper found below and put
it in the helpers folder.
Then, add the helper to the $helpers array of the controller. You can
also add it to the 'app_controller' so it works for all your
controllers.

Controller Class:
`````````````````

::

    <?php 
    	class AppController extends Controller{
    		var $helpers = array('PhpSpeedy');
    	}
    ?>



That's it, you're all set! The PHPSpeedy helper is only active when
debug mode is set to 0.

Problems I encounterd during the proces:

+ It could be that your javascript doesn't work anymore. The problem
  will most likely be a forgotten ':' at the end of the script.
+ Your first call will cause PHPSpeedy to read all javascript and css
  files and do its magic. This will take a while, but do another refresh
  and all is good again.
+ If you do not have javascript or css, set all related options in the
  config.php of PHPSpeedy to zero. If you don't, you will see PHP
  warnings regarding invalid arguments in a foreach loop
+ If you have short_open_tag disabled in your PHP configuration,
  change

Final notes:

+ This article is based on PHPSpeedy v0.5.2
+ Please refer to the readme.txt of PHPSPeedy to so pending bugs /
  inabilities



Helper Class:
`````````````

::

    <?php 
    	/**
    	* Helper for PHPSpeedy class (PHP5 only!)
    	*
    	* @author      Marcel Raaijmakers (Marcelius)
    	* @copyright   Copyright 2009, Marcel Raaijmakers
    	* @license     http://www.opensource.org/licenses/mit-license.php The MIT License
    	*/
    	class PhpSpeedyHelper extends AppHelper {
    		/**
    		* Storage of the view object
    		*/
    		private $view;
    	
    		/**
    		* Constructor
    		*/
    		public function __construct(){	
    			$this->view = ClassRegistry::getObject('view');
    		}
    		
    		/**
    		* Trigger
    		*/
    		public function afterLayout(){
    
    			if (Configure::read('debug') == 0){
    				$r = App::import('vendor', 'php_speedy');
    	
    				global $compressor;
    	
    				if ($compressor instanceof compressor){
    					$compressor->return_content = true;
    					$this->view->output = $compressor->finish($this->view->output);
    				}
    			
    				return parent::afterLayout();
    			}
    		}
    	}
    ?>

Enjoy!

.. _http://aciddrop.com/php-speedy/: http://aciddrop.com/php-speedy/
.. meta::
    :title: PHPSpeedy helper
    :description: CakePHP Article related to helper,helpers,performance,minify,phpspeedy,Helpers
    :keywords: helper,helpers,performance,minify,phpspeedy,Helpers
    :copyright: Copyright 2009 
    :category: helpers

