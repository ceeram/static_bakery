SOAP services in CakePHP
========================

by %s on February 10, 2009

This article describes what (I think) is the most effective way of
calling controller methods with soap calls. At the end of this
tutorial, you can do something like '$mySoapClient->foo()' which maps
to ControllerX::foo, and '$mySoapClient->bar()' which maps to
ControllerY::bar. Pretty cool right? ;-)
Please do not expect this to be a 'copy & paste' tutorial. Just
copying the SoapComponent I've written doesn't do the magic! In order
to make this work, you need some basic understanding of wsdl files.
This tutorial offers a step to step roadmap to soap services in
CakePHP with the help of a wsdl file. Also I will offer some tools /
tips on debugging soap services. And believe me, soap can cause a lot
of headaches ;-)


The excecutive's summary
~~~~~~~~~~~~~~~~~~~~~~~~
The trick is having a wsdl file with multiple ports. Cause each port
has an address property, we can set the address to the correct
controller/action url (for example 'http://my-kickass-
project.com/my_controller/service_handler_mehod). Once the addresses
setup correctly, all you need to do is create a small SoapComponent
that handles the incomming request as a soap request with PHP's Soap
extension and set the 'handler' class the the controller, instead of
having CakePHP handling it.
As a finishing touch, I used CakePHP's prefix routing so all soap
requests map to soap_* methods in the controller.


Introduction
~~~~~~~~~~~~
Before you start, confirm the following:

+ You have Cake 1.2 and PHP 5 is configured with the option "--enable-
  soap" (which is enabled by default)
+ You have basic experience with Eclipse

This is what you have accomplished when finshed with this tutorial:

+ Created one or more controllers that handle the incomming soap
  requests
+ A wsdl file with one or more 'ports' that describe your webservice
  where each port maps to a different controller
+ Basic knowledge of soap services, wsdl, and debugging soap services
+ How to use CakePHP's prefix routing

On the next page I will explain how to setup the wsdl file.

On page 3, I will present you my SoapComponent which needs to be added
on every controller that you want to make available by soap.

On the last page I will give you some hints on testing and debugging.

If you have followed my instructions exactly and it still doesn't
work, please keep breathing, have another cup of coffee, get a walk,
come back and try again. Near to zero 'Cake automagic' is found here,
so do not say I didn't warn you :-)



Tools I'll be using
```````````````````
Download and install Eclipse with the WSDL editor plugin.

Eclipse can be found here (I use Eclipse PDT):
`http://www.eclipse.org/pdt/`_
The WSDL editor plugin can be found here:
`http://wiki.eclipse.org/index.php/Introduction_to_the_WSDL_Editor`_
SoapUI for testing the soap service
`http://www.soapui.org/`_


The wsdl structure
``````````````````
A wsdl can be seen as some sort of contract between client and server.
It describes what messages are send back and forward. If you try to
send something which is not defined in the wsdl, either the client or
the server will break and throw some sort of error. So having a valid
and correct wsdl file is key.

A breakup of the wsdl structure:
One wsdl file can contain multiple services.
One 'service' can contain multiple ports.
A 'binding' is the glue between one 'port' and one 'portType'
Finaly, a portType contains many operations. The operations can be
seen as the methods of your controller.
These operations can take parameters of your choosing and also return
whatever you want.


Building the controller and wsdlin eclipse
``````````````````````````````````````````
Okay, now fire up your eclipse, and add the CakePHP application as a
new project.

First we create the first controller that will handle the soap
requests. Create a file named 'messages_controller.php' and save in
app/controllers. Now copy the code of the MessagesController below. If
you have another controller name, that's also fine but keep the method
name 'soap_service' the same.

Controller Class:
`````````````````

::

    <?php 
    class MessagesController extends AppController{
    	public function soap_service(){
    		//no code here
    	}
    }
    ?>

We are going to store the wsdl file in the elements folder, so
different controllers can use it.
In the PHP (or Folder) explorer, rightclick on the app/view/elements
folder. Select 'New' -> 'Other...'. A new dialog pops up. Naviate to
the 'Web services' folder, and select 'WSDL'. Click next. Enter a
filename (I did 'myWSDLFile.wsdl') and click next.
For this tutorial, the target namespace isn't really that important,
but I entered the url I'm working on now (like 'http://my-kickass-
project.com').
Make sure 'SOAP' is selected for the protocol, and the Soap binding
option is set to 'document literal'.
Click finish

Eclipse will now show a diagram of the WSDL file you've just created.
Single clicking on any element will activate it, and in the
'Properties' tab you can see what those blocks are (like 'serivce',
'port', 'binding', etc...)

If you do not see the properties tab, go to 'Window' in the menu bar,
'Show view' -> 'Other..' and type properties in the textbox. Select
it, click ok.


The left most block is the service block, with one port in it. You
will see an url in there. Single click on that url. In the properties
tab, you see the Address property. Whenever you do a request to a soap
server, the client will connect to this url. So the address property
you need to enter is: `http://my-kickass-
project.com/soap/messages/service`_.

Note the 'soap_' at the beginning of the method and '/soap' at the
begining of the url. This is called prefix routing in CakePHP. I'll
come back on that later on page 3.

The right most block is the portType block with one operation in it
named 'NewOperation'. Single click on it, go to the properties tab and
set 'Name' to 'soap_foo'. You will see that the input and output
parameters are renamed as well.


Adding a second controller (wsdl port)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The following steps can be repeated for every extra controller you
want to make callable by soap. So if you have no more controllers, you
can skip the next part and continue with 'Testing with SoapUI'

Right click on the left most block, on the url. Select 'Add port'.
Give it a name, it is important that it's unique. Keep Binding set to
'(unspecified)'. For protocol, select 'SOAP'. Make sure 'Document
literal is checked'. Click finish.
You have added a new port and as you can see, you may choose another
url to define the location of your other controller. Again, the url
should start with '/soap'.

Right click on anywhere on a white area and select 'Add PortType'. A
new port type is added and the name of the new PortType is selected,
ready for editing. Change it something else if neccesary, remember it
and hit the enter key. On my machine, eclipse messes up the layout by
crossing the lines but that's a thing you and me have to live with :-)

Right click on the newly added 'Port' (The left most square block) and
select 'Set Binding' -> 'New binding...'. In the new dialog, give the
binding a unique name. A new binding is added to your wsdl as a small
square.

Right click on the newly added binding (the small square) and click on
'Generate binding content...'. In the new dialog, set portType to last
port type you added (the one I told you to remember). For protocol,
select 'SOAP' and make sure 'document literal' is checked. Click
finish and hit 'ctrl+s' to save your work.

Validating with SoapUI
~~~~~~~~~~~~~~~~~~~~~~
Now how do we know the wsdl we have generated is valid? I use a tool
called 'SoapUI' which works pretty simple.
Download and install SoapUI `http://www.soapui.org/`_
Startup Soap UI and choose 'File' -> 'New Soap UI project'.
Give it a name and browse to the initial wsdl file. Make sure that the
first checkbox 'create sample requests...' is checked. Click ok.

Now, if SoapUI doesn't show the operation 'soap_foo' at the left side
but instead shows an error, there is probably something wrong with the
wsdl file.

NOTE: I'm sorry if your WSDL isn't valid, please try again. I will not
give support on any wsdl error that might occur...

If you got here, you've successfully made the wsdl file! :-D Now the
real fun part begins because where getting into Cake :-)

Now I will show you what to do to make your controller respond to the
soap request.
First stop is to create a new route in the routes.php

::

    
    Router::connect('/soap/:controller/:action/*', array('prefix'=>'soap', 'soap'=>true));

This will tell CakePHP that each url starting with '/soap' should be
mapped to a controller with the 'soap_' prefix.
For more information see `http://book.cakephp.org/view/544/Prefix-
Routing`_

Here is the complete source of the messages controller:

Controller Class:
`````````````````

::

    <?php 
    	class MessagesController extends AppController{
    		public $uses = null; //for demostration purposes we do not need a model
    		
    		public $components = array(
    			'Soap' => array(
    				'wsdl' => 'myWSDLFile', //the file name in the view folder
    				'action' => 'service', //soap service method / handler
    			)
    		);
    
    		public function soap_wsdl(){
    			//will be handled by SoapComponent
    		}
    
    		public function soap_service(){
    			//will be handled by SoapComponent
    		}
    		
    		/**
    		 * A soap call 'soap_foo' is handled here
    		 *
    		 * @param Object $in The input parameter 'foo'
    		 * @return Object
    		 */
    		public function soap_foo($in){
    			$obj = new stdClass();
    			$obj->out = 'foo response';
    			return $obj;
    		}
    	}
    ?>


Do not forget to create a 'Messsage' model.

As you can see there I've used a component named 'Soap'. You can find
the source at the end of this page.
The soap_wsdl and soap_service methods are both handled by the
SoapComponent. You can leave those empty, but they still need to be
defined.


The soap_wsdl method
~~~~~~~~~~~~~~~~~~~~
The soap_wsdl method returns the wsdl file you've just created
earlier. The correct url is 'http://my-kickass-
project.com/soap/messages/wsdl'. Offcourse it isn't really nice to
have the wsdl method here in the messages controller. I prefer
creating a seperate controller (SoapController for example) and put
the soap_wsdl method there. That way there is one controller returning
the wsdl, and all the other controllers have their own responsibility
of handling the wsdl operations. But to keep things simple we won't be
changing anything :-)


The soap_service method
~~~~~~~~~~~~~~~~~~~~~~~
Remember you have entered an 'Address' for a 'Port' in the wsdl file?
This is the soap_service method right here. What happens is that each
soap connection starts in the soap_service method. In this method, the
SoapComponent will create a new SoapServer and set it's handling class
to its controller (the messages controller in this case). Now the
client may call the operation 'soap_foo'. This operation is then
redirected via 'soap_service' to 'soap_foo'. And the 'soap_foo' method
in the messages receives the paramter defined in the wsdl and has the
responsibility of returning the correct response (In this case an
object with an 'in' parameter).

Here is the source of the SoapComponent. You can store this component
in a file named app/controllers/components/soap.php

Component Class:
````````````````

::

    <?php 
    	App::import('core', 'AppHelper');
    
        /**
        * Soap component for handling soap requests in Cake
        *
        * @author      Marcel Raaijmakers (Marcelius)
        * @copyright   Copyright 2009, Marcel Raaijmakers
        * @license     http://www.opensource.org/licenses/mit-license.php The MIT License
        */
    	class SoapComponent extends Component{
    
    		var $name = 'Soap';
    
    		var $components = array('RequestHandler');
    
    		var $controller;
    
    		var $__settings = array(
    			'wsdl' => false,
    			'wsdlAction' => 'wsdl',
    			'prefix' => 'soap',
    			'action' => array('service'),
    		);
    
    		public function initialize($controller, $settings = array()){
    			if (Configure::read('debug') != 0){
    				ini_set('soap.wsdl_cache_enabled', false);
    			}
    
    			$this->controller = $controller;
    
    			if (isset($settings['wsdl']) && !empty($settings['wsdl'])){
    				$this->__settings['wsdl'] = $settings['wsdl'];
    			}
    
    			if (isset($settings['prefix'])){
    				$this->__settings['prefix'] = $settings['prefix'];
    			}
    
    			if (isset($settings['action'])){
    				$this->__settings['action'] = is_array($settings['action']) ? $settings['action'] : array($settings['action']);
    			}
    
    			parent::initialize($controller);
    		}
    
    
    		public function startup(){
    			if (isset($this->controller->params['soap'])){
    				if ($this->__settings['wsdl'] != false){
    					//render the wsdl file
    					if ($this->action() == $this->__settings['wsdlAction']){
    						Configure::write('debug', 0);
    						$this->RequestHandler->respondAs('xml');
    
    						$this->controller->ext = '.wsdl';
    						$this->controller->render(null, false, DS . 'elements' . DS . $this->__settings['wsdl']); //only works with short open tags set to false!
    					} elseif(in_array($this->action(), $this->__settings['action'])) {
    
    						//handle request
    						$soapServer = new SoapServer($this->wsdlUrl());
    						$soapServer->setObject($this->controller);
    						$soapServer->handle();
    
    						//stop script execution
    						$this->_stop();
    						return false;
    
    					}
    				}
    			}
    		}
    
    		/**
    		 * Return the current action
    		 *
    		 * @return string
    		 */
    		public function action(){
    			return (!empty($this->__settings['prefix'])) ? str_replace( $this->__settings['prefix'] . '_', '',  $this->controller->action) : $this->controller->action;
    		}
    
    		/**
    		 * Return the url to the wsdl file
    		 *
    		 * @return string
    		 */
    		public function wsdlUrl(){
    			return AppHelper::url(array('controller'=>Inflector::underscore($this->controller->name), 'action'=>$this->__settings['wsdlAction'], $this->__settings['prefix'] => true), true);
    		}
    
    	}
    ?>

The next and final page gives you some tip's and tricks you can use to
test and debug your brand new Soap server.

So you have a wsdl file and controller that should handle the soap
request. You happily create a small PHP test script with a SoapClient
and start testing. If it is all working the first time: respect :-) If
not, it's time for yet another cup of coffee and start debuging :-)

You should really validate step by step each aspect of the soap server
setup, starting with the wsdl file. As I said before, the wsld is the
contract that both the client and server should respect in all times.


Validate the wsdl file
~~~~~~~~~~~~~~~~~~~~~~
If SoapUI doesn't load your wsdl at all, it just isn't valid. Go back
to eclipse and validate the xml. When you have made some changes and
want to test it again, go to SoapUI and right click on the name of
your wsdl file (the one with the green icon before it). Then choose
'Update definition'. If everything is correct, you will see the wsdl
operations at the left side. If not, back to square one. I should also
mention that not every change made in the wsdl is refreshed in SoapUI.
Instead you should create a new project in SoapUI.


Validate if the server is working (The messages controller)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Expanding the 'soap_foo' request at the left side of SoapUI will
reveal a 'Request 1' node. Double click on it and SoapUI will open a
new window with the initial soap request. At the top you need to set
the url to the service method of the messages controller ('http://my-
kickass-project/soap/messages/wsdl').
Now hit the 'Run' button and watch the response. This could be either:

+ Nothing. Blank. Turn on debug in the core.php and try again
+ A notice, warnig by PHP in a html format. Read and fix this
  offcourse :-)
+ A perfect xml rexponse with a timestamp at the bottom

Here is another tip:
To test if the soap_service method is invoked, do this:

::

    
    <?php
    public function soap_service(){
    	echo "hi";
    	exit; //important
    }
    ?>

If soap_service is invoked, you will see 'hi' in as the response in
SoapUI. Offcourse you can print_r() your response in the soap_foo
method to validate if you send out a valid respons defined in your
wsdl.

As a rule of thumb, if you successfully manage setup a working soap
server, you know your wsdl is correct and the server is working. All
you need now is creating a small client script.


Creating a small client
~~~~~~~~~~~~~~~~~~~~~~~
Here is a small script to test the soap server. It's nothing special,
you can find additional info on the internet

::

    
    <?php
    	ini_set('soap.wsdl_cache_enabled', 0); //enable when in production mode, this does save a lot of time
    
    	$soapClient = new SoapClient('http://my-kickass-project.com/soap/messages/wsdl');
    
    	$param = new StdClass();
    	$param->in = 'param';
    
    	$foo = $soapClient->soap_foo($param);
    	var_dump($foo); //an object of StdClass with an 'out' field and the value 'foo response'
    ?>

That's it, your all done :-) There are a few alternative aproaches in
getting Cake in the soap but I think this is the most effective and
scalable solution. Here are some alternatives:

+ Creating a single controller (SoapController) that handles all
  incoming requests. So all operations of the wsdl have one single port.
  This is also perfectly fine I guess. With the help of 'requestAction'
  you can pass the incomming parameter and ask the correct response from
  the controller you want. In the SoapController all you need to do is
  convert this return value to a valid response value / object.
+ Not implementing any wsdl. But realy, I cannot think of any way of
  dispatching the soap request to a Cake controller. This is a job for
  the Dispatcher which you don't have any control on.


DISCLAIMER Please note that this article is based on a real life
situation I came across about half a year ago. This was also my first
real encounter with soap services so I'm not a webservices guru :-)
All knowledge is based on experience, reading a lot of documentation
and by falling and getting up a lot of times. In my experience the
generation of the wsdl file was the hard part. So please feel free to
add some comments, I'd really like that. But do not expect that I can
offer you support on questions like "Why doesn't SoapUI load my wsdl?"
and then posting you entire wsdl file or "Why can't I return a valid
response?". I suggest you start with some tutorials on basic soap in
PHP (without the cake) like I did :-)

`1`_|`2`_|`3`_|`4`_


More
````

+ `Page 1`_
+ `Page 2`_
+ `Page 3`_
+ `Page 4`_

.. _http://wiki.eclipse.org/index.php/Introduction_to_the_WSDL_Editor: http://wiki.eclipse.org/index.php/Introduction_to_the_WSDL_Editor
.. _http://my-kickass-project.com/soap/messages/service: http://my-kickass-project.com/soap/messages/service
.. _http://book.cakephp.org/view/544/Prefix-Routing: http://book.cakephp.org/view/544/Prefix-Routing
.. _Page 3: :///articles/view/4caea0e4-0654-4596-b94a-403782f0cb67/lang:eng#page-3
.. _http://www.eclipse.org/pdt/: http://www.eclipse.org/pdt/
.. _Page 4: :///articles/view/4caea0e4-0654-4596-b94a-403782f0cb67/lang:eng#page-4
.. _Page 1: :///articles/view/4caea0e4-0654-4596-b94a-403782f0cb67/lang:eng#page-1
.. _http://www.soapui.org/: http://www.soapui.org/
.. _Page 2: :///articles/view/4caea0e4-0654-4596-b94a-403782f0cb67/lang:eng#page-2
.. meta::
    :title: SOAP services in CakePHP
    :description: CakePHP Article related to soap,wsdl,webservices,component,services,soapcomponent,soap client,soap server,Tutorials
    :keywords: soap,wsdl,webservices,component,services,soapcomponent,soap client,soap server,Tutorials
    :copyright: Copyright 2009 
    :category: tutorials

