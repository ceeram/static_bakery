HTTP basic authentication with users from database
==================================================

by eimermusic on January 18, 2009

This is a few lines of code and explanations explaining how to get
HTTP Auth to check against your normal users table.
Intended audience:
â€¢ You want to provide a protected RSS feed.
â€¢ You want to provide a protected API.

Prerequisites:
â€¢ You should be familiar with the basics of the Security and Auth
Comoponents.
â€¢ You should be aware that this technique does have a slightly lower
level of security than your normal logins.
â€¢ Your application already has some form of authentication setup.
â€¢ I will asume you are using Auth Component in your App Controller
in the example.

The Cookbook does a good job of explaining how to setup simple
protection for a controller or action using HTTP basic authentication.
In these examples the usernames and passwords are all hard-coded.
Setting things up so that the authentication uses your normal user-
data (e.g. from a database) is pretty simple but not extensively
documented.

Here is all the relevant code.

Controller Class:
`````````````````

::

    <?php 
    class MyController extends AppController {
    
        var $components = array('Security','RequestHandler');
        
        function beforeFilter() { 
            if ( $this->RequestHandler->isRss() ) { 
                $this->Auth->allow('index'); 
                $this->Security->loginOptions = array( 
                    'type'=>'basic', 
                    'login'=>'authenticate', 
                    'realm'=>'MyRealm' 
                ); 
                $this->Security->loginUsers = array(); 
                $this->Security->requireLogin('index'); 
            } 
            parent::beforeFilter(); 
        } 
        
        function authenticate($args) { 
            $data[ $this->Auth->fields['username'] ] = $args['username']; 
            $data[ $this->Auth->fields['password'] ] = $this->Auth->password($args['password']); 
            if ( $this->Auth->login($data) ) { 
                return true; 
            } else { 
                $this->Security->blackHole($this, 'login'); 
                return false; 
            } 
        } 
    
        function index() {
            // this is a protected function now
        }
    }
    ?>



A quick walkthrough
~~~~~~~~~~~~~~~~~~~

I will asume that authentication is setup globally in AppController.
Naturally it is necessary to include the relevant components.



beforeFilter
````````````

+ First I check if the request is for the RSS feed. (If not the action
  authenticates like any other and renders a html view.)
+ Normal Authentication is disabled
+ loginOptions has set the "login" key containing the name of the
  method we want to handle authentication.
+ Then loginUsers is left blank (they wont be used), and login is
  required for the index action.


authenticate
````````````

+ $args contains the username and password. They are placed into a new
  array using the keys set for Auth.
+ Auth tries to login. if the login fails the request is actively
  "blackHoled".
+ Returning true of false is just for show. It is ignored by Security
  component



This is all that is needed to get started. It is not the safest
authenticaiton in the world but using SSL alongside this technique
will provide pretty good security for your API access.



.. author:: eimermusic
.. categories:: articles, snippets
.. tags:: Auth,security,login,database,HTTP,authentication,Snippets

