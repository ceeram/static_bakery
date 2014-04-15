National Association of Music Educators
=======================================

by leveille on June 11, 2008

When the National Association for Music Education came to us for
development, and they didn't balk at PHP, Cake was a no brainer.
Though it's been at least a month or so, I wanted to take some time to
share my experiences with the creation of my (under the employment of
a development house) first CakePHP application(s):

`http://www.menc.org/`_

What menc.org provides ...
``````````````````````````

menc.org provides the following services to it's staff and members:


+ Integration with a 3rd party member management service (single
  signon -forums, etc)
+ Members can add/manage/consume/monitor lessons in a lesson library
  (`http://www.menc.org/lessons`_)
+ Integration with Google Mini search appliance
+ Integration with Sesame Vault (third party audio/video management)
+ Integration of `http://code.google.com/p/simple-mc/`_ content
  manager for ease of page and content block management
+ Administrators manage pages/content/lessons/users/etc through a
  backend administrator



In a nutshell
`````````````

In a nutshell, there are two applications running the website. The
frontend stuff (member authentication, lesson library, page view
restriction, etc) as well as the backend (administration). It was
definitely a learning experience for me. I made the decision early on
to physically separate the two sections into two different
applications, and this resulted in a lot of code duplication. I didn't
know the ramifications of the decisions I was making early on, and by
the time I was a little more educated it was too late. The code
duplication probably has a lot to do with the fact that this was my
first Cake app, but the separation of the sections didn't help.

For the record, the design work comes from a design services agency,
and the design implementation / CSS work comes from a web design and
development agency that contracted us for application development
work. Lots of hands in the cookie jar.


What I Love In Cake
```````````````````

I love the console, security/authentication, rapid development, OOP
model, API, CakeFest, debug, router, Configure, vendors, organization,
IRC Channel. I especially love how easy Cake makes implementing a
strong security model. As always a lot falls back on the developer,
but Cake makes it considerably easier. I have been so busy for the
last 3 months working on menc.org, as well as other non Cake/PHP
applications that I haven't had the time to contribute back to the
community (I know, I know, who does. Blame it on .net). This is my
first real contribution, if you can call it that. Based on what I have
seen/heard/read I think there is a solid community behind Cake, though
I do think the rudeness level can sometimes be high. I find though
that as long as questions are posted which indicate at least a minimum
of effort, the questioner will receive professional and friendly
responses. There is also a lot of activity on IRC, which is great.


Challenges
``````````

The biggest challenge for me was my constant second guessing of myself
with regards to the coding decisions I was making. I was a lot more
inefficient than I would have liked, especially in the area of
repeating myself. Often I would be under such a crunch that I would
just get things working and move on, without thinking about how I
could abstract out the logic for reuse. I would fool myself into
thinking that I could refactor things later on. Yeah right.

My other challenge was the fatness of my controllers. I was stuffing a
lot of logic in my controllers that shouldn't have gone there. At
times I would jump over to the model and put things there, only to
find 2 hours later that I had wondered back over to the controller.


Overall
```````

Overall I had a great experience. I was much more organized than I
have been in the past. I was able to provide additional features to
the client, often beyond what they were asking for, because of the
rapid nature of developing on Cake. Lucky for me, the next big
application coming in my direction will be written in PHP, and will be
built on CakePHP. I have joined the club. I have written/launched my
first application, and I'm sure future apps will only get better.

.. _http://code.google.com/p/simple-mc/: http://code.google.com/p/simple-mc/
.. _http://www.menc.org/: http://www.menc.org/
.. _http://www.menc.org/lessons: http://www.menc.org/lessons

.. author:: leveille
.. categories:: articles, case_studies
.. tags:: ,Case Studies

