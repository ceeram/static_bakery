Installing cakePHP on Ubuntu
============================

by %s on April 10, 2007

I use Ubuntu 6.10 Server with the LAMP package as my development
server. These instructions have not been tested outside of that
environment but should work on other Ubuntu installations.
Installing Cake 1.1.x on Ubuntu 6.10 (with LAMP package installed -
PHP5)

1.Ensure mod-rewrite is enabled by running the following command:

sudo a2enmod rewrite

If mod-rewrite is not enabled, follow the on-screen instructions to
install it.

2.Next, edit the site config file for which you want to enable mod-
rewrite. This is likely to be /etc/apache2/sites-available/default.
Edit the file as root using your text editor of choice (try sudo Nano
default)

3.Add the following entry:

AllowOverride All

make sure you format it as above, including the line breaks. Add it
below the entry for /www/var. Add the top of the file, change the
document root to:

/var/www/app/webroot

4.Restart apache using:

sudo /etc/init.d/apache2 force-reload

You should now be able to view http://localhost/ with the correct css
formatting for the cake default page.

.. meta::
    :title: Installing cakePHP on Ubuntu
    :description: CakePHP Article related to cake,cake ubuntu LAMP,lampp,ubuntu,Tutorials
    :keywords: cake,cake ubuntu LAMP,lampp,ubuntu,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

