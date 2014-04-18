Efficient caching with NamespaceFileEngine
==========================================

In my attempt to cache ACL permissions I stumbled upon a limitation of
the current FileEngine. It is unable to drop a group of keys, only all
keys or just one. Here is an alternative engine (which basically is a
partial reimplementation of the current engine) that supports this.
This engine is not to replace the original one and should not be used
as the App's default engine.


How it works
~~~~~~~~~~~~
Basically, this Engine builds for every namespace a folder, so you get
a nice nested tree of cache files. The last key of a path will be the
name of the cache file. So you can see the separating dot as a
DIRECTORY_SEPARATOR or DS in Cake terms. This structure is needed to
delete groups of cache files or better said, parent namespaces.

The benefit of this is that you can delete a certain part of the cache
and not the whole cache. The cache for the other namespaces remains
intact, which makes this type of deletion more efficient, since it
doesn't have to rebuild the entire cache.

This Engine uses the features of the File and Folder classes to create
and delete the cache tree. The way the content of the cache files is
saved is the same as the original FileEngine.


Configuration
~~~~~~~~~~~~~
Before you start, clone the plugin. See the bottom for the link.

Next, configure the engine in:
APP/config/bootstrap.php

By placing this in bootstrap.php:

::

    <?php
    App::import('Vendor', 'CacheEnginesNamespaceFile');
    Cache::config('app', array(
        'engine' => 'NamespaceFile',
        'duration'=> '+1 hour',
        'prefix' => 'cake.'
    ));
    ?>



Usage
~~~~~
It is easy, just use it as you would with the old Engine, but with dot
separated key to separate the namespaces:

::

    <?php
    Cache::write('app.pictures.recent', $recentPictures, 'app');
    Cache::write('app.pictures.top', $topPictures, 'app');
    ?>

And to delete all defined pictures keys:

::

    <?php
    Cache::delete('app.pictures', 'app');
    ?>

Phally
`http://www.frankdegraaf.net`_

Note: I moved the source code to my GitHub page:
`https://github.com/Phally/cache_engines`_
So if you have any enhancements/fixes, don't hesitate to fork and send
me a pull request.

.. _https://github.com/Phally/cache_engines: https://github.com/Phally/cache_engines
.. _http://www.frankdegraaf.net: http://www.frankdegraaf.net/

.. author:: Frank
.. categories:: articles, snippets
.. tags:: caching,efficient,file,alternative,phally,namespace,Snippets

