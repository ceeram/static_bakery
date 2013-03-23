Decoda Helper - A BB code styled parser
=======================================

by %s on June 05, 2009

Decoda is a lightweight class that extracts and parses a custom markup
language; based on the concept of BB code. Decoda supports all the
basic HTML tags and manages special features for making links and
emails auto-clickable and using shorthand emails and links,.


Code
~~~~
I CANNOT PLACE THE CLASS CODE HERE, BECAUSE THE BB CODE WITHIN MY
CLASS CONFLICTS WITH THE BAKERYS BB CODE. Although you can `download
the code and view original documentation here`_.


Installation
~~~~~~~~~~~~
The CakePHP Helper is extremely easy to use, simply add it to your
controllers $helpers array.

::

    var $helpers = array('Decoda');

Once you have enabled the Helper, you can use it in the view like the
following. To change the settings (make clickable, shorthand, etc) you
would set the property itself within the view, instead of calling a
function like the stand-alone version.

::

    // In the views
    // Display a blog post
    $decoda->parse($blog['Blog']['content']);
    
    // Return instead of echoing
    $content = $decoda->parse($blog['Blog']['content'], true);
    
    // Change the settings before parseing content
    $decoda->useShorthand = true;
    $decoda->makeClickable = false;
    $decoda->parse($blog['Blog']['content']);



How it works!
~~~~~~~~~~~~~
Now that you have learned how to parse the markup, I will need to show
you how the markup is written. If you have used BB code before (on a
forum system), this should be relatively easy for you. Lets begin with
the basic tags.

REMOVE THE SPACES FROM THE EXAMPLES. THE BAKERY RENDERS THE OUTPUT SO
I HAD TO WRITE IT THAT WAY.

Basics The most basic of tags will require no attributes at all, these
tags are: b, i, u, code, sup, sub, h1-h6. To use these tags, you would
place the tag around the string you want altered. For example if you
want some text to be bolded, you would do the following:

::

    // Examples
    [ b]Bold[/ b] 		// Outputs <strong>Bold</strong>
    [ i]Italics[/ i]		// Outputs <em>Italics</em>
    [ h3]Title[/ h3]		// Outputs <h3>Title</h3>

Images Unlike most BB code systems, this system allows img tags to use
attributes. You are able to add a width and height attribute, which
will accept an integer and a percentage. The following examples will
work:

::

    [ img]http://www.domain.com/image.jpg[/ img]
    [ img width=100]http://www.domain.com/image.jpg[/ img]
    [ img height=50%]http://www.domain.com/image.gif[/ img]
    [ img width=100 height=50]http://www.domain.com/image.png[/ img]

Links and Emails There are a few variations for using the link and
email tags. In the example below are all the variations that are
possible. All these variations will work, regardless of your
$useShorthand setting.

::

    // Links
    [ url]http://www.domain.com[/ url]
    [ url=http://www.domain.com]My Website![/ url]
    
    // Emails
    [ mail]email@domain.com[/ mail]
    [ mail=email@domain.com]Email Me![/ mail]
    
    // You can also use [ email] instead of [ mail]

Divs If you ever need to create a div element, you can do so with the
div tags. You can pass an id and a class; you may also use multiple
classes, just separate the classes with a space.

::

    [ div id=content]Content[/ div]
    [ div class=clear float]Content[/ div]

Quotes Quotes work in a similar fashion to other BB code parsers. You
may supply an author, or do a stand alone quote. When using an author,
the author name has to be surrounded by double quotes.

::

    [ quote]Content[/ quote]
    [ quote="Author Name"]Content[/ quote]

Lists And finally we get to the lists. Currently, only the ul tag is
supported with lists.

::

    [ list]
    [ li]Item 1[/ li]
    [ li]Item 2[/ li]
    [ li]Item 3[/ li]
    [/ list]



.. _download the code and view original documentation here: http://www.milesj.me/resources/script/decoda
.. meta::
    :title: Decoda Helper - A BB code styled parser
    :description: CakePHP Article related to bb,helper,code,parser,translator,decoda,miles,milesj,johnson,Helpers
    :keywords: bb,helper,code,parser,translator,decoda,miles,milesj,johnson,Helpers
    :copyright: Copyright 2009 
    :category: helpers

