Textile Editor Helper for Cake
==============================

by tclineks on May 03, 2007

A port of TEH(Textile Editor Helper) to Cake. See Original here: [url]
http://slateinfo.blogs.wvu.edu/plugins/textile_editor_helper[/url]


Textile Editor Helper for CakePHP
`````````````````````````````````
The folks over at slate have put together a nice Javascript Textile
editor and this is a slight modification and a helper to ease its
integration with a Cake site.

Original:
`http://slateinfo.blogs.wvu.edu/plugins/textile_editor_helper`_
Live demo (of original):
`http://slateinfo.blogs.wvu.edu/plugins/textile_editor_helper/demo`_
Rossoft's HeadHelper is a dependancy for this helper:
`http://rossoft.wordpress.com/2006/03/28/register-head-tags-from-
helpers-2/`_
As I'm a jQuery user I've changed to it as the driver for intial
attachment.

Helper Source is on next page or here:
`http://www.divshare.com/download/542720-146`_ - Also Includes
modified css and js, as well as image files.

Installation:
+++++++++++++
1. copy relevant teh folders to js, css and img
2. copy teh.php to (app)/views/helpers
3. add to controller's helpers var


Usage:
++++++

View Template:
``````````````

::

    <?php $teh->add('your jQuery DOM query here', 'extended'); ?>

'simple' is an optional second parameter.

Examples:
To make all textarea's with class 'teh' replaced with the simple
style:

View Template:
``````````````

::

    <?php $teh->add('textarea.teh', 'simple'); ?>

A specific extended textarea (with id 'toTeh'):

View Template:
``````````````

::

    <?php $teh->add('textarea#toTeh', 'extended'); ?>




Helper Class:
`````````````

::

    <?php 
    /**
     * TEH Helper Class
     * Simplifies the addition of TEH(Textile Editor Helper)
     * @see http://slateinfo.blogs.wvu.edu/plugins/textile_editor_helper
     * @requires rossofts HeadHelper http://rossoft.wordpress.com/2006/03/28/register-head-tags-from-helpers-2/
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    /**
     * TEH Helper class
     */
    class TehHelper extends AppHelper {
    	var $helpers = array('Head');
    	var $initials_added = false;
    	var $textareas = array();
    
    	/**
    	 * Attaches the TEH JavaScript
    	 * 
    	 * @see http://docs.jquery.com/Selectors
    	 * @param $elementSelector string jQuery-style DOM query to find relevant textareas
    	 * @param $editorType string typeof TEH editor to use, 'simple' or 'extended'
    	 */
    	function add($elementQuery, $editorType) {
    		if (!$this->initials_added) {
    			// Uncomment if you would like this to attach jQuery as well
    			$this->Head->register_js('http://code.jquery.com/jquery.js');
    			$this->Head->register_js('teh/textile-editor');
    			$this->Head->register_css('teh/textile-editor');
    			$this->initials_added = true;
    		}
    
    		$this->Head->register_jsblock(
    '$(document).ready(function() {
    	$("' . $elementQuery . '").each(function() {
    		if (this.id !== undefined) {
    		edToolbar(this.id, "' . $editorType . '");
    		}
    	});
    });');
    
    	}
    }
    ?>

For images, css and Javascript:
`http://www.divshare.com/download/542720-146`_
`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _http://slateinfo.blogs.wvu.edu/plugins/textile_editor_helper: http://slateinfo.blogs.wvu.edu/plugins/textile_editor_helper
.. _http://www.divshare.com/download/542720-146: http://www.divshare.com/download/542720-146
.. _http://slateinfo.blogs.wvu.edu/plugins/textile_editor_helper/demo: http://slateinfo.blogs.wvu.edu/plugins/textile_editor_helper/demo
.. _Page 2: :///articles/view/4caea0de-2534-4153-bdeb-4dff82f0cb67/lang:eng#page-2
.. _http://rossoft.wordpress.com/2006/03/28/register-head-tags-from-helpers-2/: http://rossoft.wordpress.com/2006/03/28/register-head-tags-from-helpers-2/
.. _Page 1: :///articles/view/4caea0de-2534-4153-bdeb-4dff82f0cb67/lang:eng#page-1
.. meta::
    :title: Textile Editor Helper for Cake
    :description: CakePHP Article related to textile,teh,textile editor helpe,Helpers
    :keywords: textile,teh,textile editor helpe,Helpers
    :copyright: Copyright 2007 tclineks
    :category: helpers

