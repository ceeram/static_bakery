YACCA Yet Another Cake Component for Auth
=========================================

by LazyCoder on June 11, 2007

A simple but powerful Cake Auth Component
When I have finished my first application I had the necessity to
create an authorization system to control user access. I have looked
around and founded some very good system especially in the bakery
repository. But in my mind I was thinking at something very simple for
management. My idea was to have an approach by rules. A list of
ordered rules with allow/deny actions.

`Click to see a screenshoot of a sample rules list`_

As you can see it is very intuitive how the system works:


#. The first rule allow all
#. The second rule deny access to all actions of controllers that
   start with Cake
#. The third rule allow access to Controller CakeLogin (All Actions)
#. The fourth allow only the action changepwd of controller CakeUsers
#. And last rule allow access to welcome page.

To accomplish this task we need four tables

#. users: define who access the system
#. groups: defines the roles
#. users_groups: defines the relations between users and groups
#. rules: defines the set of rules for one group

ER Diagram `Click to see the ER diagram`_

Models
file: cake_user.php

PHP Snippet:
````````````

::

    <?php 
    /*
    ** cake_user.php
    ** users model
    */
    class CakeUser extends AppModel {
      var $name = 'CakeUser';  
      var $useDbConfig = 'cakeauth'; 
    
      var $validate = Array(
            'login'  => VALID_NOT_EMPTY,
            'username' => VALID_NOT_EMPTY,
            'password' => VALID_NOT_EMPTY
          );
    
      var $hasAndBelongsToMany = array(
            'CakeGroup' => array(
              'className' => 'CakeGroup',
              'joinTable' => 'users_groups',
              'foreignKey' => 'user_id',
              'associationForeignKey' => 'group_id'
            )
          );
    
      // Called from cake_login controller
      // to get user data
      function getAclData( $login='zxcjgjhsw', $passwd='') {
        $this->recursive = 1;
        $data = $this->find("login = '$login' AND passwd = '$passwd'");
        if( $data ) {
          $data['CakeUser']['group_id'] = '(-1,0';
          if( $data['CakeGroup'] ) {
            foreach( $data['CakeGroup'] as $group)
              $data['CakeUser']['group_id'] .= ',' . $group['id'];
          }
          $data['CakeUser']['group_id'] .= ')';
        }
        return $data;
      }
    }
    ?>

file: cake_group.php

PHP Snippet:
````````````

::

    <?php 
    /*
    ** cake_group.php
    ** groups model
    */
    class CakeGroup extends AppModel {
      var $name = 'CakeGroup';  
      var $useDbConfig = 'cakeauth'; 
    
      var $validate = Array('group'  =>  VALID_NOT_EMPTY);
    
      var $hasMany = array(
            'CakeRule' => array(
              'className' => 'CakeRule',
              'exclusive' => false,
              'dependent' => true,
              'foreignKey' => 'group_id',
              'order' => 'CakeRule.rulenum ASC'
            )
          );
    
      var $hasAndBelongsToMany = array(
            'CakeUser' => array(
              'className' => 'CakeUser',
              'joinTable' => 'users_groups',
              'foreignKey' => 'group_id',
              'associationForeignKey'=> 'user_id'
            ) 
          );
    }
    ?>

file: cake_rule.php

PHP Snippet:
````````````

::

    <?php 
    /*
    ** cake_rule.php
    ** rules model
    */
    class CakeRule extends AppModel {
      var $name          = 'CakeRule';  
      var $useDbConfig   = 'cakeauth'; 
    
      var $validate = Array('rulenum'  =>  VALID_NOT_EMPTY);
    
      var $belongsTo = array(
            'CakeGroup' => array(
              'className'  => 'CakeGroup',
              'foreignKey' => 'group_id'
            )
          );
      
      /*
       * Function now meets Cake standards and best practices
       * Thanks to Mariano Iglesias for suggestion
       */ 
      function getRules( $groupId = '(-1)') {
        $conditions = "CakeRule.group_id IN {$groupId}";
    	$fields     = 'CakeRule.rulenum, CakeRule.action, CakeRule.allow ';
    	$order      = 'CakeRule.group_id ASC, CakeGroup.security_level DESC, CakeRule.rulenum ASC';
    	$data       = $this->findAll( $conditions, $fields, $order, null, 1, 0);
    	return $data;
      }
    
    }
    ?>

Now that we have defined our models create the component to manage all
the system:

file: cake_auth.php

PHP Snippet:
````````````

::

    <?php 
    /*
    ** Some code rewritten for changes in CakeRule::getRules
    ** And in Session writing and deleting
    ** Thanks to mariano and gwoo 
    */
    class CakeAuthComponent extends object {
      var $components = array('Session');
      var $externalId = null; // id of external table for specific jobs
      var $id         = null; // id of the logged in user
      var $username   = null; // username of the logged in user
      var $login      = null; // login of the logged in user
      var $security   = null; // security_level of the logged in user
      var $groupId    = null; // group(s) assigned to the logged in user
      var $errors     = null; // error messages to be displayed
      var $lastUrl    = '/' ; // last url saved just in case of redirection
      var $cacheRules = null; // cached rules for best performance
    
      // Function to save the url that will be chained
      function saveUrl( $url ) {
        $this->Session->write('cakeAuth.lastUrl', $url);
      }
    
      // Function to Set / Get Session Vars
      function set($data='') { // Line 25
        if( $data ) {
          $this->Session->write('cakeAuth', $data);
          $this->Session->write('cakeAuth.cacheRules', serialize($this->getRules($data['group_id']))); // Line 28
          $this->Session->write('cakeAuth.noCheck',   0);
        }
        if($this->Session->check('cakeAuth') && $this->Session->valid('cakeAuth')) {
          $this->id         = $this->Session->read('cakeAuth.id');
          $this->externalId = $this->Session->read('cakeAuth.external_id');
          $this->username   = $this->Session->read('cakeAuth.username');
          $this->login      = $this->Session->read('cakeAuth.login');
          $this->security   = $this->Session->read('cakeAuth.security_level');
          $this->groupId    = $this->Session->read('cakeAuth.group_id');
          $this->lastUrl    = $this->Session->read('cakeAuth.lastUrl');
          $this->cacheRules = unserialize($this->Session->read('cakeAuth.cacheRules') . ''); //Line 39
        }
        elseif($this->Session->error()) {
          return $this->Session->error();
        }
        return ($this->id != null);
      }
        
      // Logout Clean Session
      function logout() { // Line 48
        $this->Session->del('cakeAuth');
        if($this->Session->error()) {
          return $this->Session->error();
        }
      }
        
      function _normalizeCheck($check = "") {
        $check = str_replace('/', '\/', $check);
        $check = str_replace('*', '.*', $check);
        $check = '/' . $check . '/';
        return $check;
      }
    
      function getRules( $gid=null ) {
        if(empty($this->cacheRules)) {
          loadModel("CakeRule");
          $CakeRule = new CakeRule;
          $this->cacheRules = $CakeRule->getRules( $gid );
          for($i=0; $i<count ($this->cacheRules); $i++) {
            $this->cacheRules[$i]['CakeRule']['action'] = $this->_normalizeCheck($this->cacheRules[$i]['CakeRule']['action']);
          }
        }
        return $this->cacheRules;
      }
    
      // Function to check the access for the controller / action 
      function check($controller='', $action='') {
    
        $noCheck = $this->Session->read('cakeAuth.noCheck');
        if($noCheck > 0) {
          $this->noCheck( $noCheck-- );
          return true;
        }
    
        $checkStr = "{$controller}/{$action}/";
        $allow = false;
        if($this->groupId) {
          $rules = $this->getRules($this->groupId);
          foreach( $rules as $data ) {
            $check = $data['CakeRule']['action'];
            if(preg_match($check, $checkStr, $matches))
              $allow = $data['CakeRule']['allow'];
          }
        }
        return $allow;
      } 
    
      function noCheck( $forTimes=1 ) {
        $this->Session->write('cakeAuth.noCheck', $forTimes);
      }
    
      function canDo( $checkStr = "", $debug=false ) {
        $allow = false;
        foreach( $this->cacheRules as $data ) {
          if(preg_match($data['CakeRule']['action'], $checkStr, $matches)) {
            $allow = $data['CakeRule']['allow'];
            if($debug) {
              echo "<pre>";
              echo "preg_match({$data['CakeRule']['action']}, {$checkStr}, {$matches}))\n";
              echo $allow."\n";
              echo "-------------------------------------------------------------------\n";
              echo "</pre>";
            }
          }
        }
        return $allow;
      }
    }
    ?>

The set() function at line 25 serve to set and retrieve data from
session.
As you can see at line 28 and 39 the rules where serialized and cached
to avoid to query database all the times
The logout() function at line 48 I think need no explain it deletes
session data.
The private _normalizeCheck() function is an helper function to
simplify writing of rules hiding some escaping needed by regular
expressions. This function permits you to write your rule as
Controller/* instead of /Controller\/.*/

The getRules() function simply check if rules are already cached and
if not query the database and retrieve it.
It uses the function CakeRule::getRules() defined in cake_rule.php
model file.

file: cake_rule.php

PHP Snippet:
````````````

::

    <?php 
      /*
       * Function now meets Cake standards and best practices
       * Thanks to Mariano Iglesias for suggestion
       */ 
      function getRules( $groupId = '(-1)') {
        $conditions = "CakeRule.group_id IN {$groupId}";
    	$fields     = 'CakeRule.rulenum, CakeRule.action, CakeRule.allow ';
    	$order      = 'CakeRule.group_id ASC, CakeGroup.security_level DESC, CakeRule.rulenum ASC';
    	$data       = $this->findAll( $conditions, $fields, $order, null, 1, 0);
    	return $data;
      }
    ?>

The function is declared in the model Rule becouse originally it was
more complicated but with the evolution of the system and my knowledge
of cake I could do things better.
For example load Group model instead of Rule model and perform a
findAll with conditions = "Group.id in ($groupId)"
For now I think leave all as is.
The check() function is the core of the system
First of all it verify if must perform check that you can disable with
the nocheck parameter, more on this later.
Then scan all rules and set the variable allow for the current
controller action.
It is fundamental that you scan all rules becouse of the reg expr you
can have first a more restrictive rule overriden next with a more
aimed.
The noCheck() function is very useful if you want to disable check for
a number of times for example you must use it if you use a
requestAction from an allowed controller/action to a denied
controller/action one.
For example you have defined a rule that deny access to
/options/admin_view (becouse there are values that the users must not
see) but your code need to access that from another controller to
retrieve some settings, well you can do this:
file: one_not_specified_controller.php

PHP Snippet:
````````````

::

    <?php 
    function do_something( $id ) {
    	$this->CakeAuth->nocheck( 1 );
    	$data = $this->requestAction('/options/admin_view/'. $id );
    }
    ?>

And at last the canDo function.
This function is useful if you want hide/show a link or a list of link
(a menu for example :) ) that point to specific controller/action.

How to use the system?
Add reference to CakeAuth components:
And write check code in your beforeFilter function of
app_controller.php

PHP Snippet:
````````````

::

    <?php 
      var $components = array('CakeAuth');
    ?>


PHP Snippet:
````````````

::

    <?php 
      function beforeFilter() {      
        $this->CakeAuth->set(); // Load data
        if( !$this->CakeAuth->id ) { // Not yet logged in or authenticated
          $this->CakeAuth->saveUrl( $this->here ); // Save url for redirect after logged in
          $this->redirect('/cake_login/'); // Show login page
          exit();
        }
        if ( !$this->CakeAuth->check( $this->name, $this->action) ) { // Logged but not authorized
          $this->Session->setFlash('Warning: Access denied.', null);    // Set Flash message
          $this->redirect('/'); // Redirect to home page
          exit();
        }
        $this->set('CakeAuth', $this->CakeAuth);  // Make the CakeAuth object avalaible to views
        return true;
      }
    ?>

As you can see i am too lazy to write an helper and so I have set the
CakeAuth object directly available to view.
That's all for now.
You can build your own interface system to manegement of the data
tables most of this can be scaffolded.
The only code i want to show is the login controller.

file: cake_login_controller.php

PHP Snippet:
````````````

::

    <?php 
    class CakeLoginController extends AppController {
      var $name     = 'CakeLogin';
      var $uses     = array('CakeUser');
      
      function beforeFilter() {
        /*
        ** Override control function for authentication
        ** to avoid infinite loop
        */
        return true;
      }
    
      function index() {
        $this->render('index');
      }
    
      function login() {
        $login  = $this->data['CakeUser']['login'];
        $passwd = $this->data['CakeUser']['passwd'];
        $data   = $this->CakeUser->getAclData($login, md5($passwd));
    
        if(!empty( $data ) ) {
    		$this->CakeAuth->set( $data['CakeUser'] ); 
    		$this->redirect($this->CakeAuth->lastUrl);
        }
        else {
    		$this->redirect('/cake_login/');
        }
        exit();  
    }
    
      function logout() {
        $this->CakeAuth->logout();
        $this->redirect('/');
        exit();
      }
    }
    ?>

There were some interesting comments in my blog at
`http://blog.nospace.net`_ But please, post all new comments and
suggestions here at bakery.
Thanks to all for attention.

(2007.06.09)
I have built an app for explain and managing the auth system you can
download it from here.

`http://blog.nospace.net/uploads/authsample.zip`_
In the app/sql folder you can find the script for building the sample
database.

for administration:
login: admin
password: admin

For viewing:
login: view
password: view

:)

.. _Click to see a screenshoot of a sample rules list: http://blog.nospace.net/uploads/2007/03/rules.gif
.. _http://blog.nospace.net/uploads/authsample.zip: http://blog.nospace.net/uploads/authsample.zip
.. _Click to see the ER diagram: http://blog.nospace.net/uploads/2007/03/db_diagram.gif
.. _http://blog.nospace.net: http://blog.nospace.net/?p=8#comments
.. meta::
    :title: YACCA Yet Another Cake Component for Auth
    :description: CakePHP Article related to authentication,component,Tutorials
    :keywords: authentication,component,Tutorials
    :copyright: Copyright 2007 LazyCoder
    :category: tutorials

