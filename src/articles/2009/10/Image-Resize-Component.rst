Image Resize Component
======================

by reflashed on October 19, 2009

Now it's easy to resize any jpeg, gif, or png image!


Code
~~~~

Component Class:
````````````````

::

    <?php 
    /**
     * 
     * @author		David Westfall
     * @link		http://www.reflashed.com/
     * @license		MIT
     *
     */
    class ImageResizeComponent extends Object {
    	// Resize any specified jpeg, gif, or png image
    	function resize($imagePath, $destinationWidth, $destinationHeight) {
    		// The file has to exist to be resized
    		if(file_exists($imagePath)) {
    			// Gather some info about the image
    			$imageInfo = getimagesize($imagePath);
    			
    			// Find the intial size of the image
    			$sourceWidth = $imageInfo[0];
    			$sourceHeight = $imageInfo[1];
    			
    			// Find the mime type of the image
    			$mimeType = $imageInfo['mime'];
    			
    			// Create the destination for the new image
    			$destination = imagecreatetruecolor($destinationWidth, $destinationHeight);
    
    			// Now determine what kind of image it is and resize it appropriately
    			if($mimeType == 'image/jpeg' || $mimeType == 'image/pjpeg') {
    				$source = imagecreatefromjpeg($imagePath);
    				imagecopyresampled($destination, $source, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);
    				imagejpeg($destination, $imagePath);
    			} else if($mimeType == 'image/gif') {
    				$source = imagecreatefromgif($imagePath);
    				imagecopyresampled($destination, $source, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);
    				imagegif($destination, $imagePath);
    			} else if($mimeType == 'image/png' || $mimeType == 'image/x-png') {
    				$source = imagecreatefrompng($imagePath);
    				imagecopyresampled($destination, $source, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);
    				imagepng($destination, $imagePath);
    			} else {
    				$this->_error('This image type is not supported.');
    			}
    			
    			// Free up memory
    			imagedestroy($source);
    			imagedestroy($destination);
    		} else {
    			$this->_error('The requested file does not exist.');
    		}
    	}
    	
    	// Outputs errors...
    	function _error($message) {
    		trigger_error('The file could not be resized for the following reason: ' . $message);
    	}
    }
    ?>



Installation
~~~~~~~~~~~~
Place the above script in a file named image_resize.php within your
app/controllers/components/ directory. Next, simply add 'ImageResize'
to your controller's component property.


Example
~~~~~~~

::

    <?php
    $image = WWW_ROOT . 'img/myimage.png';
    $width = 50;
    $height = 50;
    
    $this->ImageResize->resize($image, $width, $height);
    ?>


.. meta::
    :title: Image Resize Component
    :description: CakePHP Article related to image,component,resize,Components
    :keywords: image,component,resize,Components
    :copyright: Copyright 2009 reflashed
    :category: components

