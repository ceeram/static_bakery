Image Magick Resize Helper
==========================

I was looking for a resizing helper and found one which wasn't exactly
what I was looking for so I tweaked it a bit.
Here is my helper based on Josh Hundley `image resize helper`_. My
helper is using ImageMagick instead of PHP GD libraries which are more
tricky to use and have some limitations.

You may add additionnal functions to the helper related to ImageMagick
tools. You can find more informations about the resize function and
other stuff at `ImageMagick documentation`_.

Helper Class:
`````````````

::

    <?php 
    class MagickConvertHelper extends Helper {
    
    	var $helpers = array('Html');
    	/**
    	* path to cache folder
    	*
    	* @var string
    	* @access public
    	*/	
    	var $cachePath = '';
    	/**
    	* path to ImageMagick convert tool
    	*
    	* @var string
    	* @access public
    	*/
    	var $convertPath = '/usr/bin/convert';
    	
    	/**
    	 * Automatically resizes an image and returns formatted img tag or only url (optional)
    	*
    	* @param string $filePath Path to the image file, relative to the webroot/ directory.
    	* @param integer $width Width of returned image
    	* @param integer $height Height of returned image
    	* @param boolean $tag Return html tag (default: true)
    	* @param boolean $cachePath Path to cache folder (default: this->cachePath)
    	* @param boolean $quality JPEG Quality (default: 100)
    	* @param boolean $aspect Maintain aspect ratio (default: true, 1 = maintain ratio, 2 = cut image to fit)
    	* @param boolean $fill Maintain aspect ratio & fill (default: false)
    	* @param array	$options Array of HTML attributes.
    	* @access public
    	*/
    	function resize($filePath, $width, $height, $tag=true, $cachePath=null, $quality=100, $aspect=true, $options = array()) {
    		$fullpath = ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.$this->themeWeb;
    		if(!$cachePath) $cachePath = $this->cachePath;
    		$url = $fullpath.$filePath;
    		
    		// Verify image exist
    		if (!($size = getimagesize($url))) {
    			return;
    		}
    
    		// Relative file (for return use)
    		$relfile = $this->webroot.$this->themeWeb.$cachePath.'/'.$width.'x'.$height.'_'.basename($filePath);
    		// Location on server (for resize use)
    		$cachefile = $fullpath.$cachePath.DS.$width.'x'.$height.'_'.basename($filePath);
    		
    		
    		// Verify if file is cached
    		$cached = false;
    		if (file_exists($cachefile)) {
    			if (@filemtime($cachefile) > @filemtime($url)) {
    				$cached = true;
    			}
    		}
    		
    		// Verify resize neccessity
    		$resize = false;
    		if (!$cached) 
    		{
    			$resize = ($size[0] > $width || $size[1] > $height) || ($size[0] < $width || $size[1] < $height);
    		}
    		
    		$jpgOptions = '';
    		if ($resize) 
    		{
    			if($aspect) 
    			{
    				// Use Image Magick build-in keep ratio option
    				if($aspect==1)
    				{
    					$resizeOption = '\'>\'';
    				}			
    				elseif($aspect==2)
    				{
    					$resizeOption = '^ -gravity center -extent '.$width.'x'.$height;
    				}
    			} else {
    				$resizeOption = '';
    			}
    			exec($this->convertPath.' -resize '.$width.'x'.$height.$resizeOption.' -quality '.$quality.$jpgOptions.' '.escapeshellarg($url).' '.escapeshellarg($cachefile).'');
    		}
    		else
    		{
    			// No resize and no cache, copy image to destination
    			if(!$cached)
    			{
    				copy($url, $cachefile);
    			}
    		}
    		if($tag){
    			return $this->Html->image($relfile, $options);
    		} else {
    			return $relfile;
    		}
    	}
    }
    ?>



.. _ImageMagick documentation: http://www.imagemagick.org/script/command-line-options.php#resize
.. _image resize helper: http://bakery.cakephp.org/articles/view/image-resize-helper

.. author:: j15e
.. categories:: articles, helpers
.. tags:: image,imagemagick,resizer,magick,Helpers

