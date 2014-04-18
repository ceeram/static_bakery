RESTful Web Services With CakePHP
=================================

From [url]http://en.wikipedia.org/wiki/REST[/url] [quote]
Representational State Transfer (REST) is a software architectural
style for distributed hypermedia systems like the world wide web. The
term originated in a 2000 doctoral dissertation about the web written
by Roy Fielding, one of the principal authors of the HTTP protocol
specification, and has quickly passed into widespread use in the
networking community. [/quote] This will be a quick tutorial to adding
a simple REST Web Service to a CakePHP site. It will be a query style
or request, not POST or PUT that will be left to a later tutorial.
Ok let's get started getting a little RESTful ;)

This is an example used to request the list of countries from you
database to share among many applications (I use it to have consistant
set of country_id's among a couple of different applications that all
share the same database back end: REALBasic Application, Aperture
Plug-in and a CakePHP web application)

Turn on webservices in config/core.php by uncommenting this line (or
changing 'off' to 'on')

[]define('WEBSERVICES', 'on');
make a rest.php file in controllers/components

::

    
    <?php
    class RestComponent extends Object {
    }
    ?>

make a rest.php file in views/helpers

::

    
    <?php
       class RestHelper extends Helper
       {
       }
    ?>

In views/layouts make a folder called 'rest' and put a default.thtml
file in it


View Template:
``````````````

::

    
    <?php e('<?xml version="1.0" encoding="utf-8" ?>'); ?>
    <?php echo $content_for_layout; ?>

Let's first create the sql data structure we will use (This is a mysql
db example but any db that works with cake will work you might need to
adjust the create table information on your own.)



sql dump
````````

::

    CREATE TABLE `countries` (
      `id` int(11) NOT NULL auto_increment,
      `title` varchar(255) default NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=830 DEFAULT CHARSET=utf8;


Now as any good CakePHP app will have a Model file for this table.


models/country.php
``````````````````

Model Class:
````````````

::

    <?php class Country extends AppModel
    {
    	var $name = 'Country';
    	var $validate = array(
    		'title' => VALID_NOT_EMPTY,
    	);
    }
    ?>

Now we have the controller, this may be more then you want to look at
because I even made a action to load all the data, you could do this
in a set of sql calls, but hey it's a fun example of something you can
do.


controllers/countries_controller.php
````````````````````````````````````

Controller Class:
`````````````````

::

    <?php class CountriesController extends AppController
    {
    	var $scaffold;
    	var $name = 'Countries';
    	var $helpers = array('Html', 'Form' );
    
    
    	function load() {
    		if ($this->Country->findCount() == 0) {
    			$countries = array("United States", "Canada", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo, Democratic Republic of (Was Zaire)", "Congo, People's Republic of", "Cook Islands", "Costa Rica", "Cote D'ivoire", "Croatia (Local Name: Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France, Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, the Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Palestinian Territory, Occupied", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City State (Holy See)", "Venezuela", "Viet Nam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
    			foreach ($countries as $country) {
    				$this->Country->create();
    				$this->Country->save(array('Country'=>array('title'=>$country)));
    			}
    		}
                 $this->redirect('/countries');
    	}
    
    	function listing() {
    		$this->set('countries',$this->Country->findAll(null,null, 'id'));
    	}
    
    }
    ?>

Note that the meat of this is the call for listing since that actually
sends all the data out to the view


views/countries/rest/listing.thtml
``````````````````````````````````

View Template:
``````````````

::

    
    if (isset($countries) and !empty($countries)) :  ?>
    <rsp stat="ok">
    <countries type='array'>
    	<?php foreach ($countries as $country) : ?>
    		<country type='struct'>
    			<id><?php e($country['Country']['id'])?></id>
    			<title><?php e($country['Country']['title'])?></title>
    		</country>
    	<?php endforeach; ?>
    </countries>
    <?php else: ?>
    <rsp stat="fail">
    	<err type='struct'>
    	<?php if ($session->check('Message.flash')): ?>
    		<msg><?php e(strip_tags($session->read('Message.flash')));?></msg>
    	<?php endif; ?>
    	</err>
    <?php endif; ?>
    </rsp>

Now when you call view your REST client application
`http://your.server.example.com/rest/countries/listing`_ you will get
a nice little xml response back, and that is the basic getting started
with requesting some data with REST, next up I'll show how I POST
information to the CakePHP backend.


.. _http://your.server.example.com/rest/countries/listing: http://your.server.example.com/rest/countries/listing

.. author:: sdevore.myopenid.com
.. categories:: articles, tutorials
.. tags:: REST,webservice,Tutorials

