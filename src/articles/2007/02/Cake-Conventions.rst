Cake Conventions
================

by sajt on February 25, 2007

This is the Cake Conventions from the old wiki site.

#. tables names are plural and lowercased
#. model names singular and CamelCased: ModelName
#. model filenames are singular and underscored: model_name.php
#. controller names are plural and CamelCased with *Controller*
   appended: ControllerNamesController
#. controller filenames are plural and underscored with *controller*
   appended: controller_names_controller.php
#. associations should use the ModelName, and the order should match
   the order of the foreignKeys: var $belongsTo = 'User';
#. foreign keys should always be: table_name_in_singular_form_id:
   user_id (foreign key) -> users (table)
#. many-to-many join tables should be named:
   alphabetically_first_table_plural_alphabetically_second_table_plural
   ie: tags_users
#. columns in many-to-many join tables should be named like other
   foreign keys ie: tag_id and user_id
#. columns named created and modified will automatically be populated
   correctly
#. components should be CamelCased: MyComponent : my_component.php :
   var $components = array('MyComponent'); $this->MyComponent->method();
#. helpers should be CamelCased: MyHelper: my_helper.php: var $helpers
   = array('MyHelper'); $myHelper->method();


.. meta::
    :title: Cake Conventions
    :description: CakePHP Article related to Cake Conventions,General Interest
    :keywords: Cake Conventions,General Interest
    :copyright: Copyright 2007 sajt
    :category: general_interest

