Gravatar Helper
===============

by bdude on April 12, 2008

Gravatars are very popular way for users to maintain the same avatar
across multiple site using just their email address. This helpful
helper makes it even easier to implement Gravatars into your site.
For more information on Gravatars, visit `http://www.gravatar.com`_.


The Functions
~~~~~~~~~~~~~
The helper consists of two main functions, imgURL and imgTag .

imgURL returns just a URL to the user, to implement in their own HTML.
Example:

::

    <?php
    /** in your view **/
    <img src="<?php echo $gravatar->imgURL($input); ?>" />
    ?>

imgTag returns actual code, and has a second (optional) parameter to
add a class to the image. Example:

::

    <?php echo $gravatar->imgTag($input); ?>



$input
``````
$input is the main variable input into the helper. It works in two
ways:

+ a string containing just the email address.
+ an array of options

If you choose to use the array of options, these are the options
available:

+ email => the email address
+ size => the width of the image (in pixels)
+ rating => restrict the avatar to a maximum rating (G, PG, R, X)
+ [li]default => the URL of an image to show if the user doesn't have
  a gravatar



The Code
~~~~~~~~

::

    
    <?php
    
    /**
    * GRAVATAR HELPER CLASS
    */
    
    class GravatarHelper extends AppHelper {
    
    
    	function imgURL($input) {
    		return $this->makeURL($input);	
    	}
    
    	function imgTag($input, $class = false) {
    		$url = $this->makeURL($input);
    		$classHTML = $class != false ? 'class="' . $class . '"' : '';
    		$output = '<img src="' . $url . '" '. $classHTML . ' />';
    		return $output;
    	}
    
    	/* Private Function to generate a URL
    	 * Takes either an array of options (including email)
    	 * or a string with the email address- to use the defaults.
    	 */
    	
    	private function makeURL($input) {
    		$baseURL = "http://www.gravatar.com/avatar/";
    		
    		if(is_string($input)) {
    			$URL = $baseURL . md5($input);
    			return $URL;
    		}
    		
    		if(is_array($input)) {
    			$URL = $baseURL . md5($input['email']) . '/?';
    			if(array_key_exists('rating', $input)) {$URL .= 'r=' . $input['rating'] . '&';}
    			if(array_key_exists('size', $input)) {$URL .= 's=' . $input['size'] . '&';}
    			if(array_key_exists('default', $input)) {$URL .= 'default=' . urlencode($input['default']);}
    			
    			return $URL;
    			
    		}
    		
    	}
    	
    }



.. _http://www.gravatar.com: http://www.gravatar.com/
.. meta::
    :title: Gravatar Helper
    :description: CakePHP Article related to gravatar,Helpers
    :keywords: gravatar,Helpers
    :copyright: Copyright 2008 bdude
    :category: helpers

