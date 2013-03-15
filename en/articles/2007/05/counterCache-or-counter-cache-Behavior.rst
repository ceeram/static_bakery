

counterCache or counter_cache Behavior
======================================

by %s on May 02, 2007

I wrote this because I needed something simple for my recent project
and CakePHP have not implemented the counterCache option yet. This is
my first time writing a Behavior and largely based on
http://groups.google.com/group/cake-php/msg/74508dad38d3d623 but it
doesn't work for me though. You may wish to learn more about why
counterCache is useful at http://groups.google.com/group/cake-
php/msg/971ced72abe96b03
This is just a quick solution for me, but I thought of just sharing it
so anyone could use and improve on it. There are currently no
configuration for the behavior yet though because I don't need it.
Feel free to bring up a new copy of the code or suggest something so I
probably could improve upon mine. ;)

Usage is very simple for now and really "convention over
configuration". It expects the `comments` table to have a `post_id`,
and the `posts` table to have a `comments_count` column.


Model Class:
````````````

::

    <?php 
    class Comment extends AppModel {
    	var $name = 'Comment';
    
    	var $actsAs = 'CounterCache';
    	var $belongsTo = array('Post');
    }
    ?>


::

    
    <?php
    /**
     * CounterCacheBehavior
     * 
     * @author Derick Ng aka dericknwq
     * @version 2007-05-01
     */
    class CounterCacheBehavior extends ModelBehavior {
    	
    	var $foreignTableIDs = array();
    
    	function setup(&$model, $config = array()) {
    	}
    
    	function afterSave(&$model, $created) {
    		if ($created) {
    			foreach ($model->belongsTo as $assocKey => $assocData) {
    				$assocModel =& $model->{$assocData['className']};
    				$field = Inflector::tableize($model->name) . '_count';
    				
    				if (!empty($model->data[$model->name][$assocData['foreignKey']]) && $assocModel->hasField($field)) {
    					$this->foreignTableIDs[$assocData['className']] = $model->data[$model->name][$assocData['foreignKey']];
    					$count = $model->findCount(array($model->name . '.' . $assocData['foreignKey'] => $this->foreignTableIDs[$assocData['className']]));
    					$assocModel->id = $this->foreignTableIDs[$assocData['className']];
    					$assocModel->save(array($field => $count), false, array($field));
    				}
    			}
    		}
    	}
    
    	function beforeDelete(&$model) {
    		foreach ($model->belongsTo as $assocKey => $assocData) {
    			$this->foreignTableIDs[$assocData['className']] = $model->field($assocData['foreignKey']);
    		}
    		return true;
    	}
    
    	function afterDelete(&$model) {
    		foreach ($model->belongsTo as $assocKey => $assocData) {
    			$assocModel =& $model->{$assocData['className']};
    			$field = Inflector::tableize($model->name) . '_count';
    			
    			if ($assocModel->hasField($field)) {
    				$count = $model->findCount(array($model->name . '.' . $assocData['foreignKey'] => $this->foreignTableIDs[$assocData['className']]));
    				$assocModel->id = $this->foreignTableIDs[$assocData['className']];
    				$assocModel->save(array($field => $count), false, array($field));
    			}
    		}
    	}
    
    }
    ?>


.. meta::
    :title: counterCache or counter_cache Behavior
    :description: CakePHP Article related to countercache,behavior,counter,Behaviors
    :keywords: countercache,behavior,counter,Behaviors
    :copyright: Copyright 2007 
    :category: behaviors

