dAuth v0.3 helper
=================

by Dieter_be on November 15, 2006

helper for dAuth v0.3
Iirc, the only real change i made for v0.3 is improving the names of
variables and function calls (like doStage1, stage2,..)

views/helpers/d_auth.php
````````````````````````

Helper Class:
`````````````

::

    <?php 
    /*
     * PHP versions 4 and 5
     *
     * dAuth: A secure authentication system for the cakePHP framework.
     * Copyright (c)	2006, Dieter Plaetinck
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author			Dieter Plaetinck
     * @copyright		Copyright (c) 2006, Dieter Plaetinck
     * @version			0.3
     * @modifiedby		Dieter@be
     * @lastmodified	$Date: 2006-12-04 16:18:00 +0000 (Mon, 4 Dec 2006) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    class dAuthHelper extends Helper
    {
    	var $helpers = array('html','javascript');
    	var $noClearTextErrorId = 'impossible_login_error';
    	var $noClearTextErrorMessage = ' impossible.  For security reasons, you should enable javascript.';
    	var $noClearTextFormId = 'not_working_form';
    
    	function loadJs()
    	{
    		return $this->output($this->javascript->link('sha1').$this->javascript->link('d_auth'));
    	}
    	function formHeader($action,$formAction,$cleartext)
    	{
    		$output ='';
    		if($action && $formAction)
    		{
    			if ($cleartext)
    			{
    				$output = "<form action='$formAction' method='post'>";
    			}
    			else
    			{
    				$output = "<p class='error_message' id='$this->noClearTextErrorId'>$action $this->noClearTextErrorMessage</p>";
    				$output .= "<form id='$this->noClearTextFormId' style='display:none'>";
    				$output .= $this->javascript->codeBlock("removeError('$this->noClearTextErrorId');fixForm('$this->noClearTextFormId','$formAction');");
    			}
    		}
    		return $this->output($output);
    	}
    
    	function errorMsg($action,$error)
    	{
    		$output = '';
    		if(!$action)
    		{
    			$action = 'Action';
    		}
    		if ($error)
    		{
    			$output = "<p class='error_message'>$action failed: $error</p>";
    		}
    		return $this->output($output);
    	}
    
    	function emptyField($id = null)
    	{
    		$output ='';
    		if ($id)
    		{
    		    $output = $this->javascript->codeBlock("emptyField('$id');");
    		}
    		return $this->output($output);
    	}
    
    	function formInput($name,$type)
    	{
    		$output ='';
    		if($name && $type)
    		{
    			$output = "<label for='".low($name)."' class='label'>$name:</label><br/>";
    			$output .= $this->html->input($type, array('size' => 20, 'class' => 'TextField', 'id'=>low($name)));
    			$output .= $this->html->tagErrorMsg($type, 'Please enter your '.low($name)).'<br/>';
    		}
    		return $this->output($output);
    	}
    
    	function formPassword($name,$type)
    	{
    		$output ='';
    		if($name && $type)
    		{
    			$output = "<label for='".low($name)."' class='label'>$name:</label><br/>";
    			$output .= $this->html->password($type, array('size' => 20, 'class' => 'TextField', 'id'=>low($name)));
    			$output .= $this->html->tagErrorMsg($type, 'Please enter your '.low($name)).'<br/>';
    		}
    		return $this->output($output);
    	}
    
    	function hiddenField($name,$type,$value)
    	{
    		$output ='';
    		if($name && $type)
    		{
    			$output = $this->html->input($type, array('type' => 'hidden', 'id'=>low($name), 'value' => $value)).'<br/>';
    		}
    		return $this->output($output);
    	}
    	function submit($name = null,$stage2 = true)
    	{
    		if(!$name)
    		{
    			$name = 'Submit';
    		}
    		$onclick ='';
    		if($stage2)
    		{
    			$onClick = 'Javascript:return doStage2();';
    		}
    		else
    		{
    			$onClick = 'Javascript:return doStage1();';
    		}
    
    		$output = $this->html->submit($name, array('class'=>'Button', 'onclick'=>$onClick));
    
    		return $this->output($output);
    	}
    }
    ?>

more info about dAuth @ `http://bakery.cakephp.org/articles/view/147`_

.. _http://bakery.cakephp.org/articles/view/147: http://bakery.cakephp.org/articles/view/147

.. author:: Dieter_be
.. categories:: articles, helpers
.. tags:: login,dauth,challenge response,secure,Helpers

