

YAMMY!: DB to YAML table converter for migrations
=================================================

by %s on November 08, 2007

YAMMY! is the "missing puzzle piece" to the great migrations Shell
coded by Joel Moss. YAMMY! provides a fast and convinient way to
convert your DB table structure into the right YAML format needed by
the migration shell. YAMMY! helps you to deploy faster!
Today we will talk about migrations, yaml files and CakePHP and in
particular I’ll introduce you to the latest shell I’ve written for
CakePHP: `YAMMY!`_ I’ve written YAMMY! a couple of weeks ago, but I
just found the time to release it today. Anyway lets get into more
details!


What are migrations?
--------------------

Well migrations are what `revision control systems`_ (`SVN`_ or
`CVS`_) is for files but applied to database tables.

Migrations allow you to define changes to your database schema, making
it possible to use a version control system to keep things
synchronized with the actual code.

This has many uses, including:

#. [li]Teams of developers: if one person makes a schema change, the
   other developers just need to update, and they can simply run a
   migration. [li]Production servers: use migrations when you roll out a
   new release to bring the database up to date as well. [li]Multiple
   machines: if you develop on both a desktop and a laptop, or in more
   than one location, migrations can help you keep them all synchronised.


Migrations in CakePHP
---------------------

`Joel Moss`_ brought migrations to CakePHP in 2006, with his excellent
migration shell, that has now reached `version 3.3`_.
Now you are nearly ready to go with YAMMY! and to speed up your
application development and project management.
YAMMY!: feed your migrations faster!
The `CakePHP`_ migration infrastructure described above needs the
“right food” in order to work properly.
To use migrations you need to provide some files in `YAML format`_
telling the migration shell what to do. Writing a DB table structure
in a yaml format is pretty boring and time consuming: YAMMY! enters
into the game here.

YAMMY! provides a convenient and super fast way to feed your
migrations converting your DB tables schema into a YAML format in few
seconds.

YAMMY! requirements
-------------------

Before I’ll go into further details let’s see what you need to use
YAMMY!
You then need to configure your PATH environment variable in order to
tell your server where the hell cake is located. These two screencats
will help you sorting these out:

#. [li] `Setting Up the CakePHP Console on *nix`_ [li] `Setting Up the
   CakePHP Console on Windows`_


You are now ready to go on!

#. [li] download the `Spyc class`_ and put it in your app/vendors
   directory [li] `download YAMMY! 1.0`_ and put it in your
   app/vendors/shells directory



YAMMY! in action
----------------

Now if you set the CakePHP console properly as described in the two
screencasts above, you are ready to go!
Open your Shell/Console and move (cd ..) to your app directory (on my
development machine I have all my projects in C:\www\\ for example,
lets say I’m working on a project called test, I’ll them move to
C:\\www\\test\\app ).

Once your Shell/Console points to your app directory type:

::

    cake yammy

Great you entered YAMMY! in interactive mode and YAMMY! will ask you
which tables you want to convert.

After answering a few questions (the DB connection you wanna use and
which tables you want to convert to yaml files) YAMMY! will write a
file in app/config/migrations with your DB schema converted to a YAML
format, and you are ready to start your migrations wih the migration
shell!

For a complete overview of all YAMMY! functionality type in your
console:

::

    cake yammy help

Don’t worry, your DB tables will stay untouched, what YAMMY! does is
simply to write their structure in a YAML format into a file in
app/migrations.

YAMMY! benefits
---------------

What the benefits of using migrations and YAMMY! for your development
cycle?!?

Well they help you to keep your development tidy and to speed up the
all process.

If you then use a tool like `Capistrano`_ (`read this post by Chris
Hartjes`_) you can not only deploy your application faster but even
run migrations remotely.

I hope that you will find YAMMY! usefull and that you will have more
time to sunbath on the beach using it along with migrations!


YAMMY! code
-----------

Here we go!

::

    
    <?php
    <?php
    /**
     * YAMMY! is a CakePHP shell script that converts your DB table schema into a YAML schema
     *
     * Run 'cake yammy help' for more info and help on using this script.
     *
     * PHP versions 4 and 5
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @filesource          http://www.4webby.com/blog/downloads
     * @copyright		Copyright 2007-2008, Daniel Vecchiato
     * @link			http://www.4webby.com
     * @since			CakePHP(tm) v 1.2
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     * @version         1.0
     * 
     * 
     * DEPENDENCIES: download Spyc class (Yaml Parser) (http://spyc.sourceforge.net/)
     *               put spyc.php in your vendors folder
     */
    class YammyShell extends Shell {
    
        var $_useDbConfig = 'default';
        
        var $_migrationTable = 'schema_info';
        
    	function main() {
            
    	    //let's initialize variables, constants etc.
    	    $this->__initialize();
    		
    		//asks options
    		$this->out('[ S ]ingle table');
    		//$this->out('[M]ultiple tables');
    		$this->out('[A]ll tables');
    		$this->out('[Q]uit');
    
    		$tablesToYammy = strtoupper($this->in('Which tables do you want to YAMMY?', array('S', 'A', 'Q')));
    		switch($tablesToYammy) {
    			case 'S':
    			    $this->__execute();
    			    break;
    			case 'M':
    			    
    			case 'A':
    				$this->all();
    				break;
    			case 'Q':
    				exit(0);
    				break;
    			default:
    				$this->out('You have made an invalid selection. Please choose what to do by entering S, A, or Q.');
    		}
    		$this->hr();
    		
    		//recursively calls main functionat the end of tasks execution
    		$this->main();
    	}
    	
    	// --------------------------------------------------------------------
    	/**
    	 * Initializes the YAMMY Shell
    	 *
    	 * @return unknown
    	 */
    	function __initialize()
    	{
    		//if we don't have an application YET let's build it
    		if (!is_dir(CONFIGS)) {
    			$this->Project->execute();
    		}
            
    		//if no db config is present let's set it
    		if (!config('database')) {
    			$this->out("Your database configuration was not found. Take a moment to create one.\n");
    			$this->args = null;
    			return $this->DbConfig->execute();
    		}
    		
    		//let's define were migration file will be written
    		if(!defined('MIGRATIONS_PATH')){
    	        define('MIGRATIONS_PATH', APP_PATH .'config' .DS. 'migrations');
    	    }
    	}
    	
    	// -------------------------------------------------------------------- 
    	/**
         * Execution method always used for tasks
         *
         * @return void
         */
    	function __execute() {
    		if (empty($this->args)) {
    			$this->hr();
    			$this->out('Bake a YAML file for CAKE migrations:');
    			$this->hr();
    
    			$useTable = null;
    
    			//let's choose DB connection
    			$dbIsGood =	$this->in('Use default database connection?', array('y','n'), 'y');
    			if(low($dbIsGood) == 'n'){
    			    $this->_useDbConfig = $this->in('Choose a database connection:', null, 'default');
    			}
    
    			$this->__interactive();
    		}
    	}
    	
        /**
         * Handles interactive YAML files construction
         *
         * @access private
         * @return void
         */
    	function __interactive() {
    		
            
            $currentModelName = $this->_getName();
            
    		$db =& ConnectionManager::getDataSource($this->_useDbConfig);
    		$tableIsGood = false;
    		$useTable = Inflector::tableize($currentModelName);
    		$fullTableName = $db->fullTableName($useTable, false);
    		if (array_search($useTable, $this->__tables) === false) {
    			$this->out("\nGiven your model named '$currentModelName', Cake would expect a database table named '" . $fullTableName . "'.");
    			$tableIsGood = $this->in('do you want to use this table?', array('y','n'), 'y');
    		}
    
    		if (low($tableIsGood) == 'n' || low($tableIsGood) == 'no') {
    			$useTable = $this->in('What is the name of the table?');
    		}
    		while ($tableIsGood == false) {
    			if (is_array($this->__tables) && !in_array($useTable, $this->__tables)) {
    				$fullTableName = $db->fullTableName($useTable, false);
    				$this->out($fullTableName . ' does not exist.');
    				$useTable = $this->in('What is the name of the table?');
    				$tableIsGood = false;
    			} else {
    				$tableIsGood = true;
    			}
    		}
    		
    		$this->out('');
    		$this->hr();
    		$this->out('The following Yaml Migration file will be created:');
    		$this->hr();
    		$this->out("DB Connection: " . $this->_useDbConfig);
    		$this->out("DB Table:	" . $fullTableName);
    		/*if ($primaryKey != 'id') {
    			$this->out("Primary Key:   " . $primaryKey);
    		}*/
            $looksGood = $this->in('do you want to use this table?', array('y','n'), 'y');
    		if (low($looksGood) == 'y' || low($looksGood) == 'yes') {
    			if ($useTable == Inflector::tableize($currentModelName)) {
    				// set it to null...
    				// putting $useTable in the model
    				// is unnecessary.
    				$useTable = null;
    			}
    			if ($this->__fireDB($fullTableName)) {
    				$this->hr();
    				$this->out('');
            		$this->out('Generation of migration file for table: \''.$fullTableName.'\' completed.');
            		$this->out('You can now edit it to customise your migration.');
            		$this->out('');
            		$this->hr();
            		$this->main();
    			}
    		} else {
    			$this->out('YAMMY Aborted.');
    		}
    	}
    	
    	// --------------------------------------------------------------------
    	/**
    	 * Converts all tables of the DB in YAML format
    	 * The generated file will be written in APP/config/migrations
    	 *
    	 * @return unknown
    	 */
    	function all()
    	{
    		$this->__initialize();
    		
    		//let's get an array with all tables in DB
    		$this->_getTables();
    		
    		if(empty($this->__tables)){
    		     $this->out('No tables in the database provided');
    		     $this->out('Yammy Aborted.');
    		     exit;
    		}
    		else{
    		    $this->hr();
        		$this->out('Converting ALL db tables to YAML schema');
        		$this->hr();
        		$this->__fireDB($this->__tables, true);
    		}
    	}
    	
    	// --------------------------------------------------------------------
    	/**
    	 * Converts the provided tables SPACE separated into a YAML file
    	 * The generated file will be written in APP/config/migrations
    	 *
    	 * @return unknown
    	 */
    	function tables()
    	{
    		$this->__initialize();
    		
    		$providedTables  = $this->args;
            
    		//let's get an array with all tables in DB
    		$this->_getTables();
    		
    		//empty database
    		if(empty($this->__tables)){
    		     $this->out('Database empty');
    		     $this->out('Yammy Aborted.');
    		     exit;
    		}
    		elseif(empty($providedTables) || $providedTables[0]==''){
    		    $this->out('Please specify at least a table name!');
    		    $this->out('Yammy Aborted.');
    		    exit;
    		}
    		else{
    		    //check if provided tables are in DB
    		    foreach($providedTables as $val){
    		        if(!in_array($val , $this->__tables)){
    		           $this->out('Table '.$val.' not in DB');
        		       $this->out('Yammy Aborted.');
        		       exit; 
    		        }
    		    }
    		    $this->hr();
        		$this->out('Converting tables to YAML schema');
        		$this->hr();
        		$this->__fireDB($providedTables, true);
    		}
    	}
    	
    	// --------------------------------------------------------------------
    	/**
    	 * Alias function for 'tables'
    	 * Converts the provided tables SPACE separated into a YAML file
    	 * The generated file will be written in APP/config/migrations
    	 *
    	 * @return unknown
    	 */
    	function t()
    	{
    		$this->tables();
    	}
    	
    	// --------------------------------------------------------------------
    	/**
    	 * Burns the provided tables Schema into a YAML file suitable for migrations
    	 *
    	 * @param array $tables
    	 * @return unknown
    	 */
    	function __fireDB($tables = null, $allTables = false)
    	{
    		$fileName = $allTables == true ? 'full_schema' : $tables;
    		
    		if(!is_array($tables)){
    		    $tables = array($tables);
    		}
    		
    		$__tables = $this->__filterMigrationTable($tables);
    		
    		if(empty($__tables)){
    		    $this->out('No tables in the database provided apart from MIGRATIONs table');
    		    $this->out('i.e. '.$this->_migrationTable);
    		    $this->out('Yammy Aborted.');
    		    exit;
    		}
    		
    		$numTables = count($__tables);
    		
    		foreach($__tables as $__table){
    
    		    //creating array for UP fields
    		    $upSchema[$__table] = $this->__buildUpSchema($__table);
    
    		}
    		$__dbShema['UP']['create_table'] = $upSchema;
    
    		//creating array for DOWN fields
    		$__dbShema['DOWN']['drop_table'] = $__tables;
    
    		//print file header
    		$out ='#'."\n";
    		$out.='# migration YAML file'."\n";
    		$out.='#'."\n";
    		$out.= $this->__toYaml($__dbShema);
    		//get version number
    		$this->_getMigrations();
    		$new_migration_count = $this->_versionIt($this->migration_count+1);
    		//write .yml file
    
    		$fileName = MIGRATIONS_PATH.DS.$new_migration_count.'_'.$fileName.'.yml';
    		return $this->createFile($fileName, $out);
    	}
    	
    	// --------------------------------------------------------------------
    	/**
    	 * Enter description here...
    	 *
    	 * @param unknown_type $name
    	 * @param unknown_type $useTable
    	 * @return array
    	 */
    	function __buildUpSchema($tableName) {
    
            $useTable = low(Inflector::pluralize($tableName));
            
            loadModel();        
            $tempModel = new Model(false, $tableName);
    		$db =& ConnectionManager::getDataSource($this->_useDbConfig);
    		$modelFields = $db->describe($tempModel);
    		foreach($modelFields as $key=>$item){
    		    if($key!='id' AND $key!='created' AND $key!='modified'){
    		        $default = !empty($item['default']) ? $item['default'] : 'false';
    
    		        $setNull = $item['null']==true ? 'is_null' : 'not_null';
    		        
    		        $tempFieldSchema[$key] = array('type'=>$item['type'],
    		                                       'default'=>$default,
    		                                       'length'=>$item['length'],
    		                                      );
    		        //let's set the option NULL field                             
    		        $tempFieldSchema [$key][] = $setNull;
    		        $tableSchema = $tempFieldSchema;		        
    		    }
    		}
    		if(!array_key_exists('id', $modelFields)){
    		    $tableSchema[] = 'no_id';
    		}
    		if(!array_key_exists('created', $modelFields)){
    		    $tableSchema[] = 'no_dates';
    		}
    		
            return $tableSchema; 
    	}
    	
    	// -------------------------------------------------------------------- 
    	function data()
    	{
    		$this->__initialize();
    		
    		$providedTables  = $this->args;
            
    		//let's get an array with all tables in DB
    		$this->_getTables();
    		
    		//empty database
    		if(empty($this->__tables)){
    		     $this->out('Database empty');
    		     $this->out('Yammy Aborted.');
    		     exit;
    		}
    		elseif(empty($providedTables) || $providedTables[0]==''){
    		    $this->out('Please specify at least a table name!');
    		    $this->out('Yammy Aborted.');
    		    exit;
    		}
    		else{
    		    //check if provided tables are in DB
    		    foreach($providedTables as $val){
    		        if(!in_array($val , $this->__tables)){
    		           $this->out('Table '.$val.' not in DB');
        		       $this->out('Yammy Aborted.');
        		       exit; 
    		        }
    		    }
    		    $this->hr();
        		$this->out('Converting tables to YAML schema');
        		$this->hr();
        		$this->_buildData($providedTables);
    		}
    	}
    	
    	
    	// --------------------------------------------------------------------
    	/**
    	 * Wrapper to use the Spyc class (Yaml Parser)
    	 * You must have spyc (http://spyc.sourceforge.net/) in your vendors folder
    	 *
    	 * @param array $schema
    	 * @return string
    	 */
    	function __toYaml($schema = null) {
    	    //let's load Spyc
    		vendor('spyc');
    		//converting array to YAML
            $out = Spyc::YAMLDump($schema);
            return $out; 
    	}
    	
    	// -------------------------------------------------------------------- 
    	/**
         * Forces the user to specify the model he wants to bake, and returns the selected model name.
         *
         * @return the model name
         */
    	function _getName() {
    		$this->_listAll($this->_useDbConfig);
    
    		$enteredModel = '';
    
    		while ($enteredModel == '') {
    			$enteredModel = $this->in('Enter a number from the list above, or type in the name of another model.');
    
    			if ($enteredModel == '' || intval($enteredModel) > count($this->_modelNames)) {
    				$this->out('Error:');
    				$this->out("The model name you supplied was empty, or the number \nyou selected was not an option. Please try again.");
    				$enteredModel = '';
    			}
    		}
    
    		if (intval($enteredModel) > 0 && intval($enteredModel) <= count($this->_modelNames)) {
    			$currentModelName = $this->_modelNames[intval($enteredModel) - 1];
    		} else {
    			$currentModelName = $enteredModel;
    		}
    
    		return $currentModelName;
    	}
    	
    	// -------------------------------------------------------------------- 
    	/**
        * outputs the a list of possible models or controllers from database
        *
        * @return output
        */
    	function _listAll() {
    		$this->_getTables();
    		$this->out('');
    		$this->out('Possible Models based on your current database:');
    		$this->hr();
    		$this->_modelNames = array();
    		$count = count($this->__tables);
    		for ($i = 0; $i < $count; $i++) {
    			$this->_modelNames[] = $this->_modelName($this->__tables[$i]);
    			$this->out($i + 1 . ". " . $this->_modelNames[$i]);
    		}
    	}
    	
    	// --------------------------------------------------------------------
    	/**
    	 * Get's the tables in DB according to your connection configuration
    	 *
    	 */
    	function _getTables(){
    	    $db =& ConnectionManager::getDataSource($this->_useDbConfig);
    		$usePrefix = empty($db->config['prefix']) ? '' : $db->config['prefix'];
    		if ($usePrefix) {
    			$tables = array();
    			foreach ($db->listSources() as $table) {
    				if (!strncmp($table, $usePrefix, strlen($usePrefix))) {
    					$tables[] = substr($table, strlen($usePrefix));
    				}
    			}
    		} else {
    			$tables = $db->listSources();
    		}
    		$this->__tables = $this->__filterMigrationTable($tables);
    	}
    	
    	// -------------------------------------------------------------------- 
    	/**
    	 * Used to build migrations file numbers
    	 * 
    	 * @author Joel Moss
    	 * @link http://joelmoss.info/
    	 *
    	 */
    	function _getMigrations()
    	{
    	    $folder = new Folder(MIGRATIONS_PATH, true, 0777);
    	    $this->migrations = $folder->find("[0-9]+_.+\.yml");
    	    usort($this->migrations, array($this, '_upMigrations'));
    	    $this->migration_count = count($this->migrations);
    	}
    	
    	// -------------------------------------------------------------------- 
    	/**
    	 * Custom function used by usort in getMigrations
    	 *
    	 * @author Joel Moss
    	 * @link http://joelmoss.info/
    	 * @param unknown_type $a
    	 * @param unknown_type $b
    	 * @return unknown
    	 */
    	function _upMigrations($a, $b)
    	{
    		list($aStr) = explode('_', $a);
    		list($bStr) = explode('_', $b);
    		$aNum = (int)$aStr;
    		$bNum = (int)$bStr;
    		if ($aNum == $bNum) {
    			return 0;
    		}
    		return ($aNum > $bNum) ? 1 : -1;
    	}
    	
        // -------------------------------------------------------------------- 
        /**
        * Converts migration number to a minimum three digit number.
        *
        * @param $num The number to convert
        * @return $num The converted three digit number
        * @author Joel Moss
        * @link http://joelmoss.info/
        */
        function _versionIt($num)
        {
            switch (strlen($num))
            {
                case 1:
                    return '00'.$num;
                case 2:
                    return '0'.$num;
                case 3:
                    return $num;
            }
        }
        
        // -------------------------------------------------------------------- 
        function __filterMigrationTable($myTables)
        {
        	$mySchemaInfoKey = array_search($this->_migrationTable, $myTables);
            $filteredArray = Set::remove($myTables, $mySchemaInfoKey);
            sort($filteredArray);
        	return $filteredArray;
        }
        // -------------------------------------------------------------------- 
        /**
         * Displays help contents
         *
         * @return void
         */
    	function help() {
    	    $this->out('YAMMY! helps you write DB schema in a YAML format.');
    	    $this->out('The generated files can then be used for DB migrations');
            $this->out('allowing you to migrate your database schema between versions.');
            $this->out('');
            $this->out('');
            $this->out('COMMAND LINE OPTIONS');
            $this->out('');
            $this->out('  cake yammy');
            $this->out('    - interactive YAML generation');
            $this->out('');
            $this->out('  cake yammy all');
            $this->out('    - generates YAML schema for all tables of default DB connection');
            $this->out('');
            $this->out('  cake yammy tables [table1_name] [table2_name]');
            $this->out('    - Generates a YAML schema for all tables supplied [migration name]');
            $this->out('      table names must be SPACE SEPARATED');
            $this->out('');
            $this->out('  cake yammy help');
            $this->out('    - Displays this Help');
            $this->out('');
            $this->out('  cake yammy h');
            $this->out('    - alias for help');
            $this->out('');
            $this->out('  cake yammy t');
            $this->out('    - alias for tables');
            $this->out('');
            $this->out('');
            $this->out('For more information and for the latest release of this and others,');
            $this->out('go to http://www.4webby.com');
            $this->out('');
            $this->hr();
            $this->out('');
    	    exit();
    	}
        
    	// --------------------------------------------------------------------
    	/**
    	 * Alias function for 'help'
    	 *
    	 */
    	function h(){
    	    $this->help();
    	}
        
    	// --------------------------------------------------------------------
    	/**
    	 * Prints intro
    	 *
    	 */
    	function _welcome()
    	{
    	    $this->out(' __  __  _  _  __    _   _  __   _  _   _  _  _   _');
    	    $this->out('|   |__| |_/  |__     \ /  |__| | \/ | | \/ |  \ / ');
    	    $this->out('|__ |  | | \_ |__      |   |  | |    | |    |   |  ');
    	    $this->out('');
    	    $this->out('burn your SQL to YAML faster!');
    	    $this->hr();
    	    $this->out('Welcome to YAMMY!');
    	    $this->out('by Daniel Vecchiato www.4webby.com');
    	    $this->hr();
    	}
    }
    ?>


YAMMY! downloads & resources
----------------------------

You can find a more detailed descriptioin of YAMMY! and download it on
the `4webby Blog`_.

You will also find screenshots of the YAMMY! shell.

Happy baking!

Daniel Vecchiato

.. _4webby Blog: http://www.4webby.com/blog/posts/view/3/yammy_db_to_yaml_shell_migrations_made_easy_in_cakephp
.. _Capistrano: http://www.capify.org/
.. _CVS: http://www.nongnu.org/cvs/
.. _Spyc class: http://spyc.sourceforge.net/
.. _Setting Up the CakePHP Console on Windows: http://cakephp.org/screencasts/view/6
.. _SVN: http://subversion.tigris.org/
.. _CakePHP: http://www.cakephp.org/
.. _revision control systems: http://en.wikipedia.org/wiki/Version_control
.. _Joel Moss: http://joelmoss.info/
.. _download YAMMY! 1.0: http://www.4webby.com/blog/posts/view/3/downloads
.. _Setting Up the CakePHP Console on *nix: http://cakephp.org/screencasts/view/7
.. _version 3.3: http://joelmoss.info/switchboard/blog/2583:Migrations_v33
.. _read this post by Chris Hartjes: http://www.littlehart.net/atthekeyboard/2007/09/21/deploying-cakephp-applications-using-capistrano/
.. _YAML format: http://www.yaml.org/
.. meta::
    :title: YAMMY!: DB to YAML table converter for migrations
    :description: CakePHP Article related to migrations,yaml,deployment,capistrano,yammy,Tutorials
    :keywords: migrations,yaml,deployment,capistrano,yammy,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

