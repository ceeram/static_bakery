

PippoAcl 0.3 ready to use Acl/Auth plugin
=========================================

by %s on June 20, 2009

A simple but powerful Auth/Acl plugin.


Introduction
~~~~~~~~~~~~

I see many post around the web about acl/auth so i made a plugin,
ready to use or to learn from.


What does it do?
~~~~~~~~~~~~~~~~

Basically with this plugin you can have an application controlled by
auth/acl.


Feature
~~~~~~~

Ajax based acl permission.
Simple search form i made with autocompletion.
Highlight of tables row.
Autocomplete search form field.
Internationalization
Mail based registration
Password forgot and reset by mail.
Simple profile editor.
A function to clean up acl.
Menu based on acl.
some i can't remember :)


Download
~~~~~~~~

`http://sourceforge.net/projects/pippoacl/ `_

Installation
~~~~~~~~~~~~

To install just extract 'pippoacl' folder in your plugins directory.
Execute the sql code located in pippoacl/docs/sql/pippoacl.sql into
your db.
Next if you want that your application is acl/auth controlled, add
this to your app_controller.php


Controller Class:
`````````````````

::

    <?php 
    var $helpers = array('Html','Ajax','Javascript');
    	var $components      = array('Acl', 'Auth', 'RequestHandler','Cookie');
    	
    	
        
        function beforeFilter()
        {
    		$this->Auth->authorize = 'actions';
            $this->Auth->loginAction = array('plugin'=>'pippoacl','controller' => 'users', 'action' => 'login');
            $this->Auth->logoutRedirect = array('plugin'=>'pippoacl','controller' => 'users', 'action' => 'login');
    		
    		//action that doesn't need have an acl all can access to this
            $this->Auth->allowedActions = array('display','logout','login','register', 'confirm', 'forget', 'activate', 'reset','profile','switch_language');
    		
    		//user needs to be active.
    		$this->Auth->userScope = array('User.active' => 1);
    		
    		
    		
    		//add this if you want to localize your application
    		 # Localization
            App::import('Core', 'l10n');
            $this->L10n = new L10n();
            # if language is already set in session, get that
            $language = $this->Session->read('Config.language');
    		
            if(!$language) {
    			$this->Session->write('Config.language', $language);
            }
            # now set the language
            $this->L10n->get($language);
    
            setlocale(LC_ALL, 
                substr($this->L10n->locale, 0, 3) .
                strtoupper(substr($this->L10n->locale, 3, 2)) . 
                '.' . $this->L10n->charset
            );
    		
        }
    ?>


Pippoacl have some routes in pippoacl/config/routes.php, copy and
paste or add this line to your routes.php:

::

    
     include(APP.'plugins'.DS.'pippoacl'.DS.'config'.DS.'routes.php');

Add this to your default.ctp or your default view if you wanna use the
js and css bundled in the plugin:

::

    
    <?php echo $javascript->link('/pippoacl/js/prototype');?>
    <?php echo $javascript->link('/pippoacl/js/scriptaculous.js?load=effects');?>
    <?php echo $javascript->link('/pippoacl/js/controls');?>
    <?php echo $javascript->link('/pippoacl/js/table');?>
    <?php echo $html->css('/pippoacl/css/pippo');?>

or copy the folder inside pippoacl/vendor directory into your
app/webroot folder and then add them in ypur default.ctp/default view
layout like:

::

    
    <?php echo $javascript->link('prototype');?>
    <?php echo $javascript->link('scriptaculous.js?load=effects');?>
    <?php echo $javascript->link('controls');?>
    <?php echo $javascript->link('table');?>
    <?php echo $html->css('pippo');?>

remember to change in pippoacl/controllers/users_controller.php this
line:


Controller Class:
`````````````````

::

    <?php  
    	var $activationEmail = 'Activation <some@mail.it>';
    	var $activationSubject = 'Activate Your Account';
    	var $forgetEmail = 'Forget Email <some@mail.it>';
    	var $forgetSubject = 'Forgot Password';
    	var $resetEmail = 'Reset Password <some@mail.it>';
    	var $resetSubject = 'Reset Password';
    ?>


they are used to email notification.

also remember to change line 211 in users_controller.php:

Controller Class:
`````````````````

::

    <?php  
    	//set default role
    	$this->User->saveField('role_id',1);
    ?>

to set the default role associated to new user, in my example 1 =
administrator.

call the url http://yourserver/roles/cleanupAcl to initialize/cleanup
the acl for your controller.
login with username= admin passwd = admin

Ok the plugin now is installed and here's some basic function used
inside, next i will explain how to improve integrate your application
with some cool stuff contained in pippoacl.

Basic function/action (if u use pippoacl's routes.php of course):

/users/index - list of user
/users/login - login function
/users/logout - logout function
/users/register - simple form to register to your site
/users/forget - password forget form, to reset it, an email will be
sent to the user or email address with the reset code
/users/reset/code - to reset the password only works with code
/users/activate - action for activating user
/users/profile - simple users's profile, if logged ofc

/roles/index - list of roles
/roles/acl - ajax based acl management
/roles/cleanupAcl - used for automatic clean/delete of the acl

all the mail layout can be modified, they are stored in
pippoacl/views/elements/email folder.

In next page i will add some customization u can do.

.. _http://sourceforge.net/projects/pippoacl/ : http://sourceforge.net/projects/pippoacl/
.. meta::
    :title: PippoAcl 0.3 ready to use Acl/Auth plugin
    :description: CakePHP Article related to acl,Auth,pippoacl,Plugins
    :keywords: acl,Auth,pippoacl,Plugins
    :copyright: Copyright 2009 
    :category: plugins

