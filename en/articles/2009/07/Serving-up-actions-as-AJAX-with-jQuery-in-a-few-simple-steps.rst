

Serving up actions as AJAX with jQuery in a few simple steps
============================================================

by %s on July 08, 2009

How to quickly setup AJAX to call Cake actions using jQuery. (For the
people who keep wondering when there will be a jQuery helper)
Since this tutorial was missing and some people on IRC have been
asking questions about when there will be jQuery support in the
ajax/javascript helper I decided to write this. I find that helpers
are used for generating HTML markup, you don't see a helper for CSS as
it shouldn't be mixed with inline markup and frankly, the same goes
for Javascript.

I like to apply Javascript (especially through the power of jQuery CSS
selectors) as a stylesheet to my work to improve versatility. But even
if you just want to figure out how to do a basic ajax call with jQuery
and Cake here's the steps you need:


#. Attach jQuery Library
#. Turn on the RequestHandler Component
#. Create and style a div (overlay) in the view to use for ajax
   populating
#. Choose an action to trigger the AJAX
#. Throw in the jQuery code



Step 1
------
Throw this in your layout:

::

    echo $javascript->link('http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js');



Step 2
------
Throw this in your controller of question (or app_controller for all-
around ajaxy goodness):

::

    var $components = array('RequestHandler');

Cake will now automatically detect the AJAX headers sent by jQuery and
will return the actions with the same views you coded for normal use
except now with the ajax layout (by default empty).


Step 3
------
Add this to your layout or the action you plan to use AJAX in for
overlays or population content with:

::

    <div id="overlayer" style="display:none"></div>

Preferably without the initial inline CSS of course


Step 4
------
The html link we will magically transform with the 'js-ajax' class
because Javascript just plain shouldn't be inline:

::

    <?php echo $html->link('Demo', array('controller'=>'posts','action'=>'view',$id), array('class'=>'js-ajax')); ?>



Step 5
------
Notice how we simply use our class to hijack a perfectly normally
working link with it's already present information to serve up as
AJAX? In addition we have the overlayer div fade in on callback so it
runs after the ajax loads instead of at the same time. You may want to
make some thinking icon appear first as this occasionally takes a
second or two.

::

    // $() is a css selector. First I choose what triggers the event. In this case when 
    // you CLICK on a LINK with the js-ajax CLASS. I changed the example to use the live()
    // function so that any javascript-generated html will be binded too instead of click().
    $('a.js-ajax').live('click', function() {
        // Now we simply make the ajax call. load($url) will pull the url's VIEW and put it 
        // into ther innerhtml of whatever tag you called load on. In this case, I want to fill 
        // up my #overlayer div with the results of the ajax.
        $('#overlayer').load(
            // Here is the tricky part. Instead of hard-coding a url to pass, I just had jquery 
            // go look at what the link (from the outside scope, .click() part) was already going 
            // to (href) and used that as the argument.
            $(this).attr('href')
        , function () {
            // This is a callback, after the ajax gets loaded, the #overlayer div gets faded in at 300 miliseconds.
            $(this).fadeIn(300);
        });
        // And finally to prevent actually making the link go anywhere
        return false;
    });

Want to have a close button on an overlay window on all ajax? You
should probably put it in the layout.

::

    LAYOUT:
    <a href="#close" title="Close Overaly" class="close">Close</a>
    
    jQuery:
    // Create the event trigger, in this case when a link with the class CLOSE inside the overlayer div is clicked
    $('#overlayer a.close').live('click', function () {
        // Fade out the overlayer
        $('#overlayer').fadeOut(300);
        return false;
    });


.. meta::
    :title: Serving up actions as AJAX with jQuery in a few simple steps
    :description: CakePHP Article related to AJAX,dhtml,jquery,ajax helper,Tutorials
    :keywords: AJAX,dhtml,jquery,ajax helper,Tutorials
    :copyright: Copyright 2009 
    :category: tutorials

