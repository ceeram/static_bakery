HTML Cache Helper
=================

by mattc on March 20, 2009

Cake's core cache helper is great, but the files it outputs are PHP
files, so it will never be as fast as straight HTML files. This HTML
Cache Helper writes out pure HTML, meaning the web server doesnâ€™t
have to touch PHP when a request is made.
This helper is for sites with high traffic pages that have nothing
unique about the user on the page. I use this helper on
`http://www.rsstalker.com`_. It handles the custom RSS feeds
(currently around 13k), which is perfect since there is nothing user
specific in the XML. Each feed gets hit multiple times a day, by
multiple aggregators. This really adds up to a ton of requests.


Limitations
~~~~~~~~~~~

+ Nothing specific to a user on the page. No "Welcome, Matt" or
  shopping carts.
+ Cache will never expire. See below for workaround.


Download the helper at
`http://github.com/mcurry/cakephp/tree/master/helpers/html_cache`_ or
copy and paste from below to /app/views/helpers/html_cache.php.

Helper Class:
`````````````

::

    <?php 
    /*
     * HTML Cache CakePHP Helper
     * Copyright (c) 2008 Matt Curry
     * www.PseudoCoder.com
     * http://github.com/mcurry/cakephp/tree/master/helpers/html_cache
     * http://www.pseudocoder.com/archives/2008/09/03/cakephp-html-cache-helpercakephp-html-cache-helper/
     *
     * @author      Matt Curry <matt@pseudocoder.com>
     * @license     MIT
     *
     */
    
    class HtmlCacheHelper extends Helper {
      function afterLayout() {
        $view =& ClassRegistry::getObject('view');
        $path = WWW_ROOT . implode(DS, array_filter(explode('/', $this->here)));
    
        $file = new File($path, true);
        $file->write($view->output);
      }
    }
    ?>



Expiring The Cache
~~~~~~~~~~~~~~~~~~
To expire the cache I use a cron job which deletes old files from the
directory.

::

    
    find /full/path/to/app/webroot/controller/ -mmin +360 | xargs rm -f



Notes
~~~~~
The cached files are getting written right to your webroot. The
default Cake .htaccess checks to see if a file actually exists, this
is what allows images, js, css, and other files to be handled directly
by the web server.

This won't work with the root file of your controller. So for example
`www.rsstalker.com/feeds`_ wonâ€™t work, but
`www.rsstalker.com/feeds/amazon`_ does.

.. _www.rsstalker.com/feeds/amazon: http://www.rsstalker.com/feeds/amazon
.. _http://www.rsstalker.com: http://www.rsstalker.com/
.. _http://github.com/mcurry/cakephp/tree/master/helpers/html_cache: http://github.com/mcurry/cakephp/tree/master/helpers/html_cache
.. _www.rsstalker.com/feeds: http://www.rsstalker.com/feeds
.. meta::
    :title: HTML Cache Helper
    :description: CakePHP Article related to cache,Helpers
    :keywords: cache,Helpers
    :copyright: Copyright 2009 mattc
    :category: helpers

