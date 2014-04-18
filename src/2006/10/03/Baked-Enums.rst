Baked Enums
===========

Moving this snippet over from CakeForge. This is a preliminary
solution to solve the problem of baking your Models's, View's and
Controller's with enum keys in the database. It is not completely
automatic and I can only tell you that it works with PHP5 and MySQL
4/5. As PHPNut stated in trac, the enum columns are too different
across databases to make this standard (at least for now). So if you
are using something other than MySQL you will have to change the code
in app_model.php accordingly.


Model Class:
````````````

::

    <?php 
    <?php
    class AppModel extends Model
    {
    
        /**
         * Get Enum Values
         * Snippet v0.1.3
         * http://cakeforge.org/snippet/detail.php?type=snippet&id=112
         *
         * Gets the enum values for MySQL 4 and 5 to use in selectTag()
         * Tested with PHP 4/5 and CakePHP 1.1.8
         */ 
        function getEnumValues($columnName=null)
        {
            if ($columnName==null) { return array(); } //no field specified
    
    
            //Get the name of the table
            $db =& ConnectionManager::getDataSource($this->useDbConfig);
            $tableName = $db->fullTableName($this, false);
    
    
            //Get the values for the specified column (database and version specific, needs testing)
            $result = $this->query("SHOW COLUMNS FROM {$tableName} LIKE '{$columnName}'");
    
            //figure out where in the result our Types are (this varies between mysql versions)
            $types = null;
            if     ( isset( $result[0]['COLUMNS']['Type'] ) ) { $types = $result[0]['COLUMNS']['Type']; } //MySQL 5
            elseif ( isset( $result[0][0]['Type'] ) )         { $types = $result[0][0]['Type'];         } //MySQL 4
            else   { return array(); } //types return not accounted for
    
            //Get the values
            $values = explode("','", preg_replace("/(enum)\('(.+?)'\)/","\\2", $types) );
    
            //explode doesn't do assoc arrays, but cake needs an assoc to assign values
            $assoc_values = array();
            foreach ( $values as $value ) {
                //leave the call to humanize if you want it to look pretty
                $assoc_values[$value] = Inflector::humanize($value);
            }
    
            return $assoc_values;
    
        } //end getEnumValues
    
    }
    ?>
    ?>



Controller Class:
`````````````````

::

    <?php 
    <?php
    /**
     * example code for your controller
     */
    class ExamplesController extends AppController
    {
        var $name = "Examples";
    
        function add() {
    
            /*
             * This is the addition to this action.  I have an enum column called
             * 'active' which I want the values for.  Bake already puts a variable
             * in the view called 'enumcolArray' to hold the values.  This is 
             * just setting the values for that for that variable using the code
             * we defined in app_model.php above.
             */
            $this->set('activeArray',    $this->Example->getEnumValues('active'));
    
            if(empty($this->data)) {
                $this->set('examples', null);
            } else {
                $this->cleanUpFields();
                if($this->Example->save($this->data)) {
                    if(is_object($this->Session)) {
                        $this->Session->setFlash('The Example has been saved');
                        $this->redirect('/examples/index');
                    } else {
                        $this->flash('Example saved.', '/examples/index');
                    }
                } else {
                    if(is_object($this->Session)) {
                        $this->Session->setFlash('Please correct errors below.');
                    }
                    $data = $this->data;
                    $this->set('examples', $data);
                }
            }
        }
    
    }
    
    ?>
    ?>



.. author:: jzimmerman
.. categories:: articles, snippets
.. tags:: enum bake mysql,Snippets

