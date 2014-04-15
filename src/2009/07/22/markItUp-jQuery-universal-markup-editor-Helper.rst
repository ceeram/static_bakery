markItUp! jQuery universal markup editor Helper
===============================================

by Jay.Salvat on July 22, 2009

markItUp! is a JavaScript plugin built on the jQuery library. It
allows you to turn any textarea into a markup editor. Html, Textile,
Wiki Syntax, Markdown, BBcode or even your own Markup system can be
easily implemented. Find here some helpers to create, remove, switch
markItUp! editor in your CakePhp projects.
This article requires to be familiar with markItUp! concept and
settings. I won't explain them here.
Please, visit the official markItUp! website for info and
documentation:
`http://markitup.jaysalvat.com/documentation/`_
First of all:

+ Download latest release of markItUp! at
  `http://markitup.jaysalvat.com/downloads/`_.
+ Add 'markitup' folder in /webroot/js/.
+ Download latest release of jQuery at `http://jquery.com/`_.
+ Add jQuery script in a new 'jquery' folder in /webroot/js/.
+ Create a new markitup.php file in /views/helpers/ and copy/paste the
  helper code below.



Usage
~~~~~

markItUp! generation helper
```````````````````````````

::

    $markitup->editor($fieldName, [$options]);

It basically behaves as the $form->input() helper but accepts more
options.
By default these options are set to:

+ set => 'default', the folder of the set.
+ settings' => 'mySettings', the name of the object in the set.
+ skin' => 'markitup', the skin of the editor.
+ parser => '', path to a controller/action to render the preview.



View Template:
``````````````

::

    
    <h1>My Post form</h1>
    <?php echo $form->create('Posts'); ?>
    <?php echo $markitup->editor('Posts.article'); ?>
    <?php echo $form->end('Validate'); ?>



Removal link helper
```````````````````

::

    $markitup->destroy($title, $fieldName, [$htmlAttributes, $confirmMessage]);

Creates a link to remove markItUp! from a textfield.
It basically behaves as the $html->link() helper but needs $fieldName.


View Template:
``````````````

::

    
    <h1>My Post form</h1>
    <?php echo $markitup->destroy("Remove markItUp! from the textarea", 'Posts.article') ?>
    <?php echo $form->create('Posts'); ?>
    <?php echo $markitup->editor('Posts.article'); ?>
    <?php echo $form->end('Validate'); ?>



Creation link helper
````````````````````

::

    $markitup->create($title, $fieldName, [$options, $htmlAttributes, $confirmMessage]);

Creates a link to add markItUp! to an existing textfield or switching
settings.
It basically behaves as the $html->link() helper but needs $fieldName.
By default the options are:

+ set => 'default', the folder of the toolbar set
+ settings' => 'mySettings', the name of the settings in the set.js
  file
+ skin' => 'markitup', the skin of the editor
+ parser => '', path to a controller/action to render the preview.



View Template:
``````````````

::

    
    <h1>My Post form</h1>
    <?php echo $markitup->create("Add markItUp! to the textarea", 'Posts.article'); ?>
    <?php echo $form->create('Posts'); ?>
    <?php echo $form->input('Posts.article'); ?>
    <?php echo $form->end('Validate'); ?>



Insertion link helper
`````````````````````

::

    $markitup->insert($title, [$fieldName], $content, [$htmlAttributes, $confirmMessage]);

Creates a link to insert content in a markItUp! editor. It basically
behaves as the $html->link() helper but needs $fieldName and $content.

+ $fieldName is optional, if it's null, the focused editor will
  receive the content.
+ $content is either a string or an array of markItUp! properties
  (openWith, closeWith, replaceWith, placeHolder, ...).

See markItUp! documentation for more information:
`http://markitup.jaysalvat.com/documentation`_

View Template:
``````````````

::

    
    <h1>My Post form</h1>
    <?php echo $markitup->insert("Insert bold text", 'Posts.article',
                                       array( 'openWith' => '<strong>',
                                              'closeWith' => '</strong>',
                                              'placeHolder' => 'Bold text here'));
    ?>
    <?php echo $form->create('Posts'); ?>
    <?php echo $markitup->editor('Posts.article'); ?>
    <?php echo $form->end('Validate'); ?>



Preview parsing helper
``````````````````````

::

    $markitup->parse($content, [$parser]);

Renders the content with a markup parser.
For more information, see BBcode example below.


Preview
~~~~~~~
markItUp! uses its own template system to display Html built-in
preview. You can find and customize this template in
/webroot/js/markitup/templates/.
In order to keep MVC structure and CakePhp logic, you can set a
controller and action to preview the content.
So create and edit a 'preview.ctp' in the 'layouts' folder and proceed
as below:


View Template:
``````````````

::

    
    <?php echo $form->create('Posts'); ?>
    <?php echo $markitup->editor('Posts.article', array('parser' => '/posts/preview/')); ?>
    <?php echo $form->end('Validate'); ?>



Controller Class:
`````````````````

::

    <?php 
    class postsController extends AppController{
    	var $name = 'posts';
    	var $helpers = array('Markitup');
    
    	function index() {
    		// ...
    	}
    	
    	function preview() {
    		$this->layout = 'preview';
    		$this->set('content', $this->data);
    	}
    }
    ?>

Edit your preview.ctp file...

View Template:
``````````````

::

    
    <?php echo $content ?>



markItUp! and BBcode Example
~~~~~~~~~~~~~~~~~~~~~~~~~~~~
By default markItUp! is provided as a Html editor, but you can
download various kinds of sets and parsers: Textile, Markdown,
BBcode...
For the example, let's set markItUp! as a BBcode editor.

+ Download the BBcode set at
  `http://markitup.jaysalvat.com/downloads/`_.
+ Add the BBcode set in /webroot/js/markitup/sets/bbcode/
+ Download a BBcode parser at
  `http://markitup.jaysalvat.com/downloads/`_.
+ Drop the .php file in /vendor/bbcode/



View Template:
``````````````

::

    
    <h1>My Post form</h1>
    <?php echo $form->create('Posts'); ?>
    <?php echo $markitup->editor('Posts.article',
                                     array( 'set' => 'bbcode',
                                            'parser' => '/posts/preview/bbcode'));
    ?>
    <?php echo $form->end('Validate'); ?>



Controller Class:
`````````````````

::

    <?php 
    class postsController extends AppController{
    	var $name = 'posts';
    	var $helpers = array('Markitup');
    
    	function index() {
    		// ...
    	}
    	
    	function preview($parser = '') {
    		$this->layout = 'preview';
    		$this->set('parser', $parser);		
    		$this->set('content', $this->data);
    		$this->autoRender = true;
    	}
    }
    ?>

Edit your preview.ctp file...

View Template:
``````````````

::

    
    <?php echo $markitup->parse($content, $parser) ?>

This Helper is designed to be used with several kinds of parsers in a
same project.
Edit the Helper at line 100 to work with the BBcode parser added to
the /vendor folder.

::

    
    switch($parser) {
           case 'bbcode':
               // App::import('Vendor', 'bbcode', array('file' => 'markitup.bbcode-parser'));
               // $parsed = myBbcodeParser($content);        
               break;
           case 'textile':
               // App::import('Vendor', 'textile', array('file' => 'myTextileParser'));
               // $parsed = myTextileParser($content);        
               break;
    	//...



The code
~~~~~~~~
Copy and paste the code below in /views/helpers/markitup.php

Helper Class:
`````````````

::

    <?php 
    <?
    /**
     * markItUp! Helpers
     * @author Jay Salvat
     * @version 1.0
     *
     * Download markItUp! at:
     * http://markitup.jaysalvat.com
     * Download jQuery at:
     * http://jquery.com
     */
    class MarkitupHelper extends AppHelper {
        var $helpers = array('Html', 'Form', 'Javascript');
        
        /**
         * Generates a form textarea element complete with label and wrapper div with markItUp! applied.
         * @param  string $fieldName This should be "Modelname.fieldname"
         * @param  array $settings
         * @return string  An <textarea /> element.
         */
        function editor($name, $settings = array()) {
            $config = $this->_build($settings);
            $settings = $config['settings'];
            $default = $config['default'];
            $textarea = array_diff_key($settings, $default);
            $textarea = am($textarea, array('type' => 'textarea'));
            $editor = $this->Form->input($name, $textarea);
            $id = '#'.parent::domId($name);
            $editor.= $this->Javascript->codeBlock('jQuery.noConflict();jQuery(function() { jQuery("'.$id.'").markItUp('.$settings['settings'].', { previewParserPath:"'.$settings['parser'].'" } ); });');
            return $this->output($editor);
        }
    
        /**
         * Link to build markItUp! on a existing textfield
         * @param  string $title The content to be wrapped by <a> tags.
         * @param  string $fieldName This should be "Modelname.fieldname" or specific domId as #id.
         * @param  array  $settings
         * @param  array  $htmlAttributes Array of HTML attributes.
         * @param  string $confirmMessage JavaScript confirmation message.
         * @return string An <a /> element.    
         */
        function create($title, $fieldName = "", $settings = array(), $htmlAttributes = array(), $confirmMessage = false) {
            $id = ($fieldName{0} === '#') ? $fieldName : '#'.parent::domId($fieldName);
            
            $config = $this->_build($settings);
            $settings = $config['settings'];
            $htmlAttributes = am($htmlAttributes, array('onclick' => 'jQuery("'.$id.'").markItUpRemove(); jQuery("'.$id.'").markItUp('.$settings['settings'].', { previewParserPath:"'.$settings['parser'].'" }); return false;'));
            return $this->Html->link($title, "#", $htmlAttributes, $confirmMessage, false);
        }    
    
        /**
         * Link to destroy a markItUp! editor from a textfield
         * @param string  $title The content to be wrapped by <a> tags.
         * @param string  $fieldName This should be "Modelname.fieldname" or specific domId as #id.
         * @param array   $htmlAttributes Array of HTML attributes.
         * @param string  $confirmMessage JavaScript confirmation message.
         * @return string An <a /> element.    
         */
        function destroy($title, $fieldName = "", $htmlAttributes = array(), $confirmMessage = false) {
            $id = ($fieldName{0} === '#') ? $fieldName : '#'.parent::domId($fieldName);
            $htmlAttributes = am($htmlAttributes, array('onclick' => 'jQuery("'.$id.'").markItUpRemove(); return false;'));
            return $this->Html->link($title, "#", $htmlAttributes, $confirmMessage, false);
        }
    
        /**
         * Link to add content to the focused textarea
         * @param string  $title The content to be wrapped by <a> tags.
         * @param string  $fieldName This should be "Modelname.fieldname" or specific domId as #id.
         * @param mixed   $content String or array of markItUp! options (openWith, closeWith, replaceWith, placeHolder and more. See markItUp! documentation for more details : http://markitup.jaysalvat.com/documentation
         * @param array   $htmlAttributes Array of HTML attributes.
         * @param string  $confirmMessage JavaScript confirmation message.
         * @return string An <a /> element.    
         */
        function insert($title, $fieldName = null, $content = array(), $htmlAttributes = array(), $confirmMessage = false) {
            if (isset($fieldName)) {
                $content['target'] = ($fieldName{0} === '#') ? $fieldName : '#'.parent::domId($fieldName);
            }
            if (!is_array($content)) {
                $content['replaceWith'] = $content;
            }
            $properties = '';
            foreach($content as $k => $v) {
                $properties .= $k.':"'.addslashes($v).'",';
            }
            $properties = substr($properties, 0, -1);
            
            $htmlAttributes = am($htmlAttributes, array('onclick' => '$.markItUp( { '.$properties.' } ); return false;'));
            return $this->Html->link($title, "#", $htmlAttributes, $confirmMessage, false);
        }
    
        /**
         * Parser to use in the preview
         * @param string  $content The content to be parsed.
         * @return string Parsed content.    
         */
        function parse($content, $parser = '') {
        // This Helper is designed to be used with several kinds of parser
        // in a same project.
            // Drop your favorite parsers in the /vendor/ folder and edit lines below.
            switch($parser) {
                case 'bbcode':
                    // App::import('Vendor', 'bbcode', array('file' => 'myFavoriteBbcodeParser'));
                    // $parsed = myFavoriteBbcodeParser($content);        
                    break;
                case 'textile':
                    // App::import('Vendor', 'textile', array('file' => 'myFavoriteTextileParser'));
                    // $parsed = myFavoriteTextileParser($content);        
                    break;
                case 'markdown':
                    // App::import('Vendor', 'markdown', array('file' => 'myFavoriteMarkDownParser'));
                    // $parsed = myFavoriteMarkDownParser($content);            
                    break;
                default:
                    // App::import('Vendor', 'favorite', array('file' => 'myFavoriteFavoriteParser'));
                    // $parsed = myFavoriteFavoriteParser($content);
            }
            return $content;
        }
        
        /**
         * Adds jQuery and markItUp! scripts to the page
         */    
        function beforeRender() {
            $this->Javascript->link('jquery/jquery.js', false);
            $this->Javascript->link('markitup/jquery.markitup.js', false);
        }
    
        /**
         * Private function.
         * Builds the settings array and add includes.
         */    
        function _build($settings) {
            $default = array(   'set' => 'default', 
                                'skin' => 'markitup', 
                                'settings' => 'mySettings',
                                'parser' => '');
            $settings = am($default, $settings);
            if ($settings['parser']) {
                $settings['parser'] = $this->Html->url($settings['parser']);
            }                
            $this->Javascript->link('markitup/sets/'.$settings['set'].'/set.js', false);
            $this->Html->css('/js/markitup/skins/'.$settings['skin'].'/style.css', null, null, false);
            $this->Html->css('/js/markitup/sets/'.$settings['set'].'/style.css', null, null, false);
    
            return array('settings' => $settings, 'default' => $default);
        }
    }
    ?>
    ?>

Enjoy,
Feel free to correct my english and post comments.


.. _http://markitup.jaysalvat.com/documentation/: http://markitup.jaysalvat.com/documentation/
.. _http://jquery.com/: http://jquery.com/
.. _http://markitup.jaysalvat.com/documentation: http://markitup.jaysalvat.com/documentation
.. _http://markitup.jaysalvat.com/downloads/: http://markitup.jaysalvat.com/downloads/

.. author:: Jay.Salvat
.. categories:: articles, helpers
.. tags::
editor,textile,BBCode,markup,markitup,markdown,texy,xbbcode,Helpers

