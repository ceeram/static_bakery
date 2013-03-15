Keeping bindModel and unbindModel out of your Controllers
=========================================================

by %s on December 06, 2006

Sometimes you need to fine-tune your associations: binding to other
Models only when needed or unbinding exisiting relations to minimize
the size of your result set. With a very simple method and a slight
change in how you write some associations, this can be done cleanly
and efficiently right from your controller.
Model::bindModel and Model::unbindModel are powerful tools that allow
you to adjust associations on the fly. However, they are often
misused, taking the association definition out of the Model and
placing it in the Controller. Changing the requirements of a bind done
in this way means going through your controllers and changing the
bindModel call, often in multiple places. Using unbindModel in a
controller also means every time a new association is added you may
need to go back into your controllers and unbind the new associations
in order to optimize your code.

Here I show you how to add a simple method to your Model classes that
allows your controllers to specify binds directly, in a cleaner, more
proper way.

Place the following code in app/app_model.php

Model Class:
````````````

::

    <?php 
    class AppModel extends Model {
    ...
        var $assocs = array();
    ...
        function expects($array) {
            foreach ($array as $assoc) {
                $this->bindModel(
                    array($this->assocs[$assoc]['type'] =>
                        array($assoc => $this->assocs[$assoc])));
            }
        }
    ...
    }
    ?>

Now, in your Models, define the associations in an array called
$assocs. The only difference between this definition and the standard
association definitions is the inclusion of a new key: 'type', which
should be 'hasOne', 'hasMany', 'belongsTo' or 'hasAndBelongsToMany'.


Model Class:
````````````

::

    <?php 
    class Title extends AppModel {
    
        var $assocs = array(
            'Book' => array(
                'type' => 'belongsTo',
                'className' => 'Book',
                'foreignKey' => 'collection_id',
            ),
            'Story' => array(
                'type' => 'hasOne',
                'className' => 'Story',
            ),
            'Album' => array(
                'type' => 'belongsTo',
                'className' => 'Album',
                'foreignKey' => 'collection_id',
            ),
            'Photo' => array(
                'type' => 'hasOne',
                'className' => 'Photo',
            ),
            'Post' => array(
                'type' => 'hasMany',
                'className' => 'Post',
                'order' => 'Post.id DESC',
            ),
        );
    }
    ?>

Now, whenever you need to extend your results through an association,
you can make a call to Model::expects() with the necessary values
right before the query, and get the results you need.


Controller Class:
`````````````````

::

    <?php 
    TitlesController extends AppController {
        function list($id) {
            // establish necessary associations
            $this->Title->expects(array('Story', 'Post'));
            $this->Title->Post->expects(array('User'));
            $this->Title->recursive = 2;
            $results = $this->Title->read(null, $id);
        }
    }
    ?>

That's it.

I still use the standard means of associations in most cases. For
example, I will probably never need to show my Post without the User
info, so my Post model has a $belongsTo = array('User') in its class
definition. The above example of
$this->Title->Post->expects(array('User')); would never be needed and
was just included here to show you how the associations can work
through recursion.

And now, with the way Models are loaded with CakePHP_1.1.11, this has
an even greater affect. Models are only loaded when needed by a
particular action.

.. meta::
    :title: Keeping bindModel and unbindModel out of your Controllers
    :description: CakePHP Article related to ,Tutorials
    :keywords: ,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

