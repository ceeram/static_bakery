othAuth 0.5 documentation
=========================

the long awaited documentation of othAuth, this article covers the new
version of othAuth 0.5


Introduction
~~~~~~~~~~~~
OthAuth is written by othman ouahbi aka CraZyLeGs, email for bugs,
enhancements, etc crazylegs AT gmail DOT com
thanks to Dieter@be for helping getting this document done.

OthAuth uses the MIT-license, which is what the cakePHP framework
uses.
OthAuth is both an authentication system (a system that tries to make
sure that it only logs in allowed users who are who they claim to be),
and a permission system (a system that regulates the access to pages
or actions depending on the permissions of the (logged-in) users or
groups)
It is initially designed to work with CakePHP v1.1.x but newer
versions of cakePHP will ofcourse be followed by newer versions of
othAuth if some incompatibility is detected.

While it's one of the first( along with gwoo's rdAuth ) othAuth is
certainly not the only player in this field, there are several other
similar projects (some inspired by othAuth ) developed or in
development for CakePHP
(there are even much more out there not specifically written for
cakephp, but you better write one from scratch for cakephp then trying
to port a "foreign" one)
While trying to not give a subjective judgement about any of these
projects' quality, we think you should consider giving them a try:

To say short, we could say that othAuth is probably the most advanced
(in terms of features), however it is still in heavy development!
(there is no such thing as the perfect solution ;-) )
The coolest features of othAuth are probably user-habtm-groups
support, integration with cake's acl (not entirely finished),
mass login (brute-force) detection & blocking, user-defineable
encryption algorythm (md5, sha-1, crc, or a combinations of them),
etc.

However, othAuth has a to-do list. These are some jobs that could use
some good taking care of:
* Enhanced Deny/Allow Logic
* Improve acl mode

Starting from 0.5, othAuth has support for modes. right now there are
3 modes:

* oth: This is the default old mode, it allows a user to have only one
group, this is not a limitation, there are systems that need a user to
belong to only one group
* nao: This mode was originally written by Naonak thus I called it
nao, he had made the effort to make othAuth work with user HMBTM
groups, I actually rewrote the code, but he made the 1st effort so
this mode is dedicated to him ;)
* acl: This is the ACL mode, it's really in developement so if you
have ideas feel free to contribute
(for an upcomming version: * sim: This is the simple mode, in this
mode you don't have access groups, users are linked to permissions
directly, use it if you don't need groups.)


Installation
~~~~~~~~~~~~

The initial installation consists of 5 steps:
1) create the right tables in your database. depending on the mode you
plan to use ( currently only the oth mode is documented ).
2) download the helper `http://bakery.cakephp.org/articles/view/149`_
and save it as /views/helpers/oth_auth.php ( optional )
3) download the component
`http://bakery.cakephp.org/articles/view/99`_ and save it as
/controllers/components/oth_auth.php
4) create the right models in /models. (you might need some tables
depending on the features you have enabled)
5) install the unbind-all-except-some function. this is actually
needed by the component to be able to unbind All models associated
with the main Models except some you may want to keep in the session
data ( e.g. User hasOne Profile )



DB tables
`````````

these are the basic tables for the oth mode, other features may
require additional tables

::

    
    CREATE TABLE `users` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `username` varchar(50) NOT NULL default '',
      `passwd` varchar(32) NOT NULL default '',
      `name` varchar(50) NOT NULL default '',
      `email` varchar(100) NOT NULL default '',
      `last_visit` datetime NOT NULL default '0000-00-00 00:00:00',
      `group_id` int(10) unsigned NOT NULL default '0',
      `active` tinyint(1) unsigned NOT NULL default '0',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`),
      UNIQUE KEY `username` (`username`),
      UNIQUE KEY `email` (`email`,`username`),
      KEY `group_id` (`group_id`)
    );
    
    CREATE TABLE `groups` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `name` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
      `level` int(11) NOT NULL,
      `redirect` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
      `perm_type` enum('allow','deny') NOT NULL default 'allow',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    );
    
    CREATE TABLE `groups_permissions` (
      `group_id` int(10) unsigned NOT NULL default '0',
      `permission_id` int(10) unsigned NOT NULL default '0',
      KEY `group_id` (`group_id`,`permission_id`)
    );
    
    CREATE TABLE `permissions` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `name` varchar(50) NOT NULL default '',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`),
      UNIQUE KEY `name` (`name`)
    );

next comes saving the php code to files in the right directories as
mentioned above. if you doubt about anything, check cake's convention
page: `http://manual.cakephp.org/appendix/conventions`_

The helper
``````````
Grab this code and save it as /views/helpers/oth_auth.php


The component
`````````````
grab the php code and save it as /controllers/components/oth_auth.php


The models
``````````
Create the right models for your mode or use Bake to do it quickly (
not that writing by hand isn't quick, it's Cake! )
I'll the basic models here if you are lazy:


Model Class:
````````````

::

    <?php 
    class User extends AppModel
    {
    	var $name = 'User';
    	
    	var $belongsTo = 'Group';
    	
    	//var $recursive = 2;
    }
    ?>



Model Class:
````````````

::

    <?php 
    class Group extends AppModel
    {
    	var $name = 'Group';
    	var $hasMany = 'User';
        var $hasAndBelongsToMany = array('Permission' =>
                                          array('className' => 'Permission',
    									  		'joinTable' => 'groups_permissions'));
    }
    ?>



Model Class:
````````````

::

    <?php 
    class Permission extends AppModel
    {
    	var $name = 'Permission';
        var $hasAndBelongsToMany = array('Group' =>
                                          array('className' => 'Group',
    									  		'joinTable' => 'groups_permissions'));
    }
    ?>



The unbind all associations except some function
````````````````````````````````````````````````
For increased performance, othAuth uses an other function, which
unbinds all unneeded associations.
If you don't have the file /app_model.php just create it, and put this
in it:
(otherwise just put the function unbindAll in there, except if you
have it already ofcourse)

::

    	
    	<?php
    class AppModel extends Model{
         function unbindAll($params = array())
        {
            foreach($this->__associations as $ass)
            {
                if(!empty($this->{$ass}))
                {
                     $this->__backAssociation[$ass] = $this->{$ass};
                    if(isset($params[$ass]))
                    {
                        foreach($this->{$ass} as $model => $detail)
                        {
                            if(!in_array($model,$params[$ass]))
                            {
                                 $this->__backAssociation = array_merge($this->__backAssociation, $this->{$ass});
                                unset($this->{$ass}[$model]);
                            }
                        }
                    }else
                    {
                        $this->__backAssociation = array_merge($this->__backAssociation, $this->{$ass});
                        $this->{$ass} = array();
                    }
                    
                }
            }
            return true;
        }
    }
    ?>

You can find more information about this function @
`http://othy.wordpress.com/2006/06/03/unbind-all-associations-except-
some/`_

DB tables
`````````
This configuration is meant for the "oth" mode, there are other modes
available as well (see below)
Keep in mind that you can change the configuration any time you want,
and the results will be immediatly visible (or, at the next page-
request)
e.g. if you want to add an extra group or user, or change a permission
in the database, or in the php files, if you save the database/file,
these new
rules will become active.

The fundamental subjects of authentication come down to dividing your
users into groups, each group having its own permissions or rights. If
you doubt about creating
an extra group, don't hesitate to do so, because it gives you more
flexibility to finetune the permissions. Here is an example sql code:

::

    
    INSERT INTO groups VALUES (1,'webmasters',100,'','allow');
    INSERT INTO groups VALUES (2,'editors',200,'','allow');
    INSERT INTO groups VALUES (3,'members',300,'','allow');

the first argument is the id, ofcourse this has to be unique, next
comes the name for the groups, choose a clear name!
the 3rd option defines the level for the group, this gives the group a
value, so that it's easier to reference, also it gives groups values
which become important when users have more then 1 group (but that's
not in the default "oth" mode, that's for "nao" mode)
next comes the redirect, this allows you to set a redirect-to page in
case the login fails, specifically for each group! (you don't have to
, ofcourse)
in my case it's empty, so othAuth uses access_page, but you could let
it redirect back to the login form, or whatever.
The last argument (called perm_type) is a very handy switch that lets
you define how to filter the permission rules. If set to allow, it
allows its users to do all the actions that
the permissions that are linked to this group define. However, if you
set this to deny, then all the permissions that are linked to this
group are denied for the users!
(there are 2 more fields, created and modified specific to cake)

After that, it's a good idea to enter some users, like this:

::

    
    INSERT INTO users VALUES (1,'root','e10adc3949ba59abbe56e057f20f883e','Firstname Lastname','user@example.com','0000-00-00 00:00:00',1,1);

Again the first argument is the user id, the username (this is the
name that the user types as the login), then comes the md5 checksum of
the password ( or sha-1 etc..depeding what you told othAuth to use).
Use an online md5 calculator like
`http://www.cs.eku.edu/faculty/styer/460/Encrypt/JS-MD5.html`_ (or you
could write your own in cake very easily ofcourse)
to find out what the hash is of your password. the hash above is for
the password 123456. Never write passwords in cleartext in the
database,
not only is that insecure, also it won't work with othAuth!
After that comes the full (real) user name (first name and last name),
and his email address. those aren't used by othAuth, they are optional
and aim to be an example
Next comes the group_id. this is very important, we want to make root
a member of the webmasters group (see above), so we put id 1 there.
The last 1 makes the user active, if this would be 0, the user would
be inactive and unable to login.
Enter as many users as you wish, just remember their passwords, and
keep in mind to pass the right group_id, to make sure they have the
right permissions (see later on)

Next you have to insert all the permissions and link them too the
groups, like this:

::

    
    INSERT INTO permissions VALUES (1,'*');
    INSERT INTO permissions VALUES (2,'news');
    INSERT INTO permissions VALUES (3,'userprofiles');
    INSERT INTO permissions VALUES (4,'userprofiles/view');
    INSERT INTO permissions VALUES (5,'userprofiles/add');
    
    
    INSERT INTO groups_permissions VALUES (1,1);
    INSERT INTO groups_permissions VALUES (2,2);
    INSERT INTO groups_permissions VALUES (2,3);
    INSERT INTO groups_permissions VALUES (3,4);
    INSERT INTO groups_permissions VALUES (3,5);


The permissions will be checked against the restricted actions
variable (see later) to check whether users are allowed to do
something or not.
The first argument is their id, the 2nd is the name of the permission.
The name deserves special attention: '*' means _all_ possible actions
for all possible controllers in your application. (you probably only
want to give this to the webmaster/root)
If the name is just ' ', then the permission means _all_ possible
actions for this one single controller.
if the name is ' / ', then the permission only means that one single
action on that one single controller.
of course you can go up to whatever param you want e.g
controller/action/p/a/r/a/m/s

The next queries just link group_id's to permission_id's. the first
query means that the group with id 1, is linked to permission with id
1.
This means that all the webmasters are allowed to do everything. keep
in mind, if you would have passed 'deny' as perm_type, the webmasters
wouldnt be allowed to do anything. (not implemented at the moment)
The next 2 queries link permissions 2 and 3 to group_id 2. This means
that users of the editor group will be allowed to do any action on
news or userprofiles.
The last one, links permission_id 4 and 5 to group_id 3, so that
members of the 'members' group are allowed to view a single
userprofile, or add one.
(but they can't see a list of all the userprofiles, that would require
'userprofiles/index' or 'userprofiles'
see also $othAuthRestrictions in chapter 5



Configuration: The component.
`````````````````````````````

After this comes the configuration of the component.
The ones at the top (form vars and DB vars) normally don't need any
editing, the defaults should work perfectly.
Below those, there are the "Internals you don't normally need to edit
those" variables. As the comment says, you don't have to edit these,
but it's a nice place to globally store any preferences, which you
would have otherwise have to pass at every login() call.

Explanations:

* $gid
* $strict_gid_check
* $gid_order

These three variables are used in conjunction. The $gid variable
defines a limit of which group_id's are allowed to login.
$strict_gid_check is a variable that defines how that limit is used.
if set to true, it means "$gid only", if false, it means
"$gid or any gid $gid_order that". $gid_order can have two values
'asc' and 'desc', and it defines the order of importance of the
groups,
asc : the most important group is the group with smallest value, desc:
the most important group is the group with greatest value
for example:
* $gid = 3 && $strict_gid_check = true;
only users with level 3 are allowed to login
* $gid = 3 && $strict_gid_check = false; && $gid_order = 'asc'
users with level 1,2 or 3 are allowed to login
* $gid = 3 && $strict_gid_check = false; && $gid_order = 'desc'
users with level 3 and above ( 4, 70,..)are allowed to login

The reason why this is useful, is that you can define loginforms that
only allow to login a specific range of users. it defines the concept
of Point of Login.
(this is something else then allowing users to actions)
For example you could have an "admin area login", that has $gid=1 and
the strict check to true, to only allow webmasters to login at that
point.
However, if you would allow other users to login, that wouldn't be a
problem either, if you defined good permissions.

* $redirect_page
use this var to globally define a redirect page (page to redirect to,
when the login fails),but...
* $auto_redirect
...only when this is set to true. otherwise no redirect will occur.
* $hashkey
a hash key for this login point, also used in different hashing
operations internally

* $login_page
define the page/url/action where users need to login. with
auto_redirect true, users trying to acces restricted
actions are redirected to this page when they don't have enough
credentials.
* $logout_page
redirect to this when they want to logout.
* $access_page
here the page that they tried to acces is temporarily stored so they
can be returned back to this page
after they succesfully logged in and have enough permissions.
* $noaccess_page
people that are logged in, but don't have enough permissions for the
request actions, are sent to this page (notice the subtle difference
with $login_page)

* $mode
this is a _very_ important variable. It controls the working of the
whole othauth system.
There are several options:
- "oth": the default. This gives each user 1 group (and thus n
permissions for that group)
- "nao": this is more advanced. it allows a user to have multiple
groups.
- "acl": this mode tries to complement cakePHP's acl functions, but
this is still in heavy development.

* $cookie_active
* $cookie_lifetime
Use cookies , and define how long they are valid.

* $gid_order
Remember the "levels" that you entered when defining groups? Well, if
you use the nao mode,
where one user can have more groups, you can use this setting to
define the order of importance of several groups.
"asc" means the most importang group is the one with the smallest
level, "desc" is the other way around. This is necessary because each
group
can have different permissions, and when a user has multiple groups,
these permissions (or better: groups) must be weighted against each
other
so that othAuth can know what a user is allowed to do.

* $kill_old_login
when true, the form can do another login with the same hash and delete
the old one.


Making it work.
~~~~~~~~~~~~~~~

Every controller that you want to use othauth, must have the right
settings and beforefilter code. But since we don't like to DRY
(don't repeat yourself), and because it's more convenient, we can just
place the code in the app_controller. All the other controllers
inherit
from it, so they also "get" the othAuth coverage! :)

define these 3 variables (inside the AppController):

::

    
    <?php
    var $components  = array('othAuth'); // necessary, we need to have the othauth component so it can do it's business logic 
    var $helpers = array('Html', 'OthAuth'); // html is always needed, othauth helper is not a must, but you can do some cool things with it (see later on)
    var $othAuthRestrictions = array( 'add','edit','delete');  // these are the global restrictions, they are very important. all the permissions defined above
    are weighted against these restrictions to calculate the total allow or deny for a specific request.
    ?>

It should be obvious that if you have Access to show/admins/1
you don't necessarily have access to show/admins or show.
but if you have access to show, you do automatically have access to
show/admin
a deny, allow logic will be added in a future release.

to ignore auth check on a controller just set $othAuthRestrictions =
null;
for overall controller auth check set $othAuthRestrictions = "*";

for CAKE_ADMIN restrictions set $othAuthRestrictions to CAKE_ADMIN or
the string you defined in "core.php"

Next, put this in the beforeFilter:

::

    
    <?php
    	function beforeFilter()
    	{
    		
    		$auth_conf = array(
    					'mode'  => 'oth',
    					'login_page'  => '/admin/login',
    					'logout_page' => '/admin/logout',
    					'access_page' => '/admin/index',
    					'hashkey'     => 'MySEcEeTHaSHKeYz',
    					'noaccess_page' => '/admin/noaccess',
    					'strict_gid_check' => false);
    		
    		$this->othAuth->controller = &$this;
    		$this->othAuth->init($auth_conf);
    		$this->othAuth->check();
    		
    	}
    ?>

you will probably recognize some variables that we also have setup
globaly in the components setup. Well, here you can override these :)
The 3 function calls inside it are mandatory to let othauth do it's
job.

Now you just need to have some place where you can login and logout.
users/login and users/logout seems like logical choice, so add this to
your users controller:

::

    
    <?php
    function login()
    {
    	if(isset($this->params['data']))
    	{
    		$auth_num = $this->othAuth->login($this->params['data']['User']);
    		
    		$this->set('auth_msg', $this->othAuth->getMsg($auth_num));
    	}
    }
    function logout()
    {
    	$this->othAuth->logout();
    	$this->flash('You are now logged out!','/users/login');
    }
    
    function noaccess()
    {
    	$this->flash("You don't have permissions to access this page.",'/admin/login');
    }
    ?>

Now, create a view for the login function (views/users/login.thtml)

::

    
    <h1>Log In:</h1>
    <form action="<?php echo $html->url('/users/login'); ?>" method="post">
    <div class="required"> 
    	<label for="user_username">Username</label>
     	<?php echo $html->input('User/username', array('id' => 'user_username', 'size' => '40')) ?>
    	<?php echo $html->tagErrorMsg('User/username', 'Please enter your username') ?>
    </div>
    <div class="required"> 
    	<label for="user_password">Password</label>
     	<?php echo $html->input('User/passwd', array('id' => 'user_passwd', 'size' => '40', 'type'=>"password")) ?>
    	<?php echo $html->tagErrorMsg('User/passwd', 'Please enter your password!') ?>
    </div>
    
     <?php echo $html->checkbox("User/cookie");?>
    
    <div class="submit"><input type="submit" value="Login" /></div>
    </form>

The last item (the checkbox) is used to store the information in a
cookie, so that the user can choose to be remembered for the next
visit!

you can configure cookie Remember me feature with these two self-
explainatory variables in the Component:
var $cookie_active = true;
var $cookie_lifetime = '+1 day';
?> Everything should work by now, but you probably want to know what
cool thingies that othauth has to offer for you to use? read on !


Cool tricks you can do with othAuth.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


Redirecting back after login when the session timeouts
``````````````````````````````````````````````````````

this feature is activated by default, suppose you work on a page, and
the session timeouts
or you accessed a page that you don't have enough permissions to
access, you are in a normal behaviour redirected to the login page,
after login, if this feature is activated, othAuth redirects back to
that page.
to disable it, simply comment $auth_url_redirect_var, simply change
the value of this var to change the url var ( if it's used by
something else )


Getting information about the user, group, etc.
```````````````````````````````````````````````
You can interact with the component (when doing business logic), or
with the helper (which aids in presentational stuff).
The component and the helper work very similarly. the most important
aspects come down to these 4 functions:
* user() <-- user information
* group() <-- group information
* permission() <-- permission information
* getData() <--- getData gets the whole othAuth session data, it's up
to you to parse it, ( print_r can be useful )

Offcourse, the helper is available in the view as $othAuth, while the
component in the controller is called $this->othAuth
Other then that, they work the same:

Component:

::

    
    <?php
    $fullname   = $this->othAuth->user('name');
    $last_visit = $this->othAuth->user('last_visit');
    $groupname  = $this->othAuth->group('name');
    ?>

Helper:

::

    
    <?php
    $fullname   = $othAuth->user('name');
    $last_visit = $othAuth->user('last_visit');
    $groupname  = $othAuth->group('name');
    ?>

The helper even has a 5th function called sessionValid, you could use
the helper like this in your view:

::

    
    <?php
    if ($othAuth->sessionValid())
    {
    	echo '<li>'.$html->link('logout', '/users/logout').'</li>';
    }			
    else
    {
    	echo '<li>'.$html->link('login','/users/login').'</li>';
    }
    ?>



Limit login attempts
````````````````````

Starting from version 0.5, othAuth offers a mechanism to limit login
attempts, using ip and cookie.
This Feature if enabled ( it is actually enabled by default ) protects
your login form mass login, after a configurable amount of tries the
user is ip and cookie banned.
( Another method is instead of banning you generate a hash image in
the form not supported within othAuth atm but it's a snap to do )
to control it, use these variables in the component:

::

    
    <?php
    	$login_limit // flag to toggle login attempts feature
    	
    	$login_attempts_model // the name of the model that interfaces the table where login attemps are stored
    	
    	$login_attempts_num // number of login attempts before an action is taken ( ban, image auth,..)
    	
    	$login_attempts_timeout // time in minutes to reset already stored attempts of this user
    	
    	$login_locked_out // Time to lock out/ban the user
    ?>

db table:

::

    
    CREATE TABLE `login_attempts` (
      `ip` varchar(15) collate utf8_unicode_ci NOT NULL,
      `num` int(11) NOT NULL default '1',
      `expire` datetime NOT NULL,
      `created` datetime NOT NULL,
      PRIMARY KEY  (`ip`)
    );

Model login_attempts.php


Model Class:
````````````

::

    <?php 
    class LoginAttempts extends AppModel
    {
        var $name       = 'LoginAttempts';
        var $primaryKey = 'ip';
        var $useTable   = 'login_attempts';
    }
    ?>


Keeping track of logins
```````````````````````

I initially wrote this for a project I was working on and plugged it
in othAuth, I kept it because it might be useful for others.
This feature saves history of logins in a db table in case you want to
do some statistics etc.
Use these two variables in the Component to control it:

::

    
    <?php
    	$history_active // flag to activate/deactivate this feature
    	$history_model // model name to store info thro
    ?>

an example table:

::

    
    CREATE TABLE `user_histories` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `username` varchar(32) NOT NULL,
      `fullname` varchar(64) NOT NULL,
      `groupname` varchar(32) NOT NULL,
      `visitdate` datetime NOT NULL,
      PRIMARY KEY  (`id`)
    );



Slipping some additional associations through othAuth's data
````````````````````````````````````````````````````````````
By default, othAuth strips the data-array to include only the
information it uses (user, group, etc)
But you can add additional association data
For example suppose you have the User model, and it's associated with
the Profile model. You can include the profile information inside the
session data so it can be used by the component or the helper
This is how:
In the component configuration alter these 3 arrays (which are empty
by default)
* $allowedAssocUserModels
* $allowedAssocGroupModels
* $allowedAssocPermissionModels

for example if you define $allowedAssocUserModels as
array('hasOne'=>array('Profile')) any data residing in your $data
array with index 'Profile' will be
kept through-out the session!


other encryption functions
``````````````````````````
By default , othAuth uses md5, but this isn't an obligation, you can
tell othAuth to use, sha1, crypt or even your own method!
Use the following vars to configure it:

::

    
    <?php
    	$pass_crypt_method   = 'md5'; // md5, sha1, crypt, crc32,callback
    	$pass_crypt_callback = null; // if you have a callback function, set its name here
    	$pass_crypt_callback_file = ''; // file where the function is declared ( in vendors )
    ?>

Hope this article helped frustrated people, sorry again for the long
delay.
Don't forget that othAuth is a community stuff, feel free to improve
it ( docs too )
Cake!

.. _http://www.cs.eku.edu/faculty/styer/460/Encrypt/JS-MD5.html: http://www.cs.eku.edu/faculty/styer/460/Encrypt/JS-MD5.html
.. _http://othy.wordpress.com/2006/06/03/unbind-all-associations-except-some/: http://othy.wordpress.com/2006/06/03/unbind-all-associations-except-some/
.. _http://bakery.cakephp.org/articles/view/99: http://bakery.cakephp.org/articles/view/99
.. _http://bakery.cakephp.org/articles/view/149: http://bakery.cakephp.org/articles/view/149
.. _http://manual.cakephp.org/appendix/conventions: http://manual.cakephp.org/appendix/conventions

.. author:: CraZyLeGs
.. categories:: articles, tutorials
.. tags:: user,access,login,othauth,permission,authentication,logout,c
omponent,restriction,Tutorials

