iCache - true query caching
===========================

by %s on August 26, 2008

So, you have an application. That application has a database. It also
has a lot of users. Those users need a LOT of data from that database.
So your application thinks, "Hey... I'm always writing out these big
chunks of data for all these people! Isn't there a better way?" This
article includes a component based on the ecache component that makes
this easy.
This was edited in Microsoft word. As a result the quotes might be all
messed up. Sorry!
So, like we said, you have an application. And that application has a
database. It also has a lot of users. Those users need a LOT of data
from that database. So your application thinks, "Hey... I'm always
writing out these big chunks of data for all these people! Maybe
instead, I should write a master copy of the data and use a copy
machine to distribute the data, and only rewrite things when the data
has changed." Smart application!

Cake already does a lot of this. But it doesn't do much for query
caching. A small example is that I have a submenu which renders a link
to every Genre from my `genres` table. Rather than having it bother
MySQL every time when I personally know my `genres` table is not going
to change much, perhaps we should cache the query!


iCache comes to save the day
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

So, how to we get from point A to point B? Let's first consider the
needs of your application. I have set up the component in advance with
two configurations: Verbose and Lean.

The Verbose Component (just below here) is best for those who want the
component to find out the caching key itself, automagically. This
means it will (in most cases) do its own work to figure out things
such as the ID of the userâ€™s group currently viewing the page to
cache THIS view for THIS user-group. (Integrates best with DarkAuth).

The Lean Component doesnâ€™t require any initial setup (the Verbose
does), but you will have to define the $key variable before each call
to iCache. It is stripped of all automagic key definition and
therefore more efficient. It is also more use-specific and versatile.

So my suggestion is to check â€˜em both out and then pick :)


The Verbose Component
`````````````````````

This one includes a good amount of the possible identifiers you'd use.
Itâ€™s set up to work with an Auth system, specifically DarkAuth, or
another Auth system that utilizes group-controlled access. So you can
specify data being cached by ID (i.e. article ID), user_id (the person
viewing it) or group ID (i.e., Admins have a different amount of data
fetched than Peasants).

*Note: features you donâ€™t use will easily revert to default; so if
you do not implement a certain feature it will be, essentially,
bypassed. You can also snip out any piece you donâ€™t use.


Component Class:
````````````````

::

    <?php 
    /***
     * PHP versions 4 and 5
     *
     * iCache VERBOSE: extends and simplifies caching content based on per-user or per--anything parameters for the cakePHP framework.
     * Copyright (c)    2008, Michael Floering
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author           Alan Blount
     * @copyright        Copyright (c) 2007, Alan Blount
     * @version          0.2
     * @modifiedby       infantigniter [a t ] g m a i l * com
     *					 Michael Floering (mubiplay.com)
     * @license          http://www.opensource.org/licenses/mit-license.php The MIT License
     *
     * == Info ==
     * caches arrays or strings... good for $this->requestEvent(), and just as good for a Database Query Result Set...
     * 
     * version below includes optional fields: $controller, $action, $id, $user_id, $groups
     * most of those fields inherit from the controller if empty... (left the code as simple as possible for easy reconfiguration)
     * --------
     * Can clear at any point with cake helper function: clearCache();
     */
    
    class icacheComponent extends Object {
        var $duration = '+2 days'; //may need to change later on.
        var $controller;
    	var $id;
    	var $user_id;
    	var $groups;
    	var $key;
    	var $duration;
    	
        function startup(&$controller) {
            $this->controller = &$controller;
            if (isset($this->controller->id)) { //This identifies the record, as in /view/46 - user_id, game_id, genre_id...
                //Utilizing ONLY this will result in "viewer-independent" caching - basically, public, but record-specific caching.
    			$this->id = $this->controller->id;
            }
            if (isset($this->controller->current_user['User']['id'])) {
    			//THIS identifies the current user's ID -- IRRELEVANT TO MOST THINGS.
                //"Viewer-dependent" caching.
    			$this->user_id = $this->controller->current_user['User']['id'];
            }
    		$this->groups = ''; //Empty, to be tacked onto; will be for categories of users (by access level).
    		//"Censored" caching.
    		//Probably needs to be tailored to your needs.
            if (isset($this->controller->current_user)) { 
                $this->groups .= 'default'; //In my app, if current_user is set, then they are users and belong to the "default" group.
    										//If current_user wasn't set, they're not logged in, and not part of any group.
    			/*if($this->controller->current_user['Access']['groupA']=="1"){
    				$this->groups .= 'groupA_';
    			}*/
    			//The above line is DarkAuth specific. Uncomment it and replace groupX with any group name, and repeat for each group that is relevant.
            }
        }
        
        function cache($data=null, $controller=null, $action=null, $id=null, $user_id=null, $groups=null, $key=null, $duration=null) {
            // set values
            if (empty($controller)) {
                if (isset($this->params['controller'])) {
                    $controller = $this->controller->params['controller'];
    			} elseif (isset($this->controller->icache['controller'])) {
    				//Don't know why you'd need this but hey it's the verbose version.
    				$controller = $this->controller->icache['controller']; 
                } else {
                    $controller = 'unknown';
                }
            }
            if (empty($action)) {
                if (isset($this->params['action'])) {
                    $action = $this->controller->params['action'];
                } elseif (isset($this->controller->icache['action'])) {
    				$action = $this->controller->icache['action'];
    			} else {
                    $action = 'unknown';
                }
            }
            if (empty($id)) {
                if (isset($this->id)) {
                    $id = intval($this->id);
                } elseif (isset($this->controller->id)) {
                    $id = intval($this->controller->id);
                } elseif (isset($this->controller->icache['id'])) {
    				$id = $this->controller->icache['id'];
    			} else {
                    $id = '0'; //In a view such as /articles/browse, there is no 'ID', so it is not cached ID-specifically.
                }
            }
            if (empty($user_id)) {
                if (isset($this->controller->user_id)) {
                    $user_id = intval($this->controller->user_id);
                } elseif (isset($this->controller->icache['user_id'])){
    				$user_id = $this->controller->icache['user_id'];
    			} else {
                    $user_id = intval($this->user_id);
    					//Note: intval($this->user_id will just yield '0' if not set already. So it's like above.
                }
            }
            if (empty($groups)) {
                if (isset($this->controller->icache['groups'])) {
                    $groups = controller->icache['groups'];
                } else {
                    $groups = '0';
    					//Same as above...
                }
            }
    		if (empty($key)){
    			if(isset($this->controller->icache['key'])) {
    				$key = $this->controller->icache['key'];
    			} else {
    				$key = '0';
    				//To keep up with the format of the above, the default is 0 meaning that this is insignificant.
    			}
    		}
            if (empty($duration)) {
                if (isset($this->controller->icache['duration'])) {
                    $duration = $this->controller->icache['duration'];
                } else {
                    $duration = $this->duration; //Not set in controller, default to config'd duration length set above.
                }
            }
            // Write our indentifying string
            $instance = $controller.'_'.$action.'_'.$id.'_'.$user_id.'_'.$groups.'_'.$key;
            $instance = str_replace(array('&', '~', '!', ',', ';', ':', '*', '__', '__', '__'), '_', $instance);
            //Check cache for data stored with this string. Duration, etc., happens automagically for Cache::read()
    		$read = Cache::read($instance);
    		if($read!==false && $read!==null){ //conditions will be met if data has been cached, and ISN'T 'stale'.
    			//Therefore unserialize (look down to see why) & return data.
    			$read = @unserialize($read);
    			return $read;
    		} else {
    		    if (is_array($data) || is_object($data)) {
                	$serializedData = serialize($data); //Serialize our data, IE if it's a query, to be stored.
           		}
    			if($data!==null || $data !== false){
    				Cache::write($instance, $serializedData, $duration);
    			}
    			return $data; //returns the original unserialized data.
    		}
        }
        
        // convenience wrapper for "clearCache"
        function clear() {
            return Cache::clearCache();
        }
    
        // convenience wrapper for "icache"
        function c($data=null, $controller=null, $action=null, $id=null, $user_id=null, $groups=null, $key=null, $duration=null) {
            return $this->icache($data, $controller, $action, $id, $user_id, $groups, $key, $duration);
        }
    	
    	function delete($key = null){
    	    if (empty($key)) {
    			if(isset($this->controller->icache['key'])) {
    				$key = $this->controller->icache['key'];
    			} else {
    				//This is our backup plan. No key set, check for $id.
    						//(If there's no ID it's probably something like "browse" and needs no key.)
    				if (isset($this->id)) {
    					$key = intval($this->id);
    				} elseif (isset($this->controller->id)) {
    					$key = intval($this->controller->id);
    				} elseif (isset($this->controller->icache['id'])) {
    					$key = $this->controller->icache['id'];
    				} else {
    					$key = '0';
    				}
    			}
            }
    		if(Cache::delete($key)){
    			return true;
    		} else {
    			return false;
    		}
    	}
    	
    	//Note: take a peak at the iCache Lean Component if you want a write() function. it's not here because I didn't want to rewrite it :)
    }
    ?>

Again, you donâ€™t need to trim out any features you donâ€™t use;
however, you might like to. It automatically checks for the
controller, the action, the id (as in /articles/view/54), the user_id
(whoâ€™s viewing it), and their group permissions. The other
parameters you can specify are an additional key if you need it, as
well as how long it is cached for.

Here is an example call using the Verbose version above:

::

    
        function view($id = null) {
            // checking cache
            $id = $this->Article->id;
            $data = $this->icache->cache(null, â€˜articlesâ€™, â€˜viewâ€™, $id, $this->current_user[â€˜Userâ€™][â€˜idâ€™]);
            //You will understand soon why data is null. We supplied some params to the component, but only to save it some time; it COULD find them out automatically.
            /* If Cache::read() doesnâ€™t find a â€œfreshâ€ copy in the cache with the params you supplied, it will return $data you fed it.
            For checking, we feed it $data = null as above.
            So, whatâ€™s going on? To check if there is a valid cache, supply $data  = null. It will return the appropriate cache if a valid one exists; if not, it will return nullâ€¦*/
            if (empty($data)) { //So this line basically translates to: â€œIf(no valid cache was found)â€.
                $results = $this->Article->read(); //Do query.
                $data = $this->icache->cache($results, â€˜articlesâ€™, â€˜viewâ€™, $id, $this->current_user[â€˜Userâ€™][â€˜idâ€™], null, null, â€˜+2 daysâ€™); //Cache query for 2 days.
            }
            $this->set('article',$data);
        }

Simpler than it looks. Just read through the excessively wordy
comments, and youâ€™ll get it. But wait! Thereâ€™s an even simpler way
to do it. Just set the $icache var in your controller. Perhaps you
want your Articles, which donâ€™t change much, to be cached for a
week; but you want Users to be cached for one day.

::

    
    //This would be in the Articles controller:
    var $icache = array(â€˜durationâ€™=>â€™+1 weekâ€™);
    //This would be in the Users controller:
    var $icache = array(â€˜durationâ€™=>â€™+1 dayâ€™);



The Lean Version
````````````````

Sure, some people like Michael Moore. Hereâ€™s the version of the
component for those who like Nicole Richie more:


Component Class:
````````````````

::

    <?php 
    /***
     * PHP versions 4 and 5
     *
    
     * iCache LEAN: extends and simplifies caching content based on per-user or per--anything parameters for the cakePHP framework.
     * Copyright (c)    2008, Michael Floering
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author           Alan Blount
     * @copyright        Copyright (c) 2007, Alan Blount
     * @version          0.2
     * @modifiedby       infantigniter [a t ] g m a i l * com
     *					 Michael Floering (mubiplay.com)
     * @license          http://www.opensource.org/licenses/mit-license.php The MIT License
     *
     * == Info ==
     * caches arrays or strings... good for $this->requestEvent(), and just as good for a Database Query Result Set...
     * 
     * version below includes optional fields: $controller, $action, $id, $user_id, $groups
     * most of those fields inherit from the controller if empty... (left the code as simple as possible for easy reconfiguration)
     * --------
     * Can clear at any point with cake helper function: clearCache();
     */
    
    class icacheComponent extends Object {
        var $duration = '+2 days'; //may need to change later on.
        var $controller;
    	var $id;
    	var $user_id;
    	var $groups;
    	var $key;
    	var $duration;
    	
        function startup(&$controller) {
            $this->controller = &$controller;
        }
        
        function cache($data=null, $controller=null, $action=null, $key=null, $duration=null) {
            // set controller & action, as well as a possible "key" slot.
            if (empty($controller)) {
                if (isset($this->params['controller'])) {
                    $controller = $this->controller->params['controller'];
                } else {
                    $controller = 'unknown';
                }
            }
            if (empty($action)) {
                if (isset($this->params['action'])) {
                    $action = $this->controller->params['action'];
                } elseif (isset($this->controller->icache['action'])) {
    				$action = $this->controller->icache['action'];
    			} else {
                    $action = 'unknown';
                }
            }
            if (empty($key)) {
    			if(isset($this->controller->icache['key'])) {
    				$key = $this->controller->icache['key'];
    			} else {
    				//This is our backup plan. No key set, check for $id.
    						//(If there's no ID it's probably something like "browse" and needs no key.)
    				if (isset($this->id)) {
    					$key = intval($this->id);
    				} elseif (isset($this->controller->id)) {
    					$key = intval($this->controller->id);
    				} elseif (isset($this->controller->icache['id'])) {
    					$key = $this->controller->icache['id'];
    				} else {
    					$key = '0';
    				}
    			}
            }
            if (empty($duration)) {
                if (isset($this->controller->icache['duration'])) {
                    $duration = $this->controller->icache['duration'];
                } else {
                    $duration = $this->duration; //Not set in controller, default to config'd duration length set above.
                }
            }
            // Write our indentifying string
            $instance = $controller.'_'.$action.'_'.$key;
            $instance = str_replace(array('&', '~', '!', ',', ';', ':', '*', '__', '__', '__'), '_', $instance);
            //Check cache for data stored with this string. Duration, etc., happens automagically for Cache::read()
    		$read = Cache::read($instance);
    		if($read!==false && $read!==null){ //conditions will be met if data has been cached, and ISN'T 'stale'.
    			//Therefore unserialize (look down to see why) & return data.
    			$read = @unserialize($read);
    			return $read;
    		} else {
    		    if (is_array($data) || is_object($data)) {
                	$serializedData = serialize($data); //Serialize our data, IE if it's a query, to be stored.
           		}
    			if($data!==null || $data !== false){
    				Cache::write($instance, $serializedData, $duration);
    			}
    			return $data; //returns the original unserialized data.
    		}
        }
        
        // convenience wrapper for "clearCache"
        function clear() {
            return Cache::clearCache();
        }
        
    
        // convenience wrapper for "icache"
        function c($data=null, $controller=null, $action=null, $key=null, $duration=null) {
            return $this->icache($data, $controller, $action, $id, $user_id, $groups, $key, $duration);
        }
    	
    	function delete($key = null){
    	    if (empty($key)) {
    			if(isset($this->controller->icache['key'])) {
    				$key = $this->controller->icache['key'];
    			} else {
    				//This is our backup plan. No key set, check for $id.
    						//(If there's no ID it's probably something like "browse" and needs no key.)
    				if (isset($this->id)) {
    					$key = intval($this->id);
    				} elseif (isset($this->controller->id)) {
    					$key = intval($this->controller->id);
    				} elseif (isset($this->controller->icache['id'])) {
    					$key = $this->controller->icache['id'];
    				} else {
    					$key = '0';
    				}
    			}
            }
    		if(Cache::delete($key)){
    			return true;
    		} else {
    			return false;
    		}
    	}
    	
    	function write($data, $controller=null, $action=null, $key=null, $duration=null){
    		//Same set of statements from above, to identify this cache...
    		if (empty($controller)) {
                if (isset($this->params['controller'])) {
                    $controller = $this->controller->params['controller'];
                } else {
                    $controller = 'unknown';
                }
            }
            if (empty($action)) {
                if (isset($this->params['action'])) {
                    $action = $this->controller->params['action'];
                } elseif (isset($this->controller->icache['action'])) {
    				$action = $this->controller->icache['action'];
    			} else {
                    $action = 'unknown';
                }
            }
            if (empty($key)) {
    			if(isset($this->controller->icache['key'])) {
    				$key = $this->controller->icache['key'];
    			} else {
    				//This is our backup plan. No key set, check for $id.
    						//(If there's no ID it's probably something like "browse" and needs no key.)
    				if (isset($this->id)) {
    					$key = intval($this->id);
    				} elseif (isset($this->controller->id)) {
    					$key = intval($this->controller->id);
    				} elseif (isset($this->controller->icache['id'])) {
    					$key = $this->controller->icache['id'];
    				} else {
    					$key = '0';
    				}
    			}
            }
            if (empty($duration)) {
                if (isset($this->controller->icache['duration'])) {
                    $duration = $this->controller->icache['duration'];
                } else {
                    $duration = $this->duration; //Not set in controller, default to config'd duration length set above.
                }
            }
            // Write our indentifying string
            $instance = $controller.'_'.$action.'_'.$key;
            $instance = str_replace(array('&', '~', '!', ',', ';', ':', '*', '__', '__', '__'), '_', $instance);
    
    		//Similar write function to above.
    		if($data!==null || $data !== false){
    			if (is_array($data) || is_object($data)) {
                	$data = serialize($data); //Serialize our data, IE if it's a query, to be stored.
           		}
    			Cache::write($instance, $data, $duration);
    			return true;
    		} else {
    			return false; //How can we save null data? return false.
    		}
    	}
    
    } ?>

Now this is the one I like. It doesnâ€™t have anything too automagic,
but because of that it is maximally efficient. As long as you set a
key appropriately, it works out great.

Hereâ€™s an example of a call using the lean version (and the
convenience wrapper, c() ):

::

    
        function view($id = null) {
            // checking cache
            $id = $this->Article->id;
            //For the sake of demonstration, letâ€™s throw in some extra parameters. Letâ€™s say that for this query, not only does the Article ID matter, but it also uses a random number for the query (bear with me here). And while weâ€™re at it weâ€™ll also act like the Userâ€™s id matters. Hereâ€™s how weâ€™d make it cache accordingly:
            $quanta = rand();
            $user_id = $this->YourAuthComponent->user[â€˜idâ€™];
            $key = $id.â€™-â€˜.$quanta.â€™-â€˜.$user_id; //This will always be specific to the case, while lacking any extra trimmings.
            $data = $this->icache->cache(null, â€˜articlesâ€™, â€˜viewâ€™, $key);
            if (empty($data)) { //So this line basically translates to: â€œIf(no valid cache was found)â€.
                $results = $this->Article->read(); //Do query.
                $data = $this->icache->c($results, â€˜articlesâ€™, â€˜viewâ€™, $key, â€˜+2 daysâ€™); //Cache query for 2 days.
            }
            $this->set('article',$data);
        }
    

Hereâ€™s another idea. Setup the icache var in your controller. In
this example we will add to it on the fly. Then we will use a very
simple call to cache the query:

::

    
    	function browse($genre_id = null){
    		if(isset($genre_id)){
    			$this->icache[â€˜keyâ€™] = â€˜gen-â€˜.$genre_id;
    			$cond = array(â€˜genre_idâ€™=>$genre_id, â€˜publishedâ€™=>â€™1â€™);
    		} else {
    			$this->icache[â€˜keyâ€™]=â€™allâ€™;
    			$cond = array(â€˜publishedâ€™=>â€™1â€™);
    		}
    		$data = $this->icache->cache(â€˜keyâ€™ = $key);
    		if (empty($data)) {
                		$results = $this->Article->find(â€˜allâ€™, array(â€˜conditionsâ€™=>$cond));
               			$data = $this->icache->c($results, â€˜keyâ€™ = $key);
           		 }
          			$this->set('articles',$data);
    	}

If you are to use the lean version, remember this: in cases where you
might vary what exactly specifies the key (as in a conditional
statement where one outcome sets the key to the value of $a, and
another sets the key to the value of $b), remember to set them up in a
unique way. See below:

::

    
    //This causes problems when $a = $b and should be avoided
    if(something){
    	$key = value from scenario a;
    } else {
    	$key = value from scenario b;
    } //Where $a = $b, we will end up getting the wrong data!
    
    //This prevents such problems:
    if(something){
    	$a = value from scenario a;
    	$b = â€˜0â€™;
    } else {
    	$b = value from scenario b;
    	$a = â€˜0â€™;
    }
    $key = $a.â€™_â€™.$b;



Ensuring data freshness
~~~~~~~~~~~~~~~~~~~~~~~
Note: I will be writing all following examples based on the Lean
version. It will not be hard to adapt them for use with the iCache
Verbose Component, should you need to.
I am not good with behaviors. So, I have written a snippet you call in
your controllerâ€™s save() actions, and so forth. It is not an
incredible method, and I hope there is a better idea out there (please
post if you have one). But here goes nothing:

::

    
    //For a CONTROLLER ACTION save(), using the iCache Lean Componentâ€¦
    function save() {
    	if($this->Articles->save()){
    		$key = â€˜The key you use in whichever save function.â€™;
    		$this->icache->delete($key);
    	}
    }

The above snippet will work for functions such as browse(), but even
that will not always work. In any event, I will explain a more complex
situation that this functionality could be used in.

Letâ€™s say you have a function browse(), in your ArticlesController,
which could be filtered by any one Genre. So whenever we call
ArticlesController->save(), we will want to update the queries for
each Genre-specific browse() call. This is how weâ€™d do that:

::

    
    //Make sure your ArticlesController has `Genre` in its $uses array before using this!
    
    function save() {
    	if($this->Articles->save()){
    		$genres = $this->Genres->find(â€˜allâ€™);
    		//I donâ€™t know how to make this query cacheable, weâ€™d need a findAll action called in Genres which would not be a normal occurrence.
    		foreach ($genres as $genre){
    			$instance = â€˜genresâ€™.â€™_â€™.â€™browseâ€™.â€™_â€™.â€˜gen-â€˜.genre[â€˜Genreâ€™][â€˜idâ€™];
    			//Recall how our iCache Lean component is called in our earlier-defined browse function. $key is set to â€˜gen-â€˜.$genre[â€˜Genreâ€™][â€˜idâ€™]. So then our Lean Component names the file with the parameters CONTROLLER.ACTION.$key; we are simply mimicking this snippet.
    			$this->icache->delete($instance);
    		}
    }
    }



Thatâ€™s all folks
~~~~~~~~~~~~~~~~~~
I have some serious Plato to be reading now (homework). I am new to
CakePHP and actually only about 8 months into any sort of PHP
programming. This is also my first contribution to the Cake community,
which I feel I must take part in as an act of gratitude if nothing
else! So please, donâ€™t hold back any criticisms or suggestions.
Thank you all very much, and I hope it helps you out!


Credits
```````
`http://bakery.cakephp.org/articles/view/ecache-easy-per-user-or-per-
anything-cache-of-html-or-arrays`_ (the original component and idea)


Highly recommended Cache reading
````````````````````````````````
`http://bakery.cakephp.org/articles/view/optimizing-your-cakephp-
elements-and-views-with-caching`_



.. _http://bakery.cakephp.org/articles/view/optimizing-your-cakephp-elements-and-views-with-caching: http://bakery.cakephp.org/articles/view/optimizing-your-cakephp-elements-and-views-with-caching
.. _http://bakery.cakephp.org/articles/view/ecache-easy-per-user-or-per-anything-cache-of-html-or-arrays: http://bakery.cakephp.org/articles/view/ecache-easy-per-user-or-per-anything-cache-of-html-or-arrays
.. meta::
    :title: iCache - true query caching
    :description: CakePHP Article related to caching,query economy,mysql cache,query caching,dependent cache,censored cache,Components
    :keywords: caching,query economy,mysql cache,query caching,dependent cache,censored cache,Components
    :copyright: Copyright 2008 
    :category: components

