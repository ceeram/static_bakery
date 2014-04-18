SluggableTree behavior to make tree based slugs
===============================================

SluggableTree behavior extends the base Sluggable behavior to make
tree based slugs which are generated as a path of tree and can be used
to make a nicer urls and much more. The main features of this behavior
are: 1) Possibility to get nice urls and retrieve your leaf by it`s
path or slug. 2) Create and highlight n-th level menus. 3) Translate
the slugs and leaf path in the tree by attaching core or extended
Translate behavior.

First of all, thanks to Mariano Iglesias the developer of `Sluggable`_
behavior, which is a core of SluggableTree behavior


Things you need to do:
~~~~~~~~~~~~~~~~~~~~~~

+ Download the Sluggable behavior
+ Saving SluggableTree behavior to /app/models/behaviors/ directory
+ Setting up Tree based model



Downloading Sluggable behavior
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Get the latest version of Sluggable behavior from `here`_ and copy it
to /app/models/behaviors/ directory

Notice: SluggableTree behavior is built on Sluggable behavior which
version is: 1.1.36

SluggableTree behavior
~~~~~~~~~~~~~~~~~~~~~~

::

    <?php
    
    // File -> app/models/behaviors/sluggable_tree.php
    
    /** 
     * Thanks to Mariano Iglesias - the developer of Sluggable behavior,
     * which is the core of SluggableTree and thanks to CakePHP core team.
     *   
     * With SluggableTree behavior you will be able to make the nice
     * urls on tree based menus and translate the tree slug and tree path 
     * to any locales your application uses. This behavior will automatically
     * synchronize the tree path with leaf`s child slugs then it is modified.
     * 
     * @author sky_l3ppard
     * @version 1.0.0
     * @license MIT
     * @category Behaviors
     */
    App::import('Behavior', 'Sluggable'); 
    
    class SluggableTreeBehavior extends SluggableBehavior {
    	
    	/**
    	 * List of options required for communication
    	 * between callback processes
    	 * 
    	 * @var array - list of runtime options
    	 * @access private
    	 */
    	var $__runtime = array();
    	
    	/**
    	 * If any behavior which extends Translate is attached,
    	 * this variable is storing the name of this behavior.
    	 * Then SluggableTree behavior knows how to translate
    	 * the slug
    	 * 
    	 * @var string - name of Translate behavior attached
    	 * @access private
    	 */
    	var $__translate = false;
    	
    	/**
    	 * If translate behavior is found. These fields
    	 * will be added for translation in runtime data
    	 * 
    	 * @var array - list of fields to translate
    	 * @access protected
    	 */
    	var $_translatable = array();
    	
    	/**
    	 * Initiate behavior with specified settings, which are common
    	 * with extended Sluggable behavior
    	 *  
    	* Available settings are:
    	 * 
    	 * For SluggableTree behavior
    	 * 
    	 * - delimiter:	(string, optional) The tree slug/path delimiter used to separate
    	 * 				slugs in path.
    	 * 
    	 * - pathField: (string, optional) If this field is not empty, the pathField will
    	 * 				contain the path of the tree leaf, if pathField value is false - 
    	 * 				then 'slug' field will be used to store the path.
    	 * 
    	 * For Sluggable behavior
    	 *  
    	 * - label: 	(array | string, optional) set to the field name that contains the
    	 * 				string from where to generate the slug, or a set of field names to
    	 * 				concatenate for generating the slug. DEFAULTS TO: title
    	 *
    	 * - slug:		(string, optional) name of the field name that holds generated slugs.
    	 * 				DEFAULTS TO: slug
    	 *
    	 * - separator:	(string, optional) separator character / string to use for replacing
    	 * 				non alphabetic characters in generated slug. DEFAULTS TO: -
    	 *
    	 * - length:	(integer, optional) maximum length the generated slug can have.
    	 * 				DEFAULTS TO: 100
    	 *
    	 * - overwrite: (boolean, optional) set to true if slugs should be re-generated when
    	 * 				updating an existing record. DEFAULTS TO: false
    	 * 
    	 * - translation: allows you to specify two methods of built-in character translation 
    	 * 				(utf-8 and iso-8859-1) to keep specific characters from being considered 
    	 * 				as invalid, or declare your own translation tables.
    	 * 	
    	 * @see cake/libs/model/ModelBehavior#setup($model, $config)
    	 * @param object $Model - reference to the Model
    	 * @param array $settings - list of settings used for this behavior
    	 * @return void
    	 * @access public
    	 */
    	function setup(&$Model, $settings) {
    		$default = array(
    			'delimiter' => '/',
    			'pathField' => 'path'
    		);
    		$settings = array_merge($default, (array)$settings);
    		//settings passed to Sluggable behavior
    		parent::setup($Model, (array)$settings);
    	}
    	
    	/**
    	 * In this case beforeSave callback converts the label fields to the slug
    	 * and updates a slug by parent leafs. Also if record is being edited, 
    	 * this method will prepare data for synchronization of tree leaf childs
    	 * 
    	 * @see models/behaviors/SluggableBehavior#beforeSave($Model)
    	 * @param object $Model - reference to the Model
    	 * @return boolean - true on success, false on rollback
    	 * @access public
    	 */
    	function beforeSave(&$Model) {
    		parent::beforeSave($Model);
    		
    		if (empty($Model->data[$Model->alias][$this->__settings[$Model->alias]['slug']])) {
    			$this->_invalidateLabelFields($Model, __('Slug was not found in Model data', true));
    			return false;
    		}
    		//check for Translate behavior 
    		$this->__isSlugTranslatable($Model);
    		//prepare Tree synchronization data if Tree behavior is enabled
    		$hasPathField = $this->__settings[$Model->alias]['pathField'] !== false;
    		//find out which field to use for path
    		$pathField = $hasPathField ? $this->__settings[$Model->alias]['pathField'] : $this->__settings[$Model->alias]['slug'];
    		if ($Model->Behaviors->enabled('Tree') && ($Model->hasField($this->__settings[$Model->alias]['pathField']) || !$hasPathField)) {
    			//get the slug and concat with elements in path
    			$path = $Model->data[$Model->alias][$this->__settings[$Model->alias]['slug']];
    			$parentId = $Model->data[$Model->alias]['parent_id'];
    			while ($parentId) {
    				$fields = array($Model->alias.'.'.$this->__settings[$Model->alias]['slug'], $Model->alias.'.parent_id');
    				$conditions = array($Model->alias.'.'.$Model->primaryKey => $parentId);
    				$recursive = -1;
    							
    				$record = $Model->find('first', compact('conditions', 'fields', 'recursive'));
    				$path = $record[$Model->alias][$this->__settings[$Model->alias]['slug']].$this->__settings[$Model->alias]['delimiter'].$path;
    				
    				$pathInfo = $Model->schema($pathField);
    				if ($pathInfo['length'] < strlen($path)) {
    					$this->_invalidateLabelFields($Model, __('Path is too long, check your sluggable field length', true));
    					return false;
    				}
    				$parentId = $record[$Model->alias]['parent_id'];
    				unset($record);
    			}
    			
    			//check if is unique path
    			$conditions = array($Model->alias.'.'.$pathField => $path);
    			if ($Model->find('count', compact('conditions'))) {
    				$this->_invalidateLabelFields($Model, __('Tree path must be unique', true));
    				return false;
    			}
    			
    			if (!empty($Model->id)) {
    				$newPath = explode('/', $path);
    				$this->__runtime[$Model->alias]['changeTo'] = $Model->data[$Model->alias][$this->__settings[$Model->alias]['slug']];
    				$position = array_search($this->__runtime[$Model->alias]['changeTo'], $newPath);
    				$this->__runtime[$Model->alias]['position'] = $position;
    				$this->__runtime[$Model->alias]['field'] = $pathField;
    			}
    			if (!$hasPathField) {
    				$Model->data[$Model->alias][$this->__settings[$Model->alias]['slug']] = $path;
    			} else {
    				$Model->data[$Model->alias][$this->__settings[$Model->alias]['pathField']] = $path;
    			}
    		}
    		
    		//if you use another Translate or extended Translate bahavior, logic goes here
    		if (!empty($this->__translate) && $Model->Behaviors->enabled($this->__translate)) {
    			//Translate behavior must be executed before SluggableTree, changing order if necessary
    			$attached = $Model->Behaviors->attached();
    			if (array_search($this->__translate, $attached) > array_search('SluggableTree', $attached)) {
    				unset($Model->Behaviors->_attached[array_search($this->__translate, $attached)]);
    				array_unshift($Model->Behaviors->_attached, $this->__translate);
    			}
    			//checking if slug is a translatable field
    			$trans =& $Model->Behaviors->{$this->__translate};
    			foreach ($this->_translatable as $fld) {
    				$trans->runtime[$Model->alias]['beforeSave'][$fld] = $Model->data[$Model->alias][$fld];
    			}
    		}
    		return true;
    	}
    	
    	/**
    	 * Synchronizes the saved leaf`s child slugs
    	 * 
    	 * @param object $Model - reference to the Model
    	 * @param boolean $created - true if record was inserted
    	 * @return void
    	 * @access public
    	 */
    	function afterSave(&$Model, $created) {
    		parent::afterSave($Model, $created);
    		
    		if (empty($this->__runtime[$Model->alias])) {
    			return;
    		}
    		//synchronization requires disabling this bahavior
    		$Model->Behaviors->disable('SluggableTree');
    		$this->_sync($Model, $Model->id);
    		$Model->Behaviors->enable('SluggableTree');
    		//clearing all runtime data
    		unset($this->__runtime[$Model->alias]);
    	}
    	
    	/**
    	 * Synchronizes child slugs
    	 * 
    	 * @param object $Model - reference to the Model
    	 * @param integer $leafId - id of leaf being updated
    	 * @return void
    	 * @access protected
    	 */
    	function _sync(&$Model, $leafId) {
    		//getting runtime data
    		$runtime =& $this->__runtime[$Model->alias];
    		
    		$conditions = array($Model->alias.'.parent_id' => $leafId);
    		$fields = array($Model->alias.'.'.$Model->primaryKey, $Model->alias.'.'.$runtime['field']);
    		$recursive = -1;
    		//get all children
    		$children = $Model->find('all', compact('conditions', 'fields', 'recursive'));
    		if (empty($children)) {
    			return;
    		}
    		foreach ($children as $child) {
    			$childPath = explode('/', $child[$Model->alias][$runtime['field']]);
    			$childPath[$runtime['position']] = $runtime['changeTo'];
    			$path = join('/', $childPath);
    
    			$Model->create();
    			$Model->id = $child[$Model->alias][$Model->primaryKey];
    			$Model->data[$Model->alias][$runtime['field']] = $path;
    			$Model->save();
    			$this->_sync($Model, $child[$Model->alias][$Model->primaryKey]);
    		}
    	}
    	
    	/**
    	 * Checks for attached Translate behavior or any extended
    	 * Translate behavior and if slug is in the list of translatable
    	 * fields, then this behavior is used to translate the slug.
    	 * 
    	 * @param object $Model - reference to the Model
    	 * @return void
    	 * @access private
    	 */
    	function __isSlugTranslatable(&$Model) {
    		if ($Model->Behaviors->attached('Translate')) {
    			$this->__translate = 'Translate';
    		} else {
    			foreach ($Model->Behaviors->attached() as $behavior) {
    				if (is_a($Model->Behaviors->{$behavior}, 'TranslateBehavior')) {
    					$this->__translate = $behavior;
    					break;
    				}
    			} 
    		}
    		
    		if (empty($this->__translate)) {
    			return;
    		}
    		
    		//check for translatable fields
    		$trans =& $Model->Behaviors->{$this->__translate};
    		foreach ($trans->settings[$Model->alias] as $key => $field) {
    			$translatableField = is_numeric($key) ? $field : $key;
    			if (in_array($translatableField, array($this->__settings[$Model->alias]['slug'], $this->__settings[$Model->alias]['pathField']))) {
    				$this->_translatable[] = $translatableField;
    			}
    		}
    		if (count($this->_translatable)) {
    			return;
    		}
    		//field slug and path are not translatable
    		$this->__translate = false;
    	}
    	
    	/**
    	 * Invalidates all label fields with given error message
    	 * 
    	 * @param object $Model - reference to the Model
    	 * @param string $message - message for invalid label fields
    	 * @return void
    	 * @access protected
    	 */
    	function _invalidateLabelFields(&$Model, $message) {
    		if (empty($message)) {
    			return;
    		}
    		
    		foreach ($this->__settings[$Model->alias]['label'] as $field) {
    			$Model->invalidate($field, $message);
    		}
    	}
    }
    ?>



Setting up tree based model
~~~~~~~~~~~~~~~~~~~~~~~~~~~

To set up your model for SluggableTree behavior correctly, you need to
make your model act as Tree first. If you do not know how to do it,
please fallow these basic steps in `CakePHP manual`_. In fact,
SluggableTree behavior can slug and translate your label fields
without using core Tree behavior


A simple setup to use the SluggableTree behavior
````````````````````````````````````````````````

If the steps were fallowed right and you have Sluggable and
SluggableTree behaviors in your behaviors folder. Then your tree based
model should look like:


Model Class:
````````````

::

    <?php 
    // File -> app/models/page.php
    
    class Page extends AppModel {
    	
    	var $name = 'Page';
    	var $actsAs = array(
    		'Tree',
    		'SluggableTree'
    	);
    }
    ?>


This is a very simple setup and if this model has fields title , slug
and path then title would be slugged in to the tree path and stored in
the path field and the single slug would be stored in the slug field.
There are some possible settings available in this behavior and they
will be discussed later

On this kind of model configuration the table pages should look like
this:
id - primary key
parent_id - id of parent record, part of tree behavior
lft - used for tree behavior
rght - used for tree behavior
slug - field to store slug
path - field to store the path of slugs
title - title of tree leaf, from which the tree_slug is formed
content - additional field for content for example - optional


More functional setup
`````````````````````


Model Class:
````````````

::

    <?php 
    // File -> app/models/page.php
    
    class Page extends AppModel {
    	
    	var $name = 'Page';
    	var $actsAs = array(
    		'Tree',
    		'Translate' => array(
    			'tree_slug', 
    			'title',
    			'tree_path',
    			'short_name'
    		),
    		'SluggableTree' => array(
    			'overwrite' => true,
    			'translation' => 'utf-8',
    			'length' => 64,
    			'slug' => 'tree_slug',
    			'pathField' => 'tree_path',
    			'label' => array('title', 'short_name'),
    			'delimiter' => '.'
    		)
    	);
    }
    ?>


In this case we are also using core Translate behavior which will make
translations for fields: tree_slug , title , short_name and tree_path
. We need to tell SluggableTree behavior that the slug field is named
like tree_slug and it will be made from title and short_name fields.
The tree path generated from slugs will be stored in tree_path field
and the slugs in the path will be separated by (. - dot). Next, the
maximum length of single leaf slug is 64 characters and setting -
overwrite says that the slug will be regenerated on every edit
operation. Translate setting will slug utf-8 characters

Notice: it is better to set Translate behavior before SluggableTree to
avoid extra time for behavior to reorder the operations and to repeat
some functionality

On this kind of model configuration the table pages should look like
this:
id - primary key
parent_id - id of parent record, part of tree behavior
lft - used for tree behavior
rght - used for tree behavior
tree_slug - field to store slug
tree_path - field to store the path of slugs
title - title of tree leaf, from which the tree_slug is formed
short_name - another label field of tree leaf, from which the
tree_slug is formed
content - additional field for content for example


Possible settings for SluggableTree behavior
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


Settings for TreeSluggable behavior:
````````````````````````````````````

+ delimiter - string, the character used to separate the tree path in
  the slug, default - /
+ pathField - string, the name of the field where tree path of slugs
  will be stored. if set to false , slug field will be used to store the
  tree path, default - path



Settings used for Sluggable behavior:
`````````````````````````````````````

+ slug - string, the name of the field where the slug will be stored,
  default - slug
+ label - string or array, the single or list of the fields in table
  which will be used to generate slug, default - title
+ separator - string, usually a single character used to separate
  words in the slug, default - -
+ length - integer, the maximum length of the slug, in SluggableTree
  it is the length of the single slug used in path, default - 100
+ overwrite - boolean, true to overwrite slug on each edit operation,
  default - false
+ translate - string, allows you to specify two methods of built-in
  character translation (utf-8 and iso-8859-1) to keep specific
  characters from being considered as invalid, or declare your own
  translation tables, default - null

`Heres more`_ about Sluggable behavior and settings


The expected result example of SluggableTree behavior
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This example is based on simple setup model

In your pages controller somewhere add this test function, notice:
your pages controller should use model Page . By calling this public
function, test data will be added to your pages table

::

    <?php 
    function test() {
    	//add page #1 with title "Home" path - "home"
    	$this->Page->create();
    	$this->Page->data[$this->Page->alias]['title'] = 'Home';
    	$this->Page->data[$this->Page->alias]['parent_id'] = null;
    	$this->Page->save();
    	//add page #2 with title "About" path - "about"
    	$this->Page->create();
    	$this->Page->data[$this->Page->alias]['title'] = 'About';
    	$this->Page->data[$this->Page->alias]['parent_id'] = null;
    	$this->Page->save();
    	$about_id = $this->Page->id;
    	//add page #3 with title "Company" path - "about/company"
    	$this->Page->create();
    	$this->Page->data[$this->Page->alias]['title'] = 'Company';
    	$this->Page->data[$this->Page->alias]['parent_id'] = $about_id;
    	$this->Page->save();
    	$company_id = $this->Page->id;
    	//add page #4 with title "Career" path - "about/company/career"
    	$this->Page->create();
    	$this->Page->data[$this->Page->alias]['title'] = 'Career';
    	$this->Page->data[$this->Page->alias]['parent_id'] = $company_id;
    	$this->Page->save();
    	//add page #5 with title "Gallery" path - "about/company/gallery"
    	$this->Page->create();
    	$this->Page->data[$this->Page->alias]['title'] = 'Gallery';
    	$this->Page->data[$this->Page->alias]['parent_id'] = $company_id;
    	$this->Page->save();
    	//add page #6 with title "Managers" path - "about/managers"
    	$this->Page->create();
    	$this->Page->data[$this->Page->alias]['title'] = 'Managers';
    	$this->Page->data[$this->Page->alias]['parent_id'] = $about_id;
    	$this->Page->save();
    	
    	echo 'Done - adding test pages';
    	$this->autoRender = false;
    }
    ?>


After calling this function, and having your database table based on
SluggableTree behavior, you should see tree path and slug fields added
in your table


Usage of SluggableTree behavior
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The main feature is to add the tree path as an url link to the
SluggableTree model record. To do so in this example the pages
controller`s display action will be modified - after some adjustments
it should look like:

::

    <?php 
    function display() {
    	//arguments are the path of page in the tree
    	$path = func_get_args();
    	
    	$count = count($path);
    	if (!$count) {
    		$this->redirect('/');
    	}
    	//find the page by it`s path
    	$page = $this->Page->findByPath(join('/', $path));
    	//store the last accessed page path
    	$this->Session->write('ActivePath', $path);
    	//params for view
    	$this->set(compact('page'));
    }
    ?>


Now then we pass a path in the link to the page, the url for example
looks like: example.com/pages/display/[i]about/company/gallery[i]
where text in italics is our page path given as slug and to end user
it simply looks like normal pretty url, he can modify this path and
get the resulted parent page


Another feature may be the highlighting the active menu items in
separated menus
```````````````

To do so, we have stored ActivePath in the session using display
action of pages controller. Then generating for example second level
of menu, you only need to check if the Page slug is in ActivePath , or
if your slugs are not unique a simple path comparison function can fit

Any ideas on functionality improvements are very welcome, enjoy..


.. _Heres more: http://bakery.cakephp.org/articles/view/sluggable-behavior
.. _CakePHP manual: http://book.cakephp.org/view/91/Tree

.. author:: sky_l3ppard
.. categories:: articles, behaviors
.. tags:: tree,menu,behavior,sky leppard,sluggable,translatable
slug,menu path,pretty urls,highlight,nice url,Behaviors

