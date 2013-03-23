Multirecord helper/behavior
===========================

by %s on December 29, 2008

Edit and add multiple record forms. A behavior and helper working
together to create multiple records form in the same way
Form->inputs() work. With a very few lines of code, go from one to
many records at once.
This project stated when I was to look at a component and redid it to
learn more about components. I chose a multiple record component by
Marcel Raaijmakers (Marcelius) and his article
`http://bakery.cakephp.org/articles/view/how-to-create-multirecord-
forms`_ as my project.

The main goal was to be able to edit, add and delete multiple record
set. Add 4 posts at once, edit 3 users at a time, delete all the
selected files.

The problem was to get the form helper to create a form, which matched
the data set Model->saveAll wants.

As Marcel Raaijmakers points out: [parafrase]"The form need to be on a
format saveAll() can handle, so the Helper has to create a data set on
the right format when posted. It has to be on the form
ModelAlias.{n}.fieldName while when it comes from Model->find('all',)
it is on the form {n}.ModelAlias.fieldName."[/parafrase]

At first I tried to write a component to rewrite the parameter array
sent to the functions on the controller. This way the data format
would fit what Model->saveAll() wants. This proved harder than I was
prepared for, and instead of a component and a helper, it became a
behavior and a helper.

The behavior do the save, find and delete operations. And the helper
duplicates the Form->inputs() functionality.

Other important aspects for the project:
* Usability
* Stick with convention
* Only minor changes to controller and views to make it work


Content:
````````
* Description-this page.
* Helper code-page 2
* Behavior code-page 3
* Full working example-page 4


Behvior
-------

FindMulti
`````````
To find data I did this

::

    $this->data = $this->Model->findMulti($ids);

Instead of

::

    $this->data = $this->Model->find('all');

The $ids are either a string on the form $ids = "3 5 8" or an array
$ids = array(2,5,8)

When Cake parses the url and sent parameters to the function, an url
on the form
http://host/app/controller/edit/3+5+8 is sent to the controller
function as a string on the form "3 5 8".

This is the reason I want the funtion to take in the string. To find
the multi record set, you only need to add the ids to the url.

The function findMulti() returns an array on the form
ModelAlias.{n}.fieldName, a normal find('all') which returns data on
the format {n}.ModelAlias.fieldName.


SaveMulti
`````````
For edit and add I also wanted to save multiple records

::

    $this->Model->saveMulti($this->data);

Instead of

::

    $this->Model->save($this->data);

By manipulating the post data, the data is now on a form where it can
use saveAll($this->data) the only thing done, is validating all,
before the save goes trough.


DeleteMulti
```````````
To delete all I did this

::

    $this->Model->deleteMulti($ids)

Instead of

::

    $this->Model->del($id)

Here again the $ids is a string on the form $ids = "3 5 8" or an array
$ids = array(3,5,8) in the same way the edit() function did.


MultipleRecords Helper
----------------------
The helper is made to supply the Form helper they are used together.
It only needs two functions.


$multipleRecords->inputs()
``````````````````````````
To add the multiple records form

::

    echo $form->create('Post', array('url'=>array('action' => 'edit')));
    echo $multipleRecords->inputs();
    echo $form->end('Submit');

Instead of

::

    echo $form->create('Post', array('url'=>array('action' => 'edit')));
    echo $$form->inputs();
    echo $form->end('Submit');

This will create a form with the same number of records as is in
$this->data. It expects the data to be on the format the
MultipleRecords Behavior provides.

The inputs() function require Form->create() to be run first.


$multipleRecords->add()
```````````````````````
The other function is only a helper to add one more empty record set
than already displayed on screen.

::

    echo $multipleRecords->add('Add Post');

When this form button is pressed, it posts the data to the add form,
sending it back to the view, while not saving it. This is done so the
entire form set is generated again, with one more empty set. The
reason for this is to make it work with the security component. As the
security component hashes the fields, an ajax call to add an empty set
would fail when the form is finely posted. It also keeps whatever you
have already written, but not saved. The same would go for script
calls to remove single records.


All in all
----------
It did work. I have kept the manipulation of data in the behavior and
the manipulation of the form helper inside the MultiRecord helper.
Using Form->inputs() in stead of a list of input is one of my
favourite cake functions. This makes form generation easy.

Here is my present Helper code. To be used together with Form helper.


Helper Class:
`````````````

::

    <?php 
    /**
     * 
     * Helper MultipleRecords
     *
     * How to easy edit and add multiple records. As the data format of Find->(all) is 
     * 
     * 
     * @name MultipleRecords
     * @abstract Do the job of Form->inputs() on multiple record sets.
     * @license MIT
     * @version 1.1.1
     * @modified 05. Jan. 2009
     * @author Eskil Mjelva Saatvedt
     * @author Ronny V Vindenes
     * @author Alexander Morland
     * @author Carl Erik Fyllingen
     *  
     */
    class MultipleRecordsHelper extends AppHelper {
    	/**
    	 * Helper name
    	 *
    	 * @var String
    	 */
    	var $name = 'MultipleRecords';
    	
    	/**
    	 * Helpers used by this helper
    	 *
    	 * @var Array
    	 */
            var $helpers = array('Form','Html'); 
    	
    	/**
    	 * Number of record set, used by the add function
    	 *
    	 * @var int
    	 */
    	var $numberOfRecords = 0;
    	
    	/**
    	 * Max number of record sets to display
    	 *
    	 * @var int
    	 */
    	var $maxLimit = 10;
    	
    	/**
    	 * Replaces the Form->inputs() with MultipleRecords->inputs() 
    	 * Creating a form with multiple record sets
    	 *
    	 * For this to work, form->create() has be be run before MultipleRecords->inputs()
    	 * 
    	 * @param Array $fields which fields is to be displayed, also takes 
    	 * inn 'legend' => 'My legend', with possible 'legend'=>'My legend %n' where n is 
    	 * the $i+1 counter
    	 * @param int $count Number of record set to display, if not set, it uses 1 if 
    	 * there is no data, or the size of the dataset it there is data
    	 * @return String
    	 */
    	function inputs($fields = array(), $numberOfRecords = false) {
    		
    		// If the number of record set is not set, use 1 if no data, and size of dataset 
    		// if it is one
    		if ($numberOfRecords === false || !is_numeric($numberOfRecords) || $numberOfRecords < 1) {
    			$numberOfRecords = 1;
    			if (sizeof($this->data[$this->model()])) {
    				$numberOfRecords = sizeof($this->data[$this->model()]);
    			}
    		}
    		$this->numberOfRecords = $numberOfRecords;
    		
    		// Check for max limit
    		if ($this->numberOfRecords > $this->maxLimit) {
    			$this->numberOfRecords = $this->maxLimit;
    			// Display a warning if debug is on and the maxLimit is breached
    			debug('Max limit of number of records reached. Can be set in 
    			app/views/helpers/multiple_records.php');
    		}
    		
    		// If ledgend is not set, use "New Modelname"
    		$legend = __('New', true) . ' ' . $this->model();
    		
    		$fieldSet = null;
    		
    		// Code parts from Form helper, to manipulate the fields
    		if (is_array($fields)) {
    			if (array_key_exists('legend', $fields)) {
    				$legend = $fields['legend'];
    				unset($fields['legend']);
    			}
    			if (isset($fields['fieldset'])) {
    				$fieldSet = $fields['legend'];
    				unset($fields['fieldset']);
    			}
    		} elseif ($fields !== null) {
    			$fields = array();
    		}
    		if (empty($fields)) {
    			// For this to work, form->create() has be run before MultipleRecords->inputs()
    			$fields = array_keys($this->Form->fieldset['fields']);
    		}
    		
    		// String holding the output, all the form fields
    		$output = '';
    		
    		// For $count number of times, call Form->inputs() with the correct field list, 
    		// with the number added to be on the form: Model.2.field_name		
    		for ($i = 0; $i < $this->numberOfRecords; $i++) {
    			$fieldStrings = array();
    			foreach ($fields as $value) {
    				$modelPaths = explode('.', $value);
    				if (sizeof($modelPaths) == 1) {
    					$fieldStrings[] = $this->model() . '.' . $i . '.' . $value;
    				} else {
    					$fieldStrings[] = $modelPaths[0] . '.' . $i . '.' . $modelPaths[1];
    				}
    			}
    			// Add a potensial counter to the ledgend
    			$fieldStrings['legend'] = str_replace('%n', $i + 1, $legend);
    			if ($fieldSet) {
    				$fieldStrings['fieldset'] = $fieldSet;
    			}
    			$output .= $this->Form->inputs($fieldStrings);
    		}
    		
    		return $output;
    	}
    	
    	/**
    	 * Display the add one more empty record set button
    	 * 
    	 * If used before the record set, the $numberOfRecords has to be set
    	 * 
    	 * @param String $title the button title
    	 * @param int $n number of record set one want in total
    	 * @return String returns a form button if maxLimit is not reached
    	 */
    	function add($title, $numberOfRecords = null) {
    		if (!$numberOfRecords || !is_numeric($numberOfRecords) || $numberOfRecords < 1) {
    			$numberOfRecords = $this->numberOfRecords;
    		}
    		// If maxLimit - 1 or higher stop displaying the add button
    		if ($numberOfRecords < $this->maxLimit) {
    			return $this->Form->submit($title, array(
    					'onClick' => 'this.form.action = "' . $this->Html->url(array(
    							($numberOfRecords + 1))) . '"; return true;'));
    		} else {
    			return '';
    		}
    	}
    }
    ?>


MultipleRecords behavior



Behavior Class:
```````````````

::

    <?php 
    /**
     * Behavior MultipleRecords
     * 
     * Normal $Model->find('all') return an array on the form {n}.$Model.fieldName
     * while we now wants $this->Model->findMulti($ids) to return an array on
     * the form Model.{n}.fieldName
     * 
     * For the findMulti and deleteMulti, it takes inn a list (or an array) on the 
     * form sent from the url http://host/app/controller/edit/3+5+7 and find or 
     * delete the data sets
     * 
     * @name MultipleRecords
     * @license MIT
     * @version 1.1
     * @modified 19. oct. 2008
     * @author Eskil Mjelva Saatvedt
     * @author Ronny V Vindenes
     * @author Alexander Morland
     * @author Carl Erik Fyllingen
     * @abstract  This behaviour let you save, find and delete multiple data sets on 
     * the same form $Model->saveAll($data) expect it to be. And on an url friendly
     * form: http://host/app/controller/edit/3+5+7
     * 
     */
    class MultipleRecordsBehavior extends ModelBehavior {
    	/**
    	 * Default options. 
    	 *
    	 * @var array
    	 */
    	var $defaultOptions = array('validate' => 'first');
    	
    	/**
    	 * Saves all with validation set to validate all before save is done
    	 *
    	 * @param Model $Model
    	 * @param Array $data
    	 * @param Array $options
    	 * @return Boolean TRUE if all is saved else FALSE
    	 */
    	function saveMulti(&$Model, $data, $options = array()) {
    		if (!isset($options['validate'])) {
    			// Set to validate all before save
    			$options = am($this->defaultOptions, $options);
    		}
    		return $Model->saveAll($data[$Model->alias], $options);
    	}
    	
    	/**
    	 * Find multiple records by taking in an array list of ids. Returning the data
    	 * on the format of Model.{n}.field, instead of on the form {n}.Model.field 
    	 *
    	 * @param mixed $ids An array of ids to get, or a string on the form 
    	 * $ids = "3 5 22" or a single id the string form is sent in the url as 3+5+22
    	 * @param array $options
    	 * @return Array of multiple datasets on the form Model.{n}.field
    	 */
    	function findMulti(&$Model, $ids = null, $options = array()) {
    		
    		if (is_array($ids) || is_numeric($ids)) {
    			// Do nothing, it is already an array or a single id
    		} else if (is_string($ids)) {
    			$ids = explode(' ', $ids);
    		}
    		
    		$conditions = array($Model->alias . '.id' => $ids);
    		if (isset($options['conditions'])) {
    			$options['conditions'] = am($options['conditions'], $conditions);
    		} else {
    			$options['conditions'] = $conditions;
    		}
    		$data = $Model->find('all', $options);
    		$ret[$Model->alias] = Set::extract($data, '{n}.' . $Model->alias);
    		return $ret;
    	}
    	
    	/**
    	 * Takes in a list of arrays and delete all
    	 *
    	 * @param Model $Model
    	 * @param Mixed $ids a list of ids to delete on the form $ids='3 5 7', in the 
    	 * URL it looks like http://host/app/controller/delete/3+5+7
    	 * also takes in an array (3,5,7). Can also take in an array of ids
    	 * @return boolean TRUE if the delete worked, else FALSE
    	 */
    	function deleteMulti(&$Model, $ids) {
    		if (is_array($ids) || is_numeric($ids)) {
    			// Do nothing, it is already an array or a single id
    		} else if (is_string($ids)) {
    			$ids = explode(' ', $ids);
    		}
    		return $Model->deleteAll(array(
    				$Model->alias . '.' . $Model->primaryKey => $ids));
    	}
    }
    ?>


For the example I have used two small tables. Users and Posts.


SQL:
````

::

    
    CREATE TABLE `posts` ( 
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
      `user_id` int(11) NOT NULL, 
      `title` VARCHAR(255)  NOT NULL, 
      `content` text NOT NULL
    ) ENGINE = MYISAM ;
    
    CREATE TABLE `users` ( 
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
      `role` varchar(50) NOT NULL, 
      `username` VARCHAR(255)  NOT NULL, 
      `firstname` VARCHAR(255)  NOT NULL, 
      `lastname` VARCHAR(255)  NOT NULL
    ) ENGINE = MYISAM ;



Post Model
----------
In my model

Model Class:
````````````

::

    <?php 
    var $actsAs = array('MultipleRecords');
    ?>



Posts Controller
----------------
In the post controller

Controller Class:
`````````````````

::

    <?php var $helpers = array('Form', 'MultipleRecords');?>



Posts action add
````````````````

Controller Class:
`````````````````

::

    <?php /**
     * Add one or more Posts
     *
     * @param int $count number of posts to add
     */
    function add($numberOfRecords = null) {
    	if (!empty($this->data)) {
    		// If there is data, but the count is not set, this is a save
    		// If there is data and a count, this is an add one more emthy, and not a save
    		if (!$numberOfRecords) {
    			$this->Post->create();
    			if ($this->Post->saveMulti($this->data)) {
    				$this->Session->setFlash(__('The Post(s) has been saved', true));
    				$this->redirect(array('action' => 'index'));
    			} else {
    				$this->Session->setFlash(__('The Post(s) could not be saved. Please, try again.', true));
    			}
    		}
    	}
    	
    	$this->set('numberOfRecords', $numberOfRecords);
    	$this->set('users', $this->Post->User->find('list'));
    }
    ?>

The if (!$numberOfRecords) is used to figure out if the user posted
the form, with or without a numberOfRecords. If there is data, and a
number or records, it is an add #n empty set request, then it is sent
back to the view. If it is not a numberOfRecords, it is a save and
redirect.



Posts action edit
`````````````````

Controller Class:
`````````````````

::

    <?php /**
     * Edit one ore more posts
     *
     * @param string $ids a list of ids, sent as get parameters on the form 2+4+7, if used internaly on the form '2  4 7'
     */
    function edit($ids = null) {
    	if (!$ids && empty($this->data)) {
    		$this->Session->setFlash(__('Invalid Post', true));
    		$this->redirect(array('action' => 'index'));
    	}
    	
    	if (!empty($this->data)) {
    		if ($this->Post->saveMulti($this->data)) {
    			$this->Session->setFlash(__('The Posts has been saved', true));
    			$this->redirect(array('action' => 'index'));
    		} else {
    			$this->Session->setFlash(__('The Posts could not be saved. Please, try again.', true));
    		}
    	} else {
    		$this->data = $this->Post->findMulti($ids);
    	}
    	
    	$this->set('users', $this->Post->User->find('list'));
    }?>



Posts action delete
```````````````````

Controller Class:
`````````````````

::

    <?php /**
     * Delete multiple dataset
     *
     * @param String $ids representing the ids to delete on the form '3 5 7' 
     * The URL is parsed so http://host/app/controller/delete/3+5+7 will match
     */
    function delete($ids = null) {
    	if (!$ids) {
    		$this->Session->setFlash(__('Invalid id for posts', true));
    		$this->redirect(array('action' => 'index'));
    	}
    	if ($this->Post->deleteMulti($ids)) {
    		$this->Session->setFlash(__('Posts deleted', true));
    	} else {
    		$this->Session->setFlash(__('Unable to delete all Posts', true));
    	}
    	$this->redirect(array('action' => 'index'));
    }?>



Post Views
----------

edit.ctp
````````

View Template:
``````````````

::

    <div class="posts form">
    <?php
        echo $form->create('Post', array('url'=>array('action' => 'edit')));
        echo $multipleRecords->inputs(array('id','user_id','title','content'));
        echo $form->end('Submit');
    ?>
    </div>



add.ctp
```````

View Template:
``````````````

::

    <div class="posts form" id="LaysAddForm">
    <?php
    echo $form->create('Post', array('action'=>'add'));
    echo $multipleRecords->inputs(array('user_id','title','content','legend'=>'Add Post %n'), $numberOfRecords);
    echo $multipleRecords->add('Add Post');
    echo $form->end('Submit');
    ?>
    </div>


`1`_|`2`_|`3`_|`4`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_

.. _Page 1: :///articles/view/4caea0e3-2e08-4ae3-b61e-492d82f0cb67#page-1
.. _Page 2: :///articles/view/4caea0e3-2e08-4ae3-b61e-492d82f0cb67#page-2
.. _Page 3: :///articles/view/4caea0e3-2e08-4ae3-b61e-492d82f0cb67#page-3
.. _Page 4: :///articles/view/4caea0e3-2e08-4ae3-b61e-492d82f0cb67#page-4
.. _http://bakery.cakephp.org/articles/view/how-to-create-multirecord-forms: http://bakery.cakephp.org/articles/view/how-to-create-multirecord-forms
.. meta::
    :title: Multirecord helper/behavior
    :description: CakePHP Article related to behavior,form,multirecord,eskil,Case Studies
    :keywords: behavior,form,multirecord,eskil,Case Studies
    :copyright: Copyright 2008 
    :category: case_studies

