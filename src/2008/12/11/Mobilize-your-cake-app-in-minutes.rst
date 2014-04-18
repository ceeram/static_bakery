Mobilize your cake app in minutes
=================================

Whilst cake provides you with basic mobile detection, it doesn't cut
it in the real world, there are literally thousands of mobile devices
/ browsers that will slip through the requestHandler's net. By
plugging Wapple Architect into cake and using web services to do your
device detection, you can easily describe your mobile pages in WAPL
and let Wapple work out which markup to output to a particular device.
Here's what you need in order to use this tutorial:

1. SOAP (in this example i'm using the native PHP soap extension)

Note: In this example, we're going through a route that would send us
through the pages_controller and normally have a view of
/app/views/pages/home.ctp


Step 1. Sign up for a Wapple Architect Dev Key
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Visit `http://wapple.net/architect.htm?trk=cakephp`_ and head over to
the registration page, once you have your dev key, you can enter it
into the mobile detection component below.

There is also a developer site at `http://wapl.info`_ and a google
group at `http://groups.google.com/group/wapl`_

Step 2. Create Mobile Detection Component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/controllers/components/mobile_detection.php

Component Class:
````````````````

::

    <?php 
    /**
     * Mobile Detection Component
     * @author rich gubby
     * @version 0.1
     */ 
    class MobileDetectionComponent extends Object
    {
        var $components = array('RequestHandler');
        var $isMobile = false;
        var $architectDevKey = 'ENTER-DEV-KEY-HERE';
    
        function startup($controller)
        {
            $this->controller = $controller;
        }
    
        function detect()
        {
            // Set up SOAP client
            try 
            {
                $sClient = @new SoapClient('http://webservices.wapple.net/wapl.wsdl');
    
                if($sClient)
                {
                    $headers = array();
                    foreach($_SERVER as $key => $val)
                    {
                        $headers[] = array('name' => $key, 'value' => $val);
                    }
                    
                    // If we're a mobile, use WAPL to display the page
                    $params = array(
                        'devKey' => $this->architectDevKey,
                        'deviceHeaders' => $headers
                    );
    					
                    if($sClient->isMobileDevice($params))
                    {
                        $this->controller->webservices = 'Wapl';
                        $this->RequestHandler->respondAs('xml');
                        $this->controller->viewPath .= DS.'wapl';
                        $this->controller->layoutPath = 'wapl';
                        $this->controller->set('sClient', $sClient);
                        $this->controller->set('sClientHeaders', $headers);
                        $this->controller->set('architectDevKey', $this->architectDevKey);
    
                        // Flag as a mobile device
                        $this->isMobile = true;
                    }
                }
            } catch (Exception $e)
            {
                // Put your error handling in here
            }
        }
    }
    ?>



Step 3. Use the mobile detection component inside a controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/app_controller.php

Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller 
    {
        var $components = array('MobileDetection');
        function beforeFilter()
        {
            $this->MobileDetection->startup($this);
            $this->MobileDetection->detect();
        }
    }
    ?>

I've put the call to detect() in the app_controller but obviously you
can put it in any controller you want.

Now you've done your device detection and the result has come back as
a mobile, the layout and view path will have been amended, so now it's
just a case of creating a layout and individual view files.


Step 4. Create WAPL layout
~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/views/layouts/wapl/default.ctp

::

    
    <?php 
    // XML headers and open
    $string = '<' . '?xml version="1.0" encoding="utf-8" ?'.'><wapl xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://wapl.wapple.net/wapl.xsd">';
    
    // Page title and external CSS
    $string .= '<head><title>Site: '.$title_for_layout.'</title>';
    $string .= '<css><url>http://your-domain.com/css/mobile.css</url></css>';
    $string .= '</head>';
    $string .= '<layout>';
    
    $string .= $content_for_layout;
    
    $string .= '</layout></wapl>';
    
    // Setup parameters for communicating
    $params = array('devKey' => $architectDevKey, 'wapl' => $string, 'deviceHeaders' => $sClientHeaders);
    
    // Send markup to API and parse through simplexml
    $xml = simplexml_load_string($sClient->getMarkupFromWapl($params));
    
    foreach($xml->header->item as $val)
    {
    	header($val);
    }
    echo trim($xml->markup);

Note: You'll want to amend your page title and the URL of your css in
the layout above.


Step 5. Create a view to display WAPL code
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Filename: /app/views/pages/wapl/home.ctp

View Template:
``````````````

::

    
    <?php
    echo '
    <row>
    <cell>
    <chars>
    <value>Hello world!!</value>
    </chars>
    </cell>
    </row>';
    ?>



Step 6. Expand your application!
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

See `http://wapl.info/docs/chapter/Developing-with-WAPL/`_ for more
information with regards to building different elements such as text,
images, links and forms into your mobile application!

Note: You'll also probably want to set a cookie / session on the value
returned from isMobileDevice() and only do a call to it if that value
isn't set in order to cut down on the number of SOAP calls you make.


Some example sites (best viewed on your mobile!)
````````````````````````````````````````````````
xboxalerts.co.uk
mobileshoppingreview.com


.. _http://wapple.net/architect.htm?trk=cakephp: http://wapple.net/architect.htm?trk=cakephp
.. _http://groups.google.com/group/wapl: http://groups.google.com/group/wapl
.. _http://wapl.info/docs/chapter/Developing-with-WAPL/: http://wapl.info/docs/chapter/Developing-with-WAPL/
.. _http://wapl.info: http://wapl.info/

.. author:: rgubby
.. categories:: articles, tutorials
.. tags:: mobile,web services,Tutorials

