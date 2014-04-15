ViewInFile component
====================

by francky06l on August 28, 2007

Sometimes in needed to output a view in a file. Most of the time for
debugging, but also for some web service (xml) where you might need to
send (by mail as a copy) an xml file reflecting the answer of the
webservice.
I came up with a Compoment and an Helper. I am sur there are better
ways to do all this, do not hesitate to comments, even for saying how
bad it is.

The component, store in controllers/components/view_in_file.php


Component Class:
````````````````

::

    <?php 
    <?php
    /*
     * ViewInfile component
     * will ouput the rendered view in a file as well
     *
     */
    class ViewInFileComponent extends Object
    {
         var $directory   = TMP;
         var $fileName    = '';
         var $Controller  = null;
         
      	 function startup(&$controller)
      	 {
    		     $this->Controller = & $controller;
         }
         
         /* set the file name for outpout, 
          * if clean is set to true the view rendered will be empty (only layout in html and empty div for ajax
          * redirect, if set (cake form ie : /Controllers/action/params), the output will contain javascript to redirect
          *           best usage is with "clean => true"
          */
              
         function setFile($filename, $clean = false, $redirect = null)
         {
            $this->FileName = $this->directory . $filename;
            $this->Controller->set('ViewInFile', array('file' => $this->FileName, 'clean' => $clean, 'redirect' => $redirect));
         }
         
         /* reset the file name for eventual next usage in action (not verified because hasRendered is set to true ..)
         */
         
         function reset()
         {
            $this->FileName = ''; 
         }
    }
    ?>
    ?>

The helper, store in views/helpers/view_in_file.php

Helper Class:
`````````````

::

    <?php 
    <?php
    /**
     * ViewInFile helper. Allow the rendered view to be captured to a file
     */
    class ViewInFileHelper extends Helper 
    {   
    	  /* we use afterRender to get the content of the rendered view and store it in the file (if found) */
    	 
        function afterRender()
        {
    	  	 $lview = ClassRegistry::getObject('view'); 
    
           $OutInFile = $lview->getVar('ViewInFile');
           
           if(!empty($OutInFile) && isset($OutInFile['file']))
           {
              if(isset($OutInFile['clean']) && $OutInFile['clean'] === true)
              {
                 $out = ob_get_clean();
                 ob_start();
              }   
              else
                 $out = ob_get_contents();
              
              /* this might not work in PHP4 ?? */
                         
              file_put_contents($OutInFile['file'], $out);     
    
              if(isset($OutInFile['redirect']) && !empty($OutInFile['redirect']))          
              {                         
                 echo '<script type="text/javascript">';
                 echo 'window.location.href="'.FULL_BASE_URL.$lview->loaded['html']->url($OutInFile['redirect']).'";';               
                 echo '</script>';
              }
    	  	 }   
        }
        
        /* Allow to get the file name into the view itself */
        
        function getOutFileName()
        {
        	 $lview = ClassRegistry::getObject('view'); 
           $OutInFile = $lview->getVar('ViewInFile');
           
           return is_array($OutInFile) && isset($OutInFile['file']) ? $OutInFile['file'] : null;          
        }
    }
    ?>
    ?>

Usage in a sample Contracts controller that has a test() action and of
course a view.


The view
````````
/Contracts/test.ctp

::

    
    <h2>ViewInFile Test</h2>
    <p>This content in file : <?php echo $viewInFile->getOutFileName();?></p>



Controller contract
```````````````````
[p]The function test() is setting the file name, set the clean flag to
true (the existing output will be cleaned) and a redirection after
rendering. Note that after calling $this->render(), you can get the
produced file and do whatever you want of it. For my test, I just show
it into the cake error.log file.

::

    
    <?php
    class ContractsController extends AppController {
    
    	var $name       = 'Contracts';
    	var $helpers    = array('Html', 'Form', 'ViewInFile' );
      var $components = array('ViewInFile');
    
      function test()
      {
      	$this->ViewInFile->setFile('mytestfile.html', true, '/Customers/index');
      	$this->render();
        $a = file_get_contents($this->ViewInFile->FileName);
        LogError("file content : ".$a);
      }
    }
    ?>

Another usage, this will render the view and capture it previously.

::

    
      function test()
      {
      	$this->ViewInFile->setFile('mysecond.html');
      }

Important: the file does not contain the layout, but the view does (if
$clean !== true).
I have tested, mainly for my needs using cake 1.2.0.5427alpha (from
the branch). The redirection works also when it's an ajax request.


.. author:: francky06l
.. categories:: articles, components
.. tags:: views,Components

