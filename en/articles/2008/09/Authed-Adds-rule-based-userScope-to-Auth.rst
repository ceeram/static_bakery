

Authed - Adds rule based userScope to Auth
==========================================

by %s on September 21, 2008

If you ever thought it would be nice to let the user know why the
login wasn't successful, this component may be right for you.
Similar to model validation rules you can simply setup an array with
fields to compare. Each field can have a message and the value. If the
value is not as expected the login will fail and the message is
displayed.


This could be useful for:
`````````````````````````

- testing a passed verfication
- seeing if the user is banned
- checking if he is still a subscriber

... it's all about the definition.

But enough talk.

The following example-code should tell you everything you need to
know.


AppController::beforeFilter()
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Controller Class:
`````````````````

::

    <?php 
    function beforeFilter() {
    	// these two we are familiar with
    	$this->Authed->loginError = __("Wrong password / username. Please try again.", true);
    	$this->Authed->authError = __("Sorry, you are not authorized. Please log in first.", true);
    
    	// now this one is new
    	$this->Authed->userScopeRules = array(
    		'is_banned' => array(
    			'expected' => 0, 
    			'message' => __("You are banned from this service. Sorry.", true)
    		),
    		'is_validated' => array(
    			'expected' => 1,
    			'message' => __("Your account is not active yet. Click the Link in our Mail.", true)
    		)
    	);
    }
    ?>


Rules will be checked in the order you defined them. First failure
will lead to a false and issue the message using Session::setFlash().


AuthedComponent Code
~~~~~~~~~~~~~~~~~~~~
version 1.0 - Save in app/controller/components/authed.php

Component Class:
````````````````

::

    <?php 
    /**
     * Authed Component
     * 
     * Custom scope rules for AuthComponent
     * 
     * The purpose of this extension is to add a flexible rule setup to 
     * the login process. You could compare their setup to model validation.
     * The rules are applied just before the usual auth is issued. 
     * 
     * Note: This extension overwrites Auth::login()
     * 
     * Example: 
     * 
     *	$this->Authed->loginError = __("Wrong password / username. Please try again.", true);
     *	$this->Authed->authError = __("Sorry, you are not authorized. Please log in first.", true);
     * 
     *	$this->Authed->userScopeRules = array(
     *		'is_banned' => array(
     *			'expected' => 0, 
     *			'message' => __("You are banned from this service. Sorry.", true)
     *		),
     *		'is_validated' => array(
     *			'expected' => 1,
     *			'message' => __("Your account is not active yet. Click the Link in our Mail.", true)
     *		)
     *	);
     *
     * @author       Kjell Bublitz <m3nt0r.de@gmail.com>
     * @version      1.0
     * @package      app
     * @subpackage   app.app.controller.components
     * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
     * 
     * PHP versions 4 and 5
     */
    App::import('component', 'Auth');
    
    /**
     * Authed Component 
     * 
     * @package		app
     * @subpackage	app.app.controller.components
     */
    class AuthedComponent extends AuthComponent
    { 
    	/**
    	 * Rules
    	 *
    	 * @var array
    	 */
    	var $userScopeRules = array();
    	
    	/**
    	 * Check variable
    	 *
    	 * @var boolean
    	 */
    	var $_scopeRuleError = false;
    	
    	/**
    	 * Walk through all available rules and compare with row data.
    	 * Break on mismatch and reset loginError to rule.message
    	 *
    	 * @param array $data UserModel row
    	 * @return boolean True on login success, false on failure
    	 * @access public
    	 */
    	function hasScopeRuleMismatch($user) {
    		foreach ($this->userScopeRules as $field => $rule) {
    			if ($user[$field] != $rule['expected']) {
    				$this->loginError = $rule['message'];
    				$this->_scopeRuleError = true;
    				break;
    			}
    		}
    		return $this->_scopeRuleError;
    	}
    	
    	/**
    	 * Overwrites Auth::login()
    	 *
    	 * Basicly the same method, but after identify() was successful call
    	 * the above hasScopeRuleMismatch passing $user.
    	 * 
    	 * Only if this method returns false we will continue the login process.
    	 * 
    	 * @param mixed $data
    	 * @return boolean True on login success, false on failure
    	 * @access public
    	 */
    	function login($data = null) { 
    		$this->__setDefaults();
    		$this->_loggedIn = false;
    
    		if (empty($data)) {
    			$data = $this->data;
    		}
    
    		if ($user = $this->identify($data)) {
    			if (!$this->hasScopeRuleMismatch($user)) {
    				$this->Session->write($this->sessionKey, $user);
    				$this->_loggedIn = true;
    			}
    		}
    		return $this->_loggedIn;
    	}
    	
    	/**
    	 * Returns true if the login error was scope rules related.
    	 * Maybe someone needs this to go on with.
    	 * 
    	 * @return boolean
    	 */
    	function wasScopeRuleError() {
    		return $this->_scopeRuleError;
    	}
    	
    }
    ?>



Why don't you simply check this in UsersController::login() ?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
a.k.a Why should i use this?

+ Before your login() action "checking" code is executed the user
  would already be authed (given good username/password).
+ There is no sane way to reach the database result before the user is
  logged in. Above applies anyway.
+ You want to deliver more detailed informations to the user why
  exactly the auth wasn't successful.
+ This is an addon feature. It does not interfer with anything that
  Auth normally does.




Some ideas for future releases
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
At first i wasn't so sure about posting this (the change is so little,
yet powerful) but while i wrote this article i got some more ideas.


+ Allowing paths like 'UserPrivacy.onVacation' => array(...), not just
  single fields.
+ Supporting greater-than, less-than, etc. Example: "subscription <
  date(...)"
+ Magic functions to allow additional checking within the controller
  itself. Example: isSubscriber()

Comments welcome.


.. meta::
    :title: Authed - Adds rule based userScope to Auth
    :description: CakePHP Article related to userscope,scope,Components
    :keywords: userscope,scope,Components
    :copyright: Copyright 2008 
    :category: components

