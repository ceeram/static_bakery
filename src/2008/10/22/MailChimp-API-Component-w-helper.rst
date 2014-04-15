MailChimp API Component w/ helper
=================================

by hapascott on October 22, 2008

MailChimp (http://www.mailchimp.com/) is an awesome service that makes
it easy for you to manage your email campaigns and integrate it into
your website. While MailChimp provides an easy embed code to add to
your website, the API they offer allows for some more advanced
integration into your website. The mailchimp component is for anyone
looking to work with the MailChimp API in Cakephp.
I wrote this component while trying to integrate Mailchimp services
into an app I was writing in Cakephp. The MailChimp API allows you to
do a lot of things like manage your campaigns, view your lists e.t.c.
I'm still working on this and so far, you can view your lists, view
the members in a list, add subcribers to lists and remove subscribers
from lists.


Before using this component
---------------------------

#. You need an account with MailChimp. Make sure you create one list
   with a few subscribers to get yourself going.
#. You need to download the MCAPI (mailchimp API)
   `http://www.mailchimp.com/api/downloads/mailchimp-api-class.zip`_.
#. Create a folder called mailchimp in /app/vendors/.
#. Inside /app/vendors/mailchimp/ put the MCAPI.class.php that was in
   the zip that you downloaded. You can toss the rest of the zip file
   (unless you want to check out the examples that they provide).

Now create /app/vendors/mailchimp.php and add the following code:

::

    <?php ini_set('include_path',ini_get('include_path').PATH_SEPARATOR . dirname(__FILE__));?>
    <?php require_once('mailchimp/MCAPI.class.php'); ?>



MailChimp Component
-------------------

#. Create the file: /app/controllers/components/mailchimp_api.php
#. Copy and past the component below into this file.
#. Make sure you edit the config info to whatever your username and
   pass is.

::

      var $_username = 'YOUR_MAILCHIMP_USERNAME';
      var $_password = 'YOUR_MAILCHIMP_PASSWORD';


Also don't forget to call the component from your controller with:

::

    var $components = array('MailchimpApi');



Component Class:
````````````````

::

    <?php 
    
    /*
    CakePHP Component for mailchimp.  
    This component is not complete.  Much more you can do with the mailchimp API;
    For full documentation check: http://www.mailchimp.com/api/rtfm/
    *
    * By Scott T. Murphy (hapascott) 2008
    */
    
    App::import('Vendor', 'mailchimp');
    
    class MailchimpApiComponent extends Object {
    
    // Configuration
    //the Username & Password you use to login to your MailChimp account
      var $_username = 'YOUR_MAILCHIMP_USERNAME';
      var $_password = 'YOUR_MAILCHIMP_PASSWORD';
        
    //Other vars
       var $apiUrl = 'http://api.mailchimp.com/1.1/';
    
    
    ///*************LISTS********************************************************/
    /***returns an array of all lists you have under your mailchimp account. *
    *
    *EXAMPLE:
    *
    Controller
    	function mc() {
    		$lists = $this->MCAPI->lists();
    		$this->set('lists', $lists); 
    	} 
    *
    View (mc.ctp)
      var_dump($lists); //to view the full array.
    *
    */
    
    function lists() {
    	$api = $this->_credentials();
    	$retval = $api->lists();
    	if (!$retval){
    				$retval = $api->errorMessage;
    		} 
    	return $retval;
    }
    
    ///*************LIST ALL MEMBERS IN A LIST*****************************************************/
    /***returns an array of all members you have under the specified mailchimp list *
    Example
    Controller
    	function mclist_view($id) {
    		$lists = $this->MailchimpApi->listMembers($id);
    		$this->set('id',$id);
    		$this->set('lists', $lists); 
    	}
    *
    View (mclist_view.ctp)
      var_dump($lists); //to view the full array.
    */
    
    function listMembers($id) {
    	
    	$api = $this->_credentials();
    	
    	$retval = $api->listMembers( $id , 'subscribed', 0, 5000 );
    	if (!$retval){
    				$retval = $api->errorMessage;
    		} 
    	return $retval;
    }
    
    ///*****ADD MEMBER TO A LIST*******************************//
    //Used to save the user's info to your subscription list.
    /*
    Example:
      $add = $this->MailchimpApi->addMembers($user_email, $id);
    	if($add) {
    		$this->Session->setFlash('Successfully added user to your list.');
    	} else {
    		$this->Session->setFlash('Oops, something went wrong.  Email was not added to your user.');
    	}
      $this->redirect(array('action'=>'mclist_view', 'id'=> $id));
    	*/
    
    function addMembers($list_id, $email, $first, $last) {
    		$api = $this->_credentials();
    		$merge_vars = array('FIRST'=> $first, 'LAST'=> $last);
    		if(empty($merge_vars)) {
    			$merge_vars = array('');
    		}
    		$retval = $api->listSubscribe($list_id, $email, $merge_vars );
    		if (!$retval){
    				$retval = $api->errorMessage;
    		} 
    		return $retval;
    }
    
    
    //****UNSUBSCRIBE OR REMOVE MEMBER FROM A LIST********************//
    //Use to remove a particular user from a list.  
    //returns true if success else return false.
    /*Example usage:
    *function mc_remove($user_email,$id) {
    	$remove = $this->MailchimpApi->remove($user_email, $id);
    	if($remove) {
    		$this->Session->setFlash('Email successfully removed from your list.');
    	} else {
    		$this->Session->setFlash('Oops, something went wrong.  Email was not removed from the list.');
    	}
           $this->redirect(array('action'=>'mclist_view', 'id'=> $id));
    }
    */
    
    function remove($user_email,$id) {
    $api = $this->_credentials();
    
    $retval = $api->listUnsubscribe($id,$user_email);
    if (!$retval){
       return false;
       exit();
    } else {
        return true;
    	exit();
    }
    
    }
    
    
    //***MailChimp Auth**/
    function _credentials() {
    	$api = new MCAPI($this->_username, $this->_password);
    	if ($api->errorCode!=''){
    		$retval = $api->errorMessage;
    		echo $retval; die;
    		exit();
    	}
    	return $api;
    }  
    
    } 
    
    
    ?>



MailChimp Helper
----------------
In addition to the above, I created a rough helper to allow me to
quickly hack up and integrate the Mailchimp into my app. I probably
will create a plugin for all this eventually but for now here it is.

Create /app/views/helpers/mailchimp.php and put following in it.

Helper Class:
`````````````

::

    <?php 
     
    
    class MailchimpHelper extends AppHelper {
    
    
    var $helpers = array ('Html');
    
    var $tags = array(
    		'ul' => '<ul%s>%s</ul>',
    		'ol' => '<ol%s>%s</ol>',
    		'li' => '<li%s>%s</li>',
    	);
    	
    /***FOR RETURNING LISTS and parsing the 'id' of a list to a particular URL****/
    	function linkedList($list, $link, $attributes = array(), $itemAttributes = array(), $tag = 'ul') {
    		if(is_string($list)) {
    		//if an error occured
    			 return $list;
    			 exit;
    		}
    		if (is_string($attributes)) {
    			$tag = $attributes;
    			$attributes = array();
    		}
    		$items = $this->__linkedListItem($list, $link);
    		return sprintf($this->tags[$tag], $this->_parseAttributes($attributes, null, ' ', ''), $items);
    	}
    	
    		function __linkedListItem($items, $link) {
    		$out = '';
    		$index = 1;
    		foreach($items as $key) {
    			$out .= '<li><a href="' . $link . $key['id']. '">' . $key['name'] . '(' . $key['member_count'] . ')' . '</a></li>';
    			$index++;
    		}
    		return $out;
    	}
    	
    /***FOR RETURNING MEMBERS FOR LIST and parsing the 'id' of a list to a particular URL****/
    	function linkedMembersList($list, $id, $link, $attributes = array(), $itemAttributes = array(), $tag = 'ul') {
    		if(is_string($list)) {
    		//if an error occured
    			 return $list;
    			 exit;
    		}
    		if (is_string($attributes)) {
    			$tag = $attributes;
    			$attributes = array();
    		}
    		$items = $this->__linkedMembersListItem($list, $id, $link);
    		return sprintf($this->tags[$tag], $this->_parseAttributes($attributes, null, ' ', ''), $items);
    	}
    	
    		function __linkedMembersListItem($items,$id, $link) {
    		$out = '';
    		$index = 1;
    		foreach($items as $key) {
    			$out .= '<li>' . $index . '    '  . $key['email'] . '<a href="' . $link . $key['email'] . '/' . $id . '"><span class="mc_remove">' . '  Remove from List  ' . '</span></a></li>';
    			$index++;
    		}
    		return $out;
    	}
    	
    
    }
    
    ?>



Example usage of component and helper in your application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Disclaimer: For obvious reasons, you need to make sure that you
restrict access to your controller and actions that use the mailchimp
API. Take proper security measures since it gives access to your
mailchimp account. The following code is just for example purposes and
does not consider authentication, escaping characters, validation...

Controller Class:
`````````````````

::

    <?php 
    <?php
    class PagesController extends AppController
    {
     var $name = 'Pages';
    var $components = array('MailchimpApi');
    var $helpers = array('Mailchimp'); 
    
    function mc() {
    	$lists = $this->MailchimpApi->lists();
    	$this->set('lists', $lists); 
    }
    
    function mclist_view($id) {
    	$lists = $this->MailchimpApi->listMembers($id);
    	$this->set('id',$id);
    	$this->set('lists', $lists); 
    }
    
    function mc_remove($user_email,$id) {
    	$remove = $this->MailchimpApi->remove($user_email, $id);
    	if($remove) {
    		$this->Session->setFlash('Email successfully removed from your list.');
    	} else {
    		$this->Session->setFlash('Oops, something went wrong.  Email was not removed from the list.');
    	}
           $this->redirect(array('action'=>'mclist_view', 'id'=> $id));
    }
    
    
    function mc_add($id) {
    	if(!empty($this->data))
    		{
    		$first = $this->data['first'];
    		$last = $this->data['last'];
    		$email = $this->data['email'];
    		$id = $this->data['id'];
    		$add = $this->MailchimpApi->addMembers($id, $email, $first, $last);
    		if($add) {
    			$this->Session->setFlash('Successfully added user to your list.  They will not be reflected in your list until the user confirms their subscription.');
    		} else {
    			$this->Session->setFlash('Oops, something went wrong.  Email was not added to your user.');
    		}
    		$this->redirect(array('action'=>'mclist_view', 'id'=> $id));
    	} else {
    	$this->set('id',$id);
    	}
    }
    
    }
    
    ?>


View mc.ctp

View Template:
``````````````

::

    <div id="mailchimp">
    <?php echo $mailchimp->linkedList($lists, '/' . $this->params['controller'] .  '/mclist_view/'); ?>
    </div>

View mclist_view.ctp

View Template:
``````````````

::

    <div id="mailchimp">
    <p><?php echo $html->link('Add New User to List', array('action' => 'mc_add', 'id' => $id)); ?></p>
    
    <?php echo $mailchimp->linkedMembersList($lists, $id, '/' . $this->params['controller'] .  '/mc_remove/'); ?>
    </div>

View mc_add.ctp

View Template:
``````````````

::

    
     <form method="post" action="">
    <label>Email</label>
     <input name="data[email]" type="text" maxlength="50" value="" />
    <br/>
    <label>First Name</label>
    <input type="text" name="data[first]" value="" /></td>
    <br/>
    <label>Last Name</label>
    <input name="data[last]" type="text" value="" /></td>
    <br/>
    <input type="hidden" name="data[id]" value="<?php echo $id; ?>" />
    <?php echo $form->end('Submit'); ?>

Okay enjoy!

.. _http://www.mailchimp.com/api/downloads/mailchimp-api-class.zip: http://www.mailchimp.com/api/downloads/mailchimp-api-class.zip

.. author:: hapascott
.. categories:: articles, components
.. tags:: Mail,newsletter,mailchimp,Components

