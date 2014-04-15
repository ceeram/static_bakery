Real World Access Control
=========================

by rtconner on June 13, 2007

Cake AC is a very powerful but generic access control sytem. Lets face
it, the only real use for Access Control is User permissions. So let
us look at the common every day use for Access Control.
I'm going to assume we all looked at (and were confused by) the Cake
Manual Tutorial on `Access Control`_. I'll also assume you all want to
know how you might practically use Cake AC to implement User and Group
permissions.

Cake Acces control was not made specifically for User/Group
permissions control. It is simply a generic access control system to
be used for anything. It can be used for User/Group permissions, but
we have a bit of work to do to figure out exactly how.


Understanding the concepts
--------------------------
How can Cake AC be used for User and Group permissions? Lets simplify
things, by talking in terminology that we all understand...

ARO (Access Request Objects) = Users or Groups

#. link_id = User ID (or 0 if it is a Group)
#. parent_id = Id of Group that a User Belongs to
#. alias = Unique Id/Name of Users and Groups

ACO (Access Control Objects) = Your Controllers or Actions

#. link_id = A Unique Page Id (I have no idea what that is)
#. parent_id = Id of Controller that an Action belongs to
#. alias = Unique Id/Name of Controlers or Actions

ACL (Access Control List) = List that holds User/Group data access
restrictions to Controllers and Actions

#. aro = Your user or group
#. aco = your controllers or actions
#. action = You define the actions, generally read, write, etc..

So some things of concern here, now that we have this in english.

While Cake AC does not force aliases of anything to be Unique.. well
as you might expect, you do run into problems if they are not unique.
What will happen if you have a User named 'Smith' and you are putting
him into a group called 'Smith'? Well, unless you add something, the
system will wonder why you are trying to make 'Smith' a parent of
himself.

So with that all said, I set my system up so that references to Users
were always made with the users Unique Id (ARO link_id) and references
to Groups were made with the Group name (ARO alias)

::

    // set user with id 2 to be part of the group 'admins'
    $aro->setParent('Admins', 2); 
    // just made sure no groups or users ever have numbers for names, and you are safe

The same problem exists with ACO's. If you have your pages treed in
controller/action format. When you go to put the home/home
(controller/action) page into permisions, well.. the system will
wonder why you are trying to make home a parent of itself. I have not
figured out how to identify controllers or actions with numbers, so
perhaps adding a convention will help.

::

    // set home action to be a child of the home controller
    $aco->setParent('home/home', 'home'); 

So be careful when using Cake access control for User Permissions, it
can work, you just have to be careful.


Putting this into your App
--------------------------
Well, perhaps a real world example of making sure access is controlled
would look like this...

You would never write the following code out (I don't think). Likely
you would want a full system where the following data is created using
form input from a user.

::

    $aro = new Aro(); // users and groups
    $aco = new Aco(); // controllers and actions
    
    // add some users and groups to the system
    // it is assumed you have your own table of user, with their own information
    $aro->create( 1, null, 'John Smith' ); // 1=user_id, null=parent
    $aro->create( 2, null, 'Millie Thompson');
    
    $aro->create(0, null, 'Admins'); // group
    $aro->create(0, null, 'Managers'); //group
    
    //set John to be an admin
    $aro->setParent('Admins', 1);
    // set Millie to be an manager
    $aro->setParent('Managers', 2);
    
    $aco->create(0, null, 'Home/home');

In one of your controllers you can do something that looks like this

::

    $this->Acl->allow('Admins', 'Home/home', 'read');
    $this->Acl->deny('Managers', 'Home/home');

There, now John can get into home/home and Millie cannot.

After you set up your data, this is practical code, that you might
actually write. It will automatically allow or deny the logged in user
access to a given action.

::

    class AppController extends Controller {
    	
    	var $components = array('Acl');
    
    	function beforeFilter() {
    		// next line will change depending on your auth system
    		// basically getting the Id of the currently logged in user
    		$user_id = $this->obAuth->getUserId(); 
    
    		$page = $this->name.DS.$this->action; 
    		$access = $this->Acl->check($user_id, $page, 'read');
    
    		//access denied (if we are in Home/home, this is Millie)
    		if ($access === false) { 
    			echo "access denied";
    			exit;
    		}
            
    		//access allowed (if we are in Home/home, this is John)
    		else {
    			echo "access allowed";
    			exit;
    		}
    	}
    
    }

I'll leave it up to you to set up your own permsissions, and groups in
a real life system. This does give you a good overview of the concepts
though.

There is an `ACL management plugin`_ on CakeForge which might help
you. Currently it is still young and in development. It might help
automate a lot of these things for you.

.. _Access Control: http://manual.cakephp.org/chapter/acl
.. _ACL management plugin: http://cakeforge.org/projects/acm/

.. author:: rtconner
.. categories:: articles, tutorials
.. tags:: acl,authentication,aro,1.1,aco,access control,Tutorials

