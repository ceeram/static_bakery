Using CakeAMFPHP 0.6 w/ Cake 1.2
================================

by morris on July 29, 2008

This is intended as a guide for those out there who *were* using the
splendid CakeAMFPHP 0.6.0 with Cake pre-1.2 and want to be able to
continue using it in Cake 1.2.

If you have the means to upgrade your gateway, or if you are starting
a new project in Cake 1.2, it would be best to use something like
CakeAMF (+ amfext) `https://trac.cakefoundation.org/amf/`_ or
CakeSWXPHP `http://blog.aboutme.be/cakeswxphp/`_ for your flash/flex
remoting needs. I have not used either of these personally though many
folks have recommended them to me.

This guide assumes you are starting with:

+ Cake 1.2 RC2 (or above will likely work)
+ CakeAMFPHP 0.6.0
+ AMFPHP 1.9


I would like to thank John David Anderson for helping me get
CakeAMFPHP working in Cake 1.2!


Let us begin!
~~~~~~~~~~~~~


#. Edit app/webroot/amfbrowser/config.inc.php , line 75

    + Change to App::import('Controller', null);
    + Add App::import('Controller', 'App');

#. Edit app/webroot/cake_gateway.php , line 75

    + Change to App::import('Controller', null);
    + Add App::import('Controller', 'App');
    + Add require_once(APP.'vendors'.DS.'cakeamfphp'.DS.'amf-
      core'.DS.'app'.DS."CakeGateway.php");
   Before:

::

    require CORE_PATH.'cake'.DS.'bootstrap.php';
    loadController (null);  
    vendor('cakeamfphp'.DS.'amf-core'.DS.'app'.DS."CakeGateway");	

   After:

::

    require CORE_PATH.'cake'.DS.'bootstrap.php';
    
    App::import('Controller', null);
    App::import('Controller', 'App');
    
    require_once( CORE_PATH . 'vendors' . DS . 'cakeamfphp'.DS.'amf-core'.DS.'app'.DS. 'CakeGateway.php');

#. Edit app/vendors/cakeamfphp/amf-core/app/CakeActions.php , line 195

    + Change to $controller->Component->init($controller);
   Before:

::

    $controller->_initComponents();

   After:

::

    $controller->Component->init($controller);

#. Edit app/vendors/cakeamfphp/amf-core/app/CakeGateway , line 25
   Change vendor calls to:

    + require_once(APP.'vendors'.DS.'cakeamfphp'.DS.'amf-
      core'.DS.'app'.DS.'Gateway.php');
    + require_once(APP.'vendors'.DS.'cakeamfphp'.DS.'amf-
      core'.DS.'app'.DS.'CakeActions.php');
   Before:

::

    vendor('cakeamfphp'.DS.'amf-core'.DS.'app'.DS.'Gateway');
    vendor('cakeamfphp'.DS.'amf-core'.DS.'app'.DS.'CakeActions');

   After:

::

    require_once(APP.'vendors'.DS.'cakeamfphp'.DS.'amf-core'.DS.'app'.DS.'Gateway.php');
    require_once(APP.'vendors'.DS.'cakeamfphp'.DS.'amf-core'.DS.'app'.DS.'CakeActions.php');



That should wrap things up!


.. _http://blog.aboutme.be/cakeswxphp/: http://blog.aboutme.be/cakeswxphp/
.. _https://trac.cakefoundation.org/amf/: https://trac.cakefoundation.org/amf/

.. author:: morris
.. categories:: articles, tutorials
.. tags:: ,Tutorials

