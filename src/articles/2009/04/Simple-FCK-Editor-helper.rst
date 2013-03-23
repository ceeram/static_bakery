Simple FCK Editor helper
========================

by %s on April 07, 2009

FCK Editor is my favourite HTML editor, mainly used for website cms.
There is already another great article on the topic
(http://bakery.cakephp.org/articles/view/using-fckeditor-with-cakephp)
which helped me a great deal, but I wanted to find a simpler way to
create a reusable FCK editor within CakePHP.


Installation
````````````
1) Copy fckeditor folder to app/webroot/js/
2) Copy fckeditor.php, fckeditor_php4.php, fckeditor_php5.php to
app/vendors/

Setup
`````

1) Create app/views/helpers/fck.php with the following code

::

    
    App::import('Vendor', 'fckeditor');
    
    class FckHelper extends AppHelper { 
                   
        /**
        * creates an fckeditor textarea
        * 
        * @param array $namepair - used to build textarea name for views, array('Model', 'fieldname')
        * @param stirng $basepath - base path of project/system
        * @param string $content
        */
        function fckeditor($namepair = array(), $basepath = '', $content = ''){
            $editor_name = 'data';
            foreach ($namepair as $name){
                $editor_name .= "[" . $name . "]";
            }
    
            $oFCKeditor = new FCKeditor($editor_name) ;
            $oFCKeditor->BasePath = $basepath . '/js/fckeditor/' ;
            $oFCKeditor->Value = $content ;
            $oFCKeditor->Create() ;            
        }      
    } 

2) add configuration options as described at `http://docs.fckeditor.ne
t/FCKeditor_2.x/Developers_Guide/Integration/PHP#Configuration_Options
`_ or in /js/fckeditor/fckconfig.js


Usage
`````

1) Add the fck helper to your controller

::

    
    var $helpers = array('Html', 'Form', 'Fck');

2) Use the following line in your views to display the fckeditor

::

    
    echo $fck->fckeditor(array('Model', 'field'), $html->base, $yourContentVariable);

This is my very first attempt at creating a component for Cake so any
comments/suggestions are welcome.

Edit: Thanks to all the comments and suggestions, they helped in
making this a better article

.. _http://docs.fckeditor.net/FCKeditor_2.x/Developers_Guide/Integration/PHP#Configuration_Options: http://docs.fckeditor.net/FCKeditor_2.x/Developers_Guide/Integration/PHP#Configuration_Options
.. meta::
    :title: Simple FCK Editor helper
    :description: CakePHP Article related to WYSIWYG,fck editor,fck,editor,html editor,Helpers
    :keywords: WYSIWYG,fck editor,fck,editor,html editor,Helpers
    :copyright: Copyright 2009 
    :category: helpers

