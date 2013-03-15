

Elistic: a Social Link-Listing Site in My Spare Time
====================================================

by %s on May 19, 2007

Another example of how CakePHP allows you to create complex web
applications quickly by giving you the tools to focus on the relevant
stuff. I designed and developed a social link listing and
recommendations site in my spare time over two months.
Elistic (`http://www.elistic.com`_) started with an idea to improve
upon existing services that allow you to share, find, and discuss
links (like Digg, Reddit, and even del.icio.us) by making them more
relevant to the site's users.

To do this, I wanted to incorporate a ratings system that allows a
little more variety than just "good" vs. "bad", but is still simple to
understand. Then I could use those ratings to analyze what users like
and don't like, and recommend links to them based on other users with
similar tastes.


From Idea to Product
~~~~~~~~~~~~~~~~~~~~
Once I had this germ of an idea, I began to think about what form it
might take as a web application. I started by opening a text editor
and writing some HTML and CSS. I wanted to see what it would take for
this idea to materialize. What would I show to the user? How would
they interact with it? I didn't write any code at this point, and
there weren't any images or a color scheme -- I just made up some
dummy links and worked with the layout to determine what kinds of
elements I would have to implement.

So now I had a pretty good idea of what components (in the general
sense, not CakePHP components) would make up this app. They were
things like user registration/login/account management, posting links,
a star-rating system for links, comments, link retrieval (ranking
algorithms, the recommendations algorithm), etc. From there it was a
relatively simple process to start working on development. I created a
simple database structure that grew as development progressed, and
started right in on user registration.

Once I started writing code, I alternated time between that and the
"real" site design. The design evolved quite a bit over the past
couple months, and eventually settled on what you see now. It was a
very iterative process, and I did the design work in chunks, using it
as something of a distraction when I got tired of writing code.

In the end, it took about two months of evenings and weekends, working
at a casual pace, to create what you can see now at elistic.com. I
learned a lot in the process and hopefully created something that will
be useful to a lot of people.


Challenges
~~~~~~~~~~
There were (and still are) a lot of interesting challenges over the
course of development:


Scope:
``````
I'm just one guy working on this in his spare time. And I actually
wanted to release this site at some point. So I didn't implement
everything that I initially wanted to. I had to identify what features
were absolutely necessary and what could wait, and then only work on
the necessary stuff.


Critical mass:
``````````````
This sort of site works best when lots of links are getting added and
lots of users are rating the existing links. It's kind of a catch-22
-- if there isn't enough activity, people will lose interest and
leave, causing even less activity. A critical mass of users is
required that will generate enough activity to keep new users
interested and to keep the website valuable to surfers. This is more a
marketing problem than anything else, and I'm working on solving it by
getting the word out to early adopters (hint, hint ;-)) any way I can.


Learning Curve:
```````````````
This was my first CakePHP project, so I was learning the framework
while I was developing the site. Thankfully, CakePHP isn't all that
tough to learn, but sometimes I would implement something only to find
there was a much better and/or easier way to implement it using
CakePHP. That sometimes meant throwing out a piece of code and
starting over.


Staying Ahead of Abuse:
```````````````````````
It's not really a problem now, but if Elistic ever generates enough
traffic, people will start trying to figure out how to game the
system. I started by allowing users to flag links as spam, and
automatically removing a link (pending moderation) if it gets flagged
enough time. I also added an email address verification to discourage
(but not completely prevent, obviously) multiple accounts.

But most importantly, I decided to make the ranking algorithms public
(`http://www.elistic.com/pages/how_elistic_works`_) -- the exact
opposite approach of Digg, for example -- with the goal of working
with the community to identify and improve upon "gaming"
vulnerabilites (not to mention just plain making it work better).


Design:
```````
I'm a programmer, not a designer, and I don't maintain any delusions
of grandeur in that respect. It was a challenge to come up with
something that looks good. I think I managed it, largely by paying
attention to what sorts of things work on other sites, but I don't
think I'll ever come up with something that looks great. If Elistic
becomes popular, I will surely have to enlist the services of a
professional designer.


Still to Come
~~~~~~~~~~~~~
There are already a lot of things in the development or planning stage
that will present my next challenges. Some of the more interesting
ones include:


Caching:
````````
I've been careful to design the database to support intelligent
indexing and minimize the expense of running queries, but that won't
be good enough if Elistic grows to support hundreds of thousands (or
more!) pageviews a day. I have yet to explore the caching options
provided by CakePHP, but have some experience working with other
solutions, and will be educating myself (and maybe designing some data
warehousing-type solutions) as time goes on.


Site Tools:
```````````
I created a bookmarklet to facilitate the simple posting of links, but
would also like to provide additional tools, such as a Firefox
extension, and plugins for major blog software packages, that make it
easier to integrate with Elistic.


API:
````
I'm a big fan of sites that provide APIs so that their users can
provide new and unanticipated ways to interact with the service. As
Elistic incorporates more features, this would become even more
useful, so a web-based API is in the works.

Hopefully Elistic is a good example of what one person can come up
with in a short amount of time using an excellent rapid development
framework like CakePHP. I look forward to seeing you there!

.. _http://www.elistic.com: http://www.elistic.com/
.. _http://www.elistic.com/pages/how_elistic_works: http://www.elistic.com/pages/how_elistic_works
.. meta::
    :title: Elistic: a Social Link-Listing Site in My Spare Time
    :description: CakePHP Article related to social,links,Case Studies
    :keywords: social,links,Case Studies
    :copyright: Copyright 2007 
    :category: case_studies

