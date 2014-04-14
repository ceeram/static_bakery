SortableBehavior: sort your models arbitrarily
==============================================

by dardosordi on July 29, 2008

This behavior allow us to sort records arbitrarily.

Some times we need to set the exact order for the records of our
model. This behavior allows us to do that.


Let's see an example
~~~~~~~~~~~~~~~~~~~~
I think is better to just present an example usage than going through
it's api. This is just a simple use case for this behavior: sort
categories.


This will be our model
++++++++++++++++++++++
We just declared the model, relations, validations and behaviors.


Model Class:
````````````

::

    <?php 
    class Category extends AppModel {
    	var $name = 'Category';
    	var $actsAs = array(
    		'Containable', // I love this thing
    		'Sortable' => array(
    			'field' => 'order'
    		),
    	);
    
    	var $validate = array(
    		'title' => array(
    			'rule' => array('minLength', 1),
    			'required' => true,
    			'allowEmpty' => false,
    			'message' => 'This field should not be empty.'
    		)
    	);
    	
    	var $hasMany = array('Publicity','Product');
    
    	
    }
    ?>


And this will be our schema:

::

    
    CREATE TABLE `categories` (
      `id` int(10) NOT NULL auto_increment,
      `title` varchar(255) NOT NULL,
      `order` int(10) NOT NULL,
      PRIMARY KEY  (`id`)
    );



This can be our controller...
+++++++++++++++++++++++++++++
It extends CrudController which provides a default implementation for
admin_ .


Controller Class:
`````````````````

::

    <?php 
    class CategoriesController extends CrudController {
    
    	var $name = 'Properties';
    	var $helpers = array('Html', 'Form');
    
    	function index() {
    		$this->Category->recursive = -1;
    
    		// the behavior will sort them automatically by the 'order' field
    		// if we don't set any sort conditions
    		$this->set('categories', $this->paginate());
    	}
    
    	function view($id = null) {
    		if (!$id) {
    			$this->Session->setFlash(__('Invalid Category.', true));
    			$this->redirect(array('action'=>'index'));
    		}
    		$this->set('category', $this->Category->read(null, $id));
    	}
    
    	function _move($id, $method) {
    		if (!$id) {
    			$this->Session->setFlash(__('Invalid Category', true));
    			$this->redirect(array('action' => 'index'));
    		}
    
    		if ($this->Category->$method($id)) {
    			$this->Session->setFlash(__('Category position updated', true));
    		} else {
    			$this->Session->setFlash(__('Category position can\'t be updated', true));
    		}
    
    		$this->redirect($this->referer());
    	}
    
    	function admin_moveUp($id = null) {
    		$this->_move($id, 'moveUp');
    	}
    
    	function admin_moveDown($id = null) {
    		$this->_move($id, 'moveDown');
    	}
    
    	function admin_moveTop($id = null) {
    		$this->_move($id, 'moveTop');
    	}
    
    	function admin_moveBottom($id = null) {
    		$this->_move($id, 'moveBottom');
    	}
    }
    ?>



At the end, the behavior code
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    <?php
    /**
     * Copyright (c) 2008, Dardo Sordi
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @copyright		Copyright (c) 2008, Dardo Sordi
     * @license		http://www.opensource.org/licenses/mit-license.php The MIT License
     * @author	Dardo Sordi
     */
    
    class SortableBehavior extends ModelBehavior {
    
    	var $__config = array();
    
    	function setup(&$model, $config = array()) {
    		$default = array('field' => 'order', 'enabled' => true, 'group' => false);
    		$this->__config[$model->name] = array_merge($default, (array)$config);
    	}
    
    	function _config(&$model, $config = array()) {
    		if (is_array($config)) {
    			if (!empty($config)) {
    				$this->__config[$model->name] = array_merge($this->__config[$model->name], $config);
    			}
    		} else {
    			return $this->__config[$model->name][$config];
    		}
    
    		return $this->__config[$model->name];
    	}
    
    	function _fullField(&$model) {
    		return $model->name . '.' . $this->_config($model, 'field');
    	}
    
    	function beforeFind(&$model, $conditions = array()) {
    		extract($this->_config($model));
    		if (!$enabled) {
    			return $conditions;
    		}
    		if (is_string($conditions['fields']) && strpos($conditions['fields'], 'COUNT') === 0) {
    			return $conditions;
    		}
    		$order = $conditions['order'];
    		if (is_array($conditions['order'])) {
    			$order = current($conditions['order']);
    		}
    		if (empty($order)) {
    			$conditions['order'] = array(array($this->_fullField($model) => 'ASC'));
    
    			if ($group !== false) {
    				$conditions['order'] = array_merge(array($group => 'ASC'), $conditions['order']);
    			}
    		}
    		return $conditions;
    	}
    
    	function beforeSave(&$model) {
    		extract($this->_config($model));
    		if ($enabled) {
    			$fixPosition = false;
    			$isInsert = !$model->id;
    			if (isset($model->data[$model->name][$field]) && !empty($model->data[$model->name][$field])) {
    				$newPosition = $model->data[$model->name][$field];
    			} else {
    				$newPosition = null;
    			}
    			$groupId = $this->_groupId($model);
    			$model->data[$model->name][$field] = $this->lastPosition($model, $groupId) + 1;
    			$model->__fixPosition = $newPosition;
    		}
    		return true;
    	}
    
    	function afterSave(&$model, $created) {
    		extract($this->_config($model));
    		if (!$enabled) {
    			return true;
    		}
    		$position = $model->data[$model->name][$field];
    		if ($model->__fixPosition) {
    			$position = $model->__fixPosition;
    			$model->__fixPosition = null;
    			$this->setPosition($model, $model->id, $position);
    		}
    	}
    
    	function beforeDelete(&$model) {
    		extract($this->_config($model));
    		if ($enabled) {
    			$model->__fixPosition = $this->position($model);
    		}
    		return true;
    	}
    
    	function afterDelete(&$model) {
    		extract($this->_config($model));
    		if (!$enabled) {
    			return true;
    		}
    		$fullField = $this->_fullField($model);
    		$position = $model->__fixPosition;
    		$model->__fixPosition = null;
    		$model->updateAll(array($fullField => "$fullField - 1"), array("$fullField >=" => $position));
    	}
    
    	function moveTop(&$model, $id = null) {
    		$this->disableSortable($model);
    		if ($id) {
    			$model->id = $id;
    		}
    		extract($this->_config($model));
    		$position = $this->position($model);
    		$groupId = $this->_groupId($model);
    		$fullField = $this->_fullField($model);
    
    		if ($position > 1) {
    			$newPosition = 1;
    			$conditions = $this->_conditions($model, $groupId, array("$fullField <=" => $position, "$fullField >=" => $newPosition));
    			$model->updateAll(array($fullField => "$fullField + 1"), $conditions);
    			$model->saveField($field, $newPosition);
    		}
    		$this->enableSortable($model);
    		return true;
    	}
    
    	function moveUp(&$model, $id = null, $step = 1) {
    		$this->disableSortable($model);
    		if ($id) {
    			$model->id = $id;
    		}
    		extract($this->_config($model));
    		$position = $this->position($model);
    		$groupId = $this->_groupId($model);
    		$fullField = $this->_fullField($model);
    
    		if ($position > 1) {
    			$newPosition = $position - $step;
    			if ($newPosition < 1) {
    				$newPosition = 1;
    			}
    			$conditions = $this->_conditions($model, $groupId, array("$fullField <=" => $position, "$fullField >=" => $newPosition));
    			$model->updateAll(array($fullField => "$fullField + 1"), $conditions);
    			$model->saveField($field, $newPosition);
    		}
    		$this->enableSortable($model);
    		return true;
    	}
    
    	function moveDown(&$model, $id = null, $step = 1) {
    		$this->disableSortable($model);
    		if ($id) {
    			$model->id = $id;
    		}
    		extract($this->_config($model));
    		$position = $this->position($model);
    		$groupId = $this->_groupId($model);
    		$id = $model->id;
    		$model->id = null;
    		$last = $this->lastPosition($model, $groupId);
    		$fullField = $this->_fullField($model);
    
    		if ($position < $last) {
    			$newPosition = $position + $step;
    			if ($newPosition > $last) {
    				$newPosition = $last;
    			}
    			$conditions = $this->_conditions($model, $groupId, array("$fullField >=" => $position, "$fullField <=" => $newPosition));
    			$model->updateAll(array($fullField => "$fullField - 1"), $conditions);
    			$model->id = $id;
    			$model->saveField($field, $newPosition);
    		}
    		$this->enableSortable($model);
    		return true;
    	}
    
    	function moveBottom(&$model, $id = null) {
    		$this->disableSortable($model);
    		if ($id) {
    			$model->id = $id;
    		}
    		extract($this->_config($model));
    
    		$position = $this->position($model);
    		$groupId = $this->_groupId($model);
    		$id = $model->id;
    		$model->id = null;
    		$last = $this->lastPosition($model, $groupId);
    		$fullField = $this->_fullField($model);
    
    		if ($position < $last) {
    			$newPosition = $last;
    			$conditions = $this->_conditions($model, $groupId, array("$fullField >=" => $position, "$fullField <=" => $newPosition));
    			$model->updateAll(array($fullField => "$fullField - 1"), $conditions);
    			$model->id = $id;
    			$model->saveField($field, $newPosition);
    		}
    		$this->enableSortable($model);
    		return true;
    	}
    
    	function setPosition(&$model, $id = null, $destination) {
    		$this->disableSortable($model);
    		if ($id) {
    			$model->id = $id;
    		}
    		extract($this->_config($model));
    		$position = $this->position($model);
    		$id = $model->id;
    		$model->id = null;
    		$delta = $position - $destination;
    
    		if ($position > $destination) {
    			$this->moveUp($model, $id, $delta);
    		} elseif ($position < $destination) {
    			$this->moveDown($model, $id, -$delta);
    		}
    		$this->enableSortable($model);
    		return true;
    	}
    
    	function disableSortable(&$model) {
    		$this->_config($model, array('enabled' => false));
    	}
    
    	function enableSortable(&$model) {
    		$this->_config($model, array('enabled' => true));
    	}
    
    	function position(&$model, $id = null) {
    		if ($id) {
    			$model->id = $id;
    		}
    		return $model->field($this->_config($model, 'field'));
    	}
    
    	function _groupId(&$model, $id = null) {
    		$group = $this->_config($model, 'group');
    
    		if ($group === false) {
    			return null;
    		}
    		if ($id) {
    			$model->id = $id;
    		}
    		return $model->field($group);
    	}
    
    	function lastPosition(&$model, $groupId = null) {
    		$id = $model->id;
    		$model->id = null;
    		$field = $this->_config($model, 'field');
    		$fields = array($field);
    		$order = array($field => 'DESC');
    		$conditions = $this->_conditions($model, $groupId);
    		$last = $model->find('first',  compact('fields', 'order', 'conditions'));
    		$model->id = $id;
    
    		if (!empty($last)) {
    			return current(current($last));
    		}
    
    		return false;
    	}
    
    	function _conditions(&$model, $groupId = null, $conditions = array()) {
    		$group = $this->_config($model, 'group');
    
    		if (($group !== false) && !is_null($groupId)) {
    			$conditions = array_merge($conditions, array($group => $groupId));
    		}
    
    		return $conditions;
    	}
    
    	function findByPosition(&$model, $position, $groupId = null) {
    		$field = $this->_fullField($model);
    		return $model->find($this->_conditions($model, $groupId, array($field => $position)));
    	}
    
    }
    
    ?>



Latest version
``````````````
Here is the latest version of the behavior code and the test case:
`http://repo.or.cz/w/my_behaviors.git`_


.. _http://repo.or.cz/w/my_behaviors.git: http://repo.or.cz/w/my_behaviors.git
.. meta::
    :title: SortableBehavior: sort your models arbitrarily
    :description: CakePHP Article related to ,Behaviors
    :keywords: ,Behaviors
    :copyright: Copyright 2008 dardosordi
    :category: behaviors

