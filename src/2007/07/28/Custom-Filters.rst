Custom Filters
==============

Learn to implement an easily extensible filtering system.
Filters are classes executed before the logic within a controllers
action and after the view has rendered. Filters are useful for the
following: access validation, hit counter, output compression, etc.
Since they have access to the parameters of a request and to the
output before it is sent to the client, they are powerful tools.


Example use of a filter
-----------------------
Here is an example of a filter class that adds the server's time to
the end of a page:

::

    
    // file app/controllers/filters/timestamp.php
    class TimestampFilter
    {
    	function doBeforeFilter(&$controller) {
    	}
    	function doAfterFilter(&$controller) {
    		$controller->outputContent .= "\n" . '<!-- Server time: ' . date('Y-m-d H:i:s') . ' -->';
    	}
    }

Note in the example the two functions of the filter. The first,
doBeforeFilter, is called during the call to the beforeFilter function
inside a controller. The passed parameter is the controller from which
the filter was called.

The second function, doAfterFilter, is called after the content is
rendered. Note the use of the new outputContent attribute of the
controller: it contains the content to be output. In our example, we
append to it the server's time using common HTML comment tags.


Implementing filters
--------------------
To add a filter to your controllers, simply add the following to the
controller's attributes:

::

    
    var $filterChain = array('GlobalFilter', 'FilterForAction' => 'edit', 'FilterForManyActions' => array('add', 'edit', 'list'));
    

Above, we reference 3 filters: GlobalFilter, FilterForAction and
FilterForManyActions. In the same way components are listed, each name
is a reference to its appropriate class: GlobalFilterFilter,
FilterForActionFilter and FilterForManyActionsFilter, respectively.

The filters are looked for in the CONTROLLERS/filters/ folder with the
name lowercased. So, for instance, TimestampFilter should be located
in the file CONTROLLERS/filters/timestamp.php.

The second and third filters in the example are associated to specific
actions. The second is associated to the action 'edit'; the third to
the actions 'add', 'edit' and 'list'. If the add action is called, the
GlobalFilter and FilterForManyActions filters are called. However, a
call to the edit action will call all 3 filters.

The filters are called in the order they are listed. The
doBeforeFilter function is called from first filter to last and the
doAfterFilter function is called from last filter to first!

Before these filters are called, you must first add the functionality
to your AppController. Copy the following content to any vendor folder
and name the file FilteredController.php

Controller Class:
`````````````````

::

    <?php 
    /**
     * This class adds filter functionality to the inheriting controller.
     * 
     * 
     */
    class FilteredController extends Controller
    {	
    /**
     * List of filters to call before a controller action is
     * called (beforeFilter()) and after the content is rendered.
     * 
     * -Filter class name extension is not necessary.
     *
     * @var array $filterChain List of filters to use.
     */
    	var $filterChain = array();
    /**
     * Output content from the view
     * 
     * @var string $outputContent
     */
    	var $outputContent = null;
    /**
     * Function called before a controller's action executed.
     * 
     * @access protected
     * @return boolean
     */
    	function beforeFilter()
    	{
    		$this->filters = array();
    		
    		foreach ($this->filterChain as $key => $filterClass) {
    			$actions = null;
    			if (is_string($key)) {
    				$actions = $filterClass;
    				if (!is_array($actions)) {
    					$actions = array($actions);
    				}
    				$filterClass = $key;
    			}
    			if (!$actions || in_array($this->action, $actions)) {
    				$filter =& ClassRegistry::getObject($filterClass);
    				if (!$filter) {
    					loadFilter($filterClass);
    					$filter =& ClassRegistry::getObject($filterClass);
    				}
    				if ($filter) {
    					if (method_exists($filter, 'init')) {
    						$filter->init();
    					}
    					if ($filter->doBeforeFilter($this) === false) {
    						break;
    					}
    					$this->filters[] = $filter;
    				}
    			}
    		}
    		
    		return parent::beforeFilter();
    	}
    /**
     * Renders.
     *
     * @param unknown_type $action
     * @param unknown_type $layout
     * @param unknown_type $file
     * @return unknown
     */
    	function render($action = null, $layout = null, $file = null) {
    
    		ob_start();
    		parent::render($action, $layout, $file);
    		$this->outputContent = ob_get_clean();
    
    		for ($i = count($this->filters) - 1; $i >= 0; $i--) {
    			$this->filters[$i]->doAfterFilter($this);
    			
    			if (method_exists($this->filters[$i], 'destroy')) {
    				$this->filters[$i]->destroy();
    			}
    		}
    		
    		print $this->outputContent;
    		
    		return $this->output;
    	}
    }
    /**
     * Function loads the indicated filter and stores it into the ClassRegistry.
     * 
     * This function can be called with more than one parameter. Each parameter
     * is considered a class name.
     * 
     * @param string $name The name of the filter to load.
     * @return boolean False if any of the filters were not loaded
     */
    	function loadFilter($name) {
    		
    		$allLoaded = false;
    		
    		$args = func_get_args();		
    		if (is_array($args[0])) {
    			$args = $args[0];
    		}
    		
    		if (empty($args)) {
    			return;
    		}
    		
    		foreach ($args as $filterClass) {
    			
    			// TODO: The path is retrieved from the Configure singleton
    			$filterClassPath = CONTROLLERS . 'filters' . DS . Inflector::underscore($filterClass) . '.php';
    			
    			if (is_file($filterClassPath))	{
    				require_once $filterClassPath;
    				
    				$fullFilterClass = $filterClass . 'Filter';
    				ClassRegistry::addObject($filterClass, new $fullFilterClass());
    				
    				$allLoaded &= true;
    			} else {
    				$allLoaded &= false;
    			}
    		}
    		
    		return $allLoaded;
    	}
    ?>

In your AppController make the following modifications:

Controller Class:
`````````````````

::

    <?php 
    loadVendor('FilteredController');
    class AppController extends FilteredController {
       ...
    }
    ?>

Hope this helps solve those last minute mods :). I would appreciate
any comments.

What kind of filters would you like to see?


.. author:: Dimitry
.. categories:: articles, tutorials
.. tags:: render,beforeFilter,filters,Tutorials

