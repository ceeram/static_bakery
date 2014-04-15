CakePHP YAML Migrations and Fixtures without PEAR
=================================================

by georgious on March 09, 2009

In his article ( [url]http://bakery.cakephp.org/articles/view/cake-db-
migrations-v2-1[/url] ) at the Bakery Joel Moss describes how to use
his CakePHP migrations shell. Although that project was my inspiration
and I highly respect his work, his approach has several drawbacks:
[ulist] [li] it uses PEAR - I donâ€™t like it - thatâ€™s why I use
CakePHP. I do not find it necessary to explain this - I guess it is
highly subjective.[/li] [li] it is non-modular - you cannot use it to
deploy applications - it is just a shell with no â€˜coreâ€™[/li] [li]
it cannot make a snapshot of your already existing schema - you
havenâ€™t used migrations yet? Thatâ€™s something you will need.[/li]
[li] it cannot merge your tables - that can be crucial when you
already have different versions of the schema on different platforms
and you just want them all to be standardized[/li] [/ulist]


Some migrationsâ€™ background
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you know what migrations are - skip that part and go directly to
the next one. Otherwise - here is a simple explanation: they are
related to the database like git ( or svn or cvs ) is related to the
source. They simple provide a way to go back or forth to a previous or
succeeding state ( or version ) of the database schema. The features
of migrations are:


+ They are DB agnostic - this means that they are not affected by the
  type of the DB server youâ€™re using and help you in deploying your
  apps on different environments
+ They help you with your agile development - you can put info in and
  out without affecting the rest of the project
+ They provide you with a quick way to revert back to a previous state
  of the DB in case something went wrong
+ DB migrations can save your life if you work in a team


To illustrate the all said, I have a simple example: You create a blog
( of course a blog - thatâ€™s probably the most often used example in
the world ). Letâ€™s split the development into three simple parts -
development of blog posts CRUD; adding ACL; adding Comments to posts
and.. thatâ€™s it - we want a simple example, after all.

So, first - blog posts Create, Read, Update, Delete. You begin by
creating your DB schema. You need a simple postsâ€™ table with a few
fields - id, title, body, created. Then you write your PHP code, of
course ( out of scope here ).

But one beautiful day you decide more than one person should be able
to add posts and modify their own posts only - thatâ€™s when you read
about Access Control Lists. Now you need a few more tables - users,
aros, acos, acl and you need to add a field to the postsâ€™ table (
user_id, for example ). Our small blog is getting just a bit more
complex.

Finally, you want comments! But you have no time and ask your cousin
for a favor ( he is a good dev, after all ). He adds another table for
that. And then decides only registered users ( who cannot write new
posts ) can add comments. And, of course, writes some more code. And
inserts some more data in the db.

You ( and your cousin ) have went through 3 distinctive phases of
agile development. You have, of course, used git ( oh, well, maybe svn
or cvs ) to keep track of your code and make sure everything is ok and
that you can revert back to a previous, stable version, if anything
goes wrong. But then, one day - something really goes wrong. And you
need to revert from state 3 to state 2 - i.e. remove the comments. But
â€¦ your cousin is

+ out of town
+ mad at you because you behaved badly on his last birthday party
+ has no idea what he did for you so many months ago


No problem - you use your source versioning system and do the
reversal. But.. something is still wrong - your database stays
unchanged. What did you add from v2 to v3? Did you remove something?
And you know nothing about Database Server X - can you write the SQLs
to revert the schema?

That is where DB migrations come in and save your life. Well, maybe
not that important with your blog but imagine 100+ development
iterations and 10+ developers working simultaneously on your project
and maintaining it on many different platforms - this can turn into a
small nightmare.


So, I proudly present you with

CakePHP Yaml Migrations and Fixtures
````````````````````````````````````

The idea was born when Eelco and I started working on PagebakeryCMS
some time ago and was later further developed when we decided we
needed a standardized CakePHP App Installer. You can get a copy (
`http://github.com/georgious/cakephp-yaml-migrations-and-fixtures`_ )
of the project and follow it, too. What I personally find useful in
it:


+ no external libraries - only the SPYC class used to parse YAML files
  ( that is authored by Chris Wanstrath )
+ can easily complement your source versioning system - use it for
  team collaboration
+ can be used for DB abstraction installers - when deploying your
  applications on different platforms you donâ€™t want to modify your
  SQLs - and the project is DB agnostic. The package also has fixtures (
  well, the terms is not used correctly - this is for adding initial
  data to your database )
+ can be used to standardize already installed applications on
  different platforms
+ can help you make your apps DB agnostic by generating a YAML
  structure of your schema from where you can go on with the agnostic
  aproach



How it works
````````````

Just put all files in your CakePHP vendors folder.

If you want to use the API only, simply include the classes in your
code

::

    App::Import('vendor', 'migrations' )

and/or

::

    App::Import('vendor', 'fixtures' )

And then, for example:

::

    $migrations = new Migrations();
    $migrations->load('comments.yml');
    $migrations->up();

This piece of code will look for the file comments.yml and then
perform the its UP section.
The file may look something like that:

::

    UP:
      create_table: 
        comments: 
          author_id: 
            type: int
            default: false
            length: 6
            - not_null
          post_id: 
            type: int
            default: false
            length: 6
            - not_null
          text: 
            type: text
            default: false
            - not_null
    DOWN:
        drop_table:
            - comments

Using the fixtures class is quite straightforward, too.

::

    $oFixtures = new Fixtures();
    $oFixtures->import( 'comments.yml' );

And a sample comments.yml would look like this:

::

    comments:
        1:
            author_id: 1
            post_id: 1
            text: My first comment
        2:
            author_id: 2
            post_id: 1
            text: A second comment by user

But as I mentioned before, there is also a shell which you can use -
it is a bit more powerful and uses the migrations and fixtures classes
for its core. You can use it like that:

::

    ./cake migrate help

to get some more info. It generally supports going UP and DOWN on
different DB versions, reset and generating an YML files from your
current DB schema. I find that last one quite useful, if you want to
start using migrations from now on.

Update : I had some questions on the e-mail by Ben Rasmussen which I
believe should be addressed here, too.
[ulist]

+ the default path to the migrations' files is APP_PATH .'config' .DS.
  'migrations'
+ the migrations' file names follow this pattern -
  /^([0-9]+)\_(.+)(\.yml)$/ - that would mean 001_somefile.yml ;
  002_someotherfile.yml or just 1_somefile.yml ; 2_someotherfile.yml
+ fixtures' filenames follow this pattern - .+_fixture\.yml - that
  would mean somedata_fixture.yml ; somemoredata_fixture.yml - they are
  all loaded into the db although there is currently no way to set the
  sequence in which they're loaded


This was my first article here, so thanks for reading :) Let me know,
if you have any questions.



.. _http://github.com/georgious/cakephp-yaml-migrations-and-fixtures: http://github.com/georgious/cakephp-yaml-migrations-and-fixtures

.. author:: georgious
.. categories:: articles, plugins
.. tags:: migrations,fixtures,db,Plugins

