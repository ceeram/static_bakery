

Captcha component with Securimage
=================================

by %s on March 09, 2009

Securimage is an open-source free PHP CAPTCHA script for generating
complex images and CAPTCHA codes to protect forms from spam and abuse.
It can be easily added into existing forms on your website to provide
protection from spam bots. It can run on most any webserver as long as
you have PHP installed, and GD support within PHP. Securimage does
everything from generate complicated CAPTCHA images to making sure the
code the user entered was correct.


Securimage Features
~~~~~~~~~~~~~~~~~~~

+ Show an image in just 3 lines of code
+ Validate submitted entries in less than 6 lines of code
+ Customizable code length
+ Choose the character set
+ TTF font support
+ Use custom GD fonts when TTF is not available
+ Easily add background images
+ Multi colored, angled, and transparent text options
+ Arched lines through text
+ Generates audible CAPTCHA files in wav format
+ Use a word list for creating CAPTCHA codes



Step 1: Download and unzip Securimage archive
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Download a Securimage archive (either .zip or .tar.gz) at
`http://www.phpcaptcha.org/download/`_ and unzip the archive into the
directory /app/vendors/securimage/



Step 2: Create captcha component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/controllers/components/captcha.php

Component Class:
````````````````

::

    <?php 
    
    /**
     * Securimage-Driven Captcha Component
     * @author debuggeddesigns.com
     * @license MIT
     * @version 0.1
     */
     
    //cake's version of a require_once() call
    vendor('securimage'.DS.'securimage'); //use this with the 1.1 core
    //App::import('Vendor','Securimage' ,array('file'=>'securimage'.DS.'securimage.php')); //use this with the 1.2 core
      
     
    //the local directory of the vendor used to retrieve files
    define('CAPTCHA_VENDOR_DIR', APP . 'vendors' . DS . 'securimage/');
    
    class CaptchaComponent extends Object {
    
        var $controller;
    
        //size configuration
        var $_image_height = 75; //the height of the captcha image
        var $_image_width = 350; //the width of the captcha image
        
        
        //background configuration
        var $_draw_lines = true; //whether to draw horizontal and vertical lines on the image
        var $_draw_lines_over_text = false; //whether to draw the lines over the text
        var $_draw_angled_lines = true; //whether to draw angled lines on the image
        
        var $_image_bg_color = '#ffffff'; //the background color for the image
        var $_line_color = '#cccccc'; //the color of the lines drawn on the image
        var $_line_distance = 15; //how far apart to space the lines from eachother in pixels
        var $_line_thickness = 2; //how thick to draw the lines in pixels
        var $_arc_line_colors = '#999999,#cccccc'; //the colors of arced lines
        
        
        //text configuration
        var $_use_gd_font = false; //whether to use a gd font instead of a ttf font
        var $_use_multi_text = true; //whether to use multiple colors for each character
        var $_use_transparent_text = true; //whether to make characters appear transparent
        var $_use_word_list = false; //whether to use a word list file instead of random code
        
        var $_charset = 'ABCDEFGHKLMNPRSTUVWYZ23456789'; //the character set used in image
        var $_code_length = 5; //the length of the code to generate
        var $_font_size = 45; //the font size
        var $_gd_font_size = 50; //the approxiate size of the font in pixels
        var $_text_color = '#000000'; //the color of the text - ignored if $_multi_text_color set
        var $_multi_text_color = '#006699,#666666,#333333'; //the colors of the text
        var $_text_transparency_percentage = 45; //the percentage of transparency, 0 to 100
        var $_text_angle_maximum = 21; //maximum angle of text in degrees
        var $_text_angle_minimum = -21; //minimum angle of text in degrees
        var $_text_maximum_distance = 70; //maximum distance for spacing between letters in pixels
        var $_text_minimum_distance = 68; //minimum distance for spacing between letters in pixels
        var $_text_x_start = 10; //the x-position on the image where letter drawing will begin
        
        
        //filename and/or directory configuration
        var $_audio_path = 'audio/'; //the full path to wav files used
        var $_gd_font_file = 'gdfonts/bubblebath.gdf'; //the gd font to use
        var $_ttf_file = 'elephant.ttf'; //the path to the ttf font file to load
        var $_wordlist_file = 'words/words.txt'; //the wordlist to use
        
        
        function startup( &$controller ) {
    
            //add local directory name to paths
            $this->_ttf_file = CAPTCHA_VENDOR_DIR.$this->_ttf_file; 
    		$this->_gd_font_file = CAPTCHA_VENDOR_DIR.$this->_gd_font_file;
        	$this->_audio_path = CAPTCHA_VENDOR_DIR.$this->_audio_path;
        	$this->_wordlist_file = CAPTCHA_VENDOR_DIR.$this->_wordlist_file; 
    		//CaptchaComponent instance of controller is replaced by a securimage instance
    		$controller->captcha =& new securimage();
    		$controller->captcha->arc_line_colors = $this->_arc_line_colors;
    		$controller->captcha->audio_path = $this->_audio_path;
    		$controller->captcha->charset = $this->_charset;
    		$controller->captcha->code_length = $this->_code_length;
    		$controller->captcha->draw_angled_lines = $this->_draw_angled_lines;
    		$controller->captcha->draw_lines = $this->_draw_lines;
    		$controller->captcha->draw_lines_over_text = $this->_draw_lines_over_text;
    		$controller->captcha->font_size = $this->_font_size;
    		$controller->captcha->gd_font_file = $this->_gd_font_file;
    		$controller->captcha->gd_font_size = $this->_gd_font_size;
    		$controller->captcha->image_bg_color = $this->_image_bg_color;
    		$controller->captcha->image_height = $this->_image_height;
    		$controller->captcha->image_width = $this->_image_width;
    		$controller->captcha->line_color = $this->_line_color;
    		$controller->captcha->line_distance = $this->_line_distance;
    		$controller->captcha->line_thickness = $this->_line_thickness;
    		$controller->captcha->multi_text_color = $this->_multi_text_color;
    		$controller->captcha->text_angle_maximum = $this->_text_angle_maximum;
    		$controller->captcha->text_angle_minimum = $this->_text_angle_minimum;
    		$controller->captcha->text_color = $this->_text_color;
    		$controller->captcha->text_maximum_distance = $this->_text_maximum_distance;
    		$controller->captcha->text_minimum_distance = $this->_text_minimum_distance;
    		$controller->captcha->text_transparency_percentage = $this->_text_transparency_percentage;
    		$controller->captcha->text_x_start = $this->_text_x_start;
    		$controller->captcha->ttf_file = $this->_ttf_file;
    		$controller->captcha->use_gd_font = $this->_use_gd_font;
    		$controller->captcha->use_multi_text = $this->_use_multi_text;
    		$controller->captcha->use_transparent_text = $this->_use_transparent_text;
    		$controller->captcha->use_word_list = $this->_use_word_list;
    		$controller->captcha->wordlist_file = $this->_wordlist_file;
    		$controller->set('captcha',$controller->captcha);
        }
    }
    
    ?>



Step 3: Use the captcha component inside a controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/controllers/contacts_controller.php

Controller Class:
`````````````````

::

    <?php 
    
    class ContactsController extends AppController {
    	
        var $name = 'Contacts';
        var $components = array('Captcha');
         
        function securimage($random_number){
            $this->autoLayout = false; //a blank layout
    
            //override variables set in the component - look in component for full list
            $this->captcha->image_height = 75;
            $this->captcha->image_width = 350;
            $this->captcha->image_bg_color = '#ffffff';
            $this->captcha->line_color = '#cccccc';
            $this->captcha->arc_line_colors = '#999999,#cccccc';
            $this->captcha->code_length = 5;
            $this->captcha->font_size = 45;
            $this->captcha->text_color = '#000000';
    
            $this->set('captcha_data', $this->captcha->show()); //dynamically creates an image
        }
    
        function index(){
            $this->set('captcha_form_url', $this->webroot.'contacts/index'); //url for the form
            $this->set('captcha_image_url', $this->webroot.'contacts/securimage/0'); //url for the captcha image
    
            $captcha_success_msg = 'The code you entered matched the captcha';
            $captcha_error_msg = 'The code you entered does not match';
    
            if( empty($this->data) ){ //form has not been submitted yet
                $this->set('error_captcha', ''); //error message displayed to user
                $this->set('success_captcha', ''); //success message displayed to user
                $this->render(); //reload page
            } else { //form was submitted 	
                if( $this->captcha->check($this->data['Contact']['captcha_code']) == false ) {
                    //the code was incorrect - display an error message to user
                    $this->set('error_captcha', $captcha_error_msg); //set error msg
                    $this->set('success_captcha', ''); //set success msg
                    $this->render(); //reload page
                } else {
                    //the code was correct - display a success message to user
                    $this->set('error_captcha', ''); //set error msg
                    $this->set('success_captcha', $captcha_success_msg); //set success msg
                    $this->render(); //reload page
    
                    //after testing is complete, you would process the other form data here and save it
                }
            }
        }
    }
    
    ?>



Step 4: Create a Contact model
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/models/contact.php

View Template:
``````````````

::

    
    <?php
    class Contact extends AppModel {
       var $useTable = false;
    }
    ?> 



Step 5: Create a view to hold the dynamic image
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/views/contacts/securimage.thtml

View Template:
``````````````

::

    
    <?php echo $captcha_data; ?>



Step 6: Create a view to test the captcha with
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/views/contacts/index.thtml

View Template:
``````````````

::

    
    <form action="<?php echo $captcha_form_url; ?>" method="post">
    <div>Verify :</div>
    <div><img src="<?php echo $captcha_image_url; ?>" id="captcha" alt="CAPTCHA Image" /></div>
    <div><input type="text" name="data[Contact][captcha_code]" size="10" maxlength="6" value="" /></div>
    <div><a href="#" onclick="document.getElementById('captcha').src = '<?php echo $this->webroot;?>contact/securimage/' + Math.random(); return false">Reload Image</a></div>
    <div style="color:red;"><?php echo $error_captcha; ?></div>
    <div style="color:green;"><?php echo $success_captcha; ?></div>
    <div><input type="submit" value="CLICK HERE TEST THE CAPTCHA" /></div>
    </form>



.. _http://www.phpcaptcha.org/download/: http://www.phpcaptcha.org/download/
.. meta::
    :title: Captcha component with Securimage
    :description: CakePHP Article related to image,GD,captcha,form,spam,securimage,verify,Tutorials
    :keywords: image,GD,captcha,form,spam,securimage,verify,Tutorials
    :copyright: Copyright 2009 
    :category: tutorials

