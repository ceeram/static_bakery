Flex remoting with cakePHP - CpAmf plugin
=========================================

by vernerd on June 28, 2009

The goal of this project was to use cakePHP controllers, as flash
remoting services.The basic idea was to use AMFPHP 1.9, because we
used it before, and it was a good choice in earlier projects.
As nobody wants to "reinvent the wheel", I googled for a solution to
integrate the AMFPHP into the cakePHP framework.

After a short research I found the following solutions:

1. cakeAMFPHP plugin
2. cakeAMF plugin

I tried both solutions, but cakeAMFPHP does not use the latest AMFPHP,
and with cakeAMF there was a problem using flex RemoteObjects.

The idea for my implementation came from cakeAMFPHP.
Requirements

1. CakePHP 1.2
2. AMFPHP 1.9
3. AMF extension (optional)


Update
------
CpAmf v0.11 is released. There are some minor enhancements, and bug
fixes in this release:

1. Service borwser displays methods beginnig with specified prefix
2. Debug mode always 0
3. CpAmf works with CakePHP Auth component

Check out the new version here:
`http://carrotplant.com/en/blog/cpamf-v011-released`_

Introduction
------------
This plugin is based on the latest AMFPHP package, and works with
cakePHP 1.2. The amf plugin uses all features of the AMFPHP package:
works with or without the amf php extension (if amfext is installed
and enabled, it will be automatically use by this plugin).

CpAmf plugin allows you to use cakePHP controllers as "services",
using all cakePHP controller features (models, behaviors etc.), also
works with flex RemotingObject, and can be used with MATE framework
(flex).


Installation
------------
Just copy the plugin into your cake application's "plugins" directory.
To check the gateway installation, simply open the following url:
yourdomain.com/cpamf/gateway.

You should see a message like this:
amfphp and this gateway are installed correctly.
You may now connect to this gateway from Flash.
If you have the amf extension you shuld see this in message:
AMF C Extension is loaded and enabled.


Value Object mapping
--------------------
Amfphp has a useful feature: the VO mapping. Cpamf plugin uses this
feature in a bit specialized way. We create a model in cake php, and a
Class in flex which corresponds to our model.
The metedata tag to achieve this mapping is:
[RemoteClass(alias="User")]
In our model we create an afterFilter method, and use the cakePHP
built in Set::Map() function to convert the associative array to an
object (or array of objects). We use generic class here (php's dummy
class 'stdClass'), set the _explicitType property of all objects, and
unset the _name_ property (which is set by Set::Map() method), because
we don't need this property in our flex class. This approach allows us
to change the model, and the corresponding flex class without the need
for changing the vo classes (we don't even need to create them).

When we get data (object) from flex we don't use mapping, on php side
we use associative arrays. The amfphp vo directory is set to default
value (vendors/amfphp/services/vo). You can change this value if you
want to use objects on php side (vendors/amfphp/globals.php).

We use one special vo class: ArrayCollection.php, it allows us to map
an array of objects (php side) to an ArrayCollection of objects (flex
side). Thanks to Wade Arnold for this solution.


Using the service browser
-------------------------
AMFPHP comes with a handy utility called service browser, which is
useful for testing the services (or in this case controllers).
Although you can test your controllers with cakePHP itself, but if you
want to test them using flash remoting you can use the service
browser.
The browser is accessible at the following url:
yourdomain.com/cpamf/browser.

NOTE: The service browser is working only when the debugging is
enabled in cakePHP.


Download
--------
`http://carrotplant.com/public/files/cpamf/cpamf.rar`_

Examples
--------
Flex: `http://carrotplant.com/en/blog/cpamf-flex-example`_ Php:
`http://carrotplant.com/en/blog/cpamf-php-example`_

.. _http://carrotplant.com/en/blog/cpamf-php-example: http://carrotplant.com/en/blog/cpamf-php-example
.. _http://carrotplant.com/public/files/cpamf/cpamf.rar: http://carrotplant.com/public/files/cpamf/cpamf.rar
.. _http://carrotplant.com/en/blog/cpamf-flex-example: http://carrotplant.com/en/blog/cpamf-flex-example
.. _http://carrotplant.com/en/blog/cpamf-v011-released: http://carrotplant.com/en/blog/cpamf-v011-released
.. meta::
    :title: Flex remoting with cakePHP - CpAmf plugin 
    :description: CakePHP Article related to Flex,CakePHP,remoting,amf,amfphp,cpamf,Plugins
    :keywords: Flex,CakePHP,remoting,amf,amfphp,cpamf,Plugins
    :copyright: Copyright 2009 vernerd
    :category: plugins

