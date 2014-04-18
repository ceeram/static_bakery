Validation in another controller
================================

You have 2 controllers, posts and comments, you want to be able to
have a form on a posts view which submits to the comments controller,
which does its thing then redirect to referrer - easy! BUT you also
want the validation errors (if any) to display on the posts view - but
currently they get lost when your redirect. Heres how to get around
it!


The problem
~~~~~~~~~~~
Sample posts/view.ctp

View Template:
``````````````

::

    
    <div class="post">
    <h2><?php  __('Post');?></h2>
    
    <?php echo $post['Post']['body']?>
    
    <fieldset>
    <?php 
    echo $form->create('Comment');
    echo('<legend>'.__('Add comment',true).'<legend>');
    echo $form->input('title');
    echo $form->input('body');
    echo $form->input('author_name');
    echo $form->input('author_email');
    echo $form->input('author_url');
    echo $form->end('submit')
    ?>
    </fieldset>
    
    </div>

I've hit this problem more than once, a nice way around it is to use
AJAX to call up and submit the form, just like the bakery does -
however ajax is not always an option, so poLK and i have come up with
a very nice solution, even if i do say so myself!


The solution
~~~~~~~~~~~~
Session to the rescue , after puzzling over this for a while and
talking with a few people on #cakephp it seemed that the simplest and
quickest solution was going to be to set the validation data into the
session from the validating controller (e.g. Comments) and then pull
it out later in the viewing controller (e.g. Posts) ready to be
displayed.

So thats exactly what we've done, we created function called
_persistValidation to simplify getting and setting the data to the
session.

Add the following function to your app_controller.php


Controller Class:
`````````````````

::

    <?php 
    /**
    	 * Called with some arguments (name of default model, or model from var $uses),
    	 * models with invalid data will populate data and validation errors into the session.
    	 *
    	 * Called without arguments, it will try to load data and validation errors from session 
    	 * and attach them to proper models. Also merges $data to $this->data in controller.
    	 * 
    	 * @author poLK
    	 * @author drayen aka Alex McFadyen
    	 * 
    	 * Licensed under The MIT License
    	 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
    	 */
    	function _persistValidation() {
    		$args = func_get_args();
    		
    		if (empty($args)) {
    			if ($this->Session->check('Validation')) {
    				$validation = $this->Session->read('Validation');
    				$this->Session->del('Validation');
    				foreach ($validation as $modelName => $sessData) {
    					if ($this->name != $sessData['controller']){
    						if (in_array($modelName, $this->modelNames)) {
    							$Model =& $this->{$modelName};
    						} elseif (ClassRegistry::isKeySet($modelName)) {
    							$Model =& ClassRegistry::getObject($modelName);
    						} else {
    							continue;
    						}
    		
    						$Model->data = $sessData['data'];
    						$Model->validationErrors = $sessData['validationErrors'];
    						$this->data = Set::merge($sessData['data'],$this->data);
    					}
    				}
    			}
    		} else {
    			foreach($args as $modelName) {
    				if (in_array($modelName, $this->modelNames) && !empty($this->{$modelName}->validationErrors)) {
    						$this->Session->write('Validation.'.$modelName, array(
    														'controller'			=>	$this->name,
    														'data' 					=> $this->{$modelName}->data,
    														'validationErrors' 	=> $this->{$modelName}->validationErrors
    						));
    				}
    			}
    		}
    	}
    ?>

We also wanted to make the act of pulling the data out of the session
seamless, so no additional code would be needed in the viewing
controller (Posts), so were going to pull out the data automatically,
using beforeRender().

Add the following function to your viewing controller or
app_contoller.php (if your lazy, like all good programmers are, and
want it enabled for all controllers).


Controller Class:
`````````````````

::

    <?php 
    	function beforeRender(){
    		$this->_persistValidation();
    	}
    ?>

With that in place, all you need to do is call _persistValidation,
from your validating controller (Comments) before you redirect e.g.


Controller Class:
`````````````````

::

    <?php 
    	function add() {
    		if (!empty($this->data)) {
    			$this->cleanUpFields();
    			
    			$this->Comment->create();
    			if ($this->Comment->save($this->data)) {
    				$this->Session->setFlash('The Comment has been saved');
    			} else {
    				$this->_persistValidation('Comment');
    				$this->Session->setFlash('The Comment could not be saved. Please correct the errors and try again.');
    			}
    		}
    		$this->redirect($this->referer(), null, true);
    	}
    ?>

Yup, its that simple, just $this->_persistValidation('Comment'); and
your done!!

Enjoy



Gotchas
+++++++

In most situations this should just work, but there are situations
where it can cause problems.

Say you have Post->Message and Message->UserTo Message->UserFrom,
after a restore, both UserTo and UserFrom (as they are references to
one model instance) will be initialized with the correct data, BUT not
with array('UserTo'=> etc - instead with array('User' =>
There may be others, please if you find them and or any improvements
let me know and i will update the tutorial.


.. author:: drayen
.. categories:: articles, tutorials
.. tags:: redirect,session,validation,Tutorials

