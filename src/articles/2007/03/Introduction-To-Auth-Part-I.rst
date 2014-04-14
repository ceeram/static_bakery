Introduction To Auth, Part I
============================

by nate on March 13, 2007

This is the first in a series of tutorials covering the Auth
component, released in CakePHP 1.2 (the tutorial is based on version
1.2alpha, SVN revision #4567). Part I covers theory, definition of
terms, and basic authentication.


Terms
`````

#. Auth - (capital "A") refers to the CakePHP Auth component, it's
   features, capabilities, and usage; auth (lowercase "a") encompasses
   the concepts and application of both authentication (auth-n) and
   authorization (auth-z). These two disciplines, while often
   intertwined, are separate and distinct.
#. Authentication - the process of verifying identity, i.e. making
   sure that a person or other entity (like a remote system) is who they
   claim to be. While there are many methods of varying integrity to
   accomplish this task, in the context of web applications, the most
   common is identifying by a user name or other unique identifier, and a
   password or other secret piece of information.
#. Authorization - the process of determining (a) what a user or other
   entity has permissions on within a system, or (b) whether or not a
   user has access to a specific object, entity or permission within a
   system. While authorization (auth-z) generally follows authentication
   (auth-n), it can still apply to non-authenticated users, i.e. implicit
   roles or 'guest' access.
#. Salted hash - a method for obfuscating data (i.e. passwords)
   whereby the data to be obfuscated is prefixed with a random string
   (known as the salt) then passed through a hash function, i.e. md5 or
   sha1. The effect of the salt is to make it more difficult to retrieve
   the original data in the event that the hashed data is compromised.



Disclaimers
```````````

#. Web application security is not a one-size-fits-all practice, and
   there is no such thing as a "secure" system. You must always weigh the
   security measures you implement against the ease-of-use of your
   application, and choose the balance that is most appropriate for your
   specific situation. The implementation demonstrated here and in
   subsequent tutorials represents common practices that are useful in
   some situations; yours may be one.
#. As of this writing, the Auth component is incomplete and still
   under heavy development. The implementation demonstrated and the API
   supporting it may change, and in all likelihood will. If you are
   developing a mission-critical application and/or do not have
   comprehensive unit test coverage, or if you feel the lack of a stable
   API will compromise your overall health and well-being, you are
   strongly urged to discontinue reading this tutorial, and to not use
   the Auth component in any production application, or around any
   animals or small children.



The Basics
``````````

The primary concerns of the Auth component are identity, state, and
permissions. This tutorial will cover the basics of how Auth handles
identity and state, as well some under-the-hood details of the
features it provides you automatically, to help enforce good security
practices within your application.

Startup Sequence

#. First, Auth takes no automatic action if the executing controller
   is the AppController, or if the executing controller is
   TestsController and you are not in production mode. This keeps Auth
   from interfering with the CakePHP Unit Test Suite, and prevents it
   from executing on error pages.
#. Next, Auth sets default values for certain properties if they have
   not been set in beforeFilter. These properties and values are covered
   in more detail below.
#. All incoming password data from POST ($this->data in the
   controller) is automatically hashed, using the value of
   CAKE_SESSION_STRING as the salt. CAKE_SESSION_STRING is defined in
   app/config/core.php, and should be given a unique value for each
   application. However, if other CakePHP applications are sharing the
   same user data, they should also be given the same value for
   CAKE_SESSION_STRING. For the security of your application, it is
   important that this value be kept secret. Also note that once this
   value is set for an application, it should never be changed.
#. Auth then checks to see if the current action is one that has been
   identified to it as public, and if so, discontinues further automatic
   operation.
#. If the current action is the login action, Auth checks for POST
   data, and if found, attempts to log the user in using the data
   provided. If the login is successful, the user's identity is written
   to the session and the user is redirected. If the login is not
   successful, the user is returned to the login page, and an error
   message is displayed.
#. If the current action is not the login action, Auth checks for the
   user's identity in the session, and if none is found, redirects the
   user to the login action. Before the user is redirected, the URL of
   the current action is written to the session, so that the user may be
   redirected back to it upon a successful login. If the current request
   was made via Ajax, Auth will attempt to render a special view element
   instead of redirecting.
#. Finally, Auth will attempt to determine whether the user is
   authorized to perform the requested action, and/or has permissions on
   the object(s) associated with the current request.

Please note: Auth makes heavy use of sessions and automatic
redirection, which can make debugging extremely difficult. Subsequent
tutorials will demonstrate how to use logging to debug authentication
issues, but if you are unsure if your issue is related to Auth, it is
recommended that you disable it while testing. This can be done by
setting the $enabled property to false in beforeFilter. This is also
recommended if your application requires that Auth to behave in a way
that differs from the automatic steps taken above. Those steps are
based on other parts of the Auth Component API, which you can tailor
to fit your specific needs using a minimum of code.

Properties, Defaults, and Assumptions

#. $userModel - the name of the model that represents your user data.
   In addition to using this model for login queries, Auth also bases
   several other default values off of it's name.
#. $ajaxLogin - if an Ajax request is made, and the user has not
   logged in (or the user's session has expired), the default behavior
   taken by Auth will be simply to exit. By setting this value, Auth can
   render an element to be displayed in the Ajax response. For example,
   you can set the value of this property to "ajax_session_error", and
   place the file ajax_session_error.ctp in your app/views/elements
   folder. A common use for this is to display an error indicating that
   the user's session has timed out, and a link to bring them back to the
   login page.
#. $userScope - Additional search conditions to specify when executing
   login queries, i.e. array("User.is_active" => 1).
#. $fields - an array containing the names of the 'username' and
   'password' fields of your user model (which are 'username' and
   'password' by default). If you wanted to use email addresses to login,
   and your password field is called 'passwd', you would set it to
   something like array("username" => "email", "password" => "passwd").
#. $sessionKey - The session key where the user's identity will be
   stored. Defaults to "Auth.{$userModel}", i.e. "Auth.User". You
   generally will not need to override this value.
#. $loginAction - The controller action containing the login page. By
   default, Auth uses the controller name corresponding to $userModel,
   and "login" as the action, i.e. "users/login".
#. $loginRedirect - By default, users are redirected to the referring
   page after successful login (the page which they were sent to the
   login page from). However, if no referrer data is in the session, the
   users will be redirected to the location specified by this property.
#. $loginError - The error message to be displayed on an unsuccessful
   login attempt. Often, sites will display different errors based on
   whether the user name or password was incorrect. However, this is an
   insecure approach, as it exposes information about your system to
   potential attackers. Therefore, it is not supported in Auth.
#. $autoRedirect - By default, users are automatically redirected
   after a successful login. By setting this to false, you can execute
   additional code inside your login action.
#. allow() - By default, Auth checks for a valid user identity on
   every request except the login page. Using this method, you can
   designate certain pages as "public", not requiring authentication.
   This method takes a list of action names as arguments, or no arguments
   to allow all actions in a certain controller (or in all controllers,
   if placed in AppController [this, however, is not recommended, as it
   becomes impossible to use the reciprocal deny() method to pear down
   the list of allowed actions in special cases]).
#. deny() - Removes actions from the list of allowed actions. Operates
   the same as allow() , except it requires at least one parameter.
#. hashing passwords - If any POST data is available in
   Controller::$data, Auth will examine it for password data, and if
   found, will hash it. Auth looks for password data using the $userModel
   property and the $fields property. For example, if $userModel is
   'User' and $fields['password'] is 'passwd', Auth will look for
   Controller::$data['User']['passwd']. Hashing is done with
   Security::hash(), which uses the most secure hashing method available
   on your system. Please note: If your development and production
   systems do not have the same hash methods installed, hashed data you
   create in development will not work in production, and vice-versa.



A Simple Example
````````````````

Let's start with a simple example that demonstrates the basics of
implementing authentication with the Auth component. This
implementation demonstrates the minimum necessary code to implement a
basic login system for protected pages, and takes advantage of all the
default Auth conventions.


Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController {
    
    	var $scaffold;
    
    	function beforeFilter() {
    		$this->Auth->loginRedirect = '/users';
    		$this->Auth->validate = false;
    		$this->Auth->allow('add');
    	}
    
    	function login() { }
    
    	function logout() {
    		$this->redirect($this->Auth->logout());
    	}
    }
    ?>



.. meta::
    :title: Introduction To Auth, Part I
    :description: CakePHP Article related to ,Tutorials
    :keywords: ,Tutorials
    :copyright: Copyright 2007 nate
    :category: tutorials

