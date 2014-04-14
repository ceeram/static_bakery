Google Map Helper
=================

by gigapromoters on December 28, 2006

A handy tool to generate maps using Google Map API.
I found CakePHP to be helpful with a lot of things and I loved the
fact that anyone can reuse any classes made in PHP using 'vendors' in
CakePHP. We recently wrote a CakePHP helper for generating map in
CakePHP application.

The helper uses Google Maps API V2 class
(`http://www.phpinsider.com/php/code/GoogleMapAPI/`_) from
phpinsider.com.

To begin with, just download the class and place them inside 'vendors'
folder.

Here's the code for the helper: map.php

::

    
    <?php
    class MapHelper extends Helper
    {
    
        var $helpers = array('Html');
    
        function displaymap($locations=false,$width=500,$height=500)
        {
    		vendor('GoogleMapAPI.class');
    		$map = new GoogleMapAPI('map');
    		if($locations)
    		foreach($locations as $location)
    		{
    			$map->addMarkerByAddress( $location['address'],strip_tags($location['title']), $location['title']);  //adds address to showup in Map
    		}
    		else
    		{
    			$map->setCenterCoords(-96.67,40.8279);   // if no locations are passed in function, then focus on US
    			$map->setZoomLevel(3);
    		}
    
    		$map->setWidth($width);
    		$map->setHeight($height);
    		$map_content=$map->getHeaderJS().$map->getMapJS().$map->getMap();
    		return $this->output($map_content);
        }
    }
    ?>

Here's the code for the view (index.thtml in my case): index.thtml

::

    
    <? php
    // initialization of $my_locations array to show in map - you can do this in your controller.
    $my_locations=array();
    $my_locations[1]['address']='621 N 48th St # 6 Lincoln NE 68502';
    $my_locations[1]['title']='PJ Pizza';
    
    $my_locations[2]['address']='826 P St Lincoln NE 68502';
    $my_locations[2]['title']='<b>PJ Pizza</b>';
    
    echo $map->displaymap($my_locations,500,500); ?>
    <script type="text/javascript">onLoad();</script>

Author: Abhimanyu Grover
`Giga Promoters`_

.. _http://www.phpinsider.com/php/code/GoogleMapAPI/: http://www.phpinsider.com/php/code/GoogleMapAPI/
.. _Giga Promoters: http://www.gigapromoters.com/
.. meta::
    :title: Google Map Helper
    :description: CakePHP Article related to ,Helpers
    :keywords: ,Helpers
    :copyright: Copyright 2006 gigapromoters
    :category: helpers

