

Obvious trick to reduce amount of HABTM relationship tables
===========================================================

by %s on August 17, 2007

Trick howto reduce amount of HABTM tables in complex application


Intro
`````

Here is some trick I would like to share to you

Assume that your application already has 20 models. Now you want to
add some extra model which HABTM to all others (ex: tag). According to
manual that gives to you +1 table for tag and +20 tables for models
relationship. And now you want to add extra model which will HABTM all
others one more time :) (ex: comment all feature). It gives to you +1
table for model and +21 for relationship.

Trick
`````

Use common sequence for id fields of models to which you would like to
reduce amount of HABTM relationship tables. On MySQL where you wont
find sequence - play with initial autoincrement value.
For sure do not forget to define HABTM in models accordingly

Example
```````

Original DB:

::

    
    CREATE TABLE `blobsters` (
      `id` int(10) NOT NULL auto_increment,
      .......,
      PRIMARY KEY  (`id`)
    ) AUTO_INCREMENT=0;
    CREATE TABLE `shmobsters` (
      `id` int(10) NOT NULL auto_increment,
      .......,
      PRIMARY KEY  (`id`)
    ) AUTO_INCREMENT=10000;
    CREATE TABLE `hopohopos` (
      `id` int(10) NOT NULL auto_increment,
      .......,
      PRIMARY KEY  (`id`)
    ) AUTO_INCREMENT=30000;

I am assume that for lifecycle that would be enough to have up to
10000 blobsters objects in db, 20000 shmobsters and billion hopohopos

Now I need to add 'comments all' feature. First create tables.

::

    
    -- Comments model table
    CREATE TABLE `comments` (
      `id` int(10) NOT NULL auto_increment,
      .......,
      `text` varchar(4096) NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1030000;
    -- Comments HABTM table
    CREATE TABLE `comments_links` (
      `object_id` int(10) unsigned NOT NULL default '0',
      `comment_id` int(10) unsigned NOT NULL default '0',
      PRIMARY KEY  (`object_id`,`comment_id`)
    );


Add comment model which HABTM to all existing model:

Model Class:
````````````

::

    <?php 
    class Comment extends AppModel {
        var $hasAndBelongsToMany = array(
            'Blobster' => array(
    	    'className'             => 'Blobster',
    	    'joinTable'             => 'comments_links',
    	    'foreignKey'            => 'comment_id',
    	    'associationForeignKey' => 'object_id'
    	),
            'Shmobster' => array(
    	    'className'             => 'Shmobster',
    	    'joinTable'             => 'comments_links',
    	    'foreignKey'            => 'comment_id',
    	    'associationForeignKey' => 'object_id'
    	),
            'Hopohopo' => array(
    	    'className'             => 'Hopohopo',
    	    'joinTable'             => 'comments_links',
    	    'foreignKey'            => 'comment_id',
    	    'associationForeignKey' => 'object_id'
    	),
        );
    }
    ?>


To the each of other models we have to define HABTM too:

var $hasAndBelongsToMany = array(

::

    
    'Comment' => array(
    	    'className'             => 'Comment',
    	    'joinTable'             => 'comments_links',
    	    'foreignKey'            => 'object_id',
    	    'associationForeignKey' => 'comment_id'
    	)

);

I specially marked that some of model has already defined HABTM array,
but part of code you need to add is same per all models .

Conclusion
``````````
[p]I have test it with scaffold and it does working. I don't see
reason why it should not to work. Bet similar technique will works
with others types of relationships.
[p]Advantages which I can see:

#. Less tables to implement complex HABTM
#. Easy to extend already existing application (new part of code is
   same and could be shared between models trough include file or base
   model. Could be used in application which supports add-ons(one add-on
   per new thing\feature such could be tags, 'comment all', raiting, ...)
   and different users can have different sets of add-ons(no needs to
   care in one add-on about others))


.. meta::
    :title: Obvious trick to reduce amount of HABTM relationship tables
    :description: CakePHP Article related to mysql,HABTM,Models
    :keywords: mysql,HABTM,Models
    :copyright: Copyright 2007 
    :category: models

