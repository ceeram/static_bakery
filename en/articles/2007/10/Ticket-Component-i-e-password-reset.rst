Ticket Component - i.e. password reset
======================================

by %s on October 23, 2007

This is my implementation of a simple Ticket Component. A ticket is a
way to store a piece of data that can be retrieved and used once. The
typical example of this would be a feature to reset user passwords.
You need to open that function to a non-secure session. By using a
ticket you limit the danger to a more reasonable level. Read on and I
will explain...
A Ticket Component is a very small component that can manage simple
one-off tickets. The most common use for this kind of ticket is when
resetting passwords for users. You can email them a link containing a
hash that can only be accessed once. This limits the dangers of some
third party gaining access.

With the password example you would could compare these urls:
`www.example.com/user/recover/user@example.com/`_`www.example.com/user
/recover/b58996c504c5638798eb6b511e6f49af/`_
The hash is temporary (typically only accessible once) and is
therefore limited in the risk it poses if this link gets sidetracked.
The ticket verifies the legitimacy of the request.


Schematic usage example
~~~~~~~~~~~~~~~~~~~~~~~
Here is how you would use it (example in parenthesis):
1. Generate a ticket and store some value in it. (= a userid)
2. Use the returned hash. (= make a url containing it and email it to
the user)
3. Set up an action to receive the hash.
4. Retrieve the value in the ticket and use it. (= load data for the
user)
5. Delete the ticket.

That was the simple step-by-step. These is some example-code at the
bottom. Other uses for this type of component would be "yousendit"
functions or any other situation when you need individual urls for
specific one-off functions or even trying to secure Flash-uploads.
Flash is unable to use any real authentication so by letting the
Flash-file first request a ticket to enable an "upload slot" you can
take a step away from a totally open script.


Bring the code!
~~~~~~~~~~~~~~~
The code examples are purposefully kept simple and (possible to some)
limited to ensure that the code is readable. Feel free to alter or
extend the functionality as needed. I can suggest the addition of
further redundant "security" and support for complex data-types.

The database table. Notice the data-field. It is simple text so if you
want to store complex data you need to serialize that data and alter
the field definition to a blob or something.

::

    
    CREATE TABLE `tickets` (
      `id` int(11) NOT NULL auto_increment,
      `hash` varchar(255) default NULL,
      `data` varchar(255) default NULL,
      `created` datetime default NULL,
      `modified` datetime default NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `hashs` (`hash`)
    )



Model Class:
````````````

::

    <?php 
    <?php
    class Ticket extends AppModel
    {
    	var $name = 'Ticket';	
    }
    ?>
    ?>



Component Class:
````````````````

::

    <?php 
    <?php
    
    class TicketsComponent
    {
    	// Create a new ticket by providing the data to be stored in the ticket.
    	function set($info = null)
    	{
    		$this->garbage();
    		if ($info)
    		{
    			$ticketObj = new Ticket();
    			$data['Ticket']['hash'] = md5(time());
    			$data['Ticket']['data'] = $info;
    
    			if ($ticketObj->save($data))
    			{
    				return $data['Ticket']['hash'];
    			}
    		}
    		return false;
    	}
    	
    	// Return the value stored or false if the ticket can not be found.
    	function get($ticket = null)
    	{
    		$this->garbage();
    		if ($ticket)
    		{
    			$ticketObj = new Ticket();
    			$data = $ticketObj->findByHash($ticket);
    			if (is_array($data) && is_array($data['Ticket']))
    			{
    				// optionally auto-delete the ticket -> this->del($ticket);
    				return $data['Ticket']['data'];
    			}
    		}
    		return false;
    	}
    
    	// Delete a used ticket
    	function del($ticket = null)
    	{
    		$this->garbage();
    		if ($ticket)
    		{
    			$ticketObj = new Ticket();
    			$data = $ticketObj->findByHash($ticket);
    			if ( is_array($data) && is_array($data['Ticket']) )
    			{
    				return $data = $ticketObj->del($data['Ticket']['id']);
    			}
    		}
    		return false;
    	}
    
    	// Remove old tickets
    	function garbage()
    	{		
    		$deadline = date('Y-m-d H:i:s', time() - (24 * 60 * 60)); // keep tickets for 24h.
    		$ticketObj = new Ticket();
    		$data = $ticketObj->query('DELETE from tickets WHERE created < \''.$deadline.'\'');
    	}
    }
    
    ?>
    ?>



Example methods using the component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Finally some example code using the Ticket Component. This is just two
methods in an imaginary controller.

Controller Class:
`````````````````

::

    <?php 
    // creates a ticket and sends an email
    	function send()
    	{
    		if (!empty($this->params['data']))
    		{
    			$theUser = $this->User->findByEmail($this->params['data']['User']['email']);
    			
    			if(is_array($theUser) && is_array($theUser['User']))
    			{
    				$ticket = $this->Tickets->set($theUser['User']['email']);
    
    			    $to      = $theUser['User']['email']; // users email
    		        $subject = utf8_decode('Password reset information');
    		        $message = 'http://'.$_SERVER['SERVER_NAME'].'/'.$this->params['controller'].'/password/'.$ticket;
    		        $from    = 'noreply@example.com';
    		        $headers = 'From: ' . $from . "\r\n" .
    		           'Reply-To: ' . $from . "\r\n" .
    		           'X-Mailer: CakePHP PHP ' . phpversion(). "\r\n" .
    		           'Content-Type: text/plain; charset=ISO-8859-1';
    				
    		       	if(mail($to, $subject, utf8_decode( sprintf($this->Lang->show('recover_email'), $message) ."\r\n"."\r\n" ), $headers))
    		    	{
    					$this->set('message', 'A recovery email was sent. Check your inbox.');
    				}else{
    					// internal error, sorry
    					$this->set('message', 'Server error, please try again later.');
    				}
    			}else{
    				// no user found for adress
    				$this->set('message', 'No user with that email address');
    			}
    		}
    	}
    
    
    // uses the ticket to reset the password for the correct user.
    	function password($hash = null)
    	{
    		if ( $email = $this->Tickets->get($this->params['controller'], $hash) )
    		{
    			$authUser = $this->User->findByEmail($email);
    			if (is_array($authUser))
    			{
    				if (!empty($this->params['data']))
    				{
    					$theUser = $this->User->findById($this->params['data']['User']['id']);
    
    					if ($this->User->save($this->params['data']))
    					{
    						$this->set('message', 'Your new password was saved.');
    					}else{
    						$this->set('message', 'User could not be saved');
    					}
    					$this->Tickets->del($hash);
    					$this->redirect( '/' );
    				}
    				unset($authUser['User']['pass']);
    				$this->params['data'] = $authUser;
    				$this->render();
    				return;
    			}
    		}
    		$this->Tickets->del($hash);
    		$this->set('message', 'No hash provided');
    		$this->redirect( '/' );	
    	}
    
    ?>

Thats all. Comment if further explanation is required.

.. _www.example.com/user/recover/b58996c504c5638798eb6b511e6f49af/: http://www.example.com/user/recover/b58996c504c5638798eb6b511e6f49af/
.. _www.example.com/user/recover/user@example.com/: http://www.example.com/user/recover/user@example.com/
.. meta::
    :title: Ticket Component - i.e. password reset
    :description: CakePHP Article related to component,Ticket,Components
    :keywords: component,Ticket,Components
    :copyright: Copyright 2007 
    :category: components

