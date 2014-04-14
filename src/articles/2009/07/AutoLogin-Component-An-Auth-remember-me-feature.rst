AutoLogin Component - An Auth remember me feature
=================================================

by milesj on July 05, 2009

A user can save their login information by ticking off a checkbox in
the login form and AutoLogin will store their information in a cookie
to automatically log them in (using the Auth Component) on their next
visit.
Below are a list of features present within the AutoLogin. Also thanks
to all the other authors with similar scripts.

* Requires no installation except for adding the checkbox into your
user login forms
* Automatically saves the cookie and info when a user logs in
* Automatically kills the cookie and session when a user logs out
* Inserts a hash within the cookie so that it cannot be hijacked
* Encrypts the cookie so the information cannot be harvested
* Configuration options for cookie name and length
* Functionality for additional user updating or error logging


Code
~~~~
`You can view the original documentation and newer versions here!`_

Component Class:
````````````````

::

    <?php 
    /** 
     * auto_login.php
     *
     * A CakePHP Component that will automatically login the Auth session for a duration if the user requested to (saves data to cookies). 
     *
     * Copyright 2006-2009, Miles Johnson - www.milesj.me
     * Licensed under The MIT License - Modification, Redistribution allowed but must retain the above copyright notice
     * @link 		http://www.opensource.org/licenses/mit-license.php
     *
     * @package		AutoLogin Component
     * @created		May 27th 2009
     * @version 	1.4
     * @link		www.milesj.me/resources/script/auto-login
     * @changelog	www.milesj.me/files/logs/auto-login 
     */
    
    class AutoLoginComponent extends Object {
    
    	/**
    	 * Current version: www.milesj.me/files/logs/auto-login
    	 * @var string
    	 */
    	var $version = '1.4';
    
    	/**
    	 * Components
    	 * @var array 
    	 */
    	var $components = array('Cookie');
    	
    	/**
    	 * Cookie name 
    	 * @var string
    	 */
    	var $cookieName = 'autoLogin';
    	
    	/**
    	 * Cookie length (strtotime())
    	 * @var string
    	 */
    	var $expires = '+2 weeks';   
    	
    	/**
    	 * Settings
    	 * @var array
    	 */
    	var $settings = array(
    		'controller' => '',
    		'loginAction' => '',
    		'logoutAction' => ''
    	);
    	
    	/**
    	 * Automatically login existent Auth session; called after controllers beforeFilter() so that Auth is initialized
    	 * @param object $Controller 
    	 * @return void 
    	 */
    	function startup(&$Controller) { 
    		$cookie = $this->Cookie->read($this->cookieName);   
    		
    		if (!is_array($cookie) || $Controller->Auth->user()) {
    			return;
    		}
    		
    		if ($cookie['hash'] != $Controller->Auth->password($cookie[$Controller->Auth->fields['username']] . $cookie['time'])) {
    			$this->delete();
    			return;
    		}
    
    		if ($Controller->Auth->login($cookie)) {
    			if (in_array('_autoLogin', get_class_methods($Controller))) {
    				call_user_func_array(array(&$Controller, '_autoLogin'), array($Controller->Auth->user()));
    			}
    		} else {
    			if (in_array('_autoLoginError', get_class_methods($Controller))) {
    				call_user_func_array(array(&$Controller, '_autoLoginError'), array($cookie));
    			}
    		}
    		
    		return true;
    	}
    	
    	/**
    	 * Automatically process logic when hitting login/logout actions
    	 * @param object $Controller  
    	 * @param array $url
    	 * @param boolean $status
    	 * @param boolean $exit
    	 * @return void
    	 */
    	function beforeRedirect(&$Controller, $url, $status = null, $exit = true) { 
    		$controller 	= $this->settings['controller'];
    		$loginAction 	= $this->settings['loginAction'];
    		$logoutAction 	= $this->settings['logoutAction'];
    		
    		if (is_array($Controller->Auth->loginAction)) {
    			if (!empty($Controller->Auth->loginAction['controller'])) {
    				$controller = Inflector::camelize($Controller->Auth->loginAction['controller']);
    			}
    			
    			if (!empty($Controller->Auth->loginAction['action'])) {
    				$loginAction = $Controller->Auth->loginAction['action'];
    			}
    		}
    		
    		if (!empty($Controller->Auth->userModel) && empty($controller)) {
    			$controller = Inflector::pluralize($Controller->Auth->userModel);
    		}
    		
    		if (empty($loginAction)) {
    			$loginAction = 'login';
    		}
    		
    		if (empty($logoutAction)) {
    			$logoutAction = 'logout';
    		}
    		
    		// Is called after user login/logout validates, but befire auth redirects
    		if ($Controller->name == $controller) {
    			$data = $Controller->data;
    			
    			switch ($Controller->action) {
    				case $loginAction:
    					$username = $data[$Controller->Auth->userModel][$Controller->Auth->fields['username']];
    					$password = $data[$Controller->Auth->userModel][$Controller->Auth->fields['password']];
    					
    					if (!empty($username) && !empty($password) && $data[$Controller->Auth->userModel]['auto_login'] == 1) {
    						$this->save($username, $password, $Controller);
    					} else if ($data[$Controller->Auth->userModel]['auto_login'] == 0) {
    						$this->delete();
    					}
    				break;
    				
    				case $logoutAction:
    					$this->delete();
    				break;
    			}
    		}
    	}
    
    	/**
    	 * Remember the user information
    	 * @param string $username
    	 * @param string $password
    	 * @param object $Controller
    	 * @return void
    	 */
    	function save($username, $password, $Controller) {
    		$time = time();
    		$cookie = array();
    		$cookie[$Controller->Auth->fields['username']] = $username;
    		$cookie[$Controller->Auth->fields['password']] = $password; // already hashed from auth
    		$cookie['hash'] = $Controller->Auth->password($username . $time);
    		$cookie['time'] = $time;
    		
    		$this->Cookie->write($this->cookieName, $cookie, true, $this->expires);
    	}
    
    	/**
    	 * Delete the cookie
    	 * @return void
    	 */
    	function delete() {
    		$this->Cookie->del($this->cookieName);
    	}
    	
    }?>



Installation
~~~~~~~~~~~~
If you haven't already, grab the script above and place the code in a
file called auto_login.php within your app/controllers/components/
folder. Once you have done that, simply add AutoLogin into your
controllers $components property. AutoLogin must be placed before Auth
in the $components array or it will not work properly.

::

    var $components = array('AutoLogin', 'Auth');

The AutoLogin component will automatically save the user info to a
cookie when they login at users/login/. It also works when logging out
at users/logout/, by removing the cookie.

The final step is to create a checkbox in your login form named
auto_login. The model used in the form should also match the User
model you are using in your Auth.

::

    <?php echo $form->input('auto_login', array('type' => 'checkbox', 'label' => 'Log me in automatically?')); ?>



Configuration
~~~~~~~~~~~~~
If you would like to change the name of the cookie, or the duration
until the cookie expires (defaults to 2 weeks), you can change it in
your AppController's beforeFilter().

::

    <?php
    function beforeFilter() {
        $this->AutoLogin->cookieName = 'rememberMe';
        $this->AutoLogin->expires = '+1 month';
    }

If for some reason the controller name and the login/logout action
names are not default (whats based in Auth), you can change them in
the $settings array (in beforeFilter() of course).

::

    <?php
    $this->AutoLogin->settings = array(
    	'controller' => 'Members',
        'loginAction' => 'signin',
        'logoutAction' => 'signout'
    );



Adding your own logic or logging
````````````````````````````````
If you need to do additional logging and updating that is not
initially in Auths user login (for example updating a users last login
time), you can place this extra code in a method called _autoLogin()
within your AppController. Also if Auth login fails, you can do some
error logging and reporting by creating a method called
_autoLoginError(). Both of these will be called automatically and only
if the method exists.

::

    <?php
    class AppController extends Controller {
    
        /**
         * Run whenever auto login is successful
         * @param array $user - The Auth user session
         * @access private
         */
        function _autoLogin($user) {
        }
        
        /**
         * Run whenever auto login fails
         * @param array $cookie - The login cookie data
         * @access private
         */
        function _autoLoginError($cookie) {
        }
        
    }



.. _You can view the original documentation and newer versions here!: http://www.milesj.me/resources/script/auto-login
.. meta::
    :title: AutoLogin Component - An Auth remember me feature
    :description: CakePHP Article related to Auth,login,session,component,autologin,milesj,cookie,Components
    :keywords: Auth,login,session,component,autologin,milesj,cookie,Components
    :copyright: Copyright 2009 milesj
    :category: components

