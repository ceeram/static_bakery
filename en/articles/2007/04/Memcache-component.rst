

Memcache component
==================

by %s on April 12, 2007

A component for using Memcache to cache data from your controllers
Needs the `Memcache class`_. You can get the `full package with
instructions from my site`_.

Following goes in /app/controllers/components/memcache.php:

::

    
    <?php
    /**
     * Wrapper for Memcache, v. 0.1
     *
     * By Jiri Kupiainen (http://jirikupiainen.com/)
     *
     * You are free to do whatever you please with this code. Enjoy.
     */
    vendor('Memcache');
    
    class MemcacheComponent extends CakeMemcache {
            function startup(&$controller) {
                    $this->_connect();
            }
    }
    ?>



.. _full package with instructions from my site: http://jirikupiainen.com/2007/04/11/memcache-cakephp-cache-memcached-component-helper/
.. _Memcache class: http://bakery.cakephp.org/articles/view/333
.. meta::
    :title: Memcache component
    :description: CakePHP Article related to caching,memcached,memcache,Components
    :keywords: caching,memcached,memcache,Components
    :copyright: Copyright 2007 
    :category: components

