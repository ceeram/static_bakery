Rails-like Data Validation
==========================

by %s on May 16, 2007

Validate your data like in rails: http://rails.rubyonrails.com/classes
/ActiveRecord/Validations/ClassMethods.html
Now you can validate your data (almost) like in rails (see `http://rai
ls.rubyonrails.com/classes/ActiveRecord/Validations/ClassMethods.html`
_)!

Note: this has not been fully tested yet, so please submit bugs and
suggestions...


Example Usage
~~~~~~~~~~~~~

Functions and parameters are almost exactly the same as the rails
functions and parameters. As of right now, there is no
validates_agreement_of() or validates_confirmation_of().

In your users model, you might have:


Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	function validates() {
    		$this->setAction();
    		#always validate presence of username
    		$this->validates_presence_of('username');
    		#validate uniqueness of username when creating a new user
    		$this->validates_uniqueness_of('username',array('on'=>'create'));
    		#validate length of username (minimum)
    		$this->validates_length_of('username',array('min'=>3));
    		#validate length of username (maximum)
    		$this->validates_length_of('username',array('max'=>50));
    		#validate presence of password
    		$this->validates_presence_of('password');
    		#validate presence of email
    		$this->validates_presence_of('email');
    		#validate uniqueness of email when creating a new user
    		$this->validates_uniqueness_of('email',array('on'=>'create'));
    		#validate format of email
    		$this->validates_format_of('email',VALID_EMAIL);
    
    		#if there were errors, return false
    		$errors = $this->invalidFields();
    		return (count($errors) == 0);
    	}
    }
    ?>



The Code
~~~~~~~~

Place the following code in app/app_model.php :


Model Class:
````````````

::

    <?php 
    class AppModel extends Model {
            var $action;
            function setAction() {
                    if (empty($this->id)) {
                            $this->action = 'create';
                    } else {
                            $this->action = 'update';
                    }
                    return true;
            }
            function invalidFields($data = array()) {
                    if ( empty($data) ) {
                            $data = $this->data;
                    }
                    if ( !$this->beforeValidate() ) {
                            return $this->validationErrors;
                    }
                    return $this->validationErrors;
            }
    
            function validates_presence_of($fieldName,$options=array()) {
                    if ( !isset($options['message']) ) {
                            $options['message'] = Inflector::humanize($fieldName) . ' is required.';
                    }
                    if ( !isset($options['on']) ) {
                            $options['on'] = 'save';
                    }
                    if ( (($options['on'] == 'save') || ($options['on'] == $this->action)) ) {
                            if ( empty($this->data[$this->name][$fieldName]) ) {
                                    $this->validationErrors[$fieldName] = $options['message'];
                            }
                    }
            }
            function validates_exclusion_of($fieldName,$options=array()) {
                    $fieldValue = $this->data[$this->name][$fieldName];
                    if ( @$options['allow_null'] && ($fieldValue == null) ) {
                            return true;
                    }
                    if ( !isset($options['in']) ) {
                            $options['in'] = array();
                    }
                    if ( !isset($options['message']) ) {
                            $options['message'] = Inflector::humanize($fieldName) . ' should be one of the following: ' . join(',',$options['in']) . '.';
                    }
                    if ( !isset($options['on']) ) {
                            $options['on'] = 'save';
                    }
                    if ( (($options['on'] == 'save') || ($options['on'] == $this->action)) ) {
                            if ( in_array($fieldValue,$options['in']) ) {
                                    $this->validationErrors[$fieldName] = $options['message'];
                            }
                    }
            }
            function validates_format_of($fieldName,$options=array()) {
                    $fieldValue = $this->data[$this->name][$fieldName]; 
                    if ( @$options['allow_null'] && ($fieldValue == null) ) {
                            return true;
                    }
                    if ( !isset($options['message']) ) {
                            $options['message'] = Inflector::humanize($fieldName) . ' has an invalid format.';
                    }
                    if ( !isset($options['on']) ) {
                            $options['on'] = 'save';
                    }
                    if ( !isset($options['with']) ) {
                            $options['with'] = '//';
                    }
                    if ( (($options['on'] == 'save') || ($options['on'] == $this->action)) ) {
                            if ( !preg_match($options['with'],$fieldValue) ) {
                                    $this->validationErrors[$fieldName] = $options['message'];
                            }
                    }
            }
            function validates_inclusion_of($fieldName,$options=array()) {
                    $fieldValue = $this->data[$this->name][$fieldName];
                    if ( @$options['allow_null'] && ($fieldValue == null) ) {
                            return true;
                    }
                    if ( !isset($options['in']) ) {
                            $options['in'] = array();
                    }
                    if ( !isset($options['message']) ) {
                            $options['message'] = Inflector::humanize($fieldName) . ' should be one of the following: ' . join(',',$options['in']) . '.';
                    }
                    if ( !isset($options['on']) ) {
                            $options['on'] = 'save';
                    }
                    if ( (($options['on'] == 'save') || ($options['on'] == $this->action)) ) {
                            if ( !in_array($fieldValue,$options['in']) ) {
                                    $this->validationErrors[$fieldName] = $options['message'];
                            }
                    }
            }
            function validates_length_of($fieldName,$options=array()) {
                    $fieldValue = $this->data[$this->name][$fieldName];
                    if ( @$options['allow_null'] && ($fieldValue == null) ) {
                            return true;
                    }
                    if ( !isset($options['message']) ) {
                            $options['message'] = Inflector::humanize($fieldName) . ' has the wrong length.';
                    }
                    if ( !isset($options['on']) ) {
                            $options['on'] = 'save';
                    }
                    if ( (($options['on'] == 'save') || ($options['on'] == $this->action)) ) {
                            if ( isset($options['max']) ) {
                                    if ( strlen($fieldValue) > $options['max'] ) {
                                            $this->validationErrors[$fieldName] = $options['message'];
                                    }
                            } elseif ( isset($options['min']) ) {
                                    if ( strlen($fieldValue) < $options['min'] ) {
                                            $this->validationErrors[$fieldName] = $options['message'];
                                    }
                            } elseif ( isset($options['in']) ) {
                                    if ( !in_array($fieldValue,$options['in']) ) {
                                            $this->validationErrors[$fieldName] = $options['message'];
                                    }
                            } elseif ( isset($options['is']) ) {
                                    if ( $fieldValue != $options['is'] ) {
                                            $this->validationErrors[$fieldName] = $options['message'];
                                    }
                            }
                    }
            }
            function validates_numericality_of($fieldName,$options=array()) {
                    $fieldValue = $this->data[$this->name][$fieldName];
                    if ( @$options['allow_null'] && ($fieldValue == null) ) {
                            return true;
                    }
                    if ( !isset($options['only_integer']) ) {
                            $options['only_integer'] = false;
                    }
                    if ( !isset($options['message']) ) {
                            if ( $options['only_integer'] ) {
                                    $options['message'] = Inflector::humanize($fieldName) . ' should be an integer.';
                            } else {
                                    $options['message'] = Inflector::humanize($fieldName) . ' should be a number.';
                            }
                    }
                    if ( !isset($options['on']) ) {
                            $options['on'] = 'save';
                    }
                    if ( (($options['on'] == 'save') || ($options['on'] == $this->action)) ) {
                            if (
                                    !is_numeric($fieldValue)
                                    || ( $options['only_integer'] && !is_int($fieldValue) )
                            ) {
                                    $this->validationErrors[$fieldName] = $options['message'];
                            }
                    }
            }
            function validates_uniqueness_of($fieldName,$options=array()) {
                    $fieldValue = $this->data[$this->name][$fieldName];
                    if ( @$options['allow_null'] && ($fieldValue == null) ) {
                            return true;
                    }
                    if ( !isset($options['message']) ) {
                            $options['message'] = Inflector::humanize($fieldName) . ' is already taken.';
                    }
                    if ( !isset($options['on']) ) {
                            $options['on'] = 'save';
                    }
                    if ( (($options['on'] == 'save') || ($options['on'] == $this->action)) ) {
                            if ( $this->hasAny(array("{$this->name}.{$fieldName}" => $fieldValue)) ) {
                                    $this->validationErrors[$fieldName] = $options['message'];
                            }
                    }
            }
    }
    ?>

Place the following code in app/views/helpers/error.php :


Helper Class:
`````````````

::

    <?php 
    class ErrorHelper extends Helper {
    	function forField($field) {
    		list($model,$fieldName) = explode('/',$field);
    		if ( isset($this->validationErrors($model,$fieldName) ) {
    			return '<div class="error">' . $this->validationErrors[$model][$fieldName] . '</div>';
    		} else {
    			return '';
    		}
    	}
    }
    ?>

In your controller, put


Controller Class:
`````````````````

::

    <?php 
    class BananasController extends AppController {
    	/* ... */
    	var $helpers = array('Html','Error');
    	/* ... */
    }
    ?>

In your views, put


View Template:
``````````````

::

    
    <label for="quantity">Quantity:
    	<input type="text" name="data[Banana][quantity]" id="quantity" />
    </label><?php print $error->forField('Banana/quantity'); ?>

In your model, put:


Model Class:
````````````

::

    <?php 
    class Banana extends AppModel {
    	function validates() {
    		#make sure quantity is an integer
    		$this->validates_numericality_of('quantity',array('only_integer'=>true));
    
    		#if there were errors, return false
    		$errors = $this->invalidFields();
    		return (count($errors) == 0);
    	}
    }
    ?>

Please comment!

.. _http://rails.rubyonrails.com/classes/ActiveRecord/Validations/ClassMethods.html: http://rails.rubyonrails.com/classes/ActiveRecord/Validations/ClassMethods.html
.. meta::
    :title: Rails-like Data Validation
    :description: CakePHP Article related to data,form,Models
    :keywords: data,form,Models
    :copyright: Copyright 2007 
    :category: models

