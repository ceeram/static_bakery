CakePHP on IIS6 with FastCGI, SQL Server 2005, and ISAPI_Rewrite
================================================================

by jamesmking on April 12, 2008

A detailed walkthrough on how to set up and configure a fast, stable
PHP 5.x environment on Windows Server 2003 for CakePHP.
Traditionally, hosting a PHP application on a Windows/IIS environment
has been a very bad idea due to poor performance and stability.
However, due to Microsoft's recent support for `PHP on IIS`_, this is
no longer the case! Rather than having to resort to installing and
maintaining Apache just for our PHP apps, we can now stay within the
x86 Windows Server ecosystem and set up a stable environment for PHP,
making CakePHP a viable framework for developers tied to Windows
hosting environments.


Requirements and Prerequisites
``````````````````````````````
For this guide, we'll be setting up a 32-bit Windows Server 2003
environment with PHP under FastCGI and will connect to a MS SQL Server
2005 database. For URL Rewriting, I'm using the freeware edition of
`ISAPI_Rewrite 3`_. There are specific limitations related to using
the freeware edition of this product - namely you can only set up
server-wide rules. Therefore if you're hosting non-Cake applications
on the same server, this particular product won't work for you (or
you'll have to dish out for the retail version).

Finally you'll need administrator level access on the server in order
to install each piece of software and configure permissions. This
guide also assumes you have some knowledge of administering an IIS
server (creating sites, handling domains, setting permissions).


FastCGI and PHP 5
`````````````````
First a little context. One of the big reasons PHP hasn't played nice
with IIS in the past is due to the fact that not all PHP extensions
are thread-safe (...and IIS is a multi-threaded environment as opposed
to multi-process like Apache/*nix). If you're interested in learning
more about the differences between the thread-safe and non-thread-safe
versions of PHP, check out `this article`_.

So the first thing we're going to do is install FastCGI. As of this
writing, Microsoft is offering an `RTM version for IIS 6.0`_. Download
the 32-bit version and run the installer - simple as that.

Once that's done, we need a copy of PHP. Thankfully due to the way
FastCGI works, instead of trying to thread PHP, it'll launch multiple
instances of PHP instead. Therefore for maximum speed and
compatibility, we'll need to specifically hunt down the non-thread
safe Windows version of PHP from the `PHP downloads page`_. As of this
writing, the newest version is `5.2.5`_.

Uncompress the zip file to a directory of your choosing. For
simplicity's sake, I popped it into C:/php and will be referencing
this path throughout this article. Make a copy of php.ini-recommended
and rename it to php.ini. We'll come back to that file later.


Set up CakePHP
``````````````
We'll set up Cake for use with a single site for simplicity's sake.
For alternate configurations, check the `installation section`_ of the
`CakePHP Manual`_.

Download the latest version of CakePHP. Decompress the zip file in
your server's default web root directory. Different server
configurations may call this directory different things and place it
in different places, but the most common location is C:/Inetpub.

Done! Moving on.


Configure FastCGI
`````````````````
Navigate to %WINDIR%/system32/inetsrv. Find the fcgiext.ini file and
open it. Append the following values to the end:

::

    
    [Types]
    php=PHP
    
    [PHP]
    ExePath=c:\PHP\php-cgi.exe
    InstanceMaxRequests=10000
    EnvironmentVars=PHP_FCGI_MAX_REQUESTS:10000

You can adjust the number of maximum requests, but you MUST make sure
the two values match.


Configure IIS
`````````````
Open up the IIS Administration Console (Start > Run > inetmgr.exe).
Open Web Sites and click on "Default Web Site". You should see the
cake folders you decompressed earlier.

The first thing we need to do is change the web root. Right click on
"Default Web Site" in the left navigation and click "Properties".
Navigate to the "Home Directory" tab, and click on "Browse" and choose
the "app/webroot" folder.

The next step will be to tell IIS to use FastCGI to handle PHP
documents. On that same "Home Directory" tab, click the
"Configuration" button. Under the "Mappings" tab click "Add".

For the executable browse to "%WINDIR%/system32/inetsrv" and choose
the fcgiext.dll file. Enter ".php" as the extension, under "Verbs"
click "Limit to" and enter "GET,HEAD,POST". Make sure "script engine"
and "verify that file exists" are checked and click OK!

Finally we're going to tell IIS that index.php is the default
document. To do this, navigate to the "Documents" tab, Add "index.php"
to the list and move it to the top (if so desired).


File Permissions
````````````````
Cake needs write permissions in its temporary directories. Open up
Windows Explorer and navigate to C:/Inetpub/app.
Right click on the tmp folder and click "Properties". Navigate to the
"Security" tab and click on the "Internet Guest Account". Grant this
user read/write/modify permissions on this folder.


Configure PHP
`````````````
Open up that php.ini file we created earlier. The following settings
need to be uncommented and/or changed. Search for them in the php.ini
file and update the values:

::

    
    extension_dir = "C:\PHP\ext\"
    
    cgi.force_redirect = 0
    
    cgi.fix_pathinfo = 1
    
    fastcgi.impersonate = 1
    


Make sure it's working!
```````````````````````
Restart your website in the IIS Administration Console by clicking on
"Default Web Site", and clicking the square "stop" button in the
toolbar. Once it has stopped, click the triangular "go" button to
start it again.

Lets make sure all our hard work has paid off so far. Navigate to your
server's IP/domain and you should see the default CakePHP page. If
you've set up everything correctly so far, you should see the
following messages:

Your tmp directory is writable.

The FileEngine is being used for caching. To change the config edit
APP/config/core.php

Your database configuration file is NOT present.
Rename config/database.php.default to config/database.php

If you aren't getting the default CakePHP page, or are getting a
FastCGI error of some kind, review the previous steps carefully and
make sure you didn't miss anything. Each setting counts!


Connecting to MSSQL 2005
````````````````````````
The first thing we need to do is edit the php.ini file to include the
php_mssql.dll extension. Search for that line in the file and un-
comment it.

The most frustrating thing for people trying to connect to MSSQL 2005
from PHP 5.2.5 is the fact that the ntwdblib.dll file included is out
of date and will just throw unhelpful "can't connect" or "can't find
the server" errors. The version of the file you need is 2000.80.194.0
(or later presumably). You can find this file on your SQL Server
install disc, or if you do some Googling you'll be able to find the
file for download. The `MSSQL page in the PHP documentation`_ has lots
of discussion about this issue.

Once you've updated your copy of ntwdblib.dll, you'll need to set up
CakePHP's database file. Under app/config rename database.php.default
to database.php. Open up the file and provide the appropriate
credentials for your SQL Server. Make sure you set the driver
attribute to "mssql".

Restart your website under the IIS Administration Console again and
reload the page in your browser. Now you should see the following
messages:

Your tmp directory is writable.

The FileEngine is being used for caching. To change the config edit
APP/config/core.php

Your database configuration file is present.

Cake is able to connect to the database.

Congratulations! We've got CakePHP running in a stable Windows/IIS
environment! We're almost done!


Configuring ISAPI Rewrite
`````````````````````````
Download the ISAPI Rewrite freeware edition and run the installer.
Once it's finished, navigate to the install directory (ie C:/Program
Files/Helicon/ISAPI_Rewrite3) and run "Helicon Manager.exe". Click on
"IIS Web Sites" in the left navigation and then on the "Edit" button.

Paste the following rewrite rules and save:

::

    
    RewriteEngine on
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [L,QSA]

With this final piece of the puzzle in place, you should have a fully
functional install of CakePHP - pretty URLs and all! Hopefully this
article will help Windows server admins and developers out there save
some time and headaches! If there's anything I've overlooked or
skimmed over too broadly, I'd be happy to revise this article.

.. _PHP downloads page: http://php.net/downloads.php
.. _CakePHP Manual: http://book.cakephp.org
.. _ISAPI_Rewrite 3: http://www.helicontech.com/download-isapi_rewrite3.htm
.. _installation section: http://book.cakephp.org/view/32/installation
.. _MSSQL page in the PHP documentation: http://php.net/mssql
.. _this article: http://www.iis-aid.com/articles/my_word/difference_between_php_thread_safe_and_non_thread_safe_binaries
.. _PHP on IIS: http://iis.net/php
.. _5.2.5: http://php.net/get/php-5.2.5-nts-Win32.zip/from/a/mirror
.. _RTM version for IIS 6.0: http://www.iis.net/downloads/default.aspx?tabid=34&g=6&i=1521

.. author:: jamesmking
.. categories:: articles, tutorials
.. tags:: IIS,mssql,windows,sql server 2005,isapi_rewrite,windows
server 2003,fastcgi,Tutorials

