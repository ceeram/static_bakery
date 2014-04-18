Using Security Component and multi Checkboxes
=============================================

As i did not find the way to do it with the Cake FormHelper i will
detail what i did and why. The helper PcformHelper is usefull when you
want to use the Security Component, and need to create an array of
checkboxes.
First in your controller add the needed Helper and Component


Controller Class:
`````````````````

::

    <?php 
    var $components = array('Security');
    var $helpers = array('Pcform');
    ?>

Then in the View , i diplay a list of forums, and choose the one i
need :

View Template:
``````````````

::

    
    <?php echo $pcform->create('Block','action'=>'active_topics')) ?>
    ...
    <p><span class="fakelabel"><?php echo __('Forums',true) ?></span>
    <span class="desc"><?php echo __('Choose the forums of which you wish to list the discussions.',true) ?></span></p>
    <?php
    // Get all the categories and forums
    $cur_category = 0;
    
    if (!empty($forums))
        foreach ($forums as $forum) {
    
    	if ($forum['Categories']['id'] != $cur_category)	// A new category since last iteration?
    	    {
    ?>
    			<p><?php echo $forum['Categories']['cat_name'] ?></p>
    			<ul class="checklist">
    <?php	
    		    if ($cur_category != 0)
        			echo "\t\t\t".'</ul>'."\n";
    
    	    	$cur_category = $forum['Categories']['id'];
        	}
    	
    	    echo '<li>'.$pcform->multiCheckboxes(
    			"Block",
    			"fids",
    			$forum['Forums']['id'], $p_fids, $forum['Forums']['forum_name']
    			).'</li>';
    
        }
    ?>
    			</ul>
    		<?php echo $pcform->submit(__('Save',true),array('class'=>'submit')) ?>
    	<?php echo $pcform->end(); ?>
    		

So when we submit the form, in the controller we will need to do :


Controller Class:
`````````````````

::

    <?php 
    	function beforeFilter () {
    
    		if (!empty($this->data)) {		
    		    
    			$this->Security->requirePost('active_topics');
    		}
    	}
    ?>

This way the Security Component can find the "fids" checkboxes and let
you continue to the active_topics method.


Here is the code of the Helper.

Helper Class:
`````````````

::

    <?php 
    /*
     * @filesource
     * @copyright		Copyright 2008, FoxMaSk
     * @link		http://puncake.foxmask.info PunCake
     * @version		$Revision$
     * @modifiedby		$LastChangedBy$
     * @lastmodified	$Date$
     * @license	        http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL
     * 
     * This helper is usefull when you want to use the Security Component
     * and need to create an array of checkboxes.
     * 
     */
    class PcformHelper extends FormHelper
    {
    
    	private $__out = array();
    	
    	/**
     	* multiCheckboxes method to display checkbox with the same name and be used as an array. 
     	*
     	* @modelName string $modelName the (same) name of the model used in the form.
     	* @colName string $colName the name of the checkbox
     	* @value string $value the value of the current checkbox
     	* @values array $values the array of all the values 
     	* @title string $title to be displayed after the checkbox
     	* 
     	* @access public
     	*/	
    	function multiCheckboxes($modelName, $colName, $value, $values=array(),$title)	{
    
    		$this->__out = array();
    		
    		#formating the $options array used later by the $this->__secure() method
            $options['value'] = $value;
            $options['name'] = 'data['.$modelName.']['.$colName.'][]';
                    
    		$selected_str = '';
    		if ( in_array($value,$values ) ) {				
    			$selected_str = 'checked="checked"';
    			$options['checked']='checked';
            }
    
    		$options = $this->__initInputField($colName, $options);
    		
    		#let's calculate the hash of the field
    		$this->__secure();
    			
    		$this->__out = '<input type="checkbox" value="'.$value.'" name="data['.$modelName.']['.$colName.'][]" '.$selected_str.'/> '.$title;
    		
    		return $this->__out;
    
    	}	
    }
    
    ?>


NOTICE :

As you can see in the View, i used $pcform for all inputs i needed.

As the PcformHelper extends the FormHelper, you can use all the input
provided by FormHelper.

So you have to avoid to mix $form->input AND $pcform->input otherwise
the Security component will not work correclty.



.. author:: foxmask
.. categories:: articles, helpers
.. tags:: security,component,form,Helpers

