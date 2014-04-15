A CakePHP ADODB data source driver for MS Access
================================================

by aspeakman on December 29, 2008

This is an ADODB data source driver to connect to an MS Access 2003
database. It has been tested for simple read operations, but not for
write/update.


Database Configuration
----------------------

::

    
    <?php
    var $default = array('driver' => 'adodb_access',
    'connect' => 'access',
    'host' => 'Driver={Microsoft Access Driver (*.mdb)};Dbq=C:\DOCUMENTS AND SETTINGS\User\My Documents\Test.mdb;',
    'login' => 'Admin',
    'password' => '',
    'database' => 'default',
    'prefix' => 'tbl');
    ?>



Code
----

Here is the code which goes in
app/models/datasources/dbo/dbo_adodb_access.php

::

    
    <?php
    /**
     * Include AdoDB files.
     */
    App::import('Vendor', 'NewADOConnection', array('file' => 'adodb' . DS . 'adodb.inc.php'));
    
    /**
     * Included cake libraries
     */
    uses ('model' . DS . 'datasources' . DS . 'dbo' . DS . 'dbo_adodb');
    
    /**
     * MS Access AdoDB DBO implementation.
     *
     * Database abstraction implementation for the AdoDB library.
     *
     * @package		cake
     * @subpackage	cake.app.models.datasources.dbo
     */
    class DboAdodbAccess extends DboAdodb {
    /**
     * Enter description here...
     *
     * @var string
     */
    	 var $description = "ADOdb DBO Driver for MS Access";
    
       
       /**
     * Returns an array of the fields in the table used by the given model.
     *
     * @param AppModel $model Model object
     * @return array Fields in table. Keys are name and type
     */
    	function describe(&$model) {
    		$cache = DataSource::describe($model);
    		if ($cache != null) {
    			return $cache;
    		} 
    
    		$fields = false;
    		$cols = $this->_adodb->MetaColumns($this->fullTableName($model, false));
    
    		foreach ($cols as $column) {
    			$fields[$column->name] = array(
    										'type' => $this->column($column->type),
    										'null' => !$column->not_null,
    										'length' => $column->max_length,
    									);
    			/* extra test otherwise causes ADOFieldObject error - the properties do not exist */
          if (isset($column->has_default) && $column->has_default) {
    				$fields[$column->name]['default'] = $column->default_value;
    			}
    			if (isset($column->primary_key) && $column->primary_key == 1) {
    				$fields[$column->name]['key'] = 'primary';
    			}
    		}
    
    		$this->__cacheDescription($this->fullTableName($model, false), $fields);
    		return $fields;
    	}
    
    /**
     * Returns a limit statement in the correct format for the particular database.
     *
     * @param integer $limit Limit of results returned
     * @param integer $offset Offset from which to start results
     * @return string SQL limit/offset statement
     */
    	function limit($limit, $offset = null) {
    		if ($limit) {
    			$rt = '';
    			if (!strpos(strtolower($limit), 'top') || strpos(strtolower($limit), 'top') === 0) {
    				$rt = ' TOP';
    			}
    			$rt .= ' ' . $limit;
    			if (is_int($offset) && $offset > 0) {
    				$rt .= ' OFFSET ' . $offset;
    			}
    			return $rt;
    		}
    		return null;
    	}
    
    /**
     * Returns a quoted and escaped string of $data for use in an SQL statement.
     *
     * @param string $data String to be prepared for use in an SQL statement
     * @param string $column_type The type of the column into which this data will be inserted
     * @param boolean $safe Whether or not numeric data should be handled automagically if no column data is provided
     * @return string Quoted and escaped data
     */
    	function value($data, $column = null, $safe = false) {
    		$parent = DboSource::value($data, $column, $safe);
    		if ($parent != null) {
    			return $parent;
    		}
    
    		if ($data === null) {
    			return 'NULL';
    		}
    
    		if ($data === '') {
    			return "''";
    		}
        
    		switch($column) {
    			case 'boolean':
    				$data = $this->boolean((bool)$data);
    			break;
    			default:
    				if (get_magic_quotes_gpc()) {
    					$data = stripslashes(str_replace("'", "''", $data));
    				} else {
    					$data = str_replace("'", "''", $data);
    				}
    			break;
    		}
    
    		if ((in_array($column, array('integer', 'float')) && is_numeric($data))
          || (empty($column) && is_numeric($data))) {
    			return $data;
    		}
    		return "'" . $data . "'";
    	}
      
     /**
     * Builds final SQL statement
     *
     * @param string $type Query type
     * @param array $data Query data
     * @return string
     */
    	function renderStatement($type, $data) {
    		switch (strtolower($type)) {
    			case 'select':
    				extract($data);
    				$fields = trim($fields);
    
    				if (strpos($limit, 'TOP') !== false && strpos($fields, 'DISTINCT ') === 0) {
    					$limit = 'DISTINCT ' . trim($limit);
    					$fields = substr($fields, 9);
    				}
            return "SELECT {$limit} {$fields} FROM {$table} {$alias} {$joins} {$conditions} {$group} {$order}";
    			break;
    			default:
    				return DboSource::renderStatement($type, $data);
    			break;
    		}
    	}
    
    /**
     * Removes Identity (primary key) column from update data before returning to parent
     *
     * @param Model $model
     * @param array $fields
     * @param array $values
     * @return array
     */
      function update(&$model, $fields = array(), $values = array()) {
    		foreach ($fields as $i => $field) {
    			if ($field == $model->primaryKey) {
    				unset ($fields[$i]);
    				unset ($values[$i]);
    				break;
    			}
    		}
    		return DboSource::update($model, $fields, $values);
    	}
      
      function buildStatement($query, $model) {
        $join_parentheses = '';
        $query = array_merge(array('offset' => null, 'joins' => array()), $query);
    		if (!empty($query['joins'])) {
    			for ($i = 0; $i < count($query['joins']); $i++) {
    				if (is_array($query['joins'][$i])) {
    					$query['joins'][$i] = $this->buildJoinStatement($query['joins'][$i]);
    					if ($i > 0) $join_parentheses = $join_parentheses . '(';
    				}
    			}
    		}
        $join_parentheses = $join_parentheses . ' ';
    		return $this->renderStatement('select', array(
    			'conditions' => $this->conditions($query['conditions']),
    			'fields' => join(', ', $query['fields']),
    			'table' => $join_parentheses . $query['table'],
    			'alias' => $this->alias . $this->name($query['alias']),
    			'order' => $this->order($query['order']),
    			'limit' => $this->limit($query['limit'], $query['offset']),
    			'joins' => join(' ) ', $query['joins']),
          'group' => $this->group($query['group'])
    		));
    	} 
      
      function renderJoinStatement($data) {
    		extract($data);
        if (empty($type)) {
            return trim("INNER JOIN {$table} {$alias} ON ({$conditions})");
          } else {
            return trim("{$type} JOIN {$table} {$alias} ON ({$conditions})");
          }
    	} 
      
    }
    ?>



.. author:: aspeakman
.. categories:: articles, models
.. tags:: adodb,ms access,Models

