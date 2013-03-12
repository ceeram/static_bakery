

Alternative Methods for Working with hasAndBelongsToMany Associations
=====================================================================

by %s on January 15, 2006

As of 1.1.12.4205, CakePHP's current functionality for working with
HABTM associations works very well for most applications, particularly
those that lend themselves well to using multiple-selectTags or
checkboxes. This article looks at proposed methods for adding or
removing join records between two models that don't rely on bulky
arrays and that minimize the amount of SQL executed on the database.
This article offers proposed code to be added either to the AppModel
or Model level which can be used for establishing join records between
two models in a simple, lean fashion for applications that find
themselves limited by Model::save.

First, a little background to justify the need; code will follow,
ending with a tutorial on page 2:

For hasAndBelongsToMany associations, CakePHP has built into
Model::save the functionality to store the appropriate join records.
However, there are two limitations to the Model::save methods.

First, Model::save requires that all associatedForeignKeys be passed
to the model. This lends itself very well to using multiple select
tags; however, thse are sometimes impractical when the number of joins
or even the number of possible joins between two models is very large.
The view elements themselves become quite daunting to the user. Also,
the controller has to query the preexisting joins to pre-populate the
selectTags or in the very least, provide Model::save with all the
necessary data.

The second limitation is that Model::_saveMulti, which is used by
Model::save to store these joins, works by deleting all join records
and reinserting the join records for each element found in
$this->data['assocModel']['assocModel']. Again, when the number of
joins between two models is very large, a large number of INSERTs are
executed on the database unnecessarily.

There are alternatives like RosSoft's method in ticket `#1348`_ which
take care of the first limitation with using multiple select tags;
however, this method still pulls down all the preexisting joins to
pass to Model::save with the insert, and the second limitation is
still a problem.

One-at-a-time inserts and deletes become necessary for some
applications requiring non-array based view elements and/or for
applications requiring better performance from the database. The
following code proposal is my attempt at rewriting RosSoft's code. The
only major difference in functionality is that $id is now optional,
just like in Model::read() if the developer chooses to first store the
id into Model->id. In fact, the code for this was taken directly from
Model::read. The rest of the code is largely based on
Model::_saveMulti and I have done my best to mimic the coding style
and conventions.

This code could be placed into app_model by users who find themselves
here or could (hopefully) be included, probably in some modified
fashion, in the core model class as an enhancement (`I'll keep my
fingers crossed`_).


Model Class:
````````````

::

    <?php 
        /**
         * Adds a join record between two records of a hasAndBelongsToMany association
         *
         * @param mixed $assoc The name of the HABTM association
         * @param mixed $assoc_ids The associated id or an array of associated ids
         * @param integer $id The id of the record in this model
         * @return boolean Success
         */
        function addAssoc($assoc, $assoc_ids, $id = null)
        {
            if ($id != null) {
                $this->id = $id;
            }
    
            $id = $this->id;
    
            if (is_array($this->id)) {
                $id = $this->id[0];
            }
            
            if ($this->id !== null && $this->id !== false) {
                $db =& ConnectionManager::getDataSource($this->useDbConfig);
                
                $joinTable = $this->hasAndBelongsToMany[$assoc]['joinTable'];
                $table = $db->name($db->fullTableName($joinTable));
                
                $keys[] = $this->hasAndBelongsToMany[$assoc]['foreignKey'];
                $keys[] = $this->hasAndBelongsToMany[$assoc]['associationForeignKey'];
                $fields = join(',', $keys);
                
                if(!is_array($assoc_ids)) {
                    $assoc_ids = array($assoc_ids);
                }
            
                // to prevent duplicates
                $this->deleteAssoc($assoc,$assoc_ids,$id);
                
                foreach ($assoc_ids as $assoc_id) {
                    $values[]  = $db->value($id, $this->getColumnType($this->primaryKey));
                    $values[]  = $db->value($assoc_id);
                    $values    = join(',', $values);
                    
                    $db->execute("INSERT INTO {$table} ({$fields}) VALUES ({$values})");
                    unset ($values);
                }
                
                return true;
            } else {
                return false;
            }
        }
    
        /**
         * Deletes any join records between two records of a hasAndBelongsToMany association
         *
         * @param integer $id The id of the record in this model
         * @param mixed $assoc The name of the HABTM association
         * @param mixed $assoc_ids The associated id or an array of associated ids
         * @return boolean Success
         */
        function deleteAssoc($assoc, $assoc_ids, $id = null)
        {
            if ($id != null) {
                $this->id = $id;
            }
    
            $id = $this->id;
    
            if (is_array($this->id)) {
                $id = $this->id[0];
            }
            
            if ($this->id !== null && $this->id !== false) {
                $db =& ConnectionManager::getDataSource($this->useDbConfig);
                
                $joinTable = $this->hasAndBelongsToMany[$assoc]['joinTable'];    
                $table = $db->name($db->fullTableName($joinTable));
                
                $mainKey = $this->hasAndBelongsToMany[$assoc]['foreignKey'];
                $assocKey = $this->hasAndBelongsToMany[$assoc]['associationForeignKey'];
                
                if(!is_array($assoc_ids)) {
                    $assoc_ids = array($assoc_ids);
                }
                
                foreach ($assoc_ids as $assoc_id) {
                    $db->execute("DELETE FROM {$table} WHERE {$mainKey} = '{$id}' AND {$assocKey} = '{$assoc_id}'");
                }
                
                return true;
            } else {
                return false;
            }
        }
    ?>


Using the code described on the previous page is quite simple. Once
you've got it loaded into your app_model, you can do something like
the following.

Let's say you have:

User hasAndBelongsToMany UserType

A really simple model function from within your users_controller.php
would look something like this:


Controller Class:
`````````````````

::

    <?php 
    function assign_user_type($user_id, $user_type_id) {
        $this->User->id = $user_id;
    
        //alternatively, you can skip the line above and make the following line
        //$this->User->addAssoc('UserType',$user_type_id,$user_id);
        $this->User->addAssoc('UserType',$user_type_id);
    
        $this->setFlash('User has been assigned.');
        $this->redirect('/users/assignments');
    }
    
    function unassign_user_type($user_id, $user_type_id) {
        $this->User->id = $user_id;
    
        //alternatively, you can skip the line above and make the following line
        //$this->User->deleteAssoc('UserType',$user_type_id,$user_id);
        $this->User->deleteAssoc('UserType',$user_type_id);
    
        $this->setFlash('User has been unassigned.');
        $this->redirect('/users/assignments');
    }
    ?>

Your view could then have code that looked like this:


View Template:
``````````````

::

    
    <!-- we're going to assume you've already defined in your view somewhere the function is_assigned() -->
    <?php foreach($user_types as $user_type): ?>
        <BR><?php echo $user_type['UserType']['name']; ?>
            <?php if(is_assigned($user,$user_type): ?>
                <?php echo $html->link('Unassign',"/users/unassign_user_type/{$user['User']['id']}/{$user_type['UserType']['id']}");
            <?php else: ?>
                <?php echo $html->link('Assign',"/users/unassign_user_type/{$user['User']['id']}/{$user_type['UserType']['id']}");
            <?php endif; ?>     
    <?php endforeach;?>

This example uses links, but you could really do whatever, including
continuing to use multiple selects. An example might be a selectTag,
an Add button, and a table. The table would be populated with
UserTypes already assigned to the User, and the selectTag would
contain those that have not. When the user selects one or more
elements from the multi-select and hits add, the controller gets back
somewhere within $this->data an array of ids for the associated model.
Just stick that array of ids into the $assoc_ids parameter of addAssoc
and deleteAssoc and it'll add each one to the joins for that model
without rebuilding the whole group.

Enjoy!
`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _I'll keep my fingers crossed: https://trac.cakephp.org/ticket/1845
.. _Page 1: :///articles/view/4caea0dd-a0b8-4c76-ac5e-43ac82f0cb67/lang:eng#page-1
.. _Page 2: :///articles/view/4caea0dd-a0b8-4c76-ac5e-43ac82f0cb67/lang:eng#page-2
.. _#1348: https://trac.cakephp.org/ticket/1348
.. meta::
    :title: Alternative Methods for Working with hasAndBelongsToMany Associations
    :description: CakePHP Article related to hasAndBelongsToMany,HABTM,Models
    :keywords: hasAndBelongsToMany,HABTM,Models
    :copyright: Copyright 2006 
    :category: models

