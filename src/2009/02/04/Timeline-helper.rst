Timeline helper
===============

by ronnyvv on February 04, 2009

Easy to use helper for the MIT Simile Web Widgets component Timeline.
Timeline allows web site creators to embed interactive timelines into
their sites. It requires only that visitors have Javascript enabled.
It's often referred to as "Google Maps" for time.


Background
~~~~~~~~~~
We often need to display time based data, but just listing a table of
dates and times doesn't give the user any real understanding of
duration or the relationship between points in time. Using the
Timeline widget you can get an immediate feeling for a series of
events and how they relate.


What it does
~~~~~~~~~~~~
Timeline helper gives you a way to present time based data in an easy
to use and fully interactive way using only javascript and a single
ajax call per dataset.

Example timelines
`````````````````
`http://simile-widgets.googlecode.com/svn/timeline/tags/latest/src/web
app/examples/index.html`_

Basic concepts
~~~~~~~~~~~~~~
A timeline contains one or more bands, which can be panned infinitely
by dragging with the mouse pointer. A band can be configured to
synchronize with another band such that panning one band also scrolls
the other.

A timeline is implemented as a div element that contains inner div
elements as its bands. The band divs are cropped and positioned
relative to the timeline div.

A band div itself contains several inner elements that implements
various parts of the band. For example, the two timelines above show
labels for days, weeks, months, and years. The bands also have
different background colors, and the weekly band of the second
timeline has weekend markings. All of these visual elements are
"painted" by adding HTML elements to the band divs at the appropriate
positions.

As a band is panned, its div is shifted horizontally or vertically,
carrying all of its visual elements along. When either end of the band
div approaches the visible (non-cropped) area, the band div is re-
centered, its coordinate origin is changed, and then its various
visual elements are re-"painted" relative to the new coordinate
origin. All of this "paging" is done as seamlessly as possible so that
the user experiences smooth, infinite panning.


More documentation for Timeline widget
``````````````````````````````````````
`http://code.google.com/p/simile-widgets/wiki/Timeline`_

To do
~~~~~
The vendor supports themes for defining how the timeline is displayed
and partially how it functions, but the api for doing this is far from
optimal and is quite tricky to wrap in php. We would really like to
support themes in the future, if anyone wants to help let us know!



Example Project:
~~~~~~~~~~~~~~~~

We have two models: MainActivity and SubActivity. MainActivity hasMany
SubActivity. We wish to take advantage of the Timeline helper to
compare main activities and to see all subactivities in relation the
main activity they belong to. We implement this as
MainActivitiesController::index and MainActivitiesController::view
actions.


View 1 : index



::

    <?php
    // MainActivites controller action:
    function index() {
    }
    ?>


View Template:
``````````````

::

    <?php // /app/main_activites/index.php ?>
    <div class="mainActivities index">
    <h2><?php __('MainActivities');?></h2>
    <div id="timeline" style="width:100%;height:500px;"></div>
    <?php 
     echo $timeline->create('timeline');
     $timeline->band(array(
     	'width' => '"90%"',
    	'intervalPixels' => '300',
    	'intervalUnit'=>'Timeline.DateTime.MONTH'
     ));
     $timeline->band(array(
     	'width' => '"10%"',
    	'intervalPixels' => '700',
    	'intervalUnit'=>'Timeline.DateTime.YEAR',
     	'overview' => true
     ));
     $timeline->setDataSource(array('action'=>'ajax_timedex')); 
     echo $timeline->end();
    ?>
    </div>

We use an empty controller action as all data rendered in the view is
rendered through the timeline and it uses the ajax request bellow to
retrieve the data. In the view we create a DIV where the timeline will
be placed, we initialize the helper and addd the two bands we wish to
use. Then we set the datasource using Helper::url array notation and
end the timeline.

::

    <?php
    // MainActivites controller action:
    function timedex() {
     Configure::write('debug',0);
     $this->layout = 'ajax';
     $this->set('data', $this->MainActivity->find('all',array('recursive' => -1)));
    }


View Template:
``````````````

::

    <?php // /app/main_activities/ajax_timedex.ctp
    	foreach ($data as $key => $row) {		
    		$data[$key]['MainActivity']['link'] =
    			$html->url(array(
    				'action' => 'timeline',
    				$row['MainActivity']['id']
    			));
    	}
    	$xpaths = array(
    		'title' => '/MainActivity/title',
    		'start' => '/MainActivity/from',
    		'end' => '/MainActivity/to',
    		'color' => '/MainActivity/color',
    		'description' => '/MainActivity/description',
    		'link' => '/MainActivity/link'
    	);
    	echo $timeline->renderJSON($data,array(),$xpaths);
    ?>

This being an ajax view, we make sure to set config to 0 and select an
ajax layout. In the view we do 3 things. First we add a url to each
main activity that will make Timeline use the title on the timeline
bubble as a link. Then we prepare an options array for specifying
where the helper can find the values that we wish included. And lastly
we call what is essentially a JavascriptHelper::object, but it makes
sure you only send the data you are going to use.



For the other view we will show relevant information about the main
activity and a timeline with it, and all it's sub activities. We do
this in the same way as before, with an actual action/view and ajax
for timeline content. This time however we have 2 datasets for the
timeline, so we need 2 ajax actions and views.


Controller Class:
`````````````````

::

    <?php class MainActivitiesController extends AppController {
    // [..]
    function ajax_timeline($id) {
    	Configure::write('debug',0);
    	$this->layout = 'ajax';
    	$this->set('data', $this->MainActivity->read(null, $id));
    }
    function ajax_timeline_main($id) {
    	Configure::write('debug',0);
    	$this->layout = 'ajax';
    	$this->MainActivity->recursive = -1;
    	$this->set('data', $this->MainActivity->read(null, $id));
    }
    function view($id = null) {
    	$this->MainActivity->recursive = -1;
    	$this->set('data', $this->MainActivity->read(null, $id));
    }
    ?>


View Template:
``````````````

::

    <?php // /app/main_activities/view.ctp
    echo '<h2'.$data['MainActivity']['title'].'</h2>';
    echo '<p>'.$data['MainActivity']['description'].'</p>';
    <div id="timeline" style="width: 1000px; height: 360px;"></div>
    <?php
    echo $timeline->create('timeline');
    	
    $timeline->band(array('width' => '"40px"','intervalPixels' => '100',
    		'intervalUnit'=>'Timeline.DateTime.WEEK'),
    	'main'
    );	
    
    $timeline->band(array('width' => '"250px"','intervalPixels' => '100',
    		'intervalUnit'=>'Timeline.DateTime.WEEK'),
    	'sub'
    );
    $timeline->band(array('width' => '"30px"','overview' => "true", 
    		'intervalPixels' => '900','intervalUnit'=>'Timeline.DateTime.YEAR'),
    	'sub'
    );
    
    $timeline->band(array('width' => '"40px"','intervalPixels' => '900',
    		'intervalUnit'=>'Timeline.DateTime.YEAR'),
    	'main'
    );	
    
    $timeline->setDataSource(array('action'=>'ajax_timeline',$data['MainActivity']['id']),'sub');
    $timeline->setDataSource(array('action'=>'ajax_timeline_main',$data['MainActivity']['id']),'main');
    	
    echo $timeline->end();
    ?>

The main difference here between this view and the previous is that we
here name our datasource(s) and specify to each band what datasource
they use (main and sub). The first band is the main activity and the
second is it's sub activities, these are both shown in weeks and are
therefore comparable. The third band is an overview band that will
help locate interesting parts of the year for sub activities and
likewise the last band shows the main activity in years.

View Template:
``````````````

::

    <?php // /app/main_activities/ajax_timeline.ctp
    $xpaths = array(
    	'title' => '/title',
    	'start' => '/from',
    	'end' => '/to',
    	'color' => '/color'
    );
    echo $timeline->renderJSON($data['SubActivity'],array(),$xpaths);
    ?>


View Template:
``````````````

::

    <?php // /app/main_activities/ajax_timeline_main.ctp
    $defaults = array(
    	'color' => '#239323',
    );
    $xpaths = array(
    	'title' => '/title',
    	'start' => '/from',
    	'end' => '/to',
    	'description' => '/description'
    );
    echo $timeline->renderJSON(array(0=> $data['MainActivity']),$defaults,$xpaths);
    ?>

In the second ajax view notice how we take advantage of the $defaults
option to give the activity a color and since renderJSON expects the
result of a find('all'), we add a level to the $data when sending it
in.




Private variables/default values
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

$bandInfoDefaults
`````````````````
Default values for each band

Public methods
~~~~~~~~~~~~~~

band($options = array(), $bandGroup = 'main', $timeSync = 'main')
`````````````````````````````````````````````````````````````````

+ $options array: options for Timeline.createBandInfo()
+ $bandGroup string: named group of bands to share datasource with
+ $timeSync string: named group of bands to syncronize with

Define a new band on the timeline


create($attributes = array(), $id = 'timeline', $createDiv = true )
```````````````````````````````````````````````````````````````````

+ $attributes array: html attributes for the div containing the
  timeline
+ $id string
+ $createDiv boolean
+ return: string DIV element if created

Initilizes timeline and renders the div that will contain the timeline


end()
`````

+ return: string SCRIPT element containing javascript code to render
  the timeline

Renders the defined timeline


setClickEvent($javascript = '')
```````````````````````````````

+ $javascript string: function called when clicking on a band

Set a custom event handler for click event on timeline


setDataSource($url, $bandGroup = 'main')
````````````````````````````````````````

+ $url mixed: url to JSON data
+ $bandGroup string: named group of bands to share datasource with

Set the URL to fetch data from

[h4]renderJSON($data = array(), $defaultEventAttributes = array(),
$xpaths = array())
``````````````````

+ $data array
+ $defaultEventAttributes array
+ $xpaths array: XPaths to Event attributes in $data
+ return: string JSON encoded timeline data

Renders JSON encoded event data




Helper Class:
`````````````

::

    <?php 
    /**
     * Helper for using SIMILE Timeline
     *
     * Examples:
     * 
     *  Timeline with single band, with data from /controller/json/$data['Timeline']['id']
     * 
     * <?php
     *	echo $timeline->create(array('style'=>'width:200px; height:100px;'));
     *	$timeline->band();
     *	$timeline->setDataSource(array('action'=>'json', $data['Timeline']['id']));
     *	echo $timeline->end();
     * ?>
     * 
     * Timeline with two bands showing the same data using two different timescales:
     * 
     * <?php
     *	echo $timeline->create(array('style'=>'width:200px; height:100px;'));
     *  $timeline->band(array('width'=>'"80%"','intervalUnit'=>'Timeline.DateTime.MONTH','intervalPixels'=> 100));
     *	$timeline->band(array('width'=>'"20%"','intervalUnit'=>'Timeline.DateTime.YEAR','intervalPixels'=> 200));	
     *	$timeline->setDataSource(array('action'=>'json', $data['Timeline']['id']));
     *	echo $timeline->end();
     * ?>
     * 
     * Timeline with two bands showing the same data using two different timescales and different styles:
     * 
     * <?php
     *	echo $timeline->create(array('style'=>'width:200px; height:100px;'));
     *  $timeline->band(array('width'=>'"80%"','intervalUnit'=>'Timeline.DateTime.MONTH','intervalPixels'=> 100));
     *	$timeline->band(array('width'=>'"20%"','intervalUnit'=>'Timeline.DateTime.YEAR','intervalPixels'=> 200, 'overview'=>'true'));	
     *	$timeline->setDataSource(array('action'=>'json', $data['Timeline']['id']));
     *	echo $timeline->end();
     * ?>
     * 
     * 
     * @author Alexander Morland
     * @author Ronny Vindenes
     * @category Cake Helper
     * @license MIT
     * @version 1.0
     * 
     */
    class TimelineHelper extends AppHelper {
    	
    	public $helpers = array('Html', 'Time', 'Javascript');
    	
    	private $bandGroups = array();
    	private $timeSyncs = array();
    	private $bands = array();
    	private $divId;
    	private $bandCount = 0;
    	
    	private $bandInfoDefaults = array(
    			'width' => '"100%"', 
    			'intervalUnit' => 'Timeline.DateTime.DAY', 
    			'intervalPixels' => 100);
    	/**
    	 * Define a new band on the timeline
    	 *
    	 * @param array $options options for Timeline.createBandInfo()
    	 * @param string $bandGroup named group of bands to share datasource with
    	 * @param string $timeSync named group of bands to syncronize with
    	 */
    	public function band($options = array(), $bandGroup = 'main', $timeSync = 'main') {
    		$band = am(array('eventSource' => 'eventSource_' . $bandGroup), $this->bandInfoDefaults, $options);
    		$this->bands[] = $band;
    		$this->bandGroups[$bandGroup][] = $this->bandCount;
    		$this->timeSyncs[$timeSync][] = $this->bandCount++;
    	}
    	
    	/**
    	 * Initilizes timeline and renders the div that will contain the timeline
    	 *
    	 * @param array $attributes html attributes for the div containing the timeline
    	 * @param string $id
    	 * @param boolean $createDiv
    	 * 
    	 * @return string DIV element if created
    	 */
    	public function create($attributes = array(), $id = 'timeline', $createDiv = true) {
    		$this->Javascript->link('http://static.simile.mit.edu/timeline/api-dev/timeline-api.js?bundle=true', false);
    		$this->divId = $id;
    		
    		$html = '';
    		
    		if ($createDiv) {
    			$html .= $this->Html->div(null, '', am(array('id' => $id), $attributes));
    		}
    		
    		return $html;
    	}
    	
    	/**
    	 * Renders the defined timeline
    	 *
    	 * @return string SCRIPT element containing javascript code to render the timeline
    	 */
    	public function end() {
    		$code = 'SimileAjax.History.enabled = false;';
    		
    		if (!empty($this->clickEvent)) {
    			$code = $this->clickEvent;
    		}
    		
    		foreach ($this->bandGroups as $group => $data) {
    			$code .= "var eventSource_$group = new Timeline.DefaultEventSource();\n";
    		}
    		
    		$code .= $this->createBandInfo();
    		$code .= 'var timeline = Timeline.create(document.getElementById("' . $this->divId . '"), bandInfo);' . "\n";
    		unset($this->divId);
    		
    		foreach ($this->bandGroups as $bandGroup => $data) {
    			if (!empty($this->bandGroups[$bandGroup]['EventSource'])) {
    				$code .= 'timeline.loadJSON("' . $this->bandGroups[$bandGroup]['EventSource'] . '", function(data, url) { eventSource_' . $bandGroup . '.loadJSON(data, url); });' . "\n";
    			}
    			unset($this->bandGroups[$bandGroup]);
    		}
    		
    		$this->timeSyncs = array();
    		$this->bands = array();
    		$this->bandCount = 0;
    		
    		return $this->Javascript->codeBlock($code);
    	}
    	
    	/**
    	 * Set a custom event handler for click event on timeline
    	 *
    	 * @param string $javascript
    	 */
    	public function setClickEvent($javascript = '') {
    		$this->clickEvent = 'Timeline.DurationEventPainter.prototype._showBubble = function(x, y, evt) { ' . $javascript . ' }';
    	}
    	
    	/**
    	 * Set the URL to fetch data from
    	 *
    	 * @param mixed $url url to JSON data
    	 * @param mixed $bandGroup named group of bands to share datasource with
    	 */
    	public function setDataSource($url, $bandGroup = 'main') {
    		$url = $this->Html->url($url);
    		foreach ((array) $bandGroup as $group) {
    			$this->bandGroups[$group]['EventSource'] = $url;
    		}
    	}
    	
    	/**
    	 * Renders JSON encoded event data
    	 *
    	 * @param array $data
    	 * @param array $defaultEventAttributes
    	 * @param array $xpaths XPaths to Event attributes in $data
    	 * @return string JSON encoded timeline data
    	 */
    	public function renderJSON($data = array(), $defaultEventAttributes = array(), $xpaths = array()) {
    		$events = array();
    		foreach ($data as $key => $row) {
    			$events[$key] = $defaultEventAttributes;
    			$start = Set::extract($row, $xpaths['start']);
    			$events[$key]['start'] = $start[0];
    			foreach ($xpaths as $field => $path) {
    				$arr = Set::extract($row, $path);
    				$events[$key][$field] = $arr[0];
    			}
    		}
    		
    		$returnArr = array('dateTimeFormat' => 'iso8601', 'events' => $events);
    		
    		return $this->Javascript->object($returnArr);
    	}
    	
    	/**
    	 * Generate the javascript code for the BandInfo structure, including setting which bands to synchronize
    	 *
    	 * @return string Javascript code
    	 */
    	private function createBandInfo() {
    		$bandInfo = 'var bandInfo = [';
    		
    		foreach ($this->bands as $band => $info) {
    			$bandInfo .= 'Timeline.createBandInfo({';
    			
    			foreach ($info as $key => $value) {
    				$bandInfo .= $key . ': ' . $value . ', ';
    			}
    			
    			$bandInfo = substr($bandInfo, 0, -2);
    			$bandInfo .= '}), ';
    		}
    		
    		if (strlen($bandInfo) >= 18) {
    			$bandInfo = substr($bandInfo, 0, -2);
    		}
    		$bandInfo .= "];\n";
    		
    		foreach ($this->timeSyncs as $timeSync) {
    			$bandCount = count($timeSync);
    			if ($bandCount > 1) {
    				for ($i = 1; $i < $bandCount; $i++) {
    					$bandInfo .= 'bandInfo[' . $timeSync[$i] . '].syncWith = ' . $timeSync[$i - 1] . '; bandInfo[' . $timeSync[$i] . "].highlight = true;\n";
    				}
    			}
    		}
    		return $bandInfo;
    	}
    }
    ?>

[h4]Vendor code
```````````````
`http://code.google.com/p/simile-
widgets/source/browse/#svn/timeline/trunk`_
`1`_|`2`_|`3`_|`4`_|`5`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_
+ `Page 5`_

.. _http://code.google.com/p/simile-widgets/wiki/Timeline: http://code.google.com/p/simile-widgets/wiki/Timeline
.. _Page 4: :///articles/view/4caea0e3-a2c0-4d6f-9f15-472c82f0cb67/lang:eng#page-4
.. _Page 5: :///articles/view/4caea0e3-a2c0-4d6f-9f15-472c82f0cb67/lang:eng#page-5
.. _Page 2: :///articles/view/4caea0e3-a2c0-4d6f-9f15-472c82f0cb67/lang:eng#page-2
.. _Page 3: :///articles/view/4caea0e3-a2c0-4d6f-9f15-472c82f0cb67/lang:eng#page-3
.. _Page 1: :///articles/view/4caea0e3-a2c0-4d6f-9f15-472c82f0cb67/lang:eng#page-1
.. _http://code.google.com/p/simile-widgets/source/browse/#svn/timeline/trunk: http://code.google.com/p/simile-widgets/source/browse/#svn/timeline/trunk
.. _http://simile-widgets.googlecode.com/svn/timeline/tags/latest/src/webapp/examples/index.html: http://simile-widgets.googlecode.com/svn/timeline/tags/latest/src/webapp/examples/index.html

.. author:: ronnyvv
.. categories:: articles, helpers
.. tags:: helper,alkemann,ronnyvv,simile,timeline,Helpers

