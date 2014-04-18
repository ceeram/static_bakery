Start to Finish: My Site Designed using CakePHP
===============================================

The long and arduous process of going from knowing nothing about
coding or graphics or web layouts to creating a website isn't easy. I
had to learn everything for the first time, thankfully the web is
amass with information about web coding and web coding standards and
the true saving grace, rapid development frameworks, namely CakePHP.
Here are some of the highlights of going from zero to creating
[url]http://www.lifeyoumentary.com[/url] a place where everyone can
congregate to ask questions, get inspired, and tell stories.


The Inspiration
~~~~~~~~~~~~~~~

The idea came to me when reading an interview in a magazine and being
fascinated by the back and fourth discussion between the interviewer
and the interviewee. I thought to myself "shouldn't everyone have the
opportunity to have a professional interview, not just the uber
famous?" I decided the answer was yes, and set off to create this
site. The design is simple and straight forward. Let everyone post
simple questions for other people to select and answer and in the end
have a great magazine style interview that's all their own on the
topics they want to talk about. Now everyone gets to be famous and
tell their life story to the world!


The Tools
~~~~~~~~~

The site was created entirely with open source applications. Here's
the list of the great applications I used:

* Komodo Edit - Open Source version of Komodo IDE - A Spectacular code
editing application great if you need support for multiple languages.
Komodo has error checking, tool tips, and code completion built right
in. It is, by far, the best open source code editor I've ever used.

* VirtualBox - VirtualBox is another open source version of a
production app. VirtualBox is great for hosting virtual machines on a
single laptop. I used virtual box with 2 images, one Ubuntu device
that hosted my "dev" server and one Windows machine that I utilized
for testing different browsers worked with the site.

* Gimp -An open source graphics application. Gimp can be closely
compared to Adobe Photoshop, the difference in price is astounding
though. Gimp is free which is better than spending a few hundred
dollars, the choice was easy. Gimp has a great base of support online
also which eases the learning curve.

* Inkscape - One of the best vector editing programs i've used, open
source or not. Inkscape is easy to learn, easy to use and produces
some very high quality graphics. Again, Inkscape has a great support
base online, just Google "inkscape tutorials" and you'll see.

* Open Office - I primarily used Draw within Open Office. I found that
while creating the data models for this site, being able to use a
Microsoft Visio style application made visualizing database models
really easy.

* Ubuntu Desktop - Ah, Linux, the poster child for open source. I used
the newest Ubuntu linux distro for hosting the site locally on my dev
server. The advantage to Ubuntu is that for a user new to Linux
looking for all the power and capability without the learning curve
you can't go wrong. I was able to set up a fully functional (LAMP
Linux, Apache, MySql, and PHP) server in under an hour without much of
a need to know how to use the command line.



Getting the Knowledge
~~~~~~~~~~~~~~~~~~~~~

There were a lot of valuable resources out there especially when
trying to learn how to code. My primary resources were the:

* Cake Google Groups
* CakePHP manual
* CakePHP API

The biggest thing to remember is never be afraid to ask questions.
I've never seen a better, more helpful group of people than the group
of people who develop and support CakePHP. They're patient and will
help you with any questions you have.



The Process
~~~~~~~~~~~

The process was fairly straight forward, but extremely important. This
is the basic process I followed:

* Define Requirements - Here I basically stepped through my design. I
took steps to define what I wanted the site to do and what was to go
into the initial release as opposed to future releases. The key is to
stick to this, when you're on your own developing a site by yourself
there is no one to keep your scope in check. By writing it down and
keeping it available for reference it keeps you from falling into the
dreaded "scope creep" trap.

* Define Database Structure - I took the time to actually visually map
out my database tables and map each table to the next. This was
probably the MOST VALUABLE step I took. I can't even tell you how many
times I stepped back to review those graphics and how much time they
saved since I was able to visualize the linkages and relationships
between tables.

* Define Cake Models - This was simple based on my database graphics.
I had visually available every relationship between every database
table whether it was One To Many, Has and Belongs to Many, etc.. I was
able to produce my basic models very quickly.

* Bake - I used the built in bake command to establish my base
controllers and views. This is a great jumping off point for designing
the rest of the site.

* Design Layout - Now that I had a site that was basically functional
I took the time to define the site's layout. This is important to do
early and be happy with early. This helped to save time later because
I didn't have to step back through views to make any adjustments after
the fact.

* Modify Models, Views, and Controllers to meet Defined Req's - This
is the the meat and potatoes section of building the site. This is
where I spent at least 60% of the time and where my focus was up until
the launch of the site. This is the actual construction of the site to
meet my initial requirements.

* Move Site from Dev to Production - This was a 48 hour process to
settle everything in production. I spent at least 5 - 8 hours
researching hosting providers to make sure they met my requirements.
My main 3 requirements were native support for CakePHP or support
without severe modification, scalability, the hoster should be able to
handle bursts in usage as well as increased site utilization without
any concern for crashes, latentcy, etc. and reporting tools, I wanted
the inherent capability of knowing where my users were coming from,
when they were using the site and how often they were using the site.
I decided on a virtualized hosting service who provided all three
capabilities to me. Based on my experience with virtual machines I
know the capabilities are there for scalability.

* Secure Advertising - I did a lot of research here as well. The
biggest question is CPM vs click throughs. I decided on a larger more
reliable service to start which also meant having to go for the click
through instead of a CPM site. I did get turned down a lot at first
for lack of content. My biggest suggestion here is just stick to it,
secure what you can up front and once you can show hit rates and
content look to secure better rates and advertisers.

* Get the Word Out - This is the hardest part. How do you sell your
site to people without a verbal conversation? How do you get media
attention without lots of users and how do you get users without media
attention? Its a formula I'm still working out, but it seems like to
start, word of mouth is valuable and don't hesitate to use smaller
more local media outlets to start. Look through some corporate press
releases and take a stab at writing your own, then send it out and see
what happens.

* Never be Content - This is the most important process step, don't
get cocky and don't think your done. Always look for ways to enhance
and promote your site and keep doing that. Just be sure if its an
enhancement you do it on a dev server first and don't touch your main
site until you can find a window where utilization is low.



Testing Multiple Browsers on one Computer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

My goal was to test everything on every browser version. This is where
an application responsible for virtual machines is extremely valuable
from both a monetary cost and time cost perspective. My choice was
VirtualBox. I had 3 standard images running on VirtualBox to test how
the site would look in each browser, they were set up like this:

* IE6, Opera 8, Safari, Firefox 2
* IE7, Opera 9, Google Chrome
* IE 8, Firefox 3

I named them all accordingly so I'd launch the correct virtual machine
when testing. I'd also recommend, based on the power of your system
not running more than 2 virtual machines at the same time, for speed
purposes I kept it to one at a time.

Once I had my 3 virtual images to account for the most common
browsers, I put together a standard list of validation points items
like:

* Page layout and alignment
* Text sizes and styles
* All forms are functional
* Graphic appearances (Namely IE PNG transparency issue)

I wrote specific test scripts to review on each browser, based on the
fact that I would be reviewing numerous browsers, I wanted to keep
consistency between each. As I reviewed each test script, I made
specific notations of exactly what issues I found and what I did to
find them, this made going back to fix them a lot easier since I would
know exactly how to recreate them. I can't stress enough to write test
scripts out and use them, it helps to be sure your consistent in your
testing.



The Result
~~~~~~~~~~

The results of my work can be found at
`http://www.lifeyoumentary.com`_. I'd love to hear what people think!



.. _http://www.lifeyoumentary.com: http://www.lifeyoumentary.com/

.. author:: Phazo
.. categories:: articles, case_studies
.. tags:: ,Case Studies

