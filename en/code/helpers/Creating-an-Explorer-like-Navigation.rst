

Creating an Explorer-like Navigation
====================================

by %s on March 09, 2009

IÂ´ve written a helper to administrate documents in an explorer-like
tree. It doesnÂ´t matter how many folders you have or how deep is the
tree.
For a project in my curriculum I wrote a tree, where you can adminster
documents.

Sorry, IÂ´m not good in writing so I only post my code without any
more words:

::

    
    <?php
    /*
     * 2 Tabellen mÃ¼ssen existieren:
     * 
     * 	create table types (
     * 		id int(4) auto_increment,
     * 		typ varchar(128) not null,
     * 		parent int(4) null,
     * 		primary key(id)
     * 	);
     * 
     * 	create table documents (
     * 		id int(8) auto_increment,
     * 		type_id int(4) not null,
     * 		name varchar(128) not null,
     * 		primary key(id)
     * 	);
     * 
     * 
     * 2 Models:
     * 
     * 	class Type extends AppModel {
     * 		var $hasMany = array('Document');
     * 	}
     * 
     *  class Document extends AppModel {
     *  	var $belongsTo = array('Type');
     *  }
     *  
     *  
     * 1 Controller:
     * 
     * 	class DocumentsController extends AppController {
     * 		var $helpers = array('explorerTree', 'javascript');
     * 
     * 		function index() {
     * 			$this->set('types', $this->Document->Type->find('all'));
     * 		}
     * 	}
     * 
     * 
     * 1 View:
     * 
     * Muss mind. das Javascript laden ($javascript->link('explorer_tree', false);)
     * und natÃ¼rlich die Methoden des Helpers aufrufen
     * ($newArray = $explorerTree->init($types);echo $explorerTree->createTree($newArray); 
     */
    class explorerTreeHelper extends AppHelper {
    	
    	/*
    	 * Initialisiert das Array
    	 */
    	function init($types) {		
    		return $this->__createArray($types);
    	}
    	
    	function __createArray($types) {
    		$newArray;
    		foreach ($types as $type) {			
    			// Dokumenten-Array leeren
    			foreach ($type['Document'] as $document) {
    				array_splice($document, 0, 2);
    			}
    			
    			// Werte auslesen und speichern
    			$id = $type['Type']['id'];
    			$name = $type['Type']['typ'];
    			$parent = $type['Type']['parent'];
    			$docs = $type['Document'];
    			
    			// Types-Array leeren
    			array_splice($type, 0);
    
    			// Array neu formatieren
    			$newArray[] = $this->__reArrangeArray($id, $name, $parent, $docs);
    		}
    		foreach ($newArray as $array) {
    			if ($array['parent'] != null) {
    				$newArray = $this->__connect($newArray);
    			} 	
    		}
    	
    		return $newArray;
    	}
    	
    	// function __reArrangeArray
    	//	$id (int)			ID des Types
    	//	$name (String)		Name des Types
    	//	$parent (int)		Vorfahre des Types
    	//	$docs (Array)		Dokumenten des Typess 
    	function __reArrangeArray($id, $name, $parent, $docs) {
    		$tmpArray['id'] = $id;
    		$tmpArray['name'] = $name;
    		$tmpArray['parent'] = $parent;
    		$tmpArray['docs'] = $docs;
    		$tmpArray['descendants'] = array(); 
    		return $tmpArray;
    	}
    	
    	// function __connect
    	//	$newArray			Neuformatiertes Array
    	function __connect($newArray) {
    		// Index zum Element mit der hÃ¶chsten ParentId ermitteln
    		$indexArr = $this->__getMaxParentIndex($newArray);
    		$indexArr = explode(':' , $indexArr);
    		$index = $indexArr[0];
    		$maxId = $indexArr[1];
    		$hookHere;
    		foreach($newArray as $key => $value) {
    			if ($value['id'] == $maxId) {
    				$hookHere = $key;
    				break;
    			}
    		}	
    		$newArray[$hookHere]['descendants'][] = $newArray[$index];
    		array_splice($newArray, $index, 1);
    		
    		return $newArray;
    	}
    	
    	// function __getMaxParentId
    	//	$newArray (Array)	neu formatiertes Array
    	function __getMaxParentIndex($newArray) {
    		$maxParent = 0;
    		$index;
    		for ($i = 0; $i < count($newArray); $i++) {
    			if ($newArray[$i]['parent'] >= $maxParent) {
    				$maxParent = $newArray[$i]['parent'];
    				$index = $i;
    			}
    		}
    		
    		//$index (int)		Index zum Element mit der hÃ¶chsten ParentId
    		//$maxParent (int)	EnthÃ¤lt den hÃ¶chsten ParentIndex
    		return $index . ':' . $maxParent;
    	}
    	/*
    	 * Ende der Initialisierung des Arrays
    	 */
    	
    	/*
    	 * Erstellung des Baumes
    	 */
    	var $helpers = array('html');
    	var $tree;
    	
    	function createTree($newArray) {				
    		$this->tree = $this->html->tag('div', null, array('id' => 'tree'));
    		$this->__getFolder($newArray);
    		$this->tree .= $this->html->tag('/div');
    		
    		return $this->tree;
    	}
    	
    	// function __getFolder
    	//	$position			EnthÃ¤t die aktuelle Position im Array
    	function __getFolder($position) {
    		$this->tree .= $this->html->tag('ul', null, array('id' => 'folder'));
    		foreach ($position as $element) {
    			$this->tree .= $this->html->tag('li', null, array('onclick' => 'javascript:toggle();'));
    			$this->tree .= $this->html->image('opened.gif');
    			$this->tree .= $element['name'];
    			$this->tree .= $this->html->tag('/li');
    			$this->__getDocuments($element['docs']);
    			if ($element['descendants']) {
    				$this->__getFolder($element['descendants']);
    			}
    		}
    		$this->tree .= $this->html->tag('/ul');
    	}
    	
    	// function __getDocuments
    	//	$position			EnthÃ¤lt die aktuelle Position im Array
    	function __getDocuments($position) {
    		$this->tree .= $this->html->tag('ul', null, array('id' => 'document'));
    		foreach ($position as $doc) {
    			$this->tree .= $this->html->tag('li');
    			$this->tree .= $this->html->image('doc.gif');
    			$this->tree .= $this->html->link($doc['name'], '');
    			$this->tree .= $this->html->tag('/li');
    		} 
    		$this->tree .= $this->html->tag('/ul');
    	}
    	/*
    	 * Ende Erstellung des Baumes
    	 */
    }
    ?>

P.S.: It would be great if anyone would expand the example with some
ajax (releoad in a period like 10sek but remain the preferences which
folder is open/closed) and javascript(open/close folder). Please post
the code. Thanks

______________________________________________________________________
_______________________________

Update on: 18. Januar 21:35

Now IÂ´ve rewritten my code and expanded it to work with some
Javascript to toggle the Folder (open/close) and additionally remain
the settings after a refresh of the page.

Heres the full code:

document.php

::

    
    <?php
    class Document extends AppModel {
    	var $belongsTo = array('Type');
    }
    ?>
    
    type.php
    [code]
    <?php
    class Type extends AppModel {
    	var $hasMany = array('Document');	
    }
    ?>

documents_controller.php

::

    
    <?php
    class DocumentsController extends AppController {
    	var $helpers = array('explorerTree', 'javascript');
    	function index() {
    		$this->set('documents', $this->Document->find('all'));
    		$this->set('types', $this->Document->Type->find('all'));
    	}
    }
    ?>

types_controller.php: not needed

index.ctp (/app/views/documents/)

::

    
    <?php
    echo $javascript->link('jquery/jquery-1.2.6', false);
    echo $javascript->link('explorer_tree', false);
    echo $html->css('explorer_tree');
    
    $newArray = $explorerTree->init($types);
    echo $explorerTree->createTree($newArray);
    ?>

helper: explorer_tree.php

::

    
    <?php
    /*
     * 2 Tabellen mÃ¼ssen existieren:
     *
     * 	create table types (
     * 		id int(4) auto_increment,
     * 		typ varchar(128) not null,
     * 		parent int(4) null,
     * 		primary key(id)
     * 	);
     *
     * 	create table documents (
     * 		id int(8) auto_increment,
     * 		type_id int(4) not null,
     * 		name varchar(128) not null,
     * 		primary key(id)
     * 	);
     *
     *
     * 2 Models:
     *
     * 	class Type extends AppModel {
     * 		var $hasMany = array('Document');
     * 	}
     *
     *  class Document extends AppModel {
     *  	var $belongsTo = array('Type');
     *  }
     *
     *
     * 1 Controller:
     *
     * 	class DocumentsController extends AppController {
     * 		var $helpers = array('explorerTree', 'javascript');
     *
     * 		function index() {
     * 			$this->set('types', $this->Document->Type->find('all'));
     * 		}
     * 	}
     *
     *
     * 1 View:
     *
     * Muss mind. das Javascript laden ($javascript->link('explorer_tree', false);)
     * und natÃ¼rlich die Methoden des Helpers aufrufen
     * ($newArray = $explorerTree->init($types);echo $explorerTree->createTree($newArray);
     */
    class explorerTreeHelper extends AppHelper {
    
    	/*
    	 * Initialisiert das Array
    	 */
    	function init($types) {
    		return $this->__createArray($types);
    	}
    
    	function __createArray($types) {
    		$newArray;
    		foreach ($types as $type) {
    			// Dokumenten-Array leeren
    			foreach ($type['Document'] as $document) {
    				array_splice($document, 0, 2);
    			}
    
    			// Werte auslesen und speichern
    			$id = $type['Type']['id'];
    			$name = $type['Type']['typ'];
    			$parent = $type['Type']['parent'];
    			$docs = $type['Document'];
    
    			// Types-Array leeren
    			array_splice($type, 0);
    
    			// Array neu formatieren
    			$newArray[] = $this->__reArrangeArray($id, $name, $parent, $docs);
    		}
    		foreach ($newArray as $array) {
    			if ($array['parent'] != null) {
    				$newArray = $this->__connect($newArray);
    			}
    		}
    
    		return $newArray;
    	}
    
    	// function __reArrangeArray
    	//	$id (int)			ID des Types
    	//	$name (String)		Name des Types
    	//	$parent (int)		Vorfahre des Types
    	//	$docs (Array)		Dokumenten des Typess
    	function __reArrangeArray($id, $name, $parent, $docs) {
    		$tmpArray['id'] = $id;
    		$tmpArray['name'] = $name;
    		$tmpArray['parent'] = $parent;
    		$tmpArray['docs'] = $docs;
    		$tmpArray['descendants'] = array();
    		return $tmpArray;
    	}
    
    	// function __connect
    	//	$newArray			Neuformatiertes Array
    	function __connect($newArray) {
    		// Index zum Element mit der hÃ¶chsten ParentId ermitteln
    		$indexArr = $this->__getMaxParentIndex($newArray);
    		$indexArr = explode(':' , $indexArr);
    		$index = $indexArr[0];
    		$maxId = $indexArr[1];
    		$hookHere;
    		foreach($newArray as $key => $value) {
    			if ($value['id'] == $maxId) {
    				$hookHere = $key;
    				break;
    			}
    		}
    		$newArray[$hookHere]['descendants'][] = $newArray[$index];
    		array_splice($newArray, $index, 1);
    
    		return $newArray;
    	}
    
    	// function __getMaxParentId
    	//	$newArray (Array)	neu formatiertes Array
    	function __getMaxParentIndex($newArray) {
    		$maxParent = 0;
    		$index;
    		for ($i = 0; $i < count($newArray); $i++) {
    			if ($newArray[$i]['parent'] >= $maxParent) {
    				$maxParent = $newArray[$i]['parent'];
    				$index = $i;
    			}
    		}
    
    		//$index (int)		Index zum Element mit der hÃ¶chsten ParentId
    		//$maxParent (int)	EnthÃ¤lt den hÃ¶chsten ParentIndex
    		return $index . ':' . $maxParent;
    	}
    	/*
    	 * Ende der Initialisierung des Arrays
    	 */
    
    	/*
    	 * Erstellung des Baumes
    	 */
    	var $helpers = array('html');
    	var $tree;
    
    	function createTree($newArray) {
    		$this->tree = $this->html->tag('div', null, array('id' => 'tree'));
    		$this->__getFolder($newArray, null, null);
    		$this->tree .= $this->html->tag('/div');
    
    		return $this->tree;
    	}
    
    	// function __getFolder
    	//	$position (Array)	EnthÃ¤t die aktuelle Position im Array
    	//	$parent	(int)		Id des Elternelements
    	//	$id (int)			Eigene Id		
    	function __getFolder($position, $parent, $id) {
    		if ($id == null) {
    			$this->tree .= $this->html->tag('ul', null, array('id' => $id));
    		}
    		foreach ($position as $element) {
    			$this->tree .= $this->html->tag('li', null, array(
    				'onclick'	=>	'javascript:toggle(' . $element['id'] . ');',
    				'class'		=>	'folder'
    			));
    			$this->tree .= $this->html->image('opened.gif', array(
    				'alt'	=>	$element['name'],
    				'id'	=>	'img' . $element['id']
    			));
    			$this->tree .= $element['name'];
    			$this->tree .= $this->html->tag('/li');
    			$this->__getDocuments($element['docs'], $element['id']);
    			if ($element['descendants']) {
    				$parent .= $element['id'] . ';';
    				$this->__getFolder($element['descendants'], $parent, $element['id']);
    				$this->tree .= $this->html->tag('/ul');
    			} else {
    				$this->tree .= $this->html->tag('/ul');
    			}
    		}
    		if ($id == null) {
    			$this->tree .= $this->html->tag('/ul');
    		}
    	}
    
    	// function __getDocuments
    	//	$position (Array)	EnthÃ¤lt die aktuelle Position im Array
    	function __getDocuments($position, $id) {
    		$this->tree .= $this->html->tag('ul', null, array('id' => $id));
    		foreach ($position as $doc) {
    			$this->tree .= $this->html->tag('li');
    			$this->tree .= $this->html->image('doc.gif');
    			$this->tree .= $this->html->link($doc['name'], '');
    			$this->tree .= $this->html->tag('/li');
    		}
    		//$this->tree .= $this->html->tag('/ul');
    	}
    	/*
    	 * Ende Erstellung des Baumes
    	 */
    }
    ?>

explorer_tree.css (app/webroot/css/)

::

    
    ul, li {
    	list-style-type: none;
    }
    
    li.folder {
      cursor: pointer;
    }
    
    a {
      cursor: default;
    }

explorer_tree.js (/app/webroot/js/)

::

    
    $(document).ready(function() {
    	$("ul").css({display: "block"});
    	loadSettings();
    });
    
    function toggle(id) {
    	ul = $("ul#" + id);
    	img = $("img#img" + id);
    	 	
    	if (ul.css("display") == "block") {
    		ul.css({display: "none"});
    		img.attr("src", "/cake/img/closed.gif");
    	} else {
    		ul.css({display: "block"});
    		img.attr("src", "/cake/img/opened.gif");
    	}	
      event.cancelBubble = true;
    	
    	/* Speichern der Einstellungen */
    	save = window.name;
    	save += ul.attr("id") + "=" + ul.css("display") + ":";
    	window.name = save;
    }
    
    function loadSettings() {
    	var settings = window.name.split(":");
    	var setting;
    	var id;
    	var css;
    	var ul;
    	var img;
    
    	if (window.name != "") {
    		for (i = 0; i < settings.length - 1; i++) {
    			setting = settings[i].split("=");
    			id = setting[0];
    			css = setting[1];
    			ul = $("ul#" + id);
    			img = $("img#img" + id);
    			ul.css({display: css});
    			if (ul.css("display") == "block") {
    				img.attr("src", "/cake/img/opened.gif");
    			} else {
    				img.attr("src", "/cake/img/closed.gif");
    			}
    		}
    	}
    }

As you can see in the view, you additionally need jquery.


.. meta::
    :title: Creating an Explorer-like Navigation
    :description: CakePHP Article related to ,Helpers
    :keywords: ,Helpers
    :copyright: Copyright 2009 
    :category: helpers

