

iCalendar event generator
=========================

by %s on January 27, 2009

I was looking for a way to export data in an iCalendar (RFC 2445)
compatible format. To accomplish this I created a helper class that
generates this code using the iCalcreator class.
There are two major iCalendar classes available for PHP, phpicalendar
`http://phpicalendar.net/`_ and iCalcreator
`http://www.kigkonsult.se/iCalcreator/index.php`_. I chose the later
since it was a single class file and slightly better from a object
oriented point of view.

The first step is to download a copy of iCalcreator and place the
iCalcreator.class.php file in your /vendors folder.
`http://www.kigkonsult.se/downloads/index.php`_
Now place the following helper class into your /views/helpers folder.


Helper Class:
`````````````

::

    <?php 
    require_once('vendors/iCalcreator.class.php');
    
    class ICalHelper extends 
    
    Helper 
    {
    	var $name = 'ICalHelper';
    	var $errorCode = null;
    	var $errorMessage = null;
    	
    	var $calendar;
    			
    	function create($name, $description='', $tz='US/Eastern')
    	{
    		$v = new vcalendar();
    		$v->setConfig('unique_id', $name.'.'.'yourdomain.com');
    		$v->setProperty('method', 'PUBLISH');
    		$v->setProperty('x-wr-calname', $name.' Calendar');
    		$v->setProperty("X-WR-CALDESC", $description);
    		$v->setProperty("X-WR-TIMEZONE", $tz);
    		$this->calendar = $v;
    	}
    	
    	function addEvent($start, $end=false, $summary, $description='', $extra=false)
    	{
    		$start = strtotime($start);
    		
    		$vevent = new vevent();
    		if(!$end)
    		{
    			$end = $start + 24*60*60;
    			$vevent->setProperty('dtstart', date('Ymd', $start), array('VALUE'=>'DATE'));
    			$vevent->setProperty('dtend', date('Ymd', $end), array('VALUE'=>'DATE'));
    		}
    		else
    		{
    			$end = strtotime($end);
    			$end = getdate($end);
    			$end['sec'] = $end['second'];
    			$end['hour'] = $end['hours'];
    			$end['min'] = $end['minutes'];
    			$end['month'] = $end['mon'];
    			
    			$start = getdate($start);
    			$start['sec'] = $start['second'];
    			$start['hour'] = $start['hours'];
    			$start['min'] = $start['minutes'];
    			$start['month'] = $start['mon'];
    			
    			$vevent->setProperty('dtstart', $start);
    			$vevent->setProperty('dtend', $end);			
    		}
    		$vevent->setProperty('summary', $summary);
    		$vevent->setProperty('description', $description);
    		if(is_array($extra))
    		{
    			foreach($extra as $key=>$value)
    			{
    				$vevent->setProperty($key, $value);
    			}
    		}
    		$this->calendar->setComponent($vevent);
    	}
    	
    	function getCalendar()
    	{
    		return $this->calendar;
    	}
    	
    	function render()
    	{
    		$this->calendar->returnCalendar();
    	}
    }
    ?>

Once you have completed this step you may want to support ics
extensions in your application, which is required by some calendar
applications.

To do this, add ics to your router extensions in config/routes.php

::

    
    Router::parseExtensions('ics'); 

Now setup a function in your controller for your calendar view. It
might look something like:


Controller Class:
`````````````````

::

    <?php 
       class Project extends AppController
       {
            var $helpers = array('Html', 'Text', 'ICal');        
    	function due() {
    		$this->Project->recursive = 0;
    		$this->paginate = array(
    		  'Project' => array(
    		    'order' => 'due ASC',
    		    'limit' => 5,
    		    'scope' => array('complete = 0 AND due IS NOT NULL')
    		  )
    		);
    		$projects_due = $this->paginate();
    		return $projects_due;
    	}
        }
    ?>

Now create a view for your data. In my case to speed up the operation
I used the caching instructions from
`http://bakery.cakephp.org/articles/view/optimizing-your-cakephp-
elements-and-views-with-caching`_ and created an element
(elements/projects_due.ctp) to handle the conversion of to ics.

::

    
    <?php
    	$projects = $this->requestAction('projects/due');
    	$iCal->create('Activeprojects', 'Active outstanding projects', 'US/Eastern');
    	
    	foreach($projects as $Project)
    	{
    		$iCal->addEvent($Project['Project']['due'], false, $Project['Project']['title'], $Project['Project']['description']."\n\n".$html->url('/Project/view/'.$Project['Project']['id'], true), array('UID'=>$Project['Project']['id'], 'attach'=>$html->url('/Project/view/'.$Project['Project']['id'], true), 'organizer'=>$Project['User']['username'], 'location'=>$Project['location']));
    	}
    	$iCal->render();
    ?>

Now include this element in your view (views/projects/due.ctp).


View Template:
``````````````

::

    
    <?php 
    echo $this->element('projects_due', array('cache'=>'+1 hour')); 
    ?>

Now go to `http://yourdomain.com/projects/due.ics`_ and you should get
an ics file download that you can open in any icalendar compatible
program or you can paste that URL in Google calendar.

.. _http://phpicalendar.net/: http://phpicalendar.net/
.. _http://yourdomain.com/projects/due.ics: http://yourdomain.com/projects/due.ics
.. _http://www.kigkonsult.se/downloads/index.php: http://www.kigkonsult.se/downloads/index.php
.. _http://www.kigkonsult.se/iCalcreator/index.php: http://www.kigkonsult.se/iCalcreator/index.php
.. _http://bakery.cakephp.org/articles/view/optimizing-your-cakephp-elements-and-views-with-caching: http://bakery.cakephp.org/articles/view/optimizing-your-cakephp-elements-and-views-with-caching
.. meta::
    :title: iCalendar event generator
    :description: CakePHP Article related to calendar,export,ics,icalendar,Helpers
    :keywords: calendar,export,ics,icalendar,Helpers
    :copyright: Copyright 2009 
    :category: helpers

