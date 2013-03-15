

Showing database-errors
=======================

by %s on June 08, 2007

When the database raises an error Cake shows the text 'Please correct
the errors below' without telling you what the error is. This tutorial
shows you a way to extend your application to show the user a
understandable message after a database error.
Before a start I want to thank ben-xo. His article about detecting
duplicate entries provided me the ideas and the basis for this
tutorial.

To make your application show database errors you have to:

#. Extend your app_model.php.
#. Create an element to show database-errors.
#. Extend your views with the element.
#. Modify your controllers to show the database-errors.

The tutorial will show the code, and hopefully speaks for itself.


xtend your app_model.php.
-------------------------

::

    
    <?php
    /**
     * The class AppModel is extended with functions to show meaningfull database-errors. 
     * The class is able to work with any kind of database.
     * 
     * Put this file in the dir app or the contents of this file in app/app_model.php. 
     * 
     * The arrays with error-numbers and messages has to be extended!!
     * 
     */
    class AppModel extends Model{
    	
    /**
     * Variables to hold information about the raised database-error. 
     *
     */
    var $dbError;
    var $dbErrorStr;
    var $dbErrorNum; 
    
    /**
     * User-understandable messages. 
     * The key is a MySQL number. 
     * Extend the array with al posible database errors!!
     */
    	var $dbErrorUserStr = array (	"1062" => 'Record with same key already exists.', 
    									"1217" => 'Record is used in another table.', ); 
    	
    /**
     * Mapping between error-numbers and user-understandable messages, per database-type.
     * Error numbers for Postgress are FAKE. 
     * Extend the array with al posible database errors!!
     */
    var $dbErrorMapping = array( "DboMysql" =>	array (	"1062" => '1062', 
    										   			"1217" => '1217', ),
    							"DboPostgres" => array ("1111" => '1062', 
    										       		"8888" => '1217', )
    							) ; 
    
    /**
     * Override the function del(), 
     * Check database-error and call a function to invalidate the form. 
     */
        function  del($id = null, $cascade = true) { 
            $returnval = parent::del($id , $cascade); 
            if(false === $returnval) { 
            	$db =& ConnectionManager::getDataSource($this->useDbConfig);  
    			$this->dbError = $db->lastError(); 
    			list($this->dbErrorNum, $this->dbErrorStr) = explode(":", $this->dbError);
    			
            	$this->afterSaveFailed(); 
            }
            return $returnval; 
    
        }
        
    /**
     * Override the function save(), 
     * Check database-error and call a function to invalidate the form. 
     */
        function save($data = null, $validate = true, $fieldList = array()) { 
            $returnval = parent::save($data, $validate, $fieldList); 
            if(false === $returnval) { 
            	$db =& ConnectionManager::getDataSource($this->useDbConfig);  
    			$this->dbError = $db->lastError(); 
    			list($this->dbErrorNum, $this->dbErrorStr) = explode(":", $this->dbError);
    			
            	$this->afterSaveFailed(); 
    		} 
            return $returnval; 
        } 
        
    /**
     * Calls invalidate()
     * Maybe overridden in model.
     * 
    */    
        function afterSaveFailed() { 
    		$this->invalidate('DbError'); 
        } 
        
    /**
     * returns the user understandable messages 
     * if the user understandable message is not defined it returns the originale database-message. 
     * 
    */    
        function  dbErrorUserStr() { 
        	
    //	Determine the type of database...
        	$db =& ConnectionManager::getDataSource($this->useDbConfig);  
        	$key = get_class($db); 
        	if ($key == 'DboMysqli') $key = 'DboMysql';
        	
    //  Determine the number of the message using the mapping array. 
    		$dbErrorNum = $this->dbErrorMapping[$key][$this->dbErrorNum]; 
    		
        	if ( isset($this->dbErrorUserStr[ $dbErrorNum ])){
    			return $this->dbErrorUserStr[ $dbErrorNum ]; 
        	} else { 
    			return $this->dbError; 
        	}
    	}
        
        
    }
    ?>



Create an element to show database-errors.
------------------------------------------

::

    
    	<?php 
    //	Suppress the echo if the variable is not defined...	
    		if ( isSet ($dbErrorUserStr)) {
    			echo $html->tagErrorMsg( $html->model . '/DbError' , $dbErrorUserStr);
    			}
    	 ;?>



Extend your views with the element.
-----------------------------------

::

    
    <?php echo $this->renderElement('mainactions'); ?>
    <h2>New Speed</h2>
    <form action="<?php echo $html->url('/speeds/add'); ?>" method="post">
    <div class="optional"> 
    	<?php echo $form->labelTag('Speed/id', 'Id');?>
     	<?php echo $html->input('Speed/id', array('size' => '60'));?>
    	<?php echo $html->tagErrorMsg('Speed/id', 'Please enter the Id.');?>
    </div>
    <?php echo $this->renderElement('DbInvalidate'); ?>
    <div class="submit">
    	<?php echo $html->submit('Add');?>
    </div>
    </form>



Modify your controllers to show the database-errors.
----------------------------------------------------

::

    
    	function edit($id = null) {
    		$this->Speed->recursive = 0;
    		if(empty($this->data)) {
    			if(!$id) {
    				$this->Session->setFlash('Invalid id for Speed');
    				$this->redirect('/speeds/index');
    			}
    			$this->data = $this->Speed->read(null, $id);
    		} else {
    			$this->cleanUpFields();
    			if($this->Speed->save($this->data)) {
    				$this->Session->setFlash('The Speed has been saved');
    				$this->redirect('/speeds/index');
    			} else {
    				$this->Session->setFlash('Please correct errors below.');
    				$this->set('dbErrorUserStr', $this->Speed->dbErrorUserStr());
    			}
    		}
    	}
    
    	function delete($id = null) {
    		if(!$id) {
    			$this->Session->setFlash('Invalid id for Speed');
    			$this->redirect('/speeds/index');
    		}
    		if($this->Speed->del($id)) {
    			$this->Session->setFlash('The Speed deleted: id '.$id.'');
    			$this->redirect('/speeds/index');
    		}
    		else
    		{
    			$this->Session->setFlash( "Record not deleted. " . $this->Speed->dbErrorUserStr() );
    			$this->redirect($this->referer());
    		}
    	}
    




.. meta::
    :title: Showing database-errors
    :description: CakePHP Article related to database errors,Tutorials
    :keywords: database errors,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

