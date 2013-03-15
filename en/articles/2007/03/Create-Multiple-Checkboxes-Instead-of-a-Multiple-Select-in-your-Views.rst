Create Multiple Checkboxes Instead of a Multiple-Select in your Views
=====================================================================

by %s on March 23, 2007

From a usablitiy stand point, multiple-select boxes are a nightmare.
Forget to hold down the modifier key when adding an option and you
loose all your selections. When the number of options are managable,
multiple checkboxs are a better choice for average users. This
functionality is coming to cake in future versions but you can have it
now with this helper.
Save this as habtm.php in app/views/helpers

::

    <?php
    class HabtmHelper extends HtmlHelper {
    	
    	/**
    	 * Returns a list of checkboxes.
    	 *
    	 * @param string $fieldName Name attribute of the SELECT
    	 * @param array $options Array of the elements (as 'value'=>'Text' pairs)
    	 * @param array $selected Selected checkboxes
    	 * @param string $inbetween String that separates the checkboxes.
    	 * @param array $htmlAttributes Array of HTML options
    	 * @param  boolean $return         Whether this method should return a value
    	 * @return string List of checkboxes
    	 */
    	function checkboxMultiple($fieldName, $options, $selected = null, $inbetween = null, $htmlAttributes = null, $return = false) {
    		$this->setFormTag($fieldName);
    		if ($this->tagIsInvalid($this->model, $this->field)) {
    			if (isset($htmlAttributes['class']) && trim($htmlAttributes['class']) != "") {
    				$htmlAttributes['class'] .= ' form_error';
    			} else {
    				$htmlAttributes['class'] = 'form_error';
    			}
    		}
    		if (!is_array($options)) {
    			return null;
    		}	
    		if (!isset($selected)) {
    			$selected = $this->tagValue($fieldName);
    		}
    		foreach($options as $name => $title) {
    			$optionsHere = $htmlAttributes;
    			if (($selected !== null) && ($selected == $name)) {
    				$optionsHere['checked'] = 'checked';
    			} else if (is_array($selected) && array_key_exists($name, $selected)) {
    				$optionsHere['checked'] = 'checked';
    			}
    			$optionsHere['value'] = $name;
    			$checkbox[] = "<li>" . sprintf($this->tags['checkboxmultiple'], $this->model, $this->field, $this->parseHtmlOptions($optionsHere), $title) . "</li>\n";
    		}
    		return "\n" . sprintf($this->tags['hiddenmultiple'], $this->model, $this->field, null, $title) . "\n<ul class=\"checkboxMultiple\">\n" . $this->output(implode($checkbox), $return) . "</ul>\n";
    	}
    	
    }
    ?>

Add this snip of css to your stylesheet and adjust the li width to
your taste

::

    ul.checkboxMultiple {
    	margin:0;
    	padding:0;
    	list-style-type:none;
    	}
    ul.checkboxMultiple li {
    	display:block;
    	float:left;
    	width: 220px;
    	margin: 0 8px 0 0;
    	}

And add these tag templates to app/config/tags.ini.php

::

    ; Tag template for an input type='hidden' tag.
    hiddenmultiple = "<input type="hidden" name="data[%s][%s][]" %s/>"
    
    ; Tag template for a input type='checkbox ' tag.
    checkboxmultiple = "<input type="checkbox" name="data[%s][%s][]" %s/>%s"

Then you can use the new helper exactly the same way as
$html->selectTag(...) just change your method call to
$habtm->checkboxMultiple(..) instead.

Acknowledgment: This is based off code by MrRio in the Trac system:
`https://trac.cakephp.org/ticket/1260`_

.. _https://trac.cakephp.org/ticket/1260: https://trac.cakephp.org/ticket/1260
.. meta::
    :title: Create Multiple Checkboxes Instead of a Multiple-Select in your Views
    :description: CakePHP Article related to helpers,checkboxList,checkboxGroup,MrRio,HtmlHelper,multipleSelect,hasAndBelongsToMany,HABTM,checkboxMultiple,checkbox,Helpers
    :keywords: helpers,checkboxList,checkboxGroup,MrRio,HtmlHelper,multipleSelect,hasAndBelongsToMany,HABTM,checkboxMultiple,checkbox,Helpers
    :copyright: Copyright 2007 
    :category: helpers

