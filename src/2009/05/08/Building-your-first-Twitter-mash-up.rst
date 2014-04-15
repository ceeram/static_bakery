Building your first Twitter mash-up
===================================

by ics on May 08, 2009

Simple tutorial to help you build your first twitter app


Before you start
~~~~~~~~~~~~~~~~

1. have a webserver running;
2. have a SQL server running;
(for these you can use something like XAMPP)
3. have basic CakePHP knowledge. The CakePHP book covers this.


What will the application do
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It will be searching for a given keyword and store found tweets in our
database.


Getting started
~~~~~~~~~~~~~~~

1. Get the latest CakePHP and drop it in your DocumentRoot. You'll
have something like this:

::

    
    /caketweet
        /app
        /cake
        /docs
        /vendors
        .htaccess
        index.php

2. Don't forget to customise your app/config/core.php and set write
permissions to your app/tmp folder (chmod -R 777 app/tmp)

3. Get Twitter datasource and put it in your app/models/datasources
directory
`http://bakery.cakephp.org/articles/view/twitter-datasource`_

Setup
~~~~~

1. Create a database:

::

    CREATE DATABASE `caketweet`;
    USE `caketweet`;
    CREATE TABLE IF NOT EXISTS `tweets` (
      `id` int(11) NOT NULL auto_increment,
      `twitter_username` varchar(255) NOT NULL,
      `tweet_content` text NOT NULL,
      `created` datetime default NULL,
      `updated` datetime default NULL,
      PRIMARY KEY  (`id`)
    );

2. Edit your app/config/database.php
Example:

::

    
    <?php
    class DATABASE_CONFIG {
    
    	var $default = array(
    		'driver' => 'mysql',
    		'persistent' => false,
    		'host' => 'localhost',
    		'login' => 'user',
    		'password' => 'password',
    		'database' => 'caketweet',
    		'prefix' => '',
    	);
    
    	var $twitter = array(
    		'datasource' => 'twitter',
    		'username' => 'your_twitter_username',
    		'password' => 'your_twitter_password',
    	); 
    }
    ?>



MVC
~~~

1. Create a tweet model.
app/models/tweet.php:

::

    
    <?php
    class Tweet extends AppModel {
    	var $name = 'Tweet';
    }

2. Create the tweets controller.
app/controllers/tweets_controller.php

::

    
    <?php
    class TweetsController extends AppController {
    	
    	var $name = 'Tweets';
    	
    	function index(){
    		$this->set('tweets', $this->paginate());
    	}
    	function search(){
    		if(!empty($this->data['Tweet']['keyword'])){
    			$this->Twitter = ConnectionManager::getDataSource('twitter');
    			$search_results = $this->Twitter->search($this->data['Tweet']['keyword'], 'all', 5);
    			// let's loop through tweets
    			foreach($search_results['Feed']['Entry'] as $rawtweet){
    				// format to our needs
    				$i = explode(' ', $rawtweet['Author']['name']);
    				$tweet['Tweet']['twitter_username'] = $i[0];
    				$tweet['Tweet']['tweet_content'] = $rawtweet['content']['value'];
    				$tweet['Tweet']['created'] = date('Y-m-d H:i:s' , strtotime($rawtweet['published']));
    				$tweet['Tweet']['updated'] = date('Y-m-d H:i:s' ,strtotime($rawtweet['updated']));
    				// and save
                                    $this->Tweet->create();			
    				$this->Tweet->save($tweet);
    			}
    			$this->Session->setFlash(__('Got tweets.', true));
    		}    
    	}
    }
    ?>

3. Create the views.
app/views/tweets/index.ctp:

::

    
    <div class="tweets index">
    <h2><?php __('Tweets');?></h2>
    <table cellpadding="0" cellspacing="0">
    <tr>
    	<th><?php echo $paginator->sort('id');?></th>
    	<th><?php echo $paginator->sort('twitter_username');?></th>
    	<th><?php echo $paginator->sort('tweet_content');?></th>
    	<th><?php echo $paginator->sort('created');?></th>
    	<th class="actions"><?php __('Actions');?></th>
    </tr>
    <?php
    $i = 0;
    foreach ($tweets as $tweet):
    	$class = null;
    	if ($i++ % 2 == 0) {
    		$class = ' class="altrow"';
    	}
    ?>
    	<tr<?php echo $class;?>>
    		<td>
    			<?php echo $tweet['Tweet']['id']; ?>
    		</td>
    		<td>
    			<?php echo $tweet['Tweet']['twitter_username']; ?>
    		</td>
    		<td>
    			<?php echo $tweet['Tweet']['tweet_content']; ?>
    		</td>
    		<td>
    			<?php echo $tweet['Tweet']['created']; ?>
    		</td>
    		<td class="actions">
               <?php echo $html->link(__('Delete', true), array('action'=>'delete', $tweet['Tweet']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $tweet['Tweet']['id'])); ?>
    		</td>
    	</tr>
    <?php endforeach; ?>
    </table>
    </div>
    <div class="paging">
        <?php echo $paginator->prev('«' .__('prev', true), array('escape' => false), null, array('class'=>'disabled', 'escape' => false));?>
     |  <?php echo $paginator->numbers();?>
        <?php echo $paginator->next(__('next', true).' »', array('escape' => false), null, array('class'=>'disabled', 'escape' => false));?>
    </div>
    <div class="actions">
    	<ul>
    		<li><?php echo $html->link(__('Search tweets', true), array('action'=>'search')); ?></li>
    	</ul>
    </div>

app/views/tweets/search.ctp:

::

    
    <div class="tweets form">
    <?php echo $form->create('Tweet', array('action' => 'search'));?>
    	<fieldset>
     		<legend><?php __('Search tweet');?></legend>
    	<?php
    		echo $form->input('keyword');
    	?>
    	</fieldset>
    <?php echo $form->end('Search');?>
    </div>
    <div class="actions">
    	<ul>
    		<li><?php echo $html->link(__('List tweets', true), array('action'=>'index'));?></li>
    	</ul>
    </div>

Now browse to http://yourhost/caketweet/tweets/search and input your
desired keyword.

Final notes
~~~~~~~~~~~
As you probably noticed this is not even near of being a full
application but it will get you started.
To see what else you can do browse the twitter datasource to view the
available methods.

.. _http://bakery.cakephp.org/articles/view/twitter-datasource: http://bakery.cakephp.org/articles/view/twitter-datasource

.. author:: ics
.. categories:: articles, tutorials
.. tags:: twitter,mashup,Tutorials

