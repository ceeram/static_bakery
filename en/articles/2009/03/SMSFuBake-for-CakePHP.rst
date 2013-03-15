

SMSFuBake for CakePHP
=====================

by %s on March 12, 2009

Simple CakePHP Component and Helper for sending and receiving SMS text
messages to/from mobile devices freely using public email to SMS
gateways and a gmail account.
At Pascal Metrics, we often use CakePHP to prototype webapps before
diving into full J2EE development. We recently used this Component and
Helper combination to support free delivery and receipt of SMS
messages to/from mobile devices within a prototype and are happy to
share it here with the CakePHP and larger opensource community.

Feel free to `post any comments or feedback to our blog`_.


Usage:
``````
First, add the following Helper and Component to your project.


Helper Class:
`````````````

::

    <?php 
    /**
     * SMSFuBake Helper for CakePHP 
     * @author Eric Simmerman <eric.simmerman @nospam pascalmetrics.com>
     * @copyright Pascal Metrics
     * @link http://www.cakephp.org CakePHP
     * @link http://www.pascalmetrics.com Pascal Metrics, Inc.
     *
     *  Permission is hereby granted, free of charge, to any person obtaining
     *  a copy of this software and associated documentation files (the
     *  "Software"), to deal in the Software without restriction, including
     *  without limitation the rights to use, copy, modify, merge, publish,
     *  distribute, sublicense, and/or sell copies of the Software, and to
     *  permit persons to whom the Software is furnished to do so, subject to
     *  the following conditions:
     *   
     *  The above copyright notice and this permission notice shall be
     *  included in all copies or substantial portions of the Software.
     *   
     *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
     *  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
     *  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
     *  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
     *  LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
     *  OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
     *  WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
     */ 
    class CarrierGatewaySelectorHelper extends AppHelper {
    
        /**
         * Helpers used.
         **/
        var $helpers = array('Form');
    
        /**
         * Returns the HTML for the carrier selection form input
         */
        function render($additionalOptions = null){
            $supportedCarriers = array(
                "message.alltell.com" => "Alltell",
                "paging.acswireless.com" => "Ameritech",
                "txt.att.net" => "AT&T",
                "blsdcs.net" => "BellSouth Mobility",
                "blueskyfrog.com" => "Blueskyfrog",
                "myboostmobile.com" => "Boost",
                "csouth1.com" => "Cellular South",
                "mobile.kajeet.net" => "kajeet",
                "mymetropcs.com" => "MetroPCS",
                "ptel.net" => "Powertel",
                "sms.pscel.com" => "PSC Wireless",
                "qwestmp.com" => "Qwest",
                "page.southernlinc.com" => "Southernlink",
                "messaging.sprintpcs.com" => "Sprint",
                "tms.suncom.com" => "Suncom",
                "tmomail.net" => "T-Mobile",
                "vmobl.net" => "Virgin",
                "vtext.com" => "Verizon",       
            );
            $options = array(       
                'div' => null,           
                'empty' => true, 
                'label' => 'Destination Carrier',
                'options' => $supportedCarriers,            
            );
            if( !empty($additionalOptions) ){
                $options = array_merge($options,$additionalOptions);
            }        
            return $this->Form->input('carrier', $options);
        }
    }
    ?>



Component Class:
````````````````

::

    <?php 
    /**
     * SMSFuBake Component for CakePHP 
     * @author Eric Simmerman <eric.simmerman @nospam pascalmetrics.com>
     * @copyright Pascal Metrics
     * @link http://www.cakephp.org CakePHP
     * @link http://www.pascalmetrics.com Pascal Metrics, Inc.
     *
     *  Permission is hereby granted, free of charge, to any person obtaining
     *  a copy of this software and associated documentation files (the
     *  "Software"), to deal in the Software without restriction, including
     *  without limitation the rights to use, copy, modify, merge, publish,
     *  distribute, sublicense, and/or sell copies of the Software, and to
     *  permit persons to whom the Software is furnished to do so, subject to
     *  the following conditions:
     *   
     *  The above copyright notice and this permission notice shall be
     *  included in all copies or substantial portions of the Software.
     *   
     *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
     *  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
     *  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
     *  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
     *  LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
     *  OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
     *  WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
     */ 
    class SmsGatewayComponent extends Object {
    
    	var $components = array('Email');
    
    	public function sendSms($message, $toPhoneNumber, $carrierGateway, $options=array(), $smtpOptions=null) {
    		
            $this->Email->to = '<'.$toPhoneNumber.'@'.$carrierGateway.'>';
            if(isset($options['subject'])){
                $this->Email->subject = $options['subject'];
            }
    	    if(isset($options['from'])){
                $this->Email->from = $options['from'];
            }                        
            $this->Email->template = null;
            $this->Email->sendAs = 'text';
    
            if($smtpOptions){
                $this->Email->smtpOptions = $smtpOptions;
                $this->Email->delivery = 'smtp';    	
            }                
            $this->Email->send($message);
            return $this->Email->smtpError;
    	}
    
    }
    ?>



.. _post any comments or feedback to our blog: http://blog.pascalmetrics.com/2009/02/smsfubake-for-cakephp.html
.. meta::
    :title: SMSFuBake for CakePHP
    :description: CakePHP Article related to sms,gmail,Components
    :keywords: sms,gmail,Components
    :copyright: Copyright 2009 
    :category: components

