Separating view's markup and logic using QueryTemplates
=======================================================

by Tobiasz.Cudnik on January 13, 2009

In this article i want to show you how to split the View layer into
more comfortable parts.
MVC gives you 3 layers of separation - Model, Controller and the View.
This third is the one i want to talk about in this article. What
actually have a place out there ? Simplest answer is "markup of
requested page, or it's part, is created". CakePHP uses native PHP
features to complete this operation. Everybody knows how it looks like

View Template:
``````````````

::

    <?php foreach($posts as $post): ?>
    <h2><?php print $post['Post']['title']; ?></h2>
    <ul>
      <li><?php print $post['Post']['author']; ?></li>
      <li><?php print $post['Post']['body']; ?></li>
    </ul>
    <?php endforeach; ?>

Now, what this really is ? It's a mix of document structure (markup)
WITH dynamic data injections, iteration instructions and possibly
conditional statements. If this is true and true is that what can be
mixed, can be not mixed and we will apply this in context of data
sources/storage, we end up with separating markup (HTML) template into
one (or more) files and some mechanism which allows us to do things
that were mentioned and those are, again:

+ dynamic data injections
+ iteration instructions
+ conditional statements

You may say there's already such layer and it is XSLT. That's correct.
But XSLT goes far beyond scope of the problem, IMHO. Mosty because
it's syntax is markup itself. It's markup-transforming-markup-to-
another-markup. Thats great, but it's not the right tool that we need
for such job. Although it's point of view can be helpful. I think it's
about "least change is best option". We don't have much change in
markup-transforming-markup-to-another-markup. It's markup all the
time. But inside an application we have programming language, not
markup language. It's probably obvious we should use programming
language to implement layer mixing up View ingredients. Now following
"least change" rule, we can see that there's common pattern of doing
things with markup. It can be called load-traverse-modify, and it's
used mostly in client-side development thanks to many popular web 2.0
JavaScript frameworks, like jQuery. Summing thing up, we have divided
View layer into 2 sub-layers which are:

+ template source as pure markup document
+ template logic as load-traverse-modify DOM API

This is quite exactly what QueryTemplates[1] engine is doing. It loads
various template sources from plain HTML files. After that it
traverses document using jQuery API (CSS selectors) and modifies it
using dedicated templating methods. Look how previous example can look
like when written using QueryTemplates. First the source template.

View Template:
``````````````

::

    <h2 class='title'>lorem ipsum</h2>
    <ul>
      <li class='author'>lorem ipsum</li>
      <li class='body'>lorem ipsum</li>
    </ul>

Isn't it much cleaner ? Now the template logic.

View Template:
``````````````

::

    
    <?php
    template()
      ->sourceCollect('template.html')
      ->parse()
        ->source('template.html')->returnReplace()
        ->varsToSelector('post', $posts[0]['Post'])
        ->find('> *')
          ->loop('posts', 'post')
    ;

It can also be written line-by-line, but chain-style is preferred.
Using above code we can produce native template presented in the first
snippet of this article.

You can easily integrate QueryTemplates into any CakePHP application
with following code:

::

    <?php
    App::import('Vendor', 'QueryTemplates', array(
    	'file' => 'QueryTemplates.php',
    ));
    QueryTemplates::$targetsPath = TMP.'cache/views/';
    QueryTemplates::$sourcesPath = APP.'webroot/templates/';

There's the CakeForms[2] plugin, which allows to rapidly convert pure
markup form into CakePHP FormHelper controls. You can see all this in
action in example QT Blog[3], which is working CakePHP application
using QueryTemplates library. There are extensive examples[4] of
framework-independent templates on the project's wiki.

+ [1] `http://code.google.com/p/querytemplates/`_
+ [2] `http://code.google.com/p/querytemplates/wiki/CakeForms`_
+ [3]
  `http://code.google.com/p/querytemplates/wiki/BlogImplementation`_
+ [4] `http://code.google.com/p/querytemplates/wiki/Examples`_



.. _http://code.google.com/p/querytemplates/wiki/Examples: http://code.google.com/p/querytemplates/wiki/Examples
.. _http://code.google.com/p/querytemplates/wiki/BlogImplementation: http://code.google.com/p/querytemplates/wiki/BlogImplementation
.. _http://code.google.com/p/querytemplates/wiki/CakeForms: http://code.google.com/p/querytemplates/wiki/CakeForms
.. _http://code.google.com/p/querytemplates/: http://code.google.com/p/querytemplates/
.. meta::
    :title: Separating view's markup and logic using QueryTemplates
    :description: CakePHP Article related to Layouts,Template,dom,Tutorials
    :keywords: Layouts,Template,dom,Tutorials
    :copyright: Copyright 2009 Tobiasz.Cudnik
    :category: tutorials

