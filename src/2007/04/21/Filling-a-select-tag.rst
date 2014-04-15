Filling a select tag
====================

by KiltOtter on April 21, 2007

On my hunt for a simple explanation on how to do things in CakePHP, I
found I was coming up with ingredients, but not getting any baking
done. This short snippet is the first example in how to fill a select
tag using Cake.
In your controller use something like:

::

    
    function index()
    {
    	$this->set('studyareas',$this->Studyarea->findAll());
    	$this->set('studyarealist', $this->Studyarea->generateList( 
    				null, "areaName ASC", null, "{n}.Studyarea.id", 
    				"{n}.Studyarea.areaName")
    				);
    }

The snippet above gets all the studyareas from the studyarea table,
and then saves the id and areaName in the atudyarealist variable.

In the view we use something like:

::

    
    	<?php echo $html->selectTag('Studyareas/areaName', $studyarealist);?>



.. author:: KiltOtter
.. categories:: articles, tutorials
.. tags:: select list,Tutorials

