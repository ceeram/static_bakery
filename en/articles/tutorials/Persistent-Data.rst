

Persistent Data
===============

by %s on September 23, 2006

I'm going to show you how to create persistent data in your apps using
Components, regardless of which Controller you're using. The reason
why I wanted persistent data, was because I have my Website
Preferences stored in a database. I didn't want to have to call that
model in every Controller, so I learned a bit about Components and
created one that can be called in my custom AppController, which would
in turn call and query the SitePreference Model every time a
Controller was used. This is a 5 step process: - Create The Table -
Create The Model - Create The Component - Execute The Component In
Your Custom AppController - Display The Data In Your 'default.thtml'
File [b]Thanks to some feedback on #cakephp, I found out that using a
Model in a Component is not a recommended practice and should be done
only in special situations[/b]
Create The Table
This one is an easy one, just execute the sql code below so we have
something to play with.

-- File: database_table.sql

CREATE TABLE `site_preferences` (
`id` int(11) NOT NULL default '0',
`site_name` varchar(50) default NULL,
`site_caption` text,
`created` datetime default NULL,
`modified` datetime default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO
`site_preferences`
VALUES
(1, 'My New Site', 'Welcome to our new home :-)', '2006-09-17
01:09:44', '2006-09-17 01:09:44');

Create The Model
This is also quite easy. All you need to do is follow the cakephp
Naming Conventions
(`http://manual.cakephp.org/appendix/conventions`_).

You also want to make sure you name your Model in order to insure
portability, using 'var $name = 'SitePreference';'.


Model Class:
````````````

::

    <?php 
    // File: /cake_install_path/app/models/site_preference.php
    
    class SitePreference extends AppModel
    {
        var $name = 'SitePreference';
    }
    ?>

Create The Component
ok, this is where it gets a bit tricky. Let's look at some code, and
then I'll go through it and explain what I've learned.


Component Class:
````````````````

::

    <?php 
    
    // File: /cake_install_path/app/controllers/components/site_preference.php
    
    class SitePreferenceComponent extends Object
    {
        var $controller = true;
        var $siteName;
        
        function startup (&$controller) {
            $this->controller = $controller;
            $this->SitePreference =& new SitePreference();
        }
        
        function getSiteName() {
            $this->siteName = $this->SitePreference->find("id = 1", "site_name");
            return $this->siteName;
        }
        
        function getSiteCaption() {
            $this->siteCaption = $this->SitePreference->find("id = 1", "site_caption");
            return $this->siteCaption;
        }
    }
    
    ?>

You want to make sure that you name everything correctly. Here's the
link to cakephp's Naming Conventions:
`http://manual.cakephp.org/appendix/conventions`_.

Let's go through each part of this Component.


Component Class:
````````````````

::

    <?php 
    class SitePreferenceComponent extends Object
    ?>

The name of your Component class needs to be CamelCased, and end with
the text 'Component', i.e. 'SitePreferenceComponent'.


Component Class:
````````````````

::

    <?php 
    var $controller = true;
    var $siteName;
    var $siteCaption;
    ?>

I'm not 100% on this, but I believe the first line is required for
each Component. The following two lines (var $siteName; var
$siteCaption;) are local variables that I created to store our data.


Component Class:
````````````````

::

    <?php 
    function startup (&$controller) {
        // This method takes a reference to the controller which is loading it.
        // Perform controller initialization here.
    }
    ?>

The above was taken from the Cakephp manual. What that means is that
what ever is in this method will be executed when the Component is
executed. Just make sure that the method definition looks exactly like
this.


Component Class:
````````````````

::

    <?php 
    function startup (&$controller) {
        $this->controller = $controller;
        $this->SitePreference =& new SitePreference();
    }
    ?>

The first line inside this method passes the parent Controller (the
Controller that is currently being executed) to $this->controller,
which allows you to access the current Controller in your Component.
Then you can then access the Controllers methods inside of the
Component like this:


Component Class:
````````````````

::

    <?php 
    $this->controller->flash("Thanks for logging in");
    </code>
    ?>

The second line inside this method is what I had some trouble with
while learning about Components. The manual discribes how to create a
new instance of the Model you want to use, but not how to use/access
the Model in your Components.

Here's the basic syntax you need to use in order to access Models from
your Components:


Component Class:
````````````````

::

    <?php 
    $this->MyModel =& new MyModel();
    ?>

In my code above, our Model name is SitePreference, so I just replace
'MyModel' with 'SitePreference'. Now you can use the Model inside the
Controller with this syntax:


Component Class:
````````````````

::

    <?php 
    $this->MyModel->method();
    ?>

That brings us to our custom Component methods:


Component Class:
````````````````

::

    <?php 
    function getSiteName() {
        $this->siteName = $this->SitePreference->find("id = 1", "site_name");
        return $this->siteName;
    }
    ?>

This method uses the Model instance we created to query the database
for our site name, and then assigns our site's name to our instance
variable "var $siteName;". It then returns the $siteName variable for
use in our app.


Component Class:
````````````````

::

    <?php 
    function getSiteCaption() {
        $this->siteCaption = $this->SitePreference->find("id = 1", "site_caption");
        return $this->siteCaption;
    }
    ?>

This is pretty much the same thing as the method described above, yet
it queries the database for our site's caption.

Here is the syntax we can use in our Controllers (I'll show you how to
use it in more detail in the next section):


Controller Class:
`````````````````

::

    <?php 
    $this->SitePreference->getSiteName();
    $this->SitePreference->getSiteCaption();
    ?>

That should do it for our Component. Now lets go learn how to use that
Component.

Execute The Component In Your Custom AppController
Once again, lets look at some code, then I'll explain what's going on.
This is our custom AppController:


Controller Class:
`````````````````

::

    <?php 
    
    // File: /cake_install_path/cake/app_controller.php
    
    class AppController extends Controller {
        
        var $components = array('SitePreference');
        
        function beforeRender()
    	{
    	    $this->set('siteName', $this->SitePreference->getSiteName());
    	    $this->set('siteCaption', $this->SitePreference->getSiteCaption());
    	}
        
    }
    ?>

The first thing we need to do is let our Controller know that our
Component exists. We do this with this syntax:


Controller Class:
`````````````````

::

    <?php 
    var $components = array('MyComponent');
    ?>

Now we can access that Component in all of our Controllers]] (since we
are calling the Component in our AppController, which is the parent
class of every other Controller that we create).

You may already be familiar with 'beforeRender()', but if not, here's
the description from the cake Manual: "beforeRender() - Called after
Controller logic, and just before a View is rendered.".

Now that we know when the 'beforeRender' will execute, and that it
will execute automaticaly, all we have to do is include the code that
we want executed. In my example above, I call the two methods that I
created in our 'SitePreference' Component, then 'set' the returned
data to variables that my View can use.

Just remember, since you initialized the Component in your custom
AppController, you can use the Component in any Controller you create
(even if you don't use the 'beforeRender'), using this syntax:


Controller Class:
`````````````````

::

    <?php 
    $this->MyComponent->method();
    ?>

Some bakers want to have a seperate 'beforeRender' for each
Controller. If you'd rather do that, then just include the
'beforeRender' in each Controller that you'd like executed (in truth,
you don't need to use 'beforeRender' at all inside your Controllers).

The only reason that I don't want to to that in this situation, is
because I'm lazy :-) I want to only have to worry about one method
that needs to be maintained/debugged, and I don't want to have to
remember to include the Component and 'beforeRender' method in my
future Controllers.

So far the design of my site hasn't needed seperate 'beforeRenders',
but that could change in the future. If that does happen, then I'm
going to keep digging for other Persistent Data methods :-p

Now that was pretty easy ;-) All we have to do to our custom
AppController is request the Component, then call the Component
methods inside our 'beforeRender' method.

Now let's go ahead and access the data that the 'SitePreference'
Component methods provided, and the AppController's 'beforeRender'
supplied to your Views.

Display The Data In Your 'default.thtml' File
Once again, some code please, James :-p


View Template:
``````````````

::

    
    <?php
    
    // File: /cake_install_path/app/views/layouts/default.thtml
    
    if (isset($siteName)) {
        $site_name = $siteName['SitePreference']['site_name'];
    }
    if (isset($siteCaption)) {
        $site_caption = $siteCaption['SitePreference']['site_caption'];
    }
    
    ?>
    
    <html>
        <head>
    	<title>
    	    <?php echo $site_name." : ".$title_for_layout; ?>
    	</title>
        </head>
    
        <body>
    	<h1>
    	    This is my site, check it out
    	</h1>
    		
    	<p>
    	    <?php echo $site_caption; ?>
    	</p>
    	
        </body>
    </html>

If you're interested in using persistent data, and have been able to
follow along so far, then you probibly know what these lines in the
AppController's beforeRender do:


Controller Class:
`````````````````

::

    <?php 
    $this->set('siteName', $this->SitePreference->getSiteName());
    $this->set('siteCaption', $this->SitePreference->getSiteCaption());
    ?>

Now all you have to do is use the variables '$siteName' and
'$siteCaption' in your layouts.

What I did what use the code

View Template:
``````````````

::

    
    <title>
        <?php echo $site_name." : ".$title_for_layout; ?>
    </title>

which would display in my web page title as "My New Site : Home", if
the user is on the home page.

I believe that the the above doesn't need much explaining, but if you
are having trouble following along, just click the 'Edit this page'
link on the top left of this wiki entry and write a short message
explaining what you'd like cleared up. I will address those issues to
the best of my ability, then remove the message requesting
clearification.

Here's a little disclaimer:

I've only been using CakePHP for a few months, and this is my first
wiki entry. Everything is as accurate as my knowledge of cake will
allow. I appoligize for any error or poor practices that I might be
promoting unintentionaly. I've done my best to stick with cakes
conventions, not messing with core files, using models and avoiding
accessing the database correctly, and not calling controllers from
views.


.. _http://manual.cakephp.org/appendix/conventions: http://manual.cakephp.org/appendix/conventions
.. meta::
    :title: Persistent Data
    :description: CakePHP Article related to component,Tutorials
    :keywords: component,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

