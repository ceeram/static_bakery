Flow Player Video Helper
========================

This helper will add Flowplayer flash video player to your page. Note:
With this helper you can add only one player. Though, this helper is
very simple, but maybe someone can enhance it.


Installation
~~~~~~~~~~~~

First, you need to download flowplayer. You can get it here:
`http://flowplayer.org/`_
Now extract downloaded file to any folder and:

#. Copy examples/js/flashembed.min.js to app/webroot/js
#. Copy examples/js/css/common.css to app/webroot/css/flash.css


This is video.php. Copy it to app/view/helpers. You need to change
some urls to meet your needs.

Helper Class:
`````````````

::

    <?php 
    <?php
    /*
    		Flow Player Helper (free video player)
    		Requed helpers HTML, Javascript
    */
    Class VideoHelper extends AppHelper {
    
    var $helpers = array('Html', 'Javascript');
    	
    	function loader($loadcss=false) {
    		$out=$this->Javascript->link('flashembed.min');
    		if ($loadcss=true) $out.=$this->Html->css('flash');
    		return $this->output($out);
    		
    	}	
    	
    	function player ($file, $div, $autoplay=false, $width=400, $height=290 ) {
    		
    		if ($autoplay=True) {$autoplay="true";} else $autoplay="false";
    		$out='
    		<script>
    	window.onload = function() {  
    		 flashembed("'.$div.'", 			
    			{
    				src:"http://www.example.com/files/FlowPlayerDark.swf",
    				width: '.$width.', 
    				height: '.$height.'
    			},
    			
    			{config: {   
    				autoPlay:'.$autoplay.',
    				controlBarBackgroundColor:"0x2e8860",
    				initialScale: "scale",
    				videoFile: "'.$file.'"
    			}} 
    		);
    	}
    	</script>	
    		';
    		return $this->output($out);
    	}
    }
    
    ?>
    ?>



How to use
~~~~~~~~~~

You need to load Html and Javascript helpers in app_controller or any
over controller. Next we need to initialize player in your template.

View Template:
``````````````

::

    
    ...
    <head>
    <?php
    echo $video->loader(true);
    echo $video->player("http://www.example.com/files/godfother.flv", "example", false, 720, 576);
    ?>
    </head>
    ...

And now we need to add this example div to our view page there video
will be played

View Template:
``````````````

::

    
    <?php foreach($movies as $movie): ?>
    	<h3><?php e($movie['Movie']['title']); ?></h3>
    <div id="example"></div>
    <?php endforeach; ?>

Thats all, falks!

.. _http://flowplayer.org/: http://flowplayer.org/

.. author:: Stas
.. categories:: articles, helpers
.. tags:: flv,Helpers

