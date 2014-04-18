Filter Out Unnecessary Recursive Relationships
==============================================

Add a small function to your AppModel to filter out those recursive
relationships that are unnecessary for a particular controller action.
I wrote this using the following articles for references. Thanks
folks, they helped me write this snippet in about 15 minutes.

`unbindAll`_ by `cornernote`_`Keeping bindModel and unbindModel out of
your Controllers`_ by `TommyO`_`An improvement to unbindModel on model
side`_ by `mariano`_
I didn't need the full functionality of some of these solutions. I
just needed a way to quickly and succinctly filter out a particular
model's associations.

Please comment with your observations, corrections, and/or comments.

Add this function to your AppModel:

Model Class:
````````````

::

    <?php 
    class AppModel extends Model{
    
        /**
         * Performs an 'unbindModel' on an array of associations
         * 
         * @param array $bindings An array of model associations to unbind from current model
         * @return null
         */
        function filterBindings($bindings = null) {
            if (empty($bindings) && !is_array($bindings)) {
                return false;
            }
            $relations = array('hasOne', 'hasMany', 'belongsTo', 'hasAndBelongsToMany');
            $unbind = array();
            foreach ($bindings as $binding) {
                foreach ($relations as $relation) {
                    if (isset($this->$relation)) {
                        $currentRelation = $this->$relation;
                        if (isset($currentRelation) && isset($currentRelation[$binding])) {
                            $unbind[$relation][] = $binding;
                        }
                    }
                }
            }
            if (!empty($unbind)) {
                $this->unbindModel($unbind);
            }
        }
    }
    ?>

Then in your controller use the 'filterBindings' function:

Controller Class:
`````````````````

::

    <?php 
    class PostsController extends AppController {
    
        var $name = 'Posts';
    
        function index() {
            $this->Post->recursive = 2;
    
            $this->Post->filterBindings(array('Comment', 'Rating'));
            $this->Post->User->filterBindings(array('Status'));
        }
    }
    ?>



.. _mariano: http://bakery.cakephp.org/users/view/mariano
.. _cornernote: http://bakery.cakephp.org/users/view/cornernote
.. _unbindAll: http://bakery.cakephp.org/articles/view/unbindall
.. _An improvement to unbindModel on model side: http://bakery.cakephp.org/articles/view/an-improvement-to-unbindmodel-on-model-side
.. _TommyO: http://bakery.cakephp.org/users/view/TommyO
.. _Keeping bindModel and unbindModel out of your Controllers: http://bakery.cakephp.org/articles/view/keeping-bindmodel-and-unbindmodel-out-of-your-controllers

.. author:: marklap
.. categories:: articles, tutorials
.. tags:: ,Tutorials

