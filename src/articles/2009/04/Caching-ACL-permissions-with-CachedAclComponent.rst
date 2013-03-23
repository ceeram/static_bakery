Caching ACL permissions with CachedAclComponent
===============================================

by %s on April 15, 2009

When you set up ACL with a bunch of groups with subgroups, you will
end up with five or maybe more queries per request. When you have a
lot of active users it might fry your database. This is a simple
solution to get rid of all those queries.


How it works
~~~~~~~~~~~~
This component checks if the permission is stored in the cache. If
this is not the case, Cake's AclComponent is called to get the
permission. The result is caught and placed in the cache for next
time. So when the user lands on the page and the permission is cached,
no queries are issued. The permission is retrieved from the cache.

When someone changes permissions on a group using allow() or deny(),
the cache for that ACO (controller/action combination) is cleared.
Beware though, if you have multiple servers and no central cache, this
won't work as expected, because the cache can't be cleared on all
servers. Instead the cache is only cleared on a single server, the
other servers depend on the cache duration.


Requirements
~~~~~~~~~~~~
The namespace supporting cache engine, found here:
`https://github.com/Phally/cache_engines`_ and a proper and working
ACL setup.


Installation
~~~~~~~~~~~~
First, clone the plugin. Next, place the following line(s) in your
bootstrap.php at APP/config/bootstrap.php:

::

    
    <?php
    App::import('Vendor', 'CacheEngines.NamespaceFile');
    Cache::config('acl', array('engine' => 'NamespaceFile', 'duration'=> '+1 month', 'prefix' => 'acl.'));
    ?>

Now it is time to see some magic happen. Change (in your
AppController) this:

Controller Class:
`````````````````

::

    <?php 
    var $components = array('Auth', 'Acl');
    ?>

To:

Controller Class:
`````````````````

::

    <?php 
    var $components = array('Auth', 'CachedAcl');
    ?>

The permissions are now being cached.

Phally
`http://www.frankdegraaf.net`_

Note: I moved the source code to my GitHub page:
`https://github.com/Phally/cached_acl`_
So if you have any enhancements/fixes, don't hesitate to fork and send
me a pull request.

Thanks to jperras, I was able to do some benchmarks for this article.
I used the Benchmark Shell of DebugKit (found here:
`http://thechaw.com/debug_kit`_) to get the results. Here is the
action that is ran:


Controller Class:
`````````````````

::

    <?php 
    public function bench()	{
    	$this->autoRender = false;
    	$this->Auth->login($this->User->findByUsername('Phally'));
    	$this->Acl->check($this->Auth->user(), "controllers/Movies/show");
    }
    ?>

These are the benchmark results, as you can see it is actually faster
too. The results depend on the number of queries issued, so it might
not always be faster. I have tested this with a medium size
application where that action issued eight queries to get that single
permission.

Without the CachedAclComponent:

::

    c:\wamp\www\aclbench>cake benchmark -n 50 http://aclbench.localhost/users/bench
    
    
    Welcome to CakePHP v1.2.2.8120 Console
    ---------------------------------------------------------------
    App : aclbench
    Path: c:/wamp/www/aclbench
    ---------------------------------------------------------------
    -> Testing http://aclbench.localhost/users/bench
    
    Total Requests made: 50
    Total Time elapsed: 48.731823444366 (seconds)
    
    Requests/Second: 1.026 req/sec
    Average request time: 0.975 seconds
    Standard deviation of average request time: 0.05
    Longest/shortest request: 1.167 sec/0.922 sec

With the CachedAclComponent:

::

    c:\wamp\www\aclbench>cake benchmark -n 50 http://aclbench.localhost/users/bench
    
    
    Welcome to CakePHP v1.2.2.8120 Console
    ---------------------------------------------------------------
    App : aclbench
    Path: c:/wamp/www/aclbench
    ---------------------------------------------------------------
    -> Testing http://aclbench.localhost/users/bench
    
    Total Requests made: 50
    Total Time elapsed: 47.651949167252 (seconds)
    
    Requests/Second: 1.049 req/sec
    Average request time: 0.953 seconds
    Standard deviation of average request time: 0.047
    Longest/shortest request: 1.169 sec/0.91 sec

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _http://thechaw.com/debug_kit: http://thechaw.com/debug_kit
.. _https://github.com/Phally/cache_engines: https://github.com/Phally/cache_engines
.. _https://github.com/Phally/cached_acl: https://github.com/Phally/cached_acl
.. _http://www.frankdegraaf.net: http://www.frankdegraaf.net/
.. _Page 2: :///articles/view/4caea0e4-2a94-466a-b1a2-4d8c82f0cb67#page-2
.. _Page 1: :///articles/view/4caea0e4-2a94-466a-b1a2-4d8c82f0cb67#page-1
.. meta::
    :title: Caching ACL permissions with CachedAclComponent
    :description: CakePHP Article related to acl,cache,phally,Components
    :keywords: acl,cache,phally,Components
    :copyright: Copyright 2009 
    :category: components

