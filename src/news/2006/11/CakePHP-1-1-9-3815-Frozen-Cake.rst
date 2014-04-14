CakePHP 1.1.9.3815 Frozen Cake
==============================

by gwoo on November 02, 2006

The latest and greatest release of CakePHP.
We're excited to see so many people enjoying Cake. To show our
appreciation, we are releasing CakePHP 1.1.9.3815[1] Check out the
changelog[2] to see what's new, and what you need to know before
upgrading. This is another bug fix for the 1.1 series and marks the
last release before 1.2. To those who submitted tickets, we'd like to
thank you for your patience while we worked through them. We work
really hard on making CakePHP the most complete and developed
framework, and we appreciate your support in making that happen.

There is one major change in this release pertaining to ACL. If you
are working with the provided database tables for ACL, we changed the
user_id column to be called foreign_key. This makes more sense in a
broader context and allows aros to relate to different tables more
easily.

Run this:
ALTER TABLE aros CHANGE user_id foreign_key INT( 10 ) UNSIGNED NULL
DEFAULT NULL;

Alternatively, an upgrade command has been added to the acl.php cli
script to run this query for you.
php scripts/acl.php upgrade [-app /path/to/app_directory]

There has been a lot of excitement around 1.2. We are working hard to
get all the new features to a point where we feel it is stable and
ready for production use. We will not set a release date, but we are
putting all our efforts into getting it ready as soon as possible.

We're excited to see so many people enjoying Cake. To show our
appreciation, we are releasing CakePHP 1.1.9.3815[1] Check out the
changelog[2] to see what's new, and what you need to know before
upgrading. This is another bug fix for the 1.1 series and marks the
last release before 1.2. To those who submitted tickets, we'd like to
thank you for your patience while we worked through them. We work
really hard on making CakePHP the most complete and developed
framework, and we appreciate your support in making that happen.

We would also like to thank everyone who has donated to the Cake
Software Foundation. We finalized a page at
`http://cakefoundation.org/donors/thanks`_. The Super donors have a
link to their website, if one was provided. If you are a Super and
your name or website link is missing please let us know via the
contact form at `http://cakefoundation.org/pages/contact`_. Some
people have made multiple donations, which is really helpful and they
should see themselves move up the donor ladder. Go to
`http://cakefoundation.org/donations`_ to learn how you can contribute
or click on one of the buttons that appear on the CakePHP sites.

Also, PhpNut has put together a list of books which are valuable to
developers who want to improve there skills
and learn how they can be ninjas like Him.
`http://astore.amazon.com/cakesoftwaref-20`_. Remember, do not try the
nail-through-the-finger trick at home. That is for trained
professionals.

[1] Download: `http://cakeforge.org/frs/?group_id=23_id=155`_ [2]
`https://trac.cakephp.org/wiki/changelog/1.1.x.x`_

.. _http://astore.amazon.com/cakesoftwaref-20: http://astore.amazon.com/cakesoftwaref-20
.. _http://cakefoundation.org/donors/thanks: http://cakefoundation.org/donors/thanks
.. _http://cakefoundation.org/donations: http://cakefoundation.org/donations
.. _https://trac.cakephp.org/wiki/changelog/1.1.x.x: https://trac.cakephp.org/wiki/changelog/1.1.x.x
.. __id=155: http://cakeforge.org/frs/?group_id=23&release_id=155
.. _http://cakefoundation.org/pages/contact: http://cakefoundation.org/pages/contact
.. meta::
    :title: CakePHP 1.1.9.3815 Frozen Cake
    :description: CakePHP Article related to latest release,News
    :keywords: latest release,News
    :copyright: Copyright 2006 gwoo
    :category: news

