Serializing find("threaded") data to XML
========================================

by rreyes on March 11, 2009

Ever tried to serialize a full find("threaded") tree to XML? Well, it
won't be easy to consume. Unless you give a try with XMLTreeHelper.


Usage
~~~~~


Controller Class:
`````````````````

::

    <?php 
    class NodesController extends AppController {
    	
    	var $name = 'Nodes';
    	var $components = array('RequestHandler');
    	var $helpers = array("Xmltree");
    	
    	function index() {
    		$data = $this->Node->find("threaded", array("order" => "lft", "contain" => false));
    		$this->set("objects", $data);
    	}
    }
    ?>



View Template:
``````````````

::

    
    <data>
    	<?php 
    		echo $xmltree->serialize($objects, "Node"); 
    	?>
    </data>



Code
~~~~

Helper Class:
`````````````

::

    <?php 
    	class XmltreeHelper extends AppHelper {
    		var $helpers = array('Xml');
    		 
    		function serialize($treeData, $containerName = NULL){
    			debug($treeData);
    			$this->normalize($treeData, $containerName);
    			return $this->Xml->serialize($treeData);
    		}
    		 
    		function normalize(&$children, $containerName){
    			if(sizeof($children) > 0){
    				foreach($children as &$node){
    					$this->normalize($node["children"], $containerName);
    	
    					if(sizeof($node["children"]) > 0){
    						$node[$containerName][$containerName] = array();
    							
    						foreach($node["children"] as &$child){
    							$node[$containerName][$containerName][] = $child[$containerName];
    						}
    					}
    	
    					unset($node["children"]);
    				}
    			}
    		}
    		 
    	}
    ?>



.. author:: rreyes
.. categories:: articles, helpers
.. tags:: helper,tree,xml,Helpers

