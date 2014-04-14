LightTPD and CakePHP setup in subdirectories
============================================

by baverman on November 23, 2006

I faced the challenge to install CakePHP under LightTPD. All works
smoothly as long as I deploy projects in document root of domain. But
when I want to setup CakePHP in subdir all goes wrong.
The source of the problem is env('PHP_SELF'). In the default
LightTPD/PHP instalation it is not working. You should tweak some
settings.


#. Add cgi.fix_pathinfo = 1 in your php.ini
#. Add "broken-scriptfilename" => "enable" into lighttpd.conf at
   fastcgi.server section.
#. Add '/subdir' into rewrite rules to emulate apache's RewriteBase
#. In webroot/index.php define('WEBROOT_DIR',
   basename(dirname(dirname(__FILE__))));

Here is piece of my lighttpd.conf:

::

    fastcgi.server = ( ".php" =>
      ((
        "socket" => "/usr/local/lighttpd/fcgi/wortex/socket",
        "check-local" => "disable",
        "broken-scriptfilename" => "enable" 
      ))
    )
    
    $HTTP["host"] == "example.com" {
      server.document-root = "/var/www/example.com/"
      url.rewrite-once = (
        "/project1/(css|files|img|js|stats)/(.*)" => "/project1/webroot/$1/$2",
        "^/project1/([^.]+)$" => "/project1/index.php?url=$1",
    
        "/project2/(css|files|img|js|stats)/(.*)" => "/project2/webroot/$1/$2",
        "^/project2/([^.]+)$" => "/project2/index.php?url=$1"
      )
    }

'project1' and 'project2' are dirs made by bake.php. Url in browser
looks like `http://example.com/project1/controller/action`_

.. _http://example.com/project1/controller/action: http://example.com/project1/controller/action
.. meta::
    :title: LightTPD and CakePHP setup in subdirectories
    :description: CakePHP Article related to lighttpd,subdirectories,Tutorials
    :keywords: lighttpd,subdirectories,Tutorials
    :copyright: Copyright 2006 baverman
    :category: tutorials

