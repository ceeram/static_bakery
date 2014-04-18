Uuid Behavior
=============

I have a requirement to use UUID's as primary keys. I was initially
using MySQL triggers to call uuid() on insert but in the end decided a
behavior makes more sense. This behavior adds a UUID to the field
specified in your models.


Installation
~~~~~~~~~~~~

#. Create app/models/behaviors/uuid.php using the code provided in the
   behavior section below.



Usage
~~~~~
Add the behavior name to the $actsAs array for your model. For
example, let's assume you have a model named Post (which maps to a
database table named posts). Edit your app/models/post.php file and
add $actsAs as follows:


Model Class:
````````````

::

    <?php 
    class Post extends AppModel {
        var $name = 'Post';
        var $actsAs = array('Uuid' => array('field' => 'id'));
    }
    ?>

That's all there is to it. When you use the function save() in your
model (native CakePHP function) the behavior will add a UUID to the
field specified. The field can be any you choose and defaults to id.

The field you choose to set a UUID on will need to be configured
appropriately in your database. For instance my id column definition
in MySQL looks like: `id` char(36) NOT NULL


Behavior
~~~~~~~~
Here's the code for the behavior. Save this as a file named uuid.php
in your app/models/behaviors folder.

::

    
    <?php
    /**
     * UUID Behavior class file.
     *
     * Model Behavior to support adding UUID's when a record is saved.
     *
     * This behavior implements the beforeSave() callback for updating the
     * specified field with a UUID. The actual randomness of the generated
     * UUID has not been tested. Use at your own risk.
     *
     * Usage in model:
     *
     * Add Uuid to the $actsAs array of your model:
     * var $actsAs = array('Uuid' => array('field' => 'id'));
     *
     * @filesource
     * @package     app
     * @subpackage  models.behaviors
     */
    
    /**
     * Add UUID behavior to a model.
     *
     * @author      Billy Gunn
     * @package     app
     * @subpackage  models.behaviors
     */
    class UuidBehavior extends ModelBehavior {
        /**
         * Default model settings
         */
        var $defaultSettings = array('field' => 'id');
    
        /**
         * Initiate behaviour for the model using settings.
         *
         * @param object $model    Model using the behaviour
         * @param array $settings    Settings to override for model.
         *
         * @access public
         */
        function setup(&$model, $settings = array()) {
            $field = $this->defaultSettings['field'];
    
            if (!empty($settings['field'])) {
                $field = $settings['field'];
            }
    
            if ($model->hasField($field)) {
                $this->settings[$model->name] = array('field' => $field);
            }
        }
    
        /**
         * Generates a pseudo-random UUID.
         * Slightly modified version of a function submitted to php.net:
         * http://us2.php.net/manual/en/function.com-create-guid.php#52354
         *
         * @access public
         */
        function uuid() {
            if (function_exists('com_create_guid')) {
                return com_create_guid();
            } else {
                mt_srand((double)microtime()*10000);
                $charid = md5(uniqid(rand(), true));
                $hyphen = chr(45);// "-"
                $uuid = substr($charid, 0, 8).$hyphen
                      . substr($charid, 8, 4).$hyphen
                      . substr($charid,12, 4).$hyphen
                      . substr($charid,16, 4).$hyphen
                      . substr($charid,20,12);
    
                return $uuid;
            }
        }
    
        /**
         * Run before a model is saved to add a UUID to a field.
         *
         * @param object $model    Model about to be saved.
         *
         * @access public
         */
        function beforeSave(&$model) {
            if ($this->settings[$model->name]) {
                $field = $this->settings[$model->name]['field'];
                if (!isset($model->data[$model->name][$field])) {
                    $model->data[$model->name][$field] = $this->uuid();
                }
            }
        }
    }
    ?>



Test Case
~~~~~~~~~
If you haven't already, read `Testing Models with CakePHP 1.2 test
suite`_ as a starting point for getting your test environment
configured.

Before continuing you should have the Uuid behavior and your test
environment installed and working. Create a file named
uuid_test_fixture.php in the app/tests/fixtures folder with the
following contents.

::

    
    <?php
    /**
     * Uuid Behavior test fixture class file.
     *
     * Fixture for tests in uuid Behavior.
     *
     * @filesource
     * @package     app
     * @subpackage  app.tests.fixtures
     */
    
    /**
     * Fixture used in tests for the Uuid behavior.
     *
     * @author      Billy Gunn
     * @package     app
     * @subpackage  app.tests.fixtures
     */
    class UuidTestFixture extends CakeTestFixture {
            var $name = 'UuidTest';
            var $fields = array('id' => array('type' => 'integer', 'key' => 'primary'),
                                'uuid' => array('type' => 'string', 'null' => false, 'length' => 36),
                                'name' => array('type' => 'string', 'null' => false),
                                'description' => array('type' => 'string', 'null' => false),
                                'created' => 'datetime',
                                'updated' => 'datetime'
            );
    
            var $records = array(
                    array ('id' => 1, 'uuid' => '758372bc-6fd4-102a-ae1c-00065becda85', 'name' => 'First record', 'description' => 'First record', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31')
            );
    }
    ?>

Next, create a file called uuid.test.php in the
app/tests/cases/behaviors folder with the following contents.

::

    
    <?php
    /**
     * Uuid Behavior test case.
     *
     * Test cases for Uuid Behavior.
     *
     * @filesource
     * @package     app
     * @subpackage  app.tests.cases.behaviors
     */
    
    /**
     * Model used in tests for Uuid.
     *
     * @author      Billy Gunn
     * @package     app
     * @subpackage  app.tests.cases.behaviors
     */
    class UuidTest extends CakeTestModel {
            var $name = 'UuidTest';
            var $actsAs = array('Uuid' => array('field' => 'uuid'));
    }
    
    /**
     * Uuid Behavior test case.
     *
     * @author      Billy Gunn
     * @package     app
     * @subpackage  app.tests.cases.behaviors
     */
    class UuidTestCase extends CakeTestCase {
    
            var $fixtures = array( 'uuid_test' );
    
        /**
         * testCreateRecord
         *
         * Create a new record and verify that a valid uuid
         * was added to the the uuid field.
         *
         * @access public
         * @return void
         */
        function testCreateRecord() {
    
            $this->Record =& new UuidTest();
    
            $data = array('UuidTest' => 
                       array('id' => 4, 
                             'uuid' => null, 
                             'name' => 'New record')
                    );
    
            $this->Record->save($data);
    
            $result = $this->Record->read(null, 4);
    
            $match = preg_match("/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/", $result['UuidTest']['uuid']);
    
            $this->assertEqual($match, 1);
        }
    
    
        /**
         * testUpdateRecord
         *
         * An existing record should not have its uuid updated on save
         *
         * @access public
         * @return void
         */
        function testUpdateRecord() {
    
            $this->Record =& new UuidTest();
    
            $data = array('UuidTest' => array ( 'id' => 1, 'uuid' => '758372bc-6fd4-102a-ae1c-00065becda85', 'description' => 'modified record'));
            $this->Record->save($data);
    
            $result = $this->Record->findAll(null, array('id', 'uuid', 'name', 'description'));
    
            $expected = array(
                    array('UuidTest' => array(
                          'id' => 1,
                          'uuid' => '758372bc-6fd4-102a-ae1c-00065becda85',
                          'name' => 'First record',
                          'description' => 'modified record'
                    ))
           );
    
           $this->assertEqual($result, $expected);
        }
    }
    ?>

Point your browser to: http:// /test.php. Once there, click on App
Test Cases, and then look for the option behaviors/uuid.test.php and
click it. You will see the results of the test in your browser.

.. _Testing Models with CakePHP 1.2 test suite: http://bakery.cakephp.org/articles/view/testing-models-with-cakephp-1-2-test-suite

.. author:: Divagater
.. categories:: articles, behaviors
.. tags:: UUID,behaviors,Behaviors

