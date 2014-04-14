GeoCouch Plugin - Storing Geocoded data in CouchDB
==================================================

by britg on August 26, 2008

This plugin is for projects that require a local repository of
geocoded data that is mined from google's geocoding service. For
instance, if your project needs more than the 15k geocode requests
allowed by google. Instead of shoving the JSON geodata into a
relational database scheme, like MySQL, I find it easier to store it
in a JSON-native document format. Fortunately, CouchDB fits that bill
and has a lot of other cool features to boot!
This GeoCouch plugin is simple. The following code assumes that:


#. You have CouchDB running somewhere
#. You have a google API key (grab one here:
   `http://code.google.com/apis/maps/signup.html`_)
#. You have a form that submits a text field named 'location' to the
   following controller action
#. [li]The location string can be anything you can type into google
   maps: i.e. 'Dallas, TX', '9151 Garland Rd, Nowhere, GA', 'Irvine
   California', etc.

Grab the geocouch library from:
`http://geocouch.googlecode.com/files/geocouch.php`_ and drop it into
your vendors folder.

Remember to jump into the GeoCouch.php script and edit the
configuration paramters.


Controller Class:
`````````````````

::

    <?php 
    class LocationsController extends AppController
    {
    
    ...
    
    
    function geocode() 
    {
    	App::import('Vendor', 'geocouch');
    	App::import('Sanitize');
            $location = Sanitize::escape($this->data['location']);
            
    	/*
    	 * Don't forget to edit the $GeoCouch->conf parameters!
    	 */
    	$GeoCouch = new GeoCouch();
    	
    	/*
    	 * The all-in-one method.
    	 * This geocodes the string and writes it to CouchDB
    	 * The second parameter is any other fields other
    	 * than the Google data that you want to save along
    	 * with this document.
    	 * 
    	 * NOTE: if this address already exists in CouchDB
    	 * a new revision is created.
    	 * 
    	 * Returns the CouchDB response, i.e.:
    	 * {"ok" : true, "rev":"3825793742", "id" : "dallas-tx" }
    	 */
    	$GeoCouch->save($location, array('custom_field' => 'value')); 
    	
    	/*
    	 * Simply geo coding.  
    	 * Does not write to CouchDB.
    	 * Returns an Google Geocoded Object.
    	 */
    	$geoObj = $GeoCouch->geoCode($location);
    	
    	/*
    	 * Write some Geo JSON to CouchDB.
    	 * First parameter is a unique name for the data
    	 * Second parameter is the JSON - in 
    	 * this case the json_encoded $geoObj from above.
    	 */
    	$GeoCouch->put($location, json_encode($geoObj));
    	
    	/*
    	 * Get some existing geo data
    	 */
    	$geoObj = $GeoCouch->get($location);
    }
    
    ...
    
    }
    ?>



.. _http://code.google.com/apis/maps/signup.html: http://code.google.com/apis/maps/signup.html
.. _http://geocouch.googlecode.com/files/geocouch.php: http://geocouch.googlecode.com/files/geocouch.php
.. meta::
    :title: GeoCouch Plugin - Storing Geocoded data in CouchDB
    :description: CakePHP Article related to geocoding,couchdb,google geocode,Plugins
    :keywords: geocoding,couchdb,google geocode,Plugins
    :copyright: Copyright 2008 britg
    :category: plugins

