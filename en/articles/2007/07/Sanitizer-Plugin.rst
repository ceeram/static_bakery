

Sanitizer Plugin
================

by %s on September 28, 2010

When you're dealing with a lot of user submitted data, it's important
to sanitize it. CakePHP provides a Sanitizer class that provides much
of the functionality you need to deal with unclean data, but it can be
a pain iterating through your data before saving it.
Sanitizer is CakePHP plugin that makes it very easy to automatically
sanitize your data. For example, if you don't want HTML tags in some
field and want clean HTML tags in others, Sanitizer makes it easy to
automatically get what you want.

Download it here: `http://codaset.com/jeremyharris/sanitizer`_

Usage
~~~~~

Simply add the behavior to your model:


Model Class:
````````````

::

    <?php var $actsAs = array('Sanitizer.Sanitize');?>

Now just define your sanitization rules. These rules are formatted
similar to CakePHP's validation rules. The Sanitize behavior uses the
built-in Sanitize class. By default, uses Sanitize::clean() and strips
html. To use a different method on the Sanitize class, set the
$sanitize var with the key as the field name and the value the method.
Optionally pass an array with the options that you would normally pass
to the Sanitize method you want to use.


Model Class:
````````````

::

    <?php 
    // clean the name field using Sanitize::html()
    var $sanitize = array(
        'name' => 'html'
    );
    
    // or clean the name field using Sanitize::paranoid() and allowing '%'
    var $sanitize = array(
        'name' => array(
            'paranoid => array('%')
        )
    );
    ?>

If you don't define the field in the $sanitize var on the model, the
Sanitize
behavior will automatically use Sanitize::clean($value,
array('remove_html' => true));
on every field passed on Model::save().

If you wish to skip a specific field, set the field to false


Model Class:
````````````

::

    <?php 
    // clean everything except the description
    var $sanitize = array(
        'description' => false
    );
    ?>

If you wish to skip everything for a specific model, set the $sanitize
var to
false. This is useful if you want to sanitize everything by default by
applying
the behavior to your AppModel but have special case models.


Model Class:
````````````

::

    <?php 
    // sanitize nothing on this model
    var $sanitize = false;
    ?>

Sanitization methods supported:

#. clean
#. html
#. paranoid
#. stripAll
#. stripImages
#. stropScripts
#. stripWhitespace



Advanced
~~~~~~~~

By default, the Sanitize behavior cleans the data before validation.
If you want
validate then sanitize, use:


Model Class:
````````````

::

    <?php 
    var $actsAs = array(
        'Sanitizer.Sanitize' => array(
            'validate' => 'before'
        )
    );
    ?>

See the test cases in the plugin for code samples.


Closing
~~~~~~~

The goal of this plugin is to make you worry less about what your
users are entering in your forms. If you want them to allow html, then
you should clean it up and allow it! If not, then strip it all away!
Enjoy!

Notes
If you upload files, the Sanitize behavior will by default sanitize
and escape the file path. Make sure to set 'file' => false in your
$sanitize var!

Plans
I would like to add the ability to define the default sanitization
rule. I also am planning on including the ability to define your own
sanitization rules, similar to how you can define your own validation
rules.

.. _http://codaset.com/jeremyharris/sanitizer: http://codaset.com/jeremyharris/sanitizer
.. meta::
    :title: Sanitizer Plugin
    :description: CakePHP Article related to behaviour,data,santize,Plugins
    :keywords: behaviour,data,santize,Plugins
    :copyright: Copyright 2007 
    :category: plugins

