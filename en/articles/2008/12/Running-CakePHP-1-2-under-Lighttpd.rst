Running CakePHP 1.2 under Lighttpd
==================================

by %s on December 14, 2008

After reading this article you will hopefully be able to run cakephp
under lighttpd
Hi bakers,

after some research on how to run CakePHP without much hassle under
lighttpd I only found outdated or non-working tutorials. Together with
some help on IRC (thanks Phally) I found a solution that works for me
with the current CakePHP version (1.2.0.7692 RC3)

You won't have to change anything in your Cake setup. I've used the
following software:

+ Arch Linux
+ Lighttpd (1.4.20)
+ CakePHP 1.2.0.7692 RC3
+ My Cake-App is called "biscuit" and is placed under
  /srv/http/biscuit, where /srv/http is the document root of my
  lighttpd.

I suppose that you've got PHP running correctly and do not want to do
something special with your server. Also make sure that you've
uncommented mod_rewrite in your module config!

Edit: the lighttpd.conf (on my system in /etc/lighttpd/lighttpd.conf).
I've put the following code under the lighttpd-fastcgi-php-stuff ;)

::

    $HTTP["host"] == "localhost" {
            server.document-root = "/srv/http/"
            url.rewrite-once = (
                    "^/biscuit/$" => "/biscuit/app/webroot/",
                    "^/biscuit/(css|files|img|js|stats)/(.*)" => "/biscuit/app/webroot/$1/$2",
                    "/biscuit/(.*)$" => "/biscuit/app/webroot/index.php?url=$1"
            )
    }

You will have to modify this code, for example if you have a
`www.example.com`_ domain, replace it with "localhost". If your server
has a different document-root replace my "/srv/http" with yours.
In the rewrite-once-code you have to change "biscuit" with your sub
directory name. If you just want one cakephp projekt on a host i
"think" you can remove the biscuit directory stuff altogether, but
check the regex first.

If you have better solutions or additions I encourage you to post them
here!

kind regards,
daschl

.. _www.example.com: http://www.example.com/
.. meta::
    :title: Running CakePHP 1.2 under Lighttpd
    :description: CakePHP Article related to lighttpd,Tutorials
    :keywords: lighttpd,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

