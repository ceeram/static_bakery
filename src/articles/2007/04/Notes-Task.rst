Notes Task
==========

by joelmoss on April 22, 2007

A source-annotations extractor task for bake2. This allows you to add
FIXME, OPTIMIZE, and TODO comments to your source code that can then
be extracted in concert with bake2 notes (shows all), bake2 notes
fixme, bake2 notes optimize and bake2 notes todo.
A source-annotations extractor task for bake2.

This allows you to add FIXME, OPTIMIZE, and TODO comments to your
source code that can then be extracted in concert with bake2 notes
(shows all), bake2 notes fixme, bake2 notes optimize and bake2 notes
todo.

Just add as comments anywhere in your code. For example:

# TODO: this is my first todo item
# FIXME: please fix this
# OPTIMIZE: this has got to be optimised!

Then run:

bake2.php notes app_alias

and the task will check all your PHP scripts in your app for any of
the above notes and print them out in a nice readable format, with
line numbers, etc.

You can find specific notes:

bake2.php notes app_alias todo
bake2.php notes app_alias fixme
bake2.php notes app_alias optimise

::

    
    /**
     * The NotesTask is a source-annotations extractor task for bake2, that allows you to add FIXME, OPTIMIZE,
     * and TODO comments to your source code that can then be extracted in concert with this task
     *
     * PHP versions 4 and 5
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @filesource
     * @copyright		Copyright 2006-2007, Joel Moss
     * @link				http://joelmoss.info
     * @since			CakePHP(tm) v 1.2
     * @version			$Version: 1.0 $
     * @modifiedby		$LastChangedBy: joelmoss $
     * @lastmodified	$Date: 2007-02-27 (Tues, 27 Feb 2007) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     * 
     * 
     * @Changelog
     * 
     * v 1.0
     *  [+] Initial code offering
     *  
     */
    
    uses('folder');
    
    class NotesTask extends BakeTask
    {
      var $notes = array();
      var $type = null;
      var $dirs = array(
        'config',
        'controllers',
        'models',
        'plugins',
      );
      
    	function execute($params)
    	{
    		$this->welcome();
    		
    		if (isset($params[0]))
    		{
    		  if ($params[0] == 'todo')
      		{
      			$this->type = 'TODO';
      		}
    		  elseif ($params[0] == 'fixme')
      		{
      			$this->type = 'FIXME';
      		}
    		  elseif ($params[0] == 'optimise' || $params[0] == 'optimize')
      		{
      			$this->type = 'OPTIMIZE';
      		}
    		  elseif ($params[0] == 'help')
      		{
      			$this->help();
      		}
    		}
    
    		$this->read();
    		foreach ($this->dirs as $d) $this->read($d, true);
    		
    		foreach ($this->notes as $file => $types)
    		{
    			$this->out("$file:");
    			$this->out();
    			foreach ($types as $type => $notes)
    			{
    			  foreach ($notes as $ln => $note)
    			  {
    			    $this->out("   * [$ln] [$type] $note");
    			  }
    			}
    			$this->out();
    		}
    		$this->hr();
      }
        
      function read($dir = null, $recursive = false)
      {
        $notes = array();
        $path = CORE_PATH.APP_PATH.$dir;
    		
        $folder = new Folder(APP_PATH.$dir);
        $fold = $recursive ? $folder->findRecursive('.*\.php') : $folder->find('.*\.php');
        foreach ($fold as $file)
        {
          $file = $recursive ? $file : $path.$file;
          $file_path = r(CORE_PATH.APP_PATH, '', $file);
          $lines = file($file);
          $ln = 1;
          foreach ($lines as $line)
          {
          	if ((is_null($this->type) || $this->type == 'TODO') &&
          	     preg_match("/[#\*\\/\\/]\s*TODO:\s*(.*)/", $line, $match))
          	{
          	  $this->notes[$file_path]['TODO'][$ln] = $match[1];
          	}
          	if ((is_null($this->type) || $this->type == 'OPTIMIZE') &&
          	     preg_match("/[#\*\\/\\/]\s*OPTIMIZE|OPTIMISE:\s*(.*)/", $line, $match))
          	{
          	  $this->notes[$file_path]['OPTIMIZE'][$ln] = $match[1];
          	}
          	if ((is_null($this->type) || $this->type == 'FIXME') &&
          	     preg_match("/[#\*\\/\\/]\s*FIXME:\s*(.*)/", $line, $match))
          	{
          	  $this->notes[$file_path]['FIXME'][$ln] = $match[1];
          	}
          	$ln++;
          }
        }
        return $this->notes;
      }
      
      function help()
      {
        $this->out("This task allows you to add FIXME, OPTIMIZE, and TODO comments to your source");
        $this->out("code that can then be extracted in concert with bake2 notes (shows all), bake2");
        $this->out("notes fixme, bake2 notes optimize and bake2 notes todo.");
        $this->out("Usage: bake2 notes app_alias [todo|optimize|fixme]");
        $this->hr();
        exit;
      }
      
      function out($str='', $newline=true)
      {
        $nl = $newline ? "\n" : "";
        echo "  $str$nl";
      }
      function hr()
      {
        echo "\n  ----------------------------------------------------------------------------\n";
      }
      function err($str)
      {
        $this->out('');
        $this->out('');
        $this->out($str);
        $this->out('');
        $this->out('');
        exit;
      }
      function welcome()
      {
        $this->out('');
        $this->hr();
        $this->out('      __  __  _  _  __  __  _  _  __          __  ___  __  __  ');
        $this->out('     |   |__| |_/  |__ |__] |__| |__]   |\ | |  |  |  |_  |__  ');
        $this->out('     |__ |  | | \_ |__ |    |  | |      | \| |__|  |  |__  __| ');
        $this->hr();
        $this->out('');
      }
      
    }


.. meta::
    :title: Notes Task
    :description: CakePHP Article related to task,notes,annotation,bake2,Components
    :keywords: task,notes,annotation,bake2,Components
    :copyright: Copyright 2007 joelmoss
    :category: components

