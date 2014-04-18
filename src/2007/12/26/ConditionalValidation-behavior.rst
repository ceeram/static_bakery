ConditionalValidation behavior
==============================

[Update]: as suggested few modifications : - model method can be set
in the condition - syntax for fields in the condition can be
'Model.field' or "Model.field" (see example) - usage of the
beforeValidate callback - "restore" parameter is now part of the
settings Suggestions and comments are welcome. The validations in cake
1.2 are great, but sometimes I needed to have conditional validation.
Because the validations are set at model level, i did implement a
behavior that allows to turn on or off validation. I decided to share
this on Bakery. Do not hesitate to comment, I am not sure I have
handle this in the correct way.
The behavior itself (model/behaviors/conditional_validation.php)

::

    
    <?php
    /*
     * conditional behavior, allow to turn on/off validations based on condition
    */
    
    class ConditionalValidationBehavior extends ModelBehavior
    {
        /* store the current validation, useful if case of multiple 'save' in same action
        *  see beforeValidate and 'restore' parameter (also afterSave)
        */
    
        var $savedValidation = array();
    
        /* initialize the settings */
    
    	function setup(&$model, $params = array())
    	{
            $this->settings[$model->name] = $params;
    	}
    
    	/* in case we need to restore the validation */
    
        function afterSave(&$model)
        {
            if($this->savedValidation && is_array($this->savedValidation) && in_array($model->name, $this->savedValidation))
            {
                $model->validate = $this->savedValidation[$model->name];
                unset($this->savedValidation[$model->name]);
            }
        }
    
        // set the model $validate array
    
        function beforeValidate(&$model)
        {
            if(empty($this->settings[$model->name]))
                return;
    
            if(isset($this->settings[$model->name]['restore']) && $this->settings[$model->name]['restore'])
                $this->savedValidation[$model->name] = $model->validate;
    
            /* the data in condition must be coded as $data['Model']['field'] (do not use $this->data) */
    
            foreach($this->settings[$model->name] as $condition)
            {
                /* avoid the "restore" parameter */
                
                if(!is_array($condition))
                    continue;
                    
                if(isset($condition['condition']) && !empty($condition['condition']))
                {
                    if(method_exists($model, $condition['condition']))
                    {
                        $rc = $model->{$condition['condition']}();
                    }
                    else
                    {
                        $f  = create_function('&$data', $this->_formatCondition($condition['condition']));
                        $rc = $f($model->data);
                    }
                }
                else
                    $rc = true;
    
                /* reference to this->data passed to the function to evaluate the condition,
                    if 'condition' is not present, the condition will be assumed as true
                */
    
                if($rc)
                {
                    $option = array_merge(array('remove' => array(), 'validate' => array()), $condition);
    
                    /* remove the fields in $validate */
    
                    if(!empty($option['remove']))
                    {
                        foreach($option['remove'] as $rmfield)
                            unset($model->validate[$rmfield]);
                    }
    
                    /* add some fields to validate */
    
                    if(!empty($option['validate']))
                    {
                        foreach($option['validate'] as $key => $addfield)
                            $model->validate[$key] = $addfield;
                    }
                }
            }
    
            return;
        }
    
        // check the fields in conditions "Model.field" will be replace by $data['Model']['field'], sane with 'model.field'
        // They might be a better way (for more level), but I am not so confortable with regexp...
        // comments and suggestions are welcome
    
        function _formatCondition($condition)
        {
            $nb = preg_match_all('/(\'|"|\b)+\w+\.+\w+\1/', $condition, $match, PREG_OFFSET_CAPTURE);
    
            if($nb)
            {
                $search = array('\'', '"', '.');
                $rep    = array('', '', '\'][\'');
    
                foreach($match[0] as $repfield)
                {
                    $condition = str_replace($repfield[0], '$data[\''.str_replace($search, $rep, $repfield[0]).'\']', $condition);
                }
            }
            return 'return '.$condition.' ? true : false;';
        }
    }
    ?>

The behavior will be executed by the beforeValidate callback, I
removed the implementation in app_model.

Sample usage

Suppose I have a customer model, and I capture the Employement,
Marital status, Salary and spouse_ssn.
I want a Salary only if the customer is not "unemployed".
I want the spouse_ssn if the customer is married

My model looking could look like this :

Model Class:
````````````

::

    <?php 
    <?php
    
    class Customer extends AppModel {
    
    	var $name = 'Customer';
    	var $validate = array(
    		                     'spouse_ssn' => VALID_NOT_EMPTY);
    
    
      var $actsAs  = array('ConditionalValidation' => array(array('condition' => '$data[\'Customer\'][\'employement\'] != "U"',
                                                                  'validate' => array('salary' => VALID_NUMBER)),
                                                            array('condition' => '$data[\'Customer\'][\'marital_status\'] != "M"',      
                                                                  'remove'    => array('spouse_ssn'))
                                                           )
    	);
    ?>

Note the syntax of the condition, you have to use $data and not
$this->data. A reference to model->data is passed to the function for
evaluating the condition.
You can combine the remove and validate, the validate takes an array
of type $this->validate.

The updated version can now accept a method of the model as condition.
The syntax of the fields in condition can also now be Model.field (I
am not good at regexp, so suggestion are welcome for deeeper level or
side effects I did not think of):


Model Class:
````````````

::

    <?php 
    <?php
    
    class Customer extends AppModel {
    
    	var $name = 'Customer';
    	var $validate = array('spouse_ssn' => VALID_NOT_EMPTY);
    
    
      var $actsAs  = array('ConditionalValidation' => array('restore' => true,
                                                            array('condition' => 'Customer.employement != "U"',
                                                                  'validate' => array('salary' => VALID_NUMBER)),
                                                            array('condition' => 'checkMaritalStatus',      
                                                                  'remove'    => array('spouse_ssn'))
                                                           )
    	);
    
        function checkMaritalStatus()
        {
            return $this->data['Customer']['marital_status'] == 'M' ? true : false;
        }
    ?>

The new callBack beforeValidate is great to process the
"conditionalValidation". Note that the parameter "restore", is now
part of the parameters and the original validations will be restored
after save.

I have tested this for my own needs, I am sure it could be extended to
more sophisticated.



.. author:: francky06l
.. categories:: articles, behaviors
.. tags:: behavior,Behaviors

