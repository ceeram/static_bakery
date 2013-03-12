

PDF helper using pdf php
========================

by %s on January 18, 2007

This Article is strongly based on the old wiki entry by Ozly all
credit goes to him all errors can be attributed to me. "This class is
designed to provide a non-module, non-commercial alternative to
dynamically creating PDF documents from within PHP. Obviously this
will not be quite as quick as the module alternatives, but it is
surprisingly fast, this demonstration page is almost a worst case due
to the large number of fonts which are displayed. There are a number
of features which can be within a PDF document that it is not at the
moment possible to use with this class, but I feel that it is useful
enough to be released."


Requirements
~~~~~~~~~~~~

Download the pdf class files from SourceForge
`http://sourceforge.net/project/showfiles.php?group_id=45168`_ File
Desc : pdfClassesAndFonts_009e.zip @ 434 KB

Tested on the following environments :

Cake version

#. 0.10.0.1076_dev
#. 0.10.0.1217_alpha
#. 0.10.1.1248_alpha
#. 0.10.3.1311pre_beta
#. I'm gong to release 0.10.5.1701_beta
#. Rc1 Rc2

Operating System

#. Windows 2000
#. Windows XP



Steps
`````

1.Unzip pdfClassesAndFonts_009e.zip

the zip file contains the following files

#. ros.jpg
#. readme.pdf
#. readme.php
#. class.ezpdf.php
#. class.pdf.php
#. /fonts folder

2. Rename class.ezpdf.php to pdf.php

3. Edit pdf.php. See Instructions below


Original : pdf.php

::

    
    	include_once('class.pdf.php');
    	
    	class Cezpdf extends Cpdf {
    	//==============================================================================
    	// more comments here
    	
    	var $ez=array('fontSize'=>10); // used for storing most of the page configuration parameters
    	var $y; // this is the current vertical positon on the page of the writing point, very important
    	var $ezPages=array(); // keep an array of the ids of the pages, making it easy to go back and add page numbers etc.
    	var $ezPageCount=0;
    	
    	// ------------------------------------------------------------------------------
    	
    	function Cezpdf($paper='a4',$orientation='portrait'){
     


Change the class name and the class constructor from Cezpdf to
pdfHelper . See instruction below

Modified : pdf.php

Helper Class:
`````````````

::

    <?php 
    	include_once('class.pdf.php');
    	
    	class pdfHelper extends Cpdf {
    	//==============================================================================
    	// more comments here
    	
    	var $ez=array('fontSize'=>10); // used for storing most of the page configuration parameters
    	var $y; // this is the current vertical positon on the page of the writing point, very important
    	var $ezPages=array(); // keep an array of the ids of the pages, making it easy to go back and add page numbers etc.
    	var $ezPageCount=0;
    	
    	// ------------------------------------------------------------------------------
    	
    	function pdfHelper($paper='a4',$orientation='portrait'){
     ?>


Yup! that is all you need to change in the pdf class, and we are
almost done. We just need to figure out how we can make it work inside
CakePHP.

4. Copy pdf.php and class.pdf.php to the CAKE_ROOT/app/views/helpers/
folder.

5. Copy /fonts folder to the CAKE_ROOT/app/webroot/ folder

6. Create a pdf.thtml layout template in CAKE_ROOT/app/views/layouts/
and put the following code inside. See example

CAKE_ROOT\app\views\layouts\pdf.thtml

View Template:
``````````````

::

    
    <?php 
    header("Content-type: application/pdf");
    echo $content_for_layout;
    ?>

Let's try it

I would assume you already have your database and Cake model in place.
So in your controller use the pdf class by adding it to the var
$helpers = array('pdf') inside your class controller. In the example
below the Cake model is called test in CAKE_ROOT/app/models/test.php.
See example below:


CAKE_ROOT/app/controllers/tests_controller.php

Controller Class:
`````````````````

::

    <?php 
    	class TestsController extends AppController 
    	{
    		var $name = 'Tests';
    		var $helpers = array('pdf'); // this will use the pdf.php class
    		
    		function index()
    		{
    			$this->layout = 'pdf'; //this will use the pdf.thtml layout
    			$this->set('data','hello world!');
    			$this->render();
    		}
    	}
    ?>

Now for the views, create a folder named tests inside
CAKE_ROOT/app/views/ and create an index.thtml template inside it.
Finally, edit index.thtml and put the following sample code inside:

CAKE_ROOT\app\views\tests\index.thtml

View Template:
``````````````

::

    
    <?php
     
    	$pdf->selectFont('fonts/Courier-Oblique.afm');
    	$pdf->ezText($data,10);
    	$pdf->ezStream();	
    ?>

See it
http://localhost/your_cake_root/tests/index

If you can see the "hello world" message inside the PDF running on top
of your browser, then you are done.

Note:

Make sure that you have Adobe Reader (or the equivalent) installed,
and that the plugin is
working properly on your browser e.g. Internet explorer or Firefox.

Otherwise, output the PDF result as a PDF file.

Happy Baking ! - Ozly

.. _http://sourceforge.net/project/showfiles.php?group_id=45168: http://sourceforge.net/project/showfiles.php?group_id=45168
.. meta::
    :title: PDF helper using pdf php
    :description: CakePHP Article related to pdf,Helpers
    :keywords: pdf,Helpers
    :copyright: Copyright 2007 
    :category: helpers

