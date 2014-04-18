HABTM Add & Delete Behavior
===========================

Many people gripe about the HABTM associations in CakePHP and how
difficult it is to add or delete a single record. This behavior takes
care of the task for you!

CakePHP makes a developers day-to-day grind very easy! But when it
comes to hasAndBelongsToMany relationships (or more commonly referred
to HABTM) many people find themselves confused -- especially when you
try to add or delete associations multiple times.

Throughout this article I will use the good ole Post
hasAndBelongsToMany Tags relationship for easy clarification.


The Age-Old Problem
+++++++++++++++++++
(Using our Post HABTM Tag concept) We want to tag a Post (id=1) with a
Tag (id=1). Our code is as follows:

::

    <?php
    $this->Post->save(array(
    	'Post' => array(
    		'id' => 1,
    	),
    	'Tag' => array(
    		'Tag' => array(1),
    	),
    ));
    ?>

No secret that this works correctly but look what happens when you try
to add (or append) another Tag say (id=2) to our Post(id=1):

::

    <?php
    $this->Post->save(array(
    	'Post' => array(
    		'id' => 1,
    	),
    	'Tag' => array(
    		'Tag' => array(2),
    	),
    ));
    // Foreward: Check out the debug SQL for this save ...
    // DELETE FROM `posts_tags` WHERE post_id = '1'
    // INSERT INTO `posts_tags` (post_id,tag_id) VALUES (1,2)
    ?>

Many bakers at this point throw their hands up in the air in
frustration, cursing to the Cake gods (or us in the IRC chat) in
futility " WHERE DID THE FIRST TAG GO? ". Yes, it's true, CakePHP has
deleted Tag (id=1) for Post (id=1) in the cross table * Check out the
debug SQL *. The evil masterminds behind CakePHP designed it this way
and it works like it should under EVERY circumstance other than
'adding' (like this example) and 'deleting' associations one at a
time.


The Solution - ExtendAssociations Behavior
++++++++++++++++++++++++++++++++++++++++++

This behavior allows you to easily add or delete HABTM associations!
(It also includes a cool unbindAll() method).


Installation
````````````
1. Create a file named 'extend_associations.php' in your
./app/models/behaviors folder and copy the following code into it.

::

    <?php
    /**
     * Extend Associations Behavior
     * Extends some basic add/delete function to the HABTM relationship
     * in CakePHP.  Also includes an unbindAll($exceptions=array()) for 
     * unbinding ALL associations on the fly.
     * 
     * This code is loosely based on the concepts from:
     * http://rossoft.wordpress.com/2006/08/23/working-with-habtm-associations/
     * 
     * @author Brandon Parise <brandon@parisemedia.com>
     * @package CakePHP Behaviors
     *
     */
    class ExtendAssociationsBehavior extends ModelBehavior {
    	/**
    	 * Model-specific settings
    	 * @var array
    	 */
    	var $settings = array();
    	
    	/**
    	 * Setup
    	 * Noething sp
    	 *
    	 * @param unknown_type $model
    	 * @param unknown_type $settings
    	 */
    	function setup(&$model, $settings = array()) {
    		// no special setup required
    		$this->settings[$model->name] = $settings;
    	}
    	
    	/**
    	 * Add an HABTM association
    	 *
    	 * @param Model $model
    	 * @param string $assoc
    	 * @param int $id
    	 * @param mixed $assoc_ids
    	 * @return boolean
    	 */
    	function habtmAdd(&$model, $assoc, $id, $assoc_ids) {
    		if(!is_array($assoc_ids)) {
    			$assoc_ids = array($assoc_ids);
    		}
    		
    		// make sure the association exists
    		if(isset($model->hasAndBelongsToMany[$assoc])) {
    			$data = $this->__habtmFind($model, $assoc, $id);
    			
    			// no data to update
    			if(empty($data)) {
    				return false;
    			}
    			
    			// important to use array_unique() since merging will add 
    			// non-unique values to the array.
    			$data[$assoc][$assoc] = array_unique(am($data[$assoc][$assoc], $assoc_ids));
    			return $model->save($data);
    		}
    		
    		// association doesn't exist, return false
    		return false;
    	}
    	
    	/**
    	 * Delete an HABTM Association
    	 *
    	 * @param Model $model
    	 * @param string $assoc
    	 * @param int $id
    	 * @param mixed $assoc_ids
    	 * @return boolean
    	 */
    	function habtmDelete(&$model, $assoc, $id, $assoc_ids) {
    		if(!is_array($assoc_ids)) {
    			$assoc_ids = array($assoc_ids);
    		}
    		
    		// make sure the association exists
    		if(isset($model->hasAndBelongsToMany[$assoc])) {
    			$data = $this->__habtmFind($model, $assoc, $id);
    			
    			// no data to update
    			if(empty($data)) {
    				return false;
    			}
    						
    			// if the * (all) is set then we want to delete all
    			if($assoc_ids[0] == '*') {
    				$data[$assoc][$assoc] = array();
    			} else {
    				// use array_diff to see what values we DONT want to delete
    				// which is the ones we want to re-save.
    				$data[$assoc][$assoc] = array_diff($data[$assoc][$assoc], $assoc_ids);
    			}
    			return $model->save($data);
    		}
    		
    		// association doesn't exist, return false		
    		return false;
    	}
    		
    	/**
    	 * Delete All HABTM Associations
    	 * Just a nicer way to do easily delete all.
    	 *
    	 * @param Model $model
    	 * @param string $assoc
    	 * @param int $id
    	 * @return boolean
    	 */
    	function habtmDeleteAll(&$model, $assoc, $id) {
    		return $this->habtmDelete($model, $assoc, $id, '*');
    	}
    	
    	/**
    	 * Find 
    	 * This method allows cake to do the dirty work to 
    	 * fetch the current HABTM association.
    	 *
    	 * @param Model $model
    	 * @param string $assoc
    	 * @param int $id
    	 * @return array
    	 */	
    	function __habtmFind(&$model, $assoc, $id) {
    		// temp holder for model-sensitive params
    		$tmp_recursive = $model->recursive;
    		$tmp_cacheQueries = $model->cacheQueries;
    		
    		$model->recursive = 1;
    		$model->cacheQueries = false;
    		
    		// unbind all models except the habtm association
    		$this->unbindAll($model, array('hasAndBelongsToMany' => array($assoc)));
    		$data = $model->find(array($model->name.'.'.$model->primaryKey => $id));
    			
    		$model->recursive = $tmp_recursive;
    		$model->cacheQueries = $tmp_cacheQueries;
    		
    		if(!empty($data)) {
    			// use Set::extract to extract the id's ONLY of the $assoc
    			$data[$assoc] = array($assoc => Set::extract($data, $assoc.'.{n}.'.$model->primaryKey));
    		}
    		
    		return $data;
    	}
    	
    	/**
    	 * UnbindAll with Exceptions
    	 * Allows you to quickly unbindAll of a model's 
    	 * associations with the exception of param 2.
    	 *
    	 * Usage:
    	 *   $this->Model->unbindAll(); // unbinds ALL
    	 *   $this->Model->unbindAll(array('hasMany' => array('Model2')) // unbind All except hasMany-Model2
    	 * 
    	 * @param Model $model
    	 * @param array $exceptions
    	 */
    	function unbindAll(&$model, $exceptions = array()) {
    		$unbind = array();
    		foreach($model->__associations as $type) {
    			foreach($model->{$type} as $assoc=>$assocData) {
    				// if the assoc is NOT in the exceptions list then
    				// add it to the list of models to be unbound.
    				if(@!in_array($assoc, $exceptions[$type])) {
    					$unbind[$type][] = $assoc;
    				}
    			}
    		}
    		// if we actually have models to unbind
    		if(count($unbind) > 0) {
    			$model->unbindModel($unbind);
    		}
    	}
    }
    ?>

2. Add the following line of code to your model.

::

    <?php 
    var $actsAs = 'ExtendAssociations';
    ?>



Example Usage
`````````````
Our Post model:

Model Class:
````````````

::

    <?php 
    class Post extends AppModel {
    	var $name = 'Post';
    
    	var $actsAs = 'ExtendAssociations';
    	
    	var $hasAndBelongsToMany = array(
    		'Tag' => array(
    			'className' => 'Tag',
    			'joinTable' => 'posts_tags',
    			'foreignKey' => 'post_id',
    			'associationForeignKey' => 'tag_id',
    		),
    	);
    }
    ?>



Adding Associations
+++++++++++++++++++

::

    <?php
    // add a single association
    $this->Post->habtmAdd('Tag', 1, 1);
    // add multiple associations in a single call
    $this->Post->habtmAdd('Tag', 1, array(1, 2, 3));
    ?>



Deleting Associations
+++++++++++++++++++++

::

    <?php
    // delete a single association
    $this->Post->habtmDelete('Tag', 1, 1);
    // delete multiple associations in a single call
    $this->Post->habtmDelete('Tag', 1, array(1, 3));
    // want to delete all associations?
    $this->Post->habtmDeleteAll('Tag', 1);
    ?>



Unbinding All Associations (with Exceptions)
++++++++++++++++++++++++++++++++++++++++++++

::

    <?php
    // unbind ALL associations
    $this->Post->unbindAll();
    // unbind ALL except hasAndBelongsToMany['Tag']
    $this->Post->unbindAll(array('hasAndBelongsToMany' => array('Tag')));
    ?>


I am sure in due time this will be added to the core but in the
meantime this should suffice!



.. author:: bparise
.. categories:: articles, behaviors
.. tags:: unbindAll,save,hasAndBelongsToMany,HABTM,behavior,Delete,upd
ate,Behaviors

