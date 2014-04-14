CSV Reader Model
================

by CakePOWER on September 25, 2009

This morning I have had to access some csv files and I went into
panic: I found some helpers to create but nothing to read! So I
created this simple model to read CSV files.
I know there are DataSource to access different types of data but i
need a way to define what file to read "on the fly" with an absolute
path.

First let me say how to use this Model:

::

    
    <?php
    class FooController extends AppController {
    	var $uses = array('Csv');
    	
    	function read_csv( $filePath ) {
    		
    		$this->Csv->setFile( $filePath );
    		
    		$list = $this->Csv->find('all',array(
    			'limit' => 5
    		));
    	
    	}
    	
    }
    ?>

You can use some find type such "first", "all", "count".

Csv model can define data headers by fetching the first line of the
csv. You can define your own headers this way:

::

    
    $this->Csv->setSchema(array(
    	'field1' => array(
    		'type' => 'mixed',
    	),
    	'field2' => array(
    		'type' => 'mixed',
    	),
    	'field3' => array(
    		'type' => 'mixed',
    	)
    ));

Where passed values are used as column's header. Column type is
reserved for future usages.

Let me show you my code:

::

    
    <?php
    /**
     * Csv Model
     * Access CSV files on the fly.
     * 
     * @Author: Marco Pegoraro
     * @mail: info(at)cakepower(dot)org
     * @web: www.cakepower.org
     * 
     */
    class Csv extends AppModel {
    	var $name 		= 'Csv';
    	var $useTable 	= false;
    	
    	// Model's settings.                                                                          #
    	var $source 		= null;			// Absolute path of the source file.                      #
    	var $delimiter		= ';';			// Separation character used for parsing.                 #
    	var $schemaOnTop 	= null;			// Define if the first row contain the data schema.       #
    	var $php4Parser		= null;			// An object with a "parse()" method to call for parsing  #
    										// a csv row in PHP4.                                     #
    	
    	// Model's internal properties.                                                               #
    	var $_file			= null;			// CSV file pointer.                                      #
    	var $_data			= null;			// Local database of parsed data.                         #
    	var $_loaded		= false;		// It estabilishes the state of load of the data.         #
    	var $_schemed		= false;		// It estabilishes the state of load of the schema.       #
    	
    	
    	
    	/**
    	 * Set the source file.
    	 * Ii extpects an absolute file path. It check for file existance and return a boolean state.
    	 * 
    	 * @return boolean
    	 * @param object $filePath
    	 */
    	function setFile( $filePath ) {
    		
    		if ( file_exists($filePath) ) {
    			$this->source 	= $filePath;
    			
    			$this->_loaded	= false;
    			$this->_schemed = false;
    			$this->_data	= null;
    			
    		} else $this->source = null;
    		
    		return ( !is_null($this->source) );
    		
    	} // EndOf: "setFile()" #######################################################################
    	
    	
    	/**
    	 * Set the column delimitation character.
    	 * 
    	 * @return 
    	 * @param object $delimiter
    	 */
    	function setDelimiter( $delimiter ) {
    		
    		$this->delimiter = $delimiter;
    		
    	} // EndOf: "setDelimiter()" ##################################################################
    	
    	
    	/**
    	 * Set a local schema to define CSV's data.
    	 * If called with an empty value schema will be auto-loaded form the first row.
    	 * 
    	 * @return 
    	 * @param array $schema[optional]
    	 */
    	function setSchema( $schema = array() ) {
    		
    		// It check for schema auto-loading.                                                      # 
    		if ( empty($schema) ) $this->_loadSchema();
    		
    		// Or set an explicit value for the local schema.                                         #
    		else $this->_schema = $schema;
    		
    		$this->_schemed = true;
    		
    	} // EndOf: "setSchema()" #####################################################################
    	
    	
    	/**
    	 * It reads the csv source.
    	 * 
    	 * @return 
    	 * @param object $type[optional]
    	 * @param object $params[optional]
    	 */
    	function find( $type = '', $params = array() ) {
    		
    		// This is the container for the result of the extraction.                                #
    		$returnValue = array();
    		
    		// Check for the loaded-status to prevent errors.                                         #
    		if ( !$this->_load() ) return $returnValue;
    		
    		// -------------------------------------------------------------------------------------- #
    		// Request-type dependant diversification.                                                #
    		switch ( $type ) {
    			
    			case 'first':
    				$params['limit'] = 1;
    				break;
    				
    			case 'headers':
    				return $this->getHeaders();
    			
    		}
    		
    		
    		// Initialization of the cycle control params.                                            #
    		$rowCount 	= 0;	// This is a file row counter.                                        #
    		$dataCount	= 0;	// This is a data filtered counter.                                   #
    		if ( !empty($params['limit']) ) $params['limit'] -= 1;
    		
    		// -------------------------------------------------------------------------------------- #
    		// Start reading of file with csv parsing.                                                #
    		while ( ( $data = $this->__fdata() ) != false ) {
    			
    			// It start by allow data-addition. This rule will be contested by filtering process. #
    			$addLine = true;
    			
    			// Filtering heading row.                                                             #
    			if ( $rowCount == 0 && $this->schemaOnTop == true ) $addLine = false;
    			
    			// Check for the permission to add data to filtered output.                           #
    			if ( $addLine ) {
    				
    				$returnValue[][$this->name] = $data;
    				
    				// Limit Param Check.                                                             #
    				if ( !empty($params['limit']) && $dataCount == $params['limit'] ) break;
    				
    				$dataCount++;
    				
    			}
    			
    			$rowCount++;
    		}
    		
    		
    		// -------------------------------------------------------------------------------------- #
    		// Check for the request-type to output values.                                           # 
    		switch ( $type ) {
    			
    			case 'count':
    				return count($returnValue);
    			
    			default:
    				return $returnValue;
    			
    		}
    		
    	} // EndOf: "find()" ##########################################################################
    	
    	
    	/**
    	 * Return an associative array filled with heading info.
    	 * @return 
    	 */
    	function getHeaders() {
    		
    		if ( is_null($this->_schema) ) $this->_loadSchema();
    		
    		$returnValue = array();
    		
    		foreach ( $this->_schema as $colName=>$colInfo ) $returnValue[$colName] = $colInfo['show'];
    		
    		return array( $this->name => $returnValue );
    		
    	} // EndOf: "getHeaders()" ####################################################################
    	
    	
    	
    	
    	
    	###############################################################################################
    	### PRIVATE METHODS.                                                                        ###
    	###############################################################################################
    	
    	function _loadSchema() {
    		
    		if ( !$this->__fopen() ) return false;
    		
    		$this->_schema = array();
    		if ( is_null($this->schemaOnTop) ) $this->schemaOnTop = true;
    		
            foreach ( $this->__fdata() as $col ) {
            	
    			$this->_schema[$col] = array(
    				'type' 	=> 'mixed',
    				'show'	=> $col,
    			);
    			
            }
    		
    		$this->__fclose();
    		
    	} // EndOf: "_loadSchema()" ###################################################################
    	
    	function _load() {
    		
    		if ( is_null($this->_schema) ) 	$this->_loadSchema();
    		if ( !$this->__fopen() )		return false;
    		
    		return true;
    		
    	} // EndOf: "_load()" #########################################################################
    	
    	
    	
    	
    	
    	###############################################################################################
    	### LOW LEVEL FILE MANAGEMENT.                                                              ###
    	###############################################################################################
    	
    	function __fopen() {
    		
    		if ( !is_null($this->_file) )	return true;	// The file is already open!              #
    		if ( is_null($this->source) ) 	return false;	// Source path not set!                   #
    		
    		$this->_file = fopen( $this->source, "r" );
    		
    		return !is_null($this->_file);
    		
    	} // EndOf: "__fopen()" #######################################################################
    	
    	/**
    	 * Close the reference with the source file.
    	 * @return 
    	 */
    	function __fclose() {
    		
    		if ( is_null($this->_file) ) return false;
    		
    		fclose($this->_file);
    		$this->_file = null;
    		
    		return true;
    		
    	} // EndOf: "__fclose()" ######################################################################
    	
    	function __fline() {
    		
    		if ( is_null($this->_file) ) return false;
    		
    		return rtrim(fgets($this->_file));
    		
    	} // EndOf: "__fline()" #######################################################################
    	
    	function __fdata() {
    		
    		if ( is_null($this->_file) ) return false;
    		
    		// PHP5 define a very usefull function to parse a CSV row.                                #
    		if ( PHP5 ) {
    			$data = fgetcsv( $this->_file, 8192, $this->delimiter );
    		
    		// PHP4 must proceed with a step-by-step parsing process.                                 #
    		// You can define an object to be called when need a csv parsing.                         #
    		} else {
    			
    			// Call an external object method.                                                    #
    			if ( !is_null($this->php4Parser) ) $data = $this->php4Parser->parse( $this->__fline(), $this->delimiter );
    			
    			// @TODO: This process is not implemented yet... Implementation is required!          #
    			$data = array();
    			
    		}
    		
    		
    		// Try to apply data-schema to the extracted data by duplicate each information with it   #
    		// field name.                                                                            #
    		if ( !empty($this->_schema) ) {
    			
    			// Check for the congruence of extracted data.                                        #
    			if ( count($data) < count($this->_schema) ) return array();
    			
    			// It adds the named field to the data array.                                         #
    			$i = 0;
    			foreach ( $this->_schema as $fieldName=>$fieldInfo ) {
    				$data[$fieldName] = $data[$i];
    				$i++;
    			}
    		}
    		
    		return $data;
    		
    	} // EndOf: "__fdata()" #######################################################################
    	
    } // EndOf: "CsvModel" -------------------------------------------------------------------------- #
    ?>


.. meta::
    :title: CSV Reader Model
    :description: CakePHP Article related to csv,read csv,access csv,Models
    :keywords: csv,read csv,access csv,Models
    :copyright: Copyright 2009 CakePOWER
    :category: models

