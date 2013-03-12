

Asset Mapper
============

by %s on January 23, 2008

[b]Map your entire web application's asset (JavaScript/CSS) includes
with one file![/b] Easier way to manage JavaScript and CSS includes.
Built on top of Asset Packer which compacts files and put them through
CSS Tidy and JSMin.


Instructions
````````````

#. Have a running installation of CakePHP (v1.2).
#. `Download CakePHP Asset Mapper (zip file)`_ .
#. Unpack asset files in new folder called, "asset" in helpers folder
   /app/views/helpers/asset/
#. Unpack css_tidy and jsmin to vendors folder /vendors/
#. Include AssetMapper Helper in your App Controller
   /app/controller/app_controller.php
#. Define Rules for including assets in Asset Map file
   /app/views/helpers/asset/asset_map.php
#. Output the CSS and JavaScript files in your view using
   $styles_for_layout and $javascript_for_layout variables.

View Readme.txt that comes with the download for detailed
instructions.

Asset Mapper will not be enabled by default. It is enabled only when
debug is set to "0". See developmentMode in asset_mapper.php to
change.

Here is an example of a mapping rule:

::

    $this->AssetRule->create();  
    $this->AssetRule->compact->css = array('site','global');  
    $this->AssetRule->compact->scripts = array('jquery', 'ui.datepicker');  
    $this->AssetRule->runRule();

Accross the entire site, this would compact the site.css and
global.css files together and run them through CSS Tidy. jQuery.js and
ui.datepicker.js would be compacted together and then ran through
JSMin.

Great, this simplifies a complex process of compacting and minimizing
- while still giving you the freedom to include scripts normally.
Compacting lowers the amount of http requests you website makes,
increasing website performance. JSMin and CSS Tidy strip comments and
whitespace leaving your files much smaller in size.

You can create a rule to include files in a specific controller and/or
action with AssetRule->map and AssetRule->controller:

::

    $this->AssetRule->create();  
    $this->AssetRule->controller = 'posts'; 
    $this->AssetRule->action = 'admin_add'; 
    $this->AssetRule->compact->css = array('user');  
    $this->AssetRule->runRule();


This would only include your 'user.css' file in the, 'posts'
controller - with the action, 'admin_add'. The css file would be
compacted with the others.

Some scripts like TinyMCE, include other files (so it can't be
compacted with the others) and requires a codeblock to be initialized.
This configuration satisfies TinyMCE:

::

    $this->AssetRule->create();  
    $this->AssetRule->action = 'admin_edit';  
    $this->AssetRule->scripts = array('tiny_mce/tiny_mce');  
    $this->AssetRule->codeblock = 'tinyMCE.init({  
        mode : "textareas",  
        theme : "advanced",  
        plugins : "media",  
        media_external_list_url : "media/list.js",  
        theme_advanced_buttons1 : "bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright, justifyfull,bullist,numlist,undo,outdent,indent,redo,link,unlink",  
        theme_advanced_buttons2 : "",  
        theme_advanced_buttons3 : "",  
        theme_advanced_resizing : true,  
        theme_advanced_toolbar_location : "top",  
        theme_advanced_toolbar_align : "left",  
        theme_advanced_statusbar_location : "bottom",  
        extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"  
    });';  
    $this->AssetRule->runRule();

Coming from a heavy UI developer background, I can afford to be
incredibly anal when managing my CSS and JavaScript files. I hope this
saves you time, frustration and makes your developer-life better.

Code
~~~~

Helper Class:
`````````````

::

    <?php 
    /*
     * Asset Mapper CakePHP Component
     * Copyright (c) 2007 Marc Grabanski
     * http://marcgrabanski.com
     *
     * @author      Marc Grabanski <m@marcgrabanski.com>
     * @version     1.0
     * @license     MIT
     *
     * Built on top of Asset Packer by Matt Curry <matt@pseudocoder.com>
     */
    
    class AssetMapperHelper extends Helper
    {
    	var $helpers = array('AssetRule','AssetPacker');
    
    	function beforeRender() {
    		// Pass the controller name to AssetRule
    		$this->AssetRule->_controller = isset($this->params['controller']) ? $this->params['controller'] : null;
    		// Pass the action action to AssetRule
    		$this->AssetRule->_action = isset($this->params['action']) ? $this->params['action'] : null;
    		// Run the rules definition
    		$this->defineRules(); 
    		if (Configure::read('debug') > 0) {
    			$this->AssetPacker->developmentMode = true;
    		}
    	}
    
    	function defineRules() {
    		include('asset_map.php');
    	}
    
    	function afterRender() {
    		// Get the view so we can output variables to it
    		$this->view =& ClassRegistry::getObject('view');
    		// Get the buffer from the AssetPacker
    		$this->view->viewVars['styles_for_layout'] = $this->AssetPacker->generateCSS();
    		$this->view->viewVars['javascript_for_layout'] = $this->AssetPacker->generateJS();
    	}
    
    }
    ?>

app/views/helpers/asset/ asset_mapper.php

Helper Class:
`````````````

::

    <?php 
    /*
     * Asset Rule, Part of Asset Mapper CakePHP Component
     * Copyright (c) 2007 Marc Grabanski
     * http://marcgrabanski.com
     *
     * @author      Marc Grabanski <m@marcgrabanski.com>
     * @version     1.0
     * @license     MIT
     *
     * Built on top of Asset Packer by Matt Curry <matt@pseudocoder.com>
     */
    class AssetRuleHelper extends Helper 
    {
    	var $helpers = array('AssetPacker');
    	
    	var $_controller; // Controller name set by AssetMap
    	var $_action; // Action name set by AssetMap
    	
    	// Create an empty rule
    	function create() {
    		$this->controller = null; // Map assets to a controller
    		$this->action = null; // Map assets to an action
    		
    		$this->compact->css = null; // CSS files to compact and compress with CSS Tidy
    		$this->compact->scripts = null; // Scripts to compact into one file and minify with JS Min
    		$this->scripts = null;
    		$this->codeblock = null;
    	}
    	
    	function runRule() {
    		if (empty($this->controller) || $this->controller === $this->_controller || 
    				(is_array($this->controller) && in_array($this->_controller, $this->controller) !== false)
    			) {
    			if (empty($this->action) || $this->action === $this->_action || 
    				(is_array($this->action) && in_array($this->_action, $this->action) !== false)) {
    				// If rule criteria is satisfied where we are, now process the rule
    				$this->processRule();
    			}
    		}
    	}
    	
    	function processRule() {
    		if (isset($this->compact->css)) {
    			$this->processCompactCSS();
    		}
    		if (isset($this->compact->scripts)) {
    			$this->processCompactScripts();
    		}
    		if (isset($this->scripts)) {
    			$this->processScripts();
    		}
    		if (isset($this->codeblock)) {
    			$this->processCodeblock();
    		}
    	}
    	
    	/* Process and send to AssetPacker buffer */
    	
    	function processCompactCSS() {
    		if (is_array($this->compact->css)) {
    			foreach ($this->compact->css as $cssfile) {
    				$this->AssetPacker->buffer['css'][] = $cssfile;
    			}
    		} else {
    			$this->AssetPacker->buffer['css'][] = $this->compact->css;
    		}
    	}
    	
    	function processCompactScripts() {
    		if (is_array($this->compact->scripts)) {
    			foreach ($this->compact->scripts as $script) {
    				$this->AssetPacker->buffer['compactScripts'][] = $script;
    			}
    		} else {
    			$this->AssetPacker->buffer['compactScripts'][] = $this->compact->scripts;
    		}
    	}
    	
    	function processScripts() {
    		if (is_array($this->scripts)) {
    			foreach ($this->scripts as $script) {
    				$this->AssetPacker->buffer['scripts'][] = $script;
    			}
    		} else {
    			$this->AssetPacker->buffer['scripts'][] = $this->scripts;
    		}
    	}
    	
    	function processCodeBlock() {
    		$this->AssetPacker->buffer['codeblock'][] = $this->codeblock;
    	}
    	
    }
    ?>

app/views/helpers/asset/ asset_rule.php

Helper Class:
`````````````

::

    <?php 
    /*
     * Asset Packer CakePHP Component
     * Copyright (c) 2007 Matt Curry
     * www.PseudoCoder.com
     *
     * @author      mattc <matt@pseudocoder.com>
     * @version     1.0
     * @license     MIT
     * 
     * Modified for Asset Map CakePHP Component
     * Marc Grabanski
     * http://MarcGrabanski.com
     *
     */
    
    class AssetPackerHelper extends Helper 
    {
    	var $helpers = array('Html', 'Javascript');
    	
    	var $developmentMode = false;
    	
        //there is a  *minimal* perfomance hit associated with looking up the filemtimes
        //if you clean out your cached dir (as set below) on builds then you don't need this.
        var $checkTS = false;
    	
        var $viewScriptCount = 0;
    
        //you can change this if you want to store the files in a different location
        var $cachePath = '../packed/';
    
        //options: default, low_compression, high_compression, highest_compression
        var $cssCompression = 'highest_compression';
    
        //flag so we know the view is done rendering and it's the layouts turn
        function beforeRender() {
            $view =& ClassRegistry::getObject('view');
            $this->viewScriptCount = count($view->__scripts);
        }
    	
    	function style_for_layout() {
    		$view =& ClassRegistry::getObject('view');
    
            //nothing to do
            if (!$view->__scripts) {
                return;
            }
    
            //move the layout scripts to the front
            $view->__scripts = array_merge(
                                   array_slice($view->__scripts, $this->viewScriptCount),
                                   array_slice($view->__scripts, 0, $this->viewScriptCount)
                               );
    
            //split the scripts into js and css
            foreach ($view->__scripts as $i => $script) {
                if (preg_match('/css\/(.*).css/', $script, $match)) {
                    $temp = array();
                    $temp['script'] = $match[1];
                    $temp['name'] = basename($match[1]);
                    $css[] = $temp;
    
                    //remove the script since it will become part of the merged script
                    unset($view->__scripts[$i]);
                }
            }
    
            $style_for_layout = '';
    		
            if (!empty($css)) {
                $style_for_layout .= $this->Html->css($this->cachePath . $this->process('css', $css));
                $style_for_layout .= "\n\t";
            }
    
            return $style_for_layout;
    	}
    
        function scripts_for_layout() {
            $view =& ClassRegistry::getObject('view');
    
            //nothing to do
            if (!$view->__scripts) {
                return;
            }
    
            //move the layout scripts to the front
            $view->__scripts = array_merge(
                                   array_slice($view->__scripts, $this->viewScriptCount),
                                   array_slice($view->__scripts, 0, $this->viewScriptCount)
                               );
    
            //split the scripts into js and css
            foreach ($view->__scripts as $i => $script) {
                if (preg_match('/js\/(.*).js/', $script, $match)) {
                    $temp = array();
                    $temp['script'] = $match[1];
                    $temp['name'] = basename($match[1]);
                    $js[] = $temp;
    
                    //remove the script since it will become part of the merged script
                    unset($view->__scripts[$i]);
                }
            }
    
            $script_for_layout = '';
    
            //then the js
            if (!empty($js)) {
                $script_for_layout .= $this->Javascript->link($this->cachePath . $this->process('js', $js));
            }
    
            return $script_for_layout;
        }
    
    
        function process($type, $data) {
            switch($type) {
                case 'js':
                    $path = JS;
                    break;
                case 'css':
                    $path = CSS;
                    break;
            }
    
            $folder = new Folder;
    
            //make sure the cache folder exists
            $folder->mkdirr($path . $this->cachePath);
    
            //check if the cached file exists
            $names = Set::extract($data, '{n}.name');
    
            $folder->cd($path . $this->cachePath);
            $fileName = $folder->find(implode('_', $names) . '.' . $type);
    
            if ($fileName) {
                //take the first file...really should only be one.
                $fileName = $fileName[0];
            }
    
            //make sure all the pieces that went into the packed script
            //are OLDER then the packed version
            if($this->checkTS && $fileName) {
                $packed_ts = filemtime($path . $this->cachePath . $fileName);
    
                $latest_ts = 0;
                $scripts = Set::extract($data, '{n}.script');
                foreach($scripts as $script) {
                    $latest_ts = max($latest_ts, filemtime($path . $script . '.' . $type));
                }
    
                //an original file is newer.  need to rebuild
                if ($latest_ts > $packed_ts) {
                    unlink($path . $this->cachePath . $fileName);
                    $fileName = null;
                }
            }
    
            //file doesn't exist.  create it.
            if (!$fileName) {
    
                //merge the script
                $scriptBuffer = '';
                $scripts = Set::extract($data, '{n}.script');
                foreach($scripts as $script) {
                    $scriptBuffer .= file_get_contents($path . $script . '.' . $type);
                }
    
                switch($type) {
                    case 'js':
                        if (PHP5) {
                            vendor('jsmin/jsmin');
                            $scriptBuffer = JSMin::minify($scriptBuffer);
                        }
                        break;
    
                    case 'css':
                        vendor('css_tidy/class.csstidy');
                        $tidy = new csstidy();
                        $tidy->load_template($this->cssCompression);
                        $tidy->parse($scriptBuffer);
                        $scriptBuffer = $tidy->print->plain();
                        break;
    
                }
    
                //write the file
                $fileName = implode($names, '_') . '.' . $type;
                $file = new File($path . $this->cachePath . $fileName);
                $file->write(trim($scriptBuffer));
            }
    
            if ($type == 'css') {
                $fileName = str_replace('.css', '', $fileName);
            }
    
            return $fileName;
        }
    	
    	/* Process the CSS buffer and send the CSS to Asset Mapper */
    	function generateCSS() {
    		if ($this->developmentMode) {
    			$out = '';
    			if(isset($this->buffer['css'])) {
    				foreach($this->buffer['css'] as $css) {
    					$out .= $this->Html->css($css);
    				}
    			}
    			return $out;
    		} else {
    			if(isset($this->buffer['css'])) {
    				foreach($this->buffer['css'] as $css) {
    					$this->Html->css($css,null,null,false);
    				}
    			}
    			return $this->style_for_layout();
    		}
    	}
    	
    	/* Process the JavaScript buffers and send the JavaScript to Asset Mapper */
    	function generateJS() {
    		
    		if ($this->developmentMode) {
    			$out = '';
    			// create javascript links with the compactscripts buffer
    			if(isset($this->buffer['compactScripts'])) {
    				foreach($this->buffer['compactScripts'] as $compactScript) {
    					$out .= $this->Javascript->link($compactScript);
    				}
    			}
    		} else {
    			// create javascript links with the compactscripts buffer
    			if(isset($this->buffer['compactScripts'])) {
    				foreach($this->buffer['compactScripts'] as $compactScript) {
    					$this->Javascript->link($compactScript, false);
    				}
    			}
    			// compact the scripts
    			$out = $this->scripts_for_layout();
    		}
    		
    		// output regular javascript links with the scripts buffer
    		if(isset($this->buffer['scripts'])) {
    			foreach($this->buffer['scripts'] as $script) {
    				$out .= $this->Javascript->link($script);
    			}
    		}
    		
    		// Concattenate all of the codeblocsk together
    		$codeblocks = '';
    		if(isset($this->buffer['codeblock'])) {
    			foreach($this->buffer['codeblock'] as $codeblock) {
    				$codeblocks .= $codeblock;
    			}
    		}
    		// output as one codeblock
    		$out .= $this->Javascript->codeblock($codeblocks);
    		
    		return $out;
    	}
    }
    ?>

app/views/helpers/asset/ asset_packer.php

::

    
    <IfModule mod_deflate.c>
      # compress content with type html, text, and css
      AddOutputFilterByType DEFLATE text/css text/javascript application/x-javascript text/js
      <IfModule mod_headers.c>
        # properly handle requests coming from behind proxies
        Header append Vary User-Agent
      </IfModule>
    </IfModule>
    
    <IfModule mod_expires.c> 
      ExpiresActive On
      ExpiresByType text/css "access plus 10 years"
      ExpiresByType text/js "access plus 10 years"
      ExpiresByType text/javascript "access plus 10 years"
      ExpiresByType application/x-javascript "access plus 10 years"
      ExpiresByType image/png "access plus 10 years"
      ExpiresByType image/gif "access plus 10 years"
      ExpiresByType image/jpeg "access plus 10 years"
    </IfModule>
    
    FileETag none

app/views/helpers/asset/ asset_packer.htaccess

Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
    	
    	var $helpers = array('AssetMapper');
    
    }
    ?>

app/controllers/ app_controller.php

View Template:
``````````````

::

    
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">
    <html>
    <head>
    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />	
    	<title><?php echo $title_for_layout?></title>
    	<?php echo $styles_for_layout ?>
    	<!--[if lte IE 7]><?php echo $html->css('ieold') ?><![endif]--> 
    </head>
    	<body>
    		<?php $session->flash() ?>
    		<?php echo $content_for_layout ?>
    		<?php echo $javascript_for_layout ?>
    	</body>
    </html>

app/views/layouts/ default.ctp
`Download CakePHP Asset Mapper (zip file)`_

Furthur documentation and updates can be found on the `CakePHP Asset
Mapper Project Page`_ .

Enjoy!


.. _Download CakePHP Asset Mapper (zip file): http://marcgrabanski.com/code/asset-mapper/AssetMapper.zip
.. _CakePHP Asset Mapper Project Page: http://marcgrabanski.com/code/asset-mapper/
.. meta::
    :title: Asset Mapper
    :description: CakePHP Article related to assets,Helpers
    :keywords: assets,Helpers
    :copyright: Copyright 2008 
    :category: helpers

