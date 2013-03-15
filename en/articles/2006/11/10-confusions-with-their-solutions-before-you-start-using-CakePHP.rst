10 confusions with their solutions before you start using CakePHP
=================================================================

by %s on November 16, 2006

Most of the new developers are afraid of spending time with new stuff
in market. This article will guide you about the confusions and how to
overcome them.
After PHP was launched, the language captured most of the market.
Developers liked it because of its simplicity and modularity. But now,
we are in a very high speed growing market and everything needs to be
achieved in short span of time.

Other platforms like `RoR`_ are already in this market and they are
doing pretty good at attracting new developers. Infact there are
rumors like most of `PHP Developers are likely to shift to Rails`_, as
described in my previous post.

Almost every experienced developer hates writing same code again and
again because its really is nothing but wastage of time. For eg:-
Creating same boring add/edit/delete functions for entities and all.
Other problem faced by most of the developers is the organization of
code. Most of the them may not understand how actually to keep their
code organized or if they really need it at starting phases. But when
we reach at later stages, our code becomes messy and more messy.

`CakePHP`_ solves this problem by its MVC structure and and its hot
features like:
[li]Model, View, Controller Architecture

[li]View Helpers for AJAX, Javascript, HTML Forms and more

[li]Built-in Validation

[li]Application Scaffolding

[li]Application and CRUD code generation via Bake

[li]Access Control Lists

[li]Data Sanitization

[li]Security, Session, and Request Handling Components

[li]Flexible View Caching

I have recently started using this and i'm very happy to take my
decision.

Here are the questions/doubts which normally a PHP developer may have
before choosing this as his main framework.

Ques. 1: What actually is Cake, can i really enjoy eating it ?
Ans: CakePHP is basically a ready-made framework for PHP which allows
us to code more structured applications without much pain.

Quote from their website:

Cake is a rapid development framework for PHP which uses commonly
known design patterns like ActiveRecord, Association Data Mapping,
Front Controller and MVC. Our primary goal is to provide a structured
framework that enables PHP users at all levels to rapidly develop
robust web applications, without any loss to flexibility.
Once you seriously start baking your application, i promise you will
enjoy this cake and chocolate cake will no more be your favorite cake
again.

Ques. 2: I am already using my own developed framework and i'm pretty
cool with it, how can it help me ?
Ans: No matter, how extensive your framework is, it can not beat
CakePHP as there is whole dedicated community working on the framework
only.

If you think that your framework will remain good forever, please
think of these:

1. Does it have MVC(Model/Views/Controllers) structure ?
2. Can it support Ajax in forms and various pages just by changing
switch ?
3. Does it have built-in Data Validation ?
4. Caching ?
5. Implementing simplest functions like: add/edit/delete automatically
?

You can see what i am saying at these links:
`CakePHP Manual`_`CakePHP API`_
Why use the framework because its your own piece of work, why not use
something which has growing support of hundreds of developers ?

Ques. 3: Ok, i'm convinced that it might help me organizing the code,
but is it really needed to my client ?

Ans: Most of the clients will never ask you directly to use CakePHP or
any other framework. But they do want:

Timely Delivery and Deployment
There's no doubt that CakePHP will accelerate your development process
once you go learn it fully. Finishing it before deadline means more
bonus plus more debugging period.

Easily Upgradable Code
If you are developing a structured application, then there is no issue
while upgrading of an application because you already have a list of
various Models in your system with their Controllers. You can easily
plan the units to upgrade without going the whole PHP code again.

Fully Functional Code
The way applications are developed using Cake offers us to perform
various tests. Best of all, it allows us unit testing to the simplest
entities and their functions. For eg: If you have built a property
rental system with it (like we did), you know 'property' is entity of
your application and the functions like rent, close_rent, etc. are
more easily tested.

Visual Tweaks after development/deployment
Since, Cake has MVC structure, all views inside Cake are always in a
separate files (*.thtml) which are free of any complex PHP code. This
not only make the Visual stuff easier to implement but makes it easier
to modify too.

Easy understandable Code
All our entities (Models) , functions to these entities (Controllers),
User Interface (View) are in separate files, so i will not start
studying a controller if i am looking to change the Caption of some
input field or any other random task.

CakePHP solves all the above things without you spending any extra
time.

Ques. 4: I studied about frameworks, i liked it. But i am afraid to
invest 'time' learning this application ?

Ans: Yes, its extremely safe from your business point of view at this
time. Web development business is at its peak and applications are
required in short period of time. Deadlines are short, Flexibility is
always required, and more things.... These all can not be taken into
consideration while you are using old methods of development. So, word
of advice... Go ahead and start learning it today.

Ques. 5: I think i can easily implement it in my next application, but
we develop in our team, how to get rest of the team comfortable with
it ?

Ans: Just develop your personal confidence with Cake before you start
telling your colleagues about using this. Once you are fully
confident, show them some impressive stuff on your own which should
help them understanding the fact that it is definately going to help
them (speed is a good stunt to show here, but be as simple as
possible).

Ques. 6: How much time will it take to learn?

Ans: As described earlier, CakePHP is very simple to use and simple to
learn also. It took me less than 4 days to understand the concept and
building a sample application with it. Go through their `video
tutorials`_. They have described things very nicely there. Also, they
have a good community and IRC channel to support you.

Ques. 7: How can i convert my existing applications to Cake?

Ans: These are the basic steps to proceed with your application:

1. Create the list of simplest entities. (Models, For eg: Users,
Properties, Countries... etc)
2. All the functions which will be needed on these entities.
(Controllers, For eg: User login, logout, edit, ban.. etc)
3. Create visual layout for them in plain HTML (Views)

You will find a lot of things to do after you start doing simple
things.

Ques. 8: Can i distribute commercial applications with CakePHP?

Ans: Yes, CakePHP is distributed under the MIT License, and all your
work in your own property. Sell it or Share it..

Ques. 9: What are the other advantages of using Cake (except its MVC
architecture)?

Ans: A lot of advantages like predefined validations, use of Ajax,
etc. Below is the text quoted from this `article`_.

...And the icing'

So that's what CakePHP is about. The project may only have just
entered alpha stage, but the code is already very stable and useable,
as PHPnut, gwoo and Marc said. So what's going to be included in the
beta and stable releases' I researched a bit and asked the developers,
and here's how Cake will probably evolve in the following months:
Cake's built-in data-validation capabilities will be extended. A
validator class - which already exists, by the way - will be extended
to include more data types and expressions to be validated before
being stored in a database.
A new default ACL system will be included and will support database
access and .ini files as well.
The AJAX helper class and AJAX support will be enhanced, featuring
unobtrusive JavaScript and ALA behavior[28].
Multiple applications with the same core files. In the future
developers will be able to create their own Cake application which
could be placed in the app/plugins directory and be seamlessly
integrated and auto-linked to other Cake applications.

Ques. 10: What if i have more questions now or later on ?

Ans: We would love to answer them here, plus you can also join CakePHP
community for more support.

As i said above, Dont hesitate to ask all your questions here. Cake
really helped me and i would love to share my solutions with you if i
can... Thank you

Author: Abhimanyu Grover
`Giga Promoters`_

.. _article: http://www.zzine.org/articles/cakephp
.. _CakePHP API: http://api.cakephp.org/
.. _RoR: http://www.rubyonrails.org
.. _CakePHP: http://www.cakephp.org/
.. _CakePHP Manual: http://manual.cakephp.org/
.. _PHP Developers are likely to shift to Rails: http://www.gigapromoters.com/blog/2006/10/14/majority-of-php-developers-likely-to-shift-to-rails/
.. _video tutorials: http://cakephp.org/screencasts
.. _Giga Promoters: http://www.gigapromoters.com/
.. meta::
    :title: 10 confusions with their solutions before you start using CakePHP
    :description: CakePHP Article related to frameworks,learn,new developers,implementing,plug,about,starter,promote,General Interest
    :keywords: frameworks,learn,new developers,implementing,plug,about,starter,promote,General Interest
    :copyright: Copyright 2006 
    :category: general_interest

