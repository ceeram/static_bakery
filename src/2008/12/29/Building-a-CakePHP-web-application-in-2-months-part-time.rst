Building a CakePHP web-application in 2 months, part time.
==========================================================

This article outlines what I went through to produce the
[url]http://www.demolistic.com[/url] AJAX-driven website over the
course of the last two months, working just nights and weekends.


Project Background
~~~~~~~~~~~~~~~~~~

Demolistic is a list building community with integrated discussion
boards for each list. A user can create a list topic (ie: Best Jazz
Bands of the 1960s), and use the AJAX interface to add entries to the
list. Other users, should they disagree with the list entries, can
quickly spawn and edit their own version of the list. When this
happens, Demolistic calculates a consensus based on all the lists,
similar to a run-off election. Discussion boards are attached to each
list topic, where users can debate the merits of anything put on the
list.

I created Demolistic because I was sick of encountering "Top 10 X" or
"5 things you should XYZ" lists that I totally disagreed with. The
tools Demolistic provides allow users to better participate and engage
authors.

Why CakePHP
~~~~~~~~~~~

If you have an idea for a web-application that has some interesting
requirements, CakePHP provides an incredible springboard for making it
happen. Full-fledged CMS systems such as Drupal drive me nuts for
projects that require anything beyond a basic online-brochure style
website. I've found that I end up spending more time fighting, trying
to implement the feature within the framework, than it would take to
write the CMS framework AND the feature. If you are a knowledgeable
developer, CakePHP offers a better balance of flexibility and
features.

That said, I am incredibly happy that I chose CakePHP for this
project. I encountered very few issues with the library.

I've been asked why I went with 1.2 if it's still in RC. I've found
1.2 to be quite stable thus far. I encountered only two bugs through
the course of the project.

Time Invested
~~~~~~~~~~~~~

When I was first looking at PHP frameworks to use, gauging
implementation time in a given framework was important to me. I'd like
to provide some insight to those of you exploring using CakePHP. I
went from zero to site launch in two months, averaging 25 hours a
week. Roughly 200 hours. I have a full-time job and said to myself
that I would have no life until Demolistic was live. ;) It should be
noted that this was my second major project in CakePHP. Please note
that this was my second major project in CakePHP.

Scope and Feature Creep
~~~~~~~~~~~~~~~~~~~~~~~

Regardless of the project, you're going to encounter this. I was
ruthless about trimming down the project until it was the perfect
balance of needed features, and stuff for the roadmap. If you're ever
feeling overwhelmed by what you want to create, I suggest a process of
iteratively removing the least-needed feature from your list until the
feeling is gone.

The approach I use when doing solo-development is a three stage
process.

#. Make it work well enough to judge if your vision of the feature
   actually works and alter plans if needed.
#. Make it work flawlessly from the user's perspective. I don't care
   how good you code works under the hood, if it's not a good experience
   for the user, you've failed.
#. If you have time, refactor your code so that it works as
   efficiently as possible.

I would like to note that the one thing I make no exceptions on in
step 2 is with regards to security. Do yourself a favor if you're new
to PHP - pick up Essential PHP Security and read the first couple
chapters. Follow the conventions outlined in it and make good use of
CakePHP's awesome Sanitize class.

Modifications to CakeLib
~~~~~~~~~~~~~~~~~~~~~~~~

I did end up making one modification to the Cake Library. I wanted a
simple way to turn off caching for authenticated users. I couldn't
find a hook to check if the user was authenticated before the
dispatcher delivered the cached version of a page. There may very well
have been an alternative solution, but for the scope of the project
and how quickly I wanted to launch Demolistic, it made sense to make a
couple minor alterations.

SEO
~~~

I spend a lot of time ensuring that I followed best SEO practices to
my knowledge. This included building out a flexible keyword generator
and sluggable URLs. The lists themselves are obviously the most
important content on the website, and the router connects them at the
root of the domain.

Frontend
~~~~~~~~

I went with XHTML Strict. I don't see much reason to go with anything
less when you're targeting early adopters. You spend a lot less time
getting things to render correctly and consistently when using XHTML
strict. Internet Explorer 6 and 7 only gave me one headache through
the whole course of converting the PSDs.

As with most CakePHP sites, I went with Scriptaculous and Prototype.
The sortable list control that Scriptaculous provides was a core
requirement for Demolistic. It allows you to quickly reorder your list
as you're building it.

Backend
~~~~~~~

I actually started out with a much more complicated and proper DB
schema when I built the prototype for Demolistic. My initial design
focused data conformity rather than minimizing DB hits. It worked
great from a user standpoint - the consensus version of the list
immediately reflected your contributions. In the end, it made a lot
more sense to move some of the code I built out into batch processing
scripts, implemented in CakePHPs custom shell facility.

Administration
~~~~~~~~~~~~~~

I built out a significant administration console. Any website with
user-submitted content is going to have a jerk, or several hundred, on
it eventually. I did some brainstorming on all the facilities needed
for controlling that. One solution was to build out user-moderation
facilities, like comment rating (and hiding poorly-rated comments.)
But in the end, make sure you have all the facilities in place to take
care up quickly cleaning up after someone should you need to. And make
sure you implement before going live ;)

Development, Staging, and Deployment Environment
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

I use Eclipse with PDT for coding.

My local staging server is Ubuntu 7.10 Desktop. I use the desktop
version because the staging server is my laptop. I tend to do a lot of
work outside my home and don't always have an internet connection.

The live server is a vanilla CentOS 5x. quad-core Xeon server.


Conclusion
~~~~~~~~~~

I hope the mish-mash of information provided in this article will
provide some insight to working with CakePHP. I encourage all would-be
entrepreneurs out there to put your head down and keep with it. Pour
your heart into it. If you have any questions, I'll be happy to answer
them in the comments. Cheers
[p]Demolistic can be viewed at `http://www.demolistic.com`_


.. _http://www.demolistic.com: http://www.demolistic.com/

.. author:: tyler
.. categories:: articles, case_studies
.. tags:: realworld,case study,web application,Case Studies

