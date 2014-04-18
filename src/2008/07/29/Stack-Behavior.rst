Stack Behavior
==============

This model behavior allows the use of the familiar stack data
structure in a CakePHP database environment. Supports push, pop, top,
and length methods, the core of any stack, along with a configurable
link field.
I decided to create a stack data structure as an exercise of learning
to write behaviors. The result is the code at the end of this article.

To use this behavior, the table you want to use this behavior with
must have an additional next column to store id's of other rows. So
for example if we implemented tags as a stack we would use the
following sql:

::

    create table tags (
        id integer primary key,
        tag varchar(255),
        nxt integer
    );

And our models/tag.php:

Model Class:
````````````

::

    <?php 
    class Tag extends AppModel {
        var $name = 'Tag';
        // since we use a non-standard next column, we need
        // to tell the behavior this
        var $actsAs = array('Stack'=>array('next'=>'nxt'));
    }
    ?>

And finally to use this in a controller, for example, we can do the
following:

Controller Class:
`````````````````

::

    <?php 
    function messWithTags {
        $this->Tag->push(array('Tag'=>array('name'=>'data structure')));
        $this->Tag->push(array('Tag'=>array('name'=>'boredom')));
        // prints 'boredom' tag
        pr($this->Tag->top());
        // prints and delete 'boredom' tag
        pr($this->Tag->pop());
        $this->Tag->push(array('Tag'=>array('name'=>'exciting')));
        // prints and deletes 'exciting' tag
        pr($this->Tag->pop());
        // prints 1
        pr($this->Tag->length());
        // finally empties out the stack, deleting and printing the 'data structure' tag
        pr($this->Tag->pop());
        // prints 0
        pr($this->Tag->length());
    }
    ?>

Of course all the entries in the Tag stack are persisted through
different controller views since the data is stored in the database,
unlike a standard stack where all the entries are stored in memory.

And now, without further ado, here is the code:

::

    <?php
    class StackBehavior extends ModelBehavior {
        var $__top = array();
        
        /**
         * Supports setting which field is the next column in the $actsAs definition
         * by setting the 'next' key to the desired field. Defaults to a 'next' field.
         */
        function setup(&$model, $settings=array()) {
            $defaults = array('next'=>'next');
            if (isset($settings[$model->alias])) {
                $this->settings[$model->alias] = array_merge($defaults, $settings[$model->alias]);
            } else {
                $this->settings[$model->alias] = $defaults;
            }
            $this->setTop($model);
        }
        
        function __getSetting(&$model, $key) {
            return $this->settings[$model->alias][$key];
        }
        
        function setTop(&$model) {
            $params = array('conditions'=>array($model->alias.'.'.$this->__getSetting($model, 'next')=>null));
            $this->__top = $model->find('first', $params);
        }
        
        function top(&$model) {
            return $this->__top;
        }
        
        /**
         * Get the number of rows currently in the stack.
         * @return int The number of rows in the stack.
         */
        function length(&$model) {
            // Dont have to traverse the list, and actually we cant since the
            // list points upward, and we dont care about any elements but the top,
            // so we just count the rows.
            return $model->find('count');
        }
        
        /**
         * Push data onto the end of the stack. Sets top to the newly formed
         * row with the specified data and sets old top to point to new.
         * @param $data Array of row data in the same format as returned by find().
         */
        function push(&$model, $data) {
            // Linked list points toward the top of the stack.
            // The top of the stack points to nothingness.
            // Makes it easier to retrieve the top on startup, simply
            // find the node with an empty next field.
            $data[$model->alias][$this->__getSetting($model, 'next')] = null;
            if ($model->save($data)) {
                $data[$model->alias][$model->primaryKey] = $model->id;
                // there is a top element, update the old top to point to the new top
                if ($this->__top !== null) {
                    $this->__top[$model->alias][$this->__getSetting($model, 'next')] = $model->id;
                    $model->save($this->__top);
                }
                // update the new top;
                $this->__top = $data;
            }
    
        }
        
        /**
         * Pops a row from the database, returning the deleted value.
         * @return array Row at the top of the stack in find() format.
         */
        function pop(&$model) {
            $top = $this->__top;
            $model->del($this->__top[$model->alias][$model->primaryKey]);
            // get the node whose next is the top, and make it new top
            $params = array('conditions'=>array($model->alias.'.'.$this->__getSetting($model, 'next')=>$top[$model->alias][$model->primaryKey]));
            $this->__top = $model->find('first', $params);
            // also set the new tops next to null
            $this->__top[$model->alias][$this->__getSetting($model, 'next')] = null;
            $model->save($this->__top);
            return $top;
        }
    } 
    ?>

And in case you don't trust me (I don't), here's the test:

::

    <?php
    class TestStack extends CakeTestModel {
        var $name = 'TestStack';
        var $cacheSources = false;
        var $actsAs = array('Stack'=>array('next'=>'next'));
        var $useDbConfig  = 'test_suite';
    }
    
    class StackTestCase extends CakeTestCase {
        var $fixtures = array('app.stack');
        var $Stack = null;
        
        function start() {
            parent::start();
        }
        
        function testSetup() {
            $this->Stack = new TestStack();
            $expected = array('TestStack'=>array(
                'id'=>3,
                'name'=>'C',
                'next'=>null,
            ));
            $this->assertEqual($expected, $this->Stack->top());
        }
        
        function testLength() {
            $this->Stack = new TestStack();
            $this->assertEqual(3, $this->Stack->length());
        }
        
        function testPush() {
            $this->Stack = new TestStack();
            $data = array('TestStack'=>array(
                'name'=>'D',
                'next'=>null,
            ));
            $this->Stack->push($data);
            $expected = array('TestStack'=>array(
                'id'=>4,
                'name'=>'D',
                'next'=>null,
            ));
            $top = $this->Stack->top();
            $this->assertEqual($expected, $top);
            $this->assertEqual(4, $this->Stack->length());
            
            $belowTop = $this->Stack->find('first', array('conditions'=>array('TestStack.next'=>$top['TestStack']['id'])));
            $expected = array('TestStack'=>array(
                'id'=>3,
                'name'=>'C',
                'next'=>4,
            ));
            $this->assertEqual($expected, $belowTop);
        }
        
        function testPop() {
            $this->Stack = new TestStack();
            $expected = array('TestStack'=>array(
                'id'=>3,
                'name'=>'C',
                'next'=>null,
            ));
            $this->assertEqual($expected, $this->Stack->pop());
            $expected = array('TestStack'=>array(
                'id'=>2,
                'name'=>'B',
                'next'=>null,
            ));
            $this->assertEqual($expected, $this->Stack->top());
        }
    }
    ?>

And the fixture to go along with it:

::

    <?php
    class StackFixture extends CakeTestFixture {
        var $name = 'Stack';
        var $table = 'test_stacks';
        var $fields = array(
            'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
            'name' => array('type'=>'string', 'null' => true, 'default' => NULL),
            'next' => array('type'=>'integer', 'null'=>true, 'default'=>NULL),
            'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
        );
        
        var $records = array(
            array(
                'id'=>1,
                'name'=>'A',
                'next'=>2,
            ),
            array(
                'id'=>2,
                'name'=>'B',
                'next'=>3,
            ),
            array(
                'id'=>3,
                'name'=>'C',
                'next'=>null,
            ),
        );
    }
    ?>



.. author:: stupergenius
.. categories:: articles, behaviors
.. tags:: ,Behaviors

