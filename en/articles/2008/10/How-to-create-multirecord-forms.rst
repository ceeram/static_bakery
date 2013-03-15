

How to create multirecord forms
===============================

by %s on October 22, 2008

One of the things I found a bit difficult to accomplish was the
ability to create, update and delete multiple records in one HTML
page. In this article I will show you the basic principles of this
concept together with a simple implementation. For advanced Cake
developers, this article will most likely sound as a â€˜been there
done thatâ€™ tutorial. But for beginning bakers I think this article
will help you better understand this concept, but I do expect you
already know the CakePHP basics like naming conventions etc. I should
also note that for the sake of simplicity I tried to write as less
code as possible just to make things work. Before you start: You need
to download the prototype.js from [url]http://prototypejs.org[/url]
and put it in the webroot/js folder because I'm using a small piece of
ajax here too ;-)
Letâ€™s get to work :-) For demostration purposes, letâ€™s create a
table called â€˜newsâ€™ with the following fields:

id [int 10] title [varchar 255] message [text]
Also, create an empty News model class and a News controller with
scaffolding enabled. Now test if your setup is working by adding some
records. This is also nessecary because I will start by creating the
â€œeditâ€ method first, so it would come in handy if you already have
some records available.


Editing mulitple records
~~~~~~~~~~~~~~~~~~~~~~~~
In order to make multirecord â€œedit formsâ€, we need to change the
structure of the array returned by find(â€œallâ€, ...) method calls.
The reason for this is the CakePHP convention. Normaly the name of an
input field is in the format â€œmodel.fieldNameâ€. With multirecord
forms, we should be using â€œmodel.number.fieldNameâ€. A
find(â€œallâ€, ..) method will return arrays in this format:
â€œnumber.model.fieldNameâ€. As you can see, these donâ€™t match, so
the FormHelper class will not be able to for example automaticly fill
in the input fields with the correct values. Iâ€™ve created a
Component (MultirecordComponent) with a â€œrewriteâ€ method for this
problem, which you can find at the end of this article. In this
component Iâ€™ve also added some convenients methods for validation.

Hereâ€™s a part of the code for the edit method in the NewsController:

::

    
    <?php
    	/**
    	 * Edit one or more items at the same time
    	 *
    	 * @param mixed Array of id's to edit, or a comma seperated string with id's
    	 */
    	function edit($ids){
    		if (!is_array($ids)){
    			$ids = explode(",", $ids);
    		}
    
    		//render with data from database
    		if (empty($this->data)){				
    			$this->data = $this->Multirecord->toMulti($this->News->findAllById($ids));
    		} else {
    			//ommitted
    		}
    		$this->set("ids", $ids);
    	}
    ?>

And here is the code for the view of the edit method. As you can see,
Iâ€™ve moved the actual code for the input views in a seperate
element. The reason for this is that this piece of code shall be used
by different other methods. So it keep things DRY :-)


View Template:
``````````````

::

    
    <?php
    	echo $form->create(array("url"=>array("action"=>"edit", implode(",", $ids))));
    	
    	foreach ($ids as $nr=>$id) {
    		echo $this->element("news.edit", array("id"=>$id));
    	}
    	
    	echo $form->submit("Save");	
    	
    	echo $form->end();
    ?>

And here is the code of the â€œnews.editâ€ element:

::

    
    <?php
    	$tmp = $form->hidden("{$id}.News.id");	
    	$tmp .= $form->input("{$id}.News.title");	
    	$tmp .= $form->input("{$id}.News.message", array("type"=>"textarea"));
    	
    	echo $html->tag("fieldset", $html->tag("legend", "News") . $tmp);
    ?>

Now if you go to â€œyour_url.com/news/edit/1,2â€ you should see 2
forms of the records with id 1 and 2 (If available of course).
Together with the code in the edit method of the NewsController that I
ommited before (see the complete code at end of this article), you
should be able to actually save all the records.


Adding multiple records
~~~~~~~~~~~~~~~~~~~~~~~
So now that weâ€™re able to edit multiple records, letâ€™s add
multiple records. As you meight have noticed, Iâ€™ve named the input
fields â€œmodel.recordId.fieldNameâ€. When adding one or more
records, there is no record id available of course. To overcome this
problem, I use a random generated id:

::

    
    <?php
    	/**
    	 * Add one or more items to the database
    	 *
    	 */
    	function add(){
    		//if posted data available, validate and save
    		if (empty($this->data)){
    			$this->set("ids", array(rand()));
    		} else {
    			if ($this->Multirecord->validate()){
    			
    				if ($this->Multirecord->save()){
    					$this->flash("All items are saved", array("controller"=>"news", "action"=>"index"));
    				} else {
    					$this->flash("Nope, that didn't work out quite well...", array("controller"=>"news", "action"=>"index"));
    				}
    			} else {
    				//data does not validate, now show all inputs
    				$this->set("ids", array_keys($this->data));
    				
    			}
    		}
    	}
    ?>

What we do now is create a view for the add method. Beside the normal
â€˜submitâ€™ button, Iâ€™ve also added a â€˜Add new recordâ€™ button
to dynamicly add more â€œedit formsâ€. This is done by an ajax call
to the addNewsItem method in the NewsController. This method will
render the â€œnews.editâ€ element. which will be inserted just below
the exisiting edit form.


View Template:
``````````````

::

    
    <?php
    
    	echo $javascript->link("prototype", false);
    	
    	echo $form->create();
    		echo $ajax->div("newsEditContainer");
    		
    		//foreach posted id, show an input form
    		foreach ($ids as $id) {
    			echo $this->element("news.edit", array("id"=>$id));
    		}
    			
    		echo $ajax->divEnd("newsEditContainer");
    
    		echo $form->submit("Save");	
    	echo $form->end();
    	
    	
    	//create an 'add' link
    	echo $html->link("Add another news item", 
    		"javascript:new Ajax.Updater('newsEditContainer', '" . Helper::url(array("controller"=>"news", "action"=>"addNewsItem")) . "', {insertion: Insertion.Bottom});");
    ?>



Deleting multiple records
~~~~~~~~~~~~~~~~~~~~~~~~~
Now you should be able to add and edit multiple records. All that is
left is deleting multiple records. Hereâ€™s the code in
NewsController:

::

    
    <?php
    		/**
    		 * Deletes one or more items
    		 *
    		 * @param mixed int or array of id's to delete
    		 */
    		function delete($ids){
    			if (!is_array($ids)){
    				$ids = explode(",", $ids);
    			}
    
    			$this->News->deleteAll( array("News.id"=>$ids) );			
    			
    			$this->flash("Item(s) are deleted", array("controller"=>"news", "action"=>"index"));
    		}
    ?>

You can test this with the following url:
â€œyour_url.com/news/delete/1,2â€.

So there we have it, creating, editing and deleting multiple records
in one simple user interface.

Some final notes:

+ The toMulti() method in the MultirecordComponent class doesn't work
  well if you have relations relations in your model. With some extra
  programming effords it is possible to adjust the code so it will also
  rewrite records from related models. I haven't done this to keep
  things simple
+ I wasn't able to test the code in PHP4. It is possible that some
  problems can occur with objects & references

Hereâ€™s the complete code of both the NewsController and the
MultirecordComponent:


Controller Class:
`````````````````

::

    <?php 
    	class NewsController extends AppController{
    		var $scaffold;
    	
    		var $components = array("Multirecord");
    		
    		var $helpers = array("html", "form", "ajax", "javascript");
    		
    		
    		/**
    		 * Add one or more items to the database
    		 *
    		 */
    		function add(){
    			//if posted data available, validate and save
    			if (empty($this->data)){
    				$this->set("ids", array(rand()));
    			} else {
    				if ($this->Multirecord->validate()){
    				
    					if ($this->Multirecord->save()){
    						$this->flash("All items are saved", array("controller"=>"news", "action"=>"index"));
    					} else {
    						$this->flash("Nope, that didn't work out quite well...", array("controller"=>"news", "action"=>"index"));
    					}
    				} else {
    					//data does not validate, now show all inputs
    					$this->set("ids", array_keys($this->data));
    					
    				}
    			}
    		}
    		
    		
    		/**
    		 * Edit one or more items at the same time
    		 *
    		 * @param mixed Array of id's to edit, or a comma seperated string with id's
    		 */
    		function edit($ids){
    			if (!is_array($ids)){
    				$ids = explode(",", $ids);
    			}
    			
    			//render with data from database
    			if (empty($this->data)){
    				
    				$this->data = $this->Multirecord->toMulti($this->News->findAllById($ids));
    
    			} else {
    				
    				if ($this->Multirecord->validate()){
    					if ($this->Multirecord->save()){
    						$this->flash("All saved!", array("action"=>"index"));
    					} else {
    						$this->flash("Too bad something unexcpected happend", array("action"=>"index"));
    					}
    				}
    				
    			}
    			
    			
    			$this->set("ids", $ids);
    		}
    		
    		
    		/**
    		 * Deletes one or more items
    		 *
    		 * @param mixed int or array of id's to delete
    		 */
    		function delete($ids){
    			if (!is_array($ids)){
    				$ids = explode(",", $ids);
    			}
    
    			$this->News->deleteAll( array("News.id"=>$ids) );			
    			
    			$this->flash("Item(s) are deleted", array("controller"=>"news", "action"=>"index"));
    		}
    		
    		
    		/**
    		 * Called via ajax call to render another input screen
    		 *
    		 */
    		function addNewsItem(){
    			Configure::write("debug", 0); //don't want the cake debug in de ajax response
    			$this->set("id", rand());
    			$this->render("","", "../elements/news.edit");
    		}
    		
    		
    	}
    ?>



Component Class:
````````````````

::

    <?php 
    	/**
    	 * Component class to help saving multiple records
    	 * @author Marcel Raaijmakers aka Marcelius
    	 *
    	 */
    	class MultirecordComponent extends Component{
    		
    		// Saving a reference to the controller on the component instance
    		public function startup(&$controller) {
    			$this->controller = &$controller;
    		}
    		
    		
    		/**
    		 * Converts array from findAll* methods (nr.className.fieldName) in the format of className.recordId.fieldName
    		 *
    		 * @param array The data in the format nr.className.fieldName
    		 * @return array in the format className.recordId.fieldName
    		 */
    		function toMulti($data){
    			$primaryKey = $this->controller->{$this->controller->modelClass}->primaryKey; //usualy 'id'
    
    			$result = array();
    
    			if (is_array($data)){
    				foreach ($data as $record) {
    					if ($ar = @each($record)){
    						$result[$ar["value"][$primaryKey]][$ar["key"]] = $ar["value"]; 
    					}
    				}
    				
    			}
    
    			return $result;
    		}
    	
    		
    		/**
    		 * Validate mulitple records
    		 * On validate failure, the validation result will be passed to that model
    		 *
    		 * @return boolean True if all records validate
    		 */
    		function validate(){
    			$validationErrors = array();
    			
    			$model = $this->controller->{$this->controller->modelClass};
    
    			foreach ($this->controller->data as $id=>$data) {
    				$model->create($this->controller->data[$id]);
    
    				
    				//if doesn't validate, add to array
    				if (!$model->validates()){
    					$validationErrors[$id][$this->controller->name] = $model->validationErrors;
    				}
    			}
    			
    			
    			if (!empty($validationErrors)){
    				$model->validationErrors = $validationErrors;
    			}
    			
    			
    			return empty($validationErrors);
    		}
    		
    		
    		/**
    		 * Saves all records in this->controller->data
    		 *
    		 * @return boolean True if all records are saved
    		 */
    		function save(){
    			$model = $this->controller->{$this->controller->modelClass};
    			
    			$result = $model->saveAll($this->controller->data, array("validate"=>false));
    			
    			return $result;
    		}
    		
    		
    	}
    ?>


.. meta::
    :title: How to create multirecord forms
    :description: CakePHP Article related to ,Tutorials
    :keywords: ,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

