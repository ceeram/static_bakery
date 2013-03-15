

Configure Plugin
================

by %s on August 22, 2010

Override and set new values from an admin interface. Allowing you to
distribute a default /app/configs/core.php and then customize it for a
specific installation of a site. Updated to work with the newest RC of
cake 1.2. Replace your plugins/configs/vendor/import.php with the new
one in this article.
Many of the projects our company work on have multiple different
versions that need to be configured in slightly different ways. We
originally dealt with this by having multiple core.php include with
app and then selecting the right one. This became a nightmare to
manage, so we created a plugin that we can use to configure the
application. It provides a simple web interface to add new Configure
values and override existing values.

This plugin requires admin routing to be enabled.
Create a directory for our plugin: /app/plugins/ configs
Create a default controller: /app/plugins/configs/
configs_app_controller.php

Controller Class:
`````````````````

::

    <?php 
    class ConfigsAppController extends AppController
    {
      // intentionally left blank, plugins require the class even if it does nothing
    }
    ?>

Create a default model: /app/plugins/configs/ configs_app_model.php

Model Class:
````````````

::

    <?php 
    class ConfigsAppModel extends AppModel
    {
      // intentionally left blank, plugins require the class even if it does nothing
    }
    ?>

Create the single controller that we use:
/app/plugins/configs/controllers/ configs_controller.php

Controller Class:
`````````````````

::

    <?php 
    class ConfigsController extends ConfigsAppController {
    
    	var $name = 'Configs';
    	var $helpers = array('Html', 'Form');
    	var $uses = array('ConfigsConfig');
    
    	function admin_index() {
    		$this->set('configs', $this->ConfigsConfig->find('all',array(
    			'order' => 'ConfigsConfig.name ASC'
    			)
    		));
    	}
    
    	function admin_save() {
    		
    		if (empty($this->data)) {
    			$this->Session->setFlash(__('Invalid Config', true));
    			$this->redirect(array('action'=>'index'));
    		}
    		
    		if (!empty($this->data)) {
    			//$this->Transaction->begin();
    			foreach($this->data['ConfigsConfig'] as $config)
    			{
    				if ( strlen($config['name']) == 0 ) continue;
    				$this->ConfigsConfig->create();
    				if (!$this->ConfigsConfig->save($config))
    				{
    					$this->Session->setFlash(__('The Config could not be saved. Please, try again.', true));
    					//$this->Transaction->rollback();
    					$this->redirect(array('action'=>'index'));					
    				}
    			}
    			//$this->Transaction->commit();
    			$this->Session->setFlash(__('The Config has been saved', true));
    			$this->redirect(array('action'=>'index'));
    		}
    	}
    
    	function admin_delete($id = null) {
    		if (!$id) {
    			$this->Session->setFlash(__('Invalid id for Config', true));
    			$this->redirect(array('action'=>'index'));
    		}
    		if ($this->ConfigsConfig->del($id)) {
    			$this->Session->setFlash(__('Config deleted', true));
    			$this->redirect(array('action'=>'index'));
    		}
    	}
    
    }
    ?>

Create the model for the configs table: /app/plugins/configs/models/
configs_config.php

Model Class:
````````````

::

    <?php 
    class ConfigsConfig extends ConfigsAppModel {
    	var $name = 'ConfigsConfig';
    	var $useTable = 'configs_configs';
    }
    ?>

Create the one view that is used: /app/plugins/configs/views/configs/
admin_index.ctp

View Template:
``````````````

::

    
    <h2>Config Values</h2>
    
    <?php
    echo $form->create('Configs',array('action' => 'save' ));
     ?>
    <table>
    <thead>
    <tr>
    <th>Name</th>
    <th>Value</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
    <td>
    <?php echo $form->input('ConfigsConfig.0.name',array('label'=>false)); ?>
    </td>
    <td>
    <?php echo $form->input('ConfigsConfig.0.value',array('label'=>false)); ?>
    </td>
    </tr>
    <tr>
    <td colspan="2">
    <?php echo $form->submit(); ?>
    </td>
    </tr>
    <tbody>
    <?php
    $i = 1;
    foreach($configs as $config) {
    	$form->data = $config;
    	echo "<tr>";
    	echo "<td>" . $form->input('ConfigsConfig.'.$i.'.id') . $form->input('ConfigsConfig.'.$i.'.name',array('label'=>false)) . "</td>";
    	echo "<td>" . $form->input('ConfigsConfig.'.$i.'.value',array('label'=>false)) . "</td>";
    	echo "<td>" . $html->link('Delete','delete/'.$config['ConfigsConfig']['id'],null,'Are your sure?') . "</td>";
    	echo "</tr>";
    	$i++;
    }
     ?>
    </tbody>
    </table>

We also have a little bit of custom code that gets used later on that
needs to be setup.

Create the following file: /app/plugins/configs/vendor/ import.php

::

    
    <?php
    function __ConfigsImport() {
    	if (defined('CORE_UPDATED')) {
    		App::import('Core','ConnectionManager');
    		$db =& ConnectionManager::getDataSource('default');
    		$query = "SELECT name,value FROM configs_configs;";
    		$results = call_user_func_array(array(&$db,'query'),$query);
    		foreach($results as $row) {
    			Configure::write($row['configs_configs']['name'],$row['configs_configs']['value']);
    		}
    		define('CONFIGSCONFIG_RUN',true);
    	}
    }
    
    __ConfigsImport();
    ?>



You will need to setup a table to store the values. Here's a mysql 5.0
schema that should be easy to adapt to if you need too:

::

    
    CREATE TABLE IF NOT EXISTS `configs_configs` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `name` varchar(128) collate utf8_unicode_ci NOT NULL,
      `value` varchar(255) collate utf8_unicode_ci NOT NULL,
      `created` datetime NOT NULL,
      `modified` datetime NOT NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='used to store configurable stuff';

You need to edit two files in /app/configs to get everything working
and compatible with the built-in config caching.

Add the following line to the bottom of your: /app/configs/core.php

::

    
    DEFINE('CORE_UPDATED',true);

Add the following line to: /app/configs/bootstrap.php

::

    
    require_once( ROOT . DS . 'app/plugins/configs/vendor/import.php' );

Now that everything is setup, you can navigate to /admin/configs/ and
start customing your site configs.
`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _Page 2: :///articles/view/4caea0e3-2e88-41a5-9169-42b282f0cb67/lang:eng#page-2
.. _Page 1: :///articles/view/4caea0e3-2e88-41a5-9169-42b282f0cb67/lang:eng#page-1
.. meta::
    :title: Configure Plugin
    :description: CakePHP Article related to admin,plugin,configure,Plugins
    :keywords: admin,plugin,configure,Plugins
    :copyright: Copyright 2010 
    :category: plugins

