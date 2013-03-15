

Image Tag Helper
================

by %s on July 30, 2009

This helper will output an XHTML valid image tag. It will get the
image dimensions (width and height), and add the attributes to the
image. You can also specify a width and height, and it will not get
them from the image.
This helper will output an XHTML valid image tag. It will get the
image dimensions (width and height), and add the attributes to the
image. You can also specify a width and height, and it will not get
them from the image. Specifying image dimensions is one of the many
front end optimizations you can make to your website. It will speed up
the rendering of the page by reducing the number of reflows and
repaints.

Helper Class:
`````````````

::

    <?php 
    /**
     * This class builds an image tag. The main purpose of this is to get the image dimensions and
     * include the appropriate attributes if not specified. This will improve front end performance.
     *  
     * @author Seth Cardoza <seth.cardoza@gmail.com>
     * @category image
     * @package helper
     */
    class ImageHelper extends Helper
    {
    	/**
    	 * Builds html img tag determining width and height if not specified in the
    	 * attributes parameter.
    	 *
    	 * @param string $src relative path to image including the 'img' directory
    	 * @param array $attributes array of html attributes to apply to the image
    	 *
    	 * @access public
    	 *
    	 * @return no return value, outputs the img tag
    	 */
    	public function displayImage($src, $attributes = array()) {
    		//get width/height via exif data
    		//build image html
    		if(file_exists(WWW_ROOT . $src)) {
    			$image_size = getimagesize(WWW_ROOT . $src);
    			if(!array_key_exists('width', $attributes) && array_key_exists('height', $attributes)) {
    				$attributes['width'] = ($image_size[0] * $attributes['height']) / $image_size[1]; 
    			} elseif(array_key_exists('width', $attributes) && !array_key_exists('height', $attributes)) {
    				$attributes['height'] = ($image_size[1] * $attributes['width']) / $image_size[0]; 
    			} else {
    				$attributes['width'] = $image_size[0];
    				$attributes['height'] = $image_size[1];
    			}
    		}
    
    		
    		$html = '<img src="' . $src . '"';
    		
    		foreach($attributes as $key => $value) {
    			$html .= ' ' . $key . '="' . htmlentities($value, ENT_COMPAT, 'ISO-8859-1', false) . '"';
    		}
    		
    		$html .= ' />';
    		echo $html;
    	}
    }
    ?>


.. meta::
    :title: Image Tag Helper
    :description: CakePHP Article related to front end optimizati,image size,image helper,Helpers
    :keywords: front end optimizati,image size,image helper,Helpers
    :copyright: Copyright 2009 
    :category: helpers

