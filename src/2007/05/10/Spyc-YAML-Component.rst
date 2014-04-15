Spyc YAML Component
===================

by c1sc0 on May 10, 2007

Spyc is a simple YAML parsing class. If you don't know YAML, it's a
sweet human-readable data serialization language. Think XML without
the tag soup ! Like most external php libraries Spyc can be easily
integrated in your CakePHP app.(Sources:
[url]http://spyc.sourceforge.net[/url] [url]http://www.yaml.org[/url])
First, you need to download the spyc library from
`http://spyc.sourceforeg.net`_. Copy the spyc.php file to your vendors
directory.


Component
`````````

There are only 2 interesting methods in the Spyc Class, so we'll write
wrappers around these. Our component class is called SpycYAMLComponent
to avoid namespace conflicts with Spyc itself.


Component Class:
````````````````

::

    <?php 
    <?php 
    class SpycYAMLComponent extends Object {
    	
    	
        function startup(&$controller)
        {
            
    		vendor('spyc');
    		$this->controller = $controller;
    		
        }
    	
    	function YAMLLoad($string) {
    		
    		$array = Spyc::YAMLLoad($string);
    		return $array;
    	}
    
    	function YAMLDump($array) {
    		$yaml = Spyc::YAMLDump($array);
    		return $yaml;
    	}
    
    
    }
    ?>
    ?>



Example Controller
``````````````````

Not that $this->spycyaml is all lowercase. The example YAML is pretty
naive, but I suppose you get the picture.


Controller Class:
`````````````````

::

    <?php 
    <?php 
    class SpycTestController extends AppController
    {
        var $name = 'SpycTest';
        var $components = array('spycyaml');
    
        function index () {
    	
    		$array = $this->spycyaml->YAMLLoad("'test' : 'some'");
    		print_r($array);
    		$yaml = $this->spycyaml->YAMLDump($array);
    		print $yaml;
    	
    	}
    
        
    }
    ?>
    ?>



Postscriptum
````````````

So, why would you want to wrap such a simple library in a component?


.. _http://spyc.sourceforeg.net: http://spyc.sourceforeg.net/

.. author:: c1sc0
.. categories:: articles, components
.. tags:: component,spyc,yaml,Components

