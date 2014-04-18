Flickr photos feed component
============================

This is a simple component that allows you to retrieve photos from a
Flickr user (or multiple users) without the need of an API Key or an
RSS parser. You can also specify tags to filter the feed by and cache
the results using a caching engine configuration.


Installation
~~~~~~~~~~~~
Create a file called flickr_photos_feed.php in
app/controllers/component and copy the following content

Component Class:
````````````````

::

    <?php 
    /**
     * FlickrPhotosFeedComponent
     *
     * Allows you to retrieve photos from a Flickr user (or multiple users).
     *
     * @author   Regis Bamba <regis.bamba@gmail.com>
     * @license     MIT 
     */ 
    class FlickrPhotosFeedComponent extends Object {
    
      /**
      * This specifies user IDs to fetch for.  
      * Can either be a string for a single user ID or an array for multiple users IDs.
      * Do not use usernames. Go to http://idgettr.com/ if you can't find the user Flickr ID
      *     
      * @var mixed
      * @access public
      */  
      var $flickrIds = null;
      
      /**
      * an array of tags to filter the feed by  
      * 
      * @var array
      * @access public
      */
      var $tags = array();
      
      /**
      * The number of items to be returned.
      * 
      * @var array
      * @access public
      */  
      var $numberOfItems = 9;
      
      /**
      * Fields to return for each item
      * Comment/Uncomment if needed
      *    
      * @var array
      * @access public
      */  
      var $itemsFields = array(
            'description',      // description of the feed (Usually HTML code)
            'description_raw',  // raw description of the feed
            'm_url',            // url to the medium size 
            't_url',            // url to the small size (thumbnail)
            'l_url',            // url to the large size (thumbnail)
            'photo_xml',        // 
            'date',             // 
            'date_taken',       //  
            'date_taken_nice',  // 
            'guid',             // 
            'author_name',      // username
            'author_url',       // user's profile
            'author_nsid',      // user's id
            'photo_url',        // url to the original picture
            'thumb_url',        // url to the small size (thumbnail)
            'height',           //
            'width',            //
            'l_width',          //
            'tags',             // list of tags separated by commas
            'tagsa',            // array of tags
            'photo_mime',       //
            'tags_list'         // array of tags 
            );
            
      /**
      * Base url for the feed
      * You can change this if you want to get items from an another feed     
      * @var string
      * @access public
      */
      var $feedBaseUrl = 'http://api.flickr.com/services/feeds/photos_public.gne';
              
      /**
      * Turn Caching on/off
      *    
      * @var boolean
      * @access public
      */
      var $useCache = false;
      
      /**
      * Cache engine configuration
      * Allows you to have a custom configuration for caching the feed   
      * @var array
      * @access public
      */                                  
      var $cacheConfig = array(  
            'engine' => 'File',  
            'duration'=> '+1 hours',  
            'path' => CACHE,  
            'prefix' => 'flickr_photos_feed_'
            );
    
      /**
      * Retrieve items from the feed  
      * @param boolean refreshCache If set to true, the component will still fetch the feed even if some cached data is present
      * @access public
      * @return array An array of photo items  
      */
      function getItems($refreshCache = false) {
        Cache::set($this->cacheConfig);
        if (Cache::read('items') === false || $refreshCache == true) {
            App::import('Core','HttpSocket');
            $HttpSocket = new HttpSocket();
            $params = array('format'=> 'php_serial');
            if (is_array($this->flickrIds)) {
                $params['ids'] = implode(",", $this->flickrIds);
            } else {
              $params['id'] = $this->flickrId; 
            }
              
            if (!empty($this->tags)) {
                $tags = implode(",", $this->tags);
                $params['tags'] = $tags; 
            }
            
            $feedString = $HttpSocket->get($this->feedBaseUrl, $params);
            $feed = unserialize($feedString);
            $result = array();
            $counter = 0;
            if (isset($feed['items'])) {
                foreach($feed['items'] as $item) {
                    $tmp = array();
                    foreach($this->itemsFields as $value) {
                        if (isset($item[$value])) {
                            $tmp[$value] = $item[$value];
                        }
                    }
                    array_push($result, $tmp);
                    $counter++;
                    if($counter >= $this->numberOfItems) {
                        break;
                    }
                }
                if($this->useCache) {
                    Cache::set($this->cacheConfig);
                    Cache::write('items', $result);
                }
            }
        } else {
            Cache::set($this->cacheConfig);
            $result = Cache::read('items');
        }
        return $result;
      }
    }
    ?>



Usage
~~~~~

Now to use it, simply:

+ add FlickrPhotosFeed to the var $component array in your controller
+ set the Flickr id/ids and other options if needed
+ call getItems() to get the photos


Here is an example:

In the controller example_controller.php you can have:

Controller Class:
`````````````````

::

    <?php 
    class ExampleController extends AppController {
      var $components = array('FlickrPhotosFeed');
      
      function MyAction() {
        $this->FlickrPhotosFeed->flickrIds = '34895824@N04';    // can also be an array
        // ...
        // ...
        // you can also set other options here
        // ...
        // ...
        $lastFlickrPhotos =  $this->FlickrPhotosFeed->getItems();
        $this->set('lastFlickrPhotos',$lastFlickrPhotos);
      }
      
    }
    ?>


And in the view my_action.ctp you can have:

View Template:
``````````````

::

    
    <h2>My Flickr Feed</h2>
    <?php foreach($lastFlickrPhotos as $photo) : ?>
      <img src="<?= $photo['t_url'] ?>" />        
    <?php endforeach; ?>

And that's it!! Good luck!


.. author:: regis
.. categories:: articles, components
.. tags:: feed,component,photo,flickr,Components

