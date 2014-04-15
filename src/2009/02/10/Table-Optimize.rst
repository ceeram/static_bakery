Table Optimize
==============

by WyriHaximus on February 10, 2009

While developing a simple wiki bot with a queue table I noticed that
sometimes the table kept it's old data as overhead. For that I
searched through the bakery, manual and API but couldn't find anything
about this. So this simple function was born.


Overview
~~~~~~~~
Normally you would have a DBA to watch over it's database tables. But
in small projects this isn't always the case and a self regulation
application is a way to keep your database optimized. This function
can aid with this task.

Code
~~~~
Put this code in your app_model.php (create it if necessary).

Model Class:
````````````

::

    <?php 
            public function optimize() {
                    $db =& ConnectionManager::getDataSource($this->useDbConfig);
                    $tablename = $db->fullTableName($this);
                    if(!empty($tablename)) {
                            return $db->query('OPTIMIZE TABLE ' . $tablename . ';');
                    } else {
                            return false;
                    }
            }
    ?>


Usage
~~~~~
You can simply optimize a table in a similar as you would
select/update/delete data from that table:

::

    
    $this->TableName->optimize();

History:
Feb 6, 2009 applied suggestion by Rafael Bandeira + it now properly
returns the result.


.. author:: WyriHaximus
.. categories:: articles, snippets
.. tags:: model,optimize,Snippets

