

OpenidAuth Component
====================

by %s on April 14, 2009

Here is a pretty basic OpenidAuth component that extends the built in
Auth component and does it's magic.
I wanted to get all the magic that you get from Auth and it turned out
to be pretty simple. I just had to override a couple functions. Here
is the component:

::

    <?php
    App::import('Component', 'Auth');
    class OpenidAuthComponent extends AuthComponent {
    
    	var $field = array('openid' => 'openid');
    
    	function startup(&$controller) {
    		$isErrorOrTests = (
    			strtolower($controller->name) == 'cakeerror' ||
    			(strtolower($controller->name) == 'tests' && Configure::read() > 0) ||
    			!in_array($controller->params['action'], $controller->methods)
    		);
    		if ($isErrorOrTests) {
    			return true;
    		}
    		if (!$this->__setDefaults()) {
    			return false;
    		}
    
    		$this->data = $controller->data;
    		$url = '';
    
    		if (isset($controller->params['url']['url'])) {
    			$url = $controller->params['url']['url'];
    		}
    		$url = Router::normalize($url);
    		$loginAction = Router::normalize($this->loginAction);
    		$isAllowed = (
    			$this->allowedActions == array('*') ||
    			in_array($controller->params['action'], $this->allowedActions)
    		);
    
    		if ($loginAction != $url && $isAllowed) {
    			return true;
    		}
    
    		if ($loginAction == $url) {
    			if (empty($controller->data) || !isset($controller->data[$this->userModel])) {
    				if (!$this->Session->check('Auth.redirect') && env('HTTP_REFERER')) {
    					$this->Session->write('Auth.redirect', $controller->referer(null, true));
    				}
    				return false;
    			}
    
    			$isValid = !empty($controller->data[$this->userModel][$this->field['openid']]);
    
    			if ($isValid) {
    				$openid = $controller->data[$this->userModel][$this->field['openid']];
    
    				$data = array(
    					$this->userModel . '.' . $this->field['openid'] => $openid
    				);
    
    				if ($this->login($data)) {
    					if ($this->autoRedirect) {
    						$controller->redirect($this->redirect(), null, true);
    					}
    					return true;
    				}
    			}
    
    			$this->Session->setFlash($this->loginError, 'default', array(), 'auth');
    			return false;
    		} else {
    			if (!$this->user()) {
    				if (!$this->RequestHandler->isAjax()) {
    					$this->Session->setFlash($this->authError, 'default', array(), 'auth');
    					$this->Session->write('Auth.redirect', $url);
    					$controller->redirect($loginAction);
    					return false;
    				} elseif (!empty($this->ajaxLogin)) {
    					$controller->viewPath = 'elements';
    					echo $controller->render($this->ajaxLogin, $this->RequestHandler->ajaxLayout);
    					$this->_stop();
    					return false;
    				} else {
    					$controller->redirect(null, 403);
    				}
    			}
    		}
    
    		if (!$this->authorize) {
    			return true;
    		}
    
    		extract($this->__authType());
    		switch ($type) {
    			case 'controller':
    				$this->object =& $controller;
    			break;
    			case 'crud':
    			case 'actions':
    				if (isset($controller->Acl)) {
    					$this->Acl =& $controller->Acl;
    				} else {
    					$err = 'Could not find AclComponent. Please include Acl in ';
    					$err .= 'Controller::$components.';
    					trigger_error(__($err, true), E_USER_WARNING);
    				}
    			break;
    			case 'model':
    				if (!isset($object)) {
    					$hasModel = (
    						isset($controller->{$controller->modelClass}) &&
    						is_object($controller->{$controller->modelClass})
    					);
    					$isUses = (
    						!empty($controller->uses) && isset($controller->{$controller->uses[0]}) &&
    						is_object($controller->{$controller->uses[0]})
    					);
    
    					if ($hasModel) {
    						$object = $controller->modelClass;
    					} elseif ($isUses) {
    						$object = $controller->uses[0];
    					}
    				}
    				$type = array('model' => $object);
    			break;
    		}
    
    		if ($this->isAuthorized($type)) {
    			return true;
    		}
    
    		$this->Session->setFlash($this->authError, 'default', array(), 'auth');
    		$controller->redirect($controller->referer(), null, true);
    		return false;
    	}
    
    	function identify($user = null, $conditions = null) {
    		if ($conditions === false) {
    			$conditions = null;
    		} elseif (is_array($conditions)) {
    			$conditions = array_merge((array)$this->userScope, $conditions);
    		} else {
    			$conditions = $this->userScope;
    		}
    		if (empty($user)) {
    			$user = $this->user();
    			if (empty($user)) {
    				return null;
    			}
    		} elseif (is_object($user) && is_a($user, 'Model')) {
    			if (!$user->exists()) {
    				return null;
    			}
    			$user = $user->read();
    			$user = $user[$this->userModel];
    		} elseif (is_array($user) && isset($user[$this->userModel])) {
    			$user = $user[$this->userModel];
    		}
    
    		if (is_array($user) && (isset($user[$this->field['openid']]) || isset($user[$this->userModel . '.' . $this->field['openid']]))) {
    
    			if (isset($user[$this->field['openid']]) && !empty($user[$this->field['openid']])) {
    				$find = array(
    					$this->userModel.'.'.$this->field['openid'] => $user[$this->field['openid']]
    				);
    			} elseif (isset($user[$this->userModel . '.' . $this->field['openid']]) && !empty($user[$this->userModel . '.' . $this->field['openid']])) {
    				$find = array(
    					$this->userModel.'.'.$this->field['openid'] => $user[$this->userModel . '.' . $this->field['openid']]
    				);
    			} else {
    				return false;
    			}
    			$model =& $this->getModel();
    			$data = $model->find(array_merge($find, $conditions), null, null, 0);
    			if (empty($data) || empty($data[$this->userModel])) {
    				return null;
    			}
    		} elseif (!empty($user) && is_string($user)) {
    			$model =& $this->getModel();
    			$data = $model->find(array_merge(array($model->escapeField() => $user), $conditions));
    
    			if (empty($data) || empty($data[$this->userModel])) {
    				return null;
    			}
    		}
    
    		if (!empty($data)) {
    			return $data[$this->userModel];
    		}
    		return null;
    	}
    
    }
    ?>

I then did the following in my app_controller. Note that the field
array contains a single value... set 'openid' => 'your db openid
field' if it's not openid.

::

    <?php
    class AppController extends Controller {
    	var $components = array('Session', 'OpenidAuth', 'RequestHandler');
    	
    	function beforeFilter() {
    		$this->OpenidAuth->loginAction = array('prefix' => null, 'controller' => 'users', 'action' => 'login', 'admin' => false);
    		$this->OpenidAuth->loginRedirect = array('prefix' => null, 'controller' => 'tags', 'action' => 'index', 'admin' => false);
    		$this->OpenidAuth->logoutRedirect = '/';
    		$this->OpenidAuth->loginError = 'Login Failed.  Please try again';
    		$this->OpenidAuth->authorize = 'controller';
    		$this->OpenidAuth->field = array('openid' => 'openid');
    	}
    }
    ?>

And this is the users_controller which requires the `OpenID Component
that CakeBaker`_ put out:

::

    <?php
    class UsersController extends AppController {
    
    	var $name = 'Users';
    	var $components = array('Openid');
    	
    	function beforeFilter() {
    		parent::beforeFilter();
    		$this->OpenidAuth->allow('login');
    	}
    
        public function login() { 
            $returnTo = 'http://'.$_SERVER['SERVER_NAME'].'/users/login';
    
            if ($this->RequestHandler->isPost()) {   
        	    $this->makeOpenIDRequest($this->data['User']['openid'], $returnTo);
            }
    
            if ($this->isOpenIDResponse()) {
                $this->handleOpenIDResponse($returnTo);
            }
        }
    
        private function makeOpenIDRequest($openid, $returnTo) {
            try {
                $this->Openid->authenticate($openid, $returnTo, 'http://'.$_SERVER['SERVER_NAME']);
            } catch (Exception $e) {
                // empty
            }
        }
    
        private function isOpenIDResponse() {
            return (count($_GET) > 1);
        }
    
        private function handleOpenIDResponse($returnTo) {
            $response = $this->Openid->getResponse($returnTo);
            $data = array('User.openid' => $response->identity_url);
            $this->OpenidAuth->login($data);
            $this->redirect($this->OpenidAuth->redirect());
        }
    
        public function logout() {
            $this->redirect($this->OpenidAuth->logout());
        }
    }
    ?>

And finally, the login view:

::

    <?php
    echo $form->create('User', array('type' => 'post', 'action' => 'login'));
    echo $form->input('openid', array('label' => 'Open ID:'));
    echo $form->end('Login');
    ?>

Seems to work pretty well over here for a start. I'll update this
article if I make more updates.

.. _OpenID Component that CakeBaker: http://cakebaker.42dh.com/downloads/openid-component-for-cakephp/
.. meta::
    :title: OpenidAuth Component
    :description: CakePHP Article related to Auth,component,openid,Components
    :keywords: Auth,component,openid,Components
    :copyright: Copyright 2009 
    :category: components

