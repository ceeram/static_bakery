

Optimizing your CakePHP elements and views with caching
=======================================================

by %s on April 10, 2007

As I have developed my CakePHP application, I have discovered new and
useful functions every day - and today I want to talk to you about
caching, and how easily it can be implemented. When I implemented it
on my front page, I went from having 7 database queries down to 1.
For my application, I wished to cache a few sections, but for this
example I will use my front page Event list. The method selects the
last X events from the database (I use 5) and displays it in the
block. If I didn't cache it, the database would be called on every
page that is viewed by every user. Thank to Andy Dawson
(`http://www.noswad.me.uk`_) aka AD7six on #cakephp for pointing this
out to me, and for all the help so far.

Also, I have to admit 2 of these optimizations were from upgrading to
the new version of the ConfComponent
(`http://bakery.cakephp.org/articles/view/243`_) by Othman ouahbi,
which now caches it's database queries per user visit.

So first, here is my EventsController method:

::

    
    **
    * Function getposts grabs x posts from database
    * @int num
    */
    function getevents($num = 5)
    {
    $getevents = $this->Event->findAll('Event.published = 1 AND Event.event_date >= NOW()', array('Event.id','Event.event_url','Event.venue','Event.address','Event.city', 'Event.country', 'Event.event_date', 'Event.event_time','Event.notes','EventType.id', 'EventType.name'), 'Event.event_date ASC', $num);
    
    if(isset($this->params['requested'])) {
    return $getevents;
    }
    $this->set('getevents', $getevents);
    }

You may notice that I select a lot of fields in this example - this is
infact another optimization. If I didn't select the fields I wanted to
use, CakePHP would select every field in the events table, as well as
the users table (because the generator of the event is linked via the
user id), including the users password (hashed) and email. It also
saves against getting ambiguous fields that may confuse during
development. Also, by checking if $this->params['requested'] has been
set, I can also pass the data to a requestAction().

So, now we are going to display the data. To do this, you first need
to create an element. I stick to naming it after the controller, so in
my /app/views/elements/ directory I create a file called getevents.ctp
(.ctp is the Cake 1.2 default views extention).

At the top of the file, I need to be able to access the data. In
elements, we use the requestAction() method. This allows us to perform
an action just like going to the URL in your browser, but outputs the
data to a variable you set. Be careful of this, it can be expensive
(see `http://www.noswad.me.uk/MiBlog/MiniControllers`_ to run a
requestAction(), but caching will help solve this problem.

::

    
    <?php $getevents = $this->requestAction('/events/getevents/5');?>

So now we have our events displayed. We have set the array variable
$events, so now do an loop like this:

::

    
    <h2>Upcoming Gigs</h2>
    <ul>
    <?php foreach($getevents as $event) : ?>
    <li class="vevent">
    <?php e($html->link($event['EventType']['name'] . ' : ' . $event['Event']['venue'], '/events/view/'.$event['Event']['id'], array('class'=>'url description')));?>
    <abbr class="dtstamp" title="<?php $time->format('Y-m-dTH:m:s', $event['Event']['event_date'] . $event['Event']['event_time']));?>"><?php e($time->format('D d M Y',$event['Event']['event_date']));?> @ <?php         e($time->format('H:i', $event['Event']['event_time']));?></abbr>
    <br />
    <span class="location"><?php e($event['Event']['address']);?>,         <?php e($event['Event']['city']);?>, <?php e($event['Event']['country']);?></span>
    </li>
    <?php endforeach; ?>
    </ul>

Now, the final step is to actually render the content to the page. In
your layout template, where you wish to place the element, type in
this line:

::

    
    <?php $this->element('getevents');?>

Ok, now go ahead and refresh your page. Woaala!, you have rendered
your element. But hold on, we haven't cached it yet! Just have a look
around your site, or refresh your page a few times. If you have debug
on 2, you will see your SQL statement form this function is still
querying the database on every page hit. So lets add caching.

::

    
    <?php $this->element('getevents', array('cache'=>'1 day'));?>

Wait? Thats it? Yep! You add caching, all you need to do is set the
attribute. Now refresh your page a couple of times, and you should see
the database stop querying for this element. This element will now
stay cached for 24 hours, but there is a problem with that - if the
data in the element is updated since the last cache action, it won't
show up until the next one. You could solve that by reducing the cache
time (e.g. 3600 seconds). Or another way is to use afterSave.
afterSave is a method in your model that is executed after the model
has been saved to the database. It is useful for clearing up unwanted
files or session data, and in our case the element cache file.

Element cache files are located in the /app/tmp/cache/views/
directory, and will normally start with the filename element__ . In
this case it's called element__getevents . To remove this after a data
save, put this method in your Model

::

    
    <?php class Event extends AppModel
    {
    function afterSave()
    {
    @unlink(CACHE.'views'.DS.'element__getposts');
    }

Now every time you update the model, the file is removed. So I hope
this article has been useful to you. If it has, please leave a comment
and let me know, or if you have anything to say about the article to
improve it, feel free.

.. _http://www.noswad.me.uk: http://www.noswad.me.uk/
.. _http://www.noswad.me.uk/MiBlog/MiniControllers: http://www.noswad.me.uk/MiBlog/MiniControllers
.. _http://bakery.cakephp.org/articles/view/243: http://bakery.cakephp.org/articles/view/243
.. meta::
    :title: Optimizing your CakePHP elements and views with caching
    :description: CakePHP Article related to caching,optimization,elements,Tutorials
    :keywords: caching,optimization,elements,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

