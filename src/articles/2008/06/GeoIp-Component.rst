GeoIp Component
===============

by kuja on June 13, 2008

If you need as much information as you can get about a client's
region, then maybe you need to get into the game with Maxmind's GeoIP
database and my GeoIpComponent!


The Boring Stuff
````````````````
Now, I'm pretty sure there is or used to be a GeoIp component
somewhere in CakeForge, but I haven't taken all that much of a look at
it.

I came up with my own because I thought that in general the Maxmind
pure PHP API was pretty dirty, limiting and quite unorganized.

This particular component contains a GeoIpComponent, and a set of
vendor libraries provided by Maxmind, used for accessing Maxmind's
free and commercial binary format country/city databases.

Some of my own vendor libraries are also included as a wrapper to
Maxmind's pure PHP libraries. That's actually where most of the beauty
of it lies. The actual Cake component simply provides a thin layer to
access the wrapper API.



Interesting Features
````````````````````
Aside from the basic features of being able to obtain regional
information (country name, city, postal code etc) about an address,
but the component also comes packed with other features.

Here are most of the additional features:


+ Directly resolve hostname to IP (makes regional lookup based on
  hostnames easier)
+ Ability to export current lookup data as an XML string (might be
  good for making web services)
+ Get a Google Maps or Yahoo! Maps URL for the current lookup data
  (based on latitude and longitude data)
+ Unlike the original geoiprecord and geoipdnsrecord classes,
  camelCased property names are used for lookup data (more Cake-ish)

Currently those are the additional features, but hopefully I'll get
feedback and suggestions and soon I'll be able to add a lot more
features to the GeoIp component distribution.



The Technical Stuff
```````````````````
Currently the GeoIp component distribution is available for download
via svn, on my private repository. If you would simply like to
download only the distribution, you can do this:

$ svn export `https://anon.svn.ariworks.co.kr/projects/geoip/trunk`_
geoip_component

Or if you want to stay updated with the component, you can check out a
working copy of the repository that you can later update with the "svn
update" command:

$ svn checkout `https://anon.svn.ariworks.co.kr/projects/geoip/trunk`_
geoip_component

In either case, "geoip_component" can be any name of your liking.

If you've got yourself a copy of the distribution, you should take a
look at it and you'll notice that the component follows a Cake
application structure. You'll find geoip_component/app,
geoip_component/app/{controllers,vendors} and more directories beneath
each. Simply copy the files to the relevant path in your own
application.

After all that, you're going to need to have a GeoIP database
obtainable for free by Maxmind (or a better one, if you pay for it!).
You'll want to get the binary format GeoLite City database
(`http://www.maxmind.com/app/geolitecity`_) and put it in your
APP/webroot/files directory. You can change the default path of the
database file in APP/controllers/components/geo_ip.php, at the top of
the file (modify the GeoIp.file configuration key).

Now let's get around to actually using the component. Let's say you
have a GeoipController, that you would use just for testing the
component.


Controller Class:
`````````````````

::

    <?php 
    cclass GeoipController extends AppController {
        var $components = array('GeoIp');
        var $uses = array();
        
        function lookup($addr = null)
        {
            $GeoIpData = $this->GeoIp->lookup($addr, true);
            if (!$GeoIpData) {
                // Couldn't find anything or there was a problem with the IP.
                $this->render('some_error_view');
            }
            else {
                // Make available to the view our GeoIpData result.
                $this->set('geo', $GeoIpData);
            }
        }
    }
    ?>

In that controller, we're looking up any address passed through a
parameter to the lookup() action. The second argument (true) to the
GeoIpComponent::lookup() method indicates that we want to
automatically resolve IP addresses, just in-case a hostname was passed
in as an argument. This allows for /geoip/lookup/google.com or
/geoip/lookup/x.x.x.x type lookups.

Now let's make the "lookup.ctp" view file to display this data in:


View Template:
``````````````

::

    
    <pre>
        <?php echo h($geo->asXML()); ?>
    </pre>

For now we'll only be showing our data as an XML string. You can
always display individual data through properties, such as
$geo->countryName, $geo->countryCode etc (and you can get the maps
urls by $geo->googleMapsUrl() and $geo->yahooMapsUrl() methods).

And that's it! It's pretty simple :)

There are many libraries used by the component, but mainly you only
need to know how to use GeoIpComponent and GeoIpData. If you've had a
chance to try this example out, then you'll already know what all the
properties are for GeoIpData, and you can use them as such:
$geo->propertyName, where $geo is your GeoIpData instance and
'propertyName' is the name of the property you'd like to get the value
for.

If you have any questions, feedback or suggestions, as usual I'm
hanging around #cakephp on irc.freenode.net, or you can contact me by
email at: shugotenshi at gmail dot com

.. _https://anon.svn.ariworks.co.kr/projects/geoip/trunk: https://anon.svn.ariworks.co.kr/projects/geoip/trunk
.. _http://www.maxmind.com/app/geolitecity: http://www.maxmind.com/app/geolitecity
.. meta::
    :title: GeoIp Component
    :description: CakePHP Article related to geoip,iptolocation,Components
    :keywords: geoip,iptolocation,Components
    :copyright: Copyright 2008 kuja
    :category: components

