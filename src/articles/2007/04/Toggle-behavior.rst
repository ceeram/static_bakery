Toggle behavior
===============

by Jippi on April 05, 2007

A small snippet to toggle an ENUM field in a SQL :)


Use
~~~

Model Class:
````````````

::

    <?php 
    class TestModel extends AppModel 
    {
        var $actsAs = array('Toggle' => array('field' => 'enabled', 'options' => array('Yes','No'));
    }
    ?>



Code
~~~~

Model Class:
````````````

::

    <?php 
    class ToggleBehavior extends ModelBehavior {
    
        var $defaultSettings = array(
            'field'     => 'active',
            'options'   => array('Y','N')
        );
    
        var $settings = array();
    
        /**
         * Setup callback
         *
         * @param AppModel $model
         * @param array $config
         */
    	function setup(&$model, $config = array() )
    	{
    		$this->settings[$model->name] = am($this->defaultSettings, $config );
    
    		if( false === $model->hasField( $this->settings[$model->name]['field']))
    		{
    		    user_error('Model "'.$model->name.'" does not have a field called: '. $this->settings[$model->name]['field'], E_USER_ERROR );
    		}
    	}
    
    	/**
    	 * Enter description here...
    	 *
    	 * @param AppModel $model
    	 * @param integer $id
    	 */
    	function toggle(&$model, $id )
    	{
    	    $config = $this->settings[$model->name];
    
            $data = $model->read($config['field'], $id );
            if( false === $data )
            {
                return 0;
            }
    
            $current = $data[$model->name][ $config['field'] ];
            $pos = array_search( $current, $config['options'] );
            $other = $pos === 0 ? 1 : 0;
    
            if( true === $model->saveField( $config['field'], $config['options'][$other] ) )
            {
                return 1;
            }
            return 0;
    	}
    }
    ?>


.. meta::
    :title: Toggle behavior
    :description: CakePHP Article related to behavior toggle mode,Behaviors
    :keywords: behavior toggle mode,Behaviors
    :copyright: Copyright 2007 Jippi
    :category: behaviors

