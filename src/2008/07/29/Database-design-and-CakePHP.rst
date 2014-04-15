Database design and CakePHP
===========================

by Frank on July 29, 2008

To start with a CakePHP application you first need a proper database.
This database will determine for a great part the structure of your
application, with the data layer (Model layer) in particular. This
article contains a way to a structured database and data layer for a
CakePHP application from a conceptual point of view.


Modeling
````````
First of all you need to forget you are designing a database ! Instead
you are describing objects with properties. These objects are called
entities. The properties of these entities are called attributes. So
an attribute belongs to an entity. An example of an entity is a User.
This User has many attributes, such as a name, a password and an
e-mail address.

Usually a project has a list of requirements and a functional design
for the application. These documents contain a non-technical
description for the application. This makes these documents the guide
for the database model, but what is there to model?


Noun phrasing
+++++++++++++
In your functional design there are lots of nouns. Some of these nouns
are entities or attributes of entities in your application. The
easiest way to map this, is to put all these words under each other in
a table and describe what they will be in your application. This might
be the most boring thing you have ever done, but it is really useful
for not forgetting anything in your database. Here is an example.

I added a column called 'CakePHP convention'. This is the entity name
I'm going to use in my conceptual model later on, because when I
generate a physical model from it, the names aren't changing. This
means if I use the actual entity name I'll end up with a table called
User instead of users and that is not according to the CakePHP
conventions.


Conceptual Database Model
+++++++++++++++++++++++++
Now that we have the base of our conceptual model, it is time to
create it. First we add the entities and attributes to our model. Here
we decide what data types they will be and what identifiers it will
have. Again, you are not creating a database, so forget about foreign
keys. Keep in mind that we have CakePHP conventions to worry about, so
the names in the model will be plural like in the table that was made
earlier. Here are the entities:

Another thing which is a bit odd is the name of the primary
identifier. I used things like 'user_id' and 'project_id'. I know
this isn't like the CakePHP conventions, but if I use 'id' in every
entity, my program starts to whine it is impossible due to
normalization rules. The primary identifiers can later on be changed
back to 'id' if you like, or you can set a different primary key in
your CakePHP model.

By the way, never use more than one attribute in your primary
identifier, because CakePHP doesn't and never will support this. (See:
`https://trac.cakephp.org/ticket/1923`_).

What we see in the model is per entity a field with three sections.
The top section of that field is the entity name. In the second field
are the attributes. The underlined attribute is the primary
identifier, which is also marked as . After that is the data type and
after that is the flag. The means mandatory, meaning required or NOT
NULL. In the last section are the identifiers.

Now that we have our entities and attributes we need to add their
associations to each other in the model. So I'm going to add these:


+ One user belongs to zero or more projects.
+ One project has one or more owners (users).
+ One release belongs to a user.
+ One user can release zero or more releases.
+ One project has zero or more releases.
+ One or more releases belongs to one project.

This is the result:

What we see here is a very important model when using CakePHP. This
model is called a Conceptual Database Model. From this model we can
easily generate the entire database with a few clicks. If you have
this model right, there is not much to do for a good database.

In this Conceptual Database Model you can see the relationships
(associations) between the entities. In the model the dash in the
associations means one and the circle means zero. A single line means
one and that trident means more. So what we have is:


+ User - zero or more - Project
+ Project - one or more - User
+ Release - one or one - User
+ User - zero or more - Release
+ Project - zero or more - Release
+ Release - one or one - Project

You see I called the associations like projects_users. This is
according the CakePHP conventions for join tables. Sometimes (only
with many-to-many relationships) there will be an extra table to link
these two entities together. This is a so called join table. You will
see that later on this article.


Physical Database Model
+++++++++++++++++++++++
Now that we have a good Conceptual Database Model, we can generate a
Physical Database Model. Finally it is database time. In this Physical
Database Model you will see tables instead of entities and columns
instead of attributes. With only three clicks I generated this
diagram, which is the map of the database.

First thing you notice is that there are four fields instead of three.
The fourth is the blue one which is called projects_users. This is the
join table I mentioned earlier. This was generated from that many-to-
many relationship. When you look closer at the releases table, you
will see that the project_id and the user_id are added. This is the
result of the other relationships. You can also see that the
conceptual data types are replaced with MySQL data types. (Variable
characters (100) became varchar (100)). It's all automagic, just like
CakePHP. The only thing you will need to add here is the Indentity
flag on the primary keys that should auto-increment.

From this diagram I can (again with a few clicks) generate the SQL
CREATE statements for the database and paste them into PHPMyAdmin and
create the entire database.


CakePHP Models
``````````````
Since we have our database we can start our CakePHP application. I'm
not describing here how you can start a CakePHP application, because
it is described in the manual. Read that first. What we need are Model
classes, that will be our data source in the application.

You can easily read your models from the Conceptual Database Model
that we made earlier. The entities there are the models you need. The
only thing you have to do is take the entity name and make it
CamelCased and singular.

The associations in the Conceptual Database Model are also the
associations between the Model classes of CakePHP. This means that the
Conceptual Database Model is a great map for that and also a handy
piece of documentation. Here I've updated the Conceptual Database
Model to match the CakePHP models and associations.

Happy baking,

Phally
This is just one way. It is not about good or bad ways, just my way
for doing this and i'm happy to share it with you all. If you have
questions or comments, please don't hesitate to post them here.
For this article I used Sybase PowerDesigner. The Physical Database
Models can also be created with programs like MySQL Workbench. The
reason I didn't use MySQL Workbench is that it turns many-to-many
associations directly to a join table and that foreign keys are
instantly passed into the tables. This means that you can't save a
Conceptual Database Model, which actually is the map of the models in
your application. So MySQL workbench wasn't very usefull for this
article.

.. _https://trac.cakephp.org/ticket/1923: https://trac.cakephp.org/ticket/1923

.. author:: Frank
.. categories:: articles, tutorials
.. tags:: database,tutorial,phally,Tutorials

