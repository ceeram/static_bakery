

phpThumb Helper
===============

by %s on July 29, 2008

Here's a simple phpThumb helper with built in cache support. If an
image is already cached when using this helper, it just displays the
cached image tag without having to instantiate a phpThumb object.
Note: I haven't done any sort of testing with this helper on a
production website. Only on a dev site so there's a chance some bugs
could creep in that I haven't experienced yet.


Setup
-----

Download a copy of `phpThumb`_ and place it in your Vendors directory.

Note: I renamed the phpThumb folder from "phpThumb_1.7.8" to just
"phpThumb". If you plan to keep the default folder name, just change
the import statement in the helper.


Usage
-----

Simple example

::

    <? $thumbnail->show($options, $tag_options); ?>


Verbose example

::

    
    <?
    $thumbnail->show(
    	// This is the options array for creating the thumbnail
    	array(
    	//Save path - (Required) - The path to save the thumbnail to if its not already cached
    	'save_path' => $_SERVER['DOCUMENT_ROOT'] . '/app/webroot/assets/images/thumbs',
    	//Display path - (Required) - The path to show in the image tag output
    	'display_path' => '/assets/images/thumbs', // or 'display_path' => 'http://images.domain.com',
    	//Error image path - (Required) - The image to show if something goes wrong with rendering a thumbnail
    	'error_image_path' => '/assets/images/error.jpg',
    	// From here on out, you can pass any standard phpThumb parameters
    	// Note: for phpThumb at least the src property is required.
    	'src' => '/app/webroot/assets/images/Dan.jpg', 
    	'w' => 300, 
    	'h' => 200,
    	'q' => 100,
    	'zc' => 1
    	),
    	// This is the tag options array for adding any other properties to the image tag
    	array('style' => 'border: 1px solid #000;')
    );
    ?>


Save the helper below as "thumbnail.php" in /app/views/helpers:


Helper Class:
`````````````

::

    <?php 
    App::import('Vendor', 'phpThumb', array('file' => 'phpThumb'.DS.'phpthumb.class.php'));
    
    class ThumbnailHelper extends Helper	{
    	
    	private $php_thumb;
    	private $options;
    	private $tag_options;
    	private $file_extension;
    	private $cache_filename;
    	private $error;
    	
    	private function init($options = array(), $tag_options = array())	{
    		$this->options = $options;
    		$this->tag_options = $tag_options;
    		$this->set_file_extension();
    		$this->set_cache_filename();
    		$this->error = '';
    	}
    	
    	private function set_file_extension()	{
    		$this->file_extension = substr($this->options['src'], strrpos($this->options['src'], '.'), strlen($this->options['src']));
    	}
    	
    	private function set_cache_filename()	{
    		ksort($this->options);
    		$filename_parts = array();
    		$cacheable_properties = array('src', 'new', 'w', 'h', 'wp', 'hp', 'wl', 'hl', 'ws', 'hs', 'f', 'q', 'sx', 'sy', 'sw', 'sh', 'zc', 'bc', 'bg', 'fltr');
    		foreach($this->options as $key => $value)	{
    			if(in_array($key, $cacheable_properties))	{
    				$filename_parts[$key] = $value;
    			}
    		}
    		
    		$this->cache_filename = '';
    		foreach($filename_parts as $key => $value)	{
    			$this->cache_filename .= $key . $value;
    		}
    		$this->cache_filename = $this->options['save_path'] . DS . md5($this->cache_filename) . $this->file_extension;
    	}
    	
    	private function image_is_cached()	{
    		if(is_file($this->cache_filename))	{
    			return true;
    		} else	{
    			return false;
    		}
    	}
    	
    	private function create_thumb()	{
    		$this->php_thumb = new phpThumb();
    		foreach($this->php_thumb as $var => $value) {
    			if(isset($this->options[$var]))	{
    				$this->php_thumb->setParameter($var, $this->options[$var]);
    			}
    		}
    		if($this->php_thumb->GenerateThumbnail()) {
    			$this->php_thumb->RenderToFile($this->cache_filename);
    		} else {
    			$this->error = ereg_replace("[^A-Za-z0-9\/: .]", "", $this->php_thumb->fatalerror);
    			$this->error = str_replace('phpThumb v1.7.8200709161750', '', $this->error);
    		}
    	}
    	
    	private function show_image_tag()	{
    		if($this->error != '')	{
    			$src = $this->options['error_image_path'];
    			$this->tag_options['alt'] = $this->error;
    		} else	{
    			$src = $this->options['display_path'] . '/' . substr($this->cache_filename, strrpos($this->cache_filename, DS) + 1, strlen($this->cache_filename));
    		}
    		$img_tag = '<img src="' . $src . '"';
    		if(isset($this->options['w']))	{
    			$img_tag .= ' width="' . $this->options['w'] . '"';
    		}
    		if(isset($this->options['h']))	{
    			$img_tag .= ' height="' .  $this->options['h'] . '"';
    		}
    		foreach($this->tag_options as $key => $value)	{
    			$img_tag .= ' ' . $key . '="' . $value . '"';
    		}
    		$img_tag .=  ' />';
    		
    		echo $img_tag;
    	}
    	
    	public function show($options = array(), $tag_options = array())	{
    		$this->init($options, $tag_options);
    		if($this->image_is_cached())	{
    			$this->show_image_tag();
    		} else	{
    			$this->create_thumb();
    			$this->show_image_tag();
    		}
    	}
    	
    }
    ?>



.. _phpThumb: http://phpthumb.sourceforge.net/
.. meta::
    :title: phpThumb Helper
    :description: CakePHP Article related to thumb,thumbnail,phpThumb,Helpers
    :keywords: thumb,thumbnail,phpThumb,Helpers
    :copyright: Copyright 2008 
    :category: helpers

