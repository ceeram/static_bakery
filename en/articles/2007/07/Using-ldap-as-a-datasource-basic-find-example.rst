

Using ldap as a datasource: basic find example
==============================================

by %s on July 26, 2007

This tutorial shows through a simple example how to perform a search
query on a model using a [url=http://bakery.cakephp.org/articles/view
/ldap-datasource-for-cakephp]ldap datasource[/url].


Introduction
------------
Please refer to this `article`_ for further details on the ldap
datasource. The class ldap_source must be deployed in your cake app in
order to run the example presented in this tutorial.


Simple user retrieval example
-----------------------------

Imagine we have a SQL table User and ldap branch
ou=person,dc=example,dc=org. Each 'table' has a corresponding model in
cake : respectively User and LDAPUser. Conceptually, these two models
are bound together with a hasOne/belongsTo relation. Our goal is to
get with a single query all data located in both Mysql and LDAP.


Database configuration:
```````````````````````

::

    <?php
    class DATABASE_CONFIG {
    	// Adapt this parameter to your data
    	var $default = array (
    		'driver' => 'mysql',
    		'connect' => 'mysql_connect',
    		'host' => 'localhost',
    		'login' => 'developer',
    		'password' => 'xxxx',
    		'database' => 'ldapSource',
    		'prefix' => ''
    	);
    	
    	// Adapt this parameter to your data
    	var $ldap = array (
    		'datasource' => 'ldap',
    		'host' => 'localhost',				
    		'port' => 389,						
    		'basedn' => 'dc=example,dc=org',	
    		'login' => 'cn=developer,dc=example,dc=org', 
    		'password' => 'xxxx',				
    		'version' => 3					
    	);	
    }
    ?>

Then, we need to create our models (User and LdapUser):

Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	var $name = 'User';
    
    	var $belongsTo = array (
    		'LdapUser' => array (
    			'className' => 'LdapUser',
    			'foreignKey' => 'username'
    		)
    	);
    }
    ?>



Model Class:
````````````

::

    <?php 
    class LdapUser extends AppModel {
    	
    	var $name = 'LdapUser';
    	
    	var $useDbConfig = 'ldap';
    	
    	var $primaryKey = 'cn';	 // Adapt this parameter to your data
    		
    	var $useTable = 'ou=person'; // Adapt this parameter to your data
    	
    	var $hasOne = array (
    		'User' => array (
    			'className' => 'User',
    			'foreignKey' => 'username' // Adapt this parameter to your data
    		)
    	);
    }
    ?>

The next step is to design a simple controller for our search query:

Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController {
    
    	var $name = 'Users';
    
    	function index() {			
    		$conditions = "id=1"; // Adapt this condition to your data
    		$recursive = 1; 
    
    		$data = $this->User->find($conditions, null, null, $recursive);
    		$this->set('data', $data);
    	}
    }
    ?>

Finally, we need a output the query result:

View Template:
``````````````

::

    
    <pre>
    <?php
    print_r($data);
    ?>
    </pre>


If you start your browser at `http://example.org/AppName/users`_, you
should get the data contained in both databases. In my case, the
output was:

::

    
    Array
    (
        [User] => Array
            (
                [id] => 1
                [username] => jean
                [password] => xxxxxxxxxxxxxxxxxxxxxxxxxx
                [mail] => jean@example.org
                [created] => 2007-01-13 12:16:09
                [modified] => 2007-05-03 15:21:12
            )
    
        [LdapUser] => Array
            (
                [mail] => jean@example.org
                [objectclass] => Person
                [telephonenumber] => 0000
                [cn] => jean
            )


Conclusion
----------

Nice isn't it? If you have any suggestions, contact me at `email_ylb-
php@yahoo.fr`_


.. _article: http://bakery.cakephp.org/articles/view/ldap-datasource-for-cakephp
.. _email_ylb-php@yahoo.fr: mailto:email_ylb-php@yahoo.fr
.. _http://example.org/AppName/users: http://example.org/AppName/users
.. meta::
    :title: Using ldap as a datasource: basic find example
    :description: CakePHP Article related to ldap,Tutorials
    :keywords: ldap,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

