input field fixer
=================

This is a small input helper function meant to check whether the input
field's value is passed as a named URL argument. If the field is
passed, then the input field will be disabled, but it's value will be
included in the post data.

::

    
    // checks whether the input field's value is passed as a named URL argument
    	function input($fieldName, $options=array()) {
        	
    		$out = '';
    		$f = preg_replace('/_id$/', '', $fieldName);
    		if (@$this->params['pass'][$f]) {
    			$disabled = 'disabled';
    			$selected = $this->params['pass'][$f];
    			$out .= $this->Form->input($fieldName, am($options, array('type'=>'hidden', 'value'=>$selected)));
    		}
        	
    		$out .= $this->Form->input($fieldName, am($options, array('disabled'=>@$disabled, 'selected'=>@$selected)));
    		return $out;
    		
        }



.. author:: sarimarton
.. categories:: articles, helpers
.. tags:: ,Helpers

