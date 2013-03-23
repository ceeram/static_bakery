LogableBehavior
===============

by %s on October 21, 2008

This behavior is created to be a plug-and-play database changes log
that will work out of the box as using the created and modified fields
does in cake core. It is NOT version control, undo or meant to be used
as part of the public application. It's intent is to easily let you
(the developer) log users activities that relates to database
modifications (ie, add, edit and delete). If you just want to see what
your users are doing or need to be able to say "That is not a bug, I
can see from my log that you deleted the post yesterday." and don't
want to spend more time that it takes to do "var $actsAs =
array('Logable');" then this behavior is for you.


What
~~~~

The intent of this behavior is to create a row in a log table every
time a model's data (or all the model that the behavior is applied to)
is created, edited or deleted. The developer can set this log table up
to include as much detail as is required, and that is all the
configuration that is needed.


How
~~~

Requirements
````````````

+ The behavior found on page 2
+ A Log model( empty but for a order variable [created DESC]
+ A "logs" table with these fields required :

    + id (int)
    + title (string) automagically filled with the display field of the
      model that was modified.
    + created (date/datetime) filled by cake in normal way

+ actsAs = array("Logable"); on models that should be logged



Optional configurations
```````````````````````

Optional extra table fields for the "logs" table


+ description (string) Fill with a descriptive text of what, who and
  to which model/row

    + Example : Contact "John Smith"(34) added by User "Administrator"(1).



or if u want more detail, add any combination of the following


+ model (string) automagically filled with the class name of the model
  that generated the activity.
+ model_id (int) automagically filled with the primary key of the
  model that was modified.
+ action (string) automagically filled with what action is made
  (add/edit/delete)
+ user_id (int) populated with the supplied user info. (May be
  renamed. See bellow.)
+ change (string) depending on setting either

    + full: [name (alek) => (Alek), age (28) => (29)] or list: [name, age]

+ version_id (int) cooperates with VersionBehavior to link the the
  shadow table (thus linking to old data)

NB! VersionBehavior cooperation not implemented this version.


Optionally register what user was responisble for the activity
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

Supply configuration only if defaults are wrong. Example given with
defaults:

Model Class:
````````````

::

    <?php 
    class Apple extends AppModel {
    	var $name = 'Apple';
    	var $actsAs = array('Logable' => array(
    		'userModel' => 'User', 
    		'userKey' => 'user_id', 
    		'change' => 'list', // options are 'list' or 'full'
    		'description_ids' => TRUE // options are TRUE or FALSE
    	));
     [..]
    ?>

The change fields modifies what will be automagic filled in the change
field if the log table has it. The description_ids option sets whether
the description field will include the ids of the mode and the user.
(See example above)


Usage
~~~~~

If you are using the user feature of the behavior, the models needs to
know the id of the active user. This is most easily set in app
controller in this way, but note that you may use the
Logable::setUserData() method manually should you so desire.


Controller Class:
`````````````````

::

    <?php 
    // In AppController (or single controller if only needed once) add these lines to beforeFilter : 
    
    if (sizeof($this->uses) && $this->{$this->modelClass}->Behaviors->attached('Logable')) {
    	$this->{$this->modelClass}->setUserData($this->activeUser);
    }
    ?>


Where "$activeUser" should be an array in the standard format for the
User model used :[/]

::

    
    <? $activeUser = array( $UserModel->alias => array( $UserModel->primaryKey => 123, $UserModel->displayField => 'Alexander')); ?>

any other key is just ignored by this behaviour.


Get the logs out
````````````````

I don't supply a helper or views for this, I am sure you can manage,
but I suggest you make one view and use the controllers viewpath or
the controllers render method to only render a single view for all
models that use the behavior.

To extract the logs, no matter of you want all events, or just for one
model or one user, or even one user's activity on one model, you can
ask any model that the behavior is enabled on. There are two methods


+ findLog($params)
+ findUserActions($user_id, $params)


You can off course query the Log model in the normal way.


findLog
+++++++

This is the main function for retrieving the logged activities. It
will by default (when called with no parameters) return all activities
for the model it is called from, but it can also be used for any or
all models from any model. The available options are listed bellow.


+ model (string)
+ action (string) (add/edit/delte) defaults to NULL (ie. all)
+ fields (array)
+ order (string) defaults to 'created DESC'
+ conditions (array) add custom conditions
+ model_id (int) ForeignKey for a single instance of logged model
+ user_id (int) defaults to NULL (all users).


Remember to user your own foreignKey if you did not use 'user_id'

::

    <?php // examples
     // All acitivities on current model
     $data = $this->Apple->findLog();
     // All acitivities on current model instance
     $data = $this->Apple->findLog('model_id'=>32);
     // I am in apple controller, but i want acitivities for the user on a specific Logo isntance
     $data = $this->Apple->findLog(array('user_id'=>66,'model'=>'Logo','model_id'=>123));
    ?>



findUserActions
+++++++++++++++

The first parameter is compulsory and is the ID of the user
(foreignKey). The second is an array of options. The available options
are listed bellow. Model and fields does the expected things, while
events will create a description on the fly. This function is intended
to be improved in the next version to be translatable / customizable.

+ model (string)
+ [li]events (boolean)
+ [li]fields (array)


::

    <?php // examples
        // note we are asking for a different model
     $data = $this->User->findUserActions(301,array('model' => 'BookTest')); 
     $data = $this->Apple->findUserActions(301,array('events' => true));
     $data = $this->Model->findUserActions(301,array('fields' => array('id','model'),'model' => 'BookTest');
    ?>


The code (or download link) can be found on the next page.


You can download the newest version, including tests, here :

`http://code.google.com/p/alkemann/downloads/list`_
[p]Or you can grab version 1.3 here

Behavior Class:
```````````````

::

    <?php 
    /**
     * Logs saves and deletes of any model
     * 
     * Requires the following to work as intended :
     * 
     * - "Log" model ( empty but for a order variable [created DESC]
     * - "logs" table with these fields required :
     *     - id			[int]			: 
     *     - title 		[string] 		: automagically filled with the display field of the model that was modified.
     * 	   - created	[date/datetime] : filled by cake in normal way
     * 
     * - actsAs = array("Logable"); on models that should be logged
     * 
     * Optional extra table fields for the "logs" table :
     * 
     * - "description" 	[string] : Fill with a descriptive text of what, who and to which model/row :  
     * 								"Contact "John Smith"(34) added by User "Administrator"(1).
     * 
     * or if u want more detail, add any combination of the following :
     * 
     * - "model"    	[string] : automagically filled with the class name of the model that generated the activity.
     * - "model_id" 	[int]	 : automagically filled with the primary key of the model that was modified.
     * - "action"   	[string] : automagically filled with what action is made (add/edit/delete) 
     * - "user_id"  	[int]    : populated with the supplied user info. (May be renamed. See bellow.)
     * - "change"   	[string] : depending on setting either : 
     * 							[name (alek) => (Alek), age (28) => (29)] or [name, age]
     * 
     * - "version_id"	[int]	 : cooperates with VersionBehavior to link the the shadow table (thus linking to old data)
     * @todo implement version cooperation
     * 
     * Optionally register what user was responisble for the activity :
     * 
     * - Supply configuration only if defaults are wrong. Example given with defaults :
     * 
     * class Apple extends AppModel {
     * 		var $name = 'Apple';
     * 		var $actsAs = array('Logable' => array('userModel' => 'User', 'userKey' => 'user_id'));
     *  [..]
     * 
     * - In AppController (or single controller if only needed once) add these lines to beforeFilter : 
     * 
     *   	if (sizeof($this->uses) && $this->{$this->modelClass}->Behaviors->attached('Logable')) {
     *			$this->{$this->modelClass}->setUserData($this->activeUser);
     *		}
     *
     *   Where "$activeUser" should be an array in the standard format for the User model used :
     * 
     *   $activeUser = array( $UserModel->alias => array( $UserModel->primaryKey => 123, $UserModel->displayField => 'Alexander'));
     *   // any other key is just ignored by this behaviour.
     * 
     * @author Alexander Morland (alexander#maritimecolours.no)
     * @co-author Eskil Mjelva Saatvedt
     * @co-author Ronny Vindenes
     * @co-author Carl Erik Fyllingen
     * @category Behavior
     * @version 1.3
     */
    
    class LogableBehavior extends ModelBehavior 
    {
    	var $user = NULL;
    	var $UserModel = FALSE;
    	
    	/**
    	 * Cake called intializer
    	 * Config options are :
    	 *    userModel 		: 'User'. Class name of the user model you want to use (User by default), if you want to save User in log
    	 *    userKey   		: 'user_id'. The field for saving the user to (user_id by default).
    	 * 	  change    		: 'list' > [name, age]. Set to 'full' for [name (alek) => (Alek), age (28) => (29)]
    	 * 	  description_ids 	: TRUE. Set to FALSE to not include model id and user id in the title field
    	 *
    	 * @param Object $Model
    	 * @param array $config
    	 */
    	function setup(&$Model, $config = null) {
    		$this->settings = array(
    			'userModel' => 'User',
    			'userKey' => 'user_id',
    			'change' => 'list',
    			'description_ids' => TRUE
    		);
    		if ($config) {
    			$this->settings = array_merge($this->settings, $config);
    		}
    				
    		App::import('model','Log');
    		$this->Log = new Log();
    		if ($this->settings['userModel'] != $Model->alias) {
    			if (App::import('model',$this->settings['userModel'])) {
    	        	$this->UserModel = new $this->settings['userModel']();
    	        }
    		} else {
    			$this->UserModel = $Model;
    		}
           
    	}
    	
    	function settings(&$Model) {
    		return $this->settings;
    	}
    	/**
    	 * Useful for getting logs for a model, takes params to narrow find. 
    	 * This method can actually also be used to find logs for all models or
    	 * even another model. Using no params will return all activities for
    	 * the models it is called from.
    	 *
    	 * Possible params :
    	 * 'model' 		: mixed  (NULL) String with className, NULL to get current or FALSE to get everything
    	 * 'action' 	: string (NULL) String with action (add/edit/delete), NULL gets all
    	 * 'order' 		: string ('created DESC') String with custom order
    	 * 'conditions  : array  (array()) Add custom conditions
    	 * 'model_id'	: int	 (NULL) Add a int 
    	 * 
    	 * (remember to use your own user key if you're not using 'user_id')
    	 * 'user_id' 	: int 	 (NULL) Defaults to all users, supply id if you want for only one User
    	 * 
    	 * @param Object $Model
    	 * @param array $params
    	 * @return array
    	 */
    	function findLog(&$Model, $params = array()) {
    		$defaults = array(
    			 'model' => NULL,
    			 'action' => NULL,
    			 'order' => 'created DESC',
    			 $this->settings['userKey'] => NULL,
    			 'conditions' => array(),
    			 'model_id' => NULL,
    			 'fields' => array(),
    		);
    		$params = array_merge($defaults, $params);
    		$options = array('order' => $params['order'], 'conditions' => $params['conditions'], 'fields' => $params['fields']);
    		if ($params['model'] === NULL) {
    			$params['model'] = $Model->alias;
    		}
    		if ($params['model']) {
    	    	if (isset($this->Log->_schema['model'])) {
    	    		$options['conditions']['model'] = $params['model'];
    	    	} elseif (isset($this->Log->_schema['description'])) {    		
    	    		$options['conditions']['description LIKE '] = $params['model'].'%';
    	    	} else {
    	    		return FALSE;
    	    	}
    		}
        	if ($params['action'] && isset($this->Log->_schema['action'])) {
        		$options['conditions']['action'] = $params['action'];
        	}     	
    		if ($params[ $this->settings['userKey'] ] && $this->UserModel && is_numeric($params[ $this->settings['userKey'] ])) {
    			$options['conditions'][$this->settings['userKey']] = $params[ $this->settings['userKey'] ];
    		}
    		if ($params['model_id'] && is_numeric($params['model_id'])) {
    			$options['conditions']['model_id'] = $params['model_id'];
    		}
        	return $this->Log->find('all',$options);
    	}
    	
    	/**
    	 * Get list of actions for one user.
    	 * Params for getting (one line) activity descriptions 
    	 * and/or for just one model 
    	 *
    	 * @example $this->Model->findUserActions(301,array('model' => 'BookTest'));
    	 * @example $this->Model->findUserActions(301,array('events' => true));
    	 * @example $this->Model->findUserActions(301,array('fields' => array('id','model'),'model' => 'BookTest');
    	 * @param Object $Model
    	 * @param int $user_id
    	 * @param array $params
    	 * @return array
    	 */
    	function findUserActions(&$Model, $user_id, $params = array()) {
    		if (!$this->UserModel) {
    			return NULL;
    		}
    		// if logged in user is asking for her own log, use the data we allready have
    		if ( isset($this->user) 
    			 && isset($this->user[$this->UserModel->alias][$this->UserModel->primaryKey]) 
    			 && $user_id == $this->user[$this->UserModel->alias][$this->UserModel->primaryKey] 
    			 && isset($this->user[$this->UserModel->alias][$this->UserModel->displayField]) ) {
    			$username = $this->user[$this->UserModel->alias][$this->UserModel->displayField];
    		} else {
    			$this->UserModel->recursive = -1;
    			$user = $this->UserModel->find(array($this->UserModel->primaryKey => $user_id));
    			$username = $user[$this->UserModel->alias][$this->UserModel->displayField];
    		}
    		$fields = array();
    		if (isset($params['fields'])) {
    			if (is_array($params['fields'])) {
    				$fields = $params['fields'];
    			} else {
    				$fields = array($params['fields']);
    			}
    		}
    		$conditions = array($this->settings['userKey'] => $user_id);
    		if (isset($params['model'])) {
    			$conditions['model'] = $params['model'];
    		}
    		$data = $this->Log->find('all', array(
    			'conditions' => $conditions,
    			'recursive' => -1,
    			'fields' => $fields
    		));
    		if (! isset($params['events']) || (isset($params['events']) && $params['events'] == false)) {
    			return $data;
    		}
    		$result = array();
    		foreach ($data as $key => $row) {$one = $row['Log'];
    			$result[$key]['Log']['id'] = $one['id'];
    			$result[$key]['Log']['event'] = $username;
    			// have all the detail models and change as list : 
    			if (isset($one['model']) && isset($one['action']) && isset($one['change']) && isset($one['model_id'])) {
    				 if ($one['action'] == 'edit') {
    				 	$result[$key]['Log']['event'] .= ' edited '.$one['change'].' of '.low($one['model']).'(id '.$one['model_id'].')';
    				 	//	' at '.$one['created']; 
    				 } elseif ($one['action'] == 'add') {
    				 	$result[$key]['Log']['event'] .= ' added a '.low($one['model']).'(id '.$one['model_id'].')';
    				 } elseif ($one['action'] == 'delete') {
    				 	$result[$key]['Log']['event'] .= ' deleted the '.low($one['model']).'(id '.$one['model_id'].')';
    				 }
    					 	
    			} elseif ( isset($one['model']) && isset($one['action'])  && isset($one['model_id']) ) { // have model,model_id and action
                     if ($one['action'] == 'edit') {
    				 	$result[$key]['Log']['event'] .= ' edited '.low($one['model']).'(id '.$one['model_id'].')';
    				 	//	' at '.$one['created']; 
    				 } elseif ($one['action'] == 'add') {
    				 	$result[$key]['Log']['event'] .= ' added a '.low($one['model']).'(id '.$one['model_id'].')';
    				 } elseif ($one['action'] == 'delete') {
    				 	$result[$key]['Log']['event'] .= ' deleted the '.low($one['model']).'(id '.$one['model_id'].')';
    				 }
    			} else { // only description field exist
                    $result[$key]['Log']['event'] = $one['description'];
    			}
    				
    		}
    		return $result;
    	}
        /**
         * Use this to supply a model with the data of the logged in User.
         * Intended to be called in AppController::beforeFilter like this :
         *   
     	 *   	if ($this->{$this->modelClass}->Behaviors->attached('Logable')) {
     	 *			$this->{$this->modelClass}->setUserData($activeUser);/
     	 *		}
         *
         * The $userData array is expected to look like the result of a 
         * User::find(array('id'=>123));
         * 
         * @param Object $Model
         * @param array $userData
         */
    	function setUserData(&$Model, $userData = null) {
    		if ($userData) {
    			$this->user = $userData;
    		}
    	}
    	
    	function clearUserData(&$Model) {
    		$this->user = NULL;
    	}
    	
    	function beforeDelete(&$Model) {
    		$Model->recursive = -1;
    		$Model->read();
    	}
    	
    	function afterDelete(&$Model) {
    		$logData = array();
    		 if (isset($this->Log->_schema['description'])) {
    		 	$logData['Log']['description'] = $Model->alias;
    		 	if (isset($Model->data[$Model->alias][$Model->displayField]) && $Model->displayField != $Model->primaryKey) {
    		 		$logData['Log']['description'] .= ' "'.$Model->data[$Model->alias][$Model->displayField].'"';
    		 	}
    			if ($this->settings['description_ids']) {
    				$logData['Log']['description'] .= ' ('.$Model->id.') ';
    			}
    			$logData['Log']['description'] .= __('deleted',TRUE);
    		 }		
        	$logData['Log']['action'] = 'delete'; 	
        	$this->_saveLog($Model, $logData);
    	}
        
    	function beforeSave(&$Model) {
            if (isset($this->Log->_schema['change']) && $Model->id) {
            	$Model->recursive = -1;
            	$this->old = $Model->find(array($Model->primaryKey => $Model->id));
            }
    	}
    	
        function afterSave(&$Model,$created) {
         	if ($Model->id) {
        		$id = $Model->id;
        	} elseif ($Model->insertId) {
        		$id = $Model->insertId;
        	}     	
            if (isset($this->Log->_schema['model_id'])) {
       			$logData['Log']['model_id'] = $id;
        	}
    		if (isset($this->Log->_schema['description'])) {		
    	    	$logData['Log']['description'] = $Model->alias;
    		 	if (isset($Model->data[$Model->alias][$Model->displayField]) && $Model->displayField != $Model->primaryKey) {
    		 		$logData['Log']['description'] .= ' "'.$Model->data[$Model->alias][$Model->displayField].'"';
    		 	}
    	    	
    	        if ($this->settings['description_ids']) {
    	        	$logData['Log']['description'] .= ' ('.$id.') ';
    	        }
    										
    	    	if ($created) {
    	    		$logData['Log']['description'] .= __('added',TRUE);
    	    	} else {
    	    		$logData['Log']['description'] .= __('updated',TRUE);   
    	    	}  
    		}     
    		if (isset($this->Log->_schema['action'])) {					
    	    	if ($created) {
    	    		$logData['Log']['action'] = 'add';
    	    	} else { 
    	    		$logData['Log']['action'] = 'edit'; 		
    	    	}  
    			
    		}
    
        	if (isset($this->Log->_schema['change'])) {
        		$logData['Log']['change'] = '';
        		foreach ($Model->data[$Model->alias] as $key => $value) {
        			if (isset($Model->data[$Model->alias][$Model->primaryKey]) && !empty($this->old)) {
        				$old = $this->old[$Model->alias][$key];
        			} else {
        				$old = '';
        			}
        			if ($key != 'modified' && $value != $old) {
        				if ($this->settings['change'] == 'full') {
        					$logData['Log']['change'] .= $key . ' ('.$old.') => ('.$value.'), ';
        				} else {
        					$logData['Log']['change'] .= $key . ', ';	
        				}    				
        			}
        		}
        		if (strlen($logData['Log']['change'])) {
        			$logData['Log']['change'] = substr($logData['Log']['change'],0,-2);
        		} else {
        			return true;
        		}    		
        	}  
        	$this->_saveLog($Model, $logData);
        }
        
        /**
         * Does the actual saving of the Log model. Also adds the special field if possible.
         * 
         * If model field in table, add the Model->alias
         * If action field is NOT in table, remove it from dataset
         * If the userKey field in table, add it to dataset
         * If userData is supplied to model, add it to the title 
         *
         * @param Object $Model
         * @param array $logData
         */
        function _saveLog(&$Model, $logData) {  
    	 	if (isset($Model->data[$Model->alias][$Model->displayField]) && $Model->displayField != $Model->primaryKey) {
    	 		$logData['Log']['title'] = $Model->data[$Model->alias][$Model->displayField];
    	 	} else {
    	 		if ($Model->id) {
    	 			$id = $Model->id;
    	 		} elseif (isset($Model->data[$Model->alias][$Model->primaryKey])) {
    	 			$id = $Model->data[$Model->alias][$Model->primaryKey];
    	 		} else {
    	 			$id = 'MISSING';
    	 		}
    	 		$logData['Log']['title'] = $Model->alias.' ('.$id.')';
    	 	}
    		
        	if (isset($this->Log->_schema['model'])) {
        		$logData['Log']['model'] = $Model->alias;
        	}
        	
        	if (isset($this->Log->_schema['model_id'])) {
        		if ($Model->id) {
        			$logData['Log']['model_id'] = $Model->id;
        		} elseif ($Model->insertId) {
        			$logData['Log']['model_id'] = $Model->insertId;
        		}     		
        	}
        	
        	if (!isset($this->Log->_schema[ 'action' ])) {
        		unset($logData['Log']['action']);
        	}
        	
        	if (isset($this->Log->_schema[ $this->settings['userKey'] ]) && $this->user) {
        		$logData['Log'][$this->settings['userKey']] = $this->user[$this->UserModel->alias][$this->UserModel->primaryKey];
        	}  	
        	
            if (isset($this->Log->_schema['description'])) {
            	if ($this->user && $this->UserModel) {
            		$logData['Log']['description'] .= ' by '.$this->settings['userModel'].' "'.
            				$this->user[$this->UserModel->alias][$this->UserModel->displayField].'"';
            		if ($this->settings['description_ids']) {
            			$logData['Log']['description'] .= ' ('.$this->user[$this->UserModel->alias][$this->UserModel->primaryKey].')';
            		}
        										
            	} else { 
            		// UserModel is active, but the data hasnt been set. Assume system action.
            		$logData['Log']['description'] .= ' by System';
            	}
        		$logData['Log']['description'] .= '.';    		
        	} 	
        	  	
        	$this->Log->create($logData);
        	$this->Log->save(NULL,FALSE);    	
        }
    }
    ?>

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _http://code.google.com/p/alkemann/downloads/list: http://code.google.com/p/alkemann/downloads/list
.. _Page 1: :///articles/view/4caea0e2-e5a4-4a4b-b302-488682f0cb67#page-1
.. _Page 2: :///articles/view/4caea0e2-e5a4-4a4b-b302-488682f0cb67#page-2
.. meta::
    :title: LogableBehavior
    :description: CakePHP Article related to behavior,logging,logs,alkemann,Behaviors
    :keywords: behavior,logging,logs,alkemann,Behaviors
    :copyright: Copyright 2008 
    :category: behaviors

