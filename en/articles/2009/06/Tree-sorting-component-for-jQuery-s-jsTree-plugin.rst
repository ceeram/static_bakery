

Tree sorting component for jQuery's jsTree plugin
=================================================

by %s on June 12, 2009

This component sorts a one dimensional array of tree nodes into a
multidimensional array, ready to be converted into JSON for the
jQuery's jsTree plugin. http://www.jstree.com/reference/_examples/


Component Class:
````````````````

::

    <?php 
    // Author: Sasha Zivaljevic - sasha.zivaljevic at gmail
    // Date: June 12, 2009
    class jsTreeComponent extends Object {
    	var $attributes = array(); // model fields that will be populated into 'attributes', if empty uses all fields
    	var $name = 'id'; // model field that will be populated into 'data'
    	var $parent_id = 'parent_id'; // The field containing the ID of the node's parent
    	var $node_id = 'id'; // The field containing the node ID's as they are represented in the tree, i.e. the ID's that are referred to by left and right keys.
    	var $left_node = 'lft';
    	
    	function initialize(&$controller, $settings = array()){
    		$this->controller =& $controller;
    		$this->model =& $this->controller->{$this->controller->modelClass};
    		$this->_set($settings);
    	}
    	
    	// Sorts a one dimensional array of tree nodes into a multidimentional array
    	function sort($node_list){
    		$results = array();
    		$prev_node = array();
    		$x_path = '';
    		// Sort $node_list for a preorder traversal
    		$node_list = Set::sort($node_list, '{n}.'.$this->controller->modelClass.'.'.$this->left_node, 'asc');
    		// Traverse the tree and generate $results
    		foreach($node_list as $node){
    			// Current node is a root node
    			if ($node[$this->controller->modelClass][$this->parent_id] == 0){
    				$x_path = count($results);
    			// Current node is not a root node
    			} else {
    				$x_path_tokenized = String::tokenize($x_path, '.');
    				$x_path = '';
    				// Current node is a sibling of the previous node
    				if ($node[$this->controller->modelClass][$this->parent_id] == $prev_node[$this->controller->modelClass][$this->parent_id]) {
    					$limit = count($x_path_tokenized) - 1;
    					if(is_array($x_path_tokenized)) {
    						foreach($x_path_tokenized as $key => $token){
    							if($key < $limit){
    								$x_path .= $token . '.';
    							}
    						}
    						// Remove last '.' from $x_path
    						$x_path = substr($x_path, 0, -1);
    					} else {
    						$x_path = $x_path_tokenized;
    					}
    					
    				// Current node is not a sibling of the previous node
    				} else {
    					// Current node is child of the previous node
    					if ($node[$this->controller->modelClass][$this->parent_id] == $prev_node[$this->controller->modelClass][$this->node_id]){	
    						if (is_array($x_path_tokenized)) {
    							foreach($x_path_tokenized as $token){
    								$x_path .= $token . '.';
    							}
    
    							$x_path .= 'children';
    						} else {
    
    							$x_path = $x_path_tokenized . '.children';
    						}
    					// Current node is not child of the previous node
    					} else {
    						// This case is when the traversal 'jumps' from one branch to another.
    						// To properly set the x_path, must calculate the depth from the previous node to the node that is a sibling of the current node 
    						$siblings = Set::extract('/'.$this->controller->modelClass.'['.$this->parent_id.'='.$node[$this->controller->modelClass][$this->parent_id].']', $node_list);				
    						$this->model->Behaviors->attach('Tree');
    						$path_to_prev_node = $this->model->getpath($prev_node[$this->controller->modelClass]['id']);
    						$this->model->Behaviors->detach('Tree');
    						
    						$sibling_in_path_to_prev = array();
    						foreach ($path_to_prev_node as $key => $path_node) {
    							if(empty($sibling_in_path_to_prev)) {
    								$sibling_in_path_to_prev = Set::extract('/'.$this->controller->modelClass.'['.$this->node_id.'='.$path_node[$this->controller->modelClass][$this->node_id].']', $siblings);
    								$sibling_in_path_to_prev_key = $key;
    							}
    						}
    
    						$depth = (count($path_to_prev_node) - 1) - $sibling_in_path_to_prev_key;
    						$limit = count($x_path_tokenized) - (($depth * 2) + 1);
    						foreach($x_path_tokenized as $key => $token){
    							if($key < $limit){
    								$x_path .= $token . '.';
    							}
    						}
    						// Remove last '.' from $x_path
    						$x_path = substr($x_path, 0, -1);
    					}
    				}
    
    				// Calculate the number of nodes at the given $x_path, the final value of $a being the index for the node to be inserted
    				$a = 0;
    				while (Set::check($results, $x_path.'.'.$a)){
    					++$a;
    				}
    				$x_path .= '.' . $a;
    			}
    			
    			if(!empty($this->attributes)){
    				foreach ($this->attributes as $attribute) {
    					$results = Set::insert($results, $x_path.'.attributes.'.$attribute, $node[$this->controller->modelClass][$attribute]);
    				}
    			} else {
    				$results = Set::insert($results, $x_path.'.attributes', $node[$this->controller->modelClass]);
    			}
    			$results = Set::insert($results, $x_path.'.data', $node[$this->controller->modelClass][$this->name]);
    
    			$prev_node = $node;
    		}
    		return $results;
    	}
    	
    }
    ?>


.. meta::
    :title: Tree sorting component for jQuery's jsTree plugin
    :description: CakePHP Article related to tree,sort,jquery,jstree,Components
    :keywords: tree,sort,jquery,jstree,Components
    :copyright: Copyright 2009 
    :category: components

