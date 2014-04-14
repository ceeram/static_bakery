Sending Email With PHPMailer
============================

by drayen on October 11, 2006

[This is a slightly updated copy of the tutorial on now defunct
wiki.cakephp.org] This example will show you how to send HTML mail
from you Cake application with PHPMailer. You will create: 1. cakePHP
component 2. a vendor package 3. view for plain text email body 4.
view for HTML email body 5. a function in your controller to send
mail.


Steps
~~~~~

Get PHPMailer
`````````````


#. Get PHPMailer `http://phpmailer.sourceforge.net/`_
#. Unpack it into app/vendors/phpmailer/ , so you'll have
   /vendors/phpmailer/class.phpmailer.php etc.etc.



Create views and layouts
````````````````````````

#. Create two views, default_html.thtml and default_text.thtml and
   place them in app/views/your_controller/email/
#. Create a layout for the HTML part of the email, call it
   app/views/layouts/email.thtml



Create component
````````````````


#. [li]Create new component email. Paste the following code into
   app/controllers/components/email.php



Component Class:
````````````````

::

    <?php 
    /**
     * This is a component to send email from CakePHP using PHPMailer
     * @link http://bakery.cakephp.org/articles/view/94
     * @see http://bakery.cakephp.org/articles/view/94
     */
    
    class EmailComponent
    {
      /**
       * Send email using SMTP Auth by default.
       */
        var $from         = 'phpmailer@cakephp';
        var $fromName     = "Cake PHP-Mailer";
        var $smtpUserName = '';  // SMTP username
        var $smtpPassword = ''; // SMTP password
        var $smtpHostNames= "";  // specify main and backup server
        var $text_body = null;
        var $html_body = null;
        var $to = null;
        var $toName = null;
        var $subject = null;
        var $cc = null;
        var $bcc = null;
        var $template = 'email/default';
        var $attachments = null;
    
        var $controller;
    
        function startup( &$controller ) {
          $this->controller = &$controller;
        }
    
        function bodyText() {
        /** This is the body in plain text for non-HTML mail clients
         */
          ob_start();
          $temp_layout = $this->controller->layout;
          $this->controller->layout = '';  // Turn off the layout wrapping
          $this->controller->render($this->template . '_text'); 
          $mail = ob_get_clean();
          $this->controller->layout = $temp_layout; // Turn on layout wrapping again
          return $mail;
        }
    
        function bodyHTML() {
        /** This is HTML body text for HTML-enabled mail clients
         */
          ob_start();
          $temp_layout = $this->controller->layout;
          $this->controller->layout = 'email';  //  HTML wrapper for my html email in /app/views/layouts
          $this->controller->render($this->template . '_html'); 
          $mail = ob_get_clean();
          $this->controller->layout = $temp_layout; // Turn on layout wrapping again
          return $mail;
        }
    
        function attach($filename, $asfile = '') {
          if (empty($this->attachments)) {
            $this->attachments = array();
            $this->attachments[0]['filename'] = $filename;
            $this->attachments[0]['asfile'] = $asfile;
          } else {
            $count = count($this->attachments);
            $this->attachments[$count+1]['filename'] = $filename;
            $this->attachments[$count+1]['asfile'] = $asfile;
          }
        }
    
    
        function send()
        {
        vendor('phpmailer'.DS.'class.phpmailer');
    
        $mail = new PHPMailer();
    
        $mail->IsSMTP();            // set mailer to use SMTP
        $mail->SMTPAuth = true;     // turn on SMTP authentication
        $mail->Host   = $this->smtpHostNames;
        $mail->Username = $this->smtpUserName;
        $mail->Password = $this->smtpPassword;
    
        $mail->From     = $this->from;
        $mail->FromName = $this->fromName;
        $mail->AddAddress($this->to, $this->toName );
        $mail->AddReplyTo($this->from, $this->fromName );
    
        $mail->CharSet  = 'UTF-8';
        $mail->WordWrap = 50;  // set word wrap to 50 characters
    
        if (!empty($this->attachments)) {
          foreach ($this->attachments as $attachment) {
            if (empty($attachment['asfile'])) {
              $mail->AddAttachment($attachment['filename']);
            } else {
              $mail->AddAttachment($attachment['filename'], $attachment['asfile']);
            }
          }
        }
    
        $mail->IsHTML(true);  // set email format to HTML
    
        $mail->Subject = $this->subject;
        $mail->Body    = $this->bodyHTML();
        $mail->AltBody = $this->bodyText();
    
        $result = $mail->Send();
    
        if($result == false ) $result = $mail->ErrorInfo;
    
        return $result;
        }
    }
    ?>



Useing it in your controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~


Controller Class:
`````````````````

::

    <?php 
    class MyController extends AppController{
        var $components = array('Email'); //  use component email
    
        ...
     
        function send() {
                $this->Email->template = 'email/confirm';
                // You can use customised thmls or the default ones you setup at the start
               
                $this->set('data', $data);
                $this->Email->to = 'someone@somewhere.com';
                $this->Email->subject = 'your new account';
               
               
                $this->Email->attach($fully_qualified_filename, optionally $new_name_when_attached);
                // You can attach as many files as you like.
               
                $result = $this->Email->send();
     
            //the rest of the controller method...
          }
    }
    ?>



Credits
+++++++

This is lifted almost word for word from the original piece at
`http://wiki.cakephp.org/tutorials:sending_email_with_phpmailer`_ but
i figured as i was about to use it, i would rewrite it for the bakery.

Enjoy.

Drayen.

.. _http://wiki.cakephp.org/tutorials:sending_email_with_phpmailer: http://wiki.cakephp.org/tutorials:sending_email_with_phpmailer
.. _http://phpmailer.sourceforge.net/: http://phpmailer.sourceforge.net/
.. meta::
    :title: Sending Email With PHPMailer
    :description: CakePHP Article related to email,phpmailer,component,Tutorials
    :keywords: email,phpmailer,component,Tutorials
    :copyright: Copyright 2006 drayen
    :category: tutorials

