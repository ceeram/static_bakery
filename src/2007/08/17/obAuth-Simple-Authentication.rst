obAuth Simple Authentication
============================

Authenticate your users and secure your controller actions from users
belonging to certain groups. Or simply secure your controller actions
to let any authenticated user to access it. How to use:
http://bakery.cakephp.org/articles/view/121 Some features: 1. User
authentication 2. User groups supported 3. Protect action access with
1 line of code


Component Class:
````````````````

::

    <?php 
    
    class obAuthComponent extends Object 
    {
    
    	/* Component config variables */
    	var $user_model = "User";
    	var $group_model = "Group";
    	var $user_fields = array('id' => 'id', 'username' => 'username', 'password' => 'password', 'group_id' => 'group_id');
    	var $group_fields = array('id' => 'id', 'name' => 'name');
    	var $components = array('Session');
    	var $login_page = 'users/login'; // login action
    	var $logout_page = null; // Page to redirect to when user logs out
    	var $deny_page = null; // Page to redirect if you deny access but don't want take user to login page
    	var $sesskey = "mYpERsOnALhaSHkeY";
    
    	/* Don't modify these variables */
    	var $last_page = null;
    	var $user = null;
    	var $controller;
    
    	function startup(&$controller)
    	{
    		$this->controller = $controller;
    		if ($this->Session->valid() &&  $this->Session->check($this->sesskey))
    		{
    			$this->user = $this->Session->read($this->sesskey);
    		}
    		$this->controller->set('obAuth', $this->user);
    	}
    
    	// Method to check if user is logged. 
    	function login($data) 
    	{
    	
    		$username = $data["{$this->user_fields['username']}"];
    		$password = $data[$this->user_fields['password']];
    		$conditions = array($this->user_model.".".$this->user_fields['username'] => $username, $this->user_model.".".$this->user_fields['password'] => md5($password), $this->user_model.".active" => 1);
    		$user = $this->controller->{$this->user_model}->find($conditions);
    
    		if (empty($user)) {
    			return false;
    		} else {
    			$sessdata["{$this->user_model}"]['id'] = $user["{$this->user_model}"]["{$this->user_fields['id']}"];
    			$sessdata["{$this->user_model}"]['username'] = $user["{$this->user_model}"]["{$this->user_fields['username']}"];
    			$sessdata["{$this->user_model}"]['password'] = $user["{$this->user_model}"]["{$this->user_fields['password']}"];
    			$sessdata["{$this->group_model}"]['id'] = $user["{$this->group_model}"]["{$this->group_fields['id']}"];
    			$sessdata["{$this->group_model}"]['name'] = $user["{$this->group_model}"]["{$this->group_fields['name']}"];
    			$sessdata["{$this->user_model}"]['login_hash'] = md5($this->sesskey . $sessdata["{$this->user_model}"]['username'] . $sessdata["{$this->user_model}"]['password'] . $sessdata["{$this->group_model}"]['id']);
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
    	}
    
    	// Check is user is part of usergroup specified
    	function lock($groups=null, $redirect=null)	
    	{
    
    		$hasAccess = false;
    
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
    					if ($this->user["{$this->group_model}"]['id'] == $group || $this->user["{$this->group_model}"]['name'] == $group)
    						$hasAccess = true;
    				}
    			}
    			else 
    			{
    				$hasAccess = true;
    			}
    		}
    
    		if(!$hasAccess) 
    		{
    			$page = (!empty($redirect)) ? $redirect : $this->login_page;
    			$this->controller->redirect($page);
    		} 
    
    	}
    
    	function deny($redirect=null)
    	{
    		$page = (!empty($redirect)) ? $redirect : $this->deny_page;
    		$this->controller->redirect($page);
    	}
    
    	function getUserId()
    	{
    		return (!empty($this->user)) ? $this->user["{$this->user_model}"]['id'] : false;
    	}
    
    	function getGroupId()
    	{
    		return (!empty($this->user)) ? $this->user["{$this->group_model}"]['id'] : false;
    	}
    }
    
    ?>



.. author:: coeus
.. categories:: articles, components
.. tags:: authentication,component,obAuth,Components

