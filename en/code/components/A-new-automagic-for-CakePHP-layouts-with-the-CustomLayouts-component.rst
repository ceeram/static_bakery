

A new "automagic" for CakePHP layouts with the CustomLayouts component
======================================================================

by %s on November 15, 2008

If you distribute your CakePHP web application to others and they ask
you how to personalize the layout of a specific controller or, with
even more granularity, a specific action, what do you do? This
component creates a new convention for layouts that lets you
automagically personalize the layouts for controllers and single
actions with one line of code.
Adding this component to the $components property of your controller
(or AppController) enables the following convention:


+ if exists, use the views/layouts/{controller}/{action}.ctp layout,
  otherwise
+ if exists, use the views/layouts/{controller}.ctp layout, otherwise
+ use the layout defined by the controller

This component is usefull if you want to make your web application
"customizable" (from the UI point of view), but you don't want to
hardcode the layout names in your controllers' source code.

The convention is "executed" before the controller's beforeFilter()
method so you can override it in case of necessity.


Component Class:
````````````````

::

    <?php 
    /**
     * PHP versions 4 and 5
     *
     * Custom Layouts Component: A component that introduces a new convention for CakePHP layout files
     * Copyright 2007-2008, Creative Park Srl
     *                      Borgo Acquileia, 3/f
     *                      33057 Palmanova (UD) Italia
     *                      http://www.creativepark.it/
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     */
    
    class CustomLayoutsComponent extends Object {
    
    	function initialize(&$controller) {
    		$action = Inflector::underscore($controller->action);
    		$name = Inflector::underscore($controller->name);
    		$file = LAYOUTS . $name . DS . $action . $controller->ext;
    		if (file_exists($file)) {
    			$controller->layout = $action;
    			$controller->layoutPath = $name;
    		} else {
    			$file = LAYOUTS . $name . $controller->ext;
    			if (file_exists($file)) {
    				$controller->layout = $name;
    				$controller->layoutPath = NULL;
    			}
    		}		 
    	}
    
    }
    ?>


P.S. This is my first component and I'm not even sure this convention
already exists :)

.. meta::
    :title: A new "automagic" for CakePHP layouts with the CustomLayouts component
    :description: CakePHP Article related to Layouts,automagic,Components
    :keywords: Layouts,automagic,Components
    :copyright: Copyright 2008 
    :category: components

