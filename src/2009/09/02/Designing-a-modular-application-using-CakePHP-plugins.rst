Designing a modular application using CakePHP plugins
=====================================================

by eimermusic on September 02, 2009

This article will try to explain how I build a modular application
using plugins. I will cover how I implement them and integrate them
into the main application.


Definition
~~~~~~~~~~

There are a lot of different ideas around about what to put into the
CakePHP plugins directory. In this article a plugin extends the
application kind-of like a plugin in Photoshop.

â€¢ A plugin does not extend CakePHP or "any" application. It is
specific to "this" application.
â€¢ A plugin may (or rather should) depend on the application.
â€¢ The application may not depend on any one plugin.


Context
~~~~~~~

We need some context. An example application and how it makes use of
plugins. I have chosen the application I continually develop at work.
It is a system for communicating with mobile phones. Primarily via SMS
and MMS. (That is: text messages and picture/video messages to most
people.) I will use simplified and generalised features of this
application to exemplify the design and implementation of plugins.


The design
~~~~~~~~~~

The application has certain obvious core features. User management,
managing phone numbers, sending SMS and MMS, receiving SMS and MMS,
reporting on all transactions and other similar features.

Each concrete end-user feature is implemented as a plugin.
A few examples:
A newsletters module used to send offers and news to customers.
A feedback module used to receive and automatically publish incoming
feedback from customers on the client's website among other things.
An opinions module to manage quick opinion polls.
A subscriptions module and many others.

Each of these make use of incoming and/or outgoing SMS and MMS
messages in some way. Each one uses stored phone numbers. They all
manage these things by making use of core features.


How plugins talk to the main application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is the simple and obvious bit. A plugin can very simply just
extend or associate itself with some resource in the main application.
As an example (finally some code!), a subscriber model would look like
this.


Model Class:
````````````

::

    <?php 
    class SubsSubscriber extends SubsAppModel {
    	var $name = 'SubsSubscriber';	
    	var $belongsTo = array(
    		'Phone',
    		'SubsSubscription'=>array(
    			'className'	=> 'Subs.SubsSubscription',
    			'counterCache' => true
    		)
    	);
    }
    </pre>
    ?>

Not only does the subscriber belong to a subscription, it also belongs
to a phone. Creating a Subscriber involves registering the
subscriber's phone number with the Phone model. This ensures that
there are no duplicate numbers, that any restrictions put on a certain
phone is retained across the app. Things like that. The Phone model
implements this registration method.

::

    
    function register($number) {
    	$number = clean_phone($number); // utility function to normalize number formatting
    
    	// not a real phone number: abort
    	if ( empty($number) ) {
    		return false;
    	}
    	
    	// look for existing registration 
    	$phone = $this->find('first',array(
    		'conditions'=>array( $this->alias.'.number'=>$number )
    	));
    	
    	if ( !empty($phone) ) {
    		// found it, return it
    		return $phone;
    	}
    
    	// new number
    	$this->create();
    	$data = array(
    		$this->alias=>array(
    			'number'=>$number
    		)
    	);
    	
    	if ( $this->save($data) ) {
    		return $this->read();
    	} else {
    		return false;
    	}	
    }

That is really the basics behind how plugins interact with the main
application. Similarly when a plugin needs to send an SMS, this is
done using the SendSmsComponent in the main application. This
component abstracts all the messy SOAP garbage phone-operators
implement. :) Me, cranky? You bet!


How about the other way around?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The less obvious part is how the main application communicates back to
the plugins. If I was starting out today I would certainly implement
Eventful from cakealot.com, or something like Teknoid's observer
pattern. I did something a bit different.

I keep all plugins in a table accessed via a model. That way I can
have an administrative GUI to activate and deactivate different
plugins for different clients. I also simplifies handling of
permissions to plugins and menu-generation and other things. It is
possible to get by with simply reading the app/plugins directory and
caching that.

AppController implements some empty methods used as callbacks or event
handlers of sorts. As an example, when an SMS message is received in
the main application it parses the message and locates the plugin that
should receive it. It stores the message in the central SMS model and
notifies the plugin that a new message with the ID=123 has arrived and
that the plugin should do something about it.

What happens next if of-course all up to the plugin. It may register
the sender as a new subscriber, unregister the sender, post the
message to the web, send an email, reply automatically... anything it
wants. The point is that it is up to the plugin to decide.

The application does this via the much debated requestAction:

::

    
    $this->requestAction('/'.$module['Module']['name'].'/on_incoming_sms/'.$saved['IncomingSms']['id'], array('return'));

[p] And each plugin would implement this method to do something with
the message. This is done in the "PluginnameController" as the request
goes to /pluginname/on_incoming_sms/123. There is a positive side-
effect to this. If I want some core feature to be called in this way I
can make add a controller to the table of "modules" and requestAction
will never know the difference. (This is why I still use a string
url.)

::

    
    function on_incoming_sms($id = null) {
    	$this->autoRender = false;
    	if ( !isset($this->params['requested']) || empty($id) ) { // check that we are contacted "from within"
    		return false;
    	}
    	// do something useful here and return the id as confirmation
    	return $id;
    }

[p] Now I feel the need to get a bit on the defensive. I can almost
hear people shouting about thin controllers, fat models and the evil
of requestAction. This is communication. that is the exactly what
controllers are meant to do. In Cake this is usually between the
browser and the models but in this case it is between the app and it's
plugins. I don't have any such logical reason for using requestAction
except that is is by far the most convenient way of achieving this
kind of communication. Since these calls are usually done from either
an API call (incoming sms) or from a cron shell any increase in
request time is not a great priority. People get annoyed by an extra
0.4sec, a cron job doesn't :)


Speaking of Cron jobs
~~~~~~~~~~~~~~~~~~~~~
[p] Housekeeping is done via cron. I have cron call a Cake shell every
hour. This shell finds all modules and calls each one in turn. Again,
the main application does not know or care about what each module is
up to. One may update a cached view, another may remove expired items,
another may send scheduled messages and guess what the Reports module
does each hour?

This is an example of such a shell

::

    
    class HourlyShell extends Shell {
    
    	var $tasks = array('LogRotation');
    	var $uses = array('Module');
    	
    	function main() {
    		$this->out('Hourly shell updates');
    		$this->log('Hourly shell updates @ '.date('Y-m-d H:i:s'), 'cron');
    		
    		$this->LogRotation->execute();
    		
    		$this->update_plugins();		
    	}
    	
    	function update_plugins() {
    		$this->out('Updating plugins:');
    		$modules = $this->Module->find('list', array(
    				'fields' => 'Module.controller',
    				'conditions' => array('Module.installed'=>'1'),
    				'recursive' => -1
    			)
    		);
    
    		foreach ( $modules as $module) {
    			$this->out('> '.$module);
    			$this->requestAction('/'.$module.'/on_hourly_update', array('return'=>true, 'bare'=>1));
    		}
    	}
    }


The method being called will typically just pass the call along to one
or more parts of the module that need to do something periodic. For
example:

::

    
    function on_hourly_update() {
    
    	// the template in AppController can be used to check that the call is legitimate.
    
    	if ( parent::on_hourly_update() ) {
    
    		$this->SubsSubscription->deleteExpired();
    
    	}
    
    }
    



A bonus trick
~~~~~~~~~~~~~
[p] One cool thing that may not be apparent is that it is very easy to
build a new feature as a normal controller and then turn it into a
plugin. The trick is to name the plugin after the controller it should
replace. Then you take the controller and make it the
PluginnameController for that plugin. Cake will pass any call to that
controller automatically. Example time:

/news/edit/123 - would be seen as /controller/action/param
/news/edit/123 - can also be be seen as /plugin/controller/action
but since there is no edit controller in the news plugin Cake will
try:
/news/edit/123 - as /plugin{/pluginname}/action/param ending up in the
PluginnameController and the edit action.

You will have to move the model and views to the plugin too, of-
course.



Closing remarks
~~~~~~~~~~~~~~~
[p] That is the basic gist of how I have chosen to design a modular
system based on plugins. If you feel I have left something out, please
comment and I will answer or update the article.

Plugins make up most end-user features.
Plugins rely on models, components, elements and other code from the
main application.
The main application can pass events and information to plugins via
the PluginnameController in each plugin.

I have left out tricks and hacks to keep the article focused. In the
real application I use a patch to the dispatcher class that reformats
urls for plugins. News plugin has NewsMessagesController but showing
/news/news_messages/index in the url is redundant to say the least.
The patch allowsme to use urls like /news/messages/index and have the
dispatcher prepend the plugin name to the controller name. Since most
urls point to plugins I chose to patch the core for this feature. It
is not necessary to do any such patch to make use of plugins but for
me it made plugins a more attractive option as the foundation of most
features.

[p] That's all. Thanks for reading.



.. author:: eimermusic
.. categories:: articles, tutorials
.. tags:: plugin,plugins,modular,Tutorials

