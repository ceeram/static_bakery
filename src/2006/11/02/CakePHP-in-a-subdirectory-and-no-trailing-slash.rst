CakePHP in a subdirectory and no trailing slash
===============================================

Are you putting CakePHP in a subdirectory and experiencing problems
with no trailing slash and 400 Bad Request errors?
This worked:
`http://mysite.com/sub/`_
This did not:
`http://mysite.com/sub`_
Usually Apache figures out that "sub" is a directory and will redirect
to the slashed location for you. However, CakePHP's default
mod_rewrite rules play havoc with this and the result was a 400 Bad
Request error. The solution is relatively simple, but was not
intuitively easy to figure out.

CakePHP ships with this .htaccess file in the base CakePHP directory:

::

    
    <IfModule mod_rewrite.c>
      RewriteEngine  on
      RewriteRule ^$  app/webroot/     [L]
      RewriteRule (.*) app/webroot/$1  [L]
    </IfModule>

In my attempts to solve this problem, I had tried several rewrite
rules and redirects from within this file and from within my website's
root web directory (one above the CakePHP directory), but only the
following worked for me:

1.) Put this in your website root directory (where "sub" is your
CakePHP directory):

::

    
    <IfModule mod_rewrite.c>
      RewriteEngine  on
      RewriteRule ^sub$       sub/app/webroot/     [L]
      RewriteRule ^sub/(.*)$ sub/app/webroot/$1  [L]
    </IfModule>

2) AND, DELETE OR RENAME your .htaccess file in the CakePHP sub
directory.

Hope that helps someone.

From this blog post:
`http://www.csummers.org/index.php/2006/11/02/cakephp-in-a
-subdirectory-no-trailing-slash/`_

.. _http://www.csummers.org/index.php/2006/11/02/cakephp-in-a-subdirectory-no-trailing-slash/: http://www.csummers.org/index.php/2006/11/02/cakephp-in-a-subdirectory-no-trailing-slash/
.. _http://mysite.com/sub/: http://mysite.com/sub/
.. _http://mysite.com/sub: http://mysite.com/sub

.. author:: curtis.summers
.. categories:: articles, tutorials
.. tags:: installation,htaccess,trailing slash,subdirectory,Tutorials

