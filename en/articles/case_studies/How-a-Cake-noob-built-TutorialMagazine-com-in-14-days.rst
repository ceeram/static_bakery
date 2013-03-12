

How a Cake-noob built TutorialMagazine.com in 14 days
=====================================================

by %s on February 10, 2009

In this article I describe how I got from idea to launch of a
tutorial-aggregate website using the CakePHP framework. Visit
[url]http://www.TutorialMagazine.com[/url] to view the final result.
â€Find out what people want, and then find a way to give them just
thatâ€
â€“ the secret to online success
Every successful website needs to solve a problem, or fulfill a need.
For me, and perhaps for many of you, finding good tutorials is hard.
There are many tutorial-aggregate sites out there, but most of them
link to thousands of poorly written, uninformative tutorials. My goal
is to showcase only the best tutorials, those that are instructive,
original and useful.

In this article Iâ€™ll describe how Iâ€™ve built the site with
CakePHP, both from a designer and programmers point of view.

Along the way I will share some tips and thoughts that might be
valuable if you are in the process of making your own website or web
service. Letâ€™s get started!


Be a rebel - do things differently!
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
First off you need to find out what will make your site unique. Is it
functionality, design, ease of use, or the quality of your content? If
youâ€™re not going to do anything diffently than your competitors, why
do anything at all? Dare to be different. Take what you think works on
other sites and incorporate your own ideas. Here are some of the
things Iâ€™ve decided to do differently from most tutorial sites.


+

Only great tutorials are showcased
``````````````````````````````````
  How do I decide which tutorials get to be added to the site? Basicly,
  if I like them and find them useful, theyâ€™ll be added. If not,
  theyâ€™re not worthy. Itâ€™s all about providing value to visitors. If
  youâ€™re always featuring crappy content, why should people come back
  to your site?
+

Something new every visit
`````````````````````````
  I want my visitors to always find something useful on my site.
  Something that will make them think â€Wow, what a great siteâ€ and
  â€I should bookmark this!â€ I will try to add content so that there
  is something new on the site every single day.
+

Bigger images
`````````````
  Most tutorial sites only show a tiny screenshot of each tutorial,
  which tells you little about the effect you can learn from it. Iâ€™ve
  used large (600px * 150px) images to stand out from the crowd. You
  wouldnâ€™t buy a painting after seeing it from 200 meters away, so why
  would you be intrigued by the 40x40 pixel thumbnails most tutorial
  sites show you? I want to help visitors click on tutorial links,
  because if they do, they might find something thatâ€™s valuable to
  them, and if that happens they are more likely to come back to my
  site. Make it easy for users to accomplish their goals.
+

Less clutter
````````````
  Iâ€™ve visited a lot of tutorial sites, and most of them seem like
  their main goal is to show people advertisements. For me, filling up
  pages with cluttering ads isnâ€™t helping my users achieve their
  goals. Focus on what is useful for your visitors. Read up on
  typography and whitespace.
+

Provide RSS feed of tutorials
`````````````````````````````
  Some tutorial sites do let you subscribe to an RSS feed, however, when
  the tutorials that are added to their directories are lacking in
  quality, visitors probably donâ€™t click on many of the items in their
  feeds either. I know I wouldnâ€™t.
+

Avoiding Featuritis
```````````````````
  Donâ€™t try to make your website do everything . Focus on the core
  features, and youâ€™ll have less complexity to deal with later. You
  should spend 80% of your time polishing existing features, and only
  20% on new features. In my case the core functionality is finding
  tutorials and suggesting new tutorials . Those are the two main things
  that need to be in place. Everything else is not important. Make your
  own decisions as to what the core functionality of your website or
  service needs to be.



Motivation
~~~~~~~~~~
Iâ€™ve started on way too many personal projects that have never made
it all the way to launch. The problem has rarely been that my website
ideas have been poor or that I havenâ€™t had the time to work on them,
but simply that Iâ€™ve run out of motivation. Part of the reason why I
decided to build and launch TutorialMagazine.com in 14 days was that I
had a feeling I could complete it. Itâ€™s not a very complex site, but
as a newbie everything does take a long time. It took me 14 days from
idea to launch, and had it taken longer I would have gotten bored.
Without CakePHP I donâ€™t think I would ever have completed any
projects! Iâ€™ve really pushed myself to finish the site in two weeks,
and if youâ€™re developing a site, you should too! Getting the site
out there in its basic form requires that you canâ€™t include every
single feature you have in mind, but thatâ€™s a good thing!

Every time I decide to add a feature that I donâ€™t know how to
program (anything ajax, custom javascript, animations etc.) it always
takes a long time find out how to do them. If I canâ€™t figure out how
to do something, Iâ€™ll get frustrated and maybe even abandon the
project, thinking that if the fancy feature Iâ€™m working on isnâ€™t
in place the site wonâ€™t be successful. Usually the simple solution
is the best solution. You probably donâ€™t need ajax or fancy
animations to fulfill your users needs!


Push for launch
~~~~~~~~~~~~~~~
Getting the site out there in the world gives you a sense of pride.
Hearing from others that they find your site useful is a great
compliment, and tremendously rewarding. My challenge to you is to
build something you want to build, do it quickly so you have a chance
of actually completing it, and see what people think. Those are my
thoughts on motivation, now letâ€™s look at my design process.


Design
~~~~~~
When I got the idea for this website, I was picturing a newspaper
layout. I wanted the website to look similar to a real newspaper with
text-columns and photos. Although my vision has changed a little along
the way, Iâ€™ve maintained some of the elements of a newspaper, among
them the giant header text at the top of each page.


Who are my users?
~~~~~~~~~~~~~~~~~
Keep in mind who you are designing for. Are you making a site for
young people, old people? Web professionals, real estate agents? Try
to find out what is important when designing for your potential users.
In my case, I was making a site for people who are trying to learn
about design and how to use Adobe Photoshop, Illustrator and Flash.
Itâ€™s safe to assume that these visitors might be more experienced
with computers than average internet users. Therefore, I decided to
use many graphical elements (as most of my visitors have broadband)
but at the same time limiting the size of image files to not punish
those still using dial-up connections.

Working from a 960pixel width grid ensures that the site looks good
for users using resolutions of at least 1024*768. Smaller resolutions
are less common these days, and Iâ€™ve not done much to cater to these
visitors. You might say â€what about mobile users?â€ Well, I donâ€™t
think many do tutorials from their cellphones (yet) so a mobile
version of the site is not a priority. Donâ€™t do something just to do
it, think about how and under what conditions users will be visiting
your site, and for what reasons.


Naming your website
~~~~~~~~~~~~~~~~~~~
The name and URL of your website is crucial. I decided early on that I
wanted to have the word â€™tutorialâ€™ in the name of the site. Try to
find a catchy name that conveys something about what your site is
about. My site presents tutorials in a magazine-style format, so
TutorialMagazine.com is a natural fit. Had i named it something web
2.0-like, or completely unrelated to tutorials, the odds of users
remembering it are slim. However, if you can find a name thats short,
web 2.0-like and that also conveys meaning (i.e. Flickr) my hatâ€™s
off to you! Also, it is beneficial for Search Engine Optimization
(SEO) if you have important keywords in your URL.

I was also lucky enough that `http://www.tutmag.com`_ was available,
this short address might come in handy later in the lifespan of my
website.


Design process: wireframing, photoshop and coding
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

+

Wireframing
```````````
  I do most of my wireframing on paper. I keep a sketchpad on my desk at
  all times, so if inspiration strikes, I can do a quick drawing of an
  interface element with comments that explain my thoughts. Iâ€™ve found
  that getting things on paper really helps in figuring things out. Try
  showing your sketches to others, you might get valuable feedback early
  on in your design process. For TutorialMagazine, i decided to base my
  layout on a grid (keeping with the newspaper/magazine theme). I found
  it useful to draw out boxes on a piece of paper showing where the
  different elements would be positioned. When I have a general idea of
  what a page should look like, I head over to photoshop.
+

Photoshop
`````````
  Using the wireframe mockup Iâ€™ve drawn on paper, I first add my
  960pixel grid as an overlay on the document (check out
  `http://www.960.gs`_ for a downloadable .psd template). Then I draw up
  boxes showing where the tutorial boxes, the headers, the lists of
  links, and sidebar will be. Note that I follow a very iterative
  process here, Iâ€™m usually not satisfied with a design element until
  Iâ€™ve done at least 5 revisions of it. My way of doing this is
  simple: I save my document as for example â€Main_1.psdâ€. Then,
  after Iâ€™ve made a dramatic change on something in my design, I save
  again, this time as â€Main_2.psdâ€ and so on. This way I can easily
  compare and contrast different versions of the page, and revert back
  to previous ones if i hit a dead end, design wise. Make sure that when
  you save your image files, you use appropriate file types (.jpg for
  photos, .png for graphics) as well as using the â€Save for Webâ€
  function to keep file sizes low.
+

Coding
``````
  This has been the second project Iâ€™ve done in CakePHP, so I am still
  very much a newbie. One of my major goals in writing this article is
  to show people curious about the CakePHP framework that it really does
  simplify development, and sometimes even makes it enjoyable. The Bake
  script, for instance, is one of my favorite parts of the framework.
  What you do is simply to set up model files (put these in /models)
  containing data about the basic structure of your site. For my needs,
  I had to make one model called â€tutorialsâ€ and one called
  â€suggestionsâ€. Now when you run â€cake bakeâ€ (read more about
  the specifics in the cakePHP book: `http://book.cakephp.org`_) the
  script generates the controllers and views necessary to show a basic
  version of your site, including CRUD (Create, Read, Update, Delete)
  actions. Once youâ€™ve done it a few times you realize how much time
  youâ€™re saving. Then I simply start to edit the views and controllers
  made by Baking, customizing layouts and controller actions so they do
  what I want. For example, one function in my tutorials controller
  prints the top 10 most popular tutorials, as you can see on the front
  page. I then add pagination, validation and authentication to my
  project. You can learn more about these features of CakePHP at
  `http://book.cakephp.org`_ [p] I do most of my web development in
  Dreamweaver, using a local version of apache, php and mysql (i use
  `http://www.wampserver.com`_). I test every link and all functionality
  in Internet Explorer 6 and 7, as well as Firefox and Opera (my
  favorite browser) to make sure that everything looks approximately the
  same in all of them. [p] Firefox has some addons that Iâ€™ve found
  useful when developing, one addon I would recommend that you download
  is Firebug. Firebug lets you inspect the page as you are viewing it in
  Firefox, so you can see â€under the hoodâ€ so to speak. It can be
  really helpful when youâ€™re struggling with CSS and layout issues. To
  upload my files to my server Iâ€™ve also recently discovered FireFTP,
  which runs in your browser and takes care of keeping local and remote
  folders synchronized.



Adding content
~~~~~~~~~~~~~~
Try to make the way you add content as painless as possible. I added
over 60 quality tutorials last night, using about 3 minutes per
tutorial. Simplify your administrative duties, donâ€™t just design for
your users, design for yourself! Iâ€™ve made an administration page
with shortcuts to common actions (add tutorial, approve or reject
suggested tutorials, check dead links, visit google analytics etc.)


Post-Launch
~~~~~~~~~~~
After launch, Iâ€™ve tried to build interest by telling friends,
online and off, as well as contacting prominent design-centric sites
asking to be featured in articles, submitting the site to digg(`http:/
/digg.com/design/Tutorial_Magazine_All_of_the_best_tutorials_in_one_pl
ace`_), etc. How you promote your site will of course depend on the
type of site you are developing.


Analyze and Refine
~~~~~~~~~~~~~~~~~~
Iâ€™ve used Google Analytics, feedburner and my own custom built
admin-page to track how many visitors have stopped by, how many
tutorials have been viewed and what the average user rating of the
tutorials Iâ€™ve added is. That way, I can make sure that I keep
adding only quality content. Focus on the user and the userâ€™s needs,
and youâ€™re well on your way to a successful website!


Final thoughts
~~~~~~~~~~~~~~
I hope you have enjoyed this article, and Iâ€™d be happy to answer any
questions you might have.
Suggestions on how to improve the site are of course also very
welcome! If you have any tutorials youâ€™d like to submit, please
visit `http://www.TutorialMagazine.com/suggestions/add`_ or email me
directly at jorgen [at] tutorialmagazine.com

Have fun cakebaking!
Written by JÃ¸rgen T.

.. _http://digg.com/design/Tutorial_Magazine_All_of_the_best_tutorials_in_one_place: http://digg.com/design/Tutorial_Magazine_All_of_the_best_tutorials_in_one_place
.. _http://book.cakephp.org: http://book.cakephp.org/
.. _http://www.wampserver.com: http://www.wampserver.com/
.. _http://www.tutmag.com: http://www.tutmag.com/
.. _http://www.960.gs: http://www.960.gs/
.. _http://www.TutorialMagazine.com/suggestions/add: http://www.TutorialMagazine.com/suggestions/add
.. meta::
    :title: How a Cake-noob built TutorialMagazine.com in 14 days
    :description: CakePHP Article related to launch,motivation,development,design,tutorialmagazine,tutorials,Case Studies
    :keywords: launch,motivation,development,design,tutorialmagazine,tutorials,Case Studies
    :copyright: Copyright 2009 
    :category: case_studies

