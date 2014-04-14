Subclass Behavior
=================

by eldonbite on September 18, 2008

This behavior simulates an Entity-Relationship database model's ISA
relationship and which demonstrates the subclassing concept inherent
in OOP. This Cake behavior class was actually based on and inspired by
the Extendable Behavior written by Matthew Harris which can be found
at http://bakery.cakephp.org/articles/view/extendablebehavior . Feel
free to check it out, use it, and help me improve it and fix bugs if
you've found any. Happy baking!


About Me
;;;;;;;;

I'm currently working as a Software Engineer in a Web development
company and I've started using CakePHP during my first month at job. I
enjoyed using the framework primarily due to the RAD methods that it
offers and also because of its flexibility. I've developed some Cake
scripts including a component for validating uploaded files with
virus-checking capabilities for one of our projects. After a few
months of being a newbie baker, I've decided to give something back to
the community by posting some of my discoveries and crafts and
continuing to develop helpful scripts for the CakePHP framework.



BACKGROUND
``````````

The object-oriented programming paradigm provides the ability for new
classes to inherit properties from another class. This concept became
necessary for me since we had to design a database with different
types of users. For example, a User which is a Buyer , or a User which
is a Seller . Wait a minute: "is a" ? For those developers familiar
with database models, there is a relationship between tables known as
the " ISA relationship" wherein a class can have specialization
classes or, in OO terms, subclasses.

The original purpose of the Subclass Behavior is to provide a way for
other models to inherit the associations present in another model.
Let's say, a User hasOne Profile and hasMany Testimonials , then a
Buyer actsAs a Subclass of User , then Buyer now also hasOne Profile
and hasMany Testimonials . Sweet. =D

Oh well, to cut the long story short, here are the codes and some
examples:


The SubclassBehavior class
++++++++++++++++++++++++++

Model Class:
````````````

::

    <?php 
    /** 
     * The SubclassBehavior allows a model to act as a subclass of another model and
     * allows the implementation of 'ISA' relationships in Entity-Relationship database models.   
     * Parameters are passed to this behavior class to define the parent model of the subclass
     * This class was based on and inspired by Matthew Harris's ExtendableBehavior class
     * which can be found at http://bakery.cakephp.org/articles/view/extendablebehavior 
     * 
     * @author 		Eldon Bite <eldonbite@gmail.com> 
     * @license   http://www.opensource.org/licenses/mit-license.php The MIT License 
     */ 
    class SubclassBehavior extends ModelBehavior { 
        /** 
         * The parent model being extended by the current model 
         * 
         * @var Object 
         */ 
        var $parentClass;
         
        /** 
         * The name of the type column, default 'type' 
         * 
         * @var string 
         */ 
        var $typeField;
        
        /** 
         * Alias for the subclass/type model 
         * 
         * @var string 
         */ 
        var $typeAlias;
         
        /** 
         * Set up the behavior. 
         * Finds parent model and determines type field settings. 
         */ 
        function setup(&$model, $config = array()) {
            $this->settings  = am(array('typeField' => 'type', 'typeAlias' => $model->alias), $config);
            $this->parentClass = $this->__getparentClass($this->settings['parentClass']);
            $this->typeField = $this->settings['typeField'];
            $this->typeAlias = $this->settings['typeAlias'];
            // Bind model associations on the fly
            foreach ($model->__associations as $assoc) {
                    foreach ($this->parentClass->$assoc as $key => $value) {
    		        $model->bindModel(array($assoc => array($key)));
                    }
            }
        }
         
        /** 
         * Filter query conditions with the correct `type' field condition. 
         */ 
        function beforeFind(&$model, $queryData) 
        {
            if (array_key_exists($this->typeField, $model->_schema) && $model->alias != $this->parentClass->alias) {
                if (!isset($queryData['conditions'])) {
                    $queryData['conditions'] = array();
                }
                if (is_string($queryData['conditions'])) {
                    if (strlen(trim($queryData['conditions']))) {
                        $queryData['conditions'] = "({$queryData['conditions']}) AND ";
                    }
                    $queryData['conditions'] .= $model->alias.'.'.$this->typeField.' = '.$this->typeAlias;
                }
                elseif (is_array($queryData['conditions'])) { 
                    if (!isset($queryData['conditions'][$model->alias.'.'.$this->typeField])) {
                        $queryData['conditions'][$model->alias.'.'.$this->typeField] = array(); 
                    }
                    $queryData['conditions'][$model->alias.'.'.$this->typeField] = $this->typeAlias;
                }
                 
            }
            return $queryData;
        }
        
        /** 
         * Set the `type' field before saving the record. 
         */ 
        function beforeSave(&$model) 
        { 
            if (array_key_exists($this->typeField, $model->_schema) && $model->alias != $this->parentClass) { 
                if (!isset($model->data[$model->alias])) { 
                    $model->data[$model->alias] = array(); 
                } 
                $model->data[$model->alias][$this->typeField] = $model->alias; 
            } 
            return true;
        }
         
        /** 
         * Get the parent model of the subclass.  
         * 
         * @param		string Parent model name
         * @return	object Parent model 
         */ 
        function __getparentClass($parentClass) 
        {
            App::import('model', $parentClass);
            return new $parentClass;
        } 
    } 
    ?>



Example parent model
++++++++++++++++++++

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	
    	var $name = 'User';
    	var $hasOne = 'Profile';
    	var $hasMany = array(
    		'Friend',
    		'Testimonial'
    	);
    	var $hasAndBelongsToMany = 'Category';
    
    }
    ?>



Example model which uses the Subclass Behavior
++++++++++++++++++++++++++++++++++++++++++++++

Model Class:
````````````

::

    <?php 
    class Jerk extends AppModel {
    
    	var $name = 'Jerk';
    	var $useTable = 'users';
    	var $hasMany = 'Girlfriend';
    	var $actsAs = array(
    		'Subclass' => array(
    			'parentClass' => 'User',
    			'typeAlias' => '1',
    		)
    	);
    	
    }
    ?>



CONFIGURATION
`````````````

We still need to set the subclassing model's $useTable property to be
the same as the one being used by its parent. The Subclass behavior
accepts the following parameters:

+ parentClass : the name of the model being subclassed
+ typeField : the name of the type column; default 'type' (just like
  in ExtendableBehavior)
+ typeAlias : an alias for the value stored in the database as the
  model's type (defaults to the model's alias)



LIMITATIONS
```````````

As of now, the model implementing the Subclass behavior cannot inherit
user-defined functions from its parent model. Also, the subclass
cannot inherit custom configurations from the associations in its
parent (i.e. foreignKey is different from the default). I'll try
adding them soon, or if anyone else does, feel free to do so. =)



FUTURE UPDATES
``````````````

While progressing through our company's project, I realized something
that I would like to add to this behavior class. I'd want to be able
to assign a model as a subclass of another without regards to its
subclass type. For example, a User has 2 types: a Buyer and a Seller .
A User , whether it is a Buyer or a Seller , hasMany Messages and can
receive messages from a Sender (also a model), which is also basically
a User . Thus, the table `messages` has a column named `sender_id` to
identify which User sent the message, whether he is a Buyer or a
Seller . Quite difficult to explain but I think you get what I mean,
LOL. Anyway, I'll try implementing it later. ;p



HAPPY BAKING! ;-)
;;;;;;;;;;;;;;;;;


.. meta::
    :title: Subclass Behavior
    :description: CakePHP Article related to database,behavior,isa relationship,inheritance,object oriented,subclass,Behaviors
    :keywords: database,behavior,isa relationship,inheritance,object oriented,subclass,Behaviors
    :copyright: Copyright 2008 eldonbite
    :category: behaviors

