SimpleResults Behavior - Re-index results from findAll()
========================================================

by %s on May 15, 2007

When returning rows from a Model::findAll() call, the result array is
indexed in a certain way. This behavior allows models to return their
results indexed in the same way as hasMany associations, which is more
concise and (in certain circumstances) easier to parse / pass to other
functions.
Returned rows from a findAll() follow this format:

::

    
    Array
     (
        [0] => Array
            (
                [Foo] => Array
                    (
                        [id] => 15
                        [title] => Aardvark Supplies, Inc.
                    )
            )
    
        [1] => Array
            (
                [Foo] => Array
                      (
                        [id] => 24
                        [title] => Lorem Ipsum Ltd.
                    )
            )
        [2] => (...)

SimpleResults Behavior re-indexes the results like this (like hasMany
associations):

::

    
    Array
    (
        [Foo] => Array
            (
                [0] => Array
                    (
                        [id] => 15
                        [title] => Aardvark Supplies, Inc.
                    )
    
                [1] => Array
                    (
                        [id] => 24
                        [title] => Lorem Ipsum Ltd.
                    )
    
                [2] => ...
            )
    )

There's only one option for this behavior - whether or not to reformat
results that contain joins. If this is set to false then it will only
re-index when there are no associated models in the results (recursive
set to zero).

In case you want to have certain actions behave differently, the
behavior also creates a model variable called 'useSimpleResults' which
is checked before any re-indexing occurs. It is set to true by
default.

Sample usage:

Model Class:
````````````

::

    <?php 
    class Foo extends AppModel {
      var $name = "Foo";
    
      var $actsAs = array('SimpleResults' => array('simplifyIfJoins' => true));
    
      var $useSimpleResults = true;
    }
    ?>



Controller Class:
`````````````````

::

    <?php 
    class FoosController extends AppController {
      // (...)
      function index() {
        // Results are simplified here
        $foos = $this->Foo->findAll();
        $this->set(compact('foos'));
      }
    
      function admin_index() {
        // Results are *not* simplified here
        $this->Foo->useSimpleResults = false;
        $foos = $this->Foo->findAll();
        $this->set(compact('foos'));
      }
      // (...)
    }
    ?>

And here's the code:

Behavior Class:
```````````````

::

    
    <?php
    
    // And the actual Behavior
    class SimpleResultsBehavior extends ModelBehavior {
      var $config = array();
    
      function setup(&$model, $config = array()) {
    
        $this->config[$model->name] = am(
          array(
            'simplifyIfJoins' => true    // Do we want to simplify the result
                                         // list if there are joined models?
          ),
          $config
        );
        if (!isset($model->useSimpleResults)) {
          // Simple switch in the model to enable per-action deactivation of this feature
          $model->useSimpleResults = true;
        }
      }
      
      function afterFind(&$model, $results) {
        // If switch has been disabled then cancel
        if (!$model->useSimpleResults) {
          return;
        }
        
        // Skip empty arrays
        if (empty($results)) { 
          return;
        }
        
        // The results must be a numerically-indexed list (0..n)
        if (!$this->_isNumericArray($results)) {
          return;
        }
        
        // The resultset must reference the model itself (sanity check)
        if (!isset($results[0][$model->name])) {
          return;
        }
    
        // If the resultset contains joins then 
        // we need to check the config to see if it's allowed
        if ( (!$this->config[$model->name]['simplifyIfJoins']) && (sizeof($results[0]) > 1) ) {
          return;
        }
        
        $out = array();
        foreach ($results as $result) {
          // Grab the self-model reference
          $base = $result[$model->name];
    
          // Remove the self-model reference from the results
          unset($result[$model->name]);
    
          // Append these (if any) to the self-model results
          $out[] = am($base, $result);
        }
        // Return the reorganized results
        return array( "{$model->name}" => $out );
      }
    
      // This handy function I wrote is actually part of my standard 
      // includes loaded in bootstrap.php - but I'll put it here instead
    
      /**
       * Check if an array is numerically indexed in a standard manner.
       * [0..(n-1)], with no other keys
       *
       * @param  array  $array Array to check
       * @return boolean
       */
      function _isNumericArray($array) {
        if (!is_array($array)) { 
          return null;
        }
        return (array_sum(array_keys($array)) === (sizeof($array) * (sizeof($array)-1))>>1)
      }
      
    }
    ?>


.. meta::
    :title: SimpleResults Behavior - Re-index results from findAll()
    :description: CakePHP Article related to hasMany,behavior,1.2,findAll,association,Behaviors
    :keywords: hasMany,behavior,1.2,findAll,association,Behaviors
    :copyright: Copyright 2007 
    :category: behaviors

