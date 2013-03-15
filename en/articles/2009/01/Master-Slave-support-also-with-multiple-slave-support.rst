

Master / Slave support (also with multiple slave support)
=========================================================

by %s on January 20, 2009

We have discovered a way how to realize master/slave support with cake
(also with multiple slaves).
This old post is completely depricated:
`http://groups.google.com/group/cake-
php/browse_thread/thread/58ea010f930fab6c`_
Because Model::find() and Model::save() create the '$db'-handle many
lines before it calles the callbacks ::beforeFind() and
::beforeSave().

The way we realized is to overwrite the Model::save() and
Model::updateAll() methodes and to set the default db-config in the
AppModel::__construct().

Note: One thing remains: It's not possible to do a bindModel in the
::beforeSave() and ::afterSave() callbacks, because they get the wrong
db-config... If you find a way to do it, please let us know!

1. Define your Database-Configs in app/config/database.php. You should
have a master and as much slaves you want to have:

::

    
    class DATABASE_CONFIG {
    
      var $master = array(
        driver' => 'mysql',
        'persistent' => false,
        'host' => 'localhost',
        'login' => 'login',
        'password' => 'password',
        'database' => 'database',
        'encoding' => 'utf8'
      );
    
      // Config for Slave #1
      var $slave1 = array(
        driver' => 'mysql',
        'persistent' => false,
        'host' => 'slave1',
        'login' => 'login',
        'password' => 'password',
        'database' => 'database',
        'encoding' => 'utf8'
      );
    
      // Config for Slave #2
      var $slave2 = array(
        driver' => 'mysql',
        'persistent' => false,
        'host' => 'slave2',
        'login' => 'login',
        'password' => 'password',
        'database' => 'database',
        'encoding' => 'utf8'
      );
    ...
    }

2. Create/Alter app/models/app_model.php and create/alter the
constructor AppModel::__construct() of it like the following:


Model Class:
````````````

::

    <?php 
    class AppModel extends Model {
    
      public function __construct($id = false, $table = null, $ds = null)
      {
        // If a datasource is set via params, use it and return
        if((is_array($id) && isset($id['ds'])) || $ds) {
          parent::__construct($id, $table, $ds);
    
          return;
        }
    
        // Use a static variable, to only use one connection per page-call (otherwise we would get a new handle every time a Model is created)
        static $_useDbConfig;
        if(!isset($_useDbConfig)) {
          // Get all available database-configs
          $sources_list = ConnectionManager::enumConnectionObjects();
    
          // Find the slaves we have
          $slaves = array();
          foreach($sources_list as $name => $values) {
            // Slaves have to be named "slave1", "slave2", etc...
            if(preg_match('/^slave[0-9]+$/i', $name) == 1) {
              $slaves[] = $name;
            }
          }
    
          // Randomly use a slave
          $_useDbConfig = $slaves[rand(0, count($slaves) - 1)];
        }
        $this->useDbConfig = $_useDbConfig;
    
        parent::__construct($id, $table, $ds);
      }
    ...
    
    }
    ?>

This method uses a local static variable to save the db-config
application-wide. It collects all configs from the database.php that
start with "slave#" and selects a random one for it. This is now the
"default"-config for the application that is used with every query
done via the Model-class UNTIL we do the next step:

3. We now overwrite the Model::save() and Model::find() methods to use
another database-config for write-queries!
Note: We discovered, that it's not enough to just overwrite
Model::save(), because Model::updateAll() doesn't use the callbacks,
neither the Model::save() method.


Model Class:
````````````

::

    <?php 
    function save($data = null, $validate = true, $fieldList = array())
    {
      // Remember the old config
      $oldDb = $this->useDbConfig;
      // Set the new config
      $this->setDataSource('master');
      // Call the original Model::save() method
      $return = parent::save($data, $validate, $fieldList);
      // Reset the config/datasource
      $this->setDataSource($oldDb);
    
      return $return;
    }
    
    function updateAll($fields, $conditions = true) {
      $oldDb = $this->useDbConfig;
      $this->setDataSource('master');
      $return = parent::updateAll($fields, $conditions);
      $this->setDataSource($oldDb);
    
      return $return;
    }
    ?>

Now you should have full master/slave support within your cake-
application.
The way it works:
When your application is called, the AppModel will be called the first
time and realizes, that the local $_useDbConfig variable is not set.
So it randomly selects one of your slave-configs and sets this to the
default used database-config (AppModel::useDbConfig). Every further
instance of AppModel will recognize that the local $_useDbConfig
variable already exists and will use the same config.
Now every query you do will use this slave-config, EXCEPT all save's
and updateAll's, because you've overwritten these methods to use your
master-config.

Hope this tutorial was helpful to you!

Greetings,

Andreas Hofmann!

.. _http://groups.google.com/group/cake-php/browse_thread/thread/58ea010f930fab6c: http://groups.google.com/group/cake-php/browse_thread/thread/58ea010f930fab6c
.. meta::
    :title: Master / Slave support (also with multiple slave support)
    :description: CakePHP Article related to mysql,slave,master,replication,Tutorials
    :keywords: mysql,slave,master,replication,Tutorials
    :copyright: Copyright 2009 
    :category: tutorials

