Multi-language country combobox
===============================

Based on the excellent article written by Tane Piper, I decided to
take the base code and complete it with multi-language capability
based on XML contents build with Excel. The version created contains
an ISO 3166 list of country in 6 languages and you can add more
languages or correct country names without having to change the Helper
code.


Building the Excel File
~~~~~~~~~~~~~~~~~~~~~~~

Excel was chosen to ease data handling. The first column corresponds
to the ISO 3166 code of the country and columns B to G correspond to
the diferent languages I chose to integrate (respectively English,
Portuguese, French, German, Russian and Spanish). The first line will
identify the language parameter to be called by the Helper and also
the name of the XML file created after clicking the button "Build XML"
(EN.xml, PT.xml, FR.xml, DE.xml. RU.xml, ES.xml). At this point a
simple macro is triggered to generate as much xml files as languages
available in columns. Obviouly you can add your own languages.

Also, since i don't know every translations in every languages, some
gaps are remaining. The macro will jump over those gaps and won't
generate any XML for them. Feel free to complete and supply me with a
more complete file, so that everybody can benefit.

The Excel file will soon be available:
`http://www.webrickco.com/download/listofcountries.zip`_


Sample xml file
~~~~~~~~~~~~~~~

Xml is generate by the excel macro and should look like this:
EN.xml in app/views/helpers/

::

    
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <root>
    <countries>
    <select id="">Please select a country</select>
    <nothing id="--">None</nothing>
    <country id="AD">Andorra</country>
    <country id="AE">United Arab Emirates</country>
    <country id="AF">Afganistan</country>
    <country id="AG">Antigua and Barbuda</country>
    <country id="AI">Anguilla</country>
    [...]
    </countries>
    </root>



Helper code
~~~~~~~~~~~

An UTF-8 file should be created at app/views/helpers/country_list.php


Helper Class:
`````````````

::

    <?php 
    class CountryListHelper extends FormHelper 
    { 
        var $helpers = array('Form'); 
    
        
        function select($fieldname, $label, $lang, $featured, $default="  ", $attributes) 
        { 
          $all = array();
          $country = array();
          $ar_featured = array();
          
          $ar_featured = split(";",$featured);
          if (is_file(HELPERS.$lang.'.xml'))
    			{
    				$dom = new DOMDocument();
    				$dom->load(HELPERS.$lang.'.xml');
    
    				//get headers
    				$elements = $dom->getElementsByTagname('select');
    				for ($i = 0; $i < $elements->length; $i++) 
    					$all[$elements->item($i)->getAttribute('id')] = $elements->item($i)->nodeValue;
    
    				$elements = $dom->getElementsByTagname('nothing');
    				for ($i = 0; $i < $elements->length; $i++) 
    					$all[$elements->item($i)->getAttribute('id')] = $elements->item($i)->nodeValue;
    					
    				if (is_array($ar_featured))
    					$all['sep1'] = "___________________________________";
    
    				//list of countries
    				$elements = $dom->getElementsByTagname('country');
    				for ($i = 0; $i < $elements->length; $i++) 
    				{
    					if (is_array($ar_featured) && in_array($elements->item($i)->getAttribute('id'),$ar_featured))
    						$all[$elements->item($i)->getAttribute('id')] = $elements->item($i)->nodeValue;
    					else
    						$country[$elements->item($i)->getAttribute('id')] = $elements->item($i)->nodeValue;
    				}
    				if (is_array($country))
    					asort($country);
    				
    				if (is_array($ar_featured))
    					$all['sep2'] = "___________________________________";
    					
    				foreach ($country as $key => $value)
    					$all[$key] = $value;	
    			
    			}
    
          $list = '<div class="input">'; 
          $list .= $this->Form->label($label); 
          $list .= $this->Form->select($fieldname , $all, $default, $attributes, false); 
          $list .= '</div>'; 
          return $this->output($list); 
        } 
    } 
    ?>



Calling the Helper
~~~~~~~~~~~~~~~~~~

Parameters to call the helper are as follow:
Param1: Name of the field,
Param2: Label of the field,
Param3: Xml language file to be loaded,
Param4: Featured list of countries (they will appear first),
Param5: Selected default country,
Param6: array of paramaters for the field.

Sample:

::

    
    echo $countryList->select('/Menu/pais', ' ', 'FR', 'FR;IT;PT;GE;BG', 'PT', array('class'=>'inputbox'));




.. _http://www.webrickco.com/download/listofcountries.zip: http://www.webrickco.com/download/listofcountries.zip

.. author:: davidc2p
.. categories:: articles, helpers
.. tags:: ,Helpers

