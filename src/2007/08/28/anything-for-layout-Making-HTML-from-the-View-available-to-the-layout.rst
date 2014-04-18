$anything_for_layout: Making HTML from the View available to the
layout
======

Did you ever have a side navigation that you wanted to change based
upon changes in the view? Using this helper, you can write HTML and
other output for display in the layout. The output itself is written
in the your view and then transfered to the layout.
This is pretty simple to use. Instead of explaining it all and it's
many many varied uses and advantages, I'll just give a quick demo.


An Example
----------
This is a quick and dirty layout:

::

    <html>
    	<head>
    		<title><?=$title_for_layout;?>		
    	</head>
    	<body>
    		
    		<h2>This is a Website header</h2>
    		<? $layout->output($subheader_for_layout);?>
    
    		<hr>
    
    		<?php echo $content_for_layout;?>
    
    		<hr>
    		<h3>A footer would go here</h3>
    
    	</body>
    </html>

And now to put a subheader into your layout, you can just write a view
like this -

::

    <?=$layout->blockStart('subheader');?>
    	<h4>This is a page Sub Heading</h4>
    <?=$layout->blockEnd();?>
    
    This would be the main content of the current page.

Enjoy ;)



The Code
--------
Here is the complete Helper Code

::

    <?php
    /**
     * LayoutHelper
     * This Helper provides a few functions that can be used to assist the layout.
     * 
     * @author Robert Conner <rtconner>
     */
    
    class LayoutHelper extends AppHelper {
    	
    	var $__blockName = null;
    	
    	/**
    	 * Start a block of output to display in layout
    	 *
    	 * @param  string $name Will be prepended to form {$name}_for_layout variable
    	 */
    	function blockStart($name) {
    
    		if(empty($name))
    			trigger_error('LayoutHelper::blockStart - name is a required parameter');
    			
    		if(!is_null($this->__blockName))
    			trigger_error('LayoutHelper::blockStart - Blocks cannot overlap');
    
    		$this->__blockName = $name;
    		ob_start();
    		return null;
    	}
    	
    	/**
    	 * Ends a block of output to display in layout
    	 */
    	function blockEnd() {
    		$buffer = @ob_get_contents();
    		@ob_end_clean();
    
    		$out = $buffer; 
    			
    		$view =& ClassRegistry::getObject('view');
    		$view->viewVars[$this->__blockName.'_for_layout'] = $out;
    		
    		$this->__blockName = null;
    	}
    	
    	/**
    	 * Output a variable only if it exists. If it does not exist you may optionally pass
    	 * in a second parameter to use as a default value.
    	 * 
    	 * @param mixed $variable Data to ourput
    	 * @param mixed $defaul Value to output if first paramter does not exist
    	 */
    	function output(&$var, $default=null) {
    		if(!isset($var) or $var==null) {
    			if(!is_null($default)) 
    				echo $default;
    		} else
    			echo $var;	
    	}
    	
    }
    
    ?>



.. author:: rtconner
.. categories:: articles, helpers
.. tags:: ,Helpers

