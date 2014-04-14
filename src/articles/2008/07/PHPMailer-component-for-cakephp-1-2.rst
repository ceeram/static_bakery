PHPMailer component for cakephp 1.2
===================================

by rapsys on July 21, 2008

After trying the two mailer component unsucessfull, I rewrite my own
to makes it works with cake 1.2. Previous compnents:
http://bakery.cakephp.org/articles/view/sending-email-with-phpmailer
http://bakery.cakephp.org/articles/view/phpmailer-with-native-api-for-
php-5-x You will need: - Phpmailer CakePHP component - phpmailer
vendor files - layout for email - view for plain text email body -
view for HTML email body - controller's function where the phpmailer
component will be used
- Phpmailer cakephp component


Component Class:
````````````````

::

    <?php 
    class PhpmailerComponent
    {
    	/**
    	 * Send email using sendmail by default.
    	 */
    	var $from = 'contact@example.com';
    	var $fromName = 'Contact Example';
    
    	// PHPMailer mailer method
    	var $mailer = 'sendmail';
    	// SMTP username
    	var $smtpUsername = '';
    	// SMTP password
    	var $smtpPassword = '';
    	// specify main and backup server
    	var $smtpHost= '';
    
    	var $text_body = null;
    	var $html_body = null;
    	var $to = null;
    	var $toName = null;
    	var $subject = null;
    	var $cc = null;
    	var $bcc = null;
    	var $template = 'phpmailer/default';
    	var $files = array();
    	var $strings = array();
    
    	var $controller;
    
    	function startup( &$controller ) {
    		$this->controller = &$controller;
    	}
    
    	/**
    	 * This is the body in plain text for non-HTML mail clients
    	 */
    	function bodyText() {
    		$temp_layout = $this->controller->layout;
    		$mail = $this->controller->render('mail', 'phpmailer'.DS.'default', $this->template . '_text');
    		$this->controller->layout = $temp_layout;
    		$this->controller->output = '';
    		return $mail;
    	}
    
    	/**
    	 * This is HTML body text for HTML-enabled mail clients
    	 */
    	function bodyHTML() {
    		$temp_layout = $this->controller->layout;
    		$mail = $this->controller->render('mail', 'phpmailer'.DS.'default', $this->template . '_html');
    		$this->controller->layout = $temp_layout;
    		$this->controller->output = '';
    		return $mail;
    	}
    
    	function attachFile($filename, $name = '', $encoding = 'base64', $type = 'application/octet-stream') {
    		$count = count($this->files);
    		$this->files[$count+1]['filename']	= $filename;
    		$this->files[$count+1]['name']		= $name;
    		$this->files[$count+1]['encoding']	= $encoding;
    		$this->files[$count+1]['type']		= $type;
    	}
    
    	function attachString($string, $name, $encoding = 'base64', $type = 'application/octet-stream') {
    		$count = count($this->strings);
    		$this->strings[$count+1]['string']		= $string;
    		$this->strings[$count+1]['name']		= $name;
    		$this->strings[$count+1]['encoding']	= $encoding;
    		$this->strings[$count+1]['type']		= $type;
    	}
    
    	function send()
    	{
    		//vendor('phpmailer'.DS.'class.phpmailer');
    		App::import('Vendor', 'PHPMailer', array('file'=>'phpmailer'.DS.'class.phpmailer.php'));
    
    		$mail = new PHPMailer();
    
    		if ($this->mailer == 'smtp') {
    			// Set mailer to use SMTP
    			$mail->IsSMTP();
    			// turn on SMTP authentication
    			$mail->SMTPAuth	= true;
    			$mail->Host		= $this->smtpHost;
    			$mail->Username	= $this->smtpUsername;
    			$mail->Password	= $this->smtpPassword;
    		}
    
    		$mail->From		= $this->from;
    		$mail->FromName	= $this->fromName;
    		$mail->AddAddress($this->to, $this->toName);
    		$mail->AddReplyTo($this->from, $this->fromName);
    
    		$mail->CharSet	= 'UTF-8';
    		// set word wrap to 50 characters
    		$mail->WordWrap = 50;
    
    		if (!empty($this->files)) {
    			foreach ($this->files as $attachment) {
    				$mail->AddAttachment($attachment['filename'], $attachment['name'], $attachment['encoding'], $attachment['type']);
    			}
    		}
    
    		if (!empty($this->strings)) {
    			foreach ($this->strings as $attachment) {
    				var_dump($attachment);
    				$mail->AddStringAttachment($attachment['string'], $attachment['name'], $attachment['encoding'], $attachment['type']);
    			}
    		}
    
    		// set email format to HTML
    		$mail->IsHTML(true);
    
    		$mail->Subject = $this->subject;
    		$mail->Body	= $this->bodyHTML();
    		$mail->AltBody = $this->bodyText();
    
    		$result = $mail->Send();
    
    		if($result == false)
    			$result = $mail->ErrorInfo;
    
    		return $result;
    	}
    }
    ?>
    ?>


- Get PHPMailer

Get PHPMailer 2.0.0 rc1 at least from
`http://phpmailer.sourceforge.net/`_ Unpack it into
app/vendors/phpmailer/ to have
app/vendors/phpmailer/class.phpmailer.php,
app/vendors/phpmailer/class.pop3.php and
app/vendors/phpmailer/class.pop3.php

- Create layout

Create the phpmailer layout in app/views/layouts/phpmailer/default.ctp

View Template:
``````````````

::

    
    <?php echo $content_for_layout; ?>


- Create views

Create app/views/controllers/phpmailer/default_html.ctp view with the
following code.

View Template:
``````````````

::

    
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
    <head>
    <title><?php echo $subject; ?></title>
    </head>
    <body>
    <p>
    <?php echo nl2br($body); ?>
    </p>
    </body>
    </html>

Do it again with app/views/controllers/phpmailer/default_text.ctp with
this code.

View Template:
``````````````

::

    
    <?php echo strip_tags($body)."\n"; ?>


- Controller's function where the phpmailer component will be used


Controller Class:
`````````````````

::

    <?php 
    class ControllersController extends AppController {
    	var $name = 'Controllers';
    	var $components = array('Phpmailer');
    
    	function send() {
    		//Backup previous layout
    		$temp_layout = $this->layout;
    		//Render the file
    		$pdf = $this->render('view','pdf'.DS.'default','pdf'.DS.'view');
    		//Revert layout to previous
    		$this->layout = $temp_layout;
    		//Empty the output
    		//XXX: It's needed to reset the output else the next render will have this one prepended
    		$this->output = '';
    
    		//Attach the pdf as string attachment
    		$this->Phpmailer->attachString($pdf, 'courrier.pdf', 'base64', 'application/pdf');
    
    		if ($return = $this->Phpmailer->send()) {
    			$this->Session->setFlash(__('Email sent', true));
    			$this->redirect(array('action'=>'index'));
    		}
    	}
    }
    ?>



.. _http://phpmailer.sourceforge.net/: http://phpmailer.sourceforge.net/
.. meta::
    :title: PHPMailer component for cakephp 1.2
    :description: CakePHP Article related to ,Components
    :keywords: ,Components
    :copyright: Copyright 2008 rapsys
    :category: components

