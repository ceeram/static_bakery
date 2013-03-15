

Captcha Components
==================

by %s on September 23, 2009

Here is a sample of code for a captcha component we made for the site
http://www.same-story.com/ Feel free to use, copy and redistribute !!
Thanks to all the cakephp community for their helps and supports on
this project!


Component Class:
````````````````

::

    <?php 
    /***************************************************
    * Captcha Component
    *
    * Create basic captacha image for website subscription
    *
    * @copyright    Copyright 2009, same-story.com
    * @link         http://www.same-story.com
    * @author       Samestory webmasters
    * @version      1.0
    * @license      BSD
    */ 
    class CaptchaComponent extends Object
    {	
    	function generate($code = null)
    	{
    		// Make sure we generate a code
    		if (empty($code)) {
    			$code = "123456";
    		}
    		
    		$img = imagecreate(120, 30);
    
    		// Pretty obvious
    		$background_color = imagecolorallocate ($img, 255, 255, 255);
    
    		// 6 different colors for each letters
    		$ecriture_color1 = imagecolorallocate ($img, 255, 102, 0);
    		$ecriture_color2 = imagecolorallocate ($img, 102, 255, 0);
    		$ecriture_color3 = imagecolorallocate ($img, 0, 255, 255);
    		$ecriture_color4 = imagecolorallocate ($img, 65, 187, 0);
    		$ecriture_color5 = imagecolorallocate ($img, 155, 86, 255);
    		$ecriture_color6 = imagecolorallocate ($img, 0, 255, 255);
    
    		// Font (define here the font you want to use)
    		$font=realpath('.')."/verdana.ttf";
    
    		header('Cache-Control: no-store, no-cache');
    		header('Content-Disposition: attachement; filename="captcha.jpg"');
    		header('Cache-Control: post-check=0, pre-check=0', false);
    		header("Content-type: image/jpeg");
    
    		// display all letters
    		imagettftext($img, 14, 0, 0, 20, $ecriture_color1, $font, $code[0]);
    		imagettftext($img, 14, 0, 20, 20, $ecriture_color2, $font, $code[1]);
    		imagettftext($img, 14, 0, 40, 20, $ecriture_color3, $font, $code[2]);
    		imagettftext($img, 14, 0, 60, 20, $ecriture_color4, $font, $code[3]);
    		imagettftext($img, 14, 0, 80, 20, $ecriture_color5, $font, $code[4]);
    		imagettftext($img, 14, 0, 100, 20, $ecriture_color6, $font, $code[5]);
    
    		// display the image
    		imagejpeg($img,'',90);
    
    		imageDestroy($img);
    	}
    }
    ?>


[h3]And here is how to use it:[h3]
[h4]1st, create an empty layout called "image"[h4]

Controller Class:
`````````````````

::

    <?php 
    class CaptchaController extends AppController {
    	function display() {
    		if (!$this->Session->check('User.Captcha.code')) 
    		{
    			$this->Session->write('User.Captcha.code', "123456");
    		}
    		$this->layout="image";
    		$this->Captcha->generate($this->Session->read('User.Captcha.code')); 
    	}
    ?>

And in the view:


View Template:
``````````````

::

    
    <img src="/captcha/display/" />

Jobs done !!

Check it out on our website, `http://www.same-story.com/inscription`_
Enjoy!


.. _http://www.same-story.com/inscription: http://www.same-story.com/inscription
.. meta::
    :title: Captcha Components
    :description: CakePHP Article related to ,Components
    :keywords: ,Components
    :copyright: Copyright 2009 
    :category: components

