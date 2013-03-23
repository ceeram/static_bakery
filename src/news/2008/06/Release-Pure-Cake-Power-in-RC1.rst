Release: Pure Cake Power in RC1
===============================

by %s on June 04, 2008

We are very happy to announce the latest release of the best and most
popular framework for web development. The release marks a great
milestone for CakePHP, where we involved more developers, implemented
better code coverage (with analysis!), received more contributions
from more community members, and ended up with a stable and feature
rich 1.2 release candidate [1]
This release is not without it's major changes, but thankfully this
should be the last of those. The two most significant changes come in
the usage of conditions and the deprecation of several methods.

Query conditions have always been a concern of ours, for the obvious
reason of helping developers maintain secure applications. We found
that in some cases, it was possible to work around what Cake expects
to produce undesirable results. To help ensure that applications are
secure with no extra effort on the part of the developer, we have
moved all operators used in conditions to the "key" side. For example,
$conditions = array('Model.field >' => $value); is the new syntax. We
have maintained backwards compatibility for the most common cases, you
will need to update your affected application code.

Deprecation is something we do our best to avoid, but sometimes in the
interest of a simple, pure API we make the move to remove some of
those old methods that have given way to simpler code.
View::renderElement() is one such method that has given way to
View::element(); You will not see a notice in this release, however,
the next release will throw a notice and in the subsequent release the
method will be removed completely. Speaking of removed methods, any
deprecated code that existed prior to this release is now gone. There
are a few other methods, mainly some more of those found in basics
that have been deprecated. Check out the changelog[2] and have a look
at the API[3] to see where these changes were made.

We also have exciting new enhancements in this release of Cake 1.2. We
already alluded to the code coverage analysis. Code coverage is
helping to ensure that we test as many lines of code as possible. If
you have XDebug installed, you can take advantage of this nice
feature. Another highly desirable feature has been additional support
for more advanced queries. To help in this area we added the
ContainableBehavior. The behavior is a optimized combination of the
code used by Felix (in his behavior by the same name) and Mariano (in
the BindableBehavior). If you were using either of these two behaviors
before you may notice some slight changes to syntax, but overall the
functionality makes for very powerful find operations. Several other
more minor enhancements trickled their way into the core, and we will
leave you to peruse the changelog[2] for those.

With all of these improvements and updates, being a CakePHP developer
and building new projects on CakePHP is better than ever. To help out
the project and everyone in the community, we have partnered with
EliteOpenSourceJobs.com. EliteOpenSourceJobs.com is a start-up that
aims to help open source communities raise funds through hosting their
own job boards. They make it simple and painless for communities like
ours to provide a service for developers and employers to create new
applications and technology together. Check out the CakePHP job board
at `http://cakephp.org/jobs`_. Posting to the job board carries a fee,
but we do that to ensure that the post is legitimate and to help the
continued growth of CakePHP.

We have also begun using Ohloh Journals to let the everyone know what
is going on. You can see the feed of CakePHP team members on the
CakePHP.org homepage and Planet. Ohloh is great resource that helps us
keep track of the project. Check it out and follow us on the
journals[3].

Overall, more than 800 revisions have produced this release. We must
thank everyone who contributed to the effort by writing tests,
creating patches, and committing code. We hope you all enjoy the
release and look forward to a quick release candidate cycle.

Bake on.

[1] `http://cakeforge.org/projects/cakephp/`_ [2]
`http://trac.cakephp.org/wiki/changelog/1.2.x.x`_ [3]
`http://api.cakephp.org/1.2/deprecated.html`_ [4]
`http://ohloh.net/projects/cakephp/messages`_

.. _http://ohloh.net/projects/cakephp/messages: http://ohloh.net/projects/cakephp/messages
.. _http://trac.cakephp.org/wiki/changelog/1.2.x.x: http://trac.cakephp.org/wiki/changelog/1.2.x.x
.. _http://cakephp.org/jobs: http://cakephp.org/jobs
.. _http://cakeforge.org/projects/cakephp/: http://cakeforge.org/projects/cakephp/
.. _http://api.cakephp.org/1.2/deprecated.html: http://api.cakephp.org/1.2/deprecated.html
.. meta::
    :title: Release: Pure Cake Power in RC1
    :description: CakePHP Article related to release,News
    :keywords: release,News
    :copyright: Copyright 2008 
    :category: news

