

Creating a Custom Shell for Adding Users for Use With AuthComponent
===================================================================

by %s on August 26, 2008

AuthComponent is great and there are tutorials for letting users
register an account. But what if you just want a convenient way to add
a few admin users? This custom shell will help you!
The AuthComponent guide at
`http://manual.cakephp.org/view/172/authentication`_ is very useful.
Read it. I have a simple admin tool that uses AuthComponent. I also
want a convenient way to create admin users, though and I surely don't
want to allow everyone to register their own admin account. So I wrote
a custom shell. This shell assumes that you have a User model like
this:


Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	var $name = 'User';
    }
    ?>

It should be backed by a database table named 'users' with this
layout:

::

    
    CREATE TABLE users (   
      id integer auto_increment,    
      username char(50),    
      password char(50),    
      PRIMARY KEY (id)
    );

To create users, put this custom shell code into the file
app/vendors/shells/create_user.php:

::

    
    <?php 
    class CreateUserShell extends Shell {
        var $uses = array('User');
    
        function main() {
            App::import('Component','Auth');
            $this->Auth = new AuthComponent(null);
          
            $this->out('Create Admin User:');
            $this->hr();
            
            while (empty($username)) {
              $username = $this->in('Username:');
              if (empty($username)) $this->out('Username must not be empty!');
            }
            
            while (empty($pwd1)) {
              $pwd1 = $this->in('Password:');
              if (empty($pwd1)) $this->out('Password must not be empty!');
            }
            
            while (empty($pwd2)) {
              $pwd2 = $this->in('Password Confirmation:');
              if ($pwd1 !== $pwd2) {
                $this->out('Passwort and confirmation do not match!');
                $pwd2 = NULL;
              }
            }
            
            // we got all the data, let's create the user        
            $this->User->create();
      			if ($this->User->save(array('username' => $username, 'password' => $this->Auth->password($pwd1)))) {
      				$this->out('Admin User created successfully!');
      			} else {
      				$this->out('ERROR while creating the Admin User!!!');
      			}
        }
    }
    ?>

That's it, now you can run your spiffy new shell script like so:

::

    
    cake/console/cake create_user

Enjoy!

For more code goodness, visit my blog at
`http://blog.springenwerk.com`_.



.. _http://blog.springenwerk.com: http://blog.springenwerk.com/
.. _http://manual.cakephp.org/view/172/authentication: http://manual.cakephp.org/view/172/authentication
.. meta::
    :title: Creating a Custom Shell for Adding Users for Use With AuthComponent
    :description: CakePHP Article related to ,Components
    :keywords: ,Components
    :copyright: Copyright 2008 
    :category: components

