

Combobox Form Field Helper
==========================

by %s on January 13, 2009

This is modeled after the Windows Combobox input field. It is a
combination textbox and select list. The user can select from a list
of values or enter their own value.
This uses $ajax->autoComplete() and is a drop-in replacement for it.

A screenshot is available at
`http://brucemyers.com/cakecombobox/comboboxScreenShot.jpg`_
The helper combobox.php, placed in /app/views/helpers/


Helper Class:
`````````````

::

    <?php 
    class ComboboxHelper extends AppHelper {
    	var $helpers = array('Html','Ajax','Javascript');
    	
    	/**
    	 * Creates Combobox form input field.
    	 * 
    	 * This is a drop-in replacement for $ajax->autoComplete().
    	 * The button image size is 'height' => 16, 'width' => 20.
    	 *
    	 * @param string $fieldId Same as Ajax->autoComplete()
    	 * @param string $url Same as Ajax->autoComplete()
    	 * @param array $options Same as Ajax->autoComplete(), except 'var' is not available.
    	 * 		'comboboxTitle' - Optional title for the arrow button.
    	 * 		'comboboxImage' - Optional arrow button image filename. default = 'combobox_arrow.gif'
    	 * @return string HTML code
    	 */
    	function create($fieldId, $url, $options) {
    		static $needs_javascript = true;
    		
    		if ($needs_javascript) {
    			$this->Javascript->codeBlock('function comboboxButton(ac){ac.changed = false; ac.element.focus(); ac.hasFocus = true; var temp = ac.element.value; ac.element.value = ""; ac.getUpdatedChoices(); ac.element.value = temp; ac.tokenBounds = [-1, 0];}',
    				array('inline' => false));
    			$needs_javascript = false;
    		}
    		
    		$options['var'] = Inflector::camelize(str_replace(".", "_", $fieldId)) . 'Combobox';
    		
    		$onclick = 'comboboxButton(' . $options['var'] . '); return false;';
    		
    		$title = null;
    		if (isset($options['comboboxTitle'])) {
    			$title = $options['comboboxTitle'];
    			unset($options['comboboxTitle']);
    		}
    		
    		$img = 'combobox_arrow.gif';
    		if (isset($options['comboboxImage'])) {
    			$img = $options['comboboxImage'];
    			unset($options['comboboxImage']);
    		}
    		
    		$ac = $this->Ajax->autoComplete($fieldId, $url, $options);
    		
    		$arrow = '/>' . $this->Html->image($img, compact('onclick','title'));
    		
    		// Have to do this because can't use 'after' option of $form->text()
    		// because $ajax->autoComplete() passes 'after' option to Scriptaculous.
    		return $this->output(str_replace('/>', $arrow, $ac));
    	}
    }
    ?>


Now for the controller which sets up the helper and has some sample
autocomplete code.


Controller Class:
`````````````````

::

    <?php 
    class StuffsController extends AppController {
    	var $helpers = array('Combobox');
    
      /**
       * Form autocompletion retriever.
       */
    	function autoComplete() {
    		Configure::write('debug', 0);
    		
    		$this->set('values', $this->Stuff->find('all', array(
    					'conditions' => array(
    						'location_id LIKE' => $this->data['Stuff']['location_id'].'%'
    					),
    					'fields' => array('location_id'),
    					'order' => 'location_id'
    		)));
    		$this->layout = 'ajax';
    	}
    }
    ?>

Now for the form view. Note the inclusion of the div and label tags.
$ajax->autoComplete() doesn't output the field label, so it has to be
done manually.

The parameters for $combobox->create() are the same as
$ajax->autoComplete(). The 'var' parameter is not available. There are
2 additional parameters: 'comboboxTitle' - Optional title for the
arrow button. 'comboboxImage' - Optional arrow button image filename.
default = 'combobox_arrow.gif'


View Template:
``````````````

::

    
    	<?php
    	$javascript->link('prototype', false);
    	$javascript->link('scriptaculous.js?load=effects,controls', false);
    	echo $form->create();
    	?>
    ...
    	<div class="input text"><label for="LocationId">Location</label><?php echo $combobox->create('location_id', '/stuffs/autoComplete', array('comboboxTitle' => "View Locations")) ?></div>
    ...
       	<?php
    	echo $form->end();
    	?>

Now for the ajax view in auto_complete.php.


View Template:
``````````````

::

    
    <ul>
     <?php foreach($values as $value): ?>
         <li><?php echo $value['Stuff']['location_id']; ?></li>
     <?php endforeach; ?>
    </ul> 


The down-arrow button is available at
`http://brucemyers.com/cakecombobox/combobox_arrow.gif`_ Put it in
/app/webroot/img/. Its dimensions are 'height' => 16, 'width' => 20. A
larger height causes the table box that wraps the form field to
increase in size in Firefox and Safari, which messes up the row
height.


How It Works
~~~~~~~~~~~~

#. User presses the down-arrow button
#. An empty field value is sent to the server via Ajax
#. The SQL LIKE matches on '%' which returns all of the records in the
   table when using MySql
#. The auto complete code displays the item list



.. _http://brucemyers.com/cakecombobox/combobox_arrow.gif: http://brucemyers.com/cakecombobox/combobox_arrow.gif
.. _http://brucemyers.com/cakecombobox/comboboxScreenShot.jpg: http://brucemyers.com/cakecombobox/comboboxScreenShot.jpg
.. meta::
    :title: Combobox Form Field Helper
    :description: CakePHP Article related to combo-box,Helpers
    :keywords: combo-box,Helpers
    :copyright: Copyright 2009 
    :category: helpers

