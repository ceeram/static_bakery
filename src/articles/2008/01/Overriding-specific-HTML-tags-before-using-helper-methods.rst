Overriding specific HTML tags before using helper methods
=========================================================

by icedcheese on January 14, 2008

CakePHP 1.2 allows you to override CakePHP's defined tags. Yeah, I
know, so did CakePHP 1.1, but now you can define your own base helper,
from which all CakePHP helpers will inherit, and only override the
tags you decide. That's right, no more copying tags.ini.php to modify
it. Just define what you need, and how you need it. Let's see how.

I got this from Mariano, which was so useful I thought I would put it
in the bakery:
`http://cricava.com/blogs/index.php?blog=6=overriding_specific_html_ta
gs_before_usi=1=1=1=1`_

Let's start by creating a sample page to test both the link tag, and
the paragraph tag. Create a file named test.ctp in your
app/views/pages directory and add the following contents to it:

::

    
    <?php debug(h($html->link('Love CakePHP', 'http://www.cakephp.org'))); ?>
    <?php debug(h($html->para('myclass', 'This is my text'))); ?>


As you can see, we are wrapping those calls with debug() and h(),
which encodes HTML entities, to take a look at the generated tag
without having to view the HTML source of the page. You can naturally
access this page by going to the URL /pages/test of your CakePHP
installation. After doing so you'll get the source for both tags:

::

    
    <a href="http://www.cakephp.org" >Love CakePHP</a>
    <p class="myclass">This is my text</p>


Continue by creating a file named app_helper.php in your app/
directory (the same place where files like app_controller.php and
app_model.php reside). Edit it and insert the following into it:

::

    
    <?php 
    class AppHelper extends Helper {
        function __construct() {
            parent::__construct();
        }
    }
    ?>


Ok, that's not fun right? Nothing in there really. Let's start by
pretending that we want to override the anchor tag (A tag, used to
build links), since we need (for some strange reason) to make sure
that absolutely *all* links built with CakePHP's helper functions have
a DIV tag wrapping them. Create now a file named tags.php and place it
in your app/config folder. Edit it and insert the following:

::

    
    <?php 
    $tags = array(
    	'link' => '<div class="link"><a href="%s" %s>%s</a></div>'
    );
    ?>


Ok so we took what was defined as the link tag in
cake/view/helpers/html.php and added it the wrapping DIV. But how do
we tell CakePHP to override its definition for the link tag with our
own? Go back and edit the file app/app_helper.php, and change it so it
now looks like this:

::

    
    <?php 
    class AppHelper extends Helper {
        function __construct() {
            parent::__construct();
            $this->loadConfig();
        }
    }
    ?>


The added call will tell CakePHP to automatically look for a file
named app/config/tags.php, and merge its $tags array with CakePHP
built-in ones. Exactly what we needed. If we go back and access our
testing page, you'll see that the link tag changed to now look like:

::

    
    <div class="link"><a href="http://www.cakephp.org" >Love CakePHP</a></div>


When you call loadConfig() you can also specify what is the name of
the file it should look for (just the name, no directory nor php
extension.) This can be used to our advantage in case we decide to
place our regular tag override in app/config/tags.php, and some
temporary overrides in another file. To test this, let's assume that
we also want to change the paragraph tag to include a wrapper div, but
we don't want to do it in the tags.php file, but on a different file.
Let's create a file named tags-para.php and place it in app/config.
Edit it and insert the following contents:

::

    
    <?php 
    $tags = array(
    	'para' => '<div class="paragraph"><p%s>%s</p></div>'
    );
    ?>


Let's go back and edit the file app/app_helper.php so it now looks
like this:

::

    
    <?php 
    class AppHelper extends Helper {
        function __construct() {
            parent::__construct();
            $this->loadConfig();
            $this->loadConfig('tags-para');
        }
    }
    ?>


Go back to our testing page, and we should now see both tags changing:

::

    
    <div class="link"><a href="http://www.cakephp.org" >Love CakePHP</a></div>
    <div class="paragraph"><p class="myclass">This is my text</p></div>


You can naturally use this method to override any tags defined by
CakePHP, including those used in the FormHelper (always look for their
definition in the HtmlHelper file.)


.. _=1: http://cricava.com/blogs/index.php?blog=6&title=overriding_specific_html_tags_before_usi&more=1&c=1&tb=1&pb=1
.. meta::
    :title: Overriding specific HTML tags before using helper methods
    :description: CakePHP Article related to HtmlHelper,overwrite,Helpers
    :keywords: HtmlHelper,overwrite,Helpers
    :copyright: Copyright 2008 icedcheese
    :category: helpers

