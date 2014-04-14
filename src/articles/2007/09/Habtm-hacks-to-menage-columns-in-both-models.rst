Habtm hacks to menage columns in both models
============================================

by mess on September 10, 2007

After searching a lot, i haven't found what i need. I had a habtm
relationship between two models and when i did a "findall" in a model
i couldn't filter the rows with a clause from the the other model.
My solution:

Create a file named "app_model.php" in app directory.

Copy in that file this:


Model Class:
````````````

::

    <?php 
    class AppModel extends Model{
    
    var $habtm = array();
    
    function prepareJoinStatement(){
    	$join_statement = "";
    	foreach($this->habtm as $key=>$value){
    		$fk_this = $value[0];
    		$fk_otherModel = $value[1];
    		
    		$otherModelName = $value[2];
    		loadModel($otherModelName); 
    		$otherModel = new $otherModelName();
    		$otherTableName = $otherModel->useTable;
    		
    		$join_statement .= " JOIN ".$key." on ".$this->name.".id=".$key.".".$fk_this;
    		$join_statement .= " JOIN ".$otherTableName." as ".$otherModelName." on ".$key.".".$fk_otherModel."=".$otherModelName.".id";
    	}
    	return $join_statement;
    }
    
    function findAll($conditions = null, $fields = null, $order = null, $limit = null, $page = 1, $recursive = null) {
    
    		$db =& ConnectionManager::getDataSource($this->useDbConfig);
    		$this->id = $this->getID();
    		$offset = null;
    
    		if ($page > 1 && $limit != null) {
    			$offset = ($page - 1) * $limit;
    		}
    
    		if ($order == null) {
    			$order = array();
    		} else {
    			$order = array($order);
    		}
    
    		$queryData = array('conditions' => $conditions,
    							'fields'    => '*',
    							'joins'     => array($this->prepareJoinStatement()),
    							'limit'     => $limit,
    							'offset'	=> $offset,
    							'order'     => $order
    		);
    
    		$ret = $this->beforeFind($queryData);
    		if (is_array($ret)) {
    			$queryData = $ret;
    		} elseif ($ret === false) {
    			return null;
    		}
    
    		$return = $this->afterFind($db->read($this, $queryData, $recursive));
    
    		if (isset($this->__backAssociation)) {
    			$this->__resetAssociations();
    		}
    
    		return $return;
    	}
    	
    }
    ?>

Now in the model you can delete (or comment) the variable
$hasAndBelongsToMany and use the variable $habtm in this way:


Model Class:
````````````

::

    <?php 
    var $habtm = array('HABTM_TABLE_NAME'=>array('FOREIGN_KEY_TO FIRST_TABLE','FOREIGN_KEY_TO_SECOND_TABLE','SECOND_TABLE_MODEL_NAME'));
    ?>

eg.

I TABLE: users(id,a,b,c,...)
I MODEL: User
II TABLE: phones(id,a,b,c,...)
II MODEL: Phone

HABTM TABLE: users_phones('user_id','phone_id','primary')

In the User Model i can write:


Model Class:
````````````

::

    <?php 
    var $habtm = array('users_phones'=>array('user_id','phone_id','Phone'));
    ?>

Now in my controller i can write:


Controller Class:
`````````````````

::

    <?php 
    $this->User->findall('Phone.a=\'xxx\'',null, null);
    ?>

or

[controller] $this->User->findall('users_phones.primary=\'Y\'',null,
null);
[controller]
I hope this can help someone...

.. meta::
    :title: Habtm hacks to menage columns in both models
    :description: CakePHP Article related to Cake HABTM findall,Tutorials
    :keywords: Cake HABTM findall,Tutorials
    :copyright: Copyright 2007 mess
    :category: tutorials

