xStandard wysiwyg Helper
========================

by DaddyCool on April 12, 2008

Based on the workaround made for fckEditor, I've made a little helper
to render easily a wysiwyg editor that IS xHTML 1.1 compliant :) This
helper assumes you are using the prototype.js framework. xStandard
If you ever wanted to use a wysiwyg that is xHTML compliant, here is
an easy way to implement it in every view you would like by a little 1
line code.

I'll assume you already link your webpages to the prototype.js, if
not, go grab a copy for yourself :
`http://www.prototypejs.org/download`_ And I've got ride of the
unicode check from xStandard, I'll presume you already use only utf-8
to encode your pages ??

XStandard Lite as the problem of asking for a plugin, I don't know yet
if there is one for linux based browser, but the group say it is
compliant for windows and Mac based browser. But, the installation of
another browser plugin is worth it. The lite version is completely
free and you have much configuration available.

Ok, now here's where the fun begins. Just create a helper called
xstandard.php and put this code into it :


Helper Class:
`````````````

::

    <?php 
    class XstandardHelper extends AppHelper
    {
        var $helpers = array('Javascript');
        var $count = 0;
        var $objectId = 'xstandard';
    /*
     * General definitions
     * Insert your web site links, specifically the url that links to the /webroot directory.
     * Must be absolute
     */
        var $baseUrl = 'http://yoursite.net';
        
    /**
     * Generate the javascript to include xStandard wysiwyg in place of textarea(s).
     *
     * @param string $formId Required! ID of the form inside which the textarea to replace is
     * @param mixed $textAreaId Required! ID of the textarea(s) to replace. Can be a string or an array if multiple texarea
     * @param string $objectWidth Specify the width of box
     * @param string $objectHeight Specify the height of box
     * @return mixed Sorted array
     */
        function load($formId, $textAreaId, $objectWidth = '100%', $objectHeight = '400'){
    //******You can specify as many settings as you'd like here******
        	$specific_xstandard_params = aa(
    	    		'Base', "$this->baseUrl/img",
    	    		'CSS', "$this->baseUrl/css/cake.generic.css",
    	    		'EditorCSS', "body{background:white; color:black;}",
    	    		'Lang', "fr",
    	    		'BackgroundColor', "#003d4c",
    	    		'IndentOutput', "true",
    	    		'ToolbarWysiwyg', "undo,redo,, ordered-list, unordered-list, definition-list,, draw-layout-table, draw-data-table, image, separator, hyperlink,, source, preview, screen-reader, help"
    	    	);
    //******Finished editing ;)
        	$this->Javascript->cacheEvents(false,true);
        	$params = '';
        	foreach($specific_xstandard_params as $k => $v){
        		$params .= "\t"."obj.appendChild( new Element('param', {name: '$k', value: '$v'}));"."\n";
        	}
        	$this->Javascript->codeBlock("
    function renderXstandard(textAreaId, objectId, objectWidth, objectHeight){
    /*creation of the elements*/
    	var textArea = $(textAreaId);
    	var obj = new Element('object', {type: 'application/x-xstandard', id: objectId, width:objectWidth, height:objectHeight});
    	obj.appendChild( new Element('param', {name: 'Value', value: textArea.value}));
    $params
    /*generation into the page*/
    	textArea.insert({before : obj});
    	obj.appendChild	(textArea);
    }");
    		if(!is_array($textAreaId)){
    			$textAreaId = a($textAreaId);
    		}
    		$function_called = '';
    		foreach ($textAreaId as $id){
    			$this->_generate_wysiwyg($id, $objectWidth, $objectHeight);
    			$function_called .= 'Sync'.$id.'(); ';
    		}
    		$this->Javascript->event($formId, 'submit', $function_called);
    		
    		return $this->Javascript->event('window', 'load', "\n".$this->Javascript->getCache()."\n", array('inline' => false));
        }
        
        function _generate_wysiwyg($textAreaId, $objectWidth, $objectHeight){
    		$this->count++;
        	$objectId = $this->objectId.$this->count;
    
    		$this->Javascript->codeBlock("
    /*Generated for each textarea*/
    renderXstandard('$textAreaId', '$objectId', '$objectWidth', '$objectHeight');
    function Sync$textAreaId() {
    	$('$textAreaId').value = $('$objectId').value;
    }");
        }
    }
    ?>

I've tried to be as clear as possible concerning the editing you may
have to do inside the file. I've used as much the javascript helper as
possible. The javascript content will be sent into the head of your
page, where you put the $script_for_layout. I suggest you put that
call after you call prototype.js .

You can get a complete list of all the params you could change/add
here `http://xstandard.com/en/documentation/xstandard-dev-
guide/api/`_.

Now, when you want to add this wysiwyg to a textarea (or many of
them), you just have to add this line into your view :

View Template:
``````````````

::

    
    <?php echo $xstandard->load('PageEditForm', 'PageContent');?>

or

View Template:
``````````````

::

    
    <?php echo $xstandard->load('PageEditForm', array('PageContent','SecondPageContent');?>

You can either specify a unique textarea or specify hundreds of them,
it's the same. The second thing is that you can put that line anywhere
into your view, at the top, bottom, left, right, it will render the
wysiwyg. (That was a tricky one with xStandard).

And that's it !!
Just try to load your page and if you've never used xStandard wysiwyg,
you'll have those little pop-up in top of your browser that'll ask you
to install the plugin. Isn't it merveilleux ?

What it does : It will add the < object > rendering the wysiwyg before
your textarea and hide it (the textarea). On submit, your data will be
transfered from the wysiwyg editor to the original textarea.

You almost have nothing to change into your page to use that, here is
an exemple using a view generated throught the console :

View Template:
``````````````

::

    
    <div class="pages form">
    <?php echo $form->create('Page');?>
    	<fieldset>
     		<legend><?php __('Edit Page');?></legend>
    	<?php
    		echo $form->input('id');
    		echo $form->input('name');
    		echo $form->input('content');	?>
    	</fieldset>
    <?php echo $form->end('Submit');?>
    </div>
    
    <?php echo $xstandard->load('PageEditForm', array('PageContent'));?>

The only tricky part is that you must specify the ID of the form and
of the textarea(s). I would have like to make this helper even more
automatic. Maybe some day...

Tested on Firefox and Opera, couldn't verify throught IE, mine is all
dead.

Your comments are welcome.
David


.. _http://xstandard.com/en/documentation/xstandard-dev-guide/api/: http://xstandard.com/en/documentation/xstandard-dev-guide/api/
.. _http://www.prototypejs.org/download: http://www.prototypejs.org/download

.. author:: DaddyCool
.. categories:: articles, helpers
.. tags:: WYSIWYG,editor,xhtml,Helpers

