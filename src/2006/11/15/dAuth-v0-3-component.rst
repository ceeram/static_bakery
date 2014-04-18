dAuth v0.3 component
====================

the component for dAuth v0.3


controllers/components/d_auth.php
`````````````````````````````````
This component contains some fixes and improvements, and is compatible
with v0.2 of the dAuth system.
Changes since v0.2:

#. changed findAllByIpAdress call to findAllByIp_adress to make it
   php4 compatible
#. improved the whole getting/setting system of userdata to/from the
   session. Also, you can now set the userdata available in the view. see
   the comment about $userDataInView . (PS: passing the password to the
   view is not necessarily dangerous. the password will never be sent to
   the client unless you - the programmer - explicitly code it. The
   default value is just to be sure... (eg debug() calls...) Check the
   sample element userinfo.thtml to see how you can use it.
#. changed $this->controller = $controller; to $this->controller =
   &$controller; in the startup function. A fix needed for php4 users.
#. changed ($time/500) to ($time%500)..
#. when doing attemptLogin now checking post data first before getting
   records from the database. I also do some more checking on the
   structure of the array now
#. gave hashing function better names (stage1Hash and stage2Hash)
#. Added user-configurable error-messages
#. fixed typo (successfull -> successful)
#. Added copyright & licensing information
#. Some changes in comments etc.
#. [li]Little cosmetic changes like camelcasing variables.


Component Class:
````````````````

::

    <?php 
    /*
     * PHP versions 4 and 5
     *
     * dAuth: A secure authentication system for the cakePHP framework.
     * Copyright (c)	2006, Dieter Plaetinck
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @author			Dieter Plaetinck
     * @copyright		Copyright (c) 2006, Dieter Plaetinck
     * @version			0.3
     * @modifiedby		Dieter@be
     * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    
    class dAuthComponent extends Object
    {
    	/* ----- internal variables start here: only for devs, not users ----- */
    
    	var $controller;
    	var $components =	array('Session');
    
    	/* Will be used for storing error messages for the user. */
    	var $error = false;
    
    	/* ----- internal variables stop here ----- */
    
    	/* ----- Edit the variables and function below to suit your needs ----- */
    
    	/* set to true to include the whole array in the view, false to include nothing,
    	 * or use the array to leave out parts.
    	 */
    	var $userDataInView = array('unset'=>array('password'));
    
    	/* here you can specify where users must be redirected when certain events happen.  */
    	var $pages =		array(	'default' => '/',
    								'login_success' => '/',
    								'logout_success' => '/',
    								'logout_failure' => '/',
    								'login' => '/users/login',
    								'logout' => '/users/logout',
    								'register_success' => '/',
    								'change_password_success' => '/'
    							);
    
    	/* Here you can change the (error) messages that will be used */
    	var $messages =		array('credentials_mismatch' => 'Credentials mismatch.',
    								'bad_username' => 'Choose a better/other username',
    								'registration_success' => 'Registration successful.',
    								'bad_userdata' => 'Bad user data',
    								'unknown_error'=> 'An unknown error occurred.',
    								'no_sessiondata' => 'Needed sessiondata not found.',
    								'youre_hammering' => 'Hammering detected.  Your host has been blocked.  Try again later.',
    								'youre_blocked' => 'Your host has been blocked.  Try again later'
    								);
    	/*
    	 * the number of attempts in the period of time that is considered hammering (brute-force).
    	 */
    	var $hammerRatio = array(	'seconds' => 5,
    								'attempts' => 4
    							);
    	/*
    	 * Whether page execution should terminate when hammering is detected.
    	 */
    	var $dieHammer = true;
    	/*
    	 * Whether page execution should terminate when the host is found to be blocked.
    	 */
    	var $dieBlocked = true;
    	/*
    	 * The time a host should be blocked, in seconds.
    	 */
    	var $blockTime = 1800;
    	/*
    	 * Whether cleartext logins should be allowed.
    	 */
    	var $allowClearText = false;
    	/*
    	 * The algorithm (constant over time) that will be used to securely store passwords in the database.
    	 * If you change this, you have to change the stage1Hash javascript function too.
    	 */
    	function stage1Hash($cleartext)
    	{
    		return sha1($cleartext.$cleartext{0});
    	}
    
    	/*
    	 * The algorithm (changing over time) that will be used to securely transport passwords over the network.
    	 * If you change this, you have to change the stage2Hash javascript function too.
    	 */
    	function stage2Hash($stage1,$salt)
    	{
    		return sha1($stage1.$salt);
    	}
    
    	/* ----- Stop editing here ----- */
    
    	function startup(&$controller)
        {
    		$this->controller = &$controller;
    		if($this->userDataInView) $this->setUserData($this->getUserData());
        }
    
    	function attemptLogin($postUser = null,$ip = null)
    	{
    		$success = false;
    		$clearText = true;
    
    		$this->_cleanUpAttempts();
    		$this->_defineHost($ip);
    		$this->controller->LoginAttempt->create();
    		$this->controller->data['LoginAttempt'][]['host_id'] = $this->controller->data['Host']['id'];
    		$this->controller->LoginAttempt->save(end($this->controller->data['LoginAttempt']));
    		$this->controller->data = array_merge($this->controller->data, $this->controller->LoginAttempt->read());
    
    		$cleanHost = $this->_checkHostBehaviour();
    
    		if($cleanHost)
    		{
    			if(is_array($postUser) && !empty($postUser) && isset($postUser['User']) &&
    			isset($postUser['User']['username']) && isset($postUser['User']['password']))
    			{
    		 	  	$salt = $this->Session->read('salt');
    				$dbUser = $this->controller->User->findByUsername($postUser['User']['username']);
    
    				if(!empty($dbUser))
    	 			{
    		 			if(isset($postUser['User']['hashed_pw']) && $postUser['User']['hashed_pw'] )
        	    		{
            				$clearText = false;
            			}
            			if($clearText && $this->allowClearText)
            			{
            				if($this->stage1Hash($postUser['User']['password']) == $dbUser['User']['password'])
            				{
            					$success = true;
            				}
            			}
            			else
            			{
            				$real_hash = $this->stage2Hash($dbUser['User']['password'],$salt);
    						$submitted_hash = $postUser['User']['hashed_pw'];
            				if($real_hash == $submitted_hash)
            				{
            					$success = true;
            				}
            			}
           			}
    	       		if($success)
    	       		{
    	        		$this->_login($dbUser['User']);
    	       		}
    	       		else
    	       		{
    	       			$this->error = $this->messages['credentials_mismatch'];
    	       		}
    	       	}
    		}
            return $success;
        }
    
    	function attemptRegister($postUser = null,$ip = null)
    	{
    		$success = false;
    		$clearText = true;
    
    		if (is_array($postUser) && !empty($postUser))
    		{
    			if(!isset($postUser['User']['username']) || !$postUser['User']['username'] || $this->controller->User->findCount(array('username'=>$postUser['User']['username'])))
    			{
    				$this->error = $this->messages['bad_username'];
    			}
    			else
    			{
    				$hash ='';
    				if(isset($postUser['User']['hashed_pw']) && $postUser['User']['hashed_pw'] )
        	    	{
            			$clearText = false;
            		}
            		if($clearText && $this->allowClearText)
            		{
            			$hash = $this->stage1Hash($postUser['User']['password']);
            		}
            		else
            		{
    					$hash = $postUser['User']['hashed_pw'];
            		}
            		$this->controller->User->create();
            		$user['User']['username'] = $postUser['User']['username'];
            		$user['User']['password'] = $hash;
         			if ($this->controller->User->save($user))
                	{
                		$success = true;
                		$this->controller->flash($this->messages['registration_success'],'/');
                	}
                	else
                	{
                			$this->error = $this->messages['unknown_error'];
                	}
    			}
    		}
            else
            {
            	$this->error = $this->messages['bad_userdata'];
            }
            return $success;
    	}
    	function attemptChangePassword($postUser = null,$ip = null)
    	{
    		$success = false;
    		$clearText = true;
    
    		if(is_array($postUser) && !empty($postUser))
    	 	{
    	 		$sessionUser = $this->getUserData();
    	 		if($sessionUser)
    	 		{
    	 			if(isset($postUser['User']['hashed_pw']) && $postUser['User']['hashed_pw'] )
        	    	{
            			$clearText = false;
            		}
            		$hash ='';
            		if($clearText && $this->allowClearText)
            		{
    					$hash = $this->stage1Hash($postUser['User']['password']);
            		}
            		else
            		{
            			$hash = $postUser['User']['hashed_pw'];
           			}
    				$success = $this->controller->User->changePassword($sessionUser['id'],$hash);
           			if(!$success)
           			{
           				$this->error = $this->messages['unknown_error'];
           			}
           			else
           			{
          				/*
           				 * Update the information in the session and -possibly- the view.
           				 */
    					$this->setUserData($sessionUser);
           			}
    	 		}
    	 		else
    	 		{
    	 			$this->error = $this->messages['no_sessiondata'];
    	 		}
           	}
           	else
            {
            	$this->error = $this->messages['bad_userdata'];
            }
            return $success;
        }
        function attemptLogout()
        {
        	$success = $this->_logout();
        	return $success;
        }
    
        function _login($user = null)
        {
        	$success = false;
           	if($user)
           	{
    			$success = $this->setUserData($user);
           	}
           	return $success;
        }
    
        function _logout()
        {
        	$success = $this->setUserData(null);
           	return $success;
        }
    
        function getUserData()
        {
        	$user = $this->Session->read('User');
        	if(!is_array($user) || empty($user))
        	{
        		$user = null;
        	}
        	return $user;
        }
        function setUserData($user)
        {
        	if($user)
        	{
        		$this->Session->write('User', $user);
        		if($this->userDataInView)
        		{
        			if(is_array($this->userDataInView))
        			{
        				if(isset($this->userDataInView['not']))
        				{
        					foreach($this->userDataInView['not'] as $attr)
        					{
    							if($attr && !is_array($attr) && isset($user[$attr])) $user[$attr] = null;
        					}
        				}
        				if(isset($this->userDataInView['unset']))
        				{
        					foreach($this->userDataInView['unset'] as $attr)
        					{
    							if($attr && !is_array($attr)&& isset($user[$attr])) unset($user[$attr]);
        					}
        				}
        			}
        			$this->controller->set('User',$user);
        		}
        	}
        	else
        	{
        		$this->Session->delete('User');
        		if($this->userDataInView)
        		{
        			$this->controller->set('User',null);
        		}
        	}
    
        	return true;
     	}
    
        function link($to)
        {
        	$path = $this->pages['default'];
        	if($to && isset($this->pages[$to]))
        	{
        		$path = $this->pages[$to];
        	}
        	return $path;
        }
    
        function redirect($to)
        {
        	$this->controller->redirect($this->link($to));
        }
    
    	function newSalt()
    	{
             $salt = crc32(time());
             $this->controller->set('special_sauce',$salt);
             $this->Session->write('salt', $salt);
    	}
    
    	function _checkHostBehaviour()
    	{
    		$hammer = false;
    		$blocked = false;
    		$clean = true;
       		if($this->controller->data['Host']['ip_adress'])
    		{
    			if($this->controller->Host->isBlocked($this->controller->data, time() - $this->blockTime))
    			{
    				$blocked = true;
    			}
    			else
    			{
    				$hammer = $this->controller->Host->isHammering($this->controller->data,$this->hammerRatio);
    				if($hammer)
    				{
    					$this->controller->Host->block($this->controller->data['Host']['id']);
    				}
    			}
    			if(($hammer && $this->diehammer) || ($blocked && $this->dieblocked))
    			{
    				die();
    			}
    			else if($hammer)
    			{
    				$this->error = $this->messages['youre_hammering'];
    			}
    			else if($blocked)
    			{
    				$this->error = $this->messages['youre_blocked'];
    			}
    		}
    		if($hammer || $blocked)
    		{
    			$clean = false;
    		}
    		return $clean;
    	}
    
    	function _defineHost($ip = null)
    	{
    		if($ip)
    		{
    			$hosts = $this->controller->Host->findAllByIp_adress($ip);
    			if(is_array($hosts)&& isset($hosts[0]))
    			{
    				$this->controller->data = array_merge($this->controller->data, $hosts[0]);
    			}
    			else
    			{
    				$this->controller->Host->create();
    				$this->controller->data['Host']['ip_adress'] = $ip;
    				$this->controller->Host->save($this->controller->data['Host']);
    				$this->controller->data = array_merge($this->controller->data, $this->controller->Host->read());
    			}
    		}
    	}
    
    	function getErrorMessage()
    	{
    		return $this->error;
    	}
    
    	function _cleanUpAttempts()
    	{
    		$time = time();
    		if(!($time%500)) // do this about once in 500 times.
    		{
    			$this->controller->LoginAttempt->cleanUpExpired($time - $this->hammerRatio['seconds'] - 1);
    		}
    	}
    }?>

more info about dAuth @ `http://bakery.cakephp.org/articles/view/147`_

.. _http://bakery.cakephp.org/articles/view/147: http://bakery.cakephp.org/articles/view/147

.. author:: Dieter_be
.. categories:: articles, components
.. tags:: login,dauth,challenge response,secure,Components

