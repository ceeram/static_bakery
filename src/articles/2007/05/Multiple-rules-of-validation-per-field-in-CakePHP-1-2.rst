Multiple rules of validation per field in CakePHP 1.2
=====================================================

by mariano on May 16, 2007

There are many great improvements coming with CakePHP 1.2. On this
article we'll take a look at multiple rules of validation per field,
and how easy it is to use them on our 1.2 models.
On its 1.1 release, CakePHP only allowed us to define one rule of
validation per field. If we needed to specify more than one rule, we
either had to use some handy extensions to CakePHP that are available,
or achieve multiple validation by using the callback method
beforeValidate() in our models. CakePHP 1.2 brings us an improved
technique to specify multiple rules per field, and best of all, it's
backwards compatible, so our existing rules will continue to work.

What better way to start that with an example, specifically the
typical Article CRUD example. We start by creating a simple controller
that provide us with an add form:


Controller Class:
`````````````````

::

    <?php 
    class ArticlesController extends AppController {
    	var $name = 'Articles';
    	var $helpers = array('Form');
    	
    	function add() {
    		if (!empty($this->data)) {
    			// We don't do any real saving, we just validate the model
    			
    			if ($this->Article->create($this->data) && $this->Article->validates()) {
    				$this->set('valid', true);
    			}
    		}
    	}
    }
    ?>

and its matching articles/add.ctp view:


View Template:
``````````````

::

    
    <?php echo $form->create('Article'); ?>
    	<?php echo $form->input('title'); ?>
    	<?php echo $form->input('body'); ?>
    <?php echo $form->end('Add Article'); ?>

Let's take the classic Article model, and add only one validation to
some fields:


Model Class:
````````````

::

    <?php 
    class Article extends AppModel {
    	var $name = 'Article';
    	var $validate = array(
    		'title' => VALID_NOT_EMPTY,
    		'body' => VALID_NOT_EMPTY
    	);
    }
    ?>

Nothing new here, except that as you can see we're not defining the
error messages. We are going to do so in the view. So we go back and
change the add.ctp view so it now looks like this:


View Template:
``````````````

::

    
    <?php echo $form->create('Article'); ?>
    	<?php echo $form->input('title', array('error' => 'Please specify a valid title')); ?>
    	<?php echo $form->input('body', array('error' => 'Please specify a valid body')); ?>
    <?php echo $form->end('Add Article'); ?>

If we try the add action and submit the form with both fields empty,
we'll get our newly defined error messages. What if we also wanted to
add another rule of validation for the title field, where we should
check that the title is at the most 100 characters long? Let's review
the model, and now change it to look like this:


Model Class:
````````````

::

    <?php 
    class Article extends AppModel {
    	var $name = 'Article';
    	var $validate = array(
    		'title' => array(
    			VALID_NOT_EMPTY,
    			array(
    				'rule' => array('maxLength', 100)
    			)
    		),
    		'body' => VALID_NOT_EMPTY
    	);
    }
    ?>

We have just converted the 'title' field to consist of an array of
rules. The first rule is a CakePHP provided regular expression that
checks for a specified value, and the second rule is a method existing
in CakePHP's built in Validation class called 'maxLength'. Since this
method takes an extra parameter (the number of maximum characters the
field can contain), we add it on an array.

So we have now two conditions that can generate an error for the field
title: an empty value, or a value with more than 100 characters. How
do we differentiate the error messages on the view? Let's change the
view so it now looks like:


View Template:
``````````````

::

    
    <?php echo $form->create('Article'); ?>
    	<?php echo $form->input('title', array('error' => array(
    		0 => 'Please specify a valid title',
    		1 => 'The title must have no more than 100 characters'
    	))); ?>
    	<?php echo $form->input('body', array('error' => 'Please specify a valid body')); ?>
    <?php echo $form->end('Add Article'); ?>

As you can see we're setting an error message per rule. 0 corresponds
to the first rule, 1 to the second, and so on. If we wanted more
flexibility (such as having the option to change the order of the
rules and still have the same error message assignment) and needed
more readability, we can then use the string index approach. Change
the model so it now looks like:


Model Class:
````````````

::

    <?php 
    class Article extends AppModel {
    	var $name = 'Article';
    	var $validate = array(
    		'title' => array(
    			'required' => VALID_NOT_EMPTY,
    			'length' => array( 'rule' => array('maxLength', 100) )
    		),
    		'body' => VALID_NOT_EMPTY
    	);
    }
    ?>

and change the view so it now looks like:


View Template:
``````````````

::

    
    <?php echo $form->create('Article'); ?>
    	<?php echo $form->input('title', array('error' => array(
    		'required' => 'Please specify a valid title',
    		'length' => 'The title must have no more than 100 characters'
    	))); ?>
    	<?php echo $form->input('body', array('error' => 'Please specify a valid body')); ?>
    <?php echo $form->end('Add Article'); ?>



Custom Validation
~~~~~~~~~~~~~~~~~

What about custom validation? What if we needed more rules than those
provided by CakePHP's Validation class? Don't sweat, it comes very
easy! All you need to do is set up your own validation functions on
either your model or your AppModel class (if you wish to share them
across your models.) For example, we're going to add a new validation
rule to allow us to specify a minimum and a maximum length for our
title. I know, what's the point when we have both minLength and
maxLength in CakePHP's Validation class? Well, to show how it can be
done :)

Edit the model and change it so it now looks like this:


Model Class:
````````````

::

    <?php 
    class Article extends AppModel {
    	var $name = 'Article';
    	var $validate = array(
    		'title' => array(
    			'required' => VALID_NOT_EMPTY,
    			'length' => array( 'rule' => 'validateLength', 'min' => 5, 'max' => 100 )
    		),
    		'body' => VALID_NOT_EMPTY
    	);
    	
    	function validateLength($value, $params = array()) {
    		$valid = false;
    		
    		$params = am(array(
    			'min' => null,
    			'max' => null,
    		), $params);
    		
    		if (empty($params['min']) || empty($params['max'])) {
    			$valid = false;
    		} else if (strlen($value) >= $params['min'] && strlen($value) <= $params['max']) {
    			$valid = true;
    		}
    		
    		return $valid;
    	}
    }
    ?>

A custom validation function takes one mandatory first parameter: the
value to validate, and must return a boolean value of true when the
value validates, or false when it doesn't. Extra parameters will be
sent to the validation function as an array through its second
parameter, and the values in the array are those values specified in
the validation rule that do not correspond to CakePHP's internal
values (such as rule or allowEmpty.)

.. meta::
    :title: Multiple rules of validation per field in CakePHP 1.2
    :description: CakePHP Article related to ,Tutorials
    :keywords: ,Tutorials
    :copyright: Copyright 2007 mariano
    :category: tutorials

