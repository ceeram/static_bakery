Custom error handing for plugins
================================

by filippo.toso on January 13, 2009

If you like to develop reusable plugins, maybe you want to create some
custom error messages using the cakeError() method. Here is a simple
snippet that lets you create your own {plugin}/app_error.php class.
First of all, let's override the cakeError() method in your
{plugin}AppController class:

File: /app/plugins/{plugin}/{plugin}_app_controller.php

::

    <?php
    
    class MyPluginController extends AppController {
    
          function cakeError($method, $messages = array()) {
                    if (!class_exists('ErrorHandler')) {
                            App::import('Core', 'Error');
    
                            $path = APP . 'plugins' . DS . Inflector::underscore($this->plugin) . DS;
    
                            if (file_exists($path . 'error.php')) {
                                    include_once ($path . 'error.php');
                            } elseif (file_exists($path . 'app_error.php')) {
                                    include_once ($path . 'app_error.php');
                            }
                    }
                    return parent::cakeError($method, $messages);
            } 
    
            // Add your own stuff here :)
    
    }
    
    ?>

Then we need to override the protected _outputMessage() method in your
plugin's AppError class:

File: /app/plugins/{plugin}/app_error.php

::

    <?php
    
    class AppError extends ErrorHandler {
    	
    	// This method is the same as the official manual, just an example
    	function cannotWriteFile($params) {
    		$this->controller->set('file', $params['file']);
    		$this->_outputMessage('cannot_write_file');
    	}
    
    	function _outputMessage($template) {	
    		$this->controller->viewPath = '..' . DS . 'plugins' . DS . basename(dirname(__FILE__)) . DS . 'views' . DS . 'errors';
    		parent::_outputMessage($template);
    	}
    	
    }	
    
    ?>

That's all! You can create your own views in
/app/plugins/{plugin}/views/errors and they will be loaded correctly.


.. author:: filippo.toso
.. categories:: articles, snippets
.. tags:: plugin,error handing,Snippets

