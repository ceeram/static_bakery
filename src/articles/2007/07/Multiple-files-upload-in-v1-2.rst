Multiple files upload in v1.2
=============================

by omeck on July 19, 2007

Are you wondering how to upload more than one file by CakePHP? Today I
wrote this component
/**
* v 1.1 - bug fixed - database records adding
*/


Component Class:
````````````````

::

    <?php 
    
    /**
     * FilesUploader class file.
     *
     * This file is used to handle uploaded files. Class is highly inspired by FileHandlerComponent written by Chris Partridge.
     *
     * @author Patrycjusz 'omeck' Omiotek <omeckmail@gmail.com>
     * @link http://omeck.net/
     * @license MIT
     * @version 1.1 $Date: 2006-07-19
     */
    
    class FilesUploaderComponent extends Object {
    
    	// You may choose two options array and db. When you use db, then file variables are saved into the database using dbModel.
    	private $handlerType = 'db'; 
    
    	private $count = 0;
    	
    	// Set your model here
    	private $dbModel = 'Attachment';
    	
    	// You can modify the array keys and values below in case you want save file variables into the database.
    	var $dbFields = array('dir' => 'dir', // The directory the file was uploaded to
    				'file_name'	=> 'file_name', // The file name it was saved with
    				'mime_type'	=> 'mime_type', // The mime type of the file
    				'file_size'	=> 'file_size', // The size of the file
    			  );
    
    	var $allowedMime = array( 'image/jpeg', // images
    				  'image/pjpeg', 
    				  'image/png', 
    				  'image/gif', 
    				  'image/tiff', 
    				  'image/x-tiff', 
    								  
    				  'application/pdf', // pdf
    				  'application/x-pdf', 
    				  'application/acrobat', 
    				  'text/pdf',
    				  'text/x-pdf', 
    								  
    				  'text/plain', // text
    							  
    				  'application/msword', // word
    								  
    				  'application/mspowerpoint', // powerpoint
    				  'application/powerpoint',
    				  'application/vnd.ms-powerpoint',
    				  'application/x-mspowerpoint',
    						  
    				  'application/x-msexcel', // excel
    				  'application/excel',
    				  'application/x-excel',
    								  
    				  'application/x-compressed', // compressed files
    				  'application/x-zip-compressed',
    				  'application/zip',
    				  'multipart/x-zip',
    				  'application/x-tar',
    				  'application/x-compressed',
    				  'application/x-gzip',
    				  'multipart/x-gzip'
    				 );
    
    
    		private $maxFileSize = 3584;
    
    		private $errorMsg = '';
    		private $isError = false;
    		private $lastUploadData;
    		private $logMsg = '';		// using this variable is generating upload log
    		private $dir = ''; 		// server directory where uploaded files will be save
    		public $files2upload = 0; 	// number of files which should be send
    		public $uploadedFiles = 0; 	// number of sent files
    		
    		/**
    		* constructor all written by Chris Partridge
    		*/
    		function __construct() {
    
    			if (!in_array($this->handlerType, array('db', 'array'))) {
    				$this->setError('The specified handler type is invalid.');
    			}
    
    			if ($this->handlerType == 'db') {
    				if (loadModel($this->dbModel)) {
    					// create the model 
    					$this->{$this->dbModel} = & new $this->dbModel;
    				} else {
    					$this->setError('The specified database model does not exist.');
    				}
    				
    				if (!is_subclass_of($this->{$this->dbModel}, 'AppModel')) {
    					unset($this->{$this->dbModel});
    					$this->setError('The specified database model is not a cake database model.');
    				}
    			}
    				
    			parent::__construct();
    		}
    
    		/**
    		* Method keeps errors
    		* @param $error - the error message
    		*/
    		private function setError($error) {
    			$this->isError = true;
    			$this->errorMsg = $error;			
    			$this->setLog($error);
    		}
    			
    		public function getError() {
    			if (true === $this->isError)
    				return $this->errorMsg;
    			else
    				return 'No errors';
    		}
    
    		/**
    		* Method generates upload log
    		* @param $logmsg - the log message
    		*/
    		private function setLog($logMsg) {
    			$this->logMsg .= $logMsg;
    		}
    
    		public function getLog() {
    			return $this->logMsg;
    		}
    
    		public function getMime($file) {
    			if (!function_exists('mime_content_type')) {
    				return system(trim('file -bi ' . escapeshellarg ($file)));
    			} else {
    				return mime_content_type($file);
    			}
    		}
    
    		/**
    		* If any files were uploaded returns last upload info
    		*/
    		public function getLastUploadInfo() {
    			if(!is_array($this->lastUploadData)) {
    				$this->setError('No upload detected.');
    			} else {
    				return $this->lastUploadData;
    			}
    		}
    
    		/**
    		* Like a name - method try to upload one file
    		* @param $field - name of form field
    		* @param $dir - server path where files will be save
    		*/
    		public function upload($field, $dir) {
    
    			if ($_FILES[$field]) {
    				$filesCount = sizeof($_FILES[$field]['name']);
    				$this->files2upload = $filesCount;
    
    				$logMsg = '=============== UPLOAD LOG ===============<br />';
    				$logMsg .= 'Upload folder: ' . $dir . '<br />';
    				$logMsg .= 'Files to send: ' . $filesCount . '<br />';
    				$logMsg .= '---------------------------------------------------------------<br />';
    				$this->setLog($logMsg);
    
    				for ($i = 0; $i < $filesCount; $i++) {
    					if ($this->tryUpload($field, $dir, $i)) {
    						$this->setLog('File was successfully uploaded.');
    						$this->uploadedFiles++;
    					} else {
    						$this->setError(' File wasn\'t uploaded.');
    					}
    					$this->setLog('<br /><br />');
    				}
    
    				$logMsg = '---------------------------------------------------------------';
    				$logMsg .= '<br />Files ' . $this->uploadedFiles . ' of ' . $filesCount . ' were successfully uploaded.<br /><br />';
    				$this->setLog($logMsg);
    			} else {
    				$this->setError('No files supplied.');
    			}
    
    		}
    
    		/**
    		* Method almost all written by Chris Partridge, original name: upload
    		* Handle the upload process
    		* @param $field - form field
    		* @param $dir - directory where file will be copy
    		* @param $Id - position in array
    		*/
    		private function tryUpload($field, $dir, $fileId) {
    
    			$logMsg = 'File number: ' . ($fileId + 1) . '<br />';
    			$logMsg .= 'name: ' . $_FILES[$field]['name'][$fileId] . '<br />';
    			$logMsg .= 'temporary name: ' . $_FILES[$field]['tmp_name'][$fileId] . '<br />';
    			$logMsg .= 'type: ' . $_FILES[$field]['type'][$fileId] . '<br />';
    			$logMsg .= 'error number: ' . $_FILES[$field]['error'][$fileId] . '<br />';
    			$logMsg .= 'size: ' . $_FILES[$field]['size'][$fileId] . '<br />';
    			$this->setLog($logMsg);
    
    			// Check that the two method variables are set
    			if (empty($field) || empty($dir)) {
    				$this->setError('You must supply a file field name and a directory on the server.');
    				return false;
    			}
    			
    			// Check that the upload file field exists
    			if (!isset($_FILES[$field]['name'][$fileId])) {
    				$this->setError('No file supplied.');
    				return false;
    			}
    			
    			// Check that the file upload was not errornous
    			if ($_FILES[$field]['error'][$fileId] != 0) {				
    				switch($_FILES[$field]['error'][$fileId]) {
    					case 1:
    						$this->setError('The file is too large (server).');
    					break;
    					
    					case 2:
    						$this->setError('The file is too large (form).');
    					break;
    					
    					case 3:
    						$this->setError('The file was only partially uploaded.');
    					break;
    					
    					case 4:
    						$this->setError('No file was uploaded.');
    					break;
    					
    					case 5:
    						$this->setError('The servers temporary folder is missing.');
    					break;
    					
    					case 6:
    						$this->setError('Failed to write to the temporary folder.');
    					break;
    				}
    				
    				return false;
    			}
    			
    			// Check that the supplied dir ends with a DS
    			if ($dir[(strlen($dir)-1)] != DS) {
    				$dir .= DS;
    			}
    
    			// Check that the given dir is writable
    			if (!is_dir($dir) || !is_writable($dir)) {
    				$this->setError('The supplied upload directory does not exist or is not writable.');
    				return false;
    			}
    			
    			// Check that the file is of a legal mime type
    			if (!in_array($_FILES[$field]['type'][$fileId], $this->allowedMime)) {
    				$this->setError('The file upload is of an illegal mime type.');
    				return false;
    			}
    			
    			// Check that the file is smaller than the maximum filesize.
    			if ((filesize($_FILES[$field]['tmp_name'][$fileId])/1024) > $this->maxFileSize) {
    				$this->setError('The file is too large (application).');
    				return false;
    			}
    			
    			// Get the mime type for the file
    			$mime_type = $_FILES[$field]['type'][$fileId];
    			
    			// Update the database is using db
    			if ($this->handlerType == 'db') {
    				// Create database update array
    				$file_details = array($this->dbModel => array( $this->dbFields['dir'] => $dir,
    										$this->dbFields['file_name'] => basename($_FILES[$field]['name'][$fileId]),
    										$this->dbFields['mime_type'] => $_FILES[$field]['type'][$fileId],
    										$this->dbFields['file_size'] => (filesize($_FILES[$field]['tmp_name'][$fileId])/1024)
    										)
    									 );
    				
    				// Update database, set error on failure		
    				$this->{$this->dbModel}->create();								  
    				if (!$this->{$this->dbModel}->save($file_details, false)) {
    					$this->setError('There was a database error');
    					return false;
    				} else {					
    					$this->setLog('File record added to the database.<br />');
    				}
    				
    				// Get the database id
    				$file_id = $this->{$this->dbModel}->getLastInsertId();
    				//$this->dir = $dir . $file_id . DS;
    			}
    			
    			// Generate dir name if using handler type of array or db - doesn't matter
    			if ($this->handlerType == 'array' || $this->handlerType == 'db') {
    				if ($this->dir == '')
    					$this->dir = $dir . uniqid('') . DS;		
    			}
    
    			// Check if dir exists
    			if (!is_dir($this->dir)) {
    				// Create a folder for the file, on failure delete db record and set error
    				if (!mkdir($this->dir)) {
    
    					// Remove db record if using db
    					if ($this->handlerType == 'db') {
    						$this->{$this->dbModel}->del($file_id);
    						$this->setLog('Removed file record from the database.<br />');
    					}
    				
    					// Set the error and return false
    					$this->setError('The folder for the file upload could not be created.');
    					return false;
    				}
    			}
    			
    			// Move the uploaded file to the new directory
    			if (!move_uploaded_file($_FILES[$field]['tmp_name'][$fileId], $this->dir . basename($_FILES[$field]['name'][$fileId]))) {
    				// Remove db record if using db
    				if($this->handlerType == 'db')	{
    					$this->{$this->dbModel}->del($file_id);
    					$this->setLog('Removed file record from the database.<br />');
    				}
    				
    				// Set the error and return false
    				$this->setError('The uploaded file could not be moved to the created directory');
    				return false;
    			}
    			
    			// Set the data for the lastUploadData variable
    			$this->lastUploadData = array( 'dir' => $this->dir,
    							'file_name' => basename($_FILES[$field]['name'][$fileId]),
    							'mime_type' => $mime_type,
    							'file_size' => (filesize($_FILES[$field]['tmp_name'][$fileId])/1024)
    							);
    			
    			// Add the id if using db
    			if($this->handlerType == 'db') {
    				$this->_lastUploadData['id'] = $file_id;
    			}
    			
    			// Return true
    			return true;
    		}
    	
    }
    
    ?>


.. meta::
    :title: Multiple files upload in v1.2
    :description: CakePHP Article related to multiple,upload,1.2,Components
    :keywords: multiple,upload,1.2,Components
    :copyright: Copyright 2007 omeck
    :category: components

