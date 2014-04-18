New Year, New Beta
==================

The CakePHP team is happy to wish everyone a wonderful new year.
Entering the 3rd year of the development we can reflect back on what
was done. A year ago, we released Cake 1.2 dev. We have worked solidly
over the last year to stabilize the new features and provide a growing
platform for development. Our goals to provide the easiest framework
are coming to fruition and we feel honored to share this with all of
you. We have hit our fair share of bumps, received our fair share of
bruises, and persevered to bring together something we can be proud
of.
For this year, we would like to share CakePHP 1.2 Beta. We can
honestly and unequivocally say this is our best release ever. The
number of new features alone make Cake a joy to use, but given the
process of development we have allowed these features to mature,
finding the proper balance between usability and extensibility. Sure,
many things have changed from the 1.1 days, but a lot of that old code
should still work just fine. Where we could add, we did so with
caution and care. Where we needed to rip apart, we sliced and diced to
find elegant solutions. The end result is a feature-rich development
framework without too many significant changes. Most changes require a
simple find and replace with clear error messages to get you to the
right place.

Some of the new features in 1.2 may seem old to people. Those in the
community who have not had a chance to try out Cake 1.2 will be happy
to see some of these features being supported by the core.

Router improvements including reverse routing for array based urls,
parseExtensions for handling multiple content types, mapResources for
handling RESTful automagic, and named arguments with a default format
of "name:value".

Forms are easier to create and maintain. Including the handling of
multiple records and complex data structures. Automagic REST handling
and unique handling of GET and POST types. Forms "know" your model and
you should thank them for it.

Security enhancements for better CSRF prevention and HTTP
Authentication.

EmailComponent handles html and text messages via templates, layouts
or on its own. It may not look like the most full featured component,
but when it can handle attachments, html, and text there should to be
too much left wanting.

CookieComponent for securely storing persistent data on the client
side

Behaviors allow the model functionality to be extended and
encapsulated by providing a simple and reusable interface. Tree and
Acl included for your enjoyment.

The "with" key allows you to define a dynamic join table model and
access it as your would any other model

Validation has been greatly extended to include the majority of common
validation methods

Pagination of model records with an extensive helper for neatly
displaying access to multiple pages and sorting the records

Internationalization and Localization with static translations in
gettext style or dynamic translations of model data.

Authentication component to validate user accounts tied to Access
Control made easier through the behavior to handle user permissions.

Configure class to provide dynamic handling of configuration settings
and App class to handle importing required classes

Cache Engines to provide an interface to memcache, xcache, apc, the
file system or database to help speed up your application and provide
access to persistent data.

Console is a complete mini framework for creating command line
interfaces for your application and development environment. The new
console provides an extensible shell and task system. To setup the new
console, see the screencasts `http://cakephp.org/screencasts`_ Bake
has been greatly improved with the ability to have custom templates
for views as well as directly access specific code generation tasks.
An interactive console allows you to run code before your write it.
The API shell gives you access to documentation without digging
through the code. Schema provides a versioning and distribution
interface for your database structure. ACL allows you to CRUD your
permissions and grant/deny/inherit access. The extractor makes
creating static translation files easier and faster by pulling strings
from your code.

A lot of new stuff and not a whole lot of documentation on it just
yet. This is also improving as the docs team is hard at work on
`http://tempdocs.cakephp.org`_. We need your help as always, so if you
can spare some extra time talk to _psychic_ or gwoo in IRC.

When you head over to `http://cakephp.org`_ you should see something
new and exciting to go along with the beta release. Once again, a
special thanks to Armando Sosa for providing the graphics and layout.
Several new features and some old ones that received a complete
rewrite for 1.2.

So, out with the old and in with new. May 2008 be more exciting and
successful for everyone.
We have a lot planned, including something with a 2, a dot, and a 0.

Happy Baking,

Beta: 1.2.0.6311 `http://cakeforge.org/frs/?group_id=23_id=372`_
1.2.0.6311 Change log:
`https://trac.cakephp.org/wiki/changelog/1.2.x.x`_
Stable: 1.1.19.6305 `http://cakeforge.org/frs/?group_id=23_id=371`_
1.1.19.6305 Change log:
`https://trac.cakephp.org/wiki/changelog/1.1.x.x`_

.. __id=371: http://cakeforge.org/frs/?group_id=23&release_id=371
.. __id=372: http://cakeforge.org/frs/?group_id=23&release_id=372
.. _http://cakephp.org: http://cakephp.org/
.. _http://tempdocs.cakephp.org: http://tempdocs.cakephp.org/
.. _http://cakephp.org/screencasts: http://cakephp.org/screencasts
.. _https://trac.cakephp.org/wiki/changelog/1.2.x.x: https://trac.cakephp.org/wiki/changelog/1.2.x.x
.. _https://trac.cakephp.org/wiki/changelog/1.1.x.x: https://trac.cakephp.org/wiki/changelog/1.1.x.x

.. author:: PhpNut
.. categories:: news
.. tags:: ,News

