

MySchemaShell - step closer to DB structure synchronization
===========================================================

by %s on May 12, 2009

I'm struggling with DB structure versions management. Recently I tried
SchemaShell (cake schema), but found it quite inappropriate with the
way the SVN works. Additionally one could only overwrite DB structure
with schema file or overwrite schema file with database structure -
that's not good for me. In early development phase fluent DB structure
versions management is essential, and without decent tool - it's just
pain in the ass. MySchemaShell is my first step to creating that tool.
First approach I get quite familiar with was
`http://bakery.cakephp.org/articles/view/cakephp-yaml-migrations-and-
fixtures-without-pear`_, but when I tried this and SchemaSchell -
there was almost no differences between this two tools. Both YAML and
Schema approach have the sane cons (which I described in the intro),
but Schema was more Cake'ish so I took that on my workshop.

The basic problem was that it is possibly problematic with using it
with SVN. I couldn't think of any process without possible problems
when generating schema from actual database (which have potentially
changed development session):

#. Team could always overvite one schema.php file. But the problem
   would be with conflicts. The conflicts could be resolved like any
   other conflict, but everyone in team should be disciplined enough to
   call 'schema generate' before any 'svn up'. If one forget it, and call
   'svn up', make some DB changes 'call schema generate' and updated data
   are in some revision back there. So You can start for searching needle
   in the haystack... On the second hand - when some Peter add field in
   table 'X' and commit new schema, and I have some local changes in DB I
   need to choose:

    + call 'schema generate' and overwrite Peters changes - not an option
    + call 'schema run update' and choose [y] when it asks if I want to
      drop fields I've just added - eorse
    + manually edit schema.php file and add my new fields into it -
      possible but inconvenient

#. Team could always generate snapshots instead of overwriting one
   file. But I don't need schema_1.php, schema_2.php ... schema_234.php
   in my repository when I have versions in SVN - its just redundancy.
   But what if two developers call 'schema generate snapshot' and both
   get schema_7.php. The faster one have no problem. He just calls 'svn
   add' and commits. But second tries to commit and get error:
   schema_7.php already in repository. He can rename it to schema_8.php
   but need to merge this changes manually - still inconvienient way to
   that

Now I can get to the point. I needed that 'cake schema' would at least
ask me about ADD's and DROPS he is planning to do, and call only that
ADD's and DROP's I agree to. I want my schema to be only in one file
(particular version is still in particular revision).

So now the scenario is:

+ svn up
+ cake my_schema run update -with-check

-This is my_schema, do You want me to ADD `name` field do USERS?
- Yes
- my schema again - do You want me to DROP `just_added_field` from
`table_you_work_on_right_now`?
- Of course not.
- my_schema here, altering 'users' table, adding `name` field

+ my_schema generate (and choose overvrite when it asks)
+ Now I have merged changes from repo and my DB in schema.php, so can
  call 'svn ci'

Example of that scenario:

::

    
    ./cake my_schema run update -with-check
    
    
    Comparing Database to Schema...
    in articles table we are about to...
            ...ADD field(s) named...
                    ... dummy_add(type:integer; null:false; default:0)
    Do You agree ?
    [y] > y
                    ... dummy_add2(type:integer; null:false; default:0)
    Do You agree ?
    [y] > y
            ...DROP field(s) named...
                    ... dummy(type:integer; null:false; default:)
    Do You agree ?
    [n] > n
    in categories table we are about to...
            ...ADD field(s) named...
                    ... dummy2(type:integer; null:false; default:)
    Do You agree ?
    [y] > y
    
    The following statements will run.
    ALTER TABLE `articles`
            ADD `dummy_add` int(11) DEFAULT 0 NOT NULL,
            ADD `dummy_add2` int(11) DEFAULT 0 NOT NULL;
    ALTER TABLE `categories`
            ADD `dummy2` int(11) NOT NULL;
    
    Are you sure you want to alter the tables? (y/n)
    [n] >
    

I assume that ADD's are good, and DROP's are unwanted in most (of my)
cases. So its possible to make my_schema not to ask about anything and
remove all drops it could do in normal behavior:

::

    
    ./cake my_schema generate -with-check -add-only

Example:

::

    
    Comparing Database to Schema...
    
            ***Accorging to -add-only option there are 1 DROP'S ommited***
    
    The following statements will run.
    ALTER TABLE `articles`
            ADD `dummy_add` int(11) DEFAULT 0 NOT NULL,
            ADD `dummy_add2` int(11) DEFAULT 0 NOT NULL;
    ALTER TABLE `categories`
            ADD `dummy2` int(11) NOT NULL;
    
    Are you sure you want to alter the tables? (y/n)
    [n] >

If You want to use this tool - copy following code to
/app/vendors/shells/my_schema.php

Thank You for reading. Any suggestions are most welcome.
Greg


::

    
    <?php
    
    require_once(CONSOLE_LIBS . DS . 'schema.php');
    
    
    /**
     * SchemaSchell with basic synchronisation added
     *
     * @author        Grzegorz Pawlik <www.grzregorzpawlik.com>
     * @version 1.2 
     * @note since 1.1 CHANGE oprerations are allowed  by default
     *       since 1.2 Not existing tables are created instead of crashing
     * @todo dorobiÄ‡ moÅ¼liwoÅ›c wywoÅ‚ania update'u bazy razem z generowaniem tabel bez koniecznoÅ›ci ingerencji kogoÅ› z zewnÄ…trz (-ad-only)
     * @todo with CHANGE operation - show old AND new table definition
     */
    class MySchemaShell extends SchemaShell {
    
    /**
     * Override initialize
     *
     * @access public
     */
    	function initialize() {
    		$this->_welcome();
    		$this->out('MySchema Shell (extended Cake Schema Shell)');
    		$this->hr();
    	}
    	
    	/**
    	 * Convert field details array into readable string
    	 *
    	 * @param array $details 
    	 * @return string with one line readable details
    	 * @access public
    	 * @note maybe this functionallity should go to Set class?
    	 */
    	function __fieldDetailsToString($details) {
    	   $str = '';
    	   
    	   foreach($details as $name => $value) {
             $value = ($value===false)? 'false': $value;
    	      $str .= " ".$name.":".$value.";";
    	      
    	   }
    	   $str = trim($str, " ;");
    	   return $str;
    	}
    	
    	/**
    	 * Allows user to decide which changes in DB are ok
    	 *
    	 * @param array $compare result from Schema::compare()
    	 * @return array without unwanted changes
    	 * @access private
    	 */
    	function __humanCheck($compare) {
    	   $unset = 0;
    	   
    	   
    	   foreach($compare as $table => $changes) {
    	      $msg_table = "in ".$table . " table we are about to...\n";
    	      foreach($changes as $action => $field) {
    	         $msg_action = "\t...". strtoupper($action)." field(s) named...\n";
    	         foreach($field as $name=> $details) {
    	            $msg_field = "\t\t... " . $name . "(" . $this->__fieldDetailsToString($details). ")\n";
    	            $save_change = ($action=='add' || $action='change')? 'y' : 'n';
    	            
    	            /**
    	             * don't display messages, and dont ask for anything if -add-onlu passed
    	             */
    	            if( !(isset($this->params['add-only'])) ) {
    	              if($msg_table) {
       	              $this->out($msg_table, false);
       	              $msg_table = false;
    	              }
    	              if($msg_action) {
                        $this->out($msg_action, false);
                        $msg_action = false;
    	              }
    	              if($msg_field) {
                        $this->out($msg_field, false);
                        $msg_field = false;
    	              }
    	              $save_change = $this->in("Do You agree ?", null, $save_change);
    	            }
    	            
    	            if($save_change === 'n') {
    	               unset($compare[$table][$action][$name]);
    	               $unset++;
    	            }
    	            
    	         }
    	      }
    	   }
    
    	   
          if( isset($this->params['add-only']) && $this->params['add-only'] == 1 && $unset) {
             $this->out("\n\t***Accorging to -add-only option there are $unset DROP'S ommited***");
          }
    	   
    	   return $compare;
    	}
    	
    	function _createTablesIfNotExists($Schema) {
    	   $tables = Configure::listObjects('model') ;
    	   
          $db =& ConnectionManager::getDataSource($this->Schema->connection);
          
          $sources = $db->listSources();
          $create = array();
          foreach ($Schema->tables as $table => $fields) {
             if(!in_array($table, $sources )) {
                $create[$table] = $db->createSchema($Schema, $table);
             }
          }
    
          if(!empty($create)) {
             $this->__run($create, 'create', $Schema);
          }
          
    //      return $Schema;
    	}
    	
    /**
     * Update database with Schema object
     * Should be called via the run method
     *
     * @access private
     */
    	function __update($Schema, $table = null) {
    
          $this->_createTablesIfNotExists($Schema);
    
          
    		$db =& ConnectionManager::getDataSource($this->Schema->connection);
    		/**
    		 * need to disable source caching
    		 */
    		$db->cacheSources = false;
    
    		$this->out('Comparing Database to Schema...');
    
          $Old = $this->Schema->read();
    		$compare = $this->Schema->compare($Old, $Schema);
    
    		if(isset($this->params['with-check']) && $this->params['with-check'] ==1) {
       		$compare = $this->__humanCheck($compare);
    		}
    		
    		$contents = array();
    
    		if (empty($table)) {
    			foreach ($compare as $table => $changes) {
    				$contents[$table] = $db->alterSchema(array($table => $changes), $table);
    			}
    		} elseif (isset($compare[$table])) {
    			$contents[$table] = $db->alterSchema(array($table => $compare[$table]), $table);
    		}
    
    
    		if (empty($contents)) {
    			$this->out(__('Schema is up to date.', true));
    			$this->_stop();
    		}
    
    		/**
    		 * check if silent mode is on
    		 */
    		if(!isset($this->params['non-interactive'])) {
       		$this->out("\n" . __('The following statements will run.', true));
       		$this->out(array_map('trim', $contents));
       		if ('y' == $this->in(__('Are you sure you want to alter the tables?', true), array('y', 'n'), 'n')) {
       			$this->out('');
       			$this->out(__('Updating Database...', true));
       			$this->__run($contents, 'update', $Schema);
       		}
    		}else {
                $this->out("\n" . __('The following statements will run.', true));
                $this->out(array_map('trim', $contents));
                $this->__run($contents, 'update', $Schema);
    		}
    
    		$this->out(__('End update.', true));
    	}
    
    
    /**
     * Displays help contents
     *
     * @access public
     */
    	function help() {
    		$this->out("The Schema Shell generates a schema object from \n\t\tthe database and updates the database from the schema.");
    		$this->hr();
    		$this->out("Usage: cake my_schema <command> <arg1> <arg2>...");
    		$this->hr();
    		$this->out('Params:');
    		$this->out("\n\t-connection <config>\n\t\tset db config <config>. uses 'default' if none is specified");
    		$this->out("\n\t-path <dir>\n\t\tpath <dir> to read and write schema.php.\n\t\tdefault path: ". $this->Schema->path);
    		$this->out("\n\t-name <name>\n\t\tclassname to use.");
    		$this->out("\n\t-file <name>\n\t\tfile <name> to read and write.\n\t\tdefault file: ". $this->Schema->file);
    		$this->out("\n\t-s <number>\n\t\tsnapshot <number> to use for run.");
    		$this->out("\n\t-dry\n\t\tPerform a dry run on 'run' commands.\n\t\tQueries will be output to window instead of executed.");
    		$this->out("\n\t-f\n\t\tforce 'generate' to create a new schema.");
          $this->out("\n\t-with-check\n\t\tYou will be asked if particullar ADD or DROP field are valid or not.");
          $this->out("\n\t-add-only\n\t\tWhen used with -with-check - You won't be bothered about field DROP's or ADD's\n\t\tno DROP's would be performed, and all ADD's are allowed.");
          $this->out("\n\t-non-interactive\n\t\tWhen used with -add-only - You won't be wheather run or not SQL statements. They're just run based on -add-only behavior.");
          $this->out('Commands:');
    		$this->out("\n\tschema help\n\t\tshows this help message.");
    		$this->out("\n\tschema view\n\t\tread and output contents of schema file");
    		$this->out("\n\tschema generate\n\t\treads from 'connection' writes to 'path'\n\t\tTo force generation of all tables into the schema, use the -f param.\n\t\tUse 'schema generate snapshot <number>' to generate snapshots\n\t\twhich you can use with the -s parameter in the other operations.");
    		$this->out("\n\tschema dump <filename>\n\t\tDump database sql based on schema file to <filename>. \n\t\tIf <filename> is write, schema dump will be written to a file\n\t\tthat has the same name as the app directory.");
    		$this->out("\n\tschema run create <schema> <table>\n\t\tDrop and create tables based on schema file\n\t\toptional <schema> arg for selecting schema name\n\t\toptional <table> arg for creating only one table\n\t\tpass the -s param with a number to use a snapshot\n\t\tTo see the changes, perform a dry run with the -dry param");
    		$this->out("\n\tschema run update <schema> <table>\n\t\talter tables based on schema file\n\t\toptional <schema> arg for selecting schema name.\n\t\toptional <table> arg for altering only one table.\n\t\tTo use a snapshot, pass the -s param with the snapshot number\n\t\tTo see the changes, perform a dry run with the -dry param".
    		           "\n\t\tTo perform synchronization under Your supervision use -with-check\n\t\t\tfor friendly (automagic) synchronisation use -add-only".
    		           "\n\t\t\tUse -non-interactive with -auto-check to make it done without any assistance.\n\t\t\t Useful when building system (like Phing) need to run update.");
    		$this->out("");
    		$this->_stop();
    	}
    }
    ?>

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _Page 1: :///articles/view/4caea0e4-32dc-4f59-9c80-4c7982f0cb67/lang:eng#page-1
.. _http://bakery.cakephp.org/articles/view/cakephp-yaml-migrations-and-fixtures-without-pear: http://bakery.cakephp.org/articles/view/cakephp-yaml-migrations-and-fixtures-without-pear
.. _Page 2: :///articles/view/4caea0e4-32dc-4f59-9c80-4c7982f0cb67/lang:eng#page-2
.. meta::
    :title: MySchemaShell - step closer to DB structure synchronization
    :description: CakePHP Article related to db structure,schema,Plugins
    :keywords: db structure,schema,Plugins
    :copyright: Copyright 2009 
    :category: plugins

