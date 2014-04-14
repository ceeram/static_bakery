TinyMCE helper
==============

by daibach on July 15, 2008

This is a basic helper to make it really easy to use TinyMCE inside
CakePHP.


Notes
~~~~~
To specify any TinyMCE options, provide the extra $tinyoptions array
with the information. It'll be converted to JavaScript and used when
creating the editor.


Tiny MCE
~~~~~~~~
Go and grab TinyMCE from its website
`http://tinymce.moxiecode.com/download.php`_ and copy the
/tinymce/jscripts/tiny_mce folder to the /app/webroot/js


Controller
~~~~~~~~~~

::

    
    var $helpers = Array('Form', 'Tinymce');



Helper code
~~~~~~~~~~~
app/views/helpers/tinymce.php

::

    
    <?php
    class TinyMceHelper extends AppHelper {
    	// Take advantage of other helpers
    	var $helpers = array('Javascript', 'Form');
    	// Check if the tiny_mce.js file has been added or not
    	var $_script = false;
    	
    	/**
    	 * Adds the tiny_mce.js file and constructs the options
    	 *
    	 * @param string $fieldName Name of a field, like this "Modelname.fieldname", "Modelname/fieldname" is deprecated
    	 * @param array $tinyoptions Array of TinyMCE attributes for this textarea
    	 * @return string JavaScript code to initialise the TinyMCE area
    	 */
    	function _build($fieldName, $tinyoptions = array()) {
    		if (!$this->_script) {
    			// We don't want to add this every time, it's only needed once
    			$this->_script = true;
    			$this->Javascript->link('/js/tiny_mce/tiny_mce.js', false);
    		}
    		// Ties the options to the field
    		$tinyoptions['mode'] = 'exact';
    		$tinyoptions['elements'] = $this->__name($fieldName);
    		return $this->Javascript->codeBlock('tinyMCE.init(' . $this->Javascript->object($tinyoptions) . ');');
    	}
    	
    	/**
    	 * Creates a TinyMCE textarea.
    	 *
    	 * @param string $fieldName Name of a field, like this "Modelname.fieldname", "Modelname/fieldname" is deprecated
    	 * @param array $options Array of HTML attributes.
    	 * @param array $tinyoptions Array of TinyMCE attributes for this textarea
    	 * @return string An HTML textarea element with TinyMCE
    	 */
    	function textarea($fieldName, $options = array(), $tinyoptions = array()) {
    		return $this->Form->textarea($fieldName, $options) . $this->_build($fieldName, $tinyoptions);
    	}
    
    	/**
    	 * Creates a TinyMCE textarea.
    	 *
    	 * @param string $fieldName Name of a field, like this "Modelname.fieldname", "Modelname/fieldname" is deprecated
    	 * @param array $options Array of HTML attributes.
    	 * @param array $tinyoptions Array of TinyMCE attributes for this textarea
    	 * @return string An HTML textarea element with TinyMCE
    	 */
    	function input($fieldName, $options = array(), $tinyoptions = array()) {
    		$options['type'] = 'textarea';
    		return $this->Form->input($fieldName, $options) . $this->_build($fieldName, $tinyoptions);
    	}
    }
    ?>



View example
~~~~~~~~~~~~

::

    
    <div class="form-container">
      <?php echo $form->create('Page'); ?>
        <fieldset>
          <legend>Page</legend>
          <?php
            echo $form->input('title');
            echo $tinymce->input('content');
          ?>
        </fieldset>
      <?php echo $form->end('Save'); ?>
    </div>

Here's an example supplying some extra tinyMCE configuration options

::

    
    <div class="form-container">
      <?php echo $form->create('Page'); ?>
        <fieldset>
          <legend>Page</legend>
          <?php
            echo $form->input('title');
            echo $tinymce->input('content', null, array(
              'theme'                             => 'advanced',
              'theme_advanced_toolbar_location'   => 'top',
              'theme_advanced_toolbar_align'      => 'left',
              'theme_advanced_statusbar_location' => 'bottom',
            ));
          ?>
        </fieldset>
      <?php echo $form->end('Save'); ?>
    </div>


Suggestions
```````````

Everyone should know that letting users submit HTML can be a bit risky
when it comes to displaying it, unless you trust them of couse (like
an admin user). If you want more general users taking advantage of
something like this, I'd suggest looking into something like
`http://htmlpurifier.org/`_. This parses the HTML and can remove
anything you don't want submitted.

.. _http://htmlpurifier.org/: http://htmlpurifier.org/
.. _http://tinymce.moxiecode.com/download.php: http://tinymce.moxiecode.com/download.php
.. meta::
    :title: TinyMCE helper
    :description: CakePHP Article related to helper,TinyMCE,Helpers
    :keywords: helper,TinyMCE,Helpers
    :copyright: Copyright 2008 daibach
    :category: helpers

