How I Built a Web 2.0 Dating Site in 66.5 Hours
===============================================

by Mingle2 on April 01, 2007

Let this be a testament to Web 2.0 and the effectiveness of rapid
development frameworks such as CakePHP: I built a full-featured dating
website, from concept to launch, in 66.5 hours. In a typical 9-5 job
this would amount to about a week and a half.
Let this be a testament to Web 2.0 and the effectiveness of rapid
development frameworks: I built a full-featured free dating website
(`http://mingle2.com`_), from concept to launch, in 66.5 hours. In a
typical 9-5 job this would amount to about a week and a half.
Deliverables included:

The Idea - Cooking up a brand with a name, identity, and purpose

Planning - Creating functional specifications, visual wireframes, and
information architecture

Design - Creating mock-ups and defining aesthetics, typography,
positioning, and color

Development - Writing the actual code

Testing - Ironing out the kinks

Launch - Going live

I didn't do this in 66.5 consecutive hours, mind you, these are actual
hours I spent working on the website. I have a day job that keeps me
pretty busy so I could only work on this during my evenings and
weekends. I started keeping a log after the first couple of days
because I realized how quickly everything was coming along and I was
curious how much time it would take me to finish. This is a guide
providing tips and tactics I employed to develop this website in such
a short amount of time.

Identify an Opportunity I'm single and after trying the online dating
thing I quickly ascertained two things:
- The paid online dating market is very saturated
- The free online dating market is also saturated but with sites that
are clunky, difficult to use, so littered with ads they're nearly
unusable, and bombarded with useless features
I saw an opening and I took it: I knew I could build something better
in a very short period of time with almost no overhead. The beauty of
this is that if this site isn't successful there's no layoffs, burned
VC funding, and I'm ultimately not contributing to another dot-com
crash. All I've lost is 66.5 hours and a couple bags of coffee beans,
I'll just go back to my day job.

Brain-dump First I did a brain-dump of all the features I'd like to
have on a dating website. I didn't put them in any particular order or
attempt to categorize things, I just wrote them down as they came to
me. This is a great tactic for just getting it all out there, save
making sense of it for later.

Generate ideas from your competition I didn't want to lock the feature
list into only my ideas, so I went and signed up for nearly a dozen
online dating websites and got a feel for how most of them work.

Brain-dump some more After a couple of hours of surfing competitor's
websites I did another brain-dump of features. I combined the list of
their features with my own.

Have a specific goal, don't try to make the website do everything I
took the feature list and narrowed them down to only those that served
a single purpose: providing a means for singles to find and
communicate with one another. I ditched all the popularity contests,
"rate my photo" clones, and other features that didn't directly
contribute to this goal.

Keep. It. Simple. Stupid You know those collars for dogs that issue an
electrical shock every time they start barking? I wish every CEO and
marketing professional in the tech industry could was equipped with a
similar device that would shriek "KISS" into their ears every time
they began making things unnecessarily complex. Throughout development
I would remind myself of the KISS principle.

Minimize interference Only utilize other people when you absolutely
have to, especially if you plan on keeping overhead low. I saw the
project from start to finish before I wrote a single line of code and
knew that I could do everything on my own. There were no design
meetings, Gantt charts, or conference calls - just myself, my
computer, and my ability.

Avoid "feature creep" Although this problem is usually more prevalent
when working with a client, it can happen while going solo as well.
Learn to avoid letting an idea grow and distort to the point where
you're 6 months into a project and all you have to show for it is one
of the most massively complex nav bars in the history of the internet.
Learn to turn the idea knob down, but not completely off.

Web 2.0 names are going to be very tacky in a few years Prefacing your
domain name with "cyber" was very popular in the mid-nineties, but
would you do it now? I think Web 2.0 names like "SquaBlar", "Fastr",
or "thisdomain.is.friggin.ridiculo.us" are going to quickly become
passe. I wanted a name that was clever, indicative of the site's
purpose, and was easy to say and type. Plus I get to use the
superscript tag :)

If you get stuck on something, put it on the backburner I actually had
a pretty hard time coming up with a name. I knew that naming the site
was not crucial to completion, so I put it off and worked on other
things. I actually referred to the site as "barnacle" for the majority
of development because I needed a temporary name and it was the first
thing that came to mind.

Prioritize features so you can give prominent real estate to those
that need it I took the list of features I'd made from my brain-dump
earlier and ranked them according to priority. I made it so the
primary features would be accessible in the most visible sections of
the website, in Mingle 2 's case this would be promoting that the site
was free, the login/signup, and the "search singles feature." It's
absolutely critical that you have a sense of priority before you begin
designing a website.

Put a lot of work into the functional mockups A functional mockup is
basically a design with no pretty stuff. There are several tools and
methods for creating a functional mockup: prototyping software,
scribbling on a whiteboard, drawing it in photoshop. Some people
prefer starting out with a taxonomy, others like to draw the pages. I
prefer pen and paper with the occasional whiteboard. I usually start
out by drawing how all the pages relate to one another, like a road-
map. From there I draw what's actually inside those pages and try to
get an idea of how it all relates. This usually involves a lot of
writing, crumpling of paper, and writing again. Don't expect to get it
right the first time, I've had pages where I'll lay it out dozens of
different ways before I'm happy.

Mix it up, keep things interesting I didn't outline every single page
on Mingle 2 in one go, I stopped and switched to working on the visual
parts of the site often throughout the planning phase. I interspersed
designing the logo and visuals in between to keep me motivated. It's
important not to dive head first into writing code or playing around
in photoshop, but don't feel obligated to map out every piece of your
site before you start playing around. Switch up tasks frequently,
it'll make you enjoy it more.

The Design Next came the fun part: creating the design that I intended
to eventually turn into HTML. There's an endless amount of knowledge
one could give another about creating a great design, but instead I'm
just going to focus on what I wanted to achieve visually with Mingle 2
:

1. Balance - I placed special emphasis on this on the home page, I
wanted the elements to be in a state of equilibrium. One of the most
balanced sites I've ever seen is Gr0w.com, I wanted to achieve
something similar.

2. Holy crap - I wanted to create something that would incite a "holy
crap that's pretty" reaction from a newcomer to the website. I don't
know how close I came to achieving this goal, but I'm happy with how
it turned out.

3. The Year is 2007 - The majority of competing websites look like
they were designed by dinosaurs, I wanted to outshine them in this
regard.

Set little itty bitty goals and watch how much you'll get done I work
a 9-5 job and I normally spend over an hour exercising every night, so
when I arrived home around 7pm my work ethic was usually in the
gutter. The best tactic I found for motivating myself was to set a
very small goal, such as changing some columns in the database or
adjusting margins on a certain page. Typically, once this small goal
was achieved it would lead into other things and pretty soon I'd
gotten three hours worth of solid work done.

Utilize rapid-development frameworks I built this site using CakePHP,
a rapid-development framework that is best described as rails for PHP,
using design patterns such as MVC and ActiveRecord. Frameworks
typically take all the repetitive tasks out of web development such as
CRUD (create/read/update/delete), forms validation, and data
sanitization and instead let you focus on making a killer website.

Expect a learning curve from whatever framework you choose This is the
fifth site I've built using CakePHP so I know my way around. Don't
expect that by switching to a rapid-development framework you'll
instantly save a bunch of time, there's a bit of a learning curve.

It's out of the oven I'm very happy with how Mingle 2 has turned out.
The design looks good, the code is clean, and it just plain works
well. The best part is: If Mingle 2 fails and goes quietly into the
night, it was only 66.5 hours out of my life.

`http://mingle2.com`_ - free online dating

.. _http://mingle2.com: http://mingle2.com/
.. meta::
    :title: How I Built a Web 2.0 Dating Site in 66.5 Hours
    :description: CakePHP Article related to design development p,Case Studies
    :keywords: design development p,Case Studies
    :copyright: Copyright 2007 Mingle2
    :category: case_studies

