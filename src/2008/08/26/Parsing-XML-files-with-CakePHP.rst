Parsing XML files with CakePHP
==============================

by fahad19 on August 26, 2008

Simple tutorial for parsing xml files quickly using the core XML class
of cakephp.
After spending one whole evening, I found out CakePHP has its own XML
class for handling xml files. In the meantime, i tried SimplePie (for
RSS only, not XML), SimpleXML, and XMLize. But still they werent of
any great help compared to cakephp's core XML class (and some Sets).

How to parse it then?
First of all, you need to import the XML class in your controller
class using App::import(). Here is the controller class parsing a
particular xml file and printing out (like print_r) the returned
array.


Controller Class:
`````````````````

::

    <?php 
    
      class ParseController extends AppController {
      var $name = "Parse";
      var $uses = array('MyModel');
    
      function xml() {
        // import XML class
        App::import('Xml');
    
        // your XML file's location
        $file = "my_xml_file.xml";
    
        // now parse it
        $parsed_xml =& new XML($file);
        $parsed_xml = Set::reverse($parsed_xml); // this is what i call magic
    
        // see the returned array
        debug($parsed_xml);
      }
    }
    
    ?>



.. author:: fahad19
.. categories:: articles, tutorials
.. tags:: xml,set,parse,Tutorials

