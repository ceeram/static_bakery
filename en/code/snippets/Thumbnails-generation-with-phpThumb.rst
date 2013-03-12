

Thumbnails generation with phpThumb
===================================

by %s on May 09, 2007

phpThumb is a great thumbnail generator, it can generate thumbs with
GD, GD2 or ImageMagick. There are many features like crop, rotate,
watermark,... see all the features on the phpThumb homepage.
- Update 04/08/2007:
Fixed cached thumbs not working, thanks to Kim Biesbjerg.
- Update 28/04/2011:
This code is totally outdated, I was new to CakePHP. I see many people
still using this.
It is easier and faster to use PHPThumb as a Helper:
`http://bakery.cakephp.org/articles/DanielMedia/2008/07/29/phpthumb-
helper-2`_
`https://github.com/DanielMedia/phpThumb-Helper`_



Download and install
````````````````````
First download phpThumb `http://phpthumb.sourceforge.net/`_ and
install it in /vendors/phpthumb/


Controller
``````````
Create a controller /app/controllers/thumbs_controller.php


Controller Class:
`````````````````

::

    <?php 
    class ThumbsController extends AppController
    {
    	var $name = 'Thumbs';
    	var $uses = null;
    	var $layout = null;
    	var $autoRender = false;
    	
    	function index()
    		{
    			if(empty($_GET['src'])){
    				die("No source image");
    			}
    			
    			//width
    			$width = (!isset($_GET['w'])) ? 100 : $_GET['w'];
    			//height
    			$height = (!isset($_GET['h'])) ? 150 : $_GET['h'];
    			//quality	
    			$quality = (!isset($_GET['q'])) ? 75 : $_GET['q'];
    			
    			$sourceFilename = WWW_ROOT.$_GET['src'];
    
    			if(is_readable($sourceFilename)){
    				vendor("phpthumb".DS."phpthumb.class");
    				$phpThumb = new phpThumb();
    
    				$phpThumb->src = $sourceFilename;
    				$phpThumb->w = $width;
    				$phpThumb->h = $height;
    				$phpThumb->q = $quality;
    				$phpThumb->config_imagemagick_path = '/usr/bin/convert';
    				$phpThumb->config_prefer_imagemagick = true;
    				$phpThumb->config_output_format = 'jpg';
    				$phpThumb->config_error_die_on_error = true;
    				$phpThumb->config_document_root = '';
    				$phpThumb->config_temp_directory = APP . 'tmp';
    				$phpThumb->config_cache_directory = CACHE.'thumbs'.DS;
    				$phpThumb->config_cache_disable_warning = true;
    				
    				$cacheFilename = md5($_SERVER['REQUEST_URI']);
    				
    				$phpThumb->cache_filename = $phpThumb->config_cache_directory.$cacheFilename;
    				
    				//Thanks to Kim Biesbjerg for his fix about cached thumbnails being regeneratd
    				if(!is_file($phpThumb->cache_filename)){ // Check if image is already cached.
    					if ($phpThumb->GenerateThumbnail()) {
    						$phpThumb->RenderToFile($phpThumb->cache_filename);
    					} else {
    						die('Failed: '.$phpThumb->error);
    					}
    				}
    			
    			if(is_file($phpThumb->cache_filename)){ // If thumb was already generated we want to use cached version
    				$cachedImage = getimagesize($phpThumb->cache_filename);
    				header('Content-Type: '.$cachedImage['mime']);
    				readfile($phpThumb->cache_filename);
    				exit;
    			}
    			
    			
    			} else { // Can't read source
    				die("Couldn't read source image ".$sourceFilename);
    			}
            }
    }
    ?>



Test app
````````
Create the folder for the thumbnails cache /app/tmp/thumbs/ (must be
writeable as other folders in tmp)

In this example, the source image is relative from the WWW_ROOT
(/app/webroot), feel free to change it.

To try the application, put a large image in the directory
/app/webroot/img/test.jpg and access to the url:
`http://www.example.com/index.php/thumbs/index?src=/img/test.jpg`_
You will see the thumbnail resized at the defaults values defined in
the controller: max width=100, max height=150.

.. _http://phpthumb.sourceforge.net/: http://phpthumb.sourceforge.net/
.. _http://www.example.com/index.php/thumbs/index?src=/img/test.jpg: http://www.example.com/index.php/thumbs/index?src=/img/test.jpg
.. _http://bakery.cakephp.org/articles/DanielMedia/2008/07/29/phpthumb-helper-2: http://bakery.cakephp.org/articles/DanielMedia/2008/07/29/phpthumb-helper-2
.. _https://github.com/DanielMedia/phpThumb-Helper: https://github.com/DanielMedia/phpThumb-Helper
.. meta::
    :title: Thumbnails generation with phpThumb
    :description: CakePHP Article related to thumb,thumbnail,phpThumb,Snippets
    :keywords: thumb,thumbnail,phpThumb,Snippets
    :copyright: Copyright 2007 
    :category: snippets

