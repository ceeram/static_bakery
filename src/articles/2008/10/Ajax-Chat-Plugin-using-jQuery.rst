Ajax Chat Plugin (using jQuery)
===============================

by %s on October 26, 2008

This is an update to my old Ajax Chat Plugin
([url]http://bakery.cakephp.org/articles/view/ajax-chat-plugin[/url]).
This version using jQuery and works with CakePHP RC3
Full source is available at GitHub:
`http://github.com/mcurry/cakephp/tree/master/plugins/chat`_
A demo is running at `http://sandbox2.pseudocoder.com/demo/chat`_

1) Download jQuery and put it in /app/webroot/js/

2) Put the chat plugin into app/plugins/chat. The plugin is called
"chat", so make sure there is no conflict with any other controllers
or plugins.

3) Run this sql to create the chats table.

::

    CREATE TABLE  `chats` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `key` varchar(45) NOT NULL default '',
      `name` varchar(20) NOT NULL default '',
      `message` text NOT NULL,
      `ip_address` varchar(15) NOT NULL default '',
      `created` datetime default NULL,
      PRIMARY KEY  (`id`),
      KEY `KEY_IDX` (`key`)
    );

4) Include the plugin helper in your controller:

Controller Class:
`````````````````

::

    <?php 
    var $helpers = array('chat.ajaxChat');
    ?>


Or just in a particular action:

Controller Class:
`````````````````

::

    <?php 
    $this->helpers[] = 'chat.ajaxChat';
    ?>


5) Include jQuery in your view if you don't already include it in your
layout.

View Template:
``````````````

::

    
    echo $javascript->link('jquery', false);

6) Include the chat js and css in your view.

View Template:
``````````````

::

    
    $javascript->link(array('jquery/jquery', '/chat/js/chat.js'), false);
    $html->css('/chat/css/chat.css', null, null, false);

7) Then just add the chat to your view. You can have multiple chats on
your site by changing the chat key - "chat1" in this example.

View Template:
``````````````

::

    
    echo $ajaxChat->generate('chat1');



.. _http://sandbox2.pseudocoder.com/demo/chat: http://sandbox2.pseudocoder.com/demo/chat
.. _http://github.com/mcurry/cakephp/tree/master/plugins/chat: http://github.com/mcurry/cakephp/tree/master/plugins/chat
.. meta::
    :title: Ajax Chat Plugin (using jQuery)
    :description: CakePHP Article related to ,Plugins
    :keywords: ,Plugins
    :copyright: Copyright 2008 
    :category: plugins

