Clean urls with isapi rewrite on iis
====================================

Forced to deal with a windows based server, without apache and
effectively also mod_rewrite? But, after much complaining and begging
your sysadmin has been kind enough to install isapi_rewrite on that
(already bloated) iis box... then this is for you!
This article (can this be called an article) is about clean and pretty
uri's for cakephp, or anything else with an url dispatcher thingy.

Why, because im not a iis guru (I usually stay as much away from iis
as humanly possible), and because of this I had some trouble giving my
cake based webapps a nice and elegant url scheme. Allthough there
where some skinny isapi_rewrite examples floating around here
somewhere, they provided inadequate and not very friendly. The most
usefull snippet i found, was directing any requests with a trailing
slash to the index.php in the webroot, and the rest was treated as a
normal ugly url to make sure your css, img and js dirs where still
possible to get to.

So i figured, i do know mod_rewrite pretty ok, and because regexp can
get a bit intimidating i decided to publish what i think is a nice way
to do things, so here is the httpd.ini / htaccess.ini rewrite rules
i've been using for a while now:

::

    
    [ISAPI_Rewrite]
    
    RewriteCond URL (?!/js/|/img/|/files/|/css/).*
    RewriteRule (.*?\.php)(\?[^/]*)?/([^/]*)/(.*) $1(?2$2&:\?url=/$3/$4)
    RewriteCond URL (?!/js/|/img/|/files/|/css/).*
    RewriteRule ^/(.*) /index.php?url=/$1 [L]
    RewriteCond URL (?!/js/|/img/|/files/|/css/).*
    RewriteRule /(.*) /$1

Ofcourse, all files in the webroot are on the same level as the
webservers document root. The actual libs and code are outside of the
servers docroot, as it should be.


.. author:: Yuka
.. categories:: articles, snippets
.. tags:: installation,behaviour,rewrties routes url,Snippets

