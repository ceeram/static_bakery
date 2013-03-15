Easy Email Address Encoder
==========================

by %s on June 26, 2008

While there are countless solutions to protecting email addresses from
page scanners, here's an easy helper to add another line of defense.
This handy helper takes a normal email address and scrambles it up
into a mix of text, decimal, and hexadecimal characters.

Add the following code to views/helpers/mailto.php (you will need to
create the file).

Helper Class:
`````````````

::

    <?php 
    class MailtoHelper extends AppHelper
    {
    	function encode($mail, $text="", $class="", $params=array())
    	{
    		$encmail ="";
    		for($i=0; $i<strlen($mail); $i++)
    		{
    			$encMod = rand(0,2);
    	        switch ($encMod) {
    	        case 0: // None
    	            $encmail .= substr($mail,$i,1);
    	            break;
    	        case 1: // Decimal
    	            $encmail .= "&#".ord(substr($mail,$i,1)).';';
    	            break;
    	        case 2: // Hexadecimal
    				$encmail .= "&#x".dechex(ord(substr($mail,$i,1))).';';
    	            break;
    			}
    		}
    
    		if(!$text)
    		{
    			$text = $encmail;
    		}
    		$encmail = "mailto:".$encmail;
    		$querystring = "";
    		foreach($params as $key=>$val)
    		{
    			if($querystring){
    				$querystring .= "&$key=".rawurlencode($val);
    			} else {
    				$querystring = "?$key=".rawurlencode($val);
    			}
    		}
    		return "<a class='$class' href='$encmail$querystring'>$text</a>";
    	}
    }
    ?>

Add the helper to your controller like so:

Controller Class:
`````````````````

::

    <?php 
    class MyController extends AppController
    {
    	var $name = 'MyController';
    	var $helpers = array('Mailto');
    }
    ?>


In your view, you can do this:

::

    
    <?php e($mailto->encode($mail, $text, 'test foo', array('subject'=>'subject', 'body'=>'body'))) ?>

or this:

::

    
    <?php e($mailto->encode($mail, $text, 'test foo')) ?>

or this:

::

    
    <?php e($mailto->encode($mail, $text)) ?>

or just this:

::

    
    <?php e($mailto->encode($mail)) ?>

You'll get something like:

::

    
    <?php e($mailto->encode('example@example.com')) ?>
    // outputs <a class='' href='mailto:example@example.com'>example@example.com</a>	

See?

Easy.

.. meta::
    :title: Easy Email Address Encoder
    :description: CakePHP Article related to ,Helpers
    :keywords: ,Helpers
    :copyright: Copyright 2008 
    :category: helpers

