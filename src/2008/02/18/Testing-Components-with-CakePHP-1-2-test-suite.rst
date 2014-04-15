Testing Components with CakePHP 1.2 test suite
==============================================

by erik_sternerson on February 18, 2008

This tutorial discusses how to use the CakePHP test suit to test
components. It builds upon the excellent work of Mariano Iglesias
found here: http://bakery.cakephp.org/articles/view/testing-models-
with-cakephp-1-2-test-suite


Preface
```````
Please start by reading
`http://bakery.cakephp.org/articles/view/testing-models-with-
cakephp-1-2-test-suite`_ to get the general idea of CakePHP testing,
fixtures and running tests.

In this tutorial we will use a component called TransporterComponent,
which uses a model called Transporter to provide functionality for
other controllers. We will use four files:

+ A component called Transporters found in
  app/controllers/components/transporter.php
+ A model called Transporter found in app/models/transporter.php
+ A fixture called TransporterTestFixture found in
  app/tests/fixtures/transporter_test_fixture.php
+ The testing code, several classes, found in
  app/tests/cases/transporter.test.php (the dot between transporter and
  test is apparently important)



Preparing the component
```````````````````````
To be able to use a component in a test we first need to tell Cake
which one we intend to use. This is done by typing:

::

    
    App::import('Component', 'Transporter');



Preparing data
``````````````
If our component needs access to a model, we need to prepare the data
for that model using fixtures, as mentioned in the tutorial linked
above.
We prepare a model for testing usage following the same pattern as the
previous tutorial suggested:

::

    
    App::import('Model', 'Transporter');
    class TransporterTest extends Transporter {
    	var $name ='Transporter';
    	var $useDbConfig = 'test_suite';
    }

Notice that we did not name the model TransporterTest, since we would
then have to change the component in an unfortunate way.


Initializing the component
``````````````````````````
Since CakePHP discourages from importing models directly into
components (see `http://manual.cakephp.org/chapter/components`_) we
need a controller to access the data in the model.

If the startup() function of the component looks like this:

::

    
    public function startup(&$controller){
    	$this->Transporter = $controller->Transporter; 
    }

then we can just design a really simple fake class:

::

    
    class FakeTransporterController {}

and assign values into it like this:

::

    
    $this->TransporterComponentTest = new TransporterComponent();
    $controller = new FakeTransporterController();
    $controller->Transporter = new TransporterTest();
    $this->TransporterComponentTest->startup(&$controller);



Writing the tests
`````````````````
We start with an empty test class that ensures that the fixture is
loaded:

::

    
    class TransporterTestCase extends CakeTestCase {
    	var $fixtures = array('transporter_test');
    }

We can then add as many test functions as we want to that class. We
probably need to initialize the component in each test function, so a
sample test function would look something like this:

::

    
    function testGetTransporter() {
    	$this->TransporterComponentTest = new TransporterComponent();
    	$controller = new FakeTransporterController();
    	$controller->Transporter = new TransporterTest();
    	$this->TransporterComponentTest->startup(&$controller);
    
    	$result = $this->TransporterComponentTest->getTransporter("12345", "Sweden", "54321", "Sweden");
    	$this->assertEqual($result, 1, "SP is best for 1xxxx-5xxxx");
    	
    	$result = $this->TransporterComponentTest->getTransporter("41234", "Sweden", "44321", "Sweden");
    	$this->assertEqual($result, 2, "WSTS is best for 41xxx-44xxx");
    
    	$result = $this->TransporterComponentTest->getTransporter("41001", "Sweden", "41870", "Sweden");
    	$this->assertEqual($result, 3, "GL is best for 410xx-419xx");
    
    	$result = $this->TransporterComponentTest->getTransporter("12345", "Sweden", "54321", "Norway");
    	$this->assertEqual($result, 0, "Noone can service Norway");		
    }

The syntax for the different assert functions can be found in the
SimpleTest documentation.


Executing the tests
```````````````````
Just browse to /your/cake/folder/test.php and click App Test Cases and
click components\transporter.test.php


Disclaimer
``````````
I'm a beginner PHP programmer, I have one week of CakePHP experience,
no SimpleTest experience. Actually I don't have very much experience
at all :)
Please comment any and all mistakes and areas of improvement that you
can identify! Thanks!

.. _http://bakery.cakephp.org/articles/view/testing-models-with-cakephp-1-2-test-suite: http://bakery.cakephp.org/articles/view/testing-models-with-cakephp-1-2-test-suite
.. _http://manual.cakephp.org/chapter/components: http://manual.cakephp.org/chapter/components

.. author:: erik_sternerson
.. categories:: articles, tutorials
.. tags:: test,component,1.2,Tutorials

