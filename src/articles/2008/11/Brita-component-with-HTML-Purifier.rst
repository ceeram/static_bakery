Brita component with HTML Purifier
==================================

by debuggeddesigns on November 04, 2008

Brita is a CakePHP Component wrapper class created to take advantage
of the functionality provided by HTML Purifier. HTML Purifier is a
standards-compliant HTML filter library written in PHP. HTML Purifier
will not only remove all malicious code (better known as XSS) with a
thoroughly audited, secure yet permissive whitelist, it will also make
sure your documents are standards compliant, something only achievable
with a comprehensive knowledge of W3C's specifications.


Step 1: Download and unzip archive
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Download an HTMLPurifier archive (either .zip or .tar.gz) at
`http://htmlpurifier.org/download.html`_ and unzip the archive into
the directory /app/vendors/htmlpurifier/

Note: Only the contents in the /app/vendors/htmlpurifier/library/
folder are necessary, so you can remove everything else when using
HTML Purifier in a production environment.

Note 2: The folder /app/vendors/htmlpurifier/library/HTMLPurifier/Defi
nitionCache/Serializer must be writeable by the webserver.



Step 2: Create brita component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/controllers/components/brita.php

Component Class:
````````````````

::

    <?php 
    //cake's version of a require_once() call
    vendor('htmlpurifier'.DS.'library'.DS.'HTMLPurifier.auto'); //use this with the 1.1 core
    //App::import('Vendor','HTMLPurifier' ,array('file'=>'htmlpurifier'.DS.'library'.DS.'HTMLPurifier.auto.php')); //use this with the 1.2 core
    
    class BritaComponent extends Object {
    
        var $controller;
    
        function startup( &$controller ) {
    
            //the next few lines allow the config settings to be cached
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML', 'DefinitionID', 'made by debugged interactive designs');
            $config->set('HTML', 'DefinitionRev', 1);
            //levels describe how aggressive the Tidy module should be when cleaning up html
            //four levels: none, light, medium, heavy
            $config->set('HTML', 'TidyLevel', 'heavy');
            //check the top of your html file for the next two
            $config->set('HTML', 'Doctype', 'XHTML 1.0 Transitional');
            $config->set('Core', 'Encoding', 'ISO-8859-1');
            
            //BritaComponent instance of controller is replaced by a htmlpurifier instance
            $controller->brita =& new HTMLPurifier($config);
            $controller->set('brita',$controller->brita);
    
       }
       
    }
    ?>



Step 3: Use the brita component inside a controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/controllers/tests_controller.php

Controller Class:
`````````````````

::

    <?php 
    class TestsController extends AppController {
        var $name = 'Tests';
        var $components = array('Brita'); //import the Brita Component
           
        function brita() {
            //fake user input that we will purify (for testing)
            $dirty_html = '<br><br><center><font size="2">testing</font></center>';
            //this one line of code does all the purifying
            $clean_html = $this->brita->purify( $dirty_html );
            //set the before and after html for the test view
            $this->set('clean_html',$clean_html);
            $this->set('dirty_html',$dirty_html);
        }   
    }
    ?>



Step 4: Create a test view
~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/views/tests/brita.thtml

::

    
    <div>DIRTY HTML = <?php echo htmlentities($dirty_html);?></div>
    <div style="border:1px solid black;"><?php echo $dirty_html;?></div>
    
    <div>CLEAN HTML = <?php echo htmlentities($clean_html);?></div>
    <div style="border:1px solid black;"><?php echo $clean_html;?></div> 



.. _http://htmlpurifier.org/download.html: http://htmlpurifier.org/download.html
.. meta::
    :title: Brita component with HTML Purifier
    :description: CakePHP Article related to security,wrapper,debuggeddesigns,wc,xss,htmlpurifier,brita,Tutorials
    :keywords: security,wrapper,debuggeddesigns,wc,xss,htmlpurifier,brita,Tutorials
    :copyright: Copyright 2008 debuggeddesigns
    :category: tutorials

