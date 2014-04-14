Bindable Behavior: control your model bindings
==============================================

by mariano on June 12, 2008

With the birth of CakePHP 1.2 pre-beta, here comes the newly,
improved, and extended version of Bindable, a method to easily control
what model relationships are obtained from a find operation, and even
customize binding settings.


Deprecation
~~~~~~~~~~~

IMPORTANT NOTE : As of CakePHP 1.2 RC1, Bindable behavior will no
longer be supported. You should use the core behavior Containable, a
mix between the Bindable behavior and Containable. If you are using
Bindable on your CakePHP application, then upgrade to 1.2 RC1 and take
the following steps:


#. Change your $actsAs = array('Bindable') to $actsAs =
   array('Containable') in your AppModel.
#. Search & Replace: Change all your restrict() calls to contain().
#. Search & Replace: Change all your 'restrict' find parameters to
   'contain'.
#. Search & Replace: (If you have any) Change your resetBindable()
   call to resetBindings().



Introduction
~~~~~~~~~~~~

In case you haven't heard of it, the previous version of Bindable was
`expects`_, which was designed to work with CakePHP 1.1.x, and its
objective was to provide an easy way to specify what models are
returned when issuing a find (find / findAll) operation over a model
that has one or more bindings, without having to deal with CakePHP's
core unbindModel. Would the previous version of Bindable work with
CakePHP 1.2? Sure. Then why change it? The 1.2 release of CakePHP has
brought us lot of exciting features, amongst them behaviors: a way to
extend the (you've guessed it) behavior of models.

While pursuing the same goals as those we had with the previous
version, it was about time that expects brought something new to the
table. Specifically, the ability to change binding settings, such as
the order on which one to many and many to many relationships are
returned, or what conditions the model binding has to meet, etc. We
also needed a strong test coverage that would help all of us prevent
any potential problems.

Meet Bindable Behavior, where expects meets CakePHP 1.2. On this
article you'll learn what features it provides, how to get it up and
running in no time, and what functionalities other than the ordinary
it offers.


Download, Source Code and Bug Tracking
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The latest Bindable Behavior release is 1.3.1 . For those of you who
wish to keep up with the latest (not necessarily stable) Bindable
Behavior resides in the SVN repository of a project that includes
other CakePHP goodies: `Cake Syrup`_. All future official releases
will be posted on this article.

`Get Bindable Behavior 1.3.1`_ (`Release Notes & Changelog`_)

All reports, enhancements and feature feedback should be provided
through the project page, and not in comments for this article, so I
can keep a closer track. Please do report any issues you find with
Bindable Behavior using its tracker:

`Cake Syrup Tracker (Bugs / Features)`_
If you want to view the source code of the latest version of the
Bindable Behavior you can do so using the SVN browser: `bindable.php`_

Features
~~~~~~~~
Let's get right into the goodies. What does Bindable Behavior offer
us?


#. Flexibile calling methodology : you can call bindable and then
   issue your find, or take advantage of the embedded find calls in
   CakePHP 1.2 and do your find and bindable all in just one simple call.
#. Parameter notation flexibility : you can choose different ways on
   how to specify the models you need. From dot notation, to associative
   arrays, or a combination of both.
#. Ability to override binding settings : tired of having to do a
   bindModel() to override some binding settings (such as limit, order,
   offset) right before your bindable call? Want to only get certain
   fields of a related model, instead of all of them? Don't stress!
   Bindable Behavior allows you to override these settings easily.
#. Auto recursiveness : determine the necessary recursiveness needed
   to fetch the models (up to the deepest level) you specified, and will
   set the main model recursiveness accordingly. No longer you need to
   remember to set the recursiveness to a high enough value to get the
   bindings you need.



Installation & Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~
After downloading the file bindable.php, place it under your
/app/models/behaviors directory. That's it, you are now ready to start
using the Bindable Behavior. Since we will want to be able to run
bindable on any model, we'll add it to the list of behaviors all our
models will utilize: the list defined at the AppModel level. If you
haven't done so before, create a file named /app/app_model.php with
the contents listed below. If you have already created the file
app_model.php, edit it and add the $actsAs member variable to your
AppModel.


Model Class:
````````````

::

    <?php 
    class AppModel extends Model {
    	var $actsAs = array('Bindable');
    }
    ?>

We now have the Bindable Behavior available for all our application
models. Since we have not defined any particular setting, the behavior
will be set up for all models using the default settings. If you
haven't been exposed to CakePHP behaviors before then all you need to
know is that some behaviors accept certain settings that affect the
way they, oh well, behave. Those settings are given right on the
$actsAs declaration, as a set of attribute-value pairs. Let's start by
seeing what settings the Bindable Behavior offers us:


#. recursive : optional, boolean value, defaults to true. Set it to
   true to automatically determine the recursiveness level needed to
   fetch specified models, and set the model recursiveness according to
   this level. Setting it to false disables this feature.
#. notices : optional, boolean value, defaults to false. Set to true
   to let Bindable issue a E_NOTICE error message when bindings
   referenced in a call do not exist. Setting it to false disable these
   notices.
#. autoFields : optional, boolean value, defaults to true. If you
   override the fields obtained for your bindings and autoFields is set
   to true, Bindable will make sure that all the necessary fields
   required to fetch your bindings are also included.

Let's say that we want to get notices on all our models when a
referenced binding is invalid, so we go back to app_model.php and edit
it so the $actsAs statement now looks like:


PHP Snippet:
````````````

::

    <?php var $actsAs = array('Bindable' => array('notices' => true));?>

If you set recursive to false then you are responsible for setting the
model recursiveness level to fetch your desired bindings. However you
can still use Bindable to let it tell you what recursiveness level you
should set, by keeping the result given after calling Bindable:


PHP Snippet:
````````````

::

    <?php 
    $this->Model->recursive = $this->Model->restrict(...);
    $results = $this->Model->findAll(...);
    ?>



Simple Usage
~~~~~~~~~~~~
Suppose we have the following models setup:


#. Article : belongsTo User ; hasMany Comment ; hasAndBelongsToMany
   Tag
#. Comment : belongsTo User , Article ; hasMany Attachment
#. User : hasMany Article , Comment ; hasOne Profile
#. Attachment : belongsTo Comment , Type
#. Profile : belongsTo User ; hasOne Picture ; hasMany Friend ,
   Setting ; hasAndBelongsToMany Tag

Let's say that we want to get a list of all articles, but we are just
interested in the User that created each article. That means that by
the time we are issuing our find call on model Article , we just need
the User binding. We then would use Bindable Behavior to define this
limitation, and perform the find:


Controller Class:
`````````````````

::

    <?php 
    $this->Article->restrict('User');
    $articles = $this->Article->findAll();
    ?>

The first line tells Bindable Behavior to unbind all bindings linked
to Article except the binding it has with User . At the same time, all
bindings in User are also removed. This is per-bindable call, since
all our bindings will get restored to their original definition right
after our find operation. Furthermore, and since we have not changed
the automatic set of recursiveness, Bindable Behavior will set the
recursiveness level of the Article model to 1, which is the level
needed to obtain the requested binding. The second line will issue the
actual find call to get all records in Article. In effect, the above
call is the equivalent of the following block of code that uses pure
CakePHP core methods:


Controller Class:
`````````````````

::

    <?php 
    $this->Article->unbindModel(array(
    	'hasMany' => array('Comment'),
    	'hasAndBelongsToMany' => array('Tag')
    ));
    $this->Article->User->unbindModel(array(
    	'hasMany' => array('Article', 'Comment'),
    	'hasOne' => array('Profile')
    ));
    $this->Article->recursive = 1;
    $articles = $this->Article->findAll();
    ?>

So we can say we have definitely saved a few lines of code. Now what
about that first feature we mentioned, "Flexibile calling
methodology"? Those of you who have been keeping up with `CakePHP 1.2
release notes`_ may have heard that there's an improved syntax to
execute your model find operations. This new syntax replaces find (to
find just one record), findCount (to find the total number of records
that optionally match a given condition), and findAll (to find a set
of records that optionally match a given condition.).

Though the old style of calling each of these methods will still work
(that's in fact what we've used on the previous example), some bakers
prefer the new way. So let's see how Bindable Behavior fits into this.
Let's write the same Bindable call as we did before, but using the new
syntax:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array('User')));
    ?>

It is up to you what syntax you decide to use, I personally prefer the
last one since it allows you to quickly see what you are searching for
(that is, if there are conditions being sent to the find), and what
models you are expecting as a result.


Deeper Bindings
~~~~~~~~~~~~~~~
So far we have seen the simplest usage possible for Bindable: working
with first level bindings. Let's now get all articles, and for each
article the user who wrote it (together with its profile record), and
the comments it has (along with the user who wrote each comment). Our
Bindable call now looks like:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User' => 'Profile',
    	'Comment' => 'User'
    )));
    ?>

Let's add one more: we are also interested in getting the attachments
each comment has, and for each of these attachments we want to get
their type:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User' => 'Profile',
    	'Comment' => array('User', 'Attachment' => 'Type')
    )));
    ?>

Get the picture? Now some of you may have already got used to the dot
notation found in the previous expects version. The Bindable Behavior
can handle it too, let's rewrite the previous call to use dot notation
instead:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User.Profile', 
    	'Comment.User', 'Comment.Attachment.Type'
    )));
    ?>

Mixing the two notations would also work:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User' => 'Profile',
    	'Comment' => array('User', 'Attachment.Type')
    )));
    ?>

As you have pointed out before, Bindable is automatically setting the
appropiate recursiveness for the model so we can fetch the models we
need. This is a setting that can be changed at any time, as we learned
on the Installation & Configuration section. But what happens if we
want to get all models linked to an inner relationship, and we don't
really want to list them all in Bindable? That is, take the last call
we made. Suppose we are interested in fetching all models linked to
the Profile binding. Profile, as we saw before, has the following
relationships: User (belongsTo), Picture (hasOne), Friend, Setting
(hasMany), and Tag (hasAndBelongsToMany). One way is to define all
these models on the Bindable call:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User' => array('Profile' => array(
    		'User', 'Picture', 'Friend', 'Setting', 'Tag'
    	)),
    	'Comment' => array('User', 'Attachment.Type')
    )));
    ?>

But that seems like a waste of time, and we might run into trouble if
we forget to add a possible future binding we'll need for Profile. So
instead let's use the wildcard to let Bindable know that we are
interested in all models directly bound to Profile:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User' => array('Profile' => '*'),
    	'Comment' => array('User', 'Attachment.Type')
    )));
    ?>

or with dot notation:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User' => 'Profile.*',
    	'Comment' => array('User', 'Attachment.Type')
    )));
    ?>



Overriding binding settings
~~~~~~~~~~~~~~~~~~~~~~~~~~~
Another great feature the Bindable Behavior has is the ability to
change binding settings on the fly. Just as we specify which models
should or should not be returned in a find operation, we can also
specify what binding settings should be used when we issue the find
call. Let's get all articles, for each article the user who wrote it,
and the tags linked to the article:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User',
    	'Tag'
    )));
    ?>

Let's say that we don't really want all the tags linked to the
article, but we only need 5 of them. We can then specify Bindable to
override the limit:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User',
    	'Tag' => array('limit' => 5)
    )));
    ?>

What about getting the latest 5 tags instead? No problem, just
override the order binding setting:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User',
    	'Tag' => array('limit' => 5, 'order' => 'Tag.created DESC')
    )));
    ?>

Assume we want only the username and the email of the user who wrote
the article. Eventhough we have that information already on our
previous call (since the User model is returned with all its fields),
we are interested in saving resources, so we want to specify exactly
the fields we need:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User' => array('fields' => array('username', 'email')),
    	'Tag' => array('limit' => 5, 'order' => 'Tag.created DESC')
    )));
    ?>

So as you see, fields is just another binding setting we can override.
The good thing about fields is that we don't really need to tell
Bindable when we're specifying fields, it will figure it out by
itself. So instead we can define the fields just as we would define a
model binding:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User' => array('username', 'email'),
    	'Tag' => array('limit' => 5, 'order' => 'Tag.created DESC')
    )));
    ?>

If we also needed User bindings to be returned, but we forget to
specify the fields needed to fetch it (like its primary key), Bindable
Behavior will do it for us.

You can also mix your binding settings definition with which models
should be returned. For example, we can take the previous example and
also define that the User model should include the Profile each user
has, but we are still only interested in the username and email
fields:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User' => array('username', 'email', 'Profile'),
    	'Tag' => array('limit' => 5, 'order' => 'Tag.created DESC')
    )));
    ?>

Bindable behavior will be smart enough to include additional fields
that may be required to fetch the Profile binding. If you don't want
Bindable to include the mandatory fields, set the behavior setting
autoFields to false.

Just in case you are interested, the above fields override can also be
written diferently, using dot notation. If we were not interested in
the Profile binding we would do:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User(username, email)',
    	'Tag' => array('limit' => 5, 'order' => 'Tag.created DESC')
    )));
    ?>

But if we are still interested in this notation and getting the
Profile model:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User(username, email)' => 'Profile',
    	'Tag' => array('limit' => 5, 'order' => 'Tag.created DESC')
    )));
    ?>

Once again, it is up to you what notation you decide to use.


Making your binding changes permanent
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Every example we've seen to this point shows how CakePHP will restore
the original bindings, and its settings, right after a find operation
is completed. This is because Bindable uses bindModel, which backs up
the bindings for later resetting. However we may find situations on
where we need to make the binding changes permanent. CakePHP already
offers us a way to specify when calling a bindModel/unbindModel if the
original associations should be reset after a find, and since Bindable
is a complex wrapper for these core functions, the behavior also
offers us a way to do such thing. Let's take the following call:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'User',
    	'Tag' => array('limit' => 5)
    )));
    ?>

And let's assume we want these changes (that is, that Article is now
only bound to User and Tag, and the Tag binding will only return 5
records) to be permanent. We specify that through the 'reset' setting:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->find('all', array('restrict' => array(
    	'reset' => false,
    	'User',
    	'Tag' => array('limit' => 5)
    )));
    ?>

In case you fancy the non-embedded Bindable call, the above would be
(we set the reset parameter as the first argument):


Controller Class:
`````````````````

::

    <?php 
    $this->Article->restrict(false, array(
    	'User',
    	'Tag' => array('limit' => 5)
    ));
    $articles = $this->Article->find('all');
    ?>

After running this find operation, if we would've done a normal
find('all') on Article we would see that the bindings returned are the
same as we've specified on the Bindable call above. This is because
the original bindings have not been reset. In fact they won't reset
until we say so. Unlike CakePHP's core unbindModel/bindModel, where
permanent changes are, well permanent, Bindable still gives us a way
to change our minds and restore the original bindings, by calling
resetBindable with a force parameter set to true:


Controller Class:
`````````````````

::

    <?php 
    $articles = $this->Article->resetBindable(true);
    ?>



.. _Cake Syrup Tracker (Bugs / Features): http://sourceforge.net/tracker/?group_id=209331
.. _ Changelog: http://sourceforge.net/project/shownotes.php?group_id=209331&release_id=598948
.. _expects: http://bakery.cakephp.org/articles/view/an-improvement-to-unbindmodel-on-model-side
.. _Get Bindable Behavior 1.3.1: http://sourceforge.net/project/showfiles.php?group_id=209331&package_id=251195&release_id=598948
.. _CakePHP 1.2 release notes: http://bakery.cakephp.org/articles/view/new-cakephp-releases
.. _Cake Syrup: http://cake-syrup.sourceforge.net/
.. _bindable.php: http://cake-syrup.svn.sourceforge.net/viewvc/cake-syrup/trunk/app/models/behaviors/bindable.php?view=markup
.. meta::
    :title: Bindable Behavior: control your model bindings
    :description: CakePHP Article related to ,Behaviors
    :keywords: ,Behaviors
    :copyright: Copyright 2008 mariano
    :category: behaviors

