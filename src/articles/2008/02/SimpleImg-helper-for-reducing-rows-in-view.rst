SimpleImg - helper for reducing rows in view
============================================

by %s on February 27, 2008

When I was playing with images I always messed up with stuff which
didn't belongs into view code. Like "if is_file(image" etc. but I want
to be sure that there is going to be an image whether wanted or empty-
image, so I wrote a following helper to make things easier.

To use it in all of your views/layout put the following clause in
app_controller.php or you can use it per view basis.

::

    
    var $helpers = array('SimpleImg');


Name as simple_img.php and put it in helpers folder.

Helper Class:
`````````````

::

    <?php 
    /**
     * 	SimpleImage - Image handling for shortening the handling and belaying the image data
     * 	
     *	When you need to move all ugly image handling stuff away from html then this
     *	class will be handy. Main purpose is to shorten an image handling rows in html and second was to authenticate returning
     *	image data so you could be sure there's correct image coming. 	 
     *	
     *	Example of usage in cake and shorthand-way:		 	 	
     *	$html->image(
     *		$simpleImg->giu("images/medium", $image, 800, 600, array("Image", "name")),
     *		array(
     *			"alt" 		=> $simpleImg->gia("images/medium", $image, 800, 600, array("Image", "name")), 
     *			"width" 	=> $simpleImg->giw("images/medium", $image, 800, 600, array("Image", "name")), 
     *			"height" 	=> $simpleImg->gih("images/medium", $image, 800, 600, array("Image", "name"))
     *		)
     *	); 	 	 
     *	
     *	Analyses:
     *		"images/medium"			=> is image's folder in disk
     *		$image					=> is images folder in disk	
     *		800						=> width
     *		600						=> height	 		 	 
     *		array("Image", "name")	=> path to image src from $image-object
     *		
     *	Or an example of usage in cake and temp_var-way:	
     *	$tmp_img = 	$simpleImg->getImgUrlA("images/medium", $image, 800, 600, array("Image", "name"));
     *	
     *	now it is easy to image's data like $tmp_img["img_src"] is img_src relative source	 	   
    
     *  InotImage is free software; you can redistribute it and/or modify
     *  it under the terms of the GNU General Public License as published by
     *  the Free Software Foundation; either version 2 of the License, or
     *  (at your option) any later version.
     *
     *  InotImage is distributed in the hope that it will be useful,
     *  but WITHOUT ANY WARRANTY; without even the implied warranty of
     *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *  GNU General Public License for more details.
     *
     * @author     unigue__ <juuttk@gmail.com>
     * @copyright  (C) 2007 unigue__
     * @license    http://gnu.org/copyleft/gpl.html GNU GPL
     * @version     1.0
     */
    class SimpleImgHelper extends Helper
    {
    	    
    	// {{{ Class's constants    
    	    
    	/**
    	 *	Inform about operator which is using this class and it's services. Used to handle paths
    	 *	
    	 *	@var	string	 	 
    	 *	@access static
    	 */	 	 	 	
    	var $operator 		= "cakephp";	
    	
    	// }}}
    	// {{{ Class's private variables	
    	
    	/**
    	 *	Empty image which is in self::image_folder
    	 *	
    	 *	@var	string	 	 
    	 *	@access private
    	 */
    	var $empty_image__ 	= "image_not_exists.jpg";
    	
    	/**
    	 *	Images folder
    	 *	
    	 *	@var	string	 	 
    	 *	@access private
    	 */
    	var $image_folder__	= "img";
    	
    	// }}}
    	// {{{ Class's functions	
    	    
        /**
    	 *	Main purpose is to scale only if necessary and if so keep aspect ratio of the image
    	 *		 	 	
    	 *	The rules: 
    	 *		1. new_width <= image_width
    	 *		2. new_height <= image_height
    	 *		3. Scale only if necessary	 	 	 	 
    	 *	
    	 *	@param	integer		Max width for image	 	 	 	
    	 *	@param	integer		Max height for image		 
    	 *	@param	string		Image's absolute url
    	 *	@return array		Integers having new_width and new_height in it 	 		 
    	 */	 	
    	function getDimensions($max_width, $max_height, $image_src)
    	{	
    		$possible_base_paths	= array("", WWW_ROOT);
    		$image_base_path 		= "";
    		$image_exists			= false; 
    		
    		// If image doesn't exist return these dimensions
    		$not_exists_dim			= array(10, 10);
    		
    		if(is_array($possible_base_paths)) {
    		
    			// Try with wildcards
    			foreach($possible_base_paths as $possible_base_path) {
    
    				if(is_file($possible_base_path.$image_src) ) {
    					$image_exists 		= true;
    					$image_base_path	= $possible_base_path; 
    					break;
    				}			
    			} 
    		}
    		
    		if(!$image_exists) {
    			return $not_exists_dim;
    		}
    		else {	
    			$size 			= getimagesize($image_base_path.$image_src);
    			
    			$image_width	= $size[0];
    			$image_height	= $size[1];
    			$image_ratio 	= $image_width / $image_height;
    			$max_ratio 		= $max_width / $max_height;
    			$new_width 		= $image_width;
    			$new_height 	= $image_height;			
    	
    			// This covers the rule: 3
    			if($image_width > $max_width || $image_height > $max_height) {
    							
    				/**
    				 *	Prove:			
    				 *	ix / iy > mx / my hence
    				 *	mx < (ix / iy) * my	
    				 *	my > mx / (ix / iy)
    				 *	Let nx = mx then ny must to be shorter, according to clause an my > mx / (ix / iy)	
    				 *	where ny must be shorter than nx divided by ratio so everything is fine
    				 *	This covers the rules: 1 & 2			 
    				 */		 			 
    				if($image_ratio > $max_ratio) {
    					$new_width 	= $max_width;
    					$new_height = $max_width / $image_ratio;			
    				}
    				/**
    				 *	Prove:			
    				 *	ix / iy < mx / my hence
    				 *	mx > (ix / iy) * my	
    				 *	my < mx / (ix / iy)
    				 *	Let ny = my then nx must to be narrower, according to clause an mx > (ix / iy) * my
    				 *	where mx is taller than ratio multiply ny	
    				 *	This covers the rules: 1 & 2			 
    				 */ 
    				else {				
    					$new_height	= $max_height;
    					$new_width 	= $max_height * $image_ratio;			
    				}	 	
    			}			
    			return array(round($new_width), round($new_height));
    		}
    	}
    	
    	/**
    	 *	Returns a verified image data. 	 	 	 
    	 *	 	 
    	 *	@param	string			Absolute url of image folder 	 
    	 *	@param	array/string	Contains the image
    	 *	@param	array			Path to image src ie. ['Image']['name']
    	 *	@param	int				Max width
    	 *	@param	int				Max height
    	 *	@return	array			Information of image
    	 *		img_src	string 	Relative url to image	 
    	 *		width	int 	Width		 
    	 *		height	int 	Height			 
    	 */
    	function getImgUrlA($base_image_url, $url_obj, 
    			$max_width, $max_height, $url_params = null, $empty_image_url = null, $settings = array())
    	{
    		// Execute basic settings
    		self::basics();
    
    		// If not defined in the call - this relative web url to empty image
    		if(!empty($empty_image_url)) {
    			$function_empty_image = $empty_image_url;
    		}
    		else {
    			$function_empty_image = $this->empty_image__;
    		}	
    		
    		// Take slashes off
    		$function_base_image_url 			= trim($base_image_url, "/"); // Relative url
    		$function_base_image_url 			= $function_base_image_url.DS; // Absolute url		
    		$function_base_image_url 			= str_ireplace("/", DS, $function_base_image_url); // Convert to absolute url
    		$function_absolute_image_base_path 	= WWW_ROOT.$this->image_folder__.DS;
    		$function_relative_image_base_path 	= $this->image_folder__.DS;
    		
    		// Cake appends /img automatically - if they are located in different place modify this
    		$function_web_base_url 				= ""; // Relative url
    		
    		// Shorthands
    		$fbiu 	= $function_base_image_url;
    		$faibp 	= $function_absolute_image_base_path;
    		$fribp 	= $function_relative_image_base_path;
    		$fei	= $function_empty_image;
    		$fwbu	= $function_web_base_url;
    		
    		// $empty_image_url-param is also empty, so give function's $empty_image infos
    		$dims 		= $this->getDimensions($max_width, $max_height, $fribp.$fei);						
    		$img_src 	= $fei; // Function's empty image
    		
    		// It's empty return empty image	
    		if(empty($url_obj)) {
    					
    			// return param's empty image's infos if they aint empty
    			if(!empty($empty_image_url) && is_file($faibp.$empty_image_url)) {
    				$dims 		= $this->getDimensions($max_width, $max_height, $faibp.$empty_image_url);						
    				$img_src 	= $fwbu.$empty_image_url;
    			}		
    		}
    		// Params are not empty and array
    		else if(!empty($url_params) && is_array($url_params)) {
    			eval("\$image_url = \$url_obj['".implode("']['", $url_params)."'];");
    			
    			// Test if (absolute_path + base_url + image_src) == file
    			if(is_file($faibp.$fbiu.$image_url)) {	
    				$dims 		= $this->getDimensions($max_width, $max_height, $faibp.$fbiu.$image_url);
    				$img_src 	= $fwbu.$base_image_url."/".$image_url; // Web url
    			}		
    		}
    		else {
    			// Test if (absolute_path + base_url + image_src) == file
    			if(is_file($faibp.$fbiu.$url_obj)) {
    				$dims 		= $this->getDimensions($max_width, $max_height, $faibp.$fbiu.$url_obj);
    				$img_src 	= $fwbu.$base_image_url."/".$url_obj; // Web url
    			}	
    		
    		}
    		
    		$image_alt = ereg_replace("\..+$", "", basename($img_src));
    		
    		return array("img_src" => $img_src, "width" => $dims[0], "height" => $dims[1], "image_alt" => $image_alt);
    	}
    	
    	/**
         *	Define basic constants for InotImage
         *	
         *	@access static
         */	
    	function basics() 
    	{
    		// For every others
    		if(strcmp($this->operator, "cakephp") != 0) {
    			self::basics_others();
    		}
        }
        
        /**
         *	Of course you could define special settings for each kind of situation
         *	Define correct path to WWW_ROOT which purpose is to hold a parent folder of image's address     *	     
         *	
         *	@access static
         */	 	     
        function basics_others() 
    	{
    		define("DS", DIRECTORY_SEPARATOR);
    
    		// This should be an absolute url to image's parent folder
    		// So you have to define it yourself because I can't know where this file may be
    		// If you are alreadyuding this constant and it is UNDER image folder 
    		//	modify $this->image_folder__ to point to parent folder of image's folder 
    		//define("WWW_ROOT", dirname(__FILE__)); 
        }
    	
    	// }}}
    	// {{{ Class's shorthand functions
    	
    	/**
    	 *	This is designed to give just an url of an image with shorter and cleaner way	 
    	 *	Usage as in getImgUrlA	
    	 *	This function's params and usage has to be synchnorized with getImgUrlA 
    	 */
    	function giu($base_image_url, $url_obj, 
    			$max_width, $max_height, $url_params = null, $empty_image_url = null, $settings = array())
    	{	
    		$img_stuff = self::getImgUrlA($base_image_url, $url_obj, 
    			$max_width, $max_height, $url_params, $empty_image_url, $settings);			
    		return $img_stuff["img_src"];
    	}
    	
    	/**
    	 *	This is designed to give just a width of an image with shorter and cleaner way	 
    	 *	Usage EXACTLY as in getImgUrlA	
    	 *	This function's params and usage has to be synchnorized with getImgUrlA 
    	 */
    	function giw($base_image_url, $url_obj, 
    			$max_width, $max_height, $url_params = null, $empty_image_url = null, $settings = array())
    	{	
    		$img_stuff = self::getImgUrlA($base_image_url, $url_obj, 
    			$max_width, $max_height, $url_params, $empty_image_url, $settings);			
    		return $img_stuff["width"];
    	}
    	
    	/**
    	 *	This is designed to give just a height of an image with shorter and cleaner way	 
    	 *	Usage EXACTLY as in getImgUrlA	
    	 *	This function's params and usage has to be synchnorized with getImgUrlA 
    	 */
    	function gih($base_image_url, $url_obj, 
    			$max_width, $max_height, $url_params = null, $empty_image_url = null, $settings = array())
    	{	
    		$img_stuff = self::getImgUrlA($base_image_url, $url_obj, 
    			$max_width, $max_height, $url_params, $empty_image_url, $settings);				
    		return $img_stuff["height"];
    	}
    	
    	/**
    	 *	This is designed to give just an alt of an image with shorter and cleaner way	 	
    	 *	Usage EXACTLY as in getImgUrlA	
    	 *	This function's params and usage has to be synchnorized with getImgUrlA 
    	 */
    	function gia($base_image_url, $url_obj, 
    			$max_width, $max_height, $url_params = null, $empty_image_url = null, $settings = array())
    	{	
    		$img_stuff = self::getImgUrlA($base_image_url, $url_obj, 
    			$max_width, $max_height, $url_params, $empty_image_url, $settings);				
    		return $img_stuff["image_alt"];
    	}    
    	
    	// }}}
    	
    }
    ?>



Now here comes a couple of examples of usage
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The shorthand way

::

    
    echo $html->image(
    
        $simpleImage->giu("images/thumbnail", $image, 80, 80, array("Image", "name")),
    
        array(
    
            "alt"       => $simpleImage->gia("images/thumbnail", $image, 80, 80, array("Image", "name")),
    
            "width"     => $simpleImage->giw("images/thumbnail", $image, 80, 80, array("Image", "name")),
    
            "height"    => $simpleImage->gih("images/thumbnail", $image, 80, 80, array("Image", "name"))
    
        )
    
    );


And temp-var - way

::

    
    $tmp_img = $simpleImg->getImgUrlA("images/medium", $image, 800, 600, array("Image", "name"));
    
    // Usage like
    echo $html->image($tmp_img["img_src"]);


Which one to use is matter of taste, both works identically but the
shorthand way will save a couple lines but in temp-var - way is less
chars on a row.

But let's analyse a bit of those examples

"images/medium"
This is where your images are in disk/hd it is assumed that images are
in img-folder if you're using cakePHP so img/images/medium is the path

$image
Actual image, I do fetch image location/information data from database
and $image is just cakePHP's query return

800
Image's max width, SimpleImg will keep the aspect ratio and scale only
if necessary

600
Like above but for height

array("Image", "name")
This is a path to image's src, ie. $image["Image"]["name"]

- These are in reserve -

$empty_image_url
You could define an empty image on runtime

$settings
Not used yet


This is what SimpleImg will do

+ Offers a smart scale where image is scaled only if necessary and
  then keep the aspect ratio of image
+ Reduces rows in html
+ Returns a valid image, either empty or wanted one
+ Designed in sense of a valid html
+ Fw independent after small modifications, those are detailed in
  SimpleImg


.. meta::
    :title: SimpleImg - helper for reducing rows in view
    :description: CakePHP Article related to image,SimpleImg,Helpers
    :keywords: image,SimpleImg,Helpers
    :copyright: Copyright 2008 
    :category: helpers

