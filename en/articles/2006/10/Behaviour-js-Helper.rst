

Behaviour.js Helper
===================

by %s on October 02, 2006

Helps you using the Behaviour Javacript in your App. Behaviour can be
used to attach Javascript to Elements. With this you can easily avoid
using inline-Javascript.
I made this Helper because i always used Behaviour in all my projects
before i recently joined the cakePHP community. Now while the AJAX
Helper is nice and the Javascript Helper too, there was one huge
problem: The inline-Javascript. BehaviourJS makes use of CSS Selectors
to attach events - and this all as a layer in an external file.

First argument why to use it is that it makes it possible for you to
let your site gracefully degrade to clients. Second is, that you can
attach the same Javascript to many elements at once by just adding one
line of code saving you repeating yourself!


Selector Magic
--------------

As an example: Lets say you have a list of links.. a menu, for that
matter. You wrap it around a div with the id=menu.

::

          <div id="menu">
          <a href="pages/test">example</a>
          <a href="pages/test">example</a>
          <a href="pages/test">example</a>
          </div>

Now with the BehaviourHelper you can attach javascript to all of them
like this:

::

    <?php $behaviour->addRule('#menu a', 'onmouseover', "new Effect.Highlight('element')"; ?>

If you now hover over the links their background will highlight.. or
jump around..whatever you want to do! You can use all available
"on"-EventHandlers. Attach Javascript as if you are writing a
stylesheet.


Form Input example
------------------

Lets say you have to validate inputfields while there are being typed
in or changed.. You could add a className "required" to them and start
a validation with - again - one line of code

::

    <?php $behaviour->addAjaxRule('input.required', 'onchange', array('url'=>'/users/formvalidator', 'update'=>'errormsg')); ?>

This saves you hours! ;D


How it works
------------
Now the outsourcing of the javascript is a bit tricky but i hope it is
still convinient enough for you. Be aware that i am into Cake for just
a few days so i don't know all stuff around it.

For stacking up all Rules, defined within views, i am writing to a js
file located in the /tmp/ folder in your app dir.

To actually get it into your part of the layout, i am using a second
PHP file which is located in your webroot/js folder. That file reads
the content of the js which should be around that time and then spits
out the content for immediate execution after the page has been
loaded.


Installation
------------
copy behaviour.js to [app]/webroot/js/
copy behaviourRules.php to [app]/webroot/js/
copy behaviour.php to [app]/views/helper/

put 'Behaviour' into your $helper array:

::

    var $helper = array('Behaviour');

put this into the head section of your layout:

::

    <?php echo $javascript->link('prototype'); ?>
    <?php echo $javascript->link('behaviour'); ?>
    <?php echo $behaviour->linkRulesJS(); ?>

And thats it ..

Of course your [app]/tmp/ folder needs to
writable for this whole thing to work.


METHOD: addAjaxRule()
---------------------
This creates a Ajax.Request or Ajax.Updater Rule based upon the first
two arguments. It uses the Ajax Helper, so for all possible options
refer to the AjaxHelper Manual.

Note: If you leave out $options['update'] the Ajax Call will become a
Request.

::

    $behaviour->addAjaxRule($target, $event, $options, [$returnFalse])



#. @param string $target - This can be a html-tagname, a id, a
   classname or a combination of all, like "li.listclass" or "a.menu".
   For id: "pre#myid"
#. @param string $event - This is the event handler. For example:
   "onsubmit" or "onkeydown"
#. @param array $options - Array with the AjaxHelper options for
   building an Prototype Ajax Function
#. @param boolean $returnFalse - If 'false' the call will not "return
   false;". Generally not a good idea but just in case, here you can
   control this



METHOD: addRule()
-----------------
This is for simple Javascript. For example if you are using the
Scriptaculous Effects in your application.

Note: Don't append the last semi-colon.

::

    $behaviour->addRule($target, $event, $script, [$returnFalse]);



#. @param string $target - This can be a html-tagname, a id, a
   classname or a combination of all, like "li.listclass" or "a.menu".
   For id: "pre#myId"
#. @param string $event - This is the event handler. For example:
   "onsubmit" or "onkeydown"
#. @param string $script - Any javascript you want..
#. @param boolean $returnFalse - If 'false' the call will not "return
   false;". (default: true)

Example:

::

    $behaviour->addRule('a#hidemenu', 'onclick', "Element.toggle('menu')");



METHOD: addLoadEvent()
----------------------
With addLoadEvent you can add javascript function calls to the window
onload event from within your view.

::

    $behaviour->addLoadEvent($script);


#. @param mixed $script - Any javscript you want..

Examples:

::

    $behaviour->addLoadEvent('initMenu()');

You can also pass an array:

::

    $behaviour->addLoadEvent(array('initMenu()', 'highlightAll()', 'loadLayout()', 'readCookies()'));



Form example in detail
----------------------

::

    
          <?php echo $html->input('test/bla', array('class'=>'required')); ?><br />
          <?php echo $html->input('test/bla', array('class'=>'required')); ?><br />
          <?php echo $html->input('test/bla', array('class'=>'required')); ?><br />
          <?php echo $html->input('test/bla'); ?><br />
          <?php echo $html->input('test/bla'); ?><br />
          <?php $behaviour->addRule('input.required', 'onchange',
                                   "new Effect.Highlight(element)"); ?>

Note: To the highlight function i pass "element". Element is the
javascript variable for the current html element. ;)

Note 2: To actually call Effect.[whatever..] you have to link up with
scriptaculous.js first!



The Helper Files
~~~~~~~~~~~~~~~~

/app/helper/behaviour.php
`````````````````````````

::

    
    <?php
    /**
     * Helper for the Behaviour Javascript
     * 
     * @version 0.2.1
     * @copyright Copyright (c) 2006, K.Bublitz
     * @author Kjell Bublitz (aka. m3nt0r) 
     * @link http://www.m3nt0r.de Author Homepage
     * @link http://www.m3nt0r.de/blog/behaviourjs-helper-for-cakephp/ Help and HowTo
     * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
     */
    
    /**
     * BehaviourHelper: Helper for working with bevaviour.js
     * 
     * @example $behaviour->addRule('li.item', 'onmouseover', 'dosomejavscript..');
     * @example $behaviour->addAjaxRule('#myForm', 'onsubmit', array('url'=>'ajax/users/login'));
     * @example $behvaiour->addLoadEvent('loadMyMenu()');
     */
    class BehaviourHelper extends Helper
    {
        /**
         * This propertie contains all rules as formatted string which
         * will be extended by addRule and used to create the temp file
         * 
         * @var string 
         */
        var $behaviourRules = null;
    
        /**
         * This propertie contains loadEvents as formatted string
         * which will be appended to the rules and so to the resulting file
         * 
         * @var string 
         */
        var $behaviourLoads = null;
    
        /**
         * Contains the Path to the app/tmp/ - directory
         * I dont know the global var yet... hook me up on irc ;)
         * 
         * @var string 
         */
        var $behaviourTempPath;
    
        /**
         * Behaviour uses Ajax- and Javascript-Helper
         * 
         * @var array 
         */
        var $helpers = array('Ajax', 'Javascript');
    
        /**
         * Array with all possible eventhandlers.
         * 
         * @var array 
         */
        var $events = array('onabort', 'onblur', 'onchange', 'onclick', 'ondblclick', 'onerror', 'onfocus',
            'onkeydown', 'onkeypress', 'onkeyup', 'onload', 'onmousedown', 'onmousemove',
            'onmouseout', 'onmouseover', 'onmouseup', 'onreset', 'onselect', 'onsubmit',
            'onunload');
    
        /**
         * This creates a Ajax.Request or Ajax.Updater Rule based upon the first two arguments.
         * It uses the Ajax Helper, so for all possible options refer to the AjaxHelper Manual. 
         * 
         * Little sidenote: If you leave out $options['update'] the Ajax Call will become a Request.
         * 
         * @param string $target This can be a html-tagname, a id, a classname or a combination of all, like "li.listclass" or "a.menu". For id: "pre#myid"
         * @param string $event This is the event handler. For example: "onsubmit" or "onkeydown"
         * @param array $options Array with the AjaxHelper options for building an Prototype Ajax Function
         * @param boolean $returnFalse If 'false' the call will not "return false;". Generally not a good idea but just in case, here you can control this.
         */
        function addAjaxRule($target = null, $event = null, $options = array(), $returnFalse = true)
        { 
            // Used for validating Rule before adding it
            $error = false; 
            
            // Check if a there is a previous rule, append (,) for array
            if (!empty($this->behaviourRules))
            {
                $this->behaviourRules = $this->behaviourRules . ",\n";
            } 
            // Does the Rule contain a target ?
            if (empty($target))
            {
                echo '<!-- no target for this rule -->';
                return;
            } 
            // Is valid event?
            if (!in_array($event, $this->events))
            {
                $error = true;
                $event = 'onclick';
            } 
            // Does the Rule contain any script ?
            if (count($options) == 0)
            {
                $error = true;
            } 
            // Check if a URL is present.
            // This is a Ajax Call so a URL is mandatory.
            if (!isset($options['url']))
            {
                $error = true;
            } 
            // This is building the actual Call
            $script = $this->Ajax->remoteFunction($options); 
            // This changes the Call because an error occured
            // If something is wrong then this will create a Alert Popup on Event.
            if ($error)
            {
                $script = "alert('Fix the rule-setup for \"" . $target . "\" in your view')";
            } 
            // This adds a return false to the javascript (default)
            $returnSnippet = "\n\t\t\treturn false;"; 
            // If that is not wanted then empty the string
            if (!$returnFalse)
            {
                $returnSnippet = "";
            } 
            // Create the rule, add to global
            $this->behaviourRules .= "\t'" . $target . "' : function(element){\n\t\telement." . $event . " = function(){\n\t\t\t" . $script . ";" . $returnSnippet . "\n\t\t}\n\t}"; 
            // write to File
            $this->__writeRuleScript();
        } 
    
        /**
         * This is for simple Javascript. For example if you are using the Scriptaculous Effects in
         * your application. $behaviour->addRule('a.menu', 'onmouseover', 'Effect.highlight(element)');
         * 
         * @param string $target This can be a html-tagname, a id, a classname or a combination of all, like "li.listclass" or "a.menu". For id: "pre#myid"
         * @param string $event This is the event handler. For example: "onsubmit" or "onkeydown"
         * @param string $script Any javscript you want..
         * @param boolean $returnFalse If 'false' the call will not "return false;". Generally not a good idea but just in case, here you can control this.
         */
        function addRule($target = null, $event = null, $script = null, $returnFalse = true)
        { 
            // Used for validating Rule before adding it
            $error = false; 
            
            // Check if a there is a previous rule, append (,) for array
            if (!empty($this->behaviourRules))
            {
                $this->behaviourRules = $this->behaviourRules . ",\n";
            } 
            // Does the Rule contain a target ?
            if (empty($target))
            {
                echo '<!-- no target for this rule -->';
                return;
            } 
            // Is valid event?
            if (!in_array($event, $this->events))
            {
                $error = true;
                $event = 'onclick';
            } 
            // Does the Rule contain any script ?
            if (empty($script))
            {
                $error = true;
            } 
            // This changes the Call because an error occured
            // If something is wrong then this will create a Alert Popup on Event.
            if ($error)
            {
                $script = "alert('Fix the rule-setup for \"" . $target . "\" in your view')";
            } 
            // This adds a return false to the javascript (default)
            $returnSnippet = "\n\t\t\treturn false;"; 
            // If that is not wanted then empty the string
            if (!$returnFalse)
            {
                $returnSnippet = "";
            } 
            // Create the rule, add to global
            $this->behaviourRules .= "\t'" . $target . "' : function(element){\n\t\telement." . $event . " = function(){\n\t\t\t" . $script . ";" . $returnSnippet . "\n\t\t}\n\t}"; 
            // write to File
            $this->__writeRuleScript();
        } 
    
        /**
         * addLoadEvent allows you to add functions to the window.onload handler.
         * 
         * @example $behaviour->addLoadEvent('yourJsFunc()');
         * @param mixed $script Can be a javascript function as string, or you can pass a array with javascript functions. The functions will be added to window.onload(). 
         */
        function addLoadEvent($script = null)
        {
            if (!empty($script))
            {
                if (is_array($script))
                {
                    $this->behaviourLoads .= "Behaviour.addLoadEvent(function(){\n";
                    foreach($script as $loadfunc)
                    {
                        $this->behaviourLoads .= "\t" . $loadfunc . ";\n";
                    } 
                    $this->behaviourLoads .= "});\n";
                } 
                else
                {
                    $this->behaviourLoads .= "Behaviour.addLoadEvent(function(){\n\t" . $script . ";\n});\n";
                } 
            } 
            $this->__writeRuleScript();
        } 
    
        /**
         * Shortcut to Javscript->link. 
         * Points to the correct file which should be in webroot/js/
         * 
         * @return string 
         */
        function linkRulesJS()
        {
            return $this->Javascript->link('behaviourRules.php');
        } 
    
        /**
         * Creates a <script></script> block with the formatted rules.
         * 
         * @param boolean $return If set to true then this function will just return the content. Default is 'echo'
         * @return string 
         */
        function outputScriptBlock($return = false)
        {
            if ($return)
            {
                return $this->Javascript->codeBlock($this->__makeRuleScript());
            } 
            else
            {
                echo $this->Javascript->codeBlock($this->__makeRuleScript());
            } 
        } 
    
        /**
         * Creates a file with the given data as content.
         * Just like file_put_contents. For compat with PHP4 
         * i've chosen the good old way..
         * 
         * @param unknown_type $filename 
         * @param unknown_type $data 
         * @return unknown 
         */
        function __write($filename, $data)
        {
            if (($handle = fopen($filename, 'w+')) === false)
            {
                return false;
            } 
            if (($bytes = fwrite($handle, $data)) === false)
            {
                return false;
            } 
            fclose($handle);
    
            return $bytes;
        } 
    
        /**
         * Writes the current Behaviour Rules into /tmp/behaviourTmp.js
         * 
         * @return void 
         */
        function __writeRuleScript()
        {
            $this->behaviourTempPath = dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'tmp' . DS . 'behaviourTmp.js';
            $this->__write($this->behaviourTempPath, $this->__makeRuleScript());
        } 
    
        /**
         * Wraps the Behaviour Rules into their main Object and appends the 
         * actual BehaviourJS function to parse this Ruleset.
         * 
         * @return string 
         */
        function __makeRuleScript()
        {
            return "\nvar behaviourRules = {\n" . $this->behaviourRules . "}\nBehaviour.register(behaviourRules);\n" . $this->behaviourLoads;
        } 
    } 
    
    ?>



/app/webroot/js/behaviour.js
````````````````````````````

::

    
    /*
       Behaviour v1.1 by Ben Nolan, June 2005. Based largely on the work
       of Simon Willison (see comments by Simon below).
    
       Description:
       	
       	Uses css selectors to apply javascript behaviours to enable
       	unobtrusive javascript in html documents.
       	
       Usage:   
       
    	var myrules = {
    		'b.someclass' : function(element){
    			element.onclick = function(){
    				alert(this.innerHTML);
    			}
    		},
    		'#someid u' : function(element){
    			element.onmouseover = function(){
    				this.innerHTML = "BLAH!";
    			}
    		}
    	};
    	
    	Behaviour.register(myrules);
    	
    	// Call Behaviour.apply() to re-apply the rules (if you
    	// update the dom, etc).
    
       License:
       
       	This file is entirely BSD licensed.
       	
       More information:
       	
       	http://ripcord.co.nz/behaviour/
       
    */   
    
    var Behaviour = {
    	list : new Array,
    	
    	register : function(sheet){
    		Behaviour.list.push(sheet);
    	},
    	
    	start : function(){
    		Behaviour.addLoadEvent(function(){
    			Behaviour.apply();
    		});
    	},
    	
    	apply : function(){
    		for (h=0;sheet=Behaviour.list[h];h++){
    			for (selector in sheet){
    				list = document.getElementsBySelector(selector);
    				
    				if (!list){
    					continue;
    				}
    
    				for (i=0;element=list[i];i++){
    					sheet[selector](element);
    				}
    			}
    		}
    	},
    	
    	addLoadEvent : function(func){
    		var oldonload = window.onload;
    		
    		if (typeof window.onload != 'function') {
    			window.onload = func;
    		} else {
    			window.onload = function() {
    				oldonload();
    				func();
    			}
    		}
    	}
    }
    
    Behaviour.start();
    
    /*
       The following code is Copyright (C) Simon Willison 2004.
    
       document.getElementsBySelector(selector)
       - returns an array of element objects from the current document
         matching the CSS selector. Selectors can contain element names, 
         class names and ids and can be nested. For example:
         
           elements = document.getElementsBySelect('div#main p a.external')
         
         Will return an array of all 'a' elements with 'external' in their 
         class attribute that are contained inside 'p' elements that are 
         contained inside the 'div' element which has id="main"
    
       New in version 0.4: Support for CSS2 and CSS3 attribute selectors:
       See http://www.w3.org/TR/css3-selectors/#attribute-selectors
    
       Version 0.4 - Simon Willison, March 25th 2003
       -- Works in Phoenix 0.5, Mozilla 1.3, Opera 7, Internet Explorer 6, Internet Explorer 5 on Windows
       -- Opera 7 fails 
    */
    
    function getAllChildren(e) {
      // Returns all children of element. Workaround required for IE5/Windows. Ugh.
      return e.all ? e.all : e.getElementsByTagName('*');
    }
    
    document.getElementsBySelector = function(selector) {
      // Attempt to fail gracefully in lesser browsers
      if (!document.getElementsByTagName) {
        return new Array();
      }
      // Split selector in to tokens
      var tokens = selector.split(' ');
      var currentContext = new Array(document);
      for (var i = 0; i < tokens.length; i++) {
        token = tokens[i].replace(/^\s+/,'').replace(/\s+$/,'');;
        if (token.indexOf('#') > -1) {
          // Token is an ID selector
          var bits = token.split('#');
          var tagName = bits[0];
          var id = bits[1];
          var element = document.getElementById(id);
          if (tagName && element.nodeName.toLowerCase() != tagName) {
            // tag with that ID not found, return false
            return new Array();
          }
          // Set currentContext to contain just this element
          currentContext = new Array(element);
          continue; // Skip to next token
        }
        if (token.indexOf('.') > -1) {
          // Token contains a class selector
          var bits = token.split('.');
          var tagName = bits[0];
          var className = bits[1];
          if (!tagName) {
            tagName = '*';
          }
          // Get elements matching tag, filter them for class selector
          var found = new Array;
          var foundCount = 0;
          for (var h = 0; h < currentContext.length; h++) {
            var elements;
            if (tagName == '*') {
                elements = getAllChildren(currentContext[h]);
            } else {
                elements = currentContext[h].getElementsByTagName(tagName);
            }
            for (var j = 0; j < elements.length; j++) {
              found[foundCount++] = elements[j];
            }
          }
          currentContext = new Array;
          var currentContextIndex = 0;
          for (var k = 0; k < found.length; k++) {
            if (found[k].className && found[k].className.match(new RegExp('\\b'+className+'\\b'))) {
              currentContext[currentContextIndex++] = found[k];
            }
          }
          continue; // Skip to next token
        }
        // Code to deal with attribute selectors
        if (token.match(/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/)) {
          var tagName = RegExp.$1;
          var attrName = RegExp.$2;
          var attrOperator = RegExp.$3;
          var attrValue = RegExp.$4;
          if (!tagName) {
            tagName = '*';
          }
          // Grab all of the tagName elements within current context
          var found = new Array;
          var foundCount = 0;
          for (var h = 0; h < currentContext.length; h++) {
            var elements;
            if (tagName == '*') {
                elements = getAllChildren(currentContext[h]);
            } else {
                elements = currentContext[h].getElementsByTagName(tagName);
            }
            for (var j = 0; j < elements.length; j++) {
              found[foundCount++] = elements[j];
            }
          }
          currentContext = new Array;
          var currentContextIndex = 0;
          var checkFunction; // This function will be used to filter the elements
          switch (attrOperator) {
            case '=': // Equality
              checkFunction = function(e) { return (e.getAttribute(attrName) == attrValue); };
              break;
            case '~': // Match one of space seperated words 
              checkFunction = function(e) { return (e.getAttribute(attrName).match(new RegExp('\\b'+attrValue+'\\b'))); };
              break;
            case '|': // Match start with value followed by optional hyphen
              checkFunction = function(e) { return (e.getAttribute(attrName).match(new RegExp('^'+attrValue+'-?'))); };
              break;
            case '^': // Match starts with value
              checkFunction = function(e) { return (e.getAttribute(attrName).indexOf(attrValue) == 0); };
              break;
            case '$': // Match ends with value - fails with "Warning" in Opera 7
              checkFunction = function(e) { return (e.getAttribute(attrName).lastIndexOf(attrValue) == e.getAttribute(attrName).length - attrValue.length); };
              break;
            case '*': // Match ends with value
              checkFunction = function(e) { return (e.getAttribute(attrName).indexOf(attrValue) > -1); };
              break;
            default :
              // Just test for existence of attribute
              checkFunction = function(e) { return e.getAttribute(attrName); };
          }
          currentContext = new Array;
          var currentContextIndex = 0;
          for (var k = 0; k < found.length; k++) {
            if (checkFunction(found[k])) {
              currentContext[currentContextIndex++] = found[k];
            }
          }
          // alert('Attribute Selector: '+tagName+' '+attrName+' '+attrOperator+' '+attrValue);
          continue; // Skip to next token
        }
        
        if (!currentContext[0]){
        	return;
        }
        
        // If we get here, token is JUST an element (not a class or ID selector)
        tagName = token;
        var found = new Array;
        var foundCount = 0;
        for (var h = 0; h < currentContext.length; h++) {
          var elements = currentContext[h].getElementsByTagName(tagName);
          for (var j = 0; j < elements.length; j++) {
            found[foundCount++] = elements[j];
          }
        }
        currentContext = found;
      }
      return currentContext;
    }
    
    /* That revolting regular expression explained 
    /^(\w+)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/
      \---/  \---/\-------------/    \-------/
        |      |         |               |
        |      |         |           The value
        |      |    ~,|,^,$,* or =
        |   Attribute 
       Tag
    */



/app/webroot/behaviourRules.php
```````````````````````````````

::

    
    <?php
    /**
     * Part of the BehaviourHelper
     * 
     * About: This file acts as Javascript and outputs the 
     * content of the file placed in /tmp/ by the Helper.
     */
    	// known to work in all browsers
    	header("Content-type: application/x-javascript");
    	
    	// relative path to the temp-directory
    	define('PATH_TO_TEMP', '../../tmp/behaviourTmp.js');
    	
    	// check if it there is a file yet. 
    	if(file_exists(PATH_TO_TEMP)) {
    		echo file_get_contents(PATH_TO_TEMP); 
    	} else {
    		echo "// empty"; 
    	}
    ?>


.. meta::
    :title: Behaviour.js Helper
    :description: CakePHP Article related to prototype,behaviour,Helpers
    :keywords: prototype,behaviour,Helpers
    :copyright: Copyright 2006 
    :category: helpers

