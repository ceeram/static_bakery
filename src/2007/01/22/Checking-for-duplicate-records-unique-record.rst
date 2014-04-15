Checking for duplicate records (unique record)
==============================================

by maruska on January 22, 2007

Expanded tutorial from CakePHPWiki:
http://wiki.cakephp.org/tutorials:duplicate_record_validation [list]
[li]validate a form field (such as a user name field), [b]both in add
and edit form[/b] and make sure that the selected user name does not
already exist in the database[/li] [li]function [b]repeated only
once[/b] (in app/app_model.php)[/li] [/list]
In app/app_model.php:

Model Class:
````````````

::

    <?php 
    class AppModel extends Model {
    
    function isUnique($field, $value, $id)
        {
            $fields[$this->name.'.'.$field] = $value;
            if (empty($id))
                // add 
                $fields[$this->name.'.id'] = "<> NULL"; 
            else
                // edit
                $fields[$this->name.'.id'] = "<> $id"; 
            
            $this->recursive = -1;
            if ($this->hasAny($fields))
            {
                $this->invalidate('unique_'.$field); 
                return false;
            }
            else 
                return true;
       }
    
    ?>

isUnique($field, $value, $id) - $field: name of field (e.g.
'username'); $value: value of field (e.g.
this->data['User']['username']); $id - id line which is edited, when
"add form" then $id = null;

Example of select (the result of the above function):
SELECT COUNT(*) AS count FROM `users` AS `User` WHERE
(`User`.`username` = 'por') AND (`User`.`id`
1)


Controller Class:
`````````````````

::

    <?php 
    if ($this->User->isUnique('username', $this->data['User']['username'], $user_id))
        { 
            if ($this->User->save($this->data))
            {
               $this->flash('User has been saved.','/users/index');
            }
         }
    ?>



View Template:
``````````````

::

    
    <?php echo $html->tagErrorMsg('User/unique_username', 'Enter another username, this is already used.'); ?>
    <?php echo $html->input('User/username', array('size' => '30'))?

The message with name Model/unique_fieldname is display, when isUnique
returns false.


.. author:: maruska
.. categories:: articles, tutorials
.. tags:: unique record,duplicate records,Tutorials

