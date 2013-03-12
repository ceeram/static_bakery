

Livevalidation Helper
=====================

by %s on June 20, 2009

- LiveValidation is a small open source javascript library for making
client-side validation quick, easy, and powerful. It comprises of two
main parts. Firstly, it provides developers with a rich set of core
validation methods, which can also be used outside the context of
forms. Secondly, it provides your visitors with real-time validation
information as they fill out forms, helping them to get it right first
time, making the forms easier, quicker and less daunting to complete.
- Base on this script, i wrote Livevalidation helper and addition some
common function to help validate form quickly.


Download
~~~~~~~~
- Download livevalidation script at
`http://www.livevalidation.com/download`_ and save in folder
webroot/js/livevalidation/
- Save this style in webroot/css/livevalidation/style.css

::

    
    .LV_validation_message{
        font-weight:bold;
        margin:0 0 0 5px;
    }
    
    .LV_valid {
        color:#00CC00;
    }
    	
    .LV_invalid {
        color:#CC0000;
    }
        
    .LV_valid_field,
    input.LV_valid_field:hover, 
    input.LV_valid_field:active,
    textarea.LV_valid_field:hover, 
    textarea.LV_valid_field:active {
        border: 1px solid #00CC00;
    }
        
    .LV_invalid_field, 
    input.LV_invalid_field:hover, 
    input.LV_invalid_field:active,
    textarea.LV_invalid_field:hover, 
    textarea.LV_invalid_field:active {
        border: 1px solid #CC0000;
    }



Code
~~~~

Helper Class:
`````````````

::

    <?php 
    <?php
    /**
    * Livevalidation Helper
     * base on livevalidation http://www.livevalidation.com
    *
    * @author  Vu Khanh Truong
    * @version 1.0.0
    */
    
    class LivevalidationHelper extends AppHelper {
    
        var $helpers = array('Javascript', 'Form', 'Html');
        var $modelClass = null;
        var $onlyOnSubmit = false;
        var $onlyOnBlur = false;
        var $wait = 0;
    /**
     * Initial function , use function to include style and js
     *
     * @param string $modelClass (the name of model class)
     * @param boolean $onlyOnSubmit (optional) - {Boolean} - whether you want it to validate as you type or only on blur (DEFAULT: false)
     * @param boolean $onlyOnBlur (optional) - {Boolean} - whether you want it to validate as you type or only on blur (DEFAULT: false)
     * @param int $wait (optional) - {Integer} - the time you want it to pause from the last keystroke before it validates (milliseconds) (DEFAULT: 0)
     * @return void
     * @access public
     */
        function setup($modelClass,$onlyOnSubmit=false,$onlyOnBlur=false,$wait=0){
            $this->modelClass = $modelClass;
            $this->onlyOnSubmit = $onlyOnSubmit;
            $this->onlyOnBlur = $onlyOnBlur;
            $this->wait = $wait;
            echo $this->Javascript->link('livevalidation/livevalidation_standalone.compressed')."\n";
            echo $this->Html->css('livevalidation/style')."\n";
        }
    /**
     * Check function : use this function to check validation for your form field
     *
     * @param string $fieldName:  name of field you want check valid, it's will be convert from field name to field id
     * @param array $options:
       Using $options["id"] (optional) for your own field id.
       Using these keys below to add validation for your form:
       1 . Key "notempty" : Validates that a value is present (ie. not null, undefined, or an empty string)
    	$options["notempty"] = array("message"=>"Your information");//=>Default message: Canâ€™t be empty!
       2. Key "format": Validates a value against a regular expression
    	$options["format"] = array(
    	   "message"=>"Your information",//=>Default message: Not valid!
    	   "pattern"=>"/^(your regular)$/");
    	
       3. Key "numeric": lets you validate against a list of allowed values. You can do an exact match or a partial match.
    	- This kind of validation is concerned with numbers. It can handle scientific numbers (ie. 2e3, being 2000), floats (numbers with a decimal place), and negative numbers properly as you would expect it to throughout, both in values and parameters.
    	- Syntax:
    		$options["numeric"] = array(
    		   "message"=>"Your information", //=> default message: Must be a number!
    		   "onlyInteger"=>array(boolean:if true will only allow integers to be valid (DEFAULT: false) , "custom message (default: Must be an integer!)"),
    		   "is"=>array(numeric value:(optional) - {mixed} - the value must be equal to this numeric value , "custom message (default: Must be {is}!)"),
    		   "minimum"=>array(value:(optional) - {mixed} - the minimum numeric allowed , "custom message (default: Must not be less than {minimum}!)"),
    		   "maximum"=>array(value:(optional) - {mixed} - the maximum numeric allowed , "custom message (default: Must not be more than {maximum}!)"),
    		)      
    	-Both "minimum" and "maximum" : check that the value is a number that falls within a range that you supply. This is done supplying both a â€˜minimumâ€™ and â€˜maximumâ€™.
       4. Key "length": Validates the length of a value is a particular length, is more than a minimum, less than a maximum, or between a range of lengths
    	- Syntax:
    		$options["length"] = array(
    		   "is"=>array(value:(optional) - {mixed} - the value must be this length  , "custom message (default: Must be {is} characters long!)"),
    		   "minimum"=>array(value:(optional) - {mixed} - the minimum length allowed , "custom message (default: Must not be less than {minimum} characters long!)"),
    		   "maximum"=>array(value:(optional) - {mixed} - the maximum length allowed , "custom message (default: Must not be more than {maximum} characters long!)"),
    		)
       5. Key "inclusion": Validates that a value falls within a given set of values
    		$options["inclusion"] =array(
    			"message"=>"Your information", //=>default: Must be included in the list!
    			"within"=>{Array} - an array of values that the value should fall in (DEFAULT: Empty array) ,
    			"allowNull" => (optional) - {Boolean} - if true, and a null value is passed in, validates as true (DEFAULT: false)  ,
    			"partialMatch" => (optional) - {Boolean}- if true, will not only validate against the whole value to check, but also if it is a substring of the value (DEFAULT: false) ,
    			"caseSensitive " => (optional) - {Boolean} - if false will compare strings case insensitively(DEFAULT: true),
    		)            
       5. Key "exclusion": Validates that a value does not fall within a given set of values
    		$options["inclusion"] =array(
    			"message"=>"Your information", //=>default: Must not be included in the list!
    			"within"=>{Array} - an array of values that the value should fall in (DEFAULT: Empty array) ,
    			"allowNull" => (optional) - {Boolean} - if true, and a null value is passed in, validates as true (DEFAULT: false)  ,
    			"partialMatch" => (optional) - {Boolean}- if true, will not only validate against the whole value to check, but also if it is a substring of the value (DEFAULT: false) ,
    			"caseSensitive " => (optional) - {Boolean} - if false will compare strings case insensitively(DEFAULT: true),
    		)
       6 . Key "acceptance" : Validates that a value equates to true (for use primarily in detemining if a checkbox has been checked)
    	$options["acceptance"] = array("message"=>"Your information");//=>Default message: Must be accepted!
       7 . Key "confirmation" : Validates that a value matches that of a given form field
    	$options["confirmation"] = array(
    		 "message"=>"Your information", //=>Default message: Does not match!
    		 "match" => -{mixed} - a reference to, or string id of the field that this should match
    	 );
    	 Example:$checkOption["confirmation"] = array("mesagge"=>"Does not match!","match"=>"myPasswordField");
       8 . Key "date" : Validates date using regular expression
    		$options["date"] = array("message"=>"Your information",
    						   "format" => "dmy" //=> in range [dmy, mdy, ymd, dMy, Mdy, My, my] , default: dmy
    							);
       9 . Key "time" : Validates time using regular expression
    		$options["time"] = array("message"=>"Your information");
       10. Key "url" : Validates url using regular expression
    		$options["url"] = array("message"=>"Your information");
       11. Key "postalcode" : Validates time using regular expression
    		$options["postalcode"] = array("message"=>"Your information",
    						   "country" => "us" //=> in range [us, uk, ca, de, be] , default: us
    							);
     * @return void
     * @access public
     */
        function check($fieldName, $options = array()){
            //$fieldid = $this->modelClass.Inflector::camelize("$fieldName");
            $fieldid = (isset($options["id"]) && !empty($options["id"])) ? $options["id"] : $this->modelClass.Inflector::camelize("{$fieldName}");
            $onlyOnSubmit = ($this->onlyOnSubmit) ? ", onlyOnSubmit:".$this->onlyOnSubmit : "";
            $onlyOnBlur  = ($this->onlyOnBlur) ? ", onlyOnBlur : ".(boolean)$this->onlyOnBlur : "";
            $wait   =  " wait : ".intval($this->wait);
            $jsprint = "var {$fieldName} = new LiveValidation('{$fieldid}', { {$wait} {$onlyOnSubmit} {$onlyOnBlur}});";
            if(!empty($options)){
                foreach($options as $validate => $valid_option){
                    $failureMessage = !empty($valid_option["message"]) ? $valid_option["message"] : "";
                    switch($validate){
                        case "notempty":
                                $jsprint .= "{$fieldName}.add( Validate.Presence ,{ failureMessage: '{$failureMessage}' });";
                            break;
                        case "format":
    					   /*
    						* fail if the value does not match the regular expression
    						*/
                               $pattern = !empty($valid_option["pattern"]) ? $valid_option["pattern"] : "";
                               $jsprint .= "{$fieldName}.add( Validate.Format  ,{ failureMessage: '{$failureMessage}', pattern: {$pattern} });";
                            break;
                        case "numeric":
    						/*
    						 * This kind of validation is concerned with numbers. It can handle scientific numbers (ie. 2e3, being 2000), floats (numbers with a decimal place), and negative numbers properly as you would expect it to throughout, both in values and parameters.
    						 */
                                //check is number
                                $is = null;
                                $is_message = null;
                                if(!empty($valid_option["is"])){
                                    if(is_array($valid_option["is"])){
                                        $is = (isset($valid_option["is"][0])) ? $valid_option["is"][0] : "";
                                        $is_message = (isset($valid_option["is"][1])) ? $valid_option["is"][1] : "";
                                    }else{
                                        $is = $valid_option["is"];
                                    }
                                }
                                $is =           $is!=null ? ", is: ".$is : "";
                                $is_message =   $is_message!=null ? ", wrongNumberMessage : '{$is_message}'" : "";
                                //check minimum
                                $minimum = null;
                                $minimum_message = null;
                                if(!empty($valid_option["minimum"])){
                                    if(is_array($valid_option["minimum"])){
                                        $minimum = (isset($valid_option["minimum"][0])) ? $valid_option["minimum"][0] : "";
                                        $minimum_message = (isset($valid_option["minimum"][1])) ? $valid_option["minimum"][1] : "";
                                    }else{
                                        $minimum = $valid_option["minimum"];
                                    }
                                }
                                $minimum = !empty($minimum) ? ", minimum: ".$minimum : "";
                                $minimum_message =   $minimum_message!=null ? ", tooLowMessage : '{$minimum_message}'" : "";
                                //check maximum
                                $maximum = null;
                                $maximum_message = null;
                                if(!empty($valid_option["maximum"])){
                                    if(is_array($valid_option["maximum"])){
                                        $maximum = (isset($valid_option["maximum"][0])) ? $valid_option["maximum"][0] : "";
                                        $maximum_message = (isset($valid_option["maximum"][1])) ? $valid_option["maximum"][1] : "";
                                    }else{
                                        $maximum = $valid_option["maximum"];
                                    }
                                }
                                $maximum = !empty($maximum) ? ", maximum: ".$maximum : "";
                                $maximum_message =   $maximum_message!=null ? ", tooHighMessage : '{$maximum_message}'" : "";
                                //check onlyInteger
                                $onlyInteger = null;
                                $onlyInteger_message = null;
                                if(!empty($valid_option["onlyInteger"])){
                                    if(is_array($valid_option["onlyInteger"])){
                                        $onlyInteger = (isset($valid_option["onlyInteger"][0])) ? $valid_option["onlyInteger"][0] : "";
                                        $onlyInteger_message = (isset($valid_option["onlyInteger"][1])) ? $valid_option["onlyInteger"][1] : "";
                                    }else{
                                        $onlyInteger = $valid_option["onlyInteger"];
                                    }
                                }
                                $onlyInteger = !empty($onlyInteger) ? ", onlyInteger: ".$onlyInteger : "";
                                $onlyInteger_message =   $onlyInteger_message!=null ? ", notAnIntegerMessage : '{$onlyInteger_message}'" : "";
                                
                                $jsprint .= "{$fieldName}.add( Validate.Numericality  ,{ notANumberMessage: '{$failureMessage}' {$minimum} {$minimum_message} {$maximum} {$maximum_message} {$onlyInteger} {$onlyInteger_message} {$is} {$is_message} });";
                            break;
                        case "length":
    						/*
    						 * Length is concerned with the number of characters in a value. You can do various validations on it, like check it is a specific length, less than or equal to a maximum length, greater than or equal to a minimum length, or falls within a range of lengths.
    						  */
                                //check is number
                                $is = null;
                                $is_message = null;
                                if(!empty($valid_option["is"])){
                                    if(is_array($valid_option["is"])){
                                        $is = (isset($valid_option["is"][0])) ? $valid_option["is"][0] : "";
                                        $is_message = (isset($valid_option["is"][1])) ? $valid_option["is"][1] : "";
                                    }else{
                                        $is = $valid_option["is"];
                                    }
                                }
                                $is =           $is!=null ? ", is: ".$is : "";
                                $is_message =   $is_message!=null ? ", wrongLengthMessage : '{$is_message}'" : "";
                                //check minimum
                                $minimum = null;
                                $minimum_message = null;
                                if(!empty($valid_option["minimum"])){
                                    if(is_array($valid_option["minimum"])){
                                        $minimum = (isset($valid_option["minimum"][0])) ? $valid_option["minimum"][0] : "";
                                        $minimum_message = (isset($valid_option["minimum"][1])) ? $valid_option["minimum"][1] : "";
                                    }else{
                                        $minimum = $valid_option["minimum"];
                                    }
                                }
                                $minimum = !empty($minimum) ? ", minimum: ".$minimum : "";
                                $minimum_message =   $minimum_message!=null ? ", tooShortMessage : '{$minimum_message}'" : "";
                                //check maximum
                                $maximum = null;
                                $maximum_message = null;
                                if(!empty($valid_option["maximum"])){
                                    if(is_array($valid_option["maximum"])){
                                        $maximum = (isset($valid_option["maximum"][0])) ? $valid_option["maximum"][0] : "";
                                        $maximum_message = (isset($valid_option["maximum"][1])) ? $valid_option["maximum"][1] : "";
                                    }else{
                                        $maximum = $valid_option["maximum"];
                                    }
                                }
                                $maximum = !empty($maximum) ? ", maximum: ".$maximum : "";
                                $maximum_message =   $maximum_message!=null ? ", tooLongMessage : '{$maximum_message}'" : "";
                                $jsprint .= "{$fieldName}.add( Validate.Length  ,{ failureMessage: '{$failureMessage}' {$is} {$is_message} {$minimum} {$minimum_message} {$maximum}  {$maximum_message} });";
                            break;
                        case "inclusion":
                                /*
    							 * Inclusion lets you validate against a list of allowed values. You can do an exact match or a partial match.
    							 * Any part of the value matches something in the allowed list. Allow this by setting the â€˜partialMatchâ€™ to be true.
    							 */
                                if(!is_array($valid_option["within"])){
                                    $valid_option["within"] = array($valid_option["within"]);
                                }
                                //$within = implode(",", $valid_option["within"]);
                                $within = "'".implode("','", array_values($valid_option["within"]))."'";
                                $allowNull = !empty($valid_option["allowNull"]) ? ", allowNull: ".$valid_option["allowNull"] : "";
                                $partialMatch = !empty($valid_option["partialMatch"]) ? ", partialMatch: ".$valid_option["partialMatch"] : "";
                                $caseSensitive = !empty($valid_option["caseSensitive"]) ? ", partialMatch: ".$valid_option["caseSensitive"] : "";
                                $jsprint .= "{$fieldName}.add( Validate.Inclusion ,{ failureMessage: '{$failureMessage}', within: [ {$within} ] {$caseSensitive} {$allowNull} {$partialMatch}} );";
                            break;
                        case "exclusion":
                                /*
    							 * Exclusion lets you validate against a list of values that are not allowed. You can do an exact match or a partial match.
    							 * No part of the value matches something in the disallowed list. Allow this by setting the â€˜partialMatchâ€™ to be true.
    							 */
                                if(!is_array($valid_option["within"])){
                                    $valid_option["within"] = array($valid_option["within"]);
                                }
                                $within = "'".implode("','", array_values($valid_option["within"]))."'";
                                $allowNull = !empty($valid_option["allowNull"]) ? ", allowNull: ".$valid_option["allowNull"] : "";
                                $partialMatch = !empty($valid_option["partialMatch"]) ? ", partialMatch: ".$valid_option["partialMatch"] : "";
                                $caseSensitive = !empty($valid_option["caseSensitive"]) ? ", partialMatch: ".$valid_option["caseSensitive"] : "";
                                $jsprint .= "{$fieldName}.add( Validate.Exclusion ,{ failureMessage: '{$failureMessage}', within: [ {$within} ] {$caseSensitive} {$allowNull} {$partialMatch}} );";
                            break;
                        case "acceptance":
    						/*
    						 * Acceptance lets you validate that a checkbox has been checked.
    						 */
                               $jsprint .= "{$fieldName}.add( Validate.acceptance  ,{ failureMessage: '{$failureMessage}' });";
                            break;
                        case "confirmation":
    					   /*
    						* This is used to check that the value of the confirmation field matches that of another field. The most common use for this is for passwords, as demonstrated below, but will work just as well on other field types.
    						*/
                               $match = !empty($valid_option["match"]) ? ", match: '".$this->modelClass.Inflector::camelize("{$valid_option["match"]}")."'" : "";
                               if(isset($options["id"]) && !empty($options["id"])){
                                   $match = ", match: '".$options["id"]."'";
                               }                               
                               $jsprint .= "{$fieldName}.add( Validate.Confirmation  ,{ failureMessage: '{$failureMessage}' {$match} });";
                            break;
                       case "email":
    					   /*
    						* fail if the value does not match the regular expression
    						*/
                               $jsprint .= "{$fieldName}.add( Validate.Email  ,{ failureMessage: '{$failureMessage}' });";
                            break;
                        case "date":
    					   /*
    						* fail if the value does not match the date regular expression
    						*/
                                $regex = array();
                                $regex['dmy'] = '/^(?:(?:31(\\/|-|\\.|\\x20)(?:0?[13578]|1[02]))\\1|(?:(?:29|30)(\\/|-|\\.|\\x20)(?:0?[1,3-9]|1[0-2])\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:29(\\/|-|\\.|\\x20)0?2\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\\d|2[0-8])(\\/|-|\\.|\\x20)(?:(?:0?[1-9])|(?:1[0-2]))\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$/';
                                $regex['mdy'] = '/^(?:(?:(?:0?[13578]|1[02])(\\/|-|\\.|\\x20)31)\\1|(?:(?:0?[13-9]|1[0-2])(\\/|-|\\.|\\x20)(?:29|30)\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:0?2(\\/|-|\\.|\\x20)29\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:(?:0?[1-9])|(?:1[0-2]))(\\/|-|\\.|\\x20)(?:0?[1-9]|1\\d|2[0-8])\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$/';
                                $regex['ymd'] = '/^(?:(?:(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\\/|-|\\.|\\x20)(?:0?2\\1(?:29)))|(?:(?:(?:1[6-9]|[2-9]\\d)?\\d{2})(\\/|-|\\.|\\x20)(?:(?:(?:0?[13578]|1[02])\\2(?:31))|(?:(?:0?[1,3-9]|1[0-2])\\2(29|30))|(?:(?:0?[1-9])|(?:1[0-2]))\\2(?:0?[1-9]|1\\d|2[0-8]))))$/';
                                $regex['dMy'] = '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\ (Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\ ((1[6-9]|[2-9]\\d)\\d{2})$/';
                                $regex['Mdy'] = '/^(?:(((Jan(uary)?|Ma(r(ch)?|y)|Jul(y)?|Aug(ust)?|Oct(ober)?|Dec(ember)?)\\ 31)|((Jan(uary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sept|Nov|Dec)(ember)?)\\ (0?[1-9]|([12]\\d)|30))|(Feb(ruary)?\\ (0?[1-9]|1\\d|2[0-8]|(29(?=,?\\ ((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))))\\,?\\ ((1[6-9]|[2-9]\\d)\\d{2}))$/';
                                $regex['My'] = '/^(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)[ /]((1[6-9]|[2-9]\\d)\\d{2})$/';
                                $regex['my'] = '/^(((0[123456789]|10|11|12)([- /.])(([1][9][0-9][0-9])|([2][0-9][0-9][0-9]))))$/';
    
                                $pattern = !empty($valid_option["format"]) ? $regex[$valid_option["format"]] : $regex['dmy'];
                                $jsprint .= "{$fieldName}.add( Validate.Format  ,{ failureMessage: '{$failureMessage}', pattern: {$pattern} });";
                            break;
                        case "time":
    					   /*
    						* fail if the value does not match the time regular expression
    						*/
                                $pattern = '/^((0?[1-9]|1[012])(:[0-5]\d){0,2}([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$/';
                                $jsprint .= "{$fieldName}.add( Validate.Format  ,{ failureMessage: '{$failureMessage}', pattern: {$pattern} });";
                            break;
                        case "url":
                               /*
    							* fail if the value does not match the url regular expression
    							*/
                                $strict = !empty($valid_option["strict"]) ? $valid_option["strict"] : "false";
                                $__pattern = array(
                                    'ip' => '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])',
                                    'hostname' => '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)'
                                );
                                $validChars = '([' . preg_quote('!"$&\'()*+,-.@_:;=') . '\/0-9a-z]|(%[0-9a-f]{2}))';
                                $pattern = '/^(?:(?:https?|ftps?|file|news|gopher):\/\/)' . ife($strict, '', '?') .
                                    '(?:' . $__pattern['ip'] . '|' . $__pattern['hostname'] . ')(?::[1-9][0-9]{0,3})?' .
                                    '(?:\/?|\/' . $validChars . '*)?' .
                                    '(?:\?' . $validChars . '*)?' .
                                    '(?:#' . $validChars . '*)?$/i';
                                $jsprint .= "{$fieldName}.add( Validate.Format  ,{ failureMessage: '{$failureMessage}', pattern: {$pattern} });";
                            break;
                        case "postalcode":
    					   /*
    						* fail if the value does not match the post code regular expression
    						*/
                                $country = !empty($valid_option["country"]) ? $valid_option["country"] : "us";
                                switch ($country) {
                                    case 'uk':
                                        $pattern  = '/\\A\\b[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}\\b\\z/i';
                                        break;
                                    case 'ca':
                                        $pattern  = '/\\A\\b[ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9]\\b\\z/i';
                                        break;
                                    case 'it':
                                    case 'de':
                                        $pattern  = '/^[0-9]{5}$/i';
                                        break;
                                    case 'be':
                                        $pattern  = '/^[1-9]{1}[0-9]{3}$/i';
                                        break;
                                    case 'us':
                                    default:
                                        $pattern  = '/\\A\\b[0-9]{5}(?:-[0-9]{4})?\\b\\z/i';
                                        break;
                                }
                                $jsprint .= "{$fieldName}.add( Validate.Format  ,{ failureMessage: '{$failureMessage}', pattern: {$pattern} });";
                            break;
                    }
                }
                $this->jsprint($jsprint);
            }
        }
        function jsprint($jstring){
    ?>
                <script>
                   <? echo $jstring;?>
                </script>
    <?
        }
    }
    ?>
    ?>


Usage
~~~~~
Example check validation for this form

Controller Class:
`````````````````

::

    <?php 
    var $helpers = array("Livevalidation");
    ?>


View Template:
``````````````

::

    
    <?php echo $form->create('User');?>
    <? echo $validation->setup($this->model);?>
    <div>Name (required, at least 3 characters)</div>
    <div>
    <?
    		echo $form->text('username');
            $checkOption = array();
            $checkOption["notempty"] = array("message"=>"This field is required!");
            $checkOption["length"] = array("message"=>"at least 3 characters","minimum"=>3);
            $livevalidation->check("username",$checkOption);
    ?>
    </div>
    <div>Email</div>
    <div>
    <?
    		echo $form->text('email');
            $checkOption = array();
            $checkOption["notempty"] = array("message"=>"This field is required!");
            $checkOption["email"] = array("message"=>"");
            $livevalidation->check("email",$checkOption);
    ?>
    </div>
    <div>URL</div>
    <div>
    <?
    		echo $form->text('url');
            $checkOption = array();
            $checkOption["url"] = array("message"=>"");
            $livevalidation->check("url",$checkOption);
    ?>
    </div>
    <div>Only numeric</div>
    <div>
    <?
    		echo $form->text('numeric1');
            $checkOption = array();
            $checkOption["numeric"] = array("message"=>"Only number");
            $livevalidation->check("numeric1",$checkOption);
    ?>
    </div>
    <div>Only 20</div>
    <div>
    <?
    		echo $form->text('numeric2');
            $checkOption = array();
            $checkOption["numeric"] = array("message"=>"Only number","is"=>array(20,"only 20"));
            $livevalidation->check("numeric2",$checkOption);
    ?>
    </div>
    <div>Only integer</div>
    <div>
    <?
    		echo $form->text('numeric3');
            $checkOption = array();
            $checkOption["numeric"] = array("message"=>"Only number","onlyInteger"=>array(true,"only int"));
            $livevalidation->check("numeric3",$checkOption);
    ?>
    </div>
    <div>Input number in range 20 - 30</div>
    <div>
    <?
    		echo $form->text('numeric4');
            $checkOption = array();
            $checkOption["numeric"] = array("message"=>"","minimum"=>array(20,"> 20"),"maximum"=>array(30,"< 30"));
            $livevalidation->check("numeric4",$checkOption);
    ?>
    </div>
    <div>Length of character in range 3 - 6</div>
    <div>
    <?
    		echo $form->text('length1');
            $checkOption = array();
            $checkOption["length"] = array("minimum"=>array(3,"must > 3"),"maximum"=>array(6,"must < 6"));
            $livevalidation->check("length1",$checkOption);
    ?>
    </div>
    <div>Must be included ["cat","tiger","lion"]</div>
    <div>
    <?
    		echo $form->text('inclusion');
            $checkOption = array();
            $checkOption["inclusion"] = array("Must be included in the list!","within"=>array("cat","tiger","lion"));
            $livevalidation->check("inclusion",$checkOption);
    ?>
    </div>
    <div>Must not be included ["cat","tiger","lion"]</div>
    <div>
    <?
    		echo $form->text('exclusion');
            $checkOption = array();
            $checkOption["exclusion"] = array("Must not be included in the list!","within"=>array("cat","tiger","lion"));
            $livevalidation->check("exclusion",$checkOption);
    ?>
    </div>
    <div>Password</div>
    <div>
    <?
    		echo $form->password('password');
            $checkOption = array();
            $checkOption["notempty"] = array("message"=>"Not empty!");
            $livevalidation->check("password",$checkOption);
    ?>
    </div>
    <div>Confirm Password</div>
    <div>
    <?
    		echo $form->password('cpassword');
            $checkOption = array();
            $checkOption["notempty"] = array("message"=>"Not empty!");
            $checkOption["confirmation"] = array("message"=>"Does not match!","match"=>"password");
            $livevalidation->check("cpassword",$checkOption);
    ?>
    </div>
    <div>Birthday (format: "dMy" - ex: 01 Nov 1984)</div>
    <div>
    <?
    		echo $form->text('birthday');
            $checkOption = array();
            $checkOption["date"] = array("message"=>"","format"=>"dMy");
            //You can use ther format like : dmy, mdy, ymd, Mdy, My, my
            $livevalidation->check("birthday",$checkOption);
    ?>
    </div>
    <?php echo $form->end('Submit');?>


Conclusion
~~~~~~~~~~
I hope this article will useful for everybody.

.. _http://www.livevalidation.com/download: http://www.livevalidation.com/download
.. meta::
    :title: Livevalidation Helper
    :description: CakePHP Article related to helper validation,Helpers
    :keywords: helper validation,Helpers
    :copyright: Copyright 2009 
    :category: helpers

