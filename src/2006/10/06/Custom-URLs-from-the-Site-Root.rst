Custom URLs from the Site Root
==============================

A number of sites nowadays are offering custom, unique URLs for
people. I'm sure we can all think of one small social networking site
that does this. I had a need to do this within a new Cake app I'm
writing. Plus, since I'm migrating from an existing site, this is a
legacy feature that must be supported.
A number of sites nowadays are offering custom, unique URLs for
people. I'm sure we can all think of one small social networking site
that does this. I had a need to do this within a new Cake app I'm
writing. Plus, since I'm migrating from an existing site, this is a
legacy feature that must be supported. I had to find a way to make
`http://www.example.com/username`_ route to
`http://www.example.com/users/view/username`_.

I will say that I don't know if this is the best solution, but it is
the one I currently have working. It does require you to do some extra
work whenever you're creating new controllers, but the work is
minimal. Also, this tutorial will only allow one of your controllers
to have this functionality.

All these changes take place inside of cake/app/config/routes.php. For
this example, we'll assume you have a collection of users and your
typical URL for viewing a user is
`http://www.example.com/users/view/username`_. Let's also assume you
have two other controllers named items and images. Finally, this
assumes you have a page called error, accessible at /pages/error.

First off, we need to create the route for our custom urls. The route
you would need to use is as follows:

::

    
    $Route->connect('/*', array('controller' => 'users', 'action' => 'view'));

What this route says is that for anything that comes in, you will use
the users controller and the view action for that controller,
essentially making the entire url a list of parameters.

We have to be careful where this goes in routes.php, as this is
basically a catch all for any URL given. We have to be sure this is
the last route used:

::

    
    <?php
    // cake/app/config/routes.php
    
    // Default route
    $Route->connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
    
    // Default pages route
    $Route->connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));
    
    // Custom URL route
    $Route->connect('/*', array('controller' => 'users', 'action' => 'view'));
    
    ?>

As you can see, the two default routes come first with our new route
coming last.

We're not finished here yet. Since we now have this catch all in
place, we need to ensure that all our controllers are treated
correctly. To do so for our controllers, we add the following to
routes.php:


::

    
    <?php
    // cake/app/config/routes.php
    
    // Default route
    $Route->connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
    
    // Set default controller routes
    $Route->connect('/users/:action/*', array('controller' => 'users'));
    $Route->connect('/items/:action/*', array('controller' => 'items'));
    $Route->connect('/images/:action/*', array('controller' => 'images'));
    
    // Default pages route
    $Route->connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));
    
    // Custom URL route
    $Route->connect('/*', array('controller' => 'users', 'action' => 'view'));
    
    ?>

Because of our new route, anything not matching the base path route or
the pages route will be routed through the view action of the users
controller. We need to ensure we grab URLs that should maintain Cake's
default routing.

The final step in this process would be to do some error handling.
Since we're using our catch all, anything that doesn't exist is still
going to try to route to our users controller. A quick bit of work on
the view function of our controller will redirect the user if an
invalid URL is given:

::

    
    function view($username)
    {
    	if ($user = $this->User->findByUsername($username)) {
    		$this->set('user', $user);
    	} else {
    		$this->redirect('/pages/error');
    		exit();
    	}
    }

If we can find the username given, $user will be set and we can then
set 'user' in our view. If not, redirect them to an error page.

That's all there is to it. I'm sure there are more ways to tweak this
strategy, but for now, it seems to work pretty well.

.. _http://www.example.com/username: http://www.example.com/username
.. _http://www.example.com/users/view/username: http://www.example.com/users/view/username

.. author:: PHPdiddy
.. categories:: articles, tutorials
.. tags:: rewrites route url,route,routing,url,Tutorials

