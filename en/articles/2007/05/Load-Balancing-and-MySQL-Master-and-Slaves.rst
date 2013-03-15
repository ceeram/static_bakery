

Load Balancing and MySQL Master and Slaves
==========================================

by %s on May 26, 2007

If you are currently using MySql master/slave replication for load
balancing and wish to transport to cakePHP, it really couldn't be
easier.
2 simple steps to master/slave replication in cake..

Firstly in app/config/database.php write the following:-

::

    
    class DATABASE_CONFIG { 
            var $default = array( 
                    'driver'                => 'mysql', 
                    'host'                  => 'slave.host.ip', 
                    'login'                 => '....', 
                    'password'              => '.....', 
                    'database'              => 'my_db' 
            ); 
    
            var $master = array( 
                    'driver'                => 'mysql', 
                    'host'                  => 'master.host.ip', 
                    'login'                 => '....', 
                    'password'              => '.....', 
                    'database'              => 'my_db' 
            ); 
    
    } 

Then, in app/app_model.php, create 4 new methods:

::

    
    function beforeSave() { 
    	$this->useDbConfig = 'master'; 
    	return true;
    } 
    
    function afterSave() { 
    	$this->useDbConfig = 'default'; 
    	return true;
    } 
    
    function beforeDelete() { 
    	$this->useDbConfig = 'master'; 
    	 return true;
    } 
    
    function afterDelete() { 
    	$this->useDbConfig = 'default'; 
    	 return true;
    } 

It really is as simple as that.

However, there are some instance where you may want to use a query in
your controller where you update/insert information. In this case, use
the following:

::

    
    $this->ModelName->setDataSource('master');
    $this->ModelName->query("UPDATE post_views SET views=views+1 WHERE id=1234");
    $this->ModelName->setDataSource('default');

I use this where I want information to be updated, but don't want cake
to flush the cache. But then again, that may be like crossing the
streams in Ghostbusters, so don't listen to me..


With thanks to Nate and gwoo

`http://www.howtoforge.com/mysql_master_master_replication`_

.. _http://www.howtoforge.com/mysql_master_master_replication: http://www.howtoforge.com/mysql_master_master_replication
.. meta::
    :title: Load Balancing and MySQL Master and Slaves
    :description: CakePHP Article related to useDbConfig,load balancing,slave,master,Tutorials
    :keywords: useDbConfig,load balancing,slave,master,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

