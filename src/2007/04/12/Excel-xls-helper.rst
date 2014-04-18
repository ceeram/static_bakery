Excel xls helper
================

Helper to creation of simple xls files.
This the helper code:


Helper Class:
`````````````

::

    <?php 
    /**
     * By Cleiton Wasen
     * wasenbr at gmail.com
     * Based in http://www.appservnetwork.com/modules.php?name=News&file=article&sid=8
     *  
     */
    class XlsHelper {
    	
    	var $helpers = array();
    	
        /**
         * set the header configuration
         * @param $filename the xls file name
         */
        function setHeader($filename)
        {
            header("Pragma: public");
    	    header("Expires: 0");
    	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    	    header("Content-Type: application/force-download");
    	    header("Content-Type: application/octet-stream");
    	    header("Content-Type: application/download");;
    	    header("Content-Disposition: attachment;filename=$filename");
    	    header("Content-Transfer-Encoding: binary ");
        }
        
        /**
         * write the xls begin of file
         */
        function BOF() {
    	    echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);  
    	    return;
    	}
    	
        /**
         * write the xls end of file
         */
    	function EOF() {
    	    echo pack("ss", 0x0A, 0x00);
    	    return;
    	}
    	
        /**
         * write a number
         * @param $Row row to write $Value (first row is 0)
         * @param $Col column to write $Value (first column is 0)
         * @param $Value number value
         */
    	function writeNumber($Row, $Col, $Value) {
    	    echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
    	    echo pack("d", $Value);
    	    return;
    	}
    	
        /**
         * write a string label
         * @param $Row row to write $Value (first row is 0)
         * @param $Col column to write $Value (first column is 0)
         * @param $Value string value
         */
    	function writeLabel($Row, $Col, $Value) {
    	    $L = strlen($Value);
    	    echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
    	    echo $Value;
            return;
    	}
    
    }
    ?>

View example:


View Template:
``````````````

::

    
    <?php
        // Send Header
        $xls->setHeader('text_'.date('Y_m_d').'.xls');
    
        // XLS Data Cell
        $xls->BOF();
        $xls->writeLabel(1,0,"Student Register");
        $xls->writeLabel(2,0,"COURSENO : ");
        $xls->writeLabel(2,1,"123");
        $xls->writeLabel(3,0,"TITLE : ");
        $xls->writeLabel(3,0,"BlaBlaBla");
        $xls->writeLabel(4,0,"SETION : ");
        $xls->writeLabel(6,0,"NO");
        $xls->writeLabel(6,1,"ID");
        $xls->writeLabel(6,2,"Gender");
        $xls->writeLabel(6,3,"Name");
        $xls->writeLabel(6,4,"Lastname");
        $xls->EOF();
        exit();
    ?>



.. author:: wasenbr
.. categories:: articles, helpers
.. tags:: xls,excel,Helpers

