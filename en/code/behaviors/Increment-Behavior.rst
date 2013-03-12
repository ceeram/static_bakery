

Increment Behavior
==================

by %s on May 13, 2008

Increment Behavior is ideal for use when you want to increment a
certain field by a delta increment such as adding votes, updating view
counters, etc.
First, the code for the Increment Behavior Class, save the following
code to a file called increment.php to the app/model/behavior
directory.


Behavior Class:
```````````````

::

    <?php 
    /**
     * Increment Behavior Class file
     * 
     * @author Ketan Patel
     * @license	http://www.opensource.org/licenses/mit-license.php The MIT License
     * @version 1
     *
     */
    
    /**
     * Increment Behavior to allow incrementing a single field by given amount.
     *
     */
    class IncrementBehavior extends ModelBehavior {
    
    	var $__settings = array();
    	
    	/**
    	 * Initiate behavior for the model using specified settings. Available settings:
    	 *
    	 * - incrementFieldName: (string) The name of the field which needs to be incremented
    	 * 
    	 *
    	 * @param object $Model Model using the behaviour
    	 * @param array $settings Settings to override for model.
    	 * @access public
    	 */
    	function setup(&$Model, $settings = array())
    	{
    		$default = array('incrementFieldName' => array('views'));
    
    		if (!isset($this->__settings[$Model->alias]))
    		{
    			$this->__settings[$Model->alias] = $default;
    		}
    
    		$this->__settings[$Model->alias] = am($this->__settings[$Model->alias], ife(is_array($settings), $settings, array()));
    	}
    	
    	function beforeFind(&$model, $query) {}
    
    	function afterFind(&$model, $results, $primary)  {}
    	
    	function beforeSave(&$model)  {}
    
    	function afterSave(&$model, $created) {}
    
    	function beforeDelete(&$model)  {}
    
    	function afterDelete(&$model)  {}
    
    	function onError(&$model, $error)  {}
    	
    	//Custom Method for a Behavior
    	/**
    	 * doIncrement method will allow user to increment
    	 * a given field by calling this function from its model.
    	 *
    	 * @param ModelObject $model
    	 * @param integer $id - Record Id for which the $field is to be incremented
    	 * @param integer (optional) $incrementValue, default is 1
    	 * @param string $field (optional) - If not supplied then field name which was provided 
    	 * 									 during initialization is used, otherwise
    	 * 									 it is overwritten with the supplied argument.
    	 * @return boolean
    	 */
    	function doIncrement(&$model, $id, $incrementValue=1, $field=null)
    	{
    		$answer = false;
    		
    		if (empty($field))
    		{
    			$field = $this->__settings[$model->alias]['incrementFieldName'];
    		}
    		
    		// Save the internal variables for the model
    		$recursiveLevel = $model->recursive ;		
    		$data = $model->data;
    		
    		$model->recursive = -1;
    		
    		$model->data = $model->findById((int)$id, array('id', $field));
    		
    		if (!empty($model->data))
    		{
    			$counter = (int)$model->data[$model->alias][$field] + (int)$incrementValue;
    			
    			$conditions = array($model->alias.'.id'=>$id);
    			
    			$fields = array($field=>$counter);
    		
    			// Issue updateAll as it won't call any other methods like beforeSave and such in the Model or the 
    			// Behavior methods. Just a step for saving callbacks which are not required.	
    			$answer = $model->updateAll($fields, $conditions);
    		}
    		
    		// restore the variables back to original
    		$model->data = $data;
    		$model->recursive = $recursiveLevel;
    		
    		return $answer;
    	}
    }
    ?>

Next, you want to implement this increment behavior in your model. Say
you have an article model for which you want to increment the field
'views' each time the user views the article. So to do this:


Model Class:
````````````

::

    <?php 
    class Article extend AppModel{
      var $name = 'Article';
      // Add the Increment Behavior as follows
      var $actsAs = array('Increment'=>array('incrementFieldName'=>'views'));
    }
    ?>

Now, you want to increment the view counter each time you show the
article to the user. So in your view action of the article controller,
you implement the call as follows:


Controller Class:
`````````````````

::

    <?php 
    class ArticlesController extends AppController{
       var $name = 'Articles';
    
       function view($id){
          // Call the doIncrement Behavior Method to increment the views counter. 
         // Scenario 1: Increment 'views' field in article table.
         // In model article, we specified that 'views' field is the
         // increment field and we would like to increment 
         // by default value of 1, so issue the following command.
          $this->Article->doIncrement($id);
    
         // Scenario 2: Increment 'votes' field in article table.
         // Since I haven't set it up in model article, 
         // I can still increment the votes field 
         // but I have to specify it as below.
          $this->Article->doIncrement($id, 1, 'votes');
       }
    }
    ?>

Hope this would come in handy to other users.

Cheers,
Ketan

.. meta::
    :title: Increment Behavior
    :description: CakePHP Article related to behavior,increment,Behaviors
    :keywords: behavior,increment,Behaviors
    :copyright: Copyright 2008 
    :category: behaviors

