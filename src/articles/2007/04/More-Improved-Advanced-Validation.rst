More Improved Advanced Validation
=================================

by evan on April 17, 2007

Here's a way to perform a cleaner and better way of validating your
model data in CakePHP.


Introduction
~~~~~~~~~~~~
I earlier posted a tutorial in the wiki on advanced validation with
parameters in CakePHP, which has apparently been ported here in the
Bakery (thanks to Ludge!). Although it was effective, I just didn't
find it efficient. So here's the modified version.

For this method, I placed all validation methods in a separate file,
and the validation routines are easily placed in the field declaration
of the $validation field in your model class.


Modify the AppModel class
~~~~~~~~~~~~~~~~~~~~~~~~~
To start with, we place the following code in our AppModel class. Only
two things are important here:
(1) the require_once('validation.php'); line, since all validation
methods are to be placed in the validation.php file
(2) the function definition for invalidFields()


Model Class:
````````````

::

    <?php 
    /**
     * Application model for CakePHP.
     *
     * This file contaings the application-wide model class for CakePHP.
     *
     * PHP versions 4 and 5
     *
     * @author      Evan Sagge <evansagge@gmail.com>
     * @package		evansagge
     * @subpackage	evansagge.cake.model
     */
    
    /**
     * Validation methods are defined in Validation class in validation.php.
     */
    require_once ('validation.php');
    
    /**
     * Application model for CakePHP.
     *
     * This class is the application-wide model class for CakePHP.  It extends the {@link Model} class,
     * and overrides some methods specific to this application.
     *
     * PHP versions 4 and 5
     *
     * @author      Evan Sagge <evansagge@gmail.com>
     * @package		evansagge
     * @subpackage	evansagge.cake
     * @see         Model
     */
    class AppModel extends Model
    {
    
        /**
         * Calls all validation routines as defined in {@link Model::beforeValidate()} and in
         *      {@link Model::$validate} and returns the validation errors if there are any.
         *
         * @param array $data The data to be validated.  If not set, the value in {@link Model::$data}
         *      is used instead.
         * @return mixed If {@link Model::$validate} is not set, this method returns true.  If the
         *      {@link Model::beforeValidate()} callback method fails, it returns false.  Otherwise,
         *      it will returns an array of validation errors and messages for this class, if
         *      there are any.
         */
        function invalidFields($data= array ())
        {
            // Initialize errors array to a new array
            $this->validationErrors = array();        
            
            // Call beforeValidate() firsthand
            if (!$this->beforeValidate())
            {
                return false;
            }
    
            // If $data parameter is empty, then we must be validating against the member variable 
            // $this->data
            if (empty($data))
            {
                $data = $this->data;
            }
            
            // If $this->validate is not set, nothing to do
            if (!isset($this->validate))
            {
                return true;
            }        
    
            // PHP < 5.0 does not automatically return a reference on new, so here
            $validation =& new Validation($data, $this);
            
            // Iterate over the fields to be validated
            foreach ($this->validate as $fieldName => $validators)
            {
                // If no validators defined, nothing to do
                if (empty($validators) || !is_array($validators))
                {
                    continue;
                }
    
                // Iterate over validators array
                foreach ($validators as $name => $validator)
                {
                    // For instances such as: 
                    // 'field' => array('custom_method' => array('method' => 'validationMethodName'))
                    if (is_array($validator) && isset($validator['method']) && $name != 'method')
                    {
                        $methodName = $validator['method'];
                        $parameters = $validator;
                        unset($parameters['method']);
                    }
                    // For instances such as: 'field' => array('unique', 'confirmed', 'number')
                    else if (is_string($validator))
                    {
                        $methodName = 'validate' . Inflector::camelize($validator);
                        $parameters = array();
                    }
                    // For instances such as: 'field' => array('range' => array('min' => 5))
                    else
                    {
                        $methodName = 'validate' . Inflector::camelize($name);
                        $parameters = $validator;
                    }
                    
                    // Set defaul 'on' parameter value to null, meaning this validation will occur both
                    // during record creation and update
                    if (!isset($parameters['on']))
                    {
                        $parameters['on'] = null;
                    }
                    
                    // Skip validation if on create and record is not new; will only occur on new records
                    if ($parameters['on'] == 'create' and !empty($this->data[$this->name][$this->primaryKey]))
                    {
                        continue;
                    }
                        
                    // Skip validation if on update and record is new; will only occur on saved records
                    if ($parameters['on'] == 'update' and empty($this->data[$this->name][$this->primaryKey]))
                    {
                        continue;
                    }  
                    
                    // Set default 'allowNull' parameter to true, meaning validation will return as
                    // successfull if field value is null; otherwise, it will proceed with the regular
                    // validation process
                    if (!isset($parameters['allowNull']))
                    {
                        $parameters['allowNull'] = true;
                    }
                    
                    // If field is empty but is not required, skip validation
                    if (empty($data[$this->name][$fieldName])
                        and $methodName != 'validateNotEmpty' 
                        and $methodName != 'validateRequired'
                        and $parameters['allowNull'] === true)
                    {
                        continue;
                    }
    
                    // Set field value to null if not present
                    if (!isset($data[$this->name][$fieldName]))
                    {
                        $data[$this->name][$fieldName] = null;
                    }
    
                    // Call method in Validation object if it exists; otherwise, consider it as a 
                    // regex expression (backwards-compatibility crap)
                    if (method_exists($validation, $methodName))
                    {
                        $result = call_user_func(array($validation, $methodName), $fieldName, $parameters);
                        
                        if (!$result)
                        {
                            // Nothing to do, error messages have already been added in Validation::_evaluate()
                        }
                    }
                    else
                    {
                        if (!preg_match($methodName, $data[$this->name][$fieldName]))
                        {
                            $this->validationErrors[$fieldName][$name] = 1;
                        }
                    }
                }
    
            }
            
            return $this->validationErrors;
        }
    }
    ?>
    ?>



Validation Class
~~~~~~~~~~~~~~~~
Next would be to copy the following block of code in a file named
validation.php and place it under your app/ directory.

This file contains the Validation class, which should contain all the
validation methods we should need in any of our models. You can easily
add your own validation methods, but for now, I've placed the ones
which I am currently using in my CakePHP projects.


validation.php:
```````````````

::

    
    <?php
    /**
     * Validation class.
     *
     * This file contaings the validation class for CakePHP.
     *
     * PHP versions 4 and 5
     *
     * @author      Evan Sagge <evansagge@gmail.com>
     * @package     evansagge
     * @subpackage  evansagge.cake.model.util
     */
    
    /**
     * Validation class.
     *
     * This class contains methods for implementing advanced model validation in CakePHP.
     * Validation for a field is enabled through the {@link @AppModel::validate} variable.
     *
     * PHP versions 4 and 5
     *
     * @author      Evan Sagge <evansagge@gmail.com>
     * @package     evansagge
     * @subpackage  evansagge.cake
     * @see         AppModel
     */
    class Validation
    {
        /**
         * Reference for model data
         */
        var $data;
    
        /**
         * Reference for model object
         */
        var $model;
        
        var $errorCount = 0;
    
        /**
         * Constructor for validation class.  This initializes the data to validate as well as the model
         * against which data should be validated.
         *
         * @param mixed $data The data to validated.
         * @param object $model The model object against which the data should be validated.
         * @return Validation
         */
        function Validation(&$data, &$model)
        {
            $this->data =& $data;
            $this->model =& $model;
            $this->name =& $this->model->name;
        }
        
        /**
         * Evaluates the given validation result.  If the value is set to true, it will return true;
         * otherwise, it has two options: if $params['message'] is defined, it will add its value to
         * the model object's $validationErrors array and return false, else it will add the value of
         * the concatenation of the humanized field name and the passed $messageOnFail string to the
         * model object's $validationErrors array and return false.
         *
         * @param bool $validation The validation result.
         * @param string $messageOnFail The default message to return if the validation results to
         * 		false.
         * @param string $fieldName The field name.
         * @param array $params Extra validation parameters.
         * @return Validation
         */    
        function _evaluate($validation, $messageOnFail, $fieldName = null, $params = array())
        {
            if ($validation)
            {
                return true;
            }
            
            if (!isset($params['message']))
            {
                $params['message'] = Inflector::humanize($fieldName) . " " . $messageOnFail . ".";
            }
            
            if ($params['message'])
            {
                $this->model->validationErrors[$this->name][$fieldName] = $params['message'];
            }
            
            $this->errorCount++;
            return false;
        }
    
        /**
         * Checks if the value defined by the field name is not empty.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name is not empty; false otherwise.
         */
        function validateNotEmpty($fieldName, $params)
        {
            return $this->_evaluate(!empty($this->data[$this->name][$fieldName]), "should not be empty",
                    $fieldName, $params);
        }
        
        /**
         * Alias for Validation::validateNotEmpty()
         */
        function validateRequired($fieldName, $params)
        {
        	return $this->_evaluate(!$this->validateNotEmpty($fieldName, $params), "is required",
                    $fieldName, $params);
        }
    
        /**
         * Matches the value defined by the field name against the pattern specified by 
         * $params['pattern'].
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Contains the pattern to match the value of the field name against.
         * @return bool True if pattern matches the value of the field name; false otherwise.
         */
        function validatePattern($fieldName, $params)
        {
            $pattern = $params['pattern'];
            return $this->_evaluate(preg_match($pattern, $this->data[$this->name][$fieldName]),
                    "does not match pattern {$pattern}", $fieldName, $params);
        }
    
        /**
         * Checks if the value defined by the field name is a valid word, i.e. contains only
         * alphanumeric characters or the underscore ('_') character.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True value of the field name is a valid word; false otherwise.
         * @see Validation::validatePattern()
         */
        function validateWord($fieldName, $params)
        {
            $params['pattern'] = '/^\\w*$/';
            return $this->_evaluate(!$this->validatePattern($fieldName, $params), 
                    "is not a valid word", $fieldName, $params);
        }
    
        /**
         * Checks if the value defined by the field name is an integer.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True value of the field name is an integer; false otherwise.
         * @see Validation::validatePattern()
         */
        function validateInteger($fieldName, $params)
        {
            $params['pattern'] = '/^\\d+$/';
            return $this->_evaluate(!$this->validatePattern($fieldName, $params), 
                    "is not a valid integer", $fieldName, $params);
        }
    
        /**
         * Checks if the value defined by the field name is a number.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True value of the field name is a floating point number; false otherwise.
         * @see Validation::validatePattern()
         */
        function validateNumber($fieldName, $params)
        {       
            if (isset($params['integerOnly']))
            {
                $params['pattern'] = '/^\\d+$/';
            }
            else
            {
                $params['pattern'] = '/^(\\d+)|(\\d*\.\\d+)$/';
            }
            return $this->_evaluate(!$this->validatePattern($fieldName, $params), 
                    "is not a valid number", $fieldName, $params);
        }    
    
        /**
         * Checks if the value defined by the field name has a valid e-mail address format.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name has a valid e-mail address format; false
         *      otherwise.
         * @see Validation::validatePattern()
         */
        function validateEmail($fieldName, $params)
        {
            $params['pattern'] = '/\\A(?:^([a-z0-9][a-z0-9_\\-\\.\\+]*)@([a-z0-9]'
                    . '[a-z0-9\\.\\-]{0,63}\\.(com|org|net|biz|info|name|net|pro|aero|coop|museum|'
                    . '[a-z]{2,4}))$)\\z/i';
            return $this->_evaluate(!$this->validatePattern($fieldName, $params), 
                    "is not a valid email", $fieldName, $params);
        }
    
        /**
         * Checks if the value defined by the field name is a valid value for a year.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name is a valid value for a year; false
         *      otherwise.
         * @see Validation::validatePattern()
         */
        function validateYear($fieldName)
        {
            $params['pattern'] = '/^[12][0-9]{3}$/';
            return $this->_evaluate(!$this->validatePattern($fieldName, $params), 
                    "is not a valid year value", $fieldName, $params);
        }
    
        /**
         * Checks if the value defined by the field name is unique for the given data model.  The
         *      check for uniqueness is case-insensitive.  If $params['conditions'] is given,
         *      this is used as a constraint.  If $params['scope'] is given, the value of
         *      the field name is only checked against records that match the value of the
         *      column/field defined by $params['scope'].
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name is unique; false otherwise.
         * @see Model::hasAny()
         */
        function validateUnique($fieldName, $params)
        {
            $val = $this->data[$this->name][$fieldName];
            $column = $this->name . '.' . $fieldName;
            $id = $this->name . '.' . $this->model->primaryKey;
    
            $conditions = array();
            if (isset($params['conditions']))
            {
                $conditions = $params['conditions'];
            }
    
            if (isset($params['scope']))
            {
                if (is_array($params['scope']))
                {
                    foreach ($params['scope'] as $scope)
                    {
                        $conditions[$scope] = $this->data[$this->name][$scope];
                    }
                }
                else if (is_string($params['scope']))
                {
                    $conditions[$params['scope']] = $this->data[$this->name][$params['scope']];
                }
            }
    
            $conditions[$column] = $val;
            if (!empty($this->data[$this->name][$this->model->primaryKey]))
            {
                $conditions[$id] = ('!=' . $this->data[$this->name][$this->model->primaryKey]);
            }
    
            return $this->_evaluate(!$this->model->hasAny($conditions), 
                    "is already in use", $fieldName, $params);
        }
    
        /**
         * Checks if the length of the string value defined by the field name is within the range
         *      specified by $params['min'], $params['max'], or both.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if length of the value of the field name is within the specified range;
         *      false otherwise.
         */
        function validateLength($fieldName, $params)
        {
            $val = $this->data[$this->name][$fieldName];
            $length = strlen($val);
    
            if (array_key_exists('min', $params) && array_key_exists('max', $params))
            {
                return $this->_evaluate($length >= $params['min'] && $length <= $params['max'],
                        "should be between {$params['min']} and {$params['max']} characters long",
                        $fieldName, $params);
            }
            else if (array_key_exists('min', $params))
            {
                return $this->_evaluate($length >= $params['min'],
                        "should be at least {$params['min']} characters long",
                        $fieldName, $params);
            }
            else if (array_key_exists('max', $params))
            {
                return $this->_evaluate($length <= $params['max'],
                        "should be at most {$params['max']} characters long",
                        $fieldName, $params);
            }
        }
    
        /**
         * Checks if the numeric value defined by the field name is within the range
         *      specified by $params['min'], $params['max'], or both.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if numeric value of the field name is within the specified range;
         *      false otherwise.
         */
        function validateRange($fieldName, $params)
        {
            if ($result = $this->validateNumber($fieldName, $params))
            {
                return $result;
            }
    
            $val = $this->data[$this->name][$fieldName];
    
            if (array_key_exists('min', $params) && array_key_exists('max', $params))
            {
                return $this->_evaluate($val >= $params['min'] && $val <= $params['max'],
                        "should be between {$params['min']} and {$params['max']}",
                        $fieldName, $params);
            }
            else if (array_key_exists('min', $params))
            {
                return $this->_evaluate($val >= $params['min'],
                        "should be at least {$params['min']}",
                        $fieldName, $params);
            }
            else if (array_key_exists('max', $params))
            {
                return $this->_evaluate($val <= $params['max'],
                        "should be at most {$params['max']}",
                        $fieldName, $params);
            }
        }
    
        /**
         * Checks if the value defined by the field name corresponds with it's confirmation value,
         *      which is defined by the field specified in {@link $params}['confirm_var'] if defined,
         *      or by <the field name>_confirmation.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name corresponds to its confirmation
         *      value; false otherwise.
         */
        function validateConfirmed($fieldName, $params)
        {
            $val = $this->data[$this->name][$fieldName];
    
            if (array_key_exists('confirm_var', $params))
            {
                $confirmVar = $params['confirm_var'];               
            }
            else
            {
                if (empty($this->data[$this->name][$fieldName . '_confirmation']))
                {
                    $returnValue = false;
                }
                $confirmVar = $fieldName . '_confirmation';
            }
            
            if (empty($this->data[$this->name][$confirmVar]))
            {
                $this->data[$this->name][$confirmVar] = null;
            }
            
            return $this->_evaluate($val == $this->data[$this->name][$confirmVar], 
                "is not confirmed", $fieldName, $params);
        }
    
        /**
         * Checks if the value defined by the field name is a file.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name is a file; false otherwise.
         */
        function validateFile($fieldName, $params)
        {
            $file = $this->data[$this->name][$fieldName];
            
            $returnValue = true;    
    
            if ($file['error'] == UPLOAD_ERR_OK)
            {
                if (isset($params['allowedTypes']) && !in_array($file['type'], $params['allowedTypes']))
                {
                    $returnValue = false;
                }
            }
            else
            {
                unset($this->data[$this->name][$fieldName]);
            }
            
            return $this->_evaluate($returnValue, "is not a valid file", 
                    $fieldName, $params);        
        }
    
        /**
         * Checks if the value defined by the field name is an image file.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name is an image file; false otherwise.
         * @see Validation::validateFile()
         */
        function validateImageFile($fieldName, $params)
        {
            $params['allowedTypes'] = array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 
                    'image/x-png', 'image/x-jg', 'image/gif');
                    
            return $this->_evaluate($this->validateFile($fieldName, $params), 
                    "is not a valid image file", $fieldName, $params);        
        }
        
        /**
         * Checks if the value defined by the field name is a properly uploaded file.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name is a properly uploaded file; false otherwise.
         */
        function validateUploaded($fieldName, $params)
        {
            return $this->_evaluate(is_uploaded_file($this->data[$this->name][$fieldName]['tmp_name']), 
                    "was not uploaded", $fieldName, $params);
        }
    
        /**
         * Checks if the value defined by the field name is a date set in the future.  This
         * automatically checks if the value is in proper date format.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name is a future date; false otherwise.
         * @see Validation::validateDate()
         */
        function validateFutureDate($fieldName, $params)
        {       
            if ($result = $this->validateDate($fieldName, $params))
            {
                return $result;
            }
            
            $date = strtotime($this->data[$this->name][$fieldName]);        
    
            return $this->_evaluate($date > time(), "is not set in a future date", $fieldName, $params);
        }
        
        /**
         * Checks if the value defined by the field name is in proper date format (yyyy-mm-dd).
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name is in proper date format; false otherwise.
         */    
        function validateDate($fieldName, $params)
        {
            $date = $this->data[$this->name][$fieldName];
            
            $datePattern = '/^\d{4}-\d?\d-\d?\d$/';
            if ($date && preg_match($datePattern, $date))
            {
                $date = explode('-',$date);
                $result = checkdate($date[1], $date[2], $date[0]);
            }
            else
            {
                $result = false;
            }
            
            return $this->_evaluate($result, "is not a valid date", $fieldName, $params);        
        }
        
        /**
         * Checks if the value defined by the field name is in proper datetime format 
         * (yyyy-mm-dd HH:MM:SS).
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name is in proper datetime format; false otherwise.
         */      
        function validateDatetime($fieldName, $params)
        {
            $dateTime = $this->data[$this->name][$fieldName];
    
            $dateTimePattern = '/^\d{4}-\d?\d-\d?\d '
                    . '([01]?[0-9]|[2][0-4]):([0-5]?[0-9]):([0-5]?[0-9])$/';
            
            if ($dateTime && preg_match($dateTimePattern, $dateTime))
            {
                list($date, $time) = explode(' ', $dateTime);
                $date = explode('-',$date);
                $result = checkdate($date[1], $date[2], $date[0]);
            }
            else
            {
                $result = false;
            }
            
            return $this->_evaluate($result, "is not a valid datetime", $fieldName, $params);        
        }
        
        /**
         * Runs a method in the model object, passing to it the value of the specified field and the
         * additional parameters.  The method's name is checked from the value of $params['method']; if
         * this is not available, then this function will try to call validate{Fieldname} instead.  If 
         * the method call fails, this function will return false.
         *
         * @param string $fieldName The name of the field to validate.
         * @param array $params Extra validation parameters.
         * @return bool True if value of the field name is in proper datetime format; false otherwise.
         */     
        function validateMethod($fieldName, $params)
        {
            $method = isset($params['method']) ? $params['method'] : 'validate' 
                    . Inflector::humanize($fieldName);
            
            if (!method_exists($this->model, $method)) 
            {
                $this->errorCount++;
                return false;
            }
            else 
            {
                if (call_user_func(array(&$this->model, $method), $this->data[$this->name][$fieldName], 
                        $params))
                {
                    return true;
                }
                else
                {
                    $this->errorCount++;
                    return false;
                }
            }
    
        }
    
    }
    
    ?>



Usage
~~~~~
Usage is fairly easy. You can define your validation routines in your
model class just like in my previous tutorial, but this time around
you don't need to place it in any function. You can directly place it
near the start of your model class code in the declaration of the
$validate field.


Model Class:
````````````

::

    <?php 
    class User extends AppModel 
    {
        var $name = 'User';
        
        var $validate = array(
            'username' => array('required', 'word', 'unique', 'length' => array('min' => 5, 'max' => 50)),
            'email' => array('required', 'email', 'unique', 'confirmed' => array('on' => 'create')),
            'password' => array('required' => array('on' => 'create'), 'confirmed' => array('on' => 'create'), 'length' => array('min' => 5, 'max' => 50)),
        ); 
    }
    ?>

Currently, the following validation routines are available for use:


#. not_empty - value should not be empty
#. required - value is required; alias of not_empty
#. pattern - value should match pattern

example custom validation method in model object:

Model Class:
````````````

::

    <?php 
    class User extends AppModel 
    {
        ...
        function myCustomValidation($fieldValue, $params)
        {
            // Handle custom validation here and message handling
            $this->validationErrors[$this->name][] 'My custom error message.';
        }
        ...
    }
    ?>



ErrorHelper class
~~~~~~~~~~~~~~~~~
Now, for a nice little helper class to help us display our validation
error messages:

Helper Class:
`````````````

::

    <?php 
    class ErrorHelper extends HtmlHelper
    {
        function modelErrors()
        {
            $html =& new HtmlHelper;
    
            $models = func_get_args();
    
            $list = '';
            foreach ($models as $model)
            {
                if (isset($this->validationErrors[$model]))
                {
                    
                    foreach ($this->validationErrors[$model] as $field => $errors)
                    {
                        foreach ($errors as $error)
                        {
                            $list .= $this->contentTag('li', $error);
                        }
                    }
                }
            }
    
            if (!empty($list))
            {
                return $this->contentTag('div', $this->contentTag('h4',
                        'The following errors need to be corrected: ') . $this->contentTag('ul', $list),
                        array('class'=>'error_messages'));
            }
        }
        
        function fieldError($fieldName)
        {
            list($model, $field) = explode('/', $fieldName);
    
            if (isset($this->validationErrors[$model][$field]))
            {
                foreach ($this->validationErrors[$model][$field] as $error)
                {
                    return $error;
                }
            }
            else
            {
                return null;
            }
        }    
    
        function fieldErrors($fieldName)
        {
            list($model, $field) = explode('/', $fieldName);
    
            if (isset($this->validationErrors[$model][$field]))
            {
                $list = '';
                foreach ($this->validationErrors[$model][$field] as $error)
                {
                    $list .= $this->contentTag('li', $error);
                }
    
                return $this->contentTag('div', $this->contentTag('ul', $list),
                        array('class'=>'form_error_message'));
            }
            else
            {
                return null;
            }
        }
    
        function isFieldInvalid($fieldName)
        {
    	    list($model, $field) = explode('/', $fieldName);
    
    	    return (isset($this->validationErrors[$model][$field]));
        }
    }
    ?>



View usage
~~~~~~~~~~

View Template:
``````````````

::

    
    <h2>New User</h2>
    <?= $error->modelErrors('User'); ?>
    <form action="<?= $html->url('/users/add'); ?>" method="post">
    <div class="optional"> 
    	<?= $form->labelTag('User/username', 'Username');?>
     	<?= $html->input('User/username', array('size' => '60'));?>
    </div>
    <div class="optional"> 
    	<?= $form->labelTag('User/email', 'Email');?>
     	<?= $html->input('User/email', array('size' => '60'));?>
        <?= $html->input('User/email_confirmation', array('size' => '60'));?>
    </div>
    <div class="optional"> 
    	<?= $form->labelTag('User/password', 'Password');?>
     	<?= $html->password('User/password', array('size' => '60'));?>
        <?= $html->password('User/password_confirmation', array('size' => '60'));?>
    </div>
    <div class="optional"> 
        <?= $form->labelTag('User/datetime', 'Time');?>
        <?= $html->input('User/datetime', array('size' => '60'));?>
    </div>
    <div class="submit">
    	<?= $html->submit('Add');?>
    </div>
    </form>

Fini.


.. meta::
    :title: More Improved Advanced Validation
    :description: CakePHP Article related to forms,errors,1.1,Tutorials
    :keywords: forms,errors,1.1,Tutorials
    :copyright: Copyright 2007 evan
    :category: tutorials

