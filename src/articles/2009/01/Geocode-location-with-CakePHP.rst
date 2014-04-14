Geocode location with CakePHP
=============================

by Firecreek on January 24, 2009

Locate postcodes and addresses with this CakePHP model.


Overview
~~~~~~~~
Cake model to call map API providers to get lng/lat and sometimes more
address information depending on the provider.


Example
~~~~~~~
`http://www.zeen.co.uk/code/view/geocode-location-with-cakephp`_

Map API Providers
~~~~~~~~~~~~~~~~~

+ Google Map API `http://code.google.com/apis/maps/`_
+ Multimap Open API `http://www.multimap.com/openapi/`_
+ Yahoo Maps API `http://api.local.yahoo.com/`_



Database
~~~~~~~~

::

    
    CREATE TABLE IF NOT EXISTS `geocodes` (
         `id` int(11) NOT NULL auto_increment,
         `key` varchar(100) NOT NULL,
         `lng` float(10,6) NOT NULL,
         `lat` float(10,6) NOT NULL,
         `address1` varchar(255) default NULL,
         `address2` varchar(255) default NULL,
         `address3` varchar(255) default NULL,
         `address4` varchar(255) default NULL,
         `town` varchar(90) default NULL,
         `postcode` varchar(10) default NULL,
         `country` varchar(6) default NULL,
         `provider` varchar(30) default NULL,
         `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
         `created` datetime NOT NULL,
         PRIMARY KEY  (`id`)
       ) ENGINE=InnoDB  DEFAULT CHARSET=latin1;



Model
~~~~~


Model Class:
````````````

::

    <?php 
     App::import('Core', 'HttpSocket');
    
        /**
         * Geocoding using Multimap and Google Maps
         *
         * This script has been designed for the UK so it might require some changes to work in other countriess
         *
         * Usage:
         *   $location = $this->Geocode->find('B91 1NE',array('provider'=>'google','cache'=>true));
         *
         * @version       1.1
         * @author        Darren Moore, zeeneo@gmail.com
         * @link          http://www.zeen.co.uk
         */
        class Geocode extends AppModel
        {
            /**
             * Providers
             *
             * Provider details and settings to parse details
             *
             * @var array
             * @access public
             */
            public $providers = array(
                'google'    => array(
                    'enabled'   => true,
                    'api'       => 'your-api-key-here',
                    'url'       => 'http://maps.google.com/maps/geo?q=:q&output=xml&key=:api',
                    'fields'    => array(
                        'lng'       => '/<coordinates>(.*?),/',
                        'lat'       => '/,(.*?),[^,\s]+<\/coordinates>/',
                        'address1'  => '/<address>(.*?)<\/address>/',
                        'postcode'  =>  '/<PostalCodeNumber>(.*?)<\/PostalCodeNumber>/',
                        'country'   =>  '/<CountryNameCode>(.*?)<\/CountryNameCode>/'
                    )
                ),
                'multimap'  => array(
                    'enabled'   => true,
                    'api'       => 'your-api-key-here',
                    'url'       => 'http://developer.multimap.com/API/geocode/1.2/:api?qs=:q&countryCode=:countryCode',
                    'fields'    => array(
                        'lat'       => '/<Lat>(.*?)<\/Lat>/',
                        'lng'       => '/<Lon>(.*?)<\/Lon>/',
                        'postcode'  =>  '/<PostalCode>(.*?)<\/PostalCode>/',
                        'country'   =>  '/<CountryCode>(.*?)<\/CountryCode>/'
                    )
                ),
                'yahoo'  => array(
                    'enabled'   => true,
                    'api'       => 'your-api-key-here',
                    'url'       => 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=:api&location=:q',
                    'fields'    => array(
                        'lat'       => '/<Latitude>(.*?)<\/Latitude>/',
                        'lng'       => '/<Longitude>(.*?)<\/Longitude>/',
                        'town'      => '/<City>(.*?), /',
                        'postcode'  =>  '/<Zip>(.*?)<\/Zip>/',
                        'country'   =>  '/<Country>(.*?)<\/Country>/'
                    )
                )
            );
            
            /**
             * Settings
             *
             * @var string
             * @access public
             */
            public $settings = array(
                'default'       => 'google',
                'countryCode'   => 'GB'
            );
            
            /**
             * Errors
             *
             * @var array
             * @access public
             */
            public $errors = array();
            
            
            /**
             * Setup model
             *
             * @param object $model 
             * @param array $config
             * @access public
             * @return void
             */
            public function __construct()
            {
                $this->connection = new HttpSocket();
                parent::__construct();
            }
            
        
            /**
             * Find location
             *
             * @param string $q Query
             * @param array $options Options when getting location, as followed:
             *                          - cache: Force caching on or off
             *                          - provider: Who to use for lookup, otherwise use $defaultProvider
             *                          - countryCode: Country code for searching, e.g. GB
             * @access public
             * @return array
             */
            public function find($q,$options = array())
            {
                //Check query exists
                if(empty($q)) { $this->errors[] = 'Missing Query'; return false; }
                
                //
                extract($this->settings);
                
                //Exception if UK postcode then always use multimap
                //Google postcode is rubbish!
                if($countryCode == 'GB' && !isset($options['provider']) && preg_match('/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9]?[A-Za-z])))) {0,1}[0-9][A-Za-z]{2})$/i',$q))
                {
                    $options['provider'] = 'multimap';
                }
                
                //Default settings
                $options = array_merge(
                    $options,
                    array(
                        'provider'    => $default,
                        'countryCode' => $countryCode,
                        'cache'       => true
                    )
                );
                
                //Check if q is in cache
                if($options['cache'] && ($cache = parent::find('first',array('conditions' => array('key'=>$q),'recursive'  => -1))))
                    return $cache[$this->alias];
                
                //Get coordinates from provider
                $data = $this->_geocoords($q,$options);
                
                //Save data and return
                if(!empty($data))
                {
                    $data = array_merge(
                        array(
                            'id'        => 0,
                            'key'       => $q,
                            'provider'  => $options['provider']
                        ),
                        $data
                    );
                    $this->create();
                    $this->save($data);
                }
                
                return $data;
            }
            
        
            /**
             * Get Lng/Lat from provider
             *
             * @param string $q Query
             * @param array $options Options
             * @see find
             * @access private
             * @return array
             */
            private function _geocoords($q,$options = array())
            {   
                $data = array();
                
                //Extract variables to use
                extract($options);
                extract($this->providers[$provider]);
                
                //Add country code to query
                $q .= ', '.$countryCode;
                
                //Build url
                $url = String::insert($url,compact('api','q','countryCode'));            
    
                //Get data and parse
                if($result = $this->connection->get($url))
                {
                    foreach($fields as $field => $regex)
                    {
                        if(preg_match($regex,$result,$match))
                        {
                            if(!empty($match[1]))
                                $data[$field] = $match[1];
                        }
                    }
                }
                
                return $data;
            }
            
        
        }
    ?>



.. _http://www.multimap.com/openapi/: http://www.multimap.com/openapi/
.. _http://api.local.yahoo.com/: http://api.local.yahoo.com/
.. _http://code.google.com/apis/maps/: http://code.google.com/apis/maps/
.. _http://www.zeen.co.uk/code/view/geocode-location-with-cakephp: http://www.zeen.co.uk/code/view/geocode-location-with-cakephp
.. meta::
    :title: Geocode location with CakePHP
    :description: CakePHP Article related to geolocation,geocode,google maps,latitude,longitude,longlat,lnglat,multimap,yahoo maps,Models
    :keywords: geolocation,geocode,google maps,latitude,longitude,longlat,lnglat,multimap,yahoo maps,Models
    :copyright: Copyright 2009 Firecreek
    :category: models

