Subdomaining with Cake
======================

by Mum_Chamber on February 04, 2008

In this article, following a practical approach to subdomaining, we
will make one model available through subdomains. Can be inspiring for
alternative uses.
Inspired by the Hosting Admin URLs on a Subdomain at
`http://bakery.cakephp.org/articles/view/hosting-admin-urls-
on-a-subdomain`_, I have tried to come up with some piece of code that
makes uses of subdomains in an elegant way.

The aim is to access all instances of a model through subdomains. For
instance, every user may have their subdomains, accessible at
username.example.com

In order to deal with this, I assume that you already made the
necessary implementations in your controller. Some sample code would
look like (user_controller.php) :

::

    function view($unique_title){
    	$this->set('users', $this->User->findByUniqueTitle($unique_title) ); 
    }

Also, you need to route your users model at app/config/routes.php with
something like:

::

    Router::connect('/user/*', array('controller' => 'user', 'action' => 'view'));

Now, what we will actually do is run some piece of code before the MVC
implementation is started. The correct place to do this is the
app/config/bootsrap.php

bootstrap.php :

::

    $subdomain = substr( env("HTTP_HOST"), 0, strpos(env("HTTP_HOST"), ".") );
    if( strlen($subdomain)>0 && $subdomain != "www" ) {
    	$_GET["url"] = "user/" . $subdomain . "/" . (isset($_GET["url"]) ? $_GET["url"] : "");
    }

This piece of code is pretty self explanatory. if the subdomain is
different than "www", the page displayed is users/ / . So,
mumchamber.example.com/about is actually displaying the url
`www.example.com/users/mumchamber/about`_
To test subdomains on localhost, you may want to have a look at
`http://digitalpbk.blogspot.com/2007/01/making-subdomains-on-
localhost.html`_ Only remember that in linux, your hosts file is
probably located at /etc/hosts

Also, you may want to see `http://httpd.apache.org/docs/1.3/vhosts/`_
for virtual host documentation

.. _www.example.com/users/mumchamber/about: http://www.example.com/users/mumchamber/about
.. _http://httpd.apache.org/docs/1.3/vhosts/: http://httpd.apache.org/docs/1.3/vhosts/
.. _http://bakery.cakephp.org/articles/view/hosting-admin-urls-on-a-subdomain: http://bakery.cakephp.org/articles/view/hosting-admin-urls-on-a-subdomain
.. _http://digitalpbk.blogspot.com/2007/01/making-subdomains-on-localhost.html: http://digitalpbk.blogspot.com/2007/01/making-subdomains-on-localhost.html

.. author:: Mum_Chamber
.. categories:: articles, tutorials
.. tags:: user,subdomain,Tutorials

