Basic Fallback Component
========================

by %s on October 30, 2006

This is some basic code showing how to capture a missing action error
from a component and call a default action defined in the controller.


Component Code
--------------

Component Class:
````````````````

::

    <?php 
    class FallbackComponent extends Object
    {
    	function startup(&$controller)
    	{
    		$allowed_methods = get_class_methods($controller);
    		if (!$allowed_methods) $allowed_methods = array();
    		if (!in_array($controller->action, $allowed_methods))
    		{
    			if (isset($controller->defaultAction) && method_exists($controller, $controller->defaultAction))
    			{
    				echo $controller->{$controller->defaultAction}();
    				exit(); // To prevent double rendering
    			}
    		}
    
    	}
    }
    ?>



Controller code
---------------
Add to your controller:

Controller Class:
`````````````````

::

    <?php 
    var $components = array('Fallback');
    var $defaultAction = 'myDefaultAction';
    
    function myDefaultAction()
    {
    echo 'I\'m a default action. I was called because nobody else answered the call...'
    }
    ?>



What happens
------------
If your controller is called with a non-existent action, then the
component will look for a variable called 'defaultAction'. If found,
it will look for a function in your controller with the name specified
within that variable and call it if it exists.



Note
----
This example is pretty stripped down and can probably be done in a
better way. If anyone fancies posting a more complete example, feel
free to update this.

.. meta::
    :title: Basic Fallback Component
    :description: CakePHP Article related to default action,component,Components
    :keywords: default action,component,Components
    :copyright: Copyright 2006 
    :category: components

