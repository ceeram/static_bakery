FMCakeMix: A Full Read/Write Datasource for FileMaker
=====================================================

Use a FileMaker database as a full read/write datasource with support
for relationships and more...


Installation
~~~~~~~~~~~~

FX.php
``````

FX.php is a PHP class created by Chris Hansen to speak with FileMaker
via XML. The FMCakeMix driver uses fx.php to send queries to FileMaker
and is necessary for the driverâ€™s functionality. Install FX.php by
downloading the files from `http://www.iviking.org/FX.php/`_ and
placing the FX.php, FX_Error.php, FX_Constants.php, and
image_proxy.php files at the root of the yourcakeinstall/vendors
folder.

FileMaker
`````````

Because the driver uses XML to communicate with FileMaker, your
FileMaker solutions must be hosted on a version of FileMaker Server
that supports web publishing and xml access. See the FileMaker Server
documentation for instructions on enabling these features.

FMCakeMix
`````````

Install the dbo_fmcakemix.php file into
yourcakeinstall/app/models/datasources/dbo.



Define Your Database Connection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Below weâ€™ve defined our default connection to use the fmcakemix
driver and provided the necessary details for cakeâ€™s connection
manager to connect with our filemaker database. If your models refer
to multiple FileMaker database files, donâ€™t worry we will override
the database attribute when defining our models.

::

    
    <?php
    var $default = array(
    	'driver' => 'fmcakemix',
    	'persistent' => false,
    	'dataSourceType' => 'FMPro7',
    	'scheme' => 'http',
    	'port' => 80,
    	'host' => '127.0.0.1',
    	'login' => 'myUserName',
    	'password' => 'myPassword',
    	'database' => 'FMServer_Sample',
    	'prefix' => '',
    );
    ?>



Define Your Model
~~~~~~~~~~~~~~~~~

In addition to the standard model attributes of name, useDbConfig, and
primaryKey, weâ€™ll also want to tell Cake to associate our model with
a default FileMaker layout using the defaultLayout attribute and
define a fmDatabaseName for the FileMaker file where our layout lives.

Relations are defined through the hasMany, hasOne, belongsTo, and
hasAndBelongsToMany attributes (Currently the driver only supports
hasMany and belongsTo relations). There are essentially two options
you have when working with related data in FileMaker; either use
relationships defined within Cake or leverage FileMakerâ€™s ability to
relate and retrieve data through portals. Remember when retrieving
data through a Cake defined relationship youâ€™re actually making a
new call for every related model, this could have a negative impact on
performance.

The fmTOtranslations attribute allows you to associate related portal
data that may be returned by the modelâ€™s layout to a Cake model we
may have defined elsewhere within our application. Here weâ€™re
associating any data returned from a portal of commentsTO, the name of
a FileMaker table occurrence, to a Comments model.

::

    
    <?php
    class Book extends AppModel {
    
    	var $name = 'Book';
    	var $useDbConfig = 'default';
    	var $primaryKey = 'ID';
    
    	// FMCakeMix specific attributes
    	var $defualtLayout = 'web_books_general';
    	var $fmDatabaseName = 'FMServer_Sample';
    	
    	// Optionally assign related models
    	var $hasMany = array(
    		'Comment' => array(
    			'foreignKey' => '_fk_book_id'
    		), 
    		'History' => array(
    			'foreignKey' => '_fk_book_id'
    		)
    	);
    	
    	// Optionally provide translations of related FileMaker table occurrences
    	// that may be returned through FileMaker portals into Cake model names
    	var $fmTOtranslations = array(
    		'CommentsTO' => 'Comment'
    	);
    	
    	// Optionally provide validation criteria for our model
    	var $validate = array(
    		'Title' => array(
    			'rule' => 'notEmpty'
    		),
    		'Author' => array(
    			'rule' => 'notEmpty'
    		)
    	);
    }
    ?>



Controller Examples
~~~~~~~~~~~~~~~~~~~

Below weâ€™ll cover the basics for creating, reading, deleting, and
updating data within our FileMaker database.

Create
``````
save
A basic add method for our controller. Here weâ€™re taking information
passed from a form, $this->data, and calling two model methods to save
this data to a new record in FileMaker. Itâ€™s important to note that
cake will continue to automagically handle certain fields, such as
created and modified.

::

    
    <?php
    function add() {
    	if (!empty($this->data)) {
    		$this->Book->create();
    		if ($this->Book->save($this->data)) {
    			$this->Session->setFlash(__('The Book has been saved', true));
    			$this->redirect(array('action'=>'index'));
    		} else {
    			$this->Session->setFlash(__('The Book could not be saved. Please, try again.', true));
    		}
    	}
    }
    ?>

saveAll
The saveAll model method will allow us to save multiple models at a
time. When using the saveAll method always pass the option atomic is
false to tell Cake not to attempt a transactional save to our
database.

::

    
    <?php
    $_data = array(
    	'Comment' => array(
    		array(
    			'_fk_article_id' => $this->Book[â€˜IDâ€™],
    			'body' => 'New Comment'
    		), 
    		array(
    			'_fk_article_id' => $this->Book[â€˜IDâ€™],
    			'body' => 'Another Comment'
    		)
    	)
    );
    $this->Comment->create();
    $this->Comment->saveAll($_data['Comment'], array('atomic' => FALSE));
    ?>



Read
````
find
Here we collect a query for a recipe title and perform a find request
for recipes containing this title and with a published value of 1.

::

    
    <?php
    function search() {
    	$query = $this->data['Recipe']['title'];
    	
    	$recipes = $this->Recipe->find('all', array(
    		'conditions' => array(
    			'titleâ€™ => $query,
    			â€˜publishedâ€™ => â€˜=â€™.1
    		)
    	));
    	
    	$this->set('recipes', $recipes);
    }
    ?>

paginate
The FMCakeMix datasource supports offsets and sorting, and can be
implemented by the paginate function.

::

    
    <?php
    var $paginate = array('limit' => 10, 'page' => 1);
    
    function index() {
    	$this->Book->recursive = 0;
    	$this->set('books', $this->paginate('Book'));
    }
    ?>



Delete
``````
del, remove
The del method and its alias remove will delete a single record from
your database. FileMaker requires that we send the internal recid of
the record we wish to delete with every delete request. A recid is
returned as one of the fields in the returned data set whenever we
return record data, such as after a find command. Additionally the
recid is saved to the model id attribute which leaves the model
referencing the record returned on the last query, this is especially
useful after a create action. Note however that this is a departure
from a CakePHP standard that assumes the primaryKey id will be stored
in this attribute.

In the example below the find sets the model id attribute so that when
calling the del method FileMaker is passed the appropriate recid of
the record to be deleted.

::

    
    <?php
    delete() {
    
    	$this->Book->find('first', array(
    		'conditions' => array(
    			'Book.ID' => 48
    		),
    		'recursive' => 0
    	));
    
    	$model->del()
    }
    ?>

deleteAll
Hereâ€™s a more functional example of how you might implement a delete
method. Here we pass the recid of the record to delete and provide
some user feedback to the view. Instead of using the del method we use
deleteAll to be explicit about the record we wish to delete.

::

    
    <?php
    function delete($recid = null) {
    	if (!$recid) {
    		$this->Session->setFlash(__('Invalid id for Book', true));
    		$this->redirect(array('action'=>'index'));
    	}
    	if ($this->Book->deleteAll(array('-recid' => $recid), false)) {
    		$this->Session->setFlash(__('Book deleted', true));
    		$this->redirect(array('action'=>'index'));
    	} else {
    		$this->Session->setFlash(__('Book could not be deleted', true));
    		$this->redirect(array('action'=>'index'));
    	}
    }
    ?>



Update
``````
save
An update works much like a create and uses the same save model
method, but instead we pass along the FileMaker required recid of the
record we wish to edit. In this example the recid is included in the
passed form data, implemented as a hidden input.

::

    
    <?php
    function edit($id = null) {
    	if (!$id && empty($this->data)) {
    		$this->Session->setFlash(__('Invalid Book', true));
    	}
    	if (!empty($this->data)) {
    		if ($this->Book->save($this->data)) {
    			$this->Session->setFlash(__('The Book has been saved', true));
    			$this->redirect(array('action'=>'index'));
    		} else {
    			$this->Session->setFlash(__('The Book could not be saved.', true));
    		}
    	}
    	if (empty($this->data)) {
    		$this->data = $this->Book->read(null, $id);
    	}
    }
    ?>





Known Limitations
~~~~~~~~~~~~~~~~~

FileMaker
`````````

+ Container Fields : container fields will supply a url string to the
  resource or a copy of the resource made by filemaker, but files can
  not be uploaded into container fields.



CakePHP Model
`````````````
Attributes

+ hasOne : currently no support for this relationship type
+ hasAndBelongsToMany : currently no support for this relationship
  type

Methods

+ deleteAll : only takes the condition that the -recid equals the
  recid of the record to delete and therefore does not support deleting
  many records at a time. Also, you must pass a boolean false as the
  second parameter of this request so that it does not attempt recursive
  deletion of related records
+ save : the fields parameter, or white list of fields to save, does
  not work.
+ saveAll : does not support database transactions and therefore the
  atomic option must be set to false



The Datasource
~~~~~~~~~~~~~~

I also maintain a github repository for this project available at:

`http://github.com/alexgb/FMCakeMix/tree/master`_

The git repository also includes a basic test suite and test database,
available in case anyone is interested in modifying the code or
contributing to the project.

::

    
    <?php 
    /** 
     * FMCakeMix 
     * @author Alex Gibbons alex_g@beezwax.net
     * @date 02/2009
     * 
     * Copyright (c) 2009 Alex Gibbons, Beezwax.net
     * 
     * 
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     * 
     * The above copyright notice and this permission notice shall be included in
     * all copies or substantial portions of the Software.
     * 
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
     * THE SOFTWARE.
     */ 
    
    
    
    
    // =================================================================================
    // = FX.php : required base class
    // =================================================================================
    // FX is a free open-source PHP class for accessing FileMaker using curl and xml
    // By: Chris Hansen with Chris Adams, Gjermund Thorsen, and others
    // Tested with version: 4.5.1
    // Web Site: www.iviking.org
    // =================================================================================
    
    App::import('Vendor','FX', array('file' => 'FX.php'));
    
    class DboFMCakeMix extends DataSource { 
    
        var $description = "FileMaker Data Source"; 
    
        var $_baseConfig = array ( 
            'host' => 'localhost', 
            'port' => 80,  
        ); 
    
    	/**
    	 * FileMaker column definition
    	 *
    	 * @var array
    	 */
    	var $columns = array(
    		'primary_key' => array('name' => 'NUMBER'),
    		'string' => array('name' => 'TEXT'),
    		'text' => array('name' => 'TEXT'),
    		'integer' => array('name' => 'NUMBER','formatter' => 'intval'),
    		'float' => array('name' => 'NUMBER', 'formatter' => 'floatval'),
    		'datetime' => array('name' => 'TIMESTAMP', 'format' => 'm/d/Y H:i:s', 'formatter' => 'date'),
    		'timestamp' => array('name' => 'TIMESTAMP', 'format' => 'm/d/Y H:i:s', 'formatter' => 'date'),
    		'time' => array('name' => 'TIME', 'format' => 'H:i:s', 'formatter' => 'date'),
    		'date' => array('name' => 'DATE', 'format' => 'm/d/Y', 'formatter' => 'date'),
    		'binary' => array('name' => 'CONTAINER'),
    		'boolean' => array('name' => 'NUMBER')
    	);
         
        /** 
         * Constructor 
         */ 
        function __construct($config = null) { 
            $this->debug = Configure :: read() > 0; 
            $this->fullDebug = Configure :: read() > 1;
    		$this->timeFlag = getMicrotime();
    		
            parent :: __construct($config); 
            return $this->connect(); 
        } 
         
        /** 
         * Destructor. Closes connection to the database. 
         */ 
        function __destruct() { 
            $this->close(); 
            parent :: __destruct(); 
        } 
    
         /** 
         * Connect. Creates connection handler to database 
         */
        function connect() { 
    	
    		// Debugger::log('fm_dbo:connect ');
            $config = $this->config; 
            $this->connected = false; 
    
            $this->connection = new FX($config['host'],$config['port'], $config['dataSourceType'], $config['scheme']);
            $this->connection->SetDBPassword($config['password'],$config['login']);
            
    		$this->connected = true; //always returns true
            return $this->connected; 
        } 
         
        /** 
         * Close.
         */ 
        function close() { 
            if ($this->fullDebug && Configure :: read() > 1) { 
                $this->showLog(); 
            } 
    
    		
            $this->disconnect(); 
        } 
         
        function disconnect() { 
    		$this->connected = false;
            return $this->connected; 
        } 
         
        /** 
         * Checks if it's connected to the database 
         * 
         * @return boolean True if the database is connected, else false 
         */ 
        function isConnected() { 
            return $this->connected; 
        } 
         
        /** 
         * Reconnects to database server with optional new settings 
         * 
         * @param array $config An array defining the new configuration settings 
         * @return boolean True on success, false on failure 
         */ 
        function reconnect($config = null) { 
            $this->disconnect(); 
            if ($config != null) { 
                $this->config = am($this->_baseConfig, $this->config, $config); 
            } 
            return $this->connect(); 
        } 
    
    	/** 
         * Returns properly formatted field name
         * 
         * @param array $config An array defining the new configuration settings 
         * @return boolean True on success, false on failure 
         */ 
        function name($data) { 
    	
            return $data; 
    
        }
    
    	/*
    		TODO_ABG: needs to use recursion
    		TODO_ABG: needs to handle filemakers ability to put mutliple tables on one layout
    		TODO_ABG: should somehow include the ability to specify layout
    	*/
        /** 
         * The "R" in CRUD 
         * 
         * @param Model $model 
         * @param array $queryData 
         * @param integer $recursive Number of levels of association 
         * @return unknown 
         */ 
        function read(& $model, $queryData = array (), $recursive = null) { 
    		
    		$fm_layout = $model->defualtLayout;
    		$fm_database = $model->fmDatabaseName;
    		$queryLimit = $queryData['limit'] == null ? 'all' : $queryData['limit'];
    		$linkedModels = array();
    		
    		if (!is_null($recursive)) {
    			$_recursive = $model->recursive;
    			$model->recursive = $recursive;
    		}
    		
    		
    		// set basic connection data
    		$this->connection->SetDBData($fm_database, $fm_layout, $queryLimit );
    		
    		
    		/*
    			TODO_ABG : this has a junk interpretation of a logical or statement, that isn't nestable
    			* it therefore turns the whole query into an or, if an or statement is injected somewhere
    			* this is a major limitation of fx.php
    		*/
    		if(!empty($queryData['conditions'])) {
    			$conditions = array(); 								// a clean set of queries
    			$isOr = false;  									// a boolean indicating wether this query is logical or
    		
    			foreach($queryData['conditions'] as $conditionField => $conditionValue) {
    				// if a logical or statement has been pased somewhere
    				if($conditionField == 'or') {
    					$isOr = true;
    					if(is_array($conditionValue)) {
    						$conditions = array_merge($conditions, $conditionValue);
    					}
    				} else {
    					$conditions[$conditionField] = $conditionValue;
    				}
    			}
    			
    			
    			foreach($conditions as $conditionField => $conditionValue) {
    				$string = $conditionField;
    				if(strpos($string,'.')) {
    					$stringExp = explode('.', $string);
    					unset($stringExp[0]);
    					$plainField = implode('.',$stringExp);
    				} else {
    					$plainField = $string;
    				}
    				
    				
    				$this->connection->AddDBParam($plainField, $conditionValue, 'eq');
    				
    				//add or operator
    				if($isOr){
    					$this->connection->SetLogicalOR();
    				}
    			}
    			
    		}
    		
    		// set sort order
    		foreach($queryData['order'] as $orderCondition) {
    			if(!empty($orderCondition)){
    				foreach($orderCondition as $field => $sortRule) {
    					$string = $field;
    					$pattern = '/(\w+)\.(-*\w+)$/i';
    					$replacement = '${2}';
    					$plainField = preg_replace($pattern, $replacement, $string);
    					
    					$sortRuleFm = $sortRule == 'desc' ? 'descend' : 'ascend';
    					$this->connection->AddSortParam($plainField, $sortRuleFm);
    				}
    			}
    		}
    		
    		// set skip records if there is an offset
    		if(!empty($queryData['offset'])) {
    			$this->connection->FMSkipRecords($queryData['offset']);
    		}
    		
    		
    		// return a found count if requested
    		if($queryData['fields'] == 'COUNT') {
    			// perform find without returning result data
    			$fmResults = $this->connection->FMFind(true, 'basic');
    			
    			// test result
    			if(!$this->handleFXResult($fmResults, $model->name, 'read (count)')) {
    				return FALSE;
    			}
    			
    			$countResult = array();
    			$countResult[0][0] = array('count' => $fmResults['foundCount']);
    			
    			// return found count
    			return $countResult;
    		} else {
    			// perform the find in FileMaker
    			$fmResults = $this->connection->FMFind();
    			
    			if(!$this->handleFXResult($fmResults, $model->name, 'read')) {
    				return FALSE;
    			}
    		}
    		
    		
    		$resultsOut = array();
    		// format results
    		if(!empty($fmResults['data'])) {
    			$i = 0;
    			foreach($fmResults['data'] as $recmodid => $recordData) {
    				$relatedModels = array();
    				$recmodid_Ary = explode('.', $recmodid);
    				$resultsOut[$i][$model->name]['-recid'] = $recmodid_Ary[0];
    				$resultsOut[$i][$model->name]['-modid'] = $recmodid_Ary[1];
    				
    				foreach($recordData as $field => $value) {
    					$resultsOut[$i][$model->name][$field] = $value[0];
    				}
    				$i++;
    			}
    		}
    		
    		
    		// ================================
    		// = Searching for Related Models =
    		// ================================
    		if ($model->recursive > 0) {
    			
    			
    			foreach ($model->__associations as $type) {
    				foreach ($model->{$type} as $assoc => $assocData) {
    					$linkModel =& $model->{$assoc};
    					
    					
    					if (!in_array($type . '/' . $assoc, $linkedModels)) {
    						if ($model->useDbConfig == $linkModel->useDbConfig) {
    							$db =& $this;
    						} else {
    							$db =& ConnectionManager::getDataSource($linkModel->useDbConfig);
    						}
    					} elseif ($model->recursive > 1 && ($type == 'belongsTo' || $type == 'hasOne')) {
    						$db =& $this;
    					}
    					
    					if (isset($db)) {
    						$stack = array($assoc);
    						$db->queryAssociation($model, $linkModel, $type, $assoc, $assocData, $array, true, $resultsOut, $model->recursive - 1, $stack);
    						unset($db);
    					}
    				}
    			}
    		}
    		
    	
    		
    		if (!is_null($recursive)) {
    			$model->recursive = $_recursive;
    		}
    		
    		
    		// return data
    		return $resultsOut;
    		
        } 
    
    	/**
    	 * Calculate
    	 * currently this only returns a 'count' flag if a count is requested. This will tell
    	 * the read function to return a found count rather than results
    	 *
    	 * @param model $model
    	 * @param string $func Lowercase name of SQL function, i.e. 'count' or 'max'
    	 * @param array $params Function parameters
    	 * @return string flag informing read function to parse results as per special case of $func
    	 * @access public
    	 */
    
    	function calculate(&$model, $func, $params = array()) {
    		$params = (array)$params;
    		
    		switch (strtolower($func)) {
    			case 'count':
    				if (!isset($params[0])) {
    					$params[0] = '*';
    				}
    				if (!isset($params[1])) {
    					$params[1] = 'count';
    				}
    				return 'COUNT';
    			case 'max':
    			case 'min':
    				if (!isset($params[1])) {
    					$params[1] = $params[0];
    				}
    				return strtoupper($func) . '(' . $this->name($params[0]) . ') AS ' . $this->name($params[1]);
    			break;
    		}
    	}
    	
    	
    	/**
    	 * The "D" in CRUD 
    	 * can only delete from the recid that is internal to filemaker
    	 * We do this by using the deleteAll model method, which lets us pass conditions to the driver
    	 * delete statement. This method will only work if the conditions array contains a 'recid' field
    	 * and value. Also, must pass cascade value of false with the deleteAll method.
    	 *
    	 * @param Model $model
    	 * @param array $conditions
    	 * @return boolean Success
    	 */
    	function delete(&$model, $conditions = null) {
    		
    		
    		$fm_layout = $model->defualtLayout;
    		$fm_database = $model->fmDatabaseName;
    		
    		// set basic connection data
    		$this->connection->SetDBData($fm_database, $fm_layout);
    		
    		if(is_null($conditions)) {
    			$this->connection->AddDBParam('-recid', $model->getId(), 'eq');
    		} else {
    			// must contain a -recid field
    			foreach($conditions as $field => $value) {
    				$this->connection->AddDBParam($field, $value, 'eq');
    			}
    		}
    		
    		// perform deletion
    		$return = $this->connection->FMDelete(TRUE);
    		
    		if(!$this->handleFXResult($return, $model->name, 'delete')) {
    			return FALSE;
    		} else {
    			return TRUE;
    		}
    	}
    	
    	/**
    	 * The "C" in CRUD
    	 *
    	 * @param Model $model
    	 * @param array $fields
    	 * @param array $values
    	 * @return boolean Success
    	 */
    	function create(&$model, $fields = null, $values = null) {
    		$id = null;
    		
    		
    		// if empty then use data in model
    		if ($fields == null) {
    			unset($fields, $values);
    			$fields = array_keys($model->data);
    			$values = array_values($model->data);
    		}
    		$count = count($fields);
    		
    		// get connection parameters
    		$fm_layout = $model->defualtLayout;
    		$fm_database = $model->fmDatabaseName;
    		
    		// set basic connection data
    		$this->connection->SetDBData($fm_database, $fm_layout);
    		
    		
    		// if by chance the recid was passed to this create method we want
    		// to make sure we remove it as filemaker will reject the request.
    		if(isset($model->fm_recid) && !empty($model->fm_recid)) {
    			foreach($fields as $index => $field) {
    				if($field == $model->fm_recid) {
    					unset($fields[$index]);
    					unset($values[$index]);
    				}
    			}
    		}
    				
    		foreach($fields as $index => $field) {
    			$this->connection->AddDBParam($field, $values[$index]);
    		}
    		
    		// perform creation
    		
    		$return = $this->connection->FMNew();
    		
    		if(!$this->handleFXResult($return, $model->name, 'new')) {
    			return FALSE;
    		}
    		
    		
    		if($return['errorCode'] != 0) {
    			return false;
    		}
    		
    		
    		
    		
    		// write recid to model id and __lastinsert attributes
    		foreach($return['data'] as $recmodid => $returnedModel){
    			$recmodid_Ary = explode('.', $recmodid);
    			$model->id = $recmodid_Ary[0];
    			$model->setInsertID($recmodid_Ary[0]);
    		}
    		
    		$resultsOut = array();
    		if(!empty($return['data'])) {
    			foreach($return['data'] as $recmodid => $recordData) {
    				$recmodid_Ary = explode('.', $recmodid);
    				$resultsOut[$model->name]['-recid'] = $recmodid_Ary[0];
    				$resultsOut[$model->name]['-modid'] = $recmodid_Ary[1];
    				
    				foreach($recordData as $field => $value) {
    					$resultsOut[$model->name][$field] = $value[0];
    				}
    			}
    		}
    		
    		$model->data  = $resultsOut; // this returns data on a create
    		
    		return true;
    	}
    	
    	
    	/**
    	 * The "U" in CRUD
    	 * This could be collapsed under create, for now it's separate for better debugging
    	 * It's important to note that edit requires a FileMaker -recid that should be
    	 * passed as a hidden form field
    	 *
    	 * @param Model $model
    	 * @param array $fields
    	 * @param array $values
    	 * @param mixed $conditions
    	 * @return array
    	 */
    	function update(&$model, $fields = array(), $values = null, $conditions = null) {
    		
    		
    		// get connection parameters
    		$fm_layout = $model->defualtLayout;
    		$fm_database = $model->fmDatabaseName;
    		
    		if(!empty($model->id)) {
    			
    			// set basic connection data
    			$this->connection->SetDBData($fm_database, $fm_layout);
    			
    			// **1 here we remove the primary key field if it's marked as readonly 
    			// other fields can be removed by the controller, but cake requires
    			// the primary key to be included in the query if it's to consider
    			// the action an edit
    			foreach($fields as $index => $field) {
    				if(isset($model->primaryKeyReadOnly) && $field == $model->primaryKey) {
    					unset($fields[$index]);
    					unset($values[$index]);
    				}
    			}
    			
    			// ensure that a recid is passed
    			if(!in_array('-recid',$fields)) {
    				array_push($fields, '-recid');
    				array_push($values, $model->getId());
    			}
    			
    			// there must be a -recid field passed in here for the edit to work
    			// could be passed in hidden form field
    			foreach($fields as $index => $field) {
    				$this->connection->AddDBParam($field, $values[$index]);
    			}
    
    			// perform edit
    			$return = $this->connection->FMEdit();
    			
    			if(!$this->handleFXResult($return, $model->name, 'update')) {
    				return FALSE;
    			}
    			
    			
    			if($return['errorCode'] != 0) {
    				return false;
    			} else {
    				
    				foreach($return['data'] as $recmodid => $returnedModel){
    					$recmodid_Ary = explode('.', $recmodid);
    					$model->id = $recmodid_Ary[0];
    					$model->setInsertID($recmodid_Ary[0]);
    				}
    				
    				return true;
    			}
    		} else {
    			return false;
    		}
    	}
    	
    	/**
    	 * Returns an array of the fields in given table name.
    	 *
    	 * @param string $model the model to inspect
    	 * @return array Fields in table. Keys are name and type
    	 */
    	function describe(&$model) {
    		
    		// describe caching
    		$cache = $this->__describeFromCache($model);
    		if ($cache != null) {
    			return $cache;
    		}
    		
    		$fm_layout = $model->defualtLayout;
    		$fm_database = $model->fmDatabaseName;
    		
    		// set basic connection data
    		$this->connection->SetDBData($fm_database, $fm_layout);
    		
    		// get layout info
    		$result = $this->connection->FMFindAny(true, 'basic');
    		
    		// check for error
    		if(!$this->handleFXResult($result, $model->name, 'describe')) {
    			return FALSE;
    		}
    		
    		$fieldsOut = array();
    		
    		$fmFieldTypeConversion = array(
    			'TEXT' => 'string',
    			'DATE' => 'date',
    			'TIME' => 'time',
    			'TIMESTAMP' => 'timestamp',
    			'NUMBER' => 'float',
    			'CONTAINER' => 'binary'
    		);
    		
    		
    		foreach($result['fields'] as $field) {
    			$type = $fmFieldTypeConversion[$field['type']];
    			$fieldsOut[$field['name']] = array(
    				'type' => $type, 		
    				'null' => null, 
    				'default' => null, 
    				'length' => null, 
    				'key' => null
    			);
    			
    		}
    		
    		$fieldsOut['-recid'] = array(
    			'type' => 'integer', 		
    			'null' => null, 
    			'default' => null, 
    			'length' => null, 
    			'key' => null
    		);
    		
    		$fieldsOut['-modid'] = array(
    			'type' => 'integer', 		
    			'null' => null, 
    			'default' => null, 
    			'length' => null, 
    			'key' => null
    		);
    		
    		
    		$this->__cacheDescription($this->fullTableName($model, false), $fieldsOut);
    		return $fieldsOut;
    		
    		
    	}
    	
    	/**
    	 * __describeFromCache
    	 * looks for and potentially returns the cached description of the model
    	 * 
    	 * @param $model
    	 * @return the models cache description or null if none exists
    	 */
    	function __describeFromCache($model) {
    		
    		if ($this->cacheSources === false) {
    			return null;
    		}
    		if (isset($this->__descriptions[$model->tablePrefix . $model->table])) {
    			return $this->__descriptions[$model->tablePrefix . $model->table];
    		}
    		$cache = $this->__cacheDescription($model->tablePrefix . $model->table);
    
    		if ($cache !== null) {
    			$this->__descriptions[$model->tablePrefix . $model->table] =& $cache;
    			return $cache;
    		}
    		return null;
    	}
    	
    	/**
    	 * __cacheDescription
    	 * 
    	 * @param string $object : name of model
    	 * @param mixed $data : the data to be cached
    	 * @return mixed : the cached data
    	 */
    	function __cacheDescription($object, $data = null) {
    		if ($this->cacheSources === false) {
    			return null;
    		}
    
    		if ($data !== null) {
    			$this->__descriptions[$object] =& $data;
    		}
    
    		$key = ConnectionManager::getSourceName($this) . '_' . $object;
    		$cache = Cache::read($key, '_cake_model_');
    		
    
    		if (empty($cache)) {
    			$cache = $data;
    			Cache::write($key, $cache, '_cake_model_');
    		}
    
    		return $cache;
    	}
    
    
        /**
         * GenerateAssociationQuery
         */    
        function generateAssociationQuery(& $model, & $linkModel, $type, $association = null, $assocData = array (), & $queryData, $external = false, & $resultSet) { 
             
             
            switch ($type) { 
                case 'hasOne' : 
    
                    return null; 
                     
                case 'belongsTo' : 
    				
                    $id = $resultSet[$model->name][$assocData['foreignKey']]; 
    				$queryData['conditions'] = array(trim($linkModel->primaryKey) => trim($id));
    				$queryData['order'] = array();
    				$queryData['fields'] = '';
                    $queryData['limit'] = 1;
    				
                    return $queryData; 
                     
                case 'hasMany' : 
    				
                    $id = $resultSet[$model->name][$model->primaryKey]; 
                    $queryData['conditions'] = array(trim($assocData['foreignKey']) => trim($id));
    				$queryData['order'] = array();
    				$queryData['fields'] = ''; 
                    $queryData['limit'] = $assocData['limit']; 
    
                    return $queryData; 
    
                case 'hasAndBelongsToMany' : 
                    return null; 
            } 
            return null; 
        } 
    
    	/**
    	 * QueryAssociation
    	 * 
    	 */
    	
        function queryAssociation(& $model, & $linkModel, $type, $association, $assocData, & $queryData, $external = false, & $resultSet, $recursive, $stack) { 
            
    		
    		 
    		foreach($resultSet as $projIndex => $row) {
    			$queryData = $this->generateAssociationQuery($model, $linkModel, $type, $association, $assocData, $queryData, $external, $row);
    		
    			$associatedData = $this->readAssociated($linkModel, $queryData, 0);
    			
    			foreach($associatedData as $assocIndex => $relatedModel) {
    				$modelName = key($relatedModel);
    				$resultSet[$projIndex][$modelName][$assocIndex] = $relatedModel[$modelName];
    			}
    		}
    		
    		
        } 
    
    	/** 
         * readAssociated
         * very similar to read but for related data
         * unlike read does not make a reference to the passed model
         * 
         * @param Model $model 
         * @param array $queryData 
         * @param integer $recursive Number of levels of association 
         * @return unknown 
         */ 
        function readAssociated($linkedModel, $queryData = array (), $recursive = null) { 
    		
    		
    		$fm_layout = $linkedModel->defualtLayout;
    		$fm_database = $linkedModel->fmDatabaseName;
    		$queryLimit = $queryData['limit'] == null ? 'all' : $queryData['limit'];
    		
    		
    		// set basic connection data
    		$this->connection->SetDBData($fm_database, $fm_layout, $queryLimit );
    		
    		
    		// add the params
    		if(!empty($queryData['conditions'])) {
    			
    			
    			foreach($queryData['conditions'] as $conditionField => $conditionValue) {
    				$string = $conditionField;
    				$pattern = '/(\w+)\.(-*\w+)$/i';
    				$replacement = '${2}';
    				$plainField = preg_replace($pattern, $replacement, $string);
    				$this->connection->AddDBParam($plainField, $conditionValue, 'eq');
    			}
    		}
    		
    		// set sort order
    		foreach($queryData['order'] as $orderCondition) {
    			if(!empty($orderCondition)){
    				foreach($orderCondition as $field => $sortRule) {
    					$string = $field;
    					$pattern = '/(\w+)\.(-*\w+)$/i';
    					$replacement = '${2}';
    					$plainField = preg_replace($pattern, $replacement, $string);
    					
    					$sortRuleFm = $sortRule == 'desc' ? 'descend' : 'ascend';
    					$this->connection->AddSortParam($plainField, $sortRuleFm);
    				}
    			}
    		}
    		
    		// set skip records if there is an offset
    		if(!empty($queryData['offset'])) {
    			$this->connection->FMSkipRecords($queryData['offset']);
    		}
    		
    		// THIS MAY NOT BE NECESSARY FOR THE READASSOCIATED FUNCTION
    		// return a found count if requested
    		if($queryData['fields'] == 'COUNT') {
    			// perform find without returning result data
    			$fmResults = $this->connection->FMFind(true, 'basic');
    			
    			// check for error
    			if(!$this->handleFXResult($fmResults, $linkedModel->name, 'readassociated (count)')) {
    				return FALSE;
    			}
    			
    			$countResult = array();
    			$countResult[0][0] = array('count' => $fmResults['foundCount']);
    			
    			// return found count
    			return $countResult;
    		} else {
    			// perform the find in FileMaker
    			$fmResults = $this->connection->FMFind();
    			
    			// check for error
    			if(!$this->handleFXResult($fmResults, $linkedModel->name, 'readassociated')) {
    				return FALSE;
    			}
    		}
    		
    		$resultsOut = array();
    		
    		// format results
    		if(!empty($fmResults['data'])) {
    			$i = 0;
    			foreach($fmResults['data'] as $recmodid => $recordData) {
    				$relatedModels = array();
    				$recmodid_Ary = explode('.', $recmodid);
    				$resultsOut[$i][$linkedModel->name]['-recid'] = $recmodid_Ary[0];
    				$resultsOut[$i][$linkedModel->name]['-modid'] = $recmodid_Ary[1];
    				foreach($recordData as $field => $value) {
    					// if $field is not a related entity
    					if(strpos($field, '::') === false) {
    						// grab table field data (grabs first repitition)
    						$resultsOut[$i][$linkedModel->name][$field] = $value[0];
    					} else {
    					}
    				}
    			$i++;
    			}
    		} else {
    			
    		}
    		
    		return $resultsOut;
    		
        }
    
    	/**
    	 * Gets full table name including prefix
    	 *
    	 * @param mixed $model
    	 * @param boolean $quote
    	 * @return string Full quoted table name
    	 */
    	function fullTableName($model, $quote = true) {
    		if (is_object($model)) {
    			$table = $model->tablePrefix . $model->table;
    		} elseif (isset($this->config['prefix'])) {
    			$table = $this->config['prefix'] . strval($model);
    		} else {
    			$table = strval($model);
    		}
    		if ($quote) {
    			return $this->name($table);
    		}
    		return $table;
    	}
    
         
        /** 
         * Returns a formatted error message from previous database operation. 
         * 
         * @return string Error message with error number 
         */ 
        function lastError() { 
            if (FX::isError($this->lastFXError)) { 
                return $this->lastFXError.getCode() . ': ' . $this->lastFXError.getMessage(); 
            } 
            return null; 
        } 
    
    	/**
    	 * handleFXResult
    	 * 
    	 * logs queries, logs errors, and returns false on error
    	 * 
    	 * @param FX result object or FX error object
    	 * @param string : model name
    	 * @param string : action name
    	 * 
    	 * @return false if result is an FX error object
    	 */
    	function handleFXResult($result, $modelName = 'N/A', $actionName = 'N/A') {
    		
    		
    		$this->_queriesCnt++;
    		
    		// if a connection error
    		if(FX::isError($result)) {
    			
    			// log error
    			$this->_queriesLog[] = array(
    				'model' 	=> $modelName,
    				'action' 	=> $actionName,
    				'query' 	=> '',
    				'error'		=> $result->toString(),
    				'numRows'	=> '',
    				'took'		=> round((getMicrotime() - $this->timeFlag) * 1000, 0)
    			);
    			if (count($this->_queriesLog) > $this->_queriesLogMax) {
    				array_pop($this->_queriesLog);
    			}
    			
    			$this->timeFlag = getMicrotime();
    			return FALSE;
    		
    		// if a filemaker error other than no records found
    		} elseif ($result['errorCode'] != 0 && $result['errorCode'] != 401)	{
    		
    			// log error
    			$this->_queriesLog[] = array(
    				'model' 	=> $modelName,
    				'action' 	=> $actionName,
    				'query' 	=> substr($result['URL'],strrpos($result['URL'], '?')),
    				'error'		=> $result['errorCode'],
    				'numRows'	=> '',
    				'took'		=> round((getMicrotime() - $this->timeFlag) * 1000, 0)
    			);
    			if (count($this->_queriesLog) > $this->_queriesLogMax) {
    				array_pop($this->_queriesLog);
    			}
    			
    			$this->timeFlag = getMicrotime();
    			return FALSE;
    		} else {
    			
    			// log query
    			$this->_queriesLog[] = array(
    				'model' 	=> $modelName,
    				'action' 	=> $actionName,
    				'query' 	=> substr($result['URL'],strrpos($result['URL'], '?')),
    				'error'		=> $result['errorCode'],
    				'numRows'	=> isset($result['data']) ? count($result['data']) : $result['foundCount'],
    				'took'		=> round((getMicrotime() - $this->timeFlag) * 1000, 0)
    			);
    			
    			$this->timeFlag = getMicrotime();
    			return TRUE;
    		}
    	}
    	
    
        /** 
         * Returns number of rows in previous resultset. If no previous resultset exists, 
         * this returns false. 
         * NOT USED
         * 
         * @return int Number of rows in resultset 
         */ 
        function lastNumRows() { 
            return null; 
        } 
         
         
        /** 
         * NOT USED
         */ 
        function execute($query) { 
            return null; 
        } 
         
        /** 
         * NOT USED 
         */ 
        function fetchAll($query, $cache = true) { 
            return array(); 
        } 
         
        // Logs -------------------------------------------------------------- 
        /** 
         * logQuery
         */ 
        function logQuery($query) {
    	}
         
        /** 
         * Outputs the contents of the queries log.
         * 
         * @param boolean $sorted 
         */ 
        function showLog() {
    		
    		$log = $this->_queriesLog;
    		
    		$totalTime = 0;
    		foreach($log as $entry) {
    			$totalTime += $entry['took'];
    		}
    		
    		
    
    		if ($this->_queriesCnt > 1) {
    			$text = 'queries';
    		} else {
    			$text = 'query';
    		}
    
    		if (PHP_SAPI != 'cli') {
    			print ("<table class=\"cake-sql-log\" id=\"cakeSqlLog_" . preg_replace('/[^A-Za-z0-9_]/', '_', uniqid(time(), true)) . "\" summary=\"Cake SQL Log\" cellspacing=\"0\" border = \"0\">\n<caption>({$this->configKeyName}) {$this->_queriesCnt} {$text} took {$totalTime} ms</caption>\n");
    			print ("<thead>\n<tr><th>Nr</th><th>Model</th><th>Action</th><th>Query</th><th>Error</th><th>Num. rows</th><th>Took (ms)</th></tr>\n</thead>\n<tbody>\n");
    			
    			foreach ($log as $k => $i) {
    				print ("<tr><td>" . ($k + 1) . "</td><td>{$i['model']}</td><td>{$i['action']}</td><td>" . h($i['query']) . "</td><td>{$i['error']}</td><td style = \"text-align: right\">{$i['numRows']}</td><td style = \"text-align: right\">{$i['took']}</td></tr>\n");
    			}
    			print ("</tbody></table>\n");
    			
    		} else {
    			foreach ($log as $k => $i) {
    				print (($k + 1) . ". {$i['query']} {$i['error']}\n");
    			}
    		}
    	}
    
        /** 
         * Output information about a query
         * NOT USED
         * 
         * @param string $query Query to show information on. 
         */ 
        function showQuery($query) { 
            
        } 
         
    
         
    
    } 
    ?>


`1`_|`2`_|`3`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_

.. _http://www.iviking.org/FX.php/: http://www.iviking.org/FX.php/
.. _Page 3: :///articles/view/4caea0e4-6404-4b72-a009-4a3882f0cb67/lang:eng#page-3
.. _http://github.com/alexgb/FMCakeMix/tree/master: http://github.com/alexgb/FMCakeMix/tree/master
.. _Page 2: :///articles/view/4caea0e4-6404-4b72-a009-4a3882f0cb67/lang:eng#page-2
.. _Page 1: :///articles/view/4caea0e4-6404-4b72-a009-4a3882f0cb67/lang:eng#page-1

.. author:: alexg
.. categories:: articles, models
.. tags:: datasource,filemaker,Models

