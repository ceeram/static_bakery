

Open Flash Chart Helper: draw charts the Cake way
=================================================

by %s on May 03, 2008

Open Flash Chart ([url]http://teethgrinder.co.uk/open-flash-
chart/[/url]) is a nice solution to drawing charts from data on your
applications. This helper makes using Open Flash Chart with CakePHP
simple.
Note. spanish translation of this tutorial can be found here:
`http://aikon.com.ve/open-flash-chart-helper-graficos-al-estilo-
cake/`_

Prerequisites
~~~~~~~~~~~~~
To make use of this helper, first you need to download Open Flash
Chart zip file from `http://teethgrinder.co.uk/open-flash-
chart/download.php`_
Inside it there are two files needed to use this helper:

- open-flash-chart.swf place this file in your [app]/webroot/
directory
- php-ofc-library/open-flash-chart.php place this file in your
[app]/vendors directory.


The Helper File:
~~~~~~~~~~~~~~~~
To get the latest version of the helper fetch it from the subversion
repo:

::

    svn co http://svn2.assembla.com/svn/cakeopenflashchart/trunk/flash_chart.php

If you don't use svn you can fetch it directly from that url.


Usage
~~~~~
Now, to use this helper do as in any other helper:

::

    <?php
    	var $helpers = array('FlashChart');
    ?>

The examples below are all placed on a view file:
[app]/views/pages/charts.ctp (so you must modify PagesController to
use the Helper) and generate random data (but using data from database
is equally easy).


Example 1 - Bar Graphs
``````````````````````

View Template:
``````````````

::

    <?php
    // Sets height and width
    $flashChart->begin(400, 250);
    // Title
    $flashChart->title('Example 1 - Bars: Hits per Day');
    // Configure Grid style and legends
    $flashChart->configureGrid(
    	array(
    		'x_axis' => array(
    			'step' => 1,
    			'legend' => 'Day'
    		),
    		'y_axis' => array(
    			'legend' => '#Hits',
    		)
    	)
    );
    // Prepare some random data (10 points)
    $random_hits = array();
    for ($i=0; $i < 10; $i++) { 
    	$random_hits[] = rand(10,100);
    }
    // Register each data set with its information.
    $data = array(
    	'Hits' => array(
    		'color' => '#afe342',
    		'font_size' => 11,
    		'data' => $random_hits,
    		'graph_style' => 'bar',
    	)
    );
    $flashChart->setData($data);
    
    // Set Ranges in the chart
    $flashChart->setRange('y', 0, 100);
    $flashChart->setRange('x', 0, 10);
    
    // Show the graph
    echo $flashChart->render();
    ?>

This view generates a simple bar graph with the random data generated
in a loop. To generate another graph in the same view you must allways
call FlashChartHelper::begin to reset all data.

Tip: change 'graph_style' => 'bar' to one of the following to see all
types of bar graphs that are available:

+ 'graph_style' => 'bar_sketch'
+ 'graph_style' => 'bar_glass'
+ 'graph_style' => 'bar_filled'
+ 'graph_style' => 'bar_3D'
+ 'graph_style' => 'bar_fade'



Example 2 - Line Graphs
```````````````````````
To draw lines instead of bars, the only change needed in the example
above is 'graph_style' => 'bar' to one of the following:

+ 'graph_style' => 'line'
+ 'graph_style' => 'line_hollow'
+ 'graph_style' => 'line_dot'



View Template:
``````````````

::

    <?php
    // Sets height and width
    $flashChart->begin(400, 250);
    // Title
    $flashChart->title('Example 2 - Lines: Hits per Day');
    // Configure Grid style and legends
    $flashChart->configureGrid(
    	array(
    		'x_axis' => array(
    			'step' => 1,
    			'legend' => 'Day'
    		),
    		'y_axis' => array(
    			'legend' => '#Hits',
    		)
    	)
    );
    // Prepare some random data (10 points)
    $random_hits = array();
    for ($i=0; $i < 10; $i++) { 
    	$random_hits[] = rand(10,100);
    }
    // Register each data set with its information.
    $data = array(
    	'Hits' => array(
    		'color' => '#00aa42',
    		'font_size' => 11,
    		'data' => $random_hits,
    		'graph_style' => 'lines',
    	)
    );
    $flashChart->setData($data);
    
    // Set Ranges in the chart
    $flashChart->setRange('y', 0, 100);
    $flashChart->setRange('x', 0, 10);
    
    // Show the graph
    echo $flashChart->render();
    ?>



Example 3 - Scatter (Points) Graphs
```````````````````````````````````
This type of graph uses a different syntax to define the data. It
requires data to be set as points (pairs x,y), here is the example:

View Template:
``````````````

::

    <?php
    // Sets height and width
    $flashChart->begin(400, 250);
    // Title
    $flashChart->title('Example 3 - Scatter: Some Random Points');
    // Configure Grid style and legends
    $flashChart->configureGrid(
    	array(
    		'x_axis' => array(
    			'step' => 1,
    			'legend' => 'Day'
    		),
    		'y_axis' => array(
    			'legend' => '#Hits',
    		)
    	)
    );
    // Prepare some random data (10 points)
    $random_points = array();
    for ($i=0; $i < 10; $i++) { 
    	// Each point is represented as a pair (x,y)
    	$random_points[] = array('x' => $i, 'y' => rand(0,100));
    }
    // Register each data set with its information.
    $data = array(
    	'Random Points' => array(
    		'color' => '#00aa42',
    		'font_size' => 11,
    		'data' => $random_points,
    		'graph_style' => 'scatter'
    	)
    );
    $flashChart->setData($data);
    
    // Set Ranges in the chart
    $flashChart->setRange('y', 0, 100);
    $flashChart->setRange('x', 0, 10);
    
    // Show the graph
    echo $flashChart->render();
    ?>



Example 4 - Pie Graphs
``````````````````````
This type of graph also uses a different syntax, here is the example:

View Template:
``````````````

::

    <?php
    $flashChart->begin(400, 250);
    $flashChart->title('Example 4 - Pie Chart: My imaginary Browser Stats');
    $browser_data = array(
    	'Firefox' => array(
    		'value' => 30
    	),
    	'Opera' => array(
    		'value' => 7
    	),
    	'IE' => array(
    		'value' => 38
    	),
    	'Other' => array(
    		'value' => 25
    	)
    );
    $flashChart->pie($browser_data);
    
    echo $flashChart->render();
    ?>

Tip: Flash Chart Helper automatically selects colors for each element
in data if you don't set them explicitly.


Example 5 - Mixed Graphs
````````````````````````
Open Flash Chart allows to draw various data sets inside one graph,
you can mix bars with lines and scatter, here are some examples that
extend the first example.

View Template:
``````````````

::

    <?php
    // Sets height and width
    $flashChart->begin(400, 250);
    // Title
    $flashChart->title('Example 5 - Mixed: Hits per Day vs. # Visits');
    // Configure Grid style and legends
    $flashChart->configureGrid(
    	array(
    		'x_axis' => array(
    			'step' => 1,
    			'legend' => 'Day'
    		),
    		'y_axis' => array(
    			'legend' => '#Hits',
    		)
    	)
    );
    // Prepare some random data (10 points)
    $visits = array();
    $random_hits2 = array();
    for ($i=0; $i < 10; $i++) { 
    	$visits[] = rand(10,50);
    	$random_hits2[] = rand(50,100);
    }
    // Register each data set with its information.
    $data = array(
    	'Hits' => array(
    		'color' => '#afe342',
    		'font_size' => 11,
    		'data' => $random_hits2,
    		'graph_style' => 'line_dot',
    	),
    	'Visits' => array(
    		'color' => '#324aef',
    		'font_size' => 11,
    		'data' => $visits,
    		'graph_style' => 'bar',
    	)
    );
    $flashChart->setData($data);
    
    // Set Ranges in the chart
    $flashChart->setRange('y', 0, 100);
    $flashChart->setRange('x', 0, 10);
    
    // Show the graph
    echo $flashChart->render();
    ?>



Results
~~~~~~~
The results from this examples can be found at
`http://aikon.com.ve/flashchart/`_

What's Missing
~~~~~~~~~~~~~~
At this point there are two types of charts that Open Flash Chart
allows that the Helper doesn't implement:

+ High Low Close: `http://teethgrinder.co.uk/open-flash-chart/gallery-
  hlc.php`_
+ Candle: `http://teethgrinder.co.uk/open-flash-chart/gallery-
  candle.php`_

Some enhancements could be done:

+ Some higher level function to encapsulate many of the lines used in
  the examples can be written.
+ Automatically choosing the ranges of the axis.

If you find anything missing, please report it:
`http://trac2.assembla.com/cakeopenflashchart/newticket`_

.. _http://trac2.assembla.com/cakeopenflashchart/newticket: http://trac2.assembla.com/cakeopenflashchart/newticket
.. _http://aikon.com.ve/open-flash-chart-helper-graficos-al-estilo-cake/: http://aikon.com.ve/open-flash-chart-helper-graficos-al-estilo-cake/
.. _http://teethgrinder.co.uk/open-flash-chart/download.php: http://teethgrinder.co.uk/open-flash-chart/download.php
.. _http://teethgrinder.co.uk/open-flash-chart/gallery-hlc.php: http://teethgrinder.co.uk/open-flash-chart/gallery-hlc.php
.. _http://teethgrinder.co.uk/open-flash-chart/gallery-candle.php: http://teethgrinder.co.uk/open-flash-chart/gallery-candle.php
.. _http://aikon.com.ve/flashchart/: http://aikon.com.ve/flashchart/
.. meta::
    :title: Open Flash Chart Helper: draw charts the Cake way
    :description: CakePHP Article related to flash,graph,chart,charts,FlashChart,FlashChartHelper,Helpers
    :keywords: flash,graph,chart,charts,FlashChart,FlashChartHelper,Helpers
    :copyright: Copyright 2008 
    :category: helpers

