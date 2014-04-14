DarkAuth - another way...
=========================

by theChrisWalker on February 18, 2008

I wrote this initially for Cake 1.1 - basing it on ideas from "obAuth"
by Steve Oliveira, but upgraded it to 1.2 rather than using the built
in Auth component, mostly because this works how I want it to and,
once setup, is really easy to use. Main Features: - Per action / per
controller / inline access control - Optional Group support for HABTM
and User BelongsTo Group associations - A "super-user" functionality
allowing one group automatic access granted - Optional tamper-proof
Cookie support - Custom password hashing to suit your Model
I'm fairly new to CakePHP and I went for the stable release, and
immediately wanted to do User Authentication. It's almost the first
thing I build into any project. CakePHP 1.1 didn't have it, so I built
my own.

This is mainly based on "obAuth" by Steve Oliveira. It was those ideas
that formed the basis for this component. It includes a lot of the
functionality that was designed into obAuth but never completed and
also now ammended to work with CakePHP 1.2.

As soon as I switched to CakePHP 1.2 and wanted the same great
features I built into the original and the same easy access control.
So, I rewrote it and here's the result.

Firstly I'll show you how easy it is to work with.

Let's say you have a SecretsController which you want to restrict
access to:


Controller Class:
`````````````````

::

    <?php 
    class SecretsController extends AppController {
      var $name = 'Secrets';
    
      var $DarkAuth_requiresAuth = array('Secret Keepers','Super Cool People');
    
    ...
    
    }
    ?>

Notice the one-liner? This will restrict access to the controller to
Users who are memebrs of either the group ' Secret Keepers ' or the
group ' Super Cool People '.

Easy huh? However, I think Cake's own is that easy as well, so lets
look at some more functionality.

How about you have a DocumentsController that only certain people can
add to?


Controller Class:
`````````````````

::

    <?php 
    class DocumentsController extends AppController {
      var $name = 'Documents';
    
      function add(){
        $this->DarkAuth->requiresAuth = array('Secret Makers');
        
        ...
      }
    
    ...
    
    }
    ?>

Now that was easy, only memebers of the group ' Secret Makers ' will
have access.

How about your DocumentsController that has some Top Secret stuff in
it and you want to restrict access? We could add to our "view" action
some simple code:


Controller Class:
`````````````````

::

    <?php 
      function view($id){
        $this->Document->id = $id;
        $doc = $this->Document->read();
    
        if($doc['Document']['top_secret'] == true){
           $this->DarkAuth->requiresAuth('Top Brass');
        }
        ...
    
      }
    ?>

Note that the function " requiresAuth() " stops processing after you
call it and will bring up an "access denied" view if authentication
fails.

What if you wanted not to fail after but to redirect somewhere else?


Controller Class:
`````````````````

::

    <?php 
    $this->DarkAuth->requiresAuth('Top Brass');
    ?>

Or more common if you wanted to show content based on whether
authentication was present?


Controller Class:
`````````````````

::

    <?php 
      if($this->DarkAuth->isAllowed(array('Chocolate Lover'))){
        $data = $this->CookieJar->findAll(array('Chocolate'=>true));
      }else{
        $data = $this->CookieJar->findAll(array('Chocolate'=>false));
      }
    ?>

The " isAllowed() " function will return true/false but not halt
processing.

The final selling point (in my opinion)! $DarkAuth_User available in
the View, automatically populated with the user info from the user
model. e.g.


View Template:
``````````````

::

    
    pr($DarkAuth_User);

Yields (if logged in, if not logged in the variable is null):

::

    
    array(
      'id' => 1
      'username' => "superstar"
      'password' => "abcdef1234567890abcdef1234567890"
      'other_info' => "Some data"
    )

Which means you can do this:


View Template:
``````````````

::

    
    if(!empty($DarkAuth_User)){ 
      echo "Some content for logged in people!";
    }

Convinced? I hope so. Now on the Code and Setup

So here's the code for the component, it's quite a chunk, then on the
next page I'll describe the setup.


Component Class:
````````````````

::

    <?php 
    class DarkAuthComponent extends Object {
    
      var $user_model_name = 'User';
      var $users_controller_name = 'Users'; 
      var $user_name_field = 'email'; //e.g. email or firstname or username...
      var $user_name_case_folding = 'lower'; //do you want to case fold the username before verifying? either 'lower','upper','none', to change case to lower/upper/leave it alone before matching.
      var $user_pass_field = 'password';
      var $user_live_field = 'live'; // surely you have a field in you users table to show whether the user is active or not? set to null if not.
      var $user_live_value = 1;
      var $group_model_name = 'Group'; //Group for access control if used. NB: DON'T CALL requiresAuth with Groups if no group model. it will error.
      var $group_name_field = 'name'; // the name of the field used for the groups name. This will be used to check against passed groups.
      var $HABTM = true; //set to false if you don use a HABTM group relationship.
      var $superuser_group = 'Root'; //if you want a single group to have automatically granted access to any restriction.
      var $login_view = '/login';  //this is the login view, usually {user_controller}/login but you may have changed the routes.
      var $deny_view = '/deny';  //this is the default denied access view.
      var $logout_page = '/'; // NB this is were to redirect AFTER logout by default
      var $login_failed_message = '<p class="error">Login Failed, Please check your details and try again.</p>'; //This message is setFlash()'d on failed login.
      var $allow_cookie = true; //Allow use of cookies to remember authenticated sessions.
      var $cookie_expiry = '+6 Months'; //how long until cookies expire. format is "strtotime()" based (http://php.net/strtotime).
    	//var $session_secure_key = 'sRmtVStkedAdlxBy'; //some random stuff that someone is unlikey to guess. 
    	var $session_secure_key = 'sJfkgD420YsfhC2k4Abs';
    
    	/*
       * You can edit this function to explain how you want to hash your passwords.
       */
      function hasher($plain_text){
    
        $hashed = md5('dark'.$plain_text.'cake');
    
        return $hashed;
      }
    
    ##########################################################################
     /*
      * DON'T EDIT THESE OR ANYTHING BELOW HERE UNLESS YOU KNOW WHAT YOU'RE DOING
      */
      var $controller;
      var $here;
      var $components=array('Session');
      var $current_user;
      var $from_session;
      var $from_post;
      var $from_cookie;
    
      function startup(&$controller){
      
      	//Let's check they have changed the secure key from the default.
    		if($this->session_secure_key == 'sRmtVStkedAdlxBy'){
    			die('<p>Please change the DarkAuth::session_secure_key value from it default.</p>');
    		}
    		
        $this->controller = $controller;
        
        $this->here = substr($controller->here,strlen($controller->base));
        
        $this->controller->_login();
        
        //now check session/cookie info.
        $this->getUserInfoFromSessionOrCookie();
    
        //now see if the calling controller wants auth (except for the users/login or logout or deny actions)
        if( array_key_exists('DarkAuth_requiresAuth', $controller) ){
          // We want Auth for any action here
          if(array_key_exists('DarkAuth_ifAccessDenied',$controller)){
    			  $deny = $controller->DarkAuth_ifAccessDenied;
    			}else{
    			  $deny = null;
    			}
          $this->requiresAuth($controller->DarkAuth_requiresAuth,$deny);
        }
        //finally give the view access to the data
        $this->controller->set('DarkAuth_User',$this->getUserInfo());
      }
    
    	function secure_key(){
    		static $key;
    		if(!$key){
    			$key = md5(Configure::read('Security.salt').'!DarkAuth!'.$this->session_secure_key);
    		}
    		return $key;
    	}
    
      function requiresAuth($groups=array(),$deny_redirect=null){
    		if( empty($this->current_user) ){
    			// Still no info! render logion page!
    			if($this->from_post){
    				$this->Session->setFlash($this->login_failed_message); 
    			}
          $this->controller->render($this->login_view);
          exit();
        }else{
          if($this->from_post){
    				// user just authed, so redirect to avoid post data refresh.
    				$this->controller->redirect($this->here);
    				exit();
          }
          // User is authenticated, so we just need to check against the groups.
          if( empty($groups) ){
            // No Groups specified so we are good to go!
            $deny = false;
          }else{
            $deny = !$this->isAllowed($groups);
          }
          if($deny){
            // Current User Doesn't Have Access! DENY
            if($deny_redirect){
    					$this->controller->redirect($deny_redirect);
    					exit();
    				}else{
    					$this->controller->render($this->deny_view);
    					exit();
    				}
          }
        }
        return true;
      }
     
      function isAllowed($groups=array()){
        if( empty($this->current_user) ){
          // No information about the user! FALSE
          return false;
        }else{
          // User is authenticated, so we just need to check against the groups.
          if(!is_array($groups)){ $groups[0] = $groups; }
          if( empty($groups) ){
            // No Groups specified so we are good to go! TRUE
            return true;
          }else{
    				//first check superuser access.
    				if($this->superuser_group){
    					array_unshift($groups,$this->superuser_group);
    				}
            // Check each group.
            if(!$this->HABTM){
              // Single relation ship.
              foreach($groups as $g){
                if(
                  $this->current_user[$this->group_model_name]['id'] == $g ||
                  $this->current_user[$this->group_model_name][$this->group_name_field] == $g
                ){
                  // Our Authenticated user matches a group! TRUE
                  return true;
                }
              }
            }else{
              //HasAndBelongToMany relationship. we search the other way around...
              foreach($this->current_user[$this->group_model_name] as $g){
                if(
                  in_array($g['id'],$groups) ||
                  in_array($g[$this->group_name_field],$groups)
                ){
                  // Our Authenticated user matches a group! TRUE
                  return true;
                }
              }
            }
            //No Access this time. FALSE
            return false;
          }
        }
      }
    
      function getCookieInfo(){
    		if(!array_key_exists('DarkAuth',$_COOKIE)){
    			//No cookie
    			return false;
    		}
    		list($hash,$data) = explode("|||",$_COOKIE['DarkAuth']);
    		if($hash != md5($data.$this->secure_key())){
    			//Cookie has been tampered with
    			return false;
    		}
    		$crumbs = unserialize(base64_decode($data));
    		if(!array_key_exists('username',$crumbs) ||
    			 !array_key_exists('password',$crumbs) ||
    			 !array_key_exists('expiry'  ,$crumbs)){
    			//Cookie doesn't contain the correct info.
    			return false;
    		}
    		if(!isset($crumbs['expiry']) || $crumbs['expiry'] <= time()){
    			//Cookie is out of date!
    			return false;
    		}
    		//All checks passed, cookie is genuine. remove expiry time and return
    		unset($crumbs['expiry']);
    		return $crumbs;		
      }
      
      function setCookieInfo($data,$expiry=0){
    	  if($data === false){
    			//remove cookie!
    			$cookie = false;
    			$expiry = 100; //should be in the past enough!
    	  }else{
    			$serial = base64_encode(serialize($data));
    			$hash = md5($serial.$this->secure_key());
    			$cookie = $hash."|||".$serial;
    		}
    		if($_SERVER['SERVER_NAME']=='localhost'){
    		  $domain = null;
    		}else{
    		  $domain = '.'.$_SERVER['SERVER_NAME'];
    		}
    		return setcookie('DarkAuth', $cookie, $expiry, $this->controller->base, $domain);
      }
    
      function authenticate_from_post($data){
    		$this->from_post = true;
    		return $this->authenticate($data);
      }
      function authenticate_from_session($data){
    		$this->from_session = true;
    		return $this->authenticate($data);
    	}
    	function authenticate_from_cookie(){
    		$this->from_cookie = true;
    		return $this->authenticate($this->getCookieInfo());
    	}
    	
      function authenticate($data){
    		if($data === false){
    			$this->destroyData();
    			return false;
    		}
        if($this->from_session || $this->from_cookie){
          $hashed_password = $data['password'];
        }else{
          $hashed_password = $this->hasher($data['password']);
        }    
        switch($this->user_name_case_folding){
    			case 'lower':
    				$data['username'] = strtolower($data['username']);
    				break;			
    			case 'upper';
    				$data['username'] = strtoupper($data['username']);
    				break;
    			default: break;
        }
        $conditions = array(
          $this->user_model_name.".".$this->user_name_field => $data['username'],
          $this->user_model_name.".".$this->user_pass_field => $hashed_password
        );
        if($this->user_live_field){
          $field = $this->user_model_name.".".$this->user_live_field;
          $conditions[$field] = $this->user_live_value;
        };
        $check = $this->controller->{$this->user_model_name}->find($conditions);
        if($check){
           $this->Session->write($this->secure_key(),$check);
           if(
    				  $this->allow_cookie && //check we're allowing cookies
    				  $this->from_post && //check this was a posted login attempt.
    				  array_key_exists('remember_me',$data) && //check they where given the option!
    				  $data['remember_me'] == true //check they WANT a cookie set
    			 ){
    				 // set our cookie!
    				 if(array_key_exists('cookie_expiry',$data)){
    				   $this->cookie_expiry = $data['cookie_expiry'];
    				 }else{
    				   $this->cookie_expiry;
    				 }
    				 if(strtotime($this->cookie_expiry) <= time()){
    					// Session cookie? might as well not set at all...
    				 }else{
    				   $expiry = strtotime($this->cookie_expiry);
    				   $this->setCookieInfo(array('username'=>$data['username'], 'password'=>$hashed_password, 'expiry'=>$expiry), $expiry);
    				 } 
    			 }
           $this->current_user = $check;
           return true;
        }else{
           $this->destroyData();
           return false;
        }
      }
    
      function getUserInfo(){
        return $this->current_user[$this->user_model_name];
      }
      function getAllUserInfo(){
        return $this->current_user;
      }
    
      function destroyData(){
        $this->Session->delete($this->secure_key());
        if($this->allow_cookie){
    				$this->setcookieInfo(false); 
    		}
        $this->current_user = null;
      }
    
      function logout($redirect=false){
        $this->destroyData();
        if(!$redirect){
          $redirect = $this->logout_page;
        }
        $this->controller->redirect($redirect);
        exit();
      }
    
      function getUserInfoFromSessionOrCookie(){
        if( !empty($this->current_user) ){ 
          return false; 
        }
        if($this->Session->valid() && $this->Session->check($this->secure_key()) ){
          $this->current_user = $this->Session->read($this->secure_key());
          return $this->authenticate_from_session(array(
            'username' => $this->current_user[$this->user_model_name][$this->user_name_field],
            'password' => $this->current_user[$this->user_model_name][$this->user_pass_field],
          ));
        }elseif($this->allow_cookie){
    			return $this->authenticate_from_cookie();
        }
      }
    }
    ?>

Got all that... good now let's set it up!

The follow steps should guide you through the setup process and the
files you need to alter.

Of course, you will need to have the models for your User table (and
groups if applicable).

I would often use the following with a $hasAndBelongsToMany
association (I pretty much always use the first 4 fields of the users
and groups tables with cake):

CREATE TABLE `users` (
`id` int(11) NOT NULL auto_increment,
`created` datetime default NULL,
`modified` datetime default NULL,
`live` tinyint(1) NOT NULL default 0,
`username` varchar(16) NOT NULL default '',
`password` varchar(32) NOT NULL default '',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `groups` (
`id` int(11) NOT NULL auto_increment,
`created` datetime default NULL,
`modified` datetime default NULL,
`live` tinyint(1) NOT NULL default 0,
`name` varchar(32) NOT NULL default '',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `groups_users` (
`group_id` int(11) NOT NULL,
`user_id` int(11) NOT NULL,
KEY `group_id` (`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

Look at the Cake Manual for how to setup the Models for these tables.

If you don't use the HABTM association, then remember to set var HABTM
= false; later. This will then assume that the user $belongsTo a group
(and therefore you'd need a "group_id" field in your "users" table).

Now we have 5 (or 6) steps to a working, powerful authentication
system!


Step 1: Modify AppController
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

I decided this was the easiest way as then your whole site knows about
the Authentication, however I can see how it might put unnecessary
load on in some situations.

This allows all controllers/views access to the auth component/data.
In app_controller.php :


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
        var $uses = array ('YOUR_MODEL_FOR_USERS');
        var $components = array('DarkAuth');
      }
    ?>

Where YOUR_MODEL_FOR_USERS is the name of your user model.

NB Remember if you want to use controllers with no models you will now
need to use var $uses = array(); rather than var $uses = null; or
you'll get errors!


Step 2: Add Default Methods
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now add the inversal login/logout methods to your app_controller.php
and auto-include the "Session" helper (or is that included by default
anyway now...):


Controller Class:
`````````````````

::

    <?php 
      var $helpers = array('Session');
    
      function _login(){
        if($this->data['DarkAuth']){
          unset($this->data['from_session']);
          $this->DarkAuth->authenticate_from_post($this->data['DarkAuth']);
          exit();
        }
      }
      
      function logout(){
        $this->DarkAuth->logout();
        // By this stage we should have redirected and exited already, but just in case we'll pass them back to home...
        $this->redirect($this->referer()); //thanks to everyone who spotted this.
      }
    ?>

NB the logout method is called "logout" meaning you can call it from
any controller at "/:controller/logout" but "_login()" won't be
available, meaning you can create your own login page in a
controller/page.


Step 3: Add the Views
~~~~~~~~~~~~~~~~~~~~~

Now we need to add the views for this component. They should be in the
root of you views folder as we will need to call them from arbitrary
controllers.

The 2 files are totally up to you except that the login page must pass
the following data in the form:

[DarkAuth][username],
[DarkAuth][password]
and optionally if you have set the "$allow_cookie" variable:

[DarkAuth][remember_me],
[DarkAuth][cookie_expires],

Here are the templates I use:


View Template:
``````````````

::

          
    <?php /* View for login.ctp */ ?>
      
    <h2>Login</h2>
      <div id='loginbox'>
          <?php 
    	echo $form->create('DarkAuth',array('url'=>substr($this->here,strlen($this->base))));
            echo "\n<div class='input required'>";
    	echo $form->input('username', array('div'=>false));
    	echo "</div>";
    	echo "\n<div class='input required'>";
    	echo $form->label('password');
            echo $form->password('DarkAuth/password');
    	echo "</div>\n";
    		
    /* if you want to use cookies uncomment this. */
    /*
          echo "<div class='input required'>";
          echo $form->checkbox('DarkAuth/remember_me');
          echo $form->label('Remember Me? (uses cookies)');
          echo "</div>\n";
          echo "<div class='input required'>";
          echo $form->label('If so, for how long?');
          echo $form->select('DarkAuth/cookie_expiry',array(
                                                            '+1 week'=>'in a week',
                                                            '+1 Months'=>'in a month',
                                                            '+6 Months'=>'in 6 months',
                                                            ));
          echo "</div>\n";
          
    /* end of cookie bits */
            
         echo $form->end('Login');
      ?>
      </div>
      
    <?php /* View for deny.ctp */ ?>
      
    <h2>Access Denied</h2>
    <p>Sorry you don't have sufficient permission to access this page!</p>



Step 4: Edit the Component Setup Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Edit the class variables in this file to match your model structure.
these are in the top of the class definition on the previous page.


Step 5: Customise the password hasher
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Change the " hasher() " function to match the way you store passwords
in your model.
By default the hasher simply md5 hashes the input. you may wish to add
salt, or encrypt in a different way.


Step 6 (optional): Create a Logout Route
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Set up a route in you routes.php to allow you to logout in a nice way.
Otherwise, you need to call "/controller/logout". I personally usually
use my "Users" controller for this.

::

    
      Router::connect('/logout', array('controller' => 'ANY_CONTROLLER', 'action' => 'logout'));



And that's all
~~~~~~~~~~~~~~

It sounds like a lot when I write it down, but actually it's not hard
and the effect is great and easy to use. I haven't looked at Cake's
own to know whether this is better / worse , simpler / more complex
but it works for me and perhap you need something exactly like this!
`1`_|`2`_|`3`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_

.. _Page 2: :///articles/view/4caea0e0-f5b8-4d33-914a-41d482f0cb67/lang:eng#page-2
.. _Page 1: :///articles/view/4caea0e0-f5b8-4d33-914a-41d482f0cb67/lang:eng#page-1
.. _Page 3: :///articles/view/4caea0e0-f5b8-4d33-914a-41d482f0cb67/lang:eng#page-3
.. meta::
    :title: DarkAuth - another way...
    :description: CakePHP Article related to authentication,obAuth,access control,superuser,Components
    :keywords: authentication,obAuth,access control,superuser,Components
    :copyright: Copyright 2008 theChrisWalker
    :category: components

