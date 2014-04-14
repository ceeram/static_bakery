mxAuth - Tutorial
=================

by medianetix on April 28, 2009

This is the tutorial for the component mxAuth. It explains in a step-
by-step manner the usage of the component.


Downloading the component
~~~~~~~~~~~~~~~~~~~~~~~~~

Download the component `...`_ and store it under the
/app/controllers/components/mx_auth.php.

Database tables
~~~~~~~~~~~~~~~

Then we need two database tables. The table "users" contains the users
and "groups" the related groups. We populate it with two groups (admin
and customer). You need to populate the users table as well. Remember
that the password field contains the hash of the password and not the
plaintext password.

::

    
    -- 
    -- Table structure for table `groups`
    -- 
    
    CREATE TABLE `groups` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `name` varchar(50) NOT NULL default '',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    );
    
    -- 
    -- Dumping data for table `groups`
    -- 
    
    INSERT INTO `groups` (`id`, `name`, `created`, `modified`) VALUES (1, 'Admin', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
    INSERT INTO `groups` (`id`, `name`, `created`, `modified`) VALUES (2, 'Customer', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
    
    -- --------------------------------------------------------
    
    -- 
    -- Table structure for table `users`
    -- 
    
    CREATE TABLE `users` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `username` varchar(50) NOT NULL default '',
      `password` varchar(32) NOT NULL default '',
      `active` tinyint(1) unsigned NOT NULL default '0',
      `group_id` int(10) unsigned NOT NULL default '0',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
    
      `fname` varchar(50) NOT NULL,
      `lname` varchar(50) NOT NULL,
      `email` varchar(100) NOT NULL default '',
      PRIMARY KEY  (`id`),
      KEY `group_id` (`group_id`)
    );

Mandatory fields for groups are:

+ id
+ name

Mandatory fields for users are:

+ id
+ username
+ password
+ active
+ group_id

Both tables may be extended with other fields (e.g. fname, lname,
email, created, modified,..).

The Users Controller
~~~~~~~~~~~~~~~~~~~~

The Users controller with the needed methods "login()" and "logout()".
The other controller methods can be used by scaffolding (remove
comment from var $scaffold) or bake it via the cake shell.
/app/controllers/users.php:

Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController {
    
    	var $name = 'Users';
    	var $helpers = array('Html', 'Form');
        var $components = array('mxAuth');
        # var $scaffold; // activate scaffolding 
    
        function login()
        {
            if (!empty($this->data['User']['return_path'])){
                // set return_path from data if set (in case of login errors)
                $this->set('return_path', $this->data['User']['return_path']);
            } else {
                // we use the passed args to built the return_path
                $this->set('return_path', '/' . implode('/', $this->params['pass']));
            }
    
            if(isset($this->data['User']))
            {
                if($this->mxAuth->login($this->data['User']))
                {
                    if (!empty($this->data['User']['return_path'])) {
                        // redir to calling page
                        $path = $this->data['User']['return_path'];
                    } else {
                        // default redir after login
                        $path = $this->mxAuth->login_redir;
                    }
                    $this->redirect($path);
                    exit;
                } else {
                    $this->Session->setFlash(__("Username/Password is incorrect"));
                }
            }
        }
        
        function logout()
        {
            $this->mxAuth->logout();
            $this->Session->setFlash(__('You are now logged out.'));
            $this->redirect($this->mxAuth->logout_page);
            exit;
        } 
    
    
    // ... index, add, etc. are stripped
    ?>


Login view
~~~~~~~~~~

/app/views/users/login.ctp:

View Template:
``````````````

::

    
    <?php echo $form->create('User', array('action'=>'login')); ?>
    <fieldset>
        <legend>User Login</legend>
            <?php echo $form->hidden('return_path', array('value'=>$return_path)); ?>
            <?php echo $form->input('username', array('style' => 'width: 150px')); ?><br />
            <?php echo $form->input('password', array('style' => 'width: 150px')); ?><br />
            <?php echo $form->end('Sign In'); ?>    
    </fieldset>
    </form> 

When a controller calls the allow()-method, the actual path is added
to the login url - so that it is handled as passed args. If for
example /posts/edit is protected with allow() the login url will look
like this: /users/login/posts/edit

May look a bit archaic, but it works with admin routing as well. A
call to allow() from /admin/posts/edit will result in the login url
/users/login/admin/posts/edit and perform a redirect.
The users controller takes over and build the "return_path" from the
passed args and stores it in the login form as hidden field. After
successful login the user is transferred to the "return_path".

...to be finished..
~~~~~~~~~~~~~~~~~~~

soon


.. _...: :///home/marc/public_html/bakery.cakephp.org/bakery/tmp/...
.. meta::
    :title: mxAuth - Tutorial
    :description: CakePHP Article related to authentication,tutorial,mxauth,Tutorials
    :keywords: authentication,tutorial,mxauth,Tutorials
    :copyright: Copyright 2009 medianetix
    :category: tutorials

