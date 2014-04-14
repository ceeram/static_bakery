Ajax elements available anywhere
================================

by Kainchi on December 02, 2007

[p] When I have chosen to work with Cakephp it was because of all
these great features that we could use to create an ajax app easily.
However, I had some problems to manage Ajax view elements like header,
footer,... I figured out a solution that I would like to share with
the community and have feedback about. (p.s : forgive me for my
english, i'm not a native speaker)[/p][p]Here's an overview of the
problem. Ajax key feature is to smooth the user navigation by
preventing page reloading through ajax requests. Let's say that you
want to put an Ajax login into your website header. You need to be
able to use the feature anywhere on the website.[/p][p]After hours of
readings i found out that to be compliant with cakephp MVC structure
the process should be the following :[/p][p][ulist][li]1. Ajax call to
current controller. [/li][li]2. request requested action from this
controller or another controller. [/li][li]3. proceed with your
algorithm. [/li][li]4. setting the values into the view that has to be
rendered. [/li][li]5. render the view or element. [/li][li]6. end of
Ajax call. [/li][/ulist][/p][p][/p]


The problem :
~~~~~~~~~~~~~

I need the same ajax call on each page of my website. Let's take as an
example the login action.

The simple process would be :


+ 0. the user displays the login view associated with the member
  controller
+ 1. the user clicks on the connect button
+ 2. the form is submitted to login action in the member controller
+ 3. the login action performs checks over the database
+ 4. the login action sets a session variable that indicates whether
  the user is logged or not and sets a view variable that indicates
  success or not
+ 5. the login view is reloaded
+ 6. the user can now access specific pages only accessible through
  session check


Now I want to make an ajax call to login action. The ajax simple
process would be :


+ 0. the user displays the login view associated with the member
  controller
+ 1. the user clicks on the connect button
+ 2. an ajax request is sent to the login action with post data
+ 3. the login action performs checks over the database
+ 4. the login action sets a session variable that indicates whether
  the user is logged or not and sets a view variable that indicates
  success or not
+ 5. the login view is updated
+ 6. the user can now access specific pages only accessible through
  session check


What has to be noticed is that in these two case we are compliant with
the MVC model. We display the login view associated with the login
action in the member controller and we request the login action to
perform a check over the login form. Requests are performed over the
same view<->controller association.

Now we need to use the login action on every views of the website. We
want it to be ajax . The view content is updated not reloaded

The simple process would be :

Bad Practice :
``````````````


+ 0. the user displays the article view associated with the article
  controller
+ 1. the user clicks on the connect button
+ 2. an ajax request is sent to the login action in the member
  controller with post data
+ 3. the login action performs checks over the database
+ 4. the login action sets a session variable that indicates whether
  the user is logged or not and sets a view variable that indicates
  success or not
+ 5. the login view is updated
+ 6. What??? Have you just said the login view not article view!


Okay there's something wrong here. That's not the way I want things to
work. I want to update the article view of the corresponding to the
view action in the article controller but I need to request the login
action in the member controller... Wait I could use the requestAction.
So how should it work.

Good Practice :
```````````````


+ 0. the user displays the article view associated with the article
  controller
+ 1. the user clicks on the connect button
+ 2. an ajax request is sent to the article login action in the
  article controller with post data
+ 3. a requestAction is sent to the login action in the member
  controller with url sent data
+ 4. the login action in the member controller performs checks over
  the database
+ 5. the login action sets a session variable that indicates whether
  the user is logged or not and returns a variable that indicates
  success or not to the article login action
+ 6. The article login action updates the article view


The problem now is that you need to put a login action in every class,
a specific ajax view for this login action in every view directory,
etc...

I came up with a solution to ease that using :


+ 1. 1 component (Ajaxthis.php)
+ 2. 1 helper (Ajaxthis.php)
+ 3. 1 view (ajax.thtml)



The code :
~~~~~~~~~~

Ajaxthis Component
``````````````````

In your cake app components directory create ajaxthis.php

Component Class:
````````````````

::

    <?php 
    /*Using sanitize library*/
    uses('sanitize');
    /********************/
    class AjaxthisComponent extends Object
    {
    	var $controller = true;
    
    	function startup(&$controller){
    		//Instantiation du controller parent
    		$this->controller = &$controller; 
    	}
    
    	/**
    	*Public : Call to controller action for initial parameters 
    	**/
    	function initThis($ajaxCall = null,$ajaxAction = null,$ajaxParams = array()){
    		if(!empty($ajaxCall)){
    			if(!empty($ajaxAction)){
    				if(empty($ajaxParams)){
    					 if((strtolower($this->controller->params['controller'])==strtolower($ajaxCall))&&(strtolower($this->controller->params['action'])==strtolower($ajaxAction))){
    						$params = call_user_func(array(&$this->controller,$ajaxAction));
    					 }
    					 else{
    						$params = $this->controller->requestAction('/'.$ajaxCall.'/'.$ajaxAction.'/');
    					 }
    				}
    				else{
    					 if((strtolower($this->controller->params['controller'])==strtolower($ajaxCall))&&(strtolower($this->controller->params['action'])==strtolower($ajaxAction))){
    						$params = call_user_func(array(&$this->controller,$ajaxAction),$ajaxParams);
    					 }
    					 else{
    						$params = $this->controller->requestAction('/'.$ajaxCall.'/'.$ajaxAction.'/'.base64_encode(http_build_query($ajaxParams, '', '&')));
    					}
    				}
    			}
    			else{
    				if(empty($ajaxParams)){
    					 if((strtolower($this->controller->params['controller'])==strtolower($ajaxCall))&&(strtolower($this->controller->params['action'])==strtolower($ajaxAction))){
    						$params = call_user_func(array(&$this->controller,$ajaxCall),$ajaxParams);
    					 }
    					 else{
    						$params = $this->controller->requestAction('/'.$ajaxCall.'/');
    					 }
    				}
    				else{
    					 if((strtolower($this->controller->params['controller'])==strtolower($ajaxCall))&&(strtolower($this->controller->params['action'])==strtolower($ajaxAction))){
    						$params = call_user_func(array(&$this->controller,$ajaxCall),$ajaxParams);
    					 }
    					 else{
    						$params = $this->controller->requestAction('/'.$ajaxCall.'/'.base64_encode(http_build_query($ajaxParams, '', '&')));
    					}
    				}
    			}
    		}
    		if(!isset($params)){
    			return null;
    		}
    		else{
    			return $params;
    		}
    	}
    
    	/**
    	*Public : Processing ajax request and rendering
    	**/
    	function ajaxThis($ajaxCall=null,$ajaxAction=null,$ajaxViews=null,$ajaxParams=null){
    		//Decoding values
    		if(!empty($ajaxCall)){
    			$decodedAjaxCall = base64_decode($ajaxCall);
    		}
    		else{
    			$decodedAjaxCall = null;
    		}
    		if(!empty($ajaxAction)){
    			$decodedAjaxAction = base64_decode($ajaxAction);
    		}
    		else{
    			$decodedAjaxAction = null;
    		}
    		if(!empty($this->controller->data)){
    			if(!empty($ajaxParams)){
    				$decodedAjaxParams = $this->_decodeAjaxParams($ajaxParams);
    				$decodedAjaxParams = $decodedAjaxParams + $this->controller->data;
    				$ajaxParams = $this->encodeAjaxParams($decodedAjaxParams);
    			}
    		}
    		else{
    			$decodedAjaxParams = $this->_decodeAjaxParams($ajaxParams); 
    		}
    		$decodedAjaxViews = $this->_decodeAjaxViews($ajaxViews);
    		//Processing values
    		if(!empty($decodedAjaxCall)){
    			if(empty($decodedAjaxParams)){
    				if(empty($decodedAjaxAction)){
    					if((!empty($decodedAjaxViews))&&(!is_array($decodedAjaxViews))){
    						if(strtolower($this->controller->name)==strtolower($decodedAjaxCall)){
    							$ajaxParams = call_user_func(array(&$this->controller, $decodedAjaxViews));
    						}
    						else{
    							//Requesting selected action (= view name) from selected controller
    							$ajaxParams = $this->controller->requestAction('/'.$decodedAjaxCall.'/'.$decodedAjaxViews.'/');
    						}
    					}
    					else{
    						if(strtolower($this->controller->name)==strtolower($decodedAjaxCall)){
    							$ajaxParams = call_user_func(array(&$this->controller,$decodedAjaxCall));
    						}
    						else{
    							//Requesting selected controller
    							$ajaxParams = $this->controller->requestAction('/'.$decodedAjaxCall.'/');
    						}
    					}
    				}
    				else{
    					if(strtolower($this->controller->name)==strtolower($decodedAjaxCall)){
    						$ajaxParams = call_user_func(array(&$this->controller, $decodedAjaxAction));
    					}
    					else{
    						//Requesting selected action from selected controller
    						$ajaxParams = $this->controller->requestAction('/'.$decodedAjaxCall.'/'.$decodedAjaxAction.'/');
    					}
    				}
    			}
    			else{
    				if(empty($decodedAjaxAction)){
    					if((!empty($decodedAjaxViews))&&(!is_array($decodedAjaxViews))){
    						if(strtolower($this->controller->name)==strtolower($decodedAjaxCall)){
    							$ajaxParams = call_user_func(array(&$this->controller,$decodedAjaxViews),$decodedAjaxParams);
    						}
    						else{
    							//Requesting selected action (= view name) from selected controller  with params
    							$ajaxParams = $this->controller->requestAction('/'.$decodedAjaxCall.'/'.$decodedAjaxViews.'/'.$ajaxParams);
    						}
    					}
    					else{
    						if(strtolower($this->controller->name)==strtolower($decodedAjaxCall)){
    							$ajaxParams = call_user_func(array(&$this->controller,$decodedAjaxCall),$decodedAjaxParams);
    						}
    						else{	
    							//Requesting selected controller  with params
    							$ajaxParams = $this->controller->requestAction('/'.$decodedAjaxCall.'/'.$ajaxParams);
    						}
    					}
    				}
    				else{
    					if(strtolower($this->controller->name)==strtolower($decodedAjaxCall)){
    						$ajaxParams = call_user_func(array(&$this->controller,$decodedAjaxAction),$decodedAjaxParams);
    					}
    					else{
    						//Requesting selected action from selected controller with params
    						$ajaxParams = $this->controller->requestAction('/'.$decodedAjaxCall.'/'.$decodedAjaxAction.'/'.$ajaxParams);
    					}
    				}
    			}
    		}
    		else{
    			if(empty($decodedAjaxParams)){
    				if(empty($decodedAjaxAction)){
    					if((!empty($decodedAjaxViews))&&(!is_array($decodedAjaxViews))){
    						if(strtolower($this->controller->name)==strtolower($decodedAjaxViews)){
    							$ajaxParams = call_user_func(array(&$this->controller,$decodedAjaxViews));
    						}
    						else{
    							//Requesting selected action (= view name) from selected controller
    							$ajaxParams = $this->controller->requestAction('/'.$decodedAjaxViews.'/');
    						}
    					}
    				}
    				else{
    					if(strtolower($this->controller->name)==strtolower($decodedAjaxAction)){
    						$ajaxParams = call_user_func(array(&$this->controller,$decodedAjaxAction));
    					}
    					else{
    						//Requesting selected action from selected controller
    						$ajaxParams = $this->controller->requestAction('/'.$decodedAjaxAction.'/');
    					}
    				}
    			}
    			else{
    				if(empty($decodedAjaxAction)){
    					if((!empty($decodedAjaxViews))&&(!is_array($decodedAjaxViews))){
    						if(strtolower($this->controller->name)==strtolower($decodedAjaxViews)){
    							$ajaxParams = call_user_func(array(&$this->controller,$decodedAjaxViews),$decodedAjaxParams);
    						}
    						else{
    							//Requesting selected action (= view name) from selected controller  with params
    							$ajaxParams = $this->controller->requestAction('/'.$decodedAjaxViews.'/'.$ajaxParams);
    						}
    					}
    				}
    				else{
    					if(strtolower($this->controller->name)==strtolower($decodedAjaxAction)){
    						$ajaxParams = call_user_func(array(&$this->controller,$decodedAjaxAction),$decodedAjaxParams);
    					}
    					else{
    						//Requesting selected action from selected controller with params
    						$ajaxParams = $this->controller->requestAction('/'.$decodedAjaxAction.'/'.$ajaxParams);
    					}
    				}
    			}
    		}
    		//setting the view vars (name of the element to render and ajax parameters)
    		$this->controller->set('ajaxViews',$decodedAjaxViews);
    		$this->controller->set($decodedAjaxCall.$decodedAjaxAction,$ajaxParams);
    		//rendering the view
    		$this->controller->render('ajax','ajax');
    	}
    
    	function getAjaxParams($params = null){
    		if(empty($params)){
    			return null;
    		}
    		else{
    			if(is_array($params)){
    				return $params;
    			}
    			else{
    				return ($this->_decodeAjaxParams($params));
    			}
    		}
    	}
    
    	function encodeAjaxParams($ajaxParams){
    		return base64_encode(http_build_query($ajaxParams, '', '&'));
    	}
    
    	function _decodeAjaxViews($ajaxViews){
    		$clean = new Sanitize();
    		if(empty($ajaxViews)){
    			return null;
    		}
    		else{
    			$clean = new Sanitize();
    			$ajaxViews = explode('&', base64_decode($ajaxViews));
    			if(is_array($ajaxViews)){
    				foreach($ajaxViews as &$views){
    					$views = $clean->cleanValue($views);//sanitize
    					if(!(strpos($views,'\\')===false)){
    						$views = stripslashes($views);
    					}
    				}
    				return $ajaxViews;
    			}
    			else{
    				$ajaxViews = $clean->cleanValue($ajaxViews);//sanitize
    				return array($ajaxViews => $ajaxViews);
    			}
    		}
    	}
    
    	function _decodeAjaxParams($ajaxParams){
    		if(empty($ajaxParams)){
    			return null;
    		}
    		else{
    			//if ajaxParams are given
    			parse_str(base64_decode($ajaxParams),$ajaxParams);
    			$clean = new Sanitize();
    			$clean->cleanArrayR($ajaxParams);
    			return $ajaxParams;
    		}
    	}
    
    	//normalize the case and sort an array values and keys 
    	function _normalizeArrays($arr = array()){
    		if(is_array($arr)){
    			foreach($arr as &$subArr)
    			if(is_array($subArr)){
    				foreach($subArr as &$value){
    					$value = ucwords(strtolower(trim($value)));
    				}	
    				$subArr = array_change_key_case($subArr, CASE_UPPER);
    				ksort($subArr,SORT_STRING);
    			}
    		}
    		return $arr;
    	}
    
    	//Remove empty values from any multidimensiannal array
    	function _cleanArray($p_value){
    		if (is_array ($p_value)){
    			if ( count ($p_value) == 0) {
    				$p_value = null;
    			} else {
    				foreach ($p_value as $m_key => $m_value) {
    					$p_value[$m_key] = $this->_cleanArray($m_value);
    					if (empty ($p_value[$m_key])) unset ($p_value[$m_key]);
    				}
    			}
    		} else {
    			if (empty ($p_value)) {
    				$p_value = null;
    			}
    		}
    		return $p_value;
    	}
    }
    ?>


Ajaxthis Helper
```````````````

In your cake app helper directory create ajaxthis.php

Helper Class:
`````````````

::

    <?php 
    class AjaxthisHelper extends Helper
    {
    	var $helpers = array('Ajax');
    	
    	/**
    	*Public : Call to controller action for view intial parameters (illegal, not compliant with cakePhp functionnal rules)
    	**/
    	function initThis($ajaxCall = null,$ajaxAction = null,$ajaxParams = array()){
    		$params = $this->view->controller->Ajaxthis->initThis($ajaxCall,$ajaxAction,$ajaxParams);
    		if(!isset($params)){
    			return null;
    		}
    		else{
    			return $params;
    		}
    	}
    	
    	/**
    	*Public : Returning path to dummy ajax method with given params + the name of element to render and requested params
    	**/
    	function ajaxThis($ajaxCall = null,$ajaxAction = null,$ajaxViews = null,$ajaxParams = array()){
    		if(!empty($ajaxAction)){
    			return array(
    				'url' => '/'.$this->view->controller->params['controller'].'/ajaxThis/'.base64_encode($ajaxCall).'/'.base64_encode($ajaxAction).'/'.$this->_encodeAjaxViews($ajaxViews).'/'.base64_encode(http_build_query($ajaxParams, '', '&')),
    				'update' => $this->_filterAjaxViews($ajaxViews)
    				);
    		}
    		else{
    			return array(
    				'url' => '/'.$this->view->controller->params['controller'].'/ajaxThis/'.base64_encode($ajaxCall).'/'.$this->_encodeAjaxViews($ajaxViews).'/'.base64_encode(http_build_query($ajaxParams, '', '&')),
    				'update' => $this->_filterAjaxViews($ajaxViews)
    				);
    		}	
    	}
    
    	/**
    	*Private : Encoding ajaxViews array
    	**/
    	function _encodeAjaxViews($ajaxViews){
    		$ajaxViews = $this->_setAjaxViews($ajaxViews);
    		if(!empty($ajaxViews)){
    			return base64_encode($ajaxViews);
    		}
    		else{
    			return $ajaxViews;
    		}
    	}
    	
    	/**
    	*Private : Filter ajaxViews array
    	**/
    	function _filterAjaxViews($ajaxViews){
    		$ajaxViews = $this->_setAjaxViews($ajaxViews);
    		if(strpos($ajaxViews , '&')===false){
    			if(!(strpos($ajaxViews,'\\')===false)){
    				return stripslashes($ajaxViews);
    			}
    			else{
    				return $ajaxViews;
    			}
    		}
    		else{
    			$ajaxViews = explode('&' , $ajaxViews);
    			foreach($ajaxViews as &$view){
    				$view = stripslashes($view);
    				if(!(strpos($view,'\\')===false)){
    					$view = stripslashes($view);
    					//$view=substr($view,strpos($view,'\\')+1,strlen($view));
    				}
    			}
    			return $ajaxViews;
    		}
    	}
    	
    	/**
    	*Private : set corresponding ajaxViews
    	**/
    	function _setAjaxViews($ajaxViews,$separator = '&'){
    		if (!empty($ajaxViews)){
    			if(!is_array($ajaxViews)){
    				return $ajaxViews;
    			}
    			else{
    				$commonViews = '';
    				$specificViews = '';
    				foreach($ajaxViews as $key => $view){
    					if($key == 'common'){
    						if(!is_array($view)){
    							$commonViews .= $view.$separator;
    						}
    						else{
    							foreach($view as $commonView){
    								$commonViews .= $commonView.$separator;
    							}
    						}
    					}
    					else{
    						if(strtolower($key) == strtolower($this->view->controller->params['controller'])){
    							if(!is_array($view)){
    								$specificViews .= $view.$separator;
    							}
    							else{
    								foreach($view as $action => $specificView){
    									if(strtolower($action) == strtolower($this->view->controller->params['action'])){
    										if(!is_array($specificView)){
    											$specificViews .= $specificView.$separator;
    										}
    										else{
    											foreach($specificView as $subview){
    												$specificViews .= $subview.$separator;
    											}
    										}
    									}
    								}
    							}
    						}
    					}
    				}
    				if($commonViews!=''){
    					if($specificViews!=''){
    						return substr($commonViews.$specificViews,0,strlen($commonViews.$specificViews)-1);
    					}
    					else{
    						return substr($commonViews,0,strlen($commonViews)-1);
    					}
    				}
    				else{
    					if($specificViews!=''){
    						return substr($specificViews,0,strlen($specificViews)-1);
    					}
    					else{
    						return null;
    					}
    				}
    			}
    		}
    		else{
    			return null;
    		}
    	}
    }
    ?>


AppController
`````````````
(super class)

Put a copy of app_controller.php in your app directory with following
code

Controller Class:
`````````````````

::

    <?php 
    /*Using sanitize library*/
    uses('sanitize');
    /********************/
    class AppController extends Controller {
    
    	function ajaxThis($ajaxCall=null,$ajaxAction=null,$ajaxViews=null,$ajaxParams=null){
    		$this->Ajaxthis->ajaxThis($ajaxCall,$ajaxAction,$ajaxViews,$ajaxParams);
    	}
    }
    ?>


ajax.thtml
``````````

ajax.thtml view file in any view directory

View Template:
``````````````

::

    
    <?php
    	(!isset($ajaxParams)) ? $ajaxParams = array() : $ajaxParams;
    	if(is_array($ajaxViews)){
    		foreach($ajaxViews as $ajaxView){
    			echo $this->renderElement($ajaxView,$ajaxParams); 
    		}
    	}
    	else{
    		echo $this->renderElement($ajaxViews,$ajaxParams); 
    	}
    ?>


How to use it :
~~~~~~~~~~~~~~~

create an ajax element in your elements directory

myajaxelement.thtml
```````````````````

View Template:
``````````````

::

    
    <?php
    echo $ajax->div('myajaxelement');
    	//the name of the controller containing requested method
    	$mycontroller = 'Mycontroller';
    	//requested method
    	$mymethod = 'mymethod';
    	//elements to update with ajax
    	$myajaxelements = array('common' => 'myajaxelement');
    	//view params you want to send to the method
    	//_____________________________________________________________________________________________default values_________________________________ajax values___________
    	(!isset($mycontrollermymethod )) ? $mycontrollermymethod = array('myfirstparam' => 'foo','mysecondparam'=> 'bar') : $mycontrollermymethod;
    	//call ajaxThis to build the path to requested method according to the current view 
    	$ajaxRequest = $ajaxthis->ajaxThis($mycontroller,$mymethod,$myajaxelements,$mycontrollermymethod);
    	//my ajax request
    	echo $ajax->div('myelement',array('onclick' => $ajax->remoteFunction(array('update' => $ajaxRequest['update'], 'url' => $ajaxRequest['url']))));
    		//whatever
    		$myfirstparam = $mycontrollermymethod['myfirstparam'];
    		$mysecondparam = $mycontrollermymethod['mysecondparam'];
    		echo $mysecondparam;
    		echo $myfirstparam;
    		
    	echo $ajax->divEnd('myelement');
    echo $ajax->divEnd('myajaxelement');
    ?>

now in mycontroller

mycontroller_controller.php
```````````````````````````

Controller Class:
`````````````````

::

    <?php 
    class MycontrollerController extends AppController {
    	var $name = 'Mycontroller';
    	var $uses = array(); 
    	
    	var $components = array('Ajaxthis');
    	var $helpers = array('Ajax','Ajaxthis');
    
    	function mymethod($params = null){
    		//base 64 decode of params array if necessary
    		$params = $this->Ajaxthis->getAjaxParams($params);
    		/*********************************************/
    		//Calling action
    		$params = $this->_myaction($params);
    		/*********************************************/
    		//Returning params array
    		return $params;
    	}
    
    	function _myaction($params = null){
    		$myfirstparam = $params['myfirstparam'];
    		$mysecondparam = $params['mysecondparam'];
    		/*********************************************/
    		//whatever
    		/*********************************************/
    		$params = array('myfirstparam' => 'zoo','mysecondparam'=> 'far');
    		//Returning params array
    		return $params;
    	}
    }
    ?>

now you can use your ajax element in any view...

Don't forget to put Ajaxthis helper and component in each view in
which you use the element.

If you read the code, you'll notice other features that i'didn't
describe properly like "initThis" method which eases the requestaction
using the same syntax as ajaxthis...


.. meta::
    :title: Ajax elements available anywhere 
    :description: CakePHP Article related to anywhere,controller,Tutorials
    :keywords: anywhere,controller,Tutorials
    :copyright: Copyright 2007 Kainchi
    :category: tutorials

