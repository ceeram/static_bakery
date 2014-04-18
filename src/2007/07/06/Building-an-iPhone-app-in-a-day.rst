Building an iPhone app in a day
===============================

The iPhone changed the way I work and made my life easier. Read about
the application that adds one more feature to an already great device.
Some of you may have heard, but PhpNut, Nate, and myself all have
iPhones. Nate and I both stood in line for hours, so we naturally felt
a strong bond to our little piece of sweetness. After spending a day
with the device, we were hooked and PhpNut soon felt left out so, he
drove an hour to some city in Tennessee to get the last 8gb iPhone
available there. Anyway, while I was playing around, syncing my life,
and getting to know my new way of living, I noticed that there was no
easy way to get RSS feeds on my iPhone.

I need RSS on my iPhone. The EDGE network is not all that fast at the
moment. None of the other sites I found really had an easy and clean
user interface that would fit perfectly with the iPhone apps. So, I
set out to create a app that would allow other users to add RSS feeds
and I could go look for some and add them to my collection. Strangely
enough, my iPhone is now my preferred way of reading all my feeds. I
have it connected to wifi at home, so speed is no issue and reading
the feeds by tapping and flicking my fingers around makes me feel like
I am reading a newspaper.

For whatever the reason, iPhoRSS was born and exists to aggregate and
make reading RSS easy. Obviously, using CakePHP would make iPhoRSS
happen fast and allow me to get the feeds on my iPhone today. Nate and
I kept talking about all the cool things on the iPhone and I told him
about the idea behind iPhoRSS. He wanted RSS too, so I put together a
quick 3-table database with feeds, users, and feeds_users. Baked the
project, database config, then baked the Feed and User models with all
the associations, baked the Feeds and Users controllers with the CRUD
methods, and baked the views for the two controllers. Next I set about
adding Authentication. The requirement was something ultra simple, so
Session was all that was needed. I sent this over to Nate with an
initial user interface that was simple and clean based off the
cake.generic.css and a custom css to make a few more changes and
additions to the style. In an hour, we had a working application to
start building out.

From there, Nate helped me test some things, add some features, and
make the interface look like it belongs on an iPhone. I added a
deleted_feeds table, so if there are some feeds another user added
that you will never need you can delete them. They will still be
available to the other users, but you won't see them when you choose
to add more feeds. I decided to allow two ways of adding feeds to the
system. You can use "Choose," which brings up a multiple select form
field. The interface for multi-selects on the iPhone is slick, so it
was a nice by-product that the multi-select works so easily with
Cake's hasAndBelongsToMany relationships. The other way to add feeds
is to tap on "New". This allows you to name a feed and input the feed
URL. Then, I check to see if that URL already exists, and if so, grab
its ID so we don't end up with a ton of duplicate data. Unfortunately,
I have not located the cut and paste for the iPhone and surfing around
and finding feeds is not all that pleasant. It will be much more
pleasant if I could get iPhoRSS to automatically add a feed from the
header of the current browser page. Maybe the next version. Instead, I
solved this problem by making the interface usable from the web. Its
still big, but it fits just fine in my browser now. I used the
RequestHandler to check if the request is coming in through a mobile
device. If it is, I have a different style. Now, that I could add
feeds easily and the interface was coming along, I had to parse the
RSS feeds and display the information back to the user. I didn't do
anything special, except use the HttpSocket and XML classes in the
Feed model. HttpSocket calls a raw GET and passes the data to the XML
class. Then I just pass the resulting XML object to the view, so I can
display the data from the feed. Now I could create and read feeds with
ease. Nate added some of his favorite feeds and I added mine and we
started really using the application. While we were doing this the
sessions kept timing out and login back in became a real annoyance.
So, the last thing I added was the Cookie Component to keep track of
the session, so I did not have to login all the time. Typing is never
fun on small keyboards with one or two fingers, and we need to limit
any requests to the server since the EDGE network is not too speedy.
The Session gets created with the login and stored in the Cookie. If
the Cookie is available and Session is not, then the Session will be
created from that, otherwise it will redirect to login.

After about 6 hours of on and off coding on it, while we waited for
the domain to propagate, we had finalized the first version and
published it. We have been testing it for the last few days and hope
that others enjoy RSS on their iPhone. Well, actually you can use
iPhoRSS from any web enabled device. If you have any comments or
suggestions feel free to leave a comment. We plan to add some more
features over time as they become necessary to us, and I dont see why
we can't release the code.

Check out `http://iphorss.com`_ to see the working application.

Bake on.

.. _http://iphorss.com: http://iphorss.com/

.. author:: gwoo
.. categories:: articles, case_studies
.. tags:: Rss,iPhone,Case Studies

