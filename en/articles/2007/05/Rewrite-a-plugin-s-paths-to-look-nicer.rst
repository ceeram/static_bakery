Rewrite a plugin's paths to look nicer.
=======================================

by %s on May 13, 2007

Plugins are a fantastic and vastly underused tool in CakePHP! But
their default paths can be strange sometimes. This article describes
how to rewrite a plugin's paths using bootstrap.php.


Overview
~~~~~~~~

Plugins are a fantastic and vastly underused tool in CakePHP. If you
aren't familiar with plugins take a few minutes to peek at the manual
for a great example: `http://manual.cakephp.org/chapter/plugins`_.

One thing you may have noticed in the manual chapter, or in your own
plugin, is that the URL paths for plugins are kind of strange if you
follow the correct naming conventions of your plugin. For instance,
say we have built a Forums plugin consisting of a few controllers,
namely `forums_topics` and `forums_posts`. The default base URL's for
these controllers are:

/forums/forums_topics/forums/forums_posts

At this point you might say, "Why don't we just rename our
`forums_posts` to `posts`?". This is a common move that many people
make but consider this (from Section 2 of the Plugins chapter of the
manual):

While it isn't required, it is recommended that you name your plugin
controllers something relatively unique in order to avoid namespace
conflicts with parent applications.

So for the sake of this article and the argument of convention we will
keep the names of the controllers how they should be.


Rewriting with bootstrap.php
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By using the ./app/config/bootstrap.php we can rewrite the URL before
it reaches the dispatcher. (There are a few other handy things you can
do with bootstrap.php but that is for another article). Our goal is to
take a URL like /forums/posts/view/1 and rewrite it to
/forums/forums_posts/view/1 before the dispatcher sees anything
(sneeky!).

./app/config/bootstrap.php

::

    <?php
    // set the plugin name and the controllers we want to hack
    $rewritePlugins = array(
    	'forums' => array('posts', 'topics'),
    );
    // rewrite it!
    foreach($rewritePlugins as $plugin=>$options) {
    	$_GET['url'] = preg_replace('%^('.$plugin.')/('.implode('|', $options).')%i', '\\1/\\1_\\2', $_GET['url']);
    }
    ?>


You should now be able to go to /forums/posts/view/11 and it work
correctly!

A few things to note:


#. Notice there is not "catch-all" part of this rewrite. If we were to
   catch all '/forums/catch_this' and rewrite it to
   '/forums/forums_catch_this' then we wouldn't be able to access
   anything in the 'forums_controller.php'. So you must define EACH
   controller you want to rewrite.
#. [li]If your plugins are scaffolded all the links that CakePHP
   generated will be redirected back to the default 'forums_xxxxx'. To
   CakePHP the URL is actually /forums/forums_posts/view/1 even through
   the address bar reads /forums/posts/view/1



.. _http://manual.cakephp.org/chapter/plugins: http://manual.cakephp.org/chapter/plugins
.. meta::
    :title: Rewrite a plugin's paths to look nicer.
    :description: CakePHP Article related to plugin,Rewrite,bootstrap,plugins,Snippets
    :keywords: plugin,Rewrite,bootstrap,plugins,Snippets
    :copyright: Copyright 2007 
    :category: snippets

