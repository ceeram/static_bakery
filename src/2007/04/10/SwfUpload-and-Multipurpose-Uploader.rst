SwfUpload and Multipurpose Uploader
===================================

SWFUpload is "a small javascript/flash library to get the best of both
worlds - The great upload capability of flash and the accessibility
and ease of html/css." The SwfUploadComponent is a piece of code that
you can drop into your components directory and immediately integrate
with your controllers to handle uploads from SWFUpload or even from a
generic HTML form.
A little disclaimer: I am not associated with SWFUpload or its
creators. If you have questions about the SwfUploadComponent PHP class
or integration with Cake, ask me. If you have questions about the
flash or the javascript api for SWFUpload itself, ask them.

Is SWFUpload right for me?
``````````````````````````
If you are working on a site or web application which needs the
ability to upload multiple files, and you want to show file upload
progress, few would argue that SWFUpload is one of the top libraries
out there which can meet your expectations.

Couple this great upload technology with CakePHP's component
architecture and handling file uploads has never been easier. The
component that is featured in this article can be dropped into your
components directory and you are ready to start handling uploads from
any controller.


Installation of the SWFUpload Component
```````````````````````````````````````

Get the component code
++++++++++++++++++++++
Grab this code and save it as
app/controllers/components/swf_upload.php


Component Class:
````````````````

::

    <?php 
    /* $Id$ */
    /**
     * SwfUploadComponent - A CakePHP Component to use with SWFUpload
     * Copyright (C) 2006-2007 James Revillini <james at revillini dot com>
     *
     * This program is free software; you can redistribute it and/or modify
     * it under the terms of the GNU Lesser General Public License as published by
     * the Free Software Foundation; either version 2.1 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU Lesser General Public License for more details.
     *
     * You should have received a copy of the GNU Lesser General Public License along
     * with this program; if not, write to the Free Software Foundation, Inc.,
     * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
     */
    
    /**
     * SwfUploadComponent - A CakePHP Component to use with SWFUpload
     * Thanks to Eelco Wiersma for the original explanation on handling 
     * uploads from SWFUpload in CakePHP. Also, thanks to gwoo for guidance 
     * and help refactoring for performance optimization and readability.
     * @author James Revillini http://james.revillini.com
     * @version 0.1.4
     */
     
    /**
     * @package SwfUploadComponent
     * @subpackage controllers.components
     */
    class SwfUploadComponent extends Object {
    	
    	/* component configuration */
    	var $name = 'SwfUploadComponent';
    	var $params = array();
    	var $errorCode = null;
    	var $errorMessage = null;
    	
    	// file and path configuration
    	var $uploadpath;
    	var $webpath = '/files/';
    	var $overwrite = false;
    	var $filename;
    	
    	/**
    	 * Contructor function
    	 * @param Object &$controller pointer to calling controller
    	 */
    	function startup(&$controller) {
    		// initialize members
    		$this->uploadpath = 'files' . DS;
    
    		//keep tabs on mr. controller's params
    		$this->params = $controller->params;
    
    	}
    	
    	/**
    	 * Uploads a file to location
    	 * @return boolean true if upload was successful, false otherwise.
    	 */
    	function upload() {
    		$ok = false;
    		if ($this->validate()) {
    			$this->filename = $this->params['form']['Filedata']['name'];
    			$ok = $this->write();
    		}
    		
    		if (!$ok) {
    			header("HTTP/1.0 500 Internal Server Error");	//this should tell SWFUpload what's up
    			$this->setError();	//this should tell standard form what's up
    		}
    		
    		return ($ok);
    	}
    	
    	/**
    	 * finds a unique name for the file for the current directory
    	 * @param array an array of filenames which exist in the desired upload directory
    	 * @return string a unique filename for the file
    	 */
    	function findUniqueFilename($existing_files = null) {
    		// append a digit to the end of the name
    		$filenumber = 0;
    		$filesuffix = '';
    		$fileparts = explode('.', $this->filename);
    		$fileext = '.' . array_pop($fileparts);
    		$filebase = implode('.', $fileparts);
    
    		if (is_array($existing_files)) {
    			do {
    				$newfile = $filebase . $filesuffix . $fileext;
    				$filenumber++;
    				$filesuffix = '(' . $filenumber . ')';
    			} while (in_array($newfile, $existing_files));
    		}
    		
    		return $newfile;
    	}
    
    	/**
    	 * moves the file to the desired location from the temp directory
    	 * @return boolean true if the file was successfully moved from the temporary directory to the desired destination on the filesystem
    	 */
    	function write() {
    		// Include libraries
    		if (!class_exists('Folder')) {
    			uses ('folder');
    		}
    		
    		$moved = false;
    		$folder = new Folder($this->uploadpath, true, 0755);
    		
    		if (!$folder) {
    			$this->setError(1500, 'File system save failed.', 'Could not create requested directory: ' . $this->uploadpath);
    		} else {
    			if (!$this->overwrite) {
    				$contents = $folder->ls();  //get directory contents
    				$this->filename = $this->findUniqueFilename($contents[1]);  //pass the file list as an array
    			}
    			if (!($moved = move_uploaded_file($this->params['form']['Filedata']['tmp_name'], $this->uploadpath . $this->filename))) {
    				$this->setError(1000, 'File system save failed.');
    			}
    		}
    		return $moved;
    	}
    	
    	/**
    	 * validates the post data and checks receipt of the upload
    	 * @return boolean true if post data is valid and file has been properly uploaded, false if not
    	 */
    	function validate() {
    		$post_ok = isset($this->params['form']['Filedata']);
    		$upload_error = $this->params['form']['Filedata']['error'];
    		$got_data = (is_uploaded_file($this->params['form']['Filedata']['tmp_name']));
    		
    		if (!$post_ok){
    			$this->setError(2000, 'Validation failed.', 'Expected file upload field to be named "Filedata."');
    		}
    		if ($upload_error){
    			$this->setError(2500, 'Validation failed.', $this->getUploadErrorMessage($upload_error));
    		}
    		return !$upload_error && $post_ok && $got_data;
    	}
    	
    	/**
    	 * parses file upload error code into human-readable phrase.
    	 * @param int $err PHP file upload error constant.
    	 * @return string human-readable phrase to explain issue.
    	 */
    	function getUploadErrorMessage($err) {
    		$msg = null;
    		switch ($err) {
    			case UPLOAD_ERR_OK:
    				break;
    			case UPLOAD_ERR_INI_SIZE:
    				$msg = ('The uploaded file exceeds the upload_max_filesize directive ('.ini_get('upload_max_filesize').') in php.ini.');
    				break;
    			case UPLOAD_ERR_FORM_SIZE:
    				$msg = ('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
    				break;
    			case UPLOAD_ERR_PARTIAL:
    				$msg = ('The uploaded file was only partially uploaded.');
    				break;
    			case UPLOAD_ERR_NO_FILE:
    				$msg = ('No file was uploaded.');
    				break;
    			case UPLOAD_ERR_NO_TMP_DIR:
    				$msg = ('The remote server has no temporary folder for file uploads.');
    				break;
    			case UPLOAD_ERR_CANT_WRITE:
    				$msg = ('Failed to write file to disk.');
    				break;
    			default:
    				$msg = ('Unknown File Error. Check php.ini settings.');
    		}
    		
    		return $msg;
    	}
    	
    	/**
    	 * sets an error code which can be referenced if failure is detected by controller.
    	 * note: the amount of info stored in message depends on debug level.
    	 * @param int $code a unique error number for the message and debug info
    	 * @param string $message a simple message that you would show the user
    	 * @param string $debug any specific debug info to include when debug mode > 1
    	 * @return bool true unless an error occurs
    	 */
    	function setError($code = 1, $message = 'An unknown error occured.', $debug = '') {
    		$this->errorCode = $code;
    		$this->errorMessage = $message;
    		if (DEBUG) {
    			$this->errorMessage .= $debug;
    		}
    		return true;
    	}
    }
    ?>



Get SWFUpload
+++++++++++++
Download SWFUpload from `http://swfupload.mammon.se/`_. I'd recommend
just grabbing the SWFUpload-min.zip file which is available on the
home page for use on your site. You can also download the full version
with examples to familiarize yourself with integration, but the
minified version is optimized for performance.

Please note: the following view and init script are for SWFUpload
1.0.2. See last section of this article for details.
Unzip SWFUpload-min.zip to app/webroot/js/swfupload/.

I am not going to cover integration of SWFUpload with your view files
in depth. As long as you understand the basics about views, you know
enough to follow my view file example below. Additionally, you should
read the superb documentation provided on the SWFUpload site.

I will offer this advice: use Cake's html helper to generate the 2
paths required by SWFUpload javascript object. It will ensure that
your don't hard-code something that might change as you move your
application around. The following example gives you an idea of how to
set up the view file.


View Template:
``````````````

::

    
    <?php echo $javascript->codeBlock("
    	var swfupload_url = '" . $html->url('/js/swfupload/SWFUpload.swf') . "';
    	var upload_url = '" . $html->url('/files/upload') . "';
    	var file_info_url = '" . $html->url('/files/info') . "';"); ?>
    <?php echo $javascript->link('swfupload/mmSWFUpload'); ?>
    <?php echo $javascript->link('swfupload/init'); ?>
    <div id="wrapper">
    
    	<div id="contentContainer">
    		<div id="SWFUploadTarget">
    			<form action="<?php echo $html->url('/files/upload') ?>" method="post" onsubmit="return false" enctype="multipart/form-data">
    				<?php print $html->file("Filedata"); ?>
    				
    				<input type="submit" value="Upload" />
    			</form>
    		</div>
    		<div id="SWFUploadFileListingFiles">
    		</div>
    	</div>
    	
    </div>

You may have noticed that I sourced an init.js file which did not come
with SWFUpload. That's because I wrote it. It contains my
initialization script which goes a little something like this:

::

    
    var swfu;
    var init_uploader = function() {
    	swfu = new SWFUpload({
    		upload_script : upload_url,
    		target : "SWFUploadTarget",
    		flash_path : swfupload_url
    		//many more options which you must determine for yourself - see the SWFUpload docs and examples
    	});
    	swfu.loadUI();
    };
    
    //please use better handler attachment than this:
    window.onload = init_uploader;

Now you are ready to handle uploads from SWFUpload or even from a
standard HTML form. Every application is going to do different things
with the data, so it's up to you to decide what else you need to do
after a successful upload occurs. A very simple scenario is provided
in the following section, along with the necessary code to make it
happen.


Sample Scenario and Component Usage
```````````````````````````````````
This scenario will be based on a boring Model called 'File.' File will
save the metadata (file name, size, etc.) about an uploaded file to
the database.

This scenario will also demonstrate that this component can be used
with a regular HTML file upload form.

Grab these files and put them where they belong:

Model Class:
````````````

::

    <?php 
    class File extends AppModel {
    
    	var $name = 'File';
    
    	function findByPath ($path, $name) {
    		return $this->find("name = '$name' and path = '$path'");
    	}
    
    }
    ?>



View Template:
``````````````

::

    
    <form action="<?php echo $html->url('/files/upload'); ?>" enctype="multipart/form-data" method="post">
        <?php echo $html->file('Filedata'); ?>
        <?php echo $html->submit('Upload'); ?>
    </form>



Controller Class:
`````````````````

::

    <?php 
    class FilesController extends AppController {
    	var $name = 'Files';
    	var $helpers = array('Html');
    	var $components = array('SwfUpload');
    	
    	function upload () {
    		if (isset($this->params['form']['Filedata'])) {
    			// upload the file
    			// use these to configure the upload path, web path, and overwrite settings if necessary
    			// $this->SwfUpload->uploadpath = 'files' . DS . 'subdir' . DS . $whatever . DS;
    			// $this->SwfUpload->webpath = '/files/subdir/' . $whatever . '/';
    			// $this->SwfUpload->overwrite = true;  //by default, SwfUploadComponent does NOT overwrite files
    			//
    			if ($this->SwfUpload->upload()) {
    				// save the file to the db, or do whateve ryou want to do with the data
    				$this->params['form']['Filedata']['name'] = $this->SwfUpload->filename;
    				$this->params['form']['Filedata']['path'] = $this->SwfUpload->webpath;
    				$this->params['form']['Filedata']['fspath'] = $this->SwfUpload->uploadpath . $this->SwfUpload->filename;
    				$this->data['File'] = $this->params['form']['Filedata'];
    				
    				if (!($file = $this->File->save($this->data))){
    					$this->Session->setFlash('Database save failed');
    				} else {
    					$this->Session->setFlash('File Uploaded: ' . $this->SwfUpload->filename . '; Database id is ' . $this->File->getLastInsertId() . '.');
    				}
    			} else {
    				$this->Session->setFlash($this->SwfUpload->errorMessage);
    			}
    		}
    	}
    	
    	function open($id) {
    		$file = $this->get($id);
    		if (isset($file)) {
    			$this->redirect($file['File']['path'] . $file['File']['name']);
    			exit();
    		}
    	}
    	
    	function get($id) {
    		//get file info
    		$file = $this->File->findById($id);
    		return $file;
    	}
    }
    ?>

Finally, a bit of SQL (exported from MySQL) to execute to create your
file storage table:

::

    
    CREATE TABLE `files` (
      `id` int(11) NOT NULL auto_increment,
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated` datetime NOT NULL default '0000-00-00 00:00:00',
      `path` varchar(255) NOT NULL default '',
      `name` varchar(255) NOT NULL default '',
      `fspath` varchar(255) default NULL,
      `type` varchar(255) NOT NULL default '',
      `size` int(11) NOT NULL default '0',
      `deleted` tinyint(4) NOT NULL default '0',
      `comment` text,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `path_name_idx` (`path`,`name`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
    



Closing Remarks
```````````````
I'm now reading over the article before I publish it and in
retrospect, I guess there are quite a few steps to getting this fully
integrated with your site. Still, the SwfUpload Component handles the
grunt work of file uploading gracefully, and has some nice error
reporting and debugging features.

In my sample scenario above, I am only saving the file information
itself. To get into setting up a HABTM relationship with another model
in order to associate uploads with something else (users, pages,
goats) would be a little over the top for this article. If there is a
demand, I'll gladly go over those procedures in subsequent articles.


Where's the API for the SwfUpload Component?
++++++++++++++++++++++++++++++++++++++++++++
It's in the works. As soon as it is ready, I will post it on my site:
`http://james.revillini.com`_.


Earlier Versions of SWFUpload
`````````````````````````````
Consult the documentation at the SWFUpload site for detailed setup
instructions for the SWFUpload package that you download. Below,
you'll find some code that may work for you for earlier versions.


SWFUpload 0.8.6
+++++++++++++++

View Template:
``````````````

::

    
    <script type="text/javascript">
    	var swfupload_url = '<?php echo $html->url('/js/swfupload/'); ?>';  //will be used in SWFUpload init
    	var upload_url = '<?php echo $html->url('/files/upload'); ?>' ;  //this varies depending on the controller and action which handles the upload
    </script>
    <?php echo $javascript->link('swfupload/mmSWFUpload'); ?>
    <?php echo $javascript->link('swfupload/init'); ?>
    <div id="wrapper">
    
    	<div id="contentContainer">
    		<div id="SWFUpload">
    			<form action="<?php echo $html->url('/files/upload') ?>" method="post" onsubmit="return false" enctype="multipart/form-data">
    				<?php print $html->file("Filedata"); ?>
    				
    				<input type="submit" value="Upload" />
    			</form>
    		</div>
    		<div id="filesDisplay">
    			<ul id="mmUploadFileListing"></ul>
    		</div>
    	</div>
    	
    </div>



The init.js script
;;;;;;;;;;;;;;;;;;

::

    
    function init_uploader() {
    	this.init({
    		upload_backend : upload_url,
    		target : 'SWFUpload',
    		flash_path : swfupload_url + 'SWFUpload.swf',
    		/* you will likely have many more configuration
    		 * options here.  See the docs on the SWFUpload
    		 * site for details.
    		 */
    	});
    };
    
    //please use better handler attachment than this:
    window.onload = init_uploader;



.. _http://swfupload.mammon.se/: http://swfupload.mammon.se/
.. _http://james.revillini.com: http://james.revillini.com/

.. author:: jrevillini
.. categories:: articles, components
.. tags:: flash,upload,swfupload,progresser,Components

