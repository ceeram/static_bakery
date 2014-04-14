Using CAKE_ADMIN for multiple user types
========================================

by skrebbel on May 09, 2007

A simple hack allowing to use the functionality of CAKE_ADMIN for more
than one usertype.
Ever wanted to have pretty URLs for multiple separate types of users
than just one, as core.php's CAKE_ADMIN allows? Try this little hack.


The situation
`````````````
By default, Cake supports a way of making specific controller actions
and views for a special class of users - generally intended for
administrator views.

This is done by setting the CAKE_ADMIN define in /app/config/core.php
with a string, such as "superuser". In that case, if a user accesses
WEBROOT/superuser/book/add, BookController::superuser_add() will be
called, rather than BookController::add() or maybe even
SuperuserController::book('add') which is, of course, not what we
intend to happen. This mechanism allows for prettier and more sensible
URLs for these admin users.

But, Cake only supports one such admin view - if there are more types
of administrators or access levels, each with their totally disjunct
pages and functionality, how do we go about that? This little snippet
will, albeit in a slightly ugly way, do the trick.


The solution
````````````
Put the following code in your /app/config/bootstrap.php:

::

    
    <?php
    /**
     * We define CAKE_ADMIN differently for the different usertypes we 
     * want to support, acting on the currently called URL to decide
     * which we tell Cake about.
     */
    function adminHack($subdirs)
    {	
    	$firstSlash = strpos($_GET['url'],'/');
    	$firstSubdir = substr($_GET['url'], 0, $firstSlash);
    	if(in_array($firstSubdir, $subdirs))
    	{
    		define('CAKE_ADMIN', $firstSubdir);
    	}
    }
    
    //example usage:
    adminHack(array('member', 'expert', 'superuser'));
    ?>

In the above example, calling (for instance) /member/banana/peel/3
would cause BananaController::member_peel(3) to be invoked.

.. meta::
    :title: Using CAKE_ADMIN for multiple user types
    :description: CakePHP Article related to url,admin,members section,Snippets
    :keywords: url,admin,members section,Snippets
    :copyright: Copyright 2007 skrebbel
    :category: snippets

