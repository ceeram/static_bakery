

Taking Advantage of the Pages Controller
========================================

by %s on May 02, 2007

Looking for a few tips on how to make the Pages Controller your new
friend? Out-of-the-box it automatically maps incoming requests to
their views, sets the page title, and even handles sub-pages well. But
wait, there's more!
Looking for a few tips on how to make the Pages Controller your new
friend? Out-of-the-box it maps incoming requests to their views, sets
the page title, and even handles sub-pages well. But wait, there's
more!


Initial Installation
--------------------

Of course, we could manipulate the pages controller in it's default
location (/cake/libs/controller/pages_controller.php), but then we
would probably forget the next time we update Cake to its `latest
version`_. Instead, let's copy it into our /app/controllers directory.
Now we can change it all we want, and it will always be with us.


Ultra Clean URLs
----------------

Sure, it's nice that our about page maps to /pages/about and our
contact page to /pages/contact. But what if we have to get rid of the
/pages/ business? Assuming that pages is our default, catch-all,
controller, this should work:

::

    
    $Route->connect('/*', array('controller' => 'pages', 'action' => 'display'));



Give Control to the Controller
------------------------------

Now we're "cooking with gas" and we have our beloved /about and
/contact urls without having to map every single page in the router.
What's next? What if we need some custom controller action before our
contact page? All we have to do is add one line to the display()
function.


Controller Class:
`````````````````

::

    <?php 
    function display() {
      ...
      // add this snippet before the last line
      if (method_exists($this, $page)) {
        $this->$page();
      }
      // here's the last line
      $this->render(join('/', $path));
    }
    ?>

Works like magic! Now, if you have a contact() function in your
controller, it will first process any logic you have before calling
the view.

Got any more snazzy ways to jazz up the Pages Controller?

.. _latest version: http://cakephp.org/downloads
.. meta::
    :title: Taking Advantage of the Pages Controller
    :description: CakePHP Article related to routes,urls,pages,Snippets
    :keywords: routes,urls,pages,Snippets
    :copyright: Copyright 2007 
    :category: snippets

