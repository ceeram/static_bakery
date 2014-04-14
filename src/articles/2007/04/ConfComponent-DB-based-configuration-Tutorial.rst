ConfComponent DB based configuration Tutorial
=============================================

by CraZyLeGs on April 10, 2007

ConfComponent allows you to store your configuration into the db, set
and retrieve values organized into categories very easily.Now with
caching. changelog ========= 0.9.1: - Added the ability to call the
component in beforeFilter(). 0.9: - Added file caching to minimize db
hits. - Added default value for get() - Changed the way you set and
get values to be more cake-ish - Introducing $getEmpty - Introducing
some options to deal with boolean values stored as 'true' and 'false'.
- Added setCat to set a category of configs at once - Added setBatch
to save a set of categories with their configs at once
Update 09-04-2007
Cake already has a configuration system, and it's really neat. You can
put files in app/config, fill them with values and load them
automagically. config::read(). This is however something different.
This is db based, meaning your values are stored in the db.
Let's see how we can set it up and use it.
Download the component from:
`http://bakery.cakephp.org/articles/view/242`_
and save it as conf.php in your components' folder.


Create the DB tables
~~~~~~~~~~~~~~~~~~~~
Alright, so the first thing we need to do is actually create the
database tables

::

    
    CREATE TABLE `configs` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `config_category_id` int(10) unsigned NOT NULL,
      `key` varchar(50) NOT NULL,
      `value` varchar(50) NOT NULL,
      PRIMARY KEY  (`id`)
    )


::

    
    CREATE TABLE `config_categories` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `name` varchar(50) NOT NULL,
      PRIMARY KEY  (`id`)
    ) 

I gave the key and value columns a length of 50, but you can choose
whatever you want.


Create the associated models
~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Next, we create the associated models

Model Class:
````````````

::

    <?php 
    class Config extends AppModel {
    	
    	var $name = "Config";
    	
    	var $belongsTo = array('ConfigCategory');
    }
    ?>


Model Class:
````````````

::

    <?php 
    class ConfigCategory extends AppModel {
    	
    	var $name = "ConfigCategory";
    	
    	var $hasMany = array('Config');
    }
    ?>



In the controller
~~~~~~~~~~~~~~~~~
As with any component, if we want to use it, we need to include it in
the controller's $components array.


Controller Class:
`````````````````

::

    <?php 
    var $components = array('Conf');
    ?>

That's all we need, we can start using our component now.
insert some data in the db tables for testing, I'll assume you have
created a conf category named 'app', and some configs values.


Usage
~~~~~


Retrieving values
`````````````````
To retrieve conf values, we have two methods, get and find.

Controller Class:
`````````````````

::

    <?php 
    $fooVal = $this->Conf->get('app.foo');
    $barVal = $this->Conf->find('bar');
    $foo2Val = $this->Conf->get('app.foo2','defaultFoo');
    $bar2Val = $this->Conf->find('bar2','defaultBar');
    $fooAll = $this->Conf->find('foo.*');
    
    $booleanFoo = $this->Conf->get('app.foo2',true,true);
    
    ?>

Simple, in the first one, we know that the key 'foo' belongs to the
conf category 'app', so we specify the name of the category and the
key.
In the second, we actually don't know which category the bar key
belongs to, so we use the method find. beware though, if you have two
keys with the same name, it will return the first one it finds.
In the third example we introduce a new parameter, the default value
that should be returned if the key we're looking for doesn't exist.
Same thing with the forth example, but with find(). And in the fifth
example we introduce a new parameter that if set to true will return
true instead of 'true' and false instead of 'false', it's basically to
deal with boolean values.
The last example shows the syntax for getting all values of a
category.


Saving values
`````````````

To save values to the db, we have a method named set.
function set($key,$val,$possibleValues = null,$addCat = false,$addKey
= false)

the first parameter is the key of the form category.key, the second is
the value, simple. The third is ( if set ) an array of possible
values, it's basically a quick validation test, $val must be one of
the values in $possibleValues. You can ignore that parameter by
setting it to null and indeed it's the default behavior. There are two
extra parameters that default to false.
If the category passed in doesn't exist and addCat is true, the
category will actually be created.
If the key passed in doesn't exist and addKey is true, the Key will
actually be created.

If there is an error, set returns false.


Controller Class:
`````````````````

::

    <?php 
    //
    $this->Conf->set('app.foo','Cake!');
    // weee and bar will be created if they don't exist
    $this->Conf->set('app.lang','php',array('php','python','ruby'));
    $this->Conf->set('weee.bar','chocolat',null,'true','true');
    ?>

Also, there is setCat and setBatch, the first expects a cat name and a
data array where the keys are the conf names and the values are the
config values something like
array('i_am_a_key'=>'i_am_a_value','aww'=>'wee')

setBatch expects an array where the keys are the category names, and
the values are arrays like the one passed to setCat


Caching
~~~~~~~
A new important feature has been introduced which is caching. File
caching to be more specific. This was added to minimize DB hits.
A word of warning though:
Caching is really problematic in the sense, you can't know if a value
has changed in the db. Maybe another user that has write access
changed it and you still have the old value. There is no efficient way
of detecting change. Looping through all the values in the db is just
a no-no.
So, you shouldn't really change values through other interfaces than
the conf component because, set() clears the cache. If you do, clear
the cache manually using $this->Conf->clearCache().

The cache file is stored in app/tmp/persistent/conf.component.data.php
you can change the name in the component.


Using the component in beforeFilter
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Because of how components work, you can't use this component in
beforeFilter. The reason is that Conf uses the startup() method to uh-
huh startup. This method however is called by Cake after beforeFilter
and before the current action. So as you guessed the component has not
been initialized yet when beforeFilter is called and thus can't be
used.
I LIE I LIE!!1 Well since the version 0.9.1 of the component, you can
use the component in beforeFilter, provided you call startup()
manually. I added logic so startup is not called twice by Cake.


Controller Class:
`````````````````

::

    <?php 
    function beforeFilter()
    {
      $this->Conf->startup(&$this);
      // call the component's methods ..
    }
    ?>


That's it, have fun with this simple component, and as usual comments
are welcome.

.. _http://bakery.cakephp.org/articles/view/242: http://bakery.cakephp.org/articles/view/242
.. meta::
    :title: ConfComponent  DB based configuration Tutorial
    :description: CakePHP Article related to database,configuration,component,config,conf,buggy,Tutorials
    :keywords: database,configuration,component,config,conf,buggy,Tutorials
    :copyright: Copyright 2007 CraZyLeGs
    :category: tutorials

