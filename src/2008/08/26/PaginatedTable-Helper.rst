PaginatedTable Helper
=====================

The PaginatedTable helper makes it easy to show your users what column
is being sorted and the current sort direction. The outputted mark-up
can easily be formatted using CSS to fit any design.


Why?
~~~~

PaginatedTableHelper is an evolution of the solution posted by Teknoid
(`http://teknoid.wordpress.com/2008/07/28/displaying-sort-direction-
in-paginated-data/`_). Teknoid's solution uses inline PHP logic making
it hard to re-use.


How?
~~~~

To use PaginatedTableHelper just include it in your controller like
any other Helper.

::

    var $helpers = array('PaginatedTable', 'Javascript', ...);

This is an example of how it can be used in your Views.


View Template:
``````````````

::

    
    <table>
    	<tr>
    		<?php echo $paginatedTable->header('name', __('Name', true)); ?>
    		<?php echo $paginatedTable->header('address', __('Address', true)); ?>
    		<?php echo $paginatedTable->header('zip', __('Postal Code', true)); ?>
    		<?php echo $paginatedTable->header('city', __('City', true)); ?>
    		<?php echo $paginatedTable->header('country', __('Country', true)); ?>
    	</tr>
    	<?php foreach ($people as $person) { ?>
    	<tr>
    		<td><?php echo $person['Person']['name']; ?></td>
    		<td><?php echo $person['Person']['address']; ?></td>
    		<td><?php echo $person['Person']['zip']; ?></td>
    		<td><?php echo $person['Person']['city']; ?></td>
    		<td><?php echo $person['Person']['country']; ?></td>
    	</tr>
    	<?php } ?>
    </table>



Styling
~~~~~~~

The headers can be styled to match your design using CSS. This is how
I usually style it just to give you an idea of how it can be used.

::

    
    th a {
            /* Move the header link to the right so that 
               it doesn't cover the direction icon. */
    	margin-left: 12px;
    }
    .asc, .desc, .undefined {
    	background-repeat: no-repeat;
    	background-position: left center;
    }
    .asc {
    	background-image: url('../img/sort_down.png');
    }
    .desc {
    	background-image: url('../img/sort_up.png');
    }
    .undefined {
    	background-image: url('../img/sort_undefined.png');
    }



Code
~~~~


Helper Class:
`````````````

::

    <?php 
    <?php
    class PaginatedTableHelper extends AppHelper {
    
    	var $helpers = array('Html', 'Paginator');
    
    	function header($field, $name = null, $options = array()) {
    		$sort = ($this->Paginator->sortKey() == $field ? $this->Paginator->sortDir() : 'undefined');
    		$name = (!isset($name) ? Inflector::humanize($field) : $name);
    		
    		return $this->Html->tag('th', 
    			$this->Paginator->sort($name, $field, $options), 
    			array('class' => $sort)
    		);
    	}
    	
    }
    ?>
    ?>

I know, it's simple, but maybe someone will find it useful :)

.. _http://teknoid.wordpress.com/2008/07/28/displaying-sort-direction-in-paginated-data/: http://teknoid.wordpress.com/2008/07/28/displaying-sort-direction-in-paginated-data/

.. author:: linnk
.. categories:: articles, helpers
.. tags:: ,Helpers

