

Lorem Ipsum - Dummy Text Helper
===============================

by %s on December 29, 2008

[p]Tired of toggling between your widget, or lipsum.org, or your text
file, etc. to pull in placeholder text?[/p] [p]Then [b]this helper is
for YOU![/b][/p] [p][i]update: fixed a few types and whatnot[/i][/p]


Helper Class:
`````````````

::

    <?php 
    class LoremHelper extends AppHelper {
    	var $helpers = array('Html');
    	var $words = array();
    	
    	/**
    	* Return placeholder text. By default, a single html-formatted paragraph.
    	* For a brief history of "lorem ipsum", see http://en.wikipedia.org/wiki/Lorem_ipsum
    	* also, thanks http://www.lipsum.org for all the faithful placeholder
    	*
    	* @param integer $number depending on the context of $type passed -- will be number of words, pargraphs, or list-items
    	* @param string $type trigger used to switch between words only, paragraph(s), or lists (ol/ul)
    	* @param array $attributes Additional HTML attributes of the list (ol/ul) tag, or paragraph (when applicable)
    	* @param array $itemAttributes Additional HTML attributes of the list item (LI) tag (when applicable)
    	* @return string placeholder text
    	* @access public
    	*/
    	function ipsum($number = 1, $type = 'p', $attributes = array(), $itemAttributes = array()) {
    		$this->words = explode(' ', 'lorem ipsum dolor sit amet consectetur adipisicing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur excepteur sint occaecat cupidatat non proident sunt in culpa qui officia deserunt mollit anim id est laborum');
    		switch ($type) {
    			// Words
    			case 'w':
    			case 'words':
    				$string = $this->_sentence($number, $number, false);
    			break;
    			// Unordered list
    			case 'l':
    			case 'ul':
    			case 'list':
    			// ordered list too!
    			case 'ol':
    				for($li=0;$li<$number;$li++) {
    					$list[] = $this->_sentence();
    				}
    				$string = $this->Html->nestedList($list, $attributes, $itemAttributes, ($type == 'ol') ? 'ol' : 'ul');
    			break;
    			// everything else paragraphs
    			default:
    				for($p=0;$p<$number;$p++) {
    					$paraText = '';
    					$numberSentences = rand(16,20);
    					for ($s=0;$s<$numberSentences;$s++) {
    						$paraText .= $this->_sentence();
    					}
    					$paras[] = $this->Html->para(null, $paraText, $attributes);
    				}
    				$string = implode("\n", $paras);
    			break;
    		}
    		return $string;
    	}
    	
    	/**
    	* Internal function to return a greeked sentence
    	* 
    	* @param integer $maxWords maximum number of words for this sentence
    	* @param integer $minWords minimum number of words for this sentence
    	* @param boolean $punctuation if false it will not append random commas and ending period
    	* @return string greeked sentence
    	* @access private
    	*/
    	function _sentence($maxWords = 10, $minWords = 4, $punctuation = true) {
    		$string = '';
    		$numWords = rand($minWords, $maxWords);
    		for($w=0;$w<$numWords;$w++) {
    			$word = $this->words[rand(0, (count($this->words)-1))];
    			// if first word capitalize letter...
    			if ($w == 0) {
    				$word = ucwords($word);
    			}
    			$string .= $word;
    			// if not the last word, 
    			if ($w != ($numWords-1)) {
    				// 5% chance of a comma...
    				if (rand(0,99) < 5) {
    					$string .= ', ';
    				} else {
    					$string .= ' ';
    				}
    			}
    		}
    		$string .= '. ';
    		return $string;
    	}
    }
    ?>

Those of you new to cake, copy this into /app/views/helpers/lorem.php,
then in your controller, or app_controller.php, add it to your
existing $helpers array ( var $helpers = array('Lorem'); )

The following usage outputs a single html paragraph of placeholder
text (drop it into a view)

View Template:
``````````````

::

    
    <h1>Sample View</h1>
    <p>This placeholder paragraph sucks! I have to waste my time typing a bunch of random shit to fill up some space just to see what the page looks like with some actual content on it. What a complete waste of energy... all these keystrokes could be doing something useful like blogging about duck tales.<p>
    <?= $lorem->ipsum(); ?>

You can also output placeholder ul/ol lists...

View Template:
``````````````

::

    
    <h1>Sample View with Lists</h1>
    <p>unordered list with 5 items...</p>
    <?= $lorem->ipsum(5, 'ul'); ?>
    <p>how about an ordered list with 25 items?</p>
    <?= $lorem->ispum(25, 'ol'); ?>

Finally, you can just have it return a single string of un-punctuated,
greeked text

View Template:
``````````````

::

    
    <h1>Sample View Again</h1>
    <blockquote><?= $lorem->ipsum(40, 'w'); ?></blockquote>

In closing, I hope this saves someone some time. Looking at the source
you'll see I setup the triggers a little loosely to appeal to wider
taste (add your own for whatever works best for you -- the goal here
is efficiency), and since the paragraphs and lists tap into the
HtmlHelper, you can actually pass it additional attributes,
itemAttributes when applicable...

thanks to all the core cake developers, cake bloggers, and all you irc
trolls for making this helper possible


.. meta::
    :title: Lorem Ipsum - Dummy Text Helper
    :description: CakePHP Article related to greeking,dummy text,lorem ipsum,placeholder text,lipsum,Helpers
    :keywords: greeking,dummy text,lorem ipsum,placeholder text,lipsum,Helpers
    :copyright: Copyright 2008 
    :category: helpers

