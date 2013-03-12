

Greater Control with Webservices Component
==========================================

by %s on October 29, 2006

Achieving better control over the Webservices feature provided by the
core of CakePHP.
I recently started working on a project where I found it would be nice
to make use of ajax and rss webservices and what better way to do this
then with the native webservices feature of CakePHP, however I found
that there was no ability for a more refined control of this feature.

Before I give you the code I want to explain its purpose and
abilities. The first purpose of this component is to determine if a
webservice is being used, the second is to provide some automated
viewPath changes and view rendering changes, if enabled.

The CakePHP Manual states that the webservices feature allows for a
prepended route for all controller/actions for the listed webservices,
but it doesn't provide any means for determining if a webservice has
been used. You are left to your own methods for determining and taking
action upon it if a webservice is being used.

The main problem is, how do you determine how a webservice is being
used or now? This component does it for you. Instead of having to
determine whether a webservice is being used in each controller/action
where you want to use it, you can take care of all that with this
component.

The Webservices component first determines if a webservice is being
used, then it determines if a controller/action pair is being called
that has been allowed to have webservices for it, then it determines
what to do based off of the variables set for each webservice.

If you are confused, please continue reading as I will try and clarify
things up a bit, but first I want to present the code.


Component Class:
````````````````

::

    <?php 
    	class WebservicesComponent extends Object
    	{
    		var $name = 'Webservices';
    		
    		var $controller = true;
    
    		// Set this to true if all you want is for this component to determine
    		// the webservice being used and set it to a variable in the controller.
    		var $setOnlyWebserviceVariable = false;
    
    
    		// What controller/actions will this component modify layout and view path views.
    		var $allowed_controller_actions = array('controller' => 'action',
    												'tests' => 'send',
    										  );
    
    
    		var $ajax = array('render_layout' => false,				// Do you want the layout to be rendered with this service?
    						  'render_alternate_view' => true,		// Do you want to render an alternate view?
    						  'alternate_view_path' => 'ajax', 		// Where is this view in relation to the controller/action view.
    					);
    
    		var $rest = array('render_layout' => false,				// Do you want the layout to be rendered with this service?
    						  'render_alternate_view' => true,		// Do you want to render an alternate view?
    						  'alternate_view_path' => 'rest', 		// Where is this view in relation to the controller/action view.
    					);
    
    		var $rss = array('render_layout' => false,				// Do you want the layout to be rendered with this service?
    						 'render_alternate_view' => true,		// Do you want to render an alternate view?
    						 'alternate_view_path' => 'rss', 		// Where is this view in relation to the controller/action view.
    				   );
    
    		var $soap = array('render_layout' => false,				// Do you want the layout to be rendered with this service?
    						  'render_alternate_view' => true,		// Do you want to render an alternate view?
    						  'alternate_view_path' => 'soap', 		// Where is this view in relation to the controller/action view.
    					);
    
    		var $xml = array('render_layout' => false,				// Do you want the layout to be rendered with this service?
    						 'render_alternate_view' => true,		// Do you want to render an alternate view?
    						 'alternate_view_path' => 'xml', 		// Where is this view in relation to the controller/action view.
    				   );
    
    		var $xmlrpc = array('render_layout' => false,				// Do you want the layout to be rendered with this service?
    						    'render_alternate_view' => true,		// Do you want to render an alternate view?
    						    'alternate_view_path' => 'xmlrpc', 		// Where is this view in relation to the controller/action view.
    					  );
    
    
    		function startup (&$controller)
    		{
    			$this->controller = &$controller;
    
    			// If webservices is not turned on, stop now.
    			if (WEBSERVICES != 'on')
    				return false;
    
    			// If this is not allowed controller/action then stop.
    			if (isset($this->allowed_controller_actions[strtolower($this->controller->name)]) &&
    				$this->allowed_controller_actions[strtolower($this->controller->name)] != strtolower($this->controller->action))
    				return false;
    
    
    			$here = $this->controller->here;
    
    			if (preg_match('/^\/ajax.*/', $here, $crap))
    				$webservice = 'ajax';
    			else if (preg_match('/^\/rest.*/', $here, $crap))
    				$webservice = 'rest';
    			else if (preg_match('/^\/rss.*/', $here, $crap))
    				$webservice = 'rss';
    			else if (preg_match('/^\/soap.*/', $here, $crap))
    				$webservice = 'soap';
    			else if (preg_match('/^\/xml.*/', $here, $crap))
    				$webservice = 'xml';
    			else if (preg_match('/^\/xmlrpc.*/', $here, $crap))
    				$webservice = 'xmlrpc';
    			else
    				$webservice = null;
    
    			// Set the webservice variable for the controller so if you 
    			$this->controller->webservice = $webservice;
    
    			// If setOnlyWebserviceVariable is true or a webservice wasn't detected, stop now.
    			if ($this->setOnlyWebserviceVariable === true || $webservice == null)
    				return;
    
    			
    			if ($this->{$webservice}['render_layout'] === false)
    				$this->controller->layout = false;
    			else
    				$this->controller->layout = $this->{$webservice}['render_layout'];
    
    			if ($this->{$webservice}['render_alternate_view'] === true)
    				$this->controller->viewPath = $this->controller->viewPath . DS . $this->{$webservice}['alternate_view_path'];
    		}
    	}
    ?>

The default settings for all the webservices in this component are 1.
not to render a layout , 2. view rendered will be different then the
default one for the controller/action , and 3. the alternate view path
is a subdirectory of the controller/action views directory with the
name of the webservice . Now for some clarification.

#1. I think is pretty straight forward, the only thing else you need
to know is that if you want to render a specific layout then specify
it instead of "false".

#2. Once again I think this one is pretty straight foward, if you want
a view other then the default view for the controller/action pair, set
to true. If set to true, then #3 comes into play.

#3. Basically at this time the alternate view path is a subdirectory
of the controller/action view path. For example if you have a
controller users with action display the normal viewPath would be
views/users/display.thtml . However with the default settings of this
component, if webservice ajax was being used the new viewPath would be
/views/users/ajax/display.thtml .

There are just a few more variables you should be aware of and I have
listed them below.

If you wish for the component to only determine whether or not a
webservice is being used then set the $setOnlyWebserviceVariable to
true. You may want to do this if you are wanted to do something other
then render a different view for the controller/action for the
webservice.

For each controller/action pair you want to this component to act upon
you will need to place a associate array entry into the
$allowed_controller_actions array.

I hope I didn't leave anything out and if I did I will add it later.
If you have any questions just post a comment or email me. I hope this
helps someone with their Cake application.

.. meta::
    :title: Greater Control with Webservices Component
    :description: CakePHP Article related to webservices,Rss,xml,component,Components
    :keywords: webservices,Rss,xml,component,Components
    :copyright: Copyright 2006 
    :category: components

