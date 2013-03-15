Routing with Multiple Subdomains
================================

by %s on August 26, 2008

Ever want to have multiple admin routes and use subdomains?
After wanting to integrate 3 different parts of an application I wrote
into one app, I looked and found some great pieces of information in
the bakery on having more then one admin route and using admin routes
on sub domains. So after playing and wanting an easier way to define
everything.

Resources Used on the Bakery
`Using CAKE_ADMIN for multiple user types`_ - By Egbert Teeselink
`Hosting Admin URLs on a Subdomain`_ - By Nate

This is what we added to our boostrap.php

Component Class:
````````````````

::

    <?php 
    $url = explode('.',env('HTTP_HOST'));
    
    switch ($url[0]) {
    	case "admin":             
    		Configure::write('Routing.admin', 'admin');
    		$_GET["url"] = "admin/" . str_replace('admin/','',$_GET['url']);
    	break;
    	case "support":        
    		Configure::write('Routing.admin', 'support');
    		$_GET["url"] = "support/" . str_replace('support/','',$_GET['url']);
    	break;
    	default:
    }
    ?>


Feedback is appreciated and hope this helps other people.

.. _Hosting Admin URLs on a Subdomain: :///home/marc/public_html/bakery.cakephp.org/bakery/tmp/Hosting
.. _Using CAKE_ADMIN for multiple user types: :///home/marc/public_html/bakery.cakephp.org/bakery/tmp/Using
.. meta::
    :title: Routing with Multiple Subdomains
    :description: CakePHP Article related to routing,subdomain,admin,Snippets
    :keywords: routing,subdomain,admin,Snippets
    :copyright: Copyright 2008 
    :category: snippets

