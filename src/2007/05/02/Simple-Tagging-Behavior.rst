Simple Tagging Behavior
=======================

by dooltaz on May 02, 2007

So far after looking at other tagging code in this site, I have not
seen tags done properly when using normal database form. So this will
be a basic, simple tagging system that allows you to use both a tags
field in your table and a separate tagging table.
First create three tables.

The first table will be your tags table. This table will consist of
the following fields id, tag.

The second table will be the table you would like to associate with
tags. For this example let's use the table posts , with the required
fields id, name, tags.

Third, we will need to build a connector table to link tags with
posts. We will call this table posts_tags .

Please also build the proper hasAndBelongsToMany relationship for your
model. The SQL and the Model Definition can be found in the Cake
Manual for Models under Section 4. Look for the heading "HABTM Join
Tables: Sample models and their join table names"

`http://manual.cakephp.org/chapter/models`_

Models
``````

/app/models/post.php

Model Class:
````````````

::

    <?php 
    <?php
    class Post extends AppModel
    {
        var $name = 'Post';
        var $hasAndBelongsToMany = array('Tag' =>
                                   array('className'    => 'Tag',
                                         'joinTable'    => 'posts_tags',
                                         'foreignKey'   => 'post_id',
                                         'associationForeignKey'=> 'tag_id',
                                         'conditions'   => '',
                                         'order'        => '',
                                         'limit'        => '',
                                         'unique'       => true,
                                         'finderQuery'  => '',
                                         'deleteQuery'  => '',
                                   )
                                   );
    }
    ?>
    ?>

/app/models/post_tag.php

Model Class:
````````````

::

    <?php 
    <?php
    class PostTag extends AppModel
    {
        var $name = 'PostTag';
    }
    ?>
    ?>

/app/models/tag.php

Model Class:
````````````

::

    <?php 
    <?php
    class Tag extends AppModel
    {
        var $name = 'Tag';
    }
    ?>
    ?>



Tag Behavior
````````````

/app/models/behaviors/tag.php

::

    
    <?php /**
     * Tag Behavior class file.
     *
     * Model Behavior to support tags.
     *
     * @filesource
     * @package    app
     * @subpackage    models.behaviors
     */
     
    /**
     * Add tag behavior to a model.
     * 
     */
    class TagBehavior extends ModelBehavior {
        /**
         * Initiate behaviour for the model using specified settings.
         *
         * @param object $model    Model using the behaviour
         * @param array $settings    Settings to override for model.
         *
         * @access public
         */
        function setup(&$model, $settings = array()) {
    
    	
            $default = array( 'table_label' => 'tags', 'tag_label' => 'tag', 'separator' => ',');
            
            if (!isset($this->settings[$model->name])) {
                $this->settings[$model->name] = $default;
            }
            
    	$this->settings[$model->name] = array_merge($this->settings[$model->name], ife(is_array($settings), $settings, array()));
    
        }
        
        /**
         * Run before a model is saved, used to set up tag for model.
         *
         * @param object $model    Model about to be saved.
         *
         * @access public
         * @since 1.0
         */
        function beforeSave(&$model) {
    	// Define the new tag model
    	$Tag =& new Tag;
            if ($model->hasField($this->settings[$model->name]['table_label']) 
    		&& $Tag->hasField($this->settings[$model->name]['tag_label'])) {
    
    
    		// Parse out all of the 
    		$tag_list = $this->_parseTag($model->data[$model->name][$this->settings[$model->name]['table_label']], $this->settings[$model->name]);
    		$tag_info = array(); // New tag array to store tag id and names from db
    		foreach($tag_list as $t) {
    			if ($res = $Tag->find($this->settings[$model->name]['tag_label'] . " LIKE '" . $t . "'")) {
    				$tag_info[] = $res['Tag']['id'];
    			} else {
    				$Tag->save(array('id'=>'',$this->settings[$model->name]['tag_label']=>$t));
    				$tag_info[] = sprintf($Tag->getLastInsertID());
    			}
    			unset($res);
    		}
    
    		// This prepares the linking table data...
    		$model->data['Tag']['Tag'] = $tag_info;
    		// This formats the tags field before save...
    		$model->data[$model->name][$this->settings[$model->name]['table_label']] = implode(', ', $tag_list);
    	}
    	return true;
        }
    
    
        /**
         * Parse the tag string and return a properly formatted array
         *
         * @param string $string    String.
         * @param array $settings    Settings to use (looks for 'separator' and 'length')
         *
         * @return string    Tag for given string.
         *
         * @access private
         */
        function _parseTag($string, $settings) {
            $string = strtolower($string);
           
            $string = preg_replace('/[^a-z0-9' . $settings['separator'] . ' ]/i', '', $string);
            $string = preg_replace('/' . $settings['separator'] . '[' . $settings['separator'] . ']*/', $settings['separator'], $string);
    
    	$string_array = preg_split('/' . $settings['separator'] . '/', $string);
    	$return_array = array();
    
    	foreach($string_array as $t) {
    		$t = ucwords(trim($t));
    		if (strlen($t)>0) {
    			$return_array[] = $t;
    		}
    	}
    	
            return $return_array;
        }
    }
    
    ?>
    



Usage
`````

/app/models/post.php (REVISION)

Model Class:
````````````

::

    <?php 
    <?php
    class Post extends AppModel
    {
        var $name = 'Post';
    
        var $actAs = array('Tag'=>array('table_label'=>'tags', 'tags_label'=>'tag', 'separator'=>',');
    
        var $hasAndBelongsToMany = array('Tag' =>
    ...
    ?>
    ?>

Telling the Post model to "act as" a tag behavior will automatically
take a comma delimited tags field from the Posts table and when it is
saved, it will parse out the tags, save them to the tags table, and
save the associated links.

This can work in one table or multiple tables that want to use the
same set of tags.


Views
`````
Here is the implementation:
/app/views/posts/add.ctp

View Template:
``````````````

::

    
    <?php echo $form->create('Posts');?>
    <?php echo $form->input('title');?>
    <?php echo $form->input('tags');?>
    <?php echo $form->input('body');?>
    </form>

/app/views/posts/edit.ctp

View Template:
``````````````

::

    
    <?php echo $form->create('Posts');?>
    <?php echo $form->input('id');?>
    <?php echo $form->input('title');?>
    <?php echo $form->input('tags');?>
    <?php echo $form->input('body');?>
    </form>



Controller
``````````


Controller Class:
`````````````````

::

    <?php 
    <?php
    class PostsController extends AppController {
        var $name = 'Posts';
        var $helpers = array('Html', 'Form' );
    
        function index() {
            $this->Post->recursive = 0;
            $this->set('posts', $this->paginate());
        }
    
        function add() {
            if(!empty($this->data)) {
                $this->cleanUpFields();
                $this->Post->create();
                if($this->Post->save($this->data)) {
                    $this->Session->setFlash('The Post has been saved');
                    $this->redirect(array('action'=>'index'), null, true);
                } else {
                    $this->Session->setFlash('The Post could not be saved. Please, try again.');
                }
            }
        }
        function edit($id = null) {
            if(!$id && empty($this->data)) {
                $this->Session->setFlash('Invalid Post');
                $this->redirect(array('action'=>'index'), null, true);
            }
            if(!empty($this->data)) {
                $this->cleanUpFields();
                if($this->Post->save($this->data)) {
                    $this->Session->setFlash('The Post saved');
                    $this->redirect(array('action'=>'index'), null, true);
                } else {
                    $this->Session->setFlash('The Post could not be saved. Please, try again.');
                }
            }
            if(empty($this->data)) {
                $this->data = $this->Post->read(null, $id);
            }
        }
    
    }
    ?>


Hope this helps someone.



.. _http://manual.cakephp.org/chapter/models: http://manual.cakephp.org/chapter/models

.. author:: dooltaz
.. categories:: articles, behaviors
.. tags:: tag,tagging,tags,behavior,simple,Behaviors

