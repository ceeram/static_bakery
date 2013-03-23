CSS Menu Helper
===============

by %s on March 16, 2007

This is a helper and CSS file for displaying a dropdown menu on your
site.
This can be used to display a dropdown menu with sub menus. There are
currently 3 kinds of menu: menus where elements are listed down, and
sub menus pop right, menus where submenus pop left, and menus where
the top level elements are listed horizontally and sub menus pop right
(except for the first, which pops down).

Demo site: `http://vectorjohn.com/cake/`_
The way the CSS works now, you can only have a maximum of 5 levels
deep in your menu structure. If you have more, they are all displayed
when you hover over the 5th level menu item. The limit can be
increased by adding 2 lines to the CSS file.

To use it, just put the file css_menu.php in your helpers directory,
and put the file css_menu.css in your webroot/css directory. You also
need the 3 arrow image files, which are referenced by the CSS file.
You could make your own, but here they are. They also belong in
webroot/css:
`http://vectorjohn.com/cake/files/arrow-
left.png`_`http://vectorjohn.com/cake/files/arrow-
right.png`_`http://vectorjohn.com/cake/files/arrow-down.png`_
Then in a view, you can do:

::

    
    echo $cssMenu->menu($menu,'down');

To make a horizontal menu, for example. The other options are 'left'
and 'right'. The $menu argument is explained in the comments of the
code.

If you want to use it in any view, you can add 'CssMenu' to your
$helpers array.

It would be easy to make a menu model to go along with this, and store
a menu structure in the database. I might just do that.

Here is the code for the helper, css_menu.php:

Helper Class:
`````````````

::

    <?php 
    /*
     * CSS menu helper.  
     * Author: John Reeves.
     */
    class CssMenuHelper extends Helper{
    
    	var $helpers = array('Html');
    	
    	/*
    	 * display a menu list.
    	 * @arg $data: a nested associative array.  The keys are the text that
    	 * 	is displayed for that menu item.  If the value is an array, it is
    	 *	treated as a sub menu, with the same format.  Otherwise it is 
    	 *	interpreted as a URL to be used for a link.
    	 * @arg $type: the type of array.  Can be right, left, or down.
    	 */
    	function menu($data=array(), $type='right'){
    		global $cm_css_inc;
    		$out ='';
    		if($cm_css_inc != true){
    			$cm_css_inc = true;
    			$out .= $this->Html->css('css_menu');
    		}
    		return $this->output($out . $this->_cm_render($data, $type));
    	}
    
    	/* render a menu. 
    	 * This is a helper for recursion.  The arguments are the 
    	 * same as for $this->menu().
    	 */
    	function _cm_render($data=array(), $type='right'){
    		$out='';
    		if($data == array()) return;
    		if(is_array($data)){
    			$out .= "<ul class=\"css_menu cm_$type\">\n";
    			foreach($data as $key => $item){
    				if(is_array($item)){
    					$out .= '<li class="parent">'. $key. "\n";
    					$out .= $this->_cm_render($item, $type);
    					$out .= "</li>\n";
    				}else{
    					$out .= '<li><a href="'. $item. '">'. $key. '</a></li>'. "\n";
    				}
    			}
    			$out .=  "</ul>\n";
    		}
    		return $out;
    	}
    }
    ?>

Here is the CSS file, css_menu.css, to go in your webroot/css folder:

::

    
    /*
     * CSS for css menu helper.
     * Author: John Reeves
     * Credit given to Jake Gordon, author of Nice Menus module
     * for Drupal, for much of the idea.
     */
    ul.css_menu,
    ul.css_menu ul{
    	list-style: none;
    	margin: 0;
    	padding: 1px;
    }
    
    ul.css_menu ul{
    	display: none;
    	position: absolute;
    	margin-right: 0;
    	z-index: 5;
    }
    
    ul.css_menu li{
    	margin: 0;
    	padding: .1em;
    }
    
    ul.css_menu li{
    	float: left;
    	border: 1px solid black;
    	background-color: #99fefd;
    	width: 6em;
    	position: relative;
    	left: 2px;
    	top: 0;
    	
    }
    
    ul.css_menu ul li{
    	display: block;
    }
    
    ul.css_menu:after{
    	clear: both;
    	display: block;
    	height: 0;
    	visibility: hidden;
    }
    
    ul.css_menu li:hover{
    	background-color: #66cbca;
    }
    
    /*
     * Hide sub menus that are not hovered over.
     * It only works for 5 levels deep.  If for some reason you need
     * more, it should be easy to see how to copy the last selector and
     * add one more li:hover.  Same goes for the display: block; part below.
     */
    ul.css_menu ul,
    ul.css_menu li:hover ul ul,
    ul.css_menu li:hover li:hover ul ul,
    ul.css_menu li:hover li:hover li:hover ul ul{
      display: none; 
    } 
    
    /* show hovered submenus */
    ul.css_menu li:hover ul,
    ul.css_menu li:hover li:hover ul,
    ul.css_menu li:hover li:hover li:hover ul,
    ul.css_menu li:hover li:hover li:hover li:hover ul{
    	display: block;
    }
    
    /* RIGHT type menus */
    
    ul.cm_right li{
    	float: none;
    }
    
    ul.cm_right li.parent:hover,
    ul.cm_right li li.parent:hover{ 
    	background: #66cbca url(arrow-right.png) right center no-repeat;
    }
    
    ul.cm_right li.parent,
    ul.cm_right li li.parent{ 
    	background-image: url(arrow-right.png);
    	background-position: right center;
    	background-repeat: no-repeat;
    }
    
    ul.cm_right li ul,
    ul.cm_right li ul li.parent ul{
    	left: 5.9em;
    	top: -2px;
    }
    
    
    /* LEFT type menus */
    
    ul.cm_left li{
    	float: none;
    	padding-left: 15px;
    }
    
    ul.cm_left li.parent:hover,
    ul.cm_left li li.parent:hover{ 
    	background: #66cbca url(arrow-left.png) left center no-repeat;
    }
    
    ul.cm_left li.parent,
    ul.cm_left li li.parent{ 
    	background-image: url(arrow-left.png);
    	background-position: left center;
    	background-repeat: no-repeat;
    }
    
    ul.cm_left li ul,
    ul.cm_left li ul li.parent ul{
    	left: -7.8em;
    	top: -2px;
    }
    
    
    
    /* DOWN type menus */
    ul.cm_down li ul{
    	top: 1.5em;
    	left: -4px;
    }
    
    ul.cm_down li ul li.parent ul{
    	left: 5.9em;
    	top: -0.1em;
    }
    
    ul.cm_down li.parent:hover{
    	background: #66cbca url(arrow-down.png) right center no-repeat;
    }
    
    ul.cm_down li.parent{
    	background-image: url(arrow-down.png);
    	background-position: right center;
    	background-repeat: no-repeat;
    }
    
    ul.cm_down li li.parent:hover{ 
    	background: #66cbca url(arrow-right.png) right center no-repeat;
    }
    
    ul.cm_down li li.parent{ 
    	background-image: url(arrow-right.png);
    	background-position: right center;
    	background-repeat: no-repeat;
    }
    
    



.. _http://vectorjohn.com/cake/files/arrow-down.png: http://vectorjohn.com/cake/files/arrow-down.png
.. _http://vectorjohn.com/cake/files/arrow-left.png: http://vectorjohn.com/cake/files/arrow-left.png
.. _http://vectorjohn.com/cake/files/arrow-right.png: http://vectorjohn.com/cake/files/arrow-right.png
.. _http://vectorjohn.com/cake/: http://vectorjohn.com/cake/
.. meta::
    :title: CSS Menu Helper
    :description: CakePHP Article related to menu,submenu,drop down,Helpers
    :keywords: menu,submenu,drop down,Helpers
    :copyright: Copyright 2007 
    :category: helpers

