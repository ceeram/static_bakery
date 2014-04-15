Baking with Markdown and dp.SyntaxHighlighter
=============================================

by lecterror on February 18, 2008

Creating a content editor with has never been easier. All you need is
Markdown and dp.SyntaxHighlighter!
After a brief discussion on CakePHP group, I've decided to try - as
John Cleese would say - something completely different. That is, I've
decided to try to use a markup language instead of WYSIWYG. I was
using TinyMCE for a while, and I suppose it's OK for an uneducated end
user and a "standard" CMS.

But since Neutrino is going to be a CMS for developers (the project
I'm working on privately), that's just not good enough.

My first choice for a markup language was Markdown
(`http://daringfireball.net/projects/markdown/`_). But then I saw
something called "Markdown extra" (`http://michelf.com/projects/php-
markdown/`_) and decided to go with that. Turned out to be a good
choice.

Integrating Markdown with CakePHP was in fact a piece of Cake, with
just a few simple steps.


+ Download Markdown extra and put it in /app/vendors/markdown
+ Create a Markdown helper


Helper Class:
`````````````

::

    <?php 
    vendor('markdown'.DS.'markdown');
    
    class MarkdownHelper extends AppHelper {
    	function parse($text) {
    		return $this->output(Markdown($text));
    	}
    }
    ?>



+ Make sure you put it in your controller



Controller Class:
`````````````````

::

    <?php var $helpers = array('Markdown');?>



+ Use it in your view



View Template:
``````````````

::

    <?php
    echo $markdown->parse($article['Article']['content']);
    ?>

All you need to do now is learn Markdown
(`http://daringfireball.net/projects/markdown/syntax`_) and some
Markdown extra stuff (`http://michelf.com/projects/php-
markdown/extra/`_) :-)


What about syntax highlighting?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

My main problem while using WYSIWYG editors was to preserve code
formatting. Which was impossible. With no respect to tricks and all
kinds of plugins and black magic, the code would always end up in a
mess.

Now that's no problem. The only problem now is handling the tab key
inside a textarea. Luckily, after googling for a while, I was able to
combine a solution that actually works:

::

    
    // /app/webroot/js/tabulator.js
    function insertTab(event, obj)
    {
        var tabKeyCode = 9;
        if (event.which) // mozilla
            var keycode = event.which;
        else // ie
            var keycode = event.keyCode;
    
        if (keycode == tabKeyCode)
        {
            if (event.type == "keydown")
            {
            	var oldscroll = obj.scrollTop;
    
                if (obj.setSelectionRange)
                {
                    // mozilla
                    var s = obj.selectionStart;
                    var e = obj.selectionEnd;
                    obj.value = obj.value.substring(0, s) + "\t" + obj.value.substr(e);
                    obj.setSelectionRange(s + 1, s + 1);
                    obj.focus();
                }
                else if (obj.createTextRange)
                {
                    // ie
                    document.selection.createRange().text = "\t"
                    obj.onblur = function() { this.focus(); this.onblur = null; };
                }
    
                obj.scrollTop = oldscroll;
            }
    
            if (event.returnValue) // ie ?
                event.returnValue = false;
            if (event.preventDefault) // dom
                event.preventDefault();
            return false; // should work in all browsers
        }
        return true;
    }

Include the script in your layout:


View Template:
``````````````

::

    <?php
    // /app/views/layouts/default.ctp
    echo $javascript->link(array('tabulator'));
    ?>

And you just attach it to your textarea:


View Template:
``````````````

::

    <?php
    echo $form->input(
    	'Article.content',
    	array(
    		'onkeydown' => 'insertTab(event, this);'
    	));
    ?>

So now we have the tab working, all that is left is highlighting
itself with dp.SyntaxHighlighter
(`http://code.google.com/p/syntaxhighlighter/`_). We accomplish that
fairly easily.


+ First, download the highlighter from the address above
+ Deploy the brushes in /app/webroot/js/dp.SyntaxHighlighter
+ Deploy the CSS in /app/webroot/css/

Now add them to your layout just as before:


View Template:
``````````````

::

    <?php
    echo $html->css('SyntaxHighlighter');
    
    echo $javascript->link(
    	array(
    		'tabulator',
    		'dp.SyntaxHighlighter/Scripts/shCore',
    		'dp.SyntaxHighlighter/Scripts/shBrushPhp',
    		// additional brushes as needed
    	));
    ?>

The only thing left is to actually tell dp.SH to highlight the code.
You do this at the end of your layout, just before you close the tag.


View Template:
``````````````

::

    <?php
    echo $javascript->codeBlock(
    	'dp.SyntaxHighlighter.HighlightAll("code_snippet");'
    	);
    ?>



So how does it work anyway?
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Fairly easy. You just type this into your textarea:

::

    <pre name="code_snippet" class="php:nocontrols:nogutter">
    	// hello
    	echo 'Arthur "Two Sheds" Jackson';
    	// go away
    </pre>

and after markup and dp.SH it should turn into a nice block of
highlighter code. If curious, you can go to the original location of
this article to see it in action (`http://lecterror.com/articles/view
/baking-with-markdown-and-dp-syntaxhighlighter`_)

You're ready to go, with one textarea, one tiny helper and a lot of
3rd party code. Total time to accomplish this: 15 minutes (coffee
break included:-))

Happy baking!

.. _http://michelf.com/projects/php-markdown/extra/: http://michelf.com/projects/php-markdown/extra/
.. _http://michelf.com/projects/php-markdown/: http://michelf.com/projects/php-markdown/
.. _http://daringfireball.net/projects/markdown/: http://daringfireball.net/projects/markdown/
.. _http://lecterror.com/articles/view/baking-with-markdown-and-dp-syntaxhighlighter: http://lecterror.com/articles/view/baking-with-markdown-and-dp-syntaxhighlighter
.. _http://code.google.com/p/syntaxhighlighter/: http://code.google.com/p/syntaxhighlighter/
.. _http://daringfireball.net/projects/markdown/syntax: http://daringfireball.net/projects/markdown/syntax

.. author:: lecterror
.. categories:: articles, tutorials
.. tags:: textarea,syntaxhighlighter,markdown,Tutorials

