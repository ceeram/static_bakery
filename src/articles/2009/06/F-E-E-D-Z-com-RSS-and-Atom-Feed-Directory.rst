F-E-E-D-Z.com - RSS and Atom Feed Directory
===========================================

by %s on June 02, 2009

It might have seemed like a very simple idea at first but I quickly
learned that building a feed reader/directory was a bit beyond just
simple data gathering and validation.
`http://www.F-E-E-D-Z.com`_ took approximately 4-5 part-time days to
map and build. The general structure was to have categories as a top
level, the feeds as a second level and then each of the feed items as
the third level. I had chosen to do Categories as a Tree, of course
which has worked out well. I setup associations as:

Category hasMany Feed
Feed belongsTo Category and hasMany FeedContent
FeedContent belongsTo Feed

This allowed me to basically, starting from the home page, display
top-level categories, 10 most recently updated feeds from those
categories and sub-categories with one FeedContent from each of those
10 feeds. Before the FeedContent->Feed association I found even if a
Feed was published and had a name (my primary two conditions) it may
not have contents associated with it because there was an error
reading the feed, for example. So I'd have an empty space on the site
with mention of the Feed and Category, but not FeedContent. Setting up
that association and adjusting the query has worked well.

I used the Simplepie
(`http://simplepie.org/wiki/plugins/cakephp/simplepie_module`_)Cake
component which runs off cron jobs inside CakePHP. The component takes
care of retrieving the data, caching the feed and delivering it to
Cake which then checks for duplicate data and inserts. The major issue
I've had with this is that some categories (i.e., news, sports) I
wanted updated quickly - as fast as possible. Others I was looking for
something more on a daily basis. I tried updating all the feeds off
one cron job but found there was a gradual separation of time between
last modified and now. I tried creating cron jobs for a number of
categories with one generic job for the remaining categories. I found
that didn't work too well, either as some categories were being
skipped (The same job would be told to access two categories, though
for whatever reason would only update one. Debug did not reveal any
explanation so I abandoned this method). Finally I minimized the cron
jobs to the basic categories and altered the scheduling a bit so that
some categories were updated every 20 minutes or so and some were
updated every minute. It seems to work out well with my lag time being
only about 18 hours for the infrequent categories such as Arts. That's
with an index of over 5,000 feeds and 170,000+ articles. As I add more
feeds it'll obviously cause additional delays so I'll have to figure
out a way to compensate for that.

I also added a little search engine though I'd like to add a more
advanced feature right now. The search brings back all matching
results - I'd like to allow the User to select what categories they
want to search and what-not. But that'll come soon.

Finally, admin (which really isn't an Admin as I didn't do the routing
and what-not) using Auth Component. When I log in I can review
submitted feeds, assign them to a category and approve or delete. I
can also manually run cron jobs from this point. I hope to add soon
the ability to change categories from within the Feed page itself,
unpublish the feed and change details as well as delete inappropriate
feed contents and what-not. Currently it does not which is ok cause
I've become an excelled PHPMyAdmin user by now!

Additionally I created a sitemap that at first was a simple initiation
of a tutorial here in the bakery. Now, however because of the
tremendous number of feeds we've indexed I have to adjust - it
currently display all categories and all Feeds associated with those
categories. I'll need to create multiple sitemaps likely for each
parent category or just every category and then an index sitemap.

I have other implementations I hope to start adding soon and I'll be
posting those updates on my blog (`http://www.timtrice.com`_. But, for
competitive purposes I'll keep those secret right now! :)

Don't get me wrong, there's still some issues and quirks that I work
out as I get a chance. But overall, I'm actually pretty proud of this
website. Considering how short it took to build it, I think it came
out rather well and immediately I've seen results from Google and
Yahoo due to the easy SEO-implementation.

.. _http://www.timtrice.com: http://www.timtrice.com/
.. _http://www.F-E-E-D-Z.com: http://www.F-E-E-D-Z.com/
.. _http://simplepie.org/wiki/plugins/cakephp/simplepie_module: http://simplepie.org/wiki/plugins/cakephp/simplepie_module
.. meta::
    :title: F-E-E-D-Z.com - RSS and Atom Feed Directory
    :description: CakePHP Article related to feeds,Rss,simplepie,sitemap,Case Studies
    :keywords: feeds,Rss,simplepie,sitemap,Case Studies
    :copyright: Copyright 2009 
    :category: case_studies

