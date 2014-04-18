Quick Tip - Doing Ad-hoc Joins in Model::find()
===============================================

Herewith, a little-known query trick that allows you to do simple ad-
hoc joins in your CakePHP finder queries. No binding or unbinding
required.
*Note: This only works if you are using the new Model::find() syntax,
which only takes two parameters. If not, please refer to the Cookbook
or API.*

Part of the "zen" of CakePHP's design is the way in which things are
layered. For example, many helper methods take an $options parameter,
and methods which are built on top of other methods (think FormHelper
or PaginatorHelper ) allow you to pass in options at the top level
that get passed down to the lower levels, giving you very granular
control, even at high levels of abstraction.

So it is with the Model layer. All the options passed in Model::find()
are simply handed off to DboSource for processing, which generates
data that is directly embedded in the query's SQL. You can pass some
of these options in from a higher level, specifically 'joins' .

One example I see commonly for this is searching tags, which are
joined to a model via a hasAndBelongsToMany association. Typically,
this is accomplished with model binding or raw query hacking. However,
you can accomplish this just as easily using manual joins.

Right now at `work`_, I'm involved in a project that integrates map-
marking features, and one of the requirements is to be able to tag
markers, and search those markers by tag. The search form provides a
text field called q , that accepts a space-separated list of tag
names, and is submitted via GET. Here is an example of the search code
in MarkersController :


Controller Class:
`````````````````

::

    <?php 
    	$markers = $this->Marker->find('all', array('joins' => array(
    		array(
    			'table' => 'markers_tags',
    			'alias' => 'MarkersTag',
    			'type' => 'inner',
    			'foreignKey' => false,
    			'conditions'=> array('MarkersTag.marker_id = Marker.id')
    		),
    		array(
    			'table' => 'tags',
    			'alias' => 'Tag',
    			'type' => 'inner',
    			'foreignKey' => false,
    			'conditions'=> array(
    				'Tag.id = MarkersTag.tag_id',
    				'Tag.tag' => explode(' ', $this->params['url']['q'])
    			)
    		)
    	)));
    ?>

We now have automatic filtering across an HABTM relationship. However,
this requires quite a bit of code, and there are some redundancies in
our implementation. Let's see if we can't refactor this such that it's
a bit more reusable:


Model Class:
````````````

::

    <?php 
    class AppModel extends Model {
    
    	public function find($type, $options = array()) {
    		if (!isset($options['joins'])) {
    			$options['joins'] = array();
    		}
    
    		switch ($type) {
    			case 'matches':
    				if (!isset($options['model']) || !isset($options['scope'])) {
    					break;
    				}
    				$assoc = $this->hasAndBelongsToMany[$options['model']];
    				$bind = "{$assoc['with']}.{$assoc['foreignKey']} = {$this->alias}.{$this->primaryKey}";
    
    				$options['joins'][] = array(
    					'table' => $assoc['joinTable'],
    					'alias' => $assoc['with'],
    					'type' => 'inner',
    					'foreignKey' => false,
    					'conditions'=> array($bind)
    				);
    
    				$bind = $options['model'] . '.' . $this->{$options['model']}->primaryKey . ' = ';
    				$bind .= "{$assoc['with']}.{$assoc['associationForeignKey']}";
    
    				$options['joins'][] = array(
    					'table' => $this->{$options['model']}->table,
    					'alias' => $options['model'],
    					'type' => 'inner',
    					'foreignKey' => false,
    					'conditions'=> array($bind) + (array)$options['scope'],
    				);
    				unset($options['model'], $options['scope']);
    				$type = 'all';
    			break;
    		}
    		return parent::find($type, $options);
    	}
    }
    ?>

Here, rather than simply hard-coding the joins for one association,
we've abstracted the code and put it in AppModel , such that it now
applies across any HABTM association and can be used with any field in
the joined table. Before examining the code, let's take a look at the
usage:


Controller Class:
`````````````````

::

    <?php 
    	$markers = $this->Marker->find('matches', array(
    		'model' => 'Tag',
    		'scope' => array('Tag.tag' => explode(' ', $this->params['url']['q']))
    	));
    ?>

Now, rather than building complicated joins in the controller, we have
all our logic neatly tucked away, and need only specify the name of
the model to be queried across, and the "scope" or filtering to be
applied when joining. Since the joins are only used for searching and
don't affect the field selection, the query results are returned in
the same format as normal, and no additional data transfer overhead is
incurred.

The rewritten method itself is actually quite simple when broken down.
In each join, we're simply doing the job that Cake would normally do,
and transferring array values from the available association info, to
the appropriate places in the array that describes the join operation.
In each instance, the $bind variable is used to generate the string
that defines the foreign key relationship to the joined table. In each
case as well, we're using INNER joins rather than LEFT, since we want
any records not matching our criteria to be filtered out of the
primary recordset.

Hopefully this sample will prove helpful in your work. If you have any
questions or ideas, please leave them in the comments.

.. _work: http://omniti.com/

.. author:: nate
.. categories:: articles, tutorials
.. tags::
model,search,find,hasAndBelongsToMany,HABTM,filter,join,Tutorials

