Automatically Generate Dynamic Sitemaps
=======================================

by masterkeedu on August 26, 2008

As little CakePHP applications flourish into popular web sites, the
need for automatic sitemap maintenance becomes apparent. With this
simple controller / view combination you can keep search engines (and
users) up to date on your latest content. Generation of 1638 url's
required 2 queries and page load took < 1.5 seconds.

Sitemaps are although not critical, have been accepted as a standard
way to let engines and users find the content on your site.

You can generate those sitemaps on the fly in Cake , and show xml to
engines, and formatted text to users.

Sitemaps that are generated dynamically are always up to date, which
is critical in achieving those top search results.

How you may ask? Read on and I shall tell you.

You need to decide what content goes in the sitemap. Most would agree
that things like pages, posts are good choices. Others way want to add
user profiles or other various model records.

In this example I care about two models Info, which are like my static
pages, and Post which are user posts.


Create the controller ( /app/controllers/sitemaps_controller.php)
`````````````````````````````````````````````````````````````````

Controller Class:
`````````````````

::

    <?php 
    <?php
    class SitemapsController extends AppController{
    
    	var $name = 'Sitemaps';
    	var $uses = array('Post', 'Info');
    	var $helpers = array('Time');
    	var $components = array('RequestHandler');
    
    	function index (){	
    		$this->set('posts', $this->Post->find('all', array( 'conditions' => array('is_published'=>1,'is_public'=>'1'), 'fields' => array('date_modified','id'))));
    		$this->set('pages', $this->Info->find('all', array( 'conditions' => array('ispublished' => 1 ), 'fields' => array('date_modified','id','url'))));
    //debug logs will destroy xml format, make sure were not in drbug mode
    Configure::write ('debug', 0);
    	}
    }
    ?>
    ?>

Now rather then building our xml in the standard layout, well need a
nice clean xml doctype layout instead.


Create the xml layout (/app/views/layouts/xml/default.ctp)
``````````````````````````````````````````````````````````

::

    
    <?php header('Content-type: text/xml'); ?>
    <?php echo $content_for_layout; ?>

Now that we have a nice clean xml layout, we can populate it using a
cool sitemap view.


Create the sitemap view (/app/views/sitemaps/xml/index.ctp)
```````````````````````````````````````````````````````````

View Template:
``````````````

::

    
    <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    	<url>
    		<loc><?php echo Router::url('/',true); ?></loc>
    		<changefreq>daily</changefreq>
    		<priority>1.0</priority>
    	</url>
    	<!-- static pages -->	
    	<?php foreach ($pages as $post):?>
    	<url>
    		<loc><?php echo Router::url('/'.$post['Info']['url'],true); ?></loc>
    		<lastmod><?php echo $time->toAtom($post['Info']['date_modified']); ?></lastmod>
    		<priority>0.8</priority>
    	</url>
    	<?php endforeach; ?>
    	<!-- posts-->	
    	<?php foreach ($posts as $post):?>
    	<url>
    		<loc><?php echo Router::url(array('controller'=>'posts','action'=>'view','id'=>$post['Post']['id']),true); ?></loc>
    		<lastmod><?php echo $time->toAtom($post['Post']['date_modified']); ?></lastmod>
    		<priority>0.8</priority>
    	</url>
    	<?php endforeach; ?>
    </urlset>

You'll notice the use of the Router class to give up the proper fully
expanded domain. You can see my two model names 'Info' and 'Post' that
were set in the controller.

Almost DOne!

We need to let Cake parse extensions like xml, and instead use them as
part of our directory structure. This turns urls like
/sitemaps/index.xml into /views/sitemaps/xml/index.ctp and uses the
appropriate layout based on extension as well, pretty cool huh?(hence
both views belong to xml folders above)

(You'll notice I also parse rss extension for my news feed, but thats
another post.)

In /app/config/routes.php add;
``````````````````````````````

::

    
    Router::parseExtensions('rss','xml');

Your done, now if you want to class it up, add a better route than
/sitemaps/index.xml

again, in /app/config/routes.php add;
`````````````````````````````````````

::

    
    Router::connect('/sitemap', array('controller' => 'sitemaps', 'action' => 'index'));

Now `http://example.org/sitemap.xml`_ will dynamically create the most
up to date sitemap possible! Go ahead and submit it to google.

All done, enjoy.


Summary
```````
My goal was to provide a instance that took advantage of Cake's Router
class and eliminated the need to statically code any urls.
Perks;

#. Works to serve multiple domain sites. Ex. if your site is hosted on
   example.com, and example.org, both sitemaps will have the proper urls
   even though they are physically the same code.
#. Can be reused across applications
#. If you serve multiple applications, the code can be used as part of
   the core shared by all those apps.
#. Never needs to be updated!



.. _http://example.org/sitemap.xml: http://example.org/sitemap.xml
.. meta::
    :title: Automatically Generate Dynamic Sitemaps 
    :description: CakePHP Article related to automatic,sitemap,Tutorials
    :keywords: automatic,sitemap,Tutorials
    :copyright: Copyright 2008 masterkeedu
    :category: tutorials

