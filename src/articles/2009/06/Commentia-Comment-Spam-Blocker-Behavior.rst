Commentia - Comment Spam Blocker Behavior
=========================================

by milesj on June 04, 2009

Commentia is a CakePHP Behavior that automatically runs after a
comment is made. Each comment is tested upon a point system to
determine and classify it. If a comment has more then 1 point it is
automatically approved, if it has 0 points it continues pending, and
if it is in the negative point range it is either marked as spam or
deleted entirely. The Behavior is extremely easy to install and
requires no moderation from you (maybe a little!).
`Download the code and view original documentation`_. The point system
is based on an idea by Jonathon Snook and his article "`How I built an
effective blog comment spam blocker`_". I merely took his points and
outlines and built the behavior from the ground up.


Code
~~~~
Its quite long...


Behavior Class:
```````````````

::

    <?php 
    /** 
     * commentia.php
     *
     * A CakePHP Behavior that moderates / validates comments to check for spam.
     * Validates based on a point system. High points is an automatic approval, where as low points is marked as spam or deleted.
     * Based on Jonathon Snooks outline.
     *
     * Copyright 2006-2009, Miles Johnson - www.milesj.me
     * Licensed under The MIT License - Modification, Redistribution allowed but must retain the above copyright notice
     * @link 		http://www.opensource.org/licenses/mit-license.php
     *
     * @package		Commentia Behavior - Comment Spam Blocker
     * @created		February 8th 2008
     * @version 	1.3
     * @link		www.milesj.me/resources/script/commentia-behavior
     * @link		www.snook.ca/archives/other/effective_blog_comment_spam_blocker/
     * @changelog	www.milesj.me/files/logs/commentia-behavior
     */
     
    class CommentiaBehavior extends ModelBehavior {
    
    	/**
    	 * Current version: www.milesj.me/files/logs/commentia-behavior
    	 * @var string
    	 */ 
    	var $version = '1.3';
    	
    	/**
    	 * Settings
    	 * - Column name for the authors name
    	 * - Column name for the comments body
    	 * - Column name for the authors email
    	 * - Column name for the authors website 
    	 * - Column name of the foreign id that links to the article/entry/etc
    	 * - Model name of the parent article/entry/etc
    	 * - Link to the parent article, use :id for the permalink id
    	 * - Email address where the notify emails should go
    	 * - Should the points be saved to the database?
    	 * - Should you receive a notification email for each comment? 
    	 * - How many points till the comment is deleted (negative)
    	 * @var array 
    	 */  
    	var $settings = array( 
    		'column_author'		=> 'name',
    		'column_content'	=> 'content',
    		'column_email'		=> 'email',
    		'column_website'	=> 'website',
    		'column_foreign_id'	=> 'entry_id',
    		'parent_model'		=> 'Entry',
    		'article_link'		=> '',
    		'notify_email'		=> '',
    		'save_points'		=> true,
    		'send_email'		=> true,
    		'blacklist_keys'	=> '',
    		'blacklist_words'	=> '',
    		'deletion'			=> -10
    	);
    	
    	/**
    	 * Disallowed words within the comment body
    	 * @var array
    	 */
    	var $blacklistKeywords = array('levitra', 'viagra', 'casino', 'sex', 'loan', 'finance', 'slots', 'debt', 'free');
    	
    	/**
    	 * Disallowed words/chars within the url links
    	 * @var array
    	 */
    	var $blacklistWords = array('.html', '.info', '?', '&', '.de', '.pl', '.cn');
    	
    	/**
    	 * Startup hook from the model
    	 * @param object $Model
    	 * @param array $settings
    	 * @return void
    	 */
    	function setup(&$Model, $settings = array()) {
    		if (!empty($settings) && is_array($settings)) {
    			$this->settings = array_merge($this->settings, $settings);
    		}
    		
    		if (!empty($this->settings['blacklist_keys']) && is_array($this->settings['blacklist_keys'])) {
    			$this->blacklistKeywords = array_merge($this->blacklistKeywords, $this->settings['blacklist_keys']);
    		}
    		
    		if (!empty($this->settings['blacklist_words']) && is_array($this->settings['blacklist_words'])) {
    			$this->blacklistWords = array_merge($this->blacklistWords, $this->settings['blacklist_words']);
    		}
    	}
    
    	/**
    	 * Runs before a save and marks the content as spam or regular comment
    	 * @param object $Model
    	 * @param boolean $created
    	 * @return mixed
    	 */
    	function afterSave(&$Model, $created) {
    		if ($created) {
    			$data = $Model->data[$Model->name];
    			$points =  0;
    			
    			if (!empty($data)) {
    				// Get links in the content
    				$links = preg_match_all("#(^|[\n ])(?:(?:http|ftp|irc)s?:\/\/|www.)(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,4}(?:[-a-zA-Z0-9._\/&=+%?;\#]+)#is", $data[$this->settings['column_content']], $matches);
    				$links = $matches[0];
    				
    				$totalLinks = count($links);
    				$length = strlen($data[$this->settings['column_content']]);
    		
    				// How many links are in the body
    				// +2 if less than 2, -1 per link if over 2
    				if ($totalLinks > 2) {
    					$points = $points - $totalLinks;
    				} else {
    					$points = $points + 2;
    				}
    				
    				// How long is the body
    				// +2 if more then 20 chars and no links, -1 if less then 20
    				if ($length >= 20 && $totalLinks <= 0) {
    					$points = $points + 2;
    				} else if ($length >= 20 && $totalLinks == 1) {
    					++$points;
    				} else if ($length < 20) {
    					--$points;
    				}
    				
    				// Number of previous comments from email
    				// +1 per approved, -1 per spam
    				$comments = $Model->find('all', array(
    					'fields' => array($Model->alias .'.id', $Model->alias .'.status'),
    					'conditions' => array($Model->alias .'.'. $this->settings['column_email'] => $data[$this->settings['column_email']]),
    					'recursive' => -1,
    					'contain' => false
    				));
    				
    				if (!empty($comments)) {
    					foreach ($comments as $comment) {
    						if ($comment[$Model->alias]['status'] == 'spam') {
    							--$points;
    						}
    						
    						if ($comment[$Model->alias]['status'] == 'approved') {
    							++$points;
    						}
    					}
    				}
    				
    				// Keyword search
    				// -1 per blacklisted keyword
    				foreach ($this->blacklistKeywords as $keyword) {
    					if (stripos($data[$this->settings['column_content']], $keyword) !== false) {
    						--$points;
    					}
    				}
    				
    				// URLs that have certain words or characters in them
    				// -1 per blacklisted word
    				// URL length
    				// -1 if more then 30 chars
    				foreach ($links as $link) {
    					foreach ($this->blacklistWords as $word) {
    						if (stripos($link, $word) !== false) {
    							--$points;
    						}
    					}
    					
    					foreach ($this->blacklistKeywords as $keyword) {
    						if (stripos($link, $keyword) !== false) {
    							--$points;
    						}
    					}
    					
    					if (strlen($link) >= 30) {
    						--$points;
    					}
    				}	
    				
    				// Body starts with...
    				// -10 points
    				$firstWord = substr($data[$this->settings['column_content']], 0, stripos($data[$this->settings['column_content']], ' '));
    				$firstDisallow = array_merge($this->blacklistKeywords, array('interesting', 'cool', 'sorry'));
    				
    				if (in_array(strtolower($firstWord), $firstDisallow)) {
    					$points = $points - 10;
    				} 
    				
    				// Author name has http:// in it
    				// -2 points
    				if (stripos($data[$this->settings['column_author']], 'http://') !== false) {
    					$points = $points - 2;
    				}
    				
    				// Body used in previous comment
    				// -1 per exact comment
    				$previousComments = $Model->find('count', array(
    					'conditions' => array($Model->alias .'.'. $this->settings['column_content'] => $data[$this->settings['column_content']]),
    					'recursive' => -1,
    					'contain' => false
    				));
    				
    				if ($previousComments > 0) {
    					$points = $points - $previousComments;
    				}
    				
    				// Random character match
    				// -1 point per 5 consecutive consonants
    				$consonants = preg_match_all('/[^aAeEiIoOuU\s]{5,}+/i', $data[$this->settings['column_content']], $matches);
    				$totalConsonants = count($matches[0]);
    				
    				if ($totalConsonants > 0) {
    					$points = $points - $totalConsonants;
    				}
    				
    				// Finalize and save
    				if ($points >= 1) {
    					$status = 'approved';
    				} else if ($points == 0) {
    					$status = 'pending';
    				} else if ($points <= $this->settings['deletion']) {
    					$status = 'delete';
    				} else {
    					$status = 'spam';
    				}
    				
    				if ($status == 'delete') {
    					$Model->delete($Model->id, false);
    				} else {
    					$update = array();
    					$update['status'] = $status;
    					$update['points'] = $points;
    					
    					$save = array('status');
    					if ($this->settings['save_points'] === true) {
    						$save[] = 'points';
    					}
    					
    					$Model->id = $Model->id;
    					$Model->save($update, false, $save);
    					
    					if ($this->settings['send_email'] === true) {
    						$this->notify($data, $update);
    					}
    				}		
    			}
    			
    			return $points;
    		}
    	}
    	
    	/**
    	 * Sends out an email notifying you of a new comment
    	 * @param array $data
    	 * @param array $stats
    	 * @return void
    	 */
    	function notify($data, $stats) {
    		if (!empty($this->settings['parent_model']) && !empty($this->settings['article_link']) && !empty($this->settings['notify_email'])) {
    			App::import('Component', 'Email');
    			$Email = new EmailComponent();
    			$Entry = ucfirst(strtolower($this->settings['parent_model']));
    			
    			// Get parent entry/blog
    			$entry = ClassRegistry::init($Entry)->find('first', array(
    				'fields' => array($Entry .'.id', $Entry .'.title'),
    				'conditions' => array($Entry .'.id' => $data[$this->settings['column_foreign_id']])
    			));
    			
    			// Config
    			$entryLink 	= str_replace(':id', $entry[$Entry]['id'], $this->settings['article_link']);
    			$entryTitle = $entry[$Entry]['title'];
    			
    			// Build message
    			$message  = "A new comment has been posted for: ". $entryLink ."\n\n";
    			$message .= 'Name: '. $data[$this->settings['column_author']] .' <'. $data[$this->settings['column_email']] .">\n";
    			$message .= 'Status: '. ucfirst($stats['status']) .' ('. $stats['points'] ." Points)\n";
    			$message .= "Message:\n\n". $data[$this->settings['column_content']];
    			
    			// Send email
    			$Email->to = $this->settings['notify_email'];
    			$Email->from = $data[$this->settings['column_author']] .' <'. $data[$this->settings['column_email']] .'>';
    			$Email->subject = 'Comment Approval: '. $entryTitle;
    			$Email->send($message); 
    		}
    	}
    	
    }?>



Installation
~~~~~~~~~~~~

First off, you need to download the script and then place
commentia.php in your app/models/behaviors/ folder of your cake
installation. Next I am assuming your adding this spam blocker to a
comments table with the model Comment (if not you will need to
manually edit the behavior to work). To enable the behavior, add it to
your $actsAs variable on the Comment Model.


Model Class:
````````````

::

    <?php class Comment extends AppModel {
    	var $actsAs = array('Commentia');
    }?>

Below is the comments table structure that Commentia is based around.
Again, if your table does not look like this, there is some
configuration you can do to get it working (which you can check in the
next step). The points column isn't necessary, it is only there for
reference and fun. If you do not want the points column, you can
disable it from updating in the database.

::

    CREATE TABLE IF NOT EXISTS `comments` (
      `id` int(11) NOT NULL auto_increment,
      `entry_id` int(11) NOT NULL,
      `name` varchar(50) NOT NULL,
      `email` varchar(75) NOT NULL,
      `website` varchar(100) NOT NULL,
      `status` enum('approved','pending','spam') NOT NULL default 'pending',
      `content` text NOT NULL,
      `points` int(11) NOT NULL,
      `created` datetime default NULL,
      `modified` datetime default NULL,
      UNIQUE KEY `id` (`id`),
      KEY `entry_id` (`entry_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Blog comments';



Configuration
~~~~~~~~~~~~~

When you install the behavior by attaching it to $actsAs, you can
supply an array of settings. These settings will be loaded
automatically when the behavior is called, so no need for manually
editing the core file. The following variables are use able in the
settings array:

column_author , column_email , column_website , column_content would
be the names of the columns in your comments table. These are here for
your use, if your column names do not match the table scheme above.

column_foreign_id works like the other columns listed above, but this
setting has a more important role. This id is used to determine which
table the comment is related to. By default, the comments table
(Comment model) belongs to the entries table (Entry model).

parent_model is the name of the model that comments belong to. By
default its Entry, but yours might be Article, News, Blog, etc.

article_link would be the full url address for the article the
comments belong to. For example, my url would be
www.milesj.me/blog/read/:id. The string :id in your url will be
replaced with the dynamic id for the corresponding article.

notify_email is the destination email, for the notification email when
a comment is made.

save_points and send_email are self explanatory; they either take
boolean true or false. Save points saves the score for each comment to
the database. Send email turns on/off the notification email.

deletion is the points number in which to delete a comment at
(deletion number should be negative). So if you want your spam to be
deleted when its points reach -5, just set deletion to -5.

If you are getting a lot of spam, and Commentias default blacklisted
words aren't working, you can add your own to the blacklist_keys and
blacklist_words settings. These must be an array of words to work
correctly.

::

    var $actsAs = array(
        'Commentia' => array(
            'article_link'	=> 'http://www.milesj.me/blog/read/:id/',
            'notify_email'	=> 'testemail@milesj.me'
        )
    );
    
    var $actsAs = array(
        'Commentia' => array(
            'send_email'		=> false,
            'column_author'		=> 'author',
            'column_foreign_id'	=> 'article_id',
            'parent_model'		=> 'Article',
            'blacklist_keys'	=> array('sex', 'drugs')
        )
    );



.. _How I built an effective blog comment spam blocker: http://snook.ca/archives/other/effective_blog_comment_spam_blocker/
.. _Download the code and view original documentation: http://www.milesj.me/resources/script/commentia-behavior
.. meta::
    :title: Commentia - Comment Spam Blocker Behavior
    :description: CakePHP Article related to behavior,spam,commentia,comment,miles,antispam,milesj,blocker,johnson,Behaviors
    :keywords: behavior,spam,commentia,comment,miles,antispam,milesj,blocker,johnson,Behaviors
    :copyright: Copyright 2009 milesj
    :category: behaviors

