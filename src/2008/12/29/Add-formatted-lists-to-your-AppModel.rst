Add formatted lists to your AppModel
====================================

by mushookies on December 29, 2008

This code will add formatted list functionallity to find you can easy
replace the $this->Model->find('list'); with
$this->Model->find('formattedlist', array('fields' =>
array('Model.id', 'Model.field1', 'Model.field2', 'Model.field3'),
'format' => '%s-%s %s')); and get option tag output of:
Model.field1-Model.field2 Model.field3 . Even better part is being
able to setup your own format for the output!
First you have to copy this to your app_model.php

::

    <?php
    class AppModel extends Model {
    	
    	
    	function find($type, $options = array()) {
            switch ($type) {
                case 'formattedlist':
                    if(!isset($options['fields']) || count($options['fields']) < 3) {
                        return parent::find('list', $options);
                    }
    				
                    $this->recursive = -1;
    				//setup formating
    				$format = '';
    				if(!isset($options['format'])) {
    					for($i = 0; $i < (count($options['fields']) - 1); $i++)
    						$format .= '%s ';
    					
    					$format = substr($format,0,-1);
    				} else
    					$format = $options['format'];
    				
    				//get data
                    $list = parent::find('all', $options);
    				// remove model alias from strings to only get field names
                    $tmpPath2[] = $format;
    				for($i = 1; $i <= (count($options['fields']) - 1); $i++) {
                        $field[$i] = str_replace($this->alias.'.', '', $options['fields'][$i]);
    					$tmpPath2[] = '{n}.'.$this->alias.'.'.$field[$i];
                    }
    				//do the magic?? read the code...
                    return Set::combine($list, '{n}.'.$this->alias.'.'.$this->primaryKey,
                                     $tmpPath2);
                break;                       
    			
                default:              
                    return parent::find($type, $options);
                break;
            }
        }
    
    }
    ?>


Then you should now be able to use it like so:

::

    
    $this->Model->find('formattedlist',
    			array(
    				'fields'=>array(
    					'Model.id', // allows start with the value="" tags field
    					'Model.field1', // then put them in order of how you want the format to output.
    					'Model.field2',
    					'Model.field3',
    					'Model.field4',
    					'Model.field5',
    				),
    				'format'=>'%s-%s%s %s%s'
    			)
    		);

That is all..



.. author:: mushookies
.. categories:: articles, snippets
.. tags:: find list formatted ,Snippets

