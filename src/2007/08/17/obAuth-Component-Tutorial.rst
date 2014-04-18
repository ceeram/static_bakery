obAuth Component Tutorial
=========================

This is a tutorial on how to use obAuth component:
http://bakery.cakephp.org/articles/view/130
This is a simple authentication component that I wrote as a quick way
to protect areas of your web application. I wanted manual control of
what permissions are granted to what action. Something as simple as
this:

::

    <?php $this->obAuth->lock(array(1)); ?>


At the top of my action should mean only users belonging to usergroup
1 are allowed to use this action.

Before we begin this tutorial, it's important to note that this
component is in it's alpha stages. I would love feedback from some of
the more experienced bakers out there. Enjoy

1. Download the component
2. Recommended database tables
3. Include the component and create models
4. Required actions
5. Start securing our private areas!
6. Extra Tips & Tricks


1. Download the component
`````````````````````````

Get the component here: `http://bakery.cakephp.org/articles/view/130`_


2. Recommended database tables
``````````````````````````````

Essentially you'll want a users table and a groups table. The users
table will hold the regular user data and a foreign key called
"group_id". The groups table is where you store your groups. Your
tables should look like the following:

::

    
    -- --------------------------------------------------------
    
    -- 
    -- Table structure for table `groups`
    -- 
    
    CREATE TABLE `groups` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `name` varchar(50) NOT NULL default '',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT;
    
    -- 
    -- Dumping data for table `groups`
    -- 
    
    INSERT INTO `groups` (`id`, `name`, `created`, `modified`) VALUES (1, 'Member', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
    INSERT INTO `groups` (`id`, `name`, `created`, `modified`) VALUES (2, 'Admin', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
    
    -- --------------------------------------------------------
    
    -- 
    -- Table structure for table `users`
    -- 
    
    CREATE TABLE `users` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `username` varchar(50) NOT NULL default '',
      `password` varchar(32) NOT NULL default '',
      `fname` varchar(50) NOT NULL,
      `lname` varchar(50) NOT NULL,
      `email` varchar(100) NOT NULL default '',
      `group_id` int(10) unsigned NOT NULL default '0',
      `active` tinyint(1) unsigned NOT NULL default '0',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`),
      KEY `group_id` (`group_id`)
    ) ENGINE=InnoDB DEFAULT;


You can do whatever you want with your users table as long as the
fields id, group_id, username, password and active are there


3. Include the component and create models
``````````````````````````````````````````

Ok, this is relatively easy. Once you've downloaded the component you
need to place it in your /app/controllers/components directory. Once
you've placed it in the right directory you need to call it in your
controller file. So let's use posts_controller.php as an example:


Model Class:
````````````

::

    <?php 
    class PostsController extends AppController 
    {
        var $name = 'Posts';
        var $components = array("obAuth");
    }
    ?>


Next you should have a User model and a Group model with the proper
associations. Users belong to a group and groups has many users.
Please refer to this tutorial if you don't understand how to setup
associations go here:
`http://wiki.cakephp.org/docs:understanding_associations`_

That's it. You can now start using the obAuth component.


4. Required actions
```````````````````

To begin using the obAuth component we need to have to important
actions. "Login" and "Logout". They are self-explanatory, so I don't
need to tell you what they're for. Instead I'll show you how mine
look:

In /app/controllers/users_controller.php

::

    
    <?php
    function login()
    {
        if(isset($this->data['User']))
        {
            if($this->obAuth->login($this->data['User']))
            {
                $this->redirect('/users');
            }
            $this->flash("Username/Password is incorrect");
        }
    }
    
    function logout()
    {
        $this->obAuth->lock();
        $this->obAuth->logout();
        $this->flash('You are now logged out.');
        $this->redirect('/');
    }
    ?>


Notice I use a method obAuth::login($data). This method is used to
check the data posted with a user in the database. If one exists then
the user will be authenticated

Now for the view you really only need one for the login action because
logout just redirects. So the view should look something like this


View Template:
``````````````

::

    
    <?php echo $html->formTag('/users/login')?>
    <fieldset>
        <legend>User Login</legend>
        
            <label for="username">Username: </label>
            <?php echo $html->input('User/username', array('style' => 'width: 150px'))?><br />
        
            <label for="password">Password: </label>
            <?php echo $html->password('User/password', array('style' => 'width: 150px'))?><br />
        
            <label for="submit"> </label><br />
            <?php echo $html->submit('Sign In')?>    
    </fieldset>
    </form>



5. Start securing your private areas
````````````````````````````````````

Alright, now that we have our login and logout actions we can get
users authenticated. So let's start securing our actions by setting
the permissions. Let's say we have an action in our Posts controller
called "add" and we only want users from the group id "3" allowed to
use it. Well this is all we need to do to secure the action

::

    
    <?php
    function add()
    {
        $this->obAuth->lock(array(3)); // Only users with the group_id '3' are allowed here
    
        if (!empty($this->data))
        {
            if ($this->Post->save($this->data))
            {
                $this->flash('Your post has been saved.','/posts');
            }
        }
    }
    ?>


Now notice that 1 line of code used to secure this action. It uses
obAuth::lock($groups=null, $redirect=null). The array passed is an
array of group ids allowed to access this action. So if you had Super
Administrators (3) and Administrators (2) and wanted them to have
access to this action you would write:

::

    $this->obAuth->lock(array(2,3));.


If you leave $groups parameter empty then it'll allow any
AUTHENTICATED user to access the action. So you're still securing it
from anonymous visitors. The $redirect parameter allows you set a url
that will redirect a user who's not allowed to access the action. If
you leave it empty it will redirect the user to the login page.


.. _http://wiki.cakephp.org/docs:understanding_associations: http://wiki.cakephp.org/docs:understanding_associations
.. _http://bakery.cakephp.org/articles/view/130: http://bakery.cakephp.org/articles/view/130

.. author:: coeus
.. categories:: articles, tutorials
.. tags:: permission,authentication,component,obAuth,Tutorials

