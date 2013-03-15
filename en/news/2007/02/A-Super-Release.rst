A Super Release
===============

by %s on February 05, 2007

Here in the US, the national holiday of Super Sunday has brought more
than just the Super Bowl.
We have two super releases, in CakePHP 1.1.13.4450 and CakePHP
1.2.0.4451alpha. CakePHP 1.1.13.4450 is a bug fix release for the
latest stable version, while CakePHP 1.2.0.4451alpha moves us closer
to a feature-complete 1.2. The latest addition to 1.2 is a
CookieComponent and AuthComponent, along with continued improvements
to forms, pagination, and DataSources. Bakery tutorials on this and
more will be forthcoming, as more features are stabilized.

Also new in 1.2 is the (re-)addition of the CakePHP test suite into
the core distribution. All you need to start testing is to grab the
latest version of SimpleTest from
`http://www.simpletest.org/en/download.html`_, drop it into your
vendors directory, and make sure (if you're updating an existing app)
that test.php is in app/webroot (don't worry, this page is not
accessible in production mode).

We will be publishing tutorials on the Bakery which will demonstrate
not only how to test your own apps, but also the Cake core. With this
new release, you'll have no excuse not to write well-tested
applications. ;-) Also with this release, we are instituting some new
bug-reporting policies: First, patches not submitted in diff format
will not be considered. We appreciate your hard work, but if we have
to manually review large blocks of code to isolate a change, it
doesn't help us very much. Second, bugs reports with attached tests
will take priority over bug reports without. Remember, if you submit a
bug (or better yet, a patch!) with a test that proves it, remember to
put "[TEST]" at the beginning of your ticket name.

These releases received quite a bit of attention over the last month.
With the previous releases getting over 40,000 downloads (40061), we
saw more new people joining the community and contributing. Over 90
issues were reported and fixed, with more than 30 revisions to 1.1 and
over 100 revisions to 1.2. We are excited with where the code is
going, and happy to see so many people joining us along the road. We
continue to maintain the mantra that simpler is better and less code
does more.

In case you missed it we updated the CafePress store.
"These posters are awesome!" -John Anderson, after receiving his
CakeSheet posters.
Check out the CakeSchwag at `http://www.cafepress.com/cakefoundation`_
Check out the CakePHP book store at
`http://astore.amazon.com/cakesoftwaref-20`_
Download 1.1.13.4450 `http://cakeforge.org/frs/?group_id=23_id=179`_
Change log 1.1.13.4450:
`https://trac.cakephp.org/wiki/changelog/1.1.x.x`_
Download 1.2.0.4451alpha
`http://cakeforge.org/frs/?group_id=23_id=180`_ Change log
1.2.0.4451alpha: `https://trac.cakephp.org/wiki/changelog/1.2.x.x`_
These releases make changes to app/webroot/index.php, so you may want
to update this file in addition to the core directory.

Happy Baking,
The CakePHP Dev Team

.. _http://astore.amazon.com/cakesoftwaref-20: http://astore.amazon.com/cakesoftwaref-20
.. __id=180: http://cakeforge.org/frs/?group_id=23&release_id=180
.. _http://www.cafepress.com/cakefoundation: http://www.cafepress.com/cakefoundation
.. __id=179: http://cakeforge.org/frs/?group_id=23&release_id=179
.. _http://www.simpletest.org/en/download.html: http://www.simpletest.org/en/download.html
.. _https://trac.cakephp.org/wiki/changelog/1.2.x.x: https://trac.cakephp.org/wiki/changelog/1.2.x.x
.. _https://trac.cakephp.org/wiki/changelog/1.1.x.x: https://trac.cakephp.org/wiki/changelog/1.1.x.x
.. meta::
    :title: A Super Release
    :description: CakePHP Article related to release,new release,1.2,News
    :keywords: release,new release,1.2,News
    :copyright: Copyright 2007 
    :category: news

