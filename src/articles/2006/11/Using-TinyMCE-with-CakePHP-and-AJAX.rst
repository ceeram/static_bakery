Using TinyMCE with CakePHP and AJAX
===================================

by TommyO on November 08, 2006

jtreglos wrote an excellent article for integrating TinyMCE into your
applications. This takes it one step further, making it possible to
have powerful wysiwyg features almost anywhere.

This tutorial assumes you\'ve already read Using TinyMCE with CakePHP
(`http://bakery.cakephp.org/articles/view/60`_). Here I will address
some of the questions from the comments of that tutorial.

How does TinyMCE work?
----------------------

I won\'t go into the gory details, but knowing some of the "How" might
help to understand a lot of the "Why".

#. TinyMCE doesn\'t play nicely with Script.aculo.us. It\'s not really
   explained anywhere, except maybe on a back page of some forum about
   javascript effects. I don\'t understand it, I just followed one simple
   rule and everything got better: load TinyMCE code before loading
   Script.aculo.us code.
#. TinyMCE instantiates itself at page load. It immediately attaches
   itself to all textareas that exist or that you specify. If the
   textarea does not yet exist, as is often the case with AJAX forms,
   then the form element remains untouched.
#. TinyMCE isn\'t really your textarea. Actually, it temporarily
   replaces your textarea with a table and an iframe. It also places a
   javascript event trigger on the submit button which copies all of the
   data from the editor\'s iframe back to your textaea before saving.


Configuration
-------------

In response to point 1
``````````````````````

You need to load the code before you load Script.aculo.us. Change the
line in your layout(s) to read:

PHP Snippet:
````````````

::

    <?php 
    if(isset($javascript)):
        ...
        echo $javascript->link('prototype');
        echo $javascript->link('tiny_mce/tiny_mce');
        echo $javascript->link('scriptaculous');
        ...
    endif;
    ?>


Note: Script.aculo.us is not required and not used in this tutorial,
but knowing this will save you hours of time debugging.

In response to point 2
``````````````````````

We will turn off TinyMCE\'s automatic attachment to textareas on load,
and instead trigger it when necessary. Change your init code to read:

HTML:
`````

::

    
    <script type="text/javascript">
        tinyMCE.init({
            theme : "simple",
            mode : "none",
            convert_urls : false
        });
    </script>


The mode setting is what we need changed here.

Note: the block that initiates TinyMCE must be part of the base page.
It may very well be best suited right in your template file. DRY
design is best, and here it\'s almost mandatory.

Now in the view that generates the form, after the textarea is
rendered, we need to initiate TinyMCE. It would be best to script
this, especially if you want to bind TinyMCE to all textarea\'s, but I
include it here for clarity.

PHP Snippet:
````````````

::

    <?php 
    echo $html->textarea('Model/field', array('cols' => '60', 'rows' => '2'));
    echo $javascript->codeBlock("tinyMCE.addMCEControl($('ModelField'),
    'ModelField');");
    ?>


In response to point 3
``````````````````````

Now that TinyMCE is bound and loaded on your form field, we need to
make sure that the data in the editor makes it back into the form
before the save is triggered. We also want to unbind TinyMCE from the
field to keep future instances of the editor from tripping over
itself. Again, it would be best to scipt this, but I provide it here
for clarity. Add a 'before' entry to your AJAX submit options, like
so:

PHP Snippet:
````````````

::

    <?php 
    echo $ajax->submit( 'Save', array(
                    ...
                    'before'=>"tinyMCE.triggerSave();
                        tinyMCE.execCommand(
                            'mceRemoveControl', true, 'ModelField');",
                    ...));
    ?>



There\'s a TinyMCEHelper just waiting to be written
---------------------------------------------------

With so many more features to be utilized, and integration with
Script.aculo.us yet to be done, there would be great use for a helper.
The gauntlet has been thrown down - who will stand up to the
challenge?


.. _http://bakery.cakephp.org/articles/view/60: http://bakery.cakephp.org/articles/view/60
.. meta::
    :title: Using TinyMCE with CakePHP and AJAX
    :description: CakePHP Article related to WYSIWYG,TinyMCE,Tutorials
    :keywords: WYSIWYG,TinyMCE,Tutorials
    :copyright: Copyright 2006 TommyO
    :category: tutorials

