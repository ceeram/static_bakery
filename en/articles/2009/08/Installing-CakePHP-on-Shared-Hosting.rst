Installing CakePHP on Shared Hosting
====================================

by %s on August 29, 2009

I am new to CakePHP and had a great deal of trouble getting it working
on my hosted site with IXWebHosting. This is how I achieved a working
installation.
I recently installed CakePHP on my hosted web but couldn't get it to
work quite right. After a few hours mucking around I've managed to get
what I think is the correct installation. Please feel free to comment
and correct me where I have made mistakes.

My hosting service allows me to publish multiple websites/domains on
the one account. They have FTP access to upload the websites and each
website/domain appears as a separate directory off the root directory.

::

    
    /
      /site1.com
         site1_files.htm
      /site2.com
         site2_files.htm
      /site3.com
         site3_files.htm
    

My original installation was to simply unzip the CakePHP files to my
new site. The result was something like:

::

    
    /
      /site1.com
         site1_files.htm
      /site2.com
         site2_files.htm
      /site3.com
         site3_files.htm
      /site4.com
         /cake
            /config
            /console
            /libs
            /tests
            basics.php
            bootstrap.php
            dispatcher.php
         /app
            /config
            /controllers
            /models
            /tests
            /tmp
            /vendors
            /webroot
                /css
                /files
                /img
                /js
                .htaccess
                index.php
            .htaccess
            index.php
         .htaccess
         index.php
         

I have not listed all files and directories here but you get the
idea...

This is not the recommended installation for a production environment
and I couldn't get the mod_rewrites working correctly. My webhost
doesn't allow the DocumentRoot to be changed in the .htaccess file nor
via the CPanel. I raised the issue with the Live Chat customer support
(using the term loosely) but they were unable to help. I had to raise
a job ticket and they would decide if it was in their interest to
change the DocumentRoot for my site.

After re-reading the installation instructions for CakePHP I went for
the Advanced installation and decided to break the directory structure
throughout my working directory. I moved the 'app', 'cake' and
'vendors' directories and contents into the root directory. I deleted
the .htaccess and index.php files from the site4.com directory (i.e
the first level). I moved the contents of the 'webroot' directory into
the site4.com directory and then deleted the empty webroot directory.
I also changed the name of my 'app' directory to 'site4app', this
allows me to run multiple cake apps from the one server, one cake app
for each domain.

This is the resulting structure:

::

    
    /
      /cake
         /config
         /console
         /libs
         /tests
         basics.php
         bootstrap.php
         dispatcher.php
      /site4app
         /config
         /controllers
         /models
         /tests
         /tmp
         /vendors
         .htaccess
         index.php
      /site1.com
         site1_files.htm
      /site2.com
         site2_files.htm
      /site3.com
         site3_files.htm
      /site4.com
         /css
         /files
         /img
         /js
         .htaccess
         index.php
      /vendors
         /css
         /js

I then had to edit the index.php file in the site4.com directory (the
old app/webroot directory) to point to the 'cake' and 'app' (now
called site4app) directories. The CPanel of my account listed the
actual directory of my site4.com domain as
/hsphere/local/home/my_account_name/site4.com. Therefore I had to
change;
ROOT to look at /hsphere/local/home/my_account_name
APP_DIR to look at /hsphere/local/home/my_account_name/site4app
CAKE_CORE_INCLUDE_PATH to look at
/hsphere/local/home/my_account_name/cake

ROOT = /hsphere/local/home/my_account_name, APP_DIR = site4app, and
CAKE_CORE_INCLUDE_PATH = /hsphere/local/home/my_account_name. The web
document root has already been set in the CPanel settings from my web
host as /hsphere/local/home/my_account_name/site4.com and therefore
doesn't need to be set anywhere in cakephp.

The section below is what the relevant section in my index.php file
looks like.

::

    
    /**
     * The full path to the directory which holds "app", WITHOUT a trailing DS.
     *
     */
        if (!defined('ROOT')) {
            define('ROOT', DS.'hsphere'.DS.'local'.DS.'home'.DS.'my_account_name');
        }
    /**
     * The actual directory name for the "app".
     *
     */
        if (!defined('APP_DIR')) {
            define('APP_DIR', 'site4app');
        }
    /**
     * The absolute path to the "cake" directory, WITHOUT a trailing DS.
     *
     */
        if (!defined('CAKE_CORE_INCLUDE_PATH')) {
            define('CAKE_CORE_INCLUDE_PATH', DS.'hsphere'.DS.'local'.DS.'home'.DS.'my_account_name');
        }
    
    /**

I used the standarf .htaccess file in the site4.com directory (the old
webroot) so it would load the correct page when someone went to
`www.site4.com`_. Anyway, mine looks like this:

::

    
    
    <IfModule mod_rewrite.c>     
      RewriteEngine On
      RewriteCond %{REQUEST_FILENAME} !-d
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
    </IfModule> 
    

I went to `www.site4.com`_ and it was all working. Nice! There was
only one problem, I was getting the session ID appended to the URL.
The advice of setting the "php_flag session.trans_id off" only caused
a web server error.

To fix the problem I changed a setting in the core.php file in the
site4app/config (old app/config) directory. Change the session.save
value from 'php' to 'cake'. It's about line 104 in my file.

::

    
      Configure::write('Session.save', 'cake');

Now when I loaded the page at `www.site4.com`_ it displayed some
errors. This turned out to be due to a missing directory. I went to
site4app/tmp (old app/tmp) directory and created a directory called
'sessions'. Loaded the page again... and..... bingo. No errors.

I hope this method also works for you. Good luck.

.. _www.site4.com: http://www.site4.com/
.. meta::
    :title: Installing CakePHP on Shared Hosting
    :description: CakePHP Article related to install,beginner,shared hosting,Tutorials
    :keywords: install,beginner,shared hosting,Tutorials
    :copyright: Copyright 2009 
    :category: tutorials

