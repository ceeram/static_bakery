ImageBehavior - best from database blobs and file storage
=========================================================

I've always had problem with uploaded images that were stored just as
files. They shouldn't go to repository and wasn't in database either.
So I created an ImageBehavior to handle my problems.
It handles storage in BLOB after upload, and convenient way to
retrieve this data.
The requirements are that model which acts as ImageBehavior has thise
fields
`content` BLOB (MEDIUMBLOB or LONGBLOB)
`modified` DATETIME (time of actual image version)
`ext` VARCHAR(10) (extension of uploaded file)
its good to have those too (but they are not required) :
`type` varchar (50) (for mime-type)
`size` int (for size in bytes)

You'll need filecache/ folder in app/webroot/img, and set permissions
so my behavior could write stuff in it.
Now, You're ready to download ImageBehavior:


Model Class:
````````````

::

    <?php 
    /**
     * ImageBehavior - take best from database blobs adn file image storage
     * requires 'content' field that is a blob (mediumblob or longblob), and
     * 'ext' varchar(10) field  and
     * 'modified' datetime field
     * @author Grzegorz Pawlik
     * @version 1.0
     */
    class ImageBehavior extends ModelBehavior {
       
       /**
        * directory in which cached files will be stored
        *
        * @var string
        */
       var $cacheSubdir = 'filecache';
       /**
        * if set to false - never check if cached file is present (nor actual)
        *
        * @var bool
        */
       var $usecache = true;
       
       function setup(&$Model) {
          // no setup at this time
       }
       
       /**
        * Insert proper blob when standard data after upload is present
        *
        * @param object $Model
        * @return bool true
        */
       function beforeSave(&$Model) {
    
          if(isset($Model->data[$Model->name]['file']['tmp_name']) && is_uploaded_file($Model->data[$Model->name]['file']['tmp_name'])) {
          // podnieÅ› wyÅ¼ej parametry
          $Model->data[$Model->name] = array_merge($Model->data[$Model->name],  $Model->data[$Model->name]['file']);
          // przygotuj blob
          $this->_prepareBlob($Model);
          
          $this->_getExt($Model);
          }
          
          return true;
       }
       
       /**
        * prepares blob contents 
        *
        * @param object $Model
        */
       function _prepareBlob(&$Model) {
          App::import('Core', 'File');
          $file = new File($Model->data['Medium']['tmp_name'], false);
          $content = $this->addSlashes( $file->read() );
          $Model->data[$Model->name]['content'] = $content;
       }
       
       /**
        * Get uploaded file extension
        *
        * @param object $Model
        */
       function _getExt(&$Model) {
          $file = explode('.', $Model->data['Medium']['name']);
          $ext = array_pop($file);
          $Model->data[$Model->name]['ext'] = $ext;
       }
       
       /**
        * replace blob contents with file path
        * After reading database checks if cached file is present. If not creates it (from blob contents) and
        * returns a 'file' field with path relative to /app/webroot/img
        * 
        *
        * @param object $model
        * @param array $results
        * @param unknown_type $primary
        * @return unknown
        */
       function afterFind(&$model, $results, $primary) {
          foreach($results as $key => $val) {
             
             
             
             $relpath = $this->cacheSubdir . DS . 
                     $val[$model->name]['id'] . '_' . $model->name . '_' . 
                     $val[$model->name]['modified'] . '.' . $val[$model->name]['ext']; 
             $relpath = str_replace( array(' ', ':') , '_', $relpath);
             
             $fullpath = IMAGES . $relpath;
             
             if(!file_exists($fullpath) || !$this->usecache ) {
                file_put_contents($fullpath, $this->stripSlashes($results[$key][$model->name]['content']));
             }
             
             $results[$key][$model->name]['file'] = $relpath;
             // remove blob from results (its messy when You want to output results in debug)
             unset($results[$key][$model->name]['content']);
          }
          return $results;
       }
       
       /**
        * add slashes (just wrapper)
        *
        * @param string $string
        * @return string with slashes
        */
       function addSlashes($string) {
          return addslashes($string);
       }
       
       /**
        * strip slashes (just wrapper)
        *
        * @param string $string
        * @return string without slashes
        */
       function stripSlashes($string) {
          return stripslashes($string);
       }
    }
    ?>

On next page - example of use

In my example I'll use this table:

::

    
    CREATE TABLE IF NOT EXISTS `media` (
      `id` int(11) NOT NULL auto_increment,
      `name` varchar(50) NOT NULL,
      `ext` varchar(10) NOT NULL,
      `content` longblob NOT NULL,
      `size` int(11) NOT NULL,
      `created` datetime NOT NULL,
      `modified` datetime NOT NULL,
      `type` varchar(20) NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM;



Model Class:
````````````

::

    <?php 
    class Medium extends AppModel {
    
    	var $name = 'Medium';
       var $actsAs = array('Image');
    }
    
    ?>



Controller Class:
`````````````````

::

    <?php 
    
    class MediaController extends AppController {
    
    	var $name = 'Media';
    	var $helpers = array('Html', 'Form');
    	
    	function index() {
          
          $this->set('media', $this->Medium->findAll());
          
    	}
    	
    	function add() {
    	   if(!empty($this->data)) {
    	      $this->Medium->save($this->data);
    	   }
    	}
    	
    }
    
    ?>

In add example You can see how files are stored in database. Model
expects file in ModelName.file field.

add.ctp view:

View Template:
``````````````

::

    
    <?php 
       echo $form->create(
          array('url' => array(
                               'controller' => 'media',
                               'action'    => 'add'
                         ),
                'enctype' => 'multipart/form-data'
                )
       ); 
    ?>
    
    <?php echo $form->file('Medium.file'); ?>
    
    <?php echo $form->end('submit'); ?>

In index action one can see how data are retrieved. Despite that
images are stored as blobs, we can use them like ordinary files.

index.ctp:

View Template:
``````````````

::

    
    <?php foreach($media as $medium): ?>
       <?php echo $html->image($medium['Medium']['file']); ?>
    <?php endforeach; ?>


When You upload file it assumes, that it will be Model.file field.
When it's ok - it just stores a BLOB in a database (with additional
data like extension and other stuff).

But magic happens when You try to retrieve data from Model acting as
Image.
* It checks /filecache folder for cached files. If one is found - it
just removes content field from results, ant place there 'file' field
with relative path to cached file.

If none is fond or found file is older than contents in DB (according
to `modified` field) it creates such file, and acts as described above
(*).

So You work with those images like with ordinary files, but when You
export database, You export files too.

Additionally You can force distant future expire header in .htaccess
file in filecache/ so any client browser will cache it until it's
changed in database.

Potential problems:
When You insert image file in text content in database (by BBCode for
example), and the image is changes - contents will remain outdated.

Probably blob field shouldn't be retrieved in every find, only when a
cached file isn't present or outdated. That's stuff to fix in future
versions.
`1`_|`2`_|`3`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_

.. _Page 3: :///articles/view/4caea0e4-2640-4212-9602-49a582f0cb67/lang:eng#page-3
.. _Page 2: :///articles/view/4caea0e4-2640-4212-9602-49a582f0cb67/lang:eng#page-2
.. _Page 1: :///articles/view/4caea0e4-2640-4212-9602-49a582f0cb67/lang:eng#page-1

.. author:: GrzegorzPawlik
.. categories:: articles, behaviors
.. tags:: image,upload,Behaviors

