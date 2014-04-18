Search Feature As An SQL Query In 2 Steps
=========================================

This tutorial is aimed at beginner CakePHP users who might be looking
for a quick and easy way to perform searches on a database they are
using with CakePHP. Note: For advanced web applications do [b]NOT[/b]
use this search function. This is intended to be a primitive solution
for beginners who might not be looking to build an advanced web
application but still require a search method. My aim is simply to
facilitate beginners to web development who are interested in CakePHP.
What We're Going To Do
In this tutorial we will create a search method in 2 easy steps.

Step 1 . Add the search method to the controller class of your
application.
Step 2 . Add the search box within the view template.

Setup I am assuming that you already have configured database.php
within /app/config and you also have created models, views, and
controllers for your web application. In other words, you have tried
the blog example from the Bakery:
`http://book.cakephp.org/view/219/blog`_.

If you have not done so do NOT continue this tutorial. Go back, try
the blog example listed above and come back later.

Step 1: Adding Search To The Controller
The first thing we are going to do is create a method called index
which we will use to perform a search based on a user's input. Add the
method index below to the controller. It doesn't really matter where
you add it. I personally like to keep it as the first method within
the controller.

::

    
    <?php   
      function index() {
    	$this->Table->recursive = 0;
    	if ($this->data['Table']['search_text']) {
    		$this->set('Table', 
    		$this->paginate('Table', array('or' => array('Table.field LIKE' => '%' . 
    		$this->data['Table']['search_text'] . '%', 'Table.field_2 LIKE' => '%' . 
    		$this->data['Table']['search_text'] . '%', 'Table.field_3 LIKE' => '%' . 
    		$this->data['Table']['search_text'] . '%', 'Table.field_4 LIKE' => '%' . 
    		$this->data['Table']['search_text'] . '%', 'Table.field_5 LIKE' => '%' . 
    		$this->data['Table']['search_text'] . '%'))));
    	}
    	else {
    		$this->set('Tables', $this->paginate());
    	}
      }
    ?>

The method above matches the search_text with any of the fields
specified within the table of a database. Above, the table we are
using is Table and we are searching through field, field_2, field_3,
... field_5. You can edit this as you please so that the search_text
will only match the fields you specify.

For example if you were to have a table of users and you only wanted
the index method to search for usersnames you would simply only add
the field specific to usernames.

Step 2: Adding Search Box To The View
Now that you have created the index method, you're ready to go ahead
and create the search box. To do this, you will want to edit the view
of the controller you just added the method index to. The view is
located in /app/views/controller_name/ .

Now that you're in the correct location can you guess which view we
will be editing? Here's a hint the filename is the same name as the
name of the method you created in step 1. That's right, index.ctp. Add
the code below to index.ctp in the location where you want the search
box to appear.

::

    
    <div id="search_box">
    <h4>Search</h4>
    <?php echo $form->create('CourseInfo', array('url' => array('action' => 'index'))); ?>
    <?php echo $form->input('search_text', array('style' => 'width: 250px;', 'label' => false)); ?>
    <?php echo $form->end('Search'); ?>
    </div>

What we've done here, is created a search box as a form which will use
the index method within the controller as the action.

Finished
There you have it! You added search functionality to your web
application in just two easy steps! Please remember, that for a more
advanced method to perform searches I recommend you consult Calin
Don's excellent tutorial: `http://bakery.cakephp.org/articles/view
/search-feature-to-cakephp-blog-example`_

.. _http://book.cakephp.org/view/219/blog: http://book.cakephp.org/view/219/blog
.. _http://bakery.cakephp.org/articles/view/search-feature-to-cakephp-blog-example: http://bakery.cakephp.org/articles/view/search-feature-to-cakephp-blog-example

.. author:: ChrisA9
.. categories:: articles, tutorials
.. tags:: database,sql query,Tutorials

