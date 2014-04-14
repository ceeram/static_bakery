TuneShout.com: Music Downloads and CakePHP
==========================================

by Fluxx on July 18, 2007

How I used CakePHP to build TuneShout.com, a socially controlled
online music network and store.
Last winter I set out to build the largest, most complicated site I'd
ever built. A friend and long time entrepreneur-buddy of mine had a
brilliant idea: combine the (excuse the web 2.0 term) crowdsourcing
power of your user base to sort & rate through independent music
artists who would host & sell their music on the site. For free. It
was a brilliant idea and a lofty goal, one that would test me as a
developer and Cake as my PHP framework of choice.

After a good 1/2 dozen brainstorming sessions, we had our feature
list, wireframe & I was off to design and code. That was in December.
5 months of working before & after work, weekends & any free time I
could get from my girlfriend...I was done. `TuneShout.com`_ was
launched. I developed all aspects of the site: database, PHP coding,
front end design, CSS, JavaScript, etc. I'm very proud of it and love
how it turned out.

Along the way, though, I learned a lot about Cake, developing with it
& utilizing it on large scale complicated websites. I wanted to share
that information with the CakePHP community that has given so much to
me. I've got some helpful hints and guidelines to make developing
large sites in Cake even easier.


Planning stages
```````````````
Having used Cake at my work for a few other projects, I was fully
aware of the Model, View, Controller functionality. First step was to
identify the models: User, Artist, Song, Album, etc. All real life
items that would be modeled in the web application. I then mapped out
a relational database to connect everything and got the tables set up
in MySQL. I then used the Bake script to quickly build out all the
create, read, update & delete (CRUD) functionality.


Build out
`````````
With the models, views and controllers all created I then set out on
building the default layout view. After that was finished, I started
to work my way down the list of features we wanted in TuneShout, point
by point. It was mostly straight forward, but there were some assumed
problems along the way. Most of them turned out to be easy to
implement, thanks to Cake's ability to be flexed and molded to however
you want to build you application.


Adding 3rd party Libraries
``````````````````````````
With TuneShout, there were some parts of the site that I didn't have
the skills to do, and decided I would be better off using an existing
3rd party solution. Cake is its own self contained universe, so I had
concerns on how well other PHP libraries would play with Cake. It
turned out to be a breeze.

Reading/writing ID3 tags was one of those problems I decided to use a
3rd party solution for. I looked at PHP's built in `ID3 library`_, but
decided to go with the `getID3`_ media parser. It had more
functionality than I'll ever need and best of all it was open source!
Integrating it in to Cake was as simple as dropping the library in the
vendors folder and including it with the vendor() function. Same goes
for including Google's PHP library for Google Checkout, Paypal's SDK &
PHPMailer to handle the application's email.


Streaming Song Previews
```````````````````````
Another big hurdle was how to stream song previews. I settled on an
open source `Flash-based MP3 player`_, and included it using the
`SWFObject`_. The question then arose: how do I stream to the Flash
player just a 30 second sample of our artist's MP3s? Well, with Cake
you're coding in a framework in PHP itself, so I was able to use PHP's
stream_get_contents() function to stream only 30 seconds worth of the
song. 30 seconds worth of bytes was calculated by looking at the file
size and bitrate of the songs at upload, analyzed by the
aforementioned getID3 library.


AJAX
````
On TuneShout, users can "Shout" an artist. Basically this just means
"I like them". They're one component of many in the algorithm
calculating which artist gets to "The Stage", our dynamic user
controller top artists chart. To shout artists, I wanted to do it
through AJAX for the best user experience. I put `Prototype`_ and
`scriptaculous`_ on the site, and thanks to Cake's built in `AJAX
helper`_ I got the shouts system working in about 20 minutes.


Development vs Live Build
`````````````````````````
Once launched, I wanted a way to have a developer build that I could
work on while simultaneously running the live production version. I
wanted both builds to use the same Cake framework library files, and I
wanted them to share an webroot. The solution involved editing the
app/webroot/index.php and define the APP_DIR constant based on the
subdomain. So anything but dev.tuneshout.com uses the production build
of the site, while dev.tuneshout.com serves as my development build
protected behind `HTTP Basic Authentication`_.


What I learned
``````````````
I had a great time building TuneShout with Cake. I couldn't imagine
building a site of that scale in such rapid time without the help of
an awesome framework. That said, there were some things I learned and
would change if I had to do it all over again.


#. Plan, plan, plan and plan. Building complicated sites is, well,
   complicated. That said, spend as much time as you can afford to map
   things out. Build wireframes, do initial designs, think abstractly,
   write ideas down. With everything you do, think about if it will be
   useful later in the site and if you should code it in such a way to
   make it reusable? Often times you will.
#. Use 1.2. At the time I first started development Cake 1.2 wasn't
   nearly as robust as it is now. Using it at work for a project there,
   I've fallen in love with it and will use it for everything from here
   on out.
#. Stick to your feature list. Lots of times good ideas will pop in
   your head during development, and you'll want do add them to the site.
   Sometimes it's a glaring necessary feature you missed (which shouldn't
   happen if you follow point 1), but often times it's a "wish list"
   feature that you're better off not developing now. Often you'll learn
   a lot from version 1, and when it comes time to code version 2 you'll
   have a much better idea of how to code that awesome feature.
#. Get familiar with the `Cake API`_. A lot of times the functionality
   I was looking for was already built in to Cake's internals - it just
   was in the API. Get familiar with the stock Model and Controller
   functions, there is a lot of good useful stuff in them. A lot it
   you've probably never heard of.


Conclusion
``````````
Since our launch, TuneShout has been doing great. I've been swamped at
work and haven't had much time to work on it lately, but tonight I had
a couple of free hours and wanted to thank the CakePHP guys for all
the hard work and share what I learned using Cake to build a large
scale website.

`TuneShout.com: Discover a New Sound`_.

.. _Flash-based MP3 player: http://musicplayer.sourceforge.net/
.. _scriptaculous: http://script.aculo.us/
.. _Cake API: http://api.cakephp.org/
.. _getID3: http://getid3.sourceforge.net/
.. _ID3 library: http://us.php.net/manual/en/ref.id3.php
.. _Prototype: http://www.prototypejs.org/
.. _HTTP Basic Authentication: http://httpd.apache.org/docs/1.3/howto/auth.html#basic
.. _SWFObject: http://blog.deconcept.com/swfobject/
.. _AJAX helper: http://api.cakephp.org/class_ajax_helper.html
.. _TuneShout.com: Discover a New Sound: http://www.tuneshout.com/
.. meta::
    :title: TuneShout.com: Music Downloads and CakePHP
    :description: CakePHP Article related to mp3,music,guide,large scale,Case Studies
    :keywords: mp3,music,guide,large scale,Case Studies
    :copyright: Copyright 2007 Fluxx
    :category: case_studies

