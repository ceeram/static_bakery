fastfoo - cms under cakephp
===========================

by fastfoo on November 06, 2010

How install fastfoo under your installations of cakephp

Fastfoo is a CMS developed under cakephp, your installations is very
simple.

#. Properly configured with a fresh install cakephp (<1.3), with
   apache rewrite module included.

    #. Unzip the file fastfoo-0.2.tar.gz
    #. Create a new database in MySQL called 'fastfoo'. Import the sql
       file 'fastfoo / sql / fastfoo.sql' within the new BD.
    #. Copy the folder 'fastfoo / app' and overwrite the folder 'app' in
       your installation of cakephp.
    #. Finally configure the file 'app / config / database.php' for proper
       access to the database.
    #. Get online so your original installation cakephp and ready as you
       can see fastfoo was installed.


You can download fastfoo from `http://www.fast-foo.com`_


.. _http://www.fast-foo.com: http://www.fast-foo.com
.. meta::
    :title: fastfoo - cms under cakephp
    :description: CakePHP Article related to CMS,fastfoo,Articles
    :keywords: CMS,fastfoo,Articles
    :copyright: Copyright 2010 fastfoo
    :category: articles

