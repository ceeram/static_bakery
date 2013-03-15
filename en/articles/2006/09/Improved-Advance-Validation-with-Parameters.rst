

Improved Advance Validation with Parameters
===========================================

by %s on September 22, 2006

This code allows you to perform complex validation on your Model data
using both regular expressions and functions as well as supporting
multiple validation routines per model field.
This is an article ported from the wiki. The original author is
evansagge .


Introduction
------------
The built-in functions for validating model data are fine for simple
validation techniques, but quite often you will find yourself needed
to perform several validations on the same field.

The method of validation below will allow you to perform complex
validation of your data with almost no extra work beyond describing
the validation.



AppModel Additions
------------------
To begin with, the following code should be added to your AppModel.


Model Class:
````````````

::

    <?php 
    // file: /app/app_model.php
    define('VALID_WORD', '/^\\w+$/');
    define('VALID_UNIQUE', 'isUnique');
    define('VALID_LENGTH_WITHIN', 'isLengthWithin');
    define('VALID_CONFIRMED', 'isConfirmed');
     
    class AppModel extends Model {
     
      // If you need to disable validation for particular columns, you may populate this variable like so:
      // $this->User->disabledValidate = array('email', 'password'); // disables validation on email and password columns
      // $this->User->disabledValidate = array(
      //   'email',
      //   'password' => array('confirmed', 'required')
      // ); // disables validation on email column, and password['confirmed'] && password['required']
      var $disabledValidate;
      
      function loadValidation() {
        // placeholder for overloading
      }
         
      function invalidFields($data = array()) {
        $this->loadValidation();
    
        if (!$this->beforeValidate()) {
            return false;
        }
    
        if (is_array($this->disabledValidate)) {
          foreach($this->disabledValidate as $field => $params) {
            if (is_string($field) && is_array($params)) {
              foreach($params as $param) {
                if (is_string($param)) {
                  $this->validate[$field][$param] = false;
                }
              }
            } else if (is_int($field) && is_string($params)) {
              $this->validate[$params] = false;
            }
          }
        }
        
        //debug($this->validate);
        
        if (!isset($this->validate) || !empty($this->validationErrors)) {
          if (!isset($this->validate)) {
            return true;
          } else {
            return $this->validationErrors;
          }
        }
     
        if (isset($this->data)) {
          $data = array_merge($data, $this->data);
        }
     
        $errors = array();
        $this->set($data);
     
        foreach ($data as $table => $field) {
          foreach ($this->validate as $field_name => $validators) {
            if ($validators) {      
              foreach($validators as $validator) {
                if (isset($validator['method'])) {
                  if (method_exists($this, $validator['method'])) {
                    $parameters = (isset($validator['parameters'])) ? $validator['parameters'] : array();
                    $parameters['var'] = $field_name;
                    if (isset($data[$table][$field_name]) &&
                      !call_user_func_array(array(&$this, $validator['method']),array($parameters))) {
                      if (!isset($errors[$field_name])) {
                        $errors[$field_name] = isset($validator['message']) ? $validator['message'] : 1;
                      }
                    }
                  } else {
                    if (isset($data[$table][$field_name]) &&
                      !preg_match($validator['method'], $data[$table][$field_name])) {
                      if (!isset($errors[$field_name])) {
                        $errors[$field_name] = isset($validator['message']) ? $validator['message'] : 1;
                      }
                    }
                  }
                }
              }
            }
          }
        }
        $this->validationErrors = $errors;
        return $errors;
      }
      
      // validation methods
        
      function isUnique($params) {
        $val = $this->data[$this->name][$params['var']];
        $db = $this->name . '.' . $params['var'];
        $id = $this->name . '.id';
        if($this->id == null ) {
          return(!$this->hasAny(array($db => $val ) ));
        } else {
          return(!$this->hasAny(array($db => $val, $id => '!='.$this->data[$this->name]['id'] ) ) );
        }
      }
     
      function isLengthWithin($params) {
        $val = $this->data[$this->name][$params['var']];
        $length = strlen($val);
     
        if (array_key_exists('min', $params) && array_key_exists('max', $params)) {
          return $length >= $params['min'] && $length <= $params['max'];
        } else if (array_key_exists('min', $params)) {
          return $length >= $params['min'];
        } else if (array_key_exists('max', $params)) {
          return $length <= $params['max'];
        }
      }
     
      function isConfirmed($params) {
        $val = $this->data[$this->name][$params['var']];
        $val_confirmation = array_key_exists('confirm_var', $params) ?
          $this->data[$this->name][$params['confirm_var']] :
          $this->data[$this->name][$params['var'].'_confirmation'];
        return $val == $val_confirmation;
      }
    }
    ?>

You will notice that all the validation routines are DEFINE()d before
the class is declared. This helps keep the appearance of the
validation system uniform and does not differentiate between regex and
method calls in the constant names.

The loadValidation() method is declared as empty, as it will be
overloaded by our individual model classes later. It will be called by
the invalidFields() method.

If you need to disable validation on certain columns or column
validation routines, you can populate the disabledValidate array on
the controller before calling save().



Usage
-----
An example of how to use these complex validation routines is shown
below, using the example of a Users model. Simply create a nested
array containing the validation methods (and their parameters) to
apply multiple validators to each field. A message can be defined
which may be displayed to the user on triggering the error.

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
      var $name = 'User';
      var $validate;
     
      function loadValidation(){
        $this->validate = array(
          'username' => array(
            'required' => array(
              'method' => VALID_NOT_EMPTY,
              'message' => 'You have not entered a username.',
            ),
            'word' => array(
              'method' => VALID_WORD,
              'message' => 'The username you entered contains invalid characters.'
            ),          
            'unique' => array(
              'method' => VALID_UNIQUE,
              'message' => 'The username you entered is already in use.'
            ),
            'length_within' => array(
              'method' => VALID_LENGTH_WITHIN,
              'message' => 'Username should be between 6 to 50 characters long.',
              'parameters' => array('min' => 6, 'max' => 50)
            ),   
          ),
          'email' => array(
            'required' => array(
              'method' => VALID_NOT_EMPTY,
              'message' => 'You have not entered an e-mail address.',
            ),
            'email' => array(
              'method' => VALID_EMAIL,
              'message' => 'The e-mail address you entered is not in proper format.'
            ),          
            'unique' => array(
              'method' => VALID_UNIQUE,
              'message' => 'The e-mail address you entered is already in use.'
            ),
            'confirmed' => array(
              'method' => VALID_CONFIRMED,
              'message' => 'The e-mail addresses you entered does not match its confirmation.'
            ),
          ),
          'password' => array(
            'required' => array(
              'method' => VALID_NOT_EMPTY,
              'message' => 'You have not entered a password.',
            ),
            'length_within' => array(
              'method' => VALID_LENGTH_WITHIN,
              'message' => 'Password should be between 8 to 50 characters long.',
              'parameters' => array('min' => 8, 'max' => 50)
            ),           
            'confirmed' => array(
              'method' => VALID_CONFIRMED,
              'message' => 'The password you entered does not match its confirmation.'
            ),
          ),               
        );
      }
    }
    ?>

When using VALID_LENGTH_WITHIN, you can either specify min parameter
(only validates minimum length requirement), max parameter (only
validates maximum length requirement), or both.


When using these routines, there is no difference as far as the
controller is concerned:

Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController {
      var $name = 'Users';
     
      var $helpers = array('Html', 'Error', 'Javascript', 'Ajax');
     
      function register() {
        if (!empty($this->data)) {
          if ($this->User->save($this->data)) {
            $this->flash('You have successfully registered your account.', '/users');
          }
        }
      }
    }
    ?>




Displaying errors
-----------------
The following error helper can be used from a view to display the
error messages defined within the model.

Helper Class:
`````````````

::

    <?php 
    class ErrorHelper extends Helper {
     
      function messageFor($target) {
          list($model, $field) = explode('/', $target);
     
          if (isset($this->validationErrors[$model][$field])) {
              return '<span class="form_error_message">'.$this->validationErrors[$model][$field].'</span>';
          } else {
              return null;
          }
      }
      
      function allMessagesFor($model) {
        $html =& new HtmlHelper;
        
        if (isset($this->validationErrors[$model])) {
          $list = '';
          foreach (array_keys($this->validationErrors[$model]) as $field) {
            $list .= $html->contentTag('li', $this->validationErrors[$model][$field]);
          }
          return $html->contentTag('div', 
            $html->contentTag('h4', 'The following errors need to be corrected: ') . 
            $html->contentTag('ul', $list), array('class'=>'error_messages'));
        }
      }
    }
    ?>

And finally, an example of the helper as used from the view:

View Template:
``````````````

::

    
    <?php echo $error->allMessagesFor('User'); // This line is for displaying the error messages from our form all at once. ?>
     
    <form id="register" name="register" method="POST" action="<?php echo $html->url('/users/register')?>">
      <label>Username</label>
      <?php echo $html->input('User/username')?>
      <?php echo $error->messageFor('User/username')?>
      <br />
     
      <label>Email</label>
      <?php echo $html->input('User/email')?>
      <?php echo $error->messageFor('User/email')?>
      <br />
     
      <label>Confirm Email</label>
      <?php echo $html->input('User/email_confirmation')?>
      <?php echo $error->messageFor('User/email_confirmation')?>
      <br />
     
      <label>Password</label>
      <?php echo $html->password('User/password')?>
      <?php echo $error->messageFor('User/password')?>
      <br />
     
      <label>Confirm Password</label>
      <?php echo $html->password('User/password_confirmation')?>
      <?php echo $error->messageFor('User/password_confirmation')?>
      <br />
     
      <?php echo $html->submit('Register')?>
    </form>



Note:
~~~~~
Don't forget that you can add your own validation routines by adding a
regular expression/method to the AppController class.

.. meta::
    :title: Improved Advance Validation with Parameters
    :description: CakePHP Article related to ,Tutorials
    :keywords: ,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

