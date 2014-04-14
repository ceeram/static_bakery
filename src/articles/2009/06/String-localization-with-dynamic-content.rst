String localization with dynamic content
========================================

by brightball on June 20, 2009

Cake has a wonderful shell script function built into it called
extract that will run through your code and create a .po file full of
all of the text contained within your __('My text here') calls. You
can then pass these files onto to translators to modify them for your
languages. When you want to add variables though, you have to break it
up into pieces which may change the context of the phrase. Here's a
way around that.
Normally you'd do something like this:

::

    
    <?php
    $message = __('My friend ',true) . $friend . __(' enjoys writing code.',true);
    ?>

Which will provide you a snippet of 'My friend ' and ' enjoys writing
code.' in your .po file. In a case where the context of the entire
phrase might matter, a translator will only get the small pieces
rather than the entire phrase. They aren't going to see how it's
spliced together in your application, only the individual parts.

We can solve that with the Cake's extremely handy String::insert()
method which allows for easy descriptive templating like this:

::

    
    <?php
    $message = String::insert('My friend :friend_name enjoys writing code.',array('friend_name' => $friend_name));
    ?>

And then finally make it play nice with extract like this:

::

    
    <?php
    $message = String::insert(__('My friend :friend_name enjoys writing code.',true),array('friend_name' => $friend_name));
    ?>

You only need to make translators aware that they should not modify
words with a colon prefix and then you'll get something that gives
them context to what is being said in your .po file. Now they'll only
see this:

"My friend :friend_name enjoys writing code."

.. meta::
    :title: String localization with dynamic content
    :description: CakePHP Article related to Localization,Internationalization,po,brightball,il,Snippets
    :keywords: Localization,Internationalization,po,brightball,il,Snippets
    :copyright: Copyright 2009 brightball
    :category: snippets

