

getDSN
======

by %s on September 27, 2006

This returns the database connection details in the form of a DSN.
driver://username:password@hostname/database This is useful if you
have a 3rd party class that goes in /vendors that requires its own DSN
information to work. (i.e. GoogleMapAPI class). I put this function in
/app/app_controller.php so that it is available to all other
controllers if necessary. Originally based on a post in the
GoogleGroup .


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller
    {
    
        /**
         * getDSN
         *  - Gets a DSN for the other apps to connect to 
         *    the database independent from CakePHP
         */
        function getDSN()
        {
            $this->db =& ConnectionManager::getDataSource('default');
            $c = $this->db->config;
            return "{$c['driver']}://{$c['login']}:{$c['password']}@{$c['host']}/{$c['database']}";
    
        } //end getDSN()
    
    } //end class
    ?>


.. meta::
    :title: getDSN
    :description: CakePHP Article related to dsn database connect,Snippets
    :keywords: dsn database connect,Snippets
    :copyright: Copyright 2006 
    :category: snippets

