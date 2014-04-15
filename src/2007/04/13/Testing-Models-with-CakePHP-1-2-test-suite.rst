Testing Models with CakePHP 1.2 test suite
==========================================

by mariano on April 13, 2007

CakePHP test suite is a powerful environment that lets you test small
to large applications testing for isolated portions of your code. It
is one of the coolest additions to the 1.2 release and in this article
we'll see how to use it to test our application models.


Installation
~~~~~~~~~~~~
First of all, you'll need to enable the test suite for your CakePHP
1.2 installation. After CakePHP is succesfully installed and
configured, get the latest release of SimpleTest from `its website`_,
and uncompress it in either your cake/vendors or your app/vendors
directory. You should have now a vendors/simpletest directory with all
SimpleTest files and folders inside.

Make sure that you at least have a DEBUG level of 1 in your
app/config/core.php file. Test your installation by running any of
CakePHP core tests, pointing your browser to
`http://www.example.com/test.php`_.


About Fixtures
~~~~~~~~~~~~~~
When testing models it is important to understand the concept of
fixtures in CakePHP test suite. Fixtures are a way for you to define
sample data that will be loaded in your models and will allow you to
perform your testing. CakePHP uses its own settings for fixtures to
not disrupt your real application data.

CakePHP will look at your app/config/database.php configuration file
and test if the connection named $test is accessible. If so, it will
use it to hold fixture data. Otherwise it will use the $default
database configuration. On either case, it will add "test_suite" to
your own table prefix (if any) to prevent collision with your existing
tables.

CakePHP will perform different operations during different stages of
your fixtured based test cases:


#. Before running the first test method in your test case, it will
   create the tables for each of your fixtures.
#. Before running any test method, it will optionally populate records
   for each of your fixtures.
#. After running each test method, it will empty each of your fixture
   tables.
#. After running your last test method, it will remove all your
   fixture tables.



Creating Fixtures
~~~~~~~~~~~~~~~~~
When creating a fixture you will mainly define two things: how the
table is created (which fields are part of the table), and which
records will be initially populated to the test table. Let's then
create our first fixture, that will be used to test our own Article
model. Create a file named article_test_fixture.php in your
app/tests/fixtures directory, with the following content:


PHP Snippet:
````````````

::

    <?php 
    class ArticleTestFixture extends CakeTestFixture {
    	var $name = 'ArticleTest';
    	
    	var $fields = array(
    		'id' => array('type' => 'integer', 'key' => 'primary'),
    		'title' => array('type' => 'string', 'length' => 255, 'null' => false),
    		'body' => 'text',
    		'published' => array('type' => 'integer', 'default' => '0', 'null' => false),
    		'created' => 'datetime',
    		'updated' => 'datetime'
    	);
    	var $records = array(
    		array ('id' => 1, 'title' => 'First Article', 'body' => 'First Article Body', 'published' => '1', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'),
    		array ('id' => 2, 'title' => 'Second Article', 'body' => 'Second Article Body', 'published' => '1', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'),
    		array ('id' => 3, 'title' => 'Third Article', 'body' => 'Third Article Body', 'published' => '1', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31')
    	);
    }
    ?>

We use $fields to specify which fields will be part of this table, on
how they are defined. The format used to define these fields is the
same used in the function generateColumnSchema() defined on Cake's
database engine classes (for example, on file dbo_mysql.php.) Let's
see the available attributes a field can take and their meaning:


#. type : CakePHP internal data type. Currently supported: string
   (maps to VARCHAR), text (maps to TEXT), integer (maps to INT), float
   (maps to FLOAT), datetime (maps to DATETIME), timestamp (maps to
   TIMESTAMP), time (maps to TIME), date (maps to DATE), and binary (maps
   to BLOB)
#. key : set to primary to make the field AUTO_INCREMENT, and a
   PRIMARY KEY for the table.
#. length : set to the specific length the field should take.
#. null : set to either true (to allow NULLs) or false (to disallow
   NULLs)
#. default : default value the field takes.

We lastly can set a set of records that will be populated after the
test table is created. The format is fairly straight forward and needs
no further explanation.


Importing table information and records
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Your application may have already working models with real data
associated to them, and you might decide to test your model with that
data. It would be then a duplicate effort to have to define the table
definition and/or records on your fixtures. Fortunately, there's a way
for you to define that table definition and/or records for a
particular fixture come from an existing model or an existing table.

Let's start with an example. Assuming you have a model named Article
available in your application (that maps to a table named articles),
change the example fixture given in the previous section (
app/tests/fixtures/article_test_fixture.php ) to:


PHP Snippet:
````````````

::

    <?php 
    class ArticleTestFixture extends CakeTestFixture {
    	var $name = 'ArticleTest';
    	var $import = 'Article';
    }
    ?>

This statement tells the test suite to import your table definition
from the table linked to the model called Article. You can use any
model available in your application. The statement above does not
import records, you can do so by changing it to:


PHP Snippet:
````````````

::

    <?php 
    class ArticleTestFixture extends CakeTestFixture {
    	var $name = 'ArticleTest';
    	var $import = array('model' => 'Article', 'records' => true);
    }
    ?>

If on the other hand you have a table created but no model available
for it, you can specify that your import will take place by reading
that table information instead. For example:


PHP Snippet:
````````````

::

    <?php 
    class ArticleTestFixture extends CakeTestFixture {
    	var $name = 'ArticleTest';
    	var $import = array('table' => 'articles');
    }
    ?>

Will import table definition from a table called 'articles' using your
CakePHP database connection named 'default'. If you want to change the
connection to use just do:


PHP Snippet:
````````````

::

    <?php 
    class ArticleTestFixture extends CakeTestFixture {
    	var $name = 'ArticleTest';
    	var $import = array('table' => 'articles', 'connection' => 'other');
    }
    ?>

Since it uses your CakePHP database connection, if there's any table
prefix declared it will be automatically used when fetching table
information. The two snippets above do not import records from the
table. To force the fixture to also import its records, change it to:


PHP Snippet:
````````````

::

    <?php 
    class ArticleTestFixture extends CakeTestFixture {
    	var $name = 'ArticleTest';
    	var $import = array('table' => 'articles', 'records' => true);
    }
    ?>

You can naturally import your table definition from an existing
model/table, but have your records defined directly on the fixture as
it was shown on previous section. For example:


PHP Snippet:
````````````

::

    <?php 
    class ArticleTestFixture extends CakeTestFixture {
    	var $name = 'ArticleTest';
    	var $import = 'Article';
    	
    	var $records = array(
    		array ('id' => 1, 'title' => 'First Article', 'body' => 'First Article Body', 'published' => '1', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'),
    		array ('id' => 2, 'title' => 'Second Article', 'body' => 'Second Article Body', 'published' => '1', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'),
    		array ('id' => 3, 'title' => 'Third Article', 'body' => 'Third Article Body', 'published' => '1', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31')
    	);
    }
    ?>



Creating your test case
~~~~~~~~~~~~~~~~~~~~~~~
Let's say we already have our Article model defined on
app/models/article.php, which looks like this:


Model Class:
````````````

::

    <?php 
    class Article extends AppModel {
    	var $name = 'Article';
    	
    	function published($fields = null) {
    		$conditions = array(
    			$this->name . '.published' => 1
    		);
    		
    		return $this->findAll($conditions, $fields);
    	}
    
    }
    ?>

We now want to set up a test that will use this model definition, but
through fixtures, to test some functionality in the model. CakePHP
test suite loads a very minimum set of files (to keep tests isolated),
so we have to start by loading our parent model (in this case the
Article model which we already defined), and then inform the test
suite that we want to test this model by specifying which DB
configuration it should use. CakePHP test suite enables a DB
configuration named test_suite that is used for all models that rely
on fixtures. Setting $useDbConfig to this configuration will let
CakePHP know that this model uses the test suite database connection.

Since we also want to reuse all our existing model code we will create
a test model that will extend from Article, set $useDbConfig and $name
appropiately. Let's now create a file named article.test.php in your
app/tests/cases/models directory, with the following contents:


PHP Snippet:
````````````

::

    <?php 
    loadModel('Article');
    
    class ArticleTest extends Article {
    	var $name = 'ArticleTest';
    	var $useDbConfig = 'test_suite';
    }
    
    class ArticleTestCase extends CakeTestCase {
    	var $fixtures = array( 'article_test' );
    }
    ?>

As you can see we're not really adding any test methods yet, we have
just defined our ArticleTest model (that inherits from Article), and
created the ArticleTestCase. In variable $fixtures we define the set
of fixtures that we'll use.


Creating our first test method
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Let's now add a method to test the function published() in the Article
model. Edit the file app/tests/cases/models/article.test.php so it now
looks like this:


PHP Snippet:
````````````

::

    <?php 
    loadModel('Article');
    
    class ArticleTest extends Article {
    	var $name = 'ArticleTest';
    	var $useDbConfig = 'test_suite';
    }
    
    class ArticleTestCase extends CakeTestCase {
    	var $fixtures = array( 'article_test' );
    	
    	function testPublished() {
    		$this->ArticleTest =& new ArticleTest();
    		
    		$result = $this->ArticleTest->published(array('id', 'title'));
    		$expected = array(
    			array('ArticleTest' => array( 'id' => 1, 'title' => 'First Article' )),
    			array('ArticleTest' => array( 'id' => 2, 'title' => 'Second Article' )),
    			array('ArticleTest' => array( 'id' => 3, 'title' => 'Third Article' ))
    		);
    		
    		$this->assertEqual($result, $expected);
    	}
    }
    ?>

You can see we have added a method called testPublished() . We start
by creating an instance of our fixture based ArticleTest model, and
then run our published() method. In $expected we set what we expect
should be the proper result (that we know since we have defined which
records are initally populated to the article_tests table.) We test
that the result equals our expectation by using the assertEqual
method.


Running your test
~~~~~~~~~~~~~~~~~
Make sure that you at least have a DEBUG level of 1 in your
app/config/core.php file, and then point your browser to
`http://www.example.com/test.php`_. Click on App Test Cases and find
the link to your models/article.test.php . Click on that link.

If everything works as expected, you will see a nice green screen
saying that your test succeded.

.. _its website: http://simpletest.sourceforge.net/
.. _http://www.example.com/test.php: http://www.example.com/test.php

.. author:: mariano
.. categories:: articles, tutorials
.. tags:: test,suite,case,1.2,Tutorials

