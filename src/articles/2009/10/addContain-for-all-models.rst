addContain() for all models
===========================

by %s on October 01, 2009

As of CakePHP 1.2 Behaviours came up and people started working with
the "Containable" (see
[url]http://book.cakephp.org/view/474/Containable[/url]). In brief you
get much better control over associations and selects on theses in
find operations. But when associations grow up in your datamodel
things might get overhauling. You'll have to keep exiting associations
while adding contains to your query. This can be simplified by adding
an addContain()-Method to your models.
As you'll probably know you can add an app-wide app_model to your
application to get some kind of general methods to be used by your
models. So create that file in your app directory (app/app_model.php)
:

Model Class:
````````````

::

    <?php 
    class AppModel extends Model{
    /**
    * Activate Containable-Behaviour for all models.
    */
       var $actsAs = array('Containable');
     
    /**
     * Method to let you add containables to existing associations.
     *
     * @param $arr array of containables as usual
     * @return void
     * @author Marcus Spiegel
     **/
        function addContain( $arr=NULL ){
            // get all existing associations
            $assoc = $this->getAssociated();
            if(!empty($assoc)){
                // merge associations with contains
                $this->contain(am(array_keys($assoc), $arr));
            }else{
                $this->contain($arr);
            }
        } // end addContain
    }
    ?>

Now you can use it in your controller similar to the contain() method:

Controller Class:
`````````````````

::

    <?php 
     $this->Article->addContain(array(
                'Ean' => array(
                    'Color'     => array('ColorLocale'),
                    'Ve'        => array('VeLocale'),
                    ),
                'Group'     => array('GroupLocale'),
            ));
    ?>

You will get all associated data of "article" in addition to these
added by contain. So that's all quite nice, but you think that this
can be done by a simple use of contain() too. You're right! But now
think of the following: Later in production you have to add some more
associations, let's say an article hasMany images, belongsTo
additional associated articles and might get some download files as
hasMany.... so on so on.

All you need to do is add these in your model and you'll get them in
every find() operation. Well, as a rule of thumb addContain() comes in
handy when you want to get your complete model-data and add some
additional extra data contained by associated models.

You can read my approach step-by-step with examples, here:
`http://marcusspiegel.de/2009/09/03/howto-cakephp-addcontain-for-all-
models/`_

.. _http://marcusspiegel.de/2009/09/03/howto-cakephp-addcontain-for-all-models/: http://marcusspiegel.de/2009/09/03/howto-cakephp-addcontain-for-all-models/
.. meta::
    :title: addContain() for all models
    :description: CakePHP Article related to model,appModel,containable,Snippets
    :keywords: model,appModel,containable,Snippets
    :copyright: Copyright 2009 
    :category: snippets

