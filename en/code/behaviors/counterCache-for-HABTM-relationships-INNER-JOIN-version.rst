

counterCache for HABTM relationships [INNER JOIN version]
=========================================================

by %s on August 24, 2009

Recently I had to work on a quite large db (>300k rows with >800k
related elements). I needed to implement counterCache and found this
article in the bakery http://bakery.cakephp.org/articles/view/counter-
cache-behavior-for-habtm-relations (at the moment counterCache is not
implemented in CakePHP core). I seemed to me like good code but got
many problems caused by db size.
For testing purposes I tried to run the code in PHP console but quite
instantly it turned out me that it was impossible to use such a
solution: for each item CakePHP worked at least 2-3 minutes (you read
it right, minutes, not seconds).

I'm quite a CakePHP newbie but I think I narrowed down the problem to
the type of JOIN CakePHP issued against the db, so I made a brand-new
behaviour based on INNER JOINs, which seemed to be extremely
lightweight and pretty solid.

I'm publishing it to get comments about the code and the choices I
made, as I'm trying to figure out more and more about CakePHP.
Comments & critics are really welcome!


Helper Class:
`````````````

::

    <?php 
    //**
     * CounterCacheHabtmBehavior - add counter cache support for HABTM relations
     *
     * @author Nicola Beghin (http://www.italo-design.com)
     * @version 2009-08-23
     */
    
    class CounterCacheHabtmBehavior extends ModelBehavior {
     
    
        function afterSave(&$model, $created) {
    		$this->updateHABTMcounters($model);
        }
    
    	
    	// updates counter of HABTM related elements
    	private function updateHABTMcounters(&$model) {
    		if (isset($model->hasAndBelongsToMany)) {			
    			foreach($model->hasAndBelongsToMany as $associatedModel=>$params) {				
    				$field=Inflector::underscore($associatedModel).'_count';				
    				if (isset($params['counterCache']) && $params['counterCache'] && $model->hasField($field)) {
    					$this->bindByInnerJoin($model, $associatedModel);			
    					$conditions=array(Inflector::camelize($model->name).'.id' => $model->id);
    					if (isset($params['counterScope'])) $conditions=array_merge($conditions, (array)$params['counterScope']);
    					$count=$model->find('count', array('recursive' => 1, 'conditions' => $conditions));
    					$model->saveField($field, $count, array('callbacks' => false));
    				} // end if counterCache enabled && hasField								
    			} // end foreach hasAndBelongsToMany
    		} // end if count hasAndBelongsToMany
    	} // end function
    
    
    	// setup an INNER JOIN between two HABTM models
    	private function bindByInnerJoin(&$model, $associatedModel) {
    		if (isset($model->hasAndBelongsToMany[$associatedModel])) {
    			$params=$model->hasAndBelongsToMany[$associatedModel];
    			$model->unbindModel(array('hasAndBelongsToMany' => array_keys($model->hasAndBelongsToMany))); // unbind any hasAndBelongToMay relationship
    			$conditions=$params['with'].'.'.$params['foreignKey'].'='.Inflector::camelize($model->name).'.id';
    			$model->bindModel(
    				array('belongsTo' => 
    						array($params['with'] => 
    							array('className' => $params['with'], 'type' => 'INNER', 'foreignKey' => false, 'conditions' => $conditions)
    						)
    				)
    			);
    		} // end if count
    	} // end function
    	
    
    	// updates counter of elements related to deleted item
        function afterDelete(&$model) {
    		if (isset($model->hasAndBelongsToMany)) {
    			foreach($model->hasAndBelongsToMany as $associatedModel=>$params) {
    				if (isset($model->store[$associatedModel])) {
    					foreach($model->store[$associatedModel] as $associated) {
    						$mode->associateModel->id=$associated[$associatedModel]['id'];
    						$data=array('id' => $associated[$associatedModel]['id']);
    						$model->$associatedModel->save($data, false); // fake request to force related fields counting
    					} // end foreach associated
    				} // end if isset store
    			} // end foreach HABTM
        		unset($model->store[$associatedModel]); // unset store to free up memory
    		} // end if isset HABTM
    	}
    
    	
    	// store IDs of HABTM related elements for post-deletion counter update
    	function beforeDelete(&$model) {
    		if (isset($model->hasAndBelongsToMany)) {
    			$model->store=array(); // used to store 
    			$field=Inflector::underscore($model->name).'_count';
    			foreach($model->hasAndBelongsToMany as $associatedModel=>$params) {	
    				$params_associated=$model->$associatedModel->hasAndBelongsToMany[$model->name];
    				if (isset($params_associated['counterCache']) && $params_associated['counterCache'] && $model->$associatedModel->hasField($field)) {
    					$this->bindByInnerJoin($model->$associatedModel, $model->name);
    					$conditions=array($params['foreignKey'] => $model->id);
    					$fields=array(Inflector::underscore($associatedModel).'.id');
    					$results=$model->$associatedModel->find('all', array('fields' => $fields, 'conditions' => $conditions));
    					if (count($results)) $model->store[$associatedModel]=$results;
    				} // end if counterCache
    			} // end foreach hasAndBelongsToMany
    		} // end if isset hasAndBelongsToMany
    
    		return true;
    	}
    
    }
    ?>


.. meta::
    :title: counterCache for HABTM relationships [INNER JOIN version]
    :description: CakePHP Article related to countercache,HABTM,behavior,join,large db,inner,Behaviors
    :keywords: countercache,HABTM,behavior,join,large db,inner,Behaviors
    :copyright: Copyright 2009 
    :category: behaviors

