SwiftMailer Component Tutorial
==============================

by %s on December 18, 2006

Based on Chris Corbyn's excellent Swift Mailer. This is a component
that does encapsulate it but not hide it, while it adds some nifty
features. Read on how to bake with it.


Prerequisites
~~~~~~~~~~~~~

I would still advise you read the Swift Mailer documentation here:
`http://www.swiftmailer.org/docs/`_ SwiftMailer Component isn't an
attempt to hide the already excellent class written by Chris Corbyn.
Rather, the component tries to integrate and extend it (read, Cake-
ish!).
The Swift object is still accessible through the member variable
$this->SwiftMailer->mailer.

But first things first, Swift Mailer can be found here:
`http://www.swiftmailer.org/download/`_ The version this Component is
based on is Swift-2.1.17-php4.
Download the package and extract it. put Swift.php and the Swift
folder into your vendors directory.
you should have something like:

::

    
    vendors/
            Swift/
            Swift.php

You are ready to go.


Installation
~~~~~~~~~~~~
Grab the component located here:
`http://bakery.cakephp.org/articles/view/192`_ Put it in your
components folder and save it as swift_mailer.php, not
swiftmailer.php!
If you don't understand why, you should probably read the conventions
appendix in the manual here:
`http://manual.cakephp.org/appendix/conventions`_
Add the component to your controller:

::

    
    var $components = array('SwiftMailer'); // along with the other components you use.

Woot, you serious?! Yep. Well you know, it's Cake! ;)


Making the connection
~~~~~~~~~~~~~~~~~~~~~
Swift Mailer has three connection types: native, sendmail and smtp.
We're going to see how to establish a connection using each type.


Native Mail
```````````
In this type, Swift uses the PHP's mail function.
This is what you should do:

::

    
    $this->SwiftMailer->connection = 'native';
    
    if($this->SwiftMailer->connect())
    {
    	// ..
    }

That's all? Absolutely.


Sendmail
````````
Quoting the Swift Mailer documentation:
Establishes a I/O hook to a sendmail process running in -bs mode. The
binary does not strictly need to be sendmail, it will work with exim,
qmail and postfix too providing the version in question supports
running in -bs mode.
Here is how to do it using the component:

::

    
    $this->SwiftMailer->connection = 'sendmail';
    $this->SwiftMailer->sendmail_cmd = '/usr/sbin/sendmail -bs';
    
    if($this->SwiftMailer->connect())
    {
    	// ..
    }

$sendmail_cmd defaults to false, Swift will then try to find sendmail
itself by using the linux command `which`. Setting it to 'default' is
equivalent to '/usr/sbin/sendmail -bs'.


SMTP
````
This is the default connection type. Here is how to use it:

::

    
    $this->SwiftMailer->connection  = 'smtp'; // default and thus you don't have to specify it
    $this->SwiftMailer->smtp_host   = 'smtp.gmail.com';
    $this->SwiftMailer->smtp_type   = 'tls'; 
    
    if($this->SwiftMailer->connect())
    {
    	// ..
    }

$smtp_host is the fully qualified domain name of the server you wish
to connect to. Defaults to null in the Component, which means Swift
will read the value from php.ini
$smtp_port : The default SMTP port is 25, 465 for SSL. It is set to
false in the Component, to let Swift choose the value depending on
$smtp_type. Set it to null to tell Swift to read the value from
php.ini
$smtp_type Can be one of the following values: 'open', 'ssl', 'tls'.
Depending on your SMTP server, you should set this value accordingly.
By the way Gmail uses TLS.


Auth
~~~~
If your server requires authetication, you can set up your username
and password by doing the following:

::

    
    $this->SwiftMailer->username  = 'user@gmail.com';
    $this->SwiftMailer->password  = 'secret';

Depending on your server, your username might be 'user' or
'user@domain.tld'.


Sending Mail
~~~~~~~~~~~~
To avoid redundancy, I'm not going to focus on how to send mail using
the Swift Mailer, rather, I will show how to do it using the
component. If you need to access the Swift object directly, use
$this->SwiftMailer->mailer.

This component isn't attempting to hide Swift, but rather extend it's
functionality and make it Cake-ish. Swift's syntax is really
clear/clean, and re-inventing it is really pointless. That being said,
I'm going to show you what this component adds:


addTo($type, $address, $name = false)
`````````````````````````````````````
Originally, this function was written by TommyO in his component. I
liked the idea, so I added it. I had to modify it though.
$type Can be one of these values: 'from', 'to', 'cc', 'bcc'.
You can have many 'to','cc', 'bcc' entries, but only one 'from'.
Here are examples of usage:

::

    
    
    	$this->SwiftMailer->addTo('from',"user@gmail.com","firstname lastname");
    	
    	$this->SwiftMailer->addTo('to',"user@domain.tld");
    	$this->SwiftMailer->addTo('to',"foobar@domain.tld","Foo Bar");
    	$this->SwiftMailer->addTo('to',"crazylegs@gmail.com","CraZyLeGs");
    	
    	$this->SwiftMailer->addTo('cc',"cc1@domain.tld","C C 1");
    	$this->SwiftMailer->addTo('cc',"cc2@domain.tld","C C 2");
    	
    	$this->SwiftMailer->addTo('bcc',"bcc1@domain.tld","B C C 1");
    	$this->SwiftMailer->addTo('bcc',"bcc2@domain.tld","B C C 2");
    	


Adding a body
`````````````
To add body to your message you can use the $mailer object within the
component. Refer to the Swift Mailer documentation for more info.
Example:

::

    
    $this->SwiftMailer->mailer->addPart("Plain Body");
    $this->SwiftMailer->mailer->addPart("Html Body", 'text/html');


I want to send it, damn it!
```````````````````````````
Fine.

::

    
    $this->SwiftMailer->send("Subject");

Happy? You could just do it with internal Swift object though:
$this->SwiftMailer->send(...);

What's the difference, then? Well, the component's send method takes
into consideration the parts you added using addPart, and if you have
specified a username and password, it will try to authenticate.
Oh really?! Really. Nice!!!. I know.
Until now, it's been pretty basic, but bear with me and continue
reading.


wrapBody($msg, $type = 'plain', $return = false)
````````````````````````````````````````````````
Actually, the exciting part starts here. The Swift Mailer Component
has the capability to wrap your body message with a layout. Yeah, a
Cake layout.
In the controller there is a variable called $layout, which defaults
to 'swift_email', and it is the layout you want your message to get
wrapped with. You need to create swift_email.thtml in your layouts
folder.

An example of a layout:

View Template:
``````````````

::

    
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <base href="<?=FULL_BASE_URL?>" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>CakePHP SwiftMailer Component</title>
    </head>
    
    <body style="background: #dedede; padding-top: 100px;" >
    
    <?=$html->image('w3c_xhtml10.png')?> <br/>
    <?=$html->image('w3c_css.png')?><br/>
    <?=$html->image('cake.power.png',array('embed'=>'swift'))?><br/>
    <?=$content_for_layout; ?>
    </body>
    </html>

Please notice " />, You need to have this in order for internal
elements ( img, anchors, etc.) to link properly from within the mail
client reading the Email you're going to send.

There is also $email_views_dir which is the folder within the views
directory where emails to be sent will be stored. More on that in the
next section, but for now you need to have a view called default.thtml
in there for wrapBody to function.

Content of default.thtml :

View Template:
``````````````

::

    
    <?php echo $swiftMailer_data;?>

$msg is the message you want to get wrapped by the layout.
$type Either 'plain' or 'html', if plain, the HTML tags are stripped.
$return defaults to false, if true the wrapped msg is returned instead
of added in the mail. You can call $this->SwiftMailer->mailer->addPart
to add it then.
Usage:

::

    
    $this->SwiftMailer->wrapBody("My Plain Body");
    $this->SwiftMailer->wrapBody("My HTML Body",'html');
    
    $body = $this->SwiftMailer->wrapBody("I want my body",'html',true);
    

This function can be useful for example to send a newsLetter, the body
content is coming from the database where you archive the newsletters
sent, you wrap the newsletter with a layout and you're all set!


function viewBody($name, $type = 'both', $return = false)
`````````````````````````````````````````````````````````
With SwiftMailer Component, you have the possibility to send views you
prepared in advance. A common example would be a confirmation email,
etc. The views should be located in the directory specified by
$email_views_dir.

$msg is the name of the view you want to send without .thtml
$type can be one of the following values 'plain', 'html' or 'both',
'both' is the default
$return defaults to false, if true the rendered view is returned
instead of added in the mail. you can call
$this->SwiftMailer->mailer->addPart to add it then.
if $type is plain, the html tags are striped. if 'both', both an html
and plain versions are added.
The 'html' view must have the suffix '_html', so if you plan to send
an html confirmation email, you should name your view
confirm_html.thtml.
So if type is 'both' and you want to send the confirm view, you need
to actually have two views, one named confirm.thtml for the plain
version and one named confirm_html.thtml for the HTML version. Clear?
Thought so.
Usage:

::

    
    
    $this->SwiftMailer->viewBody('confirm'); // defaults to 'both'
    $this->SwiftMailer->viewBody('confirm','plain');
    $this->SwiftMailer->viewBody('confirm','html');
    
    $html_plain = $this->SwiftMailer->viewBody('confirm','plain',true);
    $html_body  = $this->SwiftMailer->viewBody('confirm','html',true);
    // $both_body will contain an array of both the 'plain' and 'html' versions, in this order.
    $both_body = $this->SwiftMailer->viewBody('confirm','both',true);
    



Shortcut functions
~~~~~~~~~~~~~~~~~~

sendWrap($subject, $body, $type = 'plain')
``````````````````````````````````````````
This function is equivalent to calling wrapBody and send.

sendWrap($subject, $body, $type = 'plain')
``````````````````````````````````````````
This function is equivalent to calling viewBody and send.

Complete example
~~~~~~~~~~~~~~~~

::

    
    $this->SwiftMailer->connection  = 'smtp';
    $this->SwiftMailer->smtp_host   = 'smtp.gmail.com';
    $this->SwiftMailer->smtp_type   = 'tls'; 
    
    $this->SwiftMailer->username  = 'user@gmail.com';
    $this->SwiftMailer->password  = 'secret';
     
    if($this->SwiftMailer->connect())
    {
    	$this->SwiftMailer->addTo('to',"crazylegs@gmail.com","CraZyLeGs");
    	$this->SwiftMailer->addTo('from',"user@gmail.com","some user");
    	
    	if(!$this->SwiftMailer->sendView("SwiftComponent::sendView Exemple","confirm",'both'))
    	{
    		echo "The mailer failed to Send. Errors:";
    		pr($this->SwiftMailer->errors());	
    	}
    
    	echo "Log:";
    	pr($this->SwiftMailer->transactions());
    }
    else
    {
    		echo "The mailer failed to connect. Errors:";
    		pr($this->SwiftMailer->errors());
    }



Bonus
~~~~~
One thing that is nice with Swift Mailer, is a function called
addImage, it embeds an image into the body of the email to display
inline.
Something like:

View Template:
``````````````

::

    
    <img src="'.$swift->addImage($path_to_image).'" alt="Holiday" />

The problem with that though, is that Cake is an MVC framework and
thus the view doesn't have business logic so it can not access the
Mailer, well actually it can($this->controller->SwiftMailer->mailer->a
ddImage(WWW_ROOT.'img'.'e`www_ugly.jpg'`_);), but it should not, read
it, must not.
And so there is no obvious way of calling addImage from the view, if
we want to embed images. I hear you saying, use a helper, will this
won't solve the issue, because, you would want to create an instance
of Swift, that instance will not be the one the component is using.
Oh Ma'..so what's the solution? Heh, don't beat me if I say that it's
already solved. actually the component's viewBody function solves it.
Shut up!! yeah, it actually looks for images that have the param
embed="swift" in them and converts them automagically into embeded
images! You're kidding? Hell, no.
[view]image('cake.power.png',array('embed'=>'swift'))?> That was my
bonus.


Conclusion
~~~~~~~~~~
That's it guys, I hope you'll find the component useful, Thanks to
Chris Corbyn for making such a great class. As always, comments
enhancements, typo corrections, bug reports are welcome.

.. _www_ugly.jpg': http://www_ugly.jpg'
.. _http://bakery.cakephp.org/articles/view/192: http://bakery.cakephp.org/articles/view/192
.. _http://www.swiftmailer.org/docs/: http://www.swiftmailer.org/docs/
.. _http://manual.cakephp.org/appendix/conventions: http://manual.cakephp.org/appendix/conventions
.. _http://www.swiftmailer.org/download/: http://www.swiftmailer.org/download/
.. meta::
    :title: SwiftMailer Component Tutorial
    :description: CakePHP Article related to Mail,sendmail,smtp,mailer,swift mailer,Tutorials
    :keywords: Mail,sendmail,smtp,mailer,swift mailer,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

