Populating Select Boxes with Tree Paths
=======================================

by %s on August 26, 2008

If you populate select boxes with a tree, and your tree has many
nodes, which can be named the same, you are often left with an
unusable select box. The following methods will help alleviate this
problem by displaying the nodes in the select box as a path from the
root of the tree.
To begin, your model must behave like a tree, so at the very least
your model should look like:

Model Class:
````````````

::

    <?php 
    class Category extends AppModel {
        var $name = 'Category';
        var $actsAs = array('Tree');
    ?>

Next we will add two functions that will do the work of finding each
tree node's path.


Model Class:
````````````

::

    <?php 
    class Category extends AppModel {
        var $name = 'Category';
        var $actsAs = array('Tree');
        
        function setTreePath(&$data, $path='tree_path', $label='name') {
            if (!is_array($data) || !in_array('Tree', $this->actsAs)) {
                return $data;
            }
            if (is_array($data) && is_int(array_shift(array_keys($data)))) {
                foreach ($data as $i=>$item) {
                    $this->_setTreePath($data[$i], $path, $label);
                }
            } else {
                $this->_setTreePath($data, $path, $label);
            }
        }
        
        function _setTreePath(&$data, $pathField, $label) {
            $cats = $this->getpath($data[$this->name][$this->primaryKey]);
            $path = array();
            foreach ($cats as $cat) {
                array_push($path, $cat[$this->name][$label]);
            }
            $data[$this->name][$pathField] = implode('/', $path);
        }
        
    }
    ?>

Then in our controller we can do something like the following to get a
list of tree paths in the same format as find('list').


Controller Class:
`````````````````

::

    <?php 
    function showSelect() {
        $allCategories = $this->Category->find('all', array('fields'=>'id', 'name'));
        $this->Category->setTreePath($allCategories);
        $categories = array();
        foreach ($allCategories as $cat) {
            $categories[$cat['Category']['id']] = $cat['Category']['tree_path'];
        }
        $this->set(compact('categories'));
    }
    ?>

And simply in our show_select.ctp view we write:


View Template:
``````````````

::

    <?php
        echo $form->create('Category', array('action'=>'/not/an/action'));
        echo $form->input('Category.Category');
        echo $form->end('Submit');
    ?>

And we should get a select box filled with our category names as
paths. So, for instance, we could get 'Electronics/Televisions/LCD'
and 'Electronics/Monitors/LCD', whereas before we would get multiple
LCD options.

.. meta::
    :title: Populating Select Boxes with Tree Paths
    :description: CakePHP Article related to tree,Tutorials
    :keywords: tree,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

