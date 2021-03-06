Timestamp Custom Validation
===========================

by aparsons on July 28, 2007

An example of how to validate timestamps using a custom method.


Model Class:
````````````

::

    <?php 
    class Example extends AppModel {
    	var $name = 'Example';
    	
    	var $validate = array(
    		'published' => array(
    			'datetime' => array(
    				'rule' => array('datetime'),
    				'message' => 'This field must be a valid timestamp.',
    			),
    		),
    	);
        
    	
    	function datetime($data) {
    		$value = array_values($data);
    		$value = $value[0];
    		$regex = '%^((?:2|1)\\d{3}(?:-|\\/)(?:(?:0[1-9])|(?:1[0-2]))(?:-|\\/)(?:(?:0[1-9])|(?:[1-2][0-9])|(?:3[0-1]))(?:T|\\s)(?:(?:[0-1][0-9])|(?:2[0-3])):(?:[0-5][0-9]):(?:[0-5][0-9]))$%';
    		return preg_match($regex, $value);
    	}
    }
    ?>


.. meta::
    :title: Timestamp Custom Validation
    :description: CakePHP Article related to model,validation,code,timestamp,Models
    :keywords: model,validation,code,timestamp,Models
    :copyright: Copyright 2007 aparsons
    :category: models

