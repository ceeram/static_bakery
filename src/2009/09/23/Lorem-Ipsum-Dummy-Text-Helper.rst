Lorem Ipsum (Dummy Text) Helper
===============================

by skylersully on September 23, 2009

This helper will help display dummy text throughout your application!
Why would this be helpful to you? If you're building an app, it's nice
to see some text in your views. Sure, you could copy and paste, but
why do all that when you can use the power of Cake!
This helper is pretty simple. Drop the code below in your
'/app/views/helpers/' folder as 'lorem.php'.


Helper Class:
`````````````

::

    <?php 
    /**
     * Lorem Ipsum Helper.
     *
     * Helps with displaying dummy text
     *
     * Copyright 2009, ConsciousArts Software, Inc. (http://consciousarts.net/)
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @filesource
     * @copyright       Copyright 2009, ConsciousArts Software, Inc. (http://consciousarts.net/)
     * @link            http://consciousarts.net/
     * @since           LoremHelper v1.0
     * @version         $Revision: $
     * @modifiedby      $LastChangedBy: $
     * @lastmodified    $Date: $
     * @license         http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    class LoremHelper extends AppHelper
    {
    	/**
    	 * Contains the lorem ipsum text with each paragraph
    	 * stored as an index.
    	 *
    	 * @access    public
    	 * @var       string
    	 */
    	var $text = array(
    		"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed blandit tempus molestie. In pretium iaculis lorem id ultricies. Curabitur lobortis sapien ac magna ullamcorper faucibus. Aenean ac nibh diam. Donec id felis nunc, et bibendum erat. Nulla faucibus, lacus iaculis aliquet ultricies, diam nibh auctor risus, eget placerat est orci sed arcu. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Etiam diam tellus, scelerisque ut imperdiet eget, faucibus vel sapien. Sed vitae libero vel nisl tincidunt sodales. Donec scelerisque sodales velit, et interdum orci commodo ac. Maecenas malesuada condimentum turpis vel convallis. Integer sed mi id est scelerisque varius. Nulla facilisi.",
    		"In mollis luctus libero nec euismod. Proin pellentesque rutrum tortor id venenatis. Integer eu lorem quis odio lacinia sollicitudin. Aenean leo urna, dictum at adipiscing et, posuere eget ipsum. Cras id dolor est. Curabitur at lorem eget neque gravida imperdiet nec quis lacus. Proin et facilisis justo. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Suspendisse ut mauris vel est cursus ornare non id nisl. Aenean porttitor fringilla lectus, vitae lobortis nisi interdum id. Nullam semper, ante id dictum consectetur, elit velit accumsan urna, eu faucibus lectus justo id nibh. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec sed arcu purus.",
    		"Etiam erat mauris, ullamcorper quis fringilla at, venenatis sed eros. Mauris et nisl non libero scelerisque volutpat. Mauris luctus metus quis odio sagittis luctus. Pellentesque et arcu congue tortor tincidunt venenatis. Praesent eget diam sed nunc vulputate fringilla. Pellentesque sit amet quam vel mi tristique tristique. Praesent dictum gravida dolor vitae pulvinar. In hac habitasse platea dictumst. Integer sit amet feugiat erat. Integer vitae lacus suscipit dolor adipiscing egestas sed sed libero. Sed viverra elit eget dolor tristique interdum. Curabitur tristique risus vel ante commodo sed tincidunt nisi mollis. Maecenas malesuada placerat nunc, quis placerat nisi semper nec. Etiam sollicitudin, tortor at vulputate euismod, ligula augue ornare augue, nec bibendum orci nulla id metus. Nulla velit augue, interdum vitae dapibus et, faucibus ut ligula.",
    		"Duis sed nisl ac mauris semper cursus. Vivamus sit amet libero in dolor euismod bibendum sed in massa. Nulla iaculis bibendum tempus. Pellentesque condimentum turpis nec tortor interdum ut blandit mauris vehicula. Fusce scelerisque odio vel dui luctus vel viverra odio semper. Nunc euismod luctus sapien, eu euismod lorem iaculis id. Pellentesque suscipit, lectus id volutpat posuere, nulla risus egestas erat, vitae cursus orci augue eu tellus. Donec quis elit in neque vulputate dignissim a eu justo. Ut tempus magna non tellus ultrices rhoncus. Duis sagittis blandit ultricies. Aliquam est sem, feugiat eu suscipit a, ultricies vel felis. Maecenas vel pharetra nisl.",
    		"Vestibulum ut eros id diam viverra malesuada. Praesent vel odio odio. Nulla molestie, lectus ut vulputate posuere, sapien felis pretium magna, feugiat laoreet arcu mauris a nibh. Etiam sed tellus metus. Nam vehicula justo in odio venenatis lobortis et vel ipsum. Fusce facilisis sem non sem dictum eu congue diam euismod. Aenean at lectus nulla, nec dignissim elit. Donec viverra nunc sed tortor vehicula eu feugiat dui varius. Aenean orci tellus, adipiscing consequat bibendum placerat, molestie eu lectus. Nulla quis nunc orci. Nullam placerat nulla vitae nulla bibendum sit amet auctor risus pharetra. Sed semper lectus a purus convallis aliquam. Vestibulum massa mauris, facilisis a tincidunt eget, semper ut orci.",
    		"Pellentesque mi mi, pretium in scelerisque nec, facilisis id massa. Cras feugiat dui id dui volutpat malesuada posuere eros faucibus. Vestibulum mattis suscipit aliquam. Phasellus aliquet cursus ipsum sed cursus. Ut molestie laoreet elementum. Etiam varius orci sit amet nibh luctus tincidunt quis sed nisi. Maecenas leo nisi, ornare id fermentum sit amet, ornare at sapien. Duis pellentesque, felis vitae euismod sagittis, leo lorem vestibulum neque, in gravida urna orci vel dui. Nam ligula justo, consequat at pharetra eget, auctor in sem. In placerat quam nec felis sollicitudin vitae malesuada metus eleifend. Vivamus commodo ipsum mollis risus sodales malesuada. Duis et imperdiet elit. Proin sapien lorem, fermentum vel lacinia vitae, bibendum in erat. Etiam tristique, velit non mollis blandit, turpis eros rhoncus mi, iaculis tristique est velit at lectus. Morbi commodo felis sed ligula pellentesque sed dignissim nulla eleifend."
    	);
    	
    	/**
    	 * Contains configuration for display arguments
    	 *
    	 * @access    protected
    	 * @var       array
    	 */
    	var $_config = array(
    		'arguments' => array(
    			'characters' => array('chars', 'char', 'byte', 'bytes', 'characters'),
    			'words' => array('words', 'word'),
    			'sentences' => array('sentence', 'sentences', 'sent'),
    			'paragraphs' => array('para', 'paras', 'paragraphs'),
    		)
    	);
    
    	/**
    	 * Displays dummy text
    	 *
    	 * Takes in an array of options to determine how many words, characters,
    	 * or paragraphs to display.  Since the helper only contains 6 paragraphs,
    	 * it will loop around the paragraphs
    	 *
    	 * @access    public
    	 * @param     $unit Characters (chars), Words (words) or Paragraphs (paragraphs)
    	 * @param     $amount Number of units to display
    	 * @param     $print Flag to print or return
    	 */
    	function display($unit, $amount, $print = true) {
    		extract($this->_config['arguments']);
    		$lorem = implode('|', $this->text);
    		if( in_array($unit, $characters) ) {
    			$text = substr($lorem, 0, $amount);
    			if( $text[strlen($text)-1] != '.' && $text[strlen($text)-1] != ' ' ) {
    				$text .= '.';
    			}
    		} else {
    			if( in_array($unit, $words) ) {
    				$splitBy = ' ';
    			} elseif( in_array($unit, $sentences) ) {
    				$splitBy = '.';
    			} else {
    				$splitBy = '|';
    			}
    			$pieces = explode($splitBy, $lorem);
    			$count = sizeof($pieces);
    			while( $amount > $count ) {
    				$morePieces = explode($splitBy, $lorem);
    				$pieces = array_merge($pieces, $morePieces);
    				$count = sizeof($pieces);
    			}
    			array_splice($pieces, $amount);
    			$text = implode($splitBy, $pieces);
    		}
    		$text = str_replace('|', '</p><p>', $text);
    		$text = trim("<p>$text</p>");
    		if( $print ) {
    			echo $text;
    		} else {
    			return $text;
    		}
    	}
    }
    ?>

The display function contains 3 arguments. The first is for the unit
you want (paragraphs, characters, sentences, words), the second is for
the number of those units (as an integer), and the third is a boolean
which determines whether it should be printed or not.

To display a block of dummy text, do this:

::

    
    <?php
    $lorem->display('paragraph', 1);
    
    /**
     * You could also use these units to display a paragraph:
     *
     * 'para'
     * 'paras'
     * 'paragraph'
     */
    ?>

To display a sentence, do this:

::

    
    <?php
    $lorem->display('sentence', 1);
    
    /**
     * You could also use these units to display a sentence:
     *
     * 'sentences'
     * 'sentence'
     * 'sent'
     */
    ?>

To display words, do this:

::

    
    <?php
    $lorem->display('words', 1);
    
    /**
     * You could also use these units to display a word:
     *
     * 'words'
     * 'word'
     */
    ?>

To display characters, do this:

::

    
    <?php
    $lorem->display('characters', 1);
    
    /**
     * You could also use these units to display a character
     *
     * 'byte'
     * 'bytes'
     * 'char'
     * 'chars'
     * 'characters'
     */
    ?>

Enjoy. :)


.. author:: skylersully
.. categories:: articles, helpers
.. tags:: helper,text,dummy,lorem,ipsum,Helpers

