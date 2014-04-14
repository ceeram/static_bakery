Null Behavior
=============

by Jippi on March 25, 2007

Problems with default NULL fields not being very NULL'ish ?
This is just a small script to make sure some values arent saved if
they are supposed to be NULL in the database.

*** Updated ***
Added check on length of string, to avoid the 0 being caught as a
nullable value.


Use
~~~

Model Class:
````````````

::

    <?php 
    var $actsAs = array('Null' => array('file_id','parent_id'));
    ?>



Code
~~~~


Model Class:
````````````

::

    <?php 
    class NullBehavior extends ModelBehavior {
        var $settings = array();
    
        /**
         * Enter description here...
         *
         * @param AppModel $model
         * @param unknown_type $config
         */
        function setup(&$model, $config = array() )
        {
            $this->settings[ $model->name ] = $config;
        }
    
        /**
         * Enter description here...
         *
         * @param AppModel $model
         */
        function beforeSave(&$model)
        {
            $config = $this->settings[$model->name];
    
            foreach ( $config AS $field )
            {
                if(
                    true === array_key_exists($field,$model->data[$model->name] ) &&
                    true === empty( $model->data[$model->name][$field] ) &&
                    0 === strlen( $model->data[$model->name][$field] ) )
                {
                    unset($model->data[$model->name][$field]);
                }
            }
            return true;
        }
    }
    ?>


.. meta::
    :title: Null Behavior
    :description: CakePHP Article related to behavior null model,Behaviors
    :keywords: behavior null model,Behaviors
    :copyright: Copyright 2007 Jippi
    :category: behaviors

