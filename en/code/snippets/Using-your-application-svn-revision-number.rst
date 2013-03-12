

Using your application svn revision number
==========================================

by %s on February 12, 2008

A little snippet to show how to find and use the svn revision number -
to your advantage.
There will be times, when it would be useful to know what revision
your code is running on. The below little snippet of code (which you
can place anywhere, e.g. in /app/config/bootstrap.php if you always
want to know what revision your application is running on) will define
a constant with the current revision.

::

    
    <?php
    if (!defined('REVISION')) {
    	if (file_exists(APP . '.svn' . DS . 'entries')) {
    		$svn = file(APP . '.svn' . DS . 'entries');
    		if (is_numeric(trim($svn[3]))) {
    			$version = $svn[3];
    		} else { // pre 1.4 svn used xml for this file
    			$version = explode('"', $svn[4]);
    			$version = $version[1];    
    		}
    		define ('REVISION', trim($version));
    		unset ($svn);
    		unset ($version);
    	} else {
    		define ('REVISION', 0); // default if no svn data avilable
    	}
    }?>


Avoiding stale cache files
~~~~~~~~~~~~~~~~~~~~~~~~~~
How often have you had a conversation like this:
You : "I've fixed it, can you test that js/display problem you were
having again please"
Customer : "It's still the same"
You : "Please refresh the page by pressing F5"
Customer : "It's still the same"
You : "Please delete your browser cache files"
Customer : "It's still the same"
You : "Please close your browser and reopen it"
Customer : "It's still the same.. Oh wait sorry I was looking at the
wrong window it's fixed thanks."

You can avoid this game of its-still-the-same-arg-my-blood-pressure by
using your code revision number to version-stamp your files. e.g. in
your layout:

::

    
    <?php
    echo $html->css('styles.css?v=' . REVISION, 'stylesheet');
    echo $javascript->link('effects.js?v=' . REVISION);
    ?>



In DB updates
~~~~~~~~~~~~~
I often/almost always include a little bug reporting tool in projects
so that admin users can log their praise and admiration to a log that
I read. On rare occasions they also add a note about problems too.
Knowing the code version that was in place when they reported the
problem is often invaluable to know. So for example in any model in
which you want to store the current code version you can do:

::

    
    <?php
    function beforeSave() {
    	if (!$this->id) {
    		$this->data['Bug']['version_found'] = REVISION;
    	}
    	return true;
    }
    ?>

And that's it. If you have any other innovative uses for the revision
number your code is using comments are open :)

.. meta::
    :title: Using your application svn revision number
    :description: CakePHP Article related to ,Snippets
    :keywords: ,Snippets
    :copyright: Copyright 2008 
    :category: snippets

