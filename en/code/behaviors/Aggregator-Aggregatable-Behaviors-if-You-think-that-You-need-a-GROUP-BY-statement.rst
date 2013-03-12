

Aggregator & Aggregatable Behaviors: if You think that You need a
GROUP BY statement
==================

by %s on July 07, 2008

I wrote these two behaviors because I thought that I need to use some
GROUP BY sql statements. It is possible to use them in cakePHP only by
$this->query so it is not a good solution. Then I realized that I
don't need GROUP BY actually. All I need is solution similar to
counterCache - functionality that is already implemented in CakePHP


Reason
~~~~~~
counterCache gives us ability to add special field to one model (for
example Post) that is automatically updated when number of records in
other model (Comment) has changed.
Using Aggregatable Behavior or Aggregator Behavior You can have
special fields that not only count records but use any SQL function
You need.


Two aproaches
~~~~~~~~~~~~~
In certain situation You probably need only one of these two
behaviors. If You have two models that depend on each other (let's say
Post and Comment, where Post hasMany Comment and Comment belongsTo
Post) then You have two possibilities.

In first solution Post actsAs Aggregator, and special field is only
"virtual" - it is not saved in the database (I will explain it later).
In second solution Comment actsAs Aggregatable, and this special field
is "real" - it is saved in the database.
In both solutions, relation (SQL function) is defined in aggregating
model (Post) in an variable. I decided to do this that way, because
defining these fields in one place helps switching between these two
methods.

For example if You use Aggregatable Behavior (so there is no
aggregating field in the database), and You decide to add such field
to the database (it's faster solution), all You need to do (after
creating this field of course) is to add "actsAs =
array('Aggregatable')" to one model, delete "actsAs =
array('Aggregator')" from another, and replace string "virtual" to
"real" in sggregatingFields array.
Actually, it's possible to reduce all of that to only one action
(adding new field to the database), because behaviors could check
existence of aggregating fields, but i haven't implemented this
functionality yet.



First approach: Aggregatable Behavior
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Using counterCache You need special field in the database.
Aggregatable Behavior works in the same way.

Model Class:
````````````

::

    <?php 
    /** 
    * Aggregatable Behavior (updates real aggregators of foreign Model) 
    * 
    *  
    */ 
    class AggregatableBehavior extends ModelBehavior { 
    
    /** 
    * setup 
    * 
    * @return boolean true on success 
    */ 
    function setup(&$model, $config = array())  
    { 
      $this->settings[$model->alias] = (array)$config; 
    } 
    /** 
    * Update selected models 
    * 
    * @param AppModel &$model 
    * @param AppModel $toUpdate models to be updated 
    * @return boolean success 
    * @access private 
    */ 
    function _updateRealAggregators(&$model, $toUpdate) 
    { 
      foreach ($toUpdate as $groupingModelName) 
      { 
        $groupingModel = $model->$groupingModelName;  
        foreach($groupingModel->aggregatingFields as $fieldToUpdate => $params) 
        { 
          if ($params['model'] == $model->alias) 
          {  
            //TODO: to enable recursive calls  
            // ...  
            $grouped_model_id = $model->field("{$params['foreignKey']}"); 
            $gconds = array("{$groupingModelName}.{$groupingModel->primaryKey}" => $grouped_model_id); 
            $data = $groupingModel->findAll($gconds); 
            if (!empty($data)) 
            { 
              foreach($data as $recordToUpdate) 
              { 
                $groupingModel->create(); 
                $groupingModel->id = $grouped_model_id; 
                $mconds = array("{$model->alias}.{$params['foreignKey']}" => $grouped_model_id); 
                if (!empty($params['conditions'])) 
                { 
                  $mconds = array('AND' => $mconds, $params['conditions']); 
                }  
                $agreg = $model->find($params['conditions'], $params['function']); 
                $db =& ConnectionManager::getDataSource($model->useDbConfig); 
                
                $new_value = $this->_extractNewValue($agreg, $model, $db->name($params['function']));
                $groupingModel->saveField($fieldToUpdate, $new_value); 
              } 
            } 
          } 
        }  
      } 
    } 
    /**
     * Extracts aggregated field from a set ($data)
     * @param array $data
     * @param $model $data was extracted from it
     * @param $function field name, or an sql function
     * @return mixed an extracted value
     */
    function _extractNewValue($data, $model, $function){             
      $model_alias = $model->alias;
      if (empty($data[$model_alias]))
      {
        $model_alias = 0;
      }
      if (empty($data[$model_alias][$function]))
      {
        $function_parts = explode('.', $function);
        if (trim($function_parts[0], '`') === $model_alias)
        {
        // if $function is a table field name
          $function = trim($function_parts[1], '`');
        }  
        else
        {
          //if $function is an sql function  (for CakePHP 1.2 RC 2, because it wraps around an SQL function with '`')
          $function = trim($function, '`');
        }    
      }
      $new_value = $data[$model_alias][$function];
      return $new_value;
    }  
    /** 
    * Choose models to be updated 
    * 
    * @param AppModel &$model 
    * @return boolean success 
    * @access public 
    */ 
    function updateRealAggregators(&$model) 
    { 
      if (!function_exists('getAssociatedModel')) 
      { 
        function getAssociatedModel($record) 
        { 
          if (!empty($record['model'])) 
          { 
            return $record['model']; 
          } 
          else 
          { 
            return null; 
          } 
        } 
      } 
      if (!empty($model->belongsTo)) 
      { 
        $toUpdate = array(); 
        foreach($model->belongsTo as $foreignModel => $model_data) 
        { 
          if (!empty($model->$foreignModel->aggregatingFields)) 
          { 
            $associated = array_map('getAssociatedModel', $model->$foreignModel->aggregatingFields);  
            $associated = array_unique(array_values($associated)); 
            if (in_array($model->alias, $associated)) 
            { 
              $toUpdate[] = $foreignModel; 
            } 
          } 
        } 
        return $this->_updateRealAggregators($model, $toUpdate); 
      } 
      else 
      { 
        return true; 
      }  
    } 
    /** 
    * After save method. Called after all saves 
    * 
    * @param AppModel $model 
    * @param boolean $created indicates whether the node just saved was created or updated 
    * @return boolean true on success, false on failure 
    * @access public 
    */ 
    function afterSave(&$model, $created)  
    { 
      return $this->updateRealAggregators($model); 
    } 
    /** 
    * Before delete method. Called before all deletes 
    * 
    * @param AppModel $model 
    * @return boolean true on success, false on failure 
    * @access public 
    */ 
    function afterDelete(&$model)  
    { 
      return $this->updateRealAggregators($model); 
    } 
    } 
    
    ?>

SQL:

::

    
    CREATE TABLE `post` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `text` TEXT NOT NULL ,
    `user_id` INT UNSIGNED NOT NULL
    );
    
    CREATE TABLE `comment` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `post_id` INT UNSIGNED NOT NULL,
    `text` TEXT NOT NULL
    );

Code snippet:

Controller Class:
`````````````````

::

    <?php 
    $post1 = array('Post' => array('text' => 'first post', 'user_id' => 1));
    $this->Post->save($post1);
    
    $comment = array('Comment' => array('text' => 'OK'));
    $this->Comment->save($comment);
    $comment->create();
    $comment = array('Comment' => array('text' => 'Not OK'));
    $this->Comment->save($comment);
    
    $post2 = $this->Post->find();
    pr($post2);
    ?>

Above code will print something like:

::

    
    array(
      0 => array(
        'Post' => array(
          'id' => 1,
          'text' => 'first post',
          'user_id' => 1,
          'comment_count' => 2,
          'last_comment_id' => 2
        )
      ) 
    )


Note the 'comment_count' and 'last_comment_id' fields that where
updated automatically after Comment->save call.



Second approach: Aggregator Behavior
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Aggregatable Behavior should be declared in aggregated model
(Comment), but as I said before, sometimes You don't want additional
"real" field in your database, but only a "virtual" field that is
computed every time you call a find method of your aggregating model
(Post). And that's what Aggregating Behavior is for. This behavior
should be declared in the aggregating model (Post)

Model Class:
````````````

::

    <?php 
    /**
    * Aggregator Behavior (updates virual aggregators of a Model)
    *
    * 
    */
    /**
    * Gives ability to use virtual fields that depend on each other
    * (use Aggregatable and 'real' table fields to define recursive dependancy)
    */ 
    define('VALUATE_ALREADY_COMPUTED', true); 
    /**
     * AggregatorBehavior
     */
    class AggregatorBehavior extends ModelBehavior {
    /**
     * setup
     *
     * @param &$model
     * @param $config
     * @return boolean true on success, false on failure
     * @access public
     */
    function setup(&$model, $config = array()) 
    {
      $this->settings[$model->alias] = (array)$config;
    }
    /**
     * Changes all field names to values if they were already computed in Aggregator 
     *
     * @param array $conditions conditions to change
     * @param array $data model data (virtual fields and table data) TODO: should be only virtual?
     * @param AppModel $model model name (its alias is important - but You dont have to use this alias in conditions)
     * @access private
     */
    function _valuateAlreadyComputed($conditions, $data, &$model)
    {
      $tmp_conditions = $conditions;
      foreach ($tmp_conditions as $key => $value)
      {
        foreach($data[$model->alias] as $dkey => $dvalue)
        {
          $computed_value = $dvalue;
          if (!is_numeric($computed_value)) 
          {
            $computed_value = '"'.$computed_value.'"';
          }
          $new_value = preg_replace('/`?'.($model->alias).'`?\.`?'.($dkey).'`?/', $computed_value, $value);
          $new_value = preg_replace('/[^\.]`?'.($dkey).'`?/', $computed_value, $new_value); 
          $conditions[$key] = $new_value;
        }
      }
      return $conditions;
    }
    /**
     * updateResult
     *
     * @param array $conditions conditions to change
     * @param array $data model data (virtual fields and table data) TODO: should be only virtual?
     * @param AppModel $model model name (its alias is important - but You dont have to use this alias in conditions)
     * @access private
     */
    function _updateResult($results, &$model, &$foreignModel, $field, $params)
    {
      $grouped_model_id = $results[$model->alias][$model->primaryKey];
      $mconds = array("{$foreignModel->alias}.{$params['foreignKey']}" => $grouped_model_id);
      //TODO: recursion (needs to call beforeFind method in a find method that was called in beforeFind method â€“ difficult, and useless) 
      if (VALUATE_ALREADY_COMPUTED)
      {
        $params['conditions'] = $this->_valuateAlreadyComputed($params['conditions'], $results, $model);
      }
      $new_record = $foreignModel->find(array($mconds, $params['conditions']), "{$params['function']}"); 
      
      
      $db =& ConnectionManager::getDataSource($model->useDbConfig);
      $new_value = $this->_extractNewValue($new_record, $foreignModel, $db->name($params['function']));
      $results[$model->alias][$field] = $new_value; 
      return $results;
    }
    /**
     * Extracts aggregated field from a set ($data)
     * @param array $data
     * @param $model $data was extracted from it
     * @param $function field name, or an sql function
     * @return mixed an extracted value
     */
    function _extractNewValue($data, $model, $function){             
      $model_alias = $model->alias;
      if (empty($data[$model_alias]))
      {
        $model_alias = 0;
      }
      if (empty($data[$model_alias][$function]))
      {
        $function_parts = explode('.', $function);
        if (trim($function_parts[0], '`') === $model_alias)
        {
        // if $function is a table field name
          $function = trim($function_parts[1], '`');
        }  
        else
        {
          //if $function is an sql function  (for CakePHP 1.2 RC 2, because it wraps around an SQL function with '`')
          $function = trim($function, '`');
        }    
      }
      $new_value = $data[$model_alias][$function];
      return $new_value;
    }  
    /**
     * UpdateAllResults
     *
     * @param array $results 
     * @param AppModel &$model 
     * @param AppModel &$foreignModel
     * @param string $field 
     * @param array $params
     * @return array 
     * @access private
     */
    function _updateAllResults($results, &$model, &$foreignModel, $field, $params)
    {
      if (Set::check($results, "0.{$model->name}.id"))
      {
      foreach ($results as $key => $result)
      {
        $results[$key] = $this->_updateResult($result, $model, $foreignModel, $field, $params);
      } 
        return $results;
      }
      else
      {
        return $this->_updateResult($results, $model, $foreignModel, $field, $params);
      }
    }
    /**
     * Updates All virtual fields of a model
     *
     * @param AppModel &$model 
     * @param array $results
     * @return boolean true on success, false on failure
     * @access public
     */
    function updateVirtualAggregators(&$model, $results = array())
    {
      extract($this->settings[$model->alias]); 
      if (!empty($model->aggregatingFields))
      {
        $aggregatingFields = (array)($model->aggregatingFields); 
        foreach($aggregatingFields as $field => $params)
        {
          if ($params['mode'] == 'virtual')
          {
            $foreignModelName = $params['model'];
            if (!class_exists($foreignModelName))
            {
              App::import('Model', $foreignModelName);
            } 
            $foreignModel =& new $foreignModelName();
            $results = $this->_updateAllResults($results, $model, $foreignModel, $field, $params);
          }
        } //foreach
      } //if 
      return $results;
    }
    /**
     * After find callback. Can be used to modify any results returned by find and findAll.
     *
     * @param object $model Model using this behavior
     * @param mixed $results The results of the find operation
     * @param boolean $primary Whether this model is being queried directly (vs. being queried as an association)
     * @return mixed Result of the find operation
     * @access public
     */
    function afterFind(&$model, $results, $primary) 
    {
      $results = $this->updateVirtualAggregators($model, $results);
      return $results;
    }
    
    }
    ?>



How to use it: Aggregator Behavior
``````````````````````````````````

Model Comment:

Model Class:
````````````

::

    <?php 
    class Comment extends AppModel { 
      var $name = 'Comment'; 
      var $belongsTo = array('Post');
    } 
    ?>

Model Post:

Model Class:
````````````

::

    <?php 
    class Post extends AppModel { 
      var $name = 'Post'; 
      var $actsAs = array('Aggregator');
      var $aggregatingFields = array(
        'comment_count' => array(
          'mode' => 'virtual', 
          'key' => 'id',
          'foreignKey' => 'comment_id',
          'model' => 'Post', 
          'function' => 'CoUNT(Comment.id)', 
        ),
        'last_comment_id' => array(
          'mode' => 'virtual',
          'key' => 'id',
          'foreignKey' => 'post_id',
          'model' => 'Comment', 
          'function' => 'MAX(Comment.id)',
          'limit' => 1 
        )
      ); 
    } 
    ?>

SQL:

::

    
    CREATE TABLE `post` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `text` TEXT NOT NULL ,
    `user_id` INT UNSIGNED NOT NULL,
    `comment_count` INT UNSIGNED NOT NULL,
    `last_comment_count` INT UNSIGNED NOT NULL
    );
    
    CREATE TABLE `comment` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `post_id` INT UNSIGNED NOT NULL,
    `text` TEXT NOT NULL
    );

Code snippet:

Controller Class:
`````````````````

::

    <?php 
    $post1 = array('Post' => array('text' => 'first post', 'user_id' => 1));
    $this->Post->save($post1);
    
    $comment = array('Comment' => array('text' => 'OK'));
    $this->Comment->save($comment);
    $comment->create();
    $comment = array('Comment' => array('text' => 'Not OK'));
    $this->Comment->save($comment);
    
    $post2 = $this->Post->find();
    pr($post2);
    ?>

As before above code will print out:

::

    
    array(
      0 => array(
        'Post' => array(
          'id' => 1,
          'text' => 'first post',
          'user_id' => 1,
          'comment_count' => 2,
          'last_comment_id' => 2
        )
      ) 
    )

Note that the 'comment_count' and 'last_comment_id' fields are not
defined in the database.



Summary
~~~~~~~
Aggregator Behavior and Aggregatable Behavior have more features. You
can for example use additional conditions in aggregatingFields array,
or use "virtual" field name in SQL function of another "virtual"
field. For example You can add to above code (Aggregatable) a new
'virtual' field:

::

    
    last_comment_text = array(..., 'function' => 'Comment.text', 'condition' => 'Comment.id = Post.last_comment_id'...))

Note, that last_comment_id doesn't exist in the database.

I will write more advanced article about it soon. I am still working
on these behaviors (from time to time) so new features will appear. I
think for example, that it would be useful to implement functionality
of recursive use of Aggregatable Behavior (to use it with a Tree
Behavior), or make it possible to define only those properties that
are really needed ('mode' can be set automatically, after determining
if the field exists in the database; 'model' and 'function' can be
extracted from field name, etc.).
But until then ...


Few tips
~~~~~~~~
When You Use Aggreagator Behavior and Aggregatable Behavior You should
remember about few things

+ Aggregator Behavior is used for "virtual" fields
+ Aggregating model (Post) acts as Aggregator
+ Aggregatable Behavior is used for "real" fields
+ Aggregated model (Comment) acts as Aggregatable
+ aggregating fields are defined in aggregating model (Post) in an
  'aggregatingFields' array (both 'virtual' ones, and 'real' ones)


.. meta::
    :title: Aggregator & Aggregatable Behaviors: if You think that You need a GROUP BY statement
    :description: CakePHP Article related to virtual fields,totalizer,Behaviors
    :keywords: virtual fields,totalizer,Behaviors
    :copyright: Copyright 2008 
    :category: behaviors

