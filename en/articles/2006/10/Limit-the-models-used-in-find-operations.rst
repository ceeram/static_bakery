

Limit the models used in find operations
========================================

by %s on October 08, 2006

Often when issuing a find, findAll or other find*-variety there's way
too much data returned which puts unneccessary overhead to your
database server or it might pose a security risk when some crucial
fields like passwords are available to the view designers just because
the users-table was included in your findall...
For example you've got a User model which hasMany Article, hasOne
ExtendedProfile.
If you want to get the User with id 1 and his ExtendedProfile in one
step, for example using

PHP Snippet:
````````````

::

    <?php 
    $this->User->read(null, '1');
    ?>

You'll also get all the associated Articles which you might not be
interested at that stage and which also might put unnecessary strain
to your mysql server. So you might only want to fetch the data from
User and ExtendedProfile.

There's the famous unbindAll() function from CrazyLegs and Oth at
`http://othy.wordpress.com/tag/uncategorized/`_ which lets you ignore
certain relations for the next query.
But this one is tedious to use because the whole relationship needs to
be provided in the argument each time.
So I've modified that function to accept just a list of Model names as
parameters which will be included in the find. All not-mentioned
models are ignored (at least at recursion <= 1).

Important: You need to call useModel() before every find* function (if
needed) because after the find has executed, the relationships are
automatically restored to the default.

Example usage from a controller: Get only the data from the User and
the ExtendedProfile, but ignore the Articles this user currently has.


PHP Snippet:
````````````

::

    <?php 
    $this->User->useModel( array('ExtendedProfile') );
    $this->User->read(null, '1'); // Find the entry with id=1
    ?>

Instead of an array of relation names a single relation name can be
passed as string to the function.
The name used as argument(s) to useModel() is the name of the relation
defined in your hasOne/hasMany/belongsTo/HABTM configuration. This
usually corresponds to the model name but it can be different, for
example if there is more than one relation between two models.

If you need to do a find with recursion greater than 1 and want to
restrict the usage of the relationships in the other models you can
use a useModel() call for each Model used.
For example:

PHP Snippet:
````````````

::

    <?php 
    $this->User->useModel( array("ExtendedProfile", "Article") );
    $this->User->Article->useModel(); // To stop recursion there if Article has further relations for recursion > 1
    ?>

Place this in your /app/app_model.php to make it available everywhere


Model Class:
````````````

::

    <?php 
      function useModel($params = array())
      {
        if( !is_array($params) )
          $params = array($params);
        
        $classname = get_class($this); // for debug output
        
        foreach($this->__associations as $ass)
        {
          if(!empty($this->{$ass}))
          {
            // This model has an association '$ass' defined (like 'hasMany', ...)
            
            $this->__backAssociation[$ass] = $this->{$ass};
    
            foreach($this->{$ass} as $model => $detail)
            {
              if(!in_array($model,$params))
              {
                //debug("Ignoring association $classname <i>$ass</i> $model... ");
                $this->__backAssociation = array_merge($this->__backAssociation, $this->{$ass});
                unset($this->{$ass}[$model]);
              }
    
            }
              
          }
        }
        
        return true;
      }
    ?>



.. _http://othy.wordpress.com/tag/uncategorized/: http://othy.wordpress.com/tag/uncategorized/
.. meta::
    :title: Limit the models used in find operations
    :description: CakePHP Article related to ,Snippets
    :keywords: ,Snippets
    :copyright: Copyright 2006 
    :category: snippets

