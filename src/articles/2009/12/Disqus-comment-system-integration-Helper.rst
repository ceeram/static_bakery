Disqus comment system integration Helper
========================================

by Toss on December 09, 2009

Integrating a disqus comment system (http://disqus.com/about/) with
cakephp is really simple. To make it simpler, here is an Helper that
should do almost all the dirty work for you.
First of all, you need a disqus account. You can get one here:
`http://disqus.com/comments/register/`_
Then, grab this code and put it in ./app/views/helpers/disqus.php


Helper Class:
`````````````

::

    <?php 
    // ./app/views/helpers/disqus.php
        /**
         * Disqus comment system integration Helper
         *
         * @author ToX - http://emanuele.itoscano.com - toss82 - at - gmail.com
    	 *
    	 *
    	 *
         * @help:
    	 *		In function makeRepliable, I set the projectName somewhere in my configuration files, 
    	 *		remember to change this variable to something that suits your needs. 
    	 *		 
         */
    
    class DisqusHelper extends Helper {
    	var $helpers = array('Html', 'Javascript');
    
    	function repliesLinkCounter() {
    		$projectName = Configure::read('Disqus.projectName');
    		$createJS = "
    			(function() {
    				var links = document.getElementsByTagName('a');
    				var query = '?';
    				for(var i = 0; i < links.length; i++) {
    				if(links[i].href.indexOf('#disqus_thread') >= 0) {
    					query += 'url' + i + '=' + encodeURIComponent(links[i].href) + '&';
    				}
    				}
    				document.write('<script charset=\"utf-8\" type=\"text/javascript\" src=\"http://disqus.com/forums/{$projectName}/get_num_replies.js' + query + '\"></' + 'script>');
    			})();
    		";
    		$return = $this->Javascript->codeBlock($createJS);
    		return $return;
    	}
    
    	function makeRepliable() {
    		$projectName = Configure::read('Disqus.projectName');
    		$return = '<div id="disqus_thread"></div>';
    		$return .= $this->Javascript->link("http://disqus.com/forums/{$projectName}/embed.js");
    		return $return;
    	}
    
    	function recentComments($num_items = 5, $hide_avatars = 0, $avatar_size = 32, $excerpt_lenght = 200) {
    		$projectName = Configure::read('Disqus.projectName');
    		$return =  "<div id='recentcomments' class='dsq-widget'>";
    			$return .=  "<h2 class='dsq-widget-title'>". __('Recent Comments', true) ."</h2>";
    			$return .= $this->Javascript->link("http://disqus.com/forums/{$projectName}/recent_comments_widget.js?num_items={$num_items}&hide_avatars={$hide_avatars}&avatar_size={$avatar_size}&excerpt_length={$excerpt_lenght}");
    		$return .=  "</div>";
    		//$return .=  "<a href='http://disqus.com/'>Powered by Disqus</a>";
    		return $return;
    	}
    }
    ?>

As the header says, "In function makeRepliable, I set the projectName
somewhere in my configuration files, remember to change this variable
to something that suits your needs.". You really need to do it :)

Add the helper in app_controller, or wherever you need it...


and we are almost done: just place this where you need to show the
comments thread and form

::

    
    echo $disqus->makeRepliable();


--- optional ---
use this to show the last comments in your sidebar, or wherever you
like it to be

::

    
    echo $disqus->recentComments($num_items = 5, $hide_avatars = 0, $avatar_size = 32, $excerpt_lenght = 200);


this goes after every permalink pointing to a page where you have a
disqus thread

::

    
    echo $html->link("View comments", $yourPermaLink . "#disqus_thread");

and last of all, this one makes all of your previous links a comment
counter (eg. 0 comments, 3 comments)

::

    
    echo $disqus->repliesLinkCounter();



.. _http://disqus.com/comments/register/: http://disqus.com/comments/register/
.. meta::
    :title: Disqus comment system integration Helper
    :description: CakePHP Article related to comments,disqus,Helpers
    :keywords: comments,disqus,Helpers
    :copyright: Copyright 2009 Toss
    :category: helpers

