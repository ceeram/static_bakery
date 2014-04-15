A simple contextual help system
===============================

by KiltOtter on January 13, 2007

I wanted to show how to create a simple contextual help system in
CakePHP. It should work in pretty much any application and works a bit
like a mini-wiki. Things not directly related, like authentication and
error management, are left out of this tutorial. By contextual help I
am refering to help pages that show help for a specific part of an
application. This help information can be displayed inline on each
page of as a popup or similar.
This article as been transferred from the wiki. Author details were
not avilable

The Database
~~~~~~~~~~~~
So lets get started. First we need a database table to store our help
articles. Here it is:

::

    CREATE TABLE `help` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `url` varchar(255) DEFAULT NULL,
      `contents` text,
      `created` datetime DEFAULT NULL,
      `modified` datetime DEFAULT NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `urls` (`url`)
    )

What we have is a small table with two fields of interest to us. There
is url, containing the url in our application for which each help
acticle is related. And there is contents containing the actual help
article.

Help Model
~~~~~~~~~~
This is about as simple as a model gets. I have added the table name
here since it does not follows the usual singular/plural convention.

/models/help.php
````````````````
[model] class Help extends AppModel
{
var $name = 'Help';
var $useTable = 'help'; //Comment by Repsah: “var $useTable = ‘help’;


.. author:: KiltOtter
.. categories:: articles, tutorials
.. tags:: context sensitive he,contextual help,help,Tutorials

