

HABTM Searching
===============

by %s on October 22, 2008

Some helpful HABTM magic for searching on the associations using a web
form to pass the parameters.
For the purpose of write-up we'll be using the example of User HABTM
Interests. Essentially what we're trying to do is find all Users who
have certain Interests (as per what's entered in the form), in
addition to any search criteria on the User itself. If we were simply
searching based on Interests this would be different, but this code
(hopefully) allows us to get fancy with multiple joins.

**Note: In our example, we're using a multi-select list for Interests.
If multiple Interests are selected, the code handles these as an 'OR'
operator (since the resulting query will use 'IN (values)'), the
change to an 'AND' operator shouldn't be too hard though.

Setup View
~~~~~~~~~~
Since we're using a web form for searching, let's set the view:

View Template:
``````````````

::

    
    <?php echo $form->create('User', array('action'=>'/index')); ?>
    <fieldset>
    	<legend><?php __('Search');?></legend>
    	<?php
    
    	echo $form->input('first_name');
    	echo $form->input('last_name');
    	echo $form->input('state_id', array('empty'=>'-- Select a State --'));
    	echo $form->input('Interest.id',
    			array( 'type' => 'select',
    				'empty' => '-- Select some interests --',
    				'multiple' => true,
    				'options' => $interests,
    				'label' => 'Interest'));
    	?>
    </fieldset>
    <?php echo $form->end('Search'); ?>

**Note: The 'state' and 'interest' options we're acquired using the
typical `find('list')` way in the controller (see below).

Setup Component
~~~~~~~~~~~~~~~
When someone hits the 'Search' button we'll need to take what's
returned and convert it to something useful (ex: from $this->data to
something that could be passed straight away to a `find()` without
many problems). To do that we use a nifty component. This is based
entirely on Nick Chankov's Filter component:
`http://nik.chankov.net/2008/03/01/filtering-component-for-your-
tables/`_ but tweaked the code a wee bit, with some added comments
(plus changed the name to avoid conflict):

Component Class:
````````````````

::

    <?php 
    /**
     * Search component
     * Based on Nick Chankov's Filter component: http://nik.chankov.net/2008/03/01/filtering-component-for-your-tables/
     * This moves it to a component so it can be attached to a Model specifically (since we typically search per
     * model), plus takes it a step further to factor in associations, albeit limited.
     * This takes it a step further for associations, albeit limited.
     *
     * @author  Brenton Bartel
     */
    
    class SearchComponent extends Object {
    	/**
    	 * fields which will replace the regular syntax in where i.e. field = 'value'
    	 */
    	var $fieldFormatting	= array(
    					"string" => array('LIKE', "%s%%"),
    					"date" => array('LIKE', "'%s'")
    					);
    
    	/**
    	 * Function which will change controller->data array.
    	 * Most often used to take in controller & form data and return a useable 'conditions' array.
    	 * Currently only tested for the calling controller (where controller == model)
    	 *
    	 * @param object $controller the class of the controller which call this component
    	 * @access public
    	 */
    	function process ($controller) {
    		// clean up and do session stuff
    		$this->_prepareSearch($controller);
    
    		$controller_model = $controller->{Inflector::singularize($controller->name)};
    		$associated = $controller_model->getAssociated();
    
    		$ret_val = array();
    
    		if (isset($controller->data)) {
    
    			// Loop for models
    			foreach ($controller->data as $model_name => $form_values) {
    				// See if we're dealing with the current controller's model, 
    
    				$column_defs = false;
    
    				// First, see if it's associated
    				if (array_key_exists($model_name, $associated)) {
    					$column_defs = $controller_model->{$model_name}->getColumnTypes();
    				}
    				// See if we're dealing with one that's set (ex: if UserController has $this->User)
    				// There could be a circumstance where the controller has employed `var $uses` to instantiate a model
    				// that is not a direct link to the controller (ex: UserController has $this->Interest), which is
    				// why we want to check if $model_name is associated with our current controller model first
    				// (ex: $this->User->InterestsUser).
    				elseif (isset($controller->{$model_name})) {
    					$column_defs = $controller->{$model_name}->getColumnTypes();
    				}
    
    				// So now that we have the column definitions (ex: data type) ...
    				if (is_array($column_defs)) {
    					foreach ($form_values as $k => $v) {
    						if ($v != '') {
    							// Check if there are some fieldFormatting set
    							if (array_key_exists($column_defs[$k], $this->fieldFormatting)) {
    								$col = $this->fieldFormatting[$column_defs[$k]];
    
    								// fail-safe if an array was defined properly or not
    								if (is_array($col)) {
    									$ret_val[$model_name .'.'. $k .' '. $col[0]] = sprintf($col[1], $v);
    								}
    								else {
    									$ret_val[$model_name .'.'. $k] = sprintf($col, $v);
    								}
    							}
    							else {
    								$ret_val[$model_name .'.'. $k] = $v;
    							}
    						}
    					}
    					// unsetting the empty forms ... why? (not sure)
    					if (count($form_values) == 0) {
    						unset($controller->data[$model_name]);
    					}
    				}
    			}
    		}
    
    		return $ret_val;
    	}
    
    	/**
    	 * function which will take care of the storing the search data and loading after this from the Session
    	 */
    	function _prepareSearch (&$controller) {
    
    		if (isset($controller->data)) {
    			foreach ($controller->data as $model => $fields) {
    				foreach ($fields as $key => $field) {
    					// No point in having anything if nothing's entered
    					if ($field == '') {
    						unset($controller->data[$model][$key]);
    					}
    				}
    			}
    
    			// store for future.
    			$controller->Session->write($controller->name.'.'.$controller->params['action'], $controller->data);
    		}
    
    		$search = $controller->Session->read($controller->name.'.'.$controller->params['action']);
    		$controller->data = $search;
    	}
    
    }
    ?>



Setup Controller
~~~~~~~~~~~~~~~~
So the controller would look something like this:

Controller Class:
`````````````````

::

    <?php 
    	var $components = array('Search');
    
    	function index() {
    		$this->User->recursive = 0;
    
    		// Trim things down (pretty much unbind anything not in the list or search form)
    		// $this->User->unbindModel();
    
    		$filter = $this->Search->process($this);
    
    		$this->set('users', $this->paginate(null, $filter));
    
    		$interests = $this->User->Interest->find('list');
    		$states = $this->User->State->find('list');
    		$this->set(compact('interests', 'states'));
    	}
    ?>



Setup Models
~~~~~~~~~~~~
Just to be safe, here's what the models look like (mostly setup using
bake):

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    
    	var $name = 'User';
    
    	//The Associations below have been created with all possible keys, those that are not needed can be removed
    	var $belongsTo = array(
    			'State' => array('className' => 'State',
    					'foreignKey' => 'state_id'
    			)
    	);
    
    	var $hasAndBelongsToMany = array(
    			'Interest' => array('className' => 'Interest',
    						'joinTable' => 'interests_users',
    						'foreignKey' => 'user_id',
    						'associationForeignKey' => 'interest_id',
    						'with' => 'InterestsUser',
    						'unique' => true
    			)
    	);
    
    }
    ?>


Model Class:
````````````

::

    <?php 
    class Interest extends AppModel {
    
    	var $name = 'Interest';
    	var $order = 'Interest.name';
    	var $validate = array(
    		'name' => array(
    			'rule' => array('custom', '/\S+/'),
    			'message' => 'Name can not be left blank',
    			'required' => true
    		)
    	);
    
    	//The Associations below have been created with all possible keys, those that are not needed can be removed
    	var $hasAndBelongsToMany = array(
    			'User' => array('className' => 'User',
    					'joinTable' => 'interests_users',
    					'foreignKey' => 'interest_id',
    					'associationForeignKey' => 'user_id',
    					'with' => 'InterestsUser',
    					'unique' => true
    			)
    	);
    
    }
    ?>

Setting up the join table as a model is optional.

Now down to the guts of what we're trying to do here ...


Options
~~~~~~~
There are 2 options for doing this:

1.) Using Teknoid's tips on "on the fly binding", we swap out our
HABTM for a 'hasOne', and use a 'GROUP BY' in the query to ensure
unique rows: `http://teknoid.wordpress.com/2008/08/06/habtm-and-join-
trickery-with-cakephp/`_
2.) We use the join table to search for all User ids wherein our
selected Interest ids are matched, then use this result array of User
ids to finally search for the Users.

There are advantages and disadvantages for both, depending on your
needs and complexity of your setup. Option 1 is nice in that it uses
the power of Cake's on the fly binding; however, the use of 'GROUP BY'
is more labour intensive for the database. Plus as a personal
preference, I believe it's circumventing the model's declaration of
their associations. The solution itself is elegant in that it uses
what it has to work with. Option 2, on the other hand involves a
couple extra database hits then some in-code wrangling with the
resulting array, which is more labour intensive on the code side. For
those coming from Ruby on Rails, this method would look more familiar
to them, which can arguably be an advantage or disadvantage ;) In
developing this method there were some workarounds required due to
`find('list')` (as commented in the code below), so this too has some
circumventing going on. Based on some quick tests, I found that Option
2 (extra database queries) is actually 3-4 times faster than Option 1
(on the fly bindings). This is almost entirely due to the 'GROUP BY'
in the query because MySQL had to use filesort. Keep in mind, this is
a fairly basic example using Cake's built-in query builder, so either
of these options can be tweaked for efficiency.

Both solutions shown below are still in their infancy and so far they
should be working for the basic example we have here. There are a
couple loose-ends in the code that would need to be cleaned up once
those circumstances are encountered, but there are plenty of comments
to point out what's going on. The inspiration behind these options can
be implemented in the controller itself for specific solutions (as
Teknoid's solution shows). For a more OOP & DRY approach, both of
these solutions are implemented in the AppModel's `beforeFind()`
function.


Option 1: On the fly binding (inspired by Teknoid):
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Model Class:
````````````

::

    <?php 
    	/**
    	 * Based on: http://teknoid.wordpress.com/2008/08/06/habtm-and-join-trickery-with-cakephp/
    	 *
    	 * New function to help with searching where conditions involve HABTM.
    	 * Nothing too fancy for now, just deals with first level (ex. no `with`), also, not sure how it'll
    	 * react for multiple fields.
    	 * So pretty much just best for `id` of a foreign key.
    	 * For HABTM, association condition should not be on the join table, but association. So if:
    	 *	User HABTM Interests, and searching for Users, should be Interest.id.
    	 * TODO: End result uses the 'IN' operator for the query, which is equivalent to 'OR', and will 
    	 * eventually want 'AND' instead.
    	 * TODO: Test in conditions where no 'with'
    	 *
    	 * @return array Modified queryData array
    	 */
    	function beforeFind(&$queryData) {
    
    		$ret_queryData = $queryData;
    
    
    		// See if we've got conditions
    		if (sizeof($queryData['conditions']) > 0) {
    
    			$associated = $this->getAssociated();
    
    			foreach ($queryData['conditions'] AS $field => $search_value) {
    
    				// Period indicates that not controller's own model
    				if (strpos($field, '.')) {
    					list($model, $column) = explode('.', $field);
    
    					// See if it's an association
    					if (array_key_exists($model, $associated)) {
    
    						// Do stuff based on association type, so far only HABTM
    						if ($associated[$model] == 'hasAndBelongsToMany') {
    
    							$assoc = $this->hasAndBelongsToMany[$model];
    
    							// See if there's a "with" condition to use as join table.
    							// If there is a "with", we should already have all the info we need (ex: keys)
    							if (!empty($assoc['with'])) {
    								$bind_model = $this->{$model}->{$assoc['with']};
    								$condition = $bind_model->name .'.'. $assoc['foreignKey'] .' = '. $this->name .'.id';
    							}
    							else {
    								$bind_model = $this->{$model};
    								// TODO: finalize
    								$condition = '';
    							}
    
    							// unbind
    							// Unlike the bind model below where we pass 'false' to ensure the binding is set for the
    							// remainder of the execution, here we do not pass 'false', because if we're doing pagination
    							// we'll do a 'COUNT' find, then the actual 'SELECT' find and if we unbind the HABTM then the
    							// 2nd time we pass through here, we'll lose the association and thus won't get the table/field
    							// condition changed below.
    							// TODO: fix it so we can keep the unbind.
    							$this->unbindModel(array('hasAndBelongsToMany' => array($model)));
    
    							// bind new
    							// Pass 'false' as the 2nd parameter to bind for remainder of execution
    							$this->bindModel(
    									array(
    										'hasOne' => array(
    												$bind_model->name => array(
    										//			'fields' => '',
    													'foreignKey' => false,
    													'type' => 'INNER',
    													'conditions' => array($condition)
    												),
    										)
    									), false);
    
    
    							// we're working with a different association name now, so change the condition
    							if (!empty($assoc['with'])) {
    								// set it in our return array
    								$ret_queryData['conditions'][$bind_model->name .'.'. $assoc['associationForeignKey']] = $search_value;
    
    								// and unset the old one, since different id field and such
    								unset($ret_queryData['conditions'][$field]);
    							}
    
    							// finally: since we have a HABTM change, we add the group by so we can do it properly.
    							$ret_queryData['group'] = $this->name .'.id';
    						}
    					}
    				}
    			}
    		}
    
    		return $ret_queryData;
    	}
    ?>



Option 2: Extra Db queries
~~~~~~~~~~~~~~~~~~~~~~~~~~

Model Class:
````````````

::

    <?php 
    	/**
    	 * New function to help with searching where conditions involve HABTM.
    	 * Nothing too fancy for now, just deals with first level (ex. no `with`), also, not sure how it'll
    	 * react for multiple fields.
    	 * So pretty much just best for `id` of a foreign key.
    	 * For HABTM, association condition should not be on the join table, but association. So if:
    	 *	User HABTM Interests, and searching for Users, should be Interest.id.
    	 * TODO: End result uses the 'IN' operator for the query, which is equivalent to 'OR', and will 
    	 * eventually want 'AND' instead.
    	 * TODO: Test in conditions where no 'with'
    	 *
    	 * @return array Modified queryData array
    	 */
    	function beforeFind($queryData) {
    
    		$ret_queryData = $queryData;
    
    		// See if we've got conditions
    		if (sizeof($queryData['conditions']) > 0) {
    
    			$associated = $this->getAssociated();
    
    			foreach ($queryData['conditions'] AS $field => $search_value) {
    
    				// Period indicates that not controller's own model
    				if (strpos($field, '.')) {
    					list($model, $column) = explode('.', $field);
    
    					// See if it's an association
    					if (array_key_exists($model, $associated)) {
    
    						// Do stuff based on association type, so far only HABTM
    						if ($associated[$model] == 'hasAndBelongsToMany') {
    
    							$assoc = $this->hasAndBelongsToMany[$model];
    
    							// See if there's a "with" condition to use as join table.
    							// If there is a "with", we should already have all the info we need (ex: keys)
    							if (!empty($assoc['with'])) {
    								$search_model = $this->{$model}->{$assoc['with']};
    								// $id_field is an array due to how `list` handles it: if only 1 field, it'll use `id` as the other,
    								// which we don't want, we just want the foreign key. Plus having `id` cause it to return excess
    								// foreign keys, since it makes it unique, we just want unique foreign keys.
    								$id_field = $assoc['foreignKey'];
    								// build our condition array
    								$condition = array($search_model->name .'.'. $assoc['associationForeignKey'] => $search_value);
    							}
    							else {
    								$search_model = $this->{$model};
    								$id_field = 'id';
    								$condition = array($search_model->name .'.'. $column => $search_value);
    							}
    
    							// So far can't find a way to nicely return a distinct/unique array using the 'list'
    							// condition in `find()`, so we use 'all', and use `Set::combine()` (which is pretty
    							// much what 'list' does anyway).
    							// Another option would've been to still use 'list', but add a 'GROUP BY' 
    							// (ex: 'group' => $assoc['foreignKey']) onto the query; however, this is slower
    							// for the database (arguably, what we're doing here could make up for that, so it's
    							// really a preference thing). Maybe do some testing if it's a big issue.
    
    							$result = $search_model->find('all',
    															array(
    																'fields' => 'DISTINCT '. $id_field,
    																'conditions' => $condition,
    																'recursive' => -1,
    																'callbacks' => false // because otherwise this `beforeFind` would be called again
    															));
    
    							$key_value = '{n}.'. $search_model->name .'.'. $id_field;
    							$result = Set::combine($result, $key_value, $key_value);
    
    							// TODO: somehow save this because some times (ex: pagination) we do a `SELECT COUNT(*)`, followed
    							// by the actually query itself, so would be nice to avoid an extra query.
    							$ids = array_keys($result);
    
    							if (!empty($assoc['with'])) {
    								// set it in our return array
    								$ret_queryData['conditions'][$this->name .'.id'] = $ids;
    
    								// and unset the old one, since different id field and such
    								unset($ret_queryData['conditions'][$field]);
    							}
    						}
    					}
    				}
    			}
    		}
    
    		return $ret_queryData;
    	}
    ?>


So there you have it ... give it a whirl.

Please leave some comments.

.. _http://nik.chankov.net/2008/03/01/filtering-component-for-your-tables/: http://nik.chankov.net/2008/03/01/filtering-component-for-your-tables/
.. _http://teknoid.wordpress.com/2008/08/06/habtm-and-join-trickery-with-cakephp/: http://teknoid.wordpress.com/2008/08/06/habtm-and-join-trickery-with-cakephp/
.. meta::
    :title: HABTM Searching
    :description: CakePHP Article related to HABTM,searching,Snippets
    :keywords: HABTM,searching,Snippets
    :copyright: Copyright 2008 
    :category: snippets

