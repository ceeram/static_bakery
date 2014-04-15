Challengeresponse authentication with fallback
==============================================

by Dieter_be on November 01, 2006

This tutorial shows you how to create a simple authentication system
that doesn't send passwords in cleartext (as many others do). It has a
fallback for non-JavaScript users (cleartext mode), and uses a user-
defineable method for improved security for the storage of passwords.
Note: I recommend that you read a bit about basic encryption stuff
before proceeding. It's advisable that you know what a salt is, what
encryption functions (like crc, md5, sha1,..) are, and other stuff
like bruteforce.


What makes an authentication system secure?
-------------------------------------------
To have a secure authentication system, these things are needed:

#. encrypted passwords in the database For preparing yourself for the
   scenario where your database gets compromised, or if you are in a
   shared hosting environment (where other people, like administrators
   and personnel have access to the database) this is a must. Keep in
   mind that md5 isn't very secure anymore. It isn't possible to
   "decrypt" md5 hashes, but with current techniques (rainbow tables,
   library-based attacks and others. Even by using plain bruteforce) it
   is possible to find the original string. Some authentication systems
   (like the original challenge-response one from Jeff) use plaintext
   storage of passwords in the database, most use md5 hashes, and only a
   very small remainder uses another algorithm. I leave the choice to the
   developer (you) for defining your own method (see below). Sha1 would
   be a better choice then md5, and if you are really paranoid, you can
   apply any combination of md5, sha- or crc-functions and salts in
   between, just for securely storing passwords. For now, I've used plain
   sha1, which will be fine for most of us.
#. well-written code & good environment. Obviously the code must be
   secure and hopefully thoroughly tested. By the environment I mean PHP
   settings, for example. Other servers could impose other settings (like
   enabling register_globals and other unwanted options). This is the
   hardest part of judging an authentication system.
#. escaping data where necessary to prevent SQL injections and similar
   hacks, all special characters in incoming data from the user must be
   escaped. Luckily, with Cake's help this is a no-brainer :-) don't
   forget to prevent people from using funny characters in their
   usernames/passwords if you let them register as new user with a html
   form. (cake has VALIDATE for that)
#. requesting transmission of encrypted passwords No matter how
   complex your serverside authentication system is, without further
   action, all POST variables are sent in cleartext over the network when
   using HTTP!! Here's where the challenge-response system comes into
   play. The key is to not send the password, but a derivative of it (a
   hash), so when people sniff the network they never get to know your
   real password. But also, if network sniffers would capture your
   "derivative" (hash) it shouldn't be usable for them. That's why the
   method of deriving the hash should be unique to each try, otherwise
   bad guys could just use your hash to login. One method of achieving
   this all is by using HTTPS, which is http but with an SSL layer over
   it. This is also the best method, because everything is encrypted, in
   a pretty secure manner. However, SSL isn't always available at some
   hosts. The alternative is using JavaScript to do some clientside
   calculations and transmit the hashes. People that don't have support
   for JavaScript. (which is very uncommon these days, but still...) will
   transmit their passwords in cleartext, nothing you can do about it.
   They are the ones to blame if their account gets compromised ;)
#. prevention of session hijacking The standard php implementation of
   sessions leaves room for hijacking of sessions. This is something we
   should be prepared against, by identifying users with additional means
   (like user agent for example) and storing these means in the session.
#. protection against bruteforce attempts You can secure the
   transmission and storage of passwords as tight as you want, and
   protect your sessions as much as you can , when scripts try every
   possible combination of username and password to find matching
   combinations, they will get what they want eventually. So it's
   advisable to protect yourself against this, not only by blocking too
   many tries at a too short period of time, also by not returning too
   much information. (eg for a failed try, don't say "user not found" or
   "password incorrect", better just say "login failed" and let the
   hacker find out on himself what he did wrong ;-) Another prevention
   mechanism , not against bruteforce on itself, but more against non-
   humans trying to login, is by presenting them a CAPTCHA.
   (`http://nl.wikipedia.org/wiki/Captcha`_) If hackers want to bypass
   this, they will have to put much effort in programming OCR-modules.
   (or other nifty tricks, see wiki page) However captcha's are more used
   for forms where one needs to register (spambots like to register on
   forums to have accounts they can use to spam). It's not really needed
   for login-forms as bruteforce-protection is usually enough.

This tutorial/code will help you with most, but not all points.
Bruteforce-protection and anti-session-hijacking are things that i'm
working on right now!


The code
--------
i don't think i need to post the SQL DDL or the PHP file for the users
model. it's just a table in the database with fields id, username,
password, etc.

controllers/users_controller.php

Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController
    {
        var $name = 'Users';
        var $scaffold;
    
    	function login()
    	{
    		$error = false;
    		if (!empty($this->data))
    		{
         		$cleartext = true;
         		$sucess = false;
         		
         		$salt = $this->Session->read('salt');
    	 		$someone = $this->User->findByUsername($this->params['data']['User']['username']);
    	 		if(is_array($someone))
    	 		{
    		 		if(isset($this->data['User']['hashed_pw']) && $this->data['User']['hashed_pw'] )
        	    	{
            			$cleartext = false;
            		}
            		if($cleartext)
            		{
            			if($this->_encrypt($this->data['User']['password']) == $someone['User']['password'])
            			{
            				$sucess = true;
            			}	
            		}
            		else
            		{
            			$real_hash = sha1($someone['User']['password'] . $salt);
    					$submitted_hash = $this->data['User']['hashed_pw'];
            			if($real_hash == $submitted_hash)
            			{
            				$sucess = true;
            			}	
            		}
     			}
            	if($sucess)
            	{
            		$this->Session->write('User', $someone['User']);
            		$this->redirect('/');
            		return true;
            	}
            	else
             	{
             		$error = true;
             		$salt = crc32(time());
             		$this->set('special_sauce',$salt);
             		$this->Session->write('salt', $salt);
             	}
             }
             else
             {
             	$salt = crc32(time());
             	$this->set('special_sauce',$salt);
             	$this->Session->write('salt', $salt);
         	}
          	$this->set('error', $error);
        }
    
    	function logout()
    	{
    		$this->Session->delete('User');
    		$this->Session->setFlash('Logged out');
    		$this->redirect('');
    		return true;
    	}
    	function _encrypt($string)
    	{
    		return sha1($string);
    	}
    	    
    }
    ?>

views/users/login.thtml

View Template:
``````````````

::

    
    <script src='/js/sha1.js' language='javascript'></script>
    <script src='/js/login.js' language='javascript'></script>
    <?php if ($error): ?>
        <p class='error_message'>Invalid credentials.  Login failed</p>
    <?php endif; ?>
    
    <form action='/users/login' method='post'>
    	<div class="panel">
            <label for='username' class='label'>Username:</label>
            <br/><?php echo $html->input('User/username', array('size' => 20, 'class' => 'TextField', 'id'=>'username')); ?>
            <?php echo $html->tagErrorMsg('User/username', 'Please enter your username')?>
            <br/><label for='password' class='label'>Password:</label>
            <br/><?php echo $html->password('User/password', array('size' => 20, 'class' => 'TextField', 'id'=>"password")); ?>
            <?php echo $html->tagErrorMsg('User/password', 'Please enter your password')?>
            <input type='hidden' name='special_sauce' id='special_sauce' value='<?php echo $special_sauce; ?>'>
            <?php echo $html->input('User/hashed_pw', array('type' => 'hidden', 'id'=>'hashed_pw')); ?>
            <br/><?php echo $html->submit('Login', array('class'=>'Button', 'onclick'=>'Javascript:return transform_login();')); ?>
        </div>
    </form>
    
    <?php if ($error): ?>
        <script language='javascript'>
    	emptyFields();
        </script>
    <?php endif; ?>

put these files in webroot/js:
login.js

::

    
    function transform_login()
    {
      var password = document.getElementById('password').value;
      var salt = document.getElementById('special_sauce').value;
      
      var hash = sha1Hash(encrypt(password) + salt);
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
    function emptyFields()
    {
            document.getElementById('password').value = "";
            document.getElementById('username').value = "";
    }
    function encrypt(str)
    {
    	return sha1Hash(str);
    }

sha1.js
get it here: `http://www.ifisgeek.com/js/sha1.js`_

How does it work?
-----------------
The visitor goes to /users/login. A unique salt is generated, and is
made available in 2 places: in the session, and in the view code.
When the page has loaded, the salt is in the HTML source code. the
user enters his username and password. Before submitting, the
JavaScript function transform_login() is called and the hash is
calculated based on the entered password, and the salt that is made
available through the HTML code. This hash is put in an extra field,
and the password is replaced with a random string that has equal
length of the original password. (so that the number of stars doesn't
change in the html form input field).
The form is submitted to the same login action, where the password
(the hash of it) is drawn from the database, together with the salt
that is available in the session. the hashes get compared et voila.
If the client doesn't have JavaScript enabled, nothing special
happens, and the username/password are sent in plaintext over the
network. In the controller the password only goes through the first
encryption step so it can be compared with the password from the
database. You can create whatever encryption function you want, i've
choosen just sha1() for it. Just don't forget to change it both in the
controller as in the login.js
If the login failed, a new salt is generated and made available in the
same 2 places, $error is set to true so the user will see the warning
and will be shown 2 new emptied fields for his uername and password.

One might ask: why the fallback? Isn't it too insecure to accept
cleartext logins?
The answer lies in the transmitting, not in the accepting. If the user
doesn't have js enabled, he will transmit his password in plaintext,
and the security issue here is that some might sniff the network and
pick it up. And use that (plaintext) password to login. For the
"hacker" it doesn't matter if the password gets encrypted or not when
he tries to login, he has the original password anyway.
So just disallowing the cleartext login on itself does not improve
security at all.
However, what would help is creating clientside code that needs
javascript to submit the form. eg without js you wouldn't even be able
to submit your password in cleartext.
This is also a point that i'm working on right now: in my next
version, the developer (you, me) will be able to choose (by setting a
var), if a user can submit in cleartext. This option will totally
prevent non-javascript users from being able to login, but for their
own good.


Access control
--------------
If somebody logs in now, you can be pretty sure the user is who he
claims to be. Controlling what he can and can't do, however is
something else. CakePHP offers ACL for this. the acl mechanism on
itself is very decent, however if you store all your acl-data in your
database, i recommend using a plugin to control it, because manually
editing isn't very straightforward. Some tools for this are

#. `http://cakeforge.org/projects/acm/`_
#. `http://www.noswad.me.uk/MiBlog/ACLPart2`_

but personally, i just use the ini-file method, which is pretty much
the same, accept that it's much easier (just edit the text file) to
controll the acos and aros.

credits
-------
Jeff Read's article, which i've taken inspiration from, can be found
here:
`http://www.ifisgeek.com/tutorials/show/secure_logins_with_challengere
sponse`_

.. _http://www.noswad.me.uk/MiBlog/ACLPart2: http://www.noswad.me.uk/MiBlog/ACLPart2
.. _http://www.ifisgeek.com/js/sha1.js: http://www.ifisgeek.com/js/sha1.js
.. _http://cakeforge.org/projects/acm/: http://cakeforge.org/projects/acm/
.. _http://www.ifisgeek.com/tutorials/show/secure_logins_with_challengeresponse: http://www.ifisgeek.com/tutorials/show/secure_logins_with_challengeresponse
.. _http://nl.wikipedia.org/wiki/Captcha: http://nl.wikipedia.org/wiki/Captcha

.. author:: Dieter_be
.. categories:: articles, tutorials
.. tags:: secure login challen,login,dauth,challenge
response,secure,Tutorials

