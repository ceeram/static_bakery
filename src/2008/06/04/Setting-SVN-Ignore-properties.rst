Setting SVN Ignore properties
=============================

by AD7six on June 04, 2008

If you use svn, here's a very simple script that you can use to set
your svn ignore properties so that your tmp folder, swap files and
install specific files get ignored by svn.
Have you ever seen a page full of something like this:

::

    
    $ svn status
    ?      app_controller.php
    ?      app_controller.php~
    ?      tmp/cache/persistent/cake_core_core_paths
    ?      tmp/cache/persistent/cake_core_dir_map
    ?      tmp/cache/persistent/cake_core_file_map
    ?      tmp/cache/persistent/cake_core_default_en_us
    ?      tmp/cache/persistent/cake_core_object_map
    ?      tmp/logs/debug.log
    ?      webroot/css/admin.css
    ?      webroot/css/admin.css.swp
    ?      config/database.php
    ?      config/sql/db_structure.sql
    M      config/core.php
    etc.

and wished that what you saw looked like this:

::

    
    $ svn status
    ?      app_controller.php
    ?      webroot/css/admin.css
    ?      config/sql/db_structure.sql

Which gives you, as well as less text to filter though, the possiblity
to do things like this:

::

    
    $ # Add everything that's missing to the repo
    $ svn status | grep '\?' | awk '{print $2;}' | xargs svn add
    A      app_controller.php
    A      webroot/css/admin.css
    A      config/sql/db_structure.sql

It's easy to achieve simply by running the following commands - or by
putting them in a sh|bat file and calling from your app folder:

::

    
    #set svn ignores
    svn propset svn:ignore "*.swp
    *.swo
    *~
    svn-commit*" . -R
    svn propset svn:ignore "*.swp
    *.swo
    *~
    core.php
    database.php
    svn-commit*" config
    svn propset svn:ignore "*" tmp -R

This will exclude swap files created by your editor, exclude your
core.php 1 and database.php files from the repository, and ignore
absolutely anything that is in the tmp folder.

That's it - less ? and more concise information in a nutshell.

Bake On!
1 SVN will not ignore things you've already added to your repsoitory -
I recommend never adding core.php or database.php to your repository
to prevent the possibility of accidentally changing database or core
(typically debug) settings when checking in or updating.


.. author:: AD7six
.. categories:: articles, snippets
.. tags:: svn,subversion,version control,svn ignore,Snippets

