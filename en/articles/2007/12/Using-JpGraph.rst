Using JpGraph
=============

by %s on December 26, 2007

JpGraph can dynamically generate various types of graphs based on data
you feed it from a database/array. This tutorial is just how I use it
and how I got it working. As I deal with it more, I will try to
include more information.
First off decide if JpGraph is what you want. visit their site
`http://www.aditus.nu/jpgraph/`_ There are 2 versions. 1.x for php 4
and 2.x for php 5. They will no longer be supporting v 1.x but it is
the version I have successfully used and what this tutorial is
currently based on.


#. Download JpGraph
#. Under your Cake install, create a folder in your apps/vendors
   directory called jpgraph.
#. Upload the files in the JpGraph1.x/src folder to the folder you
   just created in your cake install

In your controller (mine: ReportsController)

set your layout: $this->layout='ajax';
which is a default layout for cake 1.2

query for your data. the format of the data has to be in an array.
array(1,2,3);
so if you do a query and the returned data looks like this
$data['Model'][['field'] you would have to do a loop or something and
set $graphdata[]=$data['Model'][['field'];
then just set that for the view $this->set('graphdata',$graphdata);

In your View (mine: fullreport)
at the top you would add (mine being a pie chart)

::

    
    <?php
    vendor( 'jpgraph/jpgraph' );
    vendor( 'jpgraph/jpgraph_pie' ); //set this to your chart type
    ?>

Then just read the manual on what all you need to do to display the
graph. Below is just some real code i used.

::

    
    <?php
    // Create the Pie Graph.
    $graph = new PieGraph(1000,950,"auto");
    $graph->SetShadow();
    $graph ->legend->Pos( 0.25,0.8,"right" ,"bottom");
    $graph->legend->SetFont(FF_VERDANA,FS_BOLD,12);
    $graph->title->SetMargin (20); 
    
    // Set A title for the plot
    $graph->title->Set("Full Report");
    $graph->title->SetFont(FF_VERDANA,FS_BOLD,24);
    //students
    $graph->subtitle->Set("(n=$total)");
    $graph->subtitle->SetFont(FF_VERDANA,FS_BOLD,8);
    
    
    // Create plots
    $size=0.13;
    $p1 = new PiePlot($data1);
    $p1->SetLegends(array("1 - Not Yet","2","3 - Emerging","4","5 - Somewhat","6","7 - Completely","Blank (No Answer)"));
    $p1->SetSize($size);
    $p1->SetGuideLines(true,false);
    $p1->SetGuideLinesAdjust(1.1,3);
    $p1->SetCenter(0.25,0.32);
    $p1->value->SetFont(FF_VERDANA);
    $p1->title->Set("Positive Social Emotional Skills");
    $p1->title->SetMargin(45);
    $p1->SetSliceColors(array('red','orange','yellow','green','purple','blue','brown','black')); 
    
    
    $p2 = new PiePlot($data2);
    $p2->SetSize($size);
    $p2->SetGuideLines(true,false);
    $p2->SetGuideLinesAdjust(1.1,3);
    $p2->SetCenter(0.65,0.32);
    $p2->value->SetFont(FF_VERDANA);
    $p2->title->Set("Acquisition and Use of Knowledge and Skills");
    $p2->title->SetMargin(45);
    $p2->SetSliceColors(array('red','orange','yellow','green','purple','blue','brown','black')); 
    
    
    $p3 = new PiePlot($data3);
    $p3->SetSize($size);
    $p3->SetGuideLines(true,false);
    $p3->SetGuideLinesAdjust(1.1,3);
    $p3->SetCenter(0.25,0.75);
    $p3->value->SetFont(FF_VERDANA);
    $p3->title->Set("Use of Appropriate Behaviors to Meet Needs");
    $p3->title->SetMargin(45);
    $p3->SetSliceColors(array('red','orange','yellow','green','purple','blue','brown','black')); 
    
    
    
    $graph->Add($p1);
    $graph->Add($p2);
    $graph->Add($p3);
    
    $graph->Stroke();
    ?

Note that this view is purely for generating the image. You can not do
any other echos or prints on this page or you will get an error and
the image will not be generated.

If you want to use this image in a view. You have to use another view
and actually call this view as an image source

So if this View was called ReportImage.
In another View called Report or whatever you would do this

::

    
    <?php
       echo "This is an image of my report":
       echo "<img src='/reports/reportimage'></img>";
    ?>

you could also pass a parameter in the img src as well if you wanted
to pass say an id for a form that the image would be generated off of.

Well thats the best way I can explain it. I'm sure there may be better
ways to do it but thats how I have it working. Ask questions and I
will try to help.

.. _http://www.aditus.nu/jpgraph/: http://www.aditus.nu/jpgraph/
.. meta::
    :title: Using JpGraph
    :description: CakePHP Article related to JpGraph,Tutorials
    :keywords: JpGraph,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

