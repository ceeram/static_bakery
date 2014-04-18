SecureGet Component
===================

I was searching a simple way to "secure" a bit the links in my cake
application. Indeed everyone had this question "what if someone types
'cake/app/user/edit/5' in the browser URL bar ?" ..etc .. I am not
pretending this can replace ACL or more complex implementation, it's
just a small component, inspired a bit from the Security component.

The idea is to generate the link and adding a key as the last
parameter of the URL.
The application would then verify the key against what is supposed to
be.
This verification is done only on "get" method, not on post neither on
"requested" (it could be maybe).
In order to "alter" the links, I needed an helper. In fact I set this
method into the app_helper.php making it available from all helpers.

The component (controllers/components/secure_get.php)

::

    
    <?php
    /*
     * Secure get link type
     *
     */
    class SecureGetComponent extends Object
    {
         var $securedActions  = array();
         var $components      = array('Session', 'RequestHandler');
         var $securityLog     = 'security';
         var $flashMessage    = 'Security alert';
         var $flashKey        = 'sec';
         var $redirectFail    = '/';
    
         /* initiialization, checks parameters overidden in config */
         
         function initialize(&$controller)
         {
              $this->_initFromConfig();
    
              if(!$this->Session->check('SecureGet.hashKey'))
                 $this->_generateHashKey();          
         }
    
         /* when starting up, verify the requested action (if get) */
    
         function startup(&$controller)
         {
              if((!isset($controller->params['requested']) || $controller->params['requested'] != 1) && !$this->RequestHandler->isPost())
              {
                   $this->_check($controller);
              }
         }
    
         /* set the secured actions */
         
         function secureActions()
         {
              $args = func_get_args();
    
              if(!empty($args))
                   $this->securedActions = am($this->securedActions, $args);
         }
         
         /* check the incoming action if not a post and not a request action */
    
         function _check(&$controller)
         {
              // check if the action is in the array
    
              if(in_array($controller->params['action'], $this->securedActions))
              {
                   $rc = false;
    
                   if(isset($controller->passedArgs) && count($controller->passedArgs) > 0)
                   {
                        $localargs = $controller->passedArgs;
    
                        // extract the last argument
    
                        $key = array_pop($localargs);
                        $lid  = implode('', $localargs);
                        $nval = sha1($this->_getHashKey().$lid);
    
                        if($nval === $key)
                           $rc = true;
                   }
    
                   if(!$rc)
                        $this->_logSecurity($controller);
    	        }
         }
    
         /* log and flash message in case of failure */
    
         function _logSecurity($controller)
         {
         	  if(!empty($this->securityLog))
         	  {
                   if(!class_exists('CakeLog'))
                        uses('cake_log');
    
                   $message = "Mismatch security arguments: ".isset($controller->params['url']['url']) ? $controller->params['url']['url'] : $controller->name."/".$controller->params['action'];
                   CakeLog::write($this->securityLog, $message);
              }
    
              // we redirect by logout with flash message
              if(!empty($this->flashMessage))
                   $controller->Session->setFlash($this->flashMessage, 'default', array(), isset($this->flashKey) ? $this->flashKey : null);
              $this->log($this->flashKey);
    
              $controller->redirect(!empty($this->redirectFail) ? $this->redirectFail : null, null, true);
         }
    
         /* initdefault from config file (if present) */
    
         function _initFromConfig()
         {
              $v = Configure::read('SecureGet');
    
              if($v)
              {
                   $local = array('securityLog', 'redirectFail', 'flashKey', 'flasMessage');
    
                   foreach($local as $value)
                   {
                        if(isset($v[$value]))
                             $this->{$value} = $v[$value];
                   }
              }
         }
    
         /* generate and store the hash key into the session if not present */
              
         function _generateHashKey()
         {
              $this->Session->write('SecureGet.hashKey', sha1(CAKE_SESSION_STRING.mt_rand()));
         }
                   
         /* retreive the hashKey from session (if there) */
         
         function _getHashKey()
         {
         	    $hashKey = $this->Session->read('SecureGet.hashKey');
         	    
         	    return  !$hashKey ? CAKE_SESSION_STRING : $hashKey;
         }
    }
    ?>

The function to "help" generating the links (in my case in
app_helper.php)

::

    
    class AppHelper extends Helper {
    	
    	  /* build a link using sha1 hashing and parameters */
    
        function buildSecureLinkArgs()
        {               	  
    	  	  $lview   = ClassRegistry::getObject('view');
    	  	  $hashKey = $lview->loaded['session']->read('SecureGet.hashKey');    	    	  
    	  	          
            if(!$hashKey)
                 $hashKey = CAKE_SESSION_STRING;
    
    		    $args    = func_get_args();
        	  $lid     = implode('', $args);  
        	               
        	  $args[]  =  sha1($hashKey.$lid);        
            return implode('/', $args);    
        }	
    }

Usage:

In a controller, you use it the Security component

::

    
    class UsersController extends AppController {
    
    	var $name           = 'Users';
    	var $helpers        = array('Html', 'Form');
    	var $components     = array('SecureGet');
    
            function beforeFilter()
            {
      	   $this->SecureGet->secureActions('edit');
            }
    
            function edit($id, $type)
            {
               /* rest of the code here */
            }
    }

Now in a menu, or a view the links can be generated as

::

    
            echo $html->link('Edit', '/users/edit/'.$html->buildSecureLinkArgs(5, 'basic'));
    
           // also
    
           echo $html->link('Edit', array('controller' => 'users', 'action' => 'edit', $html->buildSecureLinkArgs(5, 'basic'))); 

The above will produce URL such as
http://127.0.0.1/cake/app/users/edit/5/basic/357e9f43c24bda2a64905a5b5
e6a47680e725c76

The parameters of the links (ie : 5/basic) are hashed with the
hash_key. The hashKey is generated on Session bases.
Missing the key or changing the 5 to 6 will fail.

The parameters you can change :

+ redirectFail: this allow you to changes the redirection in case of
  failure. By default it goes to '/', that is usually handled in the
  route
+ securityLog: this the name of the log file that will contain all the
  failure. Default is security.log into the tmp/logs folder. If empty,
  no message is logged
+ flashMessage: the message that is set in case of failure. It works
  along with the flashKey (see below)
+ flashKey: the key for the message, default is 'sec'

The parameters can be set in a beforeFilter method or into the
config.php.

Some thoughts :

+ it works even with no parameter (ie: buildSecureLinkArgs() will
  generate a key), but I have to check if that does not too simple in
  this case.
+ For ajax links, it should work but ajax are often post and the way
  it's implementing will not handle "post" request.


I am sure this can be enhanced, comments and/or suggestions are
welcome.



.. author:: francky06l
.. categories:: articles, components
.. tags:: security,Components

