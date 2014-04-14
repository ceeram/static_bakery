AuthExt a small Auth extension to set permission on user belonging to
several groups/roles.
=====================

by francky06l on July 29, 2008

On google group I have seen many question about Auth-Acl usage for
users having several roles or belonging to different groups. There are
plenty of good articles in blogs, bakery etc .. This small extension
should make the setting of permission easier, and really keep the
native Auth goals.

I have been using ACL + Auth for many projects in order to control the
controllers/actions access for many projects. Very often my users have
roles and permissions are set on roles.
This end up having Role ARO, the user ARO get the Role Aro as parent.
Using the native Auth all this is very easy to do, even having role
inheriting from other roles (ie: super moderator have moderator ARO as
parent..).
However, having a User with nultiple distinct roles is not so straight
forward, since an ARO can only have one parent. Using a very small
extension to the Auth it becomes simple.

That's the goal of this component : AuthExt. Note that for people
using groups instead of roles, just replace the word "Role" by "Group"
anywhere you find it.
A mode detailled tutorial, as well as a complete sample project (for
download)can be found here
`http://www.cakephpforum.net/index.php?showtopic=745=0&#e;ntry3595`_.

The main point to understand resides in the Acl->check method, and the
node method of the db_acl.

::

    
    function check($aro, $aco, $aco_action = null) 
    ....
    ....
        } else {
            switch($perm['_' . $action]) {
                case -1:
                    return false;
                case 0:
                    continue;
                break;
                case 1:
                    return true;
                break;
            }
        }
    ....                    

Here is the point, giving inherit (0) permission makes the loop to
examine other permissions. Having a deny (-1) will stop it, and of
course allow (1) will succeed. That gives us a direction, for checking
different roles, we will inherit rather than deny when setting
permission on roles.

Another important point in the node function: the way the Aro path is
extracted. When using Auth native, the Aro path for a User having a
role, will be the User Aro and the Role Aro.
Using the following syntax to retrieve the Aro path, will return the
paths for all roles id = 1,3 ,5.

::

    
    $this->Acl->check(array('Role' => array('id' => array(1, 3, 5))));

Knowing the above, it becomes easy to make a small extension to the
Auth. First, the summary of the goals :

+ A user has a primary role, so in user model there is a field role_id
+ A user can have other roles (not mandatory), so User HABTM Role
+ We use the Auth "actions" mode to check the access
+ We let the native Auth makes as much work as possible

A quick look to the User model definition:

::

    
    class User extends AppModel {
    
    	var $name = 'User';
    
    	//The Associations below have been created with all possible keys, those that are not needed can be removed
    	var $hasAndBelongsToMany = array(
    			'Role' => array('className' => 'Role',
    								'foreignKey'            => 'user_id',
    								'associationForeignKey' => 'role_id',
                                    'joinTable'             => 'user_roles',
                                    'with'                  => 'UserRole',
                                    'conditions' => '',
    								'fields' => '',
    								'order' => '',
    								'counterCache' => ''),
    	);
    
    	var $belongsTo = array(
    			'FirstRole' => array('className' => 'Role',
    								'foreignKey'            => 'role_id',
                                    'conditions' => '',
    								'fields' => '',
    								'order' => '',
    								'counterCache' => ''),
    	);
        
        var $validate  = array('role_id' => array('rule' => VALID_NOT_EMPTY, 'message' => 'Mandatory'),
                               'username' => array(array('rule' => VALID_NOT_EMPTY, 'message' => 'Mandatory', 'last' => true),
                                                   array('rule' => 'isUnique', 'message' => 'already exists'))
                              );
                              
        var $actsAs = array('Acl' => 'requester');
        
        function parentNode()
        {    
            if($this->id)
            {
                $data = $this->read();
    
                if($data['User']['role_id'])
                    return array('model' => 'Role', 'foreign_key' => $data['User']['role_id']);
            }
            return null;        
        }
    }

Note that we use the Role class with an Alias "FirstRole" in belongsTo
for the Primary role, and we also have a join model "UserRole" in the
HABTM. This is not mandatory, but it's gets very useful when deleting
roles.
We use the Acl behavior as requester, and the parentNode method will
give the parent Aro wich is the Aro of the First role.

The Role model, very simple, nothing special:

::

    
    class Role extends AppModel {
    
    	var $name   = 'Role';    
        var $hasMany = array('User' => array('className' => 'User',
    								'foreignKey' => 'role_id',
    								'conditions' => '',
    								'fields' => '',
    								'order' => '',
    								'counterCache' => '')
                            );
                            
        var $validate = array('name' => array('rule' => 'isUnique', 'message' => 'already exist'));
        
        var $actsAs = array('Acl' => 'requester');
        
        function parentNode()
        {
            return null;
        }
    }

We also use here the Acl behavior as requester. We could have
implemented Role inheritance by adding a field "parent_id" to the Role
model and make the parentNode returning the parent Role.
That was not the goal of this sample project.

Now how to use this with Auth ? The logic is simple :

+ when login, let Auth doing the login, if successful we need to check
  if we have other roles that the First role
+ if we have other roles, we store their id's into the Auth Session
  key
+ when Auth check for authorization on a controller/action, it will
  check using the First Role
+ if the above fails, we can check authorization with the other roles
  (if any)



The above logic is implemented in the AuthExt component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    
    /*
    * Extend the Auth component
    *
    */
    
    App::import('component', 'Auth');
    
    class AuthExtComponent extends AuthComponent
    {
        var $parentModel = 'Role';
        var $fieldKey    = 'role_id';
        
        // override, to store the associated role
        
        function login($data = null)
        {
            if(!parent::login($data))
                return $this->_loggedIn;
    
            // Get the "User" model from Auth
            
            $model = $this->getModel();
            
            // search for an HABTM, we hope it has a "with" model
    
            if(isset($model->hasAndBelongsToMany[$this->parentModel]['with']))
            {   
                $with = $model->hasAndBelongsToMany[$this->parentModel]['with'];
                if(!isset($this->{$with}))
                    $this->{$with} =& ClassRegistry::init($with);                
    
                // fetch the associated model
                $roles = $this->{$with}->find('all', array('conditions' => 'user_id = '.$this->user('id')));
                if(!empty($roles))
                {
                    $primaryRole = $this->user($this->fieldKey);            
                    // retrieve associated role that are not the primary one
                    $roles = set::extract('/'.$with.'['.$this->fieldKey.'!='.$primaryRole.']/'.$this->fieldKey, $roles);
    
                    // add the suplemental roles id under the Auth session key
                    if(!empty($roles))
                    {
                        $completeAuth = $this->user();
                        $completeAuth[$this->userModel][$this->parentModel] = $roles;
                        $this->Session->write($this->sessionKey, $completeAuth[$this->userModel]);
                    }
                }
            }
            
            return $this->_loggedIn;        
        }
        
        // override this to find the right aro/aco
        
        function isAuthorized($type = null, $object = null, $user = null)
        {
            $valid = parent::isAuthorized($type, $object, $user);
            
            if(!$valid && $type == 'actions' && $this->user($this->parentModel))
            {
                // get the roles from the Session, and set the proper Aro path
                $otherRoles = $this->user($this->parentModel);
                // check using our Roles Aro paths
                $valid = $this->Acl->check(array($this->parentModel => array('id' => $otherRoles)), $this->action());            
    		} 
            return $valid;
        }    
    }

Notes:

+ We define a parentModel and a fieldKey, in such case I use Role and
  role_id, it can be Group/group_id or whatever
+ We overwrite the login function with the logic

    + call the Auth login
    + if success, find more Role for the current User
    + usage of the "with" model declared in the HABTM for retrieving
      supplemental roles
    + store the other roles in the Auth session key (note that we remove
      the First Role id, if declared in other role as well)

+ We overwrite the isAuthrized native function :

    + call the native isAuthorized, that will perform the check using our
      first role
    + in case of failure, if we are in "actions" mode and we have other
      roles, we perform the check for the action on the other roles


[B]Implementation: Place the AuthExt component in the component
directory.
Just replace Auth by AuthExt in AppController. Here is the sample
app_controller.php :

::

    
    class AppController extends Controller {
        var $components      = array('Acl', 'AuthExt', 'RequestHandler');
        var $helpers         = array('Javascript', 'Html', 'Form');
        
        function beforeFilter()
        {
            if(isset($this->AuthExt))
            {
                if($this->name == 'Pages')
                    $this->AuthExt->allow('*');
                else
                {   
                    $this->AuthExt->loginAction   = '/users/login';
                    $this->AuthExt->autoRedirect  = false;
                    $this->AuthExt->authorize     = 'actions';
                }
            }    
        }
    }


That's quite simple and maybe that can help some of you dealing with
multiple roles/groups.
The complete project for download, has got a single user "admin",
password "admin" and you can play with it. To run the sample, unzip
the file, run the testacl.sql for the sample database creation (adjust
the /config/database.php accordingly).
In the sample, I have done a GUI to set the permissions on roles, also
a "cleanupAcl" method in RolesControllers, that checks if the
Aco/permission for all controllers/actions (including plugin ones) are
present. It also clean the one that are not needed anymore (ie:
action/controller removed).

I hope that can help, remarks and comments are welcome.



.. _e;ntry3595: http://www.cakephpforum.net/index.php?showtopic=745&st=0&#e;ntry3595
.. meta::
    :title: AuthExt a small Auth extension to set permission on user belonging to several groups/roles.
    :description: CakePHP Article related to ,Components
    :keywords: ,Components
    :copyright: Copyright 2008 francky06l
    :category: components

