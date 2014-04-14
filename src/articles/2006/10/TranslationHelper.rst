TranslationHelper
=================

by m3nt0r on October 13, 2006

This uses the HTTP_ACCEPT_LANGUAGE to determine the current users most
accepted language. It takes the the first one of all accepted, then
looks up the available translations and delivers. If all fails, or no
good translation was found, it returns the default string.


Usage
`````

::

    $trans->text(array('default'=>'Title', 
    		'de_de'=>'Titel', 
    		'fr_fr'=>'Titre', 
    		'it_it'=>'Titolo')
    	    );



Code
````

::

    <?php
    
    /**
     * i18n Translation Helper
     *
     * Based on code from Hans Deragon
     * and his i18n Smarty Function
     *
     * Usage:
     *	 $trans->text(array('default'=>'Title', 
     *		'de_de'=>'Titel', 'fr_fr'=>'Titre',
     *		'it_it'=>'Titolo'));
     *
     * @version		0.1
     * @author		Kjell Bublitz <m3nt0r.de@gmail.com>
     * @copyright		Copyright 2006, Kjell Bublitz
     * @license		http://www.opensource.org/licenses/mit-license.php The MIT License
     * @link		http://www.m3nt0r.de/ Authors Homepage
     * @created		13.10.2006
     * @updated		13.10.2006
     */
    
    class TransHelper extends Helper
    {
    	var $lang = null;
    	var $translations = null;
    
    	function text($transArray)
    	{
    		$this->translations = $transArray;
    		$this->_findLanguage($lang);
    
    		if (!empty($this->lang))
    		{
    			$closestMatch = false;
    			list($usr_lang, $usr_country) = split('_', $this->lang);
    
    			foreach (array_keys($this->translations) as $availableTrans)
    			{
    				if ($availableTrans != 'default')
    				{
    					list($key_lang, $key_country) = split('_', $availableTrans);
    
    					if ($key_lang == $usr_lang)
    					{
    						if ($key_country == $usr_country)
    						{
    							return $this->translations[$availableTrans];
    						}
    						else
    						{
    							$closestMatch = $this->translations[$availableTrans];
    						}
    					}
    				}
    			}
    
    			// if user is en_en, but we only have en_us,
    			// then at least return 'en'.
    			if ($closestMatch)
    			{
    				return $closestMatch;
    			}
    			else // if not even close
    			{
    				return $this->_returnDefault();
    			}
    		}
    		else
    		{
    			return $this->_returnDefault();
    		}
    	}
    
    	function _findLanguage()
    	{
    		if (!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
    		{
    			$langarray = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
    
    			foreach ($langarray as $langkey)
    			{
    				$templang = explode(';', $langkey);
    				if(strstr($templang[0], '-')) {
    					$lang[] = str_replace('-','_',$templang[0]);
    				}
    			}
    			$this->lang = $lang[0];
    		}
    	}
    
    	function _returnDefault()
    	{
    		if(empty($this->translations['default']))
    		{
    			return "TranslationError: No default set.";
    		}
    		else
    		{
    			return $this->translations['default'];
    		}
    	}
    }
    ?>


.. meta::
    :title: TranslationHelper
    :description: CakePHP Article related to language,text,i18n,translation,Helpers
    :keywords: language,text,i18n,translation,Helpers
    :copyright: Copyright 2006 m3nt0r
    :category: helpers

