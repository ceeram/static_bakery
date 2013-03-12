

Report Creator Component
========================

by %s on November 09, 2006

Under most circumstances web-based intranet applications call for some
type of flexible reporting for its users. Report design is often times
a fixed process which immobilizes a user's ability to create new
reports on the fly. Utilizing some existing ingredients within Cake I
managed to bake a fairly straightforward reporting component that can
easily integrate into any Cake application.


What can this component do?
```````````````````````````
The report creator component provides users with an ability to rapidly
create dynamic
reports based off a preset of Cake models.



How do I use this component?
````````````````````````````
I have compiled a package of six files that will allow you to
instantly harness the
power of this component.


The package can be downloaded at the following address:
`http://www.internaq.com/files/reportcomponent.zip`_



Install Instructions:
`````````````````````


File: report.css
;;;;;;;;;;;;;;;;
/webroot/css/report.css


File: reports_controller.php
;;;;;;;;;;;;;;;;;;;;;;;;;;;;
Include this controller action within the controller you wish to have
reporting capability.
In this file you will need to set the models you wish to use for
reporting in the model
array. Also note that you must add the following line to your
controller:

var $components = array ('Report');


Controller Class:
`````````````````

::

    <?php 
    
    	
    	/**********************************************************************************************************
    		Function:	createReport()
    		Action:		Build a dynamic report.
    	**********************************************************************************************************/
    	function createReport()
    	{
    		if (!empty($this->data)) 
    		{ 
    			//Determine if user is pulling existing report or deleting report
    			if(isset($this->params['form']['existing']))
    			{
    				if($this->params['form']['existing']=='Pull')
    				{
    					//Pull report
    					$this->Report->pull_report($this->data['Misc']['saved_reports']);
    				}
    				else if($this->params['form']['existing']=='Delete')
    				{
    					//Delete report
    					$this->Report->delete_report($this->data['Misc']['saved_reports']);
    
    					//Return user to form
    					$this->flash('Your report has been deleted.','/'.$this->name.'/'.$this->action.'');
    				}
    			}
    			else
    			{
    				//Filter out fields
    				$this->Report->init_display($this->data);
    				
    				//Set sort parameter
    				if(!isset($this->params['form']['order_by_primary'])) { $this->params['form']['order_by_primary']=NULL; }
    				if(!isset($this->params['form']['order_by_secondary'])) { $this->params['form']['order_by_secondary']=NULL; }
    				$this->Report->get_order_by($this->params['form']['order_by_primary'], $this->params['form']['order_by_secondary']);
    
    				//Store report name
    				if(!empty($this->params['form']['report_name']))
    				{
    					$this->Report->save_report_name($this->params['form']['report_name']);
    				}
    
    				//Store report if save was executed
    				if($this->params['form']['submit']=='Create And Save Report')
    				{
    					if(empty($this->params['form']['report_name']))
    					{
    						//Return user to form
    						$this->flash('Must enter a report name when saving.','/'.$this->name.'/'.$this->action.'');
    					}
    					else
    					{
    						$this->Report->save_report();
    					}
    				}
    			}
    			
    			//Set report fields
    			$this->set('report_fields', $this->Report->report_fields);
    
    			//Set report name
    			$this->set('report_name', $this->Report->report_name);
    
    			//Allow search to go 2 associations deep
    			$this->{$this->modelClass}->recursive = 2;
    
    			//Set report data
    			$this->set('report_data', $this->{$this->modelClass}->findAll(NULL,NULL,$this->Report->order_by));
    		} 
    		else
    		{
    			//Setup options for report component
    			/*
    				You can setup a level two association by doing the following:
    				"VehicleDriver"=>"Employee" ie $models = Array ("Vehicle", "VehicleDriver"=>"Employee");
    				Please note that all fields within a level two association cannot be sorted.
    			*/
    			$models =	Array ("");
    
    			//Set array of fields
    			$this->set('report_form', $this->Report->init_form($models));
    
    			//Set current controller
    			$this->set('cur_controller', $this->name);
    
    			//Pull all existing reports
    			$this->set('existing_reports', $this->Report->existing_reports());
    		}
    	}	
    
    
    
    ?>



File: report.php
;;;;;;;;;;;;;;;;
/controllers/components/report.php
This is the component file. The default path for saving reports is
within
the /app/tmp/report/ folder. If the folder does not exist the
component will
create it for you.


Component Class:
````````````````

::

    <?php 
    
    
    class ReportComponent extends Object
    {
    
    /**
     * Place holder for the models array.
     *
     * @var array
     * @access public
     */
    	var $model = Array();
    
    /**
     * Place holder for the fields.
     *
     * @var array
     * @access public 
     */
    	var $columns = Array();
    
    
    /**
     * Specify DEFAULT folder off root directory to store reports in. 
     *
     * @var string
     * @access public
     */
    	var $path ="/app/tmp/reports/";
    
    /**
     * Place holder for the report fields. 
     *
     * @var array
     * @access public
     */
    	var $report_fields = Array();
    
    
    /**
     * Place holder for the order by clause. 
     *
     * @var string
     * @access public
     */
    	var $order_by = NULL;
    
    /**
     * Place holder for the report name. 
     *
     * @var string
     * @access public
     */
    	var $report_name = NULL;
    
    
    /**
     * Startup - Link the component to the controller.
     *
     * @param controller
     */
        function startup(&$controller)
        {
            // This method takes a reference to the controller which is loading it.
            // Perform controller initialization here.
    		$this->controller =& $controller;
        }
     
    /**
     * Initialize the report form by creating links to models
     * and storing table meta data.
     *
     * @models array
     */
        function init_form($models)
        {
    		foreach($models as $model=> $value) 
    		{
    			$this-> model = new $value; 
    			$columns = $this->model->loadInfo();
    			
    			//Extract field names from array
    			for($j=0; $j<count($columns->value); $j++) 
    			{
    				$arr[$value][$j]=$columns->value[$j]['name'];
    			}
    			
    			//If two level deep association exists set value
    			if(!empty($model)) 
    			{
    				$arr['associated_table'][$value]=$model;
    			}
    		}
    
    		return $arr;
        }
     
    /**
     * Initializes the report display.
     *
     * @form array
     */
        function init_display($form)
        {
    		//get fields that were selected
    		$this->report_fields=$this->get_selected($form);
    
    		//sort fields by priority 
    		$this->report_fields=$this->sort_fields($this->report_fields);
        }
    
    /**
     * Extracts all selected fields from form.
     *
     * @form array
     */
        function get_selected($form)
        {
    		foreach ($form as $model => $field) {
    			foreach ($field as $name) {
    				if(!empty($name['include'])) {
    					$arr[]=$name;
    				}
    			}
    		}
    		return $arr;
    	}
    
    /**
     * Sorts all selected fields from form by priority
     * entered (1-left ... 10-right).
     *
     * @fields array
     */
        function sort_fields($fields)
        {
    		for ($i=0; $i < sizeof($fields)-1; $i++) 
    		{
    			for ($j=0; $j<sizeof($fields)-1-$i; $j++)
    			{
    				if ($fields[$j]['priority'] > $fields[$j+1]['priority']) 
    				{
    					$tmp = $fields[$j];
    					$fields[$j] = $fields[$j+1];
    					$fields[$j+1] = $tmp;
    				}
    			}
    		}
    
    		return $fields;
    	}
    
    /**
     * Sets up the order by clause.   
     *
     * @primary string
     * @secondary string
     */
    	function get_order_by($primary, $secondary) 
    	{
    		//Store primary sort if exists
    		if(!empty($primary)) 
    		{
    			$this->order_by=$primary;
    		
    			//Store secondary sort if exists
    			if(!empty($secondary)) 
    			{
    				$this->order_by.=",".$secondary;
    			}			
    		}
    		else 
    		{
    			$this->order_by=NULL;
    		}
    	}
    
    /**
     * Saves the newly created report.
     *
     * @order_by string
     */
    	function save_report()
    	{
    		$content='<? $report_fields=Array(';
    		for($i=0; $i<count($this->report_fields); $i++)
    		{					
    			//get number of elements
    			$total=count($this->report_fields[$i]);
    			$counter=0;
    
    			$content.='Array(';
    			foreach($this->report_fields[$i] as $report_field => $value) 
    			{
    				$counter++;
    
    				if($total!=$counter)
    				{
    					$content.='"'.$report_field.'" => "'.$value.'", ';
    				} 
    				else
    				{
    					$content.='"'.$report_field.'" => "'.$value.'"';
    				}
    			}
    
    			if(($i+1)==count($this->report_fields)) 
    			{
    				$content.=')';
    			} 
    			else 
    			{
    				$content.='), ';
    			}
    		}
    		$content.=');'; 
    		
    		$content.='$order_by="'.$this->order_by.'";';
    		$content.='$report_name="'.$this->report_name.'"; ?>';
    		
    		//Create directory if specified one does not already exist
    		if(!is_dir($_SERVER['DOCUMENT_ROOT'].$this->path))
    		{ 
    			mkdir($_SERVER['DOCUMENT_ROOT'].$this->path);
    		}
    
    		$file_name = $this->report_name.".php"; 
    		$handle = fopen($_SERVER['DOCUMENT_ROOT'].$this->path.$file_name, 'w');
    		fwrite($handle, $content);
    		fclose($handle); 
    	}
    
    /**
     * Saves report name.
     *
     * @report_name string
     */
    	function save_report_name($report_name)
    	{
    		$this->report_name=$report_name;
    	}
    
    /**
     * Pulls listing of existing reports..
     *
     */
    	function existing_reports() 
    	{
    		//create an array to hold directory list
    		$results = array();
    
    		//create a handler for the directory
    		$handler = opendir($_SERVER['DOCUMENT_ROOT'].$this->path);
    
    		//keep going until all files in directory have been read
    		while ($file = readdir($handler)) 
    		{
    
    			// if $file isn't this directory or its parent, add it to the results array
    			if ($file != '.' && $file != '..')
    			{
    				$results[$file] = str_replace(".php", "", $file);
    			}
    		}
    
    		closedir($handler);
    
    		return $results;
    	}
    
    /**
     * Pulls field array from existing report..
     *
     * @report string
     */
    	function pull_report($report) 
    	{
    		//Pull file
    		require($_SERVER['DOCUMENT_ROOT'].$this->path.$report);
    		
    		//Store data
    		$this->order_by=$order_by;
    		$this->report_fields=$report_fields;
    		$this->report_name=$report_name;
    	}
    
    /**
     * Deletes an existing report..
     *
     * @report string
     */
    	function delete_report($report) 
    	{
    		unlink($_SERVER['DOCUMENT_ROOT'].$this->path.$report);
    	}
    
    }
    
    
    ?>



File: report_form.thtml
;;;;;;;;;;;;;;;;;;;;;;;
/views/elements/report_form.thml
This file handles the look and display of the form retrieval page.


View Template:
``````````````

::

    
    
    <div id="report_form" class="report_form">
    
    <table>
    <tr>
    	<td valign="top">
    	
    	<form action="/<?= $cur_controller; ?>/createReport/" method="post">
    
    	<fieldset>
    	<legend style="background: #E51336;">Saved Reports</legend>
    	<table class="report_small">
    	<tr>
    		<td><?php echo $html->selectTag('Misc/saved_reports', $existing_reports);  ?></td>
    		<td><input type="submit" name="existing" value="Pull" style="font: normal normal bold 8pt arial; color: #FFFFFF; background: #0066CC;"></td>
    		<td><input type="submit" name="existing" value="Delete" style="font: normal normal bold 8pt arial; color: #FFFFFF; background: #E51336;" onclick="return confirm('Are you sure you want to delete this report?')"></td>
    	</tr>
    	</table>
    	</fieldset>
    
    	</form>
    
    	</td>
    	<td width="20"></td>
    	<td valign="top">
    	
    	<form action="/<?= $cur_controller; ?>/createReport/" method="post">
    
    	<fieldset>
    	<legend style="background: #E51336;">New Report</legend>
    	<table class="report_small">
    	<tr>
    		<td>Report Name</td>
    		<td><input type="text" name="report_name" style="width: 180px;"></td>
    	</tr>
    	<tr>
    		<td></td>
    		<td>* Field required if saving report</td>
    	</tr>
    	</table>
    	</fieldset>
    
    	</td>
    </tr>
    </table>
    
    
    <? foreach ($report_form as $key => $value): ?>
    <? if($key!='associated_table') { ?>
    
    	<div style="height: 15px;"><!-- Spacer --></div>
    
    	<fieldset>
    	<legend><?= $key; ?> Table</legend>
    	
    	<table class="report">
    	<tr class="header">
    		<td>Field</td>
    		<td>Display Name</td>
    		<td style="text-align: center;">Priority</td>
    		<td style="text-align: center;">Sort By Primary</td>
    		<td style="text-align: center;">Sort By Secondary</td>
    		<td style="text-align: center;">Include</td>
    	</tr>
    		
    	<? for ($i=0; $i<count($value); $i++) { ?>
    
    	<tr class="body" onClick="if($('<?= $key; ?><?= $i; ?>').checked == true){ this.className='body_selected'; } else { this.className='body'; }">
    		<td>
    		
    		<?= $value[$i]; ?><input type="hidden" name="data[<?= $key; ?>][<?= $value[$i] ;?>][field_name]" value="<?= $value[$i]; ?>"><input type="hidden" name="data[<?= $key; ?>][<?= $value[$i] ;?>][model]" value="<?= $key; ?>"><input type="hidden" name="data[<?= $key; ?>][<?= $value[$i] ;?>][associated_table]" value="<? if(!empty($table_data['associated_table'][$key])) { echo $table_data['associated_table'][$key]; } ?>">	
    		
    		</td>
    		<td>
    		
    		<input type="text" name="data[<?= $key; ?>][<?= $value[$i] ;?>][display_name]" onFocus="if($('<?= $key; ?><?= $i; ?>').checked == false){ this.className='body_selected'; $('<?= $key; ?><?= $i; ?>').checked = true; }"></td>
    		<td style="text-align: center;"><input type="text" name="data[<?= $key; ?>][<?= $value[$i] ;?>][priority]" style="width: 50px;" onFocus="if($('<?= $key; ?><?= $i; ?>').checked == false){ this.className='body_selected'; $('<?= $key; ?><?= $i; ?>').checked = true; }">
    		
    		</td>
    		<td style="text-align: center;"><input type="radio" name="order_by_primary" value="<?= $key; ?>.<?= $value[$i]; ?>"></td>
    		<td style="text-align: center;"><input type="radio" name="order_by_secondary" value="<?= $key; ?>.<?= $value[$i]; ?>"></td>
    		<td style="text-align: center;"><input type="checkbox" id="<?= $key; ?><?= $i; ?>" name="data[<?= $key; ?>][<?= $value[$i] ;?>][include]"></td>
    	</tr>
    
    	<? } ?>
    	
    	</table>
    	</fieldset>
    	
    <? } ?>
    <?php endforeach; ?>
    
    <div style="height: 15px;"><!-- Spacer --></div>
    
    <table cellspacing="0" cellpadding="0">
    <tr>
    	<td><input type="submit" name="submit" value="Create Report"></td>
    	<td width="10"></td>
    	<td><input type="submit" name="submit" value="Create And Save Report"></td>
    </tr>
    </table>
    
    </form> 
    </div>
    



File: report_display.thtml
;;;;;;;;;;;;;;;;;;;;;;;;;;
/views/elements/report_display.thml
This file handles the look and display of the report page.


View Template:
``````````````

::

    
    
    <div id="report_display" class="report_display">
    
    <div class="report_name"><?= $report_name ?></div>
    <div class="report_date_stamp">Report run on <?= date('m/d/Y'); ?></div>
    <div style="height: 25px;"></div>
    
    <table class="report">
    <tr class="header">
    
    <? foreach ($report_fields as $field): ?>
    
    <td><? echo ($field['display_name']=='' ? $field['field_name'] : $field['display_name']); ?></td>
    
    <?php endforeach; ?>
    
    </tr>
    
    
    <? for($i=0; $i<count($report_data); $i++) { ?>
    
    <tr class="body">
    
    <? foreach ($report_fields as $field): ?>
    
    <td>
    
    <?
    	//Check to see if associated table is being used
    	if(!empty($report_data[$i][$field['associated_table']][$field['model']][$field['field_name']])) {
    		echo $report_data[$i][$field['associated_table']][$field['model']][$field['field_name']]; 
    	}
    	else if(!empty($report_data[$i][$field['model']][$field['field_name']])) {
    		echo $report_data[$i][$field['model']][$field['field_name']]; 
    	}
    ?>
    	
    </td>
    
    <?php endforeach; ?>
    
    </tr>
    
    <? } ?>
    </table>
    
    </div>
    



File: create_report.thtml
;;;;;;;;;;;;;;;;;;;;;;;;;
/views/ --controller folder -- /create_report.thtml
This is the view file that will handle outputting the correct user
displays.


File: prototype.js
;;;;;;;;;;;;;;;;;;
/webroot/js/prototype.js
I use a shorthand feature from the prototype.js library so please
include this file.

Well that's it. Hopefully this will help add a little more efficiency
to your programming
arsenal. I plan on updating this script over the next couple of months
to allow users to build
reports that utilize aggregate functions.




.. _http://www.internaq.com/files/reportcomponent.zip: http://www.internaq.com/files/reportcomponent.zip
.. meta::
    :title: Report Creator Component
    :description: CakePHP Article related to ,Components
    :keywords: ,Components
    :copyright: Copyright 2006 
    :category: components

