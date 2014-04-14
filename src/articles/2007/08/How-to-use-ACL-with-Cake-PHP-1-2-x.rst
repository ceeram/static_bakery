How to use ACL with Cake PHP 1.2.x?
===================================

by ketan on August 07, 2007

This tutorial will brief you on how to use Acl in CakePHP 1.2.x
versions. I had tough time figuring this out. But with help of Gwoo,
AD7Six & others, and doing debugging and reading code, here comes the
tutorial.
This tutorial assumes you know basic concept of ACL and what it is
suppossed to be used for? If not then please read
`http://manual.cakephp.org/chapter/acl`_
You can setup the databases needed for ACL through console command

::

    cake acl initdb

Now we would setup some higher level aros and acos for initial setup.
You could do it through console. But I prefered to do it through
controller, nothing special, just did it that way!

Think of Aros (could be Users, service, etc) as the one who is
requesting access to Acos (could be controller, actions or services).
But in this example, we will limit Aros as the users and Acos as the
controllers. We will setup the following Aros (users):

#. Admin
#. |-->User::1
#. User
#. Guest

You could add more depending on your requirements, but we will stick
to basic requirements for now. Admin, User & Guest are higher level
group and the actual users will belong to one of these groups.
'User::1' is an alias for User with user id 1. We define that user
with user id 1 is a child of Admin and will inherit all admin
previledges. Doing this is not essential, but you will have to define
at least one user to be Admin, so why not do it here. Change the id to
the userid representing the admin user on your system.

We will setup the following Acos (controllers):

#. User
#. Post

This would add two acos 'User' and 'Post'. But now you think if Acos
is controller then why not have 'Posts' instead of 'Post'? Good
question. This is because usually a controller's action can be divided
into four types of action 'create', 'read', 'update' or 'delete' which
are performed on a single or group of records belonging to a model.
Hence, in this approach we going at record level Access Control. We
want to make sure whether the current Aro (a User) has access to do
'C', 'R', 'U' or 'D' action on the Aco ( a record for eg. A post). If
yes, then let him do the action otherwise don't. Now the code, that
shows you the manual way to create aros and acos as discussed above.


Controller Class:
`````````````````

::

    <?php 
    class InitAclController extends AppController
    {
      var $name = 'InitAcl';
      var $component = array('Acl');
      var $uses = array();
    
      function setupAcl()
      {
        $aro = new aro();
    
        $aro->create();
        $aro->save(array(
          'model'=>'User',
          'foreign_key'=>null,
          'parent_id'=>null,
          'alias'=>'Admin'));
    
        $aro->create();
        $aro->save(array(
          'model'=>'User',
          'foreign_key'=>null,
          'parent_id'=>null,
          'alias'=>'User'));
    
        $aro->create();
        $aro->save(array(
          'model'=>'User',
          'foreign_key'=>null,
          'parent_id'=>null,
          'alias'=>'Guest'));
    
        $parent = $aro->findByAlias('Admin');
        $parentId = $parent['Aro']['id'];    
    
        $aro->create();
        $aro->save(array(
          'model'=>'User',
          'foreign_key'=>1,
          'parent_id'=>$parentId,
          'alias'=>'User::1'));
    
        
        
        $aco = new Aco();
        $aco->create();
        $aco->save(array(
           'model'=>'User',
           'foreign_key'=>null,
           'parent_id'=>null,
           'alias'=>'User'));
           
        $aco->create();
        $aco->save(array(
           'model'=>'Post',
           'foreign_key'=>null,
           'parent_id'=>null,
           'alias'=>'Post'));
       }
       // Give admin full control over acos 'User' & 'Post'
       $this->Acl->allow('Admin', 'User', '*');
       $this->Acl->allow('Admin', 'Post', '*');
    
       // Give the user group only create & read access for 'Post' 
       $this->Acl->allow('User', 'Post', array('create', 'read'));
    
       // Give the Guests only create access for 'User'
       $this->Acl->allow('Guest', 'User', 'create');
    }
    ?>

Above you saw that using Acl, we granted the Admin full rights over
'User' and 'Post' Acos. ie. Admin can do CRUD for all user and post,
which in turn means that for any controller action which involves
creating, reading, updating or deleting a 'User' or 'Post' record,
Admin group is allowed to do it. So does any user that belongs to
group Admin.

'User' aro is allowed to do only create & read action for 'Post' acos,
which means that a 'User' group in general has access to a controller
action that can create and read 'post' records, which is what we want.
We want that any user that belongs to 'User' group can create new
posts and read posts. But we do not want all users (aros) to 'update'
or 'delete' any 'Post' (acos) they want. Which means, that belonging
to a 'User' group does not give you any previledges to 'U', 'D'
actions of 'Post' (acos). But you want to have 'U', 'D' action for the
user who created that Post!! I will get to giving user who created
post the full CRUD rights later on, but this explanation was just to
clear your concepts. Note that, above we did not do any 'allow'
statement for 'User' aco, so this means that by default 'User' group
and its children, don't have access to 'CRUD' on 'User' records
(acos). A user himself only has the CRUD right for his record and not
other users. That's why we did it that way :)

'Guest' aro is allowed to only 'create' action for 'User' acos. ie.
Guest can only register a new user account, and is denied all other
access to everything else.

Now that we have the basic setup done, we would want to get the aros
and acos populated as and when user is added to system. Below is shown
the code on how to create aros and acos manually and also how to setup
the permissions.


Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController
    {
      var $name = 'Users';
    
      var $components = array('Acl');
    
      function register()
      {
         if(!empty($this->data))
         {
            $this->User->data = $this->data;
    
            if ($this->User->validates())
            {
               if ($this->User->save())
               {
                   $aro = new Aro();
                   $parent = $aro->findByAlias('User');
                   $parentId = $parent['aro']['id'];
    
                   $aro->create();
                   $alias = $this->User->name.'::'.$this->User->id;
                   $aro->save(
                     'model'       => $this->User->name,
                     'foreign_key' => $this->User->id,
                     'parent_id'   => $parentId,
                     'alias'   => $alias
                           );
    
                   $aco = new Aco();
                   $parent = $aco->findByAlias('User');
                   $parentId = $parent['aco']['id'];
    
                   $aco->create();
                   $aco->save(
                     'model'       => $this->User->name,
                     'foreign_key' => $this->User->id,
                     'parent_id'   => $parentId,
                     'alias'       => $alias
                           );
    
                   $this->Acl->allow(
                        $alias, 
                        $alias, 
                        array('read','update'));
               }
            }
     
         }
      }
    }
    ?>

Above you saw, how to create aro and aco each time a new user is
registered on the system. Also you saw how to allow a user himself the
full CRUD previledges on his own record. Say User 'a' with user id '5'
just registered on the site. Above code, will create an aro with alias
'User::5' and an aco with alias 'User::5' and will create an entry in
aros_acos table that would let aro with alias 'User::5' CRUD rights
over aco with alias 'User::5'. Now no other user has access User 'a'
except User 'a' and anyone who belongs to 'Admin' aro group. To
verify, give following code a try


Controller Class:
`````````````````

::

    <?php 
    class TestController extends AppController
    {
      var $name = 'Test';
      var $components = array('Acl');
      var $uses = array('User');
      var $curLoggedInUserId = 3;
    
      function view()
      { 
        $aroAlias = 'User::'.$curLoggedInUserId;
        $acoAlias = 'User::5';
    
        if ($this->Acl->check($aroAlias, $acoAlias, 'read'))
        {
           echo 'Read access allowed for User Id'.$curLoggedInUserId;
        }
        else
        {
           echo 'Read access denied for User Id'.$curLoggedInUserId;
        }
      }
    }
    ?>

When you visit the above page (http://localhost/test/view), you will
get 'access denied'. Now change the $curLoggedInUserId = 5, and try
visiting the same page again, you will get 'allowed access'. This is
because the logged in user id now is the same as user 'a'. And we had
defined that user 'a' has full rights on user 'a' record. Note what
happens when you have $curLoggedInUserId = 1!! You still get 'allowed
access', now why did this happen? Just because User with userid 1
belongs to Admin group and he has full CRUD rights over any 'User'
aco. Above code is a very crude code and is meant just to demonstrate
the purpose of Acl check & is not meant to be used in production use.

Above was a manual & tedious way to create aros and acos. Now I will
now show you the magical way to create aros and acos without much
effort on your end. All you have to do is implement the Acl Behavior
which comes with cake 1.2 distribution. Below is the code that you
would have to add to 'Post' Model.


Model Class:
````````````

::

    <?php 
    class Post extends AppModel{
    var $name = 'Post';
    var $actsAs = array('Acl'=>'controlled');
    // 'controlled' means you want to create a 'aco'
    // 'requester' means you want to create an 'aro'
    
    /**
     * Returns the parent Alias for current
     */
    function parentNode()
    {
        return $this->name;
    }
    
    }
    ?>

Above code, will now automatically create a new aco for every new post
that is posted. The Acl behavior takes care of all details. Just so
you know, in Acl behavior, there is 'afterSave' callback, which would
be called once the save callback is completed in current model.

Acl behavior would even delete the aco whenever the post is deleted,
without any extra effort on your end. Isn't this cool? Hell yaaa! it
is... Now you would want to setup the permissions on the newly create
'aco'? How do you do that, check out the code below:


Controller Class:
`````````````````

::

    <?php 
    class PostsController extends AppController {
    
       var $name = 'Posts';
       var $helpers = array('Html', 'Form' );
       var $uses = array('Post');
       var $components = array('Acl');
    
       function add() {
           if(!empty($this->data)) {
    	   $this->Post->data = $this->data;
    			
               if ($this->Post->validates())
    	   {
     		$this->Post->create();
    				
    		if($this->Post->save($this->data)) 
                    { 
                        $acoNode = array('model'=>$this->Post->name,
                                         'foreign_key' =>$this->Post->id);
    
                        $aroNode = array('model'=>'User',
                                       'foreign_key'=>$this->getUserId());
    
    		    // User has full control of the post he created
    		    $this->Acl->allow($aroNode, $acoNode, '*');
    		}
    	}
        }
    }
    ?>

So if a save is successful from Post then we know that the Aco is
created and then all we have to do is setup proper aro and aco nodes
and then give the required permissions and we are done!!

I would welcome feedback via comments and suggestions. Let me know if
you have any troubles implementing this. Till then enjoy baking.

Cheers,
Ketan Patel

.. _http://manual.cakephp.org/chapter/acl: http://manual.cakephp.org/chapter/acl
.. meta::
    :title: How to use ACL with Cake PHP 1.2.x?
    :description: CakePHP Article related to acl,aro,permissions,aco,restrictions,controlled,requester,previledges,access controll,acl initdb,initdb,user roles,user rights,acl behavior,allowed access,user permissions,Tutorials
    :keywords: acl,aro,permissions,aco,restrictions,controlled,requester,previledges,access controll,acl initdb,initdb,user roles,user rights,acl behavior,allowed access,user permissions,Tutorials
    :copyright: Copyright 2007 ketan
    :category: tutorials

