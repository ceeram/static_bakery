

Extended beforeFilter() snippet
===============================

by %s on April 07, 2007

This is a simple addition to app_controller.php to allow a more
customized beforeFilter() callback.

Updated: 3/27/2007 - added isset call before calling callback with
args

I set out to get a little more functionality out of the controller
callback beforeFilter(). I had 2 requirements for this extended
functionality:

#. Specify which actions to apply the callback to.
#. Ability to specify multiple methods beforeFilter would call.

Let me give a very simple example of how to use this snippet:
in your controller define $beforeFilter which is an array of methods
to call and the parameters.

Controller Class:
`````````````````

::

    <?php 
    class TestingsController extends AppController
    {
        var $name = 'Testings';
    	var $beforeFilter = array('requireLogin'=>array('only'=>array('add','edit','delete')));
    	
    	
    	function index(){}
    	
    	function view($id){}
    	
    	function add(){}
    	
    	function edit($id=null){}
    	
    	function delete($id){}
    
    
    }
    ?>

The $beforeFilter instance variable holds an array of methods to be
called before your actions are called. In the example above, it says
to call the 'requireLogin' method only when the 'add','edit','delete'
actions are being called.

This next example shows you how to make certain actions excluded from
the callback:

Controller Class:
`````````````````

::

    <?php 
    class TestingsController extends AppController
    {
        var $name = 'Testings';
    	var $beforeFilter = array('requireLogin'=>array('except'=>array('index')));
    	
    	
    	function index(){}
    	
    	function view($id){}
    	
    	function add(){}
    	
    	function edit($id=null){}
    	
    	function delete($id){}
    
    
    }
    ?>

You can also send in a parameter called 'args' which will call your
method with the args

Controller Class:
`````````````````

::

    <?php 
    var $beforeFilter = array('requireLogin'=>array('except'=>array('index'),
    													'args'=>array('arg1','arg2')));
    ?>


In order to make this all happen you need to place this method in your
app_controller.php class.
It will get called before every action. If you have not defined
$beforeFilter then it will skip any processing.



Controller Class:
`````````````````

::

    <?php 
    function beforeFilter(){
    		if(empty($this->beforeFilter)) return true;
    		$failures = false;
    		foreach($this->beforeFilter as $func_name=>$func){
    			$call_func = true;
    			if(!empty($func['only'])){
    				if(!in_array($this->action,$func['only']))
    					$call_func = false;
    			}
    			if(!empty($func['except'])){
    				if(in_array($this->action,$func['except']))
    					$call_func = false;
    			}
    			if($call_func){
    				$args = (isset($func['args'])) ? implode(',',$func['args']) : null;
    				if(!$this->{$func_name}($args)){
    					$failures = true;
    					break;
    				}
    			}	
    		}
    		return !$failures;
    	}
    ?>


.. meta::
    :title: Extended beforeFilter() snippet
    :description: CakePHP Article related to extend beforeFilter(,beforeFilter,Snippets
    :keywords: extend beforeFilter(,beforeFilter,Snippets
    :copyright: Copyright 2007 
    :category: snippets

