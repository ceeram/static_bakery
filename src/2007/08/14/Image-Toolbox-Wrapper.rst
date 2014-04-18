Image Toolbox Wrapper
=====================

This is a very simple wrapper for the very-excellent Image_Toolbox
class.


Image_Toolbox Component
```````````````````````

I had a hard time finding a good image-resizing
component/helper/whatever for Cake. After searching
`http://phpclasses.org`_ for a bit, I found `http://image-
toolbox.sourceforge.net/`_ (Image_Toolbox)


Installation
````````````
Pretty simple. Go to `http://image-toolbox.sourceforge.net/`_ and
download the Image_Toolbox.class.php to your vendors directory.

The component code (save as image_toolbox.php in your components
directory):


Component Class:
````````````````

::

    <?php 
    class ImageToolboxComponent extends Object {
    	public function __construct()
    	{
    		parent::__construct();	
    		vendor('imagetoolbox/Image_Toolbox.class');
    	}
    	public function makeToolbox()
    	{
    		$args = func_get_args();
    		$argc = func_num_args();
    		$params = array();
    		for($i = 0; $i < $argc; $i++)
    		{
    			$params[] = '"'.$args[$i].'"';
    		}
                    $pstring = implode(",", $params);
    		$estring = "return new Image_Toolbox($pstring);";
    		return eval($estring);
    	}
    }
    
    ?>



Usage
`````

Usage is as follows:


Controller Class:
`````````````````

::

    <?php 
    //... code
    //Stock Toolbox
    $box1 = $this->ImageToolbox->makeToolbox();
    
    //Pass normal Image_Toolbox constructor params to the makeToolbox
    //function:
    $box2 = $this->ImageToolbox->makeToolbox("path/to/image.jpg");
    
    //...code
    ?>



.. _http://phpclasses.org: http://phpclasses.org/
.. _http://image-toolbox.sourceforge.net/: http://image-toolbox.sourceforge.net/

.. author:: sintaks
.. categories:: articles, components
.. tags:: image,resize,image_toolbox,resizing,image
manipulation,Components

