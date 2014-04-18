Minimalistic group-based access control in 5 mins.
==================================================

Having a hard time with the ACL? This is probably one of the most
simple group based access solutions out there. No ACL tables, no tree
structure. It will allow you to setup access on a group level, so if
you're looking for user based differentiation this is not for you.


Goal
~~~~
In 5 mins you'll be able to define access levels by an array in each
controller, containing action/group pairs:

::

    
    var $permissions = array(
            'view' => '*',
            'add' => array('user', 'moderator')
            'delete' => array('moderator')
        );



The code
~~~~~~~~
Allright, we only got 5 mins. Let's get to it.

Create or modify your users/groups database tables to look like this:

::

    
    users
        id - primary
        username - unique
        password
        group_id
    groups
        id - primary
        name - unique

Then create some users and some groups. Make sure to create a group
named "admin"

Create or modify your user/group models to include this:

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	var $name = 'User';
        var $displayField = 'username';
    	var $belongsTo = array(
    		'Group' => array(
    			'className' => 'Group',
    			'foreignKey' => 'group_id'
    		)
    	);
    }
    ?>



Model Class:
````````````

::

    <?php 
    class Group extends AppModel {
    	var $name = 'Group';
    	var $displayField = 'name';
    
    	var $hasMany = array(
    		'User' => array(
    			'className' => 'User',
    			'foreignKey' => 'group_id',
    			'dependent' => false
    		)
    	);
    
    }
    ?>

Create or modify your app_controller.php to include this:


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
    
        var $components = array('Auth', 'RequestHandler'); 
        var $permissions = array();
        
        function beforeFilter() {
            $this->Auth->fields  = array(
                'username'=>'username', //The field the user logs in with (eg. username)
                'password' =>'password' //The password field
            );
            $this->Auth->authorize = 'controller';
            $this->Auth->autoRedirect = false;
            $this->Auth->loginAction = array('controller' => 'users', 'action' => 'login');
            $this->Auth->logoutRedirect = array('controller' => 'users', 'action' => 'login');
            $this->Auth->loginRedirect = array('controller' => 'users', 'action' => 'welcome');
    
        }
        
        function isAuthorized(){
            if($this->Auth->user('group') == 'admin') return true; //Remove this line if you don't want admins to have access to everything by default
            if(!empty($this->permissions[$this->action])){
                if($this->permissions[$this->action] == '*') return true;
                if(in_array($this->Auth->user('group'), $this->permissions[$this->action])) return true;
            }
            return false;
            
        }
    
    }
    ?>

Create or modify your users_controller.php to include this:


Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController {
    
    	var $name = 'Users';
    	var $helpers = array('Html', 'Form');
    	var $permissions = array(
            'logout' => '*',
            'welcome' => '*'
        );
    	
        function welcome(){
        }
    
        function login(){
            if($this->Auth->user()){
                $this->Session->write('Auth.User.group', $this->User->Group->field('name',array('id' => $this->Auth->user('group_id'))));
                $this->redirect($this->Auth->redirect());
            }
        }
        
        function logout(){
            $this->redirect($this->Auth->logout());
        }
    
        // Add whatever user logic methods you'd like here as well (eg. add/edit/delete users)
    ?>

Alright, that's it. You define access levels by adding the
$permissions array to any controller like we did in the users
controller.

Explanation
~~~~~~~~~~~
When the user logs in we add the group name to his Auth session. When
a controller is called, the Auth component calls the isAuthorized
function, if it returns true the user is granted acces, if false the
user is refused access. If the user is an admin it returns true no
matter what. The isAuthorized looks at the $permissions array defined
in each controller and looks up the action the user is requesting. If
the users group is in the array defined for the action (or the action
is set to '*' meaning everyone) the user is granted access. If none of
this happens, the function returns false, and the user is denied
access.
Notice: The access levels here assume that you are logged in, even if
you set it to '*'. You can allow non-logged in users access to content
by using the Auth->allow method.
Notice: We didn't define permissions for the login action, because
Auth allows access to it by default.

Example:
~~~~~~~~
Let's say i'd only allow the group 'moderator' to delete users. I
would add this to my users_controller.php:

::

    
    var $permissions = array(
            'logout' => '*',
            'welcome' => '*'
            'delete' => array('moderator')
        );

But i'd also like not logged in users to be able to sign up. I would
then add this to my users_controller.php:

::

    
    function beforeFilter(){
        $this->Auth->allow('signup');
        parent::beforeFilter();
    }

Hope this helps. It's an exceedingly simple solution to group-based
security, and ofcourse it has some downsides. An example would be
users creating an article, and only having permission to edit that
article, which is not possible. At least not without a bit of
tinkering. But i'll leave that up to you.
Cheers, Rasmus.




.. author:: rasmuspalm
.. categories:: articles, tutorials
.. tags:: acl,user,Auth,security,users,login,groups,logout,group,autho
rize,authorization,Tutorials

