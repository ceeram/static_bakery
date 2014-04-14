dAuth v0.3 views controller and javascript
==========================================

by Dieter_be on November 15, 2006

the login, register, userinfo and change_password views; the
usersController, and d_auth, sha1 javascripts for dAuth v0.3
Changes since v0.2

#. added the userinfo element, which a good starting point if you want
   to start using the new feature of v0.3 (having the userdata available
   in the view)
#. now using cake's requesthandler to get the clients IP-adress
#. Gave javascript functions better names, like stage1Hash and
   stage2Hash
#. fixed some typos
#. added copyright notices
#. probably did some more but i forgot



views/users/login.thtml
```````````````````````

View Template:
``````````````

::

    
    <?php
    /*
     * PHP versions 4 and 5
     *
     * dAuth: A secure authentication system for the cakePHP framework.
     * Copyright (c)	2006, Dieter Plaetinck
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author			Dieter Plaetinck
     * @copyright		Copyright (c) 2006, Dieter Plaetinck
     * @version			0.3
     * @modifiedby		Dieter@be
     * @lastmodified	$Date: 2006-12-04 16:18:00 +0000 (Mon, 4 Dec 2006) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    $action = 'Login';
    $formAction = $html->url('/users/login');
    echo $dAuth->loadJs();
    echo $dAuth->ErrorMsg($action,$error);
    echo $dAuth->formHeader($action,$formAction,$allowcleartext);
    ?>
    
    	<div class="panel">
            <?php
            	echo $dAuth->formInput('Username','User/username');
            	echo $dAuth->formPassword('Password','User/password');
            	echo $dAuth->hiddenField('hashed_pw','User/hashed_pw','');
            	echo $dAuth->hiddenField('special_sauce','User/special_sauce',$special_sauce);
            	echo $dAuth->submit($action,true);
            ?>
        </div>
    </form>
    
    <?php
    if($error)
    {
    	echo $dAuth->emptyField('username');
    	echo $dAuth->emptyField('password');
    }
    ?>


views/users/register.thtml
``````````````````````````

View Template:
``````````````

::

    
    <?php
    /*
     * PHP versions 4 and 5
     *
     * dAuth: A secure authentication system for the cakePHP framework.
     * Copyright (c)	2006, Dieter Plaetinck
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author			Dieter Plaetinck
     * @copyright		Copyright (c) 2006, Dieter Plaetinck
     * @version			0.3
     * @modifiedby		Dieter@be
     * @lastmodified	$Date: 2006-12-04 16:18:00 +0000 (Mon, 4 Dec 2006) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    $action = 'Register';
    $formAction = $html->url('/users/register');
    echo $dAuth->loadJs();
    echo $dAuth->ErrorMsg($action,$error);
    echo $dAuth->formHeader($action,$formAction,$allowcleartext);
    ?>
    
    	<div class="panel">
            <?php
            	echo $dAuth->formInput('Username','User/username','Please enter a valid username!  No funny characters.');
            	echo $dAuth->formPassword('Password','User/password','Please enter a valid password!');
            	echo $dAuth->hiddenField('hashed_pw','User/hashed_pw','');
            	echo $dAuth->submit($action,false);
            ?>
        </div>
    </form>
    
    <?php
    if($error)
    {
    	echo $dAuth->emptyField('username');
    	echo $dAuth->emptyField('password');
    }
    ?>


views/elements/userinfo.thtml
`````````````````````````````

View Template:
``````````````

::

    
    <?php
    /*
     * PHP versions 4 and 5
     *
     * dAuth: A secure authentication system for the cakePHP framework.
     * Copyright (c)	2006, Dieter Plaetinck
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author			Dieter Plaetinck
     * @copyright		Copyright (c) 2006, Dieter Plaetinck
     * @version			0.3
     * @modifiedby		Dieter@be
     * @lastmodified	$Date: 2006-12-04 16:18:00 +0000 (Mon, 4 Dec 2006) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    
    /* Keep in mind this will only work if you use $userDataInView in the component */
    if($User)
    {
    	echo ('Logged in');
    	if(isset($User['username']))
    	{
    		echo(' as '.$User['username']);
    	}
    }
    else
    {
    	echo 'not logged in';
    }
    ?>


views/users/change_password.thtml
`````````````````````````````````

View Template:
``````````````

::

    
    <?php
    /*
     * PHP versions 4 and 5
     *
     * dAuth: A secure authentication system for the cakePHP framework.
     * Copyright (c)	2006, Dieter Plaetinck
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author			Dieter Plaetinck
     * @copyright		Copyright (c) 2006, Dieter Plaetinck
     * @version			0.3
     * @modifiedby		Dieter@be
     * @lastmodified	$Date: 2006-12-04 16:18:00 +0000 (Mon, 4 Dec 2006) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    $action = 'Change Password';
    $formAction = $html->url('/users/changePassword');
    echo $dAuth->loadJs();
    echo $dAuth->ErrorMsg($action,$error);
    echo $dAuth->formHeader($action,$formAction,$allowcleartext);
    ?>
    
    	<div class="panel">
            <?php
            	echo $dAuth->formPassword('Password','User/password');
            	echo $dAuth->hiddenField('hashed_pw','User/hashed_pw','');
            	echo $dAuth->submit($action,false);
            ?>
        </div>
    </form>
    
    <?php
    if($error)
    {
    	echo $dAuth->emptyField('password');
    }
    ?>


controllers/users_controller.php
````````````````````````````````

Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController
    {
        var $name = 'Users';
        var $uses = array('User','Host','LoginAttempt');
        var $helpers = array('Javascript','DAuth');
        var $components = array('DAuth','RequestHandler');
        var $noReason = 'No reason given.';
    
    	function login()
    	{
    		$error = '';
    		if (!empty($this->data))
    		{
         		$success = $this->DAuth->attemptLogin($this->params['data'],$this->RequestHandler->getClientIP());
    	        if($success)
            	{
            		$this->DAuth->redirect('login_success');
            		return true;
            	}
            	else
            	{
            		$error = $this->DAuth->getErrorMessage();
            		if(!$error)
            		{
            			$error = $this->noReason;
            		}
            	}
             }
    		$this->DAuth->newSalt();
          	$this->set('error', $error);
          	$this->set('allowcleartext', $this->DAuth->allowClearText);
        }
    
    	function register()
    	{
    		$error = '';
    		if (!empty($this->data))
    		{
         		$success = $this->DAuth->attemptRegister($this->params['data'],$this->RequestHandler->getClientIP());
    	        if($success)
            	{
            		$this->DAuth->redirect('register_success');
            		return true;
            	}
            	else
            	{
            		$error = $this->DAuth->getErrorMessage();
            		if(!$error)
            		{
            			$error = $this->noReason;
            		}
            	}
             }
    		$this->set('error',$error);
    		$this->set('allowcleartext', $this->DAuth->allowClearText);
    	}
    
    	function changePassword()
    	{
    		$error = '';
    		if (!empty($this->data))
    		{
         		$success = $this->DAuth->attemptChangePassword($this->params['data'],$this->RequestHandler->getClientIP());
    	        if($success)
            	{
            		$this->DAuth->redirect('change_password_success');
            		return true;
            	}
            	else
            	{
            		$error = $this->DAuth->getErrorMessage();
            		if(!$error)
            		{
            			$error = $this->noReason;
            		}
            	}
             }
    		$this->set('error',$error);
    		$this->set('allowcleartext', $this->DAuth->allowCleartext);
    	}
    
    	function logout()
    	{
    		$success = $this->DAuth->attemptLogout();
    		if($success)
    		{
    			$this->Session->setFlash('Logout successfull');
    			$this->DAuth->redirect('logout_success');
    			return true;
    		}
    		else
    		{
    			$this->Session->setFlash('Logout failed');
    			$this->DAuth->redirect('logout_failure');
    			return true;
    		}
    	}
    }
    ?>



webroot/js/d_auth.js
````````````````````

::

    
    /*
     * dAuth: A secure authentication system for the cakePHP framework.
     * Copyright (c)	2006, Dieter Plaetinck
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author			Dieter Plaetinck
     * @copyright		Copyright (c) 2006, Dieter Plaetinck
     * @version			0.3
     * @modifiedby		Dieter@be
     * @lastmodified	$Date: 2006-12-04 16:18:00 +0000 (Mon, 4 Dec 2006) $
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    
    
    	/*
    	 * The algorithm (constant over time) that will be used to securely store passwords in the database.
    	 * If you change this, you have to change the stage1Hash component function too.
    	 */
    
    	function stage1Hash(cleartext)
    	{
    		return sha1Hash(cleartext+cleartext.charAt(0));
    	}
    
    	/*
    	 * The algorithm (changing over time) that will be used to securely transport passwords over the network.
    	 * If you change this, you have to change the stage2Hash component function too.
    	 */
    	function stage2Hash(stage1,salt)
    	{
    		return sha1Hash(stage1+salt);
    	}
    
    	function doStage2()
    	{
    		var password = document.getElementById('password').value;
      		var salt = document.getElementById('special_sauce').value;
    		var hash = stage2Hash(stage1Hash(password),salt);
    		var fake_pass = randomString(password.length);
    		document.getElementById('hashed_pw').value = hash;
    		document.getElementById('password').value = fake_pass;
    	}
    	function doStage1()
    	{
    		var password = document.getElementById('password').value;
    		var hash = stage1Hash(password);
    		var fake_pass = randomString(password.length);
    		document.getElementById('hashed_pw').value = hash;
    		document.getElementById('password').value = fake_pass;
    	}
    
    	function randomString(len)
    	{
    		var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
    		var randomstring = '';
    		for (var i=0; i<len; i++)
    		{
    			var rnum = Math.floor(Math.random() * chars.length);
    			randomstring += chars.substring(rnum,rnum+1);
    		}
    		return randomstring;
    	}
    
    	function emptyField(fieldId)
    	{
            document.getElementById(fieldId).value = "";
    	}
    
    	function removeError(errorId)
    	{
    		document.getElementById(errorId).innerHTML = "";
    	}
    
    	function fixForm(formId, action)
    	{
    		var form = document.getElementById(formId);
    		form.action = action;
    		form.method = 'post';
    		form.style.display = "block";
    	}


webroot/js/sha1.js
``````````````````
I didn't write this code. I don't know where it comes from, but the
original comments/copyright is still in the code if you want to find
the guy who wrote it ;)

PS: get this code by using the link below, don't copy paste from this
page because the bakery bbcode parser does weird things with the code.

::

    
    // ??? 2002-2005 Chris Veness
    
    function sha1Hash(msg)
    {
        // constants [4.2.1]
        var K = [0x5a827999, 0x6ed9eba1, 0x8f1bbcdc, 0xca62c1d6];
    
        // PREPROCESSING
    
        msg += String.fromCharCode(0x80); // add trailing '1' bit to string [5.1.1]
    
        // convert string msg into 512-bit/16-integer blocks arrays of ints [5.2.1]
        var l = Math.ceil(msg.length/4) + 2;  // long enough to contain msg plus 2-word length
        var N = Math.ceil(l/16);              // in N 16-int blocks
        var M = new Array(N);
        for (var i=0; i<N; i++) {
            M[i] = new Array(16);
            for (var j=0; j<16; j++) {  // encode 4 chars per integer, big-endian encoding
                M[i][j] = (msg.charCodeAt(i*64+j*4)<<24) | (msg.charCodeAt(i*64+j*4+1)<<16) |
                          (msg.charCodeAt(i*64+j*4+2)<<8) | (msg.charCodeAt(i*64+j*4+3));
            }
        }
        // add length (in bits) into final pair of 32-bit integers (big-endian) [5.1.1]
        M[N-1][14] = ((msg.length-1) >>> 30) * 8;
        M[N-1][15] = ((msg.length-1)*8) & 0xffffffff;
    
        // set initial hash value [5.3.1]
        var H0 = 0x67452301;
        var H1 = 0xefcdab89;
        var H2 = 0x98badcfe;
        var H3 = 0x10325476;
        var H4 = 0xc3d2e1f0;
    
        // HASH COMPUTATION [6.1.2]
    
        var W = new Array(80); var a, b, c, d, e;
        for (var i=0; i<N; i++) {
    
            // 1 - prepare message schedule 'W'
            for (var t=0;  t<16; t++) W[t] = M[i][t];
            for (var t=16; t<80; t++) W[t] = ROTL(W[t-3] ^ W[t-8] ^ W[t-14] ^ W[t-16], 1);
    
            // 2 - initialise five working variables a, b, c, d, e with previous hash value
            a = H0; b = H1; c = H2; d = H3; e = H4;
    
            // 3 - main loop
            for (var t=0; t<80; t++) {
                var s = Math.floor(t/20); // seq for blocks of 'f' functions and 'K' constants
                var T = (ROTL(a,5) + f(s,b,c,d) + e + K[s] + W[t]) & 0xffffffff;
                e = d;
                d = c;
                c = ROTL(b, 30);
                b = a;
                a = T;
            }
    
            // 4 - compute the new intermediate hash value
            H0 = (H0+a) & 0xffffffff;  // note 'addition modulo 2^32'
            H1 = (H1+b) & 0xffffffff;
            H2 = (H2+c) & 0xffffffff;
            H3 = (H3+d) & 0xffffffff;
            H4 = (H4+e) & 0xffffffff;
        }
    
        return H0.toHexStr() + H1.toHexStr() + H2.toHexStr() + H3.toHexStr() + H4.toHexStr();
    }
    
    //
    // function 'f' [4.1.1]
    //
    function f(s, x, y, z)
    {
        switch (s) {
        case 0: return (x & y) ^ (~x & z);
        case 1: return x ^ y ^ z;
        case 2: return (x & y) ^ (x & z) ^ (y & z);
        case 3: return x ^ y ^ z;
        }
    }
    
    //
    // rotate left (circular left shift) value x by n positions [3.2.5]
    //
    function ROTL(x, n)
    {
        return (x<<n) | (x>>>(32-n));
    }
    
    //
    // extend Number class with a tailored hex-string method
    //   (note toString(16) is implementation-dependant, and
    //   in IE returns signed numbers when used on full words)
    //
    Number.prototype.toHexStr = function()
    {
        var s="", v;
        for (var i=7; i>=0; i--) { v = (this>>>(i*4)) & 0xf; s += v.toString(16); }
        return s;
    }

more info about dAuth @ `http://bakery.cakephp.org/articles/view/147`_

.. _http://bakery.cakephp.org/articles/view/147: http://bakery.cakephp.org/articles/view/147
.. meta::
    :title: dAuth v0.3 views controller and javascript
    :description: CakePHP Article related to authentication,Snippets
    :keywords: authentication,Snippets
    :copyright: Copyright 2006 Dieter_be
    :category: snippets

