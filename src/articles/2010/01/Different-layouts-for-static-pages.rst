Different layouts for static pages
==================================

by raheel.dharolia on January 31, 2010

This simple tutorial tends to explain how easy it is to use different
layouts for static pages in your website using the built-in pages
controller.


Define your Layouts
~~~~~~~~~~~~~~~~~~~
We'll start by writing a configuration file to store layout names for
each static page.

::

    
    <?php
    /**
     * app/config/templates.php
     * Layout Definitions for all static pages
     * Define different layout for different pages here
     */
        
    $config = array();
    
    // Layout: /paramount/views/layouts/static.ctp 
    $config['layout.about-us'] = 'static';
    $config['layout.company-profile'] = 'static';
    
    // Layout: /paramount/views/layouts/homepage.ctp 
    $config['layout.home'] = 'homepage';
    
    ?>



::

    
    <?php
    // Add the following at the end of app/config/bootstrap.php
    Configure::load('templates');
    ?>



Controller Class:
`````````````````

::

    <?php 
    // Edit the pages controller
    class PagesController extends AppController {
    
    function display() {
     ..... Baked Code......
    
    // Insert this line before $this->set and $this->render
      $this->layout = Configure::read('layout.'.$page);
    
      $this->set(compact('page', 'subpage', 'title'));
      $this->render(join('/', $path));
    }
    ?>



Conclusion
~~~~~~~~~~
That's all. Hope this helps.

.. meta::
    :title: Different layouts for static pages
    :description: CakePHP Article related to pages controller,static pages,Tutorials
    :keywords: pages controller,static pages,Tutorials
    :copyright: Copyright 2010 raheel.dharolia
    :category: tutorials

