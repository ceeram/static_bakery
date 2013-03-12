

RBAuth (a spinoff from othAuth)
===============================

by %s on February 25, 2007

I want to say thanks to othman ouahbi for his wonderful Authentication
Component, this subversion is just a spin-off of othAuth. I also need
to give a big WARNING: + This component is still under heavy
development + I'm not an expert coder, nor do I have that much
experience with Cake in general. The code is still messy, and
sometimes hard-coded. The above warning is also one of the main reason
I want to post this here, I want to receive as many feedbacks from you
guys as possible to improve this Component!

::

    
    <?php
    /************************************************************************************************
    * Linkite Alpha 1																				*
    * Filename: oth_auth.php																		*
    * ----------------------------------------------------------------------------------------------*
    * This component is based on othcomponent v0.5.2. 												*
    * othAuth By othman ouahbi.																		*
    * comments, bug reports are welcome vuhoangnguyen _AT_ gmail _DOT_ com							*
    * @author yellow1912																			*
    * @version 0.1																					*
    * @license MIT																					*
    ************************************************************************************************/
    class othAuthComponent extends Object
    {
    /**
    * Constants to modify the behaviour of othAuth Component
    */
    	var $maintenance_mode;
    	var $maintenance_msg;
    	var $user_info        	   = array();
    	var $cur_url        	   = array();
    	var $login_info			   = false;
    	var $failed_auth		   = array();
    
    	// Form vars
    	var $user_login_var        = 'username';
    	var $user_passw_var        = 'passwd';
    	var $user_group_var        = 'group_id';
    	var $user_cookie_var       = 'cookie';
    	// DB vars
    	var $user_table       	   = 'users';
    	var $group_table       	   = 'groups';
    	var $user_table_id         = 'id';
    	var $user_table_login      = 'username';
    	var $user_table_passw      = 'passwd';
    	var $user_table_passwSalt  = 'passwdSalt';
    	var $user_table_realName   = 'realName';
    	var $user_table_email      = 'email';
    	var $user_table_active     = 'active';
    	var $user_table_lastLogin  = 'lastLogin';
    	var $auth_url_redirect_var = 'from';
    	var $category_model        = 'Category';
    	var $user_model       	   = 'User';
    	var $group_model           = 'Group';
    	var $permission_model      = 'Permission';
    	var $moderator_model       = 'Moderator';
    	var $history_active        = false;
    	var $history_model         = 'UserHistory';
    	/*
    	* Internals you don't normally need to edit those
    	*/
    	var $components            = array('Session','RequestHandler');
    	var $controller            = true;
    	var $redirect_page;
    	var $hashkey               = "mYpERsOnALhaSHkeY";
    	var $salt 		           = "!34%tHUo";
    	var $auto_redirect         = true;
    	var $login_page            = '/users/login';
    	var $logout_page           = '';
    	var $access_page           = '/';
    	var $noaccess_page         = "/pages/noaccess_page"; // session_flash, flash, back or a page url
    	// if no javascript then call _sha1_with_salt instead
    	var $pass_crypt_callback   = 'sha1_with_salt'; // function name
    	var $pass_crypt_callback_file = 'passwordHash'; // file where the function is declared ( in vendors )
    	var $cookie_active         = true;
    	var $cookie_lifetime       = '+1 day';
    	var $kill_old_login        = true; // when true, form can have another login with the same hash and del the old
    	var $login_limit           = false; // flag to toggle login attempts feature
    	var $login_attempts_model  = 'LoginAttempts';
    	var $login_attempts_num    = 4;
    	var $login_attempts_timeout= 2; // in minutes
    	var $login_locked_out      = '+1 day';
    
    	function startup(&$controller)
        {
           //$this->controller = &$controller;
           //pr($controller);
        }
    
        function init($auth_config = null)
    	{
    		if(is_array($auth_config) && !is_null($auth_config) && !empty($auth_config))
    		{
    			$this->login_page       = isset($auth_config['login_page']) ? $auth_config['login_page']  : 'users/login';
    			$this->logout_page      = isset($auth_config['logout_page'])? $auth_config['logout_page'] : 'users/logout';
    			$this->access_page      = isset($auth_config['access_page'])? $auth_config['access_page'] : $this->login_page;
    			$this->auto_redirect    = isset($auth_config['auto_redirect']) ? (boolean)$auth_config['auto_redirect']  : true;
    			$this->hashkey          = isset($auth_config['hashkey'])? (string) $auth_config['hashkey'] : 'mYpERsOnALhaSHkeY';
    			$this->maintenance_mode = isset($auth_config['maintenance_mode'])? (int) $auth_config['maintenance_mode']: 0;
    			$this->maintenance_msg  = isset($auth_config['maintenance_msg'])? (string) $auth_config['maintenance_msg']: "";
    		}
    		else
    		{
    			$this->login_page       = 'users/login';
    			$this->logout_page      = 'users/logout';
    			$this->auto_redirect    = true;
    			$this->hashkey          = "mYpERsOnALhaSHkeY";
    			$this->maintenance_mode = 0;
    			$this->maintenance_msg  = "";
    		}
    
    		// pass auth data to the view so it can be used by the helper
    		$this->_pass_auth_data();
    	}
    
    	// --------------These functions are supposed to be called from outside of othAuth-------------------
    	function load_user_info(){
    		$this->login_info = $this->_valid_session();
    			// Load the user info
    		$this->load_user($this->login_info);
    	}
    
    	function get_user_info ()
    	{
    		if(empty($this->user_info)){
    			$this->load_user_info();
    		}
    		return $this->user_info;
    	}
    
    	function get_user_field ($str = 'id')
    	{
    		if(empty($this->user_info)){
    			$this->load_user_info();
    		}
    		return $this->user_info[$str];
    	}
    
    	function is_guest()
    	{
    		if(empty($this->user_info)){
    			$this->load_user_info();
    		}
    		return $this->user_info['is_guest'];
    	}
    
    	function is_admin()
    	{
    		if(empty($this->user_info)){
    			$this->load_user_info();
    		}
    		return $this->user_info['is_admin'];
    	}
    
    
    	//----------------------login/logout functions------------------------------------------------------
    	// users/login users/logout
    
    	function login($params) // username,password,group
    	{
    		//$params = $params[$this->user_table];
    		if($params == null || !isset($params[$this->user_login_var]) || !isset($params[$this->user_passw_var]))
    		{
    		 	return 0;
    		}
    
    		$set_cookie = isset($params[$this->user_cookie_var]) ? (int)$params[$this->user_cookie_var] : 0;
    
    		$ret = $this->_login($params[$this->user_login_var], $params[$this->user_passw_var], $set_cookie);
    
    
    		if($ret == 1 && $this->auto_redirect == true)
    		{
    			$this->redirect($this->access_page);
    		}
    		return $ret;
    	}
    
    	function _login($login = "", $passw = "", $set_cookie = false)
    	{
    		if(!$this->_check_login_attempts())
    		{
    			return -3; // too many login attempts
    		}
    
    		if($login == "" || $passw == "")
    		{
    			return -1;
            }
    
    
    		uses('sanitize');
    		$login = Sanitize::sql($login);
    
    		$conditions = array($this->user_table_login => $login);
    		$UserModel = $this->_create_model($this->user_model);
            $row = $UserModel->find($conditions, $this->user_table_passwSalt);
    		if(!empty($row[$this->user_model]))
    			$hashed_passw = $this->_get_hash_of($passw, $row[$this->user_model][$this->user_table_passwSalt]);
    		else{
    			$this->_save_login_attempts();
    			return -2;
    		}
    
    		$conditions = array($this->user_table_login => $login,
    							$this->user_table_passw => $hashed_passw);
    		$row = $UserModel->find($conditions);
    
    		if(empty($row))
    		{
    			$this->_save_login_attempts();
    			return -2;
    		}
    		else
    		{
    			//$this->_delete_login_attempts();
    			//$row = $row[0];
    
    			if($set_cookie)
    			{
    				$this->_save_cookie($login, $passw);
    			}
    			$this->_save_session($login, $passw);
    			/*
    			// Update the last visit date to now
    			if(isset($this->user_table_lastLogin))
    			{
    				$row[$this->user_model][$this->user_table_lastLogin] = date('Y-m-d h:i:s');
    				$UserModel->id = $row[$this->user_model]['id'];
    				$res = $UserModel->saveField($this->user_table_lastLogin,$row[$this->user_model][$this->user_table_lastLogin]);
    			}
    				// 0.2.5 save history
    			if($this->history_active)
    			{
    				$this->_add_history($row);
    
    			}
    */			return 1;
    	}
    }
    
    
    	function logout ($kill_cookie = false)
        {
            $us = 'othAuth.'.$this->hashkey;
    
            if($this->Session->valid() && $this->Session->check($us))
            {
                $ses = $this->Session->read($us);
    
                if(!empty($ses) && is_array($ses))
                {
                    // two logins of different hashkeys can exist
                    if($this->hashkey == $ses[$this->user_model]['hashkey'])
                    {
                        $this->Session->del($us);
                        $this->Session->del('othAuth.frompage');
                        /*
                        $o = $this->Session->check('othAuth');
                        if( is_array( $o ) && empty( $o  ))
                        {
                            $this->Session->del('othAuth');
                        }
                        */
                        if($kill_cookie)
                        {
                            $this->_save_cookie(null,true);
                        }
                        if($this->auto_redirect == true)
                        {
                            // check if logout_page is the action where logout is called!
                            if(!empty($this->logout_page))
                            {
                                $this->redirect($this->logout_page);
                            }
                            return true;
                        }
    
                    }
                }
            }
            return false;
        }
    	function _get_hash_of($str, $salt = "")
    	{
    		vendor($this->pass_crypt_callback_file);
    		if(function_exists($this->pass_crypt_callback))
    		{
    			return call_user_func($this->pass_crypt_callback,$str, $salt);
    		}
    		return false;
    	}
    
    	//-------------------------History, session and the likes---------------------------------------------------
    	// 0.2.5
    	function _add_history(&$row)
    	{
    
    		$data[$this->history_model]['username']  = $row[$this->user_model][$this->user_table_login];
    		$data[$this->history_model]['fullname']  = $row[$this->user_model]['fullname'];
    		$data[$this->history_model]['groupname'] = $row[$this->group_model]['name'];
    		if(isset($row[$this->user_model][$this->user_table_lastLogin]))
    		{
    			$data[$this->history_model]['visitdate'] = $row[$this->user_model][$this->user_table_lastLogin];
    		}else
    		{
    			$data[$this->history_model]['visitdate'] = date('Y-m-d h:i:s');
    		}
    
    		if (!class_exists($this->history_model))
    		{
    			loadModel($this->history_model);
    		}
    		$HistoryModel =& new $this->history_model;
    		$HistoryModel->unbindAll();
    		$HistoryModel->recursive = -1;
    		$HistoryModel->save($data);
    
    	}
    
    	function _save_session($login, $passw)
    	{
    		$hk    = $this->_get_hash_of($this->hashkey.$login.$passw, $this->salt);
    		$row[$this->user_login_var] = $login;
    		$row[$this->user_passw_var] = $passw;
    		$row['login_hash'] = $hk;
    		$row['hashkey']    = $this->hashkey;
    		$this->Session->write('othAuth_'.$this->hashkey,$row);
    	}
    
    	// null, true to delete the cookie
    	function _save_cookie($login, $passw ,$del = false)
    	{	//die(pr($row));
    		if($this->cookie_active)
    		{
    			if(!$del)
    			{
    				$time   = strtotime($this->cookie_lifetime);
    				$data   = $login.'|'.$passw;
    				$data   = serialize($data);
    				$data   = $this->encrypt($data);
    				setcookie('othAuth',$data,$time,'/');
    			}else
    			{
    				setcookie('othAuth','',strtotime('-999 day'),'/');
    			}
    		}
    	}
    
    	function _read_cookie()
    	{
    		// does session exists
    		if($this->Session->valid() && $this->Session->check('othAuth_'.$this->hashkey))
    		{
    			return;
    		}
    		if($this->cookie_active && isset($_COOKIE['othAuth']))
    		{
    
    			$str = $_COOKIE['othAuth'];
    			if (get_magic_quotes_gpc())
    			{
    				$str=stripslashes($str);
    			}
    
    			$str = $this->decrypt($str);
    
    			$str = @unserialize($str);
    
    			list($login,$passw) = explode('|',$str);
    
    			$data[$this->user_login_var] = $login;
    			$data[$this->user_passw_var] = $passw;
    			$redirect_old = $this->auto_redirect;
    			$this->auto_redirect = false;
    			$ret = $this->login($data);
    			$this->auto_redirect = $redirect_old;
    
    		}
    	}
    
    	// delete attempts after a successful login
    	function _delete_login_attempts()
    	{
    		if($this->login_limit)
    		{
    			$ip = env('REMOTE_ADDR');
    
    			$Model = $this->_create_model($this->login_attempts_model);
    			$Model->del($ip);
    
    			if($this->cookie_active)
    			{
    				setcookie('othAuth_login_attempts','',time() - 31536000,'/');
    			}
    		}
    	}
    
    	function _check_login_attempts()
    	{
    		if($this->login_limit)
    		{
    			$ip = env('REMOTE_ADDR');
    
    
    			$Model = $this->_create_model($this->login_attempts_model);
    			/*
    			if (!is_numeric($this->login_locked_out))
    			{
    			$keep_for = (int) strtotime($this->login_locked_out);
    			$time   = ($keep_for > 0 ? $keep_for : 999999999);
    			}
    			else
    			{
    			$keep_for = $this->login_locked_out;
    			$time   = time() + ($keep_for > 0 ? $keep_for : 999999999);
    			}
    			*/
    
    			// delete all expired and timedout records
    			$del_sql = "DELETE FROM {$Model->useTable} WHERE expire <= NOW() AND num >= $this->login_attempts_num";
    			/*if($this->login_attempts_timeout > 0)
    			{
    			$timeout = $this->login_attempts_timeout * 60;
    			$del_sql .= " OR (UNIX_TIMESTAMP(created) > (UNIX_TIMESTAMP(NOW()) - $timeout))";
    			}*/
    			$Model->query($del_sql);
    
    			$row = $Model->find(array($this->login_attempts_model.'.ip'=>$ip));
    
    			//die("hi!");
    			if(!empty($row))
    			{
    				$num = $row[$this->login_attempts_model]['num'];
    
    				$this->login_attempts_current_num = $num;
    
    				if($num >= $this->login_attempts_num)
    				{
    					return false;
    				}
    			}else
    			{
    				$this->login_attempts_current_num = 0;
    			}
    
    			if($this->cookie_active && isset($_COOKIE['othAuth_login_attempts']))
    			{
    				$cdata = $_COOKIE['othAuth_login_attempts'];
    				if (get_magic_quotes_gpc())
    				{
    					$cdata=stripslashes($cdata);
    				}
    
    				$cdata = $this->decrypt($cdata);
    
    				$cdata = @unserialize($cdata);
    
    				$time      = $cdata['t'];
    				$num_tries = $cdata['n'];
    
    				if($num_tries >= $this->login_attempts_num)
    				{
    					return false;
    				}
    
    				if($this->login_attempts_current_num == 0 && $num_tries > 0)
    				{
    					$this->login_attempts_current_num = $num_tries;
    				}
    
    			}
    		}
    		return true;
    	}
    
    	function _save_login_attempts()
    	{
    
    		if($this->login_limit)
    		{
    			$num_tries = $this->login_attempts_current_num + 1;
    
    			//die(date("Y-m-d H:i:s",$keep_for));
    			$time = time();
    			$expire = date("Y-m-d H:i:s", $time + ($this->login_attempts_timeout * 60));
    			$ip     = env('REMOTE_ADDR');
    
    			//die(pr($expire));
    			$data[$this->login_attempts_model]['ip']     = $ip;
    			$data[$this->login_attempts_model]['num']    = $num_tries;
    			$data[$this->login_attempts_model]['expire'] = $expire;
    
    			$Model = & new $this->login_attempts_model;
    			$Model->unbindAll();
    			$Model->recursive = -1;
    
    			$Model->save($data);
    
    			if($this->cookie_active)
    			{
    				$cdata = $this->encrypt(serialize(array('t'=>time(),'n'=>$num_tries)));
    				setcookie('othAuth_login_attempts',$cdata,$time,'/');
    			}
    		}
    	}
    
    	function __not_current($page)
    	{
    		if($page == "") return false;
    
    		$c = strtolower($this->controller->name);
    		$a = strtolower($this->controller->action);
    
    		$page = strtolower($page.'/');
    
    		$c_a = $this->_handle_cake_admin($c,$a);
    
    		$not_current = strpos($page,$c_a);
    		// !== is required, $not_current might be boolean(false)
    		return ((!is_int($not_current)) || ($not_current !== 0));
    	}
    
    	function redirect($page = "",$back = false)
        {
    
            if($page == "")
                //$page = $this->redirect_page;
                $page = $this->logout_page;
    
            if(isset($this->auth_url_redirect_var))
            {
                if(!isset($this->controller->params['url'][$this->auth_url_redirect_var]))
                {
    
                    if($back == true)
                    {
                        $frompage = !isset($this->controller->params['url']['url'])? '/': '/'.$this->controller->params['url']['url'];
                        $this->Session->write('othAuth.frompage',$frompage);
                        $page .= "?".$this->auth_url_redirect_var."=".$frompage;
                    }
                    else
                    {
                        if($this->Session->check('othAuth.frompage'))
                        {
                            $page = $this->Session->read('othAuth.frompage');
                            $this->Session->del('othAuth.frompage');
                        }
                    }
                }
    
            }
    
            if($this->__not_current($page))
            {
               if($this->__not_current($page))
               {
    
                   if ($this->RequestHandler->isAjax())
                   {
                           $this->RequestHandler->setAjax($this->controller);
                           // Brute force !
                           echo '<script type="text/javascript">window.location = "'.
                           $this->noaccess_page.
                           '"</script>';
                           exit;
                   }
                   else
                   {
                           $this->controller->redirect($page);
                           exit;
                   }
               }
            }
        }
    		// Confirms that an existing login is still valid
    	function check()
    	{
    		// Level 3 Maintenance?
    		if ($this->maintenance_mode >=3)
    			die($this->maintenance_msg);
    
    		// Uhm where are we?
    		$this->cur_url = $this->current_url();
    
    		if($this->_valid_restrictions())
    		{
    			// Attempt to retrieve the user login info from session/cookie first
    			$this->login_info = $this->_valid_session();
    			// Load the user info
    			$this->load_user($this->login_info);
    			// Once you are here you must have logged in already. That means you MUST have valid session
    
    			if(!$this->login_info)
    			{
    				$this->logout();
    				if($this->auto_redirect == true)
    				{
    					$this->redirect($this->login_page,true);
    				}
    				return false;
    			}
    
    			$permi = $this->_check_permission();
    
    			// check permissions on the current controller/action/p/a/r/a/m/s
    			if(!$permi)
    			{
    				if($this->auto_redirect == true)
    				{
    					// should probably add $this->noaccess_page too or just flash
    					$this->redirect($this->noaccess_page,true);
    				}
    				return false;
    			}
    
    			return true;
    
    		}//die("lalala");
    		return true;
    	}
    
    
    	function _valid_restrictions()
    	{
    		// Whatever you say, all admin actions MUST be checked for permission
    		if($this->is_cake_admin_action())
    		if($this->__not_current($this->login_page) && $this->__not_current($this->logout_page))
    			return true;
    
    		$isset   = isset($this->controller->othAuthRestrictions);
    		if(!$isset)
    		{
    			return true;
    		}
    		else
    		{
    			$oth_res = $this->controller->othAuthRestrictions;
    
    			if(in_array($this->cur_url['con_act_par'], $oth_res) || in_array($this->cur_url['con_act'].'*', $oth_res)
    				|| in_array($this->cur_url['controller'].'/*', $oth_res))
    			{
    				return false;
    			}
    			else
    			{
    				return true;
    			}
    		}
    	}
    
    	function _check_permission()
    	{
    		// Level 2 Maintenance? Even admin can't access any page
    		if ($this->maintenance_mode == 2)
    			die($this->maintenance_msg);
    
    		// Admin is really a super human after all
    		if($this->user_info['is_admin'])
    			return true;
    
    		if ($this->maintenance_mode == 1)
    			die($this->maintenance_msg);
    
    		$method = $this->controller->action."_auth";
    		if(method_exists($this->controller, $method)){
    			switch ($this->controller->$method()){
    				case "granted":
    					return true;
    					break;
    				case "denied":
    					return false;
    					break;
    				default:
    					break;
    				}
    		}
    
    		if(array_key_exists($this->cur_url['con_act'], $this->user_info['permission'])){
    			foreach($this->user_info['permission'][$this->cur_url['con_act']] as $value){
    				$ret = "continue";
    				if($value[0] == "_"){
    					$ret = method_exists($this, $value) ? $this->$value() : $ret;
    				}
    				else{
    					$ret = method_exists($this->controller, $value) ? $this->controller->$value() : $ret;
    				}
    				switch ($ret){
    					case "granted":
    						return true;
    						break;
    					case "denied":
    						return false;
    						break;
    					default:
    						break;
    					}
    			}
    		}
    
    		// Alrighty, do you have full permission on this controller?
    		if($this->_check_permission2(&$this->user_info['permission']))
    			return true;
    		
    		/* Remove comment if you want to use this, you will need to look at the code and figure out the models/tables needed tho
    		// Now unless you are a mod, otherwise you can't do anything, really!!!
    		if($this->user_info['is_mod'])
    		{
    			// A Mod is ONLY a Mod when he/she is in his category
    			$this->cur_cat = $this->controller->current_categories();
    			// Are we at the category you moderate?
    			if (in_array($this->cur_cat, $this->user_info['mod']['cat']))
    			{
    				// Load up their mod permissions
    				$this->user_info['mod_permissions'] = $this->load_permission(implode("," ,$this->user_info['mod']['group'][$this->cur_cat]));
    				if($this->_check_permission2(&$this->user_info['mod_permissions']))
    					return true;
    			}
    		}
    		*/
    		return false;
    	}
    
    
    	function _check_permission2(&$permission_array)
    	{
    		// Alrighty, do you have full permission on this controller?
    		if(array_key_exists($this->cur_url['con_act']."*", $permission_array))
    			return true;
    
    		// Uhm, by any chance you are granted the permission to perform this very specific action?
    		if(array_key_exists($this->cur_url['con_act_par'], $permission_array))
    			return true;
    
    		return false;
    	}
    
    	function current_url()
    	{
    		uses('inflector');
    		$cur_url = array();
    		$cur_url['controller'] = strtolower(Inflector::underscore($this->controller->name));
    		$cur_url['action'] = strtolower($this->controller->action);
    		$cur_url['here'] = strtolower($this->controller->here);
    		$cur_url['con_act'] = $this->_handle_cake_admin($cur_url['controller'], $cur_url['action']);// controller/admin_action -> admin/controller/action
    		$cur_url['param_str'] = '';
    		$cur_url['param_arr'] = array();
    		if(isset($this->controller->params['pass']))
    		{
    			$cur_url['param_arr'] = $this->controller->params['pass'];
    			$cur_url['param_str'] = implode('/',$this->controller->params['pass']);
    		}
    		$cur_url['con_act_par'] = $cur_url['con_act'].$cur_url['param_str'];
    		return $cur_url;
    	}
    
    	function _handle_cake_admin($c,$a)
    	{
    		if(defined('CAKE_ADMIN'))
    		{
    			$strpos = strpos($a,CAKE_ADMIN.'_');
    			if($strpos === 0)
    			{
    				$function = substr($a,strlen(CAKE_ADMIN.'_'));
    				if($c == null) return $function.'/';
    				$c_a = CAKE_ADMIN.'/'.$c.'/'.$function.'/';
    				return $c_a;
    			}else
    			{
    				if($c == null) return $a.'/';
    			}
    		}
    		return $c.'/'.$a.'/';
    	}
    
    	function get_safe_cake_admin_action()
    	{
    		if(defined('CAKE_ADMIN'))
    		{
    			$a = $this->controller->action;
    			$strpos = strpos($a,CAKE_ADMIN.'_');
    			if($strpos === 0)
    			{
    				$function = substr($a,strlen(CAKE_ADMIN.'_'));
    
    				return $function;
    			}
    		}
    		return $this->controller->action;
    	}
    
    	function is_cake_admin_action()
    	{
    		if(defined('CAKE_ADMIN'))
    		{
    			$a = $this->controller->action;
    			$strpos = strpos($a,CAKE_ADMIN.'_');
    			if($strpos === 0)
    			{
    				return true;
    			}
    		}
    		return false;
    	}
    
    	function _valid_session()
    	{
    		// try to read cookie
    		$this->_read_cookie();
    		$us        = 'othAuth_'.$this->hashkey;
    		// does session exists
    		if($this->Session->valid() && $this->Session->check($us))
    		{
    			$ses        = $this->Session->read($us);
    			$ret = array();
    			uses('sanitize');
    			//die(pr($ses[$this->user_table_login]));
    			$ret[$this->user_login_var] = Sanitize::sql($ses[$this->user_login_var]);
    			$ret[$this->user_passw_var] = Sanitize::sql($ses[$this->user_passw_var]);
    			$hk = $ses['login_hash'];
    			// is user invalid
    			if ($this->_get_hash_of($this->hashkey.$ret[$this->user_login_var].$ret[$this->user_passw_var], $this->salt) == $hk)
    			{return $ret;}
    		}
    		//$this->logout();
    		return false;
    	}
    	//---------------------------------Below are functions that help loading the user information--------------
    
    	function load_user($login_info)
    	{
    		// Initialize the user_info array
    		$this->user_info = array(
    			'id'	=> 0,
    			'username' => '',
    			'name' => 'Guest',
    			'email' => '',
    			'passwd' => '',
    			'is_guest' => true,
    			'is_active' => false,
    			'is_admin' => false,
    			//'is_mod' => false,
    			'lastLogin' => '',
    			'ip' => $_SERVER['REMOTE_ADDR'],
    			'permission' => array(),
    			'mod' => array('cat' => array(),
    						   'group' => array()),
    			'group' => array('id' => array(),
    							  'name' => array()),
    			//'mod_permission' => array()
    			);
    
    		if ($login_info){
    			$login = $login_info[$this->user_login_var];
    			$conditions = array($this->user_table_login => $login);
    			$UserModel = $this->_create_model($this->user_model);
    			$row = $UserModel->find($conditions);
    		}
    		else
    			$row = array();
    
    		if(!empty($row)){
    			// Load groups first
    			$this->user_info['id']= $row[$this->user_model][$this->user_table_id];
    			$this->user_info['username'] = $row[$this->user_model][$this->user_table_login];
    			$this->user_info['name'] = $row[$this->user_model][$this->user_table_realName];
    			$this->user_info['email'] = $row[$this->user_model][$this->user_table_email];
    			$this->user_info['passwd'] = $row[$this->user_model][$this->user_table_passw];
    			$this->user_info['is_guest'] = $row[$this->user_model][$this->user_table_id] == 0;
    			$this->user_info['is_active'] = $row[$this->user_model][$this->user_table_active];
    			$this->user_info['lastLogin'] = $row[$this->user_model][$this->user_table_lastLogin];
    			$this->user_info['entry_group'] = array();
    
    			$this->user_info['group'] = $this->load_group($this->user_info['id']);
    			//$group_ids = $this->load_category();
    			//$this->user_info['group']['id'] = array_diff($this->user_info['group']['id'], $this->user_info['mod']['group']);
    
    			$this->user_info['permission'] = $this->load_permission(implode(",", $this->user_info['group']['id']));
    
    			// Only after loading all user's base group can we know if he is an admin (belong to group 1)
    			$this->user_info['is_admin'] = in_array(1, $this->user_info['group']['id']);
    		}
    	}
    
    
    	// Sanitize, check, do whatever needed to clean $id before passing it to this function!!!!
    	// NOTE: we only load NORMAL groups, mod_groups are loaded bt load_category
    	function load_group($id, $except = null)
    	{
    		$sql = "SELECT groups.id, groups.name FROM groups_users, groups WHERE groups_users.user_id = $id
    				AND groups.id = groups_users.group_id";
    
    		if($except != null)
    		$sql = $sql." AND groups_users.group_id NOT IN ($except)";
    
    		$GroupModel = $this->_create_model($this->group_model);
    		$rows = $GroupModel->query($sql);
    
    		$ret = array();
    		if(!empty($rows)){
    			foreach ($rows as $row){
    				$ret['id'][] = $row['groups']['id'];
    				$ret['name'][] = $row['groups']['name'];
    			}
    		}
    		return $ret;
    
    	}
    
    	// given a string of groups
    	function load_permission($group_ids)
    	{
    		// We sort the permissions by order for 1 reason: so that the permission with
    		// highest passing-possibility will be checked first, thus reduce the load
    		$sql = "SELECT * FROM permissions
    				LEFT JOIN groups_permissions ON (groups_permissions.permission_id = permissions.id)
    				WHERE groups_permissions.group_id IN ($group_ids) ORDER BY permissions.order";
    
    		$PermissionModel = $this->_create_model($this->permission_model);
    		$rows = $PermissionModel->query($sql);
    
    		//die(pr($rows));
    
    		$ret = array();
    		//e(pr($rows));
    		if(!empty($rows)){
    			foreach ($rows as $row){
    				$permission = split("->", $row['permissions']['name']);
    				$ret[$permission[0]][] = isset($permission[1]) ? $permission[1] : "";
    			}
    		}	//e(pr($ret));
    		return $ret;
    	}
    
    	// This function loads the categories the user moderates
    	// It also checks which
    	/*function load_category()
    	{
    		$group_ids = implode(",", $this->user_info['group']['id']);
    		$sql = "SELECT * FROM categories_groups
    				WHERE group_id IN ($group_ids)";
    
    		$CategoryModel = $this->_create_model($this->category_model);
    		$rows = $CategoryModel->query($sql);
    
    		$mod_groups = array ();
    
    		if(!empty($rows)){
    			foreach ($rows as $row){
    				//e(pr($row));
    				$this->user_info['mod']['cat'][] = $row['categories_groups']['category_id'];
    				// We load it this way, just in case we may have more than 1 mod-group per category
    				$this->user_info['mod']['group'][$row['categories_groups']['category_id']][] = $row['categories_groups']['group_id'];
    				$mod_groups[] = $row['categories_groups']['group_id'];
    			}
    		}
    
    		if(count($this->user_info['mod']['cat']) > 0)
    			$this->user_info['is_mod'] = true;
    		else
    			$this->user_info['is_mod'] = false;
    
    		//return $mod_groups;
    		if (count($mod_groups) > 0)
    			return implode(",", $mod_groups);
    		else
    			return null;
    	}
    	*/
    	// passes data to the view to be used by the helper
    	function _pass_auth_data()
    	{
    
    		$data = get_object_vars($this);
    
    		unset($data['controller']);
    		unset($data['components']);
    		unset($data['Session']);
    		unset($data['RequestHandler']);
    
    		$this->controller->set('othAuth_data',$data);
    	}
    
    
    	function encrypt($string)
    	{
    		$key = $this->hashkey;
    		$result = '';
    		for($i=0; $i<strlen($string); $i++) {
    			$char = substr($string, $i, 1);
    			$keychar = substr($key, ($i % strlen($key))-1, 1);
    			$char = chr(ord($char)+ord($keychar));
    			$result.=$char;
    		}
    
    		return base64_encode($result);
    	}
    
    	//--------------------------------------------The others-------------------------------------
    	function decrypt($string)
    	{
    		$key = $this->hashkey;
    		$result = '';
    		$string = base64_decode($string);
    
    		for($i=0; $i<strlen($string); $i++) {
    			$char = substr($string, $i, 1);
    			$keychar = substr($key, ($i % strlen($key))-1, 1);
    			$char = chr(ord($char)-ord($keychar));
    			$result.=$char;
    		}
    
    		return $result;
    	}
    	function get_msg($id)
    	{
    		switch($id) {
    			case 1:
    				{
    					return "You are already logged in.";
    				}break;
    			case 0:
    				{
    					return "Please login!";
    				}break;
    			case -1:
    				{
    					return $this->user_login_var."/".$this->user_passw_var." empty";
    				}break;
    			case -2:
    				{
    					return "Wrong ".$this->user_login_var."/".$this->user_passw_var;
    				}break;
    			case -3:
    				{
    					return "Too many login attempts.";
    				}break;
    			default:
    				{
    					return "Invalid error ID";
    				}break;
    		}
    	}
    
    
    	function _create_model($object_model, $recursive = -1, $unbind = array())
    	{
    		if (!class_exists($object_model))
            {
                loadModel($object_model);
            }
            $ObjectModel =& new $object_model;
    	    $ObjectModel->recursive = $recursive;
            $ObjectModel->unbindAll($unbind);
    
    	    return $ObjectModel;
    	}
    }
    ?>


.. meta::
    :title: RBAuth (a spinoff from othAuth)
    :description: CakePHP Article related to othauth,authentication,Components
    :keywords: othauth,authentication,Components
    :copyright: Copyright 2007 
    :category: components

