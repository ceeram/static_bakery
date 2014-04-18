Working with Apache Aliases During Cake Setup
=============================================

In trying to bring up an installation on our development server I ran
into a few problems with missing CSS, JS, and Images thanks to
improper references in the rendered HTML. Our development server is a
LAMP environment and we rely very heavily on apache aliases to keep
our sites seperated. That means that you can bring cake up in a
subdirectory (www.mysite.com/cakedev/) instead of your server root
(www.mysite.com/). Here's how I got cake to work for us.
First off, and please let me know of there's another way to do this,
but you need to disobey the comments in your webroot index.php. Even
though the variable WEBROOT_DIR is shown up below that DO NOT EDIT
comment, that's the key to setting the root web directory.

I put all of the cake files into
/usr/developer/cake/cake/

And all of my webroot files into:
/usr/developer/cake/www/

This means that there is no longer a webroot folder found in /app/ and
all of the files that were in that webroot directory have been placed
in your new directory, for me that was /usr/developer/cake/www/

Setup `http://www.site.com`_ with an Apache Alias in your apache
vhosts file:

Alias /cakedev /usr/developer/cake/www

So that `http://www.site.com/cakedev`_ loads web files from
/usr/developer/cake/www

Now, for the settings in index.php in your webroot folder (for me
that's: /usr/developer/cake/www/index.php):

define('ROOT', '/usr/developer/cake/cake/');
define('APP_DIR', 'app');
define('CAKE_CORE_INCLUDE_PATH', '/usr/developer/cake/cake');
define('WEBROOT_DIR', 'cakedev');

Most installation instructions leave off that last one (WEBROOT_DIR)
and I have no idea why it is appearing in the DO NOT EDIT zone on the
latest release, but without that line all of your page references will
be prefaced with a simple '/' instead of '/cakedev/'.

To round everything out, don't forget to update your .htaccess file
with 'RewriteBase /cakedev' so that you don't break MOD_REWRITE.

This is my first installation, so take this advice with a grain of
salt - but it worked for me.

.. _http://www.site.com: http://www.site.com/
.. _http://www.site.com/cakedev: http://www.site.com/cakedev

.. author:: ChopFoo
.. categories:: articles, tutorials
.. tags:: ,Tutorials

