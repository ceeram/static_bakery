Captcha component with PhpCaptcha
=================================

by %s on June 09, 2007

PhpCaptcha is a library for generating visual and audio CAPTCHAs
(completely automated public Turing test to tell computers and humans
apart).
Supported Features

+ Multiple random TrueType fonts
+ Character rotation
+ Optional chararacter shadow support
+ Optional site owner display text
+ Random custom background images
+ Font size selection
+ Greyscale or colour lines and characters
+ Character set selection
+ Integration of validation function for checking the user entered
  code with the generated code


NB: The audio CAPTCHA requires the Flite text to speech synthesis
engine.

1.- Download PhpCaptcha `http://www.ejeliot.com/pages/2`_ and unzip
archive in /vendors/phpcaptcha directory
2.- Download Vera font files: `http://ftp.gnome.org/pub/GNOME/sources
/ttf-bitstream-vera/1.10/ttf-bitstream-vera-1.10.zip`_ and unzip
archive in /vendors/phpcaptcha/fonts/
3.- Create Captcha component:


Component Class:
````````````````

::

    <?php 
    vendor('phpcaptcha'.DS.'php-captcha.inc');
    
    class CaptchaComponent extends Object
    {
    	var $controller;
     
    	function startup( &$controller ) {
    		$this->controller = &$controller;
    	}
    
    	function image(){
    		
    		$imagesPath = realpath(VENDORS . 'phpcaptcha').'/fonts/';
    		
    		$aFonts = array(
    			$imagesPath.'VeraBd.ttf',
    			$imagesPath.'VeraIt.ttf',
    			$imagesPath.'Vera.ttf'
    		);
    		
    		$oVisualCaptcha = new PhpCaptcha($aFonts, 200, 60);
    		$oVisualCaptcha->UseColour(true);
    		//$oVisualCaptcha->SetOwnerText('Source: '.FULL_BASE_URL);
    		$oVisualCaptcha->SetNumChars(6);
    		$oVisualCaptcha->Create();
    	}
    	
    	function audio(){
    		$oAudioCaptcha = new AudioPhpCaptcha('/usr/bin/flite', '/tmp/');
    		$oAudioCaptcha->Create();
    	}
    	
    	function check($userCode, $caseInsensitive = true){
    		if ($caseInsensitive) {
    			$userCode = strtoupper($userCode);
    		}
    		
    		if (!empty($_SESSION[CAPTCHA_SESSION_ID]) && $userCode == $_SESSION[CAPTCHA_SESSION_ID]) {
    			// clear to prevent re-use
    			unset($_SESSION[CAPTCHA_SESSION_ID]);
    			
    			return true;
    		}
    		else return false;
    		
    	}
    }
    ?>

4.- Use it in your Users controller


Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController
    {
        ...
        var $components = array('Captcha');
        ...
        function captcha_image()
    	{
    	    $this->Captcha->image();
    	}
    	
    	function captcha_audio()
    	{
    	    $this->Captcha->audio();
    	}
        ...
    }
    ?>

5.- Display captcha image, in your template view:


View Template:
``````````````

::

    
    <img id="captcha" src="<?php echo $html->url('/users/captcha_image');?>" alt="" />
     <a href="javascript:void(0);" onclick="javascript:document.images.captcha.src='<?php echo $html->url('/users/captcha_image');?>?' + Math.round(Math.random(0)*1000)+1">Reload image</a>

Pretty cool, we can reload captcha image if unreadable ;o)

6.- Validate captcha code in your controllers with
$this->Captcha->check() method.

With my cake apps I use the improved validation method explained on
the bakery. `http://bakery.cakephp.org/articles/view/more-improved-
advanced-validation`_ I've added this validation function to
validation.php

::

    
    function validateCaptcha($fieldName, $params){
    		$caseInsensitive = true;
    		
    		$val = $this->data[$this->name][$fieldName];
    		
    		if ($caseInsensitive) {
                $val = strtoupper($val);
            }
            
            //php-captcha.inc.php
            if(!defined('CAPTCHA_SESSION_ID'))
            	define('CAPTCHA_SESSION_ID', 'php_captcha');
             
             if (!empty($_SESSION[CAPTCHA_SESSION_ID]) && $val == $_SESSION[CAPTCHA_SESSION_ID]) {
                // clear to prevent re-use
                unset($_SESSION[CAPTCHA_SESSION_ID]);
                
                return true;
             }
             
             return false;
    	}

And voilï¿½

.. _http://www.ejeliot.com/pages/2: http://www.ejeliot.com/pages/2
.. _http://bakery.cakephp.org/articles/view/more-improved-advanced-validation: http://bakery.cakephp.org/articles/view/more-improved-advanced-validation
.. _http://ftp.gnome.org/pub/GNOME/sources/ttf-bitstream-vera/1.10/ttf-bitstream-vera-1.10.zip: http://ftp.gnome.org/pub/GNOME/sources/ttf-bitstream-vera/1.10/ttf-bitstream-vera-1.10.zip
.. meta::
    :title: Captcha component with PhpCaptcha
    :description: CakePHP Article related to image,captcha,form,audio,Components
    :keywords: image,captcha,form,audio,Components
    :copyright: Copyright 2007 
    :category: components

