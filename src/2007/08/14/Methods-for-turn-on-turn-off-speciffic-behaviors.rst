Methods for turn on/ turn off speciffic behaviors.
==================================================

Sometimes there is appear situation when you need not some action
which behavior do aftomatically using event handlers.
This code help you turn on/off behaviour. Place it in app_model.php
to have such methods in any model.



Model Class:
````````````

::

    <?php 
    class AppModel extends Model { 
    	var $_behaviors = array();
     
    			
    	function excludeBehavior($behaviors=null) {
    		if (!is_array($behaviors)) {
    			$behaviors = array($behaviors);
    		}
    		foreach ($behaviors as $behavior) {
    	 		if (isset($this->behaviors[$behavior])) {
    				$className = $behavior . 'Behavior';
    				if (!loadBehavior($behavior)) {
    					// Raise an error
    				} else {
    					if (ClassRegistry::isKeySet($className)) {
    						if (PHP5) {
    							$this->_behaviors[$behavior] = ClassRegistry::getObject($className);
    						} else {
    							$this->_behaviors[$behavior] =& ClassRegistry::getObject($className);
    						}
    						unset($this->behaviors[$behavior]);
    					} else {
    						// not registered raise exception
    					}
    				}
    			}
    		}
    	}
    
    	function includeBehavior($behaviors) {
    		if (empty($behaviors)) $behaviors=array_keys($this->_behaviors);
    		if (!is_array($behaviors)) {
    			$behaviors = array($behaviors);
    		}
    		foreach ($behaviors as $behavior) {
    			if (isset($this->behaviors[$behavior])) continue;
    			if (isset($this->_behaviors[$behavior])) {
    				$className = $behavior . 'Behavior';
    				if (!loadBehavior($behavior)) {
    					// Raise an error
    				} else {
    					if (ClassRegistry::isKeySet($className)) {
    						if (PHP5) {
    							$this->behaviors[$behavior] = ClassRegistry::getObject($className);
    						} else {
    							$this->behaviors[$behavior] =& ClassRegistry::getObject($className);
    						}
    						unset($this->_behaviors[$behavior]);
    					} else {
    						// not registered raise exception
    					}
    				}
    			}	
    		}
    	}
    }
    ?>


You can use this methods in your models or controllers like this

::

    
    	$this->Model->excludeBehavior(array('List', 'Translate'));
    	$this->Model->save($data);
    	$this->Model->includeBehavior(array('List', 'Translate'));



.. author:: Skiedr
.. categories:: articles, snippets
.. tags:: ,Snippets

