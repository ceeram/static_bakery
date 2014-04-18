One core, one app, multiple domains
===================================

Dealing with configurations for multiple domains (environments) is
always a topic for discussion. I wanted to share my way of dealing
with this common problem. This will not be right for all, which is the
point. If this does not suit you, there are a few links at the bottom.


What will this do?
~~~~~~~~~~~~~~~~~~

Simplify updates using SCM (git pull anyone?).
Ability to run a single vhost setting, single cake core, single app
directory for multiple subdomains.
Each subdomain is it's own application not simply an alias.
Can run domain-sensitive shell tasks.



Apache first.
~~~~~~~~~~~~~
(or whatever you use)
Just create a vhost and keep adding aliases for each new domain.

::

    
    ServerName client1.example.com
    ServerAlias client2.example.com
    ServerAlias client3.example.com

(I don't like wildcards here since I want any non-existing subdomains
to point to a non-cake "error site")


Now Cake.
~~~~~~~~~
The domain switching is handled in bootstrap.php by looking at the
SERVER_NAME environment variable. THis is not perfect. But I think
it's preferable to something like using "SetEnv" in htaccess or the
vhost. This is simply because that if Apache isn't providing the
server name then you probably can't see the set parameter either.
Shell access, you say? Well, that is where the first of the two if
clauses in bootstrap.php comes in. More on that in a minute.


bootstrap.php
`````````````

::

    
    <?php
    // Initial defaults go at the top, for example:
    Configure::write('Config.language', 'swe');
    
    
    // Load domain-specific config
    if ( isset($_SERVER['SERVER_NAME']) ) { // web
    	$bootstrap = CONFIGS .'domains'.DS.$_SERVER['SERVER_NAME'].'.php';
    } elseif ( count($_SERVER['argv']) ) { // cli
    	$_SERVER['SERVER_NAME'] = $_SERVER['argv'][count($_SERVER['argv'])-1];
    	$bootstrap = CONFIGS .'domains'.DS.$_SERVER['argv'][count($_SERVER['argv'])-1].'.php';
    }
    if ( file_exists($bootstrap) ) {
    	require($bootstrap); 
    } else {
    	echo 'No configuration could be loaded for domain '.$_SERVER['SERVER_NAME'].'. Exiting...';
    	exit;
    }	
    
    
    // At the bottom you can override configurations if you found you had to.
    // This is also where you define defaults for constants. (keeps the if out of the domain file)
    if ( !defined('CLIENT_NAME') ) {
    	define('CLIENT_NAME', 'No Client');
    }
    ?>



Domain files
````````````
In app/config/ I create a folder called domains . In it, I keep each
domain-specific configuration file named after each domain.

::

    
    client1.example.com.php
    client2.example.com.php
    client3.example.com.php
    myapp.site.php  <-- my local dev setup also gets it's own file.
    myapp.local.php  <-- someone elses local setup.



client1.example.com.php
```````````````````````

::

    
    <?php
    // this file will mostly contain modifications of the defaults from bootstrap.php
    Configure::write('Config.language', 'eng');
    define('CLIENT_NAME', 'Client One');
    
    
    // But this is important. I set the database setting here!
    Configure::write('Database.config', array(
    	'default' => array(
    		'driver' => 'mysqli_ex',
    		'persistent' => false,
    		'host' => 'localhost',
    		'port' => '',
    		'login' => 'client1',
    		'password' => 'client1',
    		'database' => 'client1',
    		'schema' => '',
    		'prefix' => '',
    		'encoding' => 'utf8'
    	),
    	'test' => array(
    		'driver' => 'mysqli_ex',
    		'persistent' => false,
    		'host' => 'localhost',
    		'port' => '',
    		'login' => 'testing',
    		'password' => 'testing',
    		'database' => 'probably not',
    		'schema' => '',
    		'prefix' => '',
    		'encoding' => 'utf8'
    	)
    ));
    ?>



Database settings
`````````````````
As you can see I set the database configuration in the domain file. I
prefer this to having a long list of database settings in database.php
or a second file for each domain in the domains folder. The database
settings are loaded like this:

::

    
    <?php
    class DATABASE_CONFIG {
    	var $default = array(
    		'driver' => 'mysqli_ex',
    		'persistent' => false,
    		'host' => 'localhost',
    		'port' => '',
    		'login' => 'root',
    		'password' => '',
    		'database' => 'default',
    		'schema' => '',
    		'prefix' => '',
    		'encoding' => 'utf8'
    	);
    
    	function __construct () {		
    		$config = Configure::read('Database.config');
    		if ( !is_array($config) ) {
    			// screaming exit here?
    			return;
    		}
    		foreach ( $config as $name=>$data ) {
    			$this->$name = $data;
    		}
    	}
    	
    }
    ?>

The database file dynamically creates the attributes (class-variables)
from the keys in the domain-specific file. This is probably illegal
php by some strict setting but I still sleep well at night. The reason
I do this and not, like some other people, simply set the "default" to
whatever I have in the domain-file (see links at the bottom) is that I
sometimes need several databases accessible from the application. This
way I can dynamically create as many as I like.



And there shall be a shell.
~~~~~~~~~~~~~~~~~~~~~~~~~~~
The shell access is always a problem. You have no server-environment
available and no Cake-magic to fake it, as far as I know. The fix is
simple but also the least robust part of this setup. Bootstrap is set
to accept the domain as a shell argument.

part of bootstrap.php again
```````````````````````````

::

    
    <?php
    
    if ( isset($_SERVER['SERVER_NAME']) ) { // normal web access
    	$bootstrap = CONFIGS .'domains'.DS.$_SERVER['SERVER_NAME'].'.php';
    } elseif ( count($_SERVER['argv']) ) { // we need a cli agrument (argv will always exist so this is a bit pointless)
    	$_SERVER['SERVER_NAME'] = $_SERVER['argv'][count($_SERVER['argv'])-1];
    	$bootstrap = CONFIGS .'domains'.DS.$_SERVER['argv'][count($_SERVER['argv'])-1].'.php';
    }
    
    ?>

These lines set the server name and the include file from the last
shell argument. That is the less robust part and something you may
wish to modify if you find it breaks your shells.

My hourly script is run like this:

::

    
    /path/to/cake/cake/console/cake hourly client1.example.com

And from cron that would be:

::

    
    /path/to/cake/cake/console/cake -app /path/to/cake/app/ hourly client1.example.com

I have even verified that the SERVER_NAME survives a requestAction(),
good old requesrtAction ;)


Anything to look out for?
~~~~~~~~~~~~~~~~~~~~~~~~~
This technique works. I have used a variation of this on a live
application for almost 3 years. Before devising this tweaked and
updated version I looked at the suggestions from blogs and posts
around the web. For my purposes this is the best I have seen. But it
is not without it's potential problems.

Caching is not exhaustively tested. I can say that Cake's default
caching of models and "persistent" things are not adversely affected.
Other caching, I don't know. You can specify "domains" for cache files
which would be a way to get around problems.

Logs will be jumbled together. This is a problem I am looking into but
have no good fix for yet.

Uploaded files should be pointed to domain-specific folders. You don't
want a file called trade-secrets.doc to be accessed by the wrong
domain!


That's it
~~~~~~~~~
Thanks for reading. If you are not bored yet below are a few blog
posts that I used as inspiration and reference in varying amounts.

`http://rafaelbandeira3.wordpress.com/2008/12/05/handling-multiple-
enviroments-on-
cakephp/`_`http://www.littlehart.net/atthekeyboard/2008/11/28
/handling-multiple-environments-in-your-php-
application/`_`http://edwardawebb.com/programming/php-
programming/cakephp/automatically-choose-database-connections-
cakephp`_


.. _http://edwardawebb.com/programming/php-programming/cakephp/automatically-choose-database-connections-cakephp: http://edwardawebb.com/programming/php-programming/cakephp/automatically-choose-database-connections-cakephp
.. _http://rafaelbandeira3.wordpress.com/2008/12/05/handling-multiple-enviroments-on-cakephp/: http://rafaelbandeira3.wordpress.com/2008/12/05/handling-multiple-enviroments-on-cakephp/
.. _http://www.littlehart.net/atthekeyboard/2008/11/28/handling-multiple-environments-in-your-php-application/: http://www.littlehart.net/atthekeyboard/2008/11/28/handling-multiple-environments-in-your-php-application/

.. author:: eimermusic
.. categories:: articles, tutorials
.. tags:: configuration,environments,domains,Tutorials

