Ajax Validation Component
=========================

by GarthDB on October 06, 2006

This component adds a few validation options to the already wonderful
CakePHP form validation. These features include: Confirm - Checks two
or more fields to make sure they are an exact match (designed for
password creation). Unique - Checks to make sure that a field is not
already present in the database (ie email, username, etc). More output
options - There are three output options - unordered list in a div tag
of your choice, a JavaScript alert, or just the array to do what you
want with it. Error Class - It has the option to change the CSS class
of the label tags for the invalid fields. The component uses Ajax to
be able to validate without refreshing the page. It also relies
heavily on the "Standard" CakePHP naming conventions for easier
integration.
If you have any problems you can also email me at
garth_dot_d_dot_braithwaite_at_gmail_dot_com. I have the component
below, feel free to copy and put it in a new file in
app/controllers/components/ajax_valid.php. Then you will need a come
code in the view, and controller, so I will outline it below. I think
there is a better way to do some of this so feel free to let me know
any suggestions you might have.

Here is an example of a users controller.

Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController
    {
    	var $name = 'User';
    	var $components = array('AjaxValid');//Make sure you include this, it makes the magic work.
    	var $helpers = array('Html', 'Javascript', 'Ajax','Form'); //I use these in my views so I include them here.  You can do however you like.  If you don't know how to use helpers just put this line in your controller.
    	function add(){//This component is really designed for add and edit, but there are more uses than I have thought of.
    		//here goes whatever you need for your add form and what not, the real work is done in the validator action.
    	}
    	function validator(){//This is the validator action. ha!
    		$this->layout = '';//It is best to give it a null layout because using ajax whatever is shown in this view will be the output on the form page
    		$this->AjaxValid->return = 'javascript';//This specifies how you want the validation result returned.  Your three options here are javascript, html, and arrary.  The javascript returns as an alert (which bugs some people, but it is commonly used.  html is nicer, it returns as an unordered list that you can modify with css. array is just for debugging purposes.
    		$this->AjaxValid->changeClass('errors');//This is an option that will change the class of the erroneous field labels.  I use error, but use what you want.
    		$this->AjaxValid->setForm($this->data,'User/index','redirect');//This is where you send the data from the form through to the component, so you probably want to leave the first parameter as $this->data, the third tells it to redirect after it is valid, and it redirects to the url in the second parameter.
    		$this->AjaxValid->required(array('User/fname','User/lname','User/email','User/password'));//this means that you are requiring these fields to be valid according to the validation in the model.
    		$this->AjaxValid->unique(array('User/email');//This checks to make sure that the email entered by the user is unique.
    		$this->AjaxValid->confirm('User/password', array('User/confirm'), 'Your passwords do not match');//This is designed for creating a new password.  If you have a user type in a password and retype it to confirm they didn't screw it up.  It just checks the string in the first parameter matches all the strings in the array of the second parameter.  The third parameter is the text that is returned by the validator.
    		$this->set('data',$this->AjaxValid->validate());//This sends the validation result to the validator view.
    		$this->set('valid',$this->AjaxValid->valid);//This sends a boolean of the result of the validation to the validator view, whether it is true(valid) or false(invalid)
    	if($this->AjaxValid->valid){//In this example I have the validator action save the data from the form if it is valid and then the view will redirect it.
    		$this->User->save($this->AjaxValid->form['User'];
    	}
    }?>

Here is the form view.. in this example the view for the add action

View Template:
``````````````

::

    
    <?php // you need to make sure you download the prototype and scriptaculous libraries are in your app/webroot/js folder.  If you don't know how to do this email me or ask around.  It is a pretty common task.
    if(isset($javascript))
    {
        echo $javascript->link('prototype');
    	echo $javascript->link('scriptaculous.js');
    }
    ?>
    <style><? //This is just for the color change for erroneous fields, there might be a better place to put this style on your cake project ?>
    .errors{
    	color:#F00;
    }
    </style>
    <? echo $ajax->form($params = array('action'=>'/users/validator'),$type = 'post',$options = array('url'=>'User/validator','update'=>'updater'));// This is why we have the ajax helper.  This is a ajax-form-open-tag-maker.  The important thing is the options array - make the url go to the validator and the update is the id of the div tag that ajax will dump the result of the validation into. ?>
    <? echo $form->labelTag('User/email','Email');// This component utilizes the <label> tag to change the class of the erroneous fields. This is why we have the form helper ?>
    <? echo $html->input('User/email'); //Then this is the actual input field?>
    <? echo $form->labelTag('User/password','Password');?>
    <? echo $html->password('User/password');?>
    <? echo $form->labelTag('User/confirm','Confrim Password');?>
    <? echo $html->password('User/confirm');//Please note that confirm does not get saved in the db, so it is not even a part of the user model?>
    <? echo $form->labelTag('User/fname','First Name');?>
    <? echo $html->input('User/fname');?>
    <? echo $form->labelTag('User/fname','Last Name');?>
    <? echo $html->input('User/lname');?>
    <? echo $html->submit();?>
    </form>
    <div id="updater"></div><? //This is the div tag where the results of the validation go.?>

Here is the validator view

View Template:
``````````````

::

    
    <? if($valid){//This echos the result of the validation.
    	echo "Valid";
    } else {
    	echo "not valid";
    } ?>
    <pre><? print_r($data);//I put this in a <pre> tag in case I need to debug and set the $this->AjaxValid->return to be "array" in the validator action so I can debug it easily. Take off the pre tag if you want the html result.?></pre>


Component Class:
````````````````

::

    <?php 
    class AjaxValidComponent extends Object{
    	var $controller = true;
    	var $valid = true;//Valid until proven otherwise
    	var $errors = array();//Where the list of errors will be stored
    	var $form = array();//Where the form data will be stored
    	var $return = "array";
    	var $html;
    	var $javascript;
    	var $classFlag = false;
    	var $actionUrl;
    	var $method;
    	function startup(&$controller){
    		// This method takes a reference to the controller which is loading it.
    		// Perform controller initialization here.
    		$this->controller = &$controller;
    	}
    	function setForm($form = array(), $actionUrl = null, $method = nulll){
    		$this->form = $form;
    		if ($actionUrl!=null){
    			if($method != null){
    				$this->method = $method;
    			}
    			$regEx = "^(ftp|http|https)://(www.)?";
    			if(!ereg($regEx,$actionUrl)){
    				$regEx = "^[A-Z][a-z]+/[a-z]+$";
    				if(ereg($regEx,$actionUrl)){
    					$url_ar = explode('/',$actionUrl);
    					$actionUrl = strrchr(ROOT, "/").'/'.Inflector::pluralize(strtolower($url_ar[0])).'/'.$url_ar[1];
    				}
    			}
    			$this->actionUrl = $actionUrl;
    		} else {
    			$this->actionUrl = false;
    		}
    	}
    	function submit(){
    		if($this->valid){
    			$elem = Inflector::camelize(key($this->form)." ".key($this->form[key($this->form)]));
    			$submitStr = "";
    			$submitStr = "<script type='text/javascript'>";
    			$submitStr .= "elem = document.getElementById('".$elem."');\n";
    			$submitStr .= "elem.form.action = '".$this->actionUrl."';\n";
    			$submitStr .= "alert(elem.form.action);\n";
    			$submitStr .= "elem.form.method='POST';\n";
    			$submitStr .= "//elem.form.submit();\n";
    			$submitStr .= "</script>";
    			return $submitStr;
    		}
    	}
    	function jsRedirect(){
    		if($this->valid){
    			$redirStr = "";
    			$redirStr = "<script type='text/javascript'>";
    			$redirStr .= "document.location = '".$this->actionUrl."';\n";
    			$redirStr .= "</script>";
    			return $redirStr;
    		}
    	}
    	function confirm($initField = string, $fields = array(), $errormsg = string){
    		$init_ar = explode("/",$initField);
    		foreach($fields as $field){
    			if($this->form[$init_ar[0]][$init_ar[1]] != $field){
    				$this->valid = false;
    				$this->errors[$initField]['confirm'] = $errormsg;
    				break;
    			}
    		}
    	}
    	function required($fields = array()){
    		foreach ($fields as $field){
    			$field_ar = explode('/',$field);
    			if(is_array($this->controller->{$field_ar[0]}->validate[$field_ar[1]])){
    				foreach ($this->controller->{$field_ar[0]}->validate[$field_ar[1]] as $key => $required){
    					if(!preg_match($required['expression'],$this->form[$field_ar[0]][$field_ar[1]])){
    						$this->errors[$field]['required'][$key] = $required['message'];
    						$this->valid = false;
    					}
    				}
    			} else {
    				if(!preg_match($this->controller->{$field_ar[0]}->validate[$field_ar[1]],$this->form[$field_ar[0]][$field_ar[1]])){
    					$this->errors[$field]['required'][$field_ar[1]] = Inflector::humanize(str_replace("_id","",$field_ar[1]))." is required.";
    					$this->valid = false;
    				}
    			}
    		}
    	}
    	function unique($table = array()){
    		foreach ($table as $key => $fields):
    			foreach($fields as $field):
    				$field_ar = explode('/',$field);
    				$model = $field_ar[0];
    				$fieldName = $field_ar[1];
    				$tableField = str_replace('/','.',$field);
    				$result = $this->controller->User->find(array($tableField =>$this->form[$model][$fieldName]) ,$tableField);
    				if(!empty($result)){
    					$this->errors[$field]['unique'] = $this->form[$model][$fieldName].' already exsists in the db.';
    					$this->valid = false;
    				}
    			endforeach;
    		endforeach;
    	}
    	function changeClass($errorClass = string){
    		$this->classFlag = $errorClass;
    	}
    	function changeClassFun (){
    		if (!$this->valid){
    		$classStr = "";
    		$classStr = "<script type='text/javascript'>";
    		$classStr .= "
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
    }";
    	foreach($this->form as $parentKey =>$parentVal):
    		foreach ($parentVal as $childKey => $childVal):
    			$childKey_cam = Inflector::camelize($childKey);
    			if(!empty($this->errors[$parentKey."/".$childKey])){
    				$classStr.="errorClass('".$parentKey.$childKey_cam."','".$this->classFlag."');
    ";
    			} else {
    				$classStr.="validClass('".$parentKey.$childKey_cam."','".$this->classFlag."');
    ";
    			}
    		endforeach;
    	endforeach;
    	$classStr .= "</script>" ;
    	return $classStr;
    	}
    	}
    	function validate (){
    		switch ($this->return){
    		case 'array':
    			return $this->errors;
    			break;
    		case 'html':
    			$this->html = '<ul class="errorsList">';
    			foreach ($this->errors as $err_key => $err_val):
    				$this->html .='<li>'.ucfirst(substr($err_key,strpos($err_key,'/')+1));
    				$this->html .= '<ul class="errorChild">';
    				foreach ($err_val as $error1):
    						if(is_array($error1)){
    						foreach ($error1 as $error2):
    							$this->html .='<li>'.$error2.'</li>';
    						endforeach;
    						} else {
    							$this->html .='<li>'.$error1.'</li>';
    						}
    				endforeach;
    				$this->html .='</ul></li>';
    			endforeach;
    			$this->html .= '</ul>';
    			if($this->classFlag != false){
    				$this->html .=$this->changeClassFun();
    			}
    			if($this->method == 'submit'){
    				$this->html .= $this->submit();
    			}
    			if($this->method == 'redirect'){
    				$this->html .= $this->jsRedirect();
    			}
    			return $this->html;
    			break;
    		case 'javascript':
    			if(!$this->valid){
    				$this->javascript = '<script type="text/javascript">alert("';
    				$this->javascript .= 'Please fix the following Errors:\\n';
    				foreach ($this->errors as $err_val):
    					foreach ($err_val as $error1):
    						if(is_array($error1)){
    							foreach ($error1 as $error2):
    								$this->javascript .='- '.$error2.'\\n';
    							endforeach;
    							} else {
    								$this->javascript .='- '.$error1.'\\n';
    							}
    					endforeach;
    				endforeach;
    				$this->javascript .='");</script>';
    			}
    			if($this->classFlag != false){
    				$this->javascript .=$this->changeClassFun();
    			}
    			if($this->method == 'submit'){
    				$this->javascript .= $this->submit();
    			}
    			if($this->method == 'redirect'){
    				$this->javascript .= $this->jsRedirect();
    			}
    			return $this->javascript;
    			break;
    		case 'test':
    			return $this->submit();
    			break;
    		}
    	}
    }
    ?>


.. meta::
    :title: Ajax Validation Component
    :description: CakePHP Article related to forms,Components
    :keywords: forms,Components
    :copyright: Copyright 2006 GarthDB
    :category: components

