IRWeather (Weather for Iran cities)
===================================

by cybercoder on January 13, 2009

I saw yahoo weather component in the bakery, that sent by Mr.Arash
Hemmat. with special thanks to him, it seems yahoo weather system can
not cover all of Iran cities. but in Iran Weather Organization website
(weather.ir), i saw an xml file which contain very cities weather
information every day and i decided to write this component and
element. I hope useful for Iranian cake bakers. let's do it quick and
easy...
Step1: create a file "/app/controllers/components/ir_weather.php" then
copy below codes into it:

::

    
    
    <?php 
    /* Weather.ir API component for Cakephp
     * @author Vahid Alimohammadi http://cybercoder.ir
     * @version 1.0
     * Free 2 Use
    */
    uses('Xml') ;
    class IrWeatherComponent extends Object
    {
        var $WEATHER_API_URL='http://weather.ir/farsi/RSS/xml.asp';
     
        /*
         * Get today weathers Xml file from weather.ir
         * return array|bool $results
        */
        function get_weathers()
        {
            if($this->content = file_get_contents($this->WEATHER_API_URL))
            {
                $weather_xml=& new Xml($this->content) ;
    			$results=$weather_xml->toArray() ;
                return $results ;
            }
            else
            {
                $this->errors[]='Unable to connect to Weather.ir XML file. (output of http://weather.ir/farsi/RSS/xml.asp)';
                return false;
            }
        }
    	
    	function show_details($StationNo)
    	{
    		$weathers=$this->get_weathers() ;
    		foreach ($weathers['Root']['Row'] as $weather)
    		{
    			if ($weather['StationNo']==$StationNo)
    			{
    				if (!empty($weather['StationNo']))
    					$selected_weather['StationNo']=$weather['StationNo'] ;
    				if (!empty($weather['fcity']))
    					$selected_weather['fcity']=$weather['fcity'] ;
    				if (!empty($weather['DateTime1']))
    					$selected_weather['DateTime1']=$weather['DateTime1'] ;
    				else
    					$selected_weather['DateTime1']='Ù†Ø§Ù…Ø´Ø®Øµ' ;
    				if (!empty($weather['Tmp']))
    					$selected_weather['Tmp']=$weather['Tmp'] ;
    				else
    					$selected_weather['Tmp']='Ù†Ø§Ù…Ø´Ø®Øµ' ;
    				if (!empty($weather['WindSpeed']))
    					$selected_weather['WindSpeed']=$weather['WindSpeed'] ;
    				else
    					$selected_weather['WindSpeed']='Ù†Ø§Ù…Ø´Ø®Øµ' ;
    				if (!empty($weather['widir']))
    					$selected_weather['windir']=$weather['windir'] ;
    				else
    					$selected_weather['windir']='Ù†Ø§Ù…Ø´Ø®Øµ' ;
    				if (!empty($weather['Curr_weather']))
    					$selected_weather['Curr_weather']=$weather['Curr_weather'] ;
    			}
    		}
    		return $selected_weather ;	
    	}
    }
    ?>

Step2: Create the file (if not exist)
"/app/controllers/app_controller.php" and copy following codes in to
it:

::

    
    <?php
    class AppController extends Controller {
    	var $helpers = array('Html', 'Form', 'Ajax', 'Javascript');
    	var $components= array('IrWeather') ;
    	
    	function beforeRender()
    	{
    		if (!empty($this->data['weather']['fcities']))
    		{
    			$this->layout='Ajax' ;
    			$selected_weather=$this->IrWeather->show_details($this->data['weather']['fcities']) ;
    			echo 'ØªØ§Ø±ÛŒØ®: '.$selected_weather['DateTime1'].'<br>'.'Ø¯Ù…Ø§: '.$selected_weather['Tmp'].'<br>'.'Ø³Ø±Ø¹Øª Ø¨Ø§Ø¯:'.$selected_weather['WindSpeed'].'<br>'.'Ø¬Ù‡Øª Ø¨Ø§Ø¯:'.$selected_weather['windir'].'<br>'.'Ù‡ÙˆØ§ÛŒ ÙØ¹Ù„ÛŒ:'.$selected_weather['Curr_weather'] ;
    		}
    		$this->set('weathers',$this->IrWeather->get_weathers()) ;
    	}
    ?>

Step3 : create an element "/app/views/elements/ir_weather.ctp" and
copy below codes into it:

::

    
    	<select id="fcities" name="data[weather][fcities]">
    	<?php
    		foreach ($weathers['Root']['Row'] as $weather)
    		{
    			if (!empty($weather['StationNo']) && !empty($weather['fcity']))
    				echo '<option value="'.$weather['StationNo'].'">'.$weather['fcity'].'</option>' ;
    		}
    	?>
    	</select>
    	<?php
    		echo $ajax->observeField('fcities',array('url'=>'','update'=>'weather_details')) ;
    	?>
    	<div id="weather_details"></div>

Final Step:
add this line to "/app/views/layouts/default.ctp" file in a position
you want:

::

    
    <?php echo $this->element('ir_weather'); ?>

you can use images for weather status or optimize the fetch operation
with some little modifications.

.. meta::
    :title: IRWeather (Weather for Iran cities)
    :description: CakePHP Article related to weather,iran,Components
    :keywords: weather,iran,Components
    :copyright: Copyright 2009 cybercoder
    :category: components

