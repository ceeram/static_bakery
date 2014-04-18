Creating a Simple Questionnaire App and Integrating it Into an
Existing DB Schema
==================

I have a legacy database. I need to build a questionnaire app. I need
to connect the questionnaire db to the legacy db. CakePHP to the
rescue!
First of all, a tiny bit about me: I am a Web Developer and have been
using PHP since early 2000. I have taught an "adult education" intro
to PHP class (i.e. a 2 day seminar-style continuing ed type class). I
know PHP pretty well. But I have only started using CakePHP this year,
and have finally made it up the steep climb of the learning curve to
feel reasonably comfortable using CakePHP. But I am FAR from a CakePHP
expert! My goal for this article is to provide an interesting case
study that is part CakePHP tutorial, and part CakePHP documentation
for myself to be able to remember this, that, or the other about
CakePHP. Whew! Here goes...

Allright, so, to start, let's sketch out the basics of what a
questionnaire needs -- the model. Some parts of my questionnaire model
may seem really obvious, some parts might not, and still other parts
might appear to be missing. I tried to strike a balance between a very
complex yet extremely flexible model and a very simple yet unflexible
one, and still capture all the data any potential questionnaire might
need. This particular Questionnaire is a student questionnaire that
asks questions about the course they just took and the professor of
the course.


Describing the Application's Needs
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Before discussing the Model technical details, let me describe the
questionnaire in plain ol' English. I do this for any application I
develop, as it is a good exercise to help one really get an
understanding of what one is going to have to build. And, more to the
point, I do this step on paper, with a pen ! (Remember pens?)

A "Questionnaire" is made up of several parts.


+ The questionnaire itself
+ A section describing the subject, (in my case, what course and
  faculty member), about which questions are being asked.
+ A section describing the person filling out the questionnaire.
+ The questions themselves.
+ Possible answers, some of which are predefined multiple choice, and
  some of which are fill-in-the-blank.

Okay, so, that wasn't too bad, right? One can see 5 distinct parts,
and these lead naturally to the database modelling.


Questionnaire Parts as Database Model
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

+ Faculty
+ Course
+ Student
+ Questionnaire
+ Question
+ Answer
  +

(Note that, following CakePHP conventions, I have written the Model
names as singular and capitalized (actually, CamelCased, but that is
not evident in these single-word Models).)

Hmmm... Seems the five plain-English parts have led to six database
model parts! But what's more, I think I am missing something. After
thinking for a while on how to capture the student responses, I
massaged the final model into the following, (which I have also
reordered to indicate where relationships occur):


+ Faculty
+ Course
+ Questionnaire
+ Question
+ Answer
+ Response
+ Student

And here are the relationships:


+ Each Faculty member has One Questionnaire
+ Each Course has one Questionnaire
+ Each Questionnaire has many Questions
+ Each Question belongs to a Questionnaire
+ Each Question has many Answers
+ Each Answer has many Responses
+ Each Response belongs to a Student
+ Each Response belongs to an Answer
+ Each Student has many Responses




.. author:: jdsilveronnelly
.. categories:: articles, case_studies
.. tags:: sample app,questionnaire app,Case Studies

