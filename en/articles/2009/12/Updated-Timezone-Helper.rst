

Updated: Timezone Helper
========================

by %s on December 17, 2009

This is an updated Timezone Helper. Originally found here: Pulled,
then updated from this blog post:
http://planetcakephp.org/aggregator/items/2599-user-timezones-in-
cakephp
database:
- add a field "timezone" decimal (10,2)

Usage:
-edit:

::

     
      echo $timezone->select('timezone');
     

-view:(using CakePHPs Time Helper) Used to modify displayed time

::

    
      echo $time->format('M d, Y', $post['created'], null, $auth['User']['timezone']);

-view: Used to display current setting for timezone

::

    
    echo $timezone->display($user['User']['timezone']);




Helper Class:
`````````````

::

    <?php 
    class TimeZoneHelper extends AppHelper {
    	
        public $helpers = array('Form');
    	var $timezones = array(
        '-12.0' => '(GMT -12:00 hours) Eniwetok, Kwajalein',
        '-11.0' => '(GMT -11:00 hours) Midway Island, Somoa',
        '-10.0' => '(GMT -10:00 hours) Hawaii',
        '-9.0' => '(GMT -9:00 hours) Alaska',
        '-8.0' => '(GMT -8:00 hours) Pacific Time (US & Canada)',
        '-7.0' => '(GMT -7:00 hours) Mountain Time (US & Canada)',
        '-6.0' => '(GMT -6:00 hours) Central Time (US & Canada), Mexico City',
        '-5.0' => '(GMT -5:00 hours) Eastern Time (US & Canada), Bogota, Lima, Quito',
        '-4.0' => '(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz',
        '-3.5' => '(GMT -3:30 hours) Newfoundland',
        '-3.0' => '(GMT -3:00 hours) Brazil, Buenos Aires, Georgetown',
        '-2.0' => '(GMT -2:00 hours) Mid-Atlantic',
        '-1.0' => '(GMT -1:00 hours) Azores, Cape Verde Islands',
        '0.0' => '(GMT) Western Europe Time, London, Lisbon, Casablanca, Monrovia',
        '+1.0' => '(GMT +1:00 hours) CET(Central Europe Time), Brussels, Copenhagen, Madrid, Paris',
        '+2.0' => '(GMT +2:00 hours) EET(Eastern Europe Time), Kaliningrad, South Africa',
        '+3.0' => '(GMT +3:00 hours) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg, Volgograd, Nairobi',
        '+3.5' => '(GMT +3:30 hours) Tehran',
        '+4.0' => '(GMT +4:00 hours) Abu Dhabi, Muscat, Baku, Tbilisi',
        '+4.5' => '(GMT +4:30 hours) Kabul',
        '+5.0' => '(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent',
        '+5.5' => '(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi',
        '+6.0' => '(GMT +6:00 hours) Almaty, Dhaka, Colombo',
        '+7.0' => '(GMT +7:00 hours) Bangkok, Hanoi, Jakarta',
        '+8.0' => '(GMT +8:00 hours) Beijing, Perth, Singapore, Hong Kong, Chongqing, Urumqi, Taipei',
        '+9.0' => '(GMT +9:00 hours) Tokyo, Seoul, Osaka, Sapporo, Yakutsk',
        '+9.5' => '(GMT +9:30 hours) Adelaide, Darwin',
        '+10.0' => '(GMT +10:00 hours) EAST(East Australian Standard), Guam, Papua New Guinea, Vladivostok',
        '+11.0' => '(GMT +11:00 hours) Magadan, Solomon Islands, New Caledonia',
        '+12.0' => '(GMT +12:00 hours) Auckland, Wellington, Fiji, Kamchatka, Marshall Island'   
        );
    
        function select($fieldname, $label="Please Choose a timezone") {
       
        	$list = $this->Form->input($fieldname, array("type"=>"select", "label"=>$label, "options"=>$this->timezones, "error"=>"Please choose a timezone"));
        	return $this->output($list);
        }
    
    	function display($index) {
        	return $this->output($this->timezones[$index]);
        }
    }
    ?>


.. meta::
    :title: Updated: Timezone Helper
    :description: CakePHP Article related to helper,select,timezone,Helpers
    :keywords: helper,select,timezone,Helpers
    :copyright: Copyright 2009 
    :category: helpers

