Cruise Holidays Website
=======================

by %s on March 29, 2007

The site is for a cruise company that has 2 ships and offers about 125
cruises per year around the Mediterranean and across the Atlantic.
Passenger capacities are up to about 1900 per cruise. In addition to
allowing users to search for and book cruises online, the site also
offers flights from about 20 UK airports, and stays in about 20 hotels
in Majorca. There are also loads more features that complement the
core functionality. Take a look around the site to see what I mean.
`http://www.islandcruises.com/`_
Data in most of the 106 tables is managed through simple CRUD actions
and views, and others by more complex interactions, in a powerful CMS
whose permissions can be locked down by controller action and user
group. The sales side is backed up with information pages in the front
end, added by a powerful JavaScript-enabled, full-featured content
management system that copes with multiple layouts and various module
types, including data driven "functional" and WYSIWYG text modules.

I was lead programmer on the project and all my experience prior to it
has been in writing procedural code, not OO and not MVC, however,
after spending a few days looking through the manual and checking out
the wiki, we decided to give CakePHP a try. Although, at the
beginning, this seemed like a really bad idea as progress was slow and
heads were literally being banged against a brick wall, as our
experience grew, we began to like Cake more and more. Especially after
discovering unbindModel() - in a site where practically every table
has created_by and modified_by user id fields that belongsTo the
User.id, and where some findAll queries need effectively 7 levels of
recursion, this handy little method is invaluable. One die(pr()); call
resulted in 80 Mb of data before we added unbindModel() calls!

We also found the contributions from the community on the wiki/bakery
and in the snippets, an incredibly valuable resource and in fact made
use of articles and code from othAuth, Pagination, Improved Advance
Validation with Parameters, Sending Email With PHPMailer.

Although I would not recommend you do this, in my naivety when baking
controllers, models and views for the CMS CRUD functionality, I
realised I could hack around in bake.php and tailor the resulting
baked files to our specific requirements. I needed about 50 models,
controllers and index/add/edit screens. This saved enormous amounts of
time. We baked almost our entire CMS in half a day! When I say I don't
recommend you do this... I mean don't hack bake.php, maybe try and
extend it, or write your own based on it. It should make upgrading a
lot easier.

Something else we did was to extract functionality (such as enabling,
disabling and logically deleting records, which we indicated by a
tinyint status field with values of 1,2 or 3), required on multiple
models and controllers to the respective app_ files.

In summary, if someone with no OO/MVC experience can pick up a
framework and create a site as large and complex as this one within a
couple of months, that framework must be pretty damn good, so a big
thanks go to the Cake team, and a big thanks goes to the baking
community who contribute to the wiki/bakery. I expect our next big
application will be developed twice as rapidly!

.. _http://www.islandcruises.com/: http://www.islandcruises.com/
.. meta::
    :title: Cruise Holidays Website
    :description: CakePHP Article related to ,Case Studies
    :keywords: ,Case Studies
    :copyright: Copyright 2007 
    :category: case_studies

