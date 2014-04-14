Improved Minify Helper
======================

by sdewald on June 20, 2009

[p]Basically I started out using the minify helper from
http://bakery.cakephp.org/articles/view/minify-helper-for-cakephp[/p]
[p]It worked fine, but my page uses jQuery and it was really annoying
me that the javascript loaded at the beginning of the page load. Your
browser normally downloads files in parallel which can dramatically
reduce your page load times. Problem is your browser can't do that
while it's processing javascript files. Developers will put the
javascript link at the bottom of the page so that the page will be
rendered and appear to have loaded while the javascript is processed
at the end of the page load.[/p] [p]The other minify helper couldn't
do this so I made my own.[/p]


Step 1 - Install minify
~~~~~~~~~~~~~~~~~~~~~~~

Read about the minify project at `http://code.google.com/p/minify/`_

Download the latest version and follow the install instructions. I
literally just unzipped it to my webroot/ directory and it worked.

Step 2 - Copy the helper
~~~~~~~~~~~~~~~~~~~~~~~~

Save it as minify.php in your helpers folder

Helper Class:
`````````````

::

    <?php 
    /***
     * Helper to minify local assets.  See minify at http://code.google.com/p/minify/
     * @author Steve DeWald - sdewald@gmail.com - maggwire.com
     */
    
    Class MinifyHelper extends AppHelper {
    	/**
    	 * Output js stylesheets
    	 */
    	public function js($scripts) {
    		$links = array();
    		$urls = array();
    		foreach ($scripts as $script) {
    			$matches = array();
    			if (preg_match('/src="\/js\/(.*?)\.js"/', $script, $matches)) {
    				$links[] = $script;
    				$urls[] = $matches[1];
    			}
    		}
    		
    		if (!empty($links)) {
    			if (Configure::read('MinifyAssets')) {
    				$path = $this->_path($urls, 'js');
    				return '<script type="text/javascript" src="'.$path.'"></script>';
    			} else {
    				return implode($links, "\n");
    			}
    		} else {
    			return '';
    		}
    	}
    
    	/**
    	 * Output css stylesheets
    	 */
    	public function css($scripts) {
    		$sheets = array();
    		$urls = array();
    		foreach ($scripts as $script) {
    			$matches = array();
    			if (preg_match('/href="\/css\/(.*?)\.css"/', $script, $matches)) {
    				$sheets[] = $script;
    				$urls[] = $matches[1];
    			}
    		}
    		
    		if (!empty($sheets)) {
    			if (Configure::read('MinifyAssets')) {
    				$path = $this->_path($urls, 'css');
    				return '<link rel="stylesheet" type="text/css" href="'.$path.'" />';
    			} else {
    				return implode($sheets, "\n");
    			}
    		} else {
    			return '';
    		}
    	}
    	
    	/**
    	 * Output other scripts for layout
    	 */
    	public function external($scripts) {
    		$externals = array();
    		foreach ($scripts as $script) {
    			$matches = array();
    			if (!preg_match('/href="\/css\/(.*?)\.css"/', $script, $matches) &&
    				!preg_match('/src="\/js\/(.*?)\.js"/', $script, $matches)) {
    				$externals[] = $script;
    			}
    		}
    		
    		if (!empty($externals)) {
    			return implode($externals, "\n");
    		} else {
    			return '';
    		}
    	}
    	
    	/**
    	 * Gets the minified path for a group of assets
    	 *
    	 * @param array $assets Array of asset paths
    	 * @param string $ext File extension for the assets (i.e. 'js' or 'css')
    	 */
    	private function _path($assets, $ext) {
    		$path = $this->webroot . "min/b=$ext&f=";
    		foreach ($assets as $asset) {
    			$path .= ($asset . ".$ext,");
    		}
    		return substr($path, 0, count($path)-2);
    	}
    }
    ?>


Step 3 - Edit your default layout
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In the head section of your default.ctp file, add the following code
to output a link to your minified css. This should replace the "echo
$scripts_for_layout" line that you normally use.

::

    echo $minify->css($this->__scripts);

Now add this code to the bottom of the body section of your layout

::

    echo $minify->external($this->__scripts);
    echo $minify->js($this->__scripts);

Obviously you'll have to include the external links in the head
section if you added any external css stylesheets (I mean not in your
/webroot/css folder) or meta tags with the html helper. Otherwise,
this should work fine using the html and javascript helpers.

Step 4 - Add a line to your core config file
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    /**
    * Turn on MinifyHelper
    */
    Configure::write('MinifyAssets', true);

[p]You're all done! You shouldn't have to change anything else, as
this works using the CakePHP internal $__scripts variable that is
normally outputted with "echo $scripts_for_layout". You can continue
adding stylesheets and javascript links with the html and javascript
helpers with inline=false and the minify helper will handle the rest!

.. _http://code.google.com/p/minify/: http://code.google.com/p/minify/
.. meta::
    :title: Improved Minify Helper
    :description: CakePHP Article related to javascript,CSS,packer,asset,minify,Helpers
    :keywords: javascript,CSS,packer,asset,minify,Helpers
    :copyright: Copyright 2009 sdewald
    :category: helpers

