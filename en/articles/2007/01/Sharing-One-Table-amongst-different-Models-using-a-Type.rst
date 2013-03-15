Sharing One Table amongst different Models using a Type
=======================================================

by %s on January 28, 2007

Sometimes it is necessary to link one data set(the child) to different
data sets(the parent) based on a 'type' field. Often this is done
through data manipulation and a combination of child Model and
Controller code. This behavior helps to simplify table design, and
moves all of the manipulation code to a single location: a parent
Model.
(Please note: This is as much a study in behaviors use as it is an
efficient means of accomplishing the objective. There are many ways in
CakePHP to accomplish the same thing, but this is the way I've decided
to go with this project.)
Consider this:

I have many types of 'collections' in my app. For example, 'Books' are
collections of 'Stories' and 'Albums' are collections of 'Photos'.

All of my collections have some things in common:

#. They all have associations and required methods that are unique to
   them, justifying each having their own Model.
#. They all have the same table structure.
#. Most importantly, they all have Posts that belongTo them.

I've seen many methods to handle the Posts table and how it associates
back to collections. Usually there is a condition test that checks on
the value of a 'type' field and manipulates the data, setting the
value on saves or passing an expected value condition on finds. This
is often done in a controller, and often done in more than one place
when recursion is involved.

Because of the similarities of my collections, I've decided to use the
same table for all of them, and put the 'type' field within that
table. This allows me to have many Models(point 1) share the same
table(point 2), and the behavior allows the simplification of
conditions in all CRUD actions by moving it out of the Post Model and
all controllers and into the different collection models(point 3).

To help clarify, here is the SQL to define the tables:

::

    
    CREATE TABLE `collections` (
      `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
      `created` DATETIME DEFAULT NULL,
      `modified` DATETIME DEFAULT NULL,
      `name` VARCHAR(100) DEFAULT NULL,
      `type` VARCHAR(20) DEFAULT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    
    CREATE TABLE `posts` (
      `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
      `created` DATETIME DEFAULT NULL,
      `modified` DATETIME DEFAULT NULL,
      `collection_id` INT(10) unsigned NOT NULL DEFAULT '1',
      `label` VARCHAR(100) DEFAULT NULL,
      `body` VARCHAR(100) DEFAULT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

I don't need a Collection Model, since I will never need to refer to
it that way. Instead, I create each of the models that use the table
seperately.


Model Class:
````````````

::

    <?php 
    class Book extends AppModel {
    	var $name = 'Book';
    
    	var $useTable = 'collections';
    	var $actsAs = array('Collection');
    
    	var $hasMany = array('Post');
    }
    ?>

and one Model for Posts, with the necessary associations.

Model Class:
````````````

::

    <?php 
    class Post extends AppModel {
    	var $name = 'Post';
    
    	var $belongsTo = array('Book' => array(
    		'foreignKey' => 'collection_id'));
    }
    ?>

The magic is done here with the addition of $actsAs in the Book Model.

::

    
    <?php
    class CollectionBehavior extends ModelBehavior {
    
    	function setup(&$model, $config = array()) {
    		if (empty($config)) {
    			// if no value set, then use the default 'type'=>'ModelName'
    			$this->settings[$model->name] = array('type' => $model->name);
    		} else {
    			// assign the settings for this Model
    			$this->settings[$model->name] = $config;
    		}
    	}
    
    	// we want to only get results with a fixed condition
    	function beforeFind(&$model, &$query) {
    		$query['conditions'] = am($query['conditions'], $this->settings[$model->name]);
    	}
    
    	// we force the values we want
    	function beforeSave(&$model) {
    		foreach ($this->settings[$model->name] as $field=>$value) {
    			$model->data[$model->name][$field] = $value;
    		}
    	}
    }
    ?>

[Saving to keep from timing out my session]

.. meta::
    :title: Sharing One Table amongst different Models using a Type
    :description: CakePHP Article related to ,Behaviors
    :keywords: ,Behaviors
    :copyright: Copyright 2007 
    :category: behaviors

