Automatically load all controllers and actions into ACO tables for ACL
with a CakePHP Task
===================

by %s on January 27, 2009

If you've spent anytime wanting to use ACL on your applications, you
know how tedious it can be to manually enter your entire controller
and action structure. This Task will handle finding and loading or
updating all of those for you whenever you run it from the command
line. There isn't a section for Shell/Task code so I figured plugins
was the place to go.
This code is setup as a task so that it can be executed by any Cake
shell that includes it. All that you need to do to run it in your
application is create an empty shell:

/app/vendors/shells/task_runner.php

::

    
    <?php
    class TaskRunnerShell extends Shell {
       var $tasks = array('AclControllers');
       
       function main() {      
          $this->print_instructions();
       }
       
       function print_instructions() {
          $this->out("\nCommands");
          $this->hr();
          
          foreach($this->tasks AS $t) {
             
             $description = isset($this->{$t}->description) ? $this->{$t}->description : '';
             $this->out($this->shell . ' ' . Inflector::underscore($t) . "\t$description\n");
             
          }
       }
    }
    ?>

That's just a wrapper shell that I like to use that will run through
all of it's included tasks, find the 'description' variable and list
the command to execute with the description.

Next, you'll want to add the acl_controllers.php task to

/app/vendors/shells/tasks/acl_controllers.php

::

    
    <?php
    class AclControllersTask extends Shell {
       
       //Used when printing instructions
       var $description = 'Automatically loads controllers and actions into ACOs';
       var $filter = array();
       
       function startup() {
          App::import('Core','Controller');
          App::import('Component','Acl');
          
          $this->Acl =& new AclComponent();
          $controller = null;
          $this->Acl->startup($controller);
          $this->Aco =& $this->Acl->Aco;
       }
       
       function execute() {
          
          $this->out('Load Controllers and Actions into ACO');
          
          $cf =& Configure::getInstance();
          
          $plugins = Configure::listObjects('plugin');
          
          //Find plugin controller methods
          if(!empty($plugins)) {
             foreach($plugins AS $p) {
                $this->out('Checking Plugin: ' . $p);
                $path = ROOT . DS . APP_DIR . DS . 'plugins' . DS . strtolower($p) . DS . 'controllers';
                $this->out('Adding Plugin Path: ' . $path);
                
                $cf->controllerPaths[] = $path;
                App::import('Controller',$p . '.' . $p . 'AppController');
             }
          }
                  
          $controllers   = Configure::listObjects('controller');
          
          $this->out('Controllers Found: ' . implode(', ', $controllers));
          
          $this->filter['methods'] = get_class_methods('Controller');
          $this->filter['controller'] = array('App');            
          
          $list = array();
          
          //Find controller methods
          foreach($controllers AS $c) {
             if(in_array($c,$this->filter['controller'])) continue;
    
             $this->out('Importing Controller: ' . $c);                           
             if(!App::import('Controller',$c)) {
                foreach($plugins AS $p) {
                   if(strpos($p,$c) === 0 && App::import('Controller',$p . '.' . $c)) break;
                }            
             }
             
             $list[$c] = $this->_getMethods($c . 'Controller','methods');
          }
                
          //Find ROOT node id
          $root_id = $this->Aco->field('id',array('alias' => 'ROOT'));
          
          $this->out('');
          $this->out('ROOT node id: ' . $root_id);
          
          foreach($list AS $con => $acts) { //Loop through list of controllers
             $this->out('');
             $this->hr();
             
             $conditions = array('alias' => $con,'parent_id' => $root_id);
             if($this->Aco->hasAny($conditions)) { //Check if controller is already in the table
                $this->out('Controller Already Loaded: ' . $con);
             }
             else { //If not create it
                $this->Aco->create();
                if($this->Aco->save($conditions)) $this->out('CREATED: ' . $con . ' Controller');            
                else $this->error('Controller Create Failed',$con);            
             }
             
             $con_id = $this->Aco->field('id',$conditions);
             //$this->out('con_id: ' . $con_id);
             
             //Get list of the controller's actions
             $actions = $this->Aco->find('list',array(
                'conditions' => array('parent_id' => $con_id),
                'fields' => array('alias','id')));
                
             $this->out('Actions already loaded: ' . implode(', ',$acts));   
             //Loop through list of actions
             //print_r($acts);
             
             foreach($acts AS $a) {            
                if(!empty($actions[$a])) {
                   //$this->out('Skipped: ' . $a);
                }
                else {
                   $this->out('loading... ' . $a);
                   $this->Aco->create(false);
                   
                   if($this->Aco->save(array('parent_id' => $con_id,'alias' => $a))) $this->out('CREATED: ' . $con . '/'  . $a);
                   else $this->error('Action Create Failed', $con . '/'  . $a);
                }
             }
             
          }
          //print_r($aco);      
          //print_r($list);
       }
       
       function _getMethods($className,$filter = 'methods') {
          $c_methods = get_class_methods($className);
          $c_methods = array_diff($c_methods,$this->filter[$filter]);
          $c_methods = array_filter($c_methods,array($this,"_removePrivate"));
          
          return $c_methods;
       }
       
       function _removePrivate($var) {
          if(substr($var,0,1) == '_') return false;
          else return true;
       }
    }

The ONLY assumption that this code makes is that your ACO table has a
node with an 'alias' of 'ROOT' that all of the controllers and actions
will use as a parent. If you're using something other than root, the
code looking for it is on line 57.

To run it, just run over to your cake/console directory and type

php cake.php task_runner acl_controllers


.. meta::
    :title: Automatically load all controllers and actions into ACO tables for ACL with a CakePHP Task
    :description: CakePHP Article related to acl,Auth,task,shell,permissions,aco,load,controller,action,brightball,Plugins
    :keywords: acl,Auth,task,shell,permissions,aco,load,controller,action,brightball,Plugins
    :copyright: Copyright 2009 
    :category: plugins

