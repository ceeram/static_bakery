DarkAuth v1.3 - an alternative Auth
===================================

by theChrisWalker on June 11, 2008

An update to the original DarkAuth component
(http://bakery.cakephp.org/articles/view/darkauth-another-way), with
increased efficiency and easier access control. It works exactly how I
want to, so it might not be your first choice, but it's a solid
alternative to the inbuilt AuthComponent. Main Features: - Per action
/ per controller / inline access control - Group support for HABTM and
BelongsTo Group associations, or disable groups control completely. -
A "super-user" functionality allowing one group automatic access
granted - Optional tamper-proof Cookie support - Custom password
hashing to suit your Model - Passes User Info and Access Matrix to
view - Methods to access User Info / Access Matrix in Controller

Update: 14th May 2008

After some discussion regarding this component (thanks Sam), I thought
I would point out at this stage that the component is really geared
towards "Role-based" authentication, something I hadn't even noticed
at while building it.

That being said, it makes it even more unlikely that it would be used
in a non-HABTM type situation - so be aware if you are thinking of
using this is a "User belongsTo Group" that you may find it hard to
get the fluid access control you want.

On the other hand if you are using it purely to restrict access with
NO group control, then it couldn't be easier!

Firstly thanks to everyone who encouraged me after the first release
of DarkAuth. Looking back at it, it was flawed yet I got support from
the community and have worked to better it.

Again, I'd better whet your appetite with just how easy it is to use.

Let's say you have a SecretsController which you want to restrict
access to:


Controller Class:
`````````````````

::

    <?php 
    class SecretsController extends AppController {
      var $name = 'Secrets';
      var $_DarkAuth;
    
    ...
    
    }
    ?>

See what I did there? Now you'd have to be logged in to access this
Controller. So, now you want to refine this to allow only logged on
users that are a member of the group Admin or the group SecretKeepers
. Easy!


Controller Class:
`````````````````

::

    <?php 
    class SecretsController extends AppController {
      var $name = 'Secrets';
      var $_DarkAuth = array('required'=>array('Admin','SecretKeepers'));
    
    ...
    
    }
    ?>

Not bad eh? So moving on, by default your users if not allowed access
will see your specified "deny" page, but you want them to be
redirected back to /public . Again, straightforward.


Controller Class:
`````````````````

::

    <?php 
    class SecretsController extends AppController {
      var $name = 'Secrets';
      var $_DarkAuth = array(
             'required'=>array('Admin','SecretKeepers'),
             'onDeny'=>'/public'
          );
    
    ...
    
    }
    ?>

Now, any attempt to access your SecretsController will be presented
with a login page if not logged in and if logged in but not a member
of the group, will be redirected to /public .

This can be done per action as well, to further enhance the
usefullness of the component. E.g. you have a DocumentsController
which might have some top secret documents in it. You only want
members of the group TopBrass to be able to see them.


Controller Class:
`````````````````

::

    <?php 
      function display($id=0){
        $this->Document->id = $id;
        $doc = $this->Document->read();
    
        if($doc['Document']['top_secret'] == true){
           $this->DarkAuth->requiresAuth('TopBrass');
        }
        ...
    
      }
    ?>

Note that the function DarkAuth::requiresAuth() stops processing after
you call it and will bring up an "access denied" view if
authentication fails. So no need to exit().

Or more common if you wanted to show content based on whether
authentication was present? The DarkAuth::isAllowed() function returns
whether access is allowed, but doesn't stop processing:


Controller Class:
`````````````````

::

    <?php 
      if($this->DarkAuth->isAllowed(array('ChocolateLover'))){
        $data = $this->CookieJar->findAll(array('Chocolate'=>true));
      }else{
        $data = $this->CookieJar->findAll(array('Chocolate'=>false));
      }
    ?>

The final selling point (in my opinion)! $_DarkAuth available in the
View, automatically populated with the user info from the user model
and the access control list. e.g.


View Template:
``````````````

::

    
    <?php
      pr($_DarkAuth);
    ?>

Yields if logged in:

::

    
    array(
          'User' => array(
                          'id' => 1
                          'username' => superstar
                          'password' => abcdef1234567890abcdef1234567890
                          'other_info' => Some data
                    )
      'Access' => array(
                        'group_you_have_access_to' => 1
                        'another_group_you_have_access_to' => 1
                        'group_you_have_NO_access_to' => 0
                  )
    )

Which means you can do this:


View Template:
``````````````

::

    
    <?php
    if(!empty($_DarkAuth['User'])){ 
      echo "Some content for logged in people!";
    }
    if($_DarkAuth['Access']['some_group']){
      echo "You have access to 'some_group'";
    }else{
      echo "You don't have access to 'some_group'";
    }
    ?>

Convinced? I hope so. Now on the Code and Setup!

Here's the v1.3 Code.


Component Class:
````````````````

::

    <?php 
    class DarkAuthComponent extends Object {
    
      var $user_model_name = 'User';
      var $user_name_field = 'email'; //e.g. email or firstname or username...
      var $user_name_case_folding = 'lower'; //do you want to case fold the username before verifying? either 'lower','upper','none', to change case to lower/upper/leave it alone before matching.
      var $user_pass_field = 'pswd';
      var $user_live_field = 'live'; // surely you have a field in you users table to show whether the user is active or not? set to null if not.
      var $user_live_value = 1;
      var $group_model_name = 'Group'; //Group for access control if used, if not used please set to an empty string. NB: DON'T CALL requiresAuth with Groups if no group model. it will error.
      var $group_name_field = 'name'; // the name of the field used for the groups name. This will be used to check against passed groups.
      var $HABTM = true; //set to false if you don't use a HABTM group relationship. Ignore if no association.
      var $superuser_group = 'Root'; //if you want a single group to have automatically granted access to any restriction.
      var $login_view = '/login';  //this is the login view, usually {user_controller}/login but you may have changed the routes.
      var $deny_view = '/deny';  //this is the default denied access view.
      var $logout_page = '/'; // NB this is were to redirect AFTER logout by default
      var $login_failed_message = '<p class="error">Login Failed, Please check your details and try again.</p>'; //This message is setFlash()'d on failed login.
      var $logout_message = '<p class="success">You have been succesfully logged out.</p>'; //Message to setFlash after logout.
      var $allow_cookie = false; //Allow use of cookies to remember authenticated sessions.
      var $cookie_expiry = '+6 Months'; //how long until cookies expire. format is "strtotime()" based (http://php.net/strtotime).
    	var $session_secure_key = 'sRmtVStkedAdlxBy'; //some random stuff that someone is unlikey to guess. 
    
      /*
       * You can edit this function to explain how you want to hash your passwords.
       * Also you can use it as a static function in your controller to hash passwords beforeSave
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
        
        $this->here = substr($this->controller->here,strlen($this->controller->base));
          
        $this->controller->_login();
        
        //now check session/cookie info.
        $this->getUserInfoFromSessionOrCookie();
    
        //now see if the calling controller wants auth
        if( array_key_exists('_DarkAuth', $this->controller) ){
          // We want Auth for any action here
          if(!empty($this->controller->_DarkAuth['onDeny'])){
    			  $deny = $this->controller->_DarkAuth['onDeny'];
    			}else{
    			  $deny = null;
    			}
    			if(!empty($this->controller->_DarkAuth['required'])){
    			  $this->requiresAuth($this->controller->_DarkAuth['required'],$deny);
    			}else{
            $this->requiresAuth(null,$deny);
          }
        }
        //finally give the view access to the data
        $DA = array(
          'User'=>$this->getUserInfo(),
          'Access'=>$this->getAccessList()
        );
        $this->controller->set('_DarkAuth',$DA);
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
    			// Still no info! render login page!
    			if($this->from_post){
    				$this->Session->setFlash($this->login_failed_message); 
    			}
          $this->controller->render($this->login_view);
          exit();
        }else{
          if($this->from_post){
    				// user just authed, so redirect to avoid post data refresh.
    				$this->controller->redirect($this->here,null,null,true);
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
          if( empty($groups) ){
            // No Groups specified so we are good to go! TRUE
            return true;
          }
          
          if(!is_array($groups)){
            //if a string passed, turn to an array with one element
            $groups = array(0 => $groups); 
          }
          
          $access = $this->getAccessList();
                
          foreach($groups as $g){
            if(array_key_exists($g,$access) && $access[$g]){
              return true;
            }
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
        	if($this->from_post){
    	      $this->Session->setFlash($this->login_failed_message); 
    			}
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
      function getAccessList(){
        static $access_list = false;
        if(!$access_list){
          $access_list = $this->_generateAccessList();
        }
        return $access_list;
      }
      function _generateAccessList(){
        if(!$this->group_model_name){
          return array();
        }
        $all_groups = $this->controller->{$this->user_model_name}->{$this->group_model_name}->find('list');
        if(!count($all_groups)){  return array(); }
        $access = array_combine($all_groups,array_fill(0,count($all_groups),0)); //create empty array.
        
        if(empty($this->current_user)){
          // NO AUTHENTICATION, SO EMTPY ARRAY!
          return $access;
        } 
        if($this->HABTM){
          // could be many groups 
          $ugroups = Set::combine($this->current_user[$this->group_model_name],'{n}.id','{n}.'.$this->group_name_field);
          foreach($all_groups as $id => $role){
            if(in_array($role,$ugroups)){
              $access[$role] = 1;
            }else{
              $access[$role] = 0;
            }
          }
        }else{
          // single group assoc, id = user.group_id
          $foreign_key = $this->controller->{$this->user_model_name}->belongsTo[$this->group_model_name]['foreignKey'];
          foreach($all_groups as $id => $role){
            if($this->current_user[$this->user_model_name][$foreign_key] == $id){
              $access[$role] = 1;
            }else{
              $access[$role] = 0;
            }
          }
        }
        if($this->superuser_group && $access[$this->superuser_group]){
          return array_combine($all_groups,array_fill(0,count($all_groups),1));
        }else{
          return $access;
        }
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
    		$this->Session->setFlash($this->logout_message); 
        $this->controller->redirect($redirect,null,true);
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

Now on to the Setup and the default Views for Login and Deny.

The following steps should guide you through the setup process and the
files you need to alter.

Of course, you will need to have the models for your User table (and
groups if applicable). I will assume you have these models setup with
Cake conventions with the following schema (using HABTM association):

::

    
    CREATE TABLE `users` (
    `id` int(11) NOT NULL auto_increment,
    `created` datetime default NULL,
    `modified` datetime default NULL,
    `live` tinyint(1) NOT NULL default 0,
    `username` varchar(16) NOT NULL default '',
    `pswd` varchar(32) NOT NULL default '',
    PRIMARY KEY (`id`)
    );
    
    CREATE TABLE `groups` (
    `id` int(11) NOT NULL auto_increment,
    `created` datetime default NULL,
    `modified` datetime default NULL,
    `live` tinyint(1) NOT NULL default 0,
    `name` varchar(32) NOT NULL default '',
    PRIMARY KEY (`id`)
    );
    
    CREATE TABLE `groups_users` (
    `group_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    KEY `group_id` (`group_id`,`user_id`)
    );

If you don't use the HABTM association, then remember to set var HABTM
= false; later. This will then assume that the user $belongsTo a group
(and therefore you'd need a "group_id" field in your "users" table).

Look at the Cake Manual for how to setup the Models for these tables.


Step 1: AppController
---------------------

If you have created an AppController in your own controllers
directory, nows the time, create a file called app_controller.php and
populate it as follows. If you have got one, it should be easy enough
to see what you'll need to add to yours.


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
      var $uses = array('User');
      var $components = array('DarkAuth');
    
      function _login(){
        if(is_array($this->data) && array_key_exists('DarkAuth',$this->data) ){ 
          $this->DarkAuth->authenticate_from_post($this->data['DarkAuth']);
          $this->data['DarkAuth']['password'] = '';
        }
      }
      
      function logout(){
        $this->DarkAuth->logout();
      }
    }
    ?>



Step 2: Login and Deny Views
----------------------------

You can create these however you want, however I discovered something
very useful in that you can render Views using Controller::render()
using absolute paths, so Controller::render('/login') would render a
view in the root of your Views Folder. Using this to our advantage we
can allow an arbitrary controller access to a view via the same render
path. So I create a login View at /app/views/login.ctp , again it's up
to you but it must post the following data:

[DarkAuth][username],
[DarkAuth][password]
and optionally if you have set the "$allow_cookie" variable:

[DarkAuth][remember_me],
[DarkAuth][cookie_expires],

Here's a simple one which will do the trick:


View Template:
``````````````

::

    
    <?php
      $this->pageTitle = 'Access Restricted';
      echo $form->create('DarkAuth',array('url'=>substr($this->here,strlen($this->base))));
      echo $form->input('DarkAuth.username');
      echo $form->password('DarkAuth.password');
    				
    /* Uncomment for cookies...
      echo $form->input('DarkAuth.remember_me',array(
              'label'=>'Remember Me? (uses cookies)',
              'type'=>'checkbox'
              ));
      echo $form->input('DarkAuth.cookie_expiry',array(
              'options'=>array(
                           'now'=>'end of session',
                           '+1 week'=>'in a week',
                           '+1 Months'=>'in a month',
                           '+6 Months'=>'in 6 months',
                         ),
              'label'=>'If so, for how long?'
              ));
    */
      echo $form->end('login');
    ?>

And a page for /app/views/deny.ctp :


View Template:
``````````````

::

    
    <?php
      $this->pageTitle = 'Access Denied!';
    ?>
      <p>I'm sorry, but you don't have sufficient permission to access this page!</p>



Step 4: Edit the Component's Variables and Hasher
-------------------------------------------------

There are a number which need to be configured to match your user and
group models, the fields they use for username and password and the
association type.

There are others for successful logout, login failure messages,
default redirections and more. Please look over them to get the
component to work how you want it.

The final thing to configure is the DarkAuth::hasher() function (which
can be used anywhere to hash passwords in the same way that they are
hashed in the database. Make sure your either use the same hashing
function or amend this one how you want.


Step 5: The Logout Route
------------------------

This is optional, as we put the logout() function in AppController so
accessible from any controller. However, I find it more aesthetically
pleasing to have a route for logout at /logout . Add this to your
app/config/routes.php :

::

    
    Router::connect('/logout',array('controller'=>'users','action'=>'logout'));

NB any controller would do, but you're pretty sure to have a
UsersController.


Step 6: Enjoy!
--------------

That's it. Hopefully you haven't had too many issues, and your App is
now secure and happy.
`1`_|`2`_|`3`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_

.. _Page 1: :///articles/view/4caea0e1-8710-43ba-80f5-4b8982f0cb67/lang:eng#page-1
.. _Page 3: :///articles/view/4caea0e1-8710-43ba-80f5-4b8982f0cb67/lang:eng#page-3
.. _Page 2: :///articles/view/4caea0e1-8710-43ba-80f5-4b8982f0cb67/lang:eng#page-2

.. author:: theChrisWalker
.. categories:: articles, components
.. tags:: authentication,obAuth,access control,role
based,roles,superuser,Components

