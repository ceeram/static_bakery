An Akismet Behavior
===================

by %s on March 09, 2009

There have been a lot of tools written in the past to help CakePHP
apps deal with comment spam. This is yet another.

Where many code snippets have addressed Akismet with a model, a
datasource or a component, it is my opinion that the best fit is as a
Behavior.


Configuration
~~~~~~~~~~~~~

The behavior expects the following configuration values to be set:

+ Akismet.key : provided by Akismet
+ Akismet.blog : the base url to your site.
+ Akismet.timeout : in seconds - optional - defaults to 3



Setting up the behavior in your Model
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The params array can be used to map Akismet fields to Model fields and
activate additional options.

Model Class:
````````````

::

    <?php 
    class Comment extends AppModel {
    
    	var $actsAs = array('Akismet' => array(
    			'content'=>'body',
    			'author'=>'User.name',
    			'type'=>false,
    			'owner'=>'owner_id',
    			'is_spam'=>'spam'
    	));
    }
    ?>

In the above example we map the required field of 'content' to the
'Comment.body' field, 'author' to a field in the associated User
model, protected the 'type' field from being submitted, added a custom
'owner' field and activated the automagic trigger to save the results
to the 'Comment.spam' field(see below).

The fields that the behavior looks for to send to Akismet have the
following default names, but can be mapped to local Model fields or,
with the exception of the 'content' field, to a field in an associated
model:

+ content
+ author
+ author_email
+ author_url
+ type

If the field doesn't exist in the submitted data it will be skipped
quietly. If the field does exist but you do not want it submitted, map
it to false, like in the above example. Additional fields can be added
if there is something else you'd like submitted.


notSpam(): sending a comment to Akismet for testing
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

notSpam() Parameters:

+ $data placeholder to meet custom validation requirements. Ignored
  here.
+ $strict whether or not to report errors communicating with Akismet
  as spam.

This is the heart of the behavior. There are three ways to use this
method.

1. Using it as a validation rule in the model:
``````````````````````````````````````````````

::

    <?php var $validate = array('body'=>array('notSpam'=>array('rule'=>array('notSpam', true)))); ?>


2. Calling it directly from a Model method or the controller:
`````````````````````````````````````````````````````````````

::

    <?php
    	$this->Comment->set($this->data);
    	if ($this->Comment->notSpam(null, true)) {
    		...
    	}
    ?>


3. Using the automagic of the 'is_spam' parameter
`````````````````````````````````````````````````

Map the name of a local Model field to the 'is_spam' param to trigger
the method in the beforeSave() callback and add the results to the
mapped field.

::

    <?php var $actsAs = array('Akismet'=>array('content'=>'body', 'is_spam'=>'spam')); ?>

This example will save 0 on clear, 1 on spam or null on error, to the
'spam' field. If you need to suppress this behavior, be sure to leave
the mapped field out of the $whitelist param in the save call.


markAs(): changing the status of a comment previously tested
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

markAs() Parameters:

+ $type 'spam' or 'ham' to set the status.
+ $report whether or not to report the change back to Akismet. Only
  useful when 'is_spam' parameter is set.

You can change the results on spam missed by Akismet or those caught
as a false positive. For example, from the controller:

::

    <?php
    	$this->Comment->set($this->data);
    	$this->Comment->markAs('spam');
    ?>

If you are using the 'is_spam' magic as described above, the field in
the database will be updated as well.


The code is kept in svn at `http://code.huffduff.net/svn/hoard/trunk/m
odels/behaviors/akismet.php`_.
The test case can be found at `http://code.huffduff.net/svn/hoard/trun
k/tests/cases/behaviors/akismet.test.php`_ using a generic spam
comment fixture available at `http://code.huffduff.net/svn/hoard/trunk
/tests/fixtures/spam_comment_fixture.php`_

Here is the code as of this writing(svn r.5):

Model Class:
````````````

::

    <?php 
    /**
     * Akismet Behavior for CakePHP.
     *
     * Creates an easy interface for sending comments to Akismet to test for spam.
     * Can be used in validation or to automatically flag a field in beforeSave.
     *
     * PHP versions 4 and 5
     *
     * @filesource
     * @copyright Copyright 2009, HuffDuff.net (http://www.huffduff.net/)
     * @link http://svn.huffduff.net/repos/hoard
     * @package hoard
     * @subpackage hoard.models.behaviors
     * @version $Revision: 5 $
     * @license http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    /**
     * Greatly simplifies the testing of comments using the Akismet service.
     *
     * To configure, either create an APP/config/akismet.php config file, or set the
     * following Configure keys directly, preferably in app/config/bootstrap.php:
     * 		Akismet.key = <your api key from wordpress.com> (required)
     * 		Akismet.url = <base url of your blog> (required)
     * 		Akismet.timeout = <seconds until timeout> (optional - defaults to 3)
     */
    class AkismetBehavior extends ModelBehavior {
    /**
     * Version number of this file, used in the User-Agent
     *
     * @var string
     * @access protected
     */
    	var $_version = '1.0';
    /**
     * Whether callbacks should be triggered or not
     *
     * @var boolean
     * @access protected
     */
    	var $_skip = false;
    /**
     * Instance of the HttpSocket used for Akismet calls, or false on failure.
     *
     * @var object
     * @access private
     */
    	var $__conn = null;
    /**
     * $settings map Akismet fields to Model fields. If a field is assigned to
     * $settings['is_spam'] it will be used automatically in the beforeSave().
     *
     * @see cake/libs/model/ModelBehavior#setup($model, $config)
     */
    	function setup(&$model, $settings) {
    		$map = array('author', 'author_email', 'author_url', 'content', 'type');
    		$settings = array_merge(array_combine($map, $map), $settings);
    		$this->settings[$model->alias] = array_filter($settings);
    	}
    /**
     * If $settings['is_spam'] has a Model field assigned and that field is not set
     * in the submitted data, a call will be made to Akismet automatically and the
     * result will be added to the save data for that field.
     *
     * @see cake/libs/model/ModelBehavior#beforeSave($model)
     */
    	function beforeSave(&$model) {
    		if ($this->_skip || !isset($this->settings[$model->alias]['is_spam'])) {
    			return true;
    		}
    		$field = $this->settings[$model->alias]['is_spam'];
    		if (!$model->hasField($field)) {
    			return true;
    		}
    		if (empty($model->whitelist) || in_array($field, $model->whitelist)) {
    			$isSpam = $this->notSpam($model, null, null);
    			if (!is_null($isSpam)) {
    				$isSpam = intval(!$isSpam);
    			} elseif ($schema = $model->schema($field)) {
    				if (empty($schema['null'])) {
    					$isSpam = $schema['default'];
    				}
    			}
    			$model->data[$model->alias][$field] = $isSpam;
    		}
    		return true;
    	}
    /**
     * Gets a success/fail response from Akismet.  Can be used as a model field
     * validation rule. Data should be set in the Model using Model::set()
     * NOTE: DO NOT use in conjunction with $settings['is_spam'] as it will cause
     * redundant calls to Akismet and is generally unnecessary anyway.
     *
     * @param object $model Model using this behavior
     * @param mixed $data Placeholder to match validation needs. Ignored here.
     * @param boolean $strict Whether a failed call is treated as spam or not.
     * @return boolean
     * @access public
     */
    	function notSpam(&$model, $data = null, $strict = false) {
    		$result = $this->__query($model, 'comment-check');
    		if ($result !== false) {
    			return ($result == 'false');
    		}
    		if (!is_null($strict)) {
    			return (!$strict);
    		}
    		return null;
    	}
    /**
     * Method to switch the status of a message as reported by Akismet.
     * If $settings['is_spam'] is assigned a model field, it will be updated.
     * Data should be set in the Model using Model::set()
     *
     * @param object $model Model using this behavior
     * @param string $type Mark message as 'ham' or 'spam'
     * @param	boolean $report Whether to make a call to Akismet about the change
     * @return boolean
     * @access public
     */
    	function markAs(&$model, $type, $report = true) {
    		if (!empty($this->settings[$model->alias]['is_spam'])) {
    			$field = $this->settings[$model->alias]['is_spam'];
    			if ($model->hasField($field)) {
    				$data = $model->data;
    				$this->_skip = true;
    				$ok = $model->saveField($field, intval($type != 'ham'), false);
    				$model->set($data);
    				$this->_skip = false;
    			}
    		}
    		if (!$report) {
    			return (isset($ok) && $ok);
    		}
    		$path = ($type == 'ham') ? 'submit-ham' : 'submit-spam';
    		return ($this->__query($model, $path) !== false);
    	}
    /**
     * Make the actual call to Akismet
     *
     * @param object $model Model using this behavior
     * @param string $path Akismet method to call
     * @return mixed
     * @access private
     */
    	function __query(&$model, $method) {
    		$request = $model->data;
    		$post = array();
    		foreach ($this->settings[$model->alias] as $label=>$field) {
    			if ($label != 'is_spam') {
    				$alias = $model->alias;
    				if (strpos($field, '.')) {
    					list($alias, $field) = explode('.', $field, 2);
    				}
    				if (!empty($request[$alias][$field])) {
    					$post['comment_' . $label] = $request[$alias][$field];
    				}
    			}
    		}
    
    		if (empty($post) || $this->__conn === false) {
    			return false;
    		}
    		if (!$this->__conn) {
    			if (!$auth = Configure::read('Akismet')) {
    				Configure::load('akismet');
    				if (!$auth = Configure::read('Akismet')) {
    					return $this->__conn = false;
    				}
    			}
    			App::import('Core', 'HttpSocket');
    			$userAgent = 'CakePHP/' . Configure::version();
    			$userAgent .= ' | AkismetBehavior/' . $this->_version;
    			$request = array(
    				'uri' => 'http://' . $auth['key'] . '.rest.akismet.com',
    				'header' => array('User-Agent' => $userAgent)
    			);
    			$timeout = (!empty($auth['timeout'])) ? $auth['timeout'] : 3;
    			$this->__conn =& new HttpSocket(compact('request', 'timeout'));
    			if ($this->__conn->post('/1.1/verify-key', $auth) != 'valid') {
    				return $this->__conn = false;
    			}
    			$this->__conn->config['request']['body'] = array(
    				'blog' => $auth['blog'],
    				'user_ip' => env('REMOTE_ADDR'),
    				'user_agent' => env('HTTP_USER_AGENT'),
    				'referrer' => env('HTTP_REFERER')
    			);
    		}
    
    		return $this->__conn->post('/1.1/' . $method, $post);
    	}
    }
    ?>

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _http://code.huffduff.net/svn/hoard/trunk/tests/cases/behaviors/akismet.test.php: http://code.huffduff.net/svn/hoard/trunk/tests/cases/behaviors/akismet.test.php
.. _http://code.huffduff.net/svn/hoard/trunk/tests/fixtures/spam_comment_fixture.php: http://code.huffduff.net/svn/hoard/trunk/tests/fixtures/spam_comment_fixture.php
.. _http://code.huffduff.net/svn/hoard/trunk/models/behaviors/akismet.php: http://code.huffduff.net/svn/hoard/trunk/models/behaviors/akismet.php
.. _Page 1: :///articles/view/4caea0e4-67dc-4c8d-b5fc-4d4982f0cb67#page-1
.. _Page 2: :///articles/view/4caea0e4-67dc-4c8d-b5fc-4d4982f0cb67#page-2
.. meta::
    :title: An Akismet Behavior
    :description: CakePHP Article related to comment,akismet,spam detection,Behaviors
    :keywords: comment,akismet,spam detection,Behaviors
    :copyright: Copyright 2009 
    :category: behaviors

