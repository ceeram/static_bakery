

RowObjectBehavior Row Data Gateway implementation
=================================================

by %s on January 13, 2009

"A Row Data Gateway gives you objects that look exactly like the
record in your record structure but can be accessed with the regular
mechanisms of your programming language. All details of data source
access are hidden behind this interface." - Martin Fowler
(http://www.martinfowler.com/eaaCatalog/rowDataGateway.html)
Download: `http://freenity.googlecode.com/files/RowObjectBehavior%20v.
%200.2.rar`_
Note: The version 0.2 was release, please update.

Model represents a table, it's a container which puts all the data
retrieval logic inside it. The row level, that is a single record in
this model should contain row level access logic. Currently we can
obtain an array representing the row. We can only get data, what in
most cases is fine, but it violates OOP principles by separating
operations from data. One of the very useful design pattern we could
implement is Row Data Gateway, that is represent a row as an object.

RowObjectBehavior does exactly this. By allowing you create objects
that will represent your rows. It uses the afterFind() method to
convert the array results into an object and adds some CRUD methods.

By just including the behavior you will get two functions that will
operate on this record level: save() and remove()


Model Class:
````````````

::

    <?php 
    class TestModel extends AppModel {
        var $actsAs = array('RowObject' => array('rowClass' => 'TestRow'));
    }
    ?>

By setting rowClass we could set our own row class which has to be
created in models/ directory. This class should extends from AppRow
class. If nothing is specified in the rowClass option, AppRow is used.

AppRow works just like AppModel or AppController, it contains all the
methods shared among all the row classes. This is an optional class,
and it can be created in the app folder as app_row.php AppRow class
must extend Row.

Some examples:

::

    
    public function showSomeFeatures() {
        $user = $this->findById(2);
        $user->name = 'Another name';
        $user->save();
        $user->remove();
    }

More examples are available at
`http://code.google.com/p/freenity/wiki/RowObjectBehavior`_
NOTE: most of the time you can work with models without using this, as
the model will do it's job just fine, but there are some use cases
where having this kind of abstraction is really useful specially for
application maintaining.


/app/models/behaviors/row_object.php

Behavior Class:
```````````````

::

    <?php 
    
    /**
     * RowObject Behavior is an implementation of a very simple Row Data Gateway
     * @link http://www.martinfowler.com/eaaCatalog/rowDataGateway.html
     * This behavior operates at row level, not like model which operates on a table level
     * By including this behavior, it will transform all the results fetched with find()
     * into objects, that will have some basic crud functions
     * (for now: save() - to update row data
     *           remove() - remove the row.)
     * To have your own object representing a row, set rowClass option for the bahavior.
     * @example $actsAc = array('RowObject' => array('rowClass' => 'MyRow'));
     * MyRow must be places into models folder my_row.php file.
     * MyRow class must extend AppRow class.
     *
     * You can also create your own AppRow class by creating app_row.php file in the
     * app folder, just like AppModel or AppController, methods in this class with
     * be shared among all the Row classes.
     * AppRow class must extend Row class.
     *
     * @example
     * $row = $this->findById(1);
     * $row->name = 'Another Name';
     * $row->save(); //saved.
     * $row->remove(); // removed.
     *
     * $rows = $this->findByActive(0); // find all the inactive users.
     * foreach ($rows as $row) {
     *      $row->remove();
     * }
     *
     * This becomes much more useful when you create your own row classes and put
     * row manipulation logic.
     *
     * @author Anton Galitch (freenity)
     * @version 0.2 2008.12.30
     */
    
    
    /**
     * This is the class that contains basic methods that will operate on row level
     * AppRow should extend this class.
     */
    class Row {
        /**
         *
         * @var Model used to operate on the model
         */
        private static $model = null;
        /**
         *
         * @var array - Table collumns.
         */
        private $fields = null;
        
    
        /**
         * Saves the changes made to the row.
         * IMPORTANT: the primary key shouldn't be changed at all;
         *
         * @return boolean true on success, false on fail.
         */
        public function save() {
            $record = array(
                self::$model->name => array()
            );
    
            foreach ($this->getFields() as $field) {
                $record[self::$model->name][$field] = $this->{$field};
            }
            
            return self::$model->save($record);
        }
    
        /**
         * Removes current row.
         *
         * @return boolean True on success, false of fail.
         */
        public function remove() {
            return self::$model->remove($this->{self::$model->primaryKey});
        }
    
        /**
         * This method is the inverse to toArray(), it will receive an array, and
         * set all the needed fields with it's values.
         *
         * @param array Data that will populate the object.
         * @return boolean true on success, false if fail
         */
        public function setFromArray($data) {
            foreach ($this->getFields() as $field) {
                if (isset($data[$field])) {
                    $this->$field = $data[$field];
                }
            }
            return true;
        }
    
        /**
         * Converts all the fields contained in this object into an array, so Model
         * can use it.
         */
        public function toArray() {
            $res = array();
            foreach ($this->getFields() as $field) {
                $res[$field] = $this->$field;
            }
            return $res;
        }
    
    
        /**
         * @param Model model that is used. This is set automatically,
         *              so it shouldn't be used at all.
         */
        public static function setModel(Model $model) {
            self::$model = $model;
        }
    
        private function getFields() {
            if ($this->fields === null) {
                $this->fields = array_keys(self::$model->schema());
            }
            return $this->fields;
        }
    }
    
    if (file_exists(APP . 'app_row.php')) {
        include_once(APP . 'app_row.php');
    }
    
    /**
     * Used in case app_row.php doesn't exist
     */
    
    if (!class_exists('AppRow')) {
        class AppRow extends Row {}
    }
    
    
    class RowObjectBehavior extends ModelBehavior {
    
        private $rowModel = null;
    
        /**
         *
         * Initializes the whole behavior. If rowClass options is not defined,
         * it will use app_row.php located in the app folder, if this file doesn't
         * exist it will finally use the Row class defined above.
         */
        public function setUp($model, $config = array()) {
            if (isset($config['rowClass'])) {
                $this->rowModel = $config['rowClass'];
                $filename = Inflector::underscore($this->rowModel) . '.php';
            } else if(file_exists(APP . 'app_row.php')) {
                $this->rowModel = 'AppRow';
                $filename = 'app_row.php';
            } else {
                $this->rowModel = 'AppRow';
                $filename = 'null';
            }
    
            if (file_exists(MODELS . $filename) && $filename !== null) {
                include_once(MODELS . $filename);
            }
            AppRow::setModel($model);
        }
    
        /**
         * This method returns an instance of a RowObject to be inserted as a new row.
         * when save() is called a new row will be inserted.
         *
         * @return RowObject - returns an instance of the RowObject, AppRow or Row.
         */
        public function createRow($model) {
            $initialization = array();
            foreach (array_keys($model->schema()) as $field) {
                $initialization[$field] = null;
            }
            return Set::map($initialization, $this->rowModel);
        }
    
        /**
         * Just takes the results and converts them into a user defined object.
         */
        public function afterFind($model, $results, $primary = false) {
            return Set::map($results, $this->rowModel);
        }
    }
    ?>

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _Page 2: :///articles/view/4caea0e3-bd8c-4cb5-b3d7-458282f0cb67/lang:eng#page-2
.. _http://code.google.com/p/freenity/wiki/RowObjectBehavior: http://code.google.com/p/freenity/wiki/RowObjectBehavior
.. _http://freenity.googlecode.com/files/RowObjectBehavior%20v.%200.2.rar: http://freenity.googlecode.com/files/RowObjectBehavior%20v.%200.2.rar
.. _Page 1: :///articles/view/4caea0e3-bd8c-4cb5-b3d7-458282f0cb67/lang:eng#page-1
.. meta::
    :title: RowObjectBehavior Row Data Gateway implementation
    :description: CakePHP Article related to behavior,row object,row data gateway,rowobjectbehavior,rowobject,Behaviors
    :keywords: behavior,row object,row data gateway,rowobjectbehavior,rowobject,Behaviors
    :copyright: Copyright 2009 
    :category: behaviors

