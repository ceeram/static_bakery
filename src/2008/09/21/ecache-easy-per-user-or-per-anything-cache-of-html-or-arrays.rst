ecache - easy per user or per anything cache of html or arrays
==============================================================

by zeroasterisk on September 21, 2008

Caching is basically required for any data-heavy or interaction-heavy
site... but you need to be in control of who gets what cache; more
specifically, you need to be sure people only get their own cached
content.


Intro
=====
Cake < 1.2 has caching... great caching if your contact is the same
for everyone... but it's less than ideal for per-user content... in
fact if you wanted to cache the "public" version of a view, and ignore
cache >
CakePHP does have a great little "cache()" function, which greatly
simplifies caching content yourself... but I found that I was doing
pretty much the same thing over and over again... so I created the
following component to automate the creation of cache data based on:
$this->userid; and even for one site an additional parameter:
$this->groupid. The cache file name defaults to starting with the
current controller_action... but they can be arbitrarily set (which is
how I use it most of the time).

Feel free to add other parameters, if you need to split your caches by
other things... for me, I just needed a unique cache per userid and
per groupid.


When to Use
===========
I've provided a "MockUserController" below to demonstrate the use.
Basically, I use this component anytime I want to cache an expensive
database request, or the results of a requestAction, or even other
resource intensive content like cURL requests. The benefit of it, is I
can easily use the same code to cache differing result-sets based on
the userid of the person making the request... this implies that there
is some sort of security in place that person#1 would get different
results than person#2. I have also created a cf() shortcut to force a
cache_key to simplify using this component to control/access a global
cache file.


How to Use
==========
See the controller below for specific examples.


Controller Class:
`````````````````

::

    <?php 
    /***
    * == Example of Usage ==
    * The following is simply an example of how you might use the component.
    */
    class MockUserController extends AppController {
    	var $components = array('Ecache');
    	
    	/** 
    	* This component doesn't handle authentication, nor does it automatically integrate with any other authenticaiton system.
    	* You have to tell it what userid is logged in.  
    	* This shouldn't be too difficult, as most authenticaiton systems will have unique user ids.
    	* If you don't have an authentication system, you probably don't need this component, as all you're cached data will look the same for all people.
    	*/
    	function beforeFilter() {
    		// whatever your authentication sets
    		$this->userid = $this->myAuthenticaitonSystem();
    		
    		// populating value with a number, so we can discuss in this example.
    		$this->userid = 42; // mockup value!
    		
    		// initialize Ecache
    		$this->Ecache->startup($this);
    	}
    	
    	/**
    	* simple ecache example, auto-magic controller/action cache_key.  just a regular database data result cached.
    	* cache filename will be: ./app/tmp/cache/views/mockuser_example0_42.php
    	* if $this->userid was 89, the cache filename would be: ./app/tmp/cache/views/mockuser_example0_89.php
    	*/
    	function example0() {
    		// checking cache
    		$d = $this->Ecache->c();
    		// if missing cached content
    		if (empty($d)) {
    			// now get cached content
    			$cond = array('User.parent_id'=>$this->userid);
    			$results = $this->User->findAll($cond);
    			// writing cache
    			$d = $this->Ecache->c($results);
    		}
    		$this->set('myusers', $d);
    	}
    	
    	/**
    	* simple ecache example.  just a regular database data result cached.
    	* cache filename will be: ./app/tmp/cache/views/users_list_42.php
    	* if $this->userid was 89, the cache filename would be: ./app/tmp/cache/views/users_list_89.php
    	*/
    	function example1() {
    		// checking cache
    		$d = $this->Ecache->c(null,'users','list');
    		// if missing cached content
    		if (empty($d)) {
    			// now get cached content
    			$cond = array('User.parent_id'=>$this->userid);
    			$results = $this->User->findAll($cond);
    			// writing cache
    			$d = $this->Ecache->c($results,'users','list');
    		}
    		$this->set('myusers', $d);
    	}
    
    	/** 
    	* there are 2 differnt cache calls in this example.
    	* the first is like above, based on the $this->userid.
    	* cache filename will be: ./app/tmp/cache/views/users_pageelement_42.php
    	* if $this->userid was 89, the cache filename would be: ./app/tmp/cache/views/users_pageelement_89.php
    	* the second will cache content globally, based on whatever cache_key you tell it (not on a per-user basis)
    	* cache filename will be: ./app/tmp/cache/views/myglobalcachefile.php
    	* notice that the content in this example is generated from returned "requestAction"s
    	*/
    	function example2() {
    		// checking cache
    		$d = $this->Ecache->c(null,'users','pageelement');
    		// if missing cached content
    		if (empty($d)) {
    			// now get cached content
    			$results = $this->requestAction('/something/action',array('return'));
    			// writing cache
    			$d = $this->Ecache->c($results,'users','pageelement');
    		}
    		$this->set('user_specific_content', $d);
    
    		// checking cache
    		$d = $this->Ecache->cf(null,'myglobalcachefile');
    		// if missing cached content
    		if (empty($d)) {
    			// now get cached content
    			$results = $this->requestAction('/anything/content',array('return'));
    			// writing cache
    			$d = $this->Ecache->cf($results,'myglobalcachefile');
    		}
    		$this->set('global_content', $d);
    	}
    	
    	/** 
    	* This is simply a convenience wrapper for clearing the cache files.
    	* clearing cache deletes: ./app/tmp/cache/view/*
    	* ...often you may need to clear from a model, afterSave()
    	*/
    	function exampleClear() {
    		$this->Ecache->clear();
    	}
    }
    ?>

Obviously, the above examples were simple. If you had a huge database
query it might be worth it to cache, or if you used the same content a
lot... most of the time though, caching is really useful for multiple
database operations, heavily processed data, or returned
"requestAction" data.


So Gimmie the code already
==========================
Here's the component... save it to ecache.php in your
./app/controllers/components/ folder. Then using the above controller
examples, you should be able to use it and speed up some of your
expensive tasks.


Component Class:
````````````````

::

    <?php 
    /***
     * PHP versions 4 and 5
     *
     * ecache: extends and simplifies caching content based on per-user or per--anything parameters for the cakePHP framework.
     * Copyright (c)    2008, Alan Blount
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author           Alan Blount
     * @copyright        Copyright (c) 2007, Alan Blount
     * @version          0.2
     * @modifiedby       alan [a4] zeroasterisk [d07] com
     * @license          http://www.opensource.org/licenses/mit-license.php The MIT License
     * @info             http://bakery.cakephp.org/articles/view/487
     *
     * == Info ==
     * caches arrays or strings... good for $this->requestEvent(), and just as good for a Database Query Result Set...
     * 
     * specifically useful when the cached content is different based on user or group or anything else 
     *   which would necessitate a lot of different cache files.
     * 
     * version below includes optional fields: $controller, $action, $id, $userid, $groupid
     * most of those fields inherit from the controller if the input parameters are empty... 
     * if the controller value and the input parameters are both empty, that value isn't part of the cache_key
     * --------
     * Can clear at any point with cake helper function: clearCache();
     */
    /*
    
    
    // == Example Usage (automagic controller/action determination) == 
    $components = array('Ecache');
    function beforeFilter() {
    	$this->userid=42; // set by your authentication scheme...
    	$this->Ecache->startup($this);
    }
    function myAction() { 
    	$d = $this->Ecache->c();
    	if (empty($d)) {
    		$d = $this->Ecache->c($this->User->findAll());
    		// notes: The above would make a seperate cache file for each unique $this->userid value, for each controller/action which called it...
    	}
    }
    
    // == Example Usage (manual controller/action specification) == 
    $components = array('Ecache');
    function beforeFilter() {
    	$this->userid=42; // set by your authentication scheme...
    	$this->Ecache->startup($this);
    }
    function myAction() {
    	$d = $this->Ecache->c(null,'users','list');
    	if (empty($d)) {
    		$d = $this->Ecache->c($this->User->findAll(),'users','list');
    		// notes: The above would make a seperate cache file for each unique $this->userid value...
    	}
    }
    
    // == Example Usage (manual controller/action/id specification) == 
    $components = array('Ecache');
    function myAction($id=0) {
    	$d = $this->Ecache->c(null,'users','list', $id);
    	if (empty($d)) {
    		$d = $this->Ecache->c($this->User->findAll(),'users','list');
    		// notes: The above would make a seperate cache file for each unique $this->userid value...
    	}
    }
    // == Example Usage (manual "cache_key" values, useful for global / non-id-specific content) == 
    $components = array('Ecache');
    function myAction($id=0) {
    	$d = $this->Ecache->cf(null,'global_users_list');
    	if (empty($d)) {
    		$d = $this->Ecache->cf($this->User->findAll(),'global_users_list');
    		// notes: The above would make a single cache file, for all users
    	}
    }
    
    // == Example Clearing of data ==
    $this->Ecache->clear();
    // notes: deletes all "/view/" cache files
    
    */
    class EcacheComponent extends Object {
    	var $duration = '+2 hours';
    	var $lastkey = 'none';
    	var $controller, $params, $id, $userid, $groupid; // may be filled in
        function startup(&$controller) {
            $this->controller = &$controller;
    		if (isset($this->controller->params)) {
    			$this->params = $this->controller->params;
    		}
    		if (isset($this->controller->id)) {
    			$this->id = $this->controller->id;
    		}
    		if (isset($this->controller->userid)) {
    			$this->userid = $this->controller->userid;
    		}
    		if (isset($this->controller->Uid)) {
    			$this->Uid = $this->controller->Uid;
    		}
    		if (isset($this->controller->groupid)) {
    			$this->groupid = $this->controller->groupid;
    		}
        }
    	/***
    	* create the cache-key
    	* @return cache-key value
    	*/
    	function cachekey($data=null, $controller=null, $action=null, $id=null, $userid=null, $groupid=null, $duration=null) {
    		// set values
    		if (empty($controller) && $controller!=0) {
    			if (isset($this->params['controller'])) {
    				$controller = $this->params['controller'];
    			} else {
    				$controller = 'unknown';
    			}
    		}
    		if (empty($action) && $action!=0) {
    			if (isset($this->params['action'])) {
    				$action = $this->params['action'];
    			} else {
    				$action = 'unknown';
    			}
    		}
    		if (empty($id) && $id!=0) {
    			if (isset($this->id)) {
    				$id = intval($this->id);
    			}
    		}
    		if (empty($userid) && $userid!=0) {
    			if (isset($this->userid)) {
    				$userid = intval($this->userid);
    			} elseif (isset($this->Uid)) {
    				$userid = intval($this->userid);
    			}
    		}
    		if (empty($groupid) && $groupid!=0) {
    			if (isset($this->groupid)) {
    				$groupid = $this->groupid;
    			}
    		}
    		if (empty($duration)) {
    			if (isset($this->ecache_duration)) {
    				$duration = $this->ecache_duration;
    			} elseif (isset($this->duration)) {
    				$duration = $this->duration;
    			} else {
    				$duration = '+2 hours';
    			}
    		}
    		// make cache path & filename
    		$path_key = preg_replace('/[^a-zA-Z0-9\_]/','',''.
    			$controller.'_'.$action.
    			(!empty($id) ? '_'.$id : '').
    			(!empty($userid) ? '_'.$userid : '').
    			(!empty($groupid) ? '_'.$groupid : '').
    		'');
    		$this->lastkey = 'views'.DS.$path_key.'.php';
    		return $this->lastkey;
    	}
    	/***
    	* cache content
    	* @return content
    	*/
    	function ecache($data=null, $controller=null, $action=null, $id=null, $userid=null, $groupid=null, $duration=null, $forcekey=null) {
    		if (empty($duration)) {
    			$duration = $this->duration;
    		}
    		if (!empty($forcekey)) {
    			$cachePath = $this->lastkey = 'views'.DS.$forcekey.'.php';
    		} else {
    			$cachePath = $this->cachekey($data, $controller, $action, $id, $userid, $groupid, $duration);
    		}
    		// do cache.... if data=null, retrieve... else, write & return
    		if (is_array($data) || is_object($data)) {
    			$data = serialize($data);
    		}
    		$re = cache($cachePath, $data, $duration);
    		// return data (attempt unserialize)
    		$d = @unserialize($re);
    		if ($d!==false && $d!==null) {
    			return $d;
    		} else {
    			return $re;
    		}
    	}
    	/***
    	* convenience wrapper for "ecache"
    	* @return content
    	*/
    	function c($data=null, $controller=null, $action=null, $id=null, $userid=null, $groupid=null, $duration=null) {
    		return $this->ecache($data, $controller, $action, $id, $userid, $groupid, $duration);
    	}
    	/***
    	* convenience wrapper for "ecache" - force the key
    	* @return content
    	*/
    	function cf($data=null, $forcekey, $duration=null) {
    		return $this->ecache($data,null,null,null,null,null, $duration, $forcekey);
    	}
    	/***
    	* convenience wrapper for "clearCache"
    	* @return bool
    	*/
    	function clear() {
    		return clearCache();
    	}
    }
    ?>



.. author:: zeroasterisk
.. categories:: articles, components
.. tags:: user,peruser,seperate cache,cache files,dependent
cache,Components

