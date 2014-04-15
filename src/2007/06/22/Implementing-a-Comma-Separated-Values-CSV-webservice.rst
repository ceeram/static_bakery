Implementing a Comma Separated Values (CSV) "webservice"
========================================================

by aitch on June 22, 2007

A simple howto for beginners (by a beginner!) on rendering CSV output
in a consistent manner with XML, RSS etc.
I started with CakePHP a few weeks ago and have had great success
redeveloping an intranet web application. One of the strong draws to
Cake was the promise of "free" webservice provision which is very
important. We have a number of other applications that tap into the
data feeds from this app. They vary in their requirements and
complexity but generally the newer ones are looking for XML, and the
older ones for CSV. There is also a small early dependance on RSS
feeds internally for the data. So the default webservice covers 2 of
those bases - fantastic!

I asked the question on the google group and received some varying
responses, some criticising the idea that CSV should be called a
webservice. I would agree that CSV clearly is not a webservice, but it
seems strange not to treat it in the same way as it lends itself to
the approach very well. After XML and RSS are just text rendered with
tags, so why not text rendered with commas? :-) I have to do this on
several similar models.

The requirement is largely the same as enabling the XML and RSS
services, with the addition of the critical addition to routes.php

In summary what I want to be able to publish and make available to the
consumers of my data is something like:

+ [li]/items/index [li]/xml/items/index [li]/rss/items/index
  [li]/csv/items/index

You should also be able to apply this methodology to other data
outputs as well. Internally we have some of our own plain text layouts
that are automatically sourced via wget and emailed that we will
probably do in this manner as well.

What follows therefore is a brief and simplified primer of how I chose
to implement this, which you should be able to extend for your own
purposes. I'd be delighted to hear feedback as to whether this is the
most appropriate method or not.

[H4]1. Tell Cake to watch for the new CSV "webservice".
Edit your app/config/routes.php to include the following new route:

::

    
         $Route->connect('/csv/:controller/:action/*', array('webservices' => 'Csv'));

[H4]2. Create a default component app/controllers/components/csv.php

Component Class:
````````````````

::

    <?php 
         <?php
         class CsvComponent extends Object {
         }
         ?>
    ?>

[H4]3. Create a default helper app/views/helpers/csv.php

Helper Class:
`````````````

::

    <?php 
         <?php
         class CsvHelper extends Helper {
         }
         ?>
    ?>

[H4]4. Create a default layout file
app/views/layouts/csv/default.thtml

View Template:
``````````````

::

    
         <?php header('content-type: text/plain'); ?>
         <?php echo $content_for_layout ?>

[H4]5. Finally, create the appropriate controller action associated
views
for example: app/views/employees/csv/index.thtml

View Template:
``````````````

::

    
         "ID","employeeLogin","employeeName"    (.... etc)
         <?php foreach($employees as $e); ?>
         "<?php echo $e['Employee']['id']?>","<?php echo $e['Employee']['employeeLogin']?>","<?php echo $e['Employee']['employeeName']?>"
         <?php endforeach; ?>

[H4]Open Questions:
? there may be a clever way of querying the model to derive the CSV
headers (and therefore the row data I suspect). I don't know what this
is yet.

References:
- Google Group Thread: `http://groups.google.com/group/cake-php/browse
_thread/thread/ccd5d340b656fc0f/04b3fa72a0b4166b?#04b3fa72a0b4166b`_ -
CakePHP Manual on Advanced Routing:
`http://manual.cakephp.org/chapter/configuration`_

.. _http://manual.cakephp.org/chapter/configuration: http://manual.cakephp.org/chapter/configuration
.. _http://groups.google.com/group/cake-php/browse_thread/thread/ccd5d340b656fc0f/04b3fa72a0b4166b?#04b3fa72a0b4166b: http://groups.google.com/group/cake-php/browse_thread/thread/ccd5d340b656fc0f/04b3fa72a0b4166b?#04b3fa72a0b4166b

.. author:: aitch
.. categories:: articles, tutorials
.. tags:: webservices,beginner,csv,Tutorials

