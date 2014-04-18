Multiple Display Field
======================

The behavior allows us to use multiple display field (such as
"first_name" and "last_name") as a display field for generating list
from a model.
This is really one of common issues: using multiple fields as display
field when using the find('list') function.

Strangely enough, there's only little hint out there about how to do
it "elegantly" in CakePHP. tclineks already make a great article in
`http://bin.cakephp.org/saved/19252#modify`_, but the code just won't
works. I think it's happened because CakePHP now handles find function
differently than the previous versions.

So then I wrote the code below to help anyone who need to use multiple
fields in their display field.
Note: The afterFind function is taken without any change from
tclineks' bin.

Using it is as simple as this:


#. In your model, define that the model is acting as
   MultipleDisplayFieldBehavior. For example, see Figure 1.
#. To generate a list, simply use the find('list') function. For
   example, see Figure 2.

Note: you must define the "displayField" property. This field (which
can be a virtual/non-existent field) will then holds the concatenation
of display fields that you defined.

::

    
    class User extends AppModel {
        var $name = "User";
        var $displayField = "full_name";
        var $actsAs = array('MultipleDisplayFields' => array(
    		'fields' => array('first_name', 'last_name'),
    		'pattern' => '%s %s'
        ));
    }

Figure 1

::

    
    $userList = $this->User->find("list");

Figure 2

The MultipleDisplayFieldsBehavior class is defined as below.
Save it as "multiple_display_fields.php" and put the file inside
"app/models/behaviors/" folder.


Model Class:
````````````

::

    <?php 
    class MultipleDisplayFieldsBehavior extends ModelBehavior {
    	var $config = array();
    	
    	function setup(&$model, $config = array()) {
    		$default = array(
    			'fields' => array($model->name.'.first_name', $model->name.'.last_name'),
    			'pattern' => '%s %s'
    		); 
    		$this->config[$model->name] = $default;
    		
    		if(isset($config['fields'])) {
    			$this->config[$model->name]['fields'] = $config['fields'];
    		}
    		if(isset($config['pattern'])) {
    			$this->config[$model->name]['pattern'] = $config['pattern'];
    		}
    	}
    	
    	function afterFind(&$model, $results) {
    		// if displayFields is set, attempt to populate
    		foreach ($results as $key => $val) {
    			$displayFieldValues = array();
    
    			if (isset($val[$model->name])) {
    				// ensure all fields are present
    				$fields_present = true;
    				foreach ($this->config[$model->name]['fields'] as $field) {
    					if (array_key_exists($field,$val[$model->name])) {
    						$fields_present = $fields_present && true;
    						$displayFieldValues[] = $val[$model->name][$field]; // capture field values
    					} else {
    						$fields_present = false;
    						break;
    					}
    				}
    
    				// if all fields are present then set displayField based on $displayFieldValues and displayFieldPattern
    				if ($fields_present) {
    					$params = array_merge(array($this->config[$model->name]['pattern']), $displayFieldValues);
    					$results[$key][$model->name][$model->displayField] = call_user_func_array('sprintf', $params );
    				}
    			}
    		}
    		return $results;
    	}
    
    
    	function beforeFind(&$model, &$queryData) {
    		if(isset($queryData["list"])) {
    			$queryData['fields'] = array();
    			
    			//substr is used to get rid of "{n}" fields' prefix...
    			array_push($queryData['fields'], substr($queryData['list']['keyPath'], 4));
    			foreach($this->config[$model->name]['fields'] as $field) {
    				array_push($queryData['fields'], $model->name.".".$field);
    			}
    		}
    		//$model->varDump($queryData);
    		return $queryData;
    	}
    }
    ?>

Hope it helps you... :)

.. _http://bin.cakephp.org/saved/19252#modify: http://bin.cakephp.org/saved/19252#modify

.. author:: resshin
.. categories:: articles, behaviors
.. tags:: multiple,behavior,displayfield,display field,Behaviors

