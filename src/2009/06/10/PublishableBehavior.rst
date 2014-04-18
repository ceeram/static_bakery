PublishableBehavior
===================

PublishableBehavior allows the use of datetime fields for start and
end ranges on content. Included functionality allows for checking
published status, toggling to published / unpublished status, and
adding conditions to a find to properly filter those results
In order to use Publishable you only need to add to columns to your
table. A datetime field for starting publishing and a datetime field
for ending it. These default to 'begin_publishing' and
'end_publishing' but you can set them to anything you like. These
columns also must be nullable.

Apply the behavior to your model like so:


Model Class:
````````````

::

    <?php 
    var $actsAs = array('Publishable');
    ?>

Or, if you would like to specify your columns:


Model Class:
````````````

::

    <?php 
    var $actsAs = array(
        'Publishable' => array(
            'start_column' => 'start_datetime_column',
            'end_column' => 'end_datetime_column'
            )
         );
    ?>

The Behavior checks to make sure the required columns are present when
initializing and will trigger an error if they cannot be found.

With the Behavior applied, you'll now have access to these functions:

- isPublished($id = '') - Returns true or false based on the published
status of the record.
//If $id is not passed, will attempt to use $model->id

- publish($id = array()) - Can either take a single id, a list of ids,
or no id to attempt to use $model->id. This will set the published
status of the record(s) to a start time of now and a null end time.

- unpublish($id = array()) - Can either take a single id, a list of
ids, or no id to attempt to use $model->id. This will set the
published status of the record(s) to a start time of null and an end
time of null.

- publishConditions($published = true) - Returns a conditions array
used to retrieve published or unpublished records that can be used in
your own queries / model relationships.

No records are automatically filtered though. If you'd like to
retrieve only published records from a query, simply add 'published'
=> true to the find options, like so:


Controller Class:
`````````````````

::

    <?php 
    $this->SomeModel->find('all',array('published' => true));
    ?>

You can also pass false to get only the unpublished records.

You can find the code on my public GitHub account here:

`http://github.com/brightball/open-source/tree/master`_
I've updated my GitHub account path, so your prior links may be
broken. The new path is available above.

.. _http://github.com/brightball/open-source/tree/master: http://github.com/brightball/open-source/tree/master

.. author:: brightball
.. categories:: articles, behaviors
.. tags:: behavior,intabox,unpublish,publish,brightball,Behaviors

