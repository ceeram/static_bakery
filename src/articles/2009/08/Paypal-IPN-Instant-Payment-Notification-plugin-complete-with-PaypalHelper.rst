Paypal IPN (Instant Payment Notification) plugin complete with PaypalHelper
===========================================================================


by %s on August 11, 2009

I've created a PayPal IPN (Instant Payment Notification) plugin that
includes a handy helper that will build your paypal buttons for you
(Checkout, Add to Cart, Subscribe, and Donate). The Paypal IPN Plugin
logs, and records any transaction made through your application and is
completely customizable via its config and on the fly options. The
Plugin is also very simple to switch between sandbox/live paypal.
The biggest benefit to using the PayPal IPN plugin is that it doesn't
require a Website Payment Pro account (monthly charge) to use like the
other PayPal implementations in the bakery. The IPN service is free
from paypal; you simply have to enable it. Whenever paypal receives a
transaction (complete or failure) your app will be notified.

This plugin will process Instant Payment Notifications sent from
paypal, log it, and save a record in your database. The plugin
provides an afterPaypalNotification callback you can use to apply post
transaction logic to your application (set an order to paid, give a
user premium access, etc...). This plugin also features a Paypal
Helper to create various buttons to use with your paypal IPN service.
I hope you find it useful.

Paypal IPN plugin. (Paypal Instant Payment Notification)
Version 1.4
Author: Nick Baker (nick@webtechnick.com)

Website: `http://www.webtechnick.com`_

Browse, Download, or Checkout the Plugin.
Browse: `http://projects.webtechnick.com/paypal_ipn`_
Download: `http://projects.webtechnick.com/paypal_ipn.tar.gz`_
SVN: `https://svn2.xp-dev.com/svn/nurvzy-paypal-ipn`_

I suggest reading the README.txt file included within the plugin.
Installation is very straight forward.

Install:
1) Copy plugin into your /app/plugins/paypal_ipn directory
2) Run the /plugins/paypal_ipn/paypal_ipn.sql in your database.
3) Add the following into your /app/config/routes.php file (optional):

Controller Class:
`````````````````

::

    <?php 
      /* Paypal IPN plugin */
      Router::connect('/paypal_ipn/process', array('plugin' => 'paypal_ipn', 'controller' => 'instant_payment_notifications', 'action' => 'process'));
      
      /* Optional Routes, but nice for administration */
      Router::connect('/paypal_ipn/edit/:id', array('admin' => true, 'plugin' => 'paypal_ipn', 'controller' => 'instant_payment_notifications', 'action' => 'edit'), array('id' => '[a-zA-Z0-9\-]+', 'pass' => array('id')));
      Router::connect('/paypal_ipn/view/:id', array('admin' => true, 'plugin' => 'paypal_ipn', 'controller' => 'instant_payment_notifications', 'action' => 'view'), array('id' => '[a-zA-Z0-9\-]+', 'pass' => array('id')));
      Router::connect('/paypal_ipn/delete/:id', array('admin' => true, 'plugin' => 'paypal_ipn', 'controller' => 'instant_payment_notifications', 'action' => 'delete'), array('id' => '[a-zA-Z0-9\-]+', 'pass' => array('id')));
      Router::connect('/paypal_ipn/add', array('admin' => true, 'plugin' => 'paypal_ipn', 'controller' => 'instant_payment_notifications', 'action' => 'edit'));
      Router::connect('/paypal_ipn', array('admin' => true, 'plugin' => 'paypal_ipn', 'controller' => 'instant_payment_notifications', 'action' => 'index'));/*
      /* End Paypal IPN plugin */
    ?>

Paypal Setup:
1) I suggest you start a sandbox account at
`https://developer.paypal.com`_
2) Enable IPN in your account.

Administration: (optional) If you want to use the built in admin
access to IPNs:
1) Make sure you're logged in as an Administrator via the Auth
component.
2) Navigate to `http://www.yoursite.com/paypal_ipn`_

Paypal Button Helper: (optional) if you plan on using the paypal
helper for your PayNow or Subscribe Buttons
1) Update /paypal_ipn/config/paypal_ipn_config.php with your paypal
information
2) Add 'PaypalIpn.Paypal' to your helpers list in app_controller.php:

Controller Class:
`````````````````

::

    <?php 
           var $helpers = array('Html','Form','PaypalIpn.Paypal');
    ?>

3) Usage: (view the actual /paypal_ipn/views/helpers/paypal.php for
more information)
$paypal->button(String tittle, Options array);
Examples:

View Template:
``````````````

::

    
    <?php
    //Pay Now Button
    echo $paypal->button('Pay Now', array('amount' => '12.00', 'item_name' => 'test item'));
    //Pay Now Button with Image
    echo $paypal->button('pay_now.jpg', array('amount' => '12.00', 'item_name' => 'test item'));
    
    //Subscribe Button
    echo $paypal->button('Subscribe', array('type' => 'subscribe', 'amount' => '60.00', 'term' => 'month', 'period' => '2'));
    
    //Donate Button
    echo $paypal->button('Donate', array('type' => 'donate', 'amount' => '60.00'));
    
    //Add To Cart
    echo $paypal->button('Add To Cart', array('type' => 'addtocart', 'amount' => '15.00'));
    ?>

You can seamlessly switch to your testing account by adding 'test' as
an option key.
Test Example:

View Template:
``````````````

::

    <?php echo $paypal->button('Pay Now', array('test' => true, 'amount' => '12.00', 'item_name' => 'test item')); ?>

You can also add any other valid PayPal element into the button
options.
Example:

View Template:
``````````````

::

    <?php echo $paypal->button('Pay Now', array('amount' => '12.00', 'item_name' => 'Stuff', 'return' => 'http://www.yoursite.com/thankyou')); ?>

Refer to PayPal.com for a complete list of button name-value pair
elements you can use in your buttons.

Paypal Button:
1) Use the PaypalHelper to generate your buttons for you. See Paypal
Button Helper (above) for more.
- or -
2) Make sure to use notify_url set to

::

    http://www.yoursite.com/paypal_ipn/process

in your paypal button.
Example:

View Template:
``````````````

::

    
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
      ...
      ...  
      <input type="hidden" name="notify_url" value="http://www.yoursite.com/paypal_ipn/process" />
      ...
    </form>


After Paypal Notification Callback:
After a notification is sent to your app, it is saved to your database
and an afterPaypalNotification is called (if it exists). Here is where
you can add logic to your app to do something with that specific
payment.

1) Create a function in your /app/app_controller.php like so:

Controller Class:
`````````````````

::

    <?php 
      function afterPaypalNotification($txnId){
        //Here is where you can implement code to apply the transaction to your app.
        //for example, you could now mark an order as paid, a subscription, or give the user premium access.
        //retrieve the transaction using the txnId passed and apply whatever logic your site needs.
        
        $transaction = ClassRegistry::init('PaypalIpn.InstantPaymentNotification')->findById($txnId);
        $this->log($transaction['InstantPaymentNotification']['id'], 'paypal');
    
        //Tip: be sure to check the payment_status is complete because failure transactions 
        //     are also saved to your database for review.
    
        if($transaction['InstantPaymentNotification']['payment_status'] == 'Completed'){
          //Yay!  We have monies!
        }
        else {
          //Oh no, better look at this transaction to determine what to do; like email a decline letter.
        }
      }
    ?>

I hope you find it useful.
Please, if you like the plugin, find a bug or have a feature request,
post a comment. =)

.. _http://www.yoursite.com/paypal_ipn: http://www.yoursite.com/paypal_ipn
.. _https://svn2.xp-dev.com/svn/nurvzy-paypal-ipn: https://svn2.xp-dev.com/svn/nurvzy-paypal-ipn
.. _http://www.webtechnick.com: http://www.webtechnick.com/
.. _http://projects.webtechnick.com/paypal_ipn: http://projects.webtechnick.com/paypal_ipn
.. _https://developer.paypal.com: https://developer.paypal.com/
.. _http://projects.webtechnick.com/paypal_ipn.tar.gz: http://projects.webtechnick.com/paypal_ipn.tar.gz
.. meta::
    :title: Paypal IPN (Instant Payment Notification) plugin complete with PaypalHelper
    :description: CakePHP Article related to paypal,plugin,cart,shopping,Plugins
    :keywords: paypal,plugin,cart,shopping,Plugins
    :copyright: Copyright 2009 
    :category: plugins

