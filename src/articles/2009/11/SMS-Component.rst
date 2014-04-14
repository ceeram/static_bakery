SMS Component
=============

by webtechnick on November 07, 2009

A simple and free SMS gateway component based on the information
provided in
http://en.wikipedia.org/wiki/List_of_carriers_providing_SMS_transit.
This component aims to be as easy as the Email component but for text
messages.
SMS Component
Version 1.0
Author: Nick Baker (nick [at] webtechnick [dot] com)
Website: `http://www.webtechnick.com`_

Browse, Download, or Checkout the Component.
Browse: `http://projects.webtechnick.com/sms_component`_
Download: `http://projects.webtechnick.com/sms_component.tar.gz`_
SVN: svn co `http://svn2.xp-dev.com/svn/nurvzy-sms-component`_
Code: `Page 2: SMS Component Code`_

I suggest reading the README.txt file included within the component.
Installation is very straight forward.

This component is in beta and only tested with Sprint, AT, and
VerizonWireless as those are the only carriers I have access to. I
have no reason to believe the other carriers wont work, I just haven't
been able to test them yet. If you're willing to help, please let me
know via email, a comment on this article, or on my site:
`http://www.webtechnick.com/`_

INSTALL:
1) Move the sms.php into your /app/controllers/components/ directory
2) Add Sms to the component list in the controller you want to use it
in
Example:

Controller Class:
`````````````````

::

    <?php var $components = array('Sms');?>


USAGE:
You use the Sms component much like you would the Email component.

Example:

Controller Class:
`````````````````

::

    <?php 
    $this->Sms->number = '5551234567'; //10 digit cellphone number
    $this->Sms->carrier = 'Sprint'; //carrier string
    $this->Sms->from = '5553331111'; //10 digit cellphone number OR email address
    $this->Sms->text = 'This is a text message'; //Body of text message.
    $this->Sms->send(); //Actually send the text message.?>

Or you can pass in these properties as an options array.
Example2:

Controller Class:
`````````````````

::

    <?php $this->Sms->send(array(
      'number' => '5551234567', //10 digit cellphone number
      'carrier' => 'Sprint', //carrier string
      'text' => 'This is a text', //Body of the text message
      'from' => '5553331111' //10 digit cellphone number OR email address
    ));?>


TESTING SMS component:
The SMS component has a built in testSend feature that will test if
the SMS is ready to be sent or not.

Controller Class:
`````````````````

::

    <?php 
    if($this->Sms->testSend()){
      //Hurray, we can send it!
      $this->Sms->send();
    } else {
      //Oh no, can't send it, check the errors log to see why
      debug($this->Sms->errors);
    }
    ?>



Current Carrier String List:

::

      'ATT'           //AT&T
      'Boost'        //Boost Mobile
      'Cellular One'  //Cellular One
      'Cingular'      //Cingular
      'Cricket'      //Cricket
      'Nextel'        //Nextel
      'Sprint'        //Sprint
      'Qwest'        //Qwest
      'TMobile'       //T-Mobile
      'Verizon'      //Verizon Wireless
      'Virgin'        //Virgin Mobile

I've picked out the most common carriers in my area and for my own
purposes, but its very easy to add more to the list via carrierDomain
associative array.

Controller Class:
`````````````````

::

    <?php 
    function beforeFilter(){
      $this->Sms->carrierDomain['NewCarrier'] = 'new.carrier.domain.com';
      parent::beforeFilter();
    }
    ?>

Then you could reference you're new carrier like so:

Controller Class:
`````````````````

::

    <?php 
    $this->Sms->carrier = 'NewCarrier';
    $this->Sms->number = '5555551234';
    $this->Sms->text = 'This is a text message to NewCarrier';
    $this->Sms->send();
    ?>

I hope you find this component useful. If you like the component, find
a bug, or have a feature request please post a comment.

Thanks,
Nick



Controller Class:
`````````````````

::

    <?php 
    /***************************************************
      * Sms Component
      * 
      * Send SMS messages just like you would the Email component.
      * 
      * @copyright    Copyright 2009, Webtechnick
      * @link         http://www.webtechnick.com
      * @author       Nick Baker
      * @version      1.0
      * @license      MIT
      */
    class SmsComponent extends Object {
      
      /***************************************************
        * Load the email component.
        */
      var $components = array('Email');
      
      /***************************************************
        * Associative array of carriers to its email domain.
        * Emails will be sent to number@carrierDomain
        *
        * @var array of carrier domains.
        * @link http://en.wikipedia.org/wiki/SMS_gateway
        * @access public
        */
      var $carrierDomain = array(
        'ATT'           => 'txt.att.net',
        'Boost'         => 'myboostmobile.com',
        'Cellular One'  => 'mobile.celloneusa.com',
        'Cingular'      => 'cingularme.com',
        'Cricket'       => 'sms.mycricket.com',
        'Nextel'        => 'messaging.nextel.com',
        'Sprint'        => 'messaging.sprintpcs.com',
        'Qwest'         => 'qwestmp.com',
        'TMobile'       => 'tmomail.net',
        'Verizon'       => 'vtext.com',
        'Virgin'        => 'vmobl.com'
      );
      
      /***************************************************
        * The from email or number in which to send the text from.
        *
        * @var string of 10 numbers or an email address.
        * @access public
        */
      var $from = null;
      
      /***************************************************
        * The number in which to send the text to.
        *
        * @var string of 10 numbers.
        * @access public
        */
      var $number = null;
      
      /***************************************************
        * The carrier in which to send the text to.
        * @var string of the carrier (Sprint, Verizon, etc..)
        *
        * @access public
        */
      var $carrier = null;
      
      /***************************************************
        * The body text of the SMS message.
        *
        * @var string of the actual text to send
        * @access public
        */ 
      var $text = null;
      
      /***************************************************
        * data and params are the controller data and params
        *
        * @var array
        * @access public
        */
      var $data = array();
      var $params = array();
      
      /***************************************************
        * errors
        * @var array of errors the component comes across.
        * @access public
        */
      var $errors = array();
      
      /***************************************************
        * Initializes FileUploadComponent for use in the controller
        *
        * @param object $controller A reference to the instantiating controller object
        * @return void
        * @access public
        */
      function initialize(&$controller){
        $this->data = $controller->data;
        $this->params = $controller->params;
      }
      
      
      /***************************************************
        * Actually send the SMS.
        *
        * @return boolean true if sms sent, false if missing information
        * @access public
        * @param mixed options ('string of text or array of options (number, text, from, carrier)
        */
      function send($options = array()){
        if(is_string($options)){
          $this->text = $options;
        }
        
        $this->__setupSms($options);
        
        if($this->testSend()){
          $this->Email->to = $this->__buildSmsEmail();
          $this->Email->from = $this->from;
          $this->Email->sendAs = 'text';
          
          $this->Email->send($this->text);
          return true;
        }
        return false;
      }
      
      /***************************************************
        * this function decides if we can send the message or not
        *
        * @return boolean true if it can send the SMS, false if it ran into an error
        * @access public
        */
        function testSend(){
          if($this->__isReady()){
            return true;
          }
          if(!$this->number || strlen($this->number) < 10){
            $this->_error('SMSComponent::number is not set.');
          }
          if(strlen($this->number) < 10){
            $this->_error('SMSComponent::number is too short: must be at least 10 digits long');
          }
          if(!$this->carrier){
            $this->_error('SMSComponent::carrier is not set.');
          }
          if(!array_key_exists($this->carrier, $this->carrierDomain)){
            $this->_error("SMSComponent::carrier -- {$this->carrier} -- is not listed in available SMSComponent::carrierDomain list.");
          }
          if(!$this->text){
            $this->_error('SMSComponent::text is not set.');
          }
          
          return false;
        }
      
      /*************************************************
        * showErrors itterates through the errors array
        * and returns a concatinated string of errors sepearated by
        * the $sep
        *
        * @param string $sep A seperated defaults to <br />
        * @return string
        * @access public
        */
      function showErrors($sep = "<br />"){
        $retval = "";
        foreach($this->errors as $error){
          $retval .= "$error $sep";
        }
        return $retval;
      }
      
      /***************************************************
        * Adds error messages to the component
        *
        * @param string $text String of error message to save
        * @return void
        * @access protected
        */
      function _error($text){
        $message = __($text,true);
        $this->errors[] = $message;
      }
      
      /***************************************************
        * Sets up the class number, carrier, from, and text 
        * based on the options passed in.
        *
        * @return void
        * @access private
        * @param array of options (number, carrier, text)
        */
      function __setupSms($options){
        if(isset($options['number'])){
          $this->number = $options['number'];
        }
        if(isset($options['carrier'])){
          $this->carrier = $options['carrier'];
        }
        if(isset($options['text'])){
          $this->text = $options['text'];
        }
        if(isset($options['from'])){
          $this->from = $options['from'];
        }
      }
      
      /***************************************************
        * Algorythm to deside if we're ready to send an SMS
        *
        * @return boolean true if we're ready, false if not
        * @access private
        */
      function __isReady(){
        if($this->number && $this->carrier && strlen($this->number) >= 10 && array_key_exists($this->carrier, $this->carrierDomain) && $this->text){
          return true;
        }
        return false;
      }
      
      /***************************************************
        * Builds the Sms email to field from the number, carrier, and carrierDomain list
        *
        * @access private
        * @return string of sms email address or null if none found.
        */
      function __buildSmsEmail(){
        if($this->__isReady()){
          return $this->number . "@" . $this->carrierDomain["{$this->carrier}"];
        }
        else {
          return null;
        }
      }
    }
    ?>

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _http://www.webtechnick.com/: http://www.webtechnick.com/
.. _http://svn2.xp-dev.com/svn/nurvzy-sms-component: http://svn2.xp-dev.com/svn/nurvzy-sms-component
.. _Page 2: :///articles/view/4caea0e6-394c-4198-a52c-478f82f0cb67/lang:eng#page-2
.. _Page 1: :///articles/view/4caea0e6-394c-4198-a52c-478f82f0cb67/lang:eng#page-1
.. _http://projects.webtechnick.com/sms_component.tar.gz: http://projects.webtechnick.com/sms_component.tar.gz
.. _http://projects.webtechnick.com/sms_component: http://projects.webtechnick.com/sms_component
.. _Page 2: SMS Component Code: http://bakery.cakephp.org/leafs/view/131
.. meta::
    :title: SMS Component
    :description: CakePHP Article related to Components,sms,text messages
    :keywords: Components,sms,text messages
    :copyright: Copyright 2009 webtechnick
    :category: components

