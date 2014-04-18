Coding a self-join in a cake application
========================================

This tutorial shows how to code your model, controller and views in
case of a self join. A self-join is a query in which a table is joined
(compared) to itself. Self-joins are used to compare values in a
column with other values in the same column or another column in the
same table. A self join is neccasarry when a database table has has a
column that references its own table, for example the table parts has
a column that references the table itself (parent-id -> table-id; a
part exists of sub-parts).
For this tutorial I used bake or masterbake for generating the code.
The Bake- or Masterbake-script do not always generated the code for
the model with the right relationships. So first I bake the model.
Secondly I modify the model by hand. Then I bake the rest of the code.
And finally I modify the code with references to the right array or
variable.

This tutorial shows parts of the generated and tweaked code for the

#. database definition
#. the code for the model
#. the code for the controller
#. the view



1 database definition
---------------------
The database-table in this tutorial references itself also. A nic
(network interface) can have a relation with another nic, a parent
nic. Or the other way around, a nic can have a relation ship with
zero, one or more nics, child nics.

Check out the table definition....

::

    
    CREATE TABLE nics (
      id SMALLINT(6) NOT NULL AUTO_INCREMENT,
      parent_id SMALLINT(6), 
      Nic_name VARCHAR(8) NOT NULL,
      Nic_type VARCHAR(32) NOT NULL,
      Nic_mac VARCHAR(17) NOT NULL,
      alias_address_count TINYINT(4) NOT NULL,
      slave_nic_count TINYINT(4) NOT NULL,
      PRIMARY KEY(id),
      INDEX nic_info_FKIndex1(parent_id),
      FOREIGN KEY(parent_id)
        REFERENCES nics(id)
          ON DELETE CASCADE
    );

As you see, the reference to a parent-nic, a record of database-tabel
itself, is done with the column parent_id.



2 The model
-----------
The code of the model for the table nics is...

Model Class:
````````````

::

    <?php 
    class Nic extends AppModel {
    
     var $name = 'Nic';
     var $validate = array(
    		'machine_id' => VALID_NOT_EMPTY,
    		'Nic_name' => VALID_NOT_EMPTY,
             	);
     var $displayField = 'Nic_name'; 
    	
     var $belongsTo = array(
            'Parentnic' =>
                array('className' => 'Nic',
                      'foreignKey' => 'parent_id'
    	    ),
     	);
    
     var $hasMany = array(
    	'Childnic' =>
                array('className' => 'Nic',
    		  'foreignKey' => 'parent_id'
                ),
    	);
    }
    ?>

As you can see there is a belongsto-relationship to a parent-nic and a
hasmany-relationship to child-nics. The name of the array show what
kind of relation-ship it is. It is important tho define the belongsto-
relation in order to be able to walk through the array in your
controller or views in a proper way.



3 The Controller
----------------
In my application I use Masterbake. The code in the index function for
building the query does not include the table-name in the where
clause. So I modified that part.
I will show a part of the function index() in the controller for the
table nics.

Controller Class:
`````````````````

::

    <?php 
    ...
    ...
     if(strlen($sql)){
     $condition_SQL [] = " " . $singular_name . "." .$field_names     [$i]." ".$sql;
    	$show_searchinfo = true;
     }
    ...
    ...
    ?>

The function add() generates a list of nics for the add-view. The list
is used to choose a nic as a parent for the nic that is being added.
The next code-snippet is a part of the function add()....

Controller Class:
`````````````````

::

    <?php 
    ...
    ...
    	$this->set('nics', $this->Nic->generateList());
    ...
    ...
    ?>

The edit function() has to generate a list of nics, excluding the nic
you are editing.
The next code-snippet is a part of function edit()...

Controller Class:
`````````````````

::

    <?php 
    ...
    ...
    	$conditions = array("Nic.id" => "!= " . $id);
    	$this->set('nics', $this->Nic->generateList($conditions));
    ...
    ...
    ?>



4 The Views
-----------
In the views you have to be sure to reference the right child or
parent nic.

The add.thtml and edit.thtml reference the variable $nics.
I will show a part of the add.thtml and edit.thtml.

View Template:
``````````````

::

    
    ...
    ...
    
     <div class="optional"> 
       <?php echo $form->labelTag('Nic/parent_id', 'Parent');?>
       <?php echo $html->selectTag('Nic/parent_id', $nics, $html->tagValue('Nic/parent_id'), array(), array(), true);?>
       <?php echo $html->tagErrorMsg('Nic/parent_id', 'Please select the Parent.') ?>
     </div>
    ...
    ...

The index.thtml just references the parent_id.

View Template:
``````````````

::

    
    ...
    ...
    	<td> <?php echo $html->link($nic['Nic']['Parent_id'], '/nics/view/' .$nic['Nic']['Parent_id'])?></td>
    ...
    ...

The view.thtml references the parent_id and shows a table of child
nics by referencing the array for the child-nics.

View Template:
``````````````

::

    
    ...
    ...
    	<dt>Parent</dt>
    	<dd> <?php echo $html->link($nic['Nic']['parent_id'], '/nics/view/' .$nic['Nic']['parent_id'])?></dd>
    	...	
    	...	
    	...	
    	<div class="related">
    	<h3>Related Child Nics</h3>
    	<?php if(!empty($nic['Childnic'])):?>
    	<table cellpadding="0" cellspacing="0">
    	<tr>
    	<?php foreach($nic['Childnic']['0'] as $column => $value): ?>
    	<th><?php echo $column?></th>
    	<?php endforeach; ?>
    	<th>Actions</th>
    	</tr>
    	<?php foreach($nic['Childnic'] as $Childnic):?>
    	<tr>
    		<?php foreach($Childnic as $column => $value):?>
    			<td><?php echo $value;?></td>
    		<?php endforeach;?>
    		<td class="actions">
    			<?php echo $html->link('View', '/nics/view/' . $Childnic['id']);?>
    			<?php echo $html->link('Edit', '/nics/edit/' . $Childnic['id']);?>
    			<?php echo $html->link('Delete', '/nics/delete/' . $Childnic['id'], null, 'Are you sure you want to delete: id ' . $Childnic['id'] . '?');?>
    		</td>
    	</tr>
    	<?php endforeach; ?>
    	</table>
    	<?php endif; ?>



Bottom line
-----------
The most important lesson of this tutorial is that you have to setup
your model in a proper way; defining the hasmany-relation and the
child-relation. Secondly, in the rest of your code you have to
reference the right array (parent or child).


.. author:: CodingisFun
.. categories:: articles, tutorials
.. tags:: self-join,parent,Tutorials

