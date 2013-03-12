

Using the Unobtrusive Date Picker Widget in CakePHP
===================================================

by %s on April 12, 2008

The default CakePHP date selection boxes are rather a pain in the
rear. Wouldn't it be nice if you could use a Date-Picker in your
app's? Well you can, and it's rather easy to implement.


First things first
``````````````````

+ Download and extract the Unobtrusive Date-Picker Widget here:
  `http://www.frequency-decoder.com/2006/10/02/unobtrusive-date-picker-
  widgit-update`_
+ Copy the 'css/datepicker.css' file to 'app/webroot/css'
+ Copy the 'js/datepicker.js' file to 'app/webroot/js'
+ Copy the contents of the media folder to
  'app/webroot/img/datepicker'
+ Open the datepicker.css file in your 'app/webroot/css' folder. Using
  the Find/Replace functionality of your editor replace all occurences
  of '../media' with '../img/datepicker'



Edit the default view
+++++++++++++++++++++

Note: If you do not have a default.thtml file then you will need to
create one, for simplicity you can just copy Cake's default one from
'cake/libs/view/templates/layouts' to 'app/views/layouts' and edit the
latter.

Put the following before the closing head tag
`````````````````````````````````````````````

::

    
    <?php echo $html->css('datepicker')."\n"; ?>
    <?php echo $javascript->link('datepicker.js')."\n"; ?>

Note: This may give you an error when you try to load your page, that
is because you are trying to use the javascript helper but you have
not defined it in your controller. Since it will be used site-wide the
easiest thing is to define it in 'app_controller.php'.

Create 'app/app_controller.php' and enter the following:
````````````````````````````````````````````````````````

::

    
    <?php
    class AppController extends Controller
    {
    	var $helpers = array('Javascript');       
    
    }
    ?>



Create afterFind and beforeSave methods
+++++++++++++++++++++++++++++++++++++++

So in order to separate the date from the time when using the dateTime
format in MySQL you could use a custom query string, but this was not
recommended to me so instead I decided to run an after find to create
a pseudo-field.

You will need to recombine the fields before it is written back to the
database otherwise the information will not be properly saved, this is
where the beforeSave method comes into play.

Edit your model and enter the following methods:
````````````````````````````````````````````````

::

    
    <?php
    
    /*
     * The validation below is optional, just to give you an idea
     * of how to validate these fields.
     */
    
    var $validate = array(
      'dateOnly' => '/[0-9]{2}[\-\/\.][0-9]{2}[\-\/\.][0-9]{2,4}$/i',
      'headline' => VALID_NOT_EMPTY,
      'detail' => VALID_NOT_EMPTY
    );
    
    // Extra form validation, since VALID_NOT_EMPTY does not work on
    // these fields.
    function validates() {
      $event = $this->data['Event'];
      if(empty($event['date_hour']) || empty($event['date_meridian']) || empty($event['date_meridian']))
         $this->invalidate('date');
    
      $errors = $this->invalidFields();
      return count($errors) == 0;
    }
    
    /*
     * The validation above is optional, just to give you an idea
     * of how to validate these fields.
     */
    
    function afterFind($results) {
       // Create a dateOnly pseudofield using date field.
           foreach ($results as $key => $val) {
               if (isset($val['Event']['date']))
                   $results[$key]['Event']['dateOnly'] = date('m-d-Y',strtotime($val['Event']['date']));  
           }
       return $results;
    }
    
    function beforeSave()
    {
      // Convert 12 hour to 24 hour
      if($this->data['Event']['date_meridian'] == 'pm')
         $hour = $this->data['Event']['date_hour'] + 12;
      else
         $hour = $this->data['Event']['date_hour'];
    
      // Get month day and year from date string
      $timestamp = strtotime(str_replace('-','/',$this->data['Event']['dateOnly']));
      $month = date('m',$timestamp);
      $day = date('d',$timestamp);
      $year = date('Y',$timestamp);
      
      $this->data['Event']['date'] = date('Y-m-d H:i:s', mktime(
                      $hour,
                      $this->data['Event']['date_min'],
                      null,
                      $month,
                      $day,
                      $year));
      return true;
    }
    
    ?>

Note: Be sure to substitue ['Event'] with your App name and ['date']
with the field in the database that contains the dateTime value.


Edit your view
++++++++++++++

You will need to modify you view to display the new format, below is
what I used. Feel free to make any necessary changes for your
application.

Example view:
`````````````

::

    
    <table cellpadding="0" cellspacing="0" class="view">
    	<tr>
    		<td><span class="title"><?php echo $form->labelTag('Event/dateOnly', 'Date');?></span></td>		
    		<td>
    			<?php echo $html->input('Event/dateOnly', array('size' => '15', 'class' => 'w8em format-m-d-y divider-dash highlight-days-12 no-transparency'));?>
    		</td>
    		<td><?php echo $html->tagErrorMsg('Event/dateOnly', 'Please select the Date.');?></td>
    	</tr>
    	<tr>
    		<td><span class="title"><?php echo $form->labelTag('Event/date', 'Time');?></span></td>		
    		<td>
    			<?php echo $html->hourOptionTag('Event/date'); ?>
    			<?php echo $html->minuteOptionTag('Event/date'); ?>
    			<?php echo $html->meridianOptionTag('Event/date'); ?>
    			<?php echo $html->checkbox('Event/allday', null, array());?>
    			<?php echo $form->labelTag('Event/allday', 'Allday');?>			
    		</td>
    		<td><?php echo $html->tagErrorMsg('Event/date', 'Please select the Time.');?></td>	
    	</tr>
    </table>

You should now have a fully working date picker. If you are having
problems, please post your comments and I will try to help resolve
them.

Screenshots:
````````````
`http://www.nexgentec.com/bakery/date-
picker.jpg`_`http://www.nexgentec.com/bakery/date-picker2.jpg`_

.. _http://www.frequency-decoder.com/2006/10/02/unobtrusive-date-picker-widgit-update: http://www.frequency-decoder.com/2006/10/02/unobtrusive-date-picker-widgit-update
.. _http://www.nexgentec.com/bakery/date-picker.jpg: http://www.nexgentec.com/bakery/date-picker.jpg
.. _http://www.nexgentec.com/bakery/date-picker2.jpg: http://www.nexgentec.com/bakery/date-picker2.jpg
.. meta::
    :title: Using the Unobtrusive Date Picker Widget in CakePHP
    :description: CakePHP Article related to popup calendar,date picker,Tutorials
    :keywords: popup calendar,date picker,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

