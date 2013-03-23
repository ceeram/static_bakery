CodeCheck Plugin
================

by %s on November 05, 2009

A plugin to check whether your code follows Cake conventions.
It is unlikely that I will keep the code blocks below up to date.
Please obtain the plugin from `http://github.com/petteyg/code_check`_.

--- 2011/11/12 - Branch 'master' on GitHub is updated for CakePHP 2.0;
use branch '1.x' for 1.2/1.3

Following coding conventions makes it easier for everyone to read
code. CakePHP has a set of coding standards, available at
`http://book.cakephp.org/view/509/Coding-Standards`_.

If you've written lots of code and aren't sure it follows the
conventions, it can be hard to go back and read through every file to
fix errors. This plugin includes a task to check for errors, and shows
what the code should be, and can even re-write your files for you.

To check your app code, run:
(1.x) cake code convention
(2.0) cake CodeCheck.CodeCheck convention
I've included the whitespace shell as a task, to check your app: cake
code whitespace
(1.x) cake code whitespace
(2.0) cake CodeCheck.CodeCheck whitespace


plugins/code_check/vendors/shells/code.php
``````````````````````````````````````````

::

    
    <?php
    class CodeShell extends Shell {
    
    	/**
    	* Shell tasks
    	*
    	* @var array
    	*/
    	public $tasks = array(
    		'CodeConvention',
    		'CodeWhitespace'
    	);
    
    	/**
    	* Models used by shell
    	*
    	* @var array
    	*/
    	public $uses = array();
    
    	/**
    	* Main execution function
    	*
    	* @return void
    	*/
    	public function main()  {
    		if (!empty($this->args)) {
    			if (!empty($this->args[1])) {
    				$this->args[1] = constant($this->args[1]);
    			} else {
    				$this->args[1] = APP;
    			}
    			$this->{'Code'.ucfirst($this->args[0])}->execute($this->args[1]);
    		} else {
    			$this->out('Usage: cake code type');
    			$this->out('');
    			$this->out('type should be space-separated');
    			$this->out('list of any combination of:');
    			$this->out('');
    			$this->out('convention');
    			$this->out('whitespace');
    		}
    	}
    }
    ?>


plugins/code_check/vendors/shells/tasks/code_convention.php
```````````````````````````````````````````````````````````

::

    
    <?php
    class CodeConventionTask extends Shell {
    
    	/**
    	* Models used by shell
    	*
    	* @var array
    	*/
    	public $uses = array();
    
    	/**
    	* Main execution function
    	*
    	* @return void
    	*/
    	public function execute($root = APP)  {
    		$Folder = new Folder($root);
    		$files = $Folder->findRecursive('.*\.php');
    		$files = array_diff($files, array(__FILE__));
    		$this->out("Checking *.php in ".$root);
    		$grep = 'grep -RPnh "%s" %s';
    		$regex = array();
    
    		$regex['array']['find'] = array('(^\s)=>(^\s)', '(^\s)=>', '=>(^\s)');
    		$regex['array']['replace'] = array('$1 => $2', '$1 =>', '=> $1');
    		$regex['control']['find'] = array('if\(', 'foreach\(', 'for\(', 'while\(', 'switch\(', '\)\{');
    		$regex['control']['replace'] = array('if (', 'foreach (', 'for (', 'while (', 'switch (', ') {');
    		$regex['function']['find'] = array('(function [a-zA-Z_\x7f\xff][a-zA-Z0-9_\x7f\xff]+) \(');
    		$regex['function']['replace'] = array('$1(');
    
    		$types = array_keys($regex);
    
    		foreach ($files as $file) {
    			$contents = file_get_contents($file);
    			foreach ($types as $t) {
    				for ($i = 0; $i < count($regex[$t]['find']); $i++) {
    					$f = $regex[$t]['find'][$i];
    					$grepd = exec(sprintf($grep, $f, $file), $output);
    					if (!empty($grepd)) {
    						foreach ($output as $line) {
    							$this->out('');
    							$this->out('');
    							$this->out($this->shortPath($file));
    							preg_match('/[0-9]+/', $line, $linenumber);
    							preg_match('/(?<=:)\s+(.*)/', $line, $linecode);
    							$this->out('Line '.str_pad($linenumber[0], 4, "0", STR_PAD_LEFT).': '.$linecode[1]);
    							$r = $regex[$t]['replace'][$i];
    							$replace = preg_replace('/'.$f.'/', $r, $linecode[1]);
    							$this->out('Change to: '.$replace);
    							$fix = $this->in('Fix it?', array('y', 'n'), 'y');
    							if ($fix) {
    								$contents = preg_replace('/'.$f.'/', $r, $contents);
    								file_put_contents($file, $contents);
    							}
    						}
    					}
    				}
    			}
    		}
    	}
    
    }
    ?>


plugins/code_check/vendors/shells/tasks/code_whitespace.php
```````````````````````````````````````````````````````````

::

    
    <?php
    class CodeWhitespaceTask extends Shell {
    
    	/**
    	* Models used by shell
    	*
    	* @var array
    	*/
    	public $uses = array();
    
    	/**
    	* Main execution function
    	*
    	* @return void
    	*/
    	public function execute($root = APP) {
    		$Folder = new Folder($root);
    		$files = $Folder->findRecursive('.*\.php');
    		$this->out("Checking *.php in ".$root);
    		foreach ($files as $file) {
    				$contents = file_get_contents($file);
    				if (preg_match('/^[\n\r|\n\r|\n|\r|\s]+\<\?php/', $contents)) {
    						$this->out('!!!contains leading whitespaces: '. $this->shortPath($file));
    				}
    				if (preg_match('/\?\>[\n\r|\n\r|\n|\r|\s]+$/', $contents)) {
    						$this->out('!!!contains trailing whitespaces: '. $this->shortPath($file));
    				}
    		}
    	}
    
    }
    ?>



.. _http://github.com/petteyg/code_check: http://github.com/petteyg/code_check
.. _http://book.cakephp.org/view/509/Coding-Standards: http://book.cakephp.org/view/509/Coding-Standards
.. meta::
    :title: CodeCheck Plugin
    :description: CakePHP Article related to code,coding,convention,Plugins
    :keywords: code,coding,convention,Plugins
    :copyright: Copyright 2009 
    :category: plugins

