Routes Component
================

by jbcrawford on June 04, 2009

This is a component to add/remove routes directly to/from the
/app/config/routes.php file.
You can use this if you are building a content management system or
something else where you may not be around to manually create routes
for your app.

Please keep in mind that for this to work your web server needs
read/write permission to your /app/config/routes.php file. Short story
short...here is the code:


Component Class:
````````````````

::

    <?php 
    class RouteComponent extends Object {
    	var $route_file = '../config/routes.php';
    	
    	function initialize() {
    		if (!is_file($this->route_file)) {
    			die('The path to your route file is wrong. Edit /app/controllers/components/route.php and fix the problem.');
    		}
    	}
    	
    	function add($route) {
    		$route = $route."\n";
    		if (is_writable($this->route_file)) {
    			$routes = file($this->route_file);
    			$new_routes = '';
    			foreach ($routes as $i) {
    				if (trim($i) != '?>') {
    					$new_routes .= $i;
    				} else break;
    			}
    			$handle = fopen($this->route_file, 'w');
    			if (fwrite($handle, $new_routes.$route.'?>')) {
    				return true;
    			} else return false;
    			fclose($handle);
    		}else return false;
    	}
    	
    	function remove($route) {
    		$route = $route."\n";
    		if (is_writable($this->route_file)) {
    			$routes = file($this->route_file);
    			$new_routes = '';
    			foreach ($routes as $i) {
    				if (trim($i) != '?>') {
    					if ($i != $route) {
    						$new_routes .= $i;
    					}
    				} else break;
    			}
    			$handle = fopen($this->route_file, 'w');
    			if (fwrite($handle, $new_routes.'?>')) {
    				return true;
    			} else return false;
    			fclose($handle);
    		} else return false;
    	}
    
    	/* Suggested by JosÃ© Pedro Saraiva */
    	function check( $route ) {
    		$route = $route . "\n";
    		if (is_writable( $this->route_file )) {
    			$routes = file( $this->route_file );
    			$new_routes = '';
    			foreach ( $routes as $i ) {
    				if (trim( $i ) != '?>') {
    					if ($i == $route) {
    						return true;
    					}
    				} else
    					break;
    			}
    		}
    		return false;
    	}
    }
    ?>


Usage:
------

Controller Class:
`````````````````

::

    <?php 
    // To create a new route
    $route->add("Router::connect('/test', array('controller' => 'pages', 'action' => 'display', 'test'));");
    // To remove a route
    $route->remove("Router::connect('/test', array('controller' => 'pages', 'action' => 'display', 'test'));");
    ?>



.. author:: jbcrawford
.. categories:: articles, components
.. tags:: routes,Components

