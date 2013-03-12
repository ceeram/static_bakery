

Validanguage Helper for customizable Javascript form validation
===============================================================

by %s on December 02, 2008

Validanguage is a free, open source javascript library that provides a
highly customizable, framework-independent environment for managing
client-side form validation. The Validanguage helper enables
validation rules to be generated automatically from Cake 1.2 model
validation rules.


Overview
--------

By simply including the validanguage.js javascript file on a webpage
and issuing a single call to the validanguage helper, you can
automatically transform the server-side validation rules in your model
into client-side javascript validation to create inline success and
error messages that are easily customizable via CSS.

Additional details on validanguage, including demos, the latest
version and documentation are available on the project's homepage at
`http://drlongghost.com/validanguage.php`_.


Required Files (Appear Inline Below the Article)
------------------------------------------------

+ validanguage.js (Validanguage Javascript file)
+ validanguage.php (Validanguage CakePHP Helper)

Download the latest version of both these files from the above link.


Basic Usage
-----------

Step 1 -- Download the required files
`````````````````````````````````````
Download the validanguage javascript file and CakePHP helper and place
them in the appropriate directories (typically, /app/webroot/js/ and
/app/helpers/ respectively).


Step 2 -- Add the helper to the desired controller(s)
`````````````````````````````````````````````````````
If you wish to use the Validanguage helper throughout your
application, you can add it to the helpers array for
app_controller.php. Alternately, add the Validanguage helper directly
to the desired controller(s):

::

    <?php 
    var $helpers = array('Validanguage');
    ?>



Step 3 -- Add the validanguage.js javascript file to your View
``````````````````````````````````````````````````````````````
You can add the javascript directly to the layout for all your pages
by adding a script tag to the head section of the layout:

::

    <script type="text/javascript" src="/path/to/validanguage.js"></script>

Alternately, use the javascript helper in your view file(s) to include
the validanguage javascript:

::

    <?php
    $javascript->link('/path/to/validanguage.js', false); 
    ?>



Step 4 -- Call $validanguage->printTags() inside your View
``````````````````````````````````````````````````````````
The final step is to call the printTags() method inside your view(s):

::

    <?php
    $validanguage->printTags();
    ?>

This method will convert the validation rules defined for all models
associated with the current controller into validanguage Comment tags,
similar to the following:

::

    
    <!-- <validanguage target="ArticleBody" required="true" errorMsg="You must enter some text for the body of your article."/> -->

Validanguage Comment tags are placed inside HTML comments and are
themselves written in an HTML-like syntax. When the validanguage
javascript is included on a page, it automatically scans through all
the comment tags on the page and loads javascript validation rules for
any validanguage tags which it finds.

That pretty much sums up the basic usage for this helper. It was
designed to entirely automate the process of generating the javascript
rules needed to validate your form. However, custom validation
functions which you have set up yourself in a model will not be
automatically translated into javascript validation rules. For these
custom functions, you will need to write a corresponding javascript
function to emulate the validation and manually add a validanguage tag
to associate the new function with the desired form field(s).


Additional Features Supported by the Validanguage Helper
````````````````````````````````````````````````````````
All of the optional features supported by the validanguage.php helper
are exposed to the user via publicly accessible class variables.

$validanguage->modelValidationBlacklist -- Populate this array with a
list of models for which validation rules should not be loaded.

$validanguage->modelValidationWhitelist -- If
$modelValidationWhitelist is left as the default empty array, then
validations for all models associated with the current controller
(minus any listed in $modelValidationBlacklist) will be loaded. If one
or more models are listed in $modelValidationWhitelist, then only
those models will be used, and any additional models associated with
the controller will be ignored.

$validanguage->skipTheseIds -- Populate this array with a list of IDs
for which validation rules should not be loaded.

$validanguage->fieldIds -- Use this array to specify that a non-
standard ID is being used for a given field, instead of the Cake
default. Provide the cake default as the key and the non-default,
custom ID as the value. For example: $fieldIds = array('ArticleBody'
=> 'blog_post');

$validanguage->transactionType -- Set this variable to either 'create'
or 'update' to properly suppress any validation rules from being
applied to the page when the model's validation rules are qualified
with 'on' => 'create' or 'on' => 'update'

$validanguage->country -- Set this to something other than "US" to
suppress US-specific validations.

$validanguage->validateCreditCardArgs -- Determines which arguments
will be passed to the validanguage.validateCreditCard function

$validanguage->validateDateArgs -- Determines which arguments will be
passed to the validanguage.validateDate function

$validanguage->extraFormSettings -- If you want to set one or more
validanguage form settings for all your pages, you can populate this
variable with the javascript to set the settings. To make validations
run onblur you can use the following:
'validanguage.settings.defaultValidationHandlers=["submit","blur"];'

$validanguage->apiType -- Controls which Validanguage API will be
used: 1 = HTML Comment API (Validanguage tags in HTML comments) AND 2
= Javascript Object API (Objects in a script tag). If you would like
to use the Object API, you will need to have the PECL json module
installed on your Web server's PHP install.


Additional Notes
````````````````
If you will be using validanguage for client-side validation, you will
definitely want to review the documentation on the project's homepage,
so that you have a good idea what exactly is going on behind the
scenes. Feel free to contact me if you have any additional
questions/comments, or leave a comment below.


Helper Code
```````````
Here is the text of version 1.0.0 of my validanguage helper. See the
project's homepage for the latest version.

::

    
    <?php
    /**
     * ValidanguageHelper
     * 
     * CakePHP helper to automatically convert model validation rules to either
     * the Validanguage Comment API or the Validanguage Object API to permit
     * automatic generation of javascript validation.
     * 
     * For details on Validanguage, see http://drlongghost.com/validanguage.php
     * For the Validanguage demo, see http://drlongghost.com/vd_tests/vd_demo1.php
     * 
     * Written by DLG (drlongghost@yahoo.com), Oct. 2008.
     * 
     * Released under the MIT License.
     * 
     * @version 1.0.0
     */
    class ValidanguageHelper extends AppHelper {
            
        /**
         * Holds the model validation info
         * @var array
         */
        var $modelValidations = array();
        
        /**
         * Controls which Validanguage API will be used:
         *     1 = HTML Comment API        (Validanguage tags in HTML comments)
         *     2 = Javascript Object API   (JS Objects in a script tag)
         * @var integer
         */
        var $apiType = 1;
        
        /**
         * Set to true to print debugging statements along with the validanguage tags
         * @var boolean
         */
        var $debug = false;
        
        /**
         * Determines which arguments will be passed to the validanguage.validateCreditCard function.
         * The first argument *must* be "text"
         * @var string
         */
        var $validateCreditCardArgs = "text, ['amex','disc','mc','visa'], true";
        
        /**
         * Determines which arguments will be passed to the validanguage.validateDate function.
         * The first argument *must* be "text"
         * @var string
         */
        var $validateDateArgs = "text, { dateOrder: 'mdy', allowedDelimeters: './-', twoDigitYearsAllowed: true }";
        
        /**
         * If you want to set one or more validanguage form settings for all your pages,
         * you can populate this variable with the javascript to set the settings.
         * To make validations run onblur you can use the following:
         *     'validanguage.settings.defaultValidationHandlers=["submit","blur"];'
         * @var string
         */
        var $extraFormSettings = '';
    
        /**
         * Set this to something other than US to remove US-specific validations.
         * @var string
         */
        var $country = 'US';
    
        /**
         * Populate this array with a list of models for which validation rules
         * should not be loaded.
         * @var array
         */
        var $modelValidationBlacklist = array();
        
        /**
         * If $modelValidationWhitelist is left as the default empty array,
         * then validations for all models associated with the current controller
         * (minus any listed in $modelValidationBlacklist) will be loaded.
         * If one or more models are listed in $modelValidationWhitelist,
         * then only those models will be used, and any additional models
         * associated with the controller will be ignored.
         * @var array
         */
        var $modelValidationWhitelist = array();
        
        /**
         * Populate this array with a list of IDs for which validation rules
         * should not be loaded.  If using the $fieldIds array below, you will
         * need to specify the IDs referenced in $fieldIds.
         * @var array
         */
        var $skipTheseIds = array();
        
        /**
         * Use this array to specify that a non-standard ID is being used for
         * a given field, instead of the Cake default. Provide the cake default
         * as the key and the non-default, custom ID as the value.
         * For example:  $fieldIds = array('ArticleBody' => 'blog_post');
         * @var array
         */
        var $fieldIds = array();
    
        /**
         * Set this variable to either 'create' or 'update' to properly
         * suppress any validation rules from being applied to the page
         * when the model's validation rules are qualified with
         * 'on' => 'create' or 'on' => 'update'
         * @var string
         */
        var $transactionType = '';
    
        /**
         * Program variable. Stores the form settings
         * @var string
         */
        var $validanguageFormSettings = '';
        
        /**
         * Program variable. Stores the validanguage API code generated from the cake models
         * @var string
         */
        var $validanguageText = '';
            
        /**
         * getTags
         * 
         * This method parses thru the model and builds validanguage tags for all the
         * validation rules.  The tags are returned as an array as follows:
         *     array(
         *         0 => validanguageFormSettings,
         *         1 => validanguageText,
         *     )
         * @return array
         */
        function getTags() {
            $this->_getTags();
            return array ($this->validanguageFormSettings, $this->validanguageText);
        }
        
        /**
         * printTags
         * 
         * This method parses thru the model and prints validanguage tags for all the
         * validation rules.
         */
        function printTags() {
            $this->_getTags();
            echo $this->validanguageFormSettings;
            echo $this->validanguageText;
        }
        
        /**
         * _getFormSettings
         * 
         * Populates a javascript script tag with all the requested customization settings
         */
        function _getFormSettings() {
            // Check to make sure json_encode() is available
            if ($this->apiType == 2 && !function_exists('json_encode')) {
                $this->apiType = 1;
                trigger_error("json_encode() PHP extension not installed. Switching to Comment API", E_USER_WARNING);
            } 
            
            $this->validanguageFormSettings = "<script type=\"text/javascript\">\n";
            $this->validanguageFormSettings .= "     validanguage.settings.onErrorClassName = 'error-message';\n";
            $this->validanguageFormSettings .= "     {$this->extraFormSettings}\n";
            $this->validanguageFormSettings .= "</script>\n";
        }
        
        /**
         * This function pulls out a list of all the relevant parts of a rule
         * which will be required by the _parseRule() method to properly translate
         * the rule from CakePHP to Validanguage.
         * 
         * @param $array1 Object
         * @param $array2 Object
         * @return array
         */
        function _getRelevantSettings( $arr1, $arr2=array(), $arr3=array() ) {
            $relevantSettingsList = array(
                'on',
                'message',
                'allowEmpty'
            );
            $relevantSettings = array();
            
            foreach ($relevantSettingsList as $setting) {
                if (is_array($arr1) && isset($arr1[$setting])) $relevantSettings[$setting] = $arr1[$setting];
                if (is_array($arr2) && isset($arr2[$setting])) $relevantSettings[$setting] = $arr2[$setting];
                if (is_array($arr3) && isset($arr3[$setting])) $relevantSettings[$setting] = $arr3[$setting];
            }
            return $relevantSettings;
        }
        
        /**
         * This method parses thru the model and populates validanguage tags for all the
         * validation rules.
         */
        function _getTags() {
            $i = -1;
            $validations = array();
            $this->_getFormSettings();
            if (empty($this->modelValidations)) $this->_loadModelValidations();
            foreach ($this->modelValidations as $model=>$fields ) {
                foreach ($fields as $field => $rules) {
                    $id = $model . Inflector::camelize($field);
                    if (array_key_exists($id, $this->fieldIds)) $id = $this->fieldIds[$id];
                    if (in_array($id, $this->skipTheseIds)) continue;
                    if ($this->debug) echo "<br/><br/> -- Checking $id -- <br/>";
                    $validations[$id] = array( 'validations' => array() );
                    
                    // There must be an easier way to iterate thru all this...
                    if (is_array($rules)) {
                        if (isset($rules[0])) {
                            // This array must be handled as a single rule
                            $this->_parseRule( $field, $rules, &$validations[$id] );
                        } else {
                         
                            foreach ($rules as $ruleName=>$ruleVal) {
                                
                                if (is_array($ruleVal)) {
                                    $relevantSettings = $this->_getRelevantSettings($rules, $ruleName, $ruleVal);
                                    
                                    if (isset($ruleVal[0])) {
                                        // This array must be handled as a single rule
                                        $this->_parseRule( $ruleName, $ruleVal, &$validations[$id], $relevantSettings );
                                    } else {
                                        foreach ($ruleVal as $ruleName2 => $ruleVal2) {
                                            $this->_parseRule( $ruleName2, $ruleVal2, &$validations[$id], $relevantSettings );
                                        }
                                    }
                                    
                                } else {
                                    $relevantSettings = $this->_getRelevantSettings($ruleName, $rules, $rules[$ruleName]);
                                    $this->_parseRule( $ruleName, $ruleVal, &$validations[$id], $relevantSettings );
                                }
                            }                          
                        }
                                            
                    } else {
                        // Single value
                        $this->_parseRule( $field, $rules, &$validations[$id] );
                    }
                }
            }
            $apiFunc = ($this->apiType==1) ? '_outputValidanguageTags' : '_outputValidanguageObjects';
            $this->{$apiFunc}($validations);
        }
        
        /**
         * loadModelValidations
         * 
         * Populates the modelValidations array with details on all the models
         * assigned to the controller
         */
        function _loadModelValidations() {
            $models = (empty($this->modelValidationWhitelist)) ? $this->params['models'] : $this->modelValidationWhitelist;
            foreach ($models as $m) {
                if (in_array($m, $this->modelValidationBlacklist)) continue;
                $model = new $m;
                if (method_exists($model, 'loadValidation')) $model->loadValidation();
                $this->modelValidations[$m] = $model->validate;
            }
            if ($this->debug == true) pr($this->modelValidations);
        }
        
        /**
         * Converts the $validations object into the validanguage Object API
         * and prints it in a script tag.
         * @param $validations Object
         */
        function _outputValidanguageObjects($validations) {
            if ($this->debug) pr($validations);
            $this->validanguageText = "<script type=\"text/javascript\">\n";
            foreach( $validations as $id => $rules ) {
                $this->validanguageText .= "     validanguage.el['{$id}'] = " . json_encode($rules) . ";\n";
            }
            $this->validanguageText .= "</script>\n";
        }
        
        /**
         * Converts the $validations object into the validanguage Comment API
         * and prints the tags to the page
         * @param $validations Object
         */
        function _outputValidanguageTags($validations) {
            foreach( $validations as $id => &$rules ) {
                $addOns = array('minlength','maxlength','required');
                foreach ($addOns as $addOn) {
                    if (isset($rules[$addOn])) {
                        $tag = "\n<!-- <validanguage target=\"{$id}\" ";
                        if ($addOn==='required') {
                            $tag .= "{$addOn}=\"true\" ";
                        } else {
                            $tag .= "{$addOn}=\"{$rules[$addOn]}\" ";
                        }
                        $tag .= " /> -->";
                        $this->validanguageText .= $tag;
                    }
                    unset($tag);
                }
                       
                foreach( $rules['validations'] as $validation ) {              
                    $tag = "\n<!-- <validanguage target=\"{$id}\" ";
                    
                    $tag .= "validations=\"{$validation['name']}\" ";
                    if (isset($validation['errorMsg'])) {
                        $tag .= "errorMsg=\"{$validation['errorMsg']}\" ";
                    } else if (isset($rules['errorMsg'])) {
                        $tag .= "errorMsg=\"{$rules['errorMsg']}\" ";
                    }
                    $tag .= " /> -->";
                    
                    $empty = "<!-- <validanguage target=\"{$id}\"  /> -->\n";
                    if (isset($tag) && $tag !== $empty) $this->validanguageText .= $tag;
                    unset($tag);
                }
            }
        }
        
        /**
         * _parseRule
         * 
         * This method handles a single CakePHP rule and pushes the corresponding validanguage
         * rule onto the $validations array.
         * 
         * @param object $key
         * @param object $val
         * @param object $validations
         * @param object $relevantSettings optional
         */
        function _parseRule($key, $val, $validations, $relevantSettings=array() ) {
            if (isset($relevantSettings['on']) && $relevantSettings['on'] !== $this->transactionType) return;
            if ($this->debug) {
                echo " <br/>key = $key and val = $val with ";
                print_r($relevantSettings);
            }
            if (is_array($val) && isset($val[0])) {
                // handle arrays
                if ($this->debug) pr($val);
                
                // between
                if($val[0]=='between') {
                    $max = ($val[1]>$val[2]) ? $val[1] : $val[2];
                    $min = ($val[1]<$val[2]) ? $val[1] : $val[2];
                    if (!empty($relevantSettings['message'])) {
                        $newFunc = array(
                            'name'     => "validanguage.validateMaxlength(text,{$max}), validanguage.validateMinlength(text,{$min})",
                            'errorMsg' => $relevantSettings['message'],
                        );
                    } else {
                        $validations['minlength'] = $min;
                        $validations['maxlength'] = $max;
                    }
                }
                
                // minLength/maxLength
                if($val[0]=='minLength' || $val[0]=='maxLength') {
                    if (!empty($relevantSettings['message'])) {
                        $func = ($val[0]=='minLength') ? 'Minlength': 'Maxlength';
                        $newFunc = array(
                            'name'     => "validanguage.validate{$func}(text,{$val[1]})",
                            'errorMsg' => $relevantSettings['message'],
                        );
                    } else {
                        $func = ($val[0]=='minLength') ? 'minlength': 'maxlength';
                        $validations[$func] = $val[1];
                    }
                }
                if (!empty($newFunc)) {
                    $validations['validations'][] = $newFunc;
                } else {
                    $func_args = $val;
                    $val = array_shift($val); // Reset $val to $val[0] and check for the validations below        
                }
            } // close if is_array()
            
            if (($key==='required' && $val===true) || ($key==='allowEmpty' && $val===false) || ($val===VALID_NOT_EMPTY)) {
                // required
                if (!empty($relevantSettings['message'])) {
                    $newFunc = array(
                        'name' => 'validanguage.validateRequired',
                    );       
                } else {
                    $validations['required'] = true;
                }
            } else if ($key==='min' || $key==='max') {
                // minlength/maxlength
                if (!empty($relevantSettings['message'])) {
                    $func = ($key=='min') ? 'Minlength': 'Maxlength';
                    $newFunc = array(
                        'name' => "validanguage.validate{$func}(text,{$val})",
                    );       
                } else {
                    $func = ($key=='min') ? 'minlength': 'maxlength';
                    $validations[$func] = $val;
                }
            } else if (is_string($val) && (substr($val,0,1)==='/')) {
                // regexes
                $val = str_replace(array('\\A','\\b','\\b','\\z'),'',$val); // strip out crap that js cant use
                $val = substr($val, 1); // strip out the leading and trailing slashes
                $val = substr($val, 0, strrpos($val,'/') );
                $val = str_replace("\\", "\\\\", $val); // escape the slashes
                $val = str_replace("'", "\'", $val); // escape the apostrophes
                $newFunc = array(
                    'name' => "validanguage.validateRegex(text, { expression: '{$val}' })",
                );
            } else {
                // These validations are all handled easily enough
                $easilyHandled = array(
                    'alphaNumeric'   => "validateRegex(text, { expression: /[^0-9a-zA-Z]/, errorOnMatch: true })",
                    'blank'          => "validateRegex(text, { expression: /[^\\w]/, errorOnMatch: true })",
                    'cc'             => "validateCreditCard( {$this->validateCreditCardArgs} )",
                    'date'           => "validateDate( {$this->validateDateArgs} )",
                    'email'          => 'validateEmail',
                    'ip'             => 'validateIP',
                    'max'            => 'validateMaxlength(text,{$val[1]})',
                    'min'            => 'validateMinlength(text,{$val[1]})',
                    'numeric'        => 'validateNumeric',
                    'phone'          => 'validateUSPhoneNumber',
                    'postal'         => 'validateUSZipCode',
                    'ssn'            => 'validateUSSSN',
                    'url'            => 'validateURL',
                );
                if ($this->country !== 'US') {
                    unset($easilyHandled['phone']);
                    unset($easilyHandled['postal']);
                    unset($easilyHandled['ssn']);
                }
                foreach ($easilyHandled as $provided=>$funcName) {
                    if ( $val === $provided) {
                        if ($this->debug) echo "MATCH on $provided<br/>";
                        $newFunc = array(
                            'name' => "validanguage.{$funcName}",
                        );
                        break;
                    }
                }
            }
            if (!empty($newFunc)) {
                if (!empty($relevantSettings['message'])) $newFunc['errorMsg'] = $relevantSettings['message'];        
                $validations['validations'][] = $newFunc;
            }
        }
    }
    ?>



Validanguage version 0.9.6
``````````````````````````
Here is the text of version 0.9.6 of validanguage.js. See the
project's homepage for the latest version.
[code] /**
* The validanguage library was written by DrLongGhost in 2008. See
attached MIT_License.js
* and readme.txt for licensing and documentation. Visit
`http://www.drlongghost.com/`_ for updates.
*
*
* @namespace Global validanguage object
* @author DrLongGhost
* @version 0.9.6
*/
var validanguage = {
/**
* Valid values are 'none', 'prototype', and 'scriptaculous'.
* @public
* @default 'none'
*/
useLibrary: 'none',

/**
* @private
*/
version: '0.9.6',

/**
* @namespace validanguage.settings object
*/
settings: {
/**
* Should an alert() be shown when a validation fails?
* By default, validanguage.showError() and validanguage.hideError()
instead place the
* error msg underneath the failed field.
* @default false
*/
showAlert: false,

/**
* Should the target element of a failed validation receive focus when
a validation fails?
* IMPORTANT note regarding showAlert and focusOnError. Do NOT set both
of these to true if using onblur validations. Pick either one or the
other.
* When you use both, it is possible to create infinite loops in which
a validation failure generates an alert, triggering an onblur,
* which triggers another validation failure and subsequent alert.
* If you aren't using onblur validations at all, you can safely use
both.
* @default false
*/
focusOnerror: false,

/**
* When a form is submitted, are all form fields validated, or do we
stop once one fails?
* @default true
*/
validateAllFieldsOnsubmit: true,

/**
* Override this to set a global success handlers for all validation
results
* If you want to use only alert messages via showAlert, set this to {}
to turn off inline error msgs
* @default 'validanguage.hideError'
*/
onsuccess: 'validanguage.hideError',

/**
* Override this to set a global error handler for all validation
results
* If you want to use only alert messages via showAlert, set this to {}
to turn off inline error msgs
* @default 'validanguage.showError'
*/
onerror: 'validanguage.showError',

/**
* Default generic error message
* @default 'You have entered an invalid entry in the form'
*/
errorMsg: 'You have entered an invalid entry in the form',

/**
* Default error message for the validateRequired validation
* @default 'You have skipped a required field'
*/
requiredErrorMsg: 'You have skipped a required field',

/**
* Default error message for the validateMinlength validation
* @default 'The indicated field must be at least {!minlength}
characters long'
*/
minlengthErrorMsg: 'The indicated field must be at least {!minlength}
characters long',

/**
* Default error message for the validateMaxlength validation
* @default 'The indicated field may not be longer than {!maxlength}
characters'
*/
maxlengthErrorMsg: 'The indicated field may not be longer than
{!maxlength} characters',

/**
* Default error message for the validateCharacters function
* @default 'You have entered invalid characters'
*/
characterValidationErrorMsg: 'You have entered invalid characters',

/**
* Class name used in showError() to assign to the DIVs
* which are created to show the inline error msgs.
* @default 'vdError'
*/
onErrorClassName: 'vdError',

/**
* Class name used in hideError() to assign to a DIV
* which was created to show an inline error msgs which is then
removed.
* @default 'vdNoError'
*/
noErrorClassName: 'vdNoError',

/**
* Class name used in hideError() to assign to a form field which
passes validation
* @default 'passedField'
*/
passedFieldClassName: 'passedField',

/**
* Class name used in showError() to assign to a form field which fails
validation
* @default 'failedField'
*/
failedFieldClassName: 'failedField',

/**
* Used to make the ID used in hideError() to assign to the SPAN
element inside the vdError
* DIV. The errorMsgSpanSuffix is appended to the end of the form
field's ID to make the SPAN ID.
* If a SPAN with this ID already exists in the DOM, it will be used.
If it doesn't exist, one will
* be created dynamically.
* @default '_errorMsg'
*/
errorMsgSpanSuffix: '_errorMsg',

/**
* To display a combined list of all fields which failed validation in
addition to the
* inline error msgs, set showFailedFields to true. The fields will be
listed using the
* "field" attribute (or ID if field is not available).
* @default false
*/
showFailedFields: false,

/**
* The text specified in errorListText will be placed at the top of the
errorDiv generated
* by the showFailedFields option in showError().
* @default ' Please correct the following fields: '
*/
errorListText: ' Please correct the following fields: ',

/**
* Specifies the ID to be assigned to the DIV used for the
showFailedFields option in showError().
* If a DIV with this ID exists in the DOM, it will be used. If it
doesn't exist, one will
* be created dynamically.
* @default 'vdErrorDiv'
*/
errorDivId: 'vdErrorDiv',

/**
* Specifies the ID to be assigned to the UL used for the
showFailedFields option in showError().
* @default 'vdErrorList'
*/
errorListId: 'vdErrorList',

/**
* Used to make the ID used for the showFailedFields option in
showError().
* The errorListItemSuffix is appended to the end of the form field's
ID to make the ID for the LI item.
* @default '_vd_li'
*/
errorListItemSuffix: '_vd_li',

/**
* Determines the ID of the DIV created in the showSubmitMessage()
function used to
* replace a form's submit button once the form has been submitted.
* @default 'vdSubmitMessage'
*/
showSubmitMessageId: 'vdSubmitMessage',

/**
* Determines the text used by the showSubmitMessage() function which
is used
* replace a form's submit button once the form has been submitted. If
desired, you can include HTML
* or IMG tags instead of the default text.
* @default 'Loading'
*/
showSubmitMessageMessage: 'Loading',

/**
* This array is used in the validateRequired function to determine
whether a select box
* has been left on the default, "empty" option. Add/Remove from this
array as needed.
* @default ['','0',' ',''] */
emptyOptionElements: ['','0',' ',''],

/**
* If a validation is supplied without any event handlers, how should
it be treated in loadElAPI()?
* This setting also affects the behavior of the required=true and
maxlength/minlength shortcuts.
* @default ['submit'] */
defaultValidationHandlers: ['submit'],

/**
* Should any validanguage.toggle() transformations which are defined
for form fields on the
* page be automatically called when the page has finished loading.
* @default true
*/
callToggleTransformationsOnload: true,

/**
* Should the toggle visibility API in validanguage.toggle() default to
"hidden" if a given target
* does not satisfy any provided "visible" conditions? If you set this
to false, you will need to
* explicitly provide the desired "hidden" conditions.
*/
toggleVisibilityDefaultsToHidden: true,

/**
* Should the HTML document be scanned for validanguage comment tags?
* Set this to false if you arent using the comment API for better
performance.
* @default true
*/
loadCommentAPI: true,

/**
* Determines the delimeter used in the loadCommentAPI() function to
split up each
* comment into multiple validanguage tags.
* You probably want to keep this as "\n" to be safe, but if you want
to be allowed
* to use carriage returns inside validanguage comment tags, you can
set this to
* "/>" if you are careful to always close your validanguage tags
* @default "\n"
*/
commentDelimiter: "\n",

/**
* Color for the textbox to flash when invalid input is entered. The
default is light red.
* Set this to empty to turn flashing off.
* @default '#FF6666'
*/
validationErrorColor : '#FF6666',

/**
* Normal color of the textbox. The default is empty. Used in
conjunction with validationErrorColor
* to make the textboxes flash.
* @default ''
*/
normalTextboxColor : '',

/**
* Amount of time the text box flashes the validationErrorColor. The
default is 100ms
* @default 100
*/
timeDelay : 100,

/**
* Typing delay for the ontyping event. This is the amount of time
between keystrokes
* that must elapse before the event fires. The default is just over 1
second.
* @default 1100
*/
typingDelay: 1100,

/**
* Should the validateRequiredAlternatives function be assigned onclick
to radio buttons
* and checkboxes named as "requiredAlternatives"? Setting this to true
ensures that
* checking/unchecking a radio button or checkbox will correctly call
showError/hideError.
* @default true
*/
validateRequiredAlternativesOnclick: true,

/**
* Defines the default behavior of the validateRegex function.
* Is a match against the regex an error or a success?
* @default false
*/
errorOnMatch: false,

/**
* Override this to setup a function to run after all validanguage form
fields have
* been intialized inside the populate() function. The default is an
empty function.
* @default function() { }
*/
onload: function() { },

//dummy field I put here so the onload above will have a comma after
it
foo: ''
},

//PRIVATE PROGRAM VARIABLES
alertCounter: true, //this counter prevents infinite loops from being
created between alerts() and onblur handlers
el: {},
forms: {},
formLookup: {}, //hash table to map form element IDs to the ID of the
parent form.
requiredAlternatives: [], //hash table used to store
requiredAlternatives associations
supportedEvents: ['blur','change','keypress','keyup','keydown','submit
','click','typing'],
supportedEventHandlers: ['onblur','onchange','onkeypress','onkeyup','o
nkeydown','onsubmit','onclick','ontyping'],
typingDelay: [], //hash table to store ontyping timeouts

/**
* Generic cross-browser addEvent() function.
*
* @param {Object} Object to receive the event
* @param {Object} Event type
* @param {Object} Function to be called
*/
addEvent: function(obj, event, func){
if (obj.addEventListener) {
obj.addEventListener(event, func, false);
return true;
} else if (obj.attachEvent){
var newEvent = obj.attachEvent("on"+event, func);
return newEvent;
}
},

/**
* Reassigns the validanguage.addEvent function, if an external library
is being used.
*/
addEventInit: function() {
switch ( this.useLibrary ) {
case 'prototype':
case 'scriptaculous':
//reassign the addEvent function to use Event.observe
this.addEvent = function(obj, evtHandler, func){
Event.observe(obj, evtHandler, func);
}
break;
}
},

/**
* This function wraps multiple validanguage.el.elemId.validations
event handlers
* and transformations within a single wrapper to call all loaded
validations/transformations
* and exit as soon as a validation returns false.
*
* @param {Object} Form element object
* @param {string} eventType, such as "blur" or "keydown"
* @param {integer} validationsCounter, denotes the array index of this
item in
* validanguage.el.elemId.validations
*/
addOrCreateValidationWrapper: function( Obj, eventType,
validationsCounter ) {
var id = Obj.id;

if (eventType == 'submit') {
if (this.empty(validationsCounter)) return; // exit early for onsubmit
transformations
var formId = validanguage.formLookup[id];
if (typeof formId == 'number') {
var form = document.forms[formId];
} else {
var form = document.getElementById(formId);
}
if (typeof validanguage.forms[formId].validations == 'undefined') {
validanguage.forms[formId].validations = [];
this.addEvent(form, eventType, function(e) {
var evt = e || window.evt;
var result = validanguage.validationWrapper(e);
if (result == false) {
evt.returnValue = false; //IE
if (evt.preventDefault) evt.preventDefault(); //Everyone else
return false;
} else {
return true;
}
});
}
//add the element and validationsCounter to the list of onsubmit
validations for the parent form
validanguage.forms[formId].validations[validanguage.forms[formId].vali
dations.length] = { element: Obj, validationsCounter:
validationsCounter };
} else {

if( typeof validanguage.el[id].handlers == 'undefined' )
validanguage.el[id].handlers = {};
if( typeof validanguage.el[id].handlers[eventType] == 'undefined' ) {
validanguage.el[id].handlers[eventType] = [];
if( eventType == 'typing') {
this.addEvent(Obj, 'keyup', function(e){
validanguage.validationWrapper(e, 'typingTimeout'); });
} else {
this.addEvent(Obj, eventType, function(e){
validanguage.validationWrapper(e); });
}
}
//add validationsCounter to the list of validations for this
object/eventType combo
validanguage.el[id].handlers[eventType][validanguage.el[id].handlers[e
ventType].length] = validationsCounter;
}
},

/**
* This function is used to either load a new validation for a form
field, or to
* reactivate a validation previously removed with the
removeValidation() method.
*
* NOTE: When adding a new validation, you will need to have previously
inserted
* all the relevant details about the validation in the
validanguage.el.formField
* object.
*
* @param {String} elemId
* @param {String/Array} eventTypes
* @param {String/Array/Function} validationNames
*/
addValidation: function ( elemId, eventTypes, validationNames ) {
if( typeof validationNames[0]=='undefined' ) validationNames = [
validationNames ];
if( typeof eventTypes=='string' ) eventTypes = [ eventTypes ];

var vals = this.el[elemId].validations;
for (var i = vals.length - 1; i > -1; i--) {
if ( validationNames[0] == '*' || this.inArray(vals[i].name,
validationNames) ) {
for( var j=eventTypes.length-1; j>-1; j--) {
this.addOrCreateValidationWrapper(document.getElementById(elemId),
eventTypes[j], i);
}
}
}
},

/**
* Very simple AJAX function
* @param {String} url
* @param {Function} callback
*/
ajax: function( url, callback ) {
validanguage.ajaxObj.open("POST", url, true);
this.ajaxCallback = callback;
this.ajaxObj.onreadystatechange = function() {
if(validanguage.ajaxObj.readyState==4){
validanguage.ajaxCallback(validanguage.ajaxObj.responseText)
}
};
this.ajaxObj.send(null);
},

/**
* Initializes validanguage.ajax as browser-specific
*/
ajaxInit: function() {
if(window.ActiveXObject){
this.ajaxObj = new ActiveXObject("Microsoft.XMLHTTP");
} else if(window.XMLHttpRequest){
this.ajaxObj = new XMLHttpRequest();
}
},

/**
* Combines 2 node lists into 1
* @param {Object} obj1
* @param {Object} obj2
*/
concatCollection: function(obj1,obj2) {
var i;
var arr = new Array();
var len1 = obj1.length;
var len2 = obj2.length;
for (i=0; i
arr.push(obj1[i]);
}
for (i=0; i
arr.push(obj2[i]);
}
return arr;
},

/**
* Emulates PHP's empty() function. For convenience, you can specify
whether
* boolean false is considered empty. Defaults to false is NOT empty.
* Ignores functions.
*
* @param {Object} testVar
* @param {bool} falseIsEmpty
*/
empty: function ( testVar, falseIsEmpty ) {
if( testVar == null || testVar == undefined || testVar == NaN ||
(testVar =='' && typeof testVar == 'string') ) return true;
if( falseIsEmpty==true && testVar==false) {
return true;
}
if(typeof testVar == 'object') {
for (var i in testVar) {
if( typeof testVar[i] == 'function' ) continue;
if( validanguage.empty(testVar[i], falseIsEmpty)==false ) {
return false;
}
}
return true;
} else {
return false;
}
},

/**
* This is a preset transformation which is used to reformat text input
* to match a desired pattern
* @param {String} Pattern using x to represent alphanumeric
characters.
* For example: "(xxx) xxx-xxxx"
* @param {String} String listing any characters to be removed from the
* form field's value prior to potential reformatting
* For example: "()- "
* @param {String/Regex} Regular expression which, if provided, will be
used
* to determine whether or not to proceed with reformatting.
* If not provided, the function will only reformat if the number
* of characters in the form field (after stripThese is applied)
* matches the number of x's in the provided pattern
*/
format: function( pattern, stripThese, regexMatch ) {
var text = this.value;
if(stripThese!=null && typeof stripThese=='string') {
var i = stripThese.length;
for( var i=stripThese.length-1; i>-1; i-- ) {
while (text.indexOf(stripThese.charAt(i)) != -1) {
text = text.replace(stripThese.charAt(i),'','g');
}
}
}
if( regexMatch!=null ) {
var myreg = (typeof regexMatch=='string') ? new RegExp(regexMatch) :
regexMatch;
var thisMatch = myreg.exec(text);
if (thisMatch == null) return; //exit early for no match
} else {
//check for required length based on number of x's in the pattern
var countMe = pattern.replace(/[^x]/g,'');
if( text.length != countMe.length ) return;
}
var i = pattern.length;
var k = -1; //counter for text
var newtext = '';
for( var j=0; j
newtext += (pattern.charAt(j)=='x') ? text.charAt(++k) :
pattern.charAt(j);
}
this.value = newtext;
},

/**
* This function is one big ass switch case to look up a char code
* for the supplied character
* @param {String} suppliedCharacter
*/
getCharCode: function( suppliedCharacter ){
switch(suppliedCharacter){
case ' ': return '32';
case '!': return '33';
case '"': return '34';
case '#': return '35';
case '$': return '36';
case '%': return '37';
case '&': return '38';
case "'": return '39';
case '(': return '40';
case ')': return '41';
case '*': return '42';
case '+': return '43';
case ',': return '44';
case '-': return '45';
case '.': return '46';
case '/': return '47';
case '0': return '48';
case '1': return '49';
case '2': return '50';
case '3': return '51';
case '4': return '52';
case '5': return '53';
case '6': return '54';
case '7': return '55';
case '8': return '56';
case '9': return '57';
case ':': return '58';
case ';': return '59';
case '<': return '60';
case '=': return '61';
case '>': return '62';
case '?': return '63';
case '@': return '64';
case 'A': return '65';
case 'B': return '66';
case 'C': return '67';
case 'D': return '68';
case 'E': return '69';
case 'F': return '70';
case 'G': return '71';
case 'H': return '72';
case 'I': return '73';
case 'J': return '74';
case 'K': return '75';
case 'L': return '76';
case 'M': return '77';
case 'N': return '78';
case 'O': return '79';
case 'P': return '80';
case 'Q': return '81';
case 'R': return '82';
case 'S': return '83';
case 'T': return '84';
case 'U': return '85';
case 'V': return '86';
case 'W': return '87';
case 'X': return '88';
case 'Y': return '89';
case 'Z': return '90';
case '[': return '91';
case '\\': return '92';
case ']': return '93';
case '^': return '94';
case '_': return '95';
case '`': return '96';
case 'a': return '97';
case 'b': return '98';
case 'c': return '99';
case 'd': return '100';
case 'e': return '101';
case 'f': return '102';
case 'g': return '103';
case 'h': return '104';
case 'i': return '105';
case 'j': return '106';
case 'k': return '107';
case 'l': return '108';
case 'm': return '109';
case 'n': return '110';
case 'o': return '111';
case 'p': return '112';
case 'q': return '113';
case 'r': return '114';
case 's': return '115';
case 't': return '116';
case 'u': return '117';
case 'v': return '118';
case 'w': return '119';
case 'x': return '120';
case 'y': return '121';
case 'z': return '122';
case '{': return '123';
case '|': return '124';
case '}': return '125';
case '~': return '126';
} //close switch
return '';
},

/**
* Fetches all comment nodes in the passed form node and returns them
in a node list
* Doesnt work in konqueror, since konqueror strips all comments from
the DOM
*
* @param {Containing Node} el
*/
getComments: function(el) {
if (!el) el = document.documentElement;
var comments = new Array();
var length = el.childNodes.length;
for (var c = 0; c < length; c++) {
if (el.childNodes[c].nodeType == 8) {
comments[comments.length] = el.childNodes[c];
} else if (el.childNodes[c].nodeType == 1) {
comments = comments.concat(this.getComments(el.childNodes[c]));
}
}
return comments;
},

/**
* Helper function used by validateDate() and validateTimestamp().
* @param {Object} options object provided by the user to
validateDate() or validateTimestamp().
* @param {Object} defaults which should be used. Used to allow
validateDate() and validateTimestamp()
* to have different default dateOrder values.
*/
getDateTimeDefaultOptions: function ( options, defaults ) {
if( options==null ) options = {};

// Date options
if( typeof options.dateOrder=='undefined' )
options.dateOrder=defaults.dateOrder;
options.dateOrder = options.dateOrder.toLowerCase();
if( typeof options.allowedDelimiters=='undefined' || typeof
options.allowedDelimiters!='string' ) options['allowedDelimiters'] =
'./-';
if( typeof options.twoDigitYearsAllowed=='undefined' )
options.twoDigitYearsAllowed = false;
if( typeof options.oneDigitDaysAndMonthsAllowed=='undefined' )
options.oneDigitDaysAndMonthsAllowed = true;
if( typeof options.maxYear=='undefined' ) options.maxYear = new
Date().getFullYear() + 15;
if( typeof options.minYear=='undefined' ) options.minYear = 1900;
if( typeof options.rejectDatesInTheFuture=='undefined' )
options.rejectDatesInTheFuture = false;
if( typeof options.rejectDatesInThePast=='undefined' )
options.rejectDatesInThePast = false;

// Time options
if( typeof options.timeIsRequired=='undefined' )
options.timeIsRequired = false;
if( typeof options.timeUnits=='undefined' ) options.timeUnits = 'hms';
if( typeof options.microsecondPrecision=='undefined' )
options.microsecondPrecision = 6;
return options;
},

/**
* This function checks for a given setting in increasing specificity
* within the validanguage.forms[formId].settings object, and within
the passed
* validanguage.el objects
*
* @param {string} Name of the setting to be retrieved
* @param {string} ID of the form field object being validated
* @param {Object} validanguage.el.objId.validations[index] object
*/
getElSetting: function( setting, id, validationObj ) {
var formSetting = this.getFormSettings(id);
var retVal = formSetting[setting]; //global setting
if( typeof validationObj!='undefined' && typeof validationObj[setting]
!= 'undefined' ) {
retVal = validationObj[setting];
} else if( typeof this.el[id][setting] != 'undefined' ) {
retVal = this.el[id][setting];
}
return retVal;
},

/**
* This function returns the validanguage.form[formId].setting object
for the passed element ID
* @param {string or Node} id of the input field or input node
* @return {Object} settings object
*/
getFormSettings: function(id) {
var formName = (
document.getElementById(id).nodeName.toLowerCase()=='form' ) ?
id : this.formLookup[id];
return this.forms[formName].settings;
},

/**
* This function parses the passed comment to retrieve the indicated
setting
*
* @param {String} Name of the setting to retrieve / needle
* @param {String} Full text of the HTML comment / haystack
* @return {String} The value of the requested setting
*/
getSettingFromComment: function( setting, comment ) {
var needle = ' '+setting+'=';
var startPos = comment.indexOf(needle);
if( startPos == -1) return null;
var delimiterPos = (startPos*1) + (needle.length*1);
var delimeter = '\\' + comment.charAt(delimiterPos);
var Regex = needle+delimeter+'(.+?)'+delimeter;
var myreg = new RegExp(Regex);
var thisMatch = myreg.exec(comment, 'gi');
if (thisMatch == null) {
return null; //no match
} else if (thisMatch[1]) {
//Convert booleans. I hope this doesnt screw anyone later....
if(thisMatch[1]=='true') thisMatch[1]=true;
if(thisMatch[1]=='false') thisMatch[1]=false;
return thisMatch[1];
}
},

/**
* This function hides the div containing the validanguage error
messages for
* failed validations
*/
hideError: function() {
var settings = validanguage.getFormSettings(this.id);
var errorDisplay = document.getElementById(this.id +
settings.errorMsgSpanSuffix);
if (errorDisplay != null) {
errorDisplay.innerHTML = '';
var errorDiv = errorDisplay.parentNode;

errorDiv.style.display = 'none';
errorDiv.className = settings.noErrorClassName;
}
if (!
this.className.match(validanguage.settings.passedFieldClassName))
this.className += ' '+validanguage.settings.passedFieldClassName;
if (this.className.match(validanguage.settings.failedFieldClassName))
this.className =
this.className.replace(validanguage.settings.failedFieldClassName,'');

//Do we need to remove any vd_li items?
if( !settings.showFailedFields ) return;
if( document.getElementById(this.id + settings.errorListItemSuffix) !=
null ) {
var errorList = document.getElementById(settings.errorListId);
errorList.removeChild( document.getElementById(this.id +
settings.errorListItemSuffix) );
if( errorList.getElementsByTagName('LI').length==0 )
document.getElementById(settings.errorDivId).style.display='none';
}
},

/**
* Determines whether the passed item is present in the array or
object.
*
* @param {Object} needle
* @param {Object} haystack
*/
inArray: function( needle, haystack ) {
for( var i=haystack.length-1; i>-1; i-- ){
if( haystack[i]===needle ) return true;
}
return false;
},

/**
* This function searches settingsHaystack for all variables defined in
the settingsNeedles
* array, and if they are located, they are copied over to the
settingsTarget
*
* @param {Object} settingsHaystack -- Object location to be searched
for settings
* @param {Array} settingsNeedles -- Array of settings to be checked
* @param {Object} settingsTarget -- Object location where any defined
settings should be copied to
* @param {String} constrainType -- Optional type constraint
*/
inheritIfDefined: function ( settingsHaystack, settingsNeedles,
settingsTarget, constrainType ) {
if( typeof settingsNeedles.length == 'undefined' ) return false;
for( var i=settingsNeedles.length-1;i>-1;i--) {
if ( typeof settingsHaystack[settingsNeedles[i]]!='undefined' &&
( this.empty(constrainType) || typeof
settingsHaystack[settingsNeedles[i]]==constrainType )
) {
settingsTarget[settingsNeedles[i]] =
settingsHaystack[settingsNeedles[i]];
}
}
},

/**
* Initialization function for validanguage. Adds the onload hook
* which fires off the populate() method to add all the other event
* handlers
*/
init: function() {
this.addEventInit();
this.ajaxInit();
this.addEvent(window, 'load', function() {
validanguage.populate.call(validanguage);
});
},

/**
* Function to insert 1 Node after another in the DOM. If the
referenceNode
* is a label, this function will use the nextSibling instead
*
* @param {Node} nodeToAdd
* @param {Node} referenceNode
*/
insertAfter: function (nodeToAdd, referenceNode ) {
if (referenceNode.nextSibling) {
if (referenceNode.nextSibling.nodeName.toLowerCase() == 'label') {
referenceNode.parentNode.insertBefore(nodeToAdd,
referenceNode.nextSibling.nextSibling);
} else {
referenceNode.parentNode.insertBefore(nodeToAdd,
referenceNode.nextSibling);
}
} else {
referenceNode.parentNode.appendChild(nodeToAdd);
}
},

/**
* This function parses all comments in the current document, looking
for
* the comment-based API and converts any validanguage statements it
* finds into the element/json-based API for further processing.
*
* @param {Array} For konqueror, we pass this function an Array with
all
* the comments (retrieved via AJAX)
* For all other browsers, konquerorComments is undefined and
* we retrieve the comments normally via the DOM
*/
loadCommentAPI: function( konquerorComments ) {
var supportedSettings =
['mode','expression','suppress','onsubmit','onblur','onchange',
'onkeypress','onkeyup','onkeydown','onclick', 'ontyping',
'errorMsg','onerror','onsuccess','focusOnError',
'showAlert','required','requiredAlternatives',
'maxlength','minlength','regex','field',
'errorOnMatch','modifiers','transformations','validations'];

var allComments = (this.empty(konquerorComments)) ? this.getComments()
: konquerorComments;
var length = allComments.length;
for (var j=0; j

var singleComment = (this.empty(konquerorComments)) ?
allComments[j].nodeValue : allComments[j];
var tagArray =
singleComment.split(validanguage.settings.commentDelimiter);
var tagArrayLength = tagArray.length;

for (var a=0; a
var commentText = tagArray[a];
commentText = commentText.replace(/\n/g,'');
commentText = commentText.replace(/\r/g,'');

var isValidanguageRegEx = / i;<br > if
(isValidanguageRegEx.test(commentText)) {
//get the targets
var targets = this.getSettingFromComment('target', commentText);
var settings = []; //reset settings
if (this.empty(targets, true))
continue;
targets = this.resolveArray(targets, 'string');
for (var k = supportedSettings.length - 1; k > -1; k--) {
var tempSetting = this.getSettingFromComment(supportedSettings[k],
commentText);
if (!(tempSetting == null || (typeof tempSetting == 'string' &&
tempSetting == '') ))
settings[supportedSettings[k]] = tempSetting;
}

//iterate thru our targets and assign the settings
k = targets.length;
for (var l = 0; l < k; l++) {
var id = targets[l];
var obj = document.getElementById(id);
if (typeof this.el[id] == 'undefined' || obj == null)
this.el[id] = {};

/** CHARACTER VALIDATION **/
if (typeof settings.expression != 'undefined') {
this.el[id].characters = {};
this.inheritIfDefined(settings,
['expression','errorMsg','mode','suppress','onerror','onsuccess'],
this.el[id].characters);
this.inheritIfDefined(settings, this.supportedEventHandlers,
this.el[id].characters);
}

/** REGEX **/
if (typeof settings.regex != 'undefined') {
this.el[id].regex = { expression: settings.regex };
this.inheritIfDefined(settings, ['errorOnMatch','modifiers'],
this.el[id].regex);
this.inheritIfDefined(settings, this.supportedEventHandlers,
this.el[id].regex);
}

/** MISC SETTINGS **/
// Only inherit event handlers that are non-boolean transformations
this.inheritIfDefined(settings, this.supportedEventHandlers,
this.el[id], 'string');
this.inheritIfDefined(settings, ['minlength','maxlength','requiredAlte
rnatives','required','focusOnError','showAlert',
'onsuccess','onerror','errorMsg'], this.el[id]);
if (typeof settings.minlength != 'undefined') {
this.el[id].minlengthEvents = {};
this.inheritIfDefined(settings, this.supportedEventHandlers,
this.el[id].minlengthEvents);
}
if (typeof settings.maxlength != 'undefined') {
this.el[id].maxlengthEvents = {};
this.inheritIfDefined(settings, this.supportedEventHandlers,
this.el[id].maxlengthEvents);
}
if (typeof settings.required != 'undefined') {
this.el[id].requiredEvents = {};
this.inheritIfDefined(settings, this.supportedEventHandlers,
this.el[id].requiredEvents);
}

/** VALIDATIONS AND TRANSFORMATIONS **/
if (typeof this.el[id].validations == 'undefined')
this.el[id].validations = [];
if (typeof this.el[id].transformations == 'undefined')
this.el[id].transformations = [];
var functionModifiers =
['focusOnError','showAlert','onsuccess','onerror','errorMsg'];

//Load validations
if( typeof settings.validations != 'undefined' &&
!this.empty(settings.validations) ) {
this.el[id].validations[this.el[id].validations.length] = {};
this.el[id].validations[this.el[id].validations.length-1].name =
settings.validations;
this.inheritIfDefined(settings, this.supportedEventHandlers,
this.el[id].validations[this.el[id].validations.length-1]);
this.inheritIfDefined(settings, functionModifiers,
this.el[id].validations[this.el[id].validations.length-1]);
}
//Load transformations
if( typeof settings.transformations != 'undefined' &&
!this.empty(settings.transformations) ) {
this.el[id].transformations[this.el[id].transformations.length] = {};
this.el[id].transformations[this.el[id].transformations.length-1].name
= settings.transformations;
this.inheritIfDefined(settings, this.supportedEventHandlers,
this.el[id].transformations[this.el[id].transformations.length-1]);
}

} // foreach (targets)
} // close if(validanguage_comment)
} // close tagArray loop
} // close allComments loop
},

/**
* This function parses the validanguage.el object to load all the
* form-element-specific validation settings which the end user has
defined
* via the Object-based API
*/

loadElAPI: function() {
for( var elem in this.el ) { //for each element....
//skip to the next if it's not an element ID
try { if( typeof document.getElementById(elem) == undefined ||
this.empty(document.getElementById(elem)) ) continue; } catch(e) {
continue; }
var Obj = document.getElementById(elem);
var settings = validanguage.getFormSettings(elem);
if (typeof this.el[elem].validations == 'undefined')
this.el[elem].validations = [];
if (typeof this.el[elem].field == 'undefined') this.el[elem].field =
elem;

/** REQUIRED **/
if (typeof this.el[elem].required != 'undefined' &&
this.el[elem].required==true) {
this.el[elem].validations[this.el[elem].validations.length] = {};
this.el[elem].validations[this.el[elem].validations.length-1].name =
'validanguage.validateRequired';
this.el[elem].validations[this.el[elem].validations.length-1].errorMsg
= (typeof this.el[elem].errorMsg=='undefined') ?
settings.requiredErrorMsg : this.el[elem].errorMsg;
this.inheritIfDefined( this.el[elem], this.supportedEventHandlers,
this.el[elem].validations[this.el[elem].validations.length-1] );

//If specific requiredEvents are provided, use those instead of the
element level event handlers
if( typeof this.el[elem]['requiredEvents']!='undefined')
this.inheritIfDefined( this.el[elem]['requiredEvents'],
this.supportedEventHandlers,
this.el[elem].validations[this.el[elem].validations.length-1] );

//We need to call the validateRequiredAlternatives function when a
requiredAlternative is clicked
if(settings.validateRequiredAlternativesOnclick==true && typeof
this.el[elem].requiredAlternatives != 'undefined' ) {
var onsuccessFuncs = (typeof this.el[elem].onsuccess!='undefined') ?
this.el[elem].onsuccess : settings.onsuccess;
var onerrorFuncs = (typeof this.el[elem].onerror!='undefined') ?
this.el[elem].onerror : settings.onerror;
var alts =
this.resolveArray(this.el[elem].requiredAlternatives,'string');
for( var y=alts.length-1; y>-1; y--) {
this.requiredAlternatives[alts[y]] = {};
if( !((typeof document.getElementById(alts[y]).type != 'undefined') &&
(document.getElementById(alts[y]).type=='checkbox'||document.getElemen
tById(alts[y]).type=='radio')) ) continue;
this.requiredAlternatives[alts[y]].onsuccess = onsuccessFuncs;
this.requiredAlternatives[alts[y]].onerror = onerrorFuncs;
this.requiredAlterna

.. _http://www.drlongghost.com/: http://www.drlongghost.com/
.. _http://drlongghost.com/validanguage.php: http://drlongghost.com/validanguage.php
.. meta::
    :title: Validanguage Helper for customizable Javascript form validation
    :description: CakePHP Article related to helpers,prototype,form validation,validanguage,Helpers
    :keywords: helpers,prototype,form validation,validanguage,Helpers
    :copyright: Copyright 2008 
    :category: helpers

