Implementing SOAP on cakePHP
============================

by scook on November 04, 2006

I have recently (last week) started using cakePHP. I choose cake
because it works with AMFPHP and because I thought cake had built in
support for web services (including soap). I'm not complaining, but I
was somewhat disappointed to find out that support for web services is
limited to routing, which I could not get to work anyway...


Requirements
````````````
So, I set off on my quest to implement soap. In brief, my requirement
were:
1.Serve soap
2.Automatically generate and serve wsdl
3.Handle complex types in method calls and return values
4.Work within the cakePHP framework
5.Easy to implement and easy to use


Serve Soap
``````````
Since I use php5 and because I plan to generate the wsdl separately, I
choose to use the soap server/client included with php5.
`http://us2.php.net/manual/en/ref.soap.php`_

WSDL
````
For generating wsdl I choose to use Webservice helper from David
Kingma:
`http://www.jool.nl/new/1,webservice_helper.html`_

Cake Integration
~~~~~~~~~~~~~~~~
I began by trying to create a component that would grab soap requests
and redirect them to a soap view. While this may work, and it would be
very cake-ish, it seemed like a lot of work and not very soap-ish. I
immediately ran into problems getting arguments from the soap request
into the method I was calling. Soap requests generally do not use
separate addresses for each method call, and they send arguments in
the soap envelope, which is not parsed into the data (post or get)
variable. Based on this, it seems to me that the cake view methodology
and the cake component methodology do not mesh nicely with soap.

As a side note; These problems could be overcome if the cake core
would parse and return soap envelopes, but that seems like a task for
the cake development team rather than a hack like me!

After I beat my head against the wall for a while, I realized that my
first course of action was doomed to fail; I decided to try creating
one Controller that would handle all soap method calls, and wsdl
requests. This seems to work. This technique is very class centric,
soap methods will be grouped and accessed by their controller name.

The main challenge here is to get the soap arguments out of the soap
envelope so that they can be passed to the controller method. At first
glance you might think you could simply use the SoapServer to call the
controller method. Like this:

::

    
    <?php
    $server = new SoapServer($wsdlfile);
    $server->setClass('MyControllerClass');
    $server->handle();
    ?>

The problem is that this method would NOT call the controller within
the cake framework . I'm no cake expert, but I don't think this would
produce the expected results.

Somehow we need to get the soap arguments and then call the controller
method using the cake requestAction method.

::

    
    <?php
    $server = new SoapServer($wsdlfile);
    $server->setClass('HandleSoapClass');
    $server->handle();
    ?>

My handle soap class looks like this:

::

    
    <?php
    class HandleSoapClass{
    	/** @var AppController */
    	static $controller;
    
    	/** @var string */
    	static $wsClassName;
    	
    	function __call ( $func_name, $args ){
    		return self::$controller->requestAction('/'.substr(self::$wsClassName,0,-10).'/'.$func_name.'/soap5/', array('pass'=>$args) );
    	}
    }?>

If you are unfamiliar with __call, please see the php
site.`http://us3.php.net/manual/en/language.oop5.overloading.php`_
This class basically passes any method call to the appropriate
controller class by means of the cake requestAction method. And on the
way it sends the soap arguments.

Maybe that seems obvious to you, but I thought it was pretty cool...

The other option would be be to:

#. Parse the soap envelope
#. create php vars or objects
#. run the function
#. then construct a return soap envelope

Ick...That does not sound like fun!

Below is an overview of the whole process.

::

    
    +---soap client request wsdl from uri:mydomain.com/soap5/wsdl/ControllerName
    |   |
    |   +---soap5Controller's wsdl method requests the wsdl from WebServiceHelper
    |       and returns the wsdl document
    |   
    +---soap client calls soap method from uri: mydomain.com/soap5/serve/ControllerName
        |
        +---soap5Controller's serve method receives the request and passes it to a 
            |php5 SoapServer
            |
            +---SoapServer passes the request to a dummy class
            |
            +---Dummy class parses the soap arguments and uses the cake requestAction 
                |method to call the real soap method
                |
                \---requestAction returns the results back to the dummy file and 
                    SoapServer outputs the soap response back to the client



So we want to turn this result:

::

    Array
    (
        [0] => 1
        [1] => My first note
        [2] => note body. bla, bla, bla, bla, bla, bla, bla.
        [3] => 2006-10-29 07:23:42
        [4] => 2006-10-29 07:23:42
    )

into something with field names like this:

::

    Array
    (
        [id] => 1
        [title] => My first note
        [body] => note body. bla, bla, bla, bla, bla, bla, bla.
        [created] => 2006-10-29 07:23:42
        [modified] => 2006-10-29 07:23:42
    )

Enter VOs(value objects). Also known as structures(struc). A VO is
nothing more than a simple object to hold the data. My vo for the Note
class would look like this:

::

    <?php
    class Note_vo extends DataClass
    {
    	/** @var int **/
    	public $id = 0;
    
    	/** @var string **/
    	public $title = '';
    
    	/** @var string **/
    	public $body = '';
    
    	/** @var string  **/
    	public $created = '';
    
    	/** @var string **/
    	public $modified = '';
    }
    ?>

Notice that each variable has a documented type. This is necessary for
the automatic wsdl generation. Don't worry, you don't have to manually
create a vo for each of your models; I have extended the bake script
to create VOs.

So, let's update our NotesController and view the results.

::

    <?php
    	/**
    	* Get one record
    	* @param int
    	* @return Note_vo
    	*/
    	function view($id) {
    		$note = $this->Note->read(null, $id);
    		$this->set('note', $note);
    		$note_vo = new Note_vo($note['Note']);
    		return $note_vo;
    	}
    ?>

All I did was add one line

::

    $note_vo = new Note_vo($note['Note']);

and then update the return type: @return Note_vo

Now let's view the client results:

::

    <?php
    stdClass Object
    (
        [body] => note body. bla, bla, bla, bla, bla, bla, bla.
        [created] => 2006-10-29 07:23:42
        [id] => 1
        [modified] => 2006-10-29 07:23:42
        [title] => My first note
    )
    ?>

This is a pretty simple example so I should explain that your VO can
contain other complex types (VOs), so you can exactly duplicate the
structure of your model.

Now let's add the VO concept to the client code:
Client code:

::

    <?php
    require('GenericDOM.php');
    require('DataClass.php');
    include('note.php');
    
    $client = new SoapClient("http://ftc/soap5/wsdl/NotesController/", array('classmap' => array('Note_vo' => "Note_vo")) );
    echo "<pre>";
    echo "\n\n";
    print_r( $client->view(1) );
    echo "</pre>";
    ?>

Now client result is returned as a Note_vo object:

::

    
    Note_vo Object
    (
        [id] => 1
        [title] => My first note
        [body] => note body. bla, bla, bla, bla, bla, bla, bla.
        [created] => 2006-10-29 07:23:42
        [modified] => 2006-10-29 07:23:42
    )


Let me start by saying that I do not have a tested method for securing
my soap, however I do have some ideas.

Using my previous example, you could add HTTP authentication to the
soap call by adding a user name and password to the SoapClient.

::

    
    $client = new SoapClient("http://ftc/soap5/wsdl/NotesController/", 
    	array("login" => "admin", "password" => "adminpwd", 
    	'classmap' => array('Note_vo' => "Note_vo") ) 
    );

Then when you do your authentication check $_SERVER['PHP_AUTH_USER']
and $_SERVER['PHP_AUTH_PW'] for valid user.



Conclusion
~~~~~~~~~~

Code
++++
I will be posting all code and usage instructions in the next couple
days.

I just ran into this site:
`http://instantsvc.toolslave.net`_
It looks like a pretty sweet library for serving soap and wsdl. Once
again it is php5 only... But if the cake core was to include soap
support, this could be a good place to start.


This example uses the following notes table, and MVCs created with the
bake script.

::

    -- 
    -- Table structure for table `notes`
    -- 
    CREATE TABLE `notes` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `title` varchar(50) default NULL,
      `body` text,
      `created` datetime default NULL,
      `modified` datetime default NULL,
      PRIMARY KEY  (`id`)
    ) TYPE=MyISAM  AUTO_INCREMENT=2 ;
    -- 
    -- Dumping data for table `notes`
    -- 
    INSERT INTO `notes` VALUES (1, 'My first note', 'note body. bla, bla, bla, bla, bla.', '2006-10-29 07:23:42', '2006-10-29 07:23:42');

So, we have a controller named NotesController with the following
functions: index, add, edit, delete. First thing I have to do is
document my methods. Note: Currently all methods in a controller must
be documented if you want to use the soap server on that class. This
shouldn't be a big deal since we should be documenting all of our code
anyway! The documentation uses a standard flower-box method. You must
document each parameter and a return type. If the function doesn't
return anything, declare the return type as void.
So my view function would go from this:

::

    <?php
    	function view($id) {
    		$this->set('note', $this->Note->read(null, $id));
    	}
    ?>

to this:

::

    <?php
    	/**
    	* Get one record
    	* @param int
    	* @return void
    	*/
    	function view($id) {
    		$this->set('note', $this->Note->read(null, $id));
    	}
    ?>

This function will now work in the Soap5 server. However it doesn't
return anything, so you won't get any results if you call it. So make
two more changes: First return the note array that you previously had
passed to the view, and then declare the return type to be a string
array string[]

::

    <?php
    	/**
    	* Get one record
    	* @param int
    	* @return string[]
    	*/
    	function view($id) {
    		$note = $this->Note->read(null, $id);
    		$this->set('note', $note);
    		return $note['Note'];
    	}
    ?>

So we have a documented function that works just the same as before
when you use the cake view, but now we can call it from a soap client
and get the results back.

Client code:

::

    <?php
    $client = new SoapClient("http://domain.com/soap5/wsdl/NotesController/");
    echo "<pre>";
    try {	
    	print_r( $client->view(1) );
    } catch (SoapFault $exception) {
    	$result .= '..'.var_export($exception, true);
    }
    echo "</pre>";
    ?>

Client Results:

::

    
    Array
    (
        [0] => 1
        [1] => My first note
        [2] => note body. bla, bla, bla, bla, bla, bla, bla.
        [3] => 2006-10-29 07:23:42
        [4] => 2006-10-29 07:23:42
    )

You can view the wsdl for this class by pointing your browser to:
domain.com/soap5/wsdl/NotesController/

Looks good right? Well no, actually there is one glaring problem here.
The array keys to the result array have been dropped and replaced with
numerical keys. For something as simple as a note, this might be
sufficient. But it certainly is not very user(developer) friendly. To
overcome this problem we need to use objects to return the data as
complex types. Continued on next page...

`1`_|`2`_|`3`_|`4`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_

.. _http://www.jool.nl/new/1,webservice_helper.html: http://www.jool.nl/new/1,webservice_helper.html
.. _Page 4: :///articles/view/4caea0dc-9980-4997-8e05-43c582f0cb67/lang:eng#page-4
.. _Page 2: :///articles/view/4caea0dc-9980-4997-8e05-43c582f0cb67/lang:eng#page-2
.. _Page 3: :///articles/view/4caea0dc-9980-4997-8e05-43c582f0cb67/lang:eng#page-3
.. _Page 1: :///articles/view/4caea0dc-9980-4997-8e05-43c582f0cb67/lang:eng#page-1
.. _http://instantsvc.toolslave.net: http://instantsvc.toolslave.net/
.. _http://us2.php.net/manual/en/ref.soap.php: http://us2.php.net/manual/en/ref.soap.php
.. _http://us3.php.net/manual/en/language.oop5.overloading.php: http://us3.php.net/manual/en/language.oop5.overloading.php
.. meta::
    :title: Implementing SOAP on cakePHP
    :description: CakePHP Article related to soap,wsdl,webservices,php5,Tutorials
    :keywords: soap,wsdl,webservices,php5,Tutorials
    :copyright: Copyright 2006 scook
    :category: tutorials

