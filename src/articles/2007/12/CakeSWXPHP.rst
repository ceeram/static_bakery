CakeSWXPHP
==========

by wouter on December 18, 2007

SWX is the native data format for Flash. It uses SWF files to store
and exchange data. It is also an implementation of an RPC gateway
(currently in PHP), tools (a data analyzer/debugger and a service
explorer), various APIs (Flickr, Twitter, etc.), and an ActionScript
library that make creating data driven Flash and Flash Lite
applications a piece of cake.
I based this code on the cakeamfphp project. The SWX project by Aral
Balkan also contains amfphp 1.9. Looking at the code from cakeamfphp,
I was able to get SWX running and call the cake controllers as
remoting services.

Download the latest version at: `http://blog.aboutme.be/cakeswxphp`_
Just copy/past in your cake directory! Gateway locations are:

swx.php (SWX)
amf.php (AMFPHP)
json.php (JSON)

You can test your methods, using the SWX service explorer. Point your
browser to the "explorer/" directory, and you will see your cake
controllers + methods as remoting services!

Currently it uses an adjusted version of SWX, based on SWX 1.0. I will
try to update it to future versions of SWX when I get the time...

More info about SWX at:

`http://www.swxformat.org/`_`http://www.aralbalkan.com/`_ (Aral
Balkan, SWX author)

Enjoy!

.. _http://www.aralbalkan.com/: http://www.aralbalkan.com/
.. _http://blog.aboutme.be/cakeswxphp: http://blog.aboutme.be/cakeswxphp
.. _http://www.swxformat.org/: http://www.swxformat.org/
.. meta::
    :title: CakeSWXPHP
    :description: CakePHP Article related to flash,cakeswxphp,remoting,json,swx,cakeamfphp,amfphp,Plugins
    :keywords: flash,cakeswxphp,remoting,json,swx,cakeamfphp,amfphp,Plugins
    :copyright: Copyright 2007 wouter
    :category: plugins

