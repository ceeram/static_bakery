Soft Deletable Behavior
=======================

by %s on April 16, 2007

This behavior lets you implement soft delete for your records in your
models by introducing a flag to an existing table which indicates that
a row has been deleted, instead of deleting the record.
Using this behavior you can implement soft deletion in your CakePHP
models so no real data is lost when you issue a delete on a specific
record. Instead, a field of your choosing is used to indicate that a
record has been soft deleted, and the behavior will automatically
override your specific find operations so only non-soft deleted
records are fetched.


Download, Source Code and Bug Tracking
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The latest Soft Deletable Behavior release is 1.1.38 . For those of
you who wish to keep up with the latest (not necessarily stable) Soft
Deletable Behavior resides in the SVN repository of a project that
includes other CakePHP goodies: `Cake Syrup`_. All future official
releases will be posted on this article.

`Get Soft Deletable Behavior 1.1.38`_ (`Release Notes & Changelog`_)

All reports, enhancements and feature feedback should be provided
through the project page, and not in comments for this article, so I
can keep a closer track. Please do report any issues you find with
Soft Deletable Behavior using its tracker:

`Cake Syrup Tracker (Bugs / Features)`_
If you want to view the source code of the latest version of the Soft
Deletable Behavior you can do so using the SVN browser:
`soft_deletable.php`_

Installation
~~~~~~~~~~~~

#. Create a file named soft_deletable.php in your app/models/behaviors
   folder using the contents provided below.
#. For those models that will use this behavior, you need to identify
   which field holds the deleted marker (eg: deleted) that will be set to
   '1' when a record has been soft deleted, and optionally which field
   will hold the time it was soft deleted (eg: deleted_date)



Usage
~~~~~
The simplest way you can use this behavior is by adding its name to
the $actsAs array for your model. For example, let's assume you have a
model named Article (which maps to a database table named articles,
that has among other fields 'deleted' and 'deleted_date'). Then edit
your app/models/article.php file and add $actsAs as follows:


Model Class:
````````````

::

    <?php 
    class Article extends AppModel {
    	var $name = 'Article';
    	var $actsAs = array('SoftDeletable');
    }
    ?>

That's it! When you use the function del() in your model (or its alias
delete(), both native CakePHP functions) it will instead perform a
soft delete on the record, setting its 'deleted' field to 1 and if
there's a 'deleted_date' field it will also save the timestamp when it
was deleted there.)

To change those and other settings, you can specify an array of
settings right on your $actsAs property. Let's look at all the
available settings:


#. field : name of the field in the mapped database table that will
   hold the value '1' when a record is deleted, or the value '0' when it
   is not. This field should have a default value of '0'. Defaults to
   'deleted'.
#. field_date : name of the field in the mapped database table that
   will hold the timestamp when a record has been deleted. Defaults to
   'deleted_date'. This field is optional, and can be set to null.
   Defaults to 'deleted_date'.
#. delete : a boolean, set to true if you want to soft delete the
   record when you call del() on the model, or false if when calling
   del() record should be removed from database. Defaults to true.
#. find : a boolean, set to true to automatically add conditions so
   only non-deleted records are retrieved when performing any find()
   operation by using beforeFind(), or false otherwise. Defaults to true.

Any other attributes in the form of attribute => value that are
included on the configuration will be seen as setting fields (which
names are taken from the attribute name) to specific values when a
record is being soft deleted.

On our previous example, let us say that we want to also set the value
of the field 'published' to '0' when a record gets soft deleted, and
also wish to not have added the automatic conditions to only retrieve
non-deleted records. We do so by specifying the appropiate settings:


Model Class:
````````````

::

    <?php 
    class Article extends AppModel {
    	var $name = 'Article';
    	var $actsAs = array('SoftDelete' => array('find' => false, 'published' => '0'));
    }
    ?>



Overriding automatic find conditions and soft delete behavior
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you don't specify 'find' => false when setting the conditions for
the behavior (just as we did on our previous example), then
SoftDeleteBehavior will add specific conditions so that when you issue
a find() or findAll() on the model only non-deleted records are
returned. However, you can also disable this behavior alltogether
(like we did by setting 'find' to false), or disable it when you need.
There are different ways to disable this, one easy way is that you add
your own conditions when you do a find() or findAll() on the field
'deleted':


Controller Class:
`````````````````

::

    <?php 
    class ArticlesController extends AppController {
    	var $name = 'Articles';
    	
    	function index() {
    		$articles = $this->Article->findAll(array('Article.deleted' => array(0, 1)));
    		$this->set('articles', $articles);
    	}
    }
    ?>

On this case we're obtaining both soft-deleted and non-deleted records
by specifying that we wish to get those articles that have the
'deleted' field set to either 0 or 1.

Another way is to disable these automatic conditions on demand, and
then re-enable for any future queries. We do so by using the behavior
function called enableSoftDeletable(), which takes a boolean argument
that should be set to true when you want automatic conditions added,
or false otherwise:


Controller Class:
`````````````````

::

    <?php 
    class ArticlesController extends AppController {
    	var $name = 'Articles';
    	
    	function index() {
    		$this->Article->enableSoftDeletable(false);
    		$articles = $this->Article->findAll();
    		$this->Article->enableSoftDeletable(true);
    		
    		$this->set('articles', $articles);
    	}
    }
    ?>

We'll then get all records (both soft-deleted and non deleted.) Notice
than when calling enableSoftDeletable() with just one parameter you
are also disabling the automatic soft deletion of records. If you just
wish to override the conditions Soft Deletable adds to your find
operations then a safer approach is to tell the behavior to only
disable the find override:


Controller Class:
`````````````````

::

    <?php 
    class ArticlesController extends AppController {
    	var $name = 'Articles';
    	
    	function index() {
    		$this->Article->enableSoftDeletable('find', false);
    		$articles = $this->Article->findAll();
    		$this->Article->enableSoftDeletable('find', true);
    		
    		$this->set('articles', $articles);
    	}
    }
    ?>

If you want to pemanently remove the record when calling del() on the
model that holds it (and since default behavior would be to soft-
delete the record), then you can override the behavior for method
'delete' by setting:


Controller Class:
`````````````````

::

    <?php 
    class ArticlesController extends AppController {
    	var $name = 'Articles';
    	
    	function index() {
    		$this->Article->enableSoftDeletable('delete', false);
    		$this->Article->del(1);
    	}
    }
    ?>

You can also use the provided hardDelete method to keep it simpler:


Controller Class:
`````````````````

::

    <?php 
    class ArticlesController extends AppController {
    	var $name = 'Articles';
    	
    	function index() {
    		$this->Article->hardDelete(1);
    	}
    }
    ?>

If you want to purge (permanently delete) all soft deleted records you
can also use the method purge:


Controller Class:
`````````````````

::

    <?php 
    class ArticlesController extends AppController {
    	var $name = 'Articles';
    	
    	function index() {
    		$this->Article->purge();
    	}
    }
    ?>



Undeleting a record
~~~~~~~~~~~~~~~~~~~

When a record has been deleted on a model that has the SoftDelete
behavior applied, then that record is not really being deleted.
Instead, as we've seen, a specific field on the table is set to 1 to
indicate that is deleted, and conditions are added to any find() call
to make sure that only records which have that field set to any value
other than 1 are returned. Therefore, we can safely undelete a record
by using the behavior method undelete().

On the following example we start by deleting a record, then obtaining
all records, and then undeleting that record. We use debug() instead
of proper CakePHP behavior just to show how it can be used from your
controllers:


PHP Snippet:
````````````

::

    <?php 
    // Soft-delete article with ID 1
    
    $this->Article->del(1);
    
    // Show all articles (automatic conditions are on, 
    // so only non-deleted articles are obtained)
    
    debug($this->Article->findAll());
    
    // Undelete previously deleted article
    
    $this->Article->undelete(1);
    
    // Show all articles
    
    debug($this->Article->findAll());
    ?>



Behavior
~~~~~~~~

Here's the code for the behavior. Save this as a file named
soft_deletable.php in your app/models/behaviors folder. In the
following section you can also find how to set up test cases for this
behavior.


Behavior Class:
```````````````

::

    <?php 
    /* SVN FILE: $Id: soft_deletable.php 38 2007-11-26 19:36:27Z mgiglesias $ */
    
    /**
     * SoftDeletable Behavior class file.
     *
     * @filesource
     * @author Mariano Iglesias
     * @link http://cake-syrup.sourceforge.net/ingredients/soft-deletable-behavior/
     * @version	$Revision: 38 $
     * @license	http://www.opensource.org/licenses/mit-license.php The MIT License
     * @package app
     * @subpackage app.models.behaviors
     */
    
    /**
     * Model behavior to support soft deleting records.
     *
     * @package app
     * @subpackage app.models.behaviors
     */
    class SoftDeletableBehavior extends ModelBehavior
    {
    	/**
    	 * Contain settings indexed by model name.
    	 *
    	 * @var array
    	 * @access private
    	 */
    	var $__settings = array();
    
    	/**
    	 * Initiate behaviour for the model using settings.
    	 *
    	 * @param object $Model Model using the behaviour
    	 * @param array $settings Settings to override for model.
    	 * @access public
    	 */
    	function setup(&$Model, $settings = array())
    	{
    		$default = array('field' => 'deleted', 'field_date' => 'deleted_date', 'delete' => true, 'find' => true);
    
    		if (!isset($this->__settings[$Model->alias]))
    		{
    			$this->__settings[$Model->alias] = $default;
    		}
    
    		$this->__settings[$Model->alias] = am($this->__settings[$Model->alias], ife(is_array($settings), $settings, array()));
    	}
    
    	/**
    	 * Run before a model is deleted, used to do a soft delete when needed.
    	 *
    	 * @param object $Model Model about to be deleted
    	 * @param boolean $cascade If true records that depend on this record will also be deleted
    	 * @return boolean Set to true to continue with delete, false otherwise
    	 * @access public
    	 */
    	function beforeDelete(&$Model, $cascade = true)
    	{
    		if ($this->__settings[$Model->alias]['delete'] && $Model->hasField($this->__settings[$Model->alias]['field']))
    		{
    			$attributes = $this->__settings[$Model->alias];
    			$id = $Model->id;
    
    			$data = array($Model->alias => array(
    				$attributes['field'] => 1
    			));
    
    			if (isset($attributes['field_date']) && $Model->hasField($attributes['field_date']))
    			{
    				$data[$Model->alias][$attributes['field_date']] = date('Y-m-d H:i:s');
    			}
    
    			foreach(am(array_keys($data[$Model->alias]), array('field', 'field_date', 'find', 'delete')) as $field)
    			{
    				unset($attributes[$field]);
    			}
    
    			if (!empty($attributes))
    			{
    				$data[$Model->alias] = am($data[$Model->alias], $attributes);
    			}
    
    			$Model->id = $id;
    			$deleted = $Model->save($data, false, array_keys($data[$Model->alias]));
    
    			if ($deleted && $cascade)
    			{
    				$Model->_deleteDependent($id, $cascade);
    				$Model->_deleteLinks($id);
    			}
    
    			return false;
    		}
    
    		return true;
    	}
    
    	/**
    	 * Permanently deletes a record.
    	 *
    	 * @param object $Model Model from where the method is being executed.
    	 * @param mixed $id ID of the soft-deleted record.
    	 * @param boolean $cascade Also delete dependent records
    	 * @return boolean Result of the operation.
    	 * @access public
    	 */
    	function hardDelete(&$Model, $id, $cascade = true)
    	{
    		$onFind = $this->__settings[$Model->alias]['find'];
    		$onDelete = $this->__settings[$Model->alias]['delete'];
    		$this->enableSoftDeletable($Model, false);
    
    		$deleted = $Model->del($id, $cascade);
    
    		$this->enableSoftDeletable($Model, 'delete', $onDelete);
    		$this->enableSoftDeletable($Model, 'find', $onFind);
    
    		return $deleted;
    	}
    
    	/**
    	 * Permanently deletes all records that were soft deleted.
    	 *
    	 * @param object $Model Model from where the method is being executed.
    	 * @param boolean $cascade Also delete dependent records
    	 * @return boolean Result of the operation.
    	 * @access public
    	 */
    	function purge(&$Model, $cascade = true)
    	{
    		$purged = false;
    
    		if ($Model->hasField($this->__settings[$Model->alias]['field']))
    		{
    			$onFind = $this->__settings[$Model->alias]['find'];
    			$onDelete = $this->__settings[$Model->alias]['delete'];
    			$this->enableSoftDeletable($Model, false);
    
    			$purged = $Model->deleteAll(array($this->__settings[$Model->alias]['field'] => '1'), $cascade);
    
    			$this->enableSoftDeletable($Model, 'delete', $onDelete);
    			$this->enableSoftDeletable($Model, 'find', $onFind);
    		}
    
    		return $purged;
    	}
    
    	/**
    	 * Restores a soft deleted record, and optionally change other fields.
    	 *
    	 * @param object $Model Model from where the method is being executed.
    	 * @param mixed $id ID of the soft-deleted record.
    	 * @param $attributes Other fields to change (in the form of field => value)
    	 * @return boolean Result of the operation.
    	 * @access public
    	 */
    	function undelete(&$Model, $id = null, $attributes = array())
    	{
    		if ($Model->hasField($this->__settings[$Model->alias]['field']))
    		{
    			if (empty($id))
    			{
    				$id = $Model->id;
    			}
    
    			$data = array($Model->alias => array(
    				$Model->primaryKey => $id,
    				$this->__settings[$Model->alias]['field'] => '0'
    			));
    
    			if (isset($this->__settings[$Model->alias]['field_date']) && $Model->hasField($this->__settings[$Model->alias]['field_date']))
    			{
    				$data[$Model->alias][$this->__settings[$Model->alias]['field_date']] = null;
    			}
    
    			if (!empty($attributes))
    			{
    				$data[$Model->alias] = am($data[$Model->alias], $attributes);
    			}
    
    			$onFind = $this->__settings[$Model->alias]['find'];
    			$onDelete = $this->__settings[$Model->alias]['delete'];
    			$this->enableSoftDeletable($Model, false);
    
    			$Model->id = $id;
    			$result = $Model->save($data, false, array_keys($data[$Model->alias]));
    
    			$this->enableSoftDeletable($Model, 'find', $onFind);
    			$this->enableSoftDeletable($Model, 'delete', $onDelete);
    
    			return ($result !== false);
    		}
    
    		return false;
    	}
    
    	/**
    	 * Set if the beforeFind() or beforeDelete() should be overriden for specific model.
    	 *
    	 * @param object $Model Model about to be deleted.
    	 * @param mixed $methods If string, method (find / delete) to enable on, if array array of method names, if boolean, enable it for find method
    	 * @param boolean $enable If specified method should be overriden.
    	 * @access public
    	 */
    	function enableSoftDeletable(&$Model, $methods, $enable = true)
    	{
    		if (is_bool($methods))
    		{
    			$enable = $methods;
    			$methods = array('find', 'delete');
    		}
    
    		if (!is_array($methods))
    		{
    			$methods = array($methods);
    		}
    
    		foreach($methods as $method)
    		{
    			$this->__settings[$Model->alias][$method] = $enable;
    		}
    	}
    
    	/**
    	 * Run before a model is about to be find, used only fetch for non-deleted records.
    	 *
    	 * @param object $Model Model about to be deleted.
    	 * @param array $queryData Data used to execute this query, i.e. conditions, order, etc.
    	 * @return mixed Set to false to abort find operation, or return an array with data used to execute query
    	 * @access public
    	 */
    	function beforeFind(&$Model, $queryData)
    	{
    		if ($this->__settings[$Model->alias]['find'] && $Model->hasField($this->__settings[$Model->alias]['field']))
    		{
    			$Db =& ConnectionManager::getDataSource($Model->useDbConfig);
    			$include = false;
    
    			if (!empty($queryData['conditions']) && is_string($queryData['conditions']))
    			{
    				$include = true;
    
    				$fields = array(
    					$Db->name($Model->alias) . '.' . $Db->name($this->__settings[$Model->alias]['field']),
    					$Db->name($this->__settings[$Model->alias]['field']),
    					$Model->alias . '.' . $this->__settings[$Model->alias]['field'],
    					$this->__settings[$Model->alias]['field']
    				);
    
    				foreach($fields as $field)
    				{
    					if (preg_match('/^' . preg_quote($field) . '[\s=!]+/i', $queryData['conditions']) || preg_match('/\\x20+' . preg_quote($field) . '[\s=!]+/i', $queryData['conditions']))
    					{
    						$include = false;
    						break;
    					}
    				}
    			}
    			else if (empty($queryData['conditions']) || (!in_array($this->__settings[$Model->alias]['field'], array_keys($queryData['conditions'])) && !in_array($Model->alias . '.' . $this->__settings[$Model->alias]['field'], array_keys($queryData['conditions']))))
    			{
    				$include = true;
    			}
    
    			if ($include)
    			{
    				if (empty($queryData['conditions']))
    				{
    					$queryData['conditions'] = array();
    				}
    
    				if (is_string($queryData['conditions']))
    				{
    					$queryData['conditions'] = $Db->name($Model->alias) . '.' . $Db->name($this->__settings[$Model->alias]['field']) . '!= 1 AND ' . $queryData['conditions'];
    				}
    				else
    				{
    					$queryData['conditions'][$Model->alias . '.' . $this->__settings[$Model->alias]['field']] = '!= 1';
    				}
    			}
    		}
    
    		return $queryData;
    	}
    
    	/**
    	 * Run before a model is saved, used to disable beforeFind() override.
    	 *
    	 * @param object $Model Model about to be saved.
    	 * @return boolean True if the operation should continue, false if it should abort
    	 * @access public
    	 */
    	function beforeSave(&$Model)
    	{
    		if ($this->__settings[$Model->alias]['find'])
    		{
    			if (!isset($this->__backAttributes))
    			{
    				$this->__backAttributes = array($Model->alias => array());
    			}
    			else if (!isset($this->__backAttributes[$Model->alias]))
    			{
    				$this->__backAttributes[$Model->alias] = array();
    			}
    
    			$this->__backAttributes[$Model->alias]['find'] = $this->__settings[$Model->alias]['find'];
    			$this->__backAttributes[$Model->alias]['delete'] = $this->__settings[$Model->alias]['delete'];
    			$this->enableSoftDeletable($Model, false);
    		}
    
    		return true;
    	}
    
    	/**
    	 * Run after a model has been saved, used to enable beforeFind() override.
    	 *
    	 * @param object $Model Model just saved.
    	 * @param boolean $created True if this save created a new record
    	 * @access public
    	 */
    	function afterSave(&$Model, $created)
    	{
    		if (isset($this->__backAttributes[$Model->alias]['find']))
    		{
    			$this->enableSoftDeletable($Model, 'find', $this->__backAttributes[$Model->alias]['find']);
    			$this->enableSoftDeletable($Model, 'delete', $this->__backAttributes[$Model->alias]['delete']);
    			unset($this->__backAttributes[$Model->alias]['find']);
    			unset($this->__backAttributes[$Model->alias]['delete']);
    		}
    	}
    }
    ?>



Test Case
~~~~~~~~~
First of all, follow instructions on how to set up your CakePHP test
suite by reading the section Installation on the article `Testing
Models with CakePHP 1.2 test suite`_.

Once you have your test environment setup and you have installed the
Soft Deletable behavior as was instructed on previous section, create
a file named deletable_article_fixture.php in your app/tests/fixtures
folder with the contents shown on the following link:

`deletable_article_fixture.php`_
Next, create a file named deletable_comment_fixture.php in your
app/tests/fixtures folder with the contents shown on the following
link:

`deletable_comment_fixture.php`_
Now create a file named soft_deletable.test.php and place it on your
app/tests/cases/behaviors folder with the contents shown on the
following link:

`soft_deletable.test.php`_
Run your test by accessing the URL (replace example.com with your own
server address): `http://www.example.com/test.php`_. Once there, click
on App Test Cases , and then look for the option
behaviors/soft_deletable.test.php and click it. You will see the
results of the test on your browser.


.. _ Changelog: http://sourceforge.net/project/shownotes.php?release_id=557125&group_id=209331
.. _Get Soft Deletable Behavior 1.1.38: http://sourceforge.net/project/showfiles.php?group_id=209331&package_id=253339&release_id=557125
.. _Testing Models with CakePHP 1.2 test suite: http://bakery.cakephp.org/articles/view/324
.. _Cake Syrup Tracker (Bugs / Features): http://sourceforge.net/tracker/?group_id=209331
.. _deletable_comment_fixture.php: http://cake-syrup.svn.sourceforge.net/viewvc/cake-syrup/trunk/app/tests/fixtures/deletable_comment_fixture.php?view=markup
.. _soft_deletable.test.php: http://cake-syrup.svn.sourceforge.net/viewvc/cake-syrup/trunk/app/tests/cases/behaviors/soft_deletable.test.php?view=markup
.. _soft_deletable.php: http://cake-syrup.svn.sourceforge.net/viewvc/cake-syrup/trunk/app/models/behaviors/soft_deletable.php?view=markup
.. _deletable_article_fixture.php: http://cake-syrup.svn.sourceforge.net/viewvc/cake-syrup/trunk/app/tests/fixtures/deletable_article_fixture.php?view=markup
.. _http://www.example.com/test.php: http://www.example.com/test.php
.. _Cake Syrup: http://cake-syrup.sourceforge.net/
.. meta::
    :title: Soft Deletable Behavior
    :description: CakePHP Article related to actsas,behavior,Delete,soft,deletable,Behaviors
    :keywords: actsas,behavior,Delete,soft,deletable,Behaviors
    :copyright: Copyright 2007 
    :category: behaviors

