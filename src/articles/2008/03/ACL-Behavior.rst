ACL Behavior
============

by coeus on March 12, 2008

This behavior is built off of the core Acl behavior. It fixes some
issues with the core behavior and allows you to set a model as both an
Aro and Aco. It also adds the alias where as the core behavior
doesn't.


Description
~~~~~~~~~~~
This Acl behavior makes a number of improvements from the built-in
one. It allows you to have a model act as both an "Aro" and "Aco". It
creates an alias in this format "Model.id". If parent node is not
provided, it'll create one based on root object. For example, if you
create Post.1 as an ACO and you set parentNode to null, it'll set the
parent_id to the root "Post" Aco if it exists. So your tree would look
like the following:

::

    
    Acos
    ----------
    Post
      |-Post.1
      |-Post.2
      |-Post.3



Usage Instructions
~~~~~~~~~~~~~~~~~~

Follow the following steps to use the Acl behavior.

Step 1
++++++
Copy the behavior code below into a file named "acl.php" in your
/app/models/behaviors folder.


Step 2
++++++
Load the behavior in the model you want to use it in.

Examples:

Ex 1: Act as Aco

::

    var $actsAs = array('Acl' => array('Aco'));

Ex 2:
Act as Aco

::

    var $actsAs = array('Acl' => 'Aco'); // Can accept a string as well

Ex 3: Act as Aro

::

    var $actsAs = array('Acl' => array('Aro'));

Ex 4: Act as Aco and Aro

::

    var $actsAs = array('Acl' => array('Aro','Aco'));



Step 3
++++++
Create the method parentNode($type) in your model. This will return
the parent_id to a method in the Acl behavior.

Here's an example of a User model passing a group id as it's parent id
only for the ARO:

::

    
    function parentNode($type)
    {
    	if ($type == 'Aro') {
    		if (!$this->id) {
    			return null;
    		}
    
    		$data = $this->read();
    
    		if (!$data['User']['group_id']){
    			return null;
    		} else {
    			return array('model' => 'Group', 'foreign_key' => $data['User']['group_id']);
    		}
    	} else {
    		return false;
    	}
    }

That's it!


Behavior Class
``````````````

::

    
    <?php
    
    class AclBehavior extends ModelBehavior {
    
    /**
     * Maps ACL type options to ACL models
     *
     * @var array
     * @access protected
     */
    
    /**
     * Sets up the configuation for the model, and loads ACL models if they haven't been already
     *
     * @param mixed $config
     */
    	function setup(&$model, $config = array()) {
    
    		if (empty($config)) {
    			$config = array('Aro');
    		} 
    		elseif (is_string($config)) {
    			$config = array($config);
    		}
    
    		$this->settings[$model->name]['types'] = $config;
    
    		foreach ($this->settings[$model->name]['types'] as $type)
    		{
    			if (!ClassRegistry::isKeySet($type)) {
    				uses('model' . DS . 'db_acl');
    				$object =& new $type();
    			} else {
    				$object =& ClassRegistry::getObject($type);
    			}
    			$model->{$type} =& $object;
    		}
    		
    
    		if (!method_exists($model, 'parentNode')) {
    			trigger_error("Callback parentNode() not defined in {$model->name}", E_USER_WARNING);
    		}
    	}
    /**
     * Retrieves the Aro/Aco node for this model
     *
     * @param mixed $ref
     * @return array
     */
    	function node(&$model, $type, $ref = null) {
    		if (empty($ref)) {
    			$ref = array('model' => $model->name, 'foreign_key' => $model->id);
    		}
    		return $model->{$type}->node($ref);
    	}
    /**
     * Creates a new ARO/ACO node bound to this record
     *
     * @param boolean $created True if this is a new record
     */
    	function afterSave(&$model, $created) {
    		if ($created) {
    
    			foreach ($this->settings[$model->name]['types'] as $type)
    			{
    				if ($parent = $model->parentNode($type)) {
    					$parent = $this->node($model, $type, $parent);
    				} else {
    					$parent = $model->{$type}->node($model->name);
    				}
    				$parent_id = Set::extract($parent, "0.{$type}.id");
    			
    				$model->{$type}->create();
    				$model->{$type}->save(array(
    					'parent_id'		=> $parent_id,
    					'model'			=> $model->name,
    					'foreign_key'	=> $model->id,
    					'alias'			=> $model->name . "." . $model->id
    				));
    			}
    		}
    	}
    /**
     * Destroys the ARO/ACO node bound to the deleted record
     *
     */
    	function afterDelete(&$model) {
    		foreach ($this->settings[$model->name]['types'] as $type)
    		{
    			$node = Set::extract($this->node($model, $type), "0.{$type}.id");
    			if (!empty($node)) {
    				$model->{$type}->delete($node);
    			}
    		}
    	}
    }
    
    ?>


.. meta::
    :title: ACL Behavior
    :description: CakePHP Article related to behavior,aro,aco,Behaviors
    :keywords: behavior,aro,aco,Behaviors
    :copyright: Copyright 2008 coeus
    :category: behaviors

