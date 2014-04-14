Revision Behavior - Revision control made easy
==============================================

by %s on December 18, 2008

Take full control of any changes your users makes, while also giving
them features like undo. Keep a history of previous versions of any
database model, allowing you to undo, revert to an older version (or a
specific time), manage and inspect changes and even get a difference
array for seeing changes over time to any (or all) fields.
RevisionBehavior is a solution for adding undo and other versioning
functionality
to your database models. It is set up for easy application to your
project,
ease of use and to not get in the way of your other model activity.
It is also intended to work well with it's sibling, LogableBehavior.


Feature list
~~~~~~~~~~~~

#. Easy to install
#. Automagically save revision on model save
#. Able to ignore model saves which only contain certain fields
#. Limit number of revisions to keep, will delete oldest
#. Undo functionality (or update to any revision directly)
#. Revert to a datetime (and even do so cascading)
#. Get a diff model array to compare two or more revisions
#. Inspect any or all revisions of a model



Install instructions
~~~~~~~~~~~~~~~~~~~~

#. Place the newest version of RevisionBehavior in your
   app/models/behaviors folder
#. Add the behavior to AppModel (or single models if you prefer)
#. For each model that you want revision for, create a shadow table
#. Behavior will gracefully do nothing for models that has behavior,
   but not shadow table
#. If adding Revision to an existing project, run the
   initializeRevisions() method once for each model.



About shadow tables
```````````````````

You should make these AFTER you have baked your ordinary tables as
they may interfer. By default
the tables should be named rev_[normal table name]. If you wish to
change the prefix you may
do so in the property called $revision_prefix found in the behavior.
Also by default the behavior expects
the shadow tables to be in the same dbconfig as the model, but you may
change this on a per
model basis with the useDbConfig config option.


Add the same fields as in the live table, with 3 important
differences.
````````````

#. The 'id' field should NOT be the primary key, nor auto increment.
#. Add the fields 'version_id' (int, primary key, autoincrement) and
   'version_created' (datetime).
#. Skipp fields that should not be saved in shadowtable
   (lft,right,weight for instance).



Configuration
~~~~~~~~~~~~~

When adding 'Revision' the a model's actsAs array, you may configure
the behavior with these options:

#. limit : int number of revisions to keep, must be at least 2 (as
   current is 1).
#. ignore : array containing the name of fields to ignore.
#. auto : boolean when false the behavior will NOT generate revisions
   in afterSave.
#. useDbConfig : string/null Name of dbConfig to use. Null to use
   Model's.



Limit functionality
```````````````````
The shadow table will save a revision copy when it saves live data, so
the newest
row in the shadow table will (in most cases) be the same as the
current live data.
The exception is when the ignore field functionality is used and the
live data is
updated only in those fields.


Ignore field(s) functionality
`````````````````````````````
If you wish to be able to update certain fields without generating new
revisions,
you can add those fields to the configuration ignore array. Any time
the behavior's
afterSave is called with just primary key and these fields, it will
NOT generate
a new revision. It WILL however save these fields together with other
fields when it
does save a revision. You will probably want to set up cron or
otherwise call
createRevision() to update these fields at some points.


Auto functionality
``````````````````
By default the behavior will insert itself into the Model's save
process by implementing
beforeSave and afterSave. In afterSave, the behavior will save a new
revision of the dataset
that is now the live data. If you do NOT want this automatic behavior,
you may set the config
option 'auto' to false. Then the shadow table will remain empty unless
you call createRevisions
manually.



As an example of how to use this behavior, I will demostrate parts of
a blog app bellow. I assume you already have enough cake understanding
to complete a basic blog app with cake, so I will only include the
code parts needed for the example.


The Blob Blog
~~~~~~~~~~~~~
This blog contains articles that may be posted by more than one user.
We also wish to use revision behavior on the articles. This gives us
the following 3 tables; users, posts, posts_revs.


posts
+++++

::

    id (int) primary key, autoincrement
    user_id (int)
    title (varchar)
    content (text)
    publish (date) ALLOW_NULL



posts_revs
++++++++++

::

    version_id (int) primary key, autoincrement
    version_created (datetime)
    id (int)
    user_id (int)
    title (varchar)
    content (text)
    publish (date) ALLOW_NULL



Model Class:
````````````

::

    <?php 
    class Post extends AppModel {
    var $name = 'Post';
    var $belongsTo = array('User');
    var $actsAs = array('Revision' => array('limit'=>10,'ignore'=>array('publish') );
    }?>

I configure the behavior to only keep 9 old versions and to not
generate a new revision if only the publish field is updated


Parts of app/views/posts/view.ctp
+++++++++++++++++++++++++++++++++

::

    <?php 
    // [..]
    echo 'Previous version was made by '.$users[$undo_rev['Post']['user_id']].' at '.$time->nice($undo_rev['Post']['version_created']);
    echo $html->link('Undo', array('action'=>'undo',$post['Post']['id']));
    // [..]
    echo '<h4>Revision history</h4><ul>';
    $nr_of_revs = sizeof($history);
    foreach ($history as $k => $rev) {
        echo '<li>'.($nr_of_revs-$k).' '.$rev['Post']['version_created'].' '.
           $html->link('make current', array('action'=>'make_current',$post['Post']['id'],$rev['Post']['version_id']);
    } 
    // [..]



Parts of app/views/posts/edit.ctp
+++++++++++++++++++++++++++++++++

::

    <?php 
    // [..]
    echo '<h4>Revision history</h4><ul>';
    $nr_of_revs = sizeof($history);
    foreach ($history as $k => $rev) {
        echo '<li>'.($nr_of_revs-$k).' '.$rev['Post']['version_created'].' '.
           $html->link('load revision', array('action'=>'edit',$rev['Post']['id'],$rev['Post']['version_id']);
    } //Puts selected revision in the form, user can save it as it is, edit it and then save or discard.
    // [..]



Controller Class:
`````````````````

::

    <?php  
    class PostsController extends AppController {
    // [..]
    function view($id) {
        $this->Post->id = $id;
        $post = $this->Post->read();
        $undo_rev = $this->Post->previous();
        $history = $this->Post->revisions();
        $users = $this->Post->User->find('list');
        $this->set(compact('post','undo_rev','history','users');
    }
    // [..]
    function edit($id, $version_id = null) {
        $this->Post->id = $id; //important for read,shadow and revisions call bellow
        // [..]
        if (empty($this->data)) {
            if (is_numeric($version_id)) {
                $this->data = $this->Post->shadow('first',array('conditions' => array('version_id' => $version_id)));
            } else {
                $this->data = $this->Post->read();
            }
        }
        $users = $this->Post->User->find('list');
        $history = $this->Post->revisions();
        $this->set(compact('users','history'));
    }
    // [..]
    function undo($id) {
        $this->Post->id = $id;
        $this->Post->undo();
        $this->redirect(array('action'=>'view',$id));
    }
    // [..]
    function make_current($id, $version_id) {
        $this->Post->id = $id;
        $this->Post->revertTo($version_id);
        $this->redirect(array('action'=>'view',$id);
    }
    ?>

Skipped crud actions and code parts that check valid paremeters etc.




createRevision()
````````````````

+ No parameters

Manually create a revision of the current record of Model->id

::

    Example: <?php
    $this->Post->id = 5; $this->Post->createRevision();




diff($from_version_id = null, $to_version_id = null, $options =
array())
````````

+ from_version_id : Shadow id of first version
+ to_version_id : Shadow id of last version
+ options : Extra options for the ShadowModel->find()

Returns an array that maps to the Model, only with multiple values for
fields that has been changed

::

    Example: <?php
    $this->Post->id = 4; $changes = $this->Post->diff();
    $this->Post->id = 4; $my_changes = $this->Post->diff(null, null, array('conditions' => array('user_id'=>4)));
    $this->Post->id = 4; $difference = $this->Post->diff(45,192);

::

    Result example :
    array( 'Post' => array(
      'version_id' => array(
        0 => 192,
        1 => 67,
        2 => 45),
      'version_created => array(
        0 => '2008-12-03 12:03:00',
        1 => '2008-12-02 11:02:00',
        2 => '2008-12-01 10:01:00',
      'id' => 4,
      'title' => array(
        0 => 'New title',
        1 => 'Edited title',
        2 => 'Original title'
      ),
      'content' => 'Lorem ipsum'
    );




initializeRevisions()
`````````````````````

+ No parameters

Will create a current revision of all rows in Model, if none exist.
Use this if you add the revision to a model that allready has data in
the DB.

::

    Example: <?php
    $this->Post->initializeRevisions();




newest($options = array())
``````````````````````````

+ options : extra options to the shadow find

Finds the newest revision, including the current one. Use with
caution, the live model may be different depending on the usage of
ignore fields.

::

    Example: <?php
    $this->Post->id = 6; $newest_revision = $this->Post->newest();




oldest($options = array())
``````````````````````````

+ options : extra options to the shadow find

Find the oldest revision for the current Model->id. If no limit is
used on revision and revision has been enabled for the model since
start, this call will return the original first record.

::

    Example: <?php
    $this->Post->id = 2; $original = $this->Post->oldest();




previous($options = array())
````````````````````````````

+ options : extra options to the shadow find

Find the second newest revisions, including the current one.

::

    Example: <?php
    $this->Post->id = 6; $undo_revision = $this->Post->previous();




revertTo($version_id)
`````````````````````

+ version_id : Shadow id of the revision to revert to.

Revert current Model->id to the given revision id. Will return false
if version id is invalid or save fails

::

    Example: <?php
    $this->Post->id = 3; $this->Post->revertTo(12);




revertToDate($datetime, $cascade = false)
`````````````````````````````````````````

+ datetime : Date to revert to
+ cascade : If true, revert cascades to hasOne and hasMany related
  models



::

    Example: <?php
    $this->Post->id = 3; $this->Post->revertToDate(date('Y-m-d H:i:s',strtotime('Yesterday')));
    $this->Post->id = 4; $this->Post->revertToDate('2008-09-01',true);




revisions($options = array())
`````````````````````````````

+ options : extra options to the shadow find

Returns a comeplete list of revisions for the current Model->id. The
options array may include Model::find parameters to narrow down
result. Alias for
shadow('all',array('conditions'=>array($Model->primaryKey =>
$Model->id)));

::

    Example: <?php
    $this->Post->id = 4; $history = $this->Post->revisions(); 
    $this->Post->id = 4; $today = $this->Post->revisions(array('conditions'=>array('version_create >'=>'2008-12-10')));




shadow($type = 'first', $options = array())
```````````````````````````````````````````

+ type : 'first','all','count'
+ options : extra options to the shadow find

Runs a find on the models shadow table. Basicaly :
ShadowModel->find('first',$options)

::

    Example: <?php
    $specific_version = $this->Post->shadow('first',array('conditions' => array('version_id'=>4)));
    $my_revs = $this->Post->shadow('all',array('conditions' => array('id'=>4,'user_id'=>5)));




undelete()
``````````

+ No parameters

Undoes an delete by saving the last revision to the Model. Will return
false if this Model->id exist in the live table. Callbacks
Model::beforeUndelete and Model::afterUndelete

::

    Example: <?php
    $this->Post->id = 7; $this->Post->undelete(); 




undo()
``````

+ No parameters

Update to previous revision. If there are 7 revisions including
current, undo will create version 8 with data from version 6.

::

    Example: <?php
    $this->Post->id = 2; $this->Post->undo();


Download php5 version (and also more frequently updated) here :
`http://code.google.com/p/alkemann/downloads/list`_
SVN checkout version here : `http://alkemann.googlecode.com/svn/trunk/
models/behaviors/revision.php`_

Behavior Class:
```````````````

::

    <?php 
    /**
     * Revision Behavior 1.1.1
     * 
     * Revision is a solution for adding undo and other versioning functionality
     * to your database models. It is set up to be easy to apply to your project,
     * to be easy to use and not get in the way of your other model activity.
     * It is also intended to work well with it's sibling, LogableBehavior.
     * 
     * Feature list :
     * 
     *  - Easy to install
     *  - Automagically save revision on model save
     *  - Able to ignore model saves which only contain certain fields
     *  - Limit number of revisions to keep, will delete oldest
     *  - Undo functionality (or update to any revision directly)
     *  - Revert to a datetime (and even do so cascading)
     *  - Get a diff model array to compare two or more revisions
     *  - Inspect any or all revisions of a model
     *
     * Install instructions :
     * 
     *  - Place the newest version of RevisionBehavior in your app/models/behaviors folder
     *  - Add the behavior to AppModel (or single models if you prefer)
     *  - Create a shadow table for each model that you want revision for.
     *  - Behavior will gracefully do nothing for models that has behavior, without table
     *  - If adding to an existing project, run the initializeRevisions() method once for each model.
     * 
     * About shadow tables :
     * 
     * You should make these AFTER you have baked your ordinary tables as they may interfer. By default
     * the tables should be named "[prefix][model_table_name]_revs" If you wish to change the suffix you may
     * do so in the property called $revision_suffix found bellow. Also by default the behavior expects
     * the revision tables to be in the same dbconfig as the model, but you may change this on a per 
     * model basis with the useDbConfig config option.
     * 
     * Add the same fields as in the live table, with 3 important differences. 
     *  - The 'id' field should NOT be the primary key, nor auto increment
     *  - Add the fields 'version_id' (int, primary key, autoincrement) and 
     *    'version_created' (datetime)
     *  - Skipp fields that should not be saved in shadowtable (lft,right,weight for instance)
     * 
     * Configuration :
     * 
     *  - 'limit' : number of revisions to keep, must be at least 2 
     *  - 'ignore' : array containing the name of fields to ignore
     *  - 'auto' : boolean when false the behavior will NOT generate revisions in afterSave
     *  - 'useDbConfig' : string/null Name of dbConfig to use. Null to use Model's
     * 
     * Limit functionality : 
     * The shadow table will save a revision copy when it saves live data, so the newest
     * row in the shadow table will (in most cases) be the same as the current live data.
     * The exception is when the ignore field functionality is used and the live data is 
     * updated only in those fields. 
     * 
     * Ignore field(s) functionality :
     * If you wish to be able to update certain fields without generating new revisions,
     * you can add those fields to the configuration ignore array. Any time the behavior's 
     * afterSave is called with just primary key and these fields, it will NOT generate
     * a new revision. It WILL however save these fields together with other fields when it
     * does save a revision. You will probably want to set up cron or otherwise call
     * createRevision() to update these fields at some points.
     * 
     * Auto functionality :
     * By default the behavior will insert itself into the Model's save process by implementing
     * beforeSave and afterSave. In afterSave, the behavior will save a new revision of the dataset
     * that is now the live data. If you do NOT want this automatic behavior, you may set the config
     * option 'auto' to false. Then the shadow table will remain empty unless you call createRevisions
     * manually.
     * 
     * @author Ronny Vindenes
     * @author Alexander 'alkemann' Morland
     * @license MIT
     * @modifed 27. desemeber 2008
     * @version 1.1.1
     */
    class RevisionBehavior extends ModelBehavior {
    
    	/**
    	 * Behavior settings
    	 * 
    	 * @access public
    	 * @var array
    	 */
    	public $settings = array();
    	/**
    	 * Shadow table prefix
    	 * Only change this value if it causes table name crashes
    	 *
    	 * @access private
    	 * @var string
    	 */
    	private $revision_suffix = '_revs';
    	/**
    	 * Defaul setting values
    	 *
    	 * @access private
    	 * @var array
    	 */
        private $defaults = array(
        	'limit' => false,
        	'auto' => true,
        	'ignore' => array(),
        	'useDbConfig' => null
        );
    
        /**
         * Configure the behavior through the Model::actsAs property
         *
         * @param object $Model
         * @param array $config
         */
    	public function setup(&$Model, $config = null) {	
    		if (is_array($config)) {
    			$this->settings[$Model->alias] = array_merge($this->defaults, $config);			
    		} else {
    			$this->settings[$Model->alias] = $this->defaults;
    		}		
    		$Model->ShadowModel = $this->createShadowModel($Model);	
    	}
    
    	/**
    	 * Manually create a revision of the current record of Model->id
    	 *
    	 * @example $this->Post->id = 5; $this->Post->createRevision();
    	 * @param object $Model
    	 * @return boolean success
    	 */
    	public function createRevision(&$Model) {	
    		if (is_null($Model->id)) {
    			trigger_error('RevisionBehavior: Model::id must be set', E_USER_WARNING); return null;
    		}
    		$Model->read();
    		$data = $Model->data;		
    		$data[$Model->ShadowModel->alias]['version_created'] = date('Y-m-d h:i:s');
    		return $Model->ShadowModel->save($data,false);
    	}
    	
    	/**
    	 * Returns an array that maps to the Model, only with multiple values for fields that has been changed
    	 *
    	 * @example $this->Post->id = 4; $changes = $this->Post->diff();
    	 * @example $this->Post->id = 4; $my_changes = $this->Post->diff(null,nul,array('conditions'=>array('user_id'=>4)));
    	 * @example $this->Post->id = 4; $difference = $this->Post->diff(45,192);
    	 * @param Object $Model
    	 * @param int $from_version_id
    	 * @param int $to_version_id
    	 * @param array $options
    	 * @return array
    	 */
    	public function diff(&$Model, $from_version_id = null, $to_version_id = null, $options = array()) {
    		if (is_null($Model->id)) {
    			trigger_error('RevisionBehavior: Model::id must be set', E_USER_WARNING); return null;
    		}
    		if (isset($options['conditions'])) {
    			$conditions = am($options['conditions'],array($Model->primaryKey => $Model->id));	
    		} else {
    			$conditions = array( $Model->primaryKey => $Model->id);	
    		}	
    		$conditions = array($Model->primaryKey 	=> $Model->id);		
    		if (is_numeric($from_version_id)) {
    			$conditions['version_id >='] = $from_version_id;			
    		}
    		if (is_numeric($to_version_id)) {
    			$conditions['version_id <='] = $to_version_id;		
    		}
    		$options['conditions'] = $conditions;
    		$all = $this->revisions($Model,$options);
    		if (sizeof($all) == 0) {
    			return null;
    		}
    		$unified = array();
    		$keys = array_keys($all[0][$Model->alias]);
    		foreach ($keys as $field) {
    			$all_values = Set::extract($all,'/'.$Model->alias.'/'.$field);
    			$all_values = array_unique($all_values);
    			if (sizeof($all_values) == 1) {
    				$unified[$field] = $all_values[0];
    			} else {
    				$unified[$field] = $all_values;
    			}			
    		}		 
    		return array($Model->alias => $unified);
    	}	
    
    	/**
    	 * Will create a current revision of all rows in Model, if none exist.
    	 * Use this if you add the revision to a model that allready has data in
    	 * the DB.
    	 *
    	 * @example $this->Post->initializeRevisions();
    	 * @param object $Model
    	 * @return boolean 
    	 */
    	public function initializeRevisions($Model) {
    		if ($Model->ShadowModel->useTable == false) {
    			die('RevisionBehavior: Missing shadowtable : '.$this->revision_prefix.$Model->table);
    		}
    		if ($Model->ShadowModel->find('count') != 0) {
    			return false;
    		}
    		$all = $Model->find('all');
    		$version_created = date('Y-m-d h:i:s');
    		foreach ($all as $data) {
    			$data[$Model->ShadowModel->alias]['version_created'] = $version_created;
    			$Model->ShadowModel->create($data);
    			$Model->ShadowModel->save();
    		}
    		return true;
    	}
    
    	/**
    	 * Finds the newest revision, including the current one.
    	 * Use with caution, the live model may be different depending on the usage
    	 * of ignore fields.
    	 *
    	 * @example $this->Post->id = 6; $newest_revision = $this->Post->newest();
    	 * @param object $Model
    	 * @param array $options
    	 * @return array
    	 */
    	public function newest(&$Model, $options = array()) {
    		if (is_null($Model->id)) {
    			trigger_error('RevisionBehavior: Model::id must be set', E_USER_WARNING); return null;
    		}
    		if (isset($options['conditions'])) {
    			$options['conditions'] = am($options['conditions'],array($Model->primaryKey => $Model->id));	
    		} else {
    			$options['conditions'] = array( $Model->primaryKey => $Model->id);	
    		}			
    		return $this->shadow($Model,'first',$options);
    	}
    
    	/**
    	 * Find the oldest revision for the current Model->id
    	 * If no limit is used on revision and revision has been enabled for the model
    	 * since start, this call will return the original first record.
    	 *
    	 * @example $this->Post->id = 2; $original = $this->Post->oldest();
    	 * @param object $Model
    	 * @param array $options
    	 * @return array
    	 */
    	public function oldest(&$Model, $options = array()) {
    		if (is_null($Model->id)) {
    			trigger_error('RevisionBehavior: Model::id must be set', E_USER_WARNING); return null;
    		}
    		if (isset($options['conditions'])) {
    			$options['conditions'] = am($options['conditions'],array($Model->primaryKey => $Model->id));	
    		} else {
    			$options['conditions'] = array( $Model->primaryKey => $Model->id);	
    		}			
    		$options['order'] = 'version_created ASC, version_id ASC';
    		return $this->shadow($Model,'first',$options);
    	}
    
    	/**
    	 * Find the second newest revisions, including the current one.
    	 *
    	 * @example $this->Post->id = 6; $undo_revision = $this->Post->previous();
    	 * @param object $Model
    	 * @param array $options
    	 * @return array
    	 */
    	public function previous(&$Model, $options = array()) {
    		if (is_null($Model->id)) {
    			trigger_error('RevisionBehavior: Model::id must be set', E_USER_WARNING); return null;
    		}
    		$options['limit'] = 1;
    		$options['page'] = 2;		
    		if (isset($options['conditions'])) {
    			$options['conditions'] = am($options['conditions'],array($Model->primaryKey => $Model->id));	
    		} else {
    			$options['conditions'] = array( $Model->primaryKey => $Model->id);	
    		}		
    		$revisions = $this->shadow($Model,'all',$options);
    		if (!$revisions) {
    			return null;
    		}
    		return $revisions[0]; 
    	}
    
    	/**
    	 * Revert current Model->id to the given revision id
    	 * Will return false if version id is invalid or save fails
    	 *
    	 * @example $this->Post->id = 3; $this->Post->revertTo(12); 
    	 * @param object $Model
    	 * @param int $version_id
    	 * @return boolean
    	 */
    	public function revertTo(&$Model, $version_id) {
    		if (is_null($Model->id)) {
    			trigger_error('RevisionBehavior: Model::id must be set', E_USER_WARNING); return null;
    		}
    		$data = $this->shadow($Model,'first',array('conditions'=>array('version_id'=>$version_id)));
    		if (sizeof($data) != 1) {
    			return false;
    		}
    		return $Model->save($data);
    	}
    	
    	/**
    	 * Revert to the oldest revision after the given datedate.
    	 * Will cascade to hasOne and hasMany associeted models if $cascade is true.
    	 * Will return false if no change is made on the main model 
    	 *
    	 * @example $this->Post->id = 3; $this->Post->revertToDate(date('Y-m-d H:i:s',strtotime('Yesterday')));
    	 * @example $this->Post->id = 4; $this->Post->revertToDate('2008-09-01',true);
    	 * @param object $Model
    	 * @param string $datetime
    	 * @param boolean $cascade
    	 * @return boolean
    	 */
    	public function revertToDate(&$Model, $datetime, $cascade = false) {
    		if (is_null($Model->id)) {
    			trigger_error('RevisionBehavior: Model::id must be set', E_USER_WARNING); return null;
    		}
    		if ($cascade) {		
    			$associated = array_merge($Model->hasMany, $Model->hasOne);
    			foreach ($associated as $assoc => $data) {
    				$children = $Model->$assoc->find('list', array('conditions'=>array($data['foreignKey']=>$Model->id),'recursive'=>-1));
    				$ids = array_keys($children);
    				foreach ($ids as $id) {
    					$Model->$assoc->id = $id;
    					$Model->$assoc->revertToDate($datetime,true);
    				}
    			}			
    		} 		
    		$changes = $this->revisions($Model,array('conditions'=>array('version_created >'=>$datetime),'order'=>'version_created ASC, version_id ASC'));	
    		if (sizeof($changes) == 0) {
    			return false;
    		}
    		return $Model->save($changes[0]);
    	}
    	
    	/**
    	 * Returns a comeplete list of revisions for the current Model->id. 
    	 * The options array may include Model::find parameters to narrow down result
    	 * Alias for shadow('all',array('conditions'=>array($Model->primaryKey => $Model->id)));
    	 * 
    	 * @example $this->Post->id = 4; $history = $this->Post->revisions(); 
    	 * @example $this->Post->id = 4; $today = $this->Post->revisions(array('conditions'=>array('version_create >'=>'2008-12-10'))); 
    	 * @param object $Model
    	 * @param array $options
    	 * @return array
    	 */
    	public function revisions(&$Model, $options = array()) {
    		if (is_null($Model->id)) {
    			trigger_error('RevisionBehavior: Model::id must be set', E_USER_WARNING); return null;
    		}
    		if (isset($options['conditions'])) {
    			$options['conditions'] = am($options['conditions'],array($Model->primaryKey => $Model->id));	
    		} else {
    			$options['conditions'] = array( $Model->primaryKey => $Model->id);	
    		}		
    		return $this->shadow($Model,'all',$options);		
    	}
    		
    	/**
    	 * Runs a find on the models shadow table. Basicaly : ShadowModel->find('first',$options)
    	 * 
    	 * @example $specific_version = $this->Post->shadow('first',array('conditions' => array('version_id'=>4)));
    	 * @example $my_revs = $this->Post->shadow('all',array('conditions' => array('id'=>4,'user_id'=>5)));
    	 * @param object $Model
    	 * @param array $options
    	 * @return array
    	 */
    	public function shadow(&$Model, $type = 'first', $options = array()) {
    		return $Model->ShadowModel->find($type,$options);
    	}
    
    	/**
    	 * Undoes an delete by saving the last revision to the Model
    	 * Will return false if this Model->id exist in the live table.
    	 * Calls Model::beforeUndelete and Model::afterUndelete
    	 *
    	 * @example $this->Post->id = 7; $this->Post->undelete(); 
    	 * @param object $Model
    	 * @return boolean
    	 */
    	public function undelete(&$Model) {
    		if (is_null($Model->id)) {
    			trigger_error('RevisionBehavior: Model::id must be set', E_USER_WARNING); return null;
    		}
    		if  ($Model->read()) {
    			return false;
    		}
    		$data = $this->newest($Model);
    		if (!$data) {
    			return false;
    		}
    		foreach ($this->settings[$Model->alias]['ignore'] as $field) {
    			$data[$Model->alias][$field] = NULL; 
    		}
    		$Model->create($data);
    		$beforeUndeleteSuccess = true;
    		if (method_exists($Model,'beforeUndelete')) {
    			$beforeUndeleteSuccess = $Model->beforeUndelete();
    		}
    		if (!$beforeUndeleteSuccess) {
    			return false;
    		}
    		$save_success =  $Model->save();
    		if (!$save_success) {
    			return false;
    		}
    		$afterUndeleteSuccess = true;
    		if (method_exists($Model,'afterUndelete')) {
    			$afterUndeleteSuccess = $Model->afterUndelete();
    		}
    		return $afterUndeleteSuccess;
    	}
    	
    	/**
    	 * Update to previous revision
    	 *
    	 * @example $this->Post->id = 2; $this->Post->undo();
    	 * @param object $Model
    	 * @return boolean
    	 */
    	public function undo(&$Model) {	
    		if (is_null($Model->id)) {
    			trigger_error('RevisionBehavior: Model::id must be set', E_USER_WARNING); return null;
    		}
    		$data = $this->previous($Model);
    		return $Model->save($data);
    	}	
    	
    	/**
    	 * Will create a new revision if changes have been made in the models non-ignore fields. 
    	 * Also deletes oldest revision if limit is (active and) reached.
    	 *
    	 * @param object $Model
    	 * @param boolean $created
    	 * @return boolean
    	 */
    	public function afterSave(&$Model) {
    		if ($this->settings[$Model->alias]['auto'] === false) {
    			return true;
    		}		
    		if (!$Model->ShadowModel) {
                return true;
    		}   
    		$data = $Model->findById($Model->id);
    		$changeDetected = false;
    		foreach ($data[$Model->alias] as $key => $value) {
       			if (isset($data[$Model->alias][$Model->primaryKey]) && !empty($this->old) && isset($this->old[$Model->alias][$key])) {
       				$old = $this->old[$Model->alias][$key];
       			} else {
       				$old = '';
       			}
       			if ($value != $old && !in_array($key,$this->settings[$Model->alias]['ignore'])) {
       				$changeDetected = true;				
       			}
       		}
       		if (!$changeDetected) {
       			return true;
       		}
    		$data[$Model->ShadowModel->alias]['version_created'] = date('Y-m-d h:i:s');
    		$Model->ShadowModel->save($data,false);
    		$Model->version_id = $Model->ShadowModel->id;
    		if (is_numeric($this->settings[$Model->alias]['limit'])) {
                $conditions = array('conditions'=>array($Model->alias.'.'.$Model->primaryKey => $Model->id));
    			$count = $Model->ShadowModel->find('count', $conditions);
    			if ($count > $this->settings[$Model->alias]['limit']) {
                    $conditions['order'] = $Model->alias.'.version_created ASC, '.$Model->alias.'.version_id ASC';
    				$oldest = $Model->ShadowModel->find('first',$conditions);
    				$Model->ShadowModel->id = null;
    				$Model->ShadowModel->del($oldest[$Model->alias][$Model->ShadowModel->primaryKey]);	
    			}			
    		}
    		return true;
    	}
    		
    	/**
    	 * Revision uses the beforeSave callback to remember the old data for comparison in afterSave
    	 *
    	 * @param object $Model
    	 * @return boolean
    	 */
    	public function beforeSave(&$Model) {
    		if ($this->settings[$Model->alias]['auto'] === false) {
    			return true;
    		}
    		if (!$Model->ShadowModel) {
                return true;
    		}   
    		$Model->ShadowModel->id = null;
    		$Model->ShadowModel->create();
           	$this->old = $Model->find('first', array(
           		'recursive' => -1,
           		'conditions'=>array($Model->alias.'.'.$Model->primaryKey => $Model->id)));
            return true;
    	}
    
    	/**
    	 * Returns a generic model that maps to the current $Model's shadow table.
    	 *
    	 * @param object $Model
    	 * @return object
    	 */
    	private function createShadowModel(&$Model) {	
    		if (is_null($this->settings[$Model->alias]['useDbConfig'])) {
    			$dbConfig = $Model->useDbConfig;
    		} else {
    			$dbConfig = $this->settings[$Model->alias]['useDbConfig'];			
    		}	
    		$table = $Model->useTable .$this->revision_suffix;	
    		$db = & ConnectionManager::getDataSource($dbConfig);
    		$prefix = $Model->tablePrefix ? $Model->tablePrefix : $db->config['prefix'];
    		$tables = $db->listSources();
    		$full_table_name = $prefix.$table;
    		if ($prefix && empty($db->config['prefix'])) {
    			$table = $full_table_name;
    		}
    		if (!in_array($full_table_name, $tables)) {
    			return false;
    		} 	
    		$Model->ShadowModel = new Model(false, $table, $dbConfig);	
    		$Model->ShadowModel->alias = $Model->alias;
    		$Model->ShadowModel->primaryKey = 'version_id';
    		$Model->ShadowModel->order = 'version_created DESC, version_id DESC';
    		return $Model->ShadowModel;
    	}
    
    }?>

`1`_|`2`_|`3`_|`4`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_

.. _http://code.google.com/p/alkemann/downloads/list: http://code.google.com/p/alkemann/downloads/list
.. _http://alkemann.googlecode.com/svn/trunk/models/behaviors/revision.php: http://alkemann.googlecode.com/svn/trunk/models/behaviors/revision.php
.. _Page 1: :///articles/view/4caea0e3-a8a0-4dc1-9476-449682f0cb67/lang:eng#page-1
.. _Page 2: :///articles/view/4caea0e3-a8a0-4dc1-9476-449682f0cb67/lang:eng#page-2
.. _Page 3: :///articles/view/4caea0e3-a8a0-4dc1-9476-449682f0cb67/lang:eng#page-3
.. _Page 4: :///articles/view/4caea0e3-a8a0-4dc1-9476-449682f0cb67/lang:eng#page-4
.. meta::
    :title: Revision Behavior - Revision control made easy
    :description: CakePHP Article related to version,behavior,alkemann,ronnyvv,revision,Behaviors
    :keywords: version,behavior,alkemann,ronnyvv,revision,Behaviors
    :copyright: Copyright 2008 
    :category: behaviors

