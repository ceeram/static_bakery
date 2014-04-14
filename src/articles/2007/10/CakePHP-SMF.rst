CakePHP & SMF
=============

by dirhauge on October 31, 2007

A quick guide on getting SMF and CakePHP working together. Uses SMF
for login, session handling, even layout.
A few weeks ago I was trying to find a good way to integrate SMF
Forums and CakePHP. Here is what I have done to get this working...

I'm going to assume that you have cake installed. Also assuming that
you have SMF installed and that its located in your
\cake\app\webroot\forums folder.

First step: Modify the cake_cake bootstrap.php file
Right after the comments I added this:

::

    
    $ssi_layers = array('main');

After the cake require LIBS (line 43 or so) I added this line:

::

    
    require_Once('forums/SSI.php');

This will make it so that all of your cake pages inherit the normal
SMF functions.

Next I created a new file: cake_app_views_layouts default.thtml
Inside this file I only included the following:

::

    
    <?php
    echo $content_for_layout;
    ?>

Final step: Disable CakePHP Session so that we can just use SMF for
session handling.
Modified the cake_app_config core.php file:
changed the line: define('AUTO_SESSION',true); to be

::

    
    define('AUTO_SESSION',false);

This disables Cakes session handling.
Now you can even access SMF variables, such as the user id from your
models views or controllers...
For instance to get the User ID at anytime, simply use:

::

    
    $GLOBALS['ID_MEMBER']

Other SMF globals are also easily accessible this way!

Enjoy!

Update: 10/28/2007:

After further tinkering I have altered my original approach. There was
a problem with the above. First if you want to use Ajax, you wont be
able to do the above. Second, every page will have the SMF formatting,
which is not always desirable for quick search popups.
So here is what I would suggest:

1)Don't modify the original Cakephp bootstrap.php file.
2)Modify the Cake Libs Controller Component.php file. Change the
function for init to this:

::

    
    function init(&$controller) {
    		$this->__controller =& $controller;
    
    		if ($this->__controller->components !== false) {
    			$loaded = array();
    			$this->__controller->components = array_merge($this->__controller->components, array('Session'));
    			$loaded = $this->__loadComponents($loaded, $this->__controller->components);
    			$bit = 0;
    			foreach (array_keys($loaded)as $component) {
    				$tempComponent =& $loaded[$component];
    				if($component == 'RequestHandler')
    				{
    					
    					if (env('HTTP_X_REQUESTED_WITH') != null) {
    						//echo "its not null";
    						if( env('HTTP_X_REQUESTED_WITH') == "XMLHttpRequest")
    						{
    							$bit = 1;
    						}
    					} 	
    				}
    				if (isset($tempComponent->components) && is_array($tempComponent->components)) {
    					foreach ($tempComponent->components as $subComponent) {
    						$this->__controller->{$component}->{$subComponent} =& $loaded[$subComponent];
    					}
    				}
    			}
    			if ($bit == 0)
    			{ //if its not an ajax request, then use the SMF menu
                                    //if you dont ever want to use the SMF menu, then just comment out the next line.
    				$ssi_layers = array('main');
    			}
                            require_Once('forums/SSI.php');
    		}
    	}

This solves the problem with Ajax, now you can do ajax calls and not
get the SMF menu html showing up in the response. It does not however
solve the quick search popup problem.

3) I still disable the CakePHP session and am using the
$GLOBALS['ID_MEMBER'] variable to determine which user is logged in as
above.

As of now I plan on using this as my new setup as it provides better
flexibility. I am commenting out the $ssi_layers = array('main'); line
in the component.php file since its way easier to make a menu that
looks like the SMF menu that I can include at will by using a layout
than it is to always have it around. So far I have found this to be
the best way to get these 2 working together.

Hope this helps!

.. meta::
    :title: CakePHP & SMF
    :description: CakePHP Article related to session,SMF,Snippets
    :keywords: session,SMF,Snippets
    :copyright: Copyright 2007 dirhauge
    :category: snippets

