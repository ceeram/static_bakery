

Another Jamal Helper
====================

by %s on June 25, 2008

Jamal is an MVC framework for Javascript; it sounds stupid, but it
makes it easy to understand code in retrospect. This helper is based
on Hoffstetter's helper, but adds head statement injection and other
niceties.
First of all, CakePHP's Javascript helper never worked for me (and I'm
using RC1), so I use the Head Helper for CakePHP 1.1. If you want
to/can get the Javascript helper to work, feel free to switch all the
$head->register_js statements to $javascript->link.

Second of all, you need the Jamal framework. I use version 0.4, but
this probably works with other versions.

Third of all, it would be nice to integrate a
compressor/tinifier/minifier into this, but I don't know how it would
work if you use PHP in your javascript files... I've commented all of
that potential functionality that was left from Hofstetter's legacy
and forced a 'debug=true' line there so we load the JS files as
separate files.

Ok, now comes a tricky part... the helper assumes the following
directory structure:

::

    
     app/
      webroot/     <- We put libraries in webroot
          js/
              jamal/
                  dist/      
                       jamal.js
                       jamal_packed.js
              vendors/        <- This is just for fun, to make it look like cakePHP
                  jquery/
                      jquery.js
                      plugins/
                          jquery.require.js  <- I use this file to include other JS files from within the JS environment
      vendors/     <- Putting js in the app vendors directory lets us use php in the js! think i18n!
          js/
              controllers/
                  somes_controller.js
              jamal_packed_mvc/
                  some_packed.js <- your MVC js all packed in one file
              models/
                  some.js
              views/
                  somes.js
              app_controller.js  <- common functions for controllers?
              app_model.js    <- ditto?
              app_view.js     <- ditto?
    

Since we are assuming that Jamal is an MVC framework, and we want it
to slightly resemble our familiar CakePHP layout, we use a 'vendors'
directory in the JS directory and stick static libraries like jquery
in there.
You must place your JS MVC files in the app/vendors/js because files
in there are preprocessed by cakePHP, allowing you to put php code in
them; you can finally use internationalization and localization in
your Javascript!

The directory structure is used to automatically load controller,
model and view JS files. In the sample structure above, if you had a
cakephp controller called 'SomeController', your view would
automatically load vendors/js/controller/somes_controller.js,
vendors/js/models/some.js, and vendors/js/views/somes.js.

If you wish to override the automatic file naming convention, just add
the following code to your view:

View Template:
``````````````

::

    
    <?php $jamal->set('your_controller_name', 'your_action_name') ?>

which would load files named your_controller_names.js etc. and call
the action 'your_action_name' on load.
If you don't want any of your files loading, just use

View Template:
``````````````

::

    
    <?php $jamal->set('none');?>

I believe in keeping JS to JS, and not mingling the MVC frameworks, so
I try to load Javascript files dynamically from Javascript itself,
therefore I have the jquery.require plugin. You don't need to do
things my way.

I've tried to do this as painless as possible, but you still need to
add a piece of code to your default layout inside the tag:

View Template:
``````````````

::

    
    <body<?(if (!empty($jamal_for_layout)) echo $jamal_for_layout)?>

That's it.

Let me give you some examples of what the JS code might look like,
since it's extremely hard to find any documentation of Jamal.

Our JS controller, somes_controller.js

::

    
    $j.c({Somes: {
        name: 'SomesController',
        
        index: function() {
             alert('You have just opened http://blahblahblah.com/somes/index!');
        },
        
        myAction: function() {
             alert('You have just opened http://blahblahblah.com/somes/myAction!');
        }
    }
    });

and voila, you'll have a popup whenever you open either page. Of
course, this is a trivial example, and a popup should be in the view
file, not in the controller, but you get the picture.

There is no absolute rule about what functions go in which files, but
I personally use the model file to store all functions relating to
reading files, validation, and ajax; the controller file for pretty
much any abstract action and to set button actions; and the view file
for anything that alters the html of the page. Of course, a method in
the model may call a method in the view to alter the page, and a
method in controller may call another in the model to retrieve
information from elsewhere. And so forth.

And now, the helper file (helpers/jamal.php):

Helper Class:
`````````````

::

    <?php 
    /**
     * Jamal helper
     * 
     * Requires the Jamal framework (http://jamal.moagil.de) and the JQuery framework (http://jquery.com).
     * and the 'head' helper, since the Javascript helper doesn't work for me...
     *
     * Based on the Jamal helper Copyright (c) 2007, Daniel Hofstetter (http://cakebaker.42dh.com)
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    
     /**
     * This version assumes a JS controller and JS action from cakephp's current controller
     * and action. These can be overwritten by using $jamal->set('controller', 'action')
     * in the view. To not load any files, just use $jamal->set('none');
     *
     * Using the head helper we inject the js requires at the head of the html.
     *
     * You still need to use $jamal_for_layout in your views; I suggest you wrap it 
     * in a conditional statement
     * e.g. <body<?(if (!empty($jamal_for_layout)) echo $jamal_for_layout)?>>
     *
     * Your directory structure should look like this:
     * app/
     *  webroot/     <- We put libraries in webroot
     *      js/
     *          jamal/
     *              build/
     *              demo/
     *              dist/       <- This is the only one you need, technically
     *              src/
     *              test/
     *          vendors/        <- This is just for fun, to make it look like cakePHP. Put any external JS libraries in here and load them in some way
     *              jquery/
     *                  jquery.js
     *                  plugins/
     *                      jquery.require.js  <- I use this file to include other JS files from within the JS environment
     *  vendors/     <- Putting js in the app vendors directory lets us use php in the js! think i18n!
     *      js/
     *          controllers/
     *              somes_controller.js
     *          jamal_packed_mvc/
     *              some_packed.js <- your MVC js all packed in one file
     *          models/
     *              some.js
     *          views/
     *              somes.js
     *          app_controller.js  <- common functions for controllers?
     *          app_model.js    <- ditto?
     *          app_view.js     <- ditto?
     */
    
    class JamalHelper extends AppHelper {
    		var $helpers = array('head');
    		var $controller = '';
    		var $action = '';
    		var $debug = '';
            var $classDefinition = '';		
            
    		function afterRender() {
                // Provide defaults
                if (empty($this->debug)) $this->debug = Configure::read('debug') > 0 ? 'true' : 'false';
                if (empty($this->controller)) $this->controller = $this->params['controller'];
                if (empty($this->action)) $this->action = $this->params['action'];
                
                if ($this->controller != 'none') {
    				$this->classDefinition = ' class="jamal {controller:\''.Inflector::camelize($this->controller).'\',action:\''.$this->action.'\',debug:'.$this->debug.'}"';
    			} else {
    				$this->classDefinition = '';
                    return;
    			}
    			$view = ClassRegistry::getObject('view');
    			$view->set('jamal_for_layout', $this->classDefinition);
                $this->head->register_js('vendors/jquery/jquery.js');
                if (Configure::read('debug') == 0 && file_exists(JS.'jamal_packed_mvc'.DS.$this->controller.'_packed.js')) {
                    $this->head->register_js('jamal/dist/jamal_packed.js');
                    $this->head->register_js('jamal_packed_mvc/'.$this->controller.'_packed.js');
                } else { //TODO: limit to current mvc name only...
    				$jsFiles[] = 'jamal/dist/jamal.js';
                    // jquery.require.js allows us to 'include' javascript files as required by other js files
                    if (file_exists(JS.'vendors'.DS.'jquery'.DS.'plugins'.DS.'jquery.require.js'))      $jsFiles[] = 'vendors/jquery/plugins/jquery.require.js';
                    if (file_exists(APP.DS.'vendors'.DS.'js'.DS.'app_controller.js'))    $jsFiles[] = 'app_controller.js'; 
                    if (file_exists(APP.DS.'vendors'.DS.'js'.DS.'app_model.js'))         $jsFiles[] = 'app_model.js';
                    if (file_exists(APP.DS.'vendors'.DS.'js'.DS.'app_view.js'))          $jsFiles[] = 'app_view.js';
    				if (file_exists(APP.DS.'vendors'.DS.'js'.DS.'models'.DS.Inflector::singularize($this->controller).".js"))    $jsFiles[] = "models/".Inflector::singularize($this->controller).".js";
                    if (file_exists(APP.DS.'vendors'.DS.'js'.DS.'controllers'.DS."{$this->controller}_controller.js"))           $jsFiles[] = "controllers/{$this->controller}_controller.js";
                    if (file_exists(APP.DS.'vendors'.DS.'js'.DS.'views'.DS."{$this->controller}.js"))                            $jsFiles[] = "views/{$this->controller}.js";
    				
                    foreach ($jsFiles as $file) {
                        $this->head->register_js($file);
                    }
    			}
    		}
    
    		function set($controller, $action = 'index', $debug = 'auto') {
    			$this->controller = $controller;
    			$this->action = $action;
    			//echo "...set controller: $controller...";
    			if ($debug != 'auto') {
    				$this->debug = $debug ? 'true' : 'false';
    			} else {
    				//$debugLevel = Configure::read('debug');
    				//$this->debug = $debugLevel > 0 ? 'true' : 'false';
                    $this->debug = 'true';
    			}
    		}
            
            function get_classDef() {
                return $this->classDefinition;
            }
    	}
    ?>


.. meta::
    :title: Another Jamal Helper
    :description: CakePHP Article related to mvc,jamal,Helpers
    :keywords: mvc,jamal,Helpers
    :copyright: Copyright 2008 
    :category: helpers

