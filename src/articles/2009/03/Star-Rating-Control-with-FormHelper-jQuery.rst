Star Rating Control with FormHelper & jQuery
============================================

by HyperCas on March 04, 2009

I required a simple star rating system. Searching google.com for
'cakephp star rating' returned about two usable links, both of which
used custom helpers for showing a simple star rating selector. I
wanted something simpler which would not clutter up my code and
degrade nicely if JavaScript was not present. The following plugin is
supposed to convert a drop-down selection box into a star rating
system control.


The jQuery Plugin
-----------------

::

    
    /**
     * Star Rating - jQuery plugin
     * Modified by Aaron (http://hypercas.com)
     **/
    /**
     * Star Rating - jQuery plugin
     *
     * Copyright (c) 2007 Wil Stuckey
     * Modified by John Resig
     *
     * Dual licensed under the MIT and GPL licenses:
     *   http://www.opensource.org/licenses/mit-license.php
     *   http://www.gnu.org/licenses/gpl.html
     *
     */
    
    /**
     * Create a degradeable star rating interface out of a simple form structure.
     * Returns a modified jQuery object containing the new interface.
     *
     * @example jQuery('form.rating').rating();
     * @cat plugin
     * @type jQuery
     *
     */
    jQuery.fn.rating = function(){
    
        var $entity;
        $entity = jQuery(this).find('select');
    
        return this.each(function(){
            var div = jQuery("<div/>").attr({
                title: this.title,
                className: this.className
            }).insertAfter( this );
    
            jQuery(this).find("select option").each(function(){
                div.append( this.value == "" ?
                    "<div class='cancel'><a href='#0' title='Cancel Rating'>Cancel Rating</a></div>" :
                    "<div class='star'><a href='#" + this.value + "' title='Give it a " +
                        this.value + " Star Rating'>" + this.value + "</a></div>" );
            });
    
             //left overs from old plugin
             var averageRating = $entity.val(),
                url = this.action,
                averageIndex = $entity.val(),
                averagePercent = 0;
    
    
            // hover events and focus events added
            var stars = div.find("div.star")
                .mouseover(drainFill).focus(drainFill)
                .mouseout(drainReset).blur(drainReset)
                .click(click);
    
            // cancel button events
            div.find("div.cancel")
                .mouseover(drainAdd).focus(drainAdd)
                .mouseout(resetRemove).blur(resetRemove)
                .click(click);
    
            reset();
    
            function drainFill(){ drain(); fill(this); }
            function drainReset(){ drain(); reset(); }
            function resetRemove(){ reset(); jQuery(this).removeClass('star_on'); }
            function drainAdd(){ drain(); jQuery(this).addClass('star_on'); }
    
            function click(){
                averageIndex = stars.index(this) + 1;
                averagePercent = 0;
                $entity.val(averageIndex);
    
                if ( averageIndex == 0 )
                    drain();
    
                jQuery.post(url,{
                    rating: jQuery(this).find('a')[0].href.slice(1)
                });
    
                return false;
            }
    
            // fill to the current mouse position.
            function fill( elem ){
                stars.find("a").css("width", "100%");
                stars.slice(0, stars.index(elem) + 1 ).addClass("star_hover");
            }
    
            // drain all the stars.
            function drain(){
                stars.removeClass("star_on star_hover");
            }
    
            // Reset the stars to the default index.
            function reset(){
                stars.slice(0,averageIndex).addClass("star_on");
    
                var percent = averagePercent ? averagePercent * 10 : 0;
                if (percent > 0)
                    stars.eq(averageIndex).addClass("star_on").children("a").css("width", percent + "%");
            }
        }).hide();
    };
    
    // fix ie6 background flicker problem.
    if ( jQuery.browser.msie == true )
        document.execCommand('BackgroundImageCache', false, true);



Plugin Usage
~~~~~~~~~~~~
Put this inside a script file include it in your layout

::

    
    jQuery(function(){
        jQuery('#rating').rating();
    });

#rating is the id of the div containing the selection box.



Example
~~~~~~~

View Template:
``````````````

::

    
    <?php echo $form->create('Review');?>
    	<h3>Click the stars to give a rating</h3>
    	<div id="rating">
    		<?=$form->select('rating_overall',array('1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5))?>
    	</div>
    	<div class="clear"> </div>
    <?php echo $form->end('Submit');?>

See, just plain old FormHelper code.



Required CSS classes
~~~~~~~~~~~~~~~~~~~~
.star - base star
.star_hover - star image when the mouse is hovering a star div
.star_on - selected star
...and then some

I want them handed to me
````````````````````````
`http://plugins.jquery.com/project/MultipleFriendlyStarRating/`_
Download the package, copy jquery.rating.css , star.gif and delete.gif
to your css folder under app/webroot and include the css file in your
template. It will map perfectly without any modifications (v2.61)



Disclaimer
;;;;;;;;;;
It worked for me. That's all I say about it. For all I know, it could
reincarnate GodZilla.


Tested With
;;;;;;;;;;;
CakePHP 1.2
jQuery 1.3.2


Credits
;;;;;;;
The plugin is a modified version of an old jQuery plugin created by a
guy named Wil. A few changes were made to enable it to work with
jQuery 1.3.2 and a bit of code for selection box control slapped on
top.

`http://dev.jquery.com/~john/plugins/rating/`_ - original plugin
(outdated for jQuery 1.3.2)
`http://plugins.jquery.com/project/MultipleFriendlyStarRating/`_ -
sample stylesheet and images



Yippeee, No more messing around with helper classes just to use a
simple star control
-------------------


.. _http://plugins.jquery.com/project/MultipleFriendlyStarRating/: http://plugins.jquery.com/project/MultipleFriendlyStarRating/
.. _http://dev.jquery.com/~john/plugins/rating/: http://dev.jquery.com/~john/plugins/rating/
.. meta::
    :title: Star Rating Control with FormHelper & jQuery 
    :description: CakePHP Article related to jquery,star rating,Snippets
    :keywords: jquery,star rating,Snippets
    :copyright: Copyright 2009 HyperCas
    :category: snippets

