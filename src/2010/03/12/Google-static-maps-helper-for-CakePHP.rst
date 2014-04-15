Google static maps helper for CakePHP
=====================================

by kmendes on March 12, 2010

This is a simple CakePHP helper to create static maps using google
API. For more information on the api and how to get an api key consult
the Google Static Maps API.


1. Add helper file to your app
``````````````````````````````

Download the file google_static_map.php (`http://karlmendes.com/static
/google_static_map/google_static_map.zip`_) and add it to the folder
â€œYOUR_APP/views/helperâ€.

2. Add your google api key to the core.php
``````````````````````````````````````````

Add the following line to core.php to define the google api key:

::

    <?php
    Configure::write('GoogleMapsAPIKey', '---KEY---'); 
    ?>


3. Reference it on your controller
``````````````````````````````````

Refer the helper on the controller together with the other helpers you
may be using.

::

    <?php 
    var $helpers = array('Html','Form','Javascript','GoogleStaticMap');
    ?>


4. Use it in your view
``````````````````````

To use ti just call the function map using the desired parameters,
this is a simple example:

::

    <?php
    $params = array(
    	'markers'=>array(array(-4,40),array(-20,80)),
    	'size'=>array(400,300),
    	'zoom'=>12
    	);
    echo $googleStaticMap->map($params);
    ?>

For those willing to take a quick look at the helper code go to the
next page, if you got what you want already stop right here.


::

    
    <?php
    /*
    * GoolgleStaticMapHelper by Karl Mendes
    *
    * http://karlmendes.com
    *
    */
    class GoogleStaticMapHelper extends AppHelper {
    
    	var $helpers = array('Html');
    
    	/*
    	* Insert google maps API key here or insert a new line on
    	* config/core.php Configure::write('GoogleMapsAPIKey', '---KEY---');
    	*/
    	var $key = null;
    
    	var $staticUrl = 'http://maps.google.com/maps/api/staticmap';
    
    	function __construct() {
    		if($this->key == null)
    			$this->key = Configure::read('GoogleMapsAPIKey');
    	}
    
    	/*
    	*
    	* More info: http://code.google.com/apis/maps/documentation/staticmaps/
    	*
    	* @params:
    	*	$parameters [array]
    	*		center: {required if markers not present} [array(lat,lon),string address]
    	*		zoom: {required if markers not present} [int]
    	*		size: {required} [array(width,height)]
    	*		format: {optional} [png8,png,png32,gif,jpg,jpg-baseling]
    	*		maptype: {optional} [roadmap, satellite, hybrid, terrain]
    	*		mobile: {optional} [true,false]
    	*		language: {optional} [string]
    	*		markers: {optional} [array(array(lat,lon),
    	*							string markerStyles|markerLocation1|markerLocation2|...)]
    	*		path: {optional} [array(array(lat,lon),string pathStyles|pathLocation1|pathLocation2|...)]
    	*		visible: {optional} [array(array(lat,lon),string)]
    	*		sensor: {optional} [true,false] (default will be false)
    	*	$alt: {optional} alt for the tag img
    	*	$title: {optional} title for the tag img
    	*	$createImgTag: {optional} defines if the function returns the img tag or only the url
    	*
    	* @return: The img tag or url for the static map
    	*
    	*/
    	if(isset($parameters['size']) &&
    			(isset($parameters['center'],$parameters['zoom']) ||
    			isset($parameters['markers']))){
    
    			if(!isset($parameters['sensor'])) $parameters['sensor'] = 'false';
    
    			$url = $this->staticUrl . '?key=' . $this->key;
    
    			$url .= '&sensor=' . $parameters['sensor'];
    
    			$url .= '&size=' . $parameters['size'][0] . 'x' . $parameters['size'][1];
    
    			if(isset($parameters['markers'])){
    				foreach($parameters['markers'] as $marker){
    					if(is_array($marker))
    						$url .= '&markers=' . $marker[0] . ',' . $marker[1];
    					else
    						$url .= '&markers=' .  $marker;
    				};
    			};
    
    			if(isset($parameters['path'])){
    				foreach($parameters['path'] as $path){
    					if(is_array($path))
    						$url .= '&path=' . $path[0] . ',' . $path[1];
    					else
    						$url .= '&path=' .  $path;
    				};
    			};
    
    			if(isset($parameters['visible'])){
    				foreach($parameters['visible'] as $visible){
    					if(is_array($visible))
    						$url .= '&visible=' . $visible[0] . ',' . $visible[1];
    					else
    						$url .= '&visible=' .  $visible;
    				};
    			};
    
    			if(isset($parameters['center'])){
    				$url .= '&center=';
    				if(is_array($parameters['center']))
    					$url .= $parameters['center'][0] . ',' . $parameters['center'][1];
    				else
    					$url .= $parameters['center'];
    			};
    
    			if(isset($parameters['format'])){
    				$url .= '&format=' . $parameters['format'];
    			};
    
    			if(isset($parameters['zoom'])){
    				$url .= '&zoom=' . $parameters['zoom'];
    			};
    
    			if($creatImgTag)
    				return $this->output('<img width="'.$parameters['size'][0].'" height="'.$parameters['size'][1].'" src="'.$url.'" alt="'.$alt.'" title="'.$title.'" />');
    			else return $this->output($url);
    
    		}else{
    			$this->log('GoogleStaticMapHelper: Invalid parameters for function "map"');
    		};
    	}
    
    };
    ?>

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _Page 2: :///articles/view/4caea0e7-c760-4b0f-ba7c-4fe182f0cb67/lang:eng#page-2
.. _Page 1: :///articles/view/4caea0e7-c760-4b0f-ba7c-4fe182f0cb67/lang:eng#page-1
.. _http://karlmendes.com/static/google_static_map/google_static_map.zip: http://karlmendes.com/static/google_static_map/google_static_map.zip

.. author:: kmendes
.. categories:: articles, helpers
.. tags:: helper,google maps,google static api,Helpers

