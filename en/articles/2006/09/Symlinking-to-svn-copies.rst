Symlinking to svn copies
========================

by %s on September 29, 2006

If you want to keep one install of CakePHP for all your applications,
then you may want to use a symlink to make it easier when you want to
adjust your index.php.
Adjusting the index.php and using bake.php can be annoying if you have
your cake directory tucked away deep in your folder structure or on
another partition. With two versions of Cake, this becomes even more
annoying. I dont know how they ended up there, but luckily the symlink
has come to the rescue once again.

I work on OSX, so your commands and results may vary depending on your
operating system. Also, this assumes you use svn, if you dont go get
it. If not, then you can still make use of the symlink part, but you
will not have as much fun. The $ represents a command line entry.


Grab the repos
++++++++++++++

The trunk is the current stable version

::

    
    $ svn co https://svn.cakephp.org/repo/trunk /path/to/my/other/partition/and/cake/directory/1.1.x.x

1.2 is currently here:

::

    
    $ https://svn.cakephp.org/repo/branches/1.2.x.x /path/to/my/other/partition/and/cake/directory/1.2.x.x



Create the symlinks
+++++++++++++++++++

::

    
    $ ln -s /path/to/my/other/partition/and/cake/directory/1.1.x.x /cake/1.1.x.x
    $ ln -s /path/to/my/other/partition/and/cake/directory/1.1.x.x /cake/1.2.x.x

Now, when we refer to /cake/1.1.x.x or /cake/1.2.x.x, we know we are
accessing the latest version of CakePHP 1.2.

We can access the bake script with:

::

    
    $ cd /cake/1.1.x.x/cake/scripts
    $ php bake.php -project
    or
    $ cd /cake/1.2.x.x/cake/scripts
    $ php bake.php -project

The code in /app/webroot/index.php might look like:

::

    
    define('CAKE_CORE_INCLUDE_PATH', '/cake/1.1.x.x');
    or
    define('CAKE_CORE_INCLUDE_PATH', '/cake/1.2.x.x');




.. meta::
    :title: Symlinking to svn copies
    :description: CakePHP Article related to symlink,osx,svn,subversion,General Interest
    :keywords: symlink,osx,svn,subversion,General Interest
    :copyright: Copyright 2006 
    :category: general_interest

