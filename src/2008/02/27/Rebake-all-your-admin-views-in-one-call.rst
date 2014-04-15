Rebake all your admin views in one call
=======================================

by AD7six on February 27, 2008

A console script to recreate all admin views in a single call. To
install ensure you are able to run the cake console commands copy the
below script into /app/vendors/shells/bake_admin.php To run the script
at the command line: cd /app/folder/is/here cake bake_admin help cake
bake_admin all

::

    
    <?php
    /**
     * Bake Admin console task
     * 
     * Rebake all your admin views in one call.
     *
     * PHP versions 4 and 5
     *
     * Copyright (c) Andy Dawson
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @version		1.2.0
     * @modifiedby		Andy Dawson
     * @lastmodified	2008-02-25 08:24:22 +0100 (Mon, 25 Feb 2008) $
     * @license		http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    uses('Folder','File','model'.DS.'connection_manager');
    class BakeAdminShell extends Shell {
    
    	var $tasks = array('Controller', 'View');
    
    	function help() {
    		$this->out('CakePHP bake admin, rebake all admin views in one call. Usage:');
    		$this->out('cake bake_admin');
    		$this->out('	- this text');
    		$this->out('cake bake_admin all');
    		$this->out('	- generate admin views for all controllers');
    		$this->out('	  the list of controllers to process is generated from the names of the existing models');
    		$this->out('	  option to generate missing controllers');
    		$this->out('cake bake_admin all true');
    		$this->out('	- generate admin views for all controllers');
    		$this->out('	  automatically generate missing controllers');
    		$this->out('cake bake_admin all false');
    		$this->out('	- generate admin views for all controllers');
    		$this->out('	  ignore missing controllers');
    		$this->out('cake bake_admin foos');
    		$this->out('	- generate admin views for the foos controller only');
    		$this->out('cake bake_admin foos bars');
    		$this->out('	- generate admin views for the foos and bars controllers only');
    		$this->hr();
    	}
    
    	function initialize() {
    		return true;
    	}
    
    	function main() {
    		if (!isset($this->args[0]) || $this->args[0] == 'help') {
    			$this->help();
    		} elseif ($this->args[0] == 'all') {
    			$models = $this->getAllModels();
    			$controllers = $this->getAllControllers();
    			$this->out('CakePHP Bake Admin');
    
    			$missingControllers = array();
    			foreach ($models as $model) {
    				$out = '	' . $model;
    				$controller = Inflector::pluralize($model);
    				if (!in_array($controller, $controllers)) {
    					$missingControllers[] = $controller;
    				}
    			}
    
    			if ($missingControllers) {
    				$this->out('Missing controllers found:');
    				foreach ($missingControllers as $controller) {
    					$this->out('	' . $controller . ' missing');
    				}
    				if (isset($this->args[1]) && $this->args[1] == 'true') {
    					$bakeEmpties = 'Y';
    				} elseif (isset($this->args[1]) && $this->args[1] == 'false') {
    					$bakeEmpties = 'N';
    				} else {
    					$bakeEmpties = strtoupper($this->in(__('Bake empty ones?', true), array('Y', 'N', 'Q')));
    				}
    				if ($bakeEmpties == 'Y') {
    					foreach ($missingControllers as $controller) {
    						$this->Controller->bake($controller);
    						@unlink (CONTROLLER_TESTS. Inflector::underscore($controller) . '_controller.test.php');
    						$this->Controller->bakeTest($controller);
    					}
    				} elseif($bakeEmpties == 'Q') {
    					$this->out('aborting');
    					return;
    				}
    			} else {
    				$this->out('No missing controllers detected.');
    			}
    			$controllers = $this->getAllControllers();
    			$this->processControllers($controllers);
    			$this->out('');
    			$this->out('Finished! Admin views for all controllers rebaked.');
    		} else {
    			$this->processControllers($this->args);
    			$this->out('');
    			$this->out('Finished! Admin views for requested controllers rebaked.');
    		}
    	}
    
    	function getAllControllers() {
    		$Inflector =& Inflector::getInstance();
    		$folder = new Folder(CONTROLLERS);
    		$controllers = $folder->findRecursive('.*php');
    		$folder = new Folder(COMPONENTS);
    		$components = $folder->findRecursive('.*php');
    		$controllers = array_diff($controllers, $components);
    		foreach ($controllers as $id => $controller) {
    			$file = new File($controller);
    			$controllers[$id] = str_replace('_controller', '', $file->name());
    		}
    		return array_map(array(&$Inflector, 'camelize'), $controllers);
    	}
    
    	function getAllModels() {
    		$Inflector =& Inflector::getInstance();
    		$folder = new Folder(MODELS);
    		$models = $folder->findRecursive('.*php');
    		$folder = new Folder(BEHAVIORS);
    		$behaviors = $folder->findRecursive('.*php');
    		$folder = new Folder(MODELS . 'datasources');
    		$datasources = $folder->findRecursive('.*php');
    		$models = array_diff($models, $behaviors);
    		$models = array_diff($models, $datasources);
    		foreach ($models as $id => $model) {
    			$file = new File($model);
    			$models[$id] = $file->name();
    		}
    		return array_map(array(&$Inflector, 'camelize'), $models);
    	}
    
    	function getAllTables($useDbConfig = 'default') {
    		$db =& ConnectionManager::getDataSource($useDbConfig);
    		$usePrefix = empty($db->config['prefix']) ? '': $db->config['prefix'];
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
    		return $tables;
    	}
    
    	function processControllers($controllers = array(), $actions = array('admin_index', 'admin_edit', 'admin_view')) {
    		foreach ($controllers as $controller) {
    			$this->processController($controller, $actions);
    		}
    	}
    
    	function processController($controller, $actions = array('admin_index', 'admin_edit', 'admin_view')) {
    		$this->out($controller . ' controller');
    		foreach ($actions as $action) {
    			$this->out($action . ' action');
    			@unlink (VIEWS . Inflector::underscore($controller) . DS . $action . '.ctp');
    			$this->View->args = array($controller, $action);
    			$this->View->execute();
    		}
    	}
    }
    ?>



.. author:: AD7six
.. categories:: articles, snippets
.. tags:: ,Snippets

