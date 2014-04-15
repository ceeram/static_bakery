Multiple File Fileupload Component
==================================

by halladesign on August 17, 2009

This component allows you to upload multiple files at the same time,
and assign them to a record. You can upload into the directory or into
the database (BLOB)


Component Class:
````````````````

::

    <?php 
    /***********************************************************
     * Created on Mar 6, 2008
     * @author Martin Halla thegoodbyte@gmail.com
     * @version 2.0
     * @modified Mar 18 2009
     * TODO : change the description
     * Fileuploader component that uploads  file from the form.
     * it will have an array of allowed types  original file name
     * uploaded file name (the temporary name on the server) If it succesfully 
     * uploads the file, it will add info into  the table fileuploads
     * **********************************************************/
     
     class Fileuploader2Component extends Object {
    
     	# file names
     	var 	$m_upload_file_orig_name; 				// name of the original file
     	var 	$m_upload_file_temp_name; 				// name of the file on the server
     	private $m_upload_file_new_name = null; 		// To give a new name to the uploaded file
     	private $m_upload_file_caption 	= null;     	// caption of the uploaded file
     	
     	
     	// directory where to upload - default uploads
     	// use setUploadDir to change
     	// or specify in the uploadFiles function
     	protected $m_upload_dir = "uploads/"; 			
     	
     	
     	var $m_form_field_name;					// the name of the file input inside of the HTML form
     	
     	// Error messages - anything encountered is placed here
     	var $m_error_messages 		= array();			
     	var $m_is_error 			= false;
     	
     	var $m_controller 			= true;
     	
     	// Upload modes
     	const UPLOAD_TYPE_DIR 	= 0;
     	const UPLOAD_TYPE_DB 	= 1;
     	
     	
     	// uploaded statuses
     	// all files uploaded successfully
     	const UPLOAD_STATUS_SUCCESS = 1;
     	// some files uploaded and some failed
     	const UPLOAD_STATUS_MIX = 2;
     	// all files failed 
     	const UPLOAD_STATUS_FAIL = 3;
     	
     	
     	// this is to set where to upload = dir by default
     	protected $m_upload_type = 0;
     	
     	// this error is actually no error , it mean=s
        const UPLOAD_ERROR_NO_FILE = 4;
     	
     	// hte forign id , used as the foreign key for the related records
     	private $m_foreign_id = 0;
     	
        // since this component is used by many other tables, the foreign key could be sometimes ambigious
     	// Example : table users.id = 3, fileuploads.foreign_id = 3 and cars.id = 3 and fileuploads.foreign_id = 3 woul get the same record from the fileuploader
     	// to work around this, form_name is used as the second key
     	// Example : table users.id = 3, fileuploads.foreign_id = 3 and fileuploads.form_name = 'cars'
     	// make sure to set the 'cars' as a condition in youur model car model !!!
     	private $m_form_name;
     	
     	#Amount of file to upload
     	private $m_request_files_count		= 0;
     	
     	#Here we put name of the files that failed to upload
     	private $m_arr_upload_error_files = array();
     	
     	#Info( mime, size etc) of the uploaded files
     	public $m_arr_upload_success_files = array();
     	
     	# Array of allowed mimes - it is set through the set_allowd_mime functions
     	# since this component is loaded every time for different controller
     	public $m_arr_allowed_mime_types = array();
                     
     	
     	#maxSize of uploaded file
     	public $m_upload_file_max_size = 40000000; // 40 MB ,but check PHP INI
     	
     	# this will get the last error
     	public $m_error_number = 0;
    
    
     	
    
     	
     	/** 
     	 * dbModel 
     	 * A record about every uploaded file is inserted into DB
     	 * through this dbModel.
     	 * Is instantiated in constructor
     	 */
     	private $m_db_model = 'Fileupload';
    
     	
     	# debugging
     	const DEBUG = true;
     	
     	const DOT = '.';
    
     	
     	# ====================================================================
    	# 	C O N S T R U C T O R
    	# ====================================================================
     	/**
     	 * CONSTRUCTOR
     	 */
     	 function startup(&$controller) {
    				/* cake people say not to bring the model into the conroller
    				 * but to refer it isntead from the controller
    				 * ====old code is below ==================
    		 		if(class_exists($this->m_db_model)) {
    	 				if($this->{$this->m_db_model} = & new $this->dbModel) {
    	 					
    	 				}else {
    	 					//TODO : throw an exception
    	 				}
    		 		}else {
    		 			die("The provided class ". $this->m_db_model." does not exist");
    		 			// TODO : throw an exception
    		 		} =========================================
    		 		*/
    		 		
    		 		// new code
    		 		// todo make sure that the controller has the model !
    		 		$this->{$this->m_db_model}  = $controller->{$this->m_db_model};
    		
     	 }
    	
    	
    	# ==========================================================================
    	# Upload Files
    	# ==========================================================================
    	
    	/**
    	 * function uploadFiles
    	 * this is the main method to be called from the controller
    	 * @param string $form_upload_field_name the name of the file input field in the form used for the file upload
    	 *                    (Please note it has to end with [] in order to use the multi upload functionality).The nam you use here is without the squares brackets
    	 *                    Example : <input type ="file" name = "myupload[]" />
    	 * @param string $dest_dir the directory that will hold the field uploads (set to null) //TODO : make it automatically switch to db upload when this null
    	 * @param string $upload_file_new_name specify a new name if you want the new file be renamed on the server.Please note that is should be array for multiple records
    	 * 							otherwise it will be the same caption for all of the files
    								Do not use extension(It will be parsed from the current name).Use null for not to rename
    	 * @param string captions  Use this if you want to use nice display name for your file.It is handy when you have file like "my_2009_report_approved_for_sharing".
    	 *                         You can use the caption field in your code to display name like "2009 report".Please note that is should be array for multiple records
    	 * 							otherwise it will be the same caption for all of the files 							
    	 */
    	function uploadFiles($form_upload_field_name,$dest_dir =null,$upload_file_new_name = null,$captions = null) {
    
     		
    		if (isset($_FILES[$form_upload_field_name])) {
    			
    			// get the count of the fields
    			$fields_count = count($_FILES[$form_upload_field_name]['name']);
    			
    			# go through all the files
    			for($i = 0;$i<$fields_count;$i++) {
    				
    				// do upload only if there is something in the $_FILES array
    				// this is an easy way to find out : 
    				if($_FILES[$form_upload_field_name]['error'][$i]==0) {
    					# get he count of how many files to upload
    					$this->m_request_files_count++;
    					# do the actual upload				
    					$uploaded = $this->upload($form_upload_field_name,$dest_dir,$upload_file_new_name,$captions,$i);
    				}
    				
    				
    				
    			}
    			
    			return $this->getUploadedFilesCount();
    		}else {
    			
    			
    			$this->setErrorMessage('No Files Supplied !');
    			return 0;
    		}
    	}
    	
    	
    	
    
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	# ===========================================================
    	#
    	#    D   O       U   P   L   O   A   D     ( s i n g l e )
    	#
    	# ===========================================================
     	/**
     	 * function : upload
     	 * @param string $form_upload_field_name the name of the inout field for uploads
     	 * @param string $upload_dir the full system path to the upload dir 
     	 * @param string $upload_file_new_name optional new name of the uploaded file
     	 * @param string $upload_file_caption an oprional caption for the uplaoaded files (a display name)
     	 * The most important function - uploads and moves the files does only one
     	 * The function upload() does all of them (calls this one in a loop)
     	 **/
     	
     	private function upload($form_upload_field_name,$upload_dir=null,$upload_file_new_name = null,$upload_file_caption = null,$order_id) {
    		
    		# ====================================================
    		# Name of the FORM field that loaded the file
    		# ====================================================
     		$this->setFormUploadFieldName($form_upload_field_name);
     		
     		// this lines below are necessary only if we upload to the dir
     		if($this->getUploadType() == self::UPLOAD_TYPE_DIR) {
     		
    		 		# ==========================================
    		 		# Destination DIR => where we load the file 
    		 		# ==========================================	
    		 		if(!is_null($upload_dir)) $this->setUploadDir($upload_dir);
    		 				
    		 		
    		 		
    		 		
    		 		
    		 		# =============================================
    		 		# Check that the supplied dir ends with a DS
    		 		# =============================================
    		 		
    		        if ($this->m_upload_dir[(strlen($this->m_upload_dir)-1)] != DS) {
    		            $this->m_upload_dir .= DS;
    		        } 
     		
     		
    		 		# ====================================
    		 		# Check that the given dir is a dir
    		 		# ====================================
    		 		
    		 		  if (!is_dir($this->getUploadDirPath())) {
    		                $this->setErrorMessage('The supplied upload directory ('.$this->getUploadDir().') does not exist');
    		                return false;
    		           } 
     		
     		
    		 		# ====================================
    		 		# Check that the given dir is writable
    		 		# ====================================
    		            if (!is_writable($this->getUploadDir())) {
    		                $this->setErrorMessage('The supplied upload directory ('.$this->getUploadDir().')  is not writable.');
    		                return false;
    		            } 
     		
     		}// end of the dir exception
     		
     		
     		
     		# =========================================
     		#  No file definitions have been provided
     		# =========================================
     		if(empty($this->m_form_field_name) || empty($this->m_upload_dir)) {
     			$this->setErrorMessage('You must provide a filename and directory on the server')	;
     			
     			return false;
     		}	
     		
     		
     		/* Skipping this one	
     			
    		 # terminate in case of no uploaded file 			  
    		if(empty($_FILES[$this->getFormUploadFieldName())) {
    			$this->setErrorMessage('The file field ('.$this->file_field_name.') is empty');			
    			return false;
    		}*/
     		
     		# ====================================================
    	    # terminate if  an error occurred  while uploading  	
    	    # ====================================================			
    		if($this->hasUploadErrors($order_id)) { 					
    			$this->setErrorMessage('Upload errors ocurred');
    			// place it into the failed files
    			$this->m_arr_upload_error_files[] = $this->collectUploadedFileInfo($order_id);
    			return false;
    		}
    		
    		
    		# ====================================================
    		# GET MIME TYPES
    		# ====================================================
    	
    		
    		
    		$name = $_FILES[$this->m_form_field_name]['tmp_name'][$order_id];
    		//debug echo 'name  = '.$name.'<br>';
    		$mime = $this->getFileMimeType($name);
    		
    		
    		
    		# ====================================================
    		#  WORKAROUND IN CASE OF GET MIMETYPE RETURNS NULL 
    		# ====================================================		
    		if(is_null($mime)) {
    			$mime  = $_FILES[$this->m_form_field_name]['type'][$order_id];
    		}
    		
    		
    		# ====================================================			
    		# Get the name of the original file
    		# ====================================================
    		$this->setUploadFileOrigName($_FILES[$this->getFormUploadFieldName()]['name'][$order_id]);
    		
    		# ====================================================
    		# check it against the array of allowed mimes
    		# ====================================================
            if (!$this->isAllowedMime($mime)) {
            	
            	$allowed_types = implode(',',$this->m_arr_allowed_mime_types);
            	
            	
            	$illegal_mime = $mime;
                $this->setErrorMessage('The uploaded file '.$this->getUploadFileOrigName() .' is of an illegal mime type['.$illegal_mime.'].Allowed mimes are ('.$allowed_types.')');
                // place it into the failed files
    			$this->m_arr_upload_error_files[] = $this->collectUploadedFileInfo($order_id);
                return false;
            }
            
            
            # ====================================================
            # Check that the file is smaller than the maximum filesize.
            # ====================================================
            $file_size = filesize($_FILES[$this->getFormUploadFieldName()]['tmp_name'][$order_id]);
            if (($file_size/1024) > $this->m_upload_file_max_size) {
                $this->setErrorMessage('The file '.$this->getUploadFileOrigName() .' is too large ['.$file_size.'] (application).');
                // place it into the failed files
    			$this->m_arr_upload_error_files[] = $this->collectUploadedFileInfo($order_id);
                return false;
            } 
     			
     		
     		
    		
    		
    		
    		
    		
    		
    		# ==========================================================
    		# Get the extension of the original file
    		# ==========================================================
    		
    		//$ext = $this->get_filename_apart($_FILES[$this->getFormUploadFieldName()['name'][$fileId]);
    		$file_parts = pathinfo($_FILES[$this->getFormUploadFieldName()]['name'][$order_id]);
    		
    		# in case the file does not have extension
    		$ext = (array_key_exists('extension',$file_parts)) ? $file_parts['extension'] :null;
    		
    		
    		# ====================================================
    		# Name of the file after upload (in the TMP dir)
    		# ====================================================
    		$this->m_upload_file_temp_name =$_FILES[$this->getFormUploadFieldName()]['tmp_name'][$order_id];
    		
    		# =============================================================
    		# caption of the uploaded file
    		# =============================================================
    		if(is_array($upload_file_caption)) {
    			$this->m_upload_file_caption= $$upload_file_caption[$order_id];
    		}else {
    			$this->m_upload_file_caption= $upload_file_caption;
    		}	
    		
    		
    		
    		# ====================================================
    		#	if no new name, use the original
    		# ====================================================
    		if(is_null($upload_file_new_name)) {
    			
    			$this->setUploadFileNewName($this->getUploadFileOrigName());
    		}else {
    			#file of the new file - how we want it (since we provide only the  name, we have to add the extension)
    			$new_name = $upload_file_new_name .self::DOT.$ext;
    			
    			$this->setUploadFileNewName($new_name);
    		}
    		
    		
    		
    		# ====================================================
    		# clean the file name from white spaces (' ' => '_')
    		# ====================================================
    		$this->setUploadFileNewName($this->makeCleanFileName($this->getUploadFileNewName()));
    		
    		
    		# ====================================================
    		# get the next available file name in dir
    		# ====================================================
    		$this->setUploadFileNewName($this->makeNextFileName($this->getUploadFileNewName(),$this->getUploadDir()));
    		
    		
    		// if this is UPLOAD_DIR : 
    		if($this->getUploadType() == self::UPLOAD_TYPE_DIR) :
    					# ====================================================
    					#  Move the files  
    					# ====================================================
    					$moved = move_uploaded_file($this->m_upload_file_temp_name,$this->getUploadDirPath()   .$this->getUploadFileNewName());
    					
    					$file_info = $this->collectUploadedFileInfo($order_id);
    					if ($moved) {
    						
    						if($this->addToTable($file_info)) {
    							$this->m_arr_upload_success_files[] = $file_info;
    							return true;
    						}else {
    							// roll back
    							unlink($file_info['full_path']);
    							$this->m_arr_upload_error_files[] = $file_info;
    							
    							return false;
    						}	
    					}else {
    					    $this->setErrorMessage('There was a problem to move the file '.$this->getUploadFileOrigName() .' into the'.$this->m_upload_dir." directrory");
    						$this->m_arr_upload_error_files[] = $file_info;
    						return false;
    					}
    		// UPLOAD_DB
    		else :
    			$file_info = $this->collectUploadedFileInfo($order_id);
    			$file_info['fld_blob'] = file_get_contents($this->m_upload_file_temp_name);
    			unset($file_info['full_path']);
    			unset($file_info['dir']);
    			
    			$saved  = $this->addToTable($file_info);
    			
    			// do not show  the blob in the arrays
    			unset($file_info['fld_blob']);
    			if($saved) {
    				$this->m_arr_upload_success_files[] = $file_info;
    				return true;
    			}else {
    				$this->m_arr_upload_error_files[] = $file_info;
    				return false;
    			}
    			
    		endif;
    
     	} 
     	
     	
     	
    
     	
    
     	/** 
     	 * Sets the name of the form where we are loading the file
     	 */
    
     	
    
    
     	# ======================================================
     	# 	E R R O R S  
     	# ======================================================
     	
     	
    	# ====================================================
     	#  Set error message
     	# ================================================
     	/** 
     	 * sets the error message into an array
     	 *@param String $msg Error message description
     	 *@return void
     	 */ 
     	private function setErrorMessage($msg) {
     		$this->m_is_error= true; 		
     		$this->m_error_messages[] = $msg;
     	}
     	
     	
     	# ====================================================
     	#  Get Error Message
     	# ====================================================
    	 /**
    	  * returns  error messages in an array
    	  * */ 	
    	 public function getErrorMessages() {
    	 		return $this->m_error_messages;
    	 }
     	
     	
     	# ======================================================
     	# 	 G E T   E R R O R S   M S G   S T R I N G 
     	# ======================================================
     	/**
     	 * @return string returns all error messages as one string
     	 */
     	 function getErrorMsgString() {
     		$errorMessages = $this->getErrorMessages();
     		
     		if(is_array($errorMessages)) {
     			$errorMessages = implode($errorMessages);
     		}
    		
    		return $errorMessages;
     	}
     	
     	 /**
     	  * function getUploadFileOrigName
     	  * @return string the original name of the uplaoded file
     	  */
     	  function getUploadFileOrigName() {
     	  	return $this->m_upload_file_orig_name;
     	  }
    
    	  /** function setUploadFileOrigName
    	   * @param string the poth of the original filename
    	   */
    	  function setUploadFileOrigName($file_path) {
     	  	$this->m_upload_file_orig_name = $file_path;
     	  }
     	
    
     	 # ====================================================
     	 # get upload file new name
     	 # ====================================================
     	 /**
     	  * function : getUploadFileNewName
     	  * @return string $m_upload_file_name the new custom name of the uploaded file
     	  */
     	 public function getUploadFileNewName() {
     	 	return $this->m_upload_file_new_name;
     	 }
     	 
    
     	 
     	 # ====================================================
     	 # set upload file new name
     	 # ====================================================
     	 /**
     	  * function : setUploadFileNewName
     	  * @param string $new_name the new name for the uploaded file
     	  * @return void
     	  */
     	 public function setUploadFileNewName($new_name) {
     	 	$this->m_upload_file_new_name = $new_name;
     	 }
    
     	
     	
     	# ==========================================================================
     	# 		G E T   U P L O A D E D   F I L E   I N F O 
     	# ==========================================================================
     	/**
     	 * Function : createUploadedFileInfo
     	 * description : this function collects  all the info , such as size, mime etc, about the uploaded file
     	 * 				The collected info is then used for the success/failed files array
     	 * We need : mime type, file size,file name 
     	 **/ 	 
     	private function collectUploadedFileInfo($order_id) {
    
    		$mime_type = $this->getFileMimeType($this->getUploadFileOrigName());
    		# ====================================================
    		# WORKAROUND FOR SERVERS HAVING NO MIME TYPES FUNCTIONS INSTALLED
    		# ====================================================
    		if(is_null($mime_type)) {
    			$mime_type =$_FILES[$this->getFormUploadFieldName()  ]['type'][$order_id];
    		}
    		
    		
    		# ====================================================
    		# get the info about the uploaded file
    		# ====================================================
     		$file_types = array(
    								'mime_type' 	=> $mime_type ,// mime_content_type($this->get_system_path()),  //$_FILES[$this->getFormUploadFieldName()['type'][$fileId]),//$_FILES[$this->getFormUploadFieldName()['type'][$fileId],
     								'file_size' => $_FILES[$this->getFormUploadFieldName() ]['size'][$order_id],
     								'file_name' => $this->getUploadFileNewName(), //$_FILES[$this->getFormUploadFieldName()['name'],
     								'dir' 		=> $this->getUploadDir(),
     								'form_name' => $this->getFormName(),
     								'extension' => $this->getFileExtension($this->getUploadFileOrigName()),
     							    
     							    'caption' => $this->m_upload_file_caption, // TODO : add method
     							    'full_path' => $this->getUploadFileNewFullPath(),
    								
    								// foreign id
    								'foreign_id' => $this->getForeignId(),
    								// upload type
    								'upload_type'   => $this->getUploadType(),
    							);
     		return $file_types;						
     	} 
     	
     	
     	
     	/**
     	 * function getUploadFileNewFullPath
     	 * @return string the full path of the new file
     	 */
     	function getUploadFileNewFullPath() {
     		return $this->getWebrootPath().$this->getUploadDir().$this->getUploadFileNewName();
     	}
     	
    
     	
    	
     	
     	
     	
     	# ================================
     	# set form Name
     	# ================================
     	/**
     	 * sets the new name for the form 
     	 * @param string $name;
     	 */
     	public function setFormName($name) {
     		$this->m_form_name = $name;
     	}
     	
     	
     	# ====================================================
     	# get form name
     	# ====================================================
     	 /**
     	 * gets the name of the page where we load the file
     	 * @return string the name of the form
     	 */
     	public function getFormName() {
     		return $this->m_form_name;
     	}
     	
     	
     	
     	/**
     	 * function getFormUploadFieldname
     	 * return string the name of the file input field name
     	 */
     	function getFormUploadFieldName() {
     		return $this->m_form_field_name;
     	}
     	
     	/**
     	 * function getFormUploadFieldName
     	 * @param string $name the name of the file input field 
     	 */
     	function setFormUploadFieldName($name) {
     		$this->m_form_field_name = $name;
     	}
     	
     	
     	
     	# ====================================================
     	# checkUploadErrors
     	# ====================================================
       /**  
        * function : checkUploadErrors
        * @param integer $order_id the key for the uploaded files array
        * this function verifies that the file has 
     	* been uploaded with no errors
     	*/ 	 	
     	private function hasUploadErrors($order_id) {
     		
     		$error = $_FILES[$this->m_form_field_name]['error'][$order_id] ;
     		$file_name = $_FILES[$this->m_form_field_name]['name'][$order_id] ;
     		
     		
     		// these are no errors
     		if(($error == 0) || ($error == 4) )	{ 
     			return false;
     		}
     			
     			//////$this->last_error_number = $_FILES[$this->getFormUploadFieldName()]['error'][$order_id];
     			
    			//switch($_FILES[$this->getFormUploadFieldName()]['error'][$order_id])	{
    			switch($error) {
    				case 1:
    					$this->setErrorMessage('The file '.$file_name.' is too large (server).');
    					break;
    				case 2:
    					$this->setErrorMessage('The file '.$file_name.' is too large (form).');
    					break;
    				case 3:
    					$this->setErrorMessage('The file '.$file_name.' was only partially uploaded.');
    					break;
    				case 4:
    					$this->setErrorMessage('No file was uploaded.');
    					
    					break;
    				case 5:
    					$this->setErrorMessage('The servers temporary folder is missing.');
    					break;
    				case 6:
    					$this->setErrorMessage('Failed to write to the temporary folder.');
    					break;
    				
    				default:
    					$this->setErrorMessage('Unknown Upload error');
    					break;	
    			}
    					
    				return true;
    		
     	}
     	
     	
     	/** function setUploadType
     	 * @param integer $type the upload mode, 0 for directory, 1 for database
     	 * @return void;
     	 */
     	function setUploadType($type) {
     		
     		 $this->m_upload_type = $type;
     		 
     	}
     	
     	/** 
     	 * function getUploadType
     	 * @return integer the upload type, 0 for directory, 1 for database
     	 */
     	function getUploadType() {
     		return $this->m_upload_type;
     	}
     	
     	
     	# ====================================================
     	# Set Upload Dir
     	# ====================================================
     	/** 
     	 * function : set upload dir
     	 * @param string $upload_dir ful system path where to upload the files
     	 * @retun void
     	 * 
     	 */
     	public function setUploadDir($upload_dir) {
     		$this->m_upload_dir = $upload_dir;
     	}
     	
     	
     	
     	
     	/** function setForeignId
     	 * description sets the foreign id
     	 * @param integer the foreign id of the record
     	 * @return void
     	 */
     	function setForeignId($id) {
     		$this->m_foreign_id = $id;
     	}
     	
     	
     	/** function getForeignId
     	 * @return integer the foreign id of the related record
     	 */
     	function getForeignId() {
     		return $this->m_foreign_id;
     	}
     	
     	
     	
     	# ====================================================
     	# Get upload dir
     	# ====================================================
     	/**
     	 * function : get_upload_dir
     	 * @param void
     	 * return string the full system path to the upload directory
     	 * example /var/www/uploads
     	 */
     	public function getUploadDir() {
     		return $this->m_upload_dir;
     	}
     	
     	
     	function getUploadDirPath() {
     		$path =  $this->getWebrootPath().$this->getUploadDir();
     		
     		return $path;
     	}
      
       /**
        * function getWebrootPath
        * @return  string the full system path to cake's webroot directory
        * example /var/www/websites/my_project/app/webroot/
        */
        function getWebrootPath() {
        	return WWW_ROOT;
        }
        
        
        /**
         * function : getFulluploadPath
         * @return string the full system path to the upload directory
         * example : /var/www/websites/my_project/app/webroot/uploads
         */
        function getFullUploadDirPath() {
        	$the_path = $this->getWebrootPath().$this->getUploadDir();
        	return $the_path;
        }
    
     	 
     	 
     	
     	 
     	 
     	 
     	 # ==================================================
     	 # INSERT UPLOADED FILES INFO INTO FILEUPLOAD TABLE
     	 # ==================================================
     	 /**function  addToTable
     	  * description L: goes through all of the success upload files and adds them to the fileuploads table
     	  * @param Integer $id Id of the parent record this record is associated to(lets say id of business logo)
     	  * Another Description
     	  */
     	 public function addAllToTable() {
     	 	
     	 	
     	 	$count = $this->getUploadedFilesCount();
     	 	
     	 	$success_counter = 0;
    
    		for($i = 0;$i<$count;$i++) {
    			
    			$file_info = $this->getUploadedFileInfoArray($i);
    			/*
    			$file_info = array();
    			
    			$success_files = $this->m_arr_upload_success_files[$i];
    			$file_nfo['foreign_id'] = $id;
    			$file_nfo['mime_type'] = $success_files['mime_type'];
     			$file_nfo['file_size'] = $success_files['file_size'];
     			$file_nfo['file_name'] = $success_files['file_name'];
     			$file_nfo['dir'] 		= $success_files['dir'];
     			$file_nfo['form_name'] = $success_files['form_name'];
     			$file_nfo['extension'] = $success_files['extension'];
     			$file_nfo['form_name'] = $form_name;
     			$file_nfo['caption']   = $success_files['caption'];
     			$file_nfo['full_path']   = $success_files['full_path'];
     			// the upload type
     			$file_nfo['upload_type']   = $this->getUploadType();
     			
     			$success_files = null;
     			*/
    
     			
     			#set id to null so cake will add instead of update
     			$this->{$this->m_db_model}->id = null;
    			if($this->{$this->m_db_model}->save($file_info)) $success_counter++;
    			
    		}
     	 	# if all have been saved return true
     	 	
     	 	return $count == $success_counter;
     	 	}
     	 	
     	 	
     	 	
     	 	
     	 	private function addToTable($data) {
     	 		$this->{$this->m_db_model}->id = null;
    			$saved = $this->{$this->m_db_model}->save($data);
    			return $saved;
     	 	}
     	 
     	 
     	 /** function getUploadFileInfoArray
     	  * @param integer $index the index of the record in the sucess upload files array
     	  * @return array the array including the info about the uploaded file. This array is used for updating the fileuploads table. 
     	  **/
     	 function getUploadedFileInfoArray($index) {
     	 		$file_info = array();
    			$success_files = $this->m_arr_upload_success_files[$index];
    			return $success_files;
     	 }
     	 
     	 
     	 
    
     	 
     	 
     	 
     	 
     	 /**
     	  * function getFileExtension
     	  * description : a convenient function to get the file's extension
     	  * @param string path to  the file, example : /var/www/my_img.jpg
     	  * @return string the extension of the file , example : jpg
     	  */
     	 function getFileExtension($file_path) {
     	 	$parts = pathinfo($file_path);
     	 	
     	 	return isset($parts['extension']) ?$parts['extension'] : null;
     	 }
     	 
     	 
     	 # ==================================================
     	 #      G E T   M I M E   T Y P E 
     	 # ==================================================
     	 /**
     	  * function getFileMimeType
     	  * @param string $file_name the name of the file to be checked
     	  */
     	  // TODO : fix the PECL !!!!
     	 function getFileMimeType($file_name) {
     	 	  $mime = null;
    		  
    		  if(class_exists('finfo')) {
    	     	 	 return $this->getMimePecl($file_name);
    	      	    //$mime_ = $fi->buffer(file_get_contents($filename));
    		  }
    		  if (function_exists('mime_content_type')) {
    		  	return mime_content_type($file_name);
    		  }	
    		  
    		 
    		  $mime = $_FILES[$this->getFormUploadFieldName()]['type'];
    		  
     	 }
     	 
    	/**
    	 * function getMimePecl
    	 * @param string $file_name
    	 * @return string the mime type of the file
    	 */ 	 
    	private function getMimePecl($file_name) {
    					$mime = null;
    		     	 	$finfo_db = "/usr/share/misc/magic";
    				   	$finfo = new finfo(FILEINFO_MIME,$finfo_db ); // return mime type ala mimetype extension
    		
    					if (!$finfo) {
    					   
    					    $this->setErrorMessage("Opening fileinfo database file '$finfo_db' failed");
    					}
    				   	$mime =  $finfo->file($file_name); 
    	}
    
    
     	 
     	 
     	 
     	 
     	 # ==================================================
     	 # 		C H E C K   A L L O W E D   M I M E 
     	 # ==================================================
     	 /**
     	  * function isAllowedMime
     	  * checks if the file's mime is allowed
     	  * @param string $mime the mime to check (Examople "image/jpeg")
     	  * @return boolean returns true if mime is in the array of allowed mimes
     	  * 
     	  */
     	 function isAllowedMime($mime) {
     	 	// you can pass ALL MIMES to the allowed mimes to skip this check
     	 	if(in_array("ALL",$this->m_arr_allowed_mime_types)) {
     	 		return true;
     	 	}
     	 	if(!in_array($mime,$this->m_arr_allowed_mime_types)) {
     	 		return false;
     	 	}else {
     	 		return true;
     	 	}	
     	 }
     	 
     	 
     	
     	 
     	 # ============================================
     	 #	M a k e   N e x t   F i l e      N a m e
     	 # ============================================
     	 /** Function makeNextFileName
     	  *  Finds next available name.For example.We want 
     	 * 	to add a file into  directory but the file with the name already exists
     	 *	This function adds number to the file name
     	 *   Example : image.jpg to be added
     	 *             image.jpg already exists
     	 * 			   next file : image1.jpg
     	 *  @param string $file_name the full path of the file in question
     	 *  @param string $dir_to_check the directory where the file will be uploaded
     	 *  @return string the file name with an numeric increment
     	 * 
     	 * How to use :
     	 * (Make sure you have the model Fileupload !)
     	 * 
     	 * in the view :
     	 * 1) make sure that the form enctype is set to multipart/form-data
     	 * Example : <form name ="myuplad" enctype="multipart/form-data" method="post" >
     	 * 2) Make sure the inpu file element's name includes the square brackers 
     	 * Example : <input type = "file" name ="upload_field[]" />
     	 * The brackets are important as they allow the multipart file uploads
     	 * 
     	 * In your controller
     	 * 1) // set the mime types
     	 * Example : $allowed_mimes = array("image/jpeg","image/gif");
     	 * $this->Fileuploader2->setAllowedMimes($allowed_mimes);
     	 * (You can also user magic word ALL to allow all) Example : $allowed_mimes = array("ALL");
     	 * 2) Set the upload type :
     	 * $this->Fileuploader2->setUploadType(Fileuploader2Component::UPLOAD_DIR) // you can use DB too UPLOAD_DB
     	 * 3) If using upload to dir, set the upload directory
     	 *  ( we use the app webroot by default, so all you need to  specify only the one from there, with no starting dash ("/"))
     	 *  Example : $this->setUploadDir("uploads");
     	 * 4) Set the forign_id key - it is used to relate the records in the tables
     	 *  you migh be for example updating record $id = 23 in the "profiles" and  want the uploaded records to be associated with the record,
     	 *  you then set the the foreign_id to 23
     	 * Example : $this->Fileuploader2->setForeignId(23);
     	 * note : you can also set an additional parameter : form
     	 * It is used in the where clause and identifies the related table that wil use the foriegn key
     	 * (Sometimes you have two same foreign keys - to distinguish between them , you set the form nome to the name of the model that will call it)
     	 * 5) cal the fileuploader's uploadFile method to upload the files.
     	 * It takes a required argument - the name of the <input tag> used for uploading the files
     	 *  we have used "upload_field[]" above
     	 * the name of the uploaded field will be : "upload_field" , no swuare brackets [] !!!!
     	 * Example : $this->Fileuploader2->uploadFiles("upload_field");
     	 * 
     	 * To get the result statistics, call the getResult function (returns array) :
     	 * $this->Fileuploader2->gerResult();
     	 * or
     	 * call the function called getResultString (returns nicely formatted string) :
     	 * $this->Fileuploader2->getResultString();
     	 * you can also get all uploaded files info here :
     	 * $this->Fileuploader2->getLastUploadData();
     	 * and the failed ones :
     	 * $this->fileuploader2->getUploadFailedFiles();
     	 * 
     	 **/
     	  function makeNextFileName($file_name,$dir_to_check) {  	  	
    		
    		####pr($file_name);
    		
    		$dir_to_check = $this->getWebrootPath().$dir_to_check;
    		#####pr("dir to check ".$dir_to_check);
    		# ========================
    		# construct the file name
    		# ========================
    		
    		
    		# ========================
    		# get the file info
    		# ========================
    		$fileInfo = pathinfo($file_name);
    		
    		
    		# in case the file has no extension
    		if(!array_key_exists('extension',$fileInfo)) $fileInfo['extension'] = 'NULL';
    		
    		
     	 	# test wether the file exists, if does, 
     	 	# add next available number to its name
     		$counter=0;
     		
     		
     		
     		$string_length	 	= strlen($fileInfo['filename']); // get the name of the string
     		$last_two_chars 	= substr($fileInfo['filename'],$string_length -2,2); // get hte last two characters (to learn if they are numeric)
     		$last_char 			= substr($last_two_chars,1,1); // get the last character
     		
     		while(file_exists($dir_to_check. $file_name)){
     			$counter++;
     			
     			#add a number to the file name(not the  extension and try again)
     			# [/var/something/]   [name1]   . [jpg]
     			
     			# lets go fancy and add a number
     			# if the file ends with a number
     			
     			
     			
     			
    
     			// for example ferrari17
     			if(is_numeric($last_two_chars)) {
     				$counter=    ++$last_two_chars;
     				
     				$substring 		= substr($fileInfo['filename'],0,$string_length-2);
     				$file_name   	=	$substring .$counter.self::DOT.$fileInfo['extension'];
     				
    
     			// for example ferrari7	
     			}else if(is_numeric($last_char)) {
     				$counter = ++$last_char;
     				
     				$substring 		= substr($fileInfo['filename'],0,$string_length-1);
     				$file_name   	= $substring	.$counter.self::DOT.$fileInfo['extension'];
    
     			// for example ferrari	
     			}else {
    
     				$file_name   	=	$fileInfo['filename'].$counter.self::DOT.$fileInfo['extension'];
     			}
    
     		} 	 	
    
     	 	return $file_name;
     	 	
     	 }
     	 
     	 
     	 # ============================================
     	 #	M a k e   F i l e   C l e a n   N a m e
     	 # ============================================
     	 // todo : add more parameters in an array
     	 // todo : remove slashes
     	 /** function makeCleanFileName
     	  *  Replaces white spaces
     	  * @param string $name the filename to be checked
     	  * @return string the new clean name
     	  */ 
     	 function makeCleanFileName($name) { 	  	 	 	 	
     	 	# change white spaces & add the prefix
     	 	$name = eregi_replace(" ","_",(strtolower($name)));
     	 	$find  = array("'","__");
     	 	$replace = array("","_");
     	 	$name = str_replace($find,$replace,$name);
     	 	return $name;
     	 	
     	 }
     	 
     	 
     	 # ============================================
     	 #	SET ALLOWED MIME TYPES
     	 # ============================================
     	 # Sets allowed mime types
     	 /** function setAllowedMimes
     	  *  @param array mimes the array of the mimes
     	  *  @return void
     	  *  sets the allowed mimes 
     	  */
     	 function setAllowedMimes(array $mimes) { 	 	
     	 	$this->m_arr_allowed_mime_types = $mimes;
     	 }
    
    
    
    
     	 
     	 
     	 
     	 
     	 
     	 # ====================================================
     	 # returns array of files that failed
     	 # ====================================================
     	 /** 
     	  * function getUploadFailFiles
     	  * @return array array of the failed files
     	  */
     	 function getUploadFailedFiles() {
     	 	return $this->m_arr_upload_error_files  ;
     	 }
     	 
    
     	 
     	 
     	 # ====================================================
     	 # returns info about uploaded files in an array
     	 # ====================================================
     	 
     	 /** 
     	  * function getLastUploadData
     	  * @return array array of  successfully uploaded files
     	  */
     	 public function getLastUploadData() {
     	 	return $this->m_arr_upload_success_files;
     	 }
     	 
     	 /**
     	  * function name : getUploadedFilesCount
     	  * @return integer the count of uploaded files
     	  */
     	 function getUploadedFilesCount() {
     	 	$count = count($this->m_arr_upload_success_files);
     	 	return $count;
     	 }
     	 
     	 
     	 
     	 /**
     	  * function name : getFailedFilesCount
     	  * @return integer the count of failed files
     	  */
     	 function getFailedFilesCount()  {
     	 	$count = count($this->m_arr_upload_error_files);
     	 	return $count;
     	 }
     	 
     	 
     	 /** function getResult 
     	  * description gets the results about the uploaded files in a nice array
     	  * @return array the array with the count of requested , uploaded and failed fieles
     	  * 
     	  * */
     	 function getResult () {
     	 	$result = array(
    						"upload_files"
     	 							=>array(
     	 									"requested"	=>	$this->m_request_files_count,
     	 									"uploaded" 	=>	$this->getUploadedFilesCount(),
     	 									"failed" 	=> 	$this->getFailedFilesCount()
     	 									),
     	 				    "upload_type" =>$this->getUploadType()					
     	 	);
     	 	return $result;
     	 }
     	 
     	 
     	 function getStatus() {
     	 	$result = $this->getResult();
     	 	// success
     	 	if($result['upload_files']['requested'] == $result['upload_files']['uploaded']) {
     	 		return self::UPLOAD_STATUS_SUCCESS;
     	 	}
     	 	// failure
     	 	if($result['upload_files']['requested'] == $result['upload_files']['failed']) {
     	 		return self::UPLOAD_STATUS_FAIL;
     	 	}
     	 	// Mix
     	 	
     	 		return self::UPLOAD_STATUS_MIX;
     	 	
     	 }
     	 
     	 
     	 function getResultString() {
     	 	$result = $this->getResult();
     	 	$upload_types = array(
     	 		self::UPLOAD_TYPE_DIR=>"Directory",
     	 		self::UPLOAD_TYPE_DB =>"Database",
     	 	);
     	 	
     	 	$string = "From the total of ".$result['upload_files']['requested']." requested files to be loaded into ". $upload_types[$result['upload_type']].", ";
     	 	$string .= $result['upload_files']['uploaded']." were uploaded , ";
     	 	$string .= $result['upload_files']['failed']." failed ";
     	 	
     	 	return $string;
     	 }
     	 
     	 
     	
     	# ====================================================
     	# Clear the object
     	# ====================================================
     	
     	/** function clear
     	 * @return void
     	 * description : resets the fields
     	 */
     	public function clear() {
     		$this->m_error_messages 			= null;
     		$this->m_is_error 					= false;
     		$this->m_upload_file_new_name 		= null; 		
     		$this->m_form_name  				= null;
     		$this->m_request_files_count 		= 0;
     		$this->m_arr_upload_error_files 	= null;
     		$this->m_arr_upload_success_files 	= null;
    				
    		# =========
    		$this->m_upload_file_orig_name 		= null; 
    		$this->m_upload_file_temp_name		= null; 		
    		$this->m_upload_dir 				= null; 		
    		$this->m_form_field_name			= null;	 		
    			  	
    		
    
     	}
     	
    
     	
     	
     	
     	
    
     	
     	/**
     	 * this is here just for the reference
     	 * maybe one day we will make anything out of it ...
     	 */
     	function listAllMimes($mime) {
     		/*$all_mimes = array(
     		".3dm" => 	"x-world/x-3dmf"
    ".3dmf " => 	"	x-world/x-3dmf"
    ".a " => 	"	application/octet-stream"
    ".aab " => 	"	application/x-authorware-bin"
    ".aam " => 	"	application/x-authorware-map
    ".aas " => 	"	application/x-authorware-seg
    ".abc " => 	"	text/vnd.abc
    ".acgi " => 	"	text/html
    ".afl " => 	"	video/animaflex
    ".ai " => 	"	application/postscript
    ".aif " => 	"	audio/aiff
    ".aif " => 	"	audio/x-aiff
    ".aifc " => 	"	audio/aiff
    ".aifc " => 	"	audio/x-aiff
    ".aiff " => 	"	audio/aiff
    ".aiff " => 	"	audio/x-aiff
    .aim"=> 	application/x-aim
    .aip"=> 	text/x-audiosoft-intra
    .ani"=> 	application/x-navi-animation
    .aos"=> 	application/x-nokia-9000-communicator-add-on-software
    .aps"=> 	application/mime
    .arc"=> 	application/octet-stream
    .arj"=> 	application/arj
    .arj"=> 	application/octet-stream
    .art"=> 	image/x-jg
    .asf"=> 	video/x-ms-asf
    .asm"=> 	text/x-asm
    .asp"=> 	text/asp
    .asx"=> 	application/x-mplayer2
    .asx"=> 	video/x-ms-asf
    .asx"=> 	video/x-ms-asf-plugin
    .au"=> 	audio/basic
    .au"=> 	audio/x-au
    .avi"=> 	application/x-troff-msvideo
    .avi"=> 	video/avi
    .avi"=> 	video/msvideo
    .avi"=> 	video/x-msvideo
    .avs"=> 	video/avs-video
    .bcpio"=> 	application/x-bcpio
    .bin"=> 	application/mac-binary
    .bin"=> 	application/macbinary
    .bin"=> 	application/octet-stream
    .bin"=> 	application/x-binary
    .bin"=> 	application/x-macbinary
    .bm"=> 	image/bmp
    .bmp"=> 	image/bmp
    .bmp"=> 	image/x-windows-bmp
    .boo"=> 	application/book
    .book"=> 	application/book
    .boz"=> 	application/x-bzip2
    .bsh"=> 	application/x-bsh
    .bz"=> 	application/x-bzip
    .bz2"=> 	application/x-bzip2
    .c"=> 	text/plain
    .c"=> 	text/x-c
    .c++"=> 	text/plain
    .cat"=> 	application/vnd.ms-pki.seccat
    .cc"=> 	text/plain
    .cc"=> 	text/x-c
    .ccad"=> 	application/clariscad
    .cco"=> 	application/x-cocoa
    .cdf"=> 	application/cdf
    .cdf"=> 	application/x-cdf
    .cdf"=> 	application/x-netcdf
    .cer"=> 	application/pkix-cert
    .cer"=> 	application/x-x509-ca-cert
    .cha"=> 	application/x-chat
    .chat"=> 	application/x-chat
    .class"=> 	application/java
    .class"=> 	application/java-byte-code
    .class"=> 	application/x-java-class
    .com"=> 	application/octet-stream
    .com"=> 	text/plain
    .con"=>f 	text/plain
    .cpio"=> 	application/x-cpio
    .cpp"=> 	text/x-c
    .cpt"=> 	application/mac-compactpro
    .cpt"=> 	application/x-compactpro
    .cpt"=> 	application/x-cpt
    .crl"=> 	application/pkcs-crl
    .crl"=> 	application/pkix-crl
    .crt"=> 	application/pkix-cert
    .crt"=> 	application/x-x509-ca-cert
    .crt"=> 	application/x-x509-user-cert
    .csh"=> 	application/x-csh
    .csh"=> 	text/x-script.csh
    .css"=> 	application/x-pointplus
    .css"=> 	text/css
    .cxx"=> 	text/plain
    .dcr"=> 	application/x-director
    .deepv"=> 	application/x-deepv
    .def"=> 	text/plain
    .der"=> 	application/x-x509-ca-cert
    .dif"=> 	video/x-dv
    .dir"=> 	application/x-director
    .dl"=> 	video/dl
    .dl"=> 	video/x-dl
    .doc"=> 	application/msword
    .dot"=> 	application/msword
    .dp"=> 	application/commonground
    .drw"=> 	application/drafting
    .dump"=> 	application/octet-stream
    .dv"=> 	video/x-dv
    .dvi"=> 	application/x-dvi
    .dwf 	drawing/x-dwf (old)
    .dwf 	model/vnd.dwf
    .dwg 	application/acad
    .dwg 	image/vnd.dwg
    .dwg 	image/x-dwg
    .dxf 	application/dxf
    .dxf 	image/vnd.dwg
    .dxf 	image/x-dwg
    .dxr 	application/x-director
    .el 	text/x-script.elisp
    .elc 	application/x-bytecode.elisp (compiled elisp)
    .elc 	application/x-elc
    .env 	application/x-envoy
    .eps 	application/postscript
    .es 	application/x-esrehber
    .etx 	text/x-setext
    .evy 	application/envoy
    .evy 	application/x-envoy
    .exe 	application/octet-stream
    .f 	text/plain
    .f 	text/x-fortran
    .f77 	text/x-fortran
    .f90 	text/plain
    .f90 	text/x-fortran
    .fdf 	application/vnd.fdf
    .fif 	application/fractals
    .fif 	image/fif
    .fli 	video/fli
    .fli 	video/x-fli
    .flo 	image/florian
    .flx 	text/vnd.fmi.flexstor
    .fmf 	video/x-atomic3d-feature
    .for 	text/plain
    .for 	text/x-fortran
    .fpx 	image/vnd.fpx
    .fpx 	image/vnd.net-fpx
    .frl 	application/freeloader
    .funk 	audio/make
    .g 	text/plain
    .g3 	image/g3fax
    .gif 	image/gif
    .gl 	video/gl
    .gl 	video/x-gl
    .gsd 	audio/x-gsm
    .gsm 	audio/x-gsm
    .gsp 	application/x-gsp
    .gss 	application/x-gss
    .gtar 	application/x-gtar
    .gz 	application/x-compressed
    .gz 	application/x-gzip
    .gzip 	application/x-gzip
    .gzip 	multipart/x-gzip
    .h 	text/plain
    .h 	text/x-h
    .hdf 	application/x-hdf
    .help 	application/x-helpfile
    .hgl 	application/vnd.hp-hpgl
    .hh 	text/plain
    .hh 	text/x-h
    .hlb 	text/x-script
    .hlp 	application/hlp
    .hlp 	application/x-helpfile
    .hlp 	application/x-winhelp
    .hpg 	application/vnd.hp-hpgl
    .hpgl 	application/vnd.hp-hpgl
    .hqx 	application/binhex
    .hqx 	application/binhex4
    .hqx 	application/mac-binhex
    .hqx 	application/mac-binhex40
    .hqx 	application/x-binhex40
    .hqx 	application/x-mac-binhex40
    .hta 	application/hta
    .htc 	text/x-component
    .htm 	text/html
    .html 	text/html
    .htmls 	text/html
    .htt 	text/webviewhtml
    .htx 	text/html
    .ice 	x-conference/x-cooltalk
    .ico 	image/x-icon
    .idc 	text/plain
    .ief 	image/ief
    .iefs 	image/ief
    .iges 	application/iges
    .iges 	model/iges
    .igs 	application/iges
    .igs 	model/iges
    .ima 	application/x-ima
    .imap 	application/x-httpd-imap
    .inf 	application/inf
    .ins 	application/x-internett-signup
    .ip 	application/x-ip2
    .isu 	video/x-isvideo
    .it 	audio/it
    .iv 	application/x-inventor
    .ivr 	i-world/i-vrml
    .ivy 	application/x-livescreen
    .jam 	audio/x-jam
    .jav 	text/plain
    .jav 	text/x-java-source
    .java 	text/plain
    .java 	text/x-java-source
    .jcm 	application/x-java-commerce
    .jfif 	image/jpeg
    .jfif 	image/pjpeg
    .jfif-tbnl 	image/jpeg
    .jpe 	image/jpeg
    .jpe 	image/pjpeg
    .jpeg 	image/jpeg
    .jpeg 	image/pjpeg
    .jpg 	image/jpeg
    .jpg 	image/pjpeg
    .jps 	image/x-jps
    .js 	application/x-javascript
    .jut 	image/jutvision
    .kar 	audio/midi
    .kar 	music/x-karaoke
    .ksh 	application/x-ksh
    .ksh 	text/x-script.ksh
    .la 	audio/nspaudio
    .la 	audio/x-nspaudio
    .lam 	audio/x-liveaudio
    .latex 	application/x-latex
    .lha 	application/lha
    .lha 	application/octet-stream
    .lha 	application/x-lha
    .lhx 	application/octet-stream
    .list 	text/plain
    .lma 	audio/nspaudio
    .lma 	audio/x-nspaudio
    .log 	text/plain
    .lsp 	application/x-lisp
    .lsp 	text/x-script.lisp
    .lst 	text/plain
    .lsx 	text/x-la-asf
    .ltx 	application/x-latex
    .lzh 	application/octet-stream
    .lzh 	application/x-lzh
    .lzx 	application/lzx
    .lzx 	application/octet-stream
    .lzx 	application/x-lzx
    .m 	text/plain
    .m 	text/x-m
    .m1v 	video/mpeg
    .m2a 	audio/mpeg
    .m2v 	video/mpeg
    .m3u 	audio/x-mpequrl
    .man 	application/x-troff-man
    .map 	application/x-navimap
    .mar 	text/plain
    .mbd 	application/mbedlet
    .mc$ 	application/x-magic-cap-package-1.0
    .mcd 	application/mcad
    .mcd 	application/x-mathcad
    .mcf 	image/vasa
    .mcf 	text/mcf
    .mcp 	application/netmc
    .me 	application/x-troff-me
    .mht 	message/rfc822
    .mhtml 	message/rfc822
    .mid 	application/x-midi
    .mid 	audio/midi
    .mid 	audio/x-mid
    .mid 	audio/x-midi
    .mid 	music/crescendo
    .mid 	x-music/x-midi
    .midi 	application/x-midi
    .midi 	audio/midi
    .midi 	audio/x-mid
    .midi 	audio/x-midi
    .midi 	music/crescendo
    .midi 	x-music/x-midi
    .mif 	application/x-frame
    .mif 	application/x-mif
    .mime 	message/rfc822
    .mime 	www/mime
    .mjf 	audio/x-vnd.audioexplosion.mjuicemediafile
    .mjpg 	video/x-motion-jpeg
    .mm 	application/base64
    .mm 	application/x-meme
    .mme 	application/base64
    .mod 	audio/mod
    .mod 	audio/x-mod
    .moov 	video/quicktime
    .mov 	video/quicktime
    .movie 	video/x-sgi-movie
    .mp2 	audio/mpeg
    .mp2 	audio/x-mpeg
    .mp2 	video/mpeg
    .mp2 	video/x-mpeg
    .mp2 	video/x-mpeq2a
    .mp3 	audio/mpeg3
    .mp3 	audio/x-mpeg-3
    .mp3 	video/mpeg
    .mp3 	video/x-mpeg
    .mpa 	audio/mpeg
    .mpa 	video/mpeg
    .mpc 	application/x-project
    .mpe 	video/mpeg
    .mpeg 	video/mpeg
    .mpg 	audio/mpeg
    .mpg 	video/mpeg
    .mpga 	audio/mpeg
    .mpp 	application/vnd.ms-project
    .mpt 	application/x-project
    .mpv 	application/x-project
    .mpx 	application/x-project
    .mrc 	application/marc
    .ms 	application/x-troff-ms
    .mv 	video/x-sgi-movie
    .my 	audio/make
    .mzz 	application/x-vnd.audioexplosion.mzz
    .nap 	image/naplps
    .naplps 	image/naplps
    .nc 	application/x-netcdf
    .ncm 	application/vnd.nokia.configuration-message
    .nif 	image/x-niff
    .niff 	image/x-niff
    .nix 	application/x-mix-transfer
    .nsc 	application/x-conference
    .nvd 	application/x-navidoc
    .o 	application/octet-stream
    .oda 	application/oda
    .omc 	application/x-omc
    .omcd 	application/x-omcdatamaker
    .omcr 	application/x-omcregerator
    .p 	text/x-pascal
    .p10 	application/pkcs10
    .p10 	application/x-pkcs10
    .p12 	application/pkcs-12
    .p12 	application/x-pkcs12
    .p7a 	application/x-pkcs7-signature
    .p7c 	application/pkcs7-mime
    .p7c 	application/x-pkcs7-mime
    .p7m 	application/pkcs7-mime
    .p7m 	application/x-pkcs7-mime
    .p7r 	application/x-pkcs7-certreqresp
    .p7s 	application/pkcs7-signature
    .part 	application/pro_eng
    .pas 	text/pascal
    .pbm 	image/x-portable-bitmap
    .pcl 	application/vnd.hp-pcl
    .pcl 	application/x-pcl
    .pct 	image/x-pict
    .pcx 	image/x-pcx
    .pdb 	chemical/x-pdb
    .pdf 	application/pdf
    .pfunk 	audio/make
    .pfunk 	audio/make.my.funk
    .pgm 	image/x-portable-graymap
    .pgm 	image/x-portable-greymap
    .pic 	image/pict
    .pict 	image/pict
    .pkg 	application/x-newton-compatible-pkg
    .pko 	application/vnd.ms-pki.pko
    .pl 	text/plain
    .pl 	text/x-script.perl
    .plx 	application/x-pixclscript
    .pm 	image/x-xpixmap
    .pm 	text/x-script.perl-module
    .pm4 	application/x-pagemaker
    .pm5 	application/x-pagemaker
    .png 	image/png
    .pnm 	application/x-portable-anymap
    .pnm 	image/x-portable-anymap
    .pot 	application/mspowerpoint
    .pot 	application/vnd.ms-powerpoint
    .pov 	model/x-pov
    .ppa 	application/vnd.ms-powerpoint
    .ppm 	image/x-portable-pixmap
    .pps 	application/mspowerpoint
    .pps 	application/vnd.ms-powerpoint
    .ppt 	application/mspowerpoint
    .ppt 	application/powerpoint
    .ppt 	application/vnd.ms-powerpoint
    .ppt 	application/x-mspowerpoint
    .ppz 	application/mspowerpoint
    .pre 	application/x-freelance
    .prt 	application/pro_eng
    .ps 	application/postscript
    .psd 	application/octet-stream
    .pvu 	paleovu/x-pv
    .pwz 	application/vnd.ms-powerpoint
    .py 	text/x-script.phyton
    .pyc 	applicaiton/x-bytecode.python
    .qcp 	audio/vnd.qcelp
    .qd3 	x-world/x-3dmf
    .qd3d 	x-world/x-3dmf
    .qif 	image/x-quicktime
    .qt 	video/quicktime
    .qtc 	video/x-qtc
    .qti 	image/x-quicktime
    .qtif 	image/x-quicktime
    .ra 	audio/x-pn-realaudio
    .ra 	audio/x-pn-realaudio-plugin
    .ra 	audio/x-realaudio
    .ram 	audio/x-pn-realaudio
    .ras 	application/x-cmu-raster
    .ras 	image/cmu-raster
    .ras 	image/x-cmu-raster
    .rast 	image/cmu-raster
    .rexx 	text/x-script.rexx
    .rf 	image/vnd.rn-realflash
    .rgb 	image/x-rgb
    .rm"=> 	application/vnd.rn-realmedia
    .rm"=> 	audio/x-pn-realaudio
    .rmi"=> 	audio/mid
    .rmm"=> 	audio/x-pn-realaudio
    .rmp"=> 	audio/x-pn-realaudio
    .rmp"=> 	audio/x-pn-realaudio-plugin
    .rng"=> 	application/ringing-tones
    .rng"=> 	application/vnd.nokia.ringing-tone
    .rnx"=> 	application/vnd.rn-realplayer
    .roff"=> 	application/x-troff
    .rp"=> 	image/vnd.rn-realpix
    .rpm"=>	audio/x-pn-realaudio-plugin
    .rt"=> 	text/richtext
    .rt 	text/vnd.rn-realtext
    .rtf 	application/rtf
    .rtf 	application/x-rtf
    .rtf 	text/richtext
    .rtx 	application/rtf
    .rtx 	text/richtext
    .rv 	video/vnd.rn-realvideo
    .s 	text/x-asm
    .s3m 	audio/s3m
    .saveme 	application/octet-stream
    .sbk 	application/x-tbook
    .scm 	application/x-lotusscreencam
    .scm 	text/x-script.guile
    .scm 	text/x-script.scheme
    .scm 	video/x-scm
    .sdml 	text/plain
    .sdp 	application/sdp
    .sdp 	application/x-sdp
    .sdr 	application/sounder
    .sea 	application/sea
    .sea 	application/x-sea
    .set 	application/set
    .sgm 	text/sgml
    .sgm 	text/x-sgml
    .sgml 	text/sgml
    .sgml 	text/x-sgml
    .sh 	application/x-bsh
    .sh 	application/x-sh
    .sh 	application/x-shar
    .sh 	text/x-script.sh
    .shar 	application/x-bsh
    .shar 	application/x-shar
    .shtml 	text/html
    .shtml 	text/x-server-parsed-html
    .sid 	audio/x-psid
    .sit 	application/x-sit
    .sit 	application/x-stuffit
    .skd 	application/x-koan
    .skm 	application/x-koan
    .skp 	application/x-koan
    .skt 	application/x-koan
    .sl 	application/x-seelogo
    .smi 	application/smil
    .smil 	application/smil
    .snd 	audio/basic
    .snd 	audio/x-adpcm
    .sol 	application/solids
    .spc"=> 	application/x-pkcs7-certificates
    .spc"=> 	text/x-speech
    .spl"=> 	application/futuresplash
    .spr"=> 	application/x-sprite
    .sprite"=> 	application/x-sprite
    .src"=> 	application/x-wais-source
    .ssi"=> 	text/x-server-parsed-html
    .ssm"=> 	application/streamingmedia
    .sst"=> 	application/vnd.ms-pki.certstore
    .step"=> 	application/step
    .stl"=> 	application/sla
    .stl"=> 	application/vnd.ms-pki.stl
    .stl"=> 	application/x-navistyle
    .stp"=> 	application/step
    .sv4cpio"=> 	application/x-sv4cpio
    .sv4crc"=> 	application/x-sv4crc
    .svf"=> 	image/vnd.dwg
    .svf"=> 	image/x-dwg
    .svr"=> 	application/x-world
    .svr"=> 	x-world/x-svr
    .swf"=> 	application/x-shockwave-flash
    .t"=> 	application/x-troff
    .talk"=> 	text/x-speech
    .tar"=> 	application/x-tar
    .tbk"=> 	application/toolbook
    .tbk"=> 	application/x-tbook
    .tcl"=> 	application/x-tcl
    .tcl"=> 	text/x-script.tcl
    .tcsh"=> 	text/x-script.tcsh
    .tex"=> 	application/x-tex
    .texi"=> 	application/x-texinfo
    .texinfo"=> 	application/x-texinfo
    .text"=> 	application/plain
    .text"=> 	text/plain
    .tgz"=> 	application/gnutar
    .tgz"=> 	application/x-compressed
    .tif"=> 	image/tiff
    .tif"=> 	image/x-tiff
    .tiff 	image/tiff
    .tiff 	image/x-tiff
    .tr 	application/x-troff
    .tsi 	audio/tsp-audio
    .tsp 	application/dsptype
    .tsp 	audio/tsplayer
    .tsv 	text/tab-separated-values
    .turbot 	image/florian
    .txt 	text/plain
    .uil 	text/x-uil
    .uni 	text/uri-list
    .unis 	text/uri-list
    .unv 	application/i-deas
    .uri 	text/uri-list
    .uris 	text/uri-list
    .ustar 	application/x-ustar
    .ustar 	multipart/x-ustar
    .uu 	application/octet-stream
    .uu 	text/x-uuencode
    .uue 	text/x-uuencode
    .vcd 	application/x-cdlink
    .vcs 	text/x-vcalendar
    .vda 	application/vda
    .vdo 	video/vdo
    .vew 	application/groupwise
    .viv 	video/vivo
    .viv 	video/vnd.vivo
    .vivo 	video/vivo
    .vivo 	video/vnd.vivo
    .vmd 	application/vocaltec-media-desc
    .vmf 	application/vocaltec-media-file
    .voc 	audio/voc
    .voc 	audio/x-voc
    .vos 	video/vosaic
    .vox 	audio/voxware
    .vqe 	audio/x-twinvq-plugin
    .vqf 	audio/x-twinvq
    .vql 	audio/x-twinvq-plugin
    .vrml 	application/x-vrml
    .vrml 	model/vrml
    .vrml 	x-world/x-vrml
    .vrt 	x-world/x-vrt
    .vsd 	application/x-visio
    .vst 	application/x-visio
    .vsw"=>	application/x-visio
    .w60"=> 	application/wordperfect6.0
    .w61"=> 	application/wordperfect6.1
    .w6w "=>	application/msword
    .wav"=> 	audio/wav
    .wav"=> 	audio/x-wav
    .wb1"=> 	application/x-qpro
    .wbmp"=> 	image/vnd.wap.wbmp
    .web"=> 	application/vnd.xara
    .wiz"=> 	application/msword
    .wk1"=> 	application/x-123
    .wmf"=> 	windows/metafile
    .wml"=> 	text/vnd.wap.wml
    .wmlc"=> 	application/vnd.wap.wmlc
    .wmls"=> 	text/vnd.wap.wmlscript
    .wmlsc"=> 	application/vnd.wap.wmlscriptc
    .word"=> 	application/msword
    .wp"=> 	application/wordperfect
    .wp5"=> 	application/wordperfect
    .wp5"=> 	application/wordperfect6.0
    .wp6"=> 	application/wordperfect
    .wpd"=> 	application/wordperfect
    .wpd"=> 	application/x-wpwin
    .wq1"=> 	application/x-lotus
    .wri"=> 	application/mswrite
    .wri"=> 	application/x-wri
    .wrl"=> 	application/x-world
    .wrl"=> 	model/vrml
    .wrl"=> 	x-world/x-vrml
    .wrz"=> 	model/vrml
    .wrz"=> 	x-world/x-vrml
    .wsc"=> 	text/scriplet
    .wsrc"=> 	application/x-wais-source
    .wtk"=> 	application/x-wintalk
    .xbm"=> 	image/x-xbitmap
    .xbm"=> 	image/x-xbm
    .xbm"=> 	image/xbm
    .xdr"=> 	video/x-amt-demorun
    .xgz"=> 	xgl/drawing
    .xif"=> 	image/vnd.xiff
    .xl"=> 	application/excel
    .xla"=> 	application/excel
    .xla"=> 	application/x-excel
    .xla"=> 	application/x-msexcel
    .xlb"=> 	application/excel
    .xlb"=> 	application/vnd.ms-excel
    .xlb"=> 	application/x-excel
    .xlc"=> 	application/excel
    .xlc"=> 	application/vnd.ms-excel
    .xlc"=> 	application/x-excel
    .xld"=> 	application/excel
    .xld"=> 	application/x-excel
    .xlk"=> 	application/excel
    .xlk"=> 	application/x-excel
    .xll"=> 	application/excel
    .xll"=> 	application/vnd.ms-excel
    .xll"=> 	application/x-excel
    .xlm"=> 	application/excel
    .xlm"=> 	application/vnd.ms-excel
    .xlm"=> 	application/x-excel
    .xls"=> 	application/excel
    .xls"=> 	application/vnd.ms-excel
    .xls"=> 	application/x-excel
    .xls"=> 	application/x-msexcel
    .xlt"=> 	application/excel
    .xlt"=> 	application/x-excel
    .xlv"=> 	application/excel
    .xlv"=> 	application/x-excel
    .xlw"=> 	application/excel
    .xlw"=> 	application/vnd.ms-excel
    .xlw"=> 	application/x-excel
    .xlw"=> 	application/x-msexcel
    .xm"=> 	audio/xm
    .xml"=> 	application/xml
    .xml"=> 	text/xml
    .xmz"=> 	xgl/movie
    .xpix"=> 	application/x-vnd.ls-xpix
    .xpm"=> 	image/x-xpixmap
    .xpm"=> 	image/xpm
    .x-png"=> 	image/png
    .xs"=>r 	video/x-amt-showrun
    .xwd"=> 	image/x-xwd
    .xwd"=> 	image/x-xwindowdump
    .xyz"=> 	chemical/x-pdb
    .z"=> 	application/x-compress
    .z"=> 	application/x-compressed
    .zip"=> 	application/x-compressed
    .zip"=> 	application/x-zip-compressed
    .zip"=> 	application/zip
    .zip"=> 	multipart/x-zip
    .zoo"=> 	application/octet-stream
    .zsh"=> 	text/x-script.zsh
     		
     		);" .
     				"*/
     	}
     	/**
     	 * function getCreateTableSql
     	 * @param string $table_name the name of the table to be created
     	 * @return string the sql to b used to create the table for the fileuploads info
     	 */
     	private function getCreateTableSql($table_name="fileuploads") {
     		$sql = 	  "CREATE TABLE ".$table_name." (
    				  `id` int(6) NOT NULL auto_increment,
    				  `form_name` varchar(100) NOT NULL,
    				  `foreign_id` int(6) NOT NULL,
    				  `upload_type` int(1) NOT NULL default '0',
    				  `fld_blob` longblob,
    				  `file_name` varchar(255) NOT NULL,
    				  `extension` varchar(20) NOT NULL,
    				  `dir` varchar(100) NOT NULL,
    				  `file_size` varchar(10) NOT NULL,
    				  `mime_type` varchar(50) NOT NULL,
    				  `caption` varchar(255) default NULL COMMENT 'a nickname of the file',
    				  `full_path` varchar(255) default NULL,
    				  `created` datetime NOT NULL,
    				  `modified` datetime NOT NULL,
    				  PRIMARY KEY  (id)
    				) ENGINE=InnoDb  DEFAULT CHARSET=latin1;";
    		return $sql;
     	}
     	
     	function createUploadsTable() {
     		
     	}
     	
     }
    ?>



.. author:: halladesign
.. categories:: articles, components
.. tags:: multiple file upload,Components

