

reCAPTCHA Component & Helper for CakePHP
========================================

by %s on November 15, 2008

an easy-to-use component and helper set to use reCAPTCHA on your
website.
This is an easy to use component and helper set for you to get
reCAPTCHA(including mailhide) running on your website now. If you
don't know what reCAPTCHA is please head to `http://recaptcha.net/`_
now to find out! Please note this code is largely derived off of the
library provided for reCAPTCHA - it's just repurposed for ease of use
in CakePHP.

Create the following files:

::

    app/controllers/components/recaptcha.php 
    app/views/helpers/recaptcha.php

code for app/controllers/components/recaptcha.php

Component Class:
````````````````

::

    <?php 
     class RecaptchaComponent extends Object {
    	var $publickey = "";
    	var $privatekey= "";
    	
    	var $is_valid = false;
    	var $error = "";
    	
    	function startup(&$controller){
    		Configure::write("Recaptcha.apiServer","http://api.recaptcha.net");
    		Configure::write("Recaptcha.apiSecureServer","https://api-secure.recaptcha.net");
    		Configure::write("Recaptcha.verifyServer","api-verify.recaptcha.net");
    		Configure::write("Recaptcha.pubKey", $this->publickey);
    		Configure::write("Recaptcha.privateKey", $this->privatekey);
    		
    		$this->controller =& $controller;
    		$this->controller->helpers[] = "Recaptcha";
    	}
    	
    	function valid($form){
            if (isset($form['recaptcha_challenge_field']) && isset($form['recaptcha_response_field'])){
            	if($this->recaptcha_check_answer(
                    $this->privatekey, 
                    $_SERVER["REMOTE_ADDR"],
                    $form['recaptcha_challenge_field'], 
                    $form['recaptcha_response_field']
                ) == 0)
                	return false;
    
                if ($this->is_valid)
                    return true;
            }
            return false;
        }
        
    	/**
    	  * Calls an HTTP POST function to verify if the user's guess was correct
    	  * @param string $privkey
    	  * @param string $remoteip
    	  * @param string $challenge
    	  * @param string $response
    	  * @param array $extra_params an array of extra variables to post to the server
    	  * @return ReCaptchaResponse
    	  */
    	function recaptcha_check_answer ($privkey, $remoteip, $challenge, $response, $extra_params = array()){
    		if ($privkey == null || $privkey == ''){
    			die ("To use reCAPTCHA you must get an API key from <a href='http://recaptcha.net/api/getkey'>http://recaptcha.net/api/getkey</a>");
    		}
    	
    		if ($remoteip == null || $remoteip == ''){
    			die ("For security reasons, you must pass the remote ip to reCAPTCHA");
    		}		
    			
    	        //discard spam submissions
    	        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
    	                $this->is_valid = false;
    	                $this->error = 'incorrect-captcha-sol';
    	                return 0;
    	        }
    
    	        $response = $this->_recaptcha_http_post(Configure::read('Recaptcha.verifyServer'), "/verify",
    	                                          array (
    	                                                 'privatekey' => $privkey,
    	                                                 'remoteip' => $remoteip,
    	                                                 'challenge' => $challenge,
    	                                                 'response' => $response
    	                                                 ) + $extra_params
    	                                          );
    	
    	        $answers = explode ("\n", $response [1]);
    	        
    	        if (trim ($answers [0]) == 'true') {
    	                $this->is_valid = true;
    	                return 1;
    	        }else{
    	                $this->is_valid = false;
    	                $this->error = $answers [1];
    	                return 0;
    	        }
    	}
    	
    	
    	/**
    	 * Submits an HTTP POST to a reCAPTCHA server
    	 * @param string $host
    	 * @param string $path
    	 * @param array $data
    	 * @param int port
    	 * @return array response
    	 */
    	function _recaptcha_http_post($host, $path, $data, $port = 80) {
    
            $req = $this->_recaptcha_qsencode ($data);
    
            $http_request  = "POST $path HTTP/1.0\r\n";
            $http_request .= "Host: $host\r\n";
            $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
            $http_request .= "Content-Length: " . strlen($req) . "\r\n";
            $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
            $http_request .= "\r\n";
            $http_request .= $req;
    
            $response = '';
            if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
                    die ('Could not open socket');
            }
    
            fwrite($fs, $http_request);
    
            while ( !feof($fs) )
                    $response .= fgets($fs, 1160); // One TCP-IP packet
            fclose($fs);
            $response = explode("\r\n\r\n", $response, 2);
    
            return $response;
    	}
    	
    	
    	/**
    	 * Encodes the given data into a query string format
    	 * @param $data - array of string elements to be encoded
    	 * @return string - encoded request
    	 */
    	function _recaptcha_qsencode ($data) {
            $req = "";
            foreach ( $data as $key => $value )
                    $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';
    
            // Cut the last '&'
            $req=substr($req,0,strlen($req)-1);
            return $req;
    	}
    }
    ?>



code for app/views/helpers/recaptcha.php

Helper Class:
`````````````

::

    <?php 
    class RecaptchaHelper extends AppHelper {
    	var $helpers = array('form'); 
    	
    	function display_form($output_method = 'return', $error = null, $use_ssl = false){
    		$data = $this->__form(Configure::read("Recaptcha.pubKey"),$error,$use_ssl);
    		if($output_method == "echo")
    			echo $data;
    		else
    			return $data;
    	}
    	
    	function hide_mail($email = '',$output_method = 'return'){
    		$data = $this->recaptcha_mailhide_html(Configure::read('Recaptcha.pubKey'), Configure::read('Recaptcha.privateKey'), $email);
    		if($output_method == "echo")
    			echo $data;
    		else
    			return $data;
    	}
    	
    	/**
    	 * Gets the challenge HTML (javascript and non-javascript version).
    	 * This is called from the browser, and the resulting reCAPTCHA HTML widget
    	 * is embedded within the HTML form it was called from.
    	 * @param string $pubkey A public key for reCAPTCHA
    	 * @param string $error The error given by reCAPTCHA (optional, default is null)
    	 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)
    	
    	 * @return string - The HTML to be embedded in the user's form.
    	 */
    	function __form($pubkey, $error = null, $use_ssl = false){
    		if ($pubkey == null || $pubkey == '') {
    			die ("To use reCAPTCHA you must get an API key from <a href='http://recaptcha.net/api/getkey'>http://recaptcha.net/api/getkey</a>");
    		}
    		
    		if ($use_ssl) {
    	                $server = Configure::read('Recaptcha.apiSecureServer');
    	        } else {
    	                $server = Configure::read('Recaptcha.apiServer');
    	        }
    	
    	        $errorpart = "";
    	        if ($error) {
    	           $errorpart = "&error=" . $error;
    	        }
    	        return '<script type="text/javascript" src="'. $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>
    	
    		<noscript>
    	  		<iframe src="'. $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
    	  			<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
    				<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
    	  		<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
    		</noscript>';
    	}
    
    	/* Mailhide related code */
    	function _recaptcha_aes_encrypt($val,$ky) {
    		if (! function_exists ("mcrypt_encrypt")) {
    			die ("To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.");
    		}
    		$mode=MCRYPT_MODE_CBC;   
    		$enc=MCRYPT_RIJNDAEL_128;
    		$val=$this->_recaptcha_aes_pad($val);
    		return mcrypt_encrypt($enc, $ky, $val, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
    	}
    	
    	function _recaptcha_mailhide_urlbase64 ($x) {
    		return strtr(base64_encode ($x), '+/', '-_');
    	}
    	
    	/* gets the reCAPTCHA Mailhide url for a given email, public key and private key */
    	function recaptcha_mailhide_url($pubkey, $privkey, $email) {
    		if ($pubkey == '' || $pubkey == null || $privkey == "" || $privkey == null) {
    			die ("To use reCAPTCHA Mailhide, you have to sign up for a public and private key, " .
    			     "you can do so at <a href='http://mailhide.recaptcha.net/apikey'>http://mailhide.recaptcha.net/apikey</a>");
    		}
    		
    	
    		$ky = pack('H*', $privkey);
    		$cryptmail = $this->_recaptcha_aes_encrypt ($email, $ky);
    		
    		return "http://mailhide.recaptcha.net/d?k=" . $pubkey . "&c=" . $this->_recaptcha_mailhide_urlbase64 ($cryptmail);
    	}
    	
    	/**
    	 * gets the parts of the email to expose to the user.
    	 * eg, given johndoe@example,com return ["john", "example.com"].
    	 * the email is then displayed as john...@example.com
    	 */
    	function _recaptcha_mailhide_email_parts ($email) {
    		$arr = preg_split("/@/", $email );
    	
    		if (strlen ($arr[0]) <= 4) {
    			$arr[0] = substr ($arr[0], 0, 1);
    		} else if (strlen ($arr[0]) <= 6) {
    			$arr[0] = substr ($arr[0], 0, 3);
    		} else {
    			$arr[0] = substr ($arr[0], 0, 4);
    		}
    		return $arr;
    	}
    	
    	/**
    	 * Gets html to display an email address given a public an private key.
    	 * to get a key, go to:
    	 *
    	 * http://mailhide.recaptcha.net/apikey
    	 */
    	function recaptcha_mailhide_html($pubkey, $privkey, $email) {
    		$emailparts = $this->_recaptcha_mailhide_email_parts ($email);
    		$url = $this->recaptcha_mailhide_url ($pubkey, $privkey, $email);
    		
    		return htmlentities($emailparts[0]) . "<a href='" . htmlentities ($url) .
    			"' onclick=\"window.open('" . htmlentities ($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities ($emailparts [1]);
    	
    	}
    		
    
    }
    ?>

To use the reCAPTCHA system load the component

Controller Class:
`````````````````

::

    <?php 
    var $components = array('Recaptcha'); 
    ?>

After you save the component and helper and initiate them, set your
public & private keys in "beforeFilter" of your controller to the ones
you received when you signed up on the reCAPTCHA website.


Controller Class:
`````````````````

::

    <?php 
    function beforeFilter(){
       $this->Recaptcha->publickey = "";
       $this->Recaptcha->privatekey = "";
    }
    ?>

in the view, the helper can be used to verify form submissions or hide
your e-mail addresses (NOTE: mcrypt is required for this.)

Controller Class:
`````````````````

::

    <?php 
    //create the reCAPTCHA form.
     $recaptcha->display_form('echo')
    
    //hide an e-mail address
     $recaptcha->hide_mail("someuser@somdomain.tld",'echo');
    ?>


in the controller to verify a form submission using reCAPTCHA do the
following.

Controller Class:
`````````````````

::

    <?php 
    if($this->Recaptcha->valid($this->params['form']))
      //submission is valid!
    else
      //invalid reCAPTCHA entry.
    ?>

I hope you enjoy it, this is the first component / helper (let alone
helper) pair I've written.

.. _http://recaptcha.net/: http://recaptcha.net/
.. meta::
    :title: reCAPTCHA Component & Helper for CakePHP
    :description: CakePHP Article related to component,captcha,recaptcha,hide email,Components
    :keywords: component,captcha,recaptcha,hide email,Components
    :copyright: Copyright 2008 
    :category: components

