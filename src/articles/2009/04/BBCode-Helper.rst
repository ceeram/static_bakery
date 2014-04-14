BBCode Helper
=============

by withoutnick on April 15, 2009

A BBCode Helper for CakePHP 1.2.x.x


Intro
~~~~~
Helper BbcodeHelper replaces BBcodes with the equivalent valid/invalid
HTML codes.


Usage
~~~~~
Copy the bbcode.php file to the app/views/helpers/ directory.
The Helper has only one method: string parse(string text, int valid=0,
array parse=null).

string text â€“ text for parsing
int valid â€“ 0 for invalid html, 1 for xhtml 1.0 strict valid output
array parse â€“ items in this array will be parsed. Without parameters
all items will be parsed.
Valid values in parse array:
b, i, u, s, size, color, center, url, url2, img, img2, quote, ul, ol,
li


Code
~~~~

Helper Class:
`````````````

::

    <?php 
    /**
     * BBCode Helper for CakePHP 1.2.x.x
     * @author withoutnick (withoutnick@withoutnick.cz)
     * @version 1.0 stable
     */
    	class BbcodeHelper extends AppHelper {
    		var $bbcodes = array(
    			'b' => '/(\[[Bb]\])(.+?)(\[\/[Bb]\])/',
    			'i' => '/(\[[Ii]\])(.+?)(\[\/[Ii]\])/',
    			'u' => '/(\[[Uu]\])(.+?)(\[\/[Uu]\])/',
    			's' => '/(\[[Ss]\])(.+?)(\[\/[Ss]\])/',
    			'size' => '/(\[size=)(.+?)(\])(.+?)(\[\/size\])/',
    			'color' => '/(\[color=)(.+?)(\])(.+?)(\[\/color\])/',
    			'center' => '/(\[center\])(.+?)(\[\/center\])/',
    			'url' => '/(\[url\])(.+?)(\[\/url\])/',
    			'url2' => '/(\[url=)(.+?)(\])(.+?)(\[\/url\])/',
    			'img' => '/(\)(.+?)(\[\/img\])/',
    			'img2' => '/(\[img=)(.+?)([xX])(.+?)(\])(.+?)(\[\/img\])/',
    			'quote' => '/(\[quote\])(.+?)(\[\/quote\])/',
    			'ul' => '/(\[ul\])(.+?)(\[\/ul\])/',
    			'ol' => '/(\[ol\])(.+?)(\[\/ol\])/',
    			'li' => '/(\[li\])(.+?)(\[\/li\])/'
    		);
    		
    		var $htmlcodes = array(
    			'b' => '<b>\\2</b>',
    			'i' => '<i>\\2</i>',
    			'u' => '<u>\\2</u>',
    			's' => '<strike>\\2</strike>',
    			'size' => '<font size="\\2">\\4</font>',
    			'color' => '<font color="\\2">\\4</font>',
    			'center' => '<center>\\2</center>',
    			'url' => '<a href="\\2">\\2</a>',
    			'url2' => '<a href="\\2">\\4</a>',
    			'img' => '<img src="\\2">',
    			'img2' => '<img width="\\2" height="\\4" src="\\6">',
    			'quote' => '<blockquote>\\2</blockquote>',
    			'ul' => '<ul>\\2</ul>',
    			'ol' => '<ol>\\2</ol>',
    			'li' => '<li>\\2</li>'
    		);
    		
    		var $htmlcodes_valid = array(
    			'b' => '<strong>\\2</strong>',
    			'i' => '<span style="font-style: italic;">\\2</span>',
    			'u' => '<span style="text-decoration: underline;">\\2</span>',
    			's' => '<span style="text-decoration: line-through;">\\2</span>',
    			'size' => '<span style="font-size: \\2;">\\4</span>',
    			'color' => '<span style="color: \\2;">\\4</span>',
    			'center' => '<div style="text-align: center;">\\2</div>',
    			'url' => '<a href="\\2">\\2</a>',
    			'url2' => '<a href="\\2">\\4</a>',
    			'img' => '<img src="\\2" alt="BBCode image" />',
    			'img2' => '<img width="\\2" height="\\4" src="\\6" alt="BBCode image" />',
    			'quote' => '<blockquote>\\2</blockquote>',
    			'ul' => '<ul>\\2</ul>',
    			'ol' => '<ol>\\2</ol>',
    			'li' => '<li>\\2</li>'
    		);
    		
    		function parse($text, $valid=0, $parse=null) {
    			$bbcodes = $this->bbcodes;
    			if($valid) {
    				$htmlcodes = $this->htmlcodes_valid;
    			}else{
    				$htmlcodes = $this->htmlcodes;
    			}
    			if(isset($parse)) {
    				$temp_bbcodes = array();
    				$temp_htmlcodes = array();
    				foreach ($parse as $key => $value) {
    					$temp_bbcodes[$key] = $bbcodes[$value];
    					$temp_htmlcodes[$key] = $htmlcodes[$value];
    				}
    				$htmlcodes = $temp_htmlcodes;
    				$bbcodes = $temp_bbcodes;
    			}
    			
    			$return = preg_replace($bbcodes, $htmlcodes, $text);
    			return $this->output($return);
    		}
    	}
    ?>


.. meta::
    :title: BBCode Helper
    :description: CakePHP Article related to BBCode,Helpers
    :keywords: BBCode,Helpers
    :copyright: Copyright 2009 withoutnick
    :category: helpers

