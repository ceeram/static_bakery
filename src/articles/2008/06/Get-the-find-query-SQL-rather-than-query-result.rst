Get the find query SQL, rather than query result
================================================

by grant_cox on June 23, 2008

There are times when you need custom SQL queries, but where much of
this query could be handled by Cake - the prime example of this is
subselects. This snippet extends on the original solution provided by
Yevgeny Tomenko [url]http://bakery.cakephp.org/articles/view
/extending-of-dbosource-and-model-with-sql-generator-function[/url] ,
but updated to support Cake 1.2 RC1 syntax.
This solution will allow you to retrieve the SQL for any given find
query, through the standard find() syntax, just using 'sql' rather
than 'all'

::

    $this->Model->find('sql', $options);



Example
~~~~~~~
This allows you to do queries like:

PHP Snippet:
````````````

::

    <?php $event_options = array(
    	'conditions' => array(
    		'Event.execute_date < NOW()',
    		'Event.is_triggered' => 0
    	),
    	'fields' => 'Event.campaign_id',
    	'recursive' => -1
    );
    $campaign_ids_sql = $this->Event->find('sql', $event_options);
    
    // SELECT `Event`.`campaign_id` FROM `events` AS `Event` 
    // WHERE `Event`.`execute_date` < NOW() 
    // AND `Event`.`is_triggered` = 0
    
    $user_options = array(
    	'conditions' => array(
    		'Group.campaign_id IN ('.$campaign_ids_sql.')'
    	),
    	'contain' => 'Group',
    	'fields' => 'User.id'
    );
    $user_ids = $this->User->find('all', $user_options);
    
    // SELECT `User`.`id` FROM `users` AS `User` 
    // LEFT JOIN `groups` AS `Group` ON (`User`.`group_id` = `Group`.`id`) 
    // WHERE `Group`.`campaign_id` IN (
    //   SELECT `Event`.`campaign_id` FROM `events` AS Event 
    //   WHERE `Event`.`execute_date` < NOW() AND `Event`.`is_triggered` = 0 
    // )
    ?>

Now in this example we could likely have retrieved the same data
efficiently using the Cake associations, with the Containable
behaviour limiting the data returned. However, if this first query
(finding the campaign ids) were to return many results (> 1000), the
standard Cake queries will become inefficient due to transmitting a
lot of data between the database and PHP, and due to large queries
("WHERE `id` IN (100,101,102,103...10001,10002)" can be quite slow).

Notes: Only the main query on the primary model will be returned - the
subsequent queries to find associated hasMany / hasAndBelongsToMany
models will not be included.
If you plan to use a query in a SUBSELECT, make sure that it is only
retrieving a single column - MySQL will not allow the inner query to
retrieve more. To do this, ensure that you have set 'recursive'=>-1 in
your query options.


Installation
~~~~~~~~~~~~
To install this functionality, there are three parts.


#. Add a new DBO driver
#. Use this DBO driver in your database.php configuration
#. Add support for the find('sql') to your AppModel



1. Add a new DBO driver
```````````````````````
Save the following as /app/models/datasources/dbo/dbo_mysql_ex.php

PHP Snippet:
````````````

::

    <?php 
    require (LIBS . 'model' . DS . 'datasources' . DS . 'dbo' . DS . 'dbo_mysql.php');
    
    class DboMysqlEx extends DboMysql {
    
    	var $description = "MySQL DBO Extension Driver";
    
    	function read(&$model, $queryData = array(), $recursive = null)
    	{
    		// only handle queries for the SQL
    		if ( empty($queryData['returnSQL']) ){
    			return parent::read($model, $queryData, $recursive);
    		}
    
    		// the following is just copied from the /cake/libs/model/datasources/dbo_source.php read function
    		$queryData = $this->__scrubQueryData($queryData);
    		$null = null;
    		$array = array();
    		$linkedModels = array();
    		$this->__bypass = false;
    		$this->__booleans = array();
    
    		if ($recursive === null && isset($queryData['recursive'])) {
    			$recursive = $queryData['recursive'];
    		}
    
    		if (!is_null($recursive)) {
    			$_recursive = $model->recursive;
    			$model->recursive = $recursive;
    		}
    
    		if (!empty($queryData['fields'])) {
    			$this->__bypass = true;
    			$queryData['fields'] = $this->fields($model, null, $queryData['fields']);
    		} else {
    			$queryData['fields'] = $this->fields($model);
    		}
    
    		foreach ($model->__associations as $type) {
    			foreach ($model->{$type} as $assoc => $assocData) {
    				if ($model->recursive > -1) {
    					$linkModel =& $model->{$assoc};
    					$external = isset($assocData['external']);
    
    					if ($model->useDbConfig == $linkModel->useDbConfig) {
    						if (true === $this->generateAssociationQuery($model, $linkModel, $type, $assoc, $assocData, $queryData, $external, $null)) {
    							$linkedModels[] = $type . '/' . $assoc;
    						}
    					}
    				}
    			}
    		}
    
    		$query = $this->generateAssociationQuery($model, $null, null, null, null, $queryData, false, $null);
    
    		// restore the recursive level
    		if (!is_null($recursive)) {
    			$model->recursive = $_recursive;
    		}
    
    		// but return this query instead of fetching it
    		return $query;
    	}
    }
    ?>

This class extends functionality from dbo_source.php, and has nothing
specific to MySQL. So if you are using a different database driver,
you can modify this class to extend the appropriate driver.


2. Use this DBO driver in your database.php configuration
`````````````````````````````````````````````````````````
Edit your /app/config/database.php file, and change the 'driver' to
'mysql_ex'


PHP Snippet:
````````````

::

    <?php 
    	var $default = array(
    		'driver' => 'mysql_ex',
    		'persistent' => false,
    		'host' => 'localhost',
    		'login' => 'user',
    		'password' => 'password',
    		'database' => 'database_name',
    		'prefix' => '',
    	);
    ?>



3. Add support for the find('sql') to your AppModel
```````````````````````````````````````````````````
Edit your /app/app_model.php file, to add the following. If you do not
have a /app/app_model.php file, copy the stub from
/cake/libs/model/app_model.php to your app folder, then edit it.


Model Class:
````````````

::

    <?php 
    class AppModel extends Model{
    
    	function __construct($id = false, $table = null, $ds = null) {
    		$this->__findMethods['sql'] = true;
    		parent::__construct($id, $table, $ds);
    	}
    
    	function _findSql($state, $query, $results = array()) {
    		if ($state == 'before') {
    			$query['returnSQL'] = true;
    			return $query;
    
    		} elseif ($state == 'after') {
    			return $results;
    		}
    	}
    }
    ?>


And now you're done - you can use the find('sql') syntax.


4. Optional extension
`````````````````````
If you plan to use these subselects across multiple databases (which
must still be on the same server, and be accessible by the same
authentication details), you can use the following version of the
AppModel updates - which will prefix the table name with the database
name.


Model Class:
````````````

::

    <?php 
    class AppModel extends Model{
    
    	var $_tablePrefix = null;
    
    	function __construct($id = false, $table = null, $ds = null) {
    		$this->__findMethods['sql'] = true;
    		parent::__construct($id, $table, $ds);
    	}
    
    	function _findSql($state, $query, $results = array()) {
    		if ($state == 'before') {
    			// prepend the table with the database name
    			$this->_tablePrefix = $this->tablePrefix;
    
    			$conn =& ConnectionManager::getInstance();
    			$db_name = $conn->config->{$this->useDbConfig}['database'];
    			$this->tablePrefix = $db_name .'.'. $this->_tablePrefix;
    
    			$query['returnSQL'] = true;
    			return $query;
    
    		} elseif ($state == 'after') {
    			$this->tablePrefix = $this->_tablePrefix;
    			return $results;
    		}
    	}
    }
    ?>




.. meta::
    :title: Get the find query SQL, rather than query result
    :description: CakePHP Article related to find query sql dbo,Snippets
    :keywords: find query sql dbo,Snippets
    :copyright: Copyright 2008 grant_cox
    :category: snippets

