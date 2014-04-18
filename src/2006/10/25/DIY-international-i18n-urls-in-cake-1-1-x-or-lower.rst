DIY international (i18n) urls in cake 1.1.x or lower
====================================================

CakePHP 1.2 will have decent i18n functionality, but until you switch
you can use the simple but decent solution that i have created. It
allows you to use urls in any foreign language, which are translated
right before the routing happens, so nothing intervenes with all the
other logic (which is usually in english)
So, if you want to program in english (action names in controllers for
example), but you want to use urls in an other language, and you are
using a cakePHP version prior to 1.2? Here's the solution!

What you can expect: (example from one of my projects)
browse to urls like /activiteiten/overzicht/toekomst
cake will interpret it as /activities/index/future

after the translation and interpretation, the routing happens, so you
have all the freedom to define routes for english urls, like you are
used to.

put these 2 lines in app/config/routes.php above the routing rules.

PHP Snippet:
````````````

::

    <?php 
    include_once("translate.php");
    $from_url = translate($from_url,true);
    ?>

then, create the file app/config/translate.php and put this in it:

PHP Snippet:
````````````

::

    <?php 
    function translate($str = null,$total = false)
    	{
    		$translatetable = array('kalender' => 'events',
    					'fotos' => 'images',
    					'afbeeldingen' => 'images',
    					'activiteiten' => 'events',
    					'paginas' => 'pages',
    					'standaard' => 'default',
    					'voegtoe' => 'add',
    					'verwijder' => 'delete',
    					'bewerk' => 'edit',
    					'bekijk' => 'view',
    					'overzicht' => 'index',
    					'toekomst' => 'future',
    					'verleden' => 'past',
    					'nu' => 'now',
    					'vragenenantwoorden' => 'faq'
    								);
    		if($str)
    		{
    			if($total)
    			{ 
    				$old = explode('/',$str);
    				$lastone = end($old);
    				if(empty($lastone)) array_pop($old);
    				$new = array();
    								
    				/* translate each part or leave untranslated part */
    
    				for($i = 0 ; $i <sizeof($old) ; $i++)
    				{
    					$new[$i] = translate($old[$i]);
    				}
    
    				
    				/* construct the translated url.  this also adds a trailing "/" even if it wasn't in the original */
    				$new_url="";
    				foreach($new as $n)
    				{
    					$new_url .= $n."/";
    				}
    				
    				return $new_url;
    			}
    			else
    			{
    				foreach ($translatetable as $orig => $new)
    				{
    					if($str == $orig) $str = $new;
    				}
    				return $str;
    			}
    		}
    	}
    ?>

If you want to use multiple languages, you can put all the rules in
the same array. But in this case, you could have collisions (words
that are written the same, but mean different things, for example the
same word in different languages). This happens very rarily, but if
you would encounter this, you could expand the logic by for example
adding a 2-letter language identifier like '/nl/' in the url to choose
the right array.


.. author:: Dieter_be
.. categories:: articles, tutorials
.. tags:: ,Tutorials

