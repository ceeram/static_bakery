Geocoding in CakePHP
====================

by nate on April 13, 2007

A couple of weeks ago, I was building an event management system which
required that people be able to search events near them. I found
several existing solutions, but none that I could integrate as easily
as a proper Cake extension.


Introduction
~~~~~~~~~~~~

The Geocoded Behavior for CakePHP is the simplest and most powerful
way to integrate geocoding into your CakePHP application. It
integrates with both Google Maps and Yahoo! Local geocoding services
(and is extensible to allow for the inclusion of other services), it
automatically caches geocode data, and best of all, it can be
implemented in just 3 lines of code.


Downloading & Setup
~~~~~~~~~~~~~~~~~~~

The Geocoded Behavior is featured in the initial release of the Mashup
API Project, which is a new repository for CakePHP components,
behaviors and helpers which integrate web service APIs. You can
download the latest release of the Mashup API Project here:
`https://cakeforge.org/frs/?group_id=161`_
Once you have downloaded and unzipped the release, copy geocoded.php
from the /models/behaviors folder into your application's
/models/behaviors folder. Then, import /config/sql/geocodes.php into
your application's database. Finally, you need an Application ID
(Yahoo! Local) or an API key (Google Maps) in order to integrate the
service of your choice. You can get your keys here:

#. [li] Google Maps API Key -
   `http://www.google.com/apis/maps/signup.html`_ [li] Yahoo! Local App
   ID - `http://search.yahooapis.com/webservices/register_application`_



A Simple Example
~~~~~~~~~~~~~~~~

Let's start by verifying that our service works, and we're able to
connect to it using our key. We'll create a simple model called
Location, using the following SQL:


SQL:
````

::

    
    CREATE TABLE `locations` (
      `id` int(11) unsigned NOT NULL auto_increment,
      `name` varchar(255) default NULL,
      `addr1` varchar(255) default NULL,
      `addr2` varchar(255) default NULL,
      `city` varchar(255) default NULL,
      `state` varchar(255) default NULL,
      `zip` varchar(5) NOT NULL default '',
      `lat` float default NULL,
      `lon` float default NULL,
      `created` datetime default NULL,
      `modified` datetime default NULL,
      PRIMARY KEY  (`id`)
    );

Then we'll create our model, designating that we want to enable it for
geocoding:


Model Class:
````````````

::

    <?php 
    
    class Location extends AppModel {
    
    	var $name = 'Location';
    
    	var $actsAs = array('Geocoded' => array(
    		'key' => 'ABQIAAAAn0kmVahg_WhO0jCT8Z8MkBT2yXp_ZAY8_u....'
    	));
    }
    
    ?>

Replace the value of 'key' with your Google Map API key or Yahoo! App
ID (the default API key for the localhost domain is
ABQIAAAAn0kmVahg_WhO0jCT8Z8MkBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxS-
Zl837z60cpTjKeSeelhEJVmNOQ).

Then, create a controller that uses the Location model, and add the
following to an action:


Controller Class:
`````````````````

::

    <?php 
    	pr($this->Location->geocode('1600 Pennsylvania Ave. Washington DC USA'));
    ?>

You should then see an array similar to the following:

::

    
    Array
    (
    	[lat] => 38.898758
    	[lon] => -77.037691
    )

If DEBUG is set to 2 or higher, you should also see the following
queries:

SQL:
````

::

    
    	SELECT `Geocode`.`address`, `Geocode`.`lon`, `Geocode`.`lat` FROM `geocodes` AS `Geocode` WHERE `Geocode`.`address` = '1600 pennsylvania ave. washington dc usa' LIMIT 1
    	INSERT INTO `geocodes` (`address`,`lat`,`lon`) VALUES ('1600 pennsylvania ave. washington dc usa', 38.898758,-77.037691)

Before querying the web service, the Geocoded behavior checks the
cache to see if a lookup on this address has already been performed.
Then, after successfully retrieving the results from the web service,
it saves those results to the cache table. From now on, any lookups
for that address will be read from the cache.


Getting Fancy
~~~~~~~~~~~~~

Besides strings, the geocode() method will also accept arrays, from
which it will attempt to extract address information. This makes it
extremely easy to add geo-data to models which include address data.
The list of extractable fields is as follows:

::

    
    'street', 'address', 'addr', 'address1', 'addr1', 'address2', 'address2', 'apt', 'city', 'state', 'zip', 'zipcode', 'zip_code'

Using beforeSave(), we can automatically save geocoded coordinates to
our Location model every time a record is created or updated:

Model Class:
````````````

::

    <?php 
    
    class Location extends AppModel {
    
    	var $name = 'Location';
    
    	var $actsAs = array('Geocoded' => array(
    		'key' => 'ABQIAAAAn0kmVahg_WhO0jCT8Z8MkBT2yXp_ZAY8_u....'
    	));
    
    	function beforeSave() {
    		if ($coords = $this->geocode($this->data)) {
    			$this->set($coords);
    		}
    		return true;
    	}
    }
    
    ?>

This will save any valid coordinate set, based on the data provided.
The Location model uses the field combination of 'addr1, 'addr2',
'city', 'state', 'zip' to create the address string. Some other valid
combinations are:

#. [li]street, city, state [li]address, city, state, zip [li]addr,
   apt, city, zipcode [li]city, zip_code [li]zipcode [li]And so on.

Any other valid combination of fields from the list will work just
fine. You can also customize the field names in the Location model
which are used to store the coordinate data, if, for example you
wanted to use the field names 'latitude' and 'longitude'.

The full list of configuration options for the Geocoded behavior is as
follows:

+ [li] lookup - The name of the lookup service to use. Currently
  available options are 'google' and 'yahoo'. Defaults to 'google'. [li]
  key - The Google Maps API key or Yahoo! Local App ID for your
  application [li] cacheTable - The name of the table to use when
  caching geocode data. Defaults to 'geocodes'. Alternatively, you can
  create a Geocode model which will be used for all saves and lookups,
  in which case this setting will be ignored. [li] fields - An array
  containing the field names to use for latitude and longitude data.
  These should match the field names of your geocoded model. Defaults to
  array('lat', 'lon').



Searching
~~~~~~~~~

After creating a few locations, we can search for ones in our area.
The Geocoded Behavior includes a method called findAllByDistance(),
which allows you to search for records within a given distance of a
certain point. We can first get the coordinates of our search
location, then do the search. The findAllByDistance() method can be
called in one of two ways:

::

    
    findAllByDistance($coords, $distance);
    - or -
    findAllByDistance($x, $y, $distance);

In the first example, $coords is an array containing longitude and
latitude values (in that order). In the second example, $x and $y are
longitude and latitude values, respectively. In both examples,
$distance is the search radius in miles.

Putting this into practice, we can do something like the following:

Controller Class:
`````````````````

::

    <?php 
    	$youAreHere = $this->Location->geocode("132 Tremont St. Boston, MA");
    	$locations = $this->Location->findAllByDistance($youAreHere, 5);
    ?>

This will find all the Location records within 5 miles of me.
Alternatively, you could create a form based on the address fields in
the Location model, and run your searches dynamically:

Controller Class:
`````````````````

::

    <?php 
    	if (!empty($this->data)) {
    		$youAreHere = $this->Location->geocode($this->data);
    		$locations = $this->Location->findAllByDistance($youAreHere, 5);
    	}
    ?>

Adding location searching in CakePHP is now as simple as that.

Future versions of the Geocoded Behavior will generate a virtual
'distance' field within your query, allowing you to do sorting and
more advanced filtering and comparison. Other future plans include
setting a default measurement unit, with automatic unit converstions,
as well as setting default array keys from which to generate
addresses, as well as methods for facilitating the integration of
other geocoding APIs.

Stay tuned for more fun web APIs, and check out the official CakePHP
Mashup API Project at `https://cakeforge.org/projects/mashup/`_, where
more code examples and API integrations will be appearing shortly.

.. _https://cakeforge.org/projects/mashup/: https://cakeforge.org/projects/mashup/
.. _http://www.google.com/apis/maps/signup.html: http://www.google.com/apis/maps/signup.html
.. _http://search.yahooapis.com/webservices/register_application: http://search.yahooapis.com/webservices/register_application
.. _https://cakeforge.org/frs/?group_id=161: https://cakeforge.org/frs/?group_id=161

.. author:: nate
.. categories:: articles, tutorials
.. tags:: google,api,Google Maps,geolocation,geocode,yahoo,latitude,co
ordinates,longitude,intabox,Tutorials

