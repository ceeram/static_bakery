Haml: Markup Haiku
==================

by chess64 on July 15, 2007

Haml takes your gross, ugly templates and replaces them with veritable
Haiku. Haml is based on one primary principal: [i]markup should be
beautiful[/i]. Check out [url]http://haml.hamptoncatlin.com/[/url].
This is a guide to using `Haml`_ in CakePHP instead of the normal
.ctp/.thtml files. Note: this uses `phphaml`_ which is for PHP5.

First of all, download the HamlParser and HamlView from
`http://cakeforge.org/snippet/detail.php?type=package=35`_. Place
HamlParser.class.php in the app/vendors/haml/ directory and place
haml.php in the app/views/ directory. Create the directory
app/tmp/haml and make it world-writable (chmod 0777). This is where
the compiled php files are placed. Next, in your AppController, put
var $view = 'Haml'; . Now all view files with the extension '.haml'
will be parsed as Haml. That's it!

Here's a sample Haml layout (app/views/layouts/default.haml):

::

    
    !!! Strict
    %html
      %head
        %meta{ :http-equiv => 'Content-Type', :content => 'text/html;charset=utf-8' }
        %title= $title_for_layout
      %body
        #header
          %h1 hello
        #content= $content_for_layout
        #footer
          %span.author John Q. Caker

And here's a sample view (app/views/hello/index.haml):

::

    
    .hello
      %p Hello, world!
      = $html->link('This is a link to the home page', '/')

As you can see, using helpers inside Haml is no trouble at all.

.. _phphaml: http://phphaml.sourceforge.net
.. _=35: http://cakeforge.org/snippet/detail.php?type=package&id=35
.. _Haml: http://haml.hamptoncatlin.com/
.. meta::
    :title: Haml: Markup Haiku
    :description: CakePHP Article related to Template,haml,Snippets
    :keywords: Template,haml,Snippets
    :copyright: Copyright 2007 chess64
    :category: snippets

