Image Component for Upload and Thumbnail (phpThumb)
===================================================

by %s on August 02, 2009

This Component can be used upload images and generate thumbnails from
upload or each other files

Firstly I want to say that this is my first article and my english is
not very well, so be graciously with me :-)

This Article is about Image upload and thumbnail generation with
phpThumb (`http://phpthumb.sourceforge.net`_). I wrote this component
because I had my trouble with the other code here... In my opinion, an
image component (or what else to do this job) had to do upload and
thumbnailing and I couldn't found anything for exactly this


Requirements
~~~~~~~~~~~~

First thing you have to do is downloading a copy of phpThumb and copy
it to "/vendors/phpThumb/{files}".

Next step is to save my component code below in a file called "image"
in "/controller/components".


How to use it
~~~~~~~~~~~~~

Never mind if you just want to upload some images or only thumbnail
them, you just have to add "Image" to your components array:


Controller Class:
`````````````````

::

    <?php 
    class UploadController extends AppController {
    	public $components = array('Image');
    }
    ?>



Image upload
````````````

[bold]Notica:[/bold] remember to set the right chmod value for your
upload directory!

First thing you have to do is to set the absolute paths for your
upload and thumb directory:

::

    
    $this->Image->set_paths(WWW_ROOT . 'img/upload/', WWW_ROOT . 'img/thumb/');


Then you only have to use the "upload_image()"-Method to upload the
files from $this->data array. Just pass your Model and field (in form
of Model.field) as first param to the method.

::

    
    $this->Image->upload_image('Picture.file');


The method will return the absolute destination of the uploaded image
of everything was fine, else false.


Crop Image
``````````

Crop images is as simple as uploading them. You have to set the
"save_paths" like shown in image uploading and then only use
"thumb()"-method with the absolute path of your source image as first
param.

::

    
    $this->Image->thumb($file);


If everything was ok, the method will return the destination of the
thumbnail, else it will return a false.


Crop uploaded files
```````````````````

Their is a wrapper method to crop the last uploaded image called
"thumb_uploaded_file()". This method uses the saved destination from
"upload_image()"-method as parameter so you haven't to pass any values
to the method.


Configuration
~~~~~~~~~~~~~

Their are a few configurations values (maybe I will include more
options in future versions) you can use.


Zoom crop
`````````

[p]You can use the "set_zoom_crop()"-method to set a zoom crop value
to the component. To inform about zoom crop, please look at the
official homepage of phpThumb.


Format
``````

[p]Their are two public attributes called "width" and "height" you can
use to set the format for the generated thumbnail.

::

    
    $this->Image->width(150);
    $this->Image->width(75);



Component
~~~~~~~~~


Component Class:
````````````````

::

    <?php 
    /*
     * component to create thumbnails by phpThumb
     * 
     * @author Sebastian Bechtel <kontakt@sebastian-bechtel.info>
     * @varsion 1.0
     * @package default
     */ 
    class ImageComponent extends Object {
    	/*
    	 * @var array
    	 * @access private
    	 * 
    	 * array with allowed mime types
    	 */
    	private $allowed_mime_types = array(
    		'image/jpeg',
    		'image/pjpeg',
    		'image/png',
    		'image/gif'
    	);
    	
    	/*
    	 * @var array
    	 * @access private
    	 * 
    	 * array with allowed file extensions
    	 */
    	private $allowed_extensions = array(
    		'jpg',
    		'jpeg',
    		'png',
    		'gif'
    	);
    	
    	/*
    	 * @var string
    	 * @access private
    	 * 
    	 * save paths for thumbnail and upload image
    	 */
    	private $save_paths = array(
    		'upload' => '',
    		'thumb' => ''
    	);
    	
    	/*
    	 * @var string
    	 * @access private
    	 * 
    	 * path to file
    	 */
    	private $file_path = null;
    	
    	/*
    	 * @var int
    	 * @access public
    	 * 
    	 * thumbnail width
    	 */
    	public $width = 100;
    	
    	/*
    	 * @var int
    	 * @access public
    	 * 
    	 * thumbnail height
    	 */
    	public $height = 100;
    	
    	/*
    	 * @var mixed
    	 * @access private
    	 * 
    	 * zoom crop
    	 */
    	private $zoom_crop = 0;
    	
    	/*
    	 * @var pointer
    	 * @access private
    	 * 
    	 * object pointer for controller
    	 */
    	private $controller = null;
    	
    	/*
    	 * @var array
    	 * @access public
    	 * 
    	 * array with error messages
    	 */
    	private $errorMsg = array();
    	
    	/*
    	 * @access public
    	 * @param object pointer &$controller
    	 * 
    	 * init component with controller pointer
    	 */
    	public function startup(&$controller) {
    		$this->controller = &$controller;
    	}
    	
    	/*
    	 * @access public
    	 * @param string $upload_path
    	 * @param string $thumb_path
    	 * 
    	 * set paths for upload and thumb
    	 */
    	public function set_paths($upload_path, $thumb_path) {		
    		if(!empty($upload_path) AND is_writable($upload_path)
    			AND !empty($thumb_path) AND is_writable($thumb_path))
    				$this->save_paths = array(
    					'upload' => $upload_path,
    					'thumb' => $thumb_path 
    				);
    		else return false;
    	}
    	
    	/*
    	 * @access public
    	 * @param mixed $zoom_crop
    	 * @return boulean success
    	 * 
    	 * set zoom crop for ThumbPHP
    	 */
    	public function set_zoom_crop($zoom_crop) {
    		if(empty($zoom_crop) OR $zoom_crop === '') return false;
    		
    		/*
    		 * allowed zoom crop parameter
    		 * from actual readme.txt
    		 */
    		static $allowed_zoom_crop_param = array(
    			'T',
    			'B',
    			'L',
    			'R',
    			'TL',
    			'TR',
    			'BL',
    			'BR'
    		);
    		
    		if($zoom_crop === 1 OR $zoom_crop === 'C') $this->zoom_crop = 1;
    		elseif(extension_loaded('magickwand')
    			AND in_array($zoom_crop, $allowed_zoom_crop_param)) $this->zoom_crop = $zoom_crop;
    		else return false;
    		
    		return true;
    	}
    	
    	/*
    	 * @access public
    	 * @param string $field
    	 * @return mixed destintion or false
    	 * 
    	 * upload image from $this->controller->data array and return success
    	 * writes upload path into file_path of component
    	 */
    	public function upload_image($field) {
    		if(empty($field) OR $field === '') return false;
    		
    		// get Model and field
    		$exploded = explode('.', $field);
    		if(count($exploded) !== 2) return false;
    		
    		list($model, $value) = $exploded;
    		
    		// Image data had been send?
    		if(array_key_exists($model, $this->controller->data)
    			AND array_key_exists($value, $this->controller->data[$model])
    			AND is_array($this->controller->data[$model][$value])) {
    				// get pointer for lighter code
    				$file = &$this->controller->data[$model][$value];
    				
    				// does php get any upload errors?
    				if(array_key_exists('error', $file) AND $file['error'] === 0) {
    					/*
    					 * is the size OK?
    					 * (bigger then 0 and smaller then 'upload_max_filesize' in php.ini
    					 */
    					if($file['size'] === 0
    						OR (string)(ceil((int)$file['size']/1000000) . 'M') > ini_get('upload_max_filesize')) 
    							return  false;
    					// mimetype ok?
    					elseif(!in_array($file['type'], $this->allowed_mime_types)) 
    						return false;
    					else {
    						// get extension
    						$exploded = explode('.', $file['name']);
    						$extension = end($exploded);
    						
    						// extension allowed?
    						if(in_array($extension, $this->allowed_extensions)) {
    							// generate extension
    							$destination = $this->save_paths['upload'] . 
    								md5(microtime()) . '.' . $extension;
    							
    							// move file from temp to upload directory
    							move_uploaded_file($file['tmp_name'], $destination);
    							
    							// all OK?
    							if(file_exists($destination)) {
    								// write destination to internal file_path variable and return success
    								$this->file_path = $destination;
    								return $destination;
    							}
    						}
    						return false;
    					}
    				} else return false;
    			}
    		return false;
    	}
    	
    	/*
    	 * @access public
    	 * @return mixed thumb destination or false
    	 * 
    	 * wrapper function for $this->thumb()
    	 * uses $this->file_name from upload function as parameter
    	 */
    	public function thumb_uploaded_file() {
    		// run thumb generation method with internal filepath variable
    		return $this->thumb($this->file_path);
    	}
    	
    	/*
    	 * @access public
    	 * @param string $file
    	 * @return mixed thumb destination or false
    	 * 
    	 * generates an thumbnail from source
    	 * write the result to a file
    	 */
    	public function thumb($file) {
    		if(empty($file)
    		OR !file_exists($file)) return false;
    		
    		/*
    		 * load phpThumb from vendors directory
    		 * and get a new instance
    		 */
    		App::import('Vendor', 'phpThumb', array(
    			'file' => 'phpThumb' . DS . 'phpthumb.class.php'
    		));
    		$phpThumb = new phpThumb();
    		
    		// configure phpThumb for it's thumbnail generation
    		$phpThumb->setSourceFilename($file);
    		$phpThumb->setParameter('w', $this->width);
    		$phpThumb->setParameter('h', $this->height);
    		$phpThumb->setParameter('zc', $this->zoom_crop);
    		
    		/*
    		 * generate thumbnail
    		 * and render to file
    		 */
    		$pathinfo = pathinfo($file);
    		$destination = $this->save_paths['thumb'] . 
    			md5($pathinfo['filename'] . $this->width . $this->height . $this->zoom_crop) .
    			'.' . $pathinfo['extension'];
    		
    		/*
    		 * if their is an older version of the thumbnail
    		 * (same source, width, height, zoom-crop),
    		 * then delete
    		 */
    		if(file_exists($destination))
    			unlink($destination);
    			
    		if($phpThumb->generateThumbnail()
    			AND $phpThumb->RenderToFile($destination))
    				return $destination;
    		// something goes wrong
    		return false;
    	}
    }
    ?>



.. _http://phpthumb.sourceforge.net: http://phpthumb.sourceforge.net/
.. meta::
    :title: Image Component for Upload and Thumbnail (phpThumb)
    :description: CakePHP Article related to image,thumbnail,phpThumb,upload,Components
    :keywords: image,thumbnail,phpThumb,upload,Components
    :copyright: Copyright 2009 
    :category: components

