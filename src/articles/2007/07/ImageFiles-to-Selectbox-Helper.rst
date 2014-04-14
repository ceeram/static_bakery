ImageFiles-to-Selectbox Helper
==============================

by ArcadeTV on July 28, 2007

Little Helper that generates a dropdown selectbox containing the names
of image-files within your webroot/img folder. Great for admin-sites
where you want to add image-filenames to a form or database.


Helper Class:
`````````````

::

    <?php 
    <?php
    // views/helpers/imageselectbox.php
    
    class ImageselectboxHelper extends AppHelper {
    	var $helpers = array('Form','Html');
    		
    		function make( $fieldname, $path, $filter='.' ) {
    			
    			$path = IMAGES.DS.$path;
    			
    			$arr = array();
    			if (!@is_dir( $path )) {
    				return $arr;
    			}
    			$handle = opendir( $path );
    		
    			while ($file = readdir($handle)) {
    				if (($file != ".") && ($file != "..")) {
    					if (preg_match( "/$filter/", $file )) {
    							$arr[trim( str_replace(IMAGES.'/','',$path).'/'.$file)] = trim( str_replace(IMAGES.'/','',$path).'/'.$file );
    					}
    				}
    			}
    			closedir($handle);
    			asort($arr);
    			return $this->Form->select($fieldname, $arr);
    		}
    }
    ?>
    ?>



How to use in your view
~~~~~~~~~~~~~~~~~~~~~~~


View Template:
``````````````

::

    
    echo $imageselectbox->make('name_for_selectbox', 'foldername_in_webroot_img');

Please feel free to share your opinion and code if you find this
useful.

.. meta::
    :title: ImageFiles-to-Selectbox Helper
    :description: CakePHP Article related to images,form,filenames,selectbox,Helpers
    :keywords: images,form,filenames,selectbox,Helpers
    :copyright: Copyright 2007 ArcadeTV
    :category: helpers

