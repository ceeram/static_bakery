Simple TimerHelper to measure rendering times
=============================================

by %s on April 12, 2008

Working on a project that logs a lot of data I have found it useful to
time the rendering-times of certain views, elements and other sections
of code. I wrapped this code up info a helper just to have it handy.
The same code can easily be put into a Component if it is controller-
processing that needs measuring.
There is not much to it, hardly rocket-science. But it might be of use
to someone other than myself.

Installation is like any other Helper.

You set a new timer by calling start and the time passed is returned
when stop is called.
You can start many simultaneous timers independently.
You can call stop over and over to get "lap-times" if you like.

Usage example:

::

    
    $timer->start('forloop');
    
    for ( $i=0; $i< {a big number}; $i++ )
    {
        // do calculation or some rendering
    }
    
    echo $timer->stop('forloop');


And the mini-helper (timer.php):


Helper Class:
`````````````

::

    <?php 
    // simple helper for timing the rendering of elements or other view-sections
    // microtime calculations from:
    // http://www.chauy.com/2005/11/creating-a-php-script-to-measure-php-execution-time/
    
    class TimerHelper extends Helper
    {
    	var $running_timers = array();
    	
    	function __construct() {}
    
    	function start($k)
    	{
    		$time = microtime();
    		$time = explode(' ', $time);
    		$time = $time[1] + $time[0];
    		$this->running_timers[$k] = $time;
    	}
    
    	function stop($k)
    	{
    		$time = microtime();
    		$time = explode(" ", $time);
    		$time = $time[1] + $time[0];
    		$endtime = $time;
    		return ($endtime - $this->running_timers[$k]);
    	}
    }
    ?>


.. meta::
    :title: Simple TimerHelper to measure rendering times
    :description: CakePHP Article related to timer,profile,measure,bench,Helpers
    :keywords: timer,profile,measure,bench,Helpers
    :copyright: Copyright 2008 
    :category: helpers

