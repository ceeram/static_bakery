Brute Force Protection
======================

by %s on November 04, 2006

This component is meant to protect you against brute force attacks on
forms (and other types of requests).


Usage
~~~~~

Download the code below to the proper files, and include the component
in your controller.

If the user wants to access a function, you can protect it with:

::

    $this->Protector->access($name,$limit);

$name should be unique for every action you want to protect, and
$limit is the maximum number of attemps the user can make. This method
returns true if the maximum number of tries has not been exceeded.

::

    $this->Protector->fail($name, $timeout);

This logs a failure in the database. The method simply indicates that
the user took an action. See the example below for a better
explanaition.

As in the first case, $name represents the name of the action, and
$timeout is the time this action takes to expire (only this, not all).
After expiration, the log entry will be deleted. This value must be a
valid integer, followed by "s" for second, "m" for minute, "h" for
hour, "d" for day, "w" for week, "o" for month, "q" for quarter, or
"y" for year. If none is given, minute will be assumed.



Example
~~~~~~~


Controller Class:
`````````````````

::

    <?php 
    class YourController extends AppController
    {
         var $components = array('Protector');
    
          function login()
          {
    	   if ($this->Protector->access("login",3)
    	   {
    		if (!$login_sucessfull)
    			$this->Protector->fail("login","5m");
    	    }
    	    else
    	    {
    		$this->flash("/","sorry you exeeded the amount of allowed trys, please come back in 5 minutes again");
    	    }
    
         }
    }
    ?>





The Component
~~~~~~~~~~~~~
goes to app/controllers/components/protector.php

Component Class:
````````````````

::

    <?php 
    
    class ProtectorComponent extends Object
    {
    	
    	function startup(&$controller)
    	{
    		$this->controller = $controller; 
    		loadModel('protect');
    		$this->Protect =& new Protect(); 
    	}
    
    	function access($action,$limit)
    	{
    		if ($this->Protect->amount(gethostbyname($_SERVER['REMOTE_ADDR']),$action) < $limit)
    		{
    			return true;
    		}
    		else
    		{
    			return false;
    		}
    	}
    
    
    	function fail($action,$expire)
    	{
    		$unit="MINUTE";
    		switch (strtolower($expire{strlen($expire)-1}))
    		{
    			case 's':$unit="SECOND";break;
    			case 'm':$unit="MINUTE";break;
    			case 'h':$unit="HOUR";	break;
    			case 'w':$unit="WEEK";	break;
    			case 'o':$unit="MONTH";	break;
    			case 'q':$unit="QUARTER";break;
    			case 'y':$unit="YEAR";	break;
    		}
    		$this->Protect->insert(gethostbyname($_SERVER['REMOTE_ADDR']),$action,substr ( $expire,0,strlen($expire)-1),$unit);
    	}
    }
    
    ?>




The Model
~~~~~~~~~
goes to app/models/protect.php

Model Class:
````````````

::

    <?php 
    class Protect extends AppModel 
    { 
    	var $name = 'Protect'; 
    
    	function insert($ip,$action,$expire,$unit)
    	{
    		$this->query("INSERT INTO `protects` (`IP` , `action` , `expire` ) VALUES ('$ip', '$action', TIMESTAMPADD($unit,$expire, NOW()));");
    	}
    
    
    	function cleanout()
    	{
    		$this->query("DELETE FROM `protects` WHERE `expire`<=NOW();");
    	}
    
    	function amount($ip,$action)
    	{
    		$this->cleanout();
    		$rs= $this->query("SELECT COUNT(*) AS 'amount' from  `protects` WHERE ip='$ip' AND  action='$action'");
    		return $rs[0][0]["amount"];
    	}
    } 
    ?>





The Database
~~~~~~~~~~~~
Create the following table:

CREATE TABLE `protects` (
`IP` VARCHAR(255) NOT NULL,
`action` VARCHAR(255) NOT NULL,
`expire` DATETIME NOT NULL
);


.. meta::
    :title: Brute Force Protection
    :description: CakePHP Article related to brute,security,crack,force,Components
    :keywords: brute,security,crack,force,Components
    :copyright: Copyright 2006 
    :category: components

