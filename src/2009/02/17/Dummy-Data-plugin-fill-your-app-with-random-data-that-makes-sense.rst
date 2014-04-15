Dummy Data plugin - fill your app with random data that makes sense
===================================================================

by ronnyvv on February 17, 2009

Easy to use plugin for generating "realistic" random data for your
app. It fully supports models with associations, behaviors, callbacks
and validation.


Background
~~~~~~~~~~
We often need a complete, "realistic" and/or "correct" data set during
development.


+ Good test data help us design the work flow of the app better and
  decide how much data to put in each view
+ When prototyping we need data to show customers how the final app
  will work
+ Perform QA testing with "real" data before any customer is let
  anywhere near the app
+ Last but not least - it's more enjoyable to debug and develop with a
  funny quote :)

Entering test data is for dummies! So we created this plugin to do it
for us.


What it does
~~~~~~~~~~~~
This plugin comes with a large selection of data generators (based on
a fork of php-faker), including support for foreign keys using the
real models if avaiable (so your associated model can change it's id
in afterFind() via a behavior if you really want to do mental stuff
like that).

The plugin analyzes your tables and selects suitable generators for
each field in each table, based on type, name and other criteria.

The generated data is saved using the correct model if available, so
any validation, callbacks or behaviors will be applied as normal.


Examples
~~~~~~~~

Some examples of generated data.


Generated Users:
````````````````
id firstname surname username email address phone state 15 Prescott
Swanson prescott00 prescott.swanson@example.com 20469 Barnes Howeow
555 201 640 MD 14 Kylynn James kylynn87 kylynn.james@example.com 37060
Gibson Tunneb 555 960 200 ND 13 Kevin Shaw kevin75
kevin.shaw@example.com 94307 A5ev Iaxgeys 555 539 746 TX 12 Kelsey Kim
kelsey32 kelsey.kim@example.com 38597 Stanton xriee 555 299 355 CT

Generated Posts:
````````````````
id title color description created user_id 20 Colossal cool chalk Blue
Every kind of peaceful cooperation among men is primarily based on
mutual trust and only secondarily on institutions such as courts of
justice and police. 2008-12-19 09:45:41 15 19 Low change White Please
Don't ask me what the score is, I'm not even sure what the game is.
2008-12-20 06:20:27 12 18 Puny hope Red Only two things are infinite,
the universe and human stupidity, and I'm not sure about the former.
2009-02-09 01:08:42 3 17 Old-fashioned circle Cyan The modern
conservative is engaged in one of man's oldest exercises in moral
philosophy; that is, the search for a superior moral justification for
selfishness. 2008-12-27 16:41:50 6

Default settings where used for all fields except 'User.phone' which
had the field 'custom_variable' set to indicate the format of the
phone number, and 'Post.created' which had the fields 'custom_min' set
to '-2 months' and 'custom_max' set to 'now' to indicate any datetime
in the last two months. Notice that 'Post.user_id' has automatically
been correctly identified as belonging to the 'User' model.


Settings for Users table:
`````````````````````````
name type allow_null default custom_min custom_max custom_var address
street_address No email email No firstname firstname No phone phone No
555 Xxx xxx state us_state_abbr No surname surname No username
username No


Settings for Posts table:
`````````````````````````
name type allow_null default custom_min custom_max custom_var color
color No created datetime No -2months now description quote YES title
title No user_id BelongsTo No User



Installation
~~~~~~~~~~~~

#. Download the plugin from
   `http://code.google.com/p/alkemann/downloads/list`_ and copy it to
   your /app/plugins/
#. Enable admin routing in /app/config/core.pp
#. Create a copy of your '$default' db connection in
   /app/config/database.php and name it '$dummy'
#. Add or change the 'prefix' in your '$dummy' config to e.g. 'dum_'
   (See example below)
#. Run this in console to create the tables used by Dummy :

::

    cake schema run create Dummy -path plugins/dummy/config/sql

   Alternatively you can use Dummy in "realtime" mode by changing
   DummyTable and DummyField models to have $useTable = false;



Example database.php file
`````````````````````````

::

    <?php
    class DATABASE_CONFIG {
    	var $default = array(
    		'driver' => 'mysql',
    		'persistent' => false,
    		'host' => 'localhost',
    		'login' => 'user',
    		'password' => 'password',
    		'database' => 'stands',
    		'prefix' => '',
    	);
    	
    	var $dummy = array(
    		'driver' => 'mysql',
    		'persistent' => false,
    		'host' => 'localhost',
    		'login' => 'user',
    		'password' => 'password',
    		'database' => 'stands',
    		'prefix' => 'dum_',
    	);
    
    	var $testsuite = array(
    		'driver' => 'mysql',
    		'persistent' => false,
    		'host' => 'localhost',
    		'login' => 'user',
    		'password' => 'password',
    		'database' => 'test_stands',
    		'prefix' => '',
    	);
    }
    ?>


Configuration (optional)
~~~~~~~~~~~~~~~~~~~~~~~~

You can add to or replace the default field type and name matches by
creating a 'dummy_config.php' file in /app/config/ containing one or
both the following definitions:

::

    <?php
    $config['Dummy']['name_matches'] = array( 'field_name' => 'FakerClassName->generator_name' );
    $config['Dummy']['type_matches'] = array( 'filed_type' => 'FakerClassName->generator_name' ); 
    ?>



Example dummy_config.php
````````````````````````

::

    <?php
      $config['Dummy']['name_matches'] = array(
     	'name' => 'Name->firstname',
      	'count' => 'Number->bigInt'
      );
      $config['Dummy']['type_matches'] = array(
      	'integer' => 'Number->smallInt'
      );
    ?>

If you need new generators then you should add the code in the
relevant file(s) in the 'phpfaker' vendor.
For documentation on how to do this, check readme in vendors/phpfaker
folder.



Go to /admin/dummy/dummy_tables

If this is the first time, Dummy should analyze all your tables and
save default settings automatically.

On this page you can :

+ Enable and disable data generation for individual tables
+ Generate data for individual tables or for all active tables
+ Set the number of entries to generate for each table
+ Reanalyze all tables i.e. reset the table and field settings to
  default - any changes you have made will be lost

If you click on a table name you will go to the field settings page
for that table.


Table field settings page
`````````````````````````

This page lists the settings for all fields in the table, and on the
bottom of the page it lists all the data in the table for easy visual
inspection of the data you generate.

This is the main page for controlling the data generation, it allows
you to:


+ Enable and disable data generation for individual fields
+ Generate data for this table
+ Empty the table WARNING! The table will be TRUNCATED - ALL data will
  be lost
+ Reanalyze the table i.e. reset the field settings to default
+ Change the generator used for each fieldSelect the generator you
  want from the 'type' drop down menu. The drop down should only contain
  valid generators for the database field type.
+ Customize the generator used by specifying any of three options
Click the Edit link to set them. These custom values have different
meanings depending on the generator.

min / max
+++++++++
For numbers it (in most cases) means the minimum and maximum values
that the generator make. Date and time generators take in string
representations of their min and max values. For most strings, the max
value states the maximum number of characters allowed. In
Lorem->sentence max is used for the maximum number of words. Look up
specific rule for details

custom variable
+++++++++++++++
This value is used differently depending on the generator. Most common
uses are date and time generators (valid values 'past','now','future')
and belongsTo (valid values are existing table or model names). Float
uses it to state it's range (defaults to '%01.2f') and Name->firstname
and Name->surname can take a custom_variable of 'single' to only
return one name.

examples :
++++++++++

    + For a date type field called "published" you want a value between
      now and last christmas. You could use a custom_var of "past" and a
      custom_min of "2008-12-24".
    + For date and time, the min and max values take all strtotime() valid
      strings, so to get a time of between +/- 2 hours around generation
      time, use min:'-2hours' and max:'+2hours'



Tips
````

+ If the table has any foreign keys (ie belongsTo) then you should
  generate the table for the associated model first
+ If you are running the plugin in "realtime" mode, you may not change
  the generator type of fields, but you can use the configuration file
  'app/config/dummy_config.php' to set up your app specific rules.
+ If you plan on using the "Generate ALL" function, deactivate tables
  that you dont want filled


`1`_|`2`_|`3`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_

.. _http://code.google.com/p/alkemann/downloads/list: http://code.google.com/p/alkemann/downloads/list
.. _Page 1: :///articles/view/4caea0e4-f3e0-4140-b1a8-4d2482f0cb67/lang:eng#page-1
.. _Page 3: :///articles/view/4caea0e4-f3e0-4140-b1a8-4d2482f0cb67/lang:eng#page-3
.. _Page 2: :///articles/view/4caea0e4-f3e0-4140-b1a8-4d2482f0cb67/lang:eng#page-2

.. author:: ronnyvv
.. categories:: articles, plugins
.. tags:: testing,data,plugin,alkemann,data
generation,ronnyvv,prototyping,Plugins

