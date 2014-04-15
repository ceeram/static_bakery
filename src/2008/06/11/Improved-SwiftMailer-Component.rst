Improved SwiftMailer Component
==============================

by mhuggins on June 11, 2008

I've used the [url=http://bakery.cakephp.org/articles/view/sending-
email-with-phpmailer]PHPMailer component[/url] previously, and it's
useful for basic SMTP usage. However, I recently needed to make an
SMTP connection requiring TLS authentication, which PHPMailer does not
provide. To resolve this, I decided to switch to SwiftMailer.
Unfortunately, I wasn't very satisfied with the ease of use of the
existing [url=http://bakery.cakephp.org/articles/view/swiftmailer-
component]SwiftMailer component[/url] I found on the Bakery. As such,
I created my own component that is more similar to the original
PHPMailer component I was using. It doesn't have attachments
implemented, but it's handy for sending HTML/plaintext emails.


1. Download SwiftMailer
~~~~~~~~~~~~~~~~~~~~~~~

First, download `SwiftMailer`_, and place it under
vendors/SwiftMailer.


2. Create SwiftMailer Component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To easily take advantage of SwiftMailer, you'll need to create a
component that does all the work for you. Create a file named
swift_mailer.php under app/controllers/components. Paste the code
below into the file, and save it.


Component Class:
````````````````

::

    <?php 
    /*
     * SwiftMailer 3 Component
     * @author Matt Huggins
     * @version 1.0
     * @license MIT
     */
    
    App::import('Vendor', 'Swift', array('file' => 'SwiftMailer'.DS.'Swift.php'));
    
    class SwiftMailerComponent extends Object {
    	var $controller = false;
    	
    	var $layout        = 'email';
    	var $viewPath      = 'email';
    	
    	var $smtpType      = 'open';       // open, ssl, tls
    	var $smtpUsername  = '';
    	var $smtpPassword  = '';
    	var $smtpHost      = '';           // specify host or leave blank to auto-detect
    	var $smtpPort      = null;         // null to auto-detect, otherwise specify (e.g.: 25 for open, 465 for ssl, etc.)
    	var $smtpTimeout   = 10;           // seconds before timeout occurs
    	
    	var $sendmailCmd   = null;         // null to auto-detect, otherwise manually defined (e.g.: '/usr/sbin/sendmail -bs')
    	
    	var $from          = null;
    	var $fromName      = null;
    	var $to            = null;         // Each of $to, $cc, and $bcc should all be formatted as an array of
    	var $cc            = null;         // key => value pairs that represent email address/name. e.g.:
    	var $bcc           = null;         //   array('bob@google.com'=>'Bob Smith', 'joe@yahoo.com'=>'Joe Shmoe')
    	
    	
    	function startup(&$controller) {
    		$this->controller =& $controller;
    	}
    	
    	
    	function _connect($method) { // smtp, sendmail, native
    		// Create the appropriate Swift mailer object based upon the connection type.
    		switch ($method) {
    			case 'smtp':
    				return $this->_connectSMTP();
    			case 'sendmail':
    				return $this->_connectSendmail();
    			case 'native': default:
    				return $this->_connectNative();
    		}
    	}
    	
    	
    	function _connectNative() {
    		App::import('Vendor', 'Swift_Connection_NativeMail', array('file' => 'SwiftMailer'.DS.'Swift'.DS.'Connection'.DS.'NativeMail.php'));
    		
    		// Return the swift mailer object.
    		return new Swift(new Swift_Connection_NativeMail());
    	}
    	
    	
    	function _connectSendmail() {
    		App::import('Vendor', 'Swift_Connection_Sendmail', array('file' => 'SwiftMailer'.DS.'Swift'.DS.'Connection'.DS.'Sendmail.php'));
    		
    		// Auto-detect the sendmail command to use if not specified.
    		if (empty($this->sendmailCmd)) {
    			$this->sendmailCmd = Swift_Connection_Sendmail::AUTO_DETECT;
    		}
    		
    		// Return the swift mailer object.
    		return new Swift(new Swift_Connection_Sendmail($this->sendmailCmd));
    	}
    	
    	
    	function _connectSMTP() {
    		App::import('Vendor', 'Swift_Connection_SMTP', array('file' => 'SwiftMailer'.DS.'Swift'.DS.'Connection'.DS.'SMTP.php'));
    		
    		// Detect SMTP host if not provided.
    		if (empty($this->smtpHost)) {
    			$this->smtpHost = Swift_Connection_SMTP::AUTO_DETECT;
    		}
    		
    		// Detect SMTP port if not provided.
    		if (empty($this->smtpPort)) {
    			$this->smtpPort = Swift_Connection_SMTP::AUTO_DETECT;
    		}
    		
    		// Determine what type of connection to use (open, ssl, tls).
    		switch ($this->smtpType) {
    			case 'ssl':
    				$smtpType = Swift_Connection_SMTP::ENC_SSL; break;
    			case 'tls':
    				$smtpType = Swift_Connection_SMTP::ENC_TLS; break;
    			case 'open': default:
    				$smtpType = Swift_Connection_SMTP::ENC_OFF;
    				
    		}
    		
    		// Create the swift mailer object, and prepare authentication if required.
    		$smtp =& new Swift_Connection_SMTP($this->smtpHost, $this->smtpPort, $smtpType);
    		$smtp->setTimeout($this->smtpTimeout);
    		
    		if (!empty($this->smtpUsername)) {
    			$smtp->setUsername($this->smtpUsername);
    			$smtp->setPassword($this->smtpPassword);
    		}
    		
    		// Return the swift mailer object.
    		return new Swift($smtp);
    	}
    	
    	
    	function _getBodyText($view) {
    		// Temporarily store vital variables used by the controller.
    		$tmpLayout = $this->controller->layout;
    		$tmpAction = $this->controller->action;
    		$tmpOutput = $this->controller->output;
    		$tmpRender = $this->controller->autoRender;
    		
    		// Render the plaintext email body.
    		ob_start();
    		$this->controller->output = null;
    		$body = $this->controller->render($this->viewPath . DS . $view . '_text', $this->layout . '_text');
    		ob_end_clean();
    		
    		// Restore the layout, view, output, and autoRender values to the controller.
    		$this->controller->layout = $tmpLayout;
    		$this->controller->action = $tmpAction;
    		$this->controller->output = $tmpOutput;
    		$this->controller->autoRender = $tmpRender;
    		
    		return $body;
    	}
    	
    	
    	function _getBodyHTML($view) {
    		// Temporarily store vital variables used by the controller.
    		$tmpLayout = $this->controller->layout;
    		$tmpAction = $this->controller->action;
    		$tmpOutput = $this->controller->output;
    		$tmpRender = $this->controller->autoRender;
    		
    		// Render the HTML email body.
    		ob_start();
    		$this->controller->output = null;
    		$body = $this->controller->render($this->viewPath . DS . $view . '_html', $this->layout . '_html');
    		ob_end_clean();
    		
    		// Restore the layout, view, output, and autoRender values to the controller.
    		$this->controller->layout = $tmpLayout;
    		$this->controller->action = $tmpAction;
    		$this->controller->output = $tmpOutput;
    		$this->controller->autoRender = $tmpRender;
    		
    		return $body;
    	}
    	
    	
    	function send($view = 'default', $subject = '', $method = 'smtp') {
    		// Create the message, and set the message subject.
    		$message =& new Swift_Message($subject);
    		
    		// Append the HTML and plain text bodies.
    		$bodyHTML = $this->_getBodyHTML($view);
    		$bodyText = $this->_getBodyText($view);
    		
    		$message->attach(new Swift_Message_Part($bodyHTML, "text/html"));
    		$message->attach(new Swift_Message_Part($bodyText, "text/plain"));
    		
    		// Set the from address/name.
    		$from =& new Swift_Address($this->from, $this->fromName);
    		
    		// Create the recipient list.
    		$recipients =& new Swift_RecipientList();
    		
    		// Add all TO recipients.
    		if (!empty($this->to)) {
    			if (is_array($this->to)) {
    				foreach($this->to as $address => $name) {
    					$recipients->addTo($address, $name);
    				}
    			} else {
    				$recipients->addTo($this->to, $this->to);
    			}
    		}
    		
    		// Add all CC recipients.
    		if (!empty($this->cc)) {
    			if (is_array($this->cc)) {
    				foreach($this->cc as $address => $name) {
    					$recipients->addCc($address, $name);
    				}
    			} else {
    				$recipients->addCc($this->cc, $this->cc);
    			}
    		}
    		
    		// Add all BCC recipients.
    		if (!empty($this->bcc)) {
    			if (is_array($this->bcc)) {
    				foreach($this->bcc as $address => $name) {
    					$recipients->addBcc($address, $name);
    				}
    			} else {
    				$recipients->addBcc($this->bcc, $this->bcc);
    			}
    		}
    		
    		// Attempt to send the email.
    		$mailer =& $this->_connect($method);
    		$result = $mailer->send($message, $recipients, $from);
    		$mailer->disconnect();
    		
    		return $result;
    	}
    }
    ?>



3. Create Email Layouts
~~~~~~~~~~~~~~~~~~~~~~~

You will need to create two views for this component to work properly.
By default, the layouts used for emails are "email_html.ctp" and
"email_text.ctp".

Anything you include in "email_html.ctp" will be used as the layout
for HTML content, and anything you include in "email_text.ctp" will be
used as the layout for text content.

IMPORTANT: Make sure you include within each layout wherever the
content should be rendered.


4. Create Email Views
~~~~~~~~~~~~~~~~~~~~~

Similar to the layouts created in the previous step, two views must be
created for each action where you wish to send an email. By default,
these layouts must be placed within an "email" subdirectory within the
current action's view path.

For example, if you allow users to register new accounts within
users_controller.php, and you intend to send an email to each new user
when an account is created, then you'd create files
"register_html.ctp" and "register_text.ctp" within
app/views/users/email.

Similar to standard views, variables can be placed within views.
Sticking with the previous example, you might include the new username
within the email by doing something like the following.



View Template:
``````````````

::

    
    Thanks for joining My Awesome Site, <?php echo $username;?>!

Any variables included within your view can be passed by the
controller's set() method as usual.


5. Prepare the Controller
~~~~~~~~~~~~~~~~~~~~~~~~~

Whichever controllers you're planning to use SwiftMailer in will need
to be updated to reference the component. Within your controller's
$components array, you'll need to add 'SwiftMailer'.


Controller Class:
`````````````````

::

    <?php 
    class MyController extends AppController {
    	var $components= array('SwiftMailer');
    	// ...other class code here...
    }
    ?>



6. Sending Emails
~~~~~~~~~~~~~~~~~

Now that your controller, views, and layouts are ready, you can send
emails. The following is a basic example of the code you'll use to
accomplish this.


Controller Class:
`````````````````

::

    <?php 
    // Default to localhost port 25, no user authentication.
    $this->SwiftMailer->from         = 'webmaster@mysite.com';
    $this->SwiftMailer->fromName     = 'Webmaster';
    $this->SwiftMailer->to           = $this->data['Member']['email'];
    
    $this->set(array(
    	'username' => $this->data['User']['username'],
    	'password' => $this->data['User']['password'],
    ));
    
    if (!$this->SwiftMailer->send('register', 'Thanks for Registering!')) {
    	$this->log('Error sending email "register".', LOG_ERROR);
    }
    ?>

Below is an example of connecting to Gmail, which requires TLS
authentication, a username, and password.


Controller Class:
`````````````````

::

    <?php 
    $this->SwiftMailer->smtpType     = 'tls';
    $this->SwiftMailer->smtpHost     = 'smtp.gmail.com';
    $this->SwiftMailer->smtpPort     = 465;
    $this->SwiftMailer->smtpUsername = 'my_username';
    $this->SwiftMailer->smtpPassword = 'my_password';
    $this->SwiftMailer->from         = 'my_username@gmail.com';
    $this->SwiftMailer->fromName     = 'My Name';
    $this->SwiftMailer->to           = $this->data['Member']['email'];
    
    $this->set(array(
    	'username' => $this->data['User']['username'],
    	'password' => $this->data['User']['password'],
    ));
    
    if (!$this->SwiftMailer->send('register', 'Thanks for Registering!')) {
    	$this->log('Error sending email "register".', LOG_ERROR);
    }
    ?>



7. Additional Notes
~~~~~~~~~~~~~~~~~~~


7.1. Connection Types
`````````````````````

By default, the SwiftMailer component will use an open (plaintext)
connection. Additional options include SSL and TLS. To use these,
simply set the value of smtpType.


Controller Class:
`````````````````

::

    <?php 
    $this->SwiftMailer->smtpType = 'open'; // use plaintext
    $this->SwiftMailer->smtpType = 'ssl';  // use SSL
    $this->SwiftMailer->smtpType = 'tls';  // use TLS
    ?>



7.2. Non-SMTP Alternatives
``````````````````````````

Sending can also be performed in ways other than SMTP. An optional
third parameter included in the send() method allows for sendmail and
native approaches to be used instead of the default.


Controller Class:
`````````````````

::

    <?php 
    $this->SwiftMailer->send('register', 'Thanks for Registering!', 'sendmail');  // use sendmail
    $this->SwiftMailer->send('register', 'Thanks for Registering!', 'native');    // use native
    ?>



7.3. Sending to Multiple Recipients
```````````````````````````````````

Instead of passing a single email address to SwiftMailer, you can
provide an array of key/value pairs representing the address/name
respectively. For example:


Controller Class:
`````````````````

::

    <?php 
    $this->SwiftMailer->to = array(
    	'bob@gmail.com' => 'Bob Smith',
    	'joe@yahoo.com' => 'Joe Schmoe',
    );
    ?>



7.4. Carbon Copying and Blind Carbon Copying
````````````````````````````````````````````

Similarly, single email addresses or arrays of address/name
combinations can be provided to SwiftMailer for the sake of carbon
copying. For Example:


Controller Class:
`````````````````

::

    <?php 
    $this->SwiftMailer->cc  = 'rick@msn.com';
    $this->SwiftMailer->bcc = array(
    	'bob@gmail.com' => 'Bob Smith',
    	'joe@yahoo.com' => 'Joe Schmoe',
    );
    ?>



.. _SwiftMailer: http://www.swiftmailer.org/

.. author:: mhuggins
.. categories:: articles, components
.. tags:: swift mailer,Components

