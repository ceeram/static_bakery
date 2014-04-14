PHPMailer with native API for PHP 5.x
=====================================

by kitten on April 10, 2007

This tutorial on using PHPMailer with Cake
(http://bakery.cakephp.org/articles/view/94) has a comment requesting
a component that lets you use the native PHPMailer API. This is
possible with PHP5's built-in overloading capabilities.
The Mailer component acts as a delegator and looks like this:


Component Class:
````````````````

::

    <?php 
    class MailerComponent extends Object
    {     
        /**
         * PHPMailer object.
         * 
         * @access private
         * @var object
         */
         var $m;    
        
        /**
         * Creates the PHPMailer object and sets default values.
         * Must be called before working with the component!
         *
         * @access public
         * @return void
         */
        function init()
        {
            // Include the class file and create PHPMailer instance
            vendor('phpmailer/class.phpmailer');
            $this->m = new PHPMailer;
            
            // Set default PHPMailer variables (see PHPMailer API for more info)
            $this->From = 'me@example.com';
            $this->FromName ='MyName';
            // set more PHPMailer vars, for smtp etc.
         }
    
        function __set($name, $value)
        {
            $this->m->{$name} = $value;
        }
        
        function __get($name)
        {
            if (isset($this->m->{$name})) {
                return $this->m->{$name};
            }
        }
                 
        function __call($method, $args)
        {
            if (method_exists($this->m, $method)) {
                return call_user_func_array(array($this->m, $method), $args);
            }
        }
    }
    ?>

In your controller, do the following:


Controller Class:
`````````````````

::

    <?php 
        var $components = array('Mailer');
    
        // Inside your method:
    
        // Set up mail
        $this->Mailer->init();
        $this->Mailer->AddAddress('recipient@example.com');
        $this->Mailer->Subject = 'My Subject';
        // Set PHPMailer vars and call PHPMailer methods (see PHPMailer API for more info)
        
        // Set mail body
        ob_start();
        $this->render('nameOfEmailTemplate', 'nameOfEmailLayout');
        $this->Mailer->Body = ob_get_clean();
    
        // Send mail	        		        
        if ($this->Mailer->send()) {
            echo 'Mail was sent successfully.';
         } else {
            echo 'There was a problem sending mail: '.$this->Mailer->ErrorInfo;
        }
    ?>

That's all! You can now call any PHPMailer method through
$this->Mailer->MethodName() and set any class variable through
$this->Mailer->varName = 'value'.

.. meta::
    :title: PHPMailer with native API for PHP 5.x
    :description: CakePHP Article related to overload,Mail,email,phpmailer,Components
    :keywords: overload,Mail,email,phpmailer,Components
    :copyright: Copyright 2007 kitten
    :category: components

