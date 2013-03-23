Lightroom Helper
================

by %s on October 10, 2007

This is a simple helper for people who want to use the
[b]lightbox.js[/b]
This is a helper for people using lightbox.js. Lightbox.js is
available here: `http://www.huddletogether.com/projects/lightbox2/`_.

There are 2 methods in this class:

::

    
    $lightbox->img();
    $lightbox->txt();

img() is for placing images as in a picture gallery.
txt() is for placing text links to images.

here is the code:


Helper Class:
`````````````

::

    <?php 
    <?php  
    class LightboxHelper extends AppHelper { 
    	
    	var $tags = array(	'img'=>'<a href="%s" rel="lightbox[%s]" title="%s"><img src="%s" %s %s/></a>',
    						'txt'=>'<a href="%s" rel="lightbox[%s]" title="%s" %s>%s</a>');
    
    	/**
    	 * @public
    	 * 
    	 * @param	$tn:String  	a relative or absolute path to the thumbnail image
    	 * @param	$lg:String 		a relative or absolute path to the large image to be shown
    	 * @param	$title:String 	a title for the caption as well as the alt text
    	 * @param	$group:String	a group name if images are to be put into groups (see lightbox docs for more info)
    	 * @param	$tn_att:Array	an array of html attributes for the thumbnail image
    	 * 
    	 * @returns a formated <a href></a> tag
    	 */
    	function img($tn,$lg,$title,$group='',$tn_att=array()) {
    
    		$title = htmlspecialchars($title, ENT_QUOTES);
    		return $this->output(sprintf($this->tags['img'], $this->_image($lg), $group, $title, $this->_image($tn), $this->_parseAttributes($tn_att, null, '', ' '),' alt="'.$title.'"'));
    		
    	}
    	
    	/**
    	 * @public
    	 * 
    	 * @param	$txt:String  	the text for the link that goes between the <a> and the </a>
    	 * @param	$lg:String 		a relative or absolute path to the large image to be shown
    	 * @param	$title:String 	a title for the caption as well as the alt text
    	 * @param	$group:String	a group name if images are to be put into groups (see lightbox docs for more info)
    	 * @param	$class_att:Array an array of html attributes for the text link like "class"=>"myLBClass"
    	 * 
    	 * @returns	a formated <a href></a> tag 
    	 */
    	function txt($txt,$lg,$title,$group='',$class_att=array()) {
    
    		$title = htmlspecialchars($title, ENT_QUOTES);
    		return $this->output(sprintf($this->tags['txt'], $this->_image($lg), $group, $title,$this->_parseAttributes($class_att, null, '', ' ') ,$txt));
    		
    	}
    	
    	/**
    	 * @private
    	 * @param	$path:String	a string path to be parsed
    	 * @returns a url string
    	 */
    	function _image($path) {
    		if (strpos($path, '://')||$path[0]=='/') {
    			$url = $path;
    		} else {
    			$url = $this->webroot(IMAGES_URL . $path);
    		}
    		return $url;
    	}
    }
    
    ?>
    ?>

drswank


.. _http://www.huddletogether.com/projects/lightbox2/: http://www.huddletogether.com/projects/lightbox2/
.. meta::
    :title: Lightroom Helper
    :description: CakePHP Article related to image,helpers,img,lightbox.js,lightbox,Helpers
    :keywords: image,helpers,img,lightbox.js,lightbox,Helpers
    :copyright: Copyright 2007 
    :category: helpers

