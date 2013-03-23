Migrating a real-world application to CakePHP 1.2
=================================================

by %s on November 04, 2008

[p] I thought I'd chronicle my work migrating my fist CakePHP
application to CakePHP 1.2 (rc3 at the time of writing). The
application was originally written for CakePHP 1.0 (= the good old
days). It has been updated to work with the latest versions 1.1 but
has not really been rewritten much. [/p][p] Considering how much
CakePHP has evolved and also how many beginner-mistakes I have put
into this application, this migration will also describe some pretty
huge optimizations that was possible because of new features and my
improved knowledge of CakePHP. [/p]


About The Application
~~~~~~~~~~~~~~~~~~~~~

The application if called Fileshifter and it is a simple file manager
and file sharing application. The original purpose was to complement
an ftp server. Fileshifter is generally more simple to manage and
easier for "average people" to handle. You have projects and upload
files into them or download files from them. As admin you can manage
user accounts and project permissions.

The English pages on the application-website are horribly out of date.
Sorry. There are a few screen-casts in the Swedish section that might
give some context to what I am writing about, though.
`http://www.fileshifter.se/`_


First attempt. Aka: Oh crap, nothing works
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Just dropping my old application into CakePHP 1.2 clearly didn't work.
:)
I just got two errors. The second one was a fatal error which halted
further execution in config/routes.php

I went through all files in the config directory. This took a little
while but I wanted to get the configurations identical to those I had
before. One bonus for my application is the new configuration
directive to not check the userAgent. This will be very useful for my
upload actions that will be used by the java applet the application
uses. In earlier versions I manually hacked the core to make this
possible.


core.php
````````
Starting with a clean file from CakePHP 1.2 I changed the following
things for my application. Not all as strictly necessary to get an
application running.
Session.cookie - set to the same name as in the old application.
Session.checkAgent - set to false globally for the time being. I will
check if I can set this just in the relevant controller without
causing problems.
Security.salt - should always be altered. I kept part of the original
string, moved things around and inserted some swedish words here and
there just for fun.


bootstrap.php
`````````````
My old file set a few constants used to configure the application. A
few of these I switched over to the new Configure class. Most, I left
as constants since they are nothing you would ever change at runtime.
For example the path to the root of the file area.
The current language was one of the ones I switched over. See below
about i18n.


database.php
````````````
Not a big problem here. The could have been left as is but I did copy
over the login parameters to a fresh file. I noticed that the connect
parameter does not need to be set for MySQL. I am not sure about
before but now it is enough to set persistent to true.


routes.php
``````````
The few routes I had defined only needed to call the method statically
to continue to work. For example:

::

    
    $Route->connect ('/', array('controller'=>'efs', 'action'=>'index'));

became:

::

    
    Router::connect('/', array('controller' => 'efs', 'action' => 'index'));

I also replaced all default files (index.php, .htaccess ...). I did
not bother to check which had changed. It was simpler to just replace
them all with the new 1.2 versions.



File model clashes with the File class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Since my application is a file manager I had a model called File. It
actually took me a good while before I figured out that my File model
clashes with the File class in the core. Time for some search and
replace. All references to File was replaced with EfsFile (did not
think of anything better). Most of it could be changed this simply,
but I had to make a few manual adjustments. Cakes $useTable came in
good use here. I did not have to alter the database just because I
renamed the class.



Replacing authentication
~~~~~~~~~~~~~~~~~~~~~~~~
Next big problem is authentication. Since all data the user can
interact with flows from the authenticated user account, this
basically stops my application completely until I get the new
authentication up and running. Lucky enough I was using an Auth
component that works kind-of similar to the current Auth component in
the core. There was still a lot of work but it could have been worse.

I didn't keep any of the code from the old authentication. What I did
was take copy a lot of it over from another application where I first
tried some basic 1.2 authentication. The code is pretty much straight
out of the tutorials available all over the web.



Permissions
~~~~~~~~~~~
In the original version I intentionally kept permissions to a minimum.
Simplicity was the key and increased complexity and granularity in
permissions will increase the workload for the administrator. This was
of-course no bad thing when it came to updating the authentication.
The old Component used a User model and a Permission model. I saw no
reason to even have a model for permissions anymore since all I needed
was "normal user" and "admin user". I mapped this to a simple integer
field in the users table. I can imagine that trying to migrate a role-
based system can be a lot more work.



Transitioning old passwords
~~~~~~~~~~~~~~~~~~~~~~~~~~~
I have managed to keep all the old passwords. They were md5 hashed
without a salt so I was able to get it working by defining an
authenticate object for Auth. I chose to make the User model the
authenticate object. It feels natural to have password hashing there.
In AppController::beforeFilter:

::

    
    $this->Auth->authenticate =& $this->Auth->getModel();

And in models/user.php I added:

::

    
    function hashPasswords($data) {
    	if (is_array($data) && isset($data[$this->alias])) {
    		if (isset($data[$this->alias]['email']) && isset($data[$this->alias]['pass'])) {
    			$data[$this->alias]['pass'] = Security::hash($data[$this->alias]['pass'], 'md5', false);
    		}
    	}
    	return $data;
    }

One thing still on the todo-list is that I would rather try to manage
a transitional system for the first release after this migration.
Something where the password can be checked against both the old and
new hash. Any login where the stored password matches the old hash
will be replaced by the given password hashed using the new (more
secure) system using a salt. If I can't get that to work I have to
make a judgement call wether to keep the old less safe passwords or
require all users in all installations to renew their passwords. We'll
see...



Changing the code convention of the entire application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
This can be tedious if you plan to do it in one big pass. I decided to
use this as an indicator of what code I had looked over and changed.
Any method I altered or examined and decided not to alter was given a
change in coding style to indicate that it was "approved" 1.2 code. If
I had been happy with my old style I would not have done this, but
since I planed to change it, this was a good way to do it.



Loading classes and other external code
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
All the old methods of loading files have changed. No more uses(), no
more vendor(). This is now all taken care of by App::import(). For
example when I load a PEAR class to archive a whole folder:

::

    
    vendor('Archive/Tar');

now reads:

::

    
    App::import('Vendor','Archive/Tar');

The component dealing with syncing with the filesystem nor loads the
needed models in a single call, like this:

::

    
    App::import('Model',array('Project','EfsFile'));



Removing requestAction()
~~~~~~~~~~~~~~~~~~~~~~~~
Like so many starting out, I was seduced by requestAction(). Looking
at my old code I remember believing that only the UsersController
should manipulate the User model. That is partly why I turned to
requestAction when one controller needed access to "another model".

My primary requestAction() was to call "/files/viewedBy/1/1" whenever
a user selected a new file. This would log that the user has seen the
file. By moving this code into the EfsFile model I was abel to take
the request-time for these down quite a bit. With my test-data this
went down to 0.15s from well over a second before. Partly because of
the overhead of a request and partly because of Containable helping to
optimize the data retrieval.



Hey, where did my data go?
~~~~~~~~~~~~~~~~~~~~~~~~~~
One new behavior introduced in CakePHP 1.2 RC3 may delete some data by
accident. If an association has conditions, you should use the new
array notation for conditions. If you delete a record and dependent is
true, you expect the related record(s) to be deleted as well. If
conditions is not an array you can delete delete all records of the
related model, not just related ones. I have had this happen to my
data for hasOne associations. I have not done extensive test to see if
this affects other associations.
This is straight out of manual as it was a while ago:

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	var $name = 'User';
    	var $hasMany = array(
    		'Comment' => array(
    			'className'	=> 'Comment',
    			'foreignKey' => 'user_id',
    			'conditions' => 'Comment.status = 1',
    			'order' => 'Comment.created DESC',
    			'limit' => '5',
    			'dependent'=> true
    		)
    	);
    }
    ?>

And this is the small but important change:

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	var $name = 'User';
    	var $hasMany = array(
    		'Comment' => array(
    			'className'	=> 'Comment',
    			'foreignKey' => 'user_id',
    			'conditions' => array('Comment.status' => '1'),
    			'order' => 'Comment.created DESC',
    			'limit' => '5',
    			'dependent'=> true
    		)
    	);
    }
    ?>

I believe even empty conditions can cause you problems:

::

    
    'conditions' => '',

it should be:

::

    
    'conditions' => array(),

I am not certain about that last bit but better safe than sorry,
right?



Hey, where did my data go? part 2
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
There was a bug introduced in CakePHP 1.2 rc3 that anyone updating
should be aware of. Cake can accidentally delete data in the join
tables of your HABTM associations. This happens if the association is
two-sided and you remove the "link" between two records. Any other
record the associated record is linked to is also unlinked. I found
this out the hard way. I then discovered a bug report in trac. The fix
described there has worked for me in two applications for a few weeks
now. See the ticket for details and example-code.
`https://trac.cakephp.org/ticket/5579`_


Finding new data
~~~~~~~~~~~~~~~~
The Model methods find(), findAll() and so on have changed a lot. This
required a lot of manual work, but since I could optimize thing at the
same time and speed up the application noticeably I didn't really mind
the work. I also thing this new syntax is a lot easier to use.

::

    
    $allProj = $this->Project->findAll(null,null,'Project.title ASC');

should now read:

::

    
    $allProj = $this->Project->find('all',array(
    	'order' => 'Project.title ASC'
    ));

or better yet:

::

    
    $allProj = $this->Project->find('all',array(
    	'order' => array('Project.title' => 'ASC')
    ));

Note: Keeping "ASC" in my example is not necessary but I like to have
it there so I know at a glance what is going on.
OK, all find operations are now taken care of by find. The first
parameter indicates the type of find to perform. Setting it to "first"
will find a single record for example. The second parameter is an
array, like in so many places in CakePHP 1.2. This should contain the
additions you want to make to the query. As you can see order is one
thing. Conditions is another. Fields is a third. I could never
remember if order was parameter number 3,4 or 5. Now I don't have to.
I just add a key called order and a value with the ordering field and
direction I want. It does not have to come before or after the
conditions... all very nice.

All of these parameters can be broken down into arrays themselves.
This adds another layer of protection against sql injections. The
conditions can even be broken down into ands and ors in a deep
structure. Cake will construct a nice where-clause of them for you and
keep track of all the parenthesis. See the manual for many examples of
this. The same goes for deleteAll() and any other conditions
throughout the application.

::

    
    $result = $this->deleteAll( array(
    	$this->alias.'.created <' => $deadline
    ));

Notice that the comparison operator is on the left side. This is new
since CakePHP 1.2 RC2, I think.



I can hardly contain myself
~~~~~~~~~~~~~~~~~~~~~~~~~~~
Am am bursting with joy! Containable behavior is one of my favorite
features in CakePHP 1.2. Containable is like find() on steroids. It is
like associations on acid. It is like... ok enough! The basic first
step is to simple add this to the relevant models.

::

    
    var $actsAs = array('Containable');

Containable is a behavior that helps you optimize your queries.
Perviously all I could do was to set recursive and unbind a model.
Containable sort-of does this for you, in a very intuitive way. Parts
of the application has been sped up by a factor of 8. That is 8x the
original request time, largely because of Containable.

It may sound like magic, but imagine this:

User: habtm Project, habtm File
Project: hasMany File, habtm User
File: habtm User, belongsTo Project

These three models can easily cause a lot of unnecessary data to be
loaded since they relate to each-other in several ways. By taking
advantage of some simple functionality in containable I was abel to
eliminate most of the unused data from my queries. For example,
loading a single file:

::

    
    $data = $this->User->find('first', array(
    	'conditions' => array('User.id'=>$this->userData['User']['id']),
    	'contain' => array(
    		'Project'=>array(
    			'EfsFile'=>array(
    				'conditions'=>array('EfsFile.id'=>$this->params['id'])
    			)
    		)
    	)
    ));

In english: Find the currently logged in User. Contain the results to
also include any related Project and to the project related File if
the id of the file is the one we want.

This may look like a backwards way of loading a file. However this is
quite effective since I only want the file to load is it is in a
project that the current user has access to. Using the Set class
(another great new feature in 1.2) it is no big problem picking out
the file from the results. Just imagine the mess returned if I had set
recursive to 2 instead... I would have loaded all files in all
projects related to the user and I would have had to loop through the
results to locate the one with the right id (making it a condition
would not have worked). OK, that was an example from hell but I hope
the point was made. Containable is your friend. Please check the
manual for more complete details on how to use Containable.



New validation system
~~~~~~~~~~~~~~~~~~~~~
I must admit, I did not have a lot of validation before. The
validation I had was mostly custom validation code. Some of it even in
the controller (ick!). One thing I did validate before was, naturally,
login data. For example, validating uniqueness is a lot better in 1.2.
And there is also support for multiple validation rules per field.
Apart from changing the validation I had I also added a few rules for
the files and projects. I still have to change my controller code to
make better use of the validation errors and messages, though.

::

    
    var $validate = array(
    	'name'=>VALID_NOT_EMPTY,
    	'login'=>VALID_NOT_EMPTY,
    	'email'=>VALID_EMAIL
    );

now reads:

::

    
    var $validate = array(
    	'name' => array(
    		'rule' => 'notEmpty',
    		'message' => 'The name can not be left blank'
    	),
    	'email' => array(
    		'email' => array(
    			'rule' => 'email',
    			'message' => 'This is not a valid email.'
    		),
    		'unique' => array(
    			'rule' => 'isUnique',
    			'message' => 'This email already used for another user.'
    		)
    	)
    );

The error messages above have been replaced to make them more
readable. They are in the form of translation keys in the real code.



Synchronizing with the filesystem
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The application keeps its files and folders in the database. To enable
admins to expose the files on their local network or a traditional
ftp, there is a component that checks the filesystem for changes. I
was able to optimize this comparison quite a bit.

My original code fetched all files and folders, queried the database
and then compared the two using foreach loops. These become
exponentially slower as the number of files grow. Comparing 100 files
would result in 100*100 comparisons just to determine that nothing new
has been added.

::

    
    foreach ( $system as $sysFile ) {
    	foreach ( $data as $dataFile ) {
    		if ( $sysFile['EfsFile']['filename'] == $dataFile['EfsFile']['filename'] ) {
    			// OK, this file exists already
    		}
    	}
    }

The goal was to remove at least one of the foreach loops to keep the
speed increase in check. Since these are Cake-style multi-dimensional
arrays simple comparisons like array_diff() don't work. The Set class
was of-course a little slower than the foreach loops since it does
basically the same thing, only a bit more advanced.

Once again I found that putting more responsibility on MySQL was the
answer. My new code fetches all files and folders, loops through that
data and queries the database to see if a known file can be found.
Testing with about 3'000 files, I measured this code to be twice as
fast as the original code. Checking 3'000 files in 14 folders now
takes 4.5sec instead of 8.6sec.

::

    
    foreach ( $system as $sysFile ) {
    	$this->EfsFile->contain();
    	$found = $this->EfsFile->find('first', array(
    		'conditions' => array(
    			'EfsFile.filename'=> $sysFile['filename'],
    			'EfsFile.project_id'=> $current_project_id
    		)
    	));
    	if ( $found ) {
    		// OK, this file exists already
    	}
    }



Filesystem case sensitivity
~~~~~~~~~~~~~~~~~~~~~~~~~~~
I got this strange error while testing. Each sync with the filesystem
would result in duplicate files in a certain project. Turned out the
problem was one of case sensitivity. Some filesystems are case
sensitive, some are not. PHP definitely is case sensitive when doing
simple comparisons like $filename1 == $filename2. This can cause
problems when files and folders are added and renamed outside of my
application. Renaming a folder test => Test will still find the same
folder when on a case insensitive system but it will not be considered
the same folder by the sync code discussed above. I needed a way to
handle this problem. Here is what I came up with:
in config/bootstrap.php

::

    
    define('EFS_FILESYSTEM_TYPE', 'ci'); // filesystem is set to case insensitive

in vendors/basics.php (my file of small handy global functions)

::

    
    // returns a filename or foldername in the correct case for comparison
    function file_case($str) {
    	if ( EFS_FILESYSTEM_TYPE == 'ci' ) {
    		return low($str); // Filesystem in case insensitive
    	} else {
    		return $str; // Filesystem in case sensitive
    	}
    }

That is ok and it works. But how many of my customers actually know
what filesystem they are running? These are people who choose this app
for its simplicity. Can't I use some function to find the filesystem
type automatically? Well, yes I can. I looked for some internal php
mechanism but found none. There may be plento of smarter ways to do
this but here is what I did:
in vendors/basics.php (my file of small handy global functions)

::

    
    // returns a filename or foldername in the correct case for comparison
    function file_case($str) {
    	if ( !defined('EFS_FILESYSTEM_TYPE') ) {
    		$file_name = 'CaseTest.txt';
    		if ( is_file(dirname(__FILE__).DS.$file_name) && is_file(dirname(__FILE__).DS.low($file_name)) ) {
    			define('EFS_FILESYSTEM_TYPE', 'ci'); // Filesystem in case insensitive
    		} else {
    			define('EFS_FILESYSTEM_TYPE', 'cs'); // Filesystem in case sensitive
    		}
    	}
    	
    	if ( EFS_FILESYSTEM_TYPE == 'ci' ) {
    		return low($str); // Filesystem in case insensitive
    	} else {
    		return $str; // Filesystem in case sensitive
    	}
    }

I checks "the same file" twise. Once with the filename capitalized and
once in all lowercase. If they both exist then the filesystem should
be case insensitive. This works when tested on HFS+ volumes of both
types.



Re-factoring stupid noob code
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
A post on the CakePHP Google group got me thinking about a component I
coded for this application. It is (or was) a Ticket component for
maintaining temporary tickets of the type many websites use for
password resets and registration activations.

When I started looking at this old component I realized that the only
reason this was a component at all was vanity. I thought a component
would be "kewl" and really wanted to create a component. This was of-
course horribly wrong. The component may still be published here:
`http://bakery.cakephp.org/articles/view/ticket-component-resetting-
user-passwords`_
The component is really just a proxy to the Ticket model storing the
tickets. I re-factored the code in the component into the model and
included the model in the controllers that previously included the
component.

Unfortunately I had to rename most of the methods from the component
since they clashed with internal Model methods like set() and del().
What took the most time was finding new names for these methods. Out
of frustration I (temporarily) just called them setTicket() and
drop(). Ugly, yes. I hate putting the class name in the method name...
totally useless but my English vocabulary doesn't include a good
synonym.



l10n and i18n
~~~~~~~~~~~~~
The original application used my own code for language management. I
had a php-file for each language. All labels were defined in an
associative array in those files. Like this:

::

    
    $labels = array();
    
    //--  login page
    $labels['login_username']  = 'Login Id';
    $labels['login_password']  = 'Password';
    $labels['login_login']     = 'Login';

The first step was to convert these files into gettext po-files. It
was a matter of search and replace for 90% of it. Some comments had to
be moved and changed manually and things of this nature. The results
looked something like this:

::

    
    # login page
    msgid "login_username"
    msgstr "Login Id"
    
    msgid "login_password"
    msgstr "Password"
    
    msgid "login_login"
    msgstr "Login"

These files were put into the relevant folders. E.g.
locale/eng/LC_MESSAGES/default.po

The second step was to replace my own translation function with the
built-in __(). I have never used that function directly. This is
mainly since I prefer to have my strings returned and not output. I
put a proxy function into my bootstrap which alters the default of the
second argument.

::

    
    function ___($singular, $return = true) {
    	return __($singular, $return);
    }

I managed to change all the calls to $lang->show('some_string') to
___('some_string') in one glorious find and replace. That's my kind of
migration.

The third and final change was to alter the language switching. I kept
this in my bootstrap and only change it to write the configuration for
the selected language. The old code is the commented lines. I kept the
rest of my old code. As you can see it still uses cookies and it works
just as well as before.

::

    
    if (isset($_COOKIE['EFS_LANG'])) {
    	//define('EFS_LANG', $_COOKIE['EFS_LANG']);
    	Configure::write('Config.language', $_COOKIE['EFS_LANG']);
    } else {
    	//define('EFS_LANG', 'sv');
    	Configure::write('Config.language', 'sv');
    }



Changing all view-files to use .ctp extension
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
This takes all of 5 minutes and has absolutely no effect on the
functionality of the application. It just feels better and considering
the work involved why not? I just ran everything in the views folder
through a filename changer (NameChanger for Mac OS X) and I was done.



Changes to form and html helpers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Any previous call to html helper to generate a form element needs to
be switched over to use the form helper. This did not affect my
application much. Most html did not make use of either helper. I
remember feeling it was less intuitive to make php calls to generate
html instead of just writing the html directly. The application does
not have a lot of forms either.

With the login form as an example here is some of the things to look
out for in views.

::

    
    <label for="UserLogin"><?php echo $lang->show('login_username'); ?></label>
    <?php
    $html->input('User/login',array(
    	'style'=>"width:150px;"
    ));
    ?>

now reads:

::

    
    $form->input('User.email',array(
    	'label'=>___('login_username'), 
    	'div' => false, 
    	'style'=>'width:150px;'
    ));

This is one area where the new helpers really shine. Just setting the
label property IS simpler than writing the whole tag manually. since
my layout did not have divs before I simply set that key to false to
avoid the default div around my text field.

Also notice that $html is replaced by $form and that the reference to
the model field uses the new dot-notation.

I also switched to email logins, but that has nothing to do with the
migration. I started out using usernames. When I created the feature
to reset passwords a valid email address was necessary and the last
big update changed to prefer email logins but still allowed old
usernames during a transitional period. This version will deprecate
the old logins once and for all.



Don't render your elements
~~~~~~~~~~~~~~~~~~~~~~~~~~
Don't get me wrong. Keep on using your elements. It is just the name
of the method has changed from renderElement() to just element(). The
application was full of these but this was another one of those things
that a quick find and replace could solve in minutes. I did not find
any problems afterwards. The parameters appear to work the same.



Cleaning up my urls
~~~~~~~~~~~~~~~~~~~
Besides cleaning up some of my mess in config/routes.php I did some
spring-cleaning of the urls in my views and controllers. This is not
strictly necessary but it may come in handy if, or when, I want to
create some new custom routes. Changing all urls to arrays instead of
simple strings does look like a lot of complexity with little benefit.

::

    
    '/users/edit'

becomes:

::

    
    array('controller'=>'users', 'action'=>'edit')

Clearly harder to read, right? The benefit is that I can now decide to
create an "alias" in routes.php so that links to /profile should go to
the edit action of the users controller. As if by magic, all links and
redirects to that editing screen will change from /users/edit to
/profile. Cool!

Another little nice (new?) feature I found was for the classic "logo
links back to start-page". Every website in the world has this and it
can be done very nicely. This mess:

::

    
    <a href="<?php echo $html->url('/'); ?>"><?php echo $html->image('fileshifter_logo_bar.gif',array('alt'=>'Fileshifter logo'))?></a>

could be written like this:

::

    
    <?php echo $html->image('fileshifter_logo_bar.gif',array('alt'=>'Fileshifter logo', 'url'=>'/')); ?>

The image method accepts an url parameter and creates a link around
the image for you. It does look nice and clean. If you need some
attributes set for the link you can also use the link method and just
put a call to the image method where the "text label" for the link is
set. See the manual, it has a clear example of it. It is a lot simpler
than I make it sound.



Consolidating layouts and views.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Some of my old view code was duplicated because I did not know all the
tricks back then. For example, my layouts (three of them) were almost
identical but they has a few things that set them apart.

Since data for the current user is always available to the view, one
simple fix was to add an if-clause that would output the correct menu-
items for each situation.

The other problem I had was that $content_for_layout had to be wrapped
and accompanied by different code for different sections of the
application. The fix is simple but not one I knew about two years ago.
I made a common base-layout:

::

    
    --base.ctp--
    ...lots of html for menu and header...
    
    <?php echo $content_for_layout; ?>
    
    ...some more html for the footer...

And then I created two other layouts of this kind (this is the most
simple one):

::

    
    --default.ctp--
    <?php
    echo $this->renderLayout('<div id="container">'.$content_for_layout.'</div>','base');
    ?>

This layout simply wraps the content in a container div and passes it
along to the base layout. The other layout had a lot more html around
the content but that would be less clear to display like this.



Onwards and upwards
~~~~~~~~~~~~~~~~~~~
Those were the steps I took to migrate and improve my application this
time around. Not all of them are necessary but all of them help the
application improve and conform to new conventions. For example, old
string conditions still work but using the new array notation lets
Cake protect you from SQL injection, makes it easier to modify
conditions in behaviors and beforeFind() and other advantages big and
small.

If you find that any aspect of the migration is lacking in detail or
plain missing, please don't hesitate to drop me a line in the
comments. Hopefully I will be able to cover such requests in future
updates.



.. _http://www.fileshifter.se/: http://www.fileshifter.se/
.. _https://trac.cakephp.org/ticket/5579: https://trac.cakephp.org/ticket/5579
.. _http://bakery.cakephp.org/articles/view/ticket-component-resetting-user-passwords: http://bakery.cakephp.org/articles/view/ticket-component-resetting-user-passwords
.. meta::
    :title: Migrating a real-world application to CakePHP 1.2
    :description: CakePHP Article related to optimization,migration,Case Studies
    :keywords: optimization,migration,Case Studies
    :copyright: Copyright 2008 
    :category: case_studies

