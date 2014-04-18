Easy peasy database config
==========================

Like a lot of developers out there, I use Subversion to keep control
of my code and projects, and I also use a different database for
development and production. But when using Cake this can be a problem
when checking out my code from development to production. Unless I
edit my database.php with my production config, the production code
would have problems, as it would be trying to access data from the
development database. What I needed was an easy-peasy way of being
able to check in my code to production without having to edit the
database.php config file. So what I did was very simple and can be
found below. For a full write up of this trick and other Cake stuff,
please see my Blog at http://joelmoss.info

::

    class DATABASE_CONFIG {
    
        var $development = array(
            'driver' => 'mysql',
            'connect' => 'mysql_connect',
            'host' => 'localhost',
            'login' => 'user',
            'password' => 'passwd',
            'database' => 'app_devel'
        );
        var $production = array(
            'driver' => 'mysql',
            'connect' => 'mysql_connect',
            'host' => 'localhost',
            'login' => 'user',
            'password' => 'passwd',
            'database' => 'app'
        );
        var $test = array(
            'driver' => 'mysql',
            'connect' => 'mysql_connect',
            'host' => 'localhost',
            'login' => 'user',
            'password' => 'passwd',
            'database' => 'app_test'
        );
        var $default = array();
    
        function __construct()
        {
            $this->default = ($_SERVER['SERVER_ADDR'] == '127.0.0.1') ?
                $this->development : $this->production;
        }
        function DATABASE_CONFIG()
        {
            $this->__construct();
        }
    }



.. author:: joelmoss
.. categories:: articles, snippets
.. tags:: database,Snippets

