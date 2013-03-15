

Flexible controller and modelpaths
==================================

by %s on May 09, 2007

This short tutorial will show you how to organize your model and
controller in subfolders of the controllers and models folders!
Cake enables you to define paths in the config/bootstrap.php file. If
you add some more logic to that file, you will be able to create
subfolders in the /controller and /model folders and place some
controllers and models there. This could be usefull when baking large
cakes and if you want to maintain some form of overview.

Here's how:

in config/bootstrap.php add something like:

::

    
    $modelPaths = array();
    $controllerPaths = array();
    
    function enableSubFoldersOn($baseDir, &$var) { 		
      $cwd =getcwd();
      chdir($baseDir);
      $dirs = glob("*", GLOB_ONLYDIR);
      if(sizeof($dirs) > 0) { 
        foreach($dirs as $dir) { 
          $var[] = $baseDir.DS.$dir.DS;
        }
      }
      chdir($cwd);
    }
    
    enableSubFoldersOn(ROOT.DS.APP_DIR.'/controllers', $controllerPaths);
    enableSubFoldersOn(ROOT.DS.APP_DIR.'/models', $modelPaths);

Now you can order your controllers and models in subfolders!

I hope this is useful to you!

.. meta::
    :title: Flexible controller and modelpaths
    :description: CakePHP Article related to subfolders,organize,bootstrap,Tutorials
    :keywords: subfolders,organize,bootstrap,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

