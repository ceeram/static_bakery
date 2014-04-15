JS Vaildator
============

by GarthDB on May 03, 2007

This helper generates JavaScript validation based on the model's
validate array, and also offers a few extra features. These features
include: Server: a Ajax script to validate one field server-side (this
was originally designed to make sure a field is unique on the table ie
username or email) Confirm: to check that two or more fields are equal
(designed for password fields or to confirm email accounts) Custom
required fields: if you don't want to use strictly the model's
validate array or if you want to append a few fields that may not
actually appear in the array Currently the validator uses a JavaScript
alert to let user know what changes need to be made, but I am going to
make it optional to have the results returned in a div tag of your
choice as a unordered list. Until I finish that this will get you
started. Please let me know if this helps anyone, or how I could
improve it.
For a list of functions in this helper see page 2
`http://bakery.cakephp.org/leafs/view/17`_.
For an example of it in use please see page 3
`http://bakery.cakephp.org/leafs/view/18`_.
Here is the helper code. Create app/views/helpers/jsvalid.php and copy
the following code into it.

Helper Class:
`````````````

::

    <?php 
    class JsvalidHelper extends Helper
    {
    	var $helpers = array('Html','Javascript','Form','Ajax','Jsvalid');
    	var $validate;
    	var $model;
    	var $changeClass = true;
    	var $errorClass = 'errors';
    	var $ulUpdate;
    	var $blurCheck = false;
    	var $alertFlag = true;
    	var $feedback;
    	var $unique = false;
    	var $script="<script type='text/javascript'>
    function errorClass(id,newClass){
        var elem_ar = document.getElementsByTagName('label');
        var classOld = '';
        var labelFor = '';
        var elem;
        for(x in elem_ar){
            labelFor = elem_ar[x].htmlFor+'';
            if(labelFor.indexOf(id) != -1){
                elem = elem_ar[x];
            }
        }
        classOld = elem.className+'';
        if(classOld.indexOf(newClass) == -1){
            elem.className = newClass+' '+classOld;
        }
    }
    function validClass(id,newClass){
        var elem_ar = document.getElementsByTagName('label');
        var classOld = '';
        var labelFor = '';
        var elem = '';
        for(x in elem_ar){
            labelFor = elem_ar[x].htmlFor+'';
            if(labelFor.indexOf(id) != -1){
                elem = elem_ar[x];
            }
        }
        if(elem!=''){
            classOld = elem.className+'';
            if(classOld.indexOf(newClass+' ') != -1){
                elem.className = classOld.replace(newClass+' ','');
            } else if (classOld.indexOf(newClass) != -1){
                elem.className = classOld.replace(newClass,'');
            }
        }
    }
    function jsvalidateForm(form){
    	var valid = true;
    	var result = 'Please fix the following error(s):';";
    	function feedbackfun($field){
    		if(!empty($this->feedback)){
    			return $this->feedback[$field];
    		} else {
    			return $field.' is not valid';
    		}
    	}
    	function setModel ($model,$validate = null){
    		if($validate != null){
    			$this->validate = $validate;	
    		} else {
    			$this->validate = $this->view->controller->{$model}->validate;
    		}
    		$this->model = $model;
    		if(!empty($this->view->controller->{$model}->jsFeedback) && isset($this->view->controller->{$model}->jsFeedback)){
    			$this->feedback = $this->view->controller->{$model}->jsFeedback;
    		}
    		return ($this->validate);
    	}
    	function input($fieldName,$label,$fieldAtt = null){
    		$labelTag = $this->Form->labelTag($fieldName,$label);
    		$inputTag = $this->Html->input($fieldName,$fieldAtt);
    		return $this->output($labelTag.$inputTag);
    	}
    	function textarea($fieldName,$label, $fieldAtt = null){
    		$labelTag = $this->Form->labelTag($fieldName,$label);
    		$textareaTag = $this->Html->textarea($fieldName,$fieldAtt);
    		return $this->output($labelTag.$textareaTag);
    	} 	
    	function password($fieldName,$label,$fieldAtt = null){
    		$labelTag = $this->Form->labelTag($fieldName,$label);
    		$inputTag = $this->Html->password($fieldName,$fieldAtt);
    		return $this->output($labelTag.$inputTag);
    	}
    	function form($url = null, $name = null, $method = 'post'){
    		$formTag = "<form action='".$this->Html->url($url)."' method='{$method}' onSubmit='jsvalidateForm(this); return false;'";
    		if ($name != null){
    			$formTag .=" name='{$name}'>";
    		} else {
    			$formTag .='>';
    		}
    		return $formTag;
    	}
    	function required($fields = array()){
    		if(empty($fields)){
    			foreach($this->validate as $key => $value):
    				$this->script .='
    	if(form.'.Inflector::camelize($this->model." ".$key).'){
    		str = form.'.Inflector::camelize($this->model." ".$key).'.value;
    		regexp = '.$value.';
    		if(!str.match(regexp)){
    			valid = false;
    			result +="\n'.$this->feedbackfun($key).'";';
    				if($this->changeClass){
    					$this->script .='
    			errorClass("'.Inflector::camelize($this->model." ".$key).'","'.$this->errorClass.'");
    		} else {
    			validClass("'.Inflector::camelize($this->model." ".$key).'","'.$this->errorClass.'");
    		}';
    				} else {
    					$this->script .='
    		}';
    				}
    				$this->script .='
    	}';
    			endforeach;
    		} else {
    			foreach($fields as $field => $feedback):
    				if(is_int($field)){
    					$field = $feedback;
    					$feedback = false;
    				}
    				$fieldName_ar = explode("/",$field);
    				$fieldName = Inflector::camelize($fieldName_ar[0]." ".$fieldName_ar[1]);
    				$regExp = $this->validate[$fieldName_ar[1]];
    				if($feedback == false){
    					$feedback = $this->feedbackfun($fieldName_ar[1]);
    				}
    				$this->script .='
    	if(form.'.$fieldName.'){
    		str = form.'.$fieldName.'.value;
    		regexp = '.$regExp.';
    		if(!str.match(regexp)){
    			valid = false;
    			result +="\n'.$feedback.'";';
    		if($this->changeClass){
    			$this->script .='
    			errorClass("'.$fieldName.'","'.$this->errorClass.'");
    		} else {
    			validClass("'.$fieldName.'","'.$this->errorClass.'");
    		}';
    		} else {
    			$this->script .='
    		}';
    		}
    		$this->script .='
    	}';
    			endforeach;
    		}
    	}
    	function confirm($field,$confirms = array()){
    		$fieldName_ar = explode("/",$field);
    		$fieldName = Inflector::camelize($fieldName_ar[0]." ".$fieldName_ar[1]);
    		foreach($confirms as $key => $value):
    			$confirm_ar = explode("/",$key);
    			$confirmName = Inflector::camelize($confirm_ar[0]." ".$confirm_ar[1]);
    			$this->script .= '
    	if(form.'.$fieldName.'.value != form.'.$confirmName.'.value){
    		valid = false;
    		result +="\n'.$value.'";
    		errorClass("'.$confirmName.'","'.$this->errorClass.'");
    	} else {
    		validClass("'.$confirmName.'","'.$this->errorClass.'");
    	}
    	';
    		endforeach;
    	}
    	function server($field, $label, $url, $divClass = 'jsunique',$fieldAtt = null){
    		$fieldName_ar = explode("/",$field);
    		$fieldName = Inflector::camelize($fieldName_ar[0]." ".$fieldName_ar[1]);
    		$labelTag = $this->Form->labelTag($field,$label);
    		$inputTag = $this->Html->input($field,$fieldAtt);
    		$button = "<input type='button' value='Check' onclick='unique(\"".$fieldName."\")'/>";
    		$divTag = "<div id='jsu".$fieldName."' class='".$divClass."'></div>";
    		$script = "<script type='text/javascript'>
    function unique (id){
    	elem = document.getElementById(id);
    	new Ajax.Updater('jsu".$fieldName."', '".$url."', {asynchronous:true, evalScripts:true, parameters:Form.Element.serialize(elem)});	
    }
    </script>";
    		return $script.$labelTag.$inputTag.$button.$divTag;
    	}
    	function returnScript(){
    		$this->script .= '
    	if(valid){
    		form.submit();
    	} else {
    		alert(result);
    	}';
    		$this->script.="
    }";
    		
    		$this->script .="
    </script>
    ";
    		return $this->script;
    	}
    }
    ?>



I thought an api of sorts would be useful:

::

    JsvalidHelper::confirm($field,$confirms)

Compares two or more fields to ensure that the values are equal (used
for password and email fields)

Parameters

string $field Name of the field that will be the standard of
comparison

array $confirms Use the other field names as the keys in this array,
and the values are the feedback.

::

    JsvalidHelper::form($url = null, $name = null, $method = 'post')

Returns an opening form tag with the onSubmit function needed to
execute the jsvalid's validation function

Parameters

string $url Upon successful validation data will be passed to this url

string $name You can include a name property in the tag if desired.

string $method Either Post or Get to send the data upon passing the
validation.

::

    JsvalidHelper::input($fieldName,$label,$fieldAtt = null)

Returns a label tag and a text input tag formatted to work with the
rest of the helper

Parameters

string $fieldName The name of the field ('Model/field')

string $label The text to be displayed in the label tag

array $fieldAtt Html attributes for the input tag

::

    JsvalidHelper::password($fieldName,$label,$fieldAtt = null)

Returns a label tag and a password input tag formatted to work with
the rest of the helper

Parameters

string $fieldName The name of the field ('Model/field')

string $label The text to be displayed in the label tag

array $fieldAtt Html attributes for the input tag

::

    JsvalidHelper::returnScript()

Returns the JavaScript code block that contains the validation
function.

::

    JsvalidHelper::server($field, $label, $url, $divClass = 'jsunique',$fieldAtt = null)

Returns a label tag, a text input tag, a button, a JavaScript
function, and an empty div tag. The JavaScript function submits the
value in the text input to the url via ajax and puts the results in
the empty div tag.

Parameters

string $field Name of a field, like this "Modelname/fieldname"

string $label Text that will appear in the label field.

string $url The URL where the data will be sent to be checked.

string $divClass The CSS class for the empty div tag

array $htmlAttributes Array of HTML attributes for the input field.

::

    JsvalidHelper::setModel ($model,$validate = null)

Defines what model will be used to get access to the validate and
jsFeeback (optional) arrays from the model.

Parameters

string $model Name of the model that will be used to validate

array $validate An option array that of RegEx that can be used to
validate form. If null the validate array from the model will be used.

::

    JsvalidHelper::textarea($fieldName,$label,$fieldAtt = null)

Returns a textarea tag and a text input tag formatted to work with the
rest of the helper

Parameters

string $fieldName The name of the field ('Model/field')

string $label The text to be displayed in the label tag

array $fieldAtt Html attributes for the input tag

I am sure you will have more creative uses for the helper, but here is
something basic to get you started. The example is a simple add user
action.
Here is the Users model app/models/user.php.

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	var $name = 'User';
    	var $displayField = 'fname';
    	var $validate = array(
    		'fname' => VALID_NOT_EMPTY,
    		'lname' => VALID_NOT_EMPTY,
    		'email' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',
    		'password' => '/^[_a-z0-9-][_a-z0-9-][_a-z0-9-][_a-z0-9-][_a-z0-9-][_a-z0-9-]+$/'
    	);
    	var $jsFeedback = array(
    		'fname' => 'Enter a first name',
    		'lname' => 'Enter a last name',
    		'email' => 'Enter a valid email',
    		'password' => 'Your password must be at least 6 characters'
    	);
    }
    ?>

The $jsFeedback array contains the strings that the user will see if
their information does not validate for that field. Make sure that
they keys in the $jsFeedback match the keys in $validate. It is not
required to use this array; there is a place on the helper to specify
what you want the feedback to be if you don't want to do it in the
model.

Here is the controller example. Make sure to have the var $helpers
with all the helpers listed here as all are used in this example. This
controller is app/controllers/users_controller.php

Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController
    {
        var $name = 'Users';
        var $helpers = array('Html', 'Javascript', 'Ajax','Form','Jsvalid'); 
        function add(){
        }
        function validator(){
    		$this->layout = '';
    		$this->set('user',$this->User->query("SELECT * FROM `users` WHERE `email` = '{$this->data['User']['email']}'"));
    	}
    }
    ?>

Now lets look at the add view app/views/users/add.thtml

View Template:
``````````````

::

    
    <?php 
    if(isset($javascript))
    {
        echo $javascript->link('prototype');
        echo $javascript->link('scriptaculous.js');
    }
    ?>
    <style>
    .errors{
        color:#F00;
    }
    </style>
    <? $jsvalid->setModel('User'); ?>
    <? $jsvalid->required();?>
    <? $jsvalid->confirm('User/password',array('User/confirm'=>'The password and confirm do not match'));?>
    <? echo $jsvalid->returnScript(); ?>
    <? echo $jsvalid->form('/users/validator')?>
    	<? echo $jsvalid->server('User/email', 'Email', '/users/validator'); ?>
    	<? echo $jsvalid->password('User/password','Password');?>
    	<? echo $jsvalid->password('User/confirm','Confrim Password');?>
    	<? echo $jsvalid->input('User/fname','First Name');?>
    	<? echo $jsvalid->input('User/lname','Last Name');?>
    	<? echo $html->submit();?>
    </form>

Ok lets step through this a bit. The first chunk of code makes sure
that the javascript helper is enabled, and then links the two
libraries need for ajax. This is only necessary if you are going to
use the server function. Get the libraries from
`http://script.aculo.us/`_ and put them in the app/webroot/js folder.
Next, the style tag pertains to the change class function that I made
to change the color of the label of the field that has problems after
the validate. If you don't want to change anything, don't put any
changes in the errors class. You can call the class whatever you want.

::

    $jsvalid->setModel('User');

This is essential. This is how the helper gets the validate array from
the model, and the jsFeedback array.

::

    $jsvalid->required();

This is the most useful function. if you don't put in any parameters
it will just require all the fields in the validate array but you have
the option to put in an array to specify what fields you want to
require. here are a couple examples:

::

    $jsvalid->required(array('User/email','User/password');

or you can also specify the feedback for that field (bypassing the
jsFeedback array) by using a key value array

::

    $jsvalid->required(array('User/email'=>'Hey smart guy, put in an email','User/password'=>'how can you make a new user without a password');

or mix the two

::

    $jsvalid->required(array('User/email'=>'Hey smart guy, put in an email','User/password');


::

    $jsvalid->confirm('User/password',array('User/confirm'=>'The password and confirm do not match'));

Ok this is to check that two or more fields are equal. The first
parameter the first field that you will be checking the others with.
next the array is for fieldnames => feedback to check.

::

    echo $jsvalid->returnScript();

This returns the javascript code block so echo it out wherever you
want on the page.

The next bit of code uses some of the jsvalid helpers tidy functions.
They combine the HTML, and Form helpers. Most of them make a label,
and a input of some sort.

::

    echo $jsvalid->form('/users/validator')

This returns a starting form tag, and it contains the execution of the
javascript function.

::

    echo $jsvalid->server('User/email', 'Email', '/users/validator');

This is kind of a cool extra, but it doesn't really tie into the rest
of the functionality of the helper, so I guess it could be used alone.
This makes a label, a text input tag, a button, an empty div tag, and
some simple ajax. It makes a function that when the button is pressed
it sends the data in the input tag to a url and returns the results
into the empty div tag. It was originally designed for users to be
able to check if a username is already used. The parameters are field
name, label text, and the url where it will be sent.

::

    echo $jsvalid->password('User/password','Password');

This returns a label and input pair. In this case it is a password
input, but the text input is the same syntax. The first parameter is
the field name, the second is the label text.

and that is pretty much it for this view.

If you aren't planning on using the server function you should be go
to go, if you are lets keep going.

Lets review the validator action in the users controller.

::

    
    function validator(){
    	$this->layout = '';
    	$this->set('user',$this->User->query("SELECT * FROM `users` WHERE `email` = '{$this->data['User']['email']}'"));
    }

Since the server function puts the content of this page in the div we
need to make sure that it doesn't use a layout, not even the default.
Next this is just some sql to check to see if the email is already in
the database. It sets the results of the query to the variable user

app/views/users/validator.thtml

View Template:
``````````````

::

    
    <? if(empty($user)){
    	echo "New user";
    } else {
    	echo "This email address is already found in the database";
    }
    ?>

This is just a simple if else to tell the user if the email address
was already in the database or not.
`1`_|`2`_|`3`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_

.. _http://script.aculo.us/: http://script.aculo.us/
.. _Page 1: :///articles/view/4caea0dd-a92c-44b8-99a1-408282f0cb67/lang:eng#page-1
.. _Page 2: :///articles/view/4caea0dd-a92c-44b8-99a1-408282f0cb67/lang:eng#page-2
.. _Page 3: :///articles/view/4caea0dd-a92c-44b8-99a1-408282f0cb67/lang:eng#page-3
.. _http://bakery.cakephp.org/leafs/view/17: http://bakery.cakephp.org/leafs/view/17
.. _http://bakery.cakephp.org/leafs/view/18: http://bakery.cakephp.org/leafs/view/18

.. author:: GarthDB
.. categories:: articles, helpers
.. tags:: form,Helpers

