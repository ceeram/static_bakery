ACL Management Plugin
=====================

by %s on January 25, 2008

A simple, easy, AJAXY, solution for managing your access control
lists.
Introduction
It has been done before. I believe both Mariano Iglesias
(mariano_iglesias) and Andy Dawson (ad7six) have both created ACL
plugins. But unfortunately, at the time of this writing, neither of
them worked for me. So I decided to create an AJAX ACL plugin. I
needed one for my current project, plus I wanted to contribute
something else to CakePHP outside of the crappy Oracle DBO that I
wrote a year ago; and writing a fancy new plugin is way more fun that
writing data import scripts and reports for my boss.

Before I waste too much of your time, you're welcome to check out a
demo here:
`http://dev.newnewmedia.com/cakephp/admin/acl`_
If you're still with me, then I can explain how you can get this puppy
working for yourself.

Requirements:

+ PHP 4 or 5
+ CakePHP 1.2 Beta
+ ACL Plugin - svn://newnewmedia.com/cakephp/plugins/acl
+ Prototype 1.5.1.1 -
  `http://www.prototypejs.org/assets/2007/6/20/prototype.js`_
+ Tango Icon Library - `http://tango-project.org/releases/tango-icon-
  theme-0.8.1.tar.gz`_

Instructions for Installation
Note: At the time of this writing, CakePHP has no way of bundling
images/css/js within the plugin. So if you want this thing to look
pretty, you're going to have to do a little extra work.


#. Download Prototype, drop the file directly into /app/webroot/js
#. Download Tango Icon Library, name the folder 'tango', and put it in
   /app/webroot/img
#. Checkout the ACL plugin source from svn and put the 'acl' folder in
   /app/plugins
#. edit your core.php and turn on admin routing
#. Browse to the address /admin/acl

That's it! The ACL management plugin should be installed. Usage
instructions are built into the plugin, so I do not have to go on at
length about it here.

If you have any questions feel free to leave comments here in the
Bakery.

.. _http://www.prototypejs.org/assets/2007/6/20/prototype.js: http://www.prototypejs.org/assets/2007/6/20/prototype.js
.. _http://dev.newnewmedia.com/cakephp/admin/acl: http://dev.newnewmedia.com/cakephp/admin/acl
.. _http://tango-project.org/releases/tango-icon-theme-0.8.1.tar.gz: http://tango-project.org/releases/tango-icon-theme-0.8.1.tar.gz
.. meta::
    :title: ACL Management Plugin
    :description: CakePHP Article related to acl,plugin,Plugins
    :keywords: acl,plugin,Plugins
    :copyright: Copyright 2008 
    :category: plugins

