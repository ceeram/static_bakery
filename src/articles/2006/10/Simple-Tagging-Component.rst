Simple Tagging Component
========================

by bgmill on October 03, 2006

This is a quick and easy tagging component that allows you to handle
text field, comma-separated input.
It first parses the submitted data and then checks for the existence
of each tag, before deciding whether to create it or not. It expects a
model called Tag in a HABTM association with the calling controller.

Save this as /app/controllers/components/tagging.php and include it in
your $components array.


Component Class:
````````````````

::

    <?php 
    class TaggingComponent extends Object
    {
    function tagParse($tagdump) {
    	// first we need to split the tags up and strip whitespace at the beginning and end
    	// not ALL whitespace as we're allowing multiple word tags
    	$tempTagArray = preg_split('/,/',$tagdump);
    	$tagArray = array();
    	foreach ($tempTagArray as $t) {
    		$t = trim($t);
    		if (strlen($t)>0) {
    			$tagArray[] = $t;
    		}
    	}
    	$Tag = new Tag; // create a new Tag object
    	$tagInfo = array(); // create a new array to store tag id and name combo's from db
    	foreach ($tagArray as $t) {
    		if ($res = $Tag->findByName($t)) {
    			// tag exists already, add it to our array
    			$tagInfo[] = $res['Tag']['id'];
    		} else {
    			// tag doesn't exist, lets add it
    			$Tag->save(array('id'=>'','name'=>$t));
    			// now we can add this to our array
    			$tagInfo[] = sprintf($Tag->getLastInsertID());
    		}
    		unset($res);
    	}
    	return $tagInfo;
    }
    
    }
    ?>

Usage :


Controller Class:
`````````````````

::

    <?php 
    ...
    $this->params[’data’][’Tag’][’Tag’] = $this->Tagging->tagParse($theTagField);
    ...
    ?>

This will parse the data back into the correct form for simply doing
a...


Controller Class:
`````````````````

::

    <?php 
    ...
    $this->controller->save($this->params[’data’]);
    ...
    ?>

(originally posted at `http://ben.milleare.com/2006/08/29/tagging-in-
cakephp/`_)

.. _http://ben.milleare.com/2006/08/29/tagging-in-cakephp/: http://ben.milleare.com/2006/08/29/tagging-in-cakephp/
.. meta::
    :title: Simple Tagging Component
    :description: CakePHP Article related to tag,tagging,tags,component,Components
    :keywords: tag,tagging,tags,component,Components
    :copyright: Copyright 2006 bgmill
    :category: components

