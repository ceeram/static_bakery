Using requestAction & custom layouts to add XHR functionality
=============================================================

by %s on October 23, 2007

This tutorial outlines a method for creating or modifying Cake apps
that work swiftly for XHR(XmlHTTPRequest)-enabled clients and degrade
well to non-XHR/AJAX (even javascript disabled) clients. The guidance
provided here will be most suitable to newly converted Bakers. It
simply outlines some of Cake's many flexible features and shows how
using them in combination can bring rapid returns.

We'll use the flexibility of Cake's requestAction method and some
custom layouts to add XHR to your application without having to
rewrite the core M's, V's or C's of your current app.

No internal Ajax Cake methods are used in this tutorial. Please
consider thisefore you proceed. While you won't learn anything about
those methods, you will be shown a way to turn an existing application
into an XHR enabled one with a minimum of effort and with no impact
upon the function of the application as it is without XHR or
Javascript.

For my sanity, the tutorial does use the prototype and Mootools
frameworks to provide DOM access, this is to change Cake generated
links into XHR links in loaded content. You can do this any way you
wish, but Mootools was used in this instance. The code samples expect
a 'script src' for Mootools to be included in your default layout.

+ We'll take a simple MVC setup that displays a month-to-view
  calendar, with links to the previous and next month's calendars too.
  To keep unnecessary code to a minimum, I won't be showing the
  calendar, just the month and year that the calendar represents.
+ We'll then split the controllers up into smaller units so that we
  can gain access to them via XHR.
+ Next we'll add the one (just one!) line of code to these smaller
  controllers so that they will work with our XHR request, but this one
  line will not change their usual non-XHR function. Then we'll add an
  action to aggregate them all into a response for conventional usage.
+ Once our views are in place, we'll add our additional layouts to
  make sure we retain the XHR functionality across the MVC Calendar.


Initial Setup
~~~~~~~~~~~~~

First create a dummy database table for the model (this won't be used,
but is necessary). Then create the following:
controllers/calendar_controller.php

Controller Class:
`````````````````

::

    <?php 
    class CalendarController extends AppController {
     
      function index() {
    	
      }
    ?>

models/calendar.php

Model Class:
````````````

::

    <?php 
    class Calendar extends AppModel {
       var $name = 'Calendar';
    }?>


views/calendar/index.thtml

View Template:
``````````````

::

    
    <h1>Calendar Index</h1>
     <h3>Non-XHR</h3>




Live Example and full code
~~~~~~~~~~~~~~~~~~~~~~~~~~
A live example and archive of the MVC classes, views and layouts can
be found here:
`http://ajax1app.codeandeffect.co.uk/calendar`_

A full code listing also follows on the next page, but take care to
note which views are views, and which are layouts

Conclusion
~~~~~~~~~~

This method allows you to develop an application without Ajax or XHR
but add it easily with only a little forward planning.

You can concentrate on building a solid Cake App regardless of
additional user friendly add-ons, safe in the knowledge that
Javascript is not required. Then just add the necessary additional
layouts to javascript within them to rollout user friendly features.
Your code remains very clean.

Links
`````

requestAction API reference:
Mootools: `http://mootools.net`_ CakePHP API, reuqestAction: `http://a
pi.cakephp.org/class_object.html#c40a38b60a3748b9cf75215b92ee3db1`_

models/calendar.php

Model Class:
````````````

::

    <?php class Calendar extends AppModel {
       var $name = 't1';   
    }?>



controllers/calendar_controller.php

Controller Class:
`````````````````

::

    <?php class CalendarController extends AppController {
     function index() {
        }
     function calendarnavigation($chooseYear=null,$chooseMonth=null) {
    	$this->layout = 'calendarnavigationdynamic'; 
    	$y = ($chooseYear)?(int)$chooseYear:date("Y",mktime());
    	$m = ($chooseMonth)?(int)$chooseMonth:date("n",mktime());
    	$this->set('calYearInt', $y); 
    	$this->set('calMonthInt', $m);	
     }
     function generatecalendar($chooseYear=null,$chooseMonth=null) {
    	// Create defaults for Year and Month
    	$y = ($chooseYear)?(int)$chooseYear:date("Y",mktime());
    	$m = ($chooseMonth)?(int)$chooseMonth:date("n",mktime());
    	
    	$this->set('calYearInt', $y); 
    	$this->set('calMonthInt', $m); 
    	
    	$this->set('calendar', $this->requestAction('/Calendar/singlecalendar/'.$y.'/'.$m, array('return'))); 
    	$this->set('calendarnavigation', $this->requestAction('/Calendar/calendarnavigation/'.$y.'/'.$m , array('return')));    
     }   
     function singlecalendar($chooseYear=null,$chooseMonth=null ) {
       
        $this->layout = 'calendardynamic'; 
    	
    	$y = ($chooseYear)?(int)$chooseYear:date("Y",mktime());
    	$m = ($chooseMonth)?(int)$chooseMonth:date("n",mktime());
        $this->set('calYearInt', $y); 
    	$this->set('calMonthInt', $m); 			
       }  	
    }?>



Views
~~~~~
views/calendar/index.thtml

View Template:
``````````````

::

    <h1>Calendar Index</h1><br />
     <p>non-XHR, non javascript: <a href="/calendar/generatecalendar">Click this link to go to the Calendar Page</a></p>
     <br />
    <p>XHR/AJAX,javascript: <a href="#" id="calendarGen">Click this link to load The XHR Calendar</a></p>
    <br />
    <p>Download: <a href="/addingXHR.rar">Right-click->save this link to download the example files</a></p>
    <br />
    <br />
    <div style="font-weight:bold;font-size:120%;padding3px;">This is the Calendar Nav Div â†“ </div>
    <div id="calendarNavDiv" style="border:1px solid black;margin-top:.1em;">
    
    The Calendar Navigation will load here
    
    </div>
    <br /><br />
    <div style="font-weight:bold;font-size:120%;padding3px;">This is the Calendar Div â†“ </div>
    <div id="calendarDiv" style="border:1px solid black;margin-top:.1em;">
    
    The Calendar will load here
    </div>
    <script type="text/javascript">
     
    $('calendarGen').addEvent('click', function(e) {
    	e = new Event(e).stop();
     	var url = "/calendar/singlecalendar";
     	new Ajax(url, {
    		method: 'get',
    		onRequest:  function(){
    				 $('calendarDiv').setStyle('border','12px solid red');
    			},
    		onSuccess:  function(){
    				 $('calendarDiv').setStyle('border','1px dotted blue');
    			}, 
    		update: $('calendarDiv')
    	}).request();
    });
    $('calendarGen').addEvent('click', function(e) {
    	e = new Event(e).stop();
     	var url = "/calendar/calendarnavigation";
     	/**
    	 * The simple way for an Ajax request, use onRequest/onComplete/onFailure
    	 * to do add your own Ajax depended code.
    	 */
    	new Ajax(url, {
    		method: 'get',
    		evalScripts: true,
    		onRequest:  function(){
    				 $('calendarNavDiv').setStyle('border','12px solid red');
    			}, 
    		onSuccess:  function(){
    				 $('calendarNavDiv').setStyle('border','1px dotted blue');
    			}, 
    		update: $('calendarNavDiv')
    	}).request();
    });
    </script>


views/calendar/calendarnavigation.thtml

View Template:
``````````````

::

    <?php
    $calMonthInt = (strlen($calMonthInt) ==1)?"0".$calMonthInt :$calMonthInt ;
    $YYYYMM  = $calYearInt.'-'.$calMonthInt.'';
    $firstDayOfMonthUnixTimestamp = strtotime($YYYYMM);
    $viewingMonth = date("F",$firstDayOfMonthUnixTimestamp);
    $viewingYear = date("Y",$firstDayOfMonthUnixTimestamp);
     //next month
    $nextMonthText = date('F Y',strtotime("+1 months",$firstDayOfMonthUnixTimestamp)).">>";
    // previous month
    $previousMonthText = "<<".date('F Y',strtotime("-1 months",$firstDayOfMonthUnixTimestamp));
     //next month link
     $nextMonthLink = "/calendar/generatecalendar/".date('Y\/m',strtotime("+1 months",$firstDayOfMonthUnixTimestamp));
     // previous month link
     $previousMonthLink = "/calendar/generatecalendar/".date('Y\/m',strtotime("-1 months",$firstDayOfMonthUnixTimestamp));
    ?>
    <div id="calendar_navigation" style="text-align:center;">
    <?php echo $html->link($previousMonthText,$previousMonthLink); ?>         
    <?php echo $viewingMonth ?> <?php echo $viewingYear ?>           
    <?php  echo $html->link($nextMonthText,$nextMonthLink); ?> 
    </div>

views/calendar/singlecalendar.thtml

View Template:
``````````````

::

    <?php
    $calMonthInt = (strlen($calMonthInt) ==1)?"0".$calMonthInt :$calMonthInt ;
    $YYYYMM  = $calYearInt.'-'.$calMonthInt.'';
    $firstDayOfMonthUnixTimestamp = strtotime($YYYYMM);
    $viewingMonth = date("F",$firstDayOfMonthUnixTimestamp);
    $viewingYear = date("Y",$firstDayOfMonthUnixTimestamp);
    echo "this is the Calendar for <h2>".$viewingMonth. "</h2><h3>". $viewingYear."</h3><br />";
     ?>

views/calendar/generatecalendar.thtml

View Template:
``````````````

::

    <h1>Calendar Non-XHR</h1><br />
    <p><a href="/calendar/">Return to Calendar home</a></p><br />
    <div style="padding:1em;border:2px solid green;">
    <?php echo $calendarnavigation; ?>
    </div>
    <br />
    <div style="padding:1em;border:2px solid green;">
    <?php echo $calendar; ?>
    </div>



Layouts
~~~~~~~

views/layouts/default.thtml

View Template:
``````````````

::

    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head><title><?php echo $title_for_layout ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <script type="text/javascript" src="/js/mootools.v1.1.js"></script>
    </head>
    <body>
    <div id="outermost">
    <div id="outermostInner">
    	<div id="content">
    		<div id="contentPad">
    			<?php echo $content_for_layout ?>
    		</div>
    	</div>
    	<div style="margin-top:20px;font-size:80%;color:#fff;">
    	<?php
    		echo  " ".date("M d Y H:i",mktime()) .", timezone: ".date(" \G\M\T O",mktime()) ."";   
        ?>  
    	</div>     
    </div>
    </div>	
    </body></html>

views/calendar/calendardynamic.thtml

View Template:
``````````````

::

    <div style="background-color:#fff;padding:2em;">
    <?php echo $content_for_layout ?>
    </div>

views/calendar/calendarnavigationdynamic.thtml

View Template:
``````````````

::

    <div id="calendar_navigation" style="background-color:#e0e0e0;padding:2em;">
    <?php echo $content_for_layout ?>
    </div>
    <script type="text/javascript">
     
     $$('#calendar_navigation a').each(function(el){
     var url =  el.getProperty('href');
     url = url.replace(/generatecalendar/,'singlecalendar');
     
     
     el.addEvent('click', function(e) {
     e = new Event(e).stop();
     e.preventDefault();
     
    	new Ajax(url, {
    		method: 'get',
    		onRequest:  function(){
    			  $('calendarDiv').setStyle('border','12px solid red');
    			},
    		onSuccess:  function(){
    				 $('calendarDiv').setStyle('border','1px dotted blue');
    				 UpdateNav(url);
    			}, 
    		update: $('calendarDiv')
    	}).request();
    	
    	
    	//
    	function UpdateNav(url) {
    	url = url.replace(/singlecalendar/,'calendarnavigation');
     
    	new Ajax(url, {
    		method: 'get',
    		evalScripts: true,
    		onRequest:  function(){
    				 $('calendarNavDiv').setStyle('border','12px solid red');
    			},
    		onSuccess:  function(){
    				 $('calendarNavDiv').setStyle('border','1px dotted blue');
    			}, 
    		update: $('calendarNavDiv')
    	}).request();
    	
    	}
    });
    
     
     
    });
     
    </script>   



The calendar will show information about a single month, and will
provide navigation forward one month, and back one month. For regular
viewers this will all be in one page. However I want to split this so
my default view can load each element via XHR into specified areas of
the page. So I split the requirements across more functions:

In the Controller controllers/calendar_controller.php I add:

Controller Class:
`````````````````

::

    <?php 
    function calendarnavigation($chooseYear=null,$chooseMonth=null) {
    	$this->layout = 'calendarnavigationdynamic'; 
    	
    	$y = ($chooseYear)?(int)$chooseYear:date("Y",mktime());
    	$m = ($chooseMonth)?(int)$chooseMonth:date("n",mktime());
    	$this->set('calYearInt', $y); 
    	$this->set('calMonthInt', $m);
     		
     }
     
     function singlecalendar($chooseYear=null,$chooseMonth=null ) {
       
        	$this->layout = 'calendardynamic'; 
    	
    	$y = ($chooseYear)?(int)$chooseYear:date("Y",mktime());
    	$m = ($chooseMonth)?(int)$chooseMonth:date("n",mktime());
        	$this->set('calYearInt', $y); 
    	$this->set('calMonthInt', $m); 			
       }  	
    }
    ?>

the action calendarnavigation will provide links forward one month and
back one month, based on the date specified. By default it chooses the
current month and year. the action singlecalendar will show the
calendar for the month and year specified. It too chooses the current
month and year by default.

Custom Layouts for XHR
~~~~~~~~~~~~~~~~~~~~~~

Each of these functions specifies it's own layout:

::

    
    $this->layout = 'calendardynamic'; 

and

::

    
    $this->layout = 'calendarnavigationdynamic'; 

These two layouts are for the dynamic XHR responses. But first we need
to add another function so that the non-XHR viewers can see the
calendar and navigation.

in controllers/calendar_controller.php

Controller Class:
`````````````````

::

    <?php 
    function generatecalendar($chooseYear=null,$chooseMonth=null) {
    	$y = ($chooseYear)?(int)$chooseYear:date("Y",mktime());
    	$m = ($chooseMonth)?(int)$chooseMonth:date("n",mktime());
    	$this->set('calYearInt', $y); 
    	$this->set('calMonthInt', $m); 
    	$this->set('calendar', $this->requestAction('/Calendar/singlecalendar/'.$y.'/'.$m, array('return'))); 
    	$this->set('calendarnavigation', $this->requestAction('/Calendar/calendarnavigation/'.$y.'/'.$m , array('return')));    
     } 
    ?>

Notice this latest action makes use of requestAction to call the other
two actions needed to complete our page. requestAction and the custom
layouts, are the glue for the whole method.

Without XHR, /calendar/generatecalendar can be called, which in turn
calls the two components. Using requestAction calls the method
internally so ignores the layouts
views/layouts/singlecalendardynamic.thtml and
views/layouts/calendarnavigationdynamic.thtml.

With XHR, the methods can be called directly, using their own layouts,
producing the minimal code we need to update our areas of our page.

As it's just for test purposes, our singlecalendar method simply
states it's month and year, but could be developed to show a tabled
view of each day of the month.



The view for singlecalendar looks like this:

View Template:
``````````````

::

    
    /*Format the date params a little */ 
    $calMonthInt = (strlen($calMonthInt) ==1)?"0".$calMonthInt :$calMonthInt ;
    $YYYYMM  = $calYearInt.'-'.$calMonthInt.'';
    $firstDayOfMonthUnixTimestamp = strtotime($YYYYMM);
    $viewingMonth = date("F",$firstDayOfMonthUnixTimestamp);
    $viewingYear = date("Y",$firstDayOfMonthUnixTimestamp);
     
     echo "this is the Calendar for <h2>".$viewingMonth. "</h2><h3>". $viewingYear."</h3><br />";


Our calendarnavigation view however must use links to other calendars,
and looks like this:


View Template:
``````````````

::

    $calMonthInt = (strlen($calMonthInt) ==1)?"0".$calMonthInt :$calMonthInt ;
    $YYYYMM  = $calYearInt.'-'.$calMonthInt.'';
    $firstDayOfMonthUnixTimestamp = strtotime($YYYYMM);
    $viewingMonth = date("F",$firstDayOfMonthUnixTimestamp);
    $viewingYear = date("Y",$firstDayOfMonthUnixTimestamp);
    //next month
    $nextMonthText = date('F Y',strtotime("+1 months",$firstDayOfMonthUnixTimestamp)).">>";
    // previous month
    $previousMonthText = "<<".date('F Y',strtotime("-1 months",$firstDayOfMonthUnixTimestamp));
    //next month link
    $nextMonthLink = "/calendar/generatecalendar/".date('Y\/m',strtotime("+1 months",$firstDayOfMonthUnixTimestamp));
    // previous month link
    $previousMonthLink = "/calendar/generatecalendar/".date('Y\/m',strtotime("-1 months",$firstDayOfMonthUnixTimestamp));
    ?> 
    <div id="calendar_navigation" style="text-align:center;">
    <?php echo $html->link($previousMonthText,$previousMonthLink); ?>         
    <?php echo $viewingMonth ?> <?php echo $viewingYear ?>           
    <?php  echo $html->link($nextMonthText,$nextMonthLink); ?> 
    </div>

Notice that the views contain links to other methods. This is fine for
the non-XHR but means that we do not have any links to further refresh
parts of the page, using these links would refresh the whole page.

This is where our alternate layouts come into their own. By adding
Javascript code to the additional layouts we can rewrite the links so
that they call updates, not whole pages. Using this method keeps XHR
alternatives out of our core Cake MVC files and adds them as further
customisations within layouts only called when necessary.

Without XHR, the custom layout is ignored and the links remain the
same.

With XHR, the custom layout ensures that the page navigation continues
to provide dynamic updates.

The layout views/layouts/calendardynamic.thtml simply drops in the
same content, and looks like this:

::

    <div style="background-color:#fff;padding:2em;">
    <?php echo $content_for_layout ?>
    </div>

The layout views/layouts/calendarnavigationdynamic.thtml has URLs to
rewrite, and uses the Mootools framework (you don't have to use
Mootools, So long as you update the same elements from the
main layout and index view ,it does not matter how you acheive it:

::

    
    <div id="calendar_navigation" style="background-color:#e0e0e0;padding:2em;">
     <?php echo $content_for_layout ?>
     </div>
    <script type="text/javascript">
     $$('#calendar_navigation a').each(function(el){
     var url =  el.getProperty('href');
     url = url.replace(/generatecalendar/,'singlecalendar');
     el.addEvent('click', function(e) {
     e = new Event(e).stop();
     e.preventDefault();
     
    	new Ajax(url, {
    		method: 'get',
    		onRequest:  function(){
    			  $('calendarDiv').setStyle('border','12px solid red');
    			},
    		onSuccess:  function(){
    				 $('calendarDiv').setStyle('border','1px dotted blue');
    				 UpdateNav(url);
    			}, 
    		update: $('calendarDiv')
    	}).request();
    	
    	
    	//
    	function UpdateNav(url) {
    	url = url.replace(/singlecalendar/,'calendarnavigation');
     
    	new Ajax(url, {
    		method: 'get',
    		evalScripts: true,
    		onRequest:  function(){
    				 $('calendarNavDiv').setStyle('border','12px solid red');
    			},
    		onSuccess:  function(){
    				 $('calendarNavDiv').setStyle('border','1px dotted blue');
    			}, 
    		update: $('calendarNavDiv')
    	}).request();
    	
    	}
    });
    
     
     
    });
     
    </script>  

The javascript in the views\layouts/calendarnavigationdynamic.thtml
layout simply searches for the links as they are output from the
controller and rewrites them.

First it finds all anchor elements loaded into the DOM element
calendar_navigation and adds a (Mootools) function to add an AJAX
request to it:

::

    
    $$('#calendar_navigation a').each(function(el){...

it then detects any urls with the non-ajax components, and replaces
them:

::

    
     var url =  el.getProperty('href');
     url = url.replace(/generatecalendar/,'singlecalendar');

[p]The rest of the function tells the link what to do while loading
and which DOM element to update when the request completes. In this
example the methods sequenced, when the singlecalendar request
successfully returns (updating the calendar), the calendarnavigation
function is called that updates the navigation. Making one rely on the
other helps to ensure that your two dynamic elements remain in sync.

[p]The DOM elements that are requested in these functions exist in the
index view, which is shown here complete, with XHR and non XHR links:
views/calendar/index.thtml

View Template:
``````````````

::

    
    <h1>Calendar Index</h1>
     <h3>Non Ajax</h3>
     <p><a href="/calendar/generatecalendar">Go to The Calendar Page</a></p>
      <h3>Ajax</h3>
    <p><a href="#" id="calendarGen">Load The Ajax Calendar</a></p>
     
    <div id="calendarNavDiv" style="border:1px solid black;margin-top:.5em;">
    <h3>This is the Calendar Nav Div</h3>
    The Calendar Navigation will load here
    
    </div>
     
    <div id="calendarDiv" style="border:1px solid black;margin-top:.5em;">
    <h3>This is the Calendar Div</h3>
    The Calendar will load here
    </div>
    <script type="text/javascript">
     
    $('calendarGen').addEvent('click', function(e) {
    	e = new Event(e).stop();
     	var url = "/calendar/singlecalendar";
     	new Ajax(url, {
    		method: 'get',
    		onRequest:  function(){
    				 $('calendarDiv').setStyle('border','12px solid red');
    			},
    		onSuccess:  function(){
    				 $('calendarDiv').setStyle('border','1px dotted blue');
    			}, 
    		update: $('calendarDiv')
    	}).request();
    });
    $('calendarGen').addEvent('click', function(e) {
    	e = new Event(e).stop();
     	var url = "/calendar/calendarnavigation";
     	/**
    	 * The simple way for an Ajax request, use onRequest/onComplete/onFailure
    	 * to do add your own Ajax depended code.
    	 */
    	new Ajax(url, {
    		method: 'get',
    		evalScripts: true,
    		onRequest:  function(){
    				 $('calendarNavDiv').setStyle('border','12px solid red');
    			}, 
    		onSuccess:  function(){
    				 $('calendarNavDiv').setStyle('border','1px dotted blue');
    			}, 
    		update: $('calendarNavDiv')
    	}).request();
    });
    </script>

`1`_|`2`_|`3`_|`4`_|`5`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_
+ `Page 5`_

.. _Page 4: :///articles/view/4caea0e0-854c-4aa9-80d8-434882f0cb67#page-4
.. _Page 5: :///articles/view/4caea0e0-854c-4aa9-80d8-434882f0cb67#page-5
.. _Page 1: :///articles/view/4caea0e0-854c-4aa9-80d8-434882f0cb67#page-1
.. _Page 2: :///articles/view/4caea0e0-854c-4aa9-80d8-434882f0cb67#page-2
.. _Page 3: :///articles/view/4caea0e0-854c-4aa9-80d8-434882f0cb67#page-3
.. _http://api.cakephp.org/class_object.html#c40a38b60a3748b9cf75215b92ee3db1: http://api.cakephp.org/class_object.html#c40a38b60a3748b9cf75215b92ee3db1
.. _http://ajax1app.codeandeffect.co.uk/calendar: http://ajax1app.codeandeffect.co.uk/calendar
.. _http://mootools.net: http://mootools.net/
.. meta::
    :title: Using requestAction & custom layouts to add XHR functionality
    :description: CakePHP Article related to Layouts,Mootools,XHR,Tutorials
    :keywords: Layouts,Mootools,XHR,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

