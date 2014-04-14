For beginners: famous pitfalls with cake development
====================================================

by alexdd55 on September 28, 2009

Starting from scratch in cakephp is not always easy, especially when
you are not used to MVC frameworks. I hope this will help other
beginners to make their life easier ;-)


Incorrect filenames
~~~~~~~~~~~~~~~~~~~
It happens to me over and over again, writing a filename with or
without an "s" at the end, in code and as a filename also. This drives
me crazy, because it takes hours and hours to find this mistake, if
you only watch the code.

Try to stick with the conventions:
Contollers are always plural, e.g. users_controller.php
Models are singular, e.g. user.php


Remember to use "echo" in views
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Building a form with the form helper and it isn' there... very
anoying. So check first if you use echo on all relevant lines ;-)

I try to use plain html instead of php embedded html, so i have to
write

::

    
    <div>
    <?php echo $some['array']; ?>
    </div>

instead of

::

    
    <?php
    echo '<div>';
    echo $some['array'];
    echo '</div>';

This helps also in IDEs like Netbeans with codeformating, which will
also raise productivity.



Correct paths to images
~~~~~~~~~~~~~~~~~~~~~~~
You will experience problems, when you do not use the HtmlHelper for
images in your views.
The HtmlHelper will always create the right paths to the pictures, no
matter where the site in your structur is found. Using a standard html
img tag will often lead to a problem in your view, so the images will
not be shown.

Links
HtmlHelper: `http://book.cakephp.org/view/835/image`_


Unexpected results in ajax calls
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
This can be a pain in the back. All logic is fine, but the result is
not that what you expected?
I know.. happens almost everytime to me, after creating a new method.
So, check your debug level. It can mess up the result.

::

    
    function foobar()  {
       configure::write('debug',0);
       // somthing happens here
    }

This will help a lot and will save time.


Nice urls
~~~~~~~~~
With thinking about SEO and nice urls, so a lot of people (incl. me in
the beginning) try to give controllers and actions a name that will
look great in the addressbar.
[p]Avoid that until you know what you are doing
Take what the database and the name convention gives you, just make
everything work first and do not worry about the nice urls.
In the beginning i spend almost half of my time with thinking about
names and stuff, don't waste your time and use the html-helper for
links, like you are supposed to do, and later, when you are done
coding, routes will do everything for you.
You will be surprised how great it works, because the routing will
change all related links automatically, when the links are build with
the html-helper

Links
HtmlHelper: `http://book.cakephp.org/view/206/Inserting-Well-
Formatted-elements#link-836`_
Routing: `http://book.cakephp.org/view/46/Routes-Configuration`_


Put the query where it belongs
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
In the begining i always put my query in the controller, because it
was an easy way to get what i want and it didn't bother me in any way.
After getting used to the cakephp, i found out that it was just
stupid, because the structure won't stay clean.
To stay on top of things, put all that stuff in the related model and
get the data from there.
The controller will stay lean and thats what you want... just believe
it.
If your projects starts to get more complex, you will be thankful that
you did it that way ;-)



...and always remember...
~~~~~~~~~~~~~~~~~~~~~~~~~
No matter what you are about to do, this is still PHP and you CAN do
almost everything you want,everywhere.
But be careful with that, because getting too far away from the way
you are supposed to do things, could make you rework your "lazy" code
again, after having some weeks experience with developing in cake.

.. _http://book.cakephp.org/view/46/Routes-Configuration: http://book.cakephp.org/view/46/Routes-Configuration
.. _http://book.cakephp.org/view/835/image: http://book.cakephp.org/view/835/image
.. _http://book.cakephp.org/view/206/Inserting-Well-Formatted-elements#link-836: http://book.cakephp.org/view/206/Inserting-Well-Formatted-elements#link-836
.. meta::
    :title: For beginners: famous pitfalls with cake development
    :description: CakePHP Article related to tips,beginner,basic,advice,how to,General Interest
    :keywords: tips,beginner,basic,advice,how to,General Interest
    :copyright: Copyright 2009 alexdd55
    :category: general_interest

