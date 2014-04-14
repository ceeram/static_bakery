Sqlscripts Task
===============

by dho on October 25, 2006

The "Sqlscripts Task" is a simple bake task that creates "create
table" and "drop table" statements in app/config/sql/create.sql resp.
app/config/sql/drop.sql. Installation: Copy the script to
/vendors/tasks/sqlscripts_task.php Usage: php bake2.php sqlscripts
[app-alias] table1 table2 ...

::

    
    <?php
    	/** 
    	 * Sqlscripts Task. This task creates instructions to create and drop tables. Those instructions are
    	 * stored in app/config/sql/create.sql resp. app/config/sql/drop.sql . 
    	 *
    	 * Copyright: Daniel Hofstetter  (http://cakebaker.42dh.com)
    	 * License: MIT
    	*/
    
    	define('SQL_DIR', CONFIGS.'sql'.DS);
    	uses('Inflector');
    	
    	class SqlscriptsTask extends BakeTask {
    		
    		function execute($params) {
    			
    			if (count($params) > 0) {
    				$this->createFile(SQL_DIR.'create.sql', $this->createContentForCreateSQL($params));
    				$this->createFile(SQL_DIR.'drop.sql', $this->createContentForDropSQL($params));
    			} else {
    				$this->help();
    			}
    		}
    	
    		function help() {
    			echo "The sqlscripts task creates instructions to create and drop tables. \n";
    			echo "Usage: bake2 sqlscripts [app-alias] table1 table2 ... \n";
    		}
    		
    		function createFile($path, $content) {
    		
    			if ($f = fopen($path, 'a')) {
    				fwrite($f, $content);
    				fclose($f);
    				
    				return true;
    			}
    			
    			return false;
    		}
    		
    		function createContentForCreateSQL($tableNames) {
    			$content = '';
    			
    			foreach ($tableNames as $tableName) {
    				$content .= "CREATE TABLE " . $tableName . " (\n";
    				
    				if (strpos($tableName, '_') === false) {
    					$content .= $this->getContentForTable();
    				} else {
    					$content .= $this->getContentForAssociationTable($tableName);
    				}
    				$content .= "); \n\n";
    			}
    			
    			return $content;
    		}
    		
    		function createContentForDropSQL($tableNames) {
    			$content = '';
    			
    			foreach ($tableNames as $tableName) {
    				$content .= "DROP TABLE IF EXISTS " . $tableName . ";\n\n";
    			}
    			
    			return $content;
    		}
    		
    		function getContentForAssociationTable($tableName) {
    			$associatedTables = explode('_', $tableName);
    			$ids = array();
    			$content = '';
    			
    			foreach ($associatedTables as $table) {
    				$modelName = Inflector::singularize($table);
    				$content .= "  " . $modelName . "_id INT(11) NOT NULL, \n";
    				$ids[] = $modelName . '_id';
    			}
    			
    			$content .= "  PRIMARY KEY (". implode(', ', $ids) . ")\n";
    			
    			return $content;
    		}
    		
    		function getContentForTable() {
    			$content = "  id INT(11) NOT NULL AUTO_INCREMENT, \n";
    			$content .= "  created DATETIME, \n";
    			$content .= "  modified DATETIME, \n";
    			$content .= "  PRIMARY KEY (id) \n";
    			
    			return $content;
    		}
    	}
    ?>


.. meta::
    :title: Sqlscripts Task
    :description: CakePHP Article related to bake,sql,task,table,Snippets
    :keywords: bake,sql,task,table,Snippets
    :copyright: Copyright 2006 dho
    :category: snippets

