OthAuth's NAO Mode
==================

I needed to support users belonging to multiple groups, and today I
delved into the underworkings of OthAuth's 'nao' mode.


Configure OthAuth
~~~~~~~~~~~~~~~~~
Of course, start off with configuring the OthAuth component to use the
'nao' mode.

In the oth_auth.php component:

::

    
    TBD

or in your app_controller.php file:

::

    
    TBD



Create HABTM Relationships
~~~~~~~~~~~~~~~~~~~~~~~~~~
Create the relationships in the User and Group models, and don't
forget to creat the users_groups table too.


Tweak the oth_auth.php Component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
delete the unbindAll calls - it eats up the 'hasAndBelongsToMany'
relationships

+ one other spot? (TBD)


Add the group_id to the login form
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
If you're using the asc, then group_id should be > any level set for
your defined groups. If you're using 'desc' mode, then it should
probably be below.


OthAuth Helper Tweaks
~~~~~~~~~~~~~~~~~~~~~
method: inGroup()


.. author:: chewie124
.. categories:: articles, tutorials
.. tags:: othauth,authentication,component,Tutorials

