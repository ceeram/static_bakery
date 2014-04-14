Webthumb - helping you to take screenshots on the easy
======================================================

by zeroasterisk on December 29, 2008

Webthumb is a great service providing free and low cost screenshots
with a decent API. So lets say that as part of your site, you want to
take screenshots of public webpages. Either you manage or track other
sites, or you just want to take screenshots of links people post.
Whatever your need, you can get screenshots with ease, by using this
component to access the Webthumb service.
Webthumb `http://webthumb.bluga.net/`_
Bluga.net WebThumb provides a white-label web service API for
generating web thumbnails and full size snapshots of websites.

WebThumb offers more features and quicker response times then any
other service.

+ Real-time thumbnails
+ Flash 9 Support
+ PDF Support
+ Quick response times
+ REST API
+ API clients for PHP, Ruby, Python
+ Cache the thumbnails on your server or Webthumbs
+ Browser windows from 75x75 to 1280x2048

This is a component which just scratches the surface does the most
basic task of getting a screenshot of a URL and saving it locally.


Component Code
~~~~~~~~~~~~~~

Save this to a file: ./app/controllers/components/webthumb.php


Component Class:
````````````````

::

    <?php 
    /**
    * This is a simple component to retrieve screenshots from the webthumb's easythumb API
    * http://webthumb.bluga.net/api-easythumb
    *
    * The configuration is quite simple, and though the functions are broken up for individual access, 
    * the only way I ever use this is to use the getAndSave() method
    * which requests the image from webthumb and saves it to a file you specify.
    *
    * @requires cURL // If you are using debian/ubuntu, type in: # sudo apt-get install php5-curl
    * @author alan@zeroasterisk.com
    * @version 1.0
    * tested with cake 1.1.20.7692
    */
    class WebthumbComponent extends Object {
        var $controller = false;
    	var $easythumb_url = 'http://webthumb.bluga.net/easythumb.php';
    	var $user_id = '1234';
    	var $api_key = 'replace0this0with0your0key000000';
    	var $default_size = 'medium2';
    	var $default_cache = '-1';
    	/**
    	* basic startup function, triggered upon initialization by Cake
    	* @param object $controller
    	* @return bool
    	*/
    	function startup(&$controller) {
            $this->controller = & $controller;
    		return true;
        }
    	/**
    	* executes a request from webthumbs, and saves the resulting data as a file of some sort.
    	* @param string $saveToFile full filename to save as (eg: /var/www/filepath/filename.jpg)
    	* @param string $urlToThumbnail Site to thumbnail, full URL including protocol (eg: http://google.com)
    	* @param string $size [null] Size of the thumbnail to return small, medium1, medium2, large
    	* @param string $cache [null] The # of days old a cached version of the thumbnail can be -1 to 30
    	* @return bool
    	*/
    	function getAndSave($saveToFile,$urlToThumbnail,$size=null,$cache=null) {
    		$url = $this->makeEasythumbURL($urlToThumbnail,$size,$cache);
    		$data = $this->curl_get($url);
    		if (!class_exists('File')) { uses('file'); }
    		$file = new File($saveToFile, true);
    		return $file->write($data,'w');
    	}
    	/**
    	* executes a request from webthumbs
    	* @param string $urlToThumbnail Site to thumbnail, full URL including protocol (eg: http://google.com)
    	* @param string $size [null] Size of the thumbnail to return small, medium1, medium2, large
    	* @param string $cache [null] The # of days old a cached version of the thumbnail can be -1 to 30
    	* @return string binary file data
    	*/
    	function get($urlToThumbnail,$size=null,$cache=null) {
    		$url = $this->makeEasythumbURL($urlToThumbnail,$size,$cache);
    		return $this->curl_get($url);
    	}
    	/**
    	* creates the appropriate URL format to request from webthumbs
    	* @param string $urlToThumbnail Site to thumbnail, full URL including protocol (eg: http://google.com)
    	* @param string $size [null] Size of the thumbnail to return small, medium1, medium2, large
    	* @param string $cache [null] The # of days old a cached version of the thumbnail can be -1 to 30
    	* @return string $url
    	*/
    	function makeEasythumbURL($urlToThumbnail,$size=null,$cache=null) {
    		$unEncodedUrlToThumbnail = $urlToThumbnail;
    		$urlToThumbnail = urlencode($urlToThumbnail);
    		$size = urlencode(ife(empty($size),$this->default_size,$size));
    		$cache = urlencode(ife(empty($cache),$this->default_cache,$cache));
    		$hash = md5(date('Ymd').$unEncodedUrlToThumbnail.$this->api_key);
    		return "{$this->easythumb_url}?user={$this->user_id}&url={$urlToThumbnail}&size={$size}&cache={$cache}&hash={$hash}";
    	}
    	/**
    	* cURL get the requested URL (and optional POST data)
    	* @param string $url
    	* @return string $curlResult
    	*/
    	function curl_get($url) {
    		if (!function_exists('curl_init')) {
    			die('Sorry - you need CURL and php5-curl (CURL module for php5).. If you are using debian/ubuntu, type in: # sudo apt-get install php5-curl');
    		}
    		$ch = curl_init();
    		curl_setopt($ch, CURLOPT_URL, $url);
    		curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
    		//curl_setopt ($ch, CURLOPT_HEADER, 0);
    		curl_setopt ($ch, CURLOPT_DNS_CACHE_TIMEOUT, 480); // 0 = forever, 5 = 5 seconds
    		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 480); // 0 = forever, 5 = 5 seconds
    		curl_setopt ($ch, CURLOPT_TIMEOUT, 480); // 0 = forever, 5 = 5 seconds
    		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    		$curlResult = trim(curl_exec($ch));
    		curl_close ($ch);
    		return $curlResult;
    	}
    }
    ?>



Example Extension to this Component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're going to be using this a lot on different controllers, I
would probably extend this with some custom functions... You could
simply add something like the following to the component:


Component Class:
````````````````

::

    <?php 
    	/**
    	* Helper Method: executes a request from webthumbs, and saves the resulting data as a file of some sort.
    	* @param string $siteData full filename to save as (eg: /var/www/filepath/filename.jpg)
    	* @param string $urlToThumbnail Site to thumbnail, full URL including protocol (eg: http://google.com)
    	* @param string $size [null] Size of the thumbnail to return small, medium1, medium2, large
    	* @param string $cache [null] The # of days old a cached version of the thumbnail can be -1 to 30
    	* @return bool
    	*/
    	function getSiteScreenshot($siteData,$size=null,$cache=null) {
    		$assets_folder = $this->controller->Site->get_assets_folder($siteData);
    		$saveToFile = $assets_folder.DS.'screenshot.jpg';
    		$urlToThumbnail = "http://{$siteData['Site']['domain']}";
    		if ($this->getAndSave($saveToFile,$urlToThumbnail,$size,$cache)) {
    			$wwwUrlToFile = $this->controller->Site->get_www_path($saveToFile);
    			return $this->controller->Site->save(array(
    					'id' => $siteData['Site']['id'],
    					'image_url' => $wwwUrlToFile,
    				));
    		}
    		return false;
    	}
    ?>



Example Usage in a Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Of course, you can also use it in your controller like you might
think.


Controller Class:
`````````````````

::

    <?php 
    	function get_screenshot() {
    		$MyURL = 'http://bakery.cakephp.org/';
    		$SaveFileAs = WWW_ROOT.DS.'/screenshot.jpg';
    		if ($this->Webthumb->getAndSave($$SaveFileAs,$MyURL)) {
    			$this->set('screenshot','screenshot.jpg');
    		}
    	}
    ?>




.. _http://webthumb.bluga.net/: http://webthumb.bluga.net/
.. meta::
    :title: Webthumb - helping you to take screenshots on the easy
    :description: CakePHP Article related to api,curl,webthumb,screenshot,Components
    :keywords: api,curl,webthumb,screenshot,Components
    :copyright: Copyright 2008 zeroasterisk
    :category: components

