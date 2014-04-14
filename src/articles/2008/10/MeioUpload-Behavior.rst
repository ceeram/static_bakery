MeioUpload Behavior
===================

by vbmendes on October 22, 2008

A behavior to make the file uploads as simple as defining variables.
After about one year trying to make the upload simple in CakePHP, I
finally finished an upload behavior based on digital spaghetti's
(`http://digitalspaghetti.tooum.net/`_) upload behavior (`http://digit
alspaghetti.tooum.net/switchboard/blog/2497:Upload_Behavior_for_CakePH
P_12`_) and I am glad to show it for you.

You just have to set the MeioUpload Behavior:

Model Class:
````````````

::

    <?php 
    class MyModel extends AppModel {
    	var $actsAs = array('MeioUpload' => array(
    		'filename' => array(
    			'dir' => 'files/images',
    		)
    	);		
    }
    ?>

And add the file input in the form:

View Template:
``````````````

::

    
    <?php echo $form->create('Produto',array('type' => 'file'));?>
    <?php echo $form->input('filename', array('type' => 'file')); ?>

Here is the link to the snippet with the behavior code:
`http://cakeforge.org/snippet/detail.php?type=snippet=226`_
And here id the link to the documentation:
`http://www.meiocodigo.com/projects/meioupload`_

.. _=226: http://cakeforge.org/snippet/detail.php?type=snippet&id=226
.. _http://www.meiocodigo.com/projects/meioupload: http://www.meiocodigo.com/projects/meioupload
.. _http://digitalspaghetti.tooum.net/: http://digitalspaghetti.tooum.net/
.. _http://digitalspaghetti.tooum.net/switchboard/blog/2497:Upload_Behavior_for_CakePHP_12: http://digitalspaghetti.tooum.net/switchboard/blog/2497:Upload_Behavior_for_CakePHP_12
.. meta::
    :title: MeioUpload Behavior
    :description: CakePHP Article related to behavior,upload,file upload,Behaviors
    :keywords: behavior,upload,file upload,Behaviors
    :copyright: Copyright 2008 vbmendes
    :category: behaviors

