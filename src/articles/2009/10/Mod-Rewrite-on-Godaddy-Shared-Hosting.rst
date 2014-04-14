Mod Rewrite on Godaddy Shared Hosting
=====================================

by cguyer on October 18, 2009

For those having trouble getting cake to work on godaddy.
To get cake to work on a godaddy shared linux hosting account.

Another Better Solution (Kudos to ceruleancode)
1. Upload to Server
2. Alter 1 .htaccess file
For the fix to work, modify your app/webroot/ .htaccess file to read
the following:

::

    
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
    </IfModule>

Notice the / in front of the index.php. That simple change makes
everything work great. If your project is in a folder
(`www.domain.com/application`_) just appended it to the beginning
/application/index.php.


===Old Way of Doing it====
1. Upload cake to the server, unpacked before uploading is best.
2. For these instructions to work, the domain has to be set to the
root of the cake folder. i.e. domain.com or subdomain.domain.com will
work. domain.com/cakephp has not been tested with these instructions
3. Alter 3 .htaccess files
.htaccess in root folder of cake, change the file to read this:

::

    
    <IfModule mod_rewrite.c>
       RewriteEngine on
       RewriteBase /
       RewriteRule    ^$ app/webroot/    [L]
       RewriteRule    (.*) app/webroot/$1 [L]
       
    </IfModule>

.htaccess in /app/ folder:

::

    
    <IfModule mod_rewrite.c>
        RewriteEngine on
    	RewriteBase /
        RewriteRule    ^$    webroot/    [L]
        RewriteRule    (.*) webroot/$1    [L]
     </IfModule>

.htaccess in /app/webroot/ folder:

::

    
    <IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L] 
    </IfModule>

Let me know if this was helpful or you can build off of it!


.. _www.domain.com/application: http://www.domain.com/application
.. meta::
    :title: Mod Rewrite on Godaddy Shared Hosting
    :description: CakePHP Article related to htaccess,godaddy,mod rewrite,Tutorials
    :keywords: htaccess,godaddy,mod rewrite,Tutorials
    :copyright: Copyright 2009 cguyer
    :category: tutorials

