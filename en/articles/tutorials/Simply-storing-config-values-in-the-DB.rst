

Simply storing config values in the DB
======================================

by %s on October 26, 2008

There are many situations in web apps where site-wide configurations
need to be accessible to users through admin interfaces, rather than
configuration files residing on the server. It is a practical method
of storing configuration values that may need changing from time to
time, but without access to the core configuration file.


Code
~~~~
Settings are stored in the database, so we will first need to start by
creating the table:

::

    CREATE TABLE `settings` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `key` varchar(48) NOT NULL,
      `value` text,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `key` (`key`)
    )

Next, go ahead and bake your model and controller, but don't worry
about baking-in some of the pre-built methods. Modify your model to
look like this:

Model Class:
````````````

::

    <?php 
    class Setting extends AppModel {
    
    	var $name = 'Setting';
    	var $key = 'MyApp';
    	
    	//retrieve configuration data from the DB
    	function getcfg(){
    			$key=$this->key;
    			$cfgs = $this->find('first',array('fields'=>array('id','key','value')));
    
    			if (count($cfgs)) {
    				$this->checksum=$cfgs['Setting']['value'];
    				$cfgVal = unserialize($cfgs['Setting']['value']);
    
    			}
    			Configure::write($key,$cfgVal);
    	}
    
    	//write configuration data back to the DB
    	function writecfg(){
    		$key = $this->key;
    
    		$rev = Configure::read($key);
    
    		$value=serialize($rev);
    		
    		//if the configs haven't changed, no need to save them
    		if ($value==$this->checksum) return;
    		
    		//otherwise the configs have changed, so 
    
    		$this->data = array('key'=>$key,'value'=>$value);
    
    		if ($setting = $this->findByKey($key)) {
    			$this->data['id'] = $setting['Setting']['id'];
    		}
    
    		$this->save($this->data);
    	}
    }
    ?>

You'll notice that Configure:: values are serialized and stored
together using the MyApp Configure::key. At first this may seem
somewhat counter intuitive to how we think we should store
configurations. However, consider the hassle involved with trying to
figure out how/where to store multi-dimensional arrays in an
inherently flat storage system (db). It's probably doable, but not
without considerable headaches. Storing everything in a serialized
string allows Cake to worry about creating the structure - we just
save the output.

Next, open up your app_controller.php file and add the following code
to the top of the class:

::

    var $uses = array('Setting');

You will also need to add some code to your AppController
beforeFilter() and afterFilter() methods:

Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
    
         var $uses = array('Setting');
    
         function beforeFilter(){
    	     //reads the site-wide config values from the DB and puts them through the Configure::write method
    	     $this->Setting->getcfg();
         }
    
         function afterFilter(){
    	     //retrieves the site-wide configurations from Configure::read($key) and puts it back into the db if new
    	     $this->Setting->writecfg();
         }
    }
    ?>



Usage
~~~~~
Any place you would like to store a Configure:: value in the database,
you only need to use the $key specified in the model. If you don't,
the values will not get saved. An example would look something like:

::

    <? Configure::write('MyApp.themeName','My Great Theme'); ?>

Since the retrieval code is run in the before filter, we can treat the
Configure:: vars like any others in our app when we need to access
them. To recall a value we would run something like:

::

    <? $myVar = Configure::read('MyApp.themeName'); //returns 'My Great Theme' ?>



Next Steps
``````````
Because this is only a very simple way to store configuration data
(one row for the entire app), there will likely be some desire to
extend it. You may wish to segregate certain data into their own rows
(perhaps individual plugins or components), which would only require
some additional code to accept additional keys for read/write access.
That, my friends, is a job for another tutorial.

.. meta::
    :title: Simply storing config values in the DB
    :description: CakePHP Article related to database,configuration,config,conf,settings,Tutorials
    :keywords: database,configuration,config,conf,settings,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

