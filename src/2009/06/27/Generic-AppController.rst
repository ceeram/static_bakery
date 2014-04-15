Generic AppController
=====================

by flatline on June 27, 2009

By using a Generic AppController (GAC) we can drastically reduce the
amount of code that is needlesly repeated in the controllers. It
implements the methods that are most frequently used when baking a
'standard' CakePHP application in such a way that all the child-
controllers can use them without modifications, following the DRY
principle (Don't Repeat Yourself). It's just like using scaffolding
for the controllers except that it uses the views you can customize.
It almost feels like cheating. Or [i]Magic[/i].


Download - Get the Code
~~~~~~~~~~~~~~~~~~~~~~~
You can find the latest Generic AppController in the GitHub
repository:
`http://github.com/markomarkovic/generic_app_controller/`_


Installation
~~~~~~~~~~~~
Copy the generic app_controller.php to your app directory.



Usage
~~~~~
When using GAC, you only need to have absolutely minimal controllers
as all the work is seamlessly done by the parent class. You can also
override the existing methods if you need to.


Minimal NewsController example
``````````````````````````````

Controller Class:
`````````````````

::

    <?php 
    class NewsController extends AppController {}
    ?>

app/controllers/news_controller.php

That is all you need to write if you use only the default methods in
your controller. As I've said before, Magic.

The methods in the GAC do not interfere with the methods is your other
controllers. If you write, for example, index method in your
NewsController, Cake is going to use it and ignore the one in the GAC.

Because of this, if you need to use for example a beforeFilter in both
your GAC and in a specific controller, you need to make sure that both
are called.


Controller Class:
`````````````````

::

    <?php 
    class NewsController extends AppController {
    	function beforeFilter() {
    		// Do something news specific here
    		// Call the GAC beforeFilter
    		parent::beforeFilter();
    	}
    }
    ?>

app/controllers/news_controller.php

You can also use the methods from the GAC to DRY:


Controller Class:
`````````````````

::

    <?php 
    class NewsController extends AppController {
    	function admin_delete($id = null) {
    		if (!$id) {
    			$this->Session->setFlash(__('Invalid News ID.', true));
    			$this->redirect(array('action' => 'index'));
    		}
    		if ($id < 10) { // News with the id < 10 are not deletable.
    			$this->Session->setFlash(sprintf(__('News #%d cannot be deleted.', true), $id));
    			$this->redirect(array('action' => 'index'));
    		} else {
    			parent::admin_delete($id); // Deleting using method from the GAC
    		}
    	}
    }
    ?>

app/controllers/news_controller.php


The implemented methods
~~~~~~~~~~~~~~~~~~~~~~~
They are functionally the same to the ones that are baked using the
console. They work with the baked Models and Views without any further
modifications.

+ index
+ view, using id as a parameter or slug when using Sluggable Behavior.
+ admin_index
+ admin_view
+ admin_add
+ admin_edit
+ admin_delete

The normal, non-admin, add, edit and delete are not implemented, but
if you really need that functionality you just need to take admin_*
methods and rename them.


.. _http://github.com/markomarkovic/generic_app_controller/: http://github.com/markomarkovic/generic_app_controller/

.. author:: flatline
.. categories:: articles, snippets
.. tags:: appcontroller,controller,dry,generic,Snippets

