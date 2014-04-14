Quickly Translate IP to Geo Coordinates
=======================================

by xeeton on December 21, 2006

About six months ago I posted this on the snippets section of the
CakePHP site. But, I felt this was more of a tutorial than a snippet
and perfect for the The Bakery.


Get the latest GeoLite City database
````````````````````````````````````
This is just a big binary file that you upload to your installation of
CakePHP, so don't worry about making room in your SQL database. Also,
when you want more accuracy, you can grab the non-free version and
you'll be golden. I haven't used it, but it's a drop-in replacement of
the free one. Anyway, download the file here:
`http://www.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz`_
Uncompress the file, and drop it in a directory called 'files' in your
webroot directory. Like this:

Filename: [cakeInstallDirectory]/webroot/files/geolitecity.dat

I put this step first, because you'll have the rest done by the time
you upload that 25mb file to your web server.


Obtain the GeoIP PHP API Sample
```````````````````````````````
The home page of the GeoIP PHP API (`http://www.maxmind.com/app/php`_)
explains the methods of accessing and querying the free GeoIP
database. Get the sample code here:
`http://www.maxmind.com/download/geoip/api/php/`_
For this example, I've used the geoipcity.inc since it provides all
the cool stuff I've needed. You can get the original in the
forementioned location, but since we're changing it (and it's released
under the GNU Lesser General Public License) I'll paste it here.

The following code should be saved as 'geo_ip.php' and saved in your
components directory.

Filename: [cakeInstallDirectory]/app/controllers/components/geo_ip.php

::

    
    <?php
    
    // Get original here: http://www.maxmind.com/download/geoip/api/php/sample_city.php
    // Modified by Drew Yeaton, Sentinel Design Group, (http://www.sentineldesign.net/)
    
    class GeoIpComponent extends Object {
        function lookupIp($ip) {
            vendor("geoipcity");
    
            $gi = geoip_open("files/geolitecity.dat", GEOIP_STANDARD);
            $result = geoip_record_by_addr($gi, $ip);
            geoip_close($gi);
            
            return get_object_vars($result);
        }
        
        function findIp() {
          if(getenv("HTTP_CLIENT_IP"))
            return getenv("HTTP_CLIENT_IP"); 
          elseif(getenv("HTTP_X_FORWARDED_FOR"))
            return getenv("HTTP_X_FORWARDED_FOR"); 
          else 
            return getenv("REMOTE_ADDR"); 
        }
    }
    
    ?>



Get the rest of the code
````````````````````````
Again, go and get some more free code:
`http://www.maxmind.com/download/geoip/api/php/`_ You'll want to place
the following files in your vendors folder: geoipregionvars.php,
geoipcity.php, geoip.php

Filename: [cakeInstallDirectory]/app/vendors/geoipregionvars.php
Filename: [cakeInstallDirectory]/app/vendors/geoipcity.php
Filename: [cakeInstallDirectory]/app/vendors/geoip.php

Important! In 'geoipcity.php' change the following:

require_once 'geoip.inc';
require_once 'geoipregionvars.php';

to:

vendor("geoip");
vendor("geoipregionvars");


Implement the GeoIP component in your controller
````````````````````````````````````````````````
With two lines of code, you can guess the user's IP address and get a
location based on it. Here is sample controller that I've used:

::

    
    class GeoIpSampleController extends AppController {
    	var $name = 'GeoIpSample';
    	var $components = Array('GeoIp');
    
    	function index() {
    		$ip = $this->GeoIp->findIp();
    		$ip_loc = $this->GeoIp->lookupIp($ip);
    
    		$this->set('user_ip', $ip);
    		$this->set('user_location', $ip_loc);
    	}
    }



Display user's location in your view
````````````````````````````````````
Somewhere in your view, you can put:

::

    
    <p>Ohh, <?=$user_location['city']?>, <?=$user_location['region']?> <?=$user_location['country_name']?> is my favorite place to visit.</p>
    <p><small>we think your ip is <?=$user_ip?>, are we right?</small></p>

Also, get the longitude and latitude like this:

::

    
    <p>
    <strong>Longitude:</strong> <?=$user_location['longitude']?><br/>
    <strong>Latitude:</strong> <?=$user_location['latitude']?>
    </p>



Now get started on your Google Maps mash-up...
``````````````````````````````````````````````


.. _http://www.maxmind.com/app/php: http://www.maxmind.com/app/php
.. _http://www.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz: http://www.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
.. _http://www.maxmind.com/download/geoip/api/php/: http://www.maxmind.com/download/geoip/api/php/
.. meta::
    :title: Quickly Translate IP to Geo Coordinates
    :description: CakePHP Article related to geoip,iptolocation,Tutorials
    :keywords: geoip,iptolocation,Tutorials
    :copyright: Copyright 2006 xeeton
    :category: tutorials

