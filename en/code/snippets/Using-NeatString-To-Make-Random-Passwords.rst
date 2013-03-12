

Using NeatString To Make Random Passwords
=========================================

by %s on November 11, 2006

In my hunt for a simpler way to generate a random password, I ran
across a little class that CakePHP already has.
A neat function I just discovered while looking for a useful CakePHP
tip to save me from putting my head into a cake is contained in the
NeatString class which rests in /cake/libs/neat_string.php

The function of interest is NeatString::randomPassword($length,
$available_chars) which can be used to create a random password with a
given $length which is made out of the $available_chars. By default
$available_chars contains [A-Z0-9] (All upper case letters + numbers),
but you can extend it to use any characters you consider valid for a
password (like underscores, dot's, etc.).

The usage of the function is rather simple, but let's take a look at a
little example anyway:

Imagine you want to generate a simple password made up of 8 letters in
your controller and display it to the user:


Controller Class:
`````````````````

::

    <?php 
    uses('neat_string');
    $this->set('password', NeatString::randomPassword(8));
    ?>

Or be a little bit more fancy and allow a whole bunch of other
characters:

Controller Class:
`````````````````

::

    <?php 
    NeatString::randomPassword(8, '.,#[]()\$!/\\&+-§%=abcdefghijklmnopqrstuvwxyzABDEFHKMNPRTWXYABDEFHKMNPRTWXY23456789');
    ?>

Taken from `http://www.thinkingphp.org/2006/09/15/dessert-3-generate-a
-random-password/`_

NeatString also hold some other little functions that could be useful.

NeatString::toArray($string) will split any string into an array of
characters, with whitespace removed.

NeatString::toCompressed($string) removes all whitespace, and convert
it to lowercase.

NeatString::toRoman($string) will convert all Cyrillic characters into
roman ones.

.. _http://www.thinkingphp.org/2006/09/15/dessert-3-generate-a-random-password/: http://www.thinkingphp.org/2006/09/15/dessert-3-generate-a-random-password/
.. meta::
    :title: Using NeatString To Make Random Passwords
    :description: CakePHP Article related to neatstring,random,password,Snippets
    :keywords: neatstring,random,password,Snippets
    :copyright: Copyright 2006 
    :category: snippets

