Modified Preorder Tree Traversal Component
==========================================

A component for retrieving, printing, inserting nodes into, and
deleting nodes from trees stored in mptt form.
For a good introduction to storing trees in mptt form, read this:
`http://www.sitepoint.com/article/hierarchical-data-database`_
Here is the code of my component:


Component Class:
````````````````

::

    <?php 
    class MpttComponent extends Object
    {
    
    	var $model_name;
    	var $rgt_col;
    	var $lft_col;
    
    	function getTree(&$model, $root_node_id, $lft_col = 'lft', $rgt_col = 'rgt')
    	{
    		$this->model_name = $model->name;
    		$this->rgt_col = $rgt_col;
    		$this->lft_col = $lft_col;
    	
    		$root = new Object();
    		$root->data = $model->read(null, $root_node_id);
    		$root->parent = null;
    		$lft = $root->data[$model->name][$lft_col];
    		$rgt = $root->data[$model->name][$rgt_col];
    		$children = $model->findAll($lft_col.'>'.$lft.' AND '.$rgt_col.'<'.$rgt, null, $lft_col.' ASC');
    
    		if (!empty($children)) {
    
    			$current_parent = $root;
    			$current_parent->children = array();
    
    			foreach ($children as $current_child_data) {
    				$current_child = new Object();
    				$current_child->data = $current_child_data;
    				$current_child->children = array();
    				while(!$this->_isChildOf($current_child, $current_parent)) {
    					$current_parent = $current_parent->parent;
    				}
    				$current_child->parent = $current_parent;
    				$current_parent->children []= $current_child;
    				$current_parent = $current_child;
    			}
    
    		}
    		return $root;
    	}
    
    	function _isChildOf($child, $parent)
    	{
    		$child_lft = $child->data[$this->model_name][$this->lft_col];
    		$child_rgt = $child->data[$this->model_name][$this->rgt_col];
    		$parent_lft = $parent->data[$this->model_name][$this->lft_col];
    		$parent_rgt = $parent->data[$this->model_name][$this->rgt_col];
    		return ($child_lft > $parent_lft && $child_rgt < $parent_rgt);
    	}
    
    	function printTree(&$tree, $model_name, $disp_col, $ind = '')
    	{
    		print($ind.$tree->data[$model_name][$disp_col].'<br/>');
    		foreach ($tree->children as $child) {
    			$this->printTree($child, $model_name, $disp_col, '....'.$ind);
    		}
    	}
    
    	function insertNode($lft, &$model, &$data, $lft_col= 'lft', $rgt_col = 'rgt')
    	{
    		$sql_update_rgt = 'UPDATE '.$model->useTable." SET $rgt_col=$rgt_col+2 WHERE $rgt_col>=$lft";
    		$model->query($sql_update_rgt);
    		$sql_update_lft = 'UPDATE '.$model->useTable." SET $lft_col=$lft_col+2 WHERE $lft_col>=$lft";
    		$model->query($sql_update_lft);
    		// Insert data.
    		$data[$model->name][$lft_col] = $lft;
    		$data[$model->name][$rgt_col] = $lft+1;
    		$model->id = null;
    		if ($model->save($data)) {
    			// Return true on success
    			return TRUE;
    		}
    		// Return false on failure
    		return FALSE;
    	}
    
    	function insertNodeUnder($parent_node_id, &$model, &$data, $lft_col = 'lft', $rgt_col = 'rgt')
    	{
    		$parent = $model->read(null, $parent_node_id);
    		if (!empty($parent)) {
    			$parent_rgt = $parent[$model->name][$rgt_col];
    			return $this->insertNode($parent_rgt, $model, $data, $lft_col, $rgt_col);
    		}
    		return FALSE;
    	}
    
    	function insertNodeLeftOf($sibling_id, &$model, &$data, $lft_col = 'lft', $rgt_col = 'rgt')
    	{
    		$sib = $model->read(null, $sibling_id);
    		if (!empty($sib)) {
    			$sib_lft = $sib[$model->name][$lft_col];
    			return $this->insertNode($sib_lft, $model, $data, $lft_col, $rgt_col);
    		}
    		return FALSE;
    	}
    
    	function insertNodeRightOf($sibling_id, &$model, &$data, $lft_col = 'lft', $rgt_col = 'rgt')
    	{
    		$sib = $model->read(null, $sibling_id);
    		if (!empty($sib)) {
    			$sib_rgt = $sib[$model->name][$rgt_col];
    			return $this->insertNode($sib_rgt+1, $model, $data, $lft_col, $rgt_col);
    		}
    		return FALSE;
    	}
    
    	function deleteNode($node_id, &$model, $lft_col = 'lft', $rgt_col = 'rgt')
    	{
    		// Retrieve node data.
    		$node = $model->read(null, $node_id);
    		if (!empty($node)) {
    			$lft = $node[$model->name][$lft_col];
    			$rgt = $node[$model->name][$rgt_col];
    			// Delete node and all children.	
    			$del_sql = 'DELETE FROM '.$model->useTable." WHERE $lft_col >= $lft AND $rgt_col <= $rgt";
    			$model->query($del_sql);
    			// Update table.
    			$diff = $rgt-$lft+1;
    			$update_lft_sql = 'UPDATE '.$model->useTable." SET $lft_col = $lft_col-$diff WHERE $lft_col > $rgt";
    			$model->query($update_lft_sql);
    			$update_rgt_sql = 'UPDATE '.$model->useTable." SET $rgt_col = $rgt_col-$diff WHERE $rgt_col > $rgt";
    			$model->query($update_rgt_sql);
    			return TRUE;
    		}
    		return FALSE;
    	}
    }
    ?>

The code should be fairly straightforward. The getTree function
returns an object. The returned object represents a tree node, and has
two member variables: data, and children. The data member variable
holds the information for the given node from the database. The
children member variable is an array of node objects that are children
of the current node.

.. _http://www.sitepoint.com/article/hierarchical-data-database: http://www.sitepoint.com/article/hierarchical-data-database

.. author:: mpatek
.. categories:: articles, components
.. tags:: tree,mptt,traversal,modified,preorder,Components

