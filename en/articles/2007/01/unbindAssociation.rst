

unbindAssociation
=================

by %s on January 22, 2007

I needed a quick dynamic way to remove numerous associations I had in
recent application. I decided the best to do that was through
unbindAssociation(), so I made it. This code was adapted from
expects(), a function developed by Tom O'Reilly & expanded by Mariano
Iglesias. As mentioned below I didn't want to maintain lists of
models, so I adapted the code to remove associations instead.
The follow code snippet should be placed in your app\app_model.php,
usuage is below.

Code
````

Model Class:
````````````

::

    <?php 
    class AppModel extends Model{
    	function unbindAssociation(){
    		$arguments = func_get_args();
    		$associations = array ('belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany');
    			
    		if (count($arguments) == 0){
    			$arguments = array();
    			foreach($associations as $assoc){
    					if(empty($this->{$assoc}) == false){
    						array_push($arguments, $assoc);
    					}
    			}
    			if(empty($arguments) == false){
    				$this->unbindAssociation($arguments);
    			}
    	    }
    	    else{
    			foreach($arguments as $index => $argument){
    				if (is_array($argument)){
    					if (count($argument) > 0){
    						$arguments = array_merge($arguments, $argument);
    					}
    					unset($arguments[$index]);
    				}
    			}
    				
    			foreach($arguments as $assoc){
    				if(in_array($assoc, $associations)){
    					$models = array_keys($this->{$assoc});
    					$this->__backAssociation[$assoc] = $this->{$assoc};
    					foreach($models as $model){
    						$this->__backAssociation = array_merge($this->__backAssociation, $this->{$assoc});
    						unset ($this->{$assoc}[$model]);
    					}
    				}
    			}
    		}
    		return true;
    	}
    }
    
    ?>



Usages
``````
It's a pretty straight forward function, you can pass it arrays or you
can pass it strings, I'll demonstrate both.

Inside your controller, accessing model Post

::

    
    $this->Post->unbindAssociation('belongsTo', 'hasMany');
    $this->Post->unbindAssociation(array('belongsTo','hasMany'));

Inside your model

::

    
    $this->unbindAssociation('belongsTo', 'hasMany');
    $this->unbindAssociation(array('belongsTo','hasMany'));



Why?
````
Like I said before, I needed this for a project so I figured I'd
share.

But in reality, this implementation provides a means of dynamic
unbindModel() for an entire association and there's no need for pesky
static arrays.

.. meta::
    :title: unbindAssociation
    :description: CakePHP Article related to unbindAssociation,Snippets
    :keywords: unbindAssociation,Snippets
    :copyright: Copyright 2007 
    :category: snippets

