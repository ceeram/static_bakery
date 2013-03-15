Hosting Admin URLs on a Subdomain
=================================

by %s on September 22, 2006

Quick tutorial on how to host admin routes on a subdomain.
Admin routing is one of those features in Cake that turns out to be a
lot handier than you think it will. I use it all the time, but
something about it bothers me.

It's probably just my own paranoia, but hosting the administrative
backend of a site on the same domain as the site itself has always
felt wrong to me. I doubt the following would improve the security of
a site any measurable degree, but (a) it makes me feel better, and (b)
it looks nice.

Let's say I'm hosting a new site at example.com, and using Cake's
admin routing, I access the admin section at example.com/admin/, but
I've also set up admin.example.com, and I want to access it from there
instead. When setting up admin.example.com, I want to point it to the
same path as example.com itself. Then, in app/config/bootstrap.php, I
add the following:


PHP Snippet:
````````````

::

    <?php 
    if (env("HTTP_HOST") == "admin.example.com") {
    	$_GET["url"] = "admin/" . $_GET["url"];
    } elseif (strpos($_GET["url"], "admin") === 0) {
    	header ("HTTP/1.0 404 Not Found");
    	die();
    }
    ?>

The first part of the if block adds "admin/" to the URL that is parsed
by Cake. The second part disables admin-URL access from any other
(non-admin) domains. Optionally, instead of just sending a 404 and
dying, you could set $_GET["url"] to a path that you know doesn't
exist in your site (i.e. "notfound/"), which will make Cake render
your "Not Found" page in production mode.

And that's it. The nice thing about this trick is that you can use it
for any kind of URL manipluation whatsoever (the SEO possibilities
abound).

.. meta::
    :title: Hosting Admin URLs on a Subdomain
    :description: CakePHP Article related to subdomain,admin,Tutorials
    :keywords: subdomain,admin,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

