Element Helper
==============

by ping_ch on January 13, 2009

I needed a simple, yet flexible way for backend users to add certain
pre-defined HTML elements like Youtube videos to posts. I came up with
this little helper that replaces bbcode-like element tags with the
contents of an element.


What it does
````````````

It replaces tags in the form of [element:element_name] with the
content
of the respective element. It also allows to pass parameters to the
element:

[element:element_name var1="var1 will be passed to the element."]

An example for the shorthand notation:

[e:youtube id=ARjzIyWvhn4]



Requirements
````````````

+ Place the helper found below in your /app/views/helpers folder
+ Create a folder "templates" in /app/views/elements
+ Place elements that will be used in tags in the new folder



How to use it
`````````````

+ Include the element helper in your controller
+ Pass the string to be formatted to $element->format()



Examples
````````

Testing the helper in one line:

View Template:
``````````````

::

    
    <?php echo $element->format("A youtube video: [element:youtube id=ARjzIyWvhn4] ") ?>

Example for a youtube video element:

View Template:
``````````````

::

    
    <div class="youtube_video">
    	<object width="425" height="344">
    		<param name="movie" value="http://www.youtube.com/v/<?php echo $id ?>&hl=en&fs=1"></param>
    		<param name="allowFullScreen" value="true"></param>
    		<param name="allowscriptaccess" value="always"></param>
    		<embed src="http://www.youtube.com/v/<?php echo $id ?>&hl=en&fs=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="425" height="344"></embed>
    	</object>
    </div>



The Helper
``````````

Save as element.php in your helpers folder


Helper Class:
`````````````

::

    <?php 
    
    /**
     * Element Helper
     * Helps formatting strings with custom markup
     * 
     * @author Stefan Zollinger
     * @license MIT
     * @version 1.0
     */
    
    class ElementHelper extends Helper {
    
    	/**
    	 * The base directory containing your elements.
    	 * Set to '' to include all elements in your views/elements folder
    	 */
    	var $baseDir = 'templates';
    	
    	/**
    	 * Applies all the formatting defined in this helper
    	 * to $str
    	 * (Currently only $this->getElements() )
    	 * 
    	 * @return $str Formatted string 
    	 * @param string $str 
    	 */
    	function format($str) {
    		$str =& $this->getElements($str);
    		return $str;
    	}
    	
    	/**
    	 * 
    	 * Replaces [element:element_name] tags in a string with 
    	 * output from cakephp elements
    	 * Options can be defined as follows:
    	 * 		[element:element_name id=123 otherVar=var1 nextvar="also with quotes"]
    	 *  	[e:element_name]
    	 *  
    	 * @return formatted string 
    	 * @param $str string
    	 */
    	function getElements(&$str){
    		$View =& ClassRegistry::getObject('view');		
    		preg_match_all('/\[(element|e):([A-Za-z0-9_\-]*)(.*?)\]/i', $str, $tagMatches);
    		
    		for($i=0; $i < count($tagMatches[1]); $i++){
    			
    			$regex = '/(\S+)=[\'"]?((?:.(?![\'"]?\s+(?:\S+)=|[>\'"]))+.)[\'"]?/i';
    			preg_match_all($regex, $tagMatches[3][$i], $attributes);
    			
    			$element = $tagMatches[2][$i];
    			$options = array();
    			for($j=0; $j < count($attributes[0]); $j++){
    				$options[$attributes[1][$j]] = $attributes[2][$j]; 
    			}
    			$str = str_replace($tagMatches[0][$i], $View->element($this->baseDir.DS.$element,$options), $str);
    			
    			
    		}
    		
    		return $str;
    	}
    	
    }
    
    ?>


.. meta::
    :title: Element Helper
    :description: CakePHP Article related to markup,elements,formatting,Helpers
    :keywords: markup,elements,formatting,Helpers
    :copyright: Copyright 2009 ping_ch
    :category: helpers

