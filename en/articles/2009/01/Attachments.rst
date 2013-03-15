Attachments
===========

by %s on January 27, 2009

Even though there are already good solutions out there this project
tries -as usual- to make things even a bit better and to provide a
documented, easy to setup and use, extensible, clean implementation of
the functionality described in detail below. Plus: It should work and
integrate well into the framework everyone of us loves so much. Parts
of the code is based upon or inspired by `Improved Upload Behavior` by
Tane Piper, `ImageHelper` by Jon Bennet, `Attach This!` by Alex
McFadyen and `Generic Upload Behavior` by Andy Dawson.


Outdated
--------
This article is outdated . The project has been packaged as a plugin
and moved to `http://github.com/davidpersson/media/tree/master`_.
Please see the the wiki pages of the project for documentation until a
new bakery article has been written.

Thank you for all your feedback and support!

David


Features
--------

+ Association of any number of files with any record of any model
+ Clean and documented code
+ Easy to install and setup
+ Transfer of files via HTTP, local file or from a HTML file form
+ Access attachments and versions via url
+ Creation and caching of file version (e.g. thumbnails, etc.) on the
  fly
+ Extendable to support other file types



Download
--------
`Get latest Attachments package`_


Install
-------
The package basically contains an Attachmet behavior , an Attachment
component , an Attachment element and some libraries .


#. Copy the files from the package to the appropriate folders of your
   cake app
#. Run cake bake schema run schema run create attachments from the
   working directory of your app or init the table with the provided sql



Setup
-----

Cache
`````
Edit your config/core.php and setup an additional cache config:

::

    Cache::config('binary', array('engine' => 'File', 
    								'prefix' => 'binary_', 
    								'serialize' => false)
     						);

You may choose a different cache engine but be warned that this cache
has got to chew big chunks of binary data (depends on the size of the
attached files you are requesting).



Behavior
````````
Add this to the $actsAs parameter of the model you'd like to attach
stuff to:


Model Class:
````````````

::

    <?php 
    var $actsAs = array('Attachment');
    ?>


No configuration options are required. See below on how to adjust the
behavior.



Component
`````````
The Component allows you to dynamically render versions of files.

!! This functionality is experimental and not intented to be used
within
!! production setups! It may easily let your php script run out of
memory and
!! put massive load upon your server.

Open up the controller file corresponding to your model and add the
Attachment component:


Controller Class:
`````````````````

::

    <?php 
    var $components = array('...','...','Attachment');
    ?>



Element
```````
Edit the add and edit views of the controller make sure the form type
is set to file.


View Template:
``````````````

::

    <div class="examples form">
    	<?php echo $form->create('Example',array('type' => 'file'));?>
    		...


Still in the view add the statement for rendering the attachment
element


View Template:
``````````````

::

    		...
     		<?php echo $this->element('attachment');?>
    	<?php echo $form->end('Submit');?>
    </div>

The element supplies you with a basic listing of attached files and
fields to add more files to the record.



Usage in views
--------------
Assuming you attached an image file named freekevin.jpg to the record
of the Example model with the id 23.

NOTE : You've got to completely turn off debugging in order to make
this work.
To render the image you could simply do:


View Template:
``````````````

::

    echo $html->image('/examples/23/attachments/freekevin.jpg');

To render a resized version of the image within constraints of 300
width and 300 height:



View Template:
``````````````

::

    echo $html->image('/examples/23/attachments/freekevin.jpg/thumb');



+ tiny : 16x16
+ thumb : 100x100
+ medium : 300x300
+ large : 800x800
+ port : 1000x550


Currently you can only generate version of file types that are
supported by the GD extension.
For extending this feature you may have a look into the source code of
vendors/XFile.php and vendors/XFile/XFileImageGd.php .



Adjusting the Behavior
----------------------
You can customize how the file is going to be named and where it's
stored by using special markers in the options.

The markers {DS},{APP},{WWW_ROOT} and {UNIQUE_ID} are valid for base ,
dirname and basename .
Additionally {BASENAME},{FILENAME} and {EXTENSION} as well as any
other field that is submitted with your attachment (e.g. {GROUP}) can
be used within basename .


+ base : Absolute path to base directory without trailing slash
+ dirname : Relative path without trailing slash
+ basename : Basename of the destination file

Checks are enforced onto a file being attached. All of these options
are pretty self explanatory.
See the source of the behavior for the correct syntax and defaults.

+ allowMimetype
+ denyMimetype
+ allowExtension
+ denyExtension
+ allowPaths
+ maxSize



At least there are three more general options.


+ infoLevel : Controls the verbosity of the output on find
+ checksumAlgo


NOTE : You may also add additional columns to the attachments table.


Find Operations
---------------
Assuming you already attached files to records, a find() issued on the
Example Mode would result in (depends on verbosity set for behavior
and file type):

::

    Array
    (
        [Example] => Array
            (
                [id] => 1
                [title] => Let Me Show You
                [created] => 2008-01-21 16:28:33
                [modified] => 2008-01-21 16:28:33
            )
    
        [ExampleAttachment] => Array
            (
                [0] => Array
                    (
                        [id] => 1
                        [model] => Example
                        [foreign_key] => 1
                        [base] => /home/davidpersson/Workspace/project/webroot/
                        [dirname] => files/examples
                        [basename] => freekevin.jpg
                        [filename] => freekevin
                        [extension] => jpg
                        [checksum] => 9e496bcf9f601a7501b3efaf2b19da15
                        [size] => 49160
                        [mimetype] => image/jpeg
                        [mediatype] => image
                        [width] => 640
                        [height] => 480
                        [ratio] => 4:3
                        [megapixel] => 0
                        [quality] => 0
                        [group] => demo
                        [created] => 2008-01-21 16:28:33
                        [modified] => 2008-01-21 16:28:33
                    )
    			...
    
            )
    
    )

If you'd like to attach a file directly to an existing record you
would build:

::

    Array
    (
        [Example] => Array
            (
                [id] => 1
            )
    
        [ExampleAttachment] => Array
            (
                [0] => Array
                    (
                    	[file] => /var/log/kern.log
                    )
            )
    
    )  

...then...


Controller Class:
`````````````````

::

    <?php $this->Example->save($data);?>

Of course the save operation above is going to fail because the file
is not within allowed paths.
By default all files below the app's temp , webroot and the systems
temp directory are considered to have valid locations.

NOTE : Supplying an id for the attachment would cause the attached
file to be substituted by the new file.
[p] NOTE : Supplying an delete which is set to true causes the record
and file to be deleted permanently .
[p]
You could even attach a remote file to a record by setting the file
field to
e.g. `http://www.cakephp.org/img/cake-logo.png`_.
This would cause the remote file to be downloaded, saved to your local
filesystem and then attached to the record.



.. _http://github.com/davidpersson/media/tree/master: http://github.com/davidpersson/media/tree/master
.. _http://www.cakephp.org/img/cake-logo.png: http://www.cakephp.org/img/cake-logo.png
.. _Get latest Attachments package: http://cakeforge.org/projects/attm/
.. meta::
    :title: Attachments
    :description: CakePHP Article related to image,component,behavior,upload,attach,attachment,Behaviors
    :keywords: image,component,behavior,upload,attach,attachment,Behaviors
    :copyright: Copyright 2009 
    :category: behaviors

