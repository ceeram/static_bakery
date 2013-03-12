

RBAuth (a spinoff from othAuth)
===============================

by %s on February 25, 2007

This is a brief tutorial for othAuth component spin-off, which is
called RBAuth for now (Roles based authentication) First of all, I
want to say thanks to othman ouahbi for his wonderful Authentication
Component, this subversion is just a spin-off of othAuth.
RBAuth tries to address these issues:
1. The idea (and ultimate goal) of roles base authentication is: just
like in real life, one person can take many many roles: you can be
both a son and a father, a boss at your company and a slave at home
(:p)
2. You don't need to redefine all the permissions for new groups: say
if I want have a premium user group, I can just create a premium group
and give them JUST special permissions that normal users don't have,
then I assign premium users to both normal group and premium group.
3. Another issue RBAuth tries to address: authentication using users'
defined functions
4. RBAuth also takes a quite different approach on _validRestriction :
I STRONGLY believe that all actions MUST be checked, unless you tell
RBAuth not to.
And now come the WARNINGs:
1. This component is still under heavy development. Use it at your own
risk.
2. I'm not an expert coder, nor do I have that much experience with
Cake in general. The code is still messy, and sometimes hard-coded.
The above warning is also one of the main reasons I post this
Component here, I hope I can receive as many feedbacks from you guys
as possible to improve this Component!
Ok, enough talking, let’s jump to the main content:
In your app_controller:

::

    
    var $components  = array('othAuth'); 
    // The below array contains action that does NOT need any permission checking
    var $othAuthRestrictions = 
    array('pages/display/*',
    pages/display/noaccess_page',
    'entries/index/*',
    'users/register/*',
    'users/login/*',
    'users/logout/*',
    );
    
    // are weighted against these restrictions to calculate the total allow or deny for a specific request.
    
        function beforeFilter()
        {
            $auth_conf = array(
                        'maintenance_mode'  => 0,
                        'maintenance_msg'  => 'System is down for maintenance',
                        'login_page'  => '/users/login',
                        'logout_page' => '/users/logout',
                        'access_page' => '/',
                        'hashkey'     => 'MySEcEeTHaSHKeYz',
                        'noaccess_page' => '/pages/noaccess_page'
                        );
    
              $this->othAuth->controller = &$this;
              $this->othAuth->init($auth_conf);
              $this->othAuth->check();
    
         }

Now in your app_model

::

    
    	
      // Function unbindAll for oth_auth
      // http://othy.wordpress.com/2006/06/03/unbind-all-associations-except-some/
    	 function unbindAll($params = array())
    	 {
    		foreach($this->__associations as $ass)
    		{
    			if(!empty($this->{$ass}))
    			{
    				$this->__backAssociation[$ass] = $this->{$ass};
    				if(isset($params[$ass]))
    				{
    					foreach($this->{$ass} as $model => $detail)
    					{
    					if(!in_array($model,$params[$ass]))
    						{
    						$this->__backAssociation = array_merge($this->__backAssociation, $this->{$ass});
    						unset($this->{$ass}[$model]);
    						}
    					}
    				}
    				else
    				{
    					$this->__backAssociation = array_merge($this->__backAssociation, $this->{$ass});
    					$this->{$ass} = array();
    				}
    			}
    		}
    		return true;
    	}
    

In your vendors folder, you need this file:
passwordHash.php

::

    
    function sha1_with_salt($passwd, $salt){
    		return sha1($salt . $passwd);
    	}

You need to create the following tables in order to use RBAuth
(I removed un-important fields like created, modified, etc…. I leave
only the MUST HAVE fields. So of course you can add additional fields
you need)

::

    
    CREATE TABLE `users` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `username` varchar(50) collate latin1_general_ci NOT NULL default '',
      `passwd` varchar(40) collate latin1_general_ci NOT NULL,
      `passwdSalt` varchar(5) collate latin1_general_ci NOT NULL default '',
      `email` varchar(100) collate latin1_general_ci NOT NULL default '',
      `active` tinyint(1) unsigned NOT NULL default '1',
      PRIMARY KEY  (`id`),
      UNIQUE KEY `username` (`username`),
      UNIQUE KEY `email` (`email`,`username`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=16925 ;
    
    CREATE TABLE `groups` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `name` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=11 ;
    
    CREATE TABLE `permissions` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `name` varchar(50) collate latin1_general_ci NOT NULL default '',
      `order` tinyint(3) NOT NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `name` (`name`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=40 ;
    
    
    CREATE TABLE `groups_users` (
      `user_id` int(10) unsigned NOT NULL,
      `group_id` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`user_id`,`group_id`)
    
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
    CREATE TABLE `groups_permissions` (
      `group_id` int(10) unsigned NOT NULL default '0',
      `permission_id` int(10) unsigned NOT NULL default '0',
      PRIMARY KEY  (`group_id`,`permission_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
    
    CREATE TABLE `groups_users` (
      `user_id` int(10) unsigned NOT NULL,
      `group_id` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`user_id`,`group_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

So, you can assign each user to as many groups as you want, and each
group can have as many permissions as you want.
Now here comes the most IMPORTANT things:
1. RBAuth assumes that group 1 is ADMIN group and grants that group
all permissions. This may be changed in later versions if needed.
2. The field “name

.. meta::
    :title: RBAuth (a spinoff from othAuth)
    :description: CakePHP Article related to othauth,authentication,Tutorials
    :keywords: othauth,authentication,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

