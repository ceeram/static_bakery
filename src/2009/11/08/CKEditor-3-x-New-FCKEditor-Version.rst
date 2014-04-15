CKEditor 3.x - New FCKEditor Version
====================================

by Valkum on November 08, 2009

Now FCKEditor is called CKEditor. And here is an running Version of
CKEditor and CakePHP. CKEditor is an WYSIWYG Javascript Editor.
To Enable CKEditor functionality in your CakePHP version,
download CKEditor from CKEditors Downloadpage ->
`http://ckeditor.com/download`_
Extract the downloaded archive to /app/webroot/js

so that you have the following tree structure

::

    
    +-/app/webroot/js
    +---/ckeditor
    +------/images
    +------/lang
    +------/plugins
    +------/skins
    +------/themes



Helper Class:
`````````````

::

    <?php 
    <?php
    class FckHelper extends Helper {
    
        var $helpers = Array('Html', 'Javascript');
    
        function load($id) {
            $did = '';
            foreach (explode('.', $id) as $v) {
                $did .= ucfirst($v);
            } 
    
            $code = "CKEDITOR.replace( '".$did."' );";
            return $this->Javascript->codeBlock($code); 
        }
    }
    ?>


To call CKEditor in a View put this into your *.ctp


View Template:
``````````````

::

    
    <?php
    echo $javascript->link('ckeditor/ckeditor', NULL, false);
    ?>

and

View Template:
``````````````

::

    
    <?php
    echo $fck->load('Model.field');
    ?>


An example is:

View Template:
``````````````

::

    
    <?php
    	echo $javascript->link('ckeditor/ckeditor', NULL, false);
    
    	echo $form->create('News', array('action' => 'edit'));
    	echo $form->input('title');
    	echo $form->input('body', array('cols' => '60', 'rows' => '3'));
    	echo $fck->load('News.body');
    	echo $form->input('id', array('type'=>'hidden')); 
    	echo $form->end('Save Post');
    ?>


Reqs:
Javascript helper must be included. ($helpers)

.. _http://ckeditor.com/download: http://ckeditor.com/download

.. author:: Valkum
.. categories:: articles, helpers
.. tags:: WYSIWYG,helper,session,fck,editor,authentication,ck,Helpers

