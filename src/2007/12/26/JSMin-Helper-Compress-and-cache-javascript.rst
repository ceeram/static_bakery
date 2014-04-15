JSMin Helper Compress and cache javascript
==========================================

by markstory on December 26, 2007

JSMinHelper offers an easy to use interface for compressing and
caching javascript files, reducing bandwidth and server load.


JSMin Helper
````````````

The JSMin helper helps you compress javascript used in your views.
It compresses and caches the resulted compress javascript. Saving load
for future requests. Minifing Javascript can reduce file size by
30-50%. In addition it strips out comments and whitespace. Allowing
you to comment your javascript without having to worry about creating
production ready comment free files.

JSMinHelper requires the JSMin Class which can be found
`http://code.google.com/p/jsmin-php/`_ Unfortunately the JSMin library
is PHP5 only. Once you've obtained the JSMin class place it in the
app/vendors/JSMin/JSMin.php.



Helper Class:
`````````````

::

    <?php 
    <?php
    /**
     * JSMin Helper 
     * 
     * @author Mark Story
     **/
    class JsminHelper extends AppHelper
    {
    	/**
    	 * html tags used by this helper.
    	 *
    	 * @var array
    	 */
    		var $tags = array(
    			'javascriptblock' => '<script type="text/javascript">%s</script>',
    			'javascriptstart' => '<script type="text/javascript">',
    			'javascriptlink' => '<script type="text/javascript" src="%s"></script>',
    			'javascriptend' => '</script>',
    		);
    		
    	/**
    	 * To Cache Or to Not Cache, if caching is turned off,
    	 * Minified JS files will be included inside a script block 
    	 * and not as a script include.
    	 *
    	 * @access private
    	 * @var bool - Defaults to true
    	 **/
    	var $__cacheScripts = true;
    	
    	/**
    	 * Files that have been group cached.
    	 *
    	 * @var array
    	 **/
    	var $__cachedFiles = null;
    	
    	/**
    	 * The Filename to store Cached minified JS files to. Gives you control
    	 * over the file name that jsmin caches files to.
    	 *
    	 * @access private
    	 * @var string
    	 **/
    	var $__cacheFile = null;
    	
    	/**
    	 * strtotime Compatible Time Expression for lifetime of cached JS files
    	 *
    	 * @var string
    	 **/
    	var $__cacheTime = '+99 days';
    
    	
    	/**
    	 * construct the helper and include JSMin.
    	 *
    	 * @return void
    	 **/
    	function __construct(){
    		parent::__construct();
    		if(!vendor('JSMin'.DS.'JSMin')){
    			trigger_error('Could Not locate JSMin.  Please Place it in app/vendors/JSMin/JSMin.php', E_USER_WARNING);
    			return;
    		}				
    	}
    	
    	/**
    	 * Similar to Javascript Helper's link function except the file must be local to the application.
    	 * The JS file is Minified and cached if desired.  Any files that are linked to with inline = false
    	 * Will be appended to a single file and included in the scripts for layout.
    	 *
    	 * @param string/array $file The file(s) to Minify and return
    	 * @param string $inline  Whether to include the minified js inline or in the scripts_for_layout 
    	 * @return mixed string if inline is true, or void.
    	 **/
    	function link($file, $inline = true){
    		//recur through files array
    		if (is_array($file)) {
    			$out = '';
    			foreach ($file as $i) {
    				$out .= "\n\t" . $this->link($i, $inline);
    			}
    			if ($inline)  {
    				return $out . "\n";
    			}
    			return;
    		}
    		//Check file names
    		if(strpos($file, '.js') === false || strpos($file, '?') === false){
    			$file .= '.js';
    		}
    		if(strpos($file, '://') !== false){
    			trigger_error('JSMin::link can only be used on local JS files', E_USER_WARNING);
    			return;
    		}
    		//Read file
    		$fhandle = JS.$file;
    		if(file_exists($fhandle)){
    			$fData = file_get_contents($fhandle);
    		}else{
    			trigger_error('Could not read '.$fhandle.' Check Paths', E_USER_WARNING);
    		}
    		if($fData){
    			$miniData = JSMin::minify($fData);
    			unset($fData);
    		}		
    		
    		//File Name?
    		if(!is_string($this->__cacheFile)){
    			$this->__cacheFile = md5($miniData) . '.js';
    		}
    		
    		if(strpos($this->__cacheFile, '.js') === false){
    			$this->__cacheFile = $this->__cacheFile. '.js';
    		}			
    					
    		//Include script inline or in scripts_for_layout?
    		if($inline){
    			//write cache file?
    			if(!$this->__cacheScripts || is_null($this->__cachedFile)){
    				$out = sprintf($this->tags['javascriptblock'], $miniData);
    			}else{
    				$this->_writeCacheFile($miniData);
    				$out = sprintf($this->tags['javascriptlink'], $this->webroot(JS_URL.$this->__cacheFile));
    			}			
    		}else{
    			$this->__cachedFiles[] = $miniData;
    			$out = '';
    		}
    		return $out;
    	}
    	/**
    	 * Minify and wrap a Code Block in a script tag
    	 * Does not cache minified Javascript
    	 * 
    	 * @param string $js  The Js to be squashed and wrapped
    	 * @param bool $safe  Wrap the JS in an HTML comment and CDATA block.
    	 * 
    	 * @return mixed string or void depending on cache settings
    	 **/
    	function codeBlock($js, $safe = false){
    		$miniData = JSMin::minify($js);
    		if($safe){
    			$miniData = "<!--//--><![CDATA[<!--\n" . $miniData . "\n//--><!]]>";
    		}
    		return sprintf($this->tags['javascriptblock'], $miniData);
    	}
    	
    	/**
    	 * Control Caching of JSMin helper
    	 *
    	 * @param string $filename  The filename to store minified JS in, all minified JS will be in one file.
         * @param bool $cache Whether or not to cache minified JS files. If false, script will be minified on each request.
     	 * @param string $cacheTime  strtotime compatible time expression that javascript will be cached for 
    	 * @return void
    	 **/
    	function setCache($filename, $cache = true, $cacheTime = '+99 days') {
    		$this->__cacheScripts = $cache;
    		$this->__cacheFile = $filename;
    		$this->__cacheTime = $cacheTime;
    	}
    	
    	
    	/**
    	 * After Render Callback.
    	 * If cachedFiles is an array it loops through it, making one big JS file
    	 * This large JS file is then included in the scripts for layout.
    	 *
    	 * @return bool true;
    	 **/
    	function afterRender(){
    		if(is_array($this->__cachedFiles) && $this->__cacheScripts){
    			//join files and write cached file.
    			$joinedJs = implode(" ", $this->__cachedFiles);		
    			$this->_writeCacheFile($joinedJs);					
    			$out = sprintf($this->tags['javascriptlink'], $this->webroot(JS_URL.$this->__cacheFile));				
    		}elseif(is_array($this->__cachedFiles)){
    			$joinedJs = implode(' ', $this->__cachedFiles);
    			$out = sprintf($this->tags['javascriptblock'], $joinedJs);
    		}else{
    			$out = '';
    		}
    		$view =& ClassRegistry::getObject('view');
    		$view->addScript($out);
    		return true;
    	}
    	/**
    	 * Writes the Cache File for the currently cached files
    	 *
    	 * @access protected
    	 * @return void
    	 **/
    	function _writeCacheFile($data){
    		if (!cache(r(WWW_ROOT, '', JS) . $this->__cacheFile, null, $this->__cacheTime, 'public')) {
    			cache(r(WWW_ROOT, '', JS) . $this->__cacheFile, $data, $this->__cacheTime, 'public');
    		}
    	}
    } // END class JSMinHelper extends AppHelper
    ?>
    ?>


You use the JSMin helper to include javascript files very much like
you would use the Javascript Helper.


Usage Examples:
```````````````

Including a compressed js file inline:
++++++++++++++++++++++++++++++++++++++

::

    
    <?php
        echo $jsmin->link('myJS');
    ?>


Minify a block of javascript:
+++++++++++++++++++++++++++++

::

    
    <?php 
        echo $jsmin->codeBlock("alert('javascript');");
    ?>


Configuring the JSMinHelper
+++++++++++++++++++++++++++

::

    
    <?php
      $jsmin->setCache('cachedJS', true, '+30 days');
    ?>

JSminHelper::setCache allows you to set the filename used to for the
cached scripts. Turn on or off the caching of scripts and set how long
the scripts are cached for.


If JSMinHelper is set to cache Javascript it will collect all
javascript that is linked with it compile them into one file and link
to only one file. This gives additional savings in bandwidth as fewer
requests are made.

I hope you find this helper useful, if there are any suggestions on
how to improve the helper I would love to hear them.


.. _http://code.google.com/p/jsmin-php/: http://code.google.com/p/jsmin-php/

.. author:: markstory
.. categories:: articles, helpers
.. tags:: JSMin,Helpers

