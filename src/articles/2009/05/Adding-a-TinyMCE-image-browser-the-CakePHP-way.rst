Adding a TinyMCE image browser the CakePHP way
==============================================

by Braindead on May 29, 2009

If your cake app requieres some sort of WYSIWYG editor, integrating
TinyMCE is in most cases the way to go. The only problem with TinyMCE
is that there is no image browser included for free. So we have to
build our own one.


Introduction
~~~~~~~~~~~~
This article is meant as a follow up to this one
`http://bakery.cakephp.org/articles/view/using-tinymce-with-cakephp`_
(see comment no. 4), as it will not explain how to integrate TinyMCE
into your app, but how to integrate an image browser into your already
working TinyMCE installation.

Please check the linked article to see how to integrate TinyMCE into
your app.

For the sake of simplicity, all my images will reside in one folder.
The user can browse the folder in form of a list and upload new
images.


Step 1: Create the uploads folder
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Create folder app/webroot/uploads and copy some image files into the
new folder.


Step 2: Create the Image model
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The image model will be responsible to read in all the images in the
uploads folder and will also take care that only valid files can be
uploaded.

Filename: app/models/image.php

Model Class:
````````````

::

    <?php 
    class Image extends AppModel {
    
    	var $name = 'Image';
    
    	var $validate = array(
    		'image' => array(
    			'rule' => array(
    				'validFile',
    				array(
    					'required' => true,
    					'extensions' => array(
    						'jpg',
    						'jpeg',
                            'gif',
                            'png'
    					)
    				)
    			)
    		)
    	);
    
        var $useTable = false;
    
        function readFolder($folderName = null) {
            $folder = new Folder($folderName);
            $images = $folder->read(
                true,
                array(
                    '.',
                    '..',
                    'Thumbs.db'
                ),
                true
            );
            $images = $images[1]; // We are only interested in files
    
            // Get more infos about the images
            $retVal = array();
            foreach ($images as $the_image)
            {
                $the_image = new File($the_image);
                $retVal[] = array_merge(
                    $the_image->info(),
                    array(
                        'size' => $the_image->size(),
                        'last_changed' => $the_image->lastChange()
                    )
                );
            }
    
            return $retVal;
        }
    
        function upload($data = null) {
            $this->set($data);
    
            if(empty($this->data)) {
                return false;
            }
    
            // Validation
            if(!$this->validates()) {
                return false;
            }
    
            // Move the file to the uploads folder
            if(!move_uploaded_file($this->data['Image']['image']['tmp_name'], APP.WEBROOT_DIR.DS.'uploads'.DS.$this->data['Image']['image']['name'])) {
                return false;
            }
    
            return true;
        }
    
    
    
        function validFile($check, $settings) {
        	$_default = array(
        		'required' => false,
        		'extensions' => array(
        			'jpg',
        			'jpeg',
        			'gif',
        			'png'
        		)
        	);
    
        	$_settings = array_merge(
        		$_default,
        		ife(
        			is_array($settings),
        			$settings,
        			array()
        		)
        	);
    
    		// Remove first level of Array
    		$_check = array_shift($check);
    
    		if($_settings['required'] == false && $_check['size'] == 0) {
    			return true;
            }
    
            // No file uploaded.
            if($_settings['required'] && $_check['size'] == 0) {
    			return false;
            }
    
            // Check for Basic PHP file errors.
            if($_check['error'] !== 0) {
    			return false;
            }
    
            // Use PHPs own file validation method.
            if(is_uploaded_file($_check['tmp_name']) == false) {
            	return false;
            }
    
            // Valid extension
            return Validation::extension(
            	$_check,
            	$_settings['extensions']
            );
    	}
    }
    ?>



Step 3: Create the images controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: app/controllers/images_controller.php

Controller Class:
`````````````````

::

    <?php 
    class ImagesController extends AppController {
    
        var $name = 'Images';
    
        var $uses = array('Image');
    
        var $helpers = array(
            'Html',
            'Form',
            'Javascript',
            'Number' // Used to show readable filesizes
        );
    
        function index() {
            $this->set(
                'images',
                $this->Image->readFolder(APP.WEBROOT_DIR.DS.'uploads')
            );
        }
    
        function upload() {
            // Upload an image
            if (!empty($this->data)) {
                // Validate and move the file
                if($this->Image->upload($this->data)) {
                    $this->Session->setFlash('The image was successfully uploaded.');
                } else {
                    $this->Session->setFlash('There was an error with the uploaded file.');
                }
                
                $this->redirect(
                    array(
                        'action' => 'index'
                    )
                );
            } else {
                $this->redirect(
                    array(
                        'action' => 'index'
                    )
                );
            }
        }
    }
    ?>



Step 4: Create a view to show the images and upload new ones
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: app/views/images/index.ctp

View Template:
``````````````

::

    
    <?php
        echo $javascript->codeBlock(
            "function selectURL(url) {
                if (url == '') return false;
    
                url = '".Helper::url('/uploads/')."' + url;
    
                field = window.top.opener.browserWin.document.forms[0].elements[window.top.opener.browserField];
                field.value = url;
                if (field.onchange != null) field.onchange();
                window.top.close();
                window.top.opener.browserWin.focus();
            }"
        );
    ?>
    
    <?php
        echo $form->create(
            null,
            array(
                'type' => 'file',
                'url' => array(
                    'action' => 'upload'
                )
            )
        );
        echo $form->label(
            'Image.image',
            'Upload image'
        );
        echo $form->file(
            'Image.image'
        );    
        echo $form->end('Upload');
    ?>
    
    <?php if(isset($images[0])) {
        $tableCells = array();
    
        foreach($images As $the_image) {
            $tableCells[] = array(
                $html->link(
                    $the_image['basename'],
                    '#',
                    array(
                        'onclick' => 'selectURL("'.$the_image['basename'].'");'
                    )
                ),
                $number->toReadableSize($the_image['size']),
                date('m/d/Y H:i', $the_image['last_changed'])
            );
        }
    
        echo $html->tag(
            'table',
            $html->tableHeaders(
                array(
                    'File name',
                    'Size',
                    'Date created'
                )
            ).$html->tableCells(
                $tableCells
            )
        );
    } ?>

If you now open the `http://example.com/images`_ you should see a list
with all the files you copied into the uploads folder. You should also
be able to upload a new image.


Step 5: Integrate the image browser into TinyMCE
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: app/views/elements/tinymce.ctp

View Template:
``````````````

::

    
    <?php echo $javascript->link("tiny_mce/tiny_mce.js"); ?>
    
    <?php
        echo $javascript->codeBlock(
            "function fileBrowserCallBack(field_name, url, type, win) {
                browserField = field_name;
                browserWin = win;
                window.open('".Helper::url(array('controller' => 'images'))."', 'browserWindow', 'modal,width=600,height=400,scrollbars=yes');
            }"
        );
    ?>
    
    <?php
        echo $javascript->codeBlock(
            "tinyMCE.init({
                mode : 'textareas',
                theme : 'advanced',
                theme_advanced_buttons1 : 'forecolor, bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,undo,redo,|,link,unlink,|,image,emotions,code',
                theme_advanced_buttons2 : '',
                theme_advanced_buttons3 : '',
                theme_advanced_toolbar_location : 'top',
                theme_advanced_toolbar_align : 'left',
                theme_advanced_path_location : 'bottom',
                extended_valid_elements : 'a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]',
                file_browser_callback: 'fileBrowserCallBack',
                width: '620',
                height: '480',
                relative_urls : false
            });"
        );
    ?>



Summary
~~~~~~~
As you could see, integrating the image browser into your TinyMCE
installation is actually quiete easy. If you need a more advanced
image browser, the view is first place you should tweak.

Happy baking!
Written by Markus Henke
`http://braindead1.de`_

.. _http://bakery.cakephp.org/articles/view/using-tinymce-with-cakephp: http://bakery.cakephp.org/articles/view/using-tinymce-with-cakephp
.. _http://braindead1.de: http://braindead1.de/
.. _http://example.com/images: http://example.com/images
.. meta::
    :title: Adding a TinyMCE image browser the CakePHP way
    :description: CakePHP Article related to WYSIWYG,TinyMCE,image browser,Tutorials
    :keywords: WYSIWYG,TinyMCE,image browser,Tutorials
    :copyright: Copyright 2009 Braindead
    :category: tutorials

