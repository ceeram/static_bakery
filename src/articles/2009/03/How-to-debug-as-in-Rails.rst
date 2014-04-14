How to debug as in Rails
========================

by rainchen on March 09, 2009

Rails is very friendly for debugging by logging the request info and
sql query. Developers can use tail -F command to watch the log for
keeping in mind that what is framework doing in background. Cake put
the sql query on the bottom of the page. Usually it's helpful. But in
some case it's helpless.Such as performing a delete action or get data
by AJAX. I will show you how to make Cake acts as Rails do in 3 steps.
Rails is very friendly for debugging by logging the request info and
sql query. Developers can use tail -F command to watch the log for
keeping in mind that what is framework doing in background.

Cake put the sql query on the bottom of the page. Usually it's
helpful. But in some case it's helpless.Such as performing a delete
action or get data by AJAX.

I will show you how to make Cake acts as Rails do in 3 steps.

1. save below codes to app/models/datasources/dbo/dbo_mysql_ex.php

::

    
    <?php
    // load lib
    uses('model/datasources/dbo/dbo_mysql');
    /**
    * @author RainChen @ Sun Feb 24 17:21:35 CST 2008
    * @uses usage
    * @link http://cakeexplorer.wordpress.com/2007/10/08/extending-of-dbosource-and-model-with-sql-generator-function/
    * @access public
    * @param parameter
    * @return return
    * @version 0.1
    */
    class DboMysqlEx extends DboMysql
    {
        function logQuery($sql)
        {
            $return = parent::logQuery($sql);
            if(Configure::read('Cake.logQuery'))
            {
                debugger::log("sql[$this->_queriesCnt]:".$sql);
            }
            return $return;
        }
    }
    ?>

2. edit the database config in app/config/database.php :

::

    'driver' => 'mysql_ex',

3. edit the core config in app/config/core.php, adding:

::

    Configure::write('Cake.logQuery', true);

Now have fun:
tail -F app/tmp/logs/debug.log

tips: console2 is a very useful tools for watching debugging log

.. meta::
    :title: How to debug as in Rails
    :description: CakePHP Article related to debug,Snippets
    :keywords: debug,Snippets
    :copyright: Copyright 2009 rainchen
    :category: snippets

