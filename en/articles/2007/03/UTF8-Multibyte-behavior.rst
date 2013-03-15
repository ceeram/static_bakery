

UTF8 Multibyte behavior
=======================

by %s on March 24, 2007

Simple way to make sure all your persisted data is in utf8 / same
encoding
As I was developing my CMS system, and we began to get foreign
customers, I rather quickly realised, that storing all data in
ISO-8859-15 (danish charset) was a rather bad idea.

But just the though about adding UTF-8 handling in each of my CRUD
methods gave me the chills :)

As the bleeding-edge guy I am, I was of course developing in 1.2.x.x,
so why not take full advantage of this, and develop a behavior, that
would seamlessly be intigrated in any application, and automatic
handle all the trivial work of encoding / decoding data.

** Updated 23/03/2007 **
A few bugfixes, and new param array (read/write)

A few examples:

Example 1
~~~~~~~~~
Would use default settings:
Encode to UTF-8 on save
Decode to ISO-8859-15 on read


Model Class:
````````````

::

    <?php 
    class Page extends Model {
        var $name   = "Page";
        var $actsAs = array('Utf8');
    }
    ?>



Example 2
~~~~~~~~~

Model Class:
````````````

::

    <?php 
    class Page extends Model {
        var $name   = "Page";
        var $actsAs = array('Utf8' => array('save' => array('convertTo' => 'UTF-8') ) );
    }
    ?>



Model Class:
````````````

::

    <?php 
    /**
     * @copyright       Copyright (c) 2007 Enovo
     * @author          Christian Winther
     * @link            http://www.enovo.dk
     * @filesource
     * @since           1.1
     * @package         sw.model.behaviors
     * @modifiedby      $LastChangedBy:$
     * @lastmodified    $Date:$
     * @svn             $Id:$
     */
    class Utf8Behavior extends ModelBehavior {
        /**
         * Default settings for our model
         *
         * 'convertTo' is the target output encoding
         *
         * 'primaryOnly' is if 'finder' should only convert if its the primary model
         *
         * 'use_mbstring' enable / disable the use of mbstring to decode strings
         *
         * 'convertFrom' can either be
         *      - auto :
         *          Attemps to auto detect the source encoding
         *      - array('UTF-8',...')
         *          A list of possible encodings to try
         *
         * @var array
         */
        var $defaultSettings = array(
            'save'  => array(
                'useMbstring'   => false,
                'convert'       => true,
                'convertTo'     => 'UTF-8',
                'convertFrom'   => array('ISO-8859-15','UTF-8')
            ),
            'read'  => array(
                'useMbstring'   => false,
                'convert'       => true,
                'primaryOnly'   => true,
                'convertTo'     => 'ISO-8859-15',
                'convertFrom'   => array('UTF-8')
            )
        );
    
        /**
         * List of valid encodings
         *
         * @var array
         */
        var $validEncodings;
    
        /**
         * List of model settings
         *
         * @var array
         */
        var $settings = array();
    
        /**
         * Setup callback
         *
         * @param AppModel $model
         * @param array $config
         */
    	function setup(&$model, $config = array() )
    	{
    	    if( true === empty( $config ) ) { $config = array(); }
    
    	    // Merge user settings with default
    		$settings = am($this->defaultSettings, $config );
    
    		foreach ( $settings AS $mode )
    		{
        		if( true === $mode['useMbstring'] && false !== $mode['convertTo'] )
        		{
                    if( false === function_exists('mb_convert_encoding') )
                    {
                        trigger_error('Sorry, your PHP version does not support mbstring functions. Please read notes at http://php.net/mbstring',E_USER_ERROR);
                    }
    
                    // Check if we have a list of all valid encodings supported by PHP
                    if( true === empty( $this->validEncodings ) )
                    {
        	           // Build the list of valid encodings
        	           $this->validEncodings = mb_list_encodings();
        	       }
    
        	       // Check if we have valid encodings in our list
        	       if( false === array_search( $mode['convertTo'], $this->validEncodings ) )
        	       {
                        trigger_error('Invalid target encoding for "'.$model->name.'::find" - '. $mode['convertTo'] .' is not valid!', E_USER_ERROR );
        	       }
        		}
    		}
            $this->settings[ $model->name ] = $settings;
    	}
    
    	/**
    	 * Callback for when model is saving
    	 *
    	 * @param AppModel $model
    	 */
        function beforeSave(&$model)
        {
            $settings = $this->settings[ $model->name ]['save'];
            if( false === $settings['convertTo'] ) {
                return true;
            }
    
            // Should we encode using mbstring ?
            if( true === $settings['useMbstring'] )
            {
                $model->data = $this->doMultibyte( $model->data, $settings );
            }
            else
            {
                $model->data = $this->doEncode( $model->data, $settings );
            }
            return true;
        }
    
        /**
         * Callback for when model is reading
         *
         * @param AppModel $model
         * @param array $results
         * @param boolean $primary
         */
        function afterFind(&$model, $results, $primary)
        {
            $settings = $this->settings[ $model->name ]['read'];
    
            if( false === $settings['convert'] )
            {
                return $results;
            }
    
            // Check if we should only handle primary model data
            if( true === $settings['primaryOnly'] && true !== $primary ) {
                return $results;
            }
    
            // Should we decode using mbstring ?
            if( true === $settings['useMbstring'] ) {
                return $this->doMultibyte( $results, $settings );
            }
    
            // Normal utf8 decode to ISO-8859-1
            return $this->doDecode( $results, $settings );
        }
    
        /**
         * Decode UTF-8 to another encoding, with multibyte support
         *
         * @param mixed $data
         * @param array $settings
         * @return mixed
         */
        function doMultibyte( $data, $settings ) {
            if( true === is_array( $data ) ) {
                if( 0 === count( $data ) ) {
                   return $data;
                }
                foreach ( $data AS $key => $name ) {
                    $data[ $key ] = $this->doDecode( $name, $settings );
                }
                return $data;
            }
            return mb_convert_encoding( $data, $settings['convertTo'], $settings['convertFrom'] );
        }
    
        /**
         * Decode UTF-8 back to ISO-8859-1 single-byte encoding
         *
         * @param mixed $data
         * @param array $settings
         * @return mixed
         */
        function doDecode( $data, $settings ) {
            if( true === is_array( $data ) ) {
                if( 0 === count( $data ) ) {
                   return $data;
                }
                foreach ( $data AS $key => $name ) {
                    $data[ $key ] = $this->doDecode( $name, $settings );
                }
                return $data;
            }
            return utf8_decode($data);
        }
    
        /**
         * Do the converting of data to UTF-8, recursive
         *
         * @param array $data
         * @param array $settings
         * @return array
         */
        function doEncode( $data, $settings ) {
            if( true === is_array( $data ) ) {
                if( 0 === count( $data ) ) {
                   return $data;
                }
                foreach ( $data AS $key => $name ) {
                    $data[ $key ] = $this->doEncode( $name, $settings );
                }
                return $data;
            }
            if( true === $this->isUTF8( $data ) ) {
                return $data;
            }
            return utf8_encode($data);
        }
    
        /**
         * Method to check if a string is UTF-8
         *
         * @param string $string
         * @return boolean
         */
        function isUTF8($string)
        {
            // from http://w3.org/International/questions/qa-forms-utf-8.html
            return 0 != preg_match('%^(?:
                     [\x09\x0A\x0D\x20-\x7E]            # ASCII
                   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
                   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
                   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
                   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
               )*$%xs', $string);
        }
    }
    ?>


.. meta::
    :title: UTF8 Multibyte behavior
    :description: CakePHP Article related to i18n,UTF8,behavior,multibyte,l10n,Behaviors
    :keywords: i18n,UTF8,behavior,multibyte,l10n,Behaviors
    :copyright: Copyright 2007 
    :category: behaviors

