Google Geocoder
===============

by %s on March 16, 2007

This is a wonderful component that will retrieve the latitude and
longitude of any given address.
This component is particularly helpful when you want to put more than
one address on your google map. People who have implemented google
maps know that some times it can get messy as Javascript itself is
really messy. I had a real hard time parsing multiple addresses via
javascript, although I did succeed but I realized it's far more
convenient to use PHP cURL object. First you have to retrieve the
longitudes and latitudes of your addresses and then you're able to
parse these to the GMap. Using this component all you have to do is
parse your addresses and get the longitudes and latitudes. Here it is:


Component Class:
````````````````

::

    <?php 
    class GeocoderComponent extends Object
    {
    	// URL Variable Seperator
    	var $uvs		= ', ';
    
    	// You Google Map API Key here -- This is the default API Key registered for www.webmechano.com
    	var $apiKey		= "ABQIAAAARwby5rOk_WuL_hld8cb1xBSWIj-Xv5w3UKePKfkno7uIC3OfyRSBOef-mB2swSBAuiIaF_hQsPv68w";
    	
    	var $controller	= true;
    
        function startup(&$controller)
        {
        	$this->controller = &$controller;
        }
    
    	function getLatLng($addy, $api_key = null){
    		
    		if(is_array($addy)){
    			// First of all make the address
    			if(!empty($addressArr['zip'])){
    				$address	= $addy['street'].$uvs.$addy['loc'].$uvs.$addy['zip'];
    			}
    			else{
    				$address	= $addy['street'].$uvs.$addy['loc'];
    			}
    		}else{
    			$address	= $addy;
    		}
    		// Default Api Key registered for webmechano. It's highly recommended that you use the one for stylished
    		if($api_key == null){
    			$api_key		= $this->apiKey;
    		}
    		$url		= "http://maps.google.com/maps/geo?output=xml&key=$api_key&q=";
    
    		// Here make the result array to return
    		// If the address is correct, it will return 200 in the CODE field so $result['code'] should be equal to 200
    		$result		= array('lat'=>'', 'lng'=>'', 'code'=>'');
    
    		// Make the Temporary URL for CURL to execute
    		$tempURL	= $url.urlencode($address);
    
    		// Create the cURL Object here
    		$crl	= curl_init();
    		curl_setopt($crl, CURLOPT_HEADER, 0);
    		curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
    		
    		// Here we ask google to give us the lats n longs in XML format
    		curl_setopt($crl, CURLOPT_URL, $tempURL);
    		$gXML		= curl_exec($crl);	// Here we get the google result in XML
    
    		// Using SimpleXML (Built-in XML parser in PHP5) to parse google result
    		$goo		= simplexml_load_string(utf8_encode($gXML)); // VERY IMPORTANT ! - ACHTUNG ! - this line is for documents that are UTF-8 encoded
    		// If the layout and views are not UTF-8 encoded you can use the line below - 
    		// comment the above line and un-comment the line below
    		// $goo		= simplexml_load_string($gXML);
    
    		$result['code']	= $goo->Response->Status->code;
    		if($result['code'] != 200){
    			$result['lat']		= 'error';
    			$result['lng']		= 'error';
    			$result['address']	='error';
    			return $result;
    		}
    		else{
    			$coords				= $goo->Response->Placemark->Point->coordinates;
    			list($lat, $lng)	= split(',', $coords);
    			$result['lat']		= $lat;
    			$result['lng']		= $lng;
    			$result['address']	= $gooAddress;
    			return $result;
    		}
    	}// end function / action : getLatLng	
    }
    ?>

The beauty of this component is that it will accept the address as a
string for example:

::

    
    $address = "560 Panama St, Stanford, CA 94305";

and also as an array of this format:

::

    
    $address['street'] = "560 Panama St"; // The street
    $address['loc'] = "Stanford, CA"; // The location (city)
    $address['zip'] = "94305"; // The zip code

And it will return an array of this format:

::

    
    $result['code'] // Google code: will return 200 on success
    $result['lat'] // Latitude of the given address
    $result['lng'] // Longitude of the given address

All you have to do is paste the code to your
/app/controllers/components directory as geocoder.php and viola !

Add this component in any controller and start retrieving the
longitudes and latitudes of your addresses.

::

    
    $this->Geocoder->getLatLng($address, $api_key);
    /*
    ** IMPORTANT NOTE ! Although $api_key is optional BUT Be sure to give ** it your own API KEY
    ** currently it uses the API key to my test site www.webmechano.com
    */



.. meta::
    :title: Google Geocoder
    :description: CakePHP Article related to google,component,Geocoder,Google Maps,google-component,Components
    :keywords: google,component,Geocoder,Google Maps,google-component,Components
    :copyright: Copyright 2007 
    :category: components

