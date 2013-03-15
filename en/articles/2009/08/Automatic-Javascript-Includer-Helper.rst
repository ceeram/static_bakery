Automatic Javascript Includer Helper
====================================

by %s on August 14, 2009

A quick and easy auto-magic JavaScript includer.

Often times you will have Javascript code that is specific to a
particular controller, or to a specific action of a specific
controller. In order to minimise the amount of data that is sent to a
client, it would be really handy to only have code that is required
sent across as each request is mode.

The following helper checks for the existence of files named the same
as your CakePHP controllers and actions. If these files exist, then
they are automatically included as part of the pages HEAD, and sent to
the client. If the controller / action javascript file doesn't exist,
then nothing is added to the page scripts.

Alright, so how does it work? Its configurable, and those that want to
change the default location can do so through the options. However the
default is as follows:

Consider we have a UsersController and a PostsController, each with
actions: index, add, edit. On accessing the UsersController index
action, the helper will check for the existence of
WWW_ROOT/js/autoload/users/index.js and if found, include that file.
It will also check for the existence of WWW_ROOT/js/autoload/users.js
for javascript that is to be included for all actions on the
UsersController.

An example layout of directory structure and files:


+ webroot

    + js

        + autoload

            + users

                + index.js
                + add.js
                + edit.js

            + posts

                + index.js
                + add.js
                + edit.js

            + users.js
            + posts.js




Usage couldnt be easier. In your AppController, include the helper.
Yes. Thats all you need to do.


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
    	public $helpers = array('AutoJavascript');
    }
    ?>


Here is the helper code:


Helper Class:
`````````````

::

    <?php 
    /** File: auto_javascript.php **/
    /**
     * CakeTime JavaScript Helper
     *
     * Facilitates JavaScript Automatic loading and inclusion for page specific JS
     *
     * @copyright   Copyright 2009, Graham Weldon (http://grahamweldon.com)
     * @link        http://grahamweldon.com/projects/caketime CakeTime Project
     * @package     caketime
     * @subpackage  caketime.views.helpers
     * @author      Graham Weldon (http://grahamweldon.com)
     * @license     http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    class AutoJavascriptHelper extends AppHelper {
    
    /**
     * Options
     *
     * path => Path from which the controller/action file path will be built
     *         from. This is relative to the 'WWW_ROOT/js' directory
     *
     * @var array
     * @access private
     */
    	private $__options = array('path' => 'autoload');
    
    /**
     * View helpers required by this helper
     *
     * @var array
     * @access public
     */
    	public $helpers = array('Javascript');
    
    /**
     * Object constructor
     *
     * Allows passing in options to change class behavior
     *
     * @param string $options Key value array of options
     * @access public
     */
    	public function __construct($options = array()) {
    		$this->__options = array_merge($this->__options, $options);
    	}
    
    /**
     * Before Render callback
     *
     * @return void
     * @access public
     */
    	public function beforeRender() {
    		extract($this->__options);
    		if (!empty($path)) {
    			$path .= DS;
    		}
    
    		$files = array(
    			$this->params['controller'] . '.js',
    			$this->params['controller'] . DS . $this->params['action'] . '.js');
    
    		foreach ($files as $file) {
    			$file = $path . $file;
    			$includeFile = WWW_ROOT . 'js' . DS . $file;
    			if (file_exists($includeFile)) {
    				$file = str_replace('\\', '/', $file);
    				$this->Javascript->link($file, false);
    			}
    		}
    	}
    }
    
    ?>


A small disclaimer is that this helper is very basic. There are
probably some performance considerations to make when checking the
disk for file existence on every single request. However, the solution
is elegant and unobtrusive. Questions / comments and suggestions are
encouraged.


.. meta::
    :title: Automatic Javascript Includer Helper
    :description: CakePHP Article related to javascript,helper,auto,automatic,automagic,predominant,Helpers
    :keywords: javascript,helper,auto,automatic,automagic,predominant,Helpers
    :copyright: Copyright 2009 
    :category: helpers

