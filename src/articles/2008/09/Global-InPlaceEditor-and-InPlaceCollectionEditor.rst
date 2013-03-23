Global InPlaceEditor and InPlaceCollectionEditor
================================================

by %s on September 18, 2008

Edit in place is a nice feature to offer, especially on the backend or
admin side of the site. By adding your ajax update methods in the
app_controller, you can substantially reduce the amount of code you
write and maintain. As well, you will be able to provide edit in place
in any view, and by providing an update path in the view, edit in
place becomes available globally.
This is loosely based on this article
`http://bakery.cakephp.org/articles/view/using-the-ajax-helper-for-
inplaceeditor-and-inplacecollectioneditor-fields`_, however, the code
for the controller has been abstracted out and is placed in
app_controller. The view code is much the same.

We'll start with the controller code, and then some sample view code.


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
    
    	function beforeRender(){
    		// ajax stuff
    		if( $this->RequestHandler->isAjax() ) {
    		    $this->layout = 'ajax';
    		    $this->autoRender = false;
    		    Configure::write('debug', 0);
    		}
    	} 
    	function admin_ajax_edit($id = null, $field_name) {
    		if(!$id) {
    			$this->set('error', true);
    			return;
    		}
    		$model = $this->modelClass;
    		$value = $this->params['form']['value'];
    		$this->$model->id = $id;
    		if (!$this->$model->saveField($field_name, $value)) { // Update the field
    			$this->set('error', true);
    		 }
    		$this->$model->read(array($field_name), $id);
    
    		if ( substr($field_name,-3) == '_id'){
    			// Convert the field name to a Model name by removing '_id' and camelizing
    			$related_model = Inflector::camelize( substr($field_name,0,strlen($field_name)-3) );
    			foreach( $this->$model->belongsTo as $assoc ){
    				if ( $assoc['className']== $related_model && $assoc['foreignKey'] == $field_name ){
    					$value = $this->$model->$related_model->field($this->$model->$related_model->displayField,array('id' => $value));
    					break;
    				}
    			}
    
    		}
    
    		$this->beforeRender();
    		echo $value;
    	}
    	/**
    	 * @return array each key:pair value in the passed array is broken into individual arrays,
    	 * then stored in the returned array. This is the format which allows the EditInPlace.Collection
    	 * to use the passed php associative array as a javascript array.
    	 */
    	function formatAjaxCollection($collection = array()) {
    		if(!is_array($collection)) {
    			$this->set('error', true);
    			return;
    		}
    		$formattedListAjax = array();
    		foreach($collection as $key => $value) {
    			$formattedListAjax[] = array($key,$value);
    		}
    		return $formattedListAjax;
    	}
    ?>

The beforeRender code is pretty standard: detect if its an ajax
request, turn off autoRender, set debug value to zero. The
admin_ajax_edit() code handles both text field edits and collection
edits. Since we are already using a framework that values convention
over configuration, naming matters. Note the block of code after the
call to saveField; it is specific to collection editing. The second
argument passed to the $ajax->editor view method specifies if this
code should be invoked. We'll take a look at the text field edit
first, then the collection editing.



.. _http://bakery.cakephp.org/articles/view/using-the-ajax-helper-for-inplaceeditor-and-inplacecollectioneditor-fields: http://bakery.cakephp.org/articles/view/using-the-ajax-helper-for-inplaceeditor-and-inplacecollectioneditor-fields
.. meta::
    :title: Global InPlaceEditor and InPlaceCollectionEditor
    :description: CakePHP Article related to prototype,scriptaculous,inplaceeditor,edit in place,Tutorials
    :keywords: prototype,scriptaculous,inplaceeditor,edit in place,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

