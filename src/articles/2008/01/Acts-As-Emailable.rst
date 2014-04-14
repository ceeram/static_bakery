Acts As Emailable
=================

by Thades on January 14, 2008

This little behavior is more of a learning experiment for me into how
behaviors work in the CakePHP side of things. It helps me with
maintaining a list of users who are emailable rather than write a
component or a model. Read on for more...

This jist of this is, I want whatever model I plug this into to
maintain it's own list of who (or what) will receive emails or not.
This is great when your sales department wants to send email specials
to all registered users, who have not opted out of receiving emails.

With that in mind, this behavior can also send out emails to every
user (or object) in the list.

I have tried my best to keep the code readable and tried to heavily
comment everything but, that's not to say I have not made any
mistakes. This code is for you to do with as you will. I make no
claims as to it's robustness, completeness or ability to handle
errors. You will notice some code such as debug setting is there for
future purposes.

I will also try to make every effort to update this code as I update
it in the real-world. In short, this code is provided AS IS.


Installation
~~~~~~~~~~~~

Copy the code into a file named "emailable.php" in your
/app/models/behaviors folder.

Next, you must add

::

    var $actsAs = array('emailable');

to the model that you are going to be using this for. Note that you
can pass additional options to this behavior. They are:


+ "field" - This is the field in your model's table that will be used
  to store 1 or 0 in. Default is "emailable".
+ "emailable" - This is what all new users will be defaulted to.
  Default is 1 (Can be emailed)
+ "email_fields" - The name of the field in which the email is stored.
  Default is "email".


At the time of this writing, this behavior can only handle one email
field. In the future I may add the ability to handle more such as
"email", "alternate_email", etc.

Next, you must add the column "emailable" to your model's table as a
TINYINT (MySQL) or INT for others. You can have it default to 1 or 0
if you like. If you default to 0, you must pass the "emailable" => 0
option in your $actsAs call.

From here, there is one more step if you plan to use this behavior's
emailing capabilities. You must make templates. Doesn't have to be
anything fancy but, it does have to be it's own file. The reason being
is that, this behavior includes a convenience of substituting php
variables with thier corresponding field names.

For example. I have a "test_template.php" and pass the absolute path
as $template to the email method. In this template I have

::

    
    <?php
        echo "This is a test message, ".$username."!";
    ?>


When this template gets ran, I have an actual field in the model's
table called "username". The resulting output looks like this:

::

    
    This is a test message, Thades!


From here, you are ready to go.


Examples
~~~~~~~~

Define $actsAs in your model.

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
         var $actsAs = array('emailable');
    }
    ?>

Here is how a full $actsAs call can be made for this behavior

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
         var $actsAs = array('emailable', array('field' => 'emailable',
                                                'email_fields' => 'email',
                                                'emailable' => 1);
    }
    ?>


With this done and your model set up, your model now has access to
methods to help you with your emailable list. These methods are:

+ find_all_emailable($extract = true, $fields_only = true)
+ find_all_non_emailable($extract = true, $fields_only = true)
+ add_emailable($id)
+ remove_emailable($id)
+ send_campaign_email_to_all($template, $email_options)
+ send_campaign_email_to_all_emailable($template, $email_options)
+ send_campaign_email_to_all_non_emailable($template, $email_options)



Explanation of Methods
~~~~~~~~~~~~~~~~~~~~~~

Here, I am going to try and explain what each method does and talk
about the options.


find_all_emailable($extract = true, $fields_only = true)
````````````````````````````````````````````````````````

This method returns an array of all objects that are emailable. The
options that can be passed are $extract-(true or false) and
$fields_only-(true or false).

$extract tells the method whether or not to use Set::extract on the
result. It uses your model's name in the extraction feature.

$fields_only tells the method whether to return only the email field
or everything.


find_all_non_emailable($extract = true, $fields_only = true)
````````````````````````````````````````````````````````````

This method returns an array of all objects that are NOT emailable.
The options that can be passed are $extract-(true or false) and
$fields_only-(true or false).

$extract tells the method whether or not to use Set::extract on the
result. It uses your model's name in the extraction feature.

$fields_only tells the method whether to return only the email field
or everything.


add_emailable($id)
``````````````````

Here, we just set the record with $id to emailable (true / 1)



remove_emailable($id)
`````````````````````

Here, we just set the record with $id to NOT emailable (false / 0)


send_campaign_email_to_all($template, $email_options)
`````````````````````````````````````````````````````

This method sends an email to all objects regardless of emailable or
not. This is a special method and if you are a business, certain laws
apply to how this is used. Please review them for your country.

$template is the absolute path to your template file. It doesn't have
to be php or anything. All I do is call an include() on it and php
does the rest.

$email_options is just an array of options that are going to be
applied to the email component. The array is is a 'key' => 'value'
pair arrangement. NOTE: At the very minimum, "from" and "subject" must
be passed. Using the ob, there is the possibility of setting these on
the fly.

::

    
    $email_options = array('from' => 'no-reply@mysite.com', 'subject' => 'Test Subject');


The other two email methods follow the same guidelines as the method
above, the only difference is, one emails all non-emailable objects
and the other just emails emailable objects.

Please note that I have not tested this behavior using cake's testing
facility as it never has worked for me. Also, be aware that I am using
PHP5 not PHP4 so, I don't know if it is compatible or not. I have
tried to be as version agnostic as possible but, some things, I just
don't know will work in 4. Example:

::

    
    $this->${$whatever} --or-- $this->$key = $value

The latter example, may work I am pretty sure. Don't know about the
first one, though!

Please post your thoughts, suggestions, bugs, hates, gripes, loves,
etc. but, remember, this was just an expirament for me. It is here to
help YOU and give YOU some ideas ;)

P.S. - It works fine for me....

::

    
    <?php
    /**
     * This behavior will provide email list like functions to whatever model you
     * assign $actsAs = array('emailable') to.
     *
     * The most common model that you would add this to would be the User model. As
     * your users sign up, they will be defaulted to emailable unless you specify
     * otherwise.
     *
     * This behavior also checks for the existance of the field to store whether
     * something is emailable or not. If the field does not exist, false is returned
     * for every method.
     *
     * @param string $field The name of the field in the model's table to store
     * emailable. Default is 'emailable'
     * @param integer $emailable The default for each new object. 1 or 0
     * @param mixed $email_fields Either a single string or an array of fields to
     * return. This does not have to be an email field
     * @param integer $__old_recursive This is the recursive level of the model when
     * it came to us. Since we set recursive to 0, we want to set it back to what it
     * was when we are finished
     */
    class EmailableBehavior extends ModelBehavior {
        /**
         * @param string $field The name of the field in the model's table to store
         * emailable. Default is 'emailable'
         */
        var $field = 'emailable';
    
        /**
         * @param integer $emailable The default for each new object. 1 or 0
         */
        var $emailable = 1;
    
        /**
         * @param mixed $email_fields Either a single string or an array of fields
         * to return. This does not have to be an email field
         */
        var $email_fields = 'email';
    
        /**
         * @param integer $__old_recursive This is the recursive level of the model
         * when it came to us. Since we set recursive to 0, we want to set it back
         * to what it was when we are finished
         */
        var $__old_recursive = 0;
    
        /**
         * The email component object
         */
        var $__email = null;
    
        /**
         * Skip setting the email options
         */
        var $__skip_email_options = false;
    
        /**
         * The debug setting in case of testing
         */
        var $debug = 0;
    
        /**
         * The setup method as required by cake.
         *
         * @param array $config The array of config settings. The only valid keys
         * are 'field', 'emailable' and 'email_fields'
         */
        function setup(&$model, $config = array()) {
            if(!empty($config)) {
                // If the config is not empty, check for fields
                if(array_key_exists('field', $config)) {
                    $this->field = $config['field'];
                }
                if(array_key_exists('emailable', $config)) {
                    $this->emailable = $config['emailable'];
                }
                if(array_key_exists('email_fields', $config)) {
                    $this->email_fields = $config['email_fields'];
                    if(is_array($this->email_fields)) {
                        // Emails fields was an array meaning more than one, I am not set up to handle more than one field at the moment
                        trigger_error("I am not set up to handle multiple email fields. Choose one and pass it as a string");
                        exit;
                    }
                }
            }
            // Get the current debug setting
            $this->debug = Configure::read();
        }
    
        /**
         * Before we save, we want to set the emailable field
         * We also want to switch recursive to 0
         * Please look over this method's comments
         */
        function beforeSave(&$model) {
            if($model->hasField($this->field)) {
                // Here, we check if the field was included in the form, if not, we assign it the default value.
                // If it was set, we do nothing and just let it save the value from the form
                if(!isset($model->data[$model->name][$this->field]) && empty($model->data[$model->name][$this->field])) {
                    $model->data[$model->name][$this->field] = $this->emailable;
                }
            }
            $model->recursive = $this->__old_recursive;
        }
    
        /**
         * After saving, set the recursive back to it's old value
         */
        function afterSave(&$model) {
            $model->recursive = $this->__old_recursive;
        }
    
        /**
         * Before we find something, we want to store the old recursive and set a
         * new one of 0
         */
        function beforeFind(&$model) {
            $this->__old_recursive = $model->recursive;
            $model->recursive = 0;
        }
    
        /**
         * After we finished our searches, set recursive back to it's old value
         */
        function afterFind(&$model) {
            $model->recursive = $this->__old_recursive;
        }
    
        /**
         * This method finds all entries that are emailable
         *
         * @return array An array of all emailable objects
         */
        function find_all_emailable(&$model, $extract = true, $fields_only = true) {
            $bool = 1;
            if($extract) {
                if($fields_only) {
                    return Set::extract($model->findAll("$this->field = $bool", $this->email_fields), "{n}.$model->name.$this->email_fields");
                } else {
                    return Set::extract($model->findAll("$this->field = $bool"), "{n}.$model->name");
                }
            } else {
                if($fields_only) {
                    return $model->findAll("$this->field = $bool", $this->email_fields);
                } else {
                    return $model->findAll("$this->field = $bool");
                }
            }
        }
    
        /**
         * This method finds all entries that are not emailable
         *
         * @return array An array of all non-emailable objects
         */
        function find_all_non_emailable(&$model, $extract = true, $fields_only = true) {
            $bool = 0;
            // Check if we are going to do a Set:;extract() on the array
            if($extract) {
                // We are going to extract, do they want the fields only or the whole array?
                if($fields_only) {
                    // Return the extracted array with only the fields they want
                    return Set::extract($model->findAll("$this->field = $bool", $this->email_fields), "{n}.$model->name.$this->email_fields");
                } else {
                    // Returns the extracted array with every field included
                    return Set::extract($model->findAll("$this->field = $bool"), "{n}.$model->name");
                }
            } else {
                // We are not going to extract but, do they still want the fields only?
                if($fields_only) {
                    // Return the array with only the fields they wanted
                    return $model->findAll("$this->field = $bool", $this->email_fields);
                } else {
                    // Return the whole array
                    return $model->findAll("$this->field = $bool");
                }
            }
        }
    
        /**
         * Set the emailable field to 0 (false)
         *
         * @param integer $id The id of the record to set to 0
         * @return boolean
         */
        function remove_emailable(&$model, $id) {
            // Get the object by it's id
            $object = $model->findById($id);
    
            // Set the emailable field to 0 (false)
            $object[$model->name][$this->field] = 0;
    
            // If it saves, return true else false
            return ($model->save($object)) ? true : false;
        }
    
        /**
         * Set the emailable field to 1 (true)
         *
         * @param integer $id The id of the record to set to 1
         * @return boolean
         */
        function add_emailable(&$model, $id) {
            // Get the object by it's id
            $object = $model->findById($id);
    
            // Set the emailable field to 1 (true)
            $object[$model->name][$this->field] = 1;
    
            // If it saves, return true else false
            return ($model->save($object)) ? true : false;
        }
    
        /**
         * Send an email message to everyone regardless of emailable
         *
         * @param string $template The message to send
         * @param object $email The email object pre-configured by the user
         */
        function send_campaign_to_all(&$model, $template, $email_options) {
            // Make a new email component object
            $this->__get_email_object();
    
            // Checks the message
            $this->__check_message($template);
    
            // Checks the options
            $this->__check_email_options($email_options);
    
            // Get an array of all objects
            $list = $model->findAll();
    
            // Loop through the object array, sending an email to each
            foreach($list as $object) {
                // Send the email
                if(!$this->__send_mail($model->name, $template, $object, $email_options)) {
                    // Could not send the email, trigger_error and exit
                    trigger_error("Cannot send mail!");
                    exit;
                } else {
                    // The email went through, reset the email component for the next pass
                    $this->__email->reset();
                }
            }
            // All email went through, return true
            return true;
        }
    
        function send_campaign_to_emailable(&$model, $template, $email_options) {
            // Make a new email component object
            $this->__get_email_object();
    
            // Checks the message
            $this->__check_message($template);
    
            // Checks the options
            $this->__check_email_options($email_options);
    
            // Get an array of all non emailable objects
            $list = $this->find_all_emailable($model, false, false);
    
            // Loop through the object array, sending an email to each
            foreach($list as $object) {
                // Send the email
                if(!$this->__send_mail($model->name, $template, $object, $email_options)) {
                    // Could not send the email, trigger_error and exit
                    trigger_error("Cannot send mail!");
                    exit;
                } else {
                    // The email went through, reset the email component for the next pass
                    $this->__email->reset();
                }
            }
            // All email went through, return true
            return true;
        }
    
        /**
         * Send an email to all objects marked as non-emailable
         *
         * @param string $template The message to send
         * @param array $email_options The options to be applied the the Email
         * Component
         * @return true or trigger_error
         */
        function send_campaign_to_non_emailable(&$model, $template, $email_options = array()) {
            // Make a new email component object
            $this->__get_email_object();
    
            // Checks the message
            $this->__check_message($template);
    
            // Checks the options
            $this->__check_email_options($email_options);
    
            // Get an array of all non emailable objects
            $list = $this->find_all_non_emailable($model, false, false);
    
            // Loop through the object array, sending an email to each
            foreach($list as $object) {
                // Send the email
                if(!$this->__send_mail($model->name, $template, $object, $email_options)) {
                    // Could not send the email, trigger_error and exit
                    trigger_error("Cannot send mail!");
                    exit;
                } else {
                    // The email went through, reset the email component for the next pass
                    $this->__email->reset();
                }
            }
            // All email went through, return true
            return true;
        }
    
        /**
         * Makes a new Email Component
         *
         * @param mixed $email
         */
        function __get_email_object() {
            App::import('Component', 'EmailComponent');
            $this->__email = new EmailComponent();
            $this->__email->reset();
        }
    
        /**
         * Checks to make sure the message is a string and not empty
         *
         * @param mixed $template
         */
        function __check_message($template) {
            if(!is_string($template) || empty($template)) {
                trigger_error("The message to be emailed must not be empty and be a string!");
                exit;
            }
        }
    
        /**
         * Makes sure that $email_options is set and has at least a "from" and
         * "subject"
         *
         * @param array $email_options The options to be applied to the email
         * component
         * @return boolean
         */
        function __check_email_options($email_options) {
            if(!empty($email_options)) {
                return (array_key_exists('subject', $email_options) && array_key_exists('from', $email_options)) ? true : false;
            } else {
                return false;
            }
        }
    
        /**
         * Send the email
         *
         * @param string $model_name The name of the model that is using this
         * behavior
         * @param string $template The message to send
         * @param array $object The object array to send to
         * @param array $email_options The options for the email component
         * @return boolean
         */
        function __send_mail($model_name, $template, $object, $email_options) {
            // Extract the keys for the specific model
            extract($object["$model_name"]);
    
            // Start the output buffer
            ob_start();
    
            // Include the template. This will substitute and passed variables in the string with the extracted keys
            include($template);
    
            // Clean up the buffer and store the new message in a variable
            $new_message = ob_get_clean();
    
            // Set who this email is going to
            $this->__email->to = ${$this->email_fields};
    
            // Set any other email options
            $this->__set_email_options($email_options);
    
            // Send the email and return the results
            return ($this->__email->send($new_message)) ? true : false;
        }
    
        /**
         * This method just sets the options passed for the email component. At the
         * very least, we need a "from" and a "subject"
         *
         * @param array $email_options An array of email options
         */
        function __set_email_options($email_options) {
            foreach($email_options as $eKey => $eValue) {
                $this->__email->$eKey = $eValue;
            }
        }
    }
    ?>


.. meta::
    :title: Acts As Emailable
    :description: CakePHP Article related to list,email,behavior,acts,act,Behaviors
    :keywords: list,email,behavior,acts,act,Behaviors
    :copyright: Copyright 2008 Thades
    :category: behaviors

