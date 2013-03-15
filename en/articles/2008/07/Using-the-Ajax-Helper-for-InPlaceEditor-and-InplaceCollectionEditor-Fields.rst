

Using the Ajax Helper for InPlaceEditor and InplaceCollectionEditor
Fields
======

by %s on July 07, 2008

Although there is quite a bit of information available for using the
Scriptaculous InPlacEditor, there is not much information for using it
with CakePHP. In addition, there is very little information on using
the InPlaceCollectionEditor for select boxes. Many people are familiar
with the inplace editor on Flickr. I am taking it a little further by
using these editors on a combination view/edit page to view and edit
contacts as well as related model data. Much of what you will find in
this tutorial is similar to what you will find elsewhere, with the
exception of the update method I wrote. Most of the update methods
work fine with text fields, however, they return the key and not the
value when updating a select box. I will cover the use of both the
InPlaceEditor for text fields, as well as the InPlaceCollectionEditor
for select boxes. The collection feature is now integrated into the
editor function. [b]Update: 12/04/2010[/b] In addition to fixing
several typos, I am including some updates for Scriptaculous 1.8.2+
and CakePHP 1.3+.
Ok we are going to use a simple contact form for our example. The form
will have a text field for the contact's name and a select box for the
contact's state. We need a model for Contact and State; a Contacts
Controller, and a Contact View for the form.

We'll assume you already have a contacts table with id, name, and
state_id fields and a states table with an id and name field. The
state_id field is a foreign key to the state's id. Now let's move on
to the model. Your Contact model should look something like this:


Model Class:
````````````

::

    <?php 
    class Contact extends AppModel {
    
    	var $name = 'Contact';
    	var $belongsTo = array(
    			'State' => array(
                                    'className' => 'State',
    				'foreignKey' => 'state_id'
    			)
            );
    }
    ?>

Your State model should look something like this:


Model Class:
````````````

::

    <?php 
    class State extends AppModel {
    
    	var $name = 'State';
    }
    ?>

Now let's move on to your Contacts Controller which should start out
looking something like this:


Controller Class:
`````````````````

::

    <?php 
        class ContactsController extends AppController {
    
            var $name = 'Contacts';
            var $uses = array('Contact', 'State');
            //CakePHP 1.2
            var $helpers = array('Html', 'Form', 'Ajax', 'Javascript');
            //CakePHP 1.3
            var $helpers = array('Html', 'Form', 'Js');
    }
    ?>

If you are using Cake 1.2 you need to include the Ajax Helper. In
CakePHP 1.3, the Ajax Helper has been deprecated and replaced by the
Js Helper which does not include a method for handling the editors. As
a result, we have to handle things a little differently.

Now lets add following method to your Contacts Controller. This method
is responsible for updating the database and returning the updated
value back to the view. This is where most of the other solutions you
will find choke on the select boxes. Instead of returning the state's
name, they return the state's id. The database gets update properly,
however, the state's id is sent back to the view. I modified this
method to work with CakePHP and the common scenario of handling
foreign key associations in a select box. Let's add the ajax_update
method and then discuss it.


Controller Class:
`````````````````

::

    <?php 
        function ajax_update($id,$field){ 
            //Step 1. Update the value in the database
    	$value = $this->params['form']['value']; //new value to save 
    	$this->Contact->id = $id; 
            if (!$this->Contact->saveField($field,$value,true)) { // Update the field
    	    $this->set('error', true); 
    	} 
            $contact = $this->Contact->read(array($field), $id); 
    
            //Step 2. Get the display value for the field if the field is a foreign key
            // See if field to be updated is a foreign key and set the display value
            if (substr($sub,-3) == '_id'){
            
                // Chop off the "_id"
                $new_field = substr($sub,0,strlen($field)-3); 
    
                // Camelize the result to get the Model name
                $model_name = Inflector::camelize($new_sub);
    
                // See if the model has a display name other than default "name"; 
                if (!empty($this->$model_name->display_field)){
                    $display_field = $this->$model_name->display_field;
                }else {
                    $display_field = 'name';
                }
            
                // Get the display value for the id
                $value = $this->$model_name->field($display_field,array('id' => $value));
            }
    
            //Step 3. Set the view variable and render the view.
            $this->set('value',$value); 
            $this->beforeRender();
            $this->layout = 'ajax';
        } 
    ?>

This method is passed the id of the contact record to update and the
updated value. There are three steps to this method:

First, it updates the database with the new value.

Then, we check to see if the field that was just updated is a foreign
key select box, if so, we either get the Model's $display_field or the
default display field "name."

The last step is setting the view variable and rendering the view.

Now we also need a method for handling the view so we also add the
following method the controller:


Controller Class:
`````````````````

::

    <?php 
        function view($id = null) {
    
            if (!$id) {
                $this->redirect(array('action'=>'index'));
            }
    	$this->set('contact', $this->Contact->read(null, $id));
    
            // Build the states array and set the view variable
            $states = $this->State->find('list');
            foreach ($states as $key => $value) {
                $stateListAjax[] = array($key,$value);
            }
    	
            $this->set('stateListAjax', $stateListAjax);
        }       
    ?>

This method creates the array of states to be used for the select box
and sets the view variables.

Now, we need to include the RequestHandler Component and disable
debugging output for the AJAX calls, so modify your App Controller so
it looks like this:


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
       
        var $components = array('RequestHandler');
    
        function beforeRender() {
            if($this->RequestHandler->isAjax() || $this->RequestHandler->isXml()) { 
                Configure::write('debug', 0); 
    	} 
        }
    }
    ?>

Now we can move on to the view.

Depending on which version of CakePHP and Scriptaculous you are using,
you will need to do things here a little differently. In addition,
there are some additional considerations to think about if you are
also using another library such as jQuery.

The first thing we need to do here is import the Prototype and
Scriptaculous libraries. The libraries can be imported in the view
with CakePHP 1.3 and above, however, they need to be imported a little
differently.


View Template:
``````````````

::

    
    <?php 
        echo $this->Html->script('prototype', array('inline' => false));
        echo $this->Html->script('scriptaculous', array('inline' => false)); 
    ?>
    <label for="name"><?php __('Name');?>:</label><div id="name"><?php echo $contact['Contact']['name'];?></div>
    <label for="state_id"><?php __('State');?>:</label><div id="state_id"><?php echo $contact['State']['name'];?></div>

In CakePHP 1.2 You can use krababbel's solution:

View Template:
``````````````

::

    
    <?php
    $this->addScript("prototype", $javascript->link('~path-goes-here/prototype'));
    $this->addScript("scriptaculous", $javascript->link('~path-goes-here/scriptaculous'));
    ?>
    <label for="name"><?php __('Name');?>:</label><div id="name"><?php echo $contact['Contact']['name'];?></div>
    <label for="state_id"><?php __('State');?>:</label><div id="state_id"><?php echo $contact['State']['name'];?></div>

If you are using jQuery or another library that may conflict with
another library, I have found that you need to include Prototype and
Scriptaculous last. The easiest way to accomplish this is to include
them in your layout after the $scripts_for_layout.


View Template:
``````````````

::

    
    <!DOCTYPE html>
    <html>
    <head>
    	<title>
    		<?php echo $title_for_layout; ?>
    	</title>
    <?php
        echo $this->Html->script('jquery-1.4.3.min') . "\n";
        echo $scripts_for_layout;
        $this->Js->JqueryEngine->jQueryObject = 'jQuery';
        echo $this->Html->scriptBlock('
            var jQuery = jQuery.noConflict();
        '); //Tell jQuery to go into noconflict mode
     
       echo $this->Html->script('prototype') . "\n";
        echo $this->Html->script('scriptaculous') . "\n"; 

Now we need to add the actual InPlaceEditor calls.


View Template:
``````````````

::

    
    
    <?php echo $ajax->editor('name', // This is the id of the contact name <DIV>.
    '/path/to/contacts/ajax_update/'.$contact['Contact']['id'].'/name', // Path to the update method.
    array("okButton" => "false", // Disable the submit button and use submitOnBlur
    "cancelLink" => "false", // Disable the cancelLink (Looks neater)
    "submitOnBlur" => "true")); // Enable Submit on Blur
    
    <?php echo $ajax->editor('state_id', // The id of the State <DIV>
    '/path/to/contacts/ajax_update/'.$contact['Contact']['id'].'/state_id', //Path to the update method
    array("okButton" => "true", // This time we need the OK button.
    "cancelLink" => "false", // Disable the cancelLink (Looks neater)
    "submitOnBlur" => "false", // Does not work with collection editor, so we disable it here
    "collection" => $stateListAjax)); //Here we pass the array of states to display in the select box.
    ?> 

If you want to plan for the future removal of the Ajax helper, you can
do this with CakePHP 1.3 and later.



View Template:
``````````````

::

    
    <?php
    echo $this->Html->scriptBlock('
        new Ajax.InPlaceEditor(
            'name', 
            '/path/to/ajax_update/' . $contact['Contact']['id'] . '/name', 
            {
                okControl:false, 
                cancelControl:false, 
                submitOnBlur:true, 
                ajaxOptions:{
                    asynchronous:true, 
                    evalScripts:true
                }
            }
        );
        new Ajax.InPlaceEditor(
            'state_id', 
            '/path/to/ajax_update/' . $contact['Contact']['id'] . '/state_id', 
            {
                okControl:false, 
                cancelControl:false, 
                submitOnBlur:false, 
                collection:' . $this->Js->object($stateListAjax) . '
                ajaxOptions:{
                    asynchronous:true, 
                    evalScripts:true
                }
            }
        );
    ');
    ?> 

In the first call, we tell the editor that we are updating the Contact
name, then pass the url to the update method passing the contact's id
and id of the field to be updated. We disable the OK button and cancel
links for a smoother look and then enable the submitOnBlur feature
that will submit the form when you hit ENTER or the field loses focus.

The second call is for the select box which looks similar, with the
exception that we have enabled the OK button because we can't use
submitOnBlur with a select box, and we pass the array of states to be
used when rendering the select box full of states. In the CakePHP 1.3
version, I have not tested the Js Helper's object method here, but it
should work.

We also need a view to return the value from the Ajax call.

ajax_update.ctp

View Template:
``````````````

::

     
    <?php e($value);?>

Now just browse to the url of the view, passing a contact id to see
the form. When you click the text box and change the value, the value
is updated and pushed back to the view. When you click the State
field, the select box appears and you select a state. The table is
updated with the state id and state name is pushed back to the view.
Sweet!!

Occasionally, you will have a field that is empty. This can wreak
havoc on your layout. There are two solutions to this. You can either
use CSS to set the height or min-height of that , or you can test for
an empty value and fill the with a comment. You would do the Name
field like this.


View Template:
``````````````

::

    
    <label for="name"><?php __('Name');?>:</label><div id="name">
    <?php 
        if (!empty($contact['Contact']['name'])) {
            echo $contact['Contact']['name'];
        } else {
            echo 'Click to add...';
        }
    ?>
    </div>


.. meta::
    :title: Using the Ajax Helper for InPlaceEditor and InplaceCollectionEditor Fields
    :description: CakePHP Article related to scriptaculous,inplaceeditor,edit in place,inplacecollectioneditor,Tutorials
    :keywords: scriptaculous,inplaceeditor,edit in place,inplacecollectioneditor,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

