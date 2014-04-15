Simple, Model-Based Access Control
==================================

by linnk on March 24, 2009

In this article an alternative way of doing access control with
CakePHP is presented. The solution separates all of your
authentication needs from your controller and instead uses custom-
defined functions in your models to check if the currently logged-in
user should be allowed access to a page. It builds upon the excellent
AuthComponent and an idea presented alkemann and gwoo as a group
permissions system for the new Bakery.
The code for this system is very simple and is implemented in two
parts: a special permissions file in your config-directory, and a
smart isAuthorized function. The permissions file contains an array
which defines the permissions for access restricted areas of the site.
This file is then loaded into your application and used by
isAuthorized to determine whether or not the user should be allowed
access. The general format of of the permissions array is as follows:

::

    
    $config['Config']['permissions'] = array(
        'Model1' => array(
            'action0',
            'action1' => array('method' => 'funcInModel1'),
            'action2' => array('method' => 'funcInModel2')
        ),
        'Model2' => array(
            'action3',
            'action4',
        )
        'Model3' => array(
            ...
        )
    );

The format is simple and can be easily extended. To use the array for
access control, modify your application controller in a fashion
similar to the one below. Remember to add the AuthComponent and set
$this->Auth->authorize = 'controller'.


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
    
        var $components = array('Auth');
    
        function beforeFilter() {
            if (!isset($this->permissions)) {
                $this->permissions = Config::read('permissions');
            }
            $this->Auth->authorize = 'controller';
        }
    
    	function isAuthorized() {
    		$allow = false;
    		if (isset($this->permissions[$this->name][$this->action])) {
    			$perms = $this->permissions[$this->name][$this->action];
    			if (is_array($perms)) {
    				$user = $this->Auth->user('id');
    				$model = $this->modelClass;
    				$action = $perms['method'];
    				$id = $this->params['pass'][0];
    				
    				if ($this->{$model}->{$action}($id, $user)) {
    					$allow = true;
    				}
    			}
    		} else {
    			if (in_array($this->action, $this->permissions[$this->name])) {
    				$allow = true;
    			}
    		}
    		return $allow;
    	}
    
    }
    ?>



Sources
-------


#. `http://thechaw.com/bakery/wiki/spec/users/Group_permissions`_



.. _http://thechaw.com/bakery/wiki/spec/users/Group_permissions: http://thechaw.com/bakery/wiki/spec/users/Group_permissions

.. author:: linnk
.. categories:: articles, tutorials
.. tags::
model,Auth,configuration,authentication,isauthorized,Tutorials

