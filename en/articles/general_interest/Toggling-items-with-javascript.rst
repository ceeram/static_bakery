

Toggling items with javascript
==============================

by %s on September 22, 2006

This tutorial explains how you can toggle (expand/collapse) certain
items on your webpage using javascript (without the need of new
pagerequests)
There are several methods to switch a certain element or part (or
several elements/parts) of your view/element between visible or
invisble.
There are various names for this (hide/unhide, expand/collapse,
toggle, etc)

Some methods don't require any client-side scripting but these require
a reload of the whole page in order to toggle a certain piece.
(passing a certain parameter, usually via GET)
It can be "expensive" (in terms of performance) to do it this manner
(not to mention it isn't "clean" programming)
The alternative is using client-side scripting (javascript in this
case, which is usually enabled in every browser), so that's what we
will do.

Firstly, place this little piece of javascript (i took it from the
website mentioned in the credits and changed it a very little bit to
make it more according to the cakephp style guidelines)
in the webroot/js/ directory of your app, and call it toggle.js

::

    
    /* This script and many more are available free online at
    The JavaScript Source :: http://javascript.internet.com
    Created by: Ultimater, Mr J :: http://www.webdeveloper.com/forum/showthread.php?t=77389 */
    
    function toggle(a)
    {
    	var e = document.getElementById(a);
     	if(!e) return true;
      	if(e.style.display == "none")
      	{
       	    e.style.display = "block"
     	}
      	else
      	{
       	    e.style.display = "none"
      	}
      	return true;
    }

Then, make sure that the controller you're using can call the
javascript helper, so you'll want something like this in the
_controller.php

PHP Snippet:
````````````

::

    <?php 
    var $helpers = array('Html', 'Javascript');
    ?>

Now you can start collapsing and expanding whatever part you want in
your view! (or element).
This is some stuff taken out of my views/news/index.thtml (so i edited
news_controller.php in the previous step)

View Template:
``````````````

::

    
    <?php
    	echo($javascript->link("toggle.js"));
     foreach ($news as $row)
    ?>
    	<tr>
    		<td>	<?php echo $row['News']['modified']; ?>			                        </td>
    		<td>	<?php echo $row['News']['title']; ?>			                        </td>
    		<td>	<a onclick="return toggle('news<?php echo $row['News']['id']; ?>')">More</a> 	</td>
    	</tr>	
    	<tr>
    		<td>
    			<div  id="news<?php echo $row['News']['id'] ?>" style="display:none">
    				<?php echo $row['News']['body']; ?>	
    			</div>
    		</td>	
    	</tr>
    	<?php endforeach; ?>
    </table>	

As you see it's actually very easy, just keep this in mind that when
you call the toggle() function you have to pass an id, and whatever
element has that id gets toggled. in my case, i can't just use
$row['News']['id'] as id: i have to stick "news" in front of it, so
that the id becomes "news15" or something like that, because in my
view, i have a list of both news and other stuff (posts), so if i
would just use id "15" and toggle it, i would toggle both the news and
the post that has id 15. Offcourse, in most situations you don't have
to do this.

If you want the element to be shown by default, make it display:block
in the html code (this is more safe in case your visitor doesn't have
javascript enabled. The link will not work offcourse, maybe it would
be nicer if we could detect if js is available and hide the link if
not....)

Also, you could switch between display:none and display:inline if that
suits your situation more, the css display possibilities are listed
right here: `http://www.w3schools.com/css/pr_class_display.asp`_

.. _http://www.w3schools.com/css/pr_class_display.asp: http://www.w3schools.com/css/pr_class_display.asp
.. meta::
    :title: Toggling items with javascript
    :description: CakePHP Article related to expand,toggle,unhide,collapse,prototype,hide,toggle hide expand u,General Interest
    :keywords: expand,toggle,unhide,collapse,prototype,hide,toggle hide expand u,General Interest
    :copyright: Copyright 2006 
    :category: general_interest

