Country Select List Helper
==========================

by %s on March 13, 2007

If you are having users register for your site, especially a customer
- you may want to know their country of residence. Instead of having
to create a select list and manually enter each country - this handy
helper does it for you all.
To use the below helper, just put this code into your view:

::

    
    <?php e( $countryList->select('country', 'Please select your country'));?>

The first argument is the table name in your model. The second is the
label you want to show on your site. The is a third options which
allows you to select the currently selected country to show. If not
passed, then "Please select a country" is selected. This allows you to
pass in an existing users country to the field for editing forms.
Finally, you can add an array for attributes such as class and id.

You'll now get a nice fully-filled list of all the countries in the
world. The values are stored as the 2 letter code for each country
(i.e. GB for United Kingdom, DE for Germany).


Helper Class:
`````````````

::

    <?php 
    /**
     * Helper for outputing a country select list.
     *
     * Allows you to include a selec list of all countries using 1 line of code.
     *
     * Author: Tane Piper (digitalspaghetti@gmail.com)
     * URL: http://digitalspaghetti.me.uk
     *
     * PHP versions 4 and 5
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     */
    
    
    class CountryListHelper extends FormHelper
    {
    	
    	var $helpers = array('Form');
    	
    	function select($fieldname, $label, $default="  ", $attributes)
    	{
    		$list = '<div class="input">';
    		$list .= $this->Form->label($label);
    		$list .= $this->Form->select($fieldname , array(
    			'  ' =>	'Please select a country',
    			'--' => 'None',
    			'AF' =>	'Afganistan',
    			'AL' =>	'Albania',
    			'DZ' =>	'Algeria',
    			'AS' => 'American Samoa',
    			'AD' => 'Andorra', 
    			'AO' => 'Angola',
    			'AI' => 'Anguilla',
    			'AQ' => 'Antarctica',
    			'AG' => 'Antigua and Barbuda', 
    			'AR' => 'Argentina', 
    			'AM' => 'Armenia', 
    			'AW' => 'Aruba', 
    			'AU' => 'Australia', 
    			'AT' => 'Austria', 
    			'AZ' => 'Azerbaijan',
    			'BS' => 'Bahamas', 
    			'BH' => 'Bahrain', 
    			'BD' => 'Bangladesh',
    			'BB' => 'Barbados',
    			'BY' => 'Belarus', 
    			'BE' => 'Belgium', 
    			'BZ' => 'Belize',
    			'BJ' => 'Benin', 
    			'BM' => 'Bermuda', 
    			'BT' => 'Bhutan',
    			'BO' => 'Bolivia', 
    			'BA' => 'Bosnia and Herzegowina',
    			'BW' => 'Botswana',
    			'BV' => 'Bouvet Island', 
    			'BR' => 'Brazil',
    			'IO' => 'British Indian Ocean Territory',
    			'BN' => 'Brunei Darussalam', 
    			'BG' => 'Bulgaria',
    			'BF' => 'Burkina Faso',
    			'BI' => 'Burundi', 
    			'KH' => 'Cambodia',
    			'CM' => 'Cameroon',
    			'CA' => 'Canada',
    			'CV' => 'Cape Verde',
    			'KY' => 'Cayman Islands',
    			'CF' => 'Central African Republic',
    			'TD' => 'Chad',
    			'CL' => 'Chile', 
    			'CN' => 'China',
    			'CX' => 'Christmas Island',	
    			'CC' => 'Cocos (Keeling) Islands', 
    			'CO' => 'Colombia',
    			'KM' => 'Comoros', 
    			'CG' => 'Congo', 
    			'CD' => 'Congo, the Democratic Republic of the', 
    			'CK' => 'Cook Islands',
    			'CR' => 'Costa Rica',
    			'CI' => 'Cote d\'Ivoire', 
    			'HR' => 'Croatia (Hrvatska)',
    			'CU' => 'Cuba',
    			'CY' => 'Cyprus',
    			'CZ' => 'Czech Republic',
    			'DK' => 'Denmark', 
    			'DJ' => 'Djibouti',
    			'DM' => 'Dominica',
    			'DO' => 'Dominican Republic',
    			'TP' => 'East Timor',
    			'EC' => 'Ecuador', 
    			'EG' => 'Egypt', 
    			'SV' => 'El Salvador', 
    			'GQ' => 'Equatorial Guinea', 
    			'ER' => 'Eritrea', 
    			'EE' => 'Estonia', 
    			'ET' => 'Ethiopia',
    			'FK' => 'Falkland Islands (Malvinas)', 
    			'FO' => 'Faroe Islands', 
    			'FJ' => 'Fiji',
    			'FI' => 'Finland',
    			'FR' => 'France',
    			'FX' => 'France, Metropolitan',
    			'GF' => 'French Guiana', 
    			'PF' => 'French Polynesia',
    			'TF' => 'French Southern Territories', 
    			'GA' => 'Gabon', 
    			'GM' => 'Gambia',
    			'GE' => 'Georgia', 
    			'DE' => 'Germany', 
    			'GH' => 'Ghana', 
    			'GI' => 'Gibraltar', 
    			'GR' => 'Greece',
    			'GL' => 'Greenland', 
    			'GD' => 'Grenada', 
    			'GP' => 'Guadeloupe',
    			'GU' => 'Guam',
    			'GT' => 'Guatemala', 
    			'GN' => 'Guinea',
    			'GW' => 'Guinea-Bissau', 
    			'GY' => 'Guyana',
    			'HT' => 'Haiti', 
    			'HM' => 'Heard and Mc Donald Islands', 
    			'VA' => 'Holy See (Vatican City State)', 
    			'HN' => 'Honduras',
    			'HK' => 'Hong Kong', 
    			'HU' => 'Hungary', 
    			'IS' => 'Iceland', 
    			'IN' => 'India', 
    			'ID' => 'Indonesia', 
    			'IR' => 'Iran (Islamic Republic of)',
    			'IQ' => 'Iraq',
    			'IE' => 'Ireland', 
    			'IL' => 'Israel',
    			'IT' => 'Italy', 
    			'JM' => 'Jamaica', 
    			'JP' => 'Japan',
    			'JO' => 'Jordan',
    			'KZ' => 'Kazakhstan',
    			'KE' => 'Kenya', 
    			'KI' => 'Kiribati',
    			'KP' => 'Korea, Democratic People\'s Republic of',
    			'KR' => 'Korea, Republic of',
    			'KW' => 'Kuwait',
    			'KG' => 'Kyrgyzstan',
    			'LA' => 'Lao People\'s Democratic Republic',
    			'LV' => 'Latvia',
    			'LB' => 'Lebanon',
    			'LS' => 'Lesotho', 
    			'LR' => 'Liberia', 
    			'LY' => 'Libyan Arab Jamahiriya',
    			'LI' => 'Liechtenstein', 
    			'LT' => 'Lithuania',
    			'LU' => 'Luxembourg',
    			'MO' => 'Macau', 
    			'MK' => 'Macedonia, The Former Yugoslav Republic of',
    			'MG' => 'Madagascar',
    			'MW' => 'Malawi',
    			'MY' => 'Malaysia',
    			'MV' => 'Maldives',
    			'ML' => 'Mali',
    			'MT' => 'Malta',
    			'MH' => 'Marshall Islands',
    			'MQ' => 'Martinique',
    			'MR' => 'Mauritania',
    			'MU' => 'Mauritius',
    			'YT' => 'Mayotte', 
    			'MX' => 'Mexico',
    			'FM' => 'Micronesia, Federated States of',
    			'MD' => 'Moldova, Republic of',
    			'MC' => 'Monaco',
    			'MN' => 'Mongolia',
    			'MS' => 'Montserrat',
    			'MA' => 'Morocco',
    			'MZ' => 'Mozambique',
    			'MM' => 'Myanmar',
    			'NA' => 'Namibia',
    			'NR' => 'Nauru', 
    			'NP' => 'Nepal', 
    			'NL' => 'Netherlands',
    			'AN' => 'Netherlands Antilles',
    			'NC' => 'New Caledonia',
    			'NZ' => 'New Zealand', 
    			'NI' => 'Nicaragua', 
    			'NE' => 'Niger', 
    			'NG' => 'Nigeria', 
    			'NU' => 'Niue',
    			'NF' => 'Norfolk Island',
    			'MP' => 'Northern Mariana Islands',
    			'NO' => 'Norway',
    			'OM' => 'Oman',
    			'PK' => 'Pakistan',
    			'PW' => 'Palau',
    			'PA' => 'Panama',
    			'PG' => 'Papua New Guinea',
    			'PY' => 'Paraguay',
    			'PE' => 'Peru',
    			'PH' => 'Philippines',
    			'PN' => 'Pitcairn',
    			'PL' => 'Poland',
    			'PT' => 'Portugal',
    			'PR' => 'Puerto Rico',
    			'QA' => 'Qatar',
    			'RE' => 'Reunion',
    			'RO' => 'Romania',
    			'RU' => 'Russian Federation',
    			'RW' => 'Rwanda',
    			'KN' => 'Saint Kitts and Nevis', 
    			'LC' => 'Saint LUCIA', 
    			'VC' => 'Saint Vincent and the Grenadines',
    			'WS' => 'Samoa', 
    			'SM' => 'San Marino',
    			'ST' => 'Sao Tome and Principe',
    			'SA' => 'Saudi Arabia',
    			'SN' => 'Senegal',
    			'SC' => 'Seychelles',
    			'SL' => 'Sierra Leone',
    			'SG' => 'Singapore', 
    			'SK' => 'Slovakia (Slovak Republic)',
    			'SI' => 'Slovenia',
    			'SB' => 'Solomon Islands',
    			'SO' => 'Somalia', 
    			'ZA' => 'South Africa',
    			'GS' => 'South Georgia and the South Sandwich Islands',
    			'ES' => 'Spain',
    			'LK' => 'Sri Lanka',
    			'SH' => 'St. Helena',
    			'PM' => 'St. Pierre and Miquelon', 
    			'SD' => 'Sudan', 
    			'SR' => 'Suriname',
    			'SJ' => 'Svalbard and Jan Mayen Islands',
    			'SZ' => 'Swaziland', 
    			'SE' => 'Sweden',
    			'CH' => 'Switzerland', 
    			'SY' => 'Syrian Arab Republic',
    			'TW' => 'Taiwan, Province of China',
    			'TJ' => 'Tajikistan',
    			'TZ' => 'Tanzania, United Republic of',
    			'TH' => 'Thailand',
    			'TG' => 'Togo',
    			'TK' => 'Tokelau',
    			'TO' => 'Tonga', 
    			'TT' => 'Trinidad and Tobago', 
    			'TN' => 'Tunisia', 
    			'TR' => 'Turkey',
    			'TM' => 'Turkmenistan',
    			'TC' => 'Turks and Caicos Islands',
    			'TV' => 'Tuvalu',
    			'UG' => 'Uganda',
    			'UA' => 'Ukraine',
    			'AE' => 'United Arab Emirates',
    			'GB' => 'United Kingdom',
    			'US' => 'United States',
    			'UM' => 'United States Minor Outlying Islands',
    			'UY' => 'Uruguay', 
    			'UZ' => 'Uzbekistan',
    			'VU' => 'Vanuatu', 
    			'VE' => 'Venezuela',
    			'VN' => 'Viet Nam',
    			'VG' => 'Virgin Islands (British)',
    			'VI' => 'Virgin Islands (U.S.)', 
    			'WF' => 'Wallis and Futuna Islands', 
    			'EH' => 'Western Sahara',
    			'YE' => 'Yemen', 
    			'YU' => 'Yugoslavia',
    			'ZM' => 'Zambia',
    			'ZW' => 'Zimbabwe'			
    			), $default, $attributes);
    		$list .= '</div>';
    		return $this->output($list);
    	}
    
    }
    ?>


.. meta::
    :title: Country Select List Helper
    :description: CakePHP Article related to country,countries,form,Helpers
    :keywords: country,countries,form,Helpers
    :copyright: Copyright 2007 
    :category: helpers

