500 Errors With 1and1 Hosting/Apache 1.x
========================================

by %s on September 20, 2007

When I uploaded a Cake-based site to a 1and1 shared hosting account, I
got some mysterious 500 errors despite the site working perfectly on
my dev machine.
I don't know if 1and1 is just weird or if it's an Apache 1.x issue but
when I uploaded my Cake-based site from my dev computer where it was
working perfectly running on Apache 2.x to my 1and1 account, I was
getting 500 Internal Server Errors.

What worked for me was to change the three .htaccess files as follows:

root .htaccess file

::

    
    <IfModule mod_rewrite.c>
       RewriteEngine on
       RewriteRule    ^$ /app/webroot/    [L]
       RewriteRule    (.*) /app/webroot/$1 [L]
    </IfModule>


app/.htaccess

::

    
    <IfModule mod_rewrite.c>
        RewriteEngine on
        RewriteRule    ^$    /webroot/    [L]
        RewriteRule    (.*) /webroot/$1    [L]
     </IfModule>

app/webroot/.htaccess

::

    
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
    </IfModule>

Note the added slashes. Also, this works for when Cake is the root
folder of your website, i.e. you simply go to `www.example.com`_, not
`www.example.com/path/to/cake`_.

If you do have your site in a sub-folder, like
`www.example.com/path/to/cake`_, use the following instead:

root .htaccess file

::

    
    <IfModule mod_rewrite.c>
       RewriteEngine on
       RewriteRule    ^$ /path/to/cake/app/webroot/    [L]
       RewriteRule    (.*) /path/to/cake/app/webroot/$1 [L]
    </IfModule>


app/.htaccess

::

    
    <IfModule mod_rewrite.c>
        RewriteEngine on
        RewriteRule    ^$    /path/to/cake/app/webroot/    [L]
        RewriteRule    (.*) /path/to/cake/app/webroot/$1    [L]
     </IfModule>

app/webroot/.htaccess

::

    
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ /path/to/cake/app/webroot/index.php?url=$1 [QSA,L]
    </IfModule>

Hope this saves someone days of frustration.

.. _www.example.com: http://www.example.com
.. _www.example.com/path/to/cake: http://www.example.com/path/to/cake
.. meta::
    :title: 500 Errors With 1and1 Hosting/Apache 1.x
    :description: CakePHP Article related to mod_rewrite,htaccess,error,Rewrite,apache,1and1,500,hosting,host,Tutorials
    :keywords: mod_rewrite,htaccess,error,Rewrite,apache,1and1,500,hosting,host,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

