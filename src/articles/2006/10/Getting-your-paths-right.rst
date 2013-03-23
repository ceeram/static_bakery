Getting your paths right
========================

by %s on October 16, 2006

The CakePHP manual is quite clear about the fact that images, style
sheets and javascript files should commonly be put into respectively
webroot/[b]img[/b], webroot/[b]css[/b] and webroot/[b]js[/b]. However,
it is not clear for someone who is new to CakePHP what is the general
rule for giving the correct path to these ressources. Routinely the
designer who has to work with CakePHP may wonder why using routes,
static pages and controller/actions do not yeld the same results as to
path correctness. While testing the framework, the newbie is suggested
that helpers be used to correctly output some familiar HTML tags. One
then comes to transform all tags into $html->image() method calls. But
what about background images ? and what about type="image" inputs ?


Using the webroot property in the html helper
`````````````````````````````````````````````

After trying the various CakePHP global constants (ROOT, WEBROOT_DIR,
WWW_ROOT, CSS, etc.) with no results, the general solution seems to be
found in the $this->webroot property that returns the path to the
application webroot. Thus for the various cases above we may have in
our layouts, views or elements:

A simple image tag:

::

    
    <img src="<?php echo $this->webroot; ?>img/foo.gif" .../ >

A background image within a table cell the old way:

::

    
    <td background="<?php echo $this->webroot; ?>img/foo.gif">

A background image within a table cell, the styled way (illustrating
external CSS case as well):

::

    
    <td style="{ background-image: url(<?php echo $this->webroot; ?>img/foo.gif) }">

An input of type="image":

::

    
    <input type="image" src="<?php echo $this->webroot; ?>img/go_btn.gif" ... />



Coding consistently
```````````````````
For consistency's sake, one may even use exclusively this property for
all paths and links inside the application. Instead of replacing img
tags everywhere while leaving backgrounds and image inputs with a
different syntax (applies also to CSS paths, Javascript paths and
links), one may bypass the use of some of the helper methods
altogether (such as $html->css(), $javascript->link()) and use only
the sole webroot helper property.

.. meta::
    :title: Getting your paths right
    :description: CakePHP Article related to paths webroot,Tutorials
    :keywords: paths webroot,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

