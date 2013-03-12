

Imagefile Helper
================

by %s on January 04, 2007

if you don't know if an image you're about to display exists
I was in need of a way to check if a file exists and send the viewer a
notfound image if it wasn't found

thanks to the guys on #cakephp (guille1983,Dieter@be,nathe,_seanc_)

place this file in /app/views/helpers/imagefile.php

and then you can call in your view
locateFile($file);?>"> and it will either render the file if it's
found or the notfound image (optionally a default notfound image if
one is not passed)


Helper Class:
`````````````

::

    <?php 
    class ImagefileHelper extends Helper{
    	function locateFile($filename=null,$notfoundfile=null){
    		$realfile=WWW_ROOT.$filename;
    		if(!empty($filename) && file_exists($realfile)){
    			return $filename;
    		}
    		else{
    			if(empty($notfoundfile) || !file_exists(WWW_ROOT.$notfoundfile)){
    				return "/images/nophoto.jpg";
    			}
    			else{
    				return $notfoundfile;
    			}
    		}
    	}
    }
    ?>


.. meta::
    :title: Imagefile Helper
    :description: CakePHP Article related to ,Helpers
    :keywords: ,Helpers
    :copyright: Copyright 2007 
    :category: helpers

