Polymorphic Behavior
====================

by AD7six on March 13, 2008

A behavior which will allow a model to be associated with any other
model.
In a couple of places, most notably the Generic Upload Behavior
(`http://cakeforge.org/frs/?group_id=152_id=355`_), I've made use of
polymorphic associations to allow associating a model to any other
model. Here's the polymorphic logic distilled into a dedicated
behavior.


The behavior
````````````
For any find directly on the polymorphic model, the associated model
data will also be returned.

::

    
    <?php
    /* SVN FILE: $Id: polymorphic.php 18 2008-03-07 12:56:09Z andy $ */
    /**
     * Polymorphic Behavior.
     *
     * Allow the model to be associated with any other model object
     *
     * Copyright (c), Andy Dawson
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @filesource
     * @author 		Andy Dawson (AD7six)
     * @version		$Revision: 18 $
     * @modifiedby		$LastChangedBy: andy $
     * @lastmodified	$Date: 2008-03-07 13:56:09 +0100 (Fri, 07 Mar 2008) $
     * @license		http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    class PolymorphicBehavior extends ModelBehavior {
    	
    	function setup(&$model, $config = array()) {
    		$this->settings[$model->name] = am (array('classField' => 'class', 'foreignKey' => 'foreign_id'),$config);
    	}
    
    	function afterFind (&$model, $results, $primary = false) {
    		extract($this->settings[$model->name]);
    		if ($primary && isset($results[0][$model->alias][$classField]) && $model->recursive > 0) {
    			foreach ($results as $key => $result) {
    				$associated = array();
    				$class = $result[$model->alias][$classField];
    				$foreignId = $result[$model->alias][$foreignKey];
    				if ($class && $foreignId) {
    					$result = $result[$model->alias];
    					if (!isset($model->$class)) {
    						$model->bindModel(array('belongsTo' => array(
    							$class => array(
    								'conditions' => array($model->alias . '.' . $classField => $class),
    								'foreignKey' => $foreignKey
    							)
    						)));
    					}
    					$associated = $model->$class->find(array($class . '.id' => $foreignId), 
    						array('id', $model->$class->displayField), null, -1);
    					$associated[$class]['display_field'] = $associated[$class][$model->$class->displayField];
    					$results[$key][$class] = $associated[$class];
    				}
    			}
    		} elseif(isset($results[$model->alias][$classField])) {
    			$associated = array();
    			$class = $results[$model->alias][$classField];
    			$foreignId = $results[$model->alias][$foreignKey];
    			if ($class && $foreignId) {
    				$result = $results[$model->alias];
    				if (!isset($model->$class)) {
    					$model->bindModel(array('belongsTo' => array(
    						$class => array(
    							'conditions' => array($model->alias . '.' . $classField => $class),
    							'foreignKey' => $foreignKey
    						)
    					)));
    				}
    				$associated = $model->$class->find(array($class.'.id' => $foreignId), array('id', $model->$class->displayField), null, -1);
    				$associated[$class]['display_field'] = $associated[$class][$model->$class->displayField];
    				$results[$class] = $associated[$class];
    			}
    		}
    		return $results;
    	}
    }
    ?>


Setting up the model
````````````````````
The necessary sql to setup a polymorphic association, in this example
"notes". The combination of class and foreign_id are used to find the
associated model.

::

    
    CREATE TABLE `notes` (
      `id` int(11) unsigned NOT NULL auto_increment,
      `class` varchar(30) NOT NULL,
      `foreign_id` int(11) unsigned NOT NULL,
      `title` varchar(100) NOT NULL,
      `content` text NOT NULL,
      `created` datetime default NULL,
      `modified` datetime default NULL,
      PRIMARY KEY  (`id`)
    );

The model definition for the model for the Note model:

Model Class:
````````````

::

    <?php 
    class Note extends AppModel {
    
    	var $name = 'Note';
    
    	var $actsAs = array('Polymorphic');
    }
    ?>


Setting up associations
```````````````````````
Polymorphic conditions are not applied automatically and must be
included in the association definition explicitly. For example:

Model Class:
````````````

::

    <?php 
    class Thingy extends AppModel {
    
    	var $name = 'Thingy';
    
    	var $hasMany = array(
    		'Note' => array(
    			'className' => 'Note',	
    			'foreignKey' => 'foreign_id',
    			'conditions' => array('Note.class' => 'Thingy'),
    			'dependent' => true
    		)
    	);
    }
    ?>

It's possible to avoid needing to explicitly state the conditions and
foreignKey by adding some logic to your AppModel, e.g. if it's desired
that all models have an association to Note:


Model Class:
````````````

::

    <?php 
    class AppModel extends Model{
    
    	var $hasMany => array('Note');
    
    	function __construct($id = false, $table = null, $ds = null) {
    		parent::__construct($id, $table, $ds);
    		if (isset($this->hasMany['Note'])) {
    			$this->hasMany['Note']['conditions']['Note.class'] = $this->name;
    			$this->hasMany['Note']['foreignKey'] = 'foreign_id';
    		}
    	}
    ?>

If you do the above, for any model which does not required the Note
model - override the var $hasMany and don't include Note in it.


Example Usage
`````````````
Find all notes realted to this Thingy:

::

    
    <?php
    //...
    $conditions['Note.class'] = 'Thingy';
    $conditions['Note.foreign_id'] = $this->Thingy->id;
    $notes = $this->Thingy->Note->find('all', compact('conditions'));
    
    // Or simply
    $data = $this->Thingy->read();

If you don't want to find all Notes for a particular object, but
simply all notes in the system and whatever they are associated with -
this is where the behavior actually does something. So:

::

    
    <?php
    //...
    $notes = $this->Note->find('all');

Would give you:

::

    
    <?php
    Array
    (
        [0] => Array
            (
                [Note] => Array
                    (
                        [id] => 1
                        [class] => Thingy
                        [foreign_id] => 2
                        [title] => Extremely important
                        [content] => A note on something
                    )
    
                [Thingy] => Array
                    (
                        [id] => 2
                        [name] => Something // display field for this model
                        [display_field] => Something
                    )
    
            )
    
        [1] => Array
            (
                [Note] => Array
                    (
                        [id] => 2
                        [class] => Product
                        [foreign_id] => 2
                        [title] => Careful
                        [content] => Be sure to speak to Gerald for ordering this, long lead time!
                    )
    
                [Product] => Array
                    (
                        [id] => 2
                        [title] => Extra big comb // display field for this model
                        [display_field] => Extra big comb
                    )
    
            )
    ...
    etc.

Of interest in the above example:

+ The associated model data is present in the results
+ A virtual field "display_field" is added with the contents of the
  linked model's display field (to make admin listing logic easy - since
  the key "display_field" never changes whereas the model display field
  can)

And that's all there is to it.

Bake on!

.. __id=355: http://cakeforge.org/frs/?group_id=152&release_id=355

.. author:: AD7six
.. categories:: articles, behaviors
.. tags:: ,Behaviors

