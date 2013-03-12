

SwiftMailer Component
=====================

by %s on December 18, 2006

This is a component that does encapsulate but not hide swift mailer.
In addition, it adds some nifty features. In short, this is just a
delicious cake-ish swift Mailer.


Component Class:
````````````````

::

    <?php 
    /*
     * SwiftMailer Component By othman ouahbi.
     * comments, bug reports are welcome crazylegs AT gmail DOT com
     * @author othman ouahbi aka CraZyLeGs
     * @version 0.1 
     * @license MIT
     */
    
    class SwiftMailerComponent extends Object {
    	var $controller = false;
    	var $mailer = null;
    	var $connection = 'smtp'; // sendmail, native
    
    	var $smtp_host = null; // null auto detect
    	var $smtp_port = false; // false to let the mailer choose. default 25, 465 for ssl.
    	var $smtp_type = 'open'; // open, ssl, tls
    
    	var $username = null;
    	var $password = null;
    
    	var $layout = 'swift_email';
    
    	var $email_views_dir = 'swift_emails';
    
    	var $sendmail_cmd = false; // false: SWIFT_AUTO_DETECT, 'default': '/usr/sbin/sendmail -bs' etc..
    
    	function startup(& $controller) {
    		$this->controller = & $controller;
    	}
    
    	function connect() {
    		switch ($this->connection) {
    			case 'smtp' :
    				$this->connect_smtp();
    				break;
    			case 'sendmail' :
    				$this->connect_sendmail();
    				break;
    			case 'native' :
    			default :
    				$this->connect_native();
    				break;
    		}
    
    		return $this->mailer->isConnected();
    	}
    
    	function connect_native() {
    		vendor('Swift');
    		vendor('Swift/Connection/NativeMail');
    
    		$this->mailer = new Swift(new Swift_Connection_NativeMail());
    	}
    
    	function connect_sendmail() {
    		vendor('Swift');
    		vendor('Swift/Connection/SendMail');
    
    		if ($this->sendmail_cmd == false) {
    			$this->sendmail_cmd = SWIFT_AUTO_DETECT;
    		}
    		elseif ($this->sendmail_cmd == 'default') {
    			$this->sendmail_cmd = '/usr/sbin/sendmail -bs';
    		}
    
    		$this->mailer = new Swift(new Swift_Connection_SendMail($this->sendmail_cmd));
    	}
    
    	function connect_smtp() {
    		vendor('Swift');
    		vendor('Swift/Connection/SMTP');
    
    		// SWIFT_AUTO_DETECT
    		if (is_null($this->smtp_host)) {
    			$this->smtp_host = SWIFT_AUTO_DETECT;
    		}
    		
    		if (is_null($this->smtp_port)) {
    			$this->smtp_port = SWIFT_AUTO_DETECT;
    		}
    		
    		$ssl_types = array('open'=>SWIFT_OPEN,'ssl'=>SWIFT_SSL,'tls'=>SWIFT_TLS);
    		
    		if(in_array($this->smtp_type, array ('open','ssl','tls')))
    		{
    			$ssl_type = $ssl_types[$this->smtp_type];
    		}else
    		{
    			$ssl_type = $ssl_types['open'];
    		}
    
    		$this->mailer = new Swift(new Swift_Connection_SMTP($this->smtp_host, $this->smtp_port, $ssl_type));
    
    	}
    
    	function auth() {
    		return ($this->mailer->authenticate($this->username, $this->password));
    	}
    
    	function errors() {
    		return $this->mailer->errors;
    	}
    
    	function transactions() {
    		return $this->mailer->transactions;
    	}
    
    	function close() {
    		$this->mailer->close();
    	}
    
    	/*
    	 * description: 
    	 * Renders a body view located in the emails dir.
    	 * if html, wraps it with a layout and embeds images that have the embed="swift" attribute
    	 * strip tags if plain.
    	 */
    	function viewBody($name, $type = 'both', $return = false) {
    		switch ($type) {
    			case 'both' :
    				$plain = true;
    				$html = true;
    				break;
    			case 'html' :
    				$html = true;
    				break;
    			case 'plain' :
    				$plain = true;
    				break;
    			default :
    				return;
    				break;
    		}
    
    		if (isset ($html)) {
    			$name .= "_html";
    			$view = VIEWS . $this->email_views_dir . DS . $name . '.thtml';
    			$old_layout = $this->controller->layout;
    			ob_start();
    			$this->controller->render(null, $this->layout, $view);
    			$html_msg = ob_get_clean();
    			$html_msg = $this->replaceIMG($html_msg);
    			$this->controller->layout = $old_layout;
    		}
    
    		if (isset ($plain)) {
    			$view = VIEWS . $this->email_views_dir . DS . $name . '.thtml';
    			$old_layout = $this->controller->layout;
    			$this->controller->layout = '';
    			ob_start();
    			$this->controller->render(null, null, $view);
    			$plain_msg = strip_tags(ob_get_clean());
    			$this->controller->layout = $old_layout;
    		}
    
    		switch ($type) {
    			case 'both' :
    				if ($return) {
    					return array (
    						$plain_msg,
    						$html_msg
    					);
    				}
    				$this->mailer->addPart($html_msg, 'text/html');
    				$this->mailer->addPart($plain_msg, 'text/plain');
    				break;
    			case 'html' :
    				if ($return) {
    					return $html_msg;
    				}
    				$this->mailer->addPart($html_msg, 'text/html');
    				break;
    			case 'plain' :
    				if ($return) {
    					return $plain_msg;
    				}
    				$this->mailer->addPart($plain_msg, 'text/plain');
    				break;
    		}
    
    	}
    
    	function replaceIMG($msg) {
    		$matches = array ();
    		$files = array ();
    		if (preg_match_all('#<img.*src=\"(.*?)\".*?\/>#', $msg, $matches)) {
    			for ($i = 0; $i < count($matches[0]); $i++) {
    				$pos = strpos($matches[0][$i], 'embed="swift"');
    				if ($pos !== false) {
    					$file = substr($matches[1][$i], strrpos($matches[1][$i], '/') + 1);
    					if (array_key_exists($file, $files)) {
    						$replace = $files[$file];
    					} else {
    						$replace = $this->mailer->addImage(WWW_ROOT . 'img' . DS . $file);
    						$files[$file] = $replace;
    					}
    
    					$msg = str_replace($matches[1][$i], $replace, $msg);
    				}
    			}
    		}
    		return $msg;
    
    	}
    
    	/*
    	 * description: 
    	 * Wraps the body with a layout, strips tags if not html
    	 */
    	function wrapBody($msg, $type = 'plain', $return = false) {
    		$view = VIEWS . $this->email_views_dir . DS . 'default.thtml';
    
    		$this->controller->set('swiftMailer_data', $msg);
    
    		ob_start();
    		$this->controller->render(null, $this->layout, $view);
    		$msg = ob_get_clean();
    
    		if ($type != 'html') {
    			$msg = strip_tags($msg);
    		}
    
    		if ($return) {
    			return $msg;
    		}
    
    		$this->mailer->addPart($msg, ($type == 'html') ? 'text/html' : 'text/plain');
    	}
    
    	// original idea Tommy0	
    	function addTo($type, $address, $name = false) {
    		if (in_array($type, array (
    				'to',
    				'from',
    				'cc',
    				'bcc'
    			))) {
    			if (!$name) {
    				$val = $address;
    			} else {
    				$val = array (
    					$name,
    					$address
    				);
    			}
    
    			if ($type == 'from') {
    				$this-> $type = $val;
    			} else {
    				if (!isset ($this-> $type)) {
    					$this-> $type = array ();
    				}
    				array_push($this-> $type, $val);
    			}
    		}
    	}
    
    	// original idea Tommy0
    	function send($subject) {
    		if (!empty ($this->cc)) {
    			$this->mailer->addCc($this->cc);
    		}
    		if (!empty ($this->bcc)) {
    			$this->mailer->addBcc($this->bcc);
    		}
    		if (is_array($this->from)) {
    			$from = '"' . $this->from[0] . '" <' . $this->from[1] . '>';
    		} else {
    			$from = $this->from;
    		}
    		if (!empty ($this->username) && !$this->auth()) {
    			return false;
    		}
    		if ($this->mailer->send($this->to, $from, $subject)) {
    			$this->mailer->close();
    			return true;
    		}
    		return false;
    	}
    
    	function sendWrap($subject, $body, $type = 'plain') {
    		$this->wrapBody($body, $type);
    
    		return $this->send($subject);
    	}
    
    	function sendView($subject, $view, $type = 'plain') {
    		$this->viewBody($view, $type);
    
    		return $this->send($subject);
    	}
    
    }
    ?>


.. meta::
    :title: SwiftMailer Component
    :description: CakePHP Article related to Mail,email,component,native mail,sendmail,smtp,mailer,send,swift mailer,Components
    :keywords: Mail,email,component,native mail,sendmail,smtp,mailer,send,swift mailer,Components
    :copyright: Copyright 2006 
    :category: components

