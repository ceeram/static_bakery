

CSV Helper (PHP 5)
==================

by %s on September 10, 2007

I was recently outputting some comma-delimited data and thought I
would benefit from a simple csv helper. I hope this helper benefits
someone else as well. This is my first submission so please give any
criticism.

This CSV helper enables you to output data in a csv format. It can
automatically output the necessary headers so that it will open in
Microsoft Excel.

Sample Usage

::

    $line = array('First Name', 'Last Name', 'Gender', 'City');
    $csv->addRow($line);
    
    $line = array('Adam', 'Royle', 'M', 'Brisbane');
    $csv->addRow($line);
    
    $line = array('Skrimpy', 'Bopimpy', 'M', 'North Sydney');
    $csv->addRow($line);
    
    $line = array('Sarah', 'Jincera"s', 'F', 'Melbourne');
    $csv->addRow($line);
    
    echo $csv->render('Subscribers.csv'); 

This will output the following:

::

    "First Name","Last Name",Gender,City
    Adam,Royle,M,Brisbane
    Skrimpy,Bopimpy,M,"North Sydney"
    Sarah,"Jincera""s",F,Melbourne

Instead of the addRow() method, you can also add one field at a time.

::

    $csv->addField('Adam');
    $csv->addField('Royle');
    $csv->addField('M');
    $csv->addField('Brisbane');
    $csv->endRow();

If you don't want to render headers simply use:

::

    $csv->render(false);

Save the following file as csv.php into your app/views/helpers/
directory, and include 'Csv' in your $helpers array in your
controller.

Helper Class:
`````````````

::

    <?php 
     
    class CsvHelper extends AppHelper {
    	
    	var $delimiter = ',';
    	var $enclosure = '"';
    	var $filename = 'Export.csv';
    	var $line = array();
    	var $buffer;
    	
    	function CsvHelper() {
    		$this->clear();
    	}
    	
    	function clear() {
    		$this->line = array();
    		$this->buffer = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
    	}
    	
    	function addField($value) {
    		$this->line[] = $value;
    	}
    	
    	function endRow() {
    		$this->addRow($this->line);
    		$this->line = array();
    	}
    	
    	function addRow($row) {
    		fputcsv($this->buffer, $row, $this->delimiter, $this->enclosure);
    	}
    	
    	function renderHeaders() {
    		header("Content-type:application/vnd.ms-excel");
    		header("Content-disposition:attachment;filename=".$this->filename);
    	}
    	
    	function setFilename($filename) {
    		$this->filename = $filename;
    		if (strtolower(substr($this->filename, -4)) != '.csv') {
    			$this->filename .= '.csv';
    		}
    	}
    	
    	function render($outputHeaders = true, $to_encoding = null, $from_encoding = "auto") {
    		if ($outputHeaders) {
    			if (is_string($outputHeaders)) {
    				$this->setFilename($outputHeaders);
    			}
    			$this->renderHeaders();
    		}
    		rewind($this->buffer);
    		$output = stream_get_contents($this->buffer);
    		if ($to_encoding) {
    			$output = mb_convert_encoding($output, $to_encoding, $from_encoding);
    		}
    		return $this->output($output);
    	}
    }
    
    ?>


.. meta::
    :title: CSV Helper (PHP 5)
    :description: CakePHP Article related to csv,Helpers
    :keywords: csv,Helpers
    :copyright: Copyright 2007 
    :category: helpers

