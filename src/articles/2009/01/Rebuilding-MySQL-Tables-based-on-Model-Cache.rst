Rebuilding MySQL Tables based on Model Cache
============================================

by Datawalke on January 21, 2009

By reading the title of this article you know something has gone
horribly wrong. Which was the case earlier today when my development
server crashed with our teams shared database on it. While we did have
backups of most of the data a few of the tables and a couple smaller
projects were not so lucky.
While this is not a problem most people should run into the solution
is fairly simple if using the script below. If you have any questions
or comments feel free to post or email me at: `jim@bravegamer.com`_

The Class
~~~~~~~~~

::

    
    <?php
    /**
     * Used to rebuild lost table schema based on CakePHP's cached model data
     * CakePHP stores its cached model information in seralized PHP format.
     * By reversing this we can re-create the structure of a horribly lost database.
     * To that degree -- If you are reading this now. I am sorry.
     *
     * @author Jim Walker
     * @url http://www.bravegamer.com/tools/cakephp
     * @version 1.0.0
     * 
     * @usage See code below
     * <code>
     * $directory = 'C:/www/htdocs/cakePHPApplication/app/tmp/cache/models/';
     * $rebuild = new CakeRebuildMySQL($directory, 'MyISAM');
     * print_r($rebuild->build());
     * </code>
     *
     * @comment How you get your data back into your database is up to you.
     */
    class CakeRebuildMySQL {
    
    	/**
    	 * The directory you wish to rebuild the MySQL data from
    	 *
    	 * @var string $directory
    	 */
    	public $directory;
    	
    	/**
    	 * The MySQL storage engine you are using
    	 *
    	 * @var string $engine
    	 */
    	public $engine;
    
    	
    	/**
    	 * Initiate the class and set the directory
    	 *
    	 * @param string $directory the directory you wish to set.
    	 */
    	public function __construct($directory, $engine) {
    		$this->directory = $directory;
    		$this->engine = $engine;
    	}
    	
    	/**
    	 * Builds the SQL based on the cached models.
    	 *
    	 * @return array $queries An array of SQL Table queries
    	 */
    	public function build() {
    		/**
    		 * The list of files in the directory
    		 *
    		 * @var array $files
    		 */
    		$files = $this->directoryList($this->directory);
    		foreach($files as $file) {
    			$tableName = $this->getTableName($file);
    			$tables[$tableName] = $this->getTableStructure($file);
    		}
    		
    		foreach($tables as $table => $structure) {
    			/**
    			 * The SQL queries per structure
    			 *
    			 * @var array $queries
    			 */
    			$queries[] = $this->generateSQL($table, $structure);
    		}
    		
    		return $queries;
    	}
    	
    	/**
    	 * Lists the files in a directory
    	 * 
    	 * @param string $directory the directory you wish to list the files of
    	 */
    	public function directoryList ($directory) {
    		/**
    		 * The result array
    		 *
    		 * @var array $result the results of the directory
    		 */
    		$results = array();
    		
    		/**
    		 * The directory object
    		 *
    		 * @var object $directory
    		 */
    		$directory = opendir($directory);
    
    		while ($file = readdir($directory)) {
    			if ($file != '.' && $file != '..') {
    				$check_list = strpos($file, '_list');
    				$check_empty = strpos($file, 'empty');
    				if($check_list === false && $check_empty === false) {
    					$results[] = $file;
    				}
    			}
    		}
    		
    		closedir($directory);
    
    		return $results;
    	}
    	
    	/**
    	 * Will return the table structure
    	 *
    	 * @param string $fileName The file name
    	 * @return string $tableStructure The table Name
    	 */
    	public function getTableStructure($fileName) {
    		/**
    		 * The path to the file you are getting
    		 *
    		 * @var string $path
    		 */
    		$path = $this->directory . $fileName;
    		
    		/**
    		 * The file resource 
    		 *
    		 * @var resource $fileResource
    		 */
    		$fileResource = fopen($path, "r");
    		
    		/**
    		 * An array of the contents of the opened file
    		 *
    		 * @var array $contents
    		 */
    		$contents = file($path);
    		
    		fclose($fileResource);
    		
    		return unserialize($contents[1]);
    	}
    	
    	/**
    	 * Will return the name of the table
    	 *
    	 * @param string $fileName The file name
    	 * @return string $tableName The table Name
    	 */
    	public function getTableName($fileName) {
    		/**
    		 * The table name. Replacing CakePHP's default model cache file names
    		 *
    		 * @var string $tableName
    		 */
    		$tableName = str_replace('cake_model_default_', '', $fileName);
    		
    		return $tableName;
    	}
    	
    	public function generateSQL($tableName, $structure) {
    		
    		/**
    		 * The SQL Query for the given table
    		 * Based on Cake's Serialized data
    		 *
    		 * @var string $query
    		 */
    		$query = 'CREATE TABLE IF NOT EXISTS `' .$tableName . '` (' . "\n";
    		
    		/**
    		 * The total number of fields in the table
    		 *
    		 * @var int $totalFields
    		 */
    		$totalFields = count($structure);
    		
    		/**
    		 * The count of what Field ID we are on
    		 *
    		 * @var int $fieldCoun
    		 */
    		$fieldCount = 1;
    		
    		foreach($structure as $field => $data) {
    			/**
    			 * What data types we want to replace from the seralized data
    			 *
    			 * @var array $replace
    			 */
    			$replace = array('integer', 'string');
    			
    			/**
    			 * What we want to replace it with.
    			 *
    			 * @var array $replace
    			 */
    			$replacements = array('int', 'varchar');
    			
    			/**
    			 * Replace the datatype to match MySQL
    			 *
    			 * @var string $data['type']
    			 */
    			$data['type'] = str_replace($replace, $replacements, $data['type']);
    			
    			$query .= '`' . $field . '` ' . $data['type'];
    			
    			/**
    			 * Check to see if the data type is an enum.
    			 *
    			 * @var bool $check
    			 */
    			$check = strpos($data['type'], 'enum');
    			
    			if(!empty($data['length']) && $check === false) {
    				$query .= '('.$data['length'].')';
    			}
    			
    			$query .= ' NOT NULL';
    			
    			if($data['key'] == 'primary' && $data['type'] == 'int') {
    				$query .= ' auto_increment PRIMARY KEY';
    			}
    			
    			if(!empty($data['default'])) {
    				$query .= ' default \'' . $data['default'] . '\'';
    			}
    			
    			if($fieldCount < $totalFields) {
    				$query .= ',';
    			}
    			
    			$query .= "\n";
    			
    			$fieldCount++;
    		
    		}
    		
    		$query .= ') ENGINE=' . $this->engine . ' ;' . "\n\n";
    		
    		return $query;
    	}
    	
    }
    
    ?>



Usage
~~~~~
Usage is fairly simple:

::

    
    <?php
        include_once('CakeRebuildMySQL.php');
        $directory = 'C:/www/htdocs/cakePHPApplication/app/tmp/cache/models/';
        $rebuild = new CakeRebuildMySQL($directory, 'MyISAM');
        $queries = $rebuild->build();
        foreach($queries as $query) {
           //Your Query here.
        }
    ?>



.. _jim@bravegamer.com: mailto:jim@bravegamer.com=jim@bravegamer.com
.. meta::
    :title: Rebuilding MySQL Tables based on Model Cache
    :description: CakePHP Article related to sql,model,mysql,cache,recovery,General Interest
    :keywords: sql,model,mysql,cache,recovery,General Interest
    :copyright: Copyright 2009 Datawalke
    :category: general_interest

