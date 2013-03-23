User Permissions and CakePHP ACL
================================

by %s on November 28, 2006

This article shows how to use CakePHP's ACL to control user access to
different parts of a website. It covers CakePHP 1.1.10.3825 (November,
2006)
One of the features prominently listed for CakePHP is the builtin ACL
(Access Control List.) It seems to be a perfect fit for modeling user
permissions for web applications. But to actually make this work
takessome digging into the sources. I spent a whole weekend trying to
make this work. This article documents the findings of this struggle.

Here are two simple requirements I want to build the authorization
using ACL. It is quite typical for a website:

#. There are three types of users: anonymous, member, and admin. Each
   can be further divided (e.g., there can be regular members and premium
   members)
#. Contents can be divided into (based on ULR):

    #. accessible to all
    #. only accessible to members.
    #. only accessible to admin


To model these using CakePHP's ACL, different type of users can be
mapped to AROs, and contents are ACOs. Each of these are modeled as a
tree. Let's be a little more specific. I modeled the user hierarchy as
follows:

::

    
    ARO's:
    		group.all
    			group.anoymous
    				anonymous
    			group.members
    				group.regular
    					test_regular
    				group.premium
    					test_premium
    				group.admin
    					test_admin

In the above diagram, those with the "group." prefix are not real
users, but are groups for authorization purposes. "anonymous",
"test_regular", "test_premium" and "test_admin" are real users. Let's
assume they're already in the User table:

::

    
    ID	   login			 password
    ===    =========        ===========
    101	   anonymous		 123456
    102	   test_regular		 123456
    103	   test_premium		 123456
    104	   test_admin		 123456

Simiarly, the contents of the site can be modeled as follows:

::

    
    ACO's:
    		/                   <=== accessible to all
    			/pages      <=== accessible to all
    			/posts		<=== only accessible to members
    			/users		<=== only accessible to admin
    			/authentications   <=== to all, for login

So our task is to somehow put all these into the ACL tables so that we
can use the builtin ACL to achieve the desired permissions.

The builtin database ACL uses three tables to store ARO's, ACO's and
ARO_ACO permissions. The first step is to translate the above data and
requirements into ARO/ACO and thier relations. There is a script
acl.php in the cake/scripts/ directory, but I found it to be
confusing, and buggy (it messes up my tree sometimes when I set
parents for some nodes.) So instead, I chose to do everything by hand,
this gives me better understanding of how ACL works.

Let's first create the tables (same as "php acl.php initdb" for
MySQL):

::

    
    CREATE TABLE acos (
    	id 			integer NOT NULL AUTO_INCREMENT,
    	object_id 	integer DEFAULT NULL,
    	alias	  	varchar(255) NOT NULL DEFAULT '',
    	lft			integer DEFAULT NULL,
    	rght		integer DEFAULT NULL,
    	PRIMARY KEY(id)
    );
    
    CREATE TABLE aros (
    	id integer NOT NULL AUTO_INCREMENT,
    	foreign_key integer DEFAULT NULL,
    	alias	  varchar(255) NOT NULL DEFAULT '',
    	lft		integer DEFAULT NULL,
    	rght	integer DEFAULT NULL,
    	PRIMARY KEY(id)
    );
    
    CREATE TABLE aros_acos (
    	id integer NOT NULL AUTO_INCREMENT,
    	aro_id integer DEFAULT NULL,
    	aco_id	integer DEFAULT NULL,
    	_create	integer NOT NULL DEFAULT 0,
    	_read	integer NOT NULL DEFAULT 0,
    	_update	integer NOT NULL DEFAULT 0,
    	_delete	integer NOT NULL DEFAULT 0,
    	PRIMARY KEY(id)
    );

Now we will try to put our ARO tree into the aros table. To do this,
you need to understand how CakePHP ACL stores a tree in a table. The
method is called MPTT(Modified Preorder Tree Traversal), it is better
than the other standard approach (i.e., having a "parent_id" column)
in that it only takes one select query to find a subtree or a path to
the root. The difficulty is to figure out what to put for the "lft"
and "rght" columns for each row. For a detailed introduction to MPTT,
please consult the very readable article:
`http://www.sitepoint.com/article/hierarchical-data-database`_. One
confusing point (due to the lack of documentation) is how the "id",
"foreign_key" and "alias" relates to the User table. It turns out the
AROS.id column is an internal auto_incremented id, thus not relevant
when creating the AROS (But, the confusing thing is that the AROS.id
is used for the relation mapping in AROS_ACOS.) As to the User table:
foreign_key should be the USER.id, and "alias" should be the user
name: User.login.

With this understanding, we will put our ARO tree into the AROS table
with the following insert statements (we reserve the first 100
"foreign_key" ids for future user groups, thus our real user id starts
at 101):

::

    
    insert into aros (id, foreign_key,alias,lft,rght)values(1,1,'group.all',1, 20);
    insert into aros (id, foreign_key,alias,lft,rght)values(2,2,'group.anonymous',2, 5);
    insert into aros (id, foreign_key,alias,lft,rght)values(3,3,'group.member',6, 19);
    insert into aros (id, foreign_key,alias,lft,rght)values(4,4,'group.regular',7, 10);
    insert into aros (id, foreign_key,alias,lft,rght)values(5,5,'group.premium',11, 14);
    insert into aros (id, foreign_key,alias,lft,rght)values(6,6,'group.admin',15, 18);
    insert into aros (id, foreign_key,alias,lft,rght)values(7,100,'anonymous',3, 4);
    insert into aros (id, foreign_key,alias,lft,rght)values(8,101,'test_admin',16, 17);
    insert into aros (id, foreign_key,alias,lft,rght)values(9,102,'test_regular',8, 9);
    insert into aros (id, foreign_key,alias,lft,rght)values(10,103,'test_premium',12, 13);

Similarly, we can model the ACO's tree with the following:

::

    
    insert into acos (id, object_id,alias,lft,rght)values(1,1,'/',1, 10);
    insert into acos (id, object_id,alias,lft,rght)values(2,2,'/authentications',2, 3);
    insert into acos (id, object_id,alias,lft,rght)values(3,3,'/users',4, 5);
    insert into acos (id, object_id,alias,lft,rght)values(4,4,'/posts',6, 7);
    insert into acos (id, object_id,alias,lft,rght)values(5,5,'/pages',8, 9);

If you want to check whether you modeled them correctly in the
database with the above inserts, you can either do some sql query
(again need to understand how MPTT works), or use the acl.php script
as follows:

::

    
    cake\scripts>php acl.php view aro
    
    Aro tree:
    ------------------------------------------------
    [1]group.all
      [2]group.anonymous
        [7]anonymous
      [3]group.member
        [4]group.regular
          [9]testreg
        [5]group.premium
          [10]testpre
        [6]group.admin
          [8]admin
    ------------------------------------------------

and:

::

    
    cake\scripts>php acl.php view aco
    Aco tree:
    ------------------------------------------------
    [1]/
      [2]/authentications
      [3]/users
      [4]/posts
      [5]/pages
    ------------------------------------------------

Both tree show the desired structure.

Now let's model the permissions. We can either start with allowing all
and gradually take away permissions, or the other way around, denying
all and then add permission. I think it depends on the type of site
you're trying to build. I chose the first approach for this example.

So first we grant all access of "/" to everyone:

::

    
    insert into aros_acos(id,aro_id,aco_id,_create,_read,_update,_delete)values(1,1,1,1,1,1,1);

We then require that "/users" and "/posts" are only accessible to
members. To
do this, we deny access to the "group.anonymous":

::

    
    insert into aros_acos(id,aro_id,aco_id,_create,_read,_update,_delete)values(2,2,3,-1,-1,-1,-1);
    insert into aros_acos(id,aro_id,aco_id,_create,_read,_update,_delete)values(3,2,4,-1,-1,-1,-1);

We then further require that "/users" can only be accessed by the
"group.admin":

::

    
    insert into aros_acos(id,aro_id,aco_id,_create,_read,_update,_delete)values(4,4,5,-1,-1,-1,-1);

With these in place, we expect the permission to behave correctly.
That is, among others:

#. Acl->check("anonymous","/pages","*") ====> true
#. Acl->check("anonymous","/posts","*") ====> false
#. Acl->check("anonymous","/users","*") ====> false
#. Acl->check("test_regular","/posts","*") ====> true
#. Acl->check("test_regular","/users","*") ====> false
#. [li] Acl->check("test_admin","/users","*") ====> true

To hook this into you application, the easiest is to put the
permission checking into the app_controller.php, something like the
following:

Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
        var $beforeFilter = array('checkAccess');
    
        var $components = array('Acl');
    
        function checkAccess(){
    		// This part not required. It shows one way to
    		// integrate this permission with authentication: login/logout
    		// We always put the login_name in the session under
    		// the key USER_LOGIN_KEY, even for anonymous users.
    		// So whether a user is logged in or not depends on
    		// whether this value is ANONY_USER or not. You may
    		// choose to implement it some other way (e.g., whether it's
    		// set or not.)
            if (!$this->Session->valid()) {
                $this->Session->renew();
            }
            if (!$this->Session->check(USER_LOGIN_KEY)) {
                $this->Session->write(USER_LOGIN_KEY,ANONY_USER);
            }
    
    		// here we check the permissions based on
    		// username and controller name (which is
    		// is the first part of the URL)
            $user = $this->Session->read(USER_LOGIN_KEY);
            $aco = $this->params['controller'];
            if ($this->Acl->check($user, "/$aco", '*')) {
                return; 
            }else{
                // if anonymous, redirect to login
                // otherwise, give permission error
                if( $user == ANONY_USER){
                    $this->redirect("/authentications/login");
                }else{
                    $this->redirect("/pages/permission_denied");
                }
            }
        }
    }
    ?>

In order to test/use the above setup, you will need to code/mockup the
controller/models/views for the "/users" and "/posts" part. To
completely integrate with user management, your "user" model needs to
have a modifed "save/delete" method to update the aros table.

One nice way to see whether your permissions are called correctly
(besides the fact the page accesses behave correctly) is to turn on
DEBUG = 3, you can then see all the SQL that the ACL component calls
to figure out the permission. This requires/helps your understanding
of the MPTT. The side effect is that you can also see that if your
tree is deep, the current ACL implmentation is not efficient ( to
check a permission for a ARO node, one needs to make depth(node) + 2
queries in the worst case, as in our example.)

In the next version of this article (hopefully), I'll try to make this
part of the User permission into a component, to make it easily
reusable.



.. _http://www.sitepoint.com/article/hierarchical-data-database: http://www.sitepoint.com/article/hierarchical-data-database
.. meta::
    :title: User Permissions and CakePHP ACL
    :description: CakePHP Article related to security,Tutorials
    :keywords: security,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

