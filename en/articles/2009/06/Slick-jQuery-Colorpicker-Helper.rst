

Slick jQuery Colorpicker Helper
===============================

by %s on June 23, 2009

If you need a simple way to include a color-picker widget into your
forms, look no further. This helper is the answer to all your prayers!
First, you'll need to have jquery installed (I have mine in
webroot/js/jquery.js). Then you'll need to download the colorpicker
plugin here: `http://www.eyecon.ro/colorpicker/`_

To install the colorpicker component:


#. place the colorpicker.js file in webroot/js
#. create a 'colorpicker' subdirectory in your webroot/img directory,
   and copy the contents of the colorpicker 'images' directory there.
#. place the colorpicker.css file in webroot/css and replace all
   instances of '../images/' with '../img/colorpicker/'

Here's the helper:


Helper Class:
`````````````

::

    <?php 
    class ColorPickerHelper extends AppHelper
    {
        var $helpers = array('Form', 'Html', 'Javascript');
    
        /**
         * Returns HTML for rendering a colorpicker text box.
         *
         * the options array is used to specify attributes for the input element.
         */
        function picker($fieldName, $options = array())
        {
    	// Link in javascript
    	$this->Javascript->link('jquery', false);
    	$this->Javascript->link('colorpicker', false);
    	$this->Html->css('colorpicker', NULL, array(), false);
    
    	// Create text input
    	$input = $this->Form->text($fieldName, $options);
    
    	// Get input id
    	$this->setFormTag($fieldName);
    	$html_attributes = $this->domId($options);
    	$input_id = $html_attributes['id'];
    
    	// Create js
    	$img_base = $this->Html->url('/img/colorPicker/');
    	$js = "
    	    jQuery(function () {
    	    jQuery('#$input_id').ColorPicker({
    	    onSubmit: function(hsb, hex, rgb, el) {
    		jQuery(el).val(hex);
    		jQuery(el).ColorPickerHide();
                },
    	    onBeforeShow: function () {
    	        jQuery(this).ColorPickerSetColor(this.value);
                }
                })
    	    .bind('keyup', function() {
    	        jQuery(this).ColorPickerSetColor(this.value);
                });
                });
            ";
    
    	// Put it together
    	return $input.$this->Javascript->codeBlock($js);
        }
    
    }
    ?>

To use the colorpicker, just call it from your view like this:

::

    
    $colorPicker->picker('HomePage.background_color');

This will render a text input box with colorpicker capabilities. If
you want to customize the input field, you can supply an options array
as the second parameter.

.. _http://www.eyecon.ro/colorpicker/: http://www.eyecon.ro/colorpicker/
.. meta::
    :title: Slick jQuery Colorpicker Helper
    :description: CakePHP Article related to jquery,colorpicker,Helpers
    :keywords: jquery,colorpicker,Helpers
    :copyright: Copyright 2009 
    :category: helpers

