

(RSS) Feed them Cake
====================

by %s on September 21, 2006

If your application includes any sort of internal messaging or
notifications, RSS is for you. For forums and blogs, feeds have become
a functional requirement. Integrating RSS feeds in your application
helps users keep tabs on how things are coming along, with out having
to check into the site constantly. This tutorial will help you set up
a basic RSS feed by helping you craft a controller action, and its
associated view (and layout).


Introduction
------------

If your application includes any sort of internal messaging or
notifications, RSS is for you. For forums and blogs, feeds have become
a functional requirement. Integrating RSS feeds in your application
helps users keep tabs on how things are coming along, with out having
to check into the site constantly.

This tutorial will help you set up a basic RSS feed by helping you
craft a controller action, and its associated view (and layout).


Getting the Data
----------------

One of the more common uses for RSS is to notify users of new posts on
a blog. The controller work to be done is rather simple: we just need
to get a relevant list of articles, sorted by the date they were
published.


Controller Class:
`````````````````

::

    <?php 
    class ArticlesController extends AppController
    {
        var $name = 'Articles';
        var $helpers = array('Time');
    
        function rss()
        {
            $this->layout = 'xml';
            $this->set('articles', $this->Article->findAll('Article.published = 1', null, 'Article.published_date DESC', 15));
        }
    }
    ?>

There are a few things here that will help us when we get around to
crafting the view and layout for this action. The Time helper will be
needed for some magical conversion to GMT time, and the

::

    $this->layout

instruction will use a new layout we'll create next.

The

::

    $this->set()

fetches the 15 most recently published articles.


The Layout and View
-------------------

Because we dont want our RSS feed placed inside an applications
default XHTML layout, we'll create a new layout for XML, at
/app/views/layouts/xml.thtml.


View Template:
``````````````

::

    
    <?php header('Content-type: text/xml'); ?>
    <?php echo $content_for_layout; ?>

Pretty simple, right? We just assign a different content-type header,
and spit out the feed. Let's take a look at the view. All we need to
do here is craft the XML for the feed. There are a few channel setup
tasks, then we loop through the

::

    $articles

variable to create the feed listings. Let's take a look:


View Template:
``````````````

::

    
     <rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
      <channel>
        <title>The Title of My Awesome Feed</title>
        <link>http://www.example.org/</link>
        <description>The description of my awesome feed.</description>
        <language>en-us</language>
        <pubDate><?php echo date("D, j M Y H:i:s", gmmktime()) . ' GMT';?></pubDate>
        <?php echo $time->nice($time->gmt()) . ' GMT'; ?>
        <docs>http://blogs.law.harvard.edu/tech/rss</docs>
        <generator>CakePHP</generator>
        <managingEditor>editor@example.com</managingEditor>
        <webMaster>webmaster@example.com</webMaster>
        <?php foreach ($articles as $article): ?>
        <item>
          <title><?php echo $article['title']; ?></title>
          <link>http://www.example.com/articles/view/<?php echo $article['id']; ?></link>
          <description><?php echo $article['intro']; ?></description>
          <?php echo $time->nice($article['published_date']) . ' GMT'; ?>
           <pubDate><?php echo $time->nice($time->gmt($article['published_date'])) . ' GMT'; ?></pubDate>
          <guid>http://www.example.com/articles/view/<?php echo $article['id']; ?></guid>
        </item>
        <?php endforeach; ?>
      </channel>
    </rss>

Point your users to /articles/rss and they'll be in RSS heaven - and
you haven't even written 40 lines of code.

Now that you have the basic idea, you can modify your rss() action to
take parameters that allow your users to select different filtered
versions of your feed information, or make other changes specific to
your application.

Happy feeding.


.. meta::
    :title: (RSS) Feed them Cake
    :description: CakePHP Article related to feeds,Rss,xml,Tutorials
    :keywords: feeds,Rss,xml,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

