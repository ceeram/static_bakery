Observable Models
=================

by burningodzilla on October 14, 2006

Make your Cake application more flexible with observable models.
Let me preface by saying that I am a not a zealout of any paradigm or
language.
I firmly believe in the right tool for the task, except on occasion
where I'd
prefer to do something just for the joy/challenge/hell of it.
Therefore I do not
preach design patterns, but in my experience of developing (primarliy
thick-client)
apps, I have found some to be quite useful. In this article I discuss
how the observer pattern
(`http://en.wikipedia.org/wiki/Observer_pattern`_)
may be useful in a Cake app, and how to implement it elegantly.
This article assumes that you are familiar with the concept of
observers and observables.

One of Cake's boastings is that it's a MVC
(`http://en.wikipedia.org/wiki/Model-view-controller`_)
framework. One of the core elements of MVC, OOP, and reusable software
in general is
loose coupling (`http://en.wikipedia.org/wiki/Loose_coupling`_); that
is,
components of a system should know as little as possible about the
rest of the
system. This clause is left open to interpretation, and is often
either ignored,
forgotten, or implemented so insanely that the resulting code is a
mass of
harrowing indirection. This defeats the overall goal of simplicitly.
That said,
I'm going to focus on Cake's implementation of the MVC model.

From what I've come to understand Cake's concept of a model is
primarly a canned, simplified
object persistence and storage abstraction tool (which I assume is a
carry-over
from RoR), and a querying tool. It is extremely good at this, and has
made my life much easier.
However, as useful as this feature is, it has little or nothing to do
with classic MVC models,
which are meant to encapsulate domain (often called business) logic of
a singular
model instance. (Of course it may be argued that part of an object's
domain logic
includes the storage and retrieval of its state). This is a topic
worth debating
in itself, but that is outside the scope of this article. Some of the
provided
methods can be used to query a singular model's state, but most are
used to find
groups of instances based on a certain criteria.

Another common feature of a MVC model is the ability for third parties
to receive
notification of a change in the model's state (commonly done via the
observer pattern).
This is where Cake's default model class comes up short. True, it does
provide
callbacks for certain events, but these are not available outside the
model itself.
That is, only your model implementations can receive notification. See
the model section
(`http://manual.cakephp.org/chapter/models`_) in the Cake manual
for details. What if you have a component that should react to a
certain model event?
You could probably hard-code this into your model, but aside from
being a hack,
this would mean that your model needs intimate knowledge about another
part of the system
(the external component). A better solution would be to register any
arbitrary object with the model as
an observer, and then when the event(s) of interest occurs, the model
should notify
the observing object(s).

I recently worked on an app that has several models to which a user
can associate comments.
These models are not at all related, yet both can have comments. The
comments themselves are
composed of the same data, no matter what they are associated with. As
such, I created a
model whose table consists, among the other fields, the comment id,
owner id, and the type
of model that owns it:

::

    
    CREATE TABLE `rl_object_comments` (
      `id` int(11) NOT NULL auto_increment,
      `rl_object_id` int(11) NOT NULL default '0',
      `type` varchar(255) NOT NULL default '',
      `author` varchar(50) NOT NULL default '',
      `email` varchar(255) NOT NULL default '',
      `url` varchar(255) NOT NULL default '',
      `body` text NOT NULL,
      `ip` varchar(15) NOT NULL default '',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

The model class contains a method for querying comments by the type
and id of the parent model.
This model can be associated with any other model, used in any
controller; all that's
needed is an action to accept and save the comment. To prevent comment
spam that is
rampant in sites which allow anonymous commenting, I've implemented a
simple security
mechanism that, among other things, logs the IP address of the
commenting user as
a failsafe. Yes, I could just set this in the controller action, or I
could even
use a central comment controller - but remember that this model is
meant to be associated with
completely different types of models, and therefore different
controllers.
This is a design decision that fits well in the context of my problem.
So how can my component inject the IP address into the array of data
being stored
in the database, without the model ever knowing? Let's start with an
observable base model:


Model Class:
````````````

::

    <?php 
    /**
     * An observable base model.
     *
     * @author Chris Lewis <chris@silentcitizen.com> - 10/13/2006
     */
    class AppModel extends Model {
    	
    	/* Array of observers wanting to be notified when the model is saved. */
    	var $beforeSaveL = array();
    	
    	/**
    	 * Override the beforeSave callback and notify our observers.
    	 * Remember that if this method doesn't return true, the model will be
    	 * tagged as invalid and fail to save.
    	 */
    	function beforeSave() {
    		return $this->notifyObservers();
    	}
    	
    	/**
    	 * Dump the observsers (PHP 5).
    	 */
    	function __destruct() {
    		unset($this->beforeSaveL);
    	}
    	
    	/**
    	 * Notify our observers.
    	 */
    	function notifyObservers() {
    		$valid = true;
    		foreach($this->beforeSaveL as $observer) {
    			//Using a flag like this allows the observers to invalidate
    			//the model, should they need to. The observers must implement
    			//the modelSaving(&$model) method.
    			$valid = $valid && $observer->modelSaving($this);
    		}
    		return $valid;
    	}
    	
    	/**
    	 * Register an observer to be notified during beforeSave().
    	 * @param $observer The observer.
    	 */
    	function addObserver(&$observer) {
    		array_push($this->beforeSaveL, $observer);
    	}
    	
    }
    ?>

Note that if you wanted only certain models to be observable, you
could use a deeper class hierarchy.
So now we have observable models. Great. Let's put it to use with an
observing component:


Component Class:
````````````````

::

    <?php 
    /**
     * The comment security component uses simple techniques to add assurance
     * that comments being posted are done so by humans.
     *
     * @author Chris Lewis <chris@silentcitizen.com> - 10/13/2006
     */
    class RlObjectCommentSecurityComponent extends Object {
    	
    	/* A reference to the comment model. */
    	var $commentModel = null;
    	
    	/**
    	 * To reduce coupling between the model and the component, we require
    	 * the controller to implement the getCommentModel() method, which
    	 * returns its reference to the comment model. This way we don't even
    	 * need the name of the model. Once we have the model reference, we
    	 * register $this as an observer.
    	 */
    	function startup(&$controller) {
    		$this->controller =& $controller;
    		$this->commentModel =& $controller->getCommentModel();
    		$this->commentModel->addObserver($this);
    	}
    	
    	//... other security code removed for brevity ...
    	
    	/**
    	 * Now that $this is an observer of the comment model, we can access it
    	 * here whenever save() is called.
    	 */
    	function modelSaving(&$model) {
    		//To accomplish our goal of saving the IP address, we simply add
    		//the 'ip' key to the model's data array.
    		$model->data[$this->commentModel->name]['ip'] = $_SERVER['REMOTE_ADDR'];
    		return true;
    	}
    }
    ?>

Using an observable model provides us with two powerful capabilities
that the default model doesn't provide.
1. Logic can now be associated with model events outside the model,
reducing coupling and adding flexibility.
2. Multiple external objects can be notified of these events, instead
of a single internal code block.

It's late, so if there are typos etc, I apologize. Feel free to email
me about this article.



.. _http://en.wikipedia.org/wiki/Model-view-controller: http://en.wikipedia.org/wiki/Model-view-controller
.. _http://manual.cakephp.org/chapter/models: http://manual.cakephp.org/chapter/models
.. _http://en.wikipedia.org/wiki/Observer_pattern: http://en.wikipedia.org/wiki/Observer_pattern
.. _http://en.wikipedia.org/wiki/Loose_coupling: http://en.wikipedia.org/wiki/Loose_coupling

.. author:: burningodzilla
.. categories:: articles, tutorials
.. tags:: observable model,cake models,design patterns,observer
pattern,Tutorials

