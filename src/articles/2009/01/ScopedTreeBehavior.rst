ScopedTreeBehavior
==================

by masom on January 23, 2009

ScopedTreeBehavior let's you manage several different tree within the
same table, each tree being separated by a 'scope' such as another
model id or a simple tree id. It was built with UUID in mind. Some
logic based on Core TreeBehavior. Trying to manage different trees is
troublesome and produce weird behaviours when using moveUp and
moveDown. ScopedTreeBehavior fix theses issues.

Trees are probably the most complicated data structure that your
manager or marketing teams will want. Easily managing theses
structures is troublesome in most case or require a lot of code to
make it properly work.

As I started using TreeBehavior to order different project contents
(let's say, web pages) within a rendered 'treeview', problems arises
as each project needed a different tree independent of the others.
Displayed the tree with a defined scope did work well but the MPTT
logic will use lft and rght to position a node within the tree. The
main problem with that is it would calculate lft and rght from all the
nodes in the table instead of only the nodes of the wanted scope. A
fix would have been to attach and detach the Tree behavior before any
tree modifications to include the project id in the scope. The
solution would only work up to a certain point and tree corruptions
happened quite a lot (wrong rght/lft values). Unable to use
TreeBehavior for some mission critical functions, ScopedTreeBehavior
was created.

First, you will need at least these fields in the model you would like
to use:

+ id (auto_increment / uuid)
+ parent_id (integer / uuid)
+ project_id (integer / uuid / string)
+ order (integer)
+ name (integer/string) // Don't really matter the type.


As of the first version, the default values may be changed directly in
the behavior file. Next version will include configuration when
attached / declared in the model.


The behavior:
`````````````

::

    
    <?php
    /**
     * ScopedTreeBehavior
     * Enables a model to be sorted as a tree.
     * Some logic from TreeBehavior of CakePHP :  Rapid Development Framework (http://www.cakephp.org)
     * @author Martin Samson <pyrolian@gmail.com>
     * @since 1.2
     * @todo Optimisations of the code.
     * @version 0.2
     * @see https://trac.cakephp.org/browser/trunk/cake/1.2.x.x/cake/libs/model/behaviors/tree.php
     */
    class ScopedTreeBehavior extends ModelBehavior{
        var $config = array();
        
        /**
     	 * Initiate  behavior
     	 *
     	 * @param object $Model
     	 * @param array $config
     	 * @return void
     	 * @access public
     	 */
        function setup(&$Model){
            $config['tree_id'] = 'project_id';
            $config['parent_id'] = 'parent_id';
            $config['order'] = 'order';
            $this->config = $config;
            $this->model =& $Model;
            $this->maxRecursive = 2;
        }
        
        /**
         * beforeSave Called before all saves
         *
         * Overriden to transparently manage the order of the record within the tree.
         *
         * @since         1.2
         * @param AppModel $Model
         * @return boolean true to continue, false to abort the save
         * @access public
        */
        function beforeSave(&$model){
            if(!$model->id){
    
                if(array_key_exists($this->config['parent_id'], $model->data[$model->alias]) && $model->data[$model->alias][$this->config['parent_id']]){
                    $parent_id = $this->getParent($model,$model->data[$model->alias][$this->config['parent_id']]);
                }
                else{
                    $parent_id = null;
                }
                if(array_key_exists($this->config['tree_id'], $model->data[$model->alias]) && $model->data[$model->alias][$this->config['tree_id']]){
                    $tree_id = $model->data[$model->alias][$this->config['tree_id']];
                }
                else{
                    return false;
                }
                
                $order = $this->getMax($tree_id,$parent_id);
                $order++;
            
                
                $model->data[$model->alias][$this->config['order']] = $order;
            }
            return true;
        }
    
        /**
         * moveDown
         *
         * Allows moving down a node inside it's level.
         * A node cannot change level or parent
         * @param AppModel $model
         * @param mixed $node_id The node id to use.
         * @param mixed $tree_id The tree to work in.
         * @param AppModel $node If specified, will use the provided node.
         * @return boolean
         */
        public function moveDown(&$model,$node_id,$tree_id,$node = null){
            if(!$node){
                $node = $this->getNode($model,$node_id);
                if(!$node){
                    return false;
                }
            }
            $options = array();
            $options['conditions'] = array();
            $options['conditions'][$model->alias .'.'. $this->config['tree_id']] = $tree_id;
            $options['conditions'][$model->alias .'.'. $this->config['parent_id']] = $node[$model->alias]['parent_id'];
            $options['conditions'][$model->alias .'.'. $this->config['order'] .' >'] = $node[$model->alias]['order'];
            $options['order'] = array($model->alias .'.'. $this->config['order'] .' ASC');
            $options['contain'] = array();
            $down = $model->find('first', $options);
            if($down){
    
                $nodeDown = array($model->primaryKey => $down[$model->alias][$model->primaryKey], $this->config['order'] => $down[$model->alias][$this->config['order']] - 1);
                $nodeCur = array($model->primaryKey => $node_id, $this->config['order'] => $node[$model->alias][$this->config['order']] + 1);
                
                $models = array();
                $models[0] = $nodeCur;
                $models[1] = $nodeDown;
                return $model->saveAll($models);
            }
            return false;
            
            
        }
    
        /**
         * moveUp
         *
         * Allows moving up a node inside it's parent
         * A node cannot change parent
         * @param AppModel $model
         * @param mixed $node_id The node id to use.
         * @param mixed $tree_id The tree to work in.
         * @param AppModel $node If specified, will use the provided node.
         * @return boolean
         */
        public function moveUp(&$model,$node_id,$tree_id,$node = null){
            if(!$node){
                $node = $this->getNode($model,$node_id);
                if(!$node){
                    return false;
                }
            }
            $options = array();
            $options['conditions'] = array();
            $options['conditions'][$model->alias .'.'. $this->config['tree_id']] = $tree_id;
            $options['conditions'][$model->alias .'.'. $this->config['parent_id']] = $node[$model->alias][$this->config['parent_id']];
            $options['conditions'][$model->alias .'.'. $this->config['order'] .' <'] = $node[$model->alias][$this->config['order']];
            $options['order'] = array($model->alias .'.'. $this->config['order'] .' DESC');
            $options['contain'] = array();
            $down = $model->find('first', $options);
            if($down){
    
                $nodeUp = array($model->primaryKey => $down[$model->alias][$model->primaryKey], $this->config['order'] => $down[$model->alias][$this->config['order']] + 1);
                $nodeCur = array($model->primaryKey => $node_id, $this->config['order'] => $node[$model->alias][$this->config['order']] - 1);
                
                $models = array();
                $models[0] = $nodeCur;
                $models[1] = $nodeUp;
                return $model->saveAll($models);
            }
            return false;        
        }
        /**
         * removeFromTree
         *
         * Remove a node from the tree and move all the child nodes under it's parent.
         * @param AppModel $model
         * @param mixed $id The id of the node to be removed
         * @param boolean $atomic If set to True, will use transactions. (default)
         * @return boolean
         */
        public function removeFromTree(&$model,$id,$node = null,$atomic = true){
            if(!$node){
                $node = $this->getNode($model,$id);
                if(!$node){
                    return false;
                }
            }
            
            
            // 1: update all the child nodes of the node to be removed.
            $conditions = array($model->alias.'.'.$this->config['tree_id'] => $node[$model->alias][$this->config['tree_id']],
                                $model->alias.'.'.$this->config['parent_id'] => $id);
            $db =& ConnectionManager::getDataSource($model->useDbConfig);
            $parentField = $model->alias .'.'.$this->config['parent_id'];
            $parent_id = $node[$model->alias][$this->config['parent_id']];
            $tree_id = $node[$model->alias][$this->config['tree_id']];
            if($atomic){
                $db->begin($this);
            }
            $model->updateAll(array($parentField => $db->value($parent_id, $parentField)), $conditions);
            //2: Remove the node entry.
            $model->del($id);
            
            //3: Update the ordering of the childs and the level the node was in.
            $subtree = $this->getChilds(&$model,$tree_id,$parent_id,array($model->alias.'.'.$model->primaryKey,$model->alias.'.'.$this->config['order']));
            $this->syncLevel($model,$subtree);
            
            if($atomic){
                $db->commit($this);
                return true;
            }
            return false;
        }
        /**
         * syncLevel
         * Syncs the order of all the nodes of a level (common parent).
         * @param AppModel $model
         * @param array $subtree The nodes to sync together.
         * @access private
         * @return void
         *
         */
        private function syncLevel(&$model,&$subtree){
            $i = 0;
            foreach($subtree as $key=>$node){
                $node[$model->alias][$this->config['order']] = $i;
                $subtree[$key] = $node;
                $i++;
            }
            $model->saveAll($subtree);
        }
        public function getChilds(&$model,$tree_id,$parent_id, $fields = null){
            $options = array();
            $options['contain'] = array();
            if($fields){
                $options['fields'] = $fields;
            }
            $options['conditions'] = array($model->alias.'.'. $this->config['tree_id'] => $tree_id,
                                           $model->alias.'.'. $this->config['parent_id'] => $parent_id);
            $options['order'] = array($model->alias.'.'.$this->config['order'] .' ASC');
            return $model->find('all', $options);
            
        }
        /**
         * generateTree Generates the tree structure for the specified tree scope.
         * @param AppModel $model
         * @param mixed $tree_id The project id to generate the tree for.
         * @return array The structured tree as an array.
         */
        public function generateTree(&$model,$tree_id){
            $options = array();
            $options['contain'] = array();
            $options['conditions'] = array($model->alias.'.'. $this->config['tree_id'] => $tree_id);
            $options['order'] = array($model->alias.'.'.$this->config['order'] .' ASC');
            $rawtree = $model->find('all', $options);
            return $this->getTree($rawtree,null);
        }
        /**
         * getTree Format a raw database tree into a structured tree.
         * The function is recursive. It builds all the childs elements.
         * @param array $rawtree The database raw results
         * @param mixed $parent_id The parent id to structure for.
         * @param integer $recursive The level of recursiveness to allow/limit.
         * @return array The structured tree array.
         */
        public function getTree(&$rawtree,$parent_id,$recursive = 0){
            $tmpTree = array();
            if($recursive > $this->maxRecursive){
                return $tmpTree;
            }
            foreach($rawtree as $key => $node){
                if($node[$this->model->alias][$this->config['parent_id']] == $parent_id){
    
                    //get the child nodes of the current node
                    $node['childs'] = $this->getTree($rawtree,$node[$this->model->alias][$this->model->primaryKey],$recursive + 1);
                    
                    //add the node to the tmptree.
                    $tmpTree[] = $node;
                }
            }
            return $tmpTree;
        }
        /**
         * getNode
         * Returns the node that match the provided id
         * @param AppModel $model
         * @param mixed $id The node id to return
         * @param array $fields The fields to return
         * @param array $contain Allows using containable if the argument is provided.
         * @return AppModel The model if found, otherwise will return null.
         *
         */
        public function getNode(&$model,$id,$fields = null,$contain = null){
            
            $options = array();
            
            if(is_array($contain)){
                $options['contain'] = $contain;  
            }
            
            $options['conditions'] = array($model->alias . '.' . $model->primaryKey => $id);
            
            if(is_array($fields)){
                $options['fields'] = $fields;   
            }
    
    
            
            $options['recursive'] = 0;
    
            return $model->find('first',$options);        
        }
        /**
         * getParent
         * Returns the parent of the provided node.
         * @param AppModel $model
         * @param mixed $id The id of then node.
         * @return AppModel The parent node or null
         */
        public function getParent(&$model,$id){
            $parent = $this->getNode($model,$id, array($model->primaryKey));
            if (!$parent){
                return null;
            }
            return $parent[$model->alias][$model->primaryKey];
            
        }
        /**
         * getOrder
         * Return the current maximum order value.
         * @param mixed $tree_id The id of the tree
         * @param mixed $parent_id The id of the parent
         * @return integer A the maximum value of order.
         *
         */
        private function getMax($tree_id,$parent_id){
            $db =& ConnectionManager::getDataSource($this->model->useDbConfig);
            $options = array();
            $options['conditions'] = array($this->model->alias . '.' . $this->config['tree_id'] => $tree_id,
                                           $this->model->alias . '.' . $this->config['parent_id'] => $parent_id);
            
            $options['fields'] = $db->calculate($this->model, 'max', array($this->config['order']));
            $options['recursive'] = 0;
    	    list($edge) = array_values($this->model->find('first',$options));
            return (empty($edge[$this->config['order']])) ? 0 : $edge[$this->config['order']];
        }
    }
    ?>



The code in a model:
````````````````````

Model Class:
````````````

::

    <?php 
    var $actsAs = array('ScopedTree');
    ?>



Using the behavior
~~~~~~~~~~~~~~~~~~
Each node must belongs to a scope (project/tree id).

To create a top-level node, the scope id must be set and by default,
the model would require a name:

::

    
    $this->Model->create();
    $this->Model->save(array('project_id'=>1,'name'=>4));

To create a child node, the parent_id must be supplied as well as the
tree_id:

::

    
    $this->Model->create();
    $this->Model->save(array('project_id'=>1,'name'=>'child of 45','parent_id'=>45));

To move a node up or down, the function takes the id and tree_id as
parameters:

::

    
    $this->Model->moveUp($id, $project_id);
    $this->Model->moveDown($id,$project_id);

The node will be moved only within it's level. It will not be able to
change it's parent_id. In the event the node is already at the top,
the moveUp will fail (return false). Same will happend if the node is
the last one.

To remove a node:

::

    
    $this->Model->removeFromTree($id);


All child nodes will be moved up one level. The level of the removed
node will be re-organised by merging the two levels and re-ordering
them. (Node 1 of level 1 will be next to node 1 of level 2).

To generate the tree, the tree_id must be passed by parameter:

::

    
    $this->Model->generateTree($id);

This is all for now. Some inconsistencies will be worked out when I
have time (such as the removeFromTree not requiring the project_id,
while moveup/down requires it).


The Guts
~~~~~~~~
The tree is generated with a recursive function:

::

    
        public function getTree(&$rawtree,$parent_id,$recursive = 0){
            $tmpTree = array();
            if($recursive > $this->maxRecursive){
                return $tmpTree;
            }
            foreach($rawtree as $key => $node){
                if($node[$this->model->alias][$this->config['parent_id']] == $parent_id){
    
                    //get the child nodes of the current node
                    $node['childs'] = $this->getTree($rawtree,$node[$this->model->alias][$this->model->primaryKey],$recursive + 1);
                    
                    //add the node to the tmptree.
                    $tmpTree[] = $node;
                }
            }
            return $tmpTree;
        }

The function call itself to find if the current node has any childs.
The childs would then become a sub-array of the current node, making
rendering the tree way more easier than with TreeBehavior (which uses
levels of _ to know the position of a child node).


Test Case
~~~~~~~~~
The case:

::

    
    <?php
    class ScopedTree extends CakeTestModel {
            public $name = 'ScopedTree';
            public $actsAs = array('ScopedTree');
            public $fixture = 'scoped_tree';
    }
    class ScopedTreeCase extends CakeTestCase {
    
    	var $fixtures = array('app.scoped_tree');
    
        function startTest(){
            $this->ScopedTree =& ClassRegistry::init('ScopedTree');
        }
        function endTest() {
            unset($this->ScopedTreeCase);
            ClassRegistry::flush();
        }
    
    	function testCreateNodes() {
            // Create a node
            $this->ScopedTree->create();
            $result = $this->ScopedTree->save(array('project_id'=>1,'name'=>4));
            $this->assertTrue($result);
            
            // We expect these 4 nodes:
            $expected = array();
            $expected[0] = array($this->ScopedTree->alias => array('id' => 1,'name' => 1,'parent_id'=>null,'project_id'=>1,'order'=>1));
            $expected[1] = array($this->ScopedTree->alias => array('id' => 2,'name' => 2,'parent_id'=>null,'project_id'=>1,'order'=>2));
            $expected[2] = array($this->ScopedTree->alias => array('id' => 3,'name' => 3,'parent_id'=>null,'project_id'=>1,'order'=>3));
            $expected[3] = array($this->ScopedTree->alias => array('id' => 4,'name' => 4,'parent_id'=>null,'project_id'=>1,'order'=>4));
            // Fetch them and assert.
            $results = $this->ScopedTree->find('all', array('conditions'=>array($this->ScopedTree->alias.'.project_id'=>1)));
            $this->assertEqual($results,$expected);
            
    	}
        function testCreateChilds(){
            //Create 3 childs under parent with id 1
            $this->ScopedTree->create(array('project_id'=>1,'name'=>4,'parent_id'=>1));
            $this->ScopedTree->save();
            $this->ScopedTree->create(array('project_id'=>1,'name'=>5,'parent_id'=>1));
            $this->ScopedTree->save();
            $this->ScopedTree->create(array('project_id'=>1,'name'=>6,'parent_id'=>1));
            $this->ScopedTree->save();
            
            // We expect 3 top level, and parent with id 1 to have 5 childs
            $expected = array();
            $expected[0] = array($this->ScopedTree->alias => array('id' => 1,'name' => 1,'parent_id'=>null,'project_id'=>1,'order'=>1),'childs'=>array());
            $expected[1] = array($this->ScopedTree->alias => array('id' => 2,'name' => 2,'parent_id'=>null,'project_id'=>1,'order'=>2),'childs'=>array());
            $expected[2] = array($this->ScopedTree->alias => array('id' => 3,'name' => 3,'parent_id'=>null,'project_id'=>1,'order'=>3),'childs'=>array());
            
            $childs = array();
            $childs[0] = array($this->ScopedTree->alias => array('id' => 4,'name' => 4,'parent_id'=>1,'project_id'=>1,'order'=>1),'childs'=>array());
            $childs[1] = array($this->ScopedTree->alias => array('id' => 5,'name' => 5,'parent_id'=>1,'project_id'=>1,'order'=>2),'childs'=>array());
            $childs[2] = array($this->ScopedTree->alias => array('id' => 6,'name' => 6,'parent_id'=>1,'project_id'=>1,'order'=>3),'childs'=>array());
            
            $expected[0]['childs'] = $childs;
            
            $tree = $this->ScopedTree->generateTree('1');
    
            $this->assertEqual($tree,$expected);
            
        }
        function testRemoveParent(){
            //Create 3 childs under parent with id 1 and 1 child under parent with id 2
            $this->ScopedTree->create();
            $this->ScopedTree->save(array('project_id'=>1,'name'=>4,'parent_id'=>1));
            $this->ScopedTree->create();
            $this->ScopedTree->save(array('project_id'=>1,'name'=>5,'parent_id'=>1));
            $this->ScopedTree->create();
            $this->ScopedTree->save(array('project_id'=>1,'name'=>6,'parent_id'=>1));
            $this->ScopedTree->create();
            $this->ScopedTree->save(array('project_id'=>1,'name'=>6,'parent_id'=>2));
            
            $result = $this->ScopedTree->removeFromTree(1);
            $this->assertTrue($result);
            
            // We expect 5 notes at the root level
            $tree = $this->ScopedTree->generateTree(1);
            $expected = 5;
            $this->assertEqual(count($tree),$expected);
    
                   
        }
        function testMoveUpNode(){
            $result = $this->ScopedTree->moveUp(2,1);
            $this->assertTrue($result);
            
            $tree = $this->ScopedTree->generateTree(1);
            
            $expected = array();
            $expected[0] = array('ScopedTree'=>array('id'=>2,'name'=>'2','parent_id'=>null,'project_id'=>1,'order'=>1),'childs'=>array());
            $expected[1] = array('ScopedTree'=>array('id'=>1,'name'=>'1','parent_id'=>null,'project_id'=>1,'order'=>2),'childs'=>array());
            $expected[2] = array('ScopedTree'=>array('id'=>3,'name'=>'3','parent_id'=>null,'project_id'=>1,'order'=>3),'childs'=>array());
            
            $this->assertEqual($tree,$expected);
        }
        function testMoveDownNode(){
            $result = $this->ScopedTree->moveDown(2,1);
            $this->assertTrue($result);
            
            $tree = $this->ScopedTree->generateTree(1);
            
            $expected = array();
            $expected[0] = array('ScopedTree'=>array('id'=>1,'name'=>'1','parent_id'=>null,'project_id'=>1,'order'=>1),'childs'=>array());
            $expected[1] = array('ScopedTree'=>array('id'=>3,'name'=>'3','parent_id'=>null,'project_id'=>1,'order'=>2),'childs'=>array());
            $expected[2] = array('ScopedTree'=>array('id'=>2,'name'=>'2','parent_id'=>null,'project_id'=>1,'order'=>3),'childs'=>array());
            
            $this->assertEqual($tree,$expected);        
        }
    
    }
    ?>

The fixture:

::

    
    <?php
    class ScopedTreeFixture extends CakeTestFixture {
    
    	var $name = 'ScopedTree';
    
    	var $fields = array(
    		'id'	=> array('type' => 'integer', 'key' => 'primary'),
    		'name'	=> array('type' => 'string','null' => false),
    		'parent_id' => array('type' => 'integer'),
    		'project_id'	=> array('type' => 'string','length' => 36),
    		'order'	=> array('type' => 'integer')
    	);
    	
    	var $records = array(
    						 array('project_id'=>1,'name'=>1,'parent_id'=>null,'order'=>1),
    						 array('project_id'=>1,'name'=>2,'parent_id'=>null,'order'=>2),
    						 array('project_id'=>1,'name'=>3,'parent_id'=>null,'order'=>3)
    						 );
    }
    ?>


.. meta::
    :title: ScopedTreeBehavior
    :description: CakePHP Article related to tree,behavior,scope,Behaviors
    :keywords: tree,behavior,scope,Behaviors
    :copyright: Copyright 2009 masom
    :category: behaviors

