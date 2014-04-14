Installing CakePHP on MacOS X.
==============================

by momendo on October 04, 2006

This is how I setup CakePHP on my MacBook with PHP5 and MySQL5. I have
MacOS X 10.4.8 installed.
Here's a quick howto to get CakePHP installed on MacOS X. Your goal is
to install PHP, a database, CakePHP, and have it load at
http://localhost through a browser in your local workstation
environment.

Note:

This was tested on MacOS 10.4.8. This document assumes you know how to
open a terminal, decompress tarballs, edit files, and navigate a
shell. Apache 1.x is already installed in a default MacOSX install, so
you don't need to worry about installing it.

Download and install PHP 5 for MacOS X. There's a nice package here.
Follow their instructions.
`http://www.entropy.ch/software/macosx/php/`_
If you're using MySQL 5, download the MySQL DMG package from MySQL.

Version 4 => `http://dev.mysql.com/downloads/mysql/4.0.html`_
Version 5 => `http://dev.mysql.com/downloads/mysql/5.0.html`_
Then follow their instructions to install and set the database root
user password.

`http://www.entropy.ch/software/macosx/mysql/`_
Download the latest CakePHP.

`http://cakephp.org/downloads`_
Follow the reference install document here. There are some specific
MacOS X setup that is covered below:

`http://manual.cakephp.org/chapter/installing`_
Open a terminal. Applications>Utilities>Terminal.

Decompress Cake into your webroot on MacOSX. That's located here:
/Library/WebServer/Documents

In my case, I decompressed Cake into my home folder under ~/Sites so
that I can also do local development under my username:
http://localhost/~username/cake And then I created a symbolic link
under /Library/WebServer/Documents to point to
/Users/username/Sites/cake

In /Library/WebServer/Documents type:

::

    ln -s /Users/username/Sites/cake cake

Then edit your httpd.conf file. That's located here.

/etc/httpd/httpd.conf

Edit the file and change the document root line from:

::

    DocumentRoot "/Library/WebServer/Documents"

to

::

    #DocumentRoot "/Library/WebServer/Documents"
    DocumentRoot "/Library/WebServer/Documents/cake/app/webroot"

We remarked the old setting and switched the docroot to the cake
webroot folder. Don't worry. http://localhost/~username will still
work. But http://localhost/ will always point to cake in your local
environment.

Then find the block:

::

    <Directory "/Library/WebServer/Documents">

Find & edit:

::

    AllowOverride None

to

::

    AllowOverride All

This will allow mod_rewrite to work. Save the edits.

Back in the GUI, we need to restart Apache to use our new settings.
Apple Menu>System Preferences>Sharing>Services>Personal Web Sharing

Stop the service and start it again. Make sure you have the Firewall
off or allow the Personal Web Sharing service so that it's accessible
through a browser.

You can also restart the server at the command line:

::

    sudo su
    apachectl restart

Next you need to add a user in your database that cake will access and
then add the access info in the database file
(app/config/database.php). That's covered in the cake install
document.

Open a browser and load your cake site:

::

    http://localhost

or

::

    http://127.0.0.1

If all goes well, you should see the cake default page and it should
be able to connect to your database. If not, check the logs and try to
diagnose the problem:

Applications>Utilities>Console>Logs>/var/log>httpd>error_log

If you would like a virtual hosting setup like
`http://appname.localhost/`_, add the following to apache:

::

    <VirtualHost *:80>
        ServerName appname.localhost
        DocumentRoot "/Library/WebServer/Documents/appname/webroot"
    
        ErrorLog /var/log/httpd/appname_error.log
        CustomLog /var/log/httpd/appname_access.log combined
    </VirtualHost>

Then add appname.localhost to your hosts file:

/etc/hosts

::

    127.0.0.1    appname.localhost   localhost

That's it! momendo at yahoo dot com

.. _http://www.entropy.ch/software/macosx/mysql/: http://www.entropy.ch/software/macosx/mysql/
.. _http://dev.mysql.com/downloads/mysql/5.0.html: http://dev.mysql.com/downloads/mysql/5.0.html
.. _http://manual.cakephp.org/chapter/installing: http://manual.cakephp.org/chapter/installing
.. _http://www.entropy.ch/software/macosx/php/: http://www.entropy.ch/software/macosx/php/
.. _http://cakephp.org/downloads: http://cakephp.org/downloads
.. _http://appname.localhost/: http://appname.localhost/
.. _http://dev.mysql.com/downloads/mysql/4.0.html: http://dev.mysql.com/downloads/mysql/4.0.html
.. meta::
    :title: Installing CakePHP on MacOS X.
    :description: CakePHP Article related to installation,osx,install,mac,setup,macbook,mysql,httpd,apache,Tutorials
    :keywords: installation,osx,install,mac,setup,macbook,mysql,httpd,apache,Tutorials
    :copyright: Copyright 2006 momendo
    :category: tutorials

