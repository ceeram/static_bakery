

Some ideas to organize your CSS files and autoload them in CakePHP
==================================================================

by %s on January 15, 2007

In this article, I will show some ideas in how to organize your CSS
files and how to autoload them in your CakePHP project. Some of them
are elegant , and others are not so elegant as they could be ...


First Idea: CSS files named whitout any link with controller names
------------------------------------------------------------------
In this method, the files names are completely different from
controller names. This is not recommended, because you could lose much
time searching a CSS file which is related to a specific controller.

To use this method, we will do the controller choose what CSS file
will be used, so will be used a variable $CSS to store the CSS file
name. For example, an Articles controller:


Controller Class:
`````````````````

::

    <?php 
    class ArticlesController extends AppController
    {
          var $name = 'Articles';
    function index(){
    
          $this->set("CSS", "articleshome");
          }
    function read($id){
          $this->set("CSS", "readarticle");
     }
    }
    ?>

Note that we set a variable $CSS for each method (action), and each
CSS file name doesn't have any link with the method or controller
name.

Now, in our default layout, inside the head tag:

View Template:
``````````````

::

    
    <head>
    .
    .
    <?
    if(isset($CSS)){
       echo $html->css($CSS);
    }
    ?>
    .
    .
    </head>

In the layout, we did a validation to know if $CSS has been set, so we
only include it if this occurs.

This way is not very elegant, right?


Second Idea: CSS with the same controller name
----------------------------------------------

In this method, the CSS files will have the same name of their
controllers, that is, an Articles controller will have an article.css.
So we will have a project a little more organized, and we will find
the CSS files easier then before, because both have the same name.

Now, we don't need to do anything in controller, only in the layout.

View Template:
``````````````

::

    
    .
    .
    <head>
    <?
    echo $html->css($this->params["controller"]);
    ?>
    </head>
    .
    .

We used $this->param to know what is the name of the controller. This
array contains many useful information, like controller name, method
name, and others. If you want to know more, and don't want to read
api/manual, just: print_r($this->params), and see what happens.

print_r($this->params);


Third Idea: CSS file with the same name of controller, but only if CSS
file exists
-----------

This method is an update from Second Idea, we will just validate if
CSS file is present in server, if it is, we link it to HTML. The code
for the validation is the following:


View Template:
``````````````

::

    
    <head>
    .
    .
    <?
    if (is_file(APP.WEBROOT_DIR.DS."css".DS.$this->params["controller"].".css")){
    echo $html->css($this->params["controller"]);
    }
    ?>
    .
    .
    </head>

Note that to show the complete file path, we used many constants. Here
is their meanings:
* APP : stores the complete path to current application. For example:
c:\testCake\app\
* WEBROOT_DIR : the webroot directory name.
* DS : or Directory Separator, stores which slash we will use,
depending on the OS. Backslash for windows and slash for *nix.

This method is more organized. Besides, it performs needed
validations. This method is recommend when you have all style for each
controller in a single CSS file.


Fourth Idea: one CSS file for each method of controller
-------------------------------------------------------

This idea, I think it is more accurate and organized. Why? Because we
will separate each CSS for one method.

Again, we will modify only the layout:

View Template:
``````````````

::

    
    <head>
    .
    .
    <?
    if (is_file(APP.WEBROOT_DIR.DS."css".DS.$this->params["controller"]."_".$this-params["action"].".css")){
          echo $html->css($this->params["controller"]."_".$this->params["action"]);
     }
    ?>
    .
    .
    </head>

In this example, we are using the following filename ruler for CSS
files: controllersname_method.css and we already are validating if the
CSS file exists.


Fifth Idea: one CSS file for each method of controller, a little bit
more organized
--------------

This idea is almost equal to the previous, but we won't put all CSS
files in the same directory, we will separate them in foldes with the
same names of controllers.
For each controller, we will have a folder like
webroot/css/controllername and in this folder, we will have all CSS
for each method.

View Template:
``````````````

::

    
    <head>
    .
    .
    <?
    if (is_file(APP.WEBROOT_DIR.DS."css".DS.$this->params["controller"].DS.$this->params["action"].".css")){
           echo $html->css($this->params["controller"]."/".$this->params["action"]);
    }
    ?>
    .
    .
    </head>

Note that we only change "_" for slash.
So if we have a controller called Articles and a method (action)
called read, it will be linked in html, if the file
/css/articles/read.css exists.

This last idea, I think it is the best.

Tulio Faria
`http://www.tuliofaria.net`_`http://www.iwtech.com.br`_

.. _http://www.iwtech.com.br: http://www.iwtech.com.br/
.. _http://www.tuliofaria.net: http://www.tuliofaria.net/
.. meta::
    :title: Some ideas to organize your CSS files and autoload them in CakePHP
    :description: CakePHP Article related to views,Tutorials
    :keywords: views,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

