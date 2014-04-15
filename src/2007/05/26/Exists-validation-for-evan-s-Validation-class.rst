'Exists' validation for evan's Validation class
===============================================

by farez on May 26, 2007

'Add-on' method for evan's validation class to check for existence of
value in database.


What is it
~~~~~~~~~~

An additional validation routine for Evan Sagge's very useful
Validation class (`http://bakery.cakephp.org/articles/view/more-
improved-advanced-validation`_).

This method checks that a submitted value exists in the database and
returns true if it does, false if it doesn't. Useful for ensuring that
submitted data is from a predefined list of values and that list is
stored in the database.


How to use it
~~~~~~~~~~~~~

Just add this as a method to the Validation class. Define the
validation rule in your model's $validate member var as follows:

::

    
    var $validate = array('course_id' => array('required', 'exists'=>array('model'=>'Course', 'field'=>'id', 'message'=>'Invalid course ID')));



Here's the code. Hope it's useful :)

::

    
        /**
        * Looks up the submitted value in the given model table and field name and 
        * returns false if it doesn't exist. Default model name is the current model, and
        * default field name is the form's field name.
        * 
        * @param string $fieldName The name of the field to validate.
        * @param array $params 'model' specifies the model to look up, 'field' specifies the field name to use    
        * @return bool True if submitted value exists in model.fieldname, false otherwise
        */
        function validateExists($fieldName, $params)
        {
        	$lookupModelName = isset($params['model'])? Inflector::camelize($params['model']) : $this->model->name;
        	$lookupFieldName = isset($params['field'])? Inflector::camelize($params['field']) : $fieldName;
        	$findFunc = 'findBy'.$lookupFieldName;
        	
        	if ($lookupModelName == $this->model->name)
        	{
        		$model = $this->model;
        	}
        	else
        	{
        		loadModel($lookupModelName);
        		$model = new $lookupModelName;
        	}
        	$result = $model->{$findFunc}($this->data[$this->name][$fieldName]);
        	return $this->_evaluate(($result != false), "does not exist", $fieldName, $params);
        }



.. _http://bakery.cakephp.org/articles/view/more-improved-advanced-validation: http://bakery.cakephp.org/articles/view/more-improved-advanced-validation

.. author:: farez
.. categories:: articles, snippets
.. tags:: validation exists 1.,Snippets

