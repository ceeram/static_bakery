Marinas and Yacht Sales Web Site
================================

by neilc on April 15, 2008

The site is for a company that manages 7 marinas along the south coast
of England and also sells yachts.
`http://www.premiermarinas.com`_
The site's primary function is to display content, managed through a
powerful, full featured CMS, but it also allows customers (of which
there are several thousand) to login and see personalised content such
as live weather data at their preferred marina and is integrated with
Wordpress with single sign-in to allow users to comment on posts.

It's this Content management system that I will mainly cover in this
case study. The first thing to mention is it allows construction of a
hierarchy of pages that are Content Managed, Dynamic or Static pages.

Content Managed Pages are built up of rows, columns and modules ;
modules can be of different types such as text & image, image gallery,
links modules and dynamic and static modules.

To edit the content managed pages, a representation of the page is
loaded into a large nested multi-dimensional array of rows, columns,
modules and module data, which is then json_encode 'd and passed to
the view. A custom JavaScript page editor, builds a reduced-styled
representation of the page, and allows editing of it, by dragging and
dropping rows and modules to reorder, add new ones etc. Editing of
modules is done via modal dialogs (lightbox stylee), which contain
various widgets such as rich text editors, image browsers utilising
the power of Ajax that call JSON webservices etc.

Essentially the page editor simply manipulates the data structure it
receives, then posts it back as a string which is then json_decode 'd
back into a PHP object/array, and iterated through, resaving the rows,
columns and modules with the amended data. The whole save process is
(now) wrapped in a transaction to ensure no cock-ups (there was one
just prior to launch where a clean-up routine that was supposed to
delete rows whose all columns had no modules, in fact deleted all rows
in the DB! ï¿½ TFF backups).

The dynamic modules exist in a library (database table) that the
administrator can pick and choose from. They map directly through to
real URLs on the site, and are called via requestAction, sent with the
return parameter to return the rendered content, which is then simply
echo'd when a page is displayed. However, since dynamic modules are
just regular cake controller actions, they can do much more than find
and render dynamic content. They can contain forms for example, which
when submitted can validate the data in the normal way and if valid,
save to the database and redirect off to a confirmation page. This
allows for unprecedented intermingling of content managed and dynamic
content on the same page . See this page for an example:
`http://www.premiermarinas.com/pages/12_month_berthing`_

This is a fully content managed page, the content area of which
consists of 2 rows, the first with a 2:1 column row containing a text
module in the "2" column and 2 static modules in the "1" column. The
second row, is a full width type, and contains a single dynamic
module, which initially consists of a form with options pulled from
the database. On submission of the form, if the data validates,
additional content is displayed within the same dynamic module, which
consists of results of calculations, further content pulled from the
DB and a second form. On submission of this form, if the data
validates, the user is redirected to one of 3 different content
managed pages, depending on the submit button clicked, each of which
contain further dynamic modules with data capture forms and summaries
of previously entered data retrieved from the session.

The second page type, dynamic pages , like dynamic modules, map
through to real URLs in the cake application. They are set up as
dynamic pages so the system is aware of where they sit in the
hierarchy of the site with respect to all other pages (enabling them
to be included in the navigation), and also administrators can control
the page title, meta data, headings, menu texts and even the slug .

The great thing about the way these dynamic pages work however, is not
only is $this->data still available to them, but also, even though
they have their own slugs, URL parameters can be passed through to
them allowing filtering, sorting and pagination of data.

As in my previous site; Cruise Holidays Website
`http://bakery.cakephp.org/articles/view/cruise-holidays-website`_,
this application makes use of the code in the following tutorials on
the bakery:


+ othAuth
  `http://bakery.cakephp.org/articles/view/othauth-0-5-documentation`_
+ Sending Email With PHPMailer
  `http://bakery.cakephp.org/articles/view/sending-email-with-
  phpmailer`_
+ Pagination `http://bakery.cakephp.org/articles/view/pagination`_
+ Improved Advance Validation with Parameters
  `http://bakery.cakephp.org/articles/view/improved-advance-validation-
  with-parameters`_

And also this time, the following:


+ Passing Named Parameters `http://bakery.cakephp.org/articles/view
  /passing-named-parameters`_
+ An improvement to unbindModel on model side
  `http://bakery.cakephp.org/articles/view/an-improvement-to-
  unbindmodel-on-model-side`_

So I'd like to thanks the authors of these for their contributions.

Similarly, in this site, I also enhanced the bake.php script, but
first copied it into an app/scripts/ folder. Modifications included
baking:


#. ï¿½ models with validation in the form described in the "Improved
   Advance Validation with Parameters" tutorial.
#. ï¿½ controllers with index actions that use code from the
   "Pagination" and "Passing Named Parameters" tutorials, and full
   permission checking with code from "othAuth"
#. ï¿½ views with helper functions from "othAuth", "Pagination",
   "Improved Advance Validation with Parameters"

This meant that I had baked a fully functional administration system
for all 33 of my data tables, with validation, pagination, sorting,
access control and authentication, with relative ease.


.. _http://www.premiermarinas.com/pages/12_month_berthing: http://www.premiermarinas.com/pages/12_month_berthing
.. _http://bakery.cakephp.org/articles/view/sending-email-with-phpmailer: http://bakery.cakephp.org/articles/view/sending-email-with-phpmailer
.. _http://bakery.cakephp.org/articles/view/cruise-holidays-website: http://bakery.cakephp.org/articles/view/cruise-holidays-website
.. _http://bakery.cakephp.org/articles/view/an-improvement-to-unbindmodel-on-model-side: http://bakery.cakephp.org/articles/view/an-improvement-to-unbindmodel-on-model-side
.. _http://bakery.cakephp.org/articles/view/passing-named-parameters: http://bakery.cakephp.org/articles/view/passing-named-parameters
.. _http://bakery.cakephp.org/articles/view/pagination: http://bakery.cakephp.org/articles/view/pagination
.. _http://www.premiermarinas.com: http://www.premiermarinas.com/
.. _http://bakery.cakephp.org/articles/view/othauth-0-5-documentation: http://bakery.cakephp.org/articles/view/othauth-0-5-documentation
.. _http://bakery.cakephp.org/articles/view/improved-advance-validation-with-parameters: http://bakery.cakephp.org/articles/view/improved-advance-validation-with-parameters
.. meta::
    :title: Marinas and Yacht Sales Web Site
    :description: CakePHP Article related to ,Case Studies
    :keywords: ,Case Studies
    :copyright: Copyright 2008 neilc
    :category: case_studies

