Vimeo Datasource
================

Vimeo is the most awesomest video site and filmmaker community on the
internets. Utilizing Vimeo data and content with your favorite CakePHP
Framework based web application just got a little easier...
Note that this particular datasource leverages the Vimeo Simple API as
well as the Vimeo oEmbed API. A datasource utilizing the Vimeo
Advanced API is on the way. See `http://vimeo.com/api`_ for details.

Installation
~~~~~~~~~~~~

+ Place vimeo_source.php in your app/models/datasources folder
+ Add the following (or similar) declarative in your database.php file


::

    <?php
    	var $vimeo = array('datasource' => 'vimeo');
    ?>



Usage Example
~~~~~~~~~~~~~
Letâ€™s assume you have a video model with a data field of vimeo_id

Controller Class:
`````````````````

::

    <?php 
    class VideoController extends AppController {
    	function view($id) {
    	$this->Video->id = $id;
    	$video = $this->Video->read();
    
    // Letâ€™s grab some Vimeo Video data
    $this->Vimeo = ConnectionManager::getDatasource(â€˜vimeoâ€™);
    	$vimeo = $this->Vimeo->video($video[â€˜Videoâ€™][â€˜vimeo_idâ€™]);
    	debug($vimeo);
    
    // To get only embed code:
    $vimeoEmbed = $this->Vimeo->embed($video[â€˜Videoâ€™][â€˜vimeo_idâ€™]);
    Debug($vimeoEmbed);
    }
    }
    ?>

There are a ton of other functions in there if you look through the
code and look through the Vimeo Simple API documentation. And, thar
she blows:


DataSource Class:
`````````````````

::

    <?php 
    /** 
    * Vimeo Datasource 0.1 
    * 
    * Vimeo datasource to communicate with the Vimeo Simple API (Advanced on the way...) 
    * Also utilizes the Vimeo oEmbed API for generating embed code.
    * 
    * Licensed under The MIT License 
    * Redistributions of files must retain the above copyright notice. 
    * 
    * 
    * @author Jon (pointlessjon) Adams <jon@anti-gen.com> 
    * @copyright (c) n/a
    * @link http://github.com/pointlessjon/CakePHP-Vimeo-Datasource/tree/master
    * @license http://www.opensource.org/licenses/mit-license.php The MIT License 
    * @created May 7, 2009 
    * @version 0.1 * 
    */
    App::import('Core', array('HttpSocket'));
     
    class VimeoSource extends DataSource {
    
    	var $description = 'Vimeo Simple API';
    	var $Http = null;
    	var $allowedRequests = array(
    		'user' => array(
    			'info',
    			'clips',
    			'likes',
    			'appears_in',
    			'all_clips',
    			'subscriptions',
    			'albums',
    			'channels',
    			'groups',
    			'contacts_clips',
    			'contacts_like'
    		),
    		'activity' => array(
    			'user_did',
    			'happened_to_user',
    			'contacts_did',
    			'happened_to_contacts',
    			'everyone_did'
    		),
    		'group' => array(
    			'clips',
    			'users',
    			'info'
    		),
    		'channel' => array(
    			'clips',
    			'info'
    		),
    		'album' => array(
    			'clips',
    			'info'
    		)
    	);
    	
    	/** 
    	* Constructor sets configuration and instantiates HttpSocket
    	* 
    	* @param array config Optional. 
    	* @see http://www.vimeo.com/api/docs/simple-api
    	*/ 
    	function __construct($config = null) {
    		parent::__construct($config);
    		$this->Http =& new HttpSocket();
    	}
    	
    	/** 
    	* Shortcut to retrieve only the embed code of the oembed object for a specific video.
    	* 
    	* @param string videoId Required.
    	* @param array options Optional. 
    	* @see http://www.vimeo.com/api/docs/oembed
    	*/ 
    	function embed($videoId = null, $options = null) {
    		if (!empty($videoId)) {
    			$_oembed = $this->oembed($videoId, $options);
    			return $_oembed->html;
    		}
    		return false;
    	}
    	
    	
    	/** 
    	* Retrieve oembed object for a specific video
    	* 
    	* @param string videoId Required.
    	* @param array options Optional. 
    	* @see http://www.vimeo.com/api/docs/oembed
    	*/ 
    	function oembed($videoId = null, $options = null) {
    		if (!empty($videoId)) {
    			$url = "http://vimeo.com/api/oembed.json?url=http://vimeo.com/{$videoId}";
    			foreach ($options as $key => $value) {
    				$url .= "&{$key}={$value}";
    			}
    			$response = $this->Http->get($url);
    			return json_decode($response);
    		}
    		return false;
    	}
    	
    	/** 
    	* Retrieve data about a specific video
    	* 
    	* @param string videoId Required.
    	* @see http://www.vimeo.com/api/docs/simple-api
    	*/ 
    	function video($videoId = null) {
    		if (!empty($videoId)) {
    			return $this->__vimeoApiRequest("clip/{$videoId}");
    		}
    		return false;
    	}
    	
    	/** 
    	* Retrieve data for a specific user
    	* 
    	* @param string username Required.
    	* @param string request Required. See allowed requests in api documentation
    	* @see http://www.vimeo.com/api/docs/simple-api
    	*/ 
    	function userRequest($username = null, $request = null) {
    		if (!empty($username) && !empty($request)) {
    			if (in_array($request, $this->allowedRequests['user'])) {
    				return $this->__vimeoApiRequest("{$username}/{$request}");
    			}
    		}
    		return false;
    	}
    	
    	/** 
    	* Retrieve activity data for a specific user
    	* 
    	* @param string username Required.
    	* @param string request Required. See allowed requests in api documentation
    	* @see http://www.vimeo.com/api/docs/simple-api
    	*/ 
    	function activityRequest($username = null, $request = null) {
    		if (!empty($username) && !empty($request)) {
    			if (in_array($request, $this->allowedRequests['activity'])) {
    				return $this->__vimeoApiRequest("activity/{$username}/{$request}");
    			}
    		}
    		return false;
    	}
    	
    	/** 
    	* Retrieve data for a specific group
    	* 
    	* @param string groupname Required.
    	* @param string request Required. See allowed requests in api documentation
    	* @see http://www.vimeo.com/api/docs/simple-api
    	*/ 
    	function groupRequest($groupname = null, $request = null) {
    		if (!empty($groupname) && !empty($request)) {
    			if (in_array($request, $this->allowedRequests['group'])) {
    				return $this->__vimeoApiRequest("group/{$groupname}/{$request}");
    			}
    		}
    		return false;
    	}
    	
    	/** 
    	* Retrieve data for a specific channel
    	* 
    	* @param string channelname Required.
    	* @param string request Required. See allowed requests in api documentation
    	* @see http://www.vimeo.com/api/docs/simple-api
    	*/ 
    	function channelRequest($channelname = null, $request = null) {
    		if (!empty($channelname) && !empty($request)) {
    			if (in_array($request, $this->allowedRequests['channel'])) {
    				return $this->__vimeoApiRequest("channel/{$channelname}/{$request}");
    			}
    		}
    		return false;
    	}
    	
    	/** 
    	* Retrieve data for a specific album
    	* 
    	* @param string albumname Required.
    	* @param string request Required. See allowed requests in api documentation
    	* @see http://www.vimeo.com/api/docs/simple-api
    	*/ 
    	function albumRequest($albumname = null, $request = null) {
    		if (!empty($albumname) && !empty($request)) {
    			if (in_array($request, $this->allowedRequests['album'])) {
    				return $this->__vimeoApiRequest("album/{$albumname}/{$request}");
    			}
    		}
    		return false;
    	}
    	
    	/** 
    	* Internal function to make the requests to the Vimeo Simple API
    	* 
    	* @param string data Required.
    	* @see http://www.vimeo.com/api/docs/simple-api
    	*/ 
    	function __vimeoApiRequest($data = null) {
    		if (!empty($data)) {
    			return unserialize($this->Http->get("http://vimeo.com/api/{$data}.php", null));
    		}
    		return false;
    	}
     
    }
    ?>



.. _http://vimeo.com/api: http://vimeo.com/api

.. author:: pointlessjon
.. categories:: articles, models
.. tags:: video,datasource,vimeo,oembed,Models

