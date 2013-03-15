Paypal Payments Component Using cURL
====================================

by %s on July 08, 2009

Updated: July 5th, 2009: Added Express Checkout and modified both the
component and the file under vendors. Also you will need to change the
line that calls the paypal component for the direct payment
controller. ----------------------------------------------- Looking
for a lightweight, easy to use, Paypal credit card processing script?
You have found it! This is for Cake 1.2. All you need is cURL and a
Paypal API account! I will be updating it with more features when I
get a chance. On my to do list is refund by transaction Id, and mass
refund by an array of transaction ids.
Pre-requisites:
-cURL
-Paypal Account with API keys and such

vendors/paypal/Paypal.php

::

    
    <?php
    /***********************************************************
    This File Sets Up Calls to Paypal by arranging url information.
    ***********************************************************/
    class Paypal{
    	
    	function __construct(){
    		
    	}
    	
    	function DoDirectPayment($paymentInfo=array()){
    		/**
    		 * Get required parameters from the web form for the request
    		 */
    		$paymentType =urlencode('Sale');
    		$firstName =urlencode($paymentInfo['Member']['first_name']);
    		$lastName =urlencode($paymentInfo['Member']['last_name']);
    		$creditCardType =urlencode($paymentInfo['CreditCard']['credit_type']);
    		$creditCardNumber = urlencode($paymentInfo['CreditCard']['card_number']);
    		$expDateMonth =urlencode($paymentInfo['CreditCard']['expiration_month']);
    		$padDateMonth = str_pad($expDateMonth, 2, '0', STR_PAD_LEFT);
    		$expDateYear =urlencode($paymentInfo['CreditCard']['expiration_year']);
    		$cvv2Number = urlencode($paymentInfo['CreditCard']['cv_code']);
    		$address1 = urlencode($paymentInfo['Member']['billing_address']);
    		$address2 = urlencode($paymentInfo['Member']['billing_address2']);
    		$country = urlencode($paymentInfo['Member']['billing_country']);
    		$city = urlencode($paymentInfo['Member']['billing_city']);
    		$state =urlencode($paymentInfo['Member']['billing_state']);
    		$zip = urlencode($paymentInfo['Member']['billing_zip']);
    		
    		$amount = urlencode($paymentInfo['Order']['theTotal']);
    		$currencyCode="USD";
    		$paymentType=urlencode('Sale');
    		
    		$ip=$_SERVER['REMOTE_ADDR'];
    		
    		/* Construct the request string that will be sent to PayPal.
    		   The variable $nvpstr contains all the variables and is a
    		   name value pair string with & as a delimiter */
    		$nvpstr="&PAYMENTACTION=Sale&IPADDRESS=$ip&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber&EXPDATE=".$padDateMonth.$expDateYear."&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address1&STREET2=$address2&CITYNAME=$city&STATEORPROVINCE=$state".
    		"&POSTALCODE=$zip&COUNTRY=$country&CURRENCYCODE=$currencyCode";
    		
    		/* Make the API call to PayPal, using API signature.
    		   The API response is stored in an associative array called $resArray */
    		$resArray=$this->hash_call("doDirectPayment",$nvpstr);
    		
    		/* Display the API response back to the browser.
    		   If the response from PayPal was a success, display the response parameters'
    		   If the response was an error, display the errors received using APIError.php.
    		   */
    		
    		return $resArray;
    		//Contains 'TRANSACTIONID,AMT,AVSCODE,CVV2MATCH, Or Error Codes'
    	}
    
    	function SetExpressCheckout($paymentInfo=array()){
    		$amount = urlencode($paymentInfo['Order']['theTotal']);
    		$paymentType=urlencode('Sale');
    		$currencyCode=urlencode('USD');
    		
    		$returnURL =urlencode($paymentInfo['Order']['returnUrl']);
    		$cancelURL =urlencode($paymentInfo['Order']['cancelUrl']);
    
    		$nvpstr='&AMT='.$amount.'&PAYMENTACTION='.$paymentType.'&CURRENCYCODE='.$currencyCode.'&RETURNURL='.$returnURL.'&CANCELURL='.$cancelURL;
    		$resArray=$this->hash_call("SetExpressCheckout",$nvpstr);
    		return $resArray;
    	}
    	
    	function GetExpressCheckoutDetails($token){
    		$nvpstr='&TOKEN='.$token;
    		$resArray=$this->hash_call("GetExpressCheckoutDetails",$nvpstr);
    		return $resArray;
    	}
    	
    	function DoExpressCheckoutPayment($paymentInfo=array()){
    		$paymentType='Sale';
    		$currencyCode='USD';
    		$serverName = $_SERVER['SERVER_NAME'];
    		$nvpstr='&TOKEN='.urlencode($paymentInfo['TOKEN']).'&PAYERID='.urlencode($paymentInfo['PAYERID']).'&PAYMENTACTION='.urlencode($paymentType).'&AMT='.urlencode($paymentInfo['ORDERTOTAL']).'&CURRENCYCODE='.urlencode($currencyCode).'&IPADDRESS='.urlencode($serverName); 
    		$resArray=$this->hash_call("DoExpressCheckoutPayment",$nvpstr);
    		return $resArray;
    	}
    	
    	function APIError($errorNo,$errorMsg,$resArray){
    		$resArray['Error']['Number']=$errorNo;
    		$resArray['Error']['Number']=$errorMsg;
    		return $resArray;
    	}
    	
    	function hash_call($methodName,$nvpStr)
    	{
    		require_once 'constants.php';
    		
    		$API_UserName=API_USERNAME;
    		$API_Password=API_PASSWORD;
    		$API_Signature=API_SIGNATURE;
    		$API_Endpoint =API_ENDPOINT;
    		$version=VERSION;
    		
    		//setting the curl parameters.
    		$ch = curl_init();
    		curl_setopt($ch, CURLOPT_URL,$API_Endpoint);
    		curl_setopt($ch, CURLOPT_VERBOSE, 1);
    	
    		//turning off the server and peer verification(TrustManager Concept).
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    	
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    		curl_setopt($ch, CURLOPT_POST, 1);
    	    //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
    	    //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
    		
    		if(USE_PROXY)
    			curl_setopt ($ch, CURLOPT_PROXY, PROXY_HOST.":".PROXY_PORT); 
    	
    		//NVPRequest for submitting to server
    		$nvpreq="METHOD=".urlencode($methodName)."&VERSION=".urlencode($version)."&PWD=".urlencode($API_Password)."&USER=".urlencode($API_UserName)."&SIGNATURE=".urlencode($API_Signature).$nvpStr;
    	
    		//setting the nvpreq as POST FIELD to curl
    		curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);
    	
    		//getting response from server
    		$response = curl_exec($ch);
    	
    		//convrting NVPResponse to an Associative Array
    		$nvpResArray=$this->deformatNVP($response);
    		$nvpReqArray=$this->deformatNVP($nvpreq);
    	
    		if (curl_errno($ch))
    			$nvpResArray = $this->APIError(curl_errno($ch),curl_error($ch),$nvpResArray);
    		else 
    			curl_close($ch);
    	
    		return $nvpResArray;
    	}
    	
    	/** This function will take NVPString and convert it to an Associative Array and it will decode the response.
    	  * It is usefull to search for a particular key and displaying arrays.
    	  * @nvpstr is NVPString.
    	  * @nvpArray is Associative Array.
    	  */
    	
    	function deformatNVP($nvpstr)
    	{
    	
    		$intial=0;
    	 	$nvpArray = array();
    	
    	
    		while(strlen($nvpstr)){
    			//postion of Key
    			$keypos= strpos($nvpstr,'=');
    			//position of value
    			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
    	
    			/*getting the Key and Value values and storing in a Associative Array*/
    			$keyval=substr($nvpstr,$intial,$keypos);
    			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
    			//decoding the respose
    			$nvpArray[urldecode($keyval)] =urldecode( $valval);
    			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
    	     }
    		return $nvpArray;
    	}
    }
    ?>

vendors/paypal/constants.php

::

    
    <?php
    /****************************************************
    constants.php
    
    This is the configuration file for the samples.This file
    defines the parameters needed to make an API call.
    ****************************************************/
    
    /**
    # API user: The user that is identified as making the call. you can
    # also use your own API username that you created on PayPalâ€™s sandbox
    # or the PayPal live site
    */
    
    define('API_USERNAME', 'YOUR USERNAME HERE');
    
    /**
    # API_password: The password associated with the API user
    # If you are using your own API username, enter the API password that
    # was generated by PayPal below
    # IMPORTANT - HAVING YOUR API PASSWORD INCLUDED IN THE MANNER IS NOT
    # SECURE, AND ITS ONLY BEING SHOWN THIS WAY FOR TESTING PURPOSES
    */
    
    define('API_PASSWORD', 'YOU PASS HERE');
    
    /**
    # API_Signature:The Signature associated with the API user. which is generated by paypal.
    */
    
    define('API_SIGNATURE', 'YOU SIG HERE');
    
    /**
    # Endpoint: this is the server URL which you have to connect for submitting your API request.
    */
    
    define('API_ENDPOINT', 'https://api-3t.paypal.com/nvp');
    /**
    USE_PROXY: Set this variable to TRUE to route all the API requests through proxy.
    like define('USE_PROXY',TRUE);
    */
    define('USE_PROXY',FALSE);
    /**
    PROXY_HOST: Set the host name or the IP address of proxy server.
    PROXY_PORT: Set proxy port.
    
    PROXY_HOST and PROXY_PORT will be read only if USE_PROXY is set to TRUE
    */
    define('PROXY_HOST', '127.0.0.1');
    define('PROXY_PORT', '808');
    
    /* Define the PayPal URL. This is the URL that the buyer is
       first sent to to authorize payment with their paypal account
       change the URL depending if you are testing on the sandbox
       or going to the live PayPal site
       For the sandbox, the URL is
       https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
       For the live site, the URL is
       https://www.paypal.com/webscr&cmd=_express-checkout&token=
       */
    define('PAYPAL_URL', 'https://www.paypal.com/webscr&cmd=_express-checkout&token=');
    
    /**
    # Version: this is the API version in the request.
    # It is a mandatory parameter for each API request.
    # The only supported value at this time is 2.3
    */
    
    define('VERSION', '3.0');
    
    ?>

components/paypal.php

Component Class:
````````````````

::

    <?php 
    <?php 
    /**
     * Paypal Direct Payment API Component class file.
     */
    App::import('Vendor','paypal' ,array('file'=>'paypal/Paypal.php'));
    class PaypalComponent extends Object{
    	
    	function processPayment($paymentInfo,$function){
    		$paypal = new Paypal();
    		if ($function=="DoDirectPayment")
    			return $paypal->DoDirectPayment($paymentInfo);
    		elseif ($function=="SetExpressCheckout")
    			return $paypal->SetExpressCheckout($paymentInfo);
    		elseif ($function=="GetExpressCheckoutDetails")
    			return $paypal->GetExpressCheckoutDetails($paymentInfo);
    		elseif ($function=="DoExpressCheckoutPayment")
    			return $paypal->DoExpressCheckoutPayment($paymentInfo);
    		else
    			return "Function Does Not Exist!";
    	}
    }
    ?>
    ?>

sample direct payment controller function:

Controller Class:
`````````````````

::

    <?php 
    function processPayment(){
         $paymentInfo = array('Member'=>
                               array(
                                   'first_name'=>'name_here',
                                   'last_name'=>'lastName_here',
                                   'billing_address'=>'address_here',
                                   'billing_address2'=>'address2_here',
                                   'billing_country'=>'country_here',
                                   'billing_city'=>'city_here',
                                   'billing_state'=>'state_here',
                                   'billing_zip'=>'zip_here'
                               ),
                              'CreditCard'=>
                               array(
                                   'card_number'=>'number_here',
                                   'expiration_month'=>'month_here',
                                   'expiration_year'=>'year_here',
                                   'cv_code'=>'code_here'
                               ),
                              'Order'=>
                              array('theTotal'=>1.00)
                        );
    
       /*
        * On Success, $result contains [AMT] [CURRENCYCODE] [AVSCODE] [CVV2MATCH] 
        * [TRANSACTIONID] [TIMESTAMP] [CORRELATIONID] [ACK] [VERSION] [BUILD]
        * 
        * On Fail, $ result contains [AMT] [CURRENCYCODE] [TIMESTAMP] [CORRELATIONID] 
        * [ACK] [VERSION] [BUILD] [L_ERRORCODE0] [L_SHORTMESSAGE0] [L_LONGMESSAGE0] 
        * [L_SEVERITYCODE0] 
        * 
        * Success or Failure is best tested using [ACK].
        * ACK will either be "Success" or "Failure"
        */
     
        $result = $this->Paypal->processPayment($paymentInfo,"DoDirectPayment");
        $ack = strtoupper($result["ACK"]);
    				
        if($ack!="SUCCESS")
            $error = $result['L_LONGMESSAGE0'];
        else{
            /* successful do something here! */
        }
    }
    ?>

Express Checkout Controller Example

Controller Class:
`````````````````

::

    <?php 
    function _get($var) {
        return isset($this->params['url'][$var])? $this->params['url'][$var]: null;
    }
    	
    function expressCheckout($step=1){
        $this->Ssl->force();
        $this->set('step',$step);
        //first get a token
        if ($step==1){
            // set
            $paymentInfo['Order']['theTotal']= .01;
            $paymentInfo['Order']['returnUrl']= "https://fullPathHere/orders/expressCheckout/2/";
            $paymentInfo['Order']['cancelUrl']= "https://fullPathToCancelUrl";
    			
            // call paypal
            $result = $this->Paypal->processPayment($paymentInfo,"SetExpressCheckout");
            $ack = strtoupper($result["ACK"]);
            //Detect Errors
            if($ack!="SUCCESS")
                $error = $result['L_LONGMESSAGE0'];
            else {
                // send user to paypal
                $token = urldecode($result["TOKEN"]);
                $payPalURL = PAYPAL_URL.$token;
                $this->redirect($payPalURL);
            }
        }
        //next have the user confirm
        elseif($step==2){
            //we now have the payer id and token, using the token we should get the shipping address
            //of the payer. Compile all the info into the session then set for the view.
            //Add the order total also
            $result = $this->Paypal->processPayment($this->_get('token'),"GetExpressCheckoutDetails");
            $result['PAYERID'] = $this->_get('PayerID');
            $result['TOKEN'] = $this->_get('token');
            $result['ORDERTOTAL'] = .01;
            $ack = strtoupper($result["ACK"]);
            //Detect errors
            if($ack!="SUCCESS"){
                $error = $result['L_LONGMESSAGE0'];
                $this->set('error',$error);
            }
            else {
                $this->set('result',$this->Session->read('result'));
                $this->Session->write('result',$result);
                /*
                 * Result at this point contains the below fields. This will be the result passed 
                 * in Step 3. I used a session, but I suppose one could just use a hidden field
                 * in the view:[TOKEN] [TIMESTAMP] [CORRELATIONID] [ACK] [VERSION] [BUILD] [EMAIL] [PAYERID]
                 * [PAYERSTATUS]  [FIRSTNAME][LASTNAME] [COUNTRYCODE] [SHIPTONAME] [SHIPTOSTREET]
                 * [SHIPTOCITY] [SHIPTOSTATE] [SHIPTOZIP] [SHIPTOCOUNTRYCODE] [SHIPTOCOUNTRYNAME]
                 * [ADDRESSSTATUS] [ORDERTOTAL]
                 */
            }
        }
        //show the confirmation
        elseif($step==3){
            $result = $this->Paypal->processPayment($this->Session->read('result'),"DoExpressCheckoutPayment");
    	//Detect errors
            $ack = strtoupper($result["ACK"]);
            if($ack!="SUCCESS"){
                $error = $result['L_LONGMESSAGE0'];
                $this->set('error',$error);
            }
            else {
                $this->set('result',$this->Session->read('result'));
            }
        }
    }
    ?>

Express Checkout View: express_checkout.ctp

View Template:
``````````````

::

    
    <?php 
    	if (!isset($error)){
    		if ($step==2){
    			echo $form->create('Order',array('type' => 'post', 'action' => 'expressCheckout/3', 'id' => 'OrderExpressCheckoutConfirmation')); 
    			//all shipping info contained in $result display it here and ask user to confirm.
    			//echo pr($result);
    			echo $form->end('Confirm Payment'); 
    		}
    		if ($step==3){
    			//show confirmation once again all information is contained in $result or $error
    			echo '<h2>Congrats</h2>';
    		}
    	}
    	else
    		echo $error;
    ?>

One thing to note about express checkout is that it takes you away
from your website and goes to paypal. Therefore all your session data
is lost. You can bypass this by setting your security to low in the
core. You may be able to set it on the fly, but I have not tested
that.

I hope that was clear enough...

The controller array section has not been checked for errors, but I am
sure you get the idea!
Thanks/Enjoy :)

.. meta::
    :title: Paypal Payments Component Using cURL
    :description: CakePHP Article related to express,direct,payment,paypal,component,curl,direct payment,processing,card,credit,express payment,recurring payment,Components
    :keywords: express,direct,payment,paypal,component,curl,direct payment,processing,card,credit,express payment,recurring payment,Components
    :copyright: Copyright 2009 
    :category: components

