MultivalidatableBehavior: Using many validation rulesets per model
==================================================================

by dardosordi on July 29, 2008

In this article I present the MultivalidatableBehavior, which allow us
to have multiple sets of validation rules for each model.

There are specific situations when we need to change the default
validation ruleset for a model. This is exactly what the
Multivalidatable behavior does.

First let's see an example of usage.
````````````````````````````````````

Suppose we have four sets of validation rules for our user model:

#. One for the admin (who has supercow powers)
#. One for the registration form,
#. Another for the password change form,
#. And finally a default.


Model Class:
````````````

::

    <?php 
    Class UserModel extends AppModel {
    
    	var $name = 'User';
    
    	var $actsAs = array('Multivalidatable');
    
    	/**
    	 * Default validation ruleset
    	 */
    	var $validate = array(
    			'realname' => array('rule' => '/[A-Za-z ]+/', 'message' => 'Only letters and spaces please.'),
    			'username' => array('rule' => 'alphanumeric', 'message' => 'Only letters and numbers please.'),
    			'password' => array('rule' => array('minLenght', 6), 'message' => 'Password must be at least 6 characters long.'),
    			'email' => array('rule' => 'email', 'message' => 'Must be a valid email address.'),
    		);
    
    	/**
    	 * Custom validation rulesets
    	 */
    	var $validationSets = array(
    		'admin' => array(
    				'name' => array('rule' => 'alphanumeric'),
    				'email' => array('rule' => 'email'),
    				'age' => array('rule' => 'numeric'),
    		),
    		'register' => array(
    			'realname' => array('rule' => '/[A-Za-z ]+/', 'message' => 'Only letters and spaces allowed, please try again.'),
    			'username' => array('rule' => 'alphanumeric', 'message' => 'Only letters and numbers, please try again.'),
    			'password' => array('rule' => array('minLenght', 6), 'message' => 'Password must be at least 6 characters long, please try again.'),
    			'password_confirm' => array('rule' => 'confirmPassword', 'message' => 'Passwords do not match, please try again.'),
    			'email' => array('rule' => 'email', 'message' => 'Must be a valid email address.'),
    			'captcha' => array('rule' => 'checkCaptcha', 'required' => true, 'allowEmpty' => false, 'message' => 'Incorrect validation code, please try again.')
    		),
    		'changePassword' => array(
    			'username' => array('rule' => 'alphanumeric', 'message' => 'Only letters and numbers, please try again.'),
    			'password' => array('rule' => array('minLenght', 6), 'message' => 'Password must be at least 6 characters long, please try again.'),
    			'password_confirm' => array('rule' => 'confirmPassword', 'message' => 'Passwords do not match, please try again.')
    		)
    	);
    
    	function checkCaptcha()
    	{
    		// your captcha related code here
    	}
    
    	function confirmPassword()
    	{
    		// check that both passwords are equal
    	}
    }
    ?>


Now in the controller, we can dinamically set the validation ruleset:
`````````````````````````````````````````````````````````````````````

Controller Class:
`````````````````

::

    <?php 
    Class UsersController extends AppController {
    
    	var $name = 'Users';
    
    	var $scaffold; // I'm lazy today
    
    	function beforeFilter() {
    		parent::beforeFilter();
    		if (isset($this->params['admin'])) {
    			// admins have special rules
    			$this->User->setValidation('admin');
    		}
    	}
    
    	function register() {
    		$this->User->setValidation('register');
    
    		// here goes the code for registering a new account
    	}
    
    	function password() {
    		$this->User->setValidation('changePassword');
    
    		// here goes the code to allow the users change their own password
    	}
    }
    ?>


The method setValidation() also accepts as parameter an array with the
ruleset:
````````

::

    
    $this->User->setValidation(array('email' => array('rule' => 'email', 'message' => 'Must be a valid email address')));


Also, there are other utility methods:
``````````````````````````````````````

restoreValidation() and restoreDefaultValidation() which do exactly
what their name implies.

Finally, this is the behavior:
``````````````````````````````

::

    <?php
    class MultivalidatableBehavior extends ModelBehavior {
    
    	/**
    	 * Stores previous validation ruleset
    	 *
    	 * @var Array
    	 */
    	var $__oldRules = array();
    
    	/**
    	 * Stores Model default validation ruleset
    	 *
    	 * @var unknown_type
    	 */
    	var $__defaultRules = array();
    
        function setUp(&$model, $config = array()) {
        	$this->__defaultRules[$model->name] = $model->validate;
        }
    
        /**
         * Installs a new validation ruleset
         *
         * If $rules is an array, it will be set as current validation ruleset,
         * otherwise it will look into Model::validationSets[$rules] for the ruleset to install
         *
         * @param Object $model
         * @param Mixed $rules
         */
        function setValidation(&$model, $rules = array()) {
        	if (is_array($rules)){
        		$this->_setValidation($model, $rules);
        	} elseif (isset($model->validationSets[$rules])) {
        		$this->setValidation($model, $model->validationSets[$rules]);
        	}
        }
    
        /**
         * Restores previous validation ruleset
         *
         * @param Object $model
         */
        function restoreValidation(&$model) {
        	$model->validate = $this->__oldRules[$model->name];
        }
    
        /**
         * Restores default validation ruleset
         *
         * @param Object $model
         */
        function restoreDefaultValidation(&$model) {
        	$model->validate = $this->__defaultRules[$model->name];
        }
    
        /**
         * Sets a new validation ruleset, saving the previous
         *
         * @param Object $model
         * @param Array $rules
         */
        function _setValidation(&$model, $rules) {
        		$this->__oldRules[$model->name] = $model->validate;
        		$model->validate = $rules;
        }
    
    }
    
    ?>


.. meta::
    :title: MultivalidatableBehavior: Using many validation rulesets per model
    :description: CakePHP Article related to validation,multiple validation,Behaviors
    :keywords: validation,multiple validation,Behaviors
    :copyright: Copyright 2008 dardosordi
    :category: behaviors

