Multiple Validation Rules
=========================

by %s on May 18, 2011

I had a few different forms and needed different validation rules for
each. For example an administrator could insert any type of data about
a client, but a client would be required to insert all their
information.


PHP Snippet:
````````````

::

    <?php 
    class CheckValidationRulesBehavior extends ModelBehavior {    
        function setup(&$model) {
            // We do this in setup so the form reflects the changes 
            // of the model's custom validation
            $varName = 'validate' . Inflector::camelize(Router::getParam('action'));
            
            if(property_exists($model, $varName)) {
                $model->validate = $model->{$varName};
            }
        }
    }
    ?>

This is a very simple behavior to do this.

Basically it checks your action name, and if a variable is set with
rules for that action, it takes those instead of the default
validation.

.. meta::
    :title: Multiple Validation Rules
    :description: CakePHP Article related to model,validation,multiple,custom,many,Behaviors
    :keywords: model,validation,multiple,custom,many,Behaviors
    :copyright: Copyright 2011 
    :category: behaviors

