Cakephp footnotes (on base of notepad_plus_plus). Part I. Basic
solution.
=========

by %s on September 20, 2007

It is very usefull during development if you have ability of
navigation dirrectly to source code from explorer page. I create such
solution for my editor Notepad++.

First of all please download last version of editor from sourceforge
page. `http://notepad-plus.sourceforge.net`_

Now i describe the basic solution that allow you to open model,
controller and view from browser.

As you see in Debug controllear in before filter method created two
config parameters.
First Editor.path is a path to the your editor.
Second Editor.linekey is string mask which used when need inform
editor in which line need to open file.

I have editor at folder c:\notepad++. All works well in this case.


How all works? When you click the link, browser open page with link to
debuger controller in new window, and this page close by the
javascript. Controller make system call and start editor with
necessery parameters. All really simple.



Plece next controller in controllers folder with name
debug_controller.php



Controller Class:
`````````````````

::

    <?php 
    class DebugController extends AppController {
    
    	var $name = 'Debug';
    	var $helpers = array('Html', 'Form', 'Ajax', 'Debug');
    	var $uses = array();
    	var $components = array();
    	
    	function beforeFilter() {
    		Configure::write('Editor.path', 'C:\Notepad++\notepad++.exe');
    		Configure::write('Editor.linekey', '-n%s');
    		
    		parent::beforeFilter();
    		return true;
    	}	
    	
    	function open($file, $line=null) { 
    		$file=base64_decode($file);
    		$this->_open($file, $line);
    		$this->render('close'); 
    	}
    	
    	function _open($file, $line=null, $options=array()) { 
    		if (!empty($line)) {
    			$linekey=sprintf(Configure::read('Editor.linekey'),$line);
    		} else {
    			$linekey='';
    		}
    		$command=sprintf('%s "%s" %s',Configure::read('Editor.path'),$file,$linekey);
    		$this->log($command);
    		$result=system($command);
    	} 
    	
    	function open_controller ($name, $action=null) {
    		$name=Inflector::camelize($name);
    		if (loadController($name)) {
    			$cname=$name.'Controller';
    			$filepath=APP.'controllers'.DS.Inflector::underscore($cname).'.php';
    			$line=null;
    			if (!empty($action)) {
    				$file=& new File($filepath);
    				if ($file->exists()) {
    					$text=$file->read();
    					$textarr=split("\n",$text);
    					foreach($textarr as $key => $str) {
    						if (preg_match("/function\s+$action/i",$str)) {
    							$line=$key+1;
    							break;
    						}
    					}
    				}
    			}
    			$this->_open($filepath,$line);
    		}
    		$this->render('close'); 
    		
    		
    	}
    
    	function open_model ($name) {
    		$name=Inflector::singularize(Inflector::camelize($name));
    		if (loadModel($name)) {
    			$cname=$name;
    			$filepath=APP.'models'.DS.Inflector::underscore($cname).'.php';
    			$file=& new File($filepath);
    			if ($file->exists()) {
    				$this->_open($filepath);
    			}
    		}
    		$this->render('close'); 
    	}
    
    	function open_view ($controller,$name) {
    		$cname=Inflector::camelize($controller);
    		if (loadController($cname)) {
    			$filepath=APP.'views'.DS.$controller.DS.$name;
    			$file=& new File($filepath.'.ctp');
    			if ($file->exists()) {
    				$this->_open($filepath.'.ctp');
    			} else {
    				$file=& new File($filepath.'.thtml');
    				if ($file->exists()) {
    					$this->_open($filepath.'.thtml');
    				}
    			}
    		}
    		$this->render('close'); 
    	}
    	
    }
    ?>


Create view /debug/show.ctp


View Template:
``````````````

::

    
    <script type="text/javascript">
     window.close();
    </script>


Place next code in /views/layouts/default.ctp

View Template:
``````````````

::

    
    	<?php if (Configure::read()>0) {echo $debug->current_links();}?> 

If you use delault cake layout I recomend you to place it after header
div (inside container div). This allow you always have links at top of
page.

And last feature is a helper that you need to place at
views/helpers/debug.php


Helper Class:
`````````````

::

    <?php 
    class DebugHelper extends Helper 
    { 
    	var $helpers = array('Html', 'Form', 'Ajax');//, 'Javascript');
    	var $view=null;
    	var $options = array ('target' => '_blank');
    
    
    
    	function link($file, $line=null, $title='Show') {
    		$link=$this->Html->link($title,	array ('controller' => 'debug', 'action' => 'open',$file, $line),$this->options);
    		$this->log($link);
    		return $link;
    	}
    	
    	function current_links() {
    		
    		$modelLink=$this->Html->link('Show Model',	array ('controller' => 'debug', 'action' => 'open_model',$this->params['controller']),$this->options);
    		$controllerLink=$this->Html->link('Show controller',	array ('controller' => 'debug', 'action' => 'open_controller',$this->params['controller'], $this->params['action']),$this->options);
    		$viewLink=$this->Html->link('Show view',	array ('controller' => 'debug', 'action' => 'open_view',$this->params['controller'], $this->params['action']),$this->options);
    		
    		
    		return "$modelLink $controllerLink $viewLink";
    	}
    	
    	
    }
    ?>



.. _http://notepad-plus.sourceforge.net: http://notepad-plus.sourceforge.net/
.. meta::
    :title: Cakephp footnotes (on base of notepad_plus_plus). Part I. Basic solution.
    :description: CakePHP Article related to footnote foot note e,General Interest
    :keywords: footnote foot note e,General Interest
    :copyright: Copyright 2007 
    :category: general_interest

