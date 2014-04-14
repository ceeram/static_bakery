ExtendableBehavior
==================

by kuja on June 12, 2008

ExtendableBehavior allows you to subclass models and use them to
categorize different types of model data in the same table.


The Problem
```````````
Ever made an application that had different types of users, but
referred to the same users table in your database? Maybe you had a
table called "users" and a model called "User"â€¦

Well, if you're like me, you may have had similar cases where you
wanted to have separate classes for your same users table, that would
distinguish the type of user for you.

I had recently been writing an application that had a User, Developer
and Designer model classes. I didn't want to create a separate table
to manage the types of users, nor did I want to always refer to a
particular field in my User model when I wanted to find/save a
Developer or Designer record.

Instead, I wanted to be able to subclass the User model and simply use
that class to select the correct record set. e.g., if I did
`$this->Developer->findAll()', then I'd want to select all users who
are of type Developer. How did I do this without creating a separate
table? Well, I used the ExtendableBehavior!


The Solution
````````````
The ExtendableBehavior class allows you to transparently find and save
different types of data to the same table without using the same model
class.

The default behavior for this is that it uses the `type' field in your
database table to identify the model alias (usually the model class
name itself). If you have a Developer and Designer class (which
extends the User class) and did a findAll() on both model classes, you
would be selecting from the same "users" table by finding only the
results with the correct `type' field valueâ€¦ for the Developer
model, the `type' field would be set to "Developer", and "Designer"
for the Designer model.


The Code
````````
All this talk is probably already boring you, and at this point you're
probably grinning at your monitor screen wondering when I'm going to
shut up. Well then, here's the code :)

2008-06-12 UPDATE (works with RC1 now):

Model Class:
````````````

::

    <?php 
    /** 
     * The ExtendableBehavior allows a model to record its "type" into the database 
     * table.  If you created a model and then created another model which inherits 
     * from the model you previously created, then the extending class' model name 
     * will be recorded in the database table's designated `type' column. 
     * 
     * You must apply this behavior through the $actsAs property on your model, and 
     * any children models must define a $useTable property that corresponds to their 
     * parent, otherwise finding/saving will not work. 
     * 
     * The only possible configuration option this behavior accepts is the 
     * `typeField' key, which should be named something to your liking. By default 
     * this will be set to 'type'. 
     * 
     * @author Matthew Harris <shugotenshi@gmail.com> 
     * @license    http://www.opensource.org/licenses/mit-license.php The MIT License 
     */ 
    class ExtendableBehavior extends ModelBehavior { 
        /** 
         * The most root class that has extended AppModel.  This class acts as 
         * the parent and doesn't have its `type' recorded in the table. 
         * 
         * @var string 
         */ 
        var $rootClass; 
         
        /** 
         * The name of the type column, default 'type' 
         * 
         * @var string 
         */ 
        var $typeField; 
         
        /** 
         * Set up the behavior. 
         * Finds root class and determines type field settings. 
         */ 
        function setup(&$model, $config = array()) { 
            $this->settings  = am(array('typeField' => 'type'), $config); 
            $this->rootClass = $this->__getRootClassName($model); 
            $this->typeField = $this->settings['typeField']; 
        } 
         
        /** 
         * Filter query conditions with the correct `type' field condition. 
         */ 
        function beforeFind(&$model, $queryData) 
        {
            if (array_key_exists($this->typeField, $model->_schema) && $model->alias != $this->rootClass) {
                if (!isset($queryData['conditions'])) {
                    $queryData['conditions'] = array();
                }
                
                if (is_string($queryData['conditions'])) {
                    if (strlen(trim($queryData['conditions']))) {
                        $queryData['conditions'] = "({$queryData['conditions']}) AND ";
                    }
                    $queryData['conditions'] .= $this->alias.'.'.$this->type.' = '.$this->value($model->alias);
                }
                elseif (is_array($queryData['conditions'])) { 
                    if (!isset($queryData['conditions'][$model->alias.'.'.$this->typeField])) {
                        $queryData['conditions'][$model->alias.'.'.$this->typeField] = array(); 
                    }
                    $queryData['conditions'][$model->alias.'.'.$this->typeField] = $model->alias;
                }
                 
            }
            return $queryData; 
        } 
         
        /** 
         * Set the `type' field before saving the record. 
         */ 
        function beforeSave(&$model) 
        { 
            if (array_key_exists($this->typeField, $model->_schema) && $model->alias != $this->rootClass) { 
                if (!isset($model->data[$model->alias])) { 
                    $model->data[$model->alias] = array(); 
                } 
                $model->data[$model->alias][$this->typeField] = $model->alias; 
            } 
            return true; 
        } 
         
        /** 
         * Get the uppermost parent class name that an extending model inherits from. 
         * This does not include AppModel, that's where the search stops. 
         * 
         * @return string Parent class name 
         */ 
        function __getRootClassName(&$model) 
        { 
            $parent = $current = get_class($model); 
             
            while (strtolower($current) != 'appmodel') { 
                $parent  = $current; 
                $current = get_parent_class($current); 
            } 
            return $parent; 
        } 
    } 
    ?>

I hope you find this behavior useful, and as usual, leave feedback in
the comments area please! ;)

.. meta::
    :title: ExtendableBehavior
    :description: CakePHP Article related to ,Behaviors
    :keywords: ,Behaviors
    :copyright: Copyright 2008 kuja
    :category: behaviors

