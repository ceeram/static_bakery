CakePHP's routing explained
===========================

by Frank on November 02, 2009

Routing in CakePHP is a very powerful feature and is used to make URLs
look good. Experience in the #cakephp IRC support channel tells me it
appears to be hard to grasp even though the book is quite complete.
This article should change that a bit and discuss the main features of
routing. Comments below asking for support will be ignored, to get
support, there is the Google group and the #cakephp IRC channel.


Applying routes
~~~~~~~~~~~~~~~

A common case where routing should be used is when a controller is
given a name just to make it look good in the URL. Routes can also be
used for legacy purposes, to match the URLs from the previous site, so
no dead links will end up in search engines.

A misconception is that routes cause redirects to the proper
controller and action, this is not what routing is about. A certain
route maps to a specific controller and action in the application. So
if you type that route in the browser, you should end up in the
controller and action that you had specified. So, no redirects.


Default routes
~~~~~~~~~~~~~~

By default CakePHP comes with some routes. Some are in the core and
not visible and others are in APP/config/routes.php. The ones in the
core are there just to make the basic URLs work (like:
/controller/action). However, we will focus on the custom routes in
routes.php.

A clean installation already has two routes. The first is for the main
page on the root of the domain:

::

    <?php
    Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
    ?>


You see that the route (the first parameter) is only for '/', which is
the root of the domain. The second parameter sets default values. In
this case it maps to the PagesController::display() action and sets
the first parameter to 'home'.

The second route in routes.php points to static pages. Notice the *
(wildcard), this means it matches with anything behind '/pages/'. All
those things after '/pages/' will be passed as parameters to the
'display' action.

::

    <?php
    Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));
    ?>


For example, if you type in the browser a URL like '/pages/about',
CakePHP will execute PagesController::display() with the first
parameter set to 'about'. If you try a URL like
'/pages/about/something/else', it will still execute
PagesController::display(), but with three parameters ('about',
'something', 'else').

Changing routes will cause link changes all over your website. This
means you can control your links from a single file. A requirement is
that you use the HtmlHelper and array based URLs to make your links.
This awesome feature is called 'reverse routing'. However this falls
outside the scope of this article, so I won't continue on this, more
information is available here though: `http://debuggable.com/posts
/new-router-goodies:480f4dd6-4d40-4405-908d-4cd7cbdd56cb`_


Creating routes with named elements
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In the first chapter I mentioned routes are used to pimp up the URLs
and gave the example of renaming controllers. Here is how it should be
done.

With our eye on the Bakery, let's imagine we have created an
ArticlesController with two actions called 'index' and 'show'. The
'index' action won't take any parameters and the 'show' action will
take one called $id. So it looks something like:

::

    <?php
    class ArticlesController extends AppController {
    	public function index() {
    		// Do some magic.
    	}
    
    	public function show($id = null) {
    		// Do some magic.
    	}
    }
    ?>


Now, instead of the default route ('/articles/show/69') we want
something different like: '/writings/show/69'. This can be achieved
with the following route:

::

    <?php
    Router::connect('/writings/:action/*', array('controller' => 'articles'));
    ?>


See how I used a named element called 'action' in this route. Named
elements are prefixed with: ':'. These named elements are
automatically passed to the controller in Controller::$params and so
on to the View class. :action is used by CakePHP itself, same as
:controller, :plugin or whatever you normally put in your array based
URLs.

You can also use your own custom named elements. This way you can put
anything in your URLs. For example you can put someone's username in
the URL so it should match '/writings/phally/show/69'. A route for
this would be:

::

    <?php
    Router::connect('/writings/:username/:action/*', array('controller' => 'articles'));
    ?>


To generate this link:

::

    <?php
    echo $html->link('Article 69', array(
    	'controller' => 'articles', 
    	'action' => 'show', 
    	'username' => 'phally',
    	69
    ));
    ?>


As you can see above, you can specify a value to the named element as
you would with setting a named parameter. So without the route this
would generate a link to '/articles/show/69/username:phally'.



Passing parameters to the action
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that we are using named elements to identify parameters in the
URL, we can pass these to the controller action. For this, we need to
sharpen our route a bit, so we can set the order of the parameters to
be passed. We are going to use this route:

::

    <?php
    Router::connect('/writings/:username/:action/:id/*', array('controller' => 'articles'));
    ?>


I have added the :id named element. How cool can it be to pass this on
to the actions parameters so we don't have to dig around
Controller::$params to find our named elements? Very. Extend the route
as the following:

::

    <?php
    Router::connect(
    	'/writings/:username/:action/:id/*', 
    	array(
    		'controller' => 'articles'
    	),
    	array(
    		'pass' => array(
    			'id',
    			'username'
    		)
    	)
    );
    ?>


Having this route makes CakePHP call your action like
$Controller->show(69, 'phally') and then your action should look like:

::

    <?php
    public function show($id = null, $username = null) {
    	// $id == 69;
    	// $username == 'phally';
    }
    ?>


Awesome, eh?


The order of the routes matters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Since PHP reads from top to bottom it is obvious the order of the
routes matter. Let's see what kind of effect this can have on your
URLs. For this chapter we use the example from the previous chapters.
We forget the username and the passing of the parameters.

The route discussed there will only properly work for the
ArticlesController::show() action. The 'index' action is a different
story. Without the routes the following will generate '/articles'.

::

    <?php
    echo $html->link('Article 69', array(
    	'controller' => 'articles', 
    	'action' => 'index'
    ));
    ?>


However, with the route it will generate '/writings/index' instead of
'/writings'. To correct this we need a second route:

::

    <?php
    Router::connect('/writings', array('controller' => 'articles')); // Critical.
    Router::connect('/writings/:action/*', array('controller' => 'articles')); // Catch all.
    ?>


Since 'action' defaults to 'index' we can leave that out. A URL with
parameters will not match the first route, but will match the second.
A URL without parameters will match the first and therefor the second
route is never reached and thus not matched. The second route will
also match the URL without the parameters, but since it is never
reached, there is not problem. Let's see what happens when we switch
the order of the routes.

::

    <?php
    Router::connect('/writings/:action/*', array('controller' => 'articles')); // Catch all.
    Router::connect('/writings', array('controller' => 'articles')); // Critical.
    ?>


In this order, both URLs will match the first route and the generated
URL of the link will be '/writing/index' again.

So you see that the way to go is to specify the critical routes first
and after that setting some 'catch all' routes to catch the URLs that
didn't match the critical routes.


Setting up match conditions
~~~~~~~~~~~~~~~~~~~~~~~~~~~

To make sure a route only matches when it needs to, you can setup
conditions in third parameter of Router::connect(). For this chapter I
will use a different example. Sometimes people complain about the
route to the static pages (like: '/pages/about' and '/pages/terms')
they rather have '/about' and '/terms'. This can be implemented two
ways. One is the not DRY method, to setup a route for every static
page:

::

    <?php
    Router::connect('/about', array('controller' => 'pages', 'action' => 'display', 'about'));
    Router::connect('/terms', array('controller' => 'pages', 'action' => 'display', 'terms'));
    ?>


This is just ugly. Now watch how we can place this all in one route
combining a named element that is passed to the action and a condition
to only match those two keywords:

::

    <?php
    Router::connect(
    	'/:pagename', 
    	array(
    		'controller' => 'pages', 
    		'action' => 'display'
    	), 
    	array(
    		'pagename' => 'about|terms', 
    		'pass' => array(
    			'pagename'
    		)
    	)
    );
    ?>


So basically what we made is a catch all route with a scope. Keep in
mind that you will have to change the way you generate the link from:

::

    <?php
    echo $html->link('about', array('controller' => 'pages', 'action' => 'display', 'about'));
    ?>


To this (with the definition for the named element):

::

    <?php
    echo $html->link('about', array('controller' => 'pages', 'action' => 'display', 'pagename' => 'about'));
    ?>


Your conditions can be anything, from simple things like above to
complex regexes. There are some good examples in the book:
`http://book.cakephp.org/view/46/Routes-Configuration`_

A more advanced and useful example can be found here:
`http://dsi.vozibrale.com/articles/view/advanced-routing-with-cakephp-
one-example`_

Named parameters break routing?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you are using custom named parameters, it could break routing if
you don't tell CakePHP they exist. This is where
Router::connectNamed() comes into play. With this method you can let
CakePHP know which named parameters exist. By default only the named
params of the paginator are connected. You can set your own named
parameters like this:

::

    <?php
    	Router::connectNamed(array('username', 'email'));
    ?>


Watch out though, this overwrites the default settings for the
paginator. To append your named parameters, you can do:

::

    <?php
    	Router::connectNamed(array('username', 'email'), array('default' => true));
    ?>


This will parse 'username', 'email', 'page', etc. More information and
examples can be found in the API: `http://api.cakephp.org/class/router
#method-RouterconnectNamed`_


Admin and prefix routing
~~~~~~~~~~~~~~~~~~~~~~~~

In CakePHP 1.3 prefix routing is going to work similar to admin
routing (which is explained here:
`http://bakery.cakephp.org/articles/view/secrets-of-admin-routing`_).
You will be able to give a list of prefixes that can be used in the
URL and as prefixes for actions. You can have several actions with the
same name, but different prefixes:

::

    <?php
    Configure::write('Routing.prefixes', array('author', 'moderator', 'admin'));
    ?>


::

    <?php
    class ArticlesController extends AppController {
    	public function author_edit($id = null) { }
    	public function moderator_edit($id = null) { }
    	public function admin_edit($id = null) { }
    }
    ?>


In your routes you can simply set 'admin' => true or 'moderator' =>
true. This setup makes it easy to switch layout for different sections
or to set user rights more accurate.


Debugging routes
~~~~~~~~~~~~~~~~

An easy way to debug routes is to use DebugKit for it. With this
amazing tool, you can easily check what route is matched and what
parameters are or aren't set. It sure beats using debug() to print
everything you want to know. DebugKit can be found here:
`http://github.com/cakephp/debug_kit`_

So, good luck with the routes and remember, the comments on this
article that request support will be ignored! Use the book, google
groups or the IRC channel.

[p] Phally


.. _http://book.cakephp.org/view/46/Routes-Configuration: http://book.cakephp.org/view/46/Routes-Configuration
.. _http://bakery.cakephp.org/articles/view/secrets-of-admin-routing: http://bakery.cakephp.org/articles/view/secrets-of-admin-routing
.. _http://api.cakephp.org/class/router#method-RouterconnectNamed: http://api.cakephp.org/class/router#method-RouterconnectNamed
.. _http://debuggable.com/posts/new-router-goodies:480f4dd6-4d40-4405-908d-4cd7cbdd56cb: http://debuggable.com/posts/new-router-goodies:480f4dd6-4d40-4405-908d-4cd7cbdd56cb
.. _http://github.com/cakephp/debug_kit: http://github.com/cakephp/debug_kit
.. _http://dsi.vozibrale.com/articles/view/advanced-routing-with-cakephp-one-example: http://dsi.vozibrale.com/articles/view/advanced-routing-with-cakephp-one-example

.. author:: Frank
.. categories:: articles, tutorials
.. tags:: routing,phally,Tutorials

