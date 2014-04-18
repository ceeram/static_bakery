EmailComponent in a (cake) Shell
================================

This little Shell Task will allow you to use the EmailComponent from
your shell applications like you normally do from WWW.


The problem
```````````
How can I use the kickass EmailComponent from my Shell?


The solution
````````````
EmailTask


The description
```````````````
This little Shell Task will allow you to use the EmailComponent from
your shell applications like you normally do from WWW.

The code should pretty much explain itself. Below is a simple use
example:


Demo
````
Put this code in APP/vendors/shells/my.php

MAKE SURE TO CHANGE THE EMAIL ADDRESSES TO VALID ONES

PHP Snippet:
````````````

::

    <?php 
    class MyShell extends Shell {
    /**
     * List of tasks for this shell
     *
     * @var array
     */
        var $tasks = array('Email');
    
    /**
     * Email task
     *
     * @var EmailTask
     */
        var $Email;
    
    /**
     * Startup method for the shell
     *
     * Lets set some default params for the EmailTask
     *
     */
        function startup() {
            $this->Email->settings(array(
                'from' => 'Me <my@mail.com>',
                'template' => 'test'
            ));
        }
    
    /**
     * Send just one email
     *
     */
        function sendMeAnEmail() {
            return $this->Email->send(array(
                'to' => 'some@mail.com',
                'subject' => 'Talking to myself'
            ));
        }
    
    /**
     * Send multiple emails, change a few variables on the fly
     * and test that we can 'set' variables to the view
     *
     */
        function sendMyFriendsAnEmail() {
            $myFriends = array('fake@mail.com', 'fake@mail.box', 'fake@user.com');
            $this->Email->settings(array('subject' => 'Hello friends'));
            foreach ($myFriends AS $friend) {
                $this->Email->set('someVar', $friend);
                $this->Email->send(array(
                    'to' => $friend, 
                    'subject' => 'Hello ' . $friend
                ));
            }
        }
    }
    ?>



Code
````
Put this code in APP/vendors/shells/tasks/email.php


PHP Snippet:
````````````

::

    <?php 
    App::import('Core', 'Controller');
    App::import('Component', 'Email');
    
    class EmailTask extends Shell {
    /**
    * Controller class
    *
    * @var Controller
    */
        var $Controller;
    
    /**
    * EmailComponent
    *
    * @var EmailComponent
    */
        var $Email;
    
    /**
    * List of default variables for EmailComponent
    *
    * @var array
    */
        var $defaults = array(
            'to'        => null,
            'subject'   => null,
            'charset'   => 'UTF-8',
            'from'      => null,
            'sendAs'    => 'html',
            'template'  => null,
            'debug'     => false,
            'additionalParams'    => '',
            'layout'    => 'default'
        );
    
    /**
    * Startup for the EmailTask
    *
    */
        function initialize() {
            $this->Controller =& new Controller();
            $this->Email =& new EmailComponent(null);
            $this->Email->startup($this->Controller);
        }
    
    /**
    * Send an email useing the EmailComponent
    *
    * @param array $settings
    * @return boolean
    */
        function send($settings = array()) {
            $this->settings($settings);
            return $this->Email->send();
        }
    
    /**
    * Used to set view vars to the Controller so
    * that they will be available when the view render
    * the template
    *
    * @param string $name
    * @param mixed $data
    */
        function set($name, $data) {
            $this->Controller->set($name, $data);
        }
    
    /**
    * Change default variables
    * Fancy if you want to send many emails and only want
    * to change 'from' or few keys
    *
    * @param array $settings
    */
        function settings($settings = array()) {
            $this->Email->_set($this->defaults = array_filter(am($this->defaults, $settings)));
        }
    }
    ?>



Watch the magic
```````````````
Make sure you have created a .ctp file in
APP/views/elements/email/html/test.ctp

Make sure you have created a .ctp file in
APP/views/layouts/email/html/default.ctp

Go into the CAKE/console directory and execute:
(Windows) cake.bat my sendMyFriendsAnEmail
(Linux) ./cake my sendMyFriendsAnEmail

Thanks to gwoo for feedback and on this little snippet and
mariano_iglesias for proofing :)


.. author:: Jippi
.. categories:: articles, tutorials
.. tags:: shell,email component,cake console email t,Tutorials

