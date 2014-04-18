Protect your website against CSRF attacks
=========================================

CSRF attacks take advantage of the fact that if an authenticated
client opens a page with a link , the browser will treat it as a
regular link (normal!) and send over the credentials to the website,
thus allowing the action to be performed. This component's goal is to
suppress that risk by protecting your links with a secret.
Everytime an action is authenticated and performed, the component will
automatically regenerate a new name and new value for the secret
parameter in URL.

Once this component is installed, all you have to do to protect your
website is to add the SecureAction component in your controller and
feed the property $securedActions with the actions you want to
protect.


Controller Class:
`````````````````

::

    <?php 
    class UsersController extends AppController
    {
      var $components = array('SecureAction');
    //  var $securedActions = '*'; // protect all actions of the controller
      var $securedActions = array('remove');
    
      function remove($userID)
      {
         $this->User->delete($userID);
      }
    }
    ?>

You will still have of course (I don't know how to that nicely in
CakePHP) to generate the links of the actions to be protected by using
the component method $this->SecureAction->url($originalURL); in your
controllers or $html->surl($originalURL); in your views:


Controller Class:
`````````````````

::

    <?php 
    ...
      function anything()
      {
        $url = $this->SecureAction->url('/users/delete/54');
        $this->set('deleteLink', $url);
      }
    ...
    ?>



View Template:
``````````````

::

    
    ...
    <p>Delete this user by clicking on the following link:
    <?php echo $html->link('Delete', $html->surl('/users/delete/54')); ?>
    </p>
    ...

Now the component code you will need to save in
app/controllers/components/secure_action.php:


Component Class:
````````````````

::

    <?php 
    <?php
    /** Sexy rip of: http://bakery.cakephp.org/articles/view/secureget-component */
    
    class SecureActionComponent extends Object
    {
    	var $name = 'SecureAction';
      var $components = array('Session', 'Flash');
    	var $idLength = 16;
    	var $nameLength = 4;
    
    	function startup(&$controller)
    	{
    		if (! $this->Session->check($this->name.'.name') || ! $this->Session->check($this->name.'.hashKey')) {
    			$this->regenerate();
    		}
    
    		/** Authenticate this action if necessary */
    		$this->__action = strtolower($controller->action);
    		if (! empty($controller->securedActions)) {
    			if ($controller->securedActions == '*' || in_array($this->__action, $controller->securedActions)) {
    				/** Auth required */
    				$rv = $this->auth($controller->params['url']['url']);
    				if ($rv == false) {
    					/** Sets a flash message and redirect */
     					$controller->Flash->add(__("You do not have right to perform the previous action", true));
    					if (env('HTTP_REFERER') == '') {
    						$controller->redirect('/');
    					}
    					$controller->redirect(env('HTTP_REFERER'));
    				} else {
    					/** Access granted, lets regenerate the key */
    					$this->regenerate();
    				}
    			}
    		}
    	}
    
    	function regenerate()
    	{
    		$this->Session->write($this->name.'.name', $this->_generate($this->nameLength));
    		$this->Session->write($this->name.'.hashKey', $this->_generate());
    	}
    
    	/**
    	* Authenticate the given action
    	* @returns false on error, true on success
    	*/
    	function auth($url)
    	{
    		if (empty($url)) {
    			return false;
    		}
    		if ($url[0] != '/') {
    			$url = '/'.$url;
    		}
    		$url_t = explode('/', $url);
    		$key = null;
    		for ($i = 0; isset($url_t[$i]); $i++) {
    			if (! strncmp($url_t[$i], $this->Session->read($this->name.'.name').':', $this->nameLength+1)) {
    				$key = $url_t[$i];
    			}
    		}
    		if ($key == null) {
    			return false;
    		}
    
    		$url = str_replace($key, '', $url); // we remove the key from the URI
    		$lid = str_replace('/', '', $url); // we remove all slashes
    		
    		$key_t = explode(':', $key); // we isolate the key from its name
    
    		$nkey = sha1($this->Session->read($this->name.'.hashKey').$lid);
    		if ($nkey == $key_t[1]) {
    			return true;
    		}
    		return false;
    	}
    
    	/**
    	* Generate an url from the full url (/controller/action/param1:value1/etc...)
    	*/
    	function url($url)
    	{
    		$lid = str_replace('/', '', $url);
    //  		$lid = explode('/', $url);
    // 		$lid = implode('', $lid);
    		$key = sha1($this->Session->read($this->name.'.hashKey').$lid);
    		$url .= '/'.$this->Session->read($this->name.'.name').':'.$key;
    		return $url;
    	}
    
    	function _generate($length = null)
    	{
    		if (! is_n($length)) {
    			$length = $this->idLength;
    		}
    		$chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    		$max = strlen($chars)-1;
    		$string = '';
    		for ($i = 0; $i < $length; $i++) {
    			$string .= $chars[mt_rand(0, $max)];
    		}
    		return $string;
    	}
    }
    
    ?>

And finally you need to add in your app_helper.php (for simplicity, to
allow access from any Helper, like HtmlHelper):


Helper Class:
`````````````

::

    <?php 
    class AppHelper extends Helper {
    
    	/** Check SecureAction component */
    	function surl($url) {
    
    		$view =& ClassRegistry::getObject('view');
    
    		$lid = str_replace('/', '', $url);
    		$key = sha1($view->loaded['session']->read('SecureAction.hashKey').$lid);
    		$url .= '/'.$view->loaded['session']->read('SecureAction.name').':'.$key;
    
    		return $url;
    	}
    }
    ?>

Here you go, hope this component will be usefull :)

Thanks for `http://bakery.cakephp.org/articles/view/secureget-
component`_ to give me some usefull code to start working on right
away.
And thanks to the users of `http://www.lescigales.org/`_ to let me
know about the issue ;)

.. _http://bakery.cakephp.org/articles/view/secureget-component: http://bakery.cakephp.org/articles/view/secureget-component
.. _http://www.lescigales.org/: http://www.lescigales.org/

.. author:: T0aD
.. categories:: articles, components
.. tags:: security,1.2,csrf,Components

