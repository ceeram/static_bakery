Using FCKeditor with CakePHP
============================

FCKeditor is a powerful WYSIWYG editor that replaces the plain, old
and boring textarea form element. It has file management functions (so
you can upload images, flash and other files directly from it) and
many more useful functions. To quote the FCKeditor website,
[quote]This HTML text editor brings to the web many of the powerful
functionalities of desktop editors like MS Word. It's lightweight and
doesn't require any kind of installation on the client
computer.[/quote] It is compatible with most internet browsers which
include: IE 5.5+ (Windows), Firefox 1.0+, Mozilla 1.3+ and Netscape
7+. On the server side, we're going to use PHP.
The official FCKeditor site is `fckeditor.net`_, where you can
`download the latest version`_.

Installation
~~~~~~~~~~~~

Delete the unnecessary stuff (not required)
```````````````````````````````````````````
Since we're going to use php, it's safe to delete all other
connectors.
From the editor/filemanager/browser/default/connectors, delete the asp
, aspx , cfm , lasso , perl and py directories.
From the editor/filemanager/upload delete asp , aspx , cfm and lasso
directories.
Now we've reduced the size of FCKeditor from 2.4 to 2.0 megabytes. You
can also delete skins, plugins and languages that you don't want and
also functionality you don't need (in dialog directory) but be careful
not to break things.


Copy stuff
``````````
Copy the editor directory, fckeditor.js , fckconfig.js , fckstyles.xml
and fcktemplates.xml to your app/webroot/js directory.


Configuration
~~~~~~~~~~~~~
Edit the app/webroot/.htaccess file and add the following two lines on
the end:

::

    AddType application/x-javascript .js
    AddType text/css .css

I'm not going to go into FCKeditor configuration itself. Open the
app/webroot/js/fckconfig.js file and figure it out yourself.


Helper
~~~~~~
Create the app/views/helpers/fck.php

Helper Class:
`````````````

::

    <?php 
    class FckHelper extends Helper
    {
    	function load($id, $toolbar = 'Default') {
    		foreach (explode('/', $id) as $v) {
    	 		$did .= ucfirst($v);
    		}
    
    		return <<<FCK_CODE
    <script type="text/javascript">
    fckLoader_$did = function () {
    	var bFCKeditor_$did = new FCKeditor('$did');
    	bFCKeditor_$did.BasePath = '/js/';
    	bFCKeditor_$did.ToolbarSet = '$toolbar';
    	bFCKeditor_$did.ReplaceTextarea();
    }
    fckLoader_$did();
    </script>
    FCK_CODE;
    	}
    }
    ?>



Usage
~~~~~
Now, all that is left is to include the fckeditor.js file on every
page that is going to use it.

::

    <?php echo $javascript->link('fckeditor'); ?>

Enable the fck helper in your controller

::

    var $helpers = array('Html', 'Form', 'Javascript', 'Fck');

And finally, load the editor on the textarea fields that need it.

::

    <div class="required">
    	<?php echo $form->labelTag( 'Comment/body', 'Body' );?>
    	<?php echo $html->textarea('Comment/body', array('cols' => '60', 'rows' => '10'));?>
    	<?php echo $fck->load('Comment/body'); ?>
    	<?php echo $html->tagErrorMsg('Comment/body', 'Please enter the Body.');?>
    </div>



Enabling file browser/uploader
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Create directories Image , Flash , Media , File in your
app/webroot/files directory. Chmod them to 0777.

In the app/webroot/js/fckconfig.js file, set:

::

    var _FileBrowserLanguage = 'php';
    var _QuickUploadLanguage = 'php';

To enable uploads from the 'Browse Server' window, edit the app/webroo
t/js/editor/filemanager/browser/default/connectors/php/config.php and
set:

::

    $Config['Enabled'] = true;
    $Config['UserFilesPath'] = '/app/webroot/files/';

To enable quick uploads, edit the
app/webroot/js/editor/filemanager/upload/php/config.php file and set:

::

    $Config['Enabled'] = true;
    $Config['UserFilesPath'] = '/app/webroot/files/';



.. _download the latest version: http://www.fckeditor.net/download
.. _fckeditor.net: http://www.fckeditor.net

.. author:: kliklik
.. categories:: articles, tutorials
.. tags:: WYSIWYG,fck editor,fck,editor,Tutorials

