dAuth v0.3 models
=================

by Dieter_be on November 15, 2006

User, Host, LoginAttempt models for dAuth v0.3


models/user.php
```````````````

Model Class:
````````````

::

    <?php 
    /*
     * PHP versions 4 and 5
     *
     * dAuth: A secure authentication system for the cakePHP framework.
     * Copyright (c)	2006, Dieter Plaetinck
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author			Dieter Plaetinck
     * @copyright		Copyright (c) 2006, Dieter Plaetinck
     * @version			0.3
     * @modifiedby		Dieter@be
     * @lastmodified	$Date: 2006-12-04 16:18:00 +0000 (Mon, 4 Dec 2006) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    class User extends AppModel
    {
        var $name = 'User';
        var $displayField = 'username';
    
        var $validate = array(
            'username'	=> '/[a-z0-9\_\-]{3,}$/i',
            'email'     => VALID_EMAIL,
            'password'	=> VALID_NOT_EMPTY
        );
    
        function changePassword ($id = null, $hash = null)
        {
        	$success = false;
        	if ($id && $hash)
        	{
        		$this->id = $id;
        		$success = $this->saveField('password', $hash);
        	}
        	return $success;
        }
    }
    ?>


models/host.php
```````````````

Model Class:
````````````

::

    <?php 
    /*
     * PHP versions 4 and 5
     *
     * dAuth: A secure authentication system for the cakePHP framework.
     * Copyright (c)	2006, Dieter Plaetinck
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author			Dieter Plaetinck
     * @copyright		Copyright (c) 2006, Dieter Plaetinck
     * @version			0.3
     * @modifiedby		Dieter@be
     * @lastmodified	$Date: 2006-12-04 16:18:00 +0000 (Mon, 4 Dec 2006) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    class Host extends AppModel
    {
        var $name = 'Host';
    	var $hasMany = array('LoginAttempt');
        var $validate = array (
            'ip_adress'  => VALID_NOT_EMPTY
        );
        function block ($id = null,$time = null)
        {
        	$success = false;
        	if ($id)
        	{
        		if(!$time)
        		{
        			$time = time();
        		}
        		$this->id = $id;
        		$success = $this->saveField('blocked', $time);
        	}
        	return $success;
        }
    
    	/*
    	 *  not really used since hosts are unblocked automatically when the 'blocked' timestamp expires
    	 */
       function unBlock ($id = null)
       {
       		$success = false;
       		if ($id)
       		{
       			$this->id = $id;
       			$success = $this->saveField('blocked', '0');
       		}
       		return $success;
       	}
    
       function isBlocked($host = null,$limit = null)
       {
       		$blocked = false;
    
       		if($host && $limit)
       		{
       			if($host['Host']['blocked'] >= $limit)
       			{
       				$blocked = true;
       			}
       		}
       		return $blocked;
       }
    
       function isHammering($data = null,$rules = null)
    	{
    		$hammer = false;
    
    		if($data['Host'] && $rules && is_array($rules))
    		{
    			//$datetime = gmdate("Y-m-d H:i:s", $time);
    			//strtotime($datetime.' GMT')
    			$time = time();
    			$time += 60*60;
    			//FIXME: really ugly hack . time() is gmt while cake is my timezone. making gmdate -> date below, doesn't work
    			$limit = $time - $rules['seconds'];
    			$attempts = $this->LoginAttempt->findCount(array('host_id' => ' = '.$data['Host']['id'],'LoginAttempt.created' => '>= '.gmdate("Y-m-d H:i:s", $limit)));
    			if($attempts >= $rules['attempts'])
    			{
    				$hammer = true;
    			}
    		}
    		return $hammer;
    	}
    }?>


models/login_attempt.php
````````````````````````

Model Class:
````````````

::

    <?php 
    /*
     * PHP versions 4 and 5
     *
     * dAuth: A secure authentication system for the cakePHP framework.
     * Copyright (c)	2006, Dieter Plaetinck
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author			Dieter Plaetinck
     * @copyright		Copyright (c) 2006, Dieter Plaetinck
     * @version			0.3
     * @modifiedby		Dieter@be
     * @lastmodified	$Date: 2006-12-04 16:18:00 +0000 (Mon, 4 Dec 2006) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    class LoginAttempt extends AppModel
    {
        var $name = 'LoginAttempt';
        var $validate = array(
            'host_id'  => VALID_NUMBER
        );
        var $belongsTo = array('Host');
        function cleanUpExpired($date_limit = null)
    	{
    		if($date_limit)
    		{
    			$this->query('DELETE FROM `login_attempts` WHERE `login_attempts`.`created` <= '.gmdate("Y-m-d H:i:s",$date_limit));
    		}
    	}
     }?>


Here is the sql that you should execute
```````````````````````````````````````

::

    
    --
    -- Table structure for table `hosts`
    --
    
    DROP TABLE IF EXISTS `hosts`;
    CREATE TABLE `hosts` (
      `id` int(11) NOT NULL auto_increment,
      `ip_adress` varchar(255) NOT NULL default '',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
      `blocked` int(11) NOT NULL default '0',
      PRIMARY KEY  (`id`)
    );
    
    --
    -- Table structure for table `login_attempts`
    --
    
    DROP TABLE IF EXISTS `login_attempts`;
    CREATE TABLE `login_attempts` (
      `id` bigint(20) unsigned NOT NULL auto_increment,
      `host_id` int(255) NOT NULL default '0',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    );
    
    --
    -- Table structure for table `users`
    --
    
    DROP TABLE IF EXISTS `users`;
    CREATE TABLE `users` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `username` varchar(255) NOT NULL default '',
      `email` varchar(255) NOT NULL default '',
      `password` varchar(255) NOT NULL default '',
      PRIMARY KEY  (`id`),
      UNIQUE KEY `username` (`username`)
    );

more info about dAuth @ `http://bakery.cakephp.org/articles/view/147`_

.. _http://bakery.cakephp.org/articles/view/147: http://bakery.cakephp.org/articles/view/147

.. author:: Dieter_be
.. categories:: articles, models
.. tags:: login,dauth,challenge response,secure,Models

