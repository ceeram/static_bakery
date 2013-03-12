

CakePHP on IIS
==============

by %s on January 29, 2008

In this article you find all the information required for successfully
run CakePHP web applications under Microsoft IIS web server.
I really love CakePHP. As Iï¿½m primary a Windows developer, I am very
interested in being able to run my CakePHP web applications under IIS.
For this reason I spent an afternoon trying to make it working under
the Microsoft web server. In this article I explain how you can
achieve the same result.

1. First of all download the URL Rewrite Filter for IIS (it's free and
open source). Since it seems the official web site
(`http://www.iismods.com/`_) has been closed, I have managed to
publish a copy of the filter on my company web site. You can download
it from the following URL:

`http://www.creativepark.it/downloads/iismod_rewrite.zip`_
2. The installation is pretty straight forward.


+ First copy the mod_rewrite.dll and mod_rewrite.ini files in a
  directory. For this article I use C:\\Inetpub\\.
+ Open the IIS Management Console . Under Windows XP you can start it
  clicking on Start -> Control Panel -> Administrative Tools -> Internet
  Information Services. Otheriwse you can execute
  "%SystemRoot%\\system32\\inetsrv\\iis.msc" with Start -> Run...
+ Select the web site on which you want to install the URL Rewrite
  Filter, open the Action menï¿½ and then click on Properties.
+ Select the ISAPI Filter tab and click on Add.
+ Insert a name for the filter (i.e. URL Rewrite Filter) and select
  the the mod_rewrite.dll using the Browse... button (i.e.
  C:\\Inetpub\\mod_rewrite.dll).

3. Edit the mod_rewrite.ini as follows:

::

    Debug 0
    Reload 5000
    RewriteRule ^/$    /index.php?REQUEST_URI=index.php [L]
    RewriteRule ^(.*)$ /index.php?REQUEST_URI=$1        [L]

4. Close the IIS Management Console and restart the web server. You
can use the included restart_iis.bat batch script, the IIS Management
Console or the Services Management Console.

5. Add the following code at the beginning of the index.php file of
CakePHP:

::

    /* Begin IIS MOD_REWRITE Code */
    $_SERVER['REQUEST_URI'] = $_GET['REQUEST_URI'];
    unset($_GET['REQUEST_URI']);
    /* End IIS MOD_REWRITE Code */

If you have correctly followed the above steps you should be able to
run your CakePHP web applications under IIS.

Support for Virtual Folders
Under client Windows Operating Systems (i.e. Windows 2000/XP Pro), IIS
allows to run only one web site. If you need to test multiple web
applications, you can use the Virtual Folders. For instance, if you
want to put a CakePHP web application into a /cakephp Virtual Folders,
you have to edit the mod_rewrite.ini file as follows:

::

    Debug 0
    Reload 5000
    RewriteCond HTTP_HOST localhost
    RewriteRule ^/cakephp/?$    /cakephp/index.php?REQUEST_URI=index.php [L]
    RewriteRule ^(/cakephp/.*)$ /cakephp/index.php?REQUEST_URI=$1 [L]

You can duplicate the two RewriteRule rows multiple time to support
multiple web applications, just remeber to replace che /cakephp string
with the right Virtual Folder path.

P.S. If you have a copy of iismod.com Mod Rewrite and Mod Auth
sources, please send it to me. I have downloaded them a while ago but
I can't find them anymore.

.. _http://www.iismods.com/: http://www.iismods.com/
.. _http://www.creativepark.it/downloads/iismod_rewrite.zip: http://www.creativepark.it/downloads/iismod_rewrite.zip
.. meta::
    :title: CakePHP on IIS
    :description: CakePHP Article related to Webserver,IIS,Tutorials
    :keywords: Webserver,IIS,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

