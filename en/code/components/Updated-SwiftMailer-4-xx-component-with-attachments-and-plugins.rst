

Updated SwiftMailer(4.xx) component with attachments and plugins
================================================================

by %s on November 07, 2009

This version of SwiftMailer component is built under cake conventions
and has ability to add attachments, plugins and SMTP under TLS or SSL.
Also if you are using utf-8 characters Outlook uses different encoding
for subject, so there is a possibility to change encoding for both:
subject and message. In this version message is wrapped like in cake's
email component etc. email element is wrapped in the email layout for
a content type used. I must mention that SwiftMailer library is under
GPL license. Thanks to Matt Huggins who was first to make this
component under SwiftMailer version 3.xx


Changes
~~~~~~~
2.30

+ Added callback support, see example for advanced email
+ replyTo list



Requirements
~~~~~~~~~~~~

+ PHP 5.2 or higher
+ openssl php extension if you use SSL or TLS
+ Limited network access to connect to remote SMTP servers



Step 1: downloading SwiftMailer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Download the latest version of SwiftMailer library from
`http://swiftmailer.org/download`_

Extract it somewhere on your machine, then copy all files from
SwiftMailer library in the lib folder to your
/app/vendors/swift_mailer/ directory

Now your vendors catalog should look like this:

::

    /vendors
    	/swift_mailer
    		/classes
    		/dependency_maps
    		mime_types.php
    		preferences.php
    		swift_init.php
    		swift_required.php

Notice: this article is created using SwiftMailer 4.05 version

Step 2: adding SwiftMailer component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now copy the following component code and create file swift_mailer.php
in your /app/controllers/components directory

Component Class:
````````````````

::

    <?php 
    // File -> app/controllers/components/swift_mailer.php
    
    /** 
     * SwiftMailer Component based on 4.05 version,
     * this component is inspired by Matt Hugins the developer of
     * SwiftMailer v.3 component based on 3.xx version.
     * 
     * @author Gediminas Morkevicius
     * @version 2.30
     * @license MIT
     * @category Components
     */
    
    //required third party library "SwiftMailer" under GPL license 
    App::import('Vendor', 'Swift', array('file' => 'swift_mailer'.DS.'swift_required.php'));
    
    class SwiftMailerComponent extends Object {
    	/**
    	 * Reference to controller
    	 * 
    	 * @var Object
    	 * @access Private
    	 */	
    	var $__controller = null;
    	/**
    	 * List of plugins to load then sending email
    	 * 
    	 * @var Array - list of plugins in pairs $pluginName/array($arg[0], $arg[...)
    	 * @access Private
    	 */	
    	var $__plugins = array();
    	/**
    	 * Email layout
    	 * 
    	 * @var String
    	 * @access Public
    	 */	
    	var $layout = 'default';
    	/**
    	 * Path to the email template
    	 * 
    	 * @var String
    	 * @access Public
    	 */	
    	var $viewPath = 'email';
    	/**
    	 * Send message as type:
    	 * 		"html" - content type "html/text"
    	 * 		"text" - content type "text/plain"
    	 * 		"both" - both content types are included 
    	 * 
    	 * @var String
    	 * @access Public
    	 */	
    	var $sendAs = 'both';
    	/**
    	 * Charset for message body
    	 * 
    	 * @var String
    	 * @access Public
    	 */	
    	var $bodyCharset = 'utf-8';
    	/**
    	 * Charset for message subject
    	 * 
    	 * @var String
    	 * @access Public
    	 */	
    	var $subjectCharset = 'utf-8';
    	/**
    	 * SMTP Security type: 
    	 * 		"ssl" - security type
    	 * 		"tls" - security type
    	 * 
    	 * @var String
    	 * @access Public
    	 */	
    	var $smtpType = null;
    	/**
    	 * SMTP Username for connection
    	 * 
    	 * @var String
    	 * @access Public
    	 */	 
    	var $smtpUsername = '';
    	/**
    	 * SMTP Password for connection
    	 * 
    	 * @var String
    	 * @access Public
    	 */	 
    	var $smtpPassword = '';
    	/**
    	 * SMTP Host name connection
    	 * 
    	 * @var String
    	 * @access Public
    	 */	 
    	var $smtpHost = '';
    	/**
    	 * SMTP port (e.g.: 25 for open, 465 for ssl, etc.)
    	 * 
    	 * @var Integer
    	 * @access Public
    	 */	 
    	var $smtpPort = 25;
    	/**
    	 * Seconds before timeout occurs
    	 * 
    	 * @var Integer
    	 * @access Public
    	 */	 
    	var $smtpTimeout = 10;
    	/**
    	 * Sendmail command (e.g.: '/usr/sbin/sendmail -bs')
    	 * 
    	 * @var String
    	 * @access Public
    	 */	 
    	var $sendmailCmd = null;
    	/**
    	 * Email from address
    	 * 
    	 * @var String
    	 * @access Public
    	 */	 
    	var $from = null;
    	/**
    	 * Email from name
    	 * 
    	 * @var String
    	 * @access Public
    	 */	 
    	var $fromName = null;
    	/**
    	 * Recipients
    	 * 
    	 * @var Mixed
    	 * 		Array - address/name pairs (e.g.: array(example@address.com => name, ...)
    	 * 		String - address to send email to
    	 * @access Public
    	 */	 
    	var $to = null;
    	/**
    	 * CC recipients
    	 * 
    	 * @var Mixed
    	 * 		Array - address/name pairs (e.g.: array(example@address.com => name, ...)
    	 * 		String - address to send email to
    	 * @access Public
    	 */	 
    	var $cc = null;
    	/**
    	 * BCC recipients
    	 * 
    	 * @var Mixed
    	 * 		Array - address/name pairs (e.g.: array(example@address.com => name, ...)
    	 * 		String - address to send email to
    	 * @access Public
    	 */	 
    	var $bcc = null;
    	/**
    	 * List of files that should be attached to the email.
    	 *
    	 * @var array - list of file paths
    	 * @access public
    	 */
    	var $attachments = array();
    	/**
    	 * When the email is opened, if the mail client supports it 
    	 * a notification will be sent to this address
    	 * 
    	 * @var String - email address for notification
    	 * @access Public
    	 */	 
    	var $readNotifyReceipt = null;
    	/** 
         * Reply to address
         * 
         * @var Mixed
    	 * 		Array - address/name pairs (e.g.: array(example@address.com => name, ...)
    	 * 		String - address to send reply to
    	 * @access Public
         */
        var $replyTo = null; 
    	/**
    	 * Max length of email line
    	 * 
    	 * @var Integer - length of line
    	 * @access Public
    	 */
    		 
    	var $maxLineLength = 78;
    	/**
    	 * Array of errors refreshed after send function is executed
    	 * 
    	 * @var Array - Error container
    	 * @access Public
    	 */
    	var $postErrors = array();
    	
    	/**
    	 * Initialize component
    	 * 
    	 * @param Object $controller reference to controller
    	 * @access Public
    	 */
    	function initialize(&$controller) {
    		$this->__controller = $controller;
    	}
    	
    	/**
    	 * Retrieves html/text or plain/text content from /app/views/elements/$this->viewPath/$type/$template.ctp
    	 * and wraps it in layout /app/views/layouts/$this->viewPath/$type/$this->layout.ctp
    	 * 
    	 * @param String $template - name of the template for content
    	 * @param String $type - content type:
    	 * 		html - html/text
    	 * 		text - plain/text
    	 * @return String content from template wraped in layout
    	 * @access Protected
    	 */
    	function _emailBodyPart($template, $type = 'html') {
    		$viewClass = $this->__controller->view;
    
    		if ($viewClass != 'View') {
    			if (strpos($viewClass, '.') !== false) {
    				list($plugin, $viewClass) = explode('.', $viewClass);
    			}
    			$viewClass = $viewClass . 'View';
    			App::import('View', $this->__controller->view);
    		}
    		$View = new $viewClass($this->__controller, false);
    		$View->layout = $this->layout;
    		
    		$content = $View->element($this->viewPath.DS.$type.DS.$template, array('content' => ""), true);
    		$View->layoutPath = $this->viewPath.DS.$type;
    		$content = $View->renderLayout($content);
    		
    		// Run content check callback
    		$this->__runCallback($content, 'checkContent');
    		
    		return $content;
    	}
    	
    	/**
    	 * Sends Email depending on parameters specified, using method $method,
    	 * mail template $view and subject $subject
    	 * 
    	 * @param String $view - template for mail content
    	 * @param String $subject - email message subject
    	 * @param String $method - email message sending method, possible values are:
    	 * 		"smtp" - Simple Mail Transfer Protocol method
    	 * 		"sendmail" - Sendmail method http://www.sendmail.org/
    	 * 		"native" - Native PHP mail method
    	 * @return Integer - number of emails sent
    	 * @access Public
    	 */
    	function send($view = 'default', $subject = '', $method = 'smtp') {
    		// Check subject charset, asuming we are by default using "utf-8"
    		if (strtolower($this->subjectCharset) != 'utf-8') {
    			if (function_exists('mb_convert_encoding')) {
    				//outlook uses subject in diferent encoding, this is the case to change it
    				$subject = mb_convert_encoding($subject, $this->subjectCharset, 'utf-8');
    			}
    		}
    		// Check if swift mailer is imported
    		if (!class_exists('Swift_Message')) {
    			throw new Exception('SwiftMailer was not included, check the path and filename');
    		}
    		
    		// Create message
    		$message = Swift_Message::newInstance($subject);
    		
    		// Run Init Callback
    		$this->__runCallback($message, 'initializeMessage');
    		
    		$message->setCharset($this->subjectCharset);
    		
    		// Add html text
    		if ($this->sendAs == 'both' || $this->sendAs == 'html') {
    			$html_part = $this->_emailBodyPart($view, 'html');
    			$message->addPart($html_part, 'text/html', $this->bodyCharset);
    			unset($html_part);
    		}
    		
    		// Add plain text or an alternative
    		if ($this->sendAs == 'both' || $this->sendAs == 'text') {
    			$text_part = $this->_emailBodyPart($view, 'text');
    			$message->addPart($text_part, 'text/plain', $this->bodyCharset);
    			unset($text_part);
    		}
    		
    		// Add attachments if any
    		if (!empty($this->attachments)) {
    			foreach($this->attachments as $attachment) {
    				if (!file_exists($attachment)) {
    					continue;
    				}
    				$message->attach(Swift_Attachment::fromPath($attachment));
    			}
    		}
    		
    		// On read notification if supported
    		if (!empty($this->readNotifyReceipt)) {
    			$message->setReadReceiptTo($this->readNotifyReceipt);
    		}
    		
    		$message->setMaxLineLength($this->maxLineLength);
    		
    		// Set the FROM address/name.
    		$message->setFrom($this->from, $this->fromName);
    		// Add all TO recipients.
    		if (!empty($this->to)) {
    			if (is_array($this->to)) {
    				foreach($this->to as $address => $name) {
    					$message->addTo($address, $name);
    				}
    			} 
    			else {
    				$message->addTo($this->to);
    			}
    		}
    		
    		// Add all CC recipients.
    		if (!empty($this->cc)) {
    			if (is_array($this->cc)) {
    				foreach($this->cc as $address => $name) {
    					$message->addCc($address, $name);
    				}
    			} 
    			else {
    				$message->addCc($this->cc);
    			}
    		}
    		
    		// Add all BCC recipients.
    		if (!empty($this->bcc)) {
    			if (is_array($this->bcc)) {
    				foreach($this->bcc as $address => $name) {
    					$message->addBcc($address, $name);
    				}
    			} 
    			else {
    				$message->addBcc($this->bcc);
    			}
    		}
    
    		// Set REPLY TO addresses
            if (!empty($this->replyTo)) {
            	if (is_array($this->replyTo)) {
    				foreach($this->replyTo as $address => $name) {
    					$message->addReplyTo($address, $name);
    				}
    			} 
    			else {
    				$message->addReplyTo($this->replyTo);
    			}
            } 
    		
    		// Initializing mail method object with sending parameters
    		$transport = null;
    		switch ($method) {
    			case 'smtp':
    				$transport = Swift_SmtpTransport::newInstance($this->smtpHost, $this->smtpPort, $this->smtpType);
    				$transport->setTimeout($this->smtpTimeout);
    				if (!empty($this->smtpUsername)) {
    					$transport->setUsername($this->smtpUsername);
    					$transport->setPassword($this->smtpPassword);
    				}
    				break;
    			case 'sendmail':
    				$transport = Swift_SendmailTransport::newInstance($this->sendmailCmd);
    				break;
    			case 'native': default:
    				$transport = Swift_MailTransport::newInstance();
    				break;
    		}
    		
    		// Initialize Mailer
    		$mailer = Swift_Mailer::newInstance($transport);
    		
    		// Load plugins if any
    		if (!empty($this->__plugins)) {
    			foreach($this->__plugins as $name => $args) {
    				$plugin_class = "Swift_Plugins_{$name}";
    				if (!class_exists($plugin_class)) {
    					throw new Exception("SwiftMailer library does not support this plugin: {$plugin_class}");
    				}
    				
    				$plugin = null;
    				switch(count($args)) {
    					case 1:
    						$plugin = new $plugin_class($args[0]);
    						break;
    					case 2:
    						$plugin = new $plugin_class($args[0], $args[1]);
    						break;
    					case 3:
    						$plugin = new $plugin_class($args[0], $args[1], $args[2]);
    						break;
    					case 4:
    						$plugin = new $plugin_class($args[0], $args[1], $args[2], $args[3]);
    						break;
    					default:
    						throw new Exception('SwiftMailer component plugin can register maximum of 4 arguments');
    				}
    				$mailer->registerPlugin($plugin);
    			}
    		}
    		// Run Send Callback
    		$this->__runCallback($message, 'beforeSend');
    		
    		// Attempt to send the email.
    		return $mailer->send($message, $this->postErrors);
    	}
    	
    	/**
    	 * Registers a plugin supported by SwiftMailer
    	 * function parameters are limited to 5
    	 * first argument is plugin name (e.g.: if SwiftMailer plugin class is named "Swift_Plugins_AntiFloodPlugin",
    	 * so you should pass name like "AntiFloodPlugin")
    	 * All other Mixed arguments included in plugin creation call
    	 * 
    	 * @return Integer 1 on success 0 on failure
    	 */
    	function registerPlugin() {
    		if (func_num_args()) {
    			$args = func_get_args();
    			$this->__plugins[array_shift($args)] = $args;
    			return true;
    		}
    		return false;
    	}
    	
    	/**
    	 * Run a specific by $type callback on controller
    	 * who`s action is being executed. This functionality
    	 * is used to perform additional specific methods
    	 * if any is required
    	 * 
    	 * @param mixed $object - object callback being executed on
    	 * @param string $type - type of callback to run
    	 * @return void
    	 */
    	function __runCallback(&$object, $type) {
    		$call = '__'.$type.'On'.Inflector::camelize($this->__controller->action);
    		if (method_exists($this->__controller, $call)) {
    			$this->__controller->{$call}($object);
    		}
    	}
    }
    ?>


Step 3: preparing our controller and email templates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First we need a default layout for our emails - both for text and html
. It should be located in /app/views/layouts/email/ directory. If it
does not exist create it. You should have a tree similar to this:

::

    /layouts
    	/email
    		/text
    		/html
    	/xml
    	default.ctp
    	ajax.ctp

Now in those /email/html/ and /email/text/ folders create a
default.ctp file, which will wrap an email content. In this tutorial I
will use only html template. So the layout for it can look like this:

View Template:
``````````````

::

    <!-- File: /app/views/layouts/email/html/default.ctp -->
    
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
    <html>
    <head>
    	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    	<title><?php echo $title_for_layout;?></title>
    </head>
    <body>
    	<?php echo $content_for_layout;?>
    </body>
    </html>

Now then we have a layout for an email we also need a template for a
specific content of this email. We will place these specific templates
in the /app/views/elements/email/ folder using same structure like in
layouts. We will name our first specific template like im_excited.ctp
. And the tree structure for /views/elements/ should look like:

::

    /views
    	/elements
    		/email
    			/html
    				im_excited.ctp
    			/text
    		other_stuff.ctp

And the example of email element template view:

View Template:
``````````````

::

    <!-- File: /app/views/elements/email/html/im_excited.ctp -->
    
    <p><b>Exciting isn't it?</b></p>
    
    <p><?php echo $message?></p>


You can also use a CakePHP manual about email component then setting
up email layout and templates

Example 1: Your first awesome email
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now we need to tell our controller to use this component, lets say we
have employees controller and we think sending a notification later
on, in this example we will send simple email through gmail smtp tls

Controller Class:
`````````````````

::

    <?php // File -> app/controllers/employees_controller.php
    
    class EmployeesController extends AppController {
        var $name = 'Employees';
        var $components = array('SwiftMailer');
    	
        function mail() {  	
        	$this->SwiftMailer->smtpType = 'tls';
    		$this->SwiftMailer->smtpHost = 'smtp.gmail.com';
    		$this->SwiftMailer->smtpPort = 465;
    		$this->SwiftMailer->smtpUsername = 'my_email@gmail.com';
    		$this->SwiftMailer->smtpPassword = 'hard_to_guess';
    
    		$this->SwiftMailer->sendAs = 'html';
    		$this->SwiftMailer->from = 'noone@x.com';
    		$this->SwiftMailer->fromName = 'New bakery component';
    		$this->SwiftMailer->to = 'my_email@gmail.com';
    		//set variables to template as usual
    		$this->set('message', 'My message');
    		
    		try {
    			if(!$this->SwiftMailer->send('im_excited', 'My subject')) {
    				$this->log("Error sending email");
    			}
    		}
    		catch(Exception $e) {
      			$this->log("Failed to send email: ".$e->getMessage());
    		}
    		$this->redirect($this->referer(), null, true);
        }
    }
    ?>


Example 2: Advanced Email
~~~~~~~~~~~~~~~~~~~~~~~~~

This example will show how to include attachments and plugins into
your email. Also there are 3 callback methods added, notice that for
example callback: __initializeMessageOn Mail the bolded part of the
callback is camel cased email sending function. If your function which
sends email would be named like report_bad_employee then the callback
method should look like __initializeMessageOnReportBadEmployee

Available callbacks are:

+ __initializeMessageOn Method - executed right after the SwiftMailer
  message is created
+ __checkContentOn Method - executed after a message body content is
  formed
+ __beforeSendOn Method - executed right before the email is sent



Controller Class:
`````````````````

::

    <?php // File -> app/controllers/employees_controller.php
    
    class EmployeesController extends AppController {
        var $name = 'Employees';
        var $components = array('SwiftMailer');
    
    	function __initializeMessageOnMail(&$messageInstance) {
    		//Indicate "High" priority
    		$messageInstance->setPriority(2);
    	}
    	
    	function __beforeSendOnMail(&$messageInstance) {
    		//set the bad email bounce address
    		$messageInstance->setReturnPath('bad-email-bounce-to@address.com');
    	}
    	
    	function __checkContentOnMail(&$content) {
    		//strip html tags (just 4 fun :)
    		//this should be used for example to check with regexp for unwanted content
    		$content = strip_tags($content);
    	}
    	
    	function mail() { 	
    		$this->SwiftMailer->smtpType = 'tls';
    		$this->SwiftMailer->smtpHost = 'smtp.gmail.com';
    		$this->SwiftMailer->smtpPort = 465;
    		$this->SwiftMailer->smtpUsername = 'my_email@gmail.com';
    		$this->SwiftMailer->smtpPassword = 'hard_to_guess';
    
    		$this->SwiftMailer->sendAs = 'html';
    		$this->SwiftMailer->from = 'noone@x.com';
    		$this->SwiftMailer->fromName = 'New bakery component';
    		$this->SwiftMailer->to = 'my_email@gmail.com';
    		
    		//notify then receiver reads an email
    		$this->SwiftMailer->readNotifyReceipt = 'my_email@gmail.com';
    		
    		//some attachments
    		$this->SwiftMailer->attachments = array(
    			'C:\pictures\new.jpeg',
    			'C:\schema_net.vsd'
    		);
    		
    		//add reply to
    		$this->SwiftMailer->replyTo = array('test@gmail.com', 'test@gg.com');
    		//register logger plugin
    		$this->SwiftMailer->registerPlugin('LoggerPlugin', new Swift_Plugins_Loggers_EchoLogger());
    		//set variables to template as usual
    		$this->set('message', 'My message');
    		
    		try {
    			if(!$this->SwiftMailer->send('im_excited', 'My subject')) {
    				foreach($this->SwiftMailer->postErrors as $failed_send_to) {
    					$this->log("Failed to send email to: $failed_send_to");
    				}
    			}
    		}
    		catch(Exception $e) {
      			$this->log("Failed to send email: ".$e->getMessage());
    		}
    		$this->autoRender = false;
        }
    }
    ?>


Example 3: Sending Email through shell
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This example will show how to use this component with a shell.

First we need to create a task for our shell which will initiate this
component. It should be located in app/vendors/shells/tasks/
directory, and we name file as swift_mailer.php

::

    <?php // File -> app/vendors/shells/tasks/swift_mailer.php
    
    App::import('Core', 'Controller');
    App::import('Component', 'SwiftMailer');
    
    class SwiftMailerTask extends Shell {
    	/**
    	 * Instance of controller to handle email views
    	 * 
    	 * @var Object
    	 * @access Private
    	 */
        var $__controller = null;
        /**
         * Instance of SwiftMailer component
         * 
         * @var Object
         * @access Public
         */
        var $instance = null;
    
        /**
         * Initializes this task
         * 
         * @access Public
         */
        function initialize() {
            $this->__controller = new Controller();
            $this->instance = new SwiftMailerComponent(null);
            $this->instance->initialize($this->__controller);
        }
    
        /**
         * Pass parameter to the email view as usual
         * 
         * @param String $name - parameter name
         * @param Mixed $data - mixed parameter
         * @return void
         * @access public
         */
        function set($name, $data) {
            $this->__controller->set($name, $data);
        }
    }
    ?>


And here is a shell which will execute a specified commands for email
sending. Shell should be located in app/vendors/shells/ directory, and
we name file as mailer.php

::

    <?php // File -> app/vendors/shells/mailer.php
    
    class MailerShell extends Shell {
    	var $tasks = array('SwiftMailer');
    	
    	function mail() {
    		$this->out("Executing Mail command");
    		$this->SwiftMailer->instance->smtpType = 'tls';
    		$this->SwiftMailer->instance->smtpHost = 'smtp.gmail.com';
    		$this->SwiftMailer->instance->smtpPort = 465;
    		$this->SwiftMailer->instance->smtpUsername = 'my_email@gmail.com';
    		$this->SwiftMailer->instance->smtpPassword = 'hard_to_guess';
    
    		$this->SwiftMailer->instance->sendAs = 'html';
    		$this->SwiftMailer->instance->from = 'my_email@gmail.com';
    		$this->SwiftMailer->instance->fromName = 'TEST';
    		$this->SwiftMailer->instance->to = array(
    			'my_email@gmail.com' => 'recepient 1',
    			'receiver@bad-domain.org' => 'recepient 2'
    		);
    		
    		$this->SwiftMailer->set('message', 'Smack my mailer shell');
    		$this->SwiftMailer->instance->registerPlugin('LoggerPlugin', new Swift_Plugins_Loggers_EchoLogger()); 
    		
    		try {
    			if(!$this->SwiftMailer->instance->send('im_excited', 'My subject')) {
    				foreach($this->SwiftMailer->instance->postErrors as $failed_send_to) {
    					$this->log("Failed to send email to: $failed_send_to");
    					$this->out("Failed to send email to: $failed_send_to");
    				}
    			}
    		}
    		catch(Exception $e) {
      			$this->log("Failed to send email: ".$e->getMessage());
      			$this->out("Failed to send email: ".$e->getMessage());
    		}
    		$this->out("Finished Mail command");	
    	}
    }
    ?>


To execute a SwiftMailer shell open your command prompt or shell and
go to your app directory and type cake mailer mail according to this
example. If you have any questions about shell read
`http://book.cakephp.org/view/108/The-CakePHP-Console`_ first

Thats it, any ideas on functionality improvements are very welcome


.. _http://book.cakephp.org/view/108/The-CakePHP-Console: http://book.cakephp.org/view/108/The-CakePHP-Console
.. _http://swiftmailer.org/download: http://swiftmailer.org/download
.. meta::
    :title: Updated SwiftMailer(4.xx) component with attachments and plugins
    :description: CakePHP Article related to Mail,email,component,ssl,smtp,plugins,swift mailer,tls,email shell,sky leppard,attachments,Components
    :keywords: Mail,email,component,ssl,smtp,plugins,swift mailer,tls,email shell,sky leppard,attachments,Components
    :copyright: Copyright 2009 
    :category: components

