Pagination
==========

by %s on October 09, 2006

If you have more than a few results it is useful, if not vital, to
provide a means of presenting the results a few at a time. This
tutorial will demonstrate how easy it is to present your data using
pagination.
If part of your application includes displaying lots of results, it's
a good idea to give the user the possibility to view the results in
digestable chunks and possibly to be able to sort the presented data.
This tutorial will explain how, after copying a few files into your
application, you can achieve this in very few lines of code.
You need almost no knowledge of cake to be able to make use of this
tutorial :).

If you already have a table in mind that you want to add pagination
to, read on; otherwise run through
`http://manual.cakephp.org/appendix/blog_tutorial`_ to have some code
to play with.


Setting Up
----------
All of the files necessary are available here in the bakery.

Save this file `http://bakery.cakephp.org/articles/view/67`_ as
/app/controllers/components/pagination.php

Save this file `http://bakery.cakephp.org/articles/view/68`_ as
/app/views/helpers/pagination.php

Save this file `http://bakery.cakephp.org/articles/view/69`_ as
/app/views/elements/pagination.thtml


Create/modify the Controller
----------------------------
The only change necessary to use pagination is to include the
component, the helper and call the component method "init" before the
relavent find.

Controller Class:
`````````````````

::

    <?php 
    class PostsController extends AppController
    {
    	var $name = 'Posts'; // for PHP4 installs
    	var $components = array ('Pagination'); // Added
    	var $helpers = array('Pagination'); // Added
    
    	function index() {	
    		$criteria=NULL;
    		list($order,$limit,$page) = $this->Pagination->init($criteria); // Added
    		$data = $this->Post->findAll($criteria, NULL, $order, $limit, $page); // Extra parameters added
    		
    		$this->set('data',$data);
    	}
    }
    ?>


Create/modify the View
----------------------
To make use of pagination, include the element, and optionally modify
your table headers to allow changing the sort order of results:

View Template:
``````````````

::

    
    <h1>Paginated Posts Index</h1>
    <table>
    <?php
    $pagination->setPaging($paging); // Initialize the pagination variables
    $th = array (
                $pagination->sortBy('id'),
                $pagination->sortBy('title'),
                $pagination->sortBy('created')
    ); // Generate the pagination sort links
    echo $html->tableHeaders($th); // Create the table headers with sort links if desired
    
    foreach ($data as $output)
    {
        $tr = array (
            $output['Post']['id'],
            $html->link($output['Post']['title'], "/Posts/View/{$output['Post']['id']}"),
            $output['Post']['created']
            );
        echo $html->tableCells($tr,array('class'=>'altRow'),array('class'=>'evenRow'));
    }
    ?>
    </table>
    <? echo $this->renderElement('pagination'); // Render the pagination element ?>



Adding Ajax updates
-------------------
If you include the RequestHandler component, the AJAX helper in your
controller and the prototype js file is loaded in your view - you get
your updates by ajax. Yes, it's that simple. The div that will be
updated by default is the "content" div, you can change this by
specifying in the component (either directly, or at run time) which
div to update. And yes, you can disable this automatic behaviour if
required.

How to add Prototype
~~~~~~~~~~~~~~~~~~~~
So how do you add the prototype library..? Well...
Modify your layout

The Prototype JavaScript library is availble at
`http://prototype.conio.net/`_ put prototype.js in /app/webroot/js/
Add the JavaScript code inside the tag


PHP Snippet:
````````````

::

    <?php 
    if(isset($javascript)):
        echo $javascript->link('prototype.js');
    endif;  		
    ?>


The example presented here is the most simple possible. If you want to
see more complex examples of how the code here can be put to use,
there is a demo with several permutations here:
`http://www.noswad.me.uk/Pagination`_ Demos include:
* use on tables with associations
* changing the display text for sort links
* different parameter styles
* including or removing the links to the first and last pages
* how to use with a search form (via ajax)
* using select boxes for parameters instead of links
* preventing access to sort functions
* displaying more than one set of paginated results on the same page
(see `http://www.noswad.me.uk/Pagination/Ajaxed/SeeingDouble`_)

Source code is available via the "Download Me" link (for the whole
plugin demonstration).

Have Fun!


The parameters Explained
------------------------
The component and helper have been written as clearly as possible to
give users the option to customize it's behaviour. The basic methods
are:

Component init
~~~~~~~~~~~~~~
The only method that is necessary to call, it has 3 parameters.

The Constraint
``````````````
This is the sql that will be the "where" clause in the find count
statement*

Web Parameters (Optional)
`````````````````````````
These are the publicly accessible parameters that came from the web,
it isn't necessary to pass them if you are using GET style parameters.

If you choose not to use GET parameters, it's necessary to provide the
parameters to the component as it isn't possible to automatically
determine them. All of the parameters are optional and are of the
form:

PHP Snippet:
````````````

::

    <?php 
    array (
    "page"=>$pageNumber, // Integer
    "sortBy"=>$fieldName, // string the name of a field
    "sortByClass"=>$modelName, // string the name of the model
    "direction"=>$direction, // ASC or DESC - sort direction
    "show"=>$show // Integer Number of results to display per page
    )
    ?>


Initialization Parameters
`````````````````````````
These are the private parameters that should never be accessible from
the web. These parameters can be used to set the default values if
different from those in the component itself or to apply restrictions
or different behaviour.
All of the parameters are optional and are of the form:

PHP Snippet:
````````````

::

    <?php 
    array (
    "page"=>$pageNumber, // Modify Default
    "sortBy"=>$fieldName, // Modify Default
    "sortByClass"=>$modelName, // Modify Default
    "direction"=>$direction, // Modify Default
    "show"=>$show // Modify Default
    
    "privateParams" = $privateParams, // Array of parameter names which cannot be passed via the url
    "total"=>$total // Integer Overriding the number of results, bypasses the sql count statement
    "maxPages"=>$max // Integer The maximum number of pages to be included in the list of pages
    "showLimits"=>$limits // Boolean Whether to display links to the first and last pages when they are not in the current page range.
    "resultsPerPage"=>$resutsPerPage, // Array of the possible number of results to be displayed per page.
    "paramStyle"=>$Style // String Determins how links are generated - get or pretty. Pretty means urls of the form /action/param{seperator}value
    "paramSeperator"=>$Seperator, // String The seperator used for pretty url links - defaults to ":"
    "url"=>$url // The base url used for links. If undefined it is derived from the current action, and controller name, taking care of admin and plugin parameters
    
    "ajaxAutoDetect"=>$Detect, // Boolean Use AJAX links if available defaults to true
    "ajaxDivUpdate"=>$DivId, // String The id of the div to update for AJAX updates. Defaults to content.
    "ajaxFormId"=>$FormId // String The id of a form which will be serialized and passed with all pagination links, if AJAX is enabled.
    )
    ?>


Helper sortBy
~~~~~~~~~~~~~
This method generates a link that will modify the sort order and reset
the results to page 1. It has 3 parameters

The Field to sort by
````````````````````

The display text for the link (defaults to the Field name)
``````````````````````````````````````````````````````````

The model for the sort (defaults to the controller default model)
`````````````````````````````````````````````````````````````````

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _http://bakery.cakephp.org/articles/view/69: http://bakery.cakephp.org/articles/view/69
.. _http://bakery.cakephp.org/articles/view/68: http://bakery.cakephp.org/articles/view/68
.. _http://bakery.cakephp.org/articles/view/67: http://bakery.cakephp.org/articles/view/67
.. _Page 1: :///articles/view/4caea0dc-9194-4446-89de-4c6f82f0cb67/lang:eng#page-1
.. _Page 2: :///articles/view/4caea0dc-9194-4446-89de-4c6f82f0cb67/lang:eng#page-2
.. _http://www.noswad.me.uk/Pagination/Ajaxed/SeeingDouble: http://www.noswad.me.uk/Pagination/Ajaxed/SeeingDouble
.. _http://www.noswad.me.uk/Pagination: http://www.noswad.me.uk/Pagination
.. _http://prototype.conio.net/: http://prototype.conio.net/
.. _http://manual.cakephp.org/appendix/blog_tutorial: http://manual.cakephp.org/appendix/blog_tutorial
.. meta::
    :title: Pagination
    :description: CakePHP Article related to page,Tutorials
    :keywords: page,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

