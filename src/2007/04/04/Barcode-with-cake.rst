Barcode with cake
=================

How to print barcode with cakephp and pdfb library
These days my collegue Leonello and i had the necessity to print
barcode label to stick on printed document for the documents
management system we have developed in cakephp.
After googling have found this nice library:

`http://chir.ag/tech/download/pdfb/`_
Some testing in pure php and decided that it's a cool system to use.

The integration in cakephp follows these steps:

#. From above page download PDFB Library - Compact Version - 136kb
   (For Production Use)
#. Extract all files in {Cakeroot}/vendors or {App}/vendors (`See
   figure`_)
#. Edit line 10 in file pdfb.php

PHP Snippet:
````````````

::

    <?php 
    /*
    +-----------------------------------------------------------------+
    |   Created by Chirag Mehta - http://chir.ag/tech/download/pdfb   |
    |-----------------------------------------------------------------|
    |                      For PDFB Library                           |
    +-----------------------------------------------------------------+
    */
    
      define('FPDF_FONTPATH','font/');
    ?>



Your pdfb library is now working if you want you can test it so:
Create a controller to make a quick test


PHP Snippet:
````````````

::

    <?php 
    /*
    ** File:     test_pdfb_controller.php
    ** Purpouse: test pdf barcode library
    */
    class TestPdfbController extends AppController {
    	var $name = 'TestPdfb';
    	var $uses = array(); // no models needed
    
    	function barcode() {
    		vendor('pdfb/pdfb');
    
    		$this->autoRender = false;
    
    		$pdf = new PDFB('P', 'mm', array( 75.0, 33.0 ));
    		$pdf->SetMargins(0.0, 0.0);
    		$pdf->SetFont("Helvetica", "", 9.5);
    
    		$pdf->AddPage();
    
    		$doc_id = '1234567';
    		$pdf->BarCode($doc_id, "C39", 0, 4, 75.0, 33.0, 1, 1, 1, 1, "", "PNG");
    
    		$pdf->SetXY(2, 4);
    		$pdf->Cell(0, 0, "Code: 39 - 17 march 2007", 0, 0, 'C');
    
    		$pdf->Rect(0.3, 0.3, 74.4, 32.4);
    
    		$pdf->SetDisplayMode('real');
    		$pdf->Output();
    		$pdf->closeParsers();
    	}
    }
        // BarCode function parameters explanation
        // function BarCode($barcode, $type, $x, $y, $w, $h, $sx, $sy, $xres, $font, $link, $format)
        // $barcode = any alphanumber string or UPC-A valid numeric code
        // $type = one of "C39", "C128A", "C128B", "C128C", "I25", "UPCA"
        // $x, $y = position on the PDF page
        // $w, $h = dimensions of the BarCode image
        // $sx, $sy = X & Y scaling of the BarCode image
        // $xres = thickness of the Bars in the Barcode - X-Resolution (1,2,3)
        // $font = Font size for BarCode value (1,2,3,4,5)
        // $link = URL to link the BarCode to
        // $format = "PNG" (default) or "JPEG"
    
        // If you wish to make high-resolution barcodes:
        // -> use $xres = 2 or $xres = 3
        // -> use $font = 5
        // -> use $w, $h = large dimensions
        // -> use $sx, $sy = (0.5, 0.5) or (0.25, 0.25) to scale it down to desire size.
        // Then zooming into the image you will see that it's quite scalable and high-resolution
    
    ?>

For explanation of standard pdf library functions `go here`_
But we can make things better:

First of all create an helper object

PHP Snippet:
````````````

::

    <?php 
    /*
    ** File: pdf.php
    ** Loaction: views/helpers
     * help for pdf and barcode printing
     * ---------------------------------
     * @package helpers
     */
    vendor('pdfb/pdfb');
    class pdfHelper extends PDFB {
    	var $headerData = null;
    	var $footerData = null;
    
    	function __construct($orientation='P', $unit='mm', $format='A4') {
    		$this->set($orientation, $unit, $format);
    	}
    
    	function set($orientation='P', $unit='mm', $format='A4') {
    		parent::PDFB($orientation, $unit, $format);
    	}
    
    	function Header() { 
    	  // To do: manage headerData array
    	}
    
    	function Footer() { 
    	  // To do: manage footerData array
    	}
    
    }
    ?>

Now create a layout for printing pdf

PHP Snippet:
````````````

::

    <?php 
    /*
    ** File: pdf.thtml
    ** Location: views/layouts
    ** Set content-type for pdf printing
    */
    header("Content-type: application/pdf");
    echo $content_for_layout;
    ?>

Now create the view

PHP Snippet:
````````````

::

    <?php 
    /*
    ** File: barcode.thtml
    ** Location: views/test_pdfb
    */
    	$pdf->set('P', 'mm', array( 75.0, 33.0 ));
    	$pdf->SetMargins(0.0, 0.0);
    	$pdf->SetFont("Helvetica", "", 9.5);
    
    	$pdf->AddPage();
    
    	//$doc_id = '1234567'; // come from controller
    	$pdf->BarCode($doc_id, "C39", 0, 4, 75.0, 33.0, 1, 1, 1, 1, "", "PNG");
    
    	$pdf->SetXY(2, 4);
    	$pdf->Cell(0, 0, "Code: 39 - 17 march 2007", 0, 0, 'C');
    
    	$pdf->Rect(0.3, 0.3, 74.4, 32.4);
    
    	$pdf->SetDisplayMode('real');
    	$pdf->Output();
    	$pdf->closeParsers();
    ?>

And finally modify your controller to reflect all changes

PHP Snippet:
````````````

::

    <?php 
    /*
    ** File:     test_pdfb_controller.php
    ** Location: /controllers
    ** Purpouse: test pdf barcode library
    */
    class TestPdfbController extends AppController {
    	var $name    = 'TestPdfb';
    	var $uses    = array();       // no models needed
    	var $helpers = array('pdf');  // Use the helper just created
    
    	function barcode() {
    		$this->layout = 'pdf';           // Set layout to pdf
    		$this->set('doc_id', '1234567'); // Set number to print
    		$this->render('barcode');
    	}
    }
    ?>

`View an image`_
`View original article with images`_

.. _See figure: http://blog.nospace.net/uploads/2007/03/pdfb_folders.gif
.. _View an image: http://blog.nospace.net/uploads/2007/03/pdfb_barcode.gif
.. _http://chir.ag/tech/download/pdfb/: http://chir.ag/tech/download/pdfb/
.. _View original article with images: http://blog.nospace.net/?p=13
.. _go here: http://www.fpdf.org/it/doc/index.php

.. author:: LazyCoder
.. categories:: articles, helpers
.. tags:: helpers,pdf,barcode,Helpers

