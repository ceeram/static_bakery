Ability to select and upload multiple files at once - under 5 mins.
===================================================================

by %s on February 15, 2009

You will learn how to implement advanced uploading in your application
the Cake way.


Introduction to possible approaches
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For historical reasons and due to statelessness of HTTP protocol,
browsers don't support selecting multiple files in file input fields
nor support progress indication natively. I consider this a major
usability concern.
Which is why it is a good idea to pimp out your single-select, single
upload field using standard input type="file" tag.
Stage one improvement would be auto-generation of additional standard
input type="file" field with javascript each time user selects a file,
assigning them sequential names, and then handling all at once on
server side upon form submission.

But being smart bakers like ourselves, we will take advantage of the
fact that since flash version 8, a support for a multiple file select
dialog window has been introduced (1) .
Combined with some JavaScript, this gives us the third option. There
are several popular implementations of this approach, but the overall
winner is the component used by Flickr, the YUI Uploader component
from Yahoo.


Why YUI Uploader
~~~~~~~~~~~~~~~~
Compared to the old input type="file" YUI Uploader will cost you:

+ 8kb additional traffic for the upload page. (6kb flash + 2kb
  JavaScript).
+ Flash 9 or newer and JavaScript are required


You will get full control over upload queue. Select multiple files at
once, add more, and indicate upload progress to user.
It is easy to see that the benefits this approach offers exceed the
overhead for majority of apps.
See some demos on YUI Uploader site or Flickr upload page if you're
not yet convinced.


Implementing it in your app in 2 mins
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
After the long intro above, we only have about two minutes left, so we
must act fast.

Here's the instructions:

+ Download `http://developer.yahoo.com/yui/examples/uploader/assets/up
  loader.swf`_ to /app/webroot/swf/uploader.swf
+ Save helper code to /app/views/helpers/yui_uploader.php
+ Save component code to /app/controllers/components/yui_uploader.php
+ Modify your controller to include the controller code below
+ Finally, modify your view to include the view code below



Component Class:
````````````````

::

    <?php 
    	<?php 
    	class YuiUploadComponent extends Object {	
    		var $name = 'YuiUploadComponent';
    	/**
    	 * Restore session from POST field if possible.
    	 * This is required because flash plugin does not send cookies, through which Cake usually keeps track of sessions.
    	 * @param Object &$controller pointer to calling controller
    	 * @author Andrew
    	 */
    		function startup(&$controller) {
    			if(isset($_POST[Configure::read('Session.cookie')])) { 
    				$controller->Session->id($_POST[Configure::read('Session.cookie')]);
    			}
    		}
    	}
    	?>
    ?>



Helper Class:
`````````````

::

    <?php 
    <?php
    class YuiUploaderHelper extends AppHelper {
    /**
     * name property
     *
     * @var string 'YUIUploader'
     * @access public
     */
    	var $name = 'YuiUploader';
    /**
     * helpers property
     *
     * @var array
     * @access public
     */
    	var $helpers = array('Html', 'Javascript', 'Session');
    
    /**
     * undocumented function
     *
     * @param string $settings 
     * @return void
     * @author Andrew
     */
    	function _includeLibraries() {
    		if ($this->__settings['cdn'] == 'google') {
    				return '<!-- Individual YUI JS files --> 
    						<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.6.0/build/yahoo-dom-event/yahoo-dom-event.js"></script> 
    						<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.6.0/build/element/element-beta-min.js"></script> 
    						<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.6.0/build/uploader/uploader-experimental.js"></script>
    						';
    		} elseif ($this->__settings['cdn'] == 'yahoo') {
    				return '<!-- Combo-handled YUI JS files: --> 
    						<script type="text/javascript" src="http://yui.yahooapis.com/combo?2.6.0/build/yahoo-dom-event/yahoo-dom-event.js&2.6.0/build/element/element-beta-min.js&2.6.0/build/uploader/uploader-experimental.js"></script>';
    		} else {
    				return $javascript->link(array('yahoo-dom-event', 'element-beta-min', 'uploader-experimental'));
    		 }
    	}
    	
    /**
     * undocumented function
     *
     * @param string $settings 
     * @return void
     * @author Andrew
     */
    	function uploader($settings='') {
    		$this->__settings = array_merge(array(
    			'cdn' => 'google', // 'google', 'yahoo', or false for hosting locally (you will be responsible for copying the library files).
    			'handlerName' => 'YuiUploaderEventHandler',	
    			'selectButtonId'  => 'selectLink',
    			'overlay' => 'uploaderOverlay',
    		), (array)$settings);
    		
    		ob_start();
    		echo $this->_includeLibraries();
    		?>
    		
    		<!-- YUI Uploader -->
    		<script type="text/javascript">
    			YAHOO.widget.Uploader.SWFURL = "<?php e($this->Html->url('/swf/uploader.swf')) ?>";
    			var uploader = new YAHOO.widget.Uploader("<?php e($this->__settings['overlay']) ?>");
    
    			YAHOO.util.Event.onDOMReady(function () { 
    				var uiLayer = YAHOO.util.Dom.getRegion('<?php e($this->__settings['selectButtonId']) ?>');
    				var overlay = YAHOO.util.Dom.get("<?php e($this->__settings['overlay']) ?>");
    				YAHOO.util.Dom.setStyle(overlay, 'width', uiLayer.right-uiLayer.left + "px");
    				YAHOO.util.Dom.setStyle(overlay, 'height', uiLayer.bottom-uiLayer.top + "px");
    			});
    		
    			uploader.addListener('contentReady', <?php e($this->__settings['handlerName']) ?>.contentReady);
    			uploader.addListener('fileSelect', <?php e($this->__settings['handlerName']) ?>.fileSelect)
    			uploader.addListener('uploadStart', <?php e($this->__settings['handlerName']) ?>.uploadStart);
    			uploader.addListener('uploadProgress', <?php e($this->__settings['handlerName']) ?>.uploadProgress);
    			uploader.addListener('uploadCancel', <?php e($this->__settings['handlerName']) ?>.uploadCancel);
    			uploader.addListener('uploadComplete', <?php e($this->__settings['handlerName']) ?>.uploadComplete);
    			uploader.addListener('uploadCompleteData', <?php e($this->__settings['handlerName']) ?>.uploadResponse);
    			uploader.addListener('uploadError', <?php e($this->__settings['handlerName']) ?>.uploadError);
    		    uploader.addListener('rollOver', <?php e($this->__settings['handlerName']) ?>.rollOver);
    		    uploader.addListener('rollOut', <?php e($this->__settings['handlerName']) ?>.rollOut);
    		    uploader.addListener('click', <?php e($this->__settings['handlerName']) ?>.click);
    		</script>
    <?php
    		$ret = ob_get_contents();
    		ob_end_clean();
    		return $ret;
    	}
    }
    ?>
    ?>

For those who are curious what those do (but not as much in doing a
code dive), the component helps Cake recognize a session, because Cake
relies on cookies, but evil Flash plugin doesn't send them with its
requests.
The helper takes care of loading tiny bit of javascript required
directly from yahoo or google. Naturally, you can choose to host all
files on your servers instead. Remember that if you decide to host
uploader.swf externally, a crossdomain.xml will be required for flash
to work correctly.

Using a model behavior (just like we do here) for uploads is highly
recommended. It allows us to keep the controller very clean and follow
the "skinny controllers, fat models" rule.

Controller Class:
`````````````````

::

    <?php 
    	var $components = array('YuiUploader');
    	var $helpers = array('YuiUploader');
    
    
    	// Handle incoming uploads.
    	function upload() {
    		if ($this->data) {
    			// Uploaded file is saved by a behavior attached to UserImage model (See model code below).
    			if ($this->UserImage->saveAll($this->data['UserImage'])) {
    				$this->Session->setFlash(__('profile updated', true));
    				return $this->_back();
    			} else {
    				$this->Session->setFlash(__('errors in form', true));
    			}
    		} else {
    			$this->data = $this->User->read(null, $this->Auth->user('id'));
    		}
    		$this->_setSelects();
    	}
    ?>



View Template:
``````````````

::

    
    <!-- Create buttons for uploader -->
    <div id="uploaderContainer">
    	<!-- Contain flash piece and overlay 'select files' button  -->
    	<div id="uploaderOverlay" style="position:absolute; z-index:2"></div>
    	<div id="selectFilesLink" style="z-index:1">
    		<img src="/img/btn-select-files.gif" id="selectLink" />
    		<span>photos to be visible on your gallery*</span>
    	</div>
    	<a id="uploadLink" onClick="YuiUploaderEventHandler.upload(); return false;" href="#">
    		<img src="/img/btn-upload-files.gif" />
    	</a>
    </div>
    
    // Define a custom handler for all major upload events. 
    // JQuery and even a jQueryForm plugin are used to show how you could go about creating an upload queue in your actual application.
    <?php echo $javascript->codeBlock('
    	var YuiUploaderEventHandler = { 
    		skeleton: 	\' \
    							<li class="{0} queued-file"> \
    								<fieldset class="browsePictures"> \
    									<div class="image-container"> \
    									<img src="{2}" /> \
    									</div> \
    									<fieldset> \
    										<input id="UserCoverImageId{1}" type="radio" value="{3}" class="radio" name="data[User][cover_image_id]"/> \
    										<label for="UserCoverImageId{1}">Set this photo as your profile pic.</label> \
    									</fieldset> \
    									<fieldset> \
    										<input type="text" id="UserImage{1}Title" value="" maxlength="100" name="data[UserImage][{1}][title]"/> \
    										<textarea id="UserImage{1}Description" rows="6" cols="30" name="data[UserImage][{1}][description]"/> \
    										<input type="hidden" id="UserImage{1}Id" value="{3}" name="data[UserImage][{1}][id]" /> \
    									</fieldset> \
    								</fieldset> \
    							</li>\',
    		printf: function() { 
    		  var num = arguments.length; 
    		  var oStr = arguments[0];   
    		  for (var i = 1; i < num; i++) { 
    		    var pattern = "\\\{" + (i-1) + "\\\}"; 
    		    var re = new RegExp(pattern, "g"); 
    		    oStr = oStr.replace(re, arguments[i]); 
    		  } 
    		  return oStr; 
    		},
    		contentReady: function () {
    				uploader.setAllowMultipleFiles(true);
    				uploader.setFileFilters(new Array({description:"Images", extensions:"*.jpg;*.png;*.gif"}));
    		},
    		fileSelect: function (event) {
    			$("#uploadLink").fadeIn("fast");		
    			YuiUploaderEventHandler.appendUploadQueue(event.fileList);
    		},
    		uploadStart: function (event) {
    			YuiUploaderEventHandler.getQueueItem(event["id"]).find(\'div.image-container\').html(\'<div><div style="height:5px;"></div></div>\');
    		},
    		uploadProgress: function (event) {
    			prog = Math.round(100*(event["bytesLoaded"]/event["bytesTotal"]));
    			YuiUploaderEventHandler.getQueueItem(event["id"]).find(\'div.image-container\').html(\'<div><div style="height:\' + prog + \'px;"></div></div>\');
    		},
    		uploadCancel: function (event) {},
    		uploadComplete: function (event) {
    			YuiUploaderEventHandler.getQueueItem(event["id"]).find(\'div.image-container\').html(\'<div><div style="height:165px;"></div></div>\');
    		},
    		uploadResponse: function (event) {
    			eval(\'event.data = \'+ event.data); // parse JSON response
    			YuiUploaderEventHandler.getQueueItem(event["id"]).find(\'div.image-container\').html(\'<img src="\' + event.data.url + \'" />\');
    			YuiUploaderEventHandler.getQueueItem(event["id"]).attr(\'id\', \'userImage\'+event.data.id);
    
    			//Updating form elements with newly uploaded photo id
    			YuiUploaderEventHandler.getQueueItem(event["id"]).find(\'input[type=hidden]\').val(event.data.id);
    			YuiUploaderEventHandler.getQueueItem(event["id"]).find(\'input[type=radio]\').val(event.data.id);
    			YuiUploaderEventHandler.getQueueItem(event["id"]).attr("class", "");
    			uploader.removeFile(event["id"]);
    
    			YuiUploaderEventHandler.savePhotoCaptions();
    		},
    		uploadError: function () {},
    		rollOver: function () {},
    		rollOut: function () {},
    		click: function () {},
    		queueSize : function () { return $(\'ul.gallery li.queued-file\').size() },
    		upload: function () {
    			$("#loadingBar").fadeIn("slow");
    			if (YuiUploaderEventHandler.queueSize() > 0) {
    				uploader.setSimUploadLimit(1);
    				uploader.uploadAll("'.$html->url('/users/upload').'", "POST", {'.Configure::read('Session.cookie').': "'.$session->id().'"}, "data[UserImage][filename]");
    			} else {
    				YuiUploaderEventHandler.savePhotoCaptions();
    			}
    		},
    		 savePhotoCaptions: function () {
    			        $(\'#UserEditPhotosForm\').ajaxSubmit({ 
    					        success: function(){
    								$(\'#loadingBar\').fadeOut(\'slow\');
    								if (YuiUploaderEventHandler.queueSize() == 0) $("#uploadLink").fadeOut("fast");	
    							}  
    	    			}); 	
    		},
    		 appendUploadQueue: function (entries) {
    			var numExisting = $(\'ul.gallery\').children().size();
    
    			 for(var i in entries) {
    				var entry = entries[i];
    				numExisting++;
    					
    				// Image skeleton is populated and inserted into to the existing gallery until user clicks upload button.
    				if($(\'ul.gallery li.\' + entry.id).size() == 0) {
    					$(\'ul.gallery\').prepend(YuiUploaderEventHandler.printf(YuiUploaderEventHandler.skeleton, entry.id, numExisting, "'.$html->url("/img/generic-user.jpg").'"));
    				}
    			}
    		},
    		getQueueItem: function (rowNum) {
    			return $(\'ul.gallery li.\' + rowNum);
    		}
    	};') ?>
    	
    	
    // Let YuiUploader helper load the uploader flash file and required javascript bits for us.
    <?php echo $yuiUploader->uploader() ?>
    		
    <!-- Gallery of existing uploads -->
    <ul class="gallery">
    	<?php foreach ($this->data['UserImage'] as $key => $img): ?>
    		<li id="userImage<?php e($img['id']) ?>">
    		<fieldset class="browsePictures">
    			<div class="image-container"> 
    				<?php echo $html->image($img['versions']['medium']) ?>
    			</div>
    			
    				<fieldset>
    					<input type="radio" name="data[User][cover_image_id]" <?php if($img['is_cover'])echo 'checked' ?> class="radio" value="<?php e($img['id']) ?>"/><label for="<?php e("UserImage.is_cover") ?>">Set this photo as your profile pic.</label>
    				</fieldset>
    				
    				<?php echo $form->inputs(array('legend' => false,
    												"UserImage.{$key}.id" => array('label' => false, 'div' => false),
    												"UserImage.{$key}.title" => array('label' => false, 'div' => false),
    												"UserImage.{$key}.description" => array('type' => 'textarea', 'label' => false, 'div' => false)
    												)) ?>
    			</fieldset>
    		</li>
    	<?php endforeach ?>
    </ul>

Chances are the view code above will need heavy customization to suit
your project.


Model Class:
````````````

::

    <?php 
    class UserImage extends AppModel {
    /**
     * name property
     *
     * @var string 'UserImage'
     * @access public
     */
    	var $name = 'UserImage';
    /**
     * displayField property
     *
     * @var string 'description'
     * @access public
     */
    	var $displayField = 'description';
    /**
     * validate property
     *
     * @var array
     * @access public
     */
    	var $validate = array(
    		'user_id' => array('numeric'),
    		'model' => array('alphaNumeric'),
    		'foreign_key' => array(
    			'missing' => array('rule' => array('notEmpty'))
    		),
    	);
    /**
     * actsAs property
     *
     * @var array
     * @access public
     */
    	var $actsAs = array(
    		'Polymorphic',
    		'ImageUpload' => array(
    			//FIXME: Flash does not supply proper MIME types for payload but rather defaults to application/octet-stream
    			'allowedMime' => array('image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'application/octet-stream'),
    			'dirFormat' => 'user-images{DS}{$id}',
    			'overwriteExisting' => true,
    			'dirField' => false,
    			'versions' => array(
    				'large' => array(
    					'vBaseDir' => '{IMAGES}',
    					'vDirFormat' => '{dirFormat}',
    					'vFileFormat' => '{$filenameOnly}_large.{$extension}',
    					'callback' => array('resize', 280, 325)
    				),
    				'xlarge' => array(
    					'vBaseDir' => '{IMAGES}',
    					'vDirFormat' => '{dirFormat}',
    					'vFileFormat' => '{$filenameOnly}_xlarge.{$extension}',
    					'callback' => array('resize', 450, 450)
    				)
    			)),
    		'Slugged'
    	);
    /**
     * belongsTo property
     *
     * @var array
     * @access public
     */
    	var $belongsTo = array('User');
    
    }
    ?>

Model uses mi-base skeleton for CakePHP apps by AD7six, which
implements a very elegant behavior for models you select to act as
upload handlers. But since there are many options for that in CakePHP,
you can choose one you prefer.


More information:
~~~~~~~~~~~~~~~~~
YUI Upload component repo: `http://github.com/yui/yui2/tree/master`_

References
~~~~~~~~~~

#. FileReference class `http://livedocs.adobe.com/flash/9.0/ActionScri
   ptLangRefV3/flash/net/FileReference.html`_



.. _http://github.com/yui/yui2/tree/master: http://github.com/yui/yui2/tree/master
.. _http://livedocs.adobe.com/flash/9.0/ActionScriptLangRefV3/flash/net/FileReference.html: http://livedocs.adobe.com/flash/9.0/ActionScriptLangRefV3/flash/net/FileReference.html
.. _http://developer.yahoo.com/yui/examples/uploader/assets/uploader.swf: http://developer.yahoo.com/yui/examples/uploader/assets/uploader.swf
.. meta::
    :title: Ability to select and upload multiple files at once - under 5 mins.
    :description: CakePHP Article related to swfupload,uploader,yui,file upload,Tutorials
    :keywords: swfupload,uploader,yui,file upload,Tutorials
    :copyright: Copyright 2009 
    :category: tutorials

