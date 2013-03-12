

FusionCharts v3 Free Helper
===========================

by %s on July 22, 2009

Very quick FusionCharts helper, to use this, you need to put the
following code in your views/helpers/ directory


Helper Class:
`````````````

::

    <?php 
    class FusionchartsHelper extends AppHelper {
    	
    	var $XML = "";
    	
    	// encodeDataURL function encodes the dataURL before it's served to FusionCharts.
    	// If you've parameters in your dataURL, you necessarily need to encode it.
    	// Param: $strDataURL - dataURL to be fed to chart
    	// Param: $addNoCacheStr - Whether to add aditional string to URL to disable caching of data
    	
    	function encodeDataURL($strDataURL, $addNoCacheStr=false) {
    	    //Add the no-cache string if required
    	    if ($addNoCacheStr==true) {
    	        // We add ?FCCurrTime=xxyyzz
    	        // If the dataURL already contains a ?, we add &FCCurrTime=xxyyzz
    	        // We replace : with _, as FusionCharts cannot handle : in URLs
    			if (strpos($strDataURL,"?")<>0)
    				$strDataURL .= "&FCCurrTime=" . Date("H_i_s");
    			else
    				$strDataURL .= "?FCCurrTime=" . Date("H_i_s");
    	    }
    		// URL Encode it
    		return urlencode($strDataURL);
    	}
    	
    	// datePart function converts MySQL database based on requested mask
    	// Param: $mask - what part of the date to return "m' for month,"d" for day, and "y" for year
    	// Param: $dateTimeStr - MySQL date/time format (yyyy-mm-dd HH:ii:ss)
    	
    	function datePart($mask, $dateTimeStr) {
    	    @list($datePt, $timePt) = explode(" ", $dateTimeStr);
    	    $arDatePt = explode("-", $datePt);
    	    $dataStr = "";
    	    // Ensure we have 3 parameters for the date
    	    if (count($arDatePt) == 3) {
    	        list($year, $month, $day) = $arDatePt;
    	        // determine the request
    	        switch ($mask) {
    	        case "m": return $month;
    	        case "d": return $day;
    	        case "y": return $year;
    	        }
    	        // default to mm/dd/yyyy
    	        return (trim($month . "/" . $day . "/" . $year));
    	    }
    	    return $dataStr;
    	}
    	
    	// renderChart renders the JavaScript + HTML code required to embed a chart.
    	// This function assumes that you've already included the FusionCharts JavaScript class
    	// in your page.
    	
    	// $chartSWF - SWF File Name (and Path) of the chart which you intend to plot
    	// $strURL - If you intend to use dataURL method for this chart, pass the URL as this parameter. Else, set it to "" (in case of dataXML method)
    	// $this->XML - If you intend to use dataXML method for this chart, pass the XML data as this parameter. Else, set it to "" (in case of dataURL method)
    	// $chartId - Id for the chart, using which it will be recognized in the HTML page. Each chart on the page needs to have a unique Id.
    	// $chartWidth - Intended width for the chart (in pixels)
    	// $chartHeight - Intended height for the chart (in pixels)
    	// $debugMode - Whether to start the chart in debug mode
    	// $registerWithJS - Whether to ask chart to register itself with JavaScript
    	
    	function setData($data) {
    		App::Import("Xml");
    		
    		$chartXML = new Xml($data);
    		$this->XML = str_replace(array('"', 'account', 'datecreated'),array('\'','set','value'),$chartXML->toString());
    	}
    	
    	function renderChart($chartSWF, $strURL, $chartId, $chartWidth, $chartHeight, $debugMode=false, $registerWithJS=false, $setTransparent="") {
    		//First we create a new DIV for each chart. We specify the name of DIV as "chartId"Div.			
    		//DIV names are case-sensitive.
    	
    	    // The Steps in the script block below are:
    	    //
    	    //  1)In the DIV the text "Chart" is shown to users before the chart has started loading
    	    //    (if there is a lag in relaying SWF from server). This text is also shown to users
    	    //    who do not have Flash Player installed. You can configure it as per your needs.
    	    //
    	    //  2) The chart is rendered using FusionCharts Class. Each chart's instance (JavaScript) Id 
    	    //     is named as chart_"chartId".		
    	    //
    	    //  3) Check whether we've to provide data using dataXML method or dataURL method
    	    //     save the data for usage below 
    		if ($this->XML=="")
    	        $tempData = "//Set the dataURL of the chart\n\t\tchart_$chartId.setDataURL(\"$strURL\")";
    	    else
    	        $tempData = "//Provide entire XML data using dataXML method\n\t\tchart_$chartId.setDataXML(\"{$this->XML}\")";
    	
    	    // Set up necessary variables for the RENDERCAHRT
    	    $chartIdDiv = $chartId . "Div";
    	    $ndebugMode = $this->boolToNum($debugMode);
    	    $nregisterWithJS = $this->boolToNum($registerWithJS);
    		$nsetTransparent=($setTransparent?"true":"false");
    	
    	
    	    // create a string for outputting by the caller
    	    
    $render_chart = <<<RENDERCHART
    	<!-- START Script Block for Chart $chartId -->
    	<div id="$chartIdDiv" align="center">
    		Chart.
    	</div>
    	<script type="text/javascript">	
    		//Instantiate the Chart	
    		var chart_$chartId = new FusionCharts("$chartSWF", "$chartId", "$chartWidth", "$chartHeight", "$ndebugMode", "$nregisterWithJS");
          chart_$chartId.setTransparent("$nsetTransparent");
        
    		$tempData
    		//Finally, render the chart.
    		chart_$chartId.render("$chartIdDiv");
    	</script>	
    	<!-- END Script Block for Chart $chartId -->
    RENDERCHART;
    	
    	  return $this->output($render_chart);
    	}
    	
    	//renderChartHTML function renders the HTML code for the JavaScript. This
    	//method does NOT embed the chart using JavaScript class. Instead, it uses
    	//direct HTML embedding. So, if you see the charts on IE 6 (or above), you'll
    	//see the "Click to activate..." message on the chart.
    	// $chartSWF - SWF File Name (and Path) of the chart which you intend to plot
    	// $strURL - If you intend to use dataURL method for this chart, pass the URL as this parameter. Else, set it to "" (in case of dataXML method)
    	// $this->XML - If you intend to use dataXML method for this chart, pass the XML data as this parameter. Else, set it to "" (in case of dataURL method)
    	// $chartId - Id for the chart, using which it will be recognized in the HTML page. Each chart on the page needs to have a unique Id.
    	// $chartWidth - Intended width for the chart (in pixels)
    	// $chartHeight - Intended height for the chart (in pixels)
    	// $debugMode - Whether to start the chart in debug mode
    	
    	function renderChartHTML($chartSWF, $strURL, $chartId, $chartWidth, $chartHeight, $debugMode=false,$registerWithJS=false, $setTransparent="") {
    	    // Generate the FlashVars string based on whether dataURL has been provided
    	    // or dataXML.
    	    $strFlashVars = "&chartWidth=" . $chartWidth . "&chartHeight=" . $chartHeight . "&debugMode=" . $this->boolToNum($debugMode);
    	    if ($this->XML=="")
    	        // DataURL Mode
    	        $strFlashVars .= "&dataURL=" . $strURL;
    	    else
    	        //DataXML Mode
    	        $strFlashVars .= "&dataXML=" . $this->XML;
    	    
    	    $nregisterWithJS = $this->boolToNum($registerWithJS);
    	    if($setTransparent!=""){
    	      $nsetTransparent=($setTransparent==false?"opaque":"transparent");
    	    }else{
    	      $nsetTransparent="window";
    	    }
    	    
    $HTML_chart = <<<HTMLCHART
    	<!-- START Code Block for Chart $chartId -->
    	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="$chartWidth" height="$chartHeight" id="$chartId">
    		<param name="allowScriptAccess" value="always" />
    		<param name="movie" value="$chartSWF"/>		
    		<param name="FlashVars" value="$strFlashVars&registerWithJS=$nregisterWithJS" />
    		<param name="quality" value="high" />
    		<param name="wmode" value="$nsetTransparent" />
    		<embed src="$chartSWF" FlashVars="$strFlashVars&registerWithJS=$nregisterWithJS" quality="high" width="$chartWidth" height="$chartHeight" name="$chartId" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="$nsetTransparent" />
    	</object>
    	<!-- END Code Block for Chart $chartId -->
    HTMLCHART;
    	
    	  return $this->output($HTML_chart);
    	}
    	
    		// boolToNum function converts boolean values to numeric (1/0)
    	function boolToNum($bVal) {
    	    return (($bVal==true) ? 1 : 0);
    	}
    }
    ?>

This requires your data to be returned in the format:

::

    Array
    (
        [graph] => Array
            (
                [rotateNames] => 0
                [decimalPrecision] => 0
                [set] => Array
                    (
                        [1243810800] => Array
                            (
                                [name] => 1st
                                [value] => 1
                            )
    
                        [1243897200] => Array
                            (
                                [name] => 2nd
                                [value] => 0
                            )
    
                        [1243983600] => Array
                            (
                                [name] => 3rd
                                [value] => 2
                            )
    
                        [1244070000] => Array
                            (
                                [name] => 4th
                                [value] => 2
                            )
    
                        [1244156400] => Array
                            (
                                [name] => 5th
                                [value] => 0
                            )
    
                        [1244242800] => Array
                            (
                                [name] => 6th
                                [value] => 8
                            )
    
                        [1244329200] => Array
                            (
                                [name] => 7th
                                [value] => 0
                            )
    
                        [1244415600] => Array
                            (
                                [name] => 8th
                                [value] => 0
                            )
    
                        [1244502000] => Array
                            (
                                [name] => 9th
                                [value] => 0
                            )
    
                        [1244588400] => Array
                            (
                                [name] => 10th
                                [value] => 0
                            )
    
                        [1244674800] => Array
                            (
                                [name] => 11th
                                [value] => 1
                            )
    
                        [1244761200] => Array
                            (
                                [name] => 12th
                                [value] => 2
                            )
    
                        [1244847600] => Array
                            (
                                [name] => 13th
                                [value] => 3
                            )
    
                        [1244934000] => Array
                            (
                                [name] => 14th
                                [value] => 0
                            )
    
                        [1245020400] => Array
                            (
                                [name] => 15th
                                [value] => 1
                            )
    
                        [1245106800] => Array
                            (
                                [name] => 16th
                                [value] => 2
                            )
    
                        [1245193200] => Array
                            (
                                [name] => 17th
                                [value] => 5
                            )
    
                        [1245279600] => Array
                            (
                                [name] => 18th
                                [value] => 2
                            )
    
                        [1245366000] => Array
                            (
                                [name] => 19th
                                [value] => 0
                            )
    
                        [1245452400] => Array
                            (
                                [name] => 20th
                                [value] => 2
                            )
    
                        [1245538800] => Array
                            (
                                [name] => 21st
                                [value] => 0
                            )
    
                        [1245625200] => Array
                            (
                                [name] => 22nd
                                [value] => 1
                            )
    
                        [1245711600] => Array
                            (
                                [name] => 23rd
                                [value] => 2
                            )
    
                        [1245798000] => Array
                            (
                                [name] => 24th
                                [value] => 0
                            )
    
                        [1245884400] => Array
                            (
                                [name] => 25th
                                [value] => 0
                            )
    
                        [1245970800] => Array
                            (
                                [name] => 26th
                                [value] => 0
                            )
    
                        [1246057200] => Array
                            (
                                [name] => 27th
                                [value] => 0
                            )
    
                        [1246143600] => Array
                            (
                                [name] => 28th
                                [value] => 0
                            )
    
                        [1246230000] => Array
                            (
                                [name] => 29th
                                [value] => 0
                            )
    
                        [1246316400] => Array
                            (
                                [name] => 30th
                                [value] => 0
                            )
    
                    )
    
            )
    
    )

This section is where you would put your graph options

::

    Array
    (
        [graph] => Array
            (
                [rotateNames] => 0
                [decimalPrecision] => 0
            )
    )

Usage in your view:


View Template:
``````````````

::

    <script src="/js/fusioncharts.js" type="text/javascript"></script>
    {$fusioncharts->setData($chart)}
    {$fusioncharts->renderChart("/flash/FCF_Line.swf", "", "helloWorld", 960, 350)}

The first parameter above is relative to webroot
The second is a URL to your XML defined data if you do not use setData
The third is a unique identifier for the chart
And the last two are width and height

Find more options on the fusioncharts documentation here
(`http://www.fusioncharts.com/free/Docs/Index.html`_)

Download fusioncharts free here
(`http://www.fusioncharts.com/free/Download.asp`_)

(You need the .swf files and the javascript file)

Any problems leave a message :)

.. _http://www.fusioncharts.com/free/Download.asp: http://www.fusioncharts.com/free/Download.asp
.. _http://www.fusioncharts.com/free/Docs/Index.html: http://www.fusioncharts.com/free/Docs/Index.html
.. meta::
    :title: FusionCharts v3 Free Helper
    :description: CakePHP Article related to xml,charts,lines,graphs,fusioncharts,fusion,Helpers
    :keywords: xml,charts,lines,graphs,fusioncharts,fusion,Helpers
    :copyright: Copyright 2009 
    :category: helpers

