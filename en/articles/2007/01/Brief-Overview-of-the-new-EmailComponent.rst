

Brief Overview of the new EmailComponent
========================================

by %s on January 02, 2007

It's often more practical (read: maintainable) to use the built in
components and much as I like SwiftMailer, I thought it best that I
investigated Cake's core EmailComponent.
Big Note: The examples below are not intented for production servers,
but just to give an idea of the files/code snippets needed to send
email.

First create your email layouts. Create a default.ctp in the following
directories:

::

    
    views/layouts/email/text/
    
    views/layouts/email/html/

The text layout (not a template):

View Template:
``````````````

::

    
    <?php echo $content_for_layout; ?>

and the html layout:

View Template:
``````````````

::

    
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <html>
    <body>
    <?php echo $content_for_layout; ?>
    </body>
    </html>

This is a dummy controller for demonstration purposes only:

Controller Class:
`````````````````

::

    <?php 
    class MailerController extends AppController {
    
    	var $name = 'Mailer';
    	//Not using a model
    	var $uses = '';
    	//The built in Cake Mailer
    	var $components = array('Email');
    	
    
    }
    ?>



Simple text Message
```````````````````
To send a simple text message (useful for alerts etc), you do not need
templates. Example function for the dummy Mailer controller:

Controller Class:
`````````````````

::

    <?php 
    	/**
    	 * Send a text string as email body
    	 */
    	function sendSimpleMail() {
    		$this->Email->to = 'yourlogin@localhost';
    		$this->Email->subject = 'Cake test simple email';
    		$this->Email->replyTo = 'noreply@example.com';
    		$this->Email->from = 'Cake Test Account <noreply@example.com>';
    		//Set the body of the mail as we send it.
    		//Note: the text can be an array, each element will appear as a
    		//seperate line in the message body.
    		if ( $this->Email->send('Here is the body of the email') ) {
    			$this->Session->setFlash('Simple email sent');
    		} else {
    			$this->Session->setFlash('Simple email not sent');
    		}
    		$this->redirect('/');
    	}
    ?>

This can be called from http://localhost/mailer/sendSimpleMail - if
all is OK, you should receive an email just by visiting the URL.

Pretty easy so far. Note: by default Cake EmailComponent uses the
built in php mail() function, so if you are having problems, please
ensure you can use that function successfully directly from a php
script.

The mail function used by $this->Email->send() can be set via
$this->Email->delivery to 'mail' or 'smtp', but I have only tested
'mail' (the default).


Text Message from a Template
````````````````````````````
Templates for emails are stored by default in views/element/email/text
or views/elements/email/html

Create the following template in views/elements/email/text/test.ctp:

View Template:
``````````````

::

    
    Here is the template body text.
    
    <?php echo $someValue; ?>
    

And the function for the dummy controller:

Controller Class:
`````````````````

::

    <?php 
    	/**
    	 * Use a layout for the message body
    	 * Create the following files:
    	 * views/elements/email/text/test.ctp
    	 * 
    	 * containing: the layout you want for your email
    	 *
    	 */
    	function sendTemplateMail() {
    		$this->Email->to = 'yourlogin@localhost';
    		$this->Email->subject = 'Cake test template email';
    		$this->Email->replyTo = 'noreply@example.com';
    		$this->Email->from = 'Cake Test Account <noreply@example.com>';
    		$this->Email->template = 'test';
    		//Set view variables as normal
    		$this->set('someValue', 'Cake tastes good today');
    		//Do not pass any args to send()
    		if ( $this->Email->send() ) {
    			$this->Session->setFlash('Template email sent');
    		} else {
    			$this->Session->setFlash('Template email not sent');
    		}
    		$this->redirect('/');
    		
    	}
    ?>

Nothing very new here, just set view variables as you would normally
(they are shared across all views). If you expect to have lots of
email templates (elements), then you can organize them within sub-
directories of view/elements/email/text and view/elements/email/html
then prefix 'mydir/' to the $this->Email->template value. e.g.:
$this->Email->template = 'mydir/test'.


Html Message from a template
````````````````````````````
Cake's Mail component supports 'text', 'html' or 'both' styles of
email. The default is (quite rightly) 'text' and can be changed via
$this->Email->sendAs().

Create the following template in views/elements/email/text/test2.ctp:

View Template:
``````````````

::

    
    Here is the template body text for test2.
    
    <?php echo $someValue; ?>
    

and views/elements/email/html/test2.ctp:

View Template:
``````````````

::

    
    <h2>
    Here is the template body text.
    </h2>
    <p><em><?php echo $someValue; ?></em></p>


Example function for our dummy Mailer controller:

Controller Class:
`````````````````

::

    <?php 
    	/**
    	 * Use a layout for the message body
    	 * Create the following files:
    	 * views/elements/email/html/test2.ctp
    	 * views/element/email/text/test2.ctp
    	 * 
    	 * containing: the layouts you want for your email
    	 *
    	 */
    	function sendTemplateHtmlMail() {
    		$this->Email->to = 'yourlogin@localhost';
    		$this->Email->subject = 'Cake test template email';
    		$this->Email->replyTo = 'noreply@example.com';
    		$this->Email->from = 'Cake Test Account <noreply@example.com>';
    		$this->Email->template = 'test2';
    		//Send as 'html', 'text' or 'both' (default is 'text')
    		$this->Email->sendAs = 'both';
    		//Set view variables as normal
    		$this->set('someValue', 'Cake and cream is good for you');
    		//Do not pass any args to send()
    		if ( $this->Email->send() ) {
    			$this->Session->setFlash('Template html email sent');
    		} else {
    			$this->Session->setFlash('Template html email not sent');
    		}
    //		$this->redirect('/');
    		
    	}
    
    ?>

That's all there is to it. Cake's EmailComponent is also able to
handle attachments, but that is for a later date.

The last function did not work until I had 'fixed' a few things in
email.php. See `https://trac.cakephp.org/ticket/1851`_ if you want to
play now.





.. _https://trac.cakephp.org/ticket/1851: https://trac.cakephp.org/ticket/1851
.. meta::
    :title: Brief Overview of the new EmailComponent
    :description: CakePHP Article related to 1.2,email component,Tutorials
    :keywords: 1.2,email component,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

