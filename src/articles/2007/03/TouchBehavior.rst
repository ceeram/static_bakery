TouchBehavior
=============

by polk on March 19, 2007

Would You like to have method touch() for some models, which updates
only one field in database table and leaves 'created' and 'modified'
(and all other) fields untouched?


Installation
~~~~~~~~~~~~

...is simple. If You're using CakePHP 1.2, then

#. download (or copy && paste) TouchBehavior and put it into file
   app/models/behaviors/touch.php
#. customize some database table so it contains last_access datetime
   field
#. in related model define

::

    
        var $actsAs = array(
            'Touch' => array()
        );





Usage
~~~~~

If Your model has initialized $id property (after read/insert/update),
then call in controller (for touch of current record)

::

    
    $this->ModelName->touch();



If You want to touch some other record with known id, then call in
controller

::

    
    $this->ModelName->touch($someId);



As You can see in code of TouchBehavior, You can use any field name -
just define $acstAs property in Your model like

::

    
        var $actsAs = array(
            'Touch' => array('field' => 'my_own_field_name')
        );



Field 'last_access' (or custom one) will be also properly filled up in
every insert/save, but beware: if You're using argument $fieldList in
model's save() calls then don't omit Your 'last_access' field in this
array.



Behavior Class:
```````````````

::

    <?php 
    /**
     * TouchBehavior
     * 
     * Usage in model: 
     * 
     * 1) make sure model's $actsAs array contains something like (those keys are
     * default values You don't need to specify)
     * 
     * 'Touch' => array('field' => 'last_access', 'timestamp'=>'Y-m-d H:i:s')
     * 
     * 2) You can call $this->ModelName->touch() or $this->ModelName->touch(3) in
     * controller
     * 
     * 3) this behavior implements beforeSave() callback for updating value of last
     * access field ;)
     */
    class TouchBehavior extends ModelBehavior {
        /**
         * Default model settings
         */
        var $defaultSettings = array('field' => 'last_access', 'timestamp' => 'Y-m-d H:i:s');
    
        /**
         * Prepare model settings
         * 
         * Redefines parent method
         */
        function setup(&$model, $config = array()) {
            $field = $this->defaultSettings['field'];
    
            if (!empty($config['field'])) {
                $field = $config['field'];
            }
    
            // conditional initialization of settings for this model
            if ($model->hasField($field)) {
                $timestamp = $this->defaultSettings['timestamp'];
                
                if (!empty($config['timestamp'])) {
                    $timestamp = $config['timestamp'];
                }
    
                $this->settings[$model->name] = array(
                    'field' => $field, 
                    'timestamp' => $timestamp
                );
            }
        }
    
        /**
         * Updates only field for last access information
         */
        function touch(&$model, $id = null) {
            if (isset($this->settings[$model->name])) {
                if (!empty($id)) {
                    $model->id = $id; 
                }
    
                if (!empty($model->id)) {
                    $field = $this->settings[$model->name]['field'];
    
                    // uses $fieldList argument, so data can be prepared in beforeSave()
                    return $model->save(null, false, array($field));
                }
            }
            
            return false;
        }
    
        /**
         * Modify last access field on every save
         * 
         * Redefines parent method
         */
        function beforeSave(&$model) {
            if (isset($this->settings[$model->name])) {
                $field = $this->settings[$model->name]['field'];
                $timestamp = $this->settings[$model->name]['timestamp'];
    
                $model->data[$model->name][$field] = date($timestamp);
            }
        }
    }
    ?>


.. meta::
    :title: TouchBehavior
    :description: CakePHP Article related to actsas,behavior,beforeSave,touch,Behaviors
    :keywords: actsas,behavior,beforeSave,touch,Behaviors
    :copyright: Copyright 2007 polk
    :category: behaviors

