OpenInviter For CakePHP
=======================

by %s on October 13, 2009

You can now integrate an open source into cakePHP for Invite Your
Friends Direct form email services. Invite Your Friends From Your Web
Site.
After Spending continuous 48 hour of time on internet . i have create
a small function with the help of open source Tool openinviter you can
download it form here.

`http://openinviter.com`_.

you have to 1st create an account to download the open source library.
which is wonder full and work nice.

now next is step to unzip it and place OpenInviter Folder in to
vendors folder which is in the root of cakePHP.

This code can also work with 1.2.5

now it's finish.
you just have to create a function in controller.

here is the example.


Controller Class:
`````````````````

::

    <?php 
    
    // thi line is important.  this will work just like import in core php
    App::import('Vendor', 'openinviter', array('file' => 'openinviter'.DS.'openinviter.php'));
            
            
    $inviter = new OpenInviter();
    $oi_services = $inviter->getPlugins();
            
            
    $inviter->startPlugin('google'); 
    // supply a file name with ought .php in the parameter. you will fine the files in the "vendors/openinviter/plugins/" In the Plugins you will find all the files which communicate with the respected services to fatch data. you will pass google, yahoo etc.
    
    // it will return error if any
    $internal = $inviter->getInternalError();
    
    
    // this is use for login in to services just like gmail.com account 1st. parameter take login id and 2nd. parameter takes password
    $inviter->login('userid@gmail.com','password');
    
    // this will return the array which contain all the email address from the account you want to fetch.
    $contacts = $inviter->getMyContacts();
    
    
    ?>


you just need to change 2 lince according to you need.

For Service you want to fetch email addresses
1.

::

    $inviter->startPlugin('google');


for User ID and Password
Full Email Address and password eg. `userid@gmail.com`_ , password 2.

::

    $inviter->login('userid@gmail.com','password');



.. _http://openinviter.com: http://openinviter.com/
.. _userid@gmail.com: mailto:userid@gmail.com=userid@gmail.com
.. meta::
    :title: OpenInviter For CakePHP
    :description: CakePHP Article related to ,Tutorials
    :keywords: ,Tutorials
    :copyright: Copyright 2009 
    :category: tutorials

