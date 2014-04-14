Automagic JavaScript Validation Helper
======================================

by mattc on October 26, 2008

This helper takes your model validation rules and converts them to
JavaScript so they can be applied in the client's browser before
submitting to the server.
This helper requires jQuery (`http://www.jquery.com`_). Sorry to all
you Prototype/script.aculo.us users.
Way back when CakePHP had a wiki there were a series of articles on
"Advanced Validation." One aspect of these articles was using the
Model rules in JavaScript to validate on the client side. The code
(`http://cakeforge.org/snippet/detail.php?type=snippet=109`_) was
originally for CakePHP version .10 (iirc). I had been using a heavily
modified version of this code even in my 1.2 projects, but it was due
for a ground up re-write.

A zip file of the code is available at
`http://github.com/mcurry/cakephp/tree/master/helpers/validation`_ or
you can just copy and paste from below. A demo is available at
`http://sandbox2.pseudocoder.com/demo/validation_test`_.


Step 1
~~~~~~
If you're not already using jQuery download it and include it in your
layout/view.


Step 2
~~~~~~
Create /app/webroot/js/validation.js

::

    
    function validateForm(form, rules) {
      //clear out any old errors
      $("#messages").html("");
      $("#messages").slideUp();
      $(".error-message").hide();
      
      //loop through the validation rules and check for errors
      $.each(rules, function(field) {
        var val = $.trim($("#" + field).val());
        
        $.each(this, function() {
          console.log(this['rule']);
          
          //check if the input exists
          if ($("#" + field).attr("id") != undefined) {
            var valid = true;
            
            if (this['allowEmpty'] && val == '') {
              //do nothing
            } else if (this['rule'].match(/^range/)) {
              var range = this['rule'].split('|');
              if (val < parseInt(range[1])) {
                valid = false;
              }
              if (val > parseInt(range[2])) {
                valid = false;
              }
            } else if (this['negate']) {
              if (val.match(eval(this['rule']))) {
                valid = false;
              }
            } else if (!val.match(eval(this['rule']))) {
              valid = false;
            }
            
            if (!valid) {
              //add the error message
              $("#messages").append("<p>" + this['message'] + "</p>");
              
              //highlight the label
              //$("label[for='" + field + "']").addClass("error");
              $("#" + field).parent().addClass("error");
            }
          }
        });
      });
      
      if($("#messages").html() != "") {
        $("#messages").wrapInner("<div class='errors'></div>");
        $("#messages").slideDown();
        return false;
      }
    
      return true;
    }



Step 3
~~~~~~
Create /app/views/helpers/validation.php

Helper Class:
`````````````

::

    <?php 
    /*
     * Javascript Validation CakePHP Helper
     * Copyright (c) 2008 Matt Curry
     * www.PseudoCoder.com
     *
     * @author      mattc <matt@pseudocoder.com>
     * @version     0.1
     * @license     MIT
     *
     */
    
    //feel free to replace these or overwrite in your bootstrap.php
    if (!defined('VALID_EMAIL_JS')) {
      define('VALID_EMAIL_JS', '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/');
    }
    //I know the octals should be capped at 255
    if (!defined('VALID_IP_JS')) {
      define('VALID_IP_JS', '/^[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}$/');
    }
    
    //list taken from /cake/libs/validation.php line 497
    if (!defined('DEFAULT_VALIDATION_EXTENSIONS')) {
      define('DEFAULT_VALIDATION_EXTENSIONS', 'gif,jpeg,png,jpg');
    }
    
    class ValidationHelper extends Helper {
      var $helpers = array('Javascript');
    
      //For security reasons you may not want to include all possible validations
      //in your bootstrap you can define which are allowed
      //Configure::write('javascriptValidationWhitelist', array('alphaNumeric', 'minLength'));
      var $whitelist = false;
    
      function rules($modelNames, $options=array()) {
        $scriptTags = '';
        
        if (empty($options) || !is_array($options)) {
          $options = array();
        }
    
        $defaultOptions = array('formId' => false, 'inline' => true);
        $options = array_merge($defaultOptions, $options);
    
        //load the whitelist
        $this->whitelist = Configure::read('javascriptValidationWhitelist');
    
        if (!is_array($modelNames)) {
          $modelNames = array($modelNames);
        }
    
        //catch the form submit
        $formId = 'form';
        if ($options['formId']) {
          $formId = '#' . $formName;
        }
        $scriptTags  	.= "$(document).ready(function(){ $('". $formId . "').submit( function() { return validateForm(this, rules); }); });\n";
    
        //filter the rules to those that can be handled with JavaScript
        $validation = array();
        foreach($modelNames as $modelName) {
          $model = new $modelName();
    
          foreach ($model->validate as $field => $validators) {
            if (array_intersect(array('rule', 'required', 'allowEmpty', 'on', 'message'), array_keys($validators))) {
              $validators = array($validators);
            }
    
            foreach($validators as $key => $validator) {
              $rule = null;
    
              if (!is_array($validator)) {
                $validator = array('rule' => $validator);
              }
    
              //skip rules that are applied only on created or updates
              if (!empty($validator['on'])) {
                continue;
              }
    
              if (!isset($validator['message'])) {
                $validator['message'] = sprintf('%s %s',
                                                __('There was a problem with the field', true),
                                                Inflector::humanize($field)
                                               );
              }
    
    
              if (!empty($validator['rule'])) {
                $rule = $this->convertRule($validator['rule']);
              }
    
              if ($rule) {
                $temp = array('rule' => $rule,
                              'message' => __($validator['message'], true)
                             );
    
    
                if (isset($validator['allowEmpty']) && $validator['allowEmpty'] === true) {
                  $temp['allowEmpty'] = true;
                }
    
                if (in_array($validator['rule'], array('alphaNumeric', 'blank'))) {
                  //Cake Validation::_check returning true is actually false for alphaNumeric and blank
                  //add a "!" so that JavaScript knows
                  $temp['negate'] = true;
                }
    
                $validation[$modelName . Inflector::camelize($field)][] = $temp;
              }
            }
          }
        }
    
        //pr($validation); die;
    
        $scriptTags 	.= "var rules = eval(" . json_encode($validation) . ");\n";
    
        if ($options['inline']) {
          return sprintf($this->Javascript->tags['javascriptblock'], $scriptTags);
        } else {
          $this->Javascript->codeBlock($scriptTags, array('inline' => false));
        }
        
        return true;
      }
    
      function convertRule($rule) {
        $regex = false;
    
        if ($rule == '_extract') {
          return false;
        }
    
        if (is_array($this->whitelist) && !in_array($rule, $this->whitelist)) {
          return false;
        }
    
        $params = array();
        if (is_array($rule)) {
          $params = array_slice($rule, 1);
          $rule = $rule[0];
        }
    
        //Certain Cake built-in validations can be handled with regular expressions,
        //but aren't on the Cake side.
        switch ($rule) {
          case 'between':
            return sprintf('/^.{%d,%d}$/', $params[0], $params[1]);
          case 'date':
            //Some of Cake's date regexs aren't JavaScript compatible. Skip for now
            return false;
          case 'email':
            return VALID_EMAIL_JS;
          case 'equalTo':
            return sprintf('/^%s$/', $params[0]);
          case 'extension':
            return sprintf('/\.(%s)$/', implode('|', explode(',', DEFAULT_VALIDATION_EXTENSIONS)));
          case 'ip':
            return VALID_IP_JS;
          case 'minLength':
            return sprintf('/^.{%d,}$/', $params[0]);
          case 'maxLength':
            return sprintf('/^.{0,%d}$/', $params[0]);
          case 'money':
            //The Cake regex for money was giving me issues, even within PHP.  Skip for now
            return false;
          case 'numeric':
            //Cake uses PHP's is_numeric function, which actually accepts a varied input
            //(both +0123.45e6 and 0xFF are valid) then what is allowed in this regular expression.
            //99% of people using this validation probably want to restrict to just numbers in standard
            //decimal notation.  Feel free to alter or delete.
            return '/^[+-]?[0-9]+$/';
          case 'range':
            //Don't think there is a way to do this with a regular expressions,
            //so we'll handle this with plain old javascript
            return sprintf('range|%s|%s', $params[0], $params[1]);
        }
    
        //try to lookup the regular expression from
        //CakePHP's built-in validation rules
        $Validation =& Validation::getInstance();
        if (method_exists($Validation, $rule)) {
          $Validation->regex = null;
          call_user_func_array(array(&$Validation, $rule), array_merge(array(true), $params));
    
          if ($Validation->regex) {
            $regex = $Validation->regex;
          }
        }
    
        //special handling
        switch ($rule) {
          case 'postal':
          case 'ssn':
            //I'm not a regex guru and I have no idea what "\\A\\b" and "\\b\\z" do.
            //Is it just to match start and end of line?  Why not use
            //"^" and "$" then?  Eitherway they don't work in JavaScript.
            return str_replace(array('\\A\\b', '\\b\\z'), array('^', '$'), $regex);
        }
    
        return $regex;
      }
    }
    ?>



Step 4
~~~~~~
Include the helper in any controller that will need it.

Controller Class:
`````````````````

::

    <?php 
    var $helpers = array('Validation');
    ?>



Step 5
~~~~~~
Include the Javascript files in your view. If you are already using
jQuery throughout your app, and it is included in your layout, you can
removed it from the line below.

View Template:
``````````````

::

    
    $javascript->link(array('jquery', 'validation'), false);



Step 6
~~~~~~
Then in the views for your forms, call the helper. Replace "Model"
with the model name for the form.

View Template:
``````````````

::

    
    echo $validation->rules('Model');  



Step 7
~~~~~~
You can pass a second param to the method call above, which is an
array of options. The available options are:

+ formId - The specific form id if you have multiple forms on a page
  and only want to target one.
+ inline - Setting this to true will return the ouput for direct
  echoing. If false then the codeblock will be added to the output of
  $scripts_for_layout for display in the HEAD tag.
+ messageId - The id of a div where all the validation messages will
  be displayed.



Step 8
~~~~~~
If a particular field fails the input will be marked with the css
class "form-error" and the message will be added after the field with
the class "error-message". This is the same as Cake would do if you
submitted to the server. In addition you can specify a div messageId
and all the messages will be shown there as well.


Step 9
~~~~~~
I wrote an article for PHPArch about JavaScript validation (`http://c7
y.phparch.com/c/entry/1/art,improved_javascript_validation`_), which
raised some concerns (`http://www.pseudocoder.com/archives/2008/02/12
/article-on-javascript-validation/#comment-2667`_) that this approach
may reveal too much about an application's security. If this is a
concern for you, but you still want to use this helper, there is an
option to whitelist rules can be applied on the client side. To use to
this feature set the list in your bootstrap.php:

::

    
    Configure::write('javascriptValidationWhitelist', array('alphaNumeric', 'minLength'));  



.. _http://sandbox2.pseudocoder.com/demo/validation_test: http://sandbox2.pseudocoder.com/demo/validation_test
.. _http://www.jquery.com: http://www.jquery.com/
.. _http://c7y.phparch.com/c/entry/1/art,improved_javascript_validation: http://c7y.phparch.com/c/entry/1/art,improved_javascript_validation
.. _=109: http://cakeforge.org/snippet/detail.php?type=snippet&id=109
.. _http://www.pseudocoder.com/archives/2008/02/12/article-on-javascript-validation/#comment-2667: http://www.pseudocoder.com/archives/2008/02/12/article-on-javascript-validation/#comment-2667
.. _http://github.com/mcurry/cakephp/tree/master/helpers/validation: http://github.com/mcurry/cakephp/tree/master/helpers/validation
.. meta::
    :title: Automagic JavaScript Validation Helper
    :description: CakePHP Article related to validation,jquery,Helpers
    :keywords: validation,jquery,Helpers
    :copyright: Copyright 2008 mattc
    :category: helpers

