Using TinyMCE with CakePHP
==========================

by %s on September 23, 2006

TinyMCE is a nice WYSIWYG web editor by Moxiecode, giving your users a
very convenient way to edit HTML content. And guess what, its very
easy to use it in your Cake apps !


What is TinyMCE ?
-----------------
TinyMCE is a WYSIWYG editor by Moxiecode that you can find here :
`http://tinymce.moxiecode.com`_
Its very fast to load (much faster than FCKEditor last time i've
tried, which is quite a long time ago now, I must admit...), easy to
setup and configure. It isn't as powerful as FCKEditor
(`http://www.fckeditor.net/`_) in terms of text processing, but if all
you need is a small WYSIWYG editor to get clean (X)HTML, TinyMCE is
your boy.


Installation
------------
First step, of course, is to download TinyMCE . You can do this here :
`http://tinymce.moxiecode.com/download.php`_. At the time of writing,
latest version is 2.0.6.1, and has been for a few months. 2.0.6.1 is
very stable. Once downloaded, unpack TinyMCE, and copy just the
tinymce/jscripts/tiny_mce folder in /webroot/js . You don't need the
rest of the archive (documentation, examples, etc.) on your webserver.


Configuration
-------------
Next step is to implement it in Cake. Its very easy. First, we need to
add this to the layout(s) that will be used on the page(s) that will
have the editor. Of course, you need to add the javascript helper to
your $helpers array in your controller(s) :


PHP Snippet:
````````````

::

    <?php 
    if(isset($javascript)):
        ...
        echo $javascript->link('tiny_mce/tiny_mce.js');
        ...
    endif;
    ?>

Then, for each page that will have a TinyMCE editor, you'll have to
add to the top of the view file :


HTML:
`````

::

    
    <script type="text/javascript">
        tinyMCE.init({
            theme : "simple",
            mode : "textareas",
            convert_urls : false
        });
    </script>

By default, there are 2 themes with TinyMCE : 'simple' and 'advanced'.
You specify the one you want to use on your page with the theme
parameter. You can create your own themes or modify the existing ones
in the webroot/js/tiny_mce/themes folder. The mode parameter is set
here to 'textareas', meaning that all textareas of the page will be
replaced by TinyMCE editors of the same size. If this behaviour
doesn't satisfy you, check the documentation
(`http://tinymce.moxiecode.com/tinymce/docs/option_mode.html`_). The
last parameter, convert_urls , is set to false so that TinyMCE doesn't
try to process URLs for images or links.

There are loads of other parameters (All documented here :
`http://tinymce.moxiecode.com/tinymce/docs/index.html`_) to customize
the way the editor will work. For example, the file_browser_callback
allows you to give a js callback function or method to call a custom
file browser for the image insertion popup dialog.

If you want to change the way the text in the editor appears, each
theme has a css/editor_content.css file that you can modify to match
your site's styles.

.. _http://www.fckeditor.net/: http://www.fckeditor.net/
.. _http://tinymce.moxiecode.com/tinymce/docs/option_mode.html: http://tinymce.moxiecode.com/tinymce/docs/option_mode.html
.. _http://tinymce.moxiecode.com/download.php: http://tinymce.moxiecode.com/download.php
.. _http://tinymce.moxiecode.com: http://tinymce.moxiecode.com/
.. _http://tinymce.moxiecode.com/tinymce/docs/index.html: http://tinymce.moxiecode.com/tinymce/docs/index.html
.. meta::
    :title: Using TinyMCE with CakePHP
    :description: CakePHP Article related to WYSIWYG,fck editor,TinyMCE,Tutorials
    :keywords: WYSIWYG,fck editor,TinyMCE,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

