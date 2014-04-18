SimplePie CakePHP Component
===========================

SimplePHP is a PHP class for retrieval and parsing of RSS feeds. This
is a wrapper to that class making it easy to use in the CakePHP
framwork. Much of this component is taken from the work of Scott
Sansoni (http://cakeforge.org/snippet/detail.php?type=snippet=53).
This is mostly an update so the component works with the lastest
version of SimplePie.


Download
````````
You can download a zip of the component at
`http://sandbox.pseudocoder.com/demo/simplepie`_.


Usage
`````

#. CakePHP either 1.1 or 1.2
#. Download SimplePie 1.0.1 (`http://simplepie.org/downloads`_) and
   unzip the contents. Move the simplepie.inc to one of the vendors
   folders. Rename the file to simplepie.php. I like to put the file in
   the sub folder with the README.txt and LICENSE.txt for easy reference.
#. Download the component (or paste the full code from below) and
   unzip it to app/controllers/components.
#. Include the component in any controller that will need it.



Component Class:
````````````````

::

    <?php <?php
    /*
     * SimplePie CakePHP Component
     * Copyright (c) 2007 Matt Curry
     * www.PseudoCoder.com
     *
     * Based on the work of Scott Sansoni (http://cakeforge.org/snippet/detail.php?type=snippet&id=53)
     *
     * @author      mattc <matt@pseudocoder.com>
     * @version     1.0
     * @license     MIT
     *
     */
    
    class SimplepieComponent extends Object {
      var $cache;
    
      function __construct() {
        $this->cache = CACHE . 'rss' . DS;
      }
    
      function feed($feed_url) {
        
        //make the cache dir if it doesn't exist
        if (!file_exists($this->cache)) {
          $folder = new Folder();
          $folder->mkdirr($this->cache); 
        }
    
        //include the vendor class
        vendor('simplepie/simplepie');
    
        //setup SimplePie
        $feed = new SimplePie();
        $feed->set_feed_url($feed_url);
        $feed->set_cache_location($this->cache);
    
        //retrieve the feed
        $feed->init();
    
        //get the feed items
        $items = $feed->get_items();
    
        //return
        if ($items) {
          return $items;
        } else {
          return false;
        }
      }
    }
    ?>
    ?>



Controller Class:
`````````````````

::

    <?php $items = $this->Simplepie->feed('http://feeds.feedburner.com/pseudocoder');
    ?>



View Template:
``````````````

::

    foreach($items as $item) {
      echo $html->link($item->get_title(), $item->get_permalink()) . '<br />';
    }



.. _http://simplepie.org/downloads: http://simplepie.org/downloads
.. _http://sandbox.pseudocoder.com/demo/simplepie: http://sandbox.pseudocoder.com/demo/simplepie

.. author:: mattc
.. categories:: articles, components
.. tags:: Rss,component,simplepie,Components

