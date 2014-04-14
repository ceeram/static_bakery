An improvement to unbindModel on model side
===========================================

by mariano on March 25, 2007

Not long ago I saw Tom OReilly's great tutorial entitled "Keeping
bindModel and unbindModel out of your Controllers." While he showed us
some great tips I was not so comfortable having to define my model
relations in a different way, but I still wanted the possibility to
only specify what relations I want to get when querying a model.
For those of you who didn't, I recommend you read Tom OReilly's
`Keeping bindModel and unbindModel out of your Controllers`_. He makes
a good argument why it is better to take care of the details of
ubinding models on the model itself, and just let the controller
specify which models it's expecting to get.

The problem with that solution is that it requires you to change the
way you define your model relations. While it may make sense on some
specific cases, I am not a fan of changing the way things are done
within CakePHP. Rather try to change your code to suit your needs, and
let CakePHP do what it does best: act as a framework.

Also it approaches model associations in a different way: model
relations get loaded *when* you call expects(), while I wanted more a
way to have my associations defined on each model the CakePHP way, and
then specify which relations I'm interested in getting back when
querying a model. So models behave the way models are supposed: they
define the data within and associations to other models, and
controllers behave the way controllers do: they query the models and
optionally specify what model information they are insterested in.

Furthermore, I wanted to change it in a way that if I don't use
expects() I get the standard result CakePHP brings: a model with its
related data. With Tom's way, you have to call expects() for model
associations to become useful. With this modification, you call
expects() when you want to limit the amount of information you get.
Makes sense, doesn't it?

UPDATE December 16, 2006 : Now we have two ways to call expects. The
version 1 way (array of models to include on response) or the new way
which is a variable number of arguments including the models, and
inner models, that should be returned when querying. Please refer to
the section included at the bottom of this article. Also note that
those bakers who downloaded the code prior to this update may update
your expects() function and yet no change on your code is necessary.

UPDATE February 26, 2007 : Two reported issues have been solved: "when
defining multiple relations to the same Model, expects() would not
work as expected", and "when unattaching inner model relationships
through expects, such as by calling expects('InnerModelA',
'InnerModelB.InnerModelB') inner model relations are not restored
after find()." This has change the code to the latest version you see
below. IMPORTANT : as you see the new version includes a re-definition
of afterFind() at the AppModel level. So if you re-define this
function on your models, make sure to always call parent::afterFind()
(which you should've been doing anyway)

UPDATE March 24, 2007 : An issue with afterFind() being executed for
inner model relationships (and thus re-binding relationships before
main query was executed) has been fixed.

So here's the code. The first thing we will need is to add the
following code to our AppModel class:

LATEST CODE UPDATE: March 24, 2007

Model Class:
````````````

::

    <?php class AppModel extends Model
    {
    	function afterFind($results) 
    	{ 
    		if (isset($this->__runResetExpects) && $this->__runResetExpects)
    		{
    			$this->__resetExpects();
    			unset($this->__runResetExpects);
    		}
    		
    		return parent::afterFind($results);
    	}
    	
    	/**
    	 * Unbinds all relations from a model except the specified ones. Calling this function without
    	 * parameters unbinds all related models.
    	 * 
    	 * @access public
    	 * @since 1.0
    	 */
    	function expects() 
    	{ 
    		$models = array();
    		$arguments = func_get_args();
    		$innerCall = false;
    
    		if (!empty($arguments) && is_bool($arguments[0]))
    		{
    			$innerCall = $arguments[0];
    		}
    		
    		foreach($arguments as $index => $argument) 
    		{ 
    			if (is_array($argument)) 
    			{ 
    				if (count($argument) > 0) 
    				{ 
    					$arguments = am($arguments, $argument); 
    				} 
    
    				unset($arguments[$index]); 
    			}
    		}
    		
    		foreach($arguments as $index => $argument)
    		{
    			if (!is_string($argument))
    			{
    				unset($arguments[$index]);
    			}
    		}
    
    		if (count($arguments) == 0) 
    		{ 
    			$models[$this->name] = array(); 
    		} 
    		else 
    		{ 
    			foreach($arguments as $argument) 
    			{ 
    				if (strpos($argument, '.') !== false) 
    				{ 
    					$model = substr($argument, 0, strpos($argument, '.')); 
    					$child = substr($argument, strpos($argument, '.') + 1); 
    
    					if ($child == $model) 
    					{
    						$models[$model] = array(); 
    					} 
    					else 
    					{ 
    						$models[$model][] = $child; 
    					} 
    				}
    				else 
    				{ 
    					$models[$this->name][] = $argument; 
    				} 
    			} 
    		}
    		
    		$relationTypes = array ('belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany');
    
    		foreach($models as $bindingName => $children) 
    		{
    			$model = null;
    			
    			foreach($relationTypes as $relationType) 
    			{ 
    				$currentRelation = (isset($this->$relationType) ? $this->$relationType : null);
    				
    				if (isset($currentRelation) && isset($currentRelation[$bindingName]) && is_array($currentRelation[$bindingName]) && isset($currentRelation[$bindingName]['className'])) 
    				{
    					$model = $currentRelation[$bindingName]['className'];
    					break;
    				}
    			}
    			
    			if (!isset($model))
    			{
    				$model = $bindingName;
    			}
    			
    			if (isset($model) && $model != $this->name && isset($this->$model)) 
    			{
    				if (!isset($this->__backInnerAssociation))
    				{
    					$this->__backInnerAssociation = array();
    				} 
    				
    				$this->__backInnerAssociation[] = $model;
    				
    				$this->$model->expects(true, $children);
    			} 
    		}
    		
    		if (isset($models[$this->name])) 
    		{ 
    			foreach($models as $model => $children) 
    			{ 
    				if ($model != $this->name) 
    				{ 
    					$models[$this->name][] = $model; 
    				} 
    			} 
    	
    			$models = array_unique($models[$this->name]);
    			$unbind = array(); 
    	
    			foreach($relationTypes as $relation) 
    			{ 
    				if (isset($this->$relation)) 
    				{ 
    					foreach($this->$relation as $bindingName => $bindingData)
    					{ 
    						if (!in_array($bindingName, $models))
    						{ 
    							$unbind[$relation][] = $bindingName; 
    						} 
    					} 
    				} 
    			} 
    	
    			if (count($unbind) > 0) 
    			{ 
    				$this->unbindModel($unbind); 
    			}
    		}
    
    		if (!$innerCall)
    		{
    			$this->__runResetExpects = true;
    		}
    	}
    	
    	/**
    	 * Resets all relations and inner model relations after calling expects() and find().
    	 * 
    	 * @access private
    	 * @since 1.1
    	 */
    	function __resetExpects()
    	{
    		if (isset($this->__backAssociation))
    		{
    			$this->__resetAssociations();
    		}
    		
    		if (isset($this->__backInnerAssociation))
    		{
    			foreach($this->__backInnerAssociation as $model)
    			{
    				$this->$model->__resetExpects();
    			}
    			
    			unset($this->__backInnerAssociation);
    		}
    	}
    }?>

You don't need to define another variable on your model, just set your
relations as you normally do on Cake. For example, let's take Tom's
Title example but let's build it the Cake way:


Model Class:
````````````

::

    <?php class Title extends AppModel
    {
    	var $belongsTo = array (
    		'Book' => array (
    			'className' => 'Book',
    			'foreignKey' => 'collection_id'
    		),
    		'Album' => array (
    			'className' => 'Album',
    			'foreignKey' => 'collection_id'
    		)
    	);
    	
    	var $hasOne = array (
    		'Story' => array (
    			'className' => 'Story'
    		),
    		'Photo' => array (
    			'className' => 'Photo'
    		)
    	);
    	
    	var $hasMany = array (
    		'Post' => array (
    			'className' => 'Post',
    			'order' => 'Post.id DESC'
    		)
    	);
    }?>

Following his example, we now want to query this model and only return
its associations with Story and Post, disregarding the rest:


Controller Class:
`````````````````

::

    <?php class TitlesController extends AppController 
    { 
    	function list($id) 
    	{ 
    		// establish necessary associations 
    		
    		$this->Title->expects(array('Story', 'Post')); 
    		$this->Title->Post->expects(array('User')); 
    		
    		$this->Title->recursive = 2; 
    		
    		$results = $this->Title->read(null, $id); 
    	} 
    } 
    ?>

As you can see you use the expects() function the same way, but you
don't need to change the way associations are defined in CakePHP.
Furthermore, we make clean calls to CakePHP's bult in unbindModel()
function in the model class, so we are safe for any further CakePHP
upgrades. Also, there's an easy way to do an unbindAll() as Tom was
requested, just call expects() with no parameters:


Controller Class:
`````````````````

::

    <?php class TitlesController extends AppController 
    { 
    	function list($id) 
    	{ 
    		$this->Title->expects(); 
    		
    		$results = $this->Title->read(null, $id); 
    	} 
    } 
    ?>



Making multiple expects() in one call
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
As noted earlier, on December 16, 2006 I added a new version of the
code to allow an easier way to do multiple expects() calls. Let's take
this code:


Controller Class:
`````````````````

::

    <?php 
    $this->Post->Author->expects();
    $this->Post->Category->expects();
    $this->Post->PostDetail->expects(array('PostExtendedDetail', 'PostAttachment'));
    ?>

We are here not limiting the Post model, but its related models. You
can achieve the same result by using the new method of call:


Controller Class:
`````````````````

::

    <?php 
    $this->Post->expects('Author.Author', 'Category.Category', 
    	'PostDetail.PostExtendedDetail', 'PostDetail.PostAttachment');
    ?>

As you can see in just one call we can provide the necessary
restrictions. Note the form of specifying an inner restriction:
Model.InnerModel. If you wish to obtain the same effect as:
$this->Model->InnerModel->expects() then the inner restriction is of
the form: Model.Model
Let's look at another example. On the old form we do:


Controller Class:
`````````````````

::

    <?php 
    $this->Title->expects(array('Story', 'Post')); 
    $this->Title->Post->expects(array('User'));
    ?>

On the new form we would do:


Controller Class:
`````````````````

::

    <?php 
    $this->Title->expects('Story', 'Post', 'Post.User');
    ?>

Or better yet:


Controller Class:
`````````````````

::

    <?php 
    $this->Title->expects('Story', 'Post.User');
    ?>

A final yet simpler example:


Controller Class:
`````````````````

::

    <?php 
    $this->Title->expects(array('Story', 'Post'));
    ?>

can be also obtained by doing:


Controller Class:
`````````````````

::

    <?php 
    $this->Title->expects('Story', 'Post');
    ?>

Once again I must alert that the previous form of method calling
(through array of models) is still valid and will work as expected.
This was just a handy modification to further improve the way you use
this functionality from your controllers.

.. _Keeping bindModel and unbindModel out of your Controllers: http://bakery.cakephp.org/articles/view/179
.. meta::
    :title: An improvement to unbindModel on model side
    :description: CakePHP Article related to relationships,unbind,expects,relation,Tutorials
    :keywords: relationships,unbind,expects,relation,Tutorials
    :copyright: Copyright 2007 mariano
    :category: tutorials

