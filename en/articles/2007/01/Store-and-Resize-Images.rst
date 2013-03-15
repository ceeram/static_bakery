Store and Resize Images
=======================

by %s on January 06, 2007

This guide should help you learn how to store images into your
database as well as use the powerful GD library to resize your images.
Here are some things this guide will teach you:

#. Resize images into various sizes(thumb, small, medium, large)
#. Use GD library in PHP
#. Upload and store files into the DB using CakePHP

Let's start right away.

Here's the SQL table I used. I have 5 different fields for the data of
the files I am storing, thumb small medium large original. In addition
each file being stored has a subsequent file size to go along with
each file being stored. This is important when you have to reproduce
the image later on.

::

    
    CREATE TABLE `images` (
      `id` int(11) NOT NULL auto_increment,
      `name` varchar(75) NOT NULL,
      `type` varchar(255) NOT NULL,
      `size` int(11) NOT NULL,
      `smallsize` int(11) NOT NULL,
      `thumbsize` int(11) NOT NULL,
      `data` mediumblob NOT NULL,
      `small` mediumblob NOT NULL,
      `thumb` mediumblob NOT NULL,
      `created` datetime default NULL,
      `modified` datetime default NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;
    


.. meta::
    :title: Store and Resize Images
    :description: CakePHP Article related to ,Tutorials
    :keywords: ,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

