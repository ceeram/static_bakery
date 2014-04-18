How to create an XML-RPC server with CakePHP
============================================

This tutorial describes a method to create an XML-RPC server that goes
a bit against Cakeâ€™s logic and conventions.
I like the way the framework â€œdistributesâ€ the logic of the web
application between different controllers (i.e. Posts, Comments, and
so on). But when I want to develop an XML-RPC server, I prefer to
place all the code into a single controller so I can maintain
consistency and have a single place to look for errors. The other
advantage of this solution is that you donâ€™t need to add logic in
the single controllers to handle XML-RPC calls.

For the implementation of the XML-RPC protocol I decided to go with
the Inutio XML-RPC Library (`http://scripts.incutio.com/xmlrpc/`_).
This library is completely object oriented, easy to use and works with
both PHP 4 and PHP 5.

You can download a little modified version from the following URL:

`http://www.creativepark.it/downloads/xmlrpc.zip`_

The 2 changes I made are described in the header of the file.

Here are the steps to build your XML-RPC server with CakePHP:


#. Download the xmlrpc.zip archive and extract its content into your
   app/vendors folder.
#. Create the app/controllers/xml_rpc_controller.php file as follows:

File: app/controllers/xml_rpc_controller.php

Controller Class:
`````````````````

::

    <?php 
    
    // Import the app/vendor/xmlrpc.php library
    App::import('Vendor', 'xmlrpc');
    
    class XmlRpcController extends AppController {
    
    	// This demo doesn't need models
    	var $uses = array();
    	
    	// The XML-RPC server object
    	var $server = null;
    
    	function index() {
    
    		// Disable debug information
    		// Required to generate valid XML output
    		Configure::write('debug', 0); 
    
    		// Avoids render() call
    		$this->autoRender = false;
    
    		// XML-RPC callbacks settings
    		// Use this parameter to map XML-RPC methods to your protected or private controller methods
    		$callbacks = array();
    		$callbacks['demo.sayHello'] = array(&$this, '_sayHello');
    
    		// Handle XML-RPC request
    		$this->server = new IXR_Server($callbacks);
    	}
    	
    	// Protected Method
    	function _sayHello($name, $country) {
    		return "Hi {$name}! You come from {$country}!";
    	}
    	
    }
    
    ?>

Thatâ€™s all. You have created your first, simple XML-RPC server.
To test its behavior, you can use the following client script:

::

    <?php
    
    // Include the XML-RPC class
    require_once(dirname(__FILE__) . '/xmlrpc.php');
    
    // Set here your XML-RPF control url
    $url = 'http://cake.local/xml_rpc';
    
    // Create the client object
    $client = new IXR_Client($url);
    
    // Set the debug property to true during development to see both request and response data
    // $client->debug = true;
    
    // Call the demo.sayHello method passing 'Filippo' and 'Italy' as first and second parameter
    if (!$client->query('demo.sayHello', 'Filippo', 'Italy')) {
        die('Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage());
    }
    
    // Display the result
    echo '<pre>';
    print_r($client->getResponse());
    echo '</pre>';
    
    ?>

Of course this isnâ€™t very useful, but itâ€™s a starting point.

Here is the another controller that exposes some functions of the Blog
sample application (requires the Post table and model).

File: app/controllers/xml_rpc_controller.php

Controller Class:
`````````````````

::

    <?php 
    
    // Import the app/vendor/xmlrpc.php library
    App::import('Vendor', 'xmlrpc');
    
    class XmlRpcController extends AppController {
    
    	// This time we need the Post model
    	var $uses = array('Post');
    	
    	// The XML-RPC server object
    	var $server = null;
    	
    	// XML-RPC access point
    	function index() {
    
    		// Disable debug information
    		// Required to generate valid XML output
    		Configure::write('debug', 0); 
    
    		// Avoids render() call
    		$this->autoRender = false;
    
    		// XML-RPC callbacks settings
    		// Use this parameter to map XML-RPC methods to your protected or private controller methods
    		$callbacks = array();
    		$callbacks['post.view']   = array(&$this, '_postView');
    		$callbacks['post.add']    = array(&$this, '_postAdd');
    		$callbacks['post.edit']   = array(&$this, '_postEdit');
    		$callbacks['post.delete'] = array(&$this, '_postDelete');
    
    		// Handle XML-RPC request
    		$this->server = new IXR_Server($callbacks);
    	}
    
    	function _postView($id = null) {
    		if (!$id) {
    			return new IXR_Error(2, 'Invalid Post');
    		}
    		return $this->Post->read(null, $id);
    	}
    
    	function _postAdd($data = array()) {
    		if (!empty($data)) {
    			$this->Post->create();
    			if ($this->Post->save($data)) {
    				return (int)$this->Post->id;
    			} else {
    				return new IXR_Error(1, 'Post not saved');
    			}
    		}
    		return false;
    	}
    	
    	function _postEdit($data = array()) {
    		if (empty($data)) {
    			return new IXR_Error(2, 'Invalid Post');
    		} elseif (!$this->Post->save($data)) {
    			return new IXR_Error(1, 'Post not saved');
    		}
    		return true;
    	}	
    	
    	function _postDelete($id = null) {
    		if (!$id) {
    			return new IXR_Error(2, 'Invalid Post');
    		} elseif (!$this->Post->del($id)) {
    			return new IXR_Error(3, 'Post not deleted');
    		}		
    		return true;
    	}	
    	
    }
    
    ?>

And this is a simple client that uses all the methods exposed by the
controller:

::

    <?php
    
    // Include the XML-RPC class
    require_once(dirname(__FILE__) . '/xmlrpc.php');
    
    // Set here your XML-RPF control url
    $url = 'http://cake.local/xml_rpc';
    
    // Create the client object
    $client = new IXR_Client($url);
    
    // Set the debug property to true during development to see both request and response data
    // $client->debug = true;
    
    echo('<pre>');
    
    // ####################### Let's create a new Post ####################### //
    
    // Set Post data
    $post = array ('Post' => 
      array (
        'title' => 'My First XML-RPC Post',
        'body' => 'This is the post body.',
      ),
    );
    
    // Call post.add
    if (!$client->query('post.add', $post)) {
        die('Something went wrong - ' . $client->getErrorCode() . ' : ' . $client->getErrorMessage());
    }
    
    // Get new Post ID
    $post_id = (int)$client->getResponse();
    
    echo("New Post ID: {$post_id}\r\n");
    
    // ####################### Let's display the new Post ####################### //
    
    // Call post.view
    if (!$client->query('post.view', $post_id)) {
        die('Something went wrong - ' . $client->getErrorCode() . ' : ' . $client->getErrorMessage());
    }
    
    // Get the Post data
    $data = $client->getResponse();
    
    // Display the Post data
    print('Post data: ');
    print_r($data);
    
    // ####################### Let's edit the Post ####################### //
    
    // Change Post data
    $data['Post']['title']    = 'My First Edited XML-RPC Post';
    $data['Post']['modified'] =  date('Y-m-d H:i:s');
    
    // Call post.edit
    if (!$client->query('post.edit', $data)) {
        die('Something went wrong - ' . $client->getErrorCode() . ' : ' . $client->getErrorMessage());
    }
    
    echo("Post Modified\r\n");
    
    // ####################### Let's delete the Post ####################### //
    
    // Call post.delete
    if (!$client->query('post.delete', $data['Post']['id'])) {
        die('Something went wrong - ' . $client->getErrorCode() . ' : ' . $client->getErrorMessage());
    }
    
    echo("Post Deleted\r\n");
    
    echo('</pre>');
    
    ?>

As you can seen, thereâ€™s a bit of redundance of code between the
XmlRpcController and the PostsController of the Blog sample
application.

Creating an XML-RPC server with CakePHP is straight forward. You can
easily build an API for external developers using a common protocol
and the power of CakePHP.

.. _http://scripts.incutio.com/xmlrpc/: http://scripts.incutio.com/xmlrpc/
.. _http://www.creativepark.it/downloads/xmlrpc.zip: http://www.creativepark.it/downloads/xmlrpc.zip

.. author:: filippo.toso
.. categories:: articles, snippets
.. tags:: controller,xmlrpc,Snippets

