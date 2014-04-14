Baking Cakes with FirePHP
=========================

by Warringer on September 21, 2008

Some days ago I discovered the Firebug extension FirePHP while looking
around for Firefox addons. The way to use Firebug to log Error and
other general debugging messages looked very promising and I decided
to add it to my current Project.
Of course you need some things to use FirePHP.


#. Firefox 2 or 3(I guess everyone knows where to find it... :) )
#. Firebug `http://getfirebug.com/`_ 1.0 or 1.1 for Firefox 2, 1.2 for
   Firefox 3
#. FirePHP plugin for Firebug `https://addons.mozilla.org/en-
   US/firefox/addon/6149`_ or `http://www.firephp.org/`_
#. FirePHP Core library for PHP `http://www.firephp.org/`_

NOTE: You must have the Firebug Net panel enabled for FirePHP to work.
New NOTE 2: FirePHP works only for PHP 5.

For general information about FirePHP you should visit
`http://www.firephp.org/`_ anyway.

Please note that the following way to use FirePHP is very basic at the
moment and pretty much not more than a quick 'hack'.

The first is to put the file FirePHP.class.php into /app/vendors
folder.

The next if to make a copy of dbo_source.php and put it into
/app/models/datasources to keep the core of Cake untouched.

Now you just need to replace showLog() in your copy of dbo_source.php
with the following.

::

    <?php 
    /**
     * Outputs the contents of the queries log.
     *
     * @param boolean $sorted
     */
    	function showLog($sorted = false) {
    		if ($sorted) {
    			$log = sortByKey($this->_queriesLog, 'took', 'desc', SORT_NUMERIC);
    		} else {
    			$log = $this->_queriesLog;
    		}
    
    		if ($this->_queriesCnt > 1) {
    			$text = 'queries';
    		} else {
    			$text = 'query';
    		}
    
    		if (php_sapi_name() != 'cli') {
    			$summery = "{$this->_queriesCnt} {$text} took {$this->_queriesTime} ms";
    			$header = array("Nr", "Query", "Error", "Affected", "Num. rows", "Took (ms)");
    			$body = array($header);
    			foreach ($log as $k => $i) {
    				$row = array(($k + 1), $i['query'], $i['error'], $i['affected'], $i['numRows'], $i['took']);
    				$body[] = $row;
    				}
    			fb(array($summery, $body), FirePHP::TABLE);
    			} else {
    			foreach ($log as $k => $i) {
    				print (($k + 1) . ". {$i['query']} {$i['error']}\n");
    			}
    		}
    	}
    ?>

Next you modify your bootstrap.php with the following:

::

    <?php 
    ob_start();
    App:: import ( 'Vendor', 'FirePHP', array ( 'file' => 'FirePHP.class.php'));
    function fb() 
    {
      $instance = FirePHP::getInstance(true);
      $args = func_get_args();
      return call_user_func_array(array($instance,'fb'),$args);
      return true;
    }
    ?>

FirePHP requires output buffering and fb() is for convenience... :)

Now enjoy your baking with some more fire...

Through of course other Debugging can be done with FirePHP as well,
aside from logging Database access, like in this example.

I'm pretty sure it can be turned into a Plugin, but I have to say that
I'm a little to new to Cake to be able to create one...

.. _https://addons.mozilla.org/en-US/firefox/addon/6149: https://addons.mozilla.org/en-US/firefox/addon/6149
.. _http://getfirebug.com/: http://getfirebug.com/
.. _http://www.firephp.org/: http://www.firephp.org/
.. meta::
    :title: Baking Cakes with FirePHP
    :description: CakePHP Article related to debug,firephp,Tutorials
    :keywords: debug,firephp,Tutorials
    :copyright: Copyright 2008 Warringer
    :category: tutorials

