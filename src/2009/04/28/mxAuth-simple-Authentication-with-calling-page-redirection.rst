mxAuth - simple Authentication with calling page redirection
============================================================

by medianetix on April 28, 2009

Using Auth and ACL to protect your pages may be more effort than the
rest of your business logic. For these cases Steve Oliveira developed
his component obAuth for cake v1.1
[url]http://bakery.cakephp.org/articles/view/obauth-simple-
authentication[/url]. It was really simple to use and i loved it.
Unfortunately with version 1.2 there are some problems (beforeFilter()
vs. startup()). So I modified his component for v1.2. Adding
redirection to the calling page made it more suitable to my needs. And
I renamed lock() to allow() for personal reasons ;-) A tutorial can be
found following this link. The component was tested with cake
1.2.2.8120.


Component Class:
````````````````

::

    <?php 
    
    /**
     *  component mxAuth
     *     
     *  simple Authentication and Authorisation for cakephp v1.2 
     *  modified version of component obAuth for cake v1.1
     *  2009-04-28 by Heiko Thurat, 
     *  original version (obAuth) from Steve Oliveira - thanks for the great work! 
     *  
     *  improvements: 
     *  - works with cakephp version 1.2 (beforeFilter() problems)
     *  - redirects to calling page after successfull login
     *  - lock() renamed to allow() (confused me)
     *  
     */ 
    
    class mxAuthComponent extends Object 
    {
    
    	/* Component config variables */
    	var $user_model = "User";
    	var $group_model = "Group";
    	var $user_fields = array('id' => 'id', 'username' => 'username', 'password' => 'password', 'group_id' => 'group_id');
    	var $group_fields = array('id' => 'id', 'name' => 'name');
    	var $components = array('Session');
    	var $login_page = '/users/login'; // login action
    	var $login_redir = '/pages/loggedin'; // display this page, when logged in
    	var $logout_page = '/pages/loggedout'; // Page to redirect to when user logs out
    	var $deny_page = null; // Page to redirect if you deny access but don't want take user to login page
    	var $sesskey = "mYSEcretSySsioNKey";
    
    	/* Don't modify these variables */
    	var $last_page = null;
    	var $user = null;
    	var $controller;
    	var $initialized = 0; // new flag added to see if component is initialized
    
        // initialize instead of startup
    	function initialize(&$controller)
    	{
            if ($this->initialized) {
                return;
            }
    		$this->controller = $controller;
    		if ($this->Session->valid() &&  $this->Session->check($this->sesskey))
    		{
    			$this->user = $this->Session->read($this->sesskey);
    		}
    		$this->controller->set('mxAuth', $this->user);
    		$this->initialized = 1; // mark as initialized
    	}
    
    
        function startup(&$controller){
            $this->initialize(&$controller);
        }
    
    
    	// Method to check if user is logged. 
    	function login($data) 
    	{
    		$username = $data[$this->user_fields['username']];
    		$password = $data[$this->user_fields['password']];
    		$conditions = array($this->user_model.".".$this->user_fields['username'] => $username, $this->user_model.".".$this->user_fields['password'] => md5($password), $this->user_model.".active" => 1);
    		$user = $this->controller->{$this->user_model}->find($conditions);
    
    		if (empty($user)) {
    			return false;
    		} else {
    			$sessdata[$this->user_model]['id'] = $user[$this->user_model][$this->user_fields['id']];
    			$sessdata[$this->user_model]['username'] = $user[$this->user_model][$this->user_fields['username']];
    			$sessdata[$this->user_model]['password'] = $user[$this->user_model][$this->user_fields['password']];
    			$sessdata[$this->group_model]['id'] = $user[$this->group_model][$this->group_fields['id']];
    			$sessdata[$this->group_model]['name'] = $user[$this->group_model][$this->group_fields['name']];
    			$sessdata[$this->user_model]['login_hash'] = md5($this->sesskey . $sessdata[$this->user_model]['username'] . $sessdata[$this->user_model]['password'] . $sessdata[$this->group_model]['id']);
    			$this->Session->write($this->sesskey, $sessdata);
    
    			return true;
    		}
    	}
    
    	// Logout user and destroy cookie
    	function logout($redirect=null) 
    	{
    		$this->user = null;
    		$this->Session->delete($this->sesskey);
    		$page = (!empty($redirect)) ? $redirect : $this->logout_page;
    		$this->controller->redirect($page);
    		exit;
    	}
    
    	// Check is user is part of usergroup specified
    	// success_redir = redirection after successfull login
    	//     false:  use default login page
    	//     empty: use the default login page and add the actual url (from params["url"]["url"])
    	//     not empty: use the given path
    	function allow($groups=null, $redirect=null, $success_redir=null)	
    	{
    		$hasAccess = false;
    		// if success === false -> no add to login page
    		// if empty success -> default = url
    		// else the set
    		if (($success_redir !== FALSE) && empty($success_redir)) {
    		  $success_redir = $this->controller->params['url']['url'];
    		}
    
    		// User page tracker
    		if ($this->controller->action != "login")
    		{
    			$this->last_page = $this->controller->here;
    		}
    
    		if (!empty($this->user)) 
    		{
    			if (!empty($groups))
    			{
    				foreach ($groups as $group) 
    				{
    					if ($this->user[$this->group_model]['id'] == $group || $this->user[$this->group_model]['name'] == $group)
    						$hasAccess = true;
    				}
    			}
    			else 
    			{
                    // e.g. allow(array()) = all
    				$hasAccess = true;
    			}
    		}
    
    		if(!$hasAccess) 
    		{
                if (!empty($success_redir)){
                // adding the path/url to login_page -> they will become params
                    $login_page = $this->login_page . '/' . $success_redir;
                } else {
                    $login_page = $this->login_page;
                }
                // $redirect is the alternative login page
    			$page = (!empty($redirect)) ? $redirect : $login_page;
    			$this->controller->redirect($page);
    			exit;
    		}
    
    	}
    
    	function deny($redirect=null)
    	{
    		$page = (!empty($redirect)) ? $redirect : $this->deny_page;
    		$this->controller->redirect($page);
    		exit;
    	}
    
    	function getUserId()
    	{
    		return (!empty($this->user)) ? $this->user[$this->user_model]['id'] : false;
    	}
    
    	function getGroupId()
    	{
    		return (!empty($this->user)) ? $this->user[$this->group_model]['id'] : false;
    	}
    }
    
    /*
    -- --------------------------------------------------------
    
    -- 
    -- Table structure for table `groups`
    -- 
    
    CREATE TABLE `groups` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `name` varchar(50) NOT NULL default '',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    );
    
    -- 
    -- Dumping data for table `groups`
    -- 
    
    INSERT INTO `groups` (`id`, `name`, `created`, `modified`) VALUES (1, 'Admin', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
    INSERT INTO `groups` (`id`, `name`, `created`, `modified`) VALUES (2, 'Customer', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
    
    -- --------------------------------------------------------
    
    -- 
    -- Table structure for table `users`
    -- 
    
    CREATE TABLE `users` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `username` varchar(50) NOT NULL default '',
      `password` varchar(32) NOT NULL default '',
      `active` tinyint(1) unsigned NOT NULL default '0',
      `group_id` int(10) unsigned NOT NULL default '0',
      `created` datetime NOT NULL default '0000-00-00 00:00:00',
      `modified` datetime NOT NULL default '0000-00-00 00:00:00',
    
      `fname` varchar(50) NOT NULL,
      `lname` varchar(50) NOT NULL,
      `email` varchar(100) NOT NULL default '',
      PRIMARY KEY  (`id`),
      KEY `group_id` (`group_id`)
    );
    
    */
    
    ?>



.. author:: medianetix
.. categories:: articles, components
.. tags:: authentication,cake,mxauth,Components

