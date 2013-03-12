

Validati18n Behavior
====================

by %s on October 29, 2009

This Behavior will add country based validation rules to your model.
The inspiration for the behavior came from the
[url=http://code.cakephp.org/wiki/RFCs/1-3-todo-list]1.3-to-do-
list[/url] that refers to several pending tickets to include more
regexes into core's Validation Class. This class already has a few
rules with regexes for some countries, although i'm not sure if an
endless list with regexes in the Validation class is that desirable.
By writing this article i also am completing the missing step of the
[url=http://teknoid.wordpress.com/2009/02/04/12-step-program-to-get-
addicted-to-cakephp/]12-step-program of Teknoid[/url]
The behavior has been put into a plugin together with some tests,
which can be found here: `http://github.com/ceeram/validati18n`_
The regexes i have used for the behavior are mostly from the existing
tickets, although some of them did not work and have been replaced
with some regexes i found working. Any additions for other countries
or updates for existing regexes are very welcome.

If there is no regex provided for the selected country, validation
will fail. The default country is set to 'us'. You can set the country
for all rules in the behavior settings:

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	var $actsAs = array('Validati18n' => array('country' => 'nl'));
    	var $validate = array(
    		'phone_number' => array(
    			'rule' => array('lc_phone'),
    			'message' => 'This phone is not valid.'
    		),
    		'zip_code' => array(
    			'rule' => array('lc_postal'),
    			'message' => 'This zipcode is not valid'
    		),
    		'ssnumber' => array(
    			'rule' => array('lc_ssn'),
    			'message' => 'This social security number is not valid')
    	);
    }
    ?>

If you want to override the country setting you can do this by adding
the country parameter to the rule:

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	var $actsAs = array('Validati18n' => array('country' => 'nl'));
    
    	var $validate = array(
    		'phone_number' => array(
    			'rule' => array('lc_phone', 'it'),
    			'message' => 'This phone is not valid.'
    		),
    		'zip_code' => array(
    			'rule' => array('lc_postal', 'it'),
    			'message' => 'This zipcode is not valid'
    		),
    		'ssnumber' => array(
    			'rule' => array('lc_ssn'),
    			'message' => 'This social security number is not valid')
    	);
    }
    ?>

To change the country code setting based on a user form input:

View Template:
``````````````

::

    
    echo $form->input('country_code', array('options'=>array('nl'=>'Netherlands', 'de'=>'Germany', 'dk'=>'Denmark')));

and put in your User model(or AppModel):

::

    
    	function beforeValidate() {
    		if(!empty($this->data[$this->alias]['country_code'])){
    			$this->Behaviors->Validati18n->settings[$this->alias]['country'] = $this->data[$this->alias]['country_code'];
    		}
    	}


And finally the code itself:

::

    
    <?php
    /**
     * Behavior for country based validation
     *
     * @copyright 2009 Marc Ypes, The Netherlands
     * @author Ceeram
     * @license http://www.opensource.org/licenses/mit-license.php The MIT License
     */ 
    class Validati18nBehavior extends ModelBehavior {
    /**
    * Behavior settings
    * 
    * @access public
    * @var array
    */
    	var $settings = array(); 
    /**
    * Default setting values
    *
    * @access private
    * @var array
    */ 	
    	var $_defaults = array('country'=>'us');
    /**
    * Country based regexes
    *
    * @access private
    * @var array
    */
    	var $_regex = array(
    		'au' => array(
    			'phone' => null,
    			'postal' => '/^[0-9]{4}$/i',
    			'ssn' => null),
    		'be' => array(
    			'phone' => null,
    			'postal' => '/^[1-9]{1}[0-9]{3}$/i',
    			'ssn' => null),
    		'ca' => array(
    			'phone' => null,
    			'postal' => '/\\A\\b[ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9]\\b\\z/i',
    			'ssn' => null),
    		'cs' => array(
    			'phone' => null,
    			'postal' => '/^[1-7]\d{2} ?\d{2}$/i',
    			'ssn' => null),
    		'dk' => array(
    			'phone' => null,
    			'postal' => null,
    			'ssn' => '/\\A\\b[0-9]{6}-[0-9]{4}\\b\\z/i'),
    		'de' => array(
    			'phone' => null,
    			'postal' => '/^[0-9]{5}$/i',
    			'ssn' => null),
    		'es' => array(
    			'phone' => '/^\\+?(34[-. ]?)?\\(?(([689]{1})(([0-9]{2})\\)?[-. ]?|([0-9]{1})\\)?[-. ]?([0-9]{1}))|70\\)?[-. ]?([0-9]{1}))([0-9]{2})[-. ]?([0-9]{1})[-. ]?([0-9]{1})[-. ]?([0-9]{2})$/',
    			'postal' => null,
    			'ssn' => null),
    		'fr' => array(
    			'phone' => '/^0[1-6]{1}(([0-9]{2}){4})|((\s[0-9]{2}){4})|((-[0-9]{2}){4})$/',
    			'postal' => null,
    			'ssn' => null),
    		'it' => array(
    			'phone' => '/^([0-9]*\-?\ ?\/?[0-9]*)$/',
    			'postal' => '/^[0-9]{5}$/i',
    			'ssn' => null),
    		'jp' => array(
    			'phone' => null,
    			'postal' => '/^[0-9]{3}-[0-9]{4}$/',
    			'ssn' => null),
    		'nl' => array(
    			'phone' => '/^0(6[\s-]?[1-9]\d{7}|[1-9]\d[\s-]?[1-9]\d{6}|[1-9]\d{2}[\s-]?[1-9]\d{5})$/',
    			'postal' => '/^[1-9][0-9]{3}\s?[A-Z]{2}$/i',
    			'ssn' => '/\\A\\b[0-9]{9}\\b\\z/i'),
    		'sk' => array(
    			'phone' => null,
    			'postal' => '/^[0,8,9]\d{2} ?\d{2}$/i',
    			'ssn' => null),
    		'uk' => array(
    			'phone' => null,
    			'postal' => '/\\A\\b[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}\\b\\z/i',
    			'ssn' => null),
    		'us' => array(
    			'phone' => '/^(?:\+?1)?[-. ]?\\(?[2-9][0-8][0-9]\\)?[-. ]?[2-9][0-9]{2}[-. ]?[0-9]{4}$/',
    			'postal' => '/\\A\\b[0-9]{5}(?:-[0-9]{4})?\\b\\z/i',
    			'ssn' => '/\\A\\b[0-9]{3}-[0-9]{2}-[0-9]{4}\\b\\z/i')
    		);
    /**
     * @param object $Model Model using the behavior
     * @param array $settings Settings to override for model.
     * @access public
     * @return void
     */
    	function setup(&$Model, $config = null) {
    		if (is_array($config)) {
    			$this->settings[$Model->alias] = array_merge($this->_defaults, $config);            
    		} else {
    			$this->settings[$Model->alias] = $this->_defaults;
    		}
    	}
    /**
     * Validation rule for phonenumbers
     * 
     * @param object $Model Model using the behavior
     * @param array $check
     * @param array $country Override the country from default or settings
     * @access public
     * @return boolean
     */
    	function lc_phone(&$Model, $check, $country = null) {
    		$check = array_values($check);
    		$check = $check[0];
    		if(!is_string($country)){
    			$country = $this->settings[$Model->alias]['country'];
    		}
    		if($this->_regex[$country]['phone']) {
    			return preg_match($this->_regex[$country]['phone'], $check);
    		}
    		return false;
    	}
    /**
     * Validation rule for zip codes
     * 
     * @param object $Model Model using the behavior
     * @param array $check
     * @param array $country Override the country from default or settings
     * @access public
     * @return boolean
     */
    	function lc_postal(&$Model, $check, $country = null) {
    		$check = array_values($check);
    		$check = $check[0];
    		if(!is_string($country)){
    			$country = $this->settings[$Model->alias]['country'];
    		}
    		if($this->_regex[$country]['postal']) {
    			return preg_match($this->_regex[$country]['postal'], $check);
    		}
    		return false;
    	}
    /**
     * Validation rule for social security numbers
     * 
     * @param object $Model Model using the behavior
     * @param array $check
     * @param array $country Override the country from default or settings
     * @access public
     * @return boolean
     */
    	function lc_ssn(&$Model, $check, $country = null) {
    		$check = array_values($check);
    		$check = $check[0];
    		if(!is_string($country)){
    			$country = $this->settings[$Model->alias]['country'];
    		}
    		if($this->_regex[$country]['ssn']) {
    			return preg_match($this->_regex[$country]['ssn'], $check);
    		}
    		return false;
    	}
    }
    ?>



.. _http://github.com/ceeram/validati18n: http://github.com/ceeram/validati18n
.. meta::
    :title: Validati18n Behavior
    :description: CakePHP Article related to validation,behavior,Behaviors
    :keywords: validation,behavior,Behaviors
    :copyright: Copyright 2009 
    :category: behaviors

