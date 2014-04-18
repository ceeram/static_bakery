Mailchimp API Component
=======================

Mailchimp provides a nice API to their email marketing service. Here
is a very simple component that you can use to make calls to the
Mailchimp API. This component assumes that you are using PHP 5 or
higher.
To make this component work you will need to download the Mailchimp
API wrapper class (MCAPI.class.php file), and place it in app/vendors.
You can download the wrapper class here:
`http://www.mailchimp.com/api/downloads/`_

I'm using version 1.2.1 of the MCAPI wrapper class.

I've also placed my Mailchimp API key in a mail_chimp.php file in the
config directory.

Here's what that config file looks like:

::

    
    <?php
    /**
     * MailChimp api key
     */
    $config['MailChimp'] = array('apiKey' => 'YOUR API KEY GOES HERE');
    ?>

Here is my component class. It uses the magical __call method to wrap
the Mailchimp API wrapper.


Component Class:
````````````````

::

    <?php 
    class MailChimpComponent extends Object
    {
        /**
         * The MailChimp API wrapper.
         */
        var $mcapi = null;
    
        /**
         * Creates an instance of the MailChimp API wrapper class.
         */
        function __construct()
        {
    	App::import('Vendor', 'MCAPI', array('file' => 'MCAPI.class.php'));
    	$this->mcapi = new MCAPI($this->getApikey());
        }
    
        /**
         * Get the MailChimp apikey.
         */
        function getApikey()
        {
    	Configure::load('mail_chimp');
    	return Configure::read('MailChimp.apiKey');
        }
    
        /**
         * Pass the called function directly to the MailChimp API.
         */
        function __call($method, $args)
        {
    	if (!empty($this->mcapi) && method_exists($this->mcapi, $method)) {
    	    $caller = array($this->mcapi, $method);
    	    return call_user_func_array($caller, $args);
    	}
    	return null;
        }
    
        /**
         * Returns the last error code from MailChimp.
         */
        function errorCode()
        {
    	if (!empty($this->mcapi)) {
    	    return $this->mcapi->errorCode;
    	}
    	return null;
        }
    
        /**
         * Returns the last error message from MailChimp.
         */
        function errorMessage()
        {
    	if (!empty($this->mcapi)) {
    	    return $this->mcapi->errorMessage;
    	}
    	return null;
        }
    }
    ?>

Here's how you might use this in a controller.


Controller Class:
`````````````````

::

    <?php 
    class MailingListController extends AppController
    {
      var $uses = array();
      var $components = array('MailChimp');  
    
      function showLists()
      {
        $lists = $this->MailChimp->lists();
        $this->set('lists', $lists);
      }
    }
    ?>



.. _http://www.mailchimp.com/api/downloads/: http://www.mailchimp.com/api/downloads/

.. author:: mpatek
.. categories:: articles, components
.. tags:: email,mailchimp,marketing,Components

