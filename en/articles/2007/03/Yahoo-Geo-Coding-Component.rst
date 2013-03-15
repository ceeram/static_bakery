Yahoo Geo Coding Component
==========================

by %s on March 16, 2007

I've been looking around the Bakery, and I noticed that there was at
least one Google Geo Coding Component, and no Yahoo equivilants. I
can't speak as to what the difference is, but I wrote a Yahoo Geo
Coding component, which is very simple to use. All you do is plug it
in, and start baking.
There are only 4 steps to integrating this Geo Coder into your
application.

1. You need to get a Yahoo Application ID from yahoo.com
`http://developer.yahoo.com/maps/rest/V1/geocode.html`_ should point
you in the write direction).

2. You need to put this file in your app/controllers/components/
directory. (yahoo_geo_coder.php).


Component Class:
````````````````

::

    <?php 
    /*
    This php class allows an address to be converted into Geographic Coordinates, 
    Latitude and Longitude through the use of Yahoo's Geocoding Service. This code
    is in no way related nor affiliated with Yahoo.  
    Copyright (C) 2007 James Rubenstein
    
    This software is licensed under the MIT License:
    
    Permission is hereby granted, free of charge, to any person
    obtaining a copy of this software and associated documentation
    files (the "Software"), to deal in the Software without
    restriction, including without limitation the rights to use,
    copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following
    conditions:
    
    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.
    
    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
    OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
    HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
    WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
    OTHER DEALINGS IN THE SOFTWARE.
    
    Requirements:
    
    1. A Yahoo Application Id
            http://developer.yahoo.com/maps/rest/V1/geocode.html
    
    Usage:
    
    In your controller:
    var $components = array ('YahooGeoCoder');
    
    $address = array(   'street'=>'1600 Pennsylvania Ave NW', 
                        'city'=>'Washington', 
                        'state'=>'DC', 
                        'zip'=>'20500');
    					
    $longlat = $this->YahooGeoCoder->getPosition($address);
    
    OR 
    
    $longlat = $this->YahooGeoCoder->getPosition('1600 pennsylvania Ave NW, Washington, DC 20500');
    
    OR
    
    $longlat = $this->YahooGeoCoder->getPosition('Washington, DC 20500');
    
    OR
    
    $longlat = $this->YahooGeoCoder->getPosition('DC 20500');
    
    OR 
    
    $longlat = $this->YahooGeoCoder->getPosition('DC');
    
    OR
    
    $longlat = $this->YahooGeoCoder->getPosition('20500');
    
    
    */ 
    class YahooGeoTrackerComponent extends Object
    {
    
    	/**
    	 * Array holding latitude & longitude pair (returns false by default)
    	 *
    	 * @var array
    	 * @access private
    	 */
    	var $coords = array ('lat' => false, 'long' => false);
    	
    	/**
    	 * String holding the query string to query Yahoo
    	 *
    	 * @var string
    	 * @access private
    	 */
    	var $queryString = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=';
    	
    	/**
    	 * String holding your application Id
    	 *
    	 * @var string
    	 * @access private
    	 */
    	var $appId = '#YourAppId#';
    	
    	/**
     	 * XML Parser Array
    	 *
    	 * @var array
    	 * @access private
    	 */
    	var $xmlParser = array('currentTag' => '');
    	
    	function startup(&$controller)
    	{
    		$this->queryString .= $this->appId;
    	}
    	
    	
    	/**
    	 * Function getPosition
    	 *
    	 * This method takes an address and queries yahoo's servers in order to generate
    	 * a longitude and latitude for the address.
    	 *
    	 * The more information you provide, the more exact the address (obviously)
    	 * Pass an array with 'street', 'city', 'state', and 'zip' keys.
    	 *
    	 * @author Jim Rubenstein <jrubenstein (at) gmail (dot) com>
    	 * @param array $address - address array
    	 * @return array - will return longitude and latitude for given address. if no longitude/latitude is found, an empty set is returned.
    	 *
    	 */
    	function getPosition($address)
    	{
    		$this->_resetCoords();
    		$query = $this->queryString;
    
    		if (!is_array($address))
    		{
    			$address = $this->_parseStringAddress($address);
    		}
    		
    		if ($address && sizeof($address) > 0)
    		{
    		
    			if (!empty($address['street']))
    			{
    				$query .= '&street=' . urlencode($address['street']);
    			}
    			
    			if (!empty($address['city']))
    			{
    				$query .= '&city=' . urlencode($address['city']);
    			}
    			
    			if (!empty($address['state']))
    			{
    				$query .= '&state=' . urlencode($address['state']);
    			}
    			
    			if (!empty($address['zip']))
    			{
    				$query .= '&zip=' . preg_replace('#[^\d]+#s','',$address['zip']);
    			}
    			
    			
    			$parser = xml_parser_create();
    			
    			xml_set_object($parser, $this);	  // allows to use parser inside object
    			xml_set_element_handler($parser, 'openTag', 'closeTag');	  // Sets the element handler functions for the XML parser parser
    			xml_set_character_data_handler($parser, 'characterData');   // Sets the character data handler function for the XML parser parser
    			
    			$fp = fopen($query, 'r');
    			
    			while (!feof($fp))
    				xml_parse( $parser, fgets($fp, 4096), feof($fp) );
    				
    			fclose($fp);
    			
    			xml_parser_free($parser);
    		}
    		
    		return $this->coords;
    	}
    	
    	/*
    	 * Function _parseStringAddress
    	 *
    	 * Private function called by getPosition to turn a 1 line string address into an array.
    	 * Expects Address to comply to format "Street Address, City, State Zip" (For US Addresses only)
    	 * 
    	 * Accepts 5 different inputs in the following formats:
    	 * Street Address, City, State Zip
    	 * City, State Zip
    	 * State and/or Zip
    	 *
    	 * @author Jim Rubenstein <jrubenstein (at) gmail (dot) com>
    	 * @param string $string - The address
    	 * @return array $address - The address, broken into an array.
    	 */
    	function _parseStringAddress ($string)
    	{
    		$string = explode(',', trim($string));
    		$address = array();
    		
    		//check the parts of the address
    		
    		if (sizeof($string) == 3) // 3 parts == Street Addy, City, State Zip
    		{
    			list($state, $zip) = explode(' ', trim($string[2]));
    			
    			$address = array (
    								'street' => trim($string[0]),
    								'city' => trim($string[1]),
    								'state' => $state,
    								'zip' => $zip
    							);
    		}
    		else if (sizeof($string) == 2) // 2 parts == City, State Zip
    		{
    			list($state, $zip) = explode(' ', trim($string[1]));
    			
    			$address = array (
    								'city' => trim($string[0]),
    								'state' => $state,
    								'zip' => $zip
    							);
    		}
    		else if (sizeof($string) == 1) // 1 part == State and/or Zip
    		{
    			$string = explode(' ', trim($string[0]));
    			
    			if (sizeof($string) == 2)
    			{
    				$address = array (
    									'state' => $string[0],
    									'zip' => $string[1]
    								);
    			}
    			else if (sizeof($string) == 1)
    			{
    				if (is_numeric($string[0]))
    				{
    					$address = array('zip' => $string[0]);
    				}
    				else
    				{
    					$address = array('state' => $string[0]);
    				}
    			}
    		}
    		
    		return sizeof($address) ? $address : false;
    	}
    	
    	functin _resetCoords()
    	{
    		$this->coords = array ('lat' => false, 'long' => false);
    	}
    	
    	function openTag($parser, $tag, $attrs)
    	{
    		$this->xmlParser['currentTag'] = strtolower($tag);
    	}
    	
    	function characterData ($parser, $data)
    	{
    		switch ($this->xmlParser['currentTag'])
    		{
    			case 'longitude':
    				$this->coords['long'] = $data;
    			break;
    			case 'latitude':
    				$this->coords['lat'] = $data;
    			break;
    		}
    	}
    	
    	function closeTag ($parser, $tag)
    	{
    		$this->xmlParser['currentTag'] = '';
    	}
    }
    
    ?>

3. You need to add this code to your controller, to instanciate the
component when the controller is loaded.


Controller Class:
`````````````````

::

    <?php 
    
    class MyController extends AppController {
    
    var $components = array('YahooGeoCoder');
    }
    
    ?>

4. You can to start using it!

::

    
    function doSomething()
    {
        $address = array(   'street'=>'1600 Pennsylvania Ave NW', 
                            'city'=>'Washington', 
                            'state'=>'DC', 
                            'zip'=>'20500');
        $longLat = $this->YahooGeoCoder->getPosition($address);
    
        //do whatever you want with $longLat
    }

That's all there is to it. Once you have your long/lat you can add all
kinds of mash-up functionality to your application.

Let me know what you all think, or if you have any enhancement ideas!

.. _http://developer.yahoo.com/maps/rest/V1/geocode.html: http://developer.yahoo.com/maps/rest/V1/geocode.html
.. meta::
    :title: Yahoo Geo Coding Component
    :description: CakePHP Article related to component,yahoo geo location,geolocation,yahoo geo coder,yahoo,Components
    :keywords: component,yahoo geo location,geolocation,yahoo geo coder,yahoo,Components
    :copyright: Copyright 2007 
    :category: components

