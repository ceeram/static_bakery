PDF helper using fpdf
=====================

by sdevore.myopenid.com on January 27, 2007

How to easily create pdf output from CakePHP using the Freely licenced
fpdf library found at http://fpdf.org/


Download
````````

Download the FPDF files from FPDF.org:
`http://fpdf.org/en/download.php`_ This example was tested with
version 1.53 but should work with any newer ones that are released.



Steps
`````

1.Unarchive the download (in my case fpdf153.zip)

the zip file contains the following files

#. doc/
#. FAQ.htm
#. font/
#. fpdf.css
#. fpdf.php
#. histo.htm
#. install.txt
#. tutorial/

2. Create a new directory in your vendors directory (either the one in
your app/ or in the one in the shared vendors directory at the same
level as cake app and others it shouldn't matter) called fpdf, in to
this folder you need to copy at a minimum fpdf.php and the font
directory.

3. In your app's views/helpers directory create a file called fpdf.php
and add the following code


Helper Class:
`````````````

::

    <?php 
    vendor('fpdf/fpdf');
    
    if (!defined('PARAGRAPH_STRING')) define('PARAGRAPH_STRING', '~~~');
    
    class fpdfHelper extends FPDF {
    	
    	/**
    	* Allows you to change the defaults set in the FPDF constructor
    	*
    	* @param string $orientation page orientation values: P, Portrait, L, or Landscape	(default is P)
    	* @param string $unit values: pt (point 1/72 of an inch), mm, cm, in. Default is mm
    	* @param string $format values: A3, A4, A5, Letter, Legal or a two element array with the width and height in unit given in $unit
    	*/
    	function setup ($orientation='P',$unit='mm',$format='A4') {
    		$this->FPDF($orientation, $unit, $format); 
    	}
    	
    	/**
    	* Allows you to control how the pdf is returned to the user, most of the time in CakePHP you probably want the string
    	*
    	* @param string $name name of the file.
    	* @param string $destination where to send the document values: I, D, F, S
    	* @return string if the $destination is S
    	*/
    	function fpdfOutput ($name = 'page.pdf', $destination = 's') {
    		// I: send the file inline to the browser. The plug-in is used if available. 
    		//	The name given by name is used when one selects the "Save as" option on the link generating the PDF.
    		// D: send to the browser and force a file download with the name given by name.
    		// F: save to a local file with the name given by name.
    		// S: return the document as a string. name is ignored.
    		return $this->Output($name, $destination);
    	}
    }
    ?>


6. Create a pdf.thtml layout template in your app's /views/layouts/
and put the following code inside. See example


View Template:
``````````````

::

    
    <?php
    header("Content-type: application/pdf");
    echo $content_for_layout;
    ?>

Now in one of your controllers you can create a new index function for
making pdf



Controller Class:
`````````````````

::

    <?php 
    
    	class TestsController extends AppController 
    	{
    		var $name = 'Tests';
    		var $helpers = array('pdf'); // this will use the pdf.php class
    		
    		function indexPdf()
    		{
    			$this->layout = 'pdf'; //this will use the pdf.thtml layout
    			$this->set('data','hello world!');
    			$this->render();
    		}
    	}
    
    ?>

Now for the views, create a folder named tests inside /app/views/ and
create an index_pdf_.thtml template inside it. Finally, edit
index_pdf.thtml and put the following sample code inside:



View Template:
``````````````

::

    
    <?php
    	$fpdf->AddPage();
    	$fpdf->SetFont('Arial','B',16);
    	$fpdf->Cell(40,10,$data);
    	echo $fpdf->fpdfOutput();	
    ?>

Now this is not very exciting or useful with CakePHP so let's look at
how to extend it so that we can make tables and add page headers and
footers (this part will be based on a couple of the tutorials found at
the FPDF site.)


Extending our FPDF Helper
`````````````````````````


Adding support for tables
+++++++++++++++++++++++++

Edit the helper file and add the following (I removed the comments
from the existing functions for brevity)


Helper Class:
`````````````

::

    <?php 
    vendors('fpdf/fpdf');
    
    if (!defined('PARAGRAPH_STRING')) define('PARAGRAPH_STRING', '~~~');
    
    class fpdfHelper extends FPDF {
    	var $title;
    	function setup ($orientation='P',$unit='mm',$format='A4') {
    		$this->FPDF($orientation, $unit, $format); 
    	}
    	
    	function fpdfOutput ($name = 'page.pdf', $destination = 's') {
    		return $this->Output($name, $destination);
    	}
    	
    	function Header()
    	{
    	    //Logo
    	    $this->Image(WWW_ROOT.DS.'img/logo.png',10,8,33);  
    		// you can use jpeg or pngs see the manual for fpdf for more info
    	    //Arial bold 15
    	    $this->SetFont('Arial','B',15);
    	    //Move to the right
    	    $this->Cell(80);
    	    //Title
    	    $this->Cell(30,10,$this->title,1,0,'C');
    	    //Line break
    	    $this->Ln(20);
    	}
    
    	//Page footer
    	function Footer()
    	{
    	    //Position at 1.5 cm from bottom
    	    $this->SetY(-15);
    	    //Arial italic 8
    	    $this->SetFont('Arial','I',8);
    	    //Page number
    	    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    	}
    }
    ?>

Now let's update the view for this to be demonstrated


View Template:
``````````````

::

    
    <?php
    	$fpdf->AliasNbPages();  // allows us to do the page numbering
    	$fpdf->AddPage();
    	$fpdf->setTitle('Our Cool PDF');
    	$pdf->SetFont('Times','',12);
    	for($i=1;$i<=40;$i++)
    	    $fpdf->Cell(0,10,'Printing line number '.$i,0,1); // just fill up the page
    	echo $fpdf->fpdfOutput();
    ?>

now you should get a page with a bunch of meaningless lines but with
page breaks and headers and footers

ok one more extension to let you have simple tables, I'm only going to
add the table functions to our helper (you probably know to leave the
rest)


Helper Class:
`````````````

::

    <?php 
    	function basicTable($header,$data)
    	{
    	    //Header
    	    foreach($header as $col)
    	        $this->Cell(40,7,$col,1);
    	    $this->Ln();
    	    //Data
    	    foreach($data as $row) {
    			foreach($row as $col) {
    				$this->Cell(40,6,$col,1);
    			}
    			$this->Ln();
    	    }
    	}
    ?>

then the view becomes (assuming you now load the $data with some real
data lets say it's a model with id, country, capital, area and
population)


View Template:
``````````````

::

    
    <?php
    	
    	//Column titles
    	$header=array('Id','Country','Capital','Area (sq km)','Pop. (thousands)');
    	//Data loading
    	$pdf->SetFont('Arial','',14);
    	$pdf->AddPage();
    	$pdf->basicTable($header,$data);
    	echo $fpdf->fpdfOutput();
    ?>

Too boring you say ok let's get a little fancier, add this function to
your helper


Helper Class:
`````````````

::

    <?php 
    
    function fancyTable($header, $colWidth, $data) {
        //Colors, line width and bold font
        $this->SetFillColor(255,0,0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        //Header
        
        for($i=0;$i<count($header);$i++)
            $this->Cell($colWidth[i],7,$header[$i],1,0,'C',1);
        $this->Ln();
        //Color and font restoration
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        //Data
        $fill=0;
        foreach($data as $row) {
    		$i = 0;
            foreach($row as $col) {
    			$this->Cell($colWidth[$i++],6,$col,'LR',0,'L',$fill);
    		}
            $this->Ln();
            $fill=!$fill;
        }
        $this->Cell(array_sum($w),0,'','T');
    }
    ?>



View Template:
``````````````

::

    
    <?php
    	
    	//Column titles
    	$header=array('Id','Country','Capital','Area (sq km)','Pop. (thousands)');
    	//Data loading
    	$pdf->SetFont('Arial','',14);
    	$pdf->AddPage();
    	$colWidth = array(40,35,40,45);
    	$pdf->fancyTable($header,$colWidth, $data);
    	echo $fpdf->fpdfOutput();
    ?>

Now the data will be in a nice colored table, now you can see the
basics and you can see how you can extend it by adding functions, this
can be used to add stuff from the scripts section
`http://fpdf.org/en/script/index.php`_ to further extend your basic
pdf helper. There is a lot of stuff there, like you could make a label
helper from `http://fpdf.org/en/script/script29.php`_ or a
invoiceHelper from `http://fpdf.org/en/script/script20.php`_ the
possiblities are not exactly endless but they are pretty extensive
even if you know very little about PDFs

.. _http://fpdf.org/en/script/index.php: http://fpdf.org/en/script/index.php
.. _http://fpdf.org/en/download.php: http://fpdf.org/en/download.php
.. _http://fpdf.org/en/script/script29.php: http://fpdf.org/en/script/script29.php
.. _http://fpdf.org/en/script/script20.php: http://fpdf.org/en/script/script20.php
.. meta::
    :title: PDF helper using fpdf
    :description: CakePHP Article related to pdf,Helpers
    :keywords: pdf,Helpers
    :copyright: Copyright 2007 sdevore.myopenid.com
    :category: helpers

