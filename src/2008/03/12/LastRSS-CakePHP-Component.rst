LastRSS CakePHP Component
=========================

by jimmygle on March 12, 2008

LastRSS is a PHP class for retrieval and parsing of RSS feeds. This is
a wrapper to that class making it easy to use in the CakePHP framwork.
Much of this component is taken from the work of Matt Curry
(http://bakery.cakephp.org/articles/view/simplepie-cakephp-component)
and Scott Sansoni
(http://cakeforge.org/snippet/detail.php?type=snippet=53).


Download
````````
You can download the required LastRSS files at
`http://lastrss.oslab.net/`_

Usage
`````

#. CakePHP either 1.1 - I have not tested on 1.2
#. Download LastRSS (`http://lastrss.oslab.net/`_) and unzip the
   contents into the vendors folder and rename the file to 'lastrss.php'
   (all lowercase).
#. Download the component (or paste the full code from below) and put
   it in 'app/controllers/components/lastrss.php'.
#. Include the component in any controller that will need it.



Component Class:
````````````````

::

    <?php 
    /*
     * LastRSS CakePHP Component
     * Copyright (c) 2007 Jimmy Gleason
     * www.jimmygleason.com
     *
     * Based on the work of Matt Curry (http://bakery.cakephp.org/articles/view/simplepie-cakephp-component) &
     * Based on the work of Scott Sansoni (http://cakeforge.org/snippet/detail.php?type=snippet&id=53) 
     *
     * @author Jimmy Gleason
     * @version 1.0
     * @license MIT
     *
     */  
    
    class LastrssComponent extends Object {
       var $cache;
    
       function __construct() {
          $this->cache = CACHE . 'rss' . DS; 
       }
    
       function feed($feed_url) {
    
          // Make the cache dir if it doesn't exist
          if (!file_exists($this->cache)) {
             uses('folder');
             $folder = new Folder();
             $folder->mkdirr($this->cache);
          }
    
          // Include the vendor class
          vendor('lastrss');
    
          // Setup LastRSS
          $feed = new lastRSS();
          $feed->cache_dir = $this->cache;
          $feed->cache_time = 3600; // one hour
    
          // Load RSS file
          if($rss = $feed->get($feed_url)) {
             $items = $rss;
             return $items;
          } else {
             // Return false
             return false;
          }
       }
    }
    ?>



Controller Class:
`````````````````

::

    <?php 
    class FeedsController extends AppController {
    
       var $name = 'Feeds';
       var $components = array('Lastrss');
    
       function showFeed() {
    
          $items = $this->Lastrss->feed('http://rss.news.yahoo.com/rss/topstories'); // Get feed
          $this->set("items",$items); // Send feed to view
       }
    
    }
    ?>



View Template:
``````````````

::

    
       <ul class="rssfeed">
          <?php if($items) { foreach($items['items'] as $item) { ?>
             <li class="rss"><?php echo $html->link($item['title'],$item['link']); ?></li>
          <?php } else { ?>
             <li class="rsserror">Feed Error</li>
          <?php } ?>
       </ul>



.. _http://lastrss.oslab.net/: http://lastrss.oslab.net/

.. author:: jimmygle
.. categories:: articles, components
.. tags:: Rss,component,lastrss,Components

