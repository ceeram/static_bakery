Maintaining an Application-independant Code Library
===================================================

As a developper for my web company, I often write cake
components/helpers/etc that I reuse often throughout my various client
projects. For a while now I've been looking for a way to use and
manage this code with CVS in a way that avoids duplication. I finally
found the solution.
In trying to find a way to manage all my code snippets, I finally
settled on a solution that I think merits everyone's attention since
it's so simple, so maintainable and so useful.

This idea is as follows, I set up a code library on my filesystem (I
use windows, but this will work anywhere) with the following file
structure:

::

    
    D:/codelib/cake/
    D:/codelib/cake/app/
    D:/codelib/cake/app/controllers/
    D:/codelib/cake/app/controllers/components/
    D:/codelib/cake/app/models/
    D:/codelib/cake/app/models/behaviors/
    D:/codelib/cake/app/views/
    D:/codelib/cake/app/views/helpers/
    D:/codelib/cake/app/plugins/
    D:/codelib/cake/app/vendors/

The idea here is that I have a copy of my cake app/ file structure
where I can put code that I will reuse in all my applications. This
method now will allow any files I put in this code library to be
available in any cake application I make in the future.

The simplicity in this approach is outlined by this example; Suppose I
made a "FileHandlerComponent", the use of this component would simply
go as follows:


+ Save my file_handler.php component file in
  D:/codelib/cake/app/controllers/components/file_handler.php
+ In any of my cake applications , I would simply put the line "var
  $components = array('FileHandler');" in a controller and the
  FileHandler component will be loaded properly.

That's all!

To do this, first create the above code library file structure. Now
create a file D:/codelib/cake/codelib.inc with the following contents
(adjust directories appropriately):

::

    
    <?php
    
    $PATH_TO_CODE_LIBRARY = dirname(__FILE__) . DS;
    
    $modelPaths = array($PATH_TO_CODE_LIBRARY.'app'.DS.'models'.DS);
    $behaviorPaths = array($PATH_TO_CODE_LIBRARY.'app'.DS.'models'.DS.'behaviours'.DS);
    $controllerPaths = array($PATH_TO_CODE_LIBRARY.'app'.DS.'controllers'.DS);
    $componentPaths = array($PATH_TO_CODE_LIBRARY.'app'.DS.'controllers'.DS.'components'.DS);
    $viewPaths = array($PATH_TO_CODE_LIBRARY.'app'.DS.'views'.DS);
    $helperPaths = array($PATH_TO_CODE_LIBRARY.'app'.DS.'views'.DS.'helpers'.DS);
    $pluginPaths = array($PATH_TO_CODE_LIBRARY.'app'.DS.'plugins'.DS);
    $vendorPaths = array($PATH_TO_CODE_LIBRARY.'app'.DS.'vendors'.DS);
    
    ?>

Once that is complete, all you have to do to make this code library
available to your cake app is open your /app/config/bootstrap.php and
put the following line in there:

::

    
    include('D:'.DS.'codelib'.DS.'cake'.DS.'codelib.inc');

Now, when you enter the following code in your controller:

::

    
    var components = array('FileHandler');

It will first check your application's /app/controller/components/
folder for a file_handler.php file. If it's there, it will use the
local one, otherwise it will check in your code library to see if the
file exists there.

The advantage of this is that now you can develop a library of
application-wide software functionality using cake, and this library
can be maintained via CVS or SVN, and no more copy-paste related
synchronization issues!


.. author:: Sake
.. categories:: articles, tutorials
.. tags:: config,configure,repository,code_management,Tutorials

