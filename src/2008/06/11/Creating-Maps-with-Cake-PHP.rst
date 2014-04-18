Creating Maps with Cake PHP
===========================

A social mapping application built with Cake: MAPME.com (BETA) is a
social mapping application that allows users to create maps, add
content and start communities.

The site was developed to allow as much flexibility as possible and
features a customizable permissions system that allows a map owner to
control the way information is presented on their maps. Visitors to a
map on the site (once registered) can interact with maps by adding
their own content, which includes comments, ratings, photos and
Youtube Videos.

An example a public map which requires moderation:
`http://www.mapme.com/map/tokyoneighbourhoods`_

As well as public or community maps, a user can create a personal or
even a private (non-viewable) map that is only available to map
members. Public maps are usually a free-for-all where all registered
users can contribute. Community maps on the other hand would usually
require moderation before content appears on the map. By default, the
map owner is the sole moderator, although other map members can also
be assigned as a moderator.

Once a map has been created, or a registered user has become a member
of a map, they are free to start adding (or suggesting) Hotspots (map
markers). Each Hotspot on a map represents a â€œnodeâ€ that is nested
within a typical CAKE category heirarchy. Each node also forms the
basis of of static (entry pages) which are designed to funnel traffic
back to the main map view...

`http://www.mapme.com/nodes/view/30458-nakano-station`_

The nodes are parsed onto the map using JavaScript where they can be
edited through the map interface. As mentioned before, owners (and map
members) can add text, images and videos. The nodes are also present
on the map menu (Jquery tabs) which can be used navigate through the
various Hotspots and locations on the map. Controls within the map
interface also provide another way for visitors to the site to sort
and view content.

The permissions system which allows all of this to happen smootly is
built around the CAKE user admin / user module which is also the
foundation of the custom inter-site messaging system which we had a
3rd party build for us. Working in CAKE meant that it was a breeze to
outsource components of the site to other developers.

MAPME.com also makes use of CAKE pages, and nearly every other part of
the framework (pagination, all path assignment, headings, meta data,
filtering, RSS etc). In particular the CAKE Test Suite, Pagination and
Caching features where particularly useful;

Cake Test Suite `http://bakery.cakephp.org/articles/view/testing-
models-with-cakephp-1-2-test-suite`_
The test suite available with CakePHP was an absolute life-saver
during development. With such a complex map interface to manage with
data flowing in every imaginable direction, it was vital to have a
quick and easy way of running diagnostic tests on the various critical
systems - Since this was the first site we'd built using CakePHP, we
developed the use of the test suite slightly later in the development,
but from experience I'd now say "test quickly, test often" It'll take
a bit longer, but you'll thank yourself in the long run, especially
when you're staring bleary eyed at non-functional code at 3am!

Cake Pagination
Pagination provided a wonderfully simple way for us to present data to
the users, even through an AJAX interface - on the map itself, you can
view lists of comments, videos, images, and each of these can be
dynamically loaded from the server with a list of page options.

Caching `http://bakery.cakephp.org/articles/view/optimizing-your-
cakephp-elements-and-views-with-caching`_
One of the areas focused on later in development was site
optimization, and the Cake cache was a hugely important tool here.
Especially on the main index pages, we managed to reduce the average
number of database queries down by a huge factor using the cache to
store pre-fetched data. Using the cache for the map data was a little
trickier, and not always applicable, since we have the option of
allowing a user to select from a wide range of filter options, and so
this data has to be created upon every request.

If you have any questions about implementation of CAKE with Google
Maps, then please submit your questions through the helpdesk, the guys
at MAPME will be happy to help you out.


For more information about the site, please visit:
`http://www.mapme.com/pages/About-Us`_

.. _http://www.mapme.com/map/tokyoneighbourhoods: http://www.mapme.com/map/tokyoneighbourhoods
.. _http://www.mapme.com/nodes/view/30458-nakano-station: http://www.mapme.com/nodes/view/30458-nakano-station
.. _http://bakery.cakephp.org/articles/view/testing-models-with-cakephp-1-2-test-suite: http://bakery.cakephp.org/articles/view/testing-models-with-cakephp-1-2-test-suite
.. _http://bakery.cakephp.org/articles/view/optimizing-your-cakephp-elements-and-views-with-caching: http://bakery.cakephp.org/articles/view/optimizing-your-cakephp-elements-and-views-with-caching
.. _http://www.mapme.com/pages/About-Us: http://www.mapme.com/pages/About-Us

.. author:: map_me
.. categories:: articles, case_studies
.. tags:: ,Case Studies

