Yahoo weather component
=======================

Yahoo! developer network provides a RSS based API to access weather
information for locations all around the world. The component has a
method named get_weather($locationCode,$degreeUnit) that retrieves the
weather information for the given location code and outputs the data
in an easy to use array. Please read
"http://developer.yahoo.com/weather/" for more information about the
location code and degree unit. I will provide a tutorial as soon as
possible.


Component Class:
````````````````

::

    <?php 
    /* Yahoo! Weather API component for Cakephp
     * @author Arash Hemmat www.hemmat.biz
     * @version 1.2  some bugs has been fixed.
     * @license http://www.opensource.org/licenses/mit-license.php
     * Special thanks to "Ed Eliot" (http://www.ejeliot.com/blog/38) ,I used his code for this component.
     */
    
    /*
     * Yahoo weather component class
     */
    class yahooWeatherComponent extends Object
    {
    	/*
    	 * @var bool
    	 */
    	var $controller = true;
    
    	/*
    	 * @var array
    	 */
    	var $errors=array();
    
    	/*
    	 * @var string
    	 */
    	var $content;
    
    	/*
    	 * The url of Yahoo! weather API
    	 * @var string
    	 */
    	var $WEATHER_API_URL='http://weather.yahooapis.com/forecastrss?p=';
    
    	/*
    	 * THe address for images, you need to change this if you want to use your own images.
    	 * @var string
    	 */
    	var $WEATHER_IMAGE_BASE='http://l.yimg.com/us.yimg.com/i/us/we/52/';
    	
    	/*
    	 * initialization
    	 * @param bool $controller
    	 */
    	function startup(&$controller)
    	{
    		$this->controller =& $controller;
    	}
    
    	/*
    	 * Get the weather information for given location code
    	 * @param string $locationCode a code that represent a location in Yahoo weather system
    	 * @param string $degreeUnit it could be f: Fahrenheit or c: Celsius
    	 * @param bool $translate this parametr indicates if the result should be translated of not
    	 * @return array|bool $result
    	 */
    	function get_weather($locationCode,$degreeUnit='c')
    	{
    		if($this->content = file_get_contents($this->WEATHER_API_URL.$location_code.'&u='.$degreeUnit))
    		{
    			$result['location']=$this->GetTagAttributes('yweather:location');
    			$result['condition']=$this->GetTagAttributes('yweather:condition');
    			$result['units']=$this->GetTagAttributes('yweather:units');
    			$result['wind']=$this->GetTagAttributes('yweather:wind');
    			$result['atmosphere']=$this->GetTagAttributes('yweather:atmosphere');
    			$result['astronomy']=$this->GetTagAttributes('yweather:astronomy');
    			$result['geo:lat']=$this->GetTagValue('geo:lat');
    			$result['geo:long']=$this->GetTagValue('geo:long');
    			$result['link']=explode('*',$this->GetTagValue('link'));
    			$result['pubDate']=$this->GetTagValue('pubDate');
    			$result['description']=$this->GetTagValue('description');
    			$result['image_url']=$this->WEATHER_IMAGE_BASE.$result['condition']['code'].'.gif';
    			$forecasts=$this->GetTagAttributes('yweather:forecast');
    			$result['tonight_forecast']=$forecasts[0];
    			$result['tonight_forecast']['image_url']=$this->WEATHER_IMAGE_BASE.$result['tonight_forecast']['code'].'.gif';		
    			$result['tomorrow_forecast']=$forecasts[1];
    			$result['tomorrow_forecast']['image_url']=$this->WEATHER_IMAGE_BASE.$result['tomorrow_forecast']['code'].'.gif';
    			return $result;
    		}
    		else
    		{
    			$this->errors[]='Unable to connect to yahoo api.';
    			return false;
    		}
    	}
    
    	/*
    	 * @param string $sTag
    	 * @return bool
    	 */
    	function GetTagValue($sTag)
    	{
    		$aMatches = array();
    			
    		if (preg_match("/<$sTag>([^<]*)<\/$sTag>/i", $this->content, $aMatches))
    		{
    			$aResult = array();
    			$aResult['value'] = $aMatches[1];
    			return trim($aMatches[1]);
    		}
    		return false;
    	}
    
    	/*
    	 * @param string $sTag
    	 * @return string $aResult
    	 */
    	function GetTagAttributes($sTag)
    	{
    		$aMatches = array();
    			
    		if (preg_match_all("/<$sTag([^\/]*)\/>/i", $this->content, $aMatches))
    		{
    			$aResult = array();
    
    			for ($i = 0; $i < count($aMatches[1]); $i++)
    			{
    				$aSubMatches = array();
    
    				if (preg_match_all("/([^=]+)=\"([^\"]*)\"/i", $aMatches[1][$i], $aSubMatches))
    				{
    					for ($j = 0; $j < count($aSubMatches[1]); $j++)
    					{
    						$aResult[$i][trim($aSubMatches[1][$j])] = trim($aSubMatches[2][$j]);
    					}
    				}
    			}
    			$iNumResults = count($aResult);
    			if ($iNumResults > 1)
    			{
    				return $aResult;
    			} elseif ($iNumResults == 1)
    			{
    				return $aResult[0];
    			}
    		}
    		return false;
    	}
    }
    ?>



.. author:: arash.hemmat
.. categories:: articles, components
.. tags:: api,component,yahoo,weather,Components

