MonitorableBehavior
===================

by sassman on May 15, 2009

Have you ever need a functionality like: how many people have clicked
a picture or when did anyone download a file? Then the
MonitorableBehavior helps you to solve this problem by a quit simply
use of it. You can monitor flexible things like clicks or downloads on
your model. I use MonitorableBehavior to monitor which client has
downloaded an 'Attachment'-Model, as you like a [b]download
monitor[/b].


Background
~~~~~~~~~~
On a website it is often a default functionality to track user
interactions like "has clicked a picture" or "has seen a profile" but
to implement this feature on every single way of usage can be hard
work. So i decidet to implement a behavior wich can do the work in a
very simple way.

simply like:

Model Class:
````````````

::

    <?php 
    class Attachment extends AppModel{
        ...
        var $actsAs = array('Monitorable');
        ...
    }
    ?>



How do i use it
~~~~~~~~~~~~~~~

Requirements:

+ the behavior found on page 3
+ monitoring model called MonitoringObject
+ monitoring_objects table with these fields (code also found on page
  3):
  +

    + id (bigint, autoincrement)
    + model (string) automaticly filled with the model name you apply the
      behavior on
    + foreign_key (bigint) automaticly filled with the id of the model you
      apply the behavior on
    + type (string) filled with the type of monitor behavior i.g. 'clicks'
      or 'downloads', depends on your needs
    + ip (string) automaticly filled with the ip of the client requestor
    + user_agent (string) automaticly filled with user agent name of the
      client requestor
    + created (date/datetime) filled by cake on creation

+ actsAs = array('Monitorable'); on models that should be monitored
  (full example on page 2)





.. author:: sassman
.. categories:: articles, behaviors
.. tags:: behavior,monitoring,Behaviors

