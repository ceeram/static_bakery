Generic Taggable Behavior
=========================

by malte on April 23, 2009

A solution for application wide tagging support. After a short setup
you will be able to easily tag all models necessary for your
application.


Preface
~~~~~~~
All instructions in this tutorial assume that you implement this code
in a default CakePHP 1.2 application, otherwise i guess you know
CakePHP quite a bit to modify the code to your needs. Also there will
be a revised version which will be configurable without modification
of the code at hand. It will hopefully be completely downwards
compatible.

I presume that you are firm with the
`http://book.cakephp.org/complete/22/CakePHP-Conventions`_ and
selected your database table and model names accordingly.


What we will do
~~~~~~~~~~~~~~~

#. Create database tables
#. Create Tag and ObjectsTag models
#. Create TaggableBehavior
#. Create Tags element
#. Implement tagging support by example




1. Create database tables
~~~~~~~~~~~~~~~~~~~~~~~~~

Table tags
``````````
This table will contain all tags. just create it in your default
database.

::

    CREATE TABLE `tags` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `slug` varchar(255) NOT NULL,
      `created` datetime NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    


Table objects_tags
``````````````````
Proceed as before.

::

    CREATE TABLE `objects_tags` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `tag_id` int(10) unsigned NOT NULL,
      `model` varchar(255) NOT NULL,
      `foreign_key` int(10) unsigned NOT NULL,
      `created` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



2. Create models
~~~~~~~~~~~~~~~~

Model Class:
````````````

::

    <?php 
    class Tag extends AppModel {
    	public $name = 'Tag';
    	public $validate = array(
        	'slug' => array(
        		'required' => array(
        			'rule' => 'notEmpty', 
       			),
       			'pattern' => array(
       				'rule' => array('custom', '/[a-zA-Z0-9\_\-\.]{1,100}$/i')
       			),
       			'unique' => array(
       				'rule' => 'isUnique'
       			)
        	)
        );
    	
        public $hasMany = array(
        	'ObjectsTag' => array()
        );
        
    	public function beforeValidate() {
    		$this->data[$this->name]['slug'] = Inflector::slug(strtolower($this->data[$this->name]['name']), '-');
    		return true;
    	}
    	
    	public function getObjects($tagId) {
    		$list = $this->ObjectsTag->findAllByTagId($tagId);
    		$objects = array();
    		foreach ($list as $assoc) {
    			$model = ClassRegistry::init($assoc['ObjectsTag']['model']);
    			$object = $model->findById($assoc['ObjectsTag']['foreign_key']);
    			$objects[ $assoc['ObjectsTag']['model'] ][] = $object[ $assoc['ObjectsTag']['model'] ];
    		}
    		return $objects;
    	}
    }
    ?>



Model Class:
````````````

::

    <?php class ObjectsTag extends AppModel {
    	public $name = 'ObjectsTag';
    
    	public $belongsTo = array(
    		'Tag' => array('className' => 'Tag',
    			'foreignKey' => 'tag_id',
    			'counterCache' => true
    		)
    	);
    }
    ?>



Behavior Class:
```````````````

::

    <?php 
    class TaggableBehavior extends ModelBehavior {
    	public function setup(&$model, $config = null) {
    		$this->settings = array(
    			'tagModel' => 'Tag',
    			'associationModel' => 'ObjectsTag',
    			'associationTable' => 'objects_tags',
    			'associationForeignKey' => 'tag_id',
    			'foreignKey' => 'foreign_key',
    			'formField' => 'tag_list',
    			'conditions' => array('ObjectsTag.model' => $model->name)
    		);
    		if (is_array($config)) {
    			$this->settings = array_merge($this->settings, $config);
    		}
    	}
    	
    	public function afterSave(&$model, $created) {
    		$this->bindTag($model);
    		$tag_id = null;
    		$object_id = $model->id;
    		
    		if (!empty($model->data[$model->name][$this->settings['formField']])) {
    			$tags = split(',', $model->data[$model->name][$this->settings['formField']]);
    			foreach ($tags as $id => $tag) {
    				$tag = trim($tag);
    				if (!empty($tag)) {
    					$slug = Inflector::slug(strtolower($tag), '-');
    					$tags[$slug] = $tag;
    				}
    				unset($tags[$id]);
    			}
    			// check for deleted tags
    			$currentTags = $model->{$this->settings['tagModel']}->{$this->settings['associationModel']}->find('all', array(
    				'conditions' => array_merge(array(
    					$this->settings['associationModel'].'.'.$this->settings['foreignKey'] => $model->id,
    				), $this->settings['conditions'])
    			));
    			
    			foreach ($currentTags as $assoc) {
    				if (!array_key_exists($assoc[ $this->settings['tagModel'] ]['slug'], $tags)) {
    					// delete old association (tag not existent in new tag list)
    					$model->{$this->settings['tagModel']}->{$this->settings['associationModel']}->del($assoc[ $this->settings['associationModel'] ]['id']);
    				} elseif ($assoc[ $this->settings['associationModel'] ]['foreign_key'] == $object_id
    					&& $assoc[ $this->settings['associationModel'] ]['model'] == $model->name
    					&& array_key_exists($assoc[ $this->settings['tagModel'] ]['slug'], $tags)
    				) {
    					// tag association already exists
    					unset($tags[ $assoc[ $this->settings['tagModel'] ]['slug'] ]);
    				}
    			}
    			
    			foreach ($tags as $slug => $tag) {
    				/* see if the tag already exists */
    				$result = $model->{$this->settings['tagModel']}->findBySlug($slug);
    				
    				if (!$result) {
    					/* create the tag ourselves */
    					$model->{$this->settings['tagModel']}->create();
    					$model->{$this->settings['tagModel']}->save(array(
    						$this->settings['tagModel'] => array(
    							'name' => $tag,
    							'slug' => $slug
    						)
    					));
    					$tag_id = $model->{$this->settings['tagModel']}->getLastInsertId();
    				} else {
    					$tag_id = $result[$this->settings['tagModel']]['id'];
    				}
    				
    				if (!is_null($tag_id)) {
    					/* add the tag associations */
    					$model->{$this->settings['associationModel']}->create();
    					$model->{$this->settings['associationModel']}->save(array(
    						$this->settings['associationModel'] => array(
    							$this->settings['foreignKey'] => $object_id,
    							$this->settings['associationForeignKey'] => $tag_id,
    							'model' => $model->name
    						)
    					));
    				} else {
    					// TODO: error detection
    					echo 'Failed to get tag';
    				}
    			}
    		}
    		$this->unbindTag($model);
    	}
    	
    	public function bindTag(&$model) {
    		/* set up model relationship */
    		$model->bindModel(
    			array(
    				'hasAndBelongsToMany' => array(
    					$this->settings['tagModel'] => array(
    						'className' => $this->settings['tagModel'],
    						'joinTable' => $this->settings['associationTable'],
    						'foreignKey' => $this->settings['foreignKey'],
    						'associationForeignKey' => $this->settings['associationForeignKey'],
    						'conditions' => $this->settings['conditions'],
    						'order' => '',
    						'limit' => '',
    						'unique' => 'true',
    						'finderQuery' => '',
    						'deleteQuery' => ''
    					)
    				)
    			)
    		);
    	}
    	
    	public function unbindTag(&$model) {
    		$model->unbindModel(array('hasAndBelongsToMany' => array($this->settings['tagModel'])));
    	}
    	
    	public function beforeFind(&$model, $queryData) {
    		$this->bindTag($model);
    		return $queryData;
    	}
    	
    	/* for combining tags for form usage */
    	public function afterFind(&$model, $results, $primary) {
    		$this->unbindTag($model);
    		for ($i = 0; $i < sizeof($results); $i++) {
    			foreach ($results[$i] as $key => $value) {
    				$tags = '';
    				if ($key == $this->settings['tagModel']) {
    					foreach ($value as $tag) {
    						$tags .= $tag['name'].',';
    					}
    					
    					$tags = substr($tags, 0, strlen($tags) - 1);
    					$results[$i][$model->name][$this->settings['formField']] = $tags;
    				}
    				
    			}
    		}
    		return $results;
    	}
    }
    ?>

[element] if (!isset($tags)) {
$modelVar =
Inflector::underscore(Inflector::singularize($this->name));
if (!isset(${$modelVar})) {
if (isset($workstation_model)) {
$modelVar = 'workstation_model';
}
}
$tags = ${$modelVar}['Tag'];
}
if (isset($div) && $div !== false) {
?> }
$tagList = array();
foreach ($tags as $tag) {
$tagList[] = $html->link($tag['name'], array('controller' => 'Tags',
'action' => 'view', $tag['slug']));
}
echo implode(', ', $tagList);

if (isset($div) && $div !== false) {
?> }
[/element]

.. _http://book.cakephp.org/complete/22/CakePHP-Conventions: http://book.cakephp.org/complete/22/CakePHP-Conventions

.. author:: malte
.. categories:: articles, tutorials
.. tags:: ,Tutorials

