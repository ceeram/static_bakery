Vimeo Helper
============

by jreeves on June 15, 2008

Short and Simple helper to assist with embedding Vimeo.com videos in
your CakePHP Projects.
Not much to say, once you have included this helper in your
controller, var $helpers = array('Vimeo');, simply call it in your
view with:

::

    
    <?php echo $vimeo->getEmbedHtml('http://www.vimeo.com/{vimeo_id}', array()); ?>

Please view the code below to get a complete list options which are
passed via the second argument.

::

    
    <?php
    class VimeoHelper extends AppHelper
    {
    	/**
    	 * Creates Vimeo Embed Code from a given Vimeo Video.
    	 *
    	 *	@param String $vimeo_id URL or ID of Video on Vimeo.com
    	 *	@param Array $usr_options VimeoHelper Options Array (see below)
    	 *	@return String HTML output.
    	*/
    	function getEmbedCode($vimeo_id, $usr_options = array())
    	{
    		// Default options.
    		$options = array
    		(
    			'width' => 400,
    			'height' => 225,
    			'show_title' => 1,
    			'show_byline' => 1,
    			'show_portrait' => 0,
    			'color' => '00adef',
    		);
    		$options = array_merge($options, $usr_options);
    		
    		// Extract Vimeo.id from URL.
    		if (substr($vimeo_id, 0, 21) == 'http://www.vimeo.com/') {
    			$vimeo_id = substr($vimeo_id, 21);
    		}
    		
    		$output = array();
    		$output[] = sprintf('<object width="%s" height="%s">', $options['width'], $options['height']);
    		$output[] = ' <param name="allowfullscreen" value="true" />';
    		$output[] =	' <param name="allowscriptaccess" value="always" />';
    		$output[] =	sprintf(' <param name="movie" value="http://www.vimeo.com/moogaloop.swf?clip_id=%s&server=www.vimeo.com&show_title=%s&show_byline=%s&show_portrait=%s&color=%s&fullscreen=1" />', $vimeo_id, $options['show_title'], $options['show_byline'], $options['show_portrait'], $options['color']);
    		$output[] = sprintf(' <embed src="http://www.vimeo.com/moogaloop.swf?clip_id=%s&server=www.vimeo.com&show_title=%s&show_byline=%s&show_portrait=%s&color=%s&fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="%s" height="%s"></embed>', $vimeo_id, $options['show_title'], $options['show_byline'], $options['show_portrait'], $options['color'], $options['width'], $options['height']);
    		$output[] = '</object>';
    		
    		return $this->output(implode($output, "\n"));
    	}
    }
    ?>


.. meta::
    :title: Vimeo Helper
    :description: CakePHP Article related to nice,best,good,Helpers
    :keywords: nice,best,good,Helpers
    :copyright: Copyright 2008 jreeves
    :category: helpers

