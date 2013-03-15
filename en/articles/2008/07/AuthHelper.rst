AuthHelper
==========

by %s on July 29, 2008

This AuthHelper provide access to user information on view side. If
you have used OthAuth helper before, this helper replicate exactly the
same functionalities.
Create a file in app/view/helpers/ with file name as auth.php. paste
the below code in auth.php

Helper Class:
`````````````

::

    <?php 
    <?php 
    
    /*
     *This helper provide access to user parameters such as username, id, etc., of currently logged in user
     *Send comments and feature request to ragrawal at gmail dot com
     *@author - Ritesh Agrawal
     *@version 0.1.0 
     *@license MIT
     */
    class AuthHelper extends Helper {
    	var $hashKey = null;
    	var $initialized = false;
    	var $helpers = array ('Session');
    	
    	/**
    	 * Initialize AuthHelper
    	 */
    	function init() {		
    		if (!$this->initialized) {
    			if (!isset ($this->view)) {
    				$this->view = & ClassRegistry :: getObject('view');
    			}
    			if (!empty ($this->view->viewVars['authSessionKey'])) {
    				$this->hashKey = $this->view->viewVars['authSessionKey'];
    			}
    			$this->initialized = true;
    		}
    	}
    
    	/***
    	 * Check whether a user is logged in or not
    	 */
    	function sessionValid(){
    		$this->init();
    		return ($this->Session->check($this->hashKey));
    	}
    
    	/*
    	 * Retrieve user information
    	 *@param string $key - user table field such as username, id, etc.
    	 *@return  if valid then then value otherwise false;
    	 */
    	function user($key) {
    		$this->init();
    		// does session exists
    		if ($this->sessionValid()) {
    			$user = $this->Session->read($this->hashKey);
    			if (isset($user[$key])) {
    				return $user[$key];
    			}
    		}
    		return false;
    	}
    
    }
    ?>
    ?>

Setting up helper In AppController, copy and paste the below code. The
only thing to note is initAuth function. AuthHelper requires access to
sessionkey used by Auth Component. In initAuth function, we explicit
tell Auth Component to use a particular sessionKey. This is important
to because, by default, Auth Component uses Auth.{$userModel} as the
session Key but leaves the session key variable (sessionKey) to null.
So if you call $this->Auth->sessionKey without explicitly setting
sessionKey, you will recieve null value. To overcome this incosistency
in sessionKey, we explicitly set the sessionKey and pass it to view
through a variable name "authSessionKey". AuthHelper uses this
variable to fetch sessionKey information.


Controller Class:
`````````````````

::

    <?php 
    var $components  = array('Auth');
    var $helpers = array('Auth');
    
    function beforeFilter(){ 
        //Set Authentication System
        $this->initAuth();
    } 
        
    /**
     * Setup Authentication Component
    */
    protected function initAuth(){
       $this->Auth->sessionKey = 'SomeRandomStringValue';
       $this->set('authSessionKey', $this->Auth->sessionKey);
    } 
    
    ?>

How to use AuthHelper To use AuthHelper in your views and to retrieve
username, use the below syntax

View Template:
``````````````

::

     $auth->user('username'); 

Similarly, if you want to check whether user is logged in or not, use
the below syntax

View Template:
``````````````

::

     $auth->sessionValid(); 


Tip:
If you are migrating from OthAuth to Auth component, then checkout my
blog post which describes in details migration steps and uses above
AuthHelper to minimize changes in the code
`http://ragrawal.wordpress.com/2008/07/01/migrating-from-othauth-to-
cakephp-auth/`_


.. _http://ragrawal.wordpress.com/2008/07/01/migrating-from-othauth-to-cakephp-auth/: http://ragrawal.wordpress.com/2008/07/01/migrating-from-othauth-to-cakephp-auth/
.. meta::
    :title: AuthHelper
    :description: CakePHP Article related to authentication,Helpers
    :keywords: authentication,Helpers
    :copyright: Copyright 2008 
    :category: helpers

