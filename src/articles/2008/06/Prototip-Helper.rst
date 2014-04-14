Prototip Helper
===============

by andy on June 25, 2008

This is a helper for people using prototip. Prototip is available
here: http://www.nickstakenburg.com/projects/prototip/files/prototip1.
1.0.zip The provided code was written for the 1.1.x branch of CakePHP.
I would imagine that it would easily be updated for 1.2.x
There are three public functions in this class:
1. $prototip->tooltip()
2. $prototip->addTooltip()
3. $prototip->renderTooltips()

A single private function parses argument arrays:
$this->_parseOptions()

There are three arguments to pass to the tooltip() function: el the
element, content the content of the tooltip, and an optional options
array.

tooltip() simply adds a single toolip for the element specified in the
argument. It automagically detects if it should render a simple
tooltip, 2 arguments provided, or a fancy tooltip, 3 arguments
provided.

addTooltip() adds a tooltip to an internal array, but does not render
it to screen until told to do so. This is helpful if you are
generating tooltips for each row in a database in an element, but wish
to render all tooltips in a single script tag.

Update 03/02/08 - modified to allow any DOM element as the first
argument for both tooltip() and addTootip(). (See code below).

renderTooltips() renders all tooltips added to the internal array via
addTooltip() in a single script tag.

When using this helper in ajax callbacks, it's best to use the
addTooltip() + renderTootlip() combination in your calling code, and
in the callback update the tooltip with tooltip().

Here is the code:

::

    
    <?php
    class PrototipHelper extends Helper {
    
    	/**
    	 * @link http://www.nickstakenburg.com/projects/prototip/ the js library this helper works with
    	 * @var Array a flat array of the allowed options
    	 */
    	var $allowed_options = array('className','closeButton','duration','delay','effect','fixed','hideOn','hook','offset','showOn','target','title','viewport');
    
    	/**
    	 * @var Array a flat array with the special case option names
    	 */
    	 var $sc_options = array('hook','offset','hideOn');
    
    	/**
    	 * @var Array holds the basic string for creating a new Tooltip
    	 */
    	var $tooltip = array('base'=>"new Tip('%s',%s);", 'fancy'=>"new Tip('%s',%s,%s);");
    
    	/**
    	 * @var Array cakephp helpers used by this helper
    	 */
    	var $helpers = array('Html', 'Javascript');
    
    	/**
    	 * @var Array holds tooltips to be rendered in a block
    	 */
    	var $tips = array();
    
    	/**
    	 * @public
    	 *
    	 * @param String	$el				the id of the element
    	 * @param String	$content		html to show in the tip
    	 * @param Array		$options		the tooltip options
    	 *
    	 * @return String a formatted tooltip instantiation
    	 */
    	function tooltip($el,$content,$options=array()) {
    		if ( substr($content,0,1)!='$') $content = "'$content'";
    		if($options) {
    			return $this->Javascript->codeBlock($this->output(sprintf($this->tooltip['fancy'],$el,$content,$this->_parseOptions($options))));
    		} else {
    			return $this->Javascript->codeBlock($this->output(sprintf($this->tooltip['base'],$el,$content)));
    		}
    	}
    
    	/**
    	 * @public
    	 *
    	 * @param String	$el				the id of the element
    	 * @param String	$content		html to show in the tip
    	 * @param Array		$options		the tooltip options
    	 *
    	 * Adds a formatted tooltip to the $tips array
    	 */
    	function addTooltip($el, $content, $options=array()) {
    		if ( substr($content,0,1)!='$') $content = "'$content'";
    		if($options) {
    			$this->tips[] = sprintf($this->tooltip['fancy'],$el,$content,$this->_parseOptions($options));
    		} else {
    			$this->tips[] = sprintf($this->tooltip['base'],$el,$content);
    		}
    	}
    
    	/**
    	 * @public
    	 *
    	 * Renders the array of tooltips in $tips as a Javascript code block
    	 */
    	function renderTooltips() {
    		$tips_string = "<script type=\"text/javascript\">\n";
    		foreach($this->tips as $tip) {
    			$tips_string .= $tip . "\n";
    		}
    		$tips_string .= '</script>';
    		return $this->output($tips_string);
    	}
    
    	/**
    	 * @private
    	 *
    	 * @param Array $options an array of the options available to prototip
    	 *
    	 * @return String a formatted string of options i.e. {'opt':'value'...}
    	 */
    	function _parseOptions($options=array()) {
    		$opts = "{";
    		$arr_opts = array();
    		foreach($options as $key => $value) {
    			if(in_array($key,$this->allowed_options)) {
    				if(in_array($key, $this->sc_options)) { //special case for formatting options
    					if(strpos($value, '{') !== false) { // the option has a tuple...e.g. 'hook:{target:'topLeft',tip:'rightMiddle'}
    						$sc = explode(',' ,$value);
    						$str_sc_opts = "'$key':";
    						$sc_arr_opts = array();
    						foreach($sc as $opt => $val) {
    							$sc_arr_opts[] = "$val";
    						}
    						$arr_opts[] = $str_sc_opts . join(",", $sc_arr_opts);
    					}
    				} else {
    					$arr_opts[] = "'$key':'$value'";
    				}
    			}
    		}
    		$opts .= join(",", $arr_opts);
    		$opts .="}";
    		return $opts;
    	}
    }
    
    ?>


NB: when using the prototip options that require a hash, you may need
some funky quotes...e.g.

::

    'hook'=>"{'key':'value','key':'value'}"

unless you have numeric values, e.g.

::

    'offset'=>"{'x':5,'y':10}"

.
Loosey goosey scripting languages indeed.

.. meta::
    :title: Prototip Helper
    :description: CakePHP Article related to ,Helpers
    :keywords: ,Helpers
    :copyright: Copyright 2008 andy
    :category: helpers

