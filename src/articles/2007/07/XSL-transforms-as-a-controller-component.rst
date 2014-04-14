XSL transforms as a controller component
========================================

by yjaques on July 28, 2007

If you're looking for how to conveniently perform XML/XSL transforms
in cake this article is for you. There are several things to consider:
1. You often will not be using a model coming from your DB, but
perhaps an external source such as an RSS file. This means you must
not attach your controller to a model. 2. There are a number of steps
in setting up DOMS and stylesheets and so on. This is not the kind of
thing that should be in a view. It belongs in the controller, but
often you want to reuse this logic. This means they must go in a
component. 3. XSL files are performing most of the presentation logic
but maybe not all. For this reason it's still best to use a ctp file
for your layout and hand it the transformed xml/xsl from the
controller. If you want an AJAX type response with no other markup
then just set your layout to 'AJAX'
First step is to create a controller that is going to handle your
XML/XSL actions and put it in the controllers directory as
parses_controller.php:

::

    
    <?php 
      class ParsesController extends AppController {
      var $name = "Parses";
      var $uses = null;
      var $layout = 'ajax';
      //custom component that handles common XML/XSL functions
      var $components = array('Xslt');
    ?>

A couple of points to remember:
var $uses = null;
This uncouples your controller from the 'parses' model which doesn't
exist in your DB.

var $layout = 'ajax';
This is a good default for this controller as it enables you to just
return the contents of your transform.

var $components = array('Xslt');
This is a custom component that handles common XML/XSL functions. By
calling it here we can use it from any of our controller's functions.

So, what does this Xslt component consist of? It has two vital
functions. The first returns a transformed XML doc, the second creates
a stylesheet object. This second function is separated out because you
may want to create a stylesheet once and then apply it to multiple
transforms - more efficient. This should be saved as xslt.php in the
controllers/components directory:

::

    
    <?php
    class XsltComponent extends Object {
    	
        function transformXml($xml, $xsl) {
    		$xmldoc = DOMDocument::load($xml);
    		$result = $xsl->transformToXML($xmldoc);
    		return $result;
        }
        
        function createStyleSheet($xslPath) {
        	$xsl = new XSLTProcessor();
    	$xsldoc = new DOMDocument();
    	$xsldoc->load($xslPath);
    	$xsl->importStyleSheet($xsldoc);
    	return $xsl;
        }
    }
    ?>

Now that we have our component we can make a function in
parses_controller.php that accesses its methods:

::

    
      function latestBlogs() {
    	// URL to the posts RSS
    	$xml = "http://".$_SERVER['SERVER_NAME']."/wordpress/feed/";
    	// create a a stylesheet
    	$xsl = $this->Xslt->createStyleSheet(VIEWS.'parses/blog.xslt');
    	//perform the transform;
    	$trans = $this->Xslt->transformXml($xml,$xsl);
    	//set it for the view
    	$this->set('trans', $trans);
      }

This example will get a wordpress blog feed as its XML. There is an
XSL file named blog.xslt in the views/parses folder used for the
transform. We are calling both our component methods, once to create a
stylesheet object, and once to transform the XML. Afterwards, we are
simply setting the result for access by the view. In this case, as the
function is latestBlogs, we should also create a ctp file named
lastest_blogs.ctp in the views/parses folder (alongside our blog.xslt
file). This CTP file can be incredibly simple if we just want our
transform output:

::

    
    <?php
    echo $trans;
    ?>

That's it. Now calling from the url to parses/latestBlogs should get
you the output of your wordpress RSS (provided you have wordpress
running...). In my own case I use this setup together with
requestAction to generate a list of the latest blog entries in my
wordpress site on the home page of my CakePHP site. This is extremely
easy, just a line like this does the trick:

::

    
    	echo $this->requestAction(array('controller' => 'Parses', 'action' => 'latestBlogs'),array('return'));



.. meta::
    :title: XSL transforms as a controller component
    :description: CakePHP Article related to Rss,xml,wordpress,xsl,Tutorials
    :keywords: Rss,xml,wordpress,xsl,Tutorials
    :copyright: Copyright 2007 yjaques
    :category: tutorials

