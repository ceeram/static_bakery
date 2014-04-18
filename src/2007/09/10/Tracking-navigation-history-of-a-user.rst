Tracking navigation history of a user
=====================================

Many times it can be very useful to track the navigation history of a
user. Especially if you want to redirect the user to a page where he
came from after some action (e.g. login). With this HistoryComponent,
it's extremely easy to handle such actions.
Store the component code in: controllers/components/history.php .

Component Class:
````````````````

::

    <?php 
    /**
     * Maximum size of the array containing the user navigation history
     */
    define('STUDIOSIPAK_MAX_HISTORY', 10);
    /*
     * HistoryComponent: User navigation history
     * @author: Studio Sipak
     * @website: http://webdesign.janenanneriet.nl
     * @license: MIT
     * @version: 0.1
     * */
    class HistoryComponent extends Object
    {
    	var $data = array();
    	var $started = false;
    	var $controller = true;
    
    	function startup(&$controller) {
    		// This test will prevent it from running twice.
    		if(!$this->started) {
    			$this->started = true;
    			$this->controller = $controller;
    			$this->data = $controller->Session->read('User.history');
    			if($controller->params['bare'] == 0) {
    				$this->_addUrl($controller->params);
    			}
    			$controller->Session->write('User.history', $this->data);
    		}
    	}
    
    	function goBack($step = 1) {
    		$pos = count($this->data) - $step - 1;
    		$this->controller->redirect($this->data[$pos]);
    		exit();
    	}
    
    	function show() {
    		return $this->data;
    	}
    
    	function _addUrl($params) {
    		count($params['url']) ? $url = '/'.$params['url']['url'] : $url = '/';
    		if(count($this->data) == STUDIOSIPAK_MAX_HISTORY) {
    			$this->_deleteUrl();
    		}
    		$this->data[] = $url;
    	}
    
    	function _deleteUrl($position = 0) {
    		if($position == 0) {
    			array_shift($this->data);
    		}
    		else {
    			array_splice($this->data, $position, 1);
    		}
    	}
    
    }
    ?>



Usage
`````
The component will automatically be loaded by the Controller. In your
controller you can use the functions goBack($position) and show() .
The function goBack() will send the user x many pages back in his
navigation history. If you don't specify a number, it will be the last
page. The function show() gives an overview of the navigation history.

::

    
    class YourController extends AppController
    {
    	function doSomething() {
    		...
    		// Redirect user to previous page
    		$this->History->goBack();
    	}
    
    }



Expansion
`````````
This version is just my first one, so it is might not be complete yet.
It is not difficult to expand the component for more advanced usage.
Comments regarding more functionality and bugs are welcome.


.. author:: janb
.. categories:: articles, components
.. tags:: redirect,component,navigation,history,Components

