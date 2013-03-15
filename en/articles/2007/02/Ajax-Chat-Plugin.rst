Ajax Chat Plugin
================

by %s on February 03, 2007

A basic Ajax chat plugin.
A .zip of the plugin is available at
`http://sandbox.siteamonth.com/demo/chat`_
1. Download Prototype (`http://www.prototypejs.org/`_) and put it in
/app/webroot/js/

2. Download and unzip the plugin to app/plugins. The plugin is called
"chat", so make sure there is no conflict with any other
controllers/plugins

3. Run this sql to create the chats table.

::

    CREATE TABLE  `chats` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `key` varchar(45) NOT NULL default '',
            `handle` varchar(20) NOT NULL default '',
            `text` text NOT NULL,
            `ip_address` varchar(12) NOT NULL default '',
            `created` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (`id`),
            KEY `KEY_IDX` (`key`)
          );

4. Include the helper in your controller

::

    var $helpers = array('Ajax', 'chat/AjaxChat');

5. Include Prototype in your view if you don't already include it in
your layout.

::

    echo $javascript->link('prototype');

6. Then just add the chat to your view. You can have multiple chats on
your site by changing the chat key - "chat1" in this example.

::

    echo $ajaxChat->generate('chat1');



.. _http://www.prototypejs.org/: http://www.prototypejs.org/
.. _http://sandbox.siteamonth.com/demo/chat: http://sandbox.siteamonth.com/demo/chat
.. meta::
    :title: Ajax Chat Plugin
    :description: CakePHP Article related to chat,plugin,Plugins
    :keywords: chat,plugin,Plugins
    :copyright: Copyright 2007 
    :category: plugins

