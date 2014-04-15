Loading SQL with table prefixes
===============================

by zomg on October 08, 2006

Your project uses a table prefix because you share a database, or you
donâ€™t want to clash table names. Maybe you just like the groupings
that prefixes provide. Now what if someone hands you some SQL code
that you need to load, but it doesnâ€™t have your prefixes applied?
What do you do?


Search and Replace is Your Friend!
``````````````````````````````````
Simply open up that SQL code in your favorite text editor. Use the
Search/Find and replace tool. In Notepad++, it’s Ctrl + H. Don't
include the actual quotes below!


#. [li]Search for CREATE TABLE and replace with CREATE TABLE pre_ .
   [li]Search for INSERT INTO and replace with INSERT INTO pre_ .
   [li]Search for REPLACE and replace with REPLACE pre_ . [li]Search for
   ALTER TABLE and replace with ALTER TABLE pre_ .



Tips
````


#. [li]Be careful not to replace something that isn’t supposed to have
   a pre_ tacked onto it. [li]Be sure to include a trailing space in the
   search, it can help get rid of some false positives. You don't want a
   trailing space after the pre_ in replace, because the underscore needs
   to be connected to the table name. [li]Replace pre_ with whatever your
   prefix really is. [li]Keep a back-up of this file with the changed
   prefixes, as well as the original non changed one. [li]With the new
   back-up, all you have to do is search for pre_ now, if you ever wanted
   to change prefixes again.




.. author:: zomg
.. categories:: articles, general_interest
.. tags:: sql,prefix,General Interest

