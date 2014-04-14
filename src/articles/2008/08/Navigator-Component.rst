Navigator Component
===================

by joebeeson on August 17, 2008

A component to allow for URLs based off a Tree structure in your model
I built this component to allow for URLs such as
`http://www.website.com/categories/top-category/sub-category1/sub-
category2`_ in an application. It requires the Tree and Sluggable
behaviors to be attached to your model.


Component Class:
````````````````

::

    <?php 
    
    	class NavigatorComponent extends Object {
    		
    		function initialize(&$Controller) {
    
    			// Split our URL into an array and remove the controller path from the URL
    			$URL = array_slice(array_filter(explode('/',$Controller->here)), 1);
    			
    			// Setup our model object. Allow for controller overrides of the model
    			$Model = $Controller->{(!empty($Controller->uses) 
    					? $Controller->uses 
    					: Inflector::singularize($Controller->name))};
    					
    			// Determine which column is being used for the slugs. Default to 'slug'
    			$SlugColumn = (isset($Model->actsAs['Sluggable']['slug'])
    								? $Model->actsAs['Sluggable']['slug']
    								: 'slug');
    			
    			// Don't run if the model isn't sluggable or acting as a tree.
    			if (!isset($Model->actsAs['Sluggable']) and !isset($Model->actsAs['Tree']) or !in_array('Tree', $Model->actsAs)) { return; }
    							
    			// Don't run if there's an action with the name we've been passed.	
    			if (!method_exists($Controller, $URL[0])) {
    				
    				// Check if the last slug in the URL is valid
    				$Slug = $Model->find(array($Model->name.'.'.$SlugColumn => array_slice($URL, -1)));
    				if ($Slug) {
    					
    					// Get the row ID of our slug and retrieve the path for it
    					$SlugID 	= $Slug[$Model->name][$Model->primaryKey];
    					$ValidPath	= Set::extract('/'.$Model->name.'/'.$SlugColumn, $Model->getPath($SlugID, array($SlugColumn), false));
    
    					// Check if the path matches the reported correct path
    					if (!array_diff($ValidPath, $URL)) {
    						// We have a valid slug and a valid path. Use the dispatcher to redirect
    						$Dispatch = ClassRegistry::init('Dispatcher', 'Dispatcher');
    						$Dispatch->dispatch('/'.$Controller->name.'/view/'.$SlugID);
    						die; // We have to halt here or we'll trigger a "Missing Action" error.
    					}
    				}
    			}
    		}
    	}
    ?>

For a more detailed explanation, please visit
`http://blog.joebeeson.com/?p=93`_

.. _http://www.website.com/categories/top-category/sub-category1/sub-category2: http://www.website.com/categories/top-category/sub-category1/sub-category2
.. _http://blog.joebeeson.com/?p=93: http://blog.joebeeson.com/?p=93
.. meta::
    :title: Navigator Component
    :description: CakePHP Article related to ,Components
    :keywords: ,Components
    :copyright: Copyright 2008 joebeeson
    :category: components

