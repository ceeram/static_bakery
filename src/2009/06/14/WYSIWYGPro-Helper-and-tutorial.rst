WYSIWYGPro Helper and tutorial
==============================

I couldn't find any resources on setting up WYSIWYGPro with Cake so I
developed this helper along with instructions for total integration
with your system. If you've never used WYSIWYGPro, you should check
out the demos. I've tried every WYSIWYG editor out there and none of
the other ones even come close as far as I'm concerned.
WYSIWYGPro is actually PHP based. It assumes a PHP backend and has a
lot of callbacks, tie ins, configuration, and extension options. From
a purely interface standpoint it's tremendously more stable than any
other that I've used (especially when switching from source editing
and back) and provides valid XHTML. Images and can be inserted and
displayed in-line making it feel much more like a desktop editor.
There's even a full screen mode.

I mentioned integration. For images, documents, and media files
WYSIWYGPro will allow you to give users file browsing access wherever
you allow, thumbnail image selection, uploading right there in the
interface, as well as alignment, padding, rotation, resizing and title
editing (with preview). Even video thumbnails with ffmpeg and
customizable embedded flash players.

There's also a hyperlink interface that makes creating direct links
and mail to links easy. One of the extensions offers the ability to
load a tree list of URLs from your site so that your end users can
just pick the page that they want to link to rather than copying and
pasting URLs. The logic for creating this tree is up to you but I'll
show you how I did it for an example.

For those clients that refuse to stop copying and pasting from MS Word
(you know who I'm talking about) there's even a C from Word feature
that will selectively strip out all of that formatting for you. Every
feature can be easily turned on and off as well.

The downside to WYSIWYGPro of course, it's not free. It is pretty
reasonable though, however, those types of decisions are up to you.

Check it out at:
`WYSIWYGPro`_
Once you have everything setup as detailed below, all that you have to
do to use WYSIWYGPro in your code will be:


View Template:
``````````````

::

    
    <?php echo $wysiwygpro->input('Content.stuff') ?>

On to setup. Once you've downloaded WYSIWYGPro, drop the entire folder
into app/webroot/wysiwygPro. I realize that the vendors folder is
where this kind've thing should usually go, however, there's a lot of
files and images that WYSIWYGPro automatically links to and it's
tremendously simpler this way.

Next, there are a couple of configuration files to edit. Open up
wysiwygPro/config.inc.php and find line 90 which should be:

::

    
    define('WPRO_SESSION_ENGINE', '<SESSION_TYPE>');

should be either PHP or WP. WP allows WYSIWYGPro to handle their
session information independently. PHP will uses the default PHP
sessions. If you'd like to include your own session logic, you may
wish to take a look at the wysiwygPro/conf/customSessHandlers.inc.php
file to add your own custom session handler. I wrote one for this
exact purpose which you can find here:

`Using Cake Sessions Outside of Cake`_
This seems to have issues when using Cake's database sessions but only
on IIS. Works fine otherwise and I'm not sure what the issue is with
IIS. Haven't had any problems with a LAMP configuration though.

For easiest use though, I recommend setting to WP. It will work
independently of your stuff, you don't have to worry about session
names, etc. Much simpler. You'll also want to make sure that the
wysiwygPro/temp directory is writable.

The one giant warning from WYSIWYGPro is not to allow access to any of
the uploader or browser functions by default. This is how it is
already setup. Those should be set when you create the editor and they
are stored in session. That way you can create settings specially for
your logged in users, giving them access to their own personal
directory, etc.


The helper actually has 2 files, a configuration file and the helper
itself. Both can be found at my public GitHub account here:

`Source Code on GitHub`_
You'll want to drop the config file into your app/config folder. It's
setup as a standard Configure::load() file. All of your application
defaults can be set here to be used by the helper. You can also change
any settings you wish every time that you call the helper.

Let's look at the part of the configuration file:

::

    
    <?php 
    $config['Wysiwygpro']['htmlCharset'] = 'UTF-8';
    $config['Wysiwygpro']['operaSupport'] = true;
    
    //Disabled but likely requested features: fontcolor, highlight
    $config['Wysiwygpro']['disableFeatures'] = array(array('print','outdent','indent','full','fontcolor','spacer','emoticon','snippets','highlight','dirltr','dirrtl','bookmark'));
    
    //Add buttons not included by default
    $config['Wysiwygpro']['addRegisteredButton'] = array('document','after:link');
    ?>

The settings prefixed with _ are special settings for the helper
itself. Others are all references to either functions or properties
used by WYSIWYGPro itself. The settings are iterated through,
detecting either method or property within WYSIWYGPro and setting them
appropriately. If you notice, the disableFeatures setting is using
array(array( syntax. That's because the functions are called using
php's call_user_func_array and if we want to pass an array as the
first argument of a function, that array must be the first value of
the passed array.

This structure allows you to call any WYSIWYGPro setting even if I
haven't included it in the config file, just reference the developer
documentation to expand on everything.

Other settings such as directory defaults are fairly self-explanatory.
There are 3 types, images, documents, and media. Images and Media can
be directly embedded, document (including images and media) can be
linked to.

::

    
    <?php
    $config['Wysiwygpro']['_directory_settings'] = array(
       //images
       'image' => array(
          'type' => 'image',
          'dir' => WWW_ROOT . 'img',
          'URL' => '/img',
          'name' => 'All Images',
          'editImages' => false,
          'renameFiles' => false,
          'renameFolders' => false,
          'deleteFiles' => false,
          'deleteFolders' => false,
          'copyFiles' => true,
          'copyFolders' => true,
          'moveFiles' => false,
          'moveFolders' => false,
          'upload' => true,
          'overwrite' => false,
          'createFolders' => true,
          'filters' => array('Thumbnails')
       ),
       ...
    ?>

The 'directories' setting is where you specify which directories you
actually want to be available.

::

    
    <?php
    $config['Wysiwygpro']['directories'] = array(
          array('type' => 'image'),
          array('type' => 'document'),
          array('type' => 'media'),
          array( //Example of including a custom directory
             'type' => 'image',
             'dir' => WWW_ROOT . 'img/mine',
             'URL' => '/img/mine',
             'name' => 'My Images',
             'editImages' => true,
             'renameFiles' => true,
    ...
    ?>

Here we've include 4 directories, an image, document, and media
directory using the default settings and an image directory using some
custom settings. The full example is available in the config file.
Directories that are missing will be created using the
'_directory_permissions' setting.

You can also provide a list of styles for end users to use. I find
this is preferable to giving full font/color control to most people so
you can limit the available options to only your chosen styles.

::

    
    <?php
    //Provide a list of styles that users can choose from
    $config['Wysiwygpro']['stylesMenu'] = array( 
           'p' => 'Paragraph',
           'div' => 'Div',
           'h2' => 'Heading 2',
           'h3' => 'Heading 3',
           'h4' => 'Heading 4',
           'h5' => 'Heading 5',
           'blockquote' => 'Blockquote',
           'p class="warning"' => 'Warning Box' //Example of a style with a class
    );
    ?>

You can also have the editor use a specified CSS file to format the
contents. It's not included in the config file by default, but you
could easily do something like this seeing that the function is
available here.

`WYSIWYGPro Developer Docs - addStylesheet`_

::

    
    <?php
    $config['Wysiwygpro']['addStylesheet'] = '/css/wysiwygpro.css';
    ?>



The helper itself uses the form helper to generate the standard Cake
textarea code (complete with div/label structure) and then replaces
the text area with WYSIWYGPro. For sheer visibility sake, I altered
the code if an error is present on the field to place the error-
message div BEFORE the WYSIWYG simply because it's more readable to
have the small label and small error before the large editor. The code
doing that is on line 124-132.

You've already seen all of the configuration settings in the config
file, but what if you want to override some of them on the fly.

::

    
    <?php 
    echo $wysiwygpro->input('Content.stuff',
                             array('alt' => 'some normal options'),
                             array(
                                 '_editor_height' => '200px',
                                 'directories' => array(
                                        array( //Example of including a custom directory
                                         'type' => 'image',
                                         'dir' => WWW_ROOT . 'img/mine/' . $session->read('Auth.User.id'),
                                         'URL' => '/img/mine/' . $session->read('Auth.User.id'),
                                         'name' => 
                                               Inflector::pluralize($session->read('Auth.User.first_name')) 
                                               . ' Images',
                                         'editImages' => true,
                                         'renameFiles' => true,
                                         ),
                                   )
                              )
                         );
    ?>

Here we've changed the editor height setting for this instance and
added a special image directory for the current user where they can
edit and rename images.


#page4

To include links from your own system you can add a plugin that will
map out your link structure for users to simply pick a page to link to
from a list rather than copying and pasting URLs.

::

    
    $config['Wysiwygpro']['loadPlugin'] = 'MySiteLinks';

You can use WYSIWYGPro's built in javascript tree code (which we'll do
in this example) or even provide a path to an iFrame where you
generate your own. Details on how are included in the WYSIWYGPro
online documentation.

If you'd like to do this though, create a folder called
webroot/wysiwygPro/plugins/MySiteLinks that contains a single file,
plugin.php. I've set mine to look for a CakePHP /tmp/cache/persistent
file created using the Object::persist function. This file contains
the link tree that I created from a function in my content controller
(also shown shortly afterwards).

If the file isn't found, I'm using a curl to trigger the function
which will build these links. I'm doing that for simplicity in this
example, however, I would strongly recommend using a cake shell rather
than exposing this function publicly. I cannot emphasize enough, if
you want to use integrated URLs this is nothing more than an example
and you will need to build your own for your own site/CMS.

::

    
    <?php
    if (!defined('IN_WPRO')) exit;
    
    class wproPlugin_MySiteLinks {
       
        function onBeforeGetLinks(&$editor) {
           $editor->links = $this->linkList();
        }
        
        function linkList() {
            if(!defined('DS')) define('DS',DIRECTORY_SEPARATOR);
    
            list($app,$plug) = explode('webroot',dirname(__FILE__));
            $links = $app . 'tmp' . DS . 'cache' . DS . 'persistent' . DS . 'wysiwygpro.php';
            
            if(file_exists($links)) return $this->loadLinks($links);
            else { //Generate the list
               $cmd = 'curl http://' . $_SERVER['HTTP_HOST'] . '/content/generate_links';
               exec($cmd);
    
               if(file_exists($links)) return $this->loadLinks($links);
            }
        }
        
        function loadLinks($filename) {
           include_once($filename);
           if(isset($wysiwygpro)) {
              $links = unserialize($wysiwygpro);
              return $links[0];
           }
        }
       
    }
    ?>

The built in WYSIWYGPro tree, just needs an array in the structure of:

::

    
    array(
       0 => array(
          'title' => 'link title here',
          'URL'   => '/somewhere/view/stuff-article-here',
          'children' => array(...more of the same)
       )
    )

And here's how I created the link tree file used above, organized by
Category.


Model Class:
````````````

::

    <?php 
    	function generateWYSIWYGProLinks() {
    	   $out = array();
    	   
    	   $cats = $this->Category->find('all',array('fields' => array('Category.name','Category.slug','Category.id')));
    	   
    	   foreach($cats AS $c) {
    	      $out[$c['Category']['id']] = array(
    	         'title' => $c['Category']['name'],
    	         'children' => array());
    	   }
    	   
    	   $links = $this->find('all',array('fields' => array('Content.title','Content.slug','Content.category_id','Content.parent_id','Category.slug')));
                
          foreach($links AS $l) {
          
             $link = array('title' => $l['Content']['title'],'URL' => Router::url(array(
                'controller' => 'content',
                'action' => 'view',
                'category' => $l['Category']['slug'],
                'slug' => $l['Content']['slug'],
                'admin' => false
                )));
          
             $out[$l['Content']['category_id']]['children'][] = $link;
          }
          
    	   $this->_savePersistent('wysiwygpro',$out);   	
    	}
    ?>

And finally, the code is publicly available on my GitHub account

`Source Code (/config/wysiwygpro.php and /helpers/wysiwygpro.php)`_
I hope everyone gets some use out of this. If anybody sees room for
improvement, just add them in the comments and I will update the code.
`1`_|`2`_|`3`_|`4`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_

.. _Page 4: :///articles/view/4caea0e3-cda0-4cee-8bfc-412982f0cb67/lang:eng#page-4
.. _Page 1: :///articles/view/4caea0e3-cda0-4cee-8bfc-412982f0cb67/lang:eng#page-1
.. _Page 3: :///articles/view/4caea0e3-cda0-4cee-8bfc-412982f0cb67/lang:eng#page-3
.. _Source Code (/config/wysiwygpro.php and /helpers/wysiwygpro.php): http://github.com/brightball/open-source/tree/master
.. _Page 2: :///articles/view/4caea0e3-cda0-4cee-8bfc-412982f0cb67/lang:eng#page-2
.. _WYSIWYGPro: http://www.wysiwygpro.com/
.. _Using Cake Sessions Outside of Cake: http://bakery.cakephp.org/articles/view/using-cake-sessions-outside-of-cake
.. _WYSIWYGPro Developer Docs - addStylesheet: http://www.wysiwygpro.com/index.php?id=127

.. author:: brightball
.. categories:: articles, helpers
.. tags:: WYSIWYG,editor,TinyMCE,form,wysiwygpro,brightball,Helpers

