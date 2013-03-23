CakePHP Simple Queue Plugin
===========================

by %s on September 04, 2009

This Plugin is an attempt to provide a basic, simple to use method to
enable deferred job execution, without the hassle of setting up or
running an extra queue daemon, while integrating nicely into CakePHP
and also simplifying the creation of worker scripts.


Why use deferred execution?
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Deferred execution makes sense (especially in PHP) when your page
wants to execute tasks, which are not directly related to rendering
the current page.
For instance, in a BBS-type system, a new users post might require the
creation of multiple personalized email messages, notifying other
users of the new content.
Creating and sending these emails is completely irrelevant to the
currently active user, and should not increase page response time.
Another example would be downloading, extraction and/or analyzing an
external file per request of the user. The regular solution to these
problems would be to create specialized cronjobs which use specific
database states to determine which action should be done.

The Queue Plugin provides a simple method to create and run such non-
user-interaction-critical tasks.


Installation
~~~~~~~~~~~~
The Sourcecode can be found at
`http://github.com/MSeven/cakephp_queue`_
Either download or git-clone the sourcecode and place it into
'plugins/queue' below your app-folder.

Then run the following command as cake shell to create the database
table:
cake schema run create -path plugins\queue\config\sql -name queue

Now, when running the CakePHP console with no parameters, you should
see â€œqueueâ€ listed as an available Plugin shell.


Usage
~~~~~

The Plugin provides a Model with which "tasks" can be created, and a
shell for task execution.

Let's look at the Shell first.

You can get some basic information using
cake queue help

Besides usage info, this will also list the available task types.

The Plugin ships with a 'queue_example' task, which we will use for
our demonstration.

just run the following:

cake queue add example

This will add a Job of the type 'example' (or queue_example if you
want to be picky). Basically this calls the function
queueExampleTask->add() which in turn will output some text and create
a job entry.

Go ahead, run the last line some more times, creating a few more jobs.

Now, after having a few of these jobs queued up run the following to
execute them:

cake queue runworker

This will run the (by default persistent) workerprocess, which will in
turn fetch and process the exampleTask jobs you just created.

Output will propably look like this:

Looking for Job....
Running Job of type "example"
---------------------------------------------------------------
CakePHP Queue Example task.
---------------------------------------------------------------
->Success, the Example Job was run.<-

Job Finished.
---------------------------------------------------------------
Looking for Job....
Running Job of type "example"
---------------------------------------------------------------
CakePHP Queue Example task.
---------------------------------------------------------------
->Success, the Example Job was run.<-

Job Finished.
---------------------------------------------------------------
Looking for Job....
nothing to do, sleeping.
... after which the worker process will just sit there and check for
new tasks every few seconds.

If you like, you can open another terminal/command prompt and add
another example task, which will be picked up by the running worker.

To quit the worker, you will have to kill it using ctrl-c

That's basically it.

You can also run multiple workers, each executing parts of the queue.


Creating your own QueueTasks
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The QueueTasks included in the plugin are some simple examples of
usage, so you will have to create your own QueueTask types.

Doing so is quite simple.

Create the file for your Task in
YOURAPP/vendors/shells/tasks/queue_somename.php

Use the following template and add your own functionality:

::

    
    <?php
    class queueSomenameTask extends Shell {
    	public $uses = array(
    		'Queue.QueuedTask'
    	);
    
    	public function run($data) {
    	/**
    		add your code here, using $data.
    		return true on success
    		return false on failure (to requeue task for later)
    	 **/
    	}
    }
    ?>

This should give you a (minimal) executable task. This Task currently
does'nt support the 'add' command used in the example above.

To be able to create job's via the shell add this function to your
QueueTask:

::

    
    <?php
    public function add() {
    	$url = $this->in('give me a string:');
    	if ($this->QueuedTask->createJob('somename', array(
    		'url' => $url
    	))) {
    		$this->out('OK, job created');
    	} else {
    		$this->err('Could not create Job');
    	}
    }
    ?>

This will add a Job to the queue, that will execute the run() method
of queueSomenameTask passing the string that was inserted in the shell
as $data.
Remember, you don't NEED to add this, it's just a convenience
function. You can instance the model 'Queue.QueuedTask' from pretty
much anywhere and use QueuedTask->createJob(taskname,data) to create a
new job. The passed data is serialized into the database, and passed
to run(), use this either for configuration or small amounts of array
data, but be sensible. Of course you can use Model's inside your
QueueTask.

Another, more practical example is the following twitter scraper,
which is based upon this bakery article
(`http://bakery.cakephp.org/articles/view/building-your-first-twitter-
mash-up`_).

After having the Twitter datasource and tweets model/table set up as
described, you can add the following queueTask to keep tweets up to
date:

::

    
    <?php
    class queueTwitterscrapeTask extends Shell {
    	public $uses = array(
    		'Tweet',
    		'Queue.QueuedTask'
    	);
    
    	public function add() {
    		$this->out('Twitterscraper');
    		$term = $this->in('Tag/term to keep updated:');
    		if (!empty($term)) {
    			if ($this->QueuedTask->createJob('twitterscrape', array(
    				'search' => $term
    			))) {
    				$this->out('Searchterm update Queued');
    			} else {
    				$this->err('Could not create Twitterscrape Job.');
    			}
    		}
    	}
    
    	public function run($data) {
    		if (array_key_exists('search', $data) && !empty($data['search'])) {
    			$search = $data['search'];
    
    			$this->Twitter = ConnectionManager::getDataSource('twitter');
    			$search_results = $this->Twitter->search(urlencode($search), 'all', 100);
    			$count = 0;
    			foreach ($search_results['Feed']['Entry'] as $rawtweet) {
    				$idarr = explode(':', $rawtweet['id']);
    				// format to our needs
    				$i = explode(' ', $rawtweet['Author']['name']);
    				$tweet['Tweet']['id'] = $idarr[2];
    				$tweet['Tweet']['twitter_username'] = $i[0];
    				$tweet['Tweet']['tweet_content'] = $rawtweet['title'];
    				$tweet['Tweet']['created'] = date('Y-m-d H:i:s', strtotime($rawtweet['published']));
    				$tweet['Tweet']['updated'] = date('Y-m-d H:i:s', strtotime($rawtweet['updated']));
    				// and save
    
    
    				$tweet = $this->Tweet->create($tweet);
    				if (!$this->Tweet->exists()) {
    					$this->Tweet->save($tweet);
    					$count++;
    				}
    			}
    			$this->out('Found ' . $count . ' New tweets for ' . $search);
    			if ($this->QueuedTask->createJob('twitterscrape', array(
    				'search' => $search
    			), '+5 Minutes')) {
    				$this->out('Searchterm update Queued');
    			} else {
    				$this->err('Could not create Twitterscrape Job.');
    			}
    			return true;
    		} else {
    			$this->out('No Search term found, Cancelling');
    			// return true so the task does NOT get requeued.
    			return true;
    		}
    	}
    }
    ?>


Refer to the Github wiki and the example Tasks distributed with the
plugin (in plugins/queue/shells/tasks).



Limits
~~~~~~
While overall functionality is inspired by Queue Systems like Gearman,
Beanstalk, Starling etc. i decided against using an external daemon to
simplify setup and usage. This of course puts limits on overall
performance and distributivity. The main design goal was to create a
method to simply push a piece of code out of a regular web request and
execute it via shell without juggling a multitude of individual shells
and cronjobs etc.
While you can run multiple workers, and can (to some extend) spread
these workers to different machines via a shared database, you should
seriously consider using a more advanced system for high volume/high
number of worker systems.

You might want to take a look at David Persson's Beanstalk Queue
Plugin `http://github.com/davidpersson/queue`_, which will require
extra server side setup, but provide a more Thorough approach.


Thankyou for reading.

For more background information and configuration options, read the
wiki at
`http://github.com/MSeven/cakephp_queue`_

.. _http://bakery.cakephp.org/articles/view/building-your-first-twitter-mash-up: http://bakery.cakephp.org/articles/view/building-your-first-twitter-mash-up
.. _http://github.com/MSeven/cakephp_queue: http://github.com/MSeven/cakephp_queue
.. _http://github.com/davidpersson/queue: http://github.com/davidpersson/queue
.. meta::
    :title: CakePHP Simple Queue Plugin
    :description: CakePHP Article related to plugin,shell,queue,deferred,Plugins
    :keywords: plugin,shell,queue,deferred,Plugins
    :copyright: Copyright 2009 
    :category: plugins

