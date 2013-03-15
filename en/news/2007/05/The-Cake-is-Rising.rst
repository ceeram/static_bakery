

The Cake is Rising
==================

by %s on May 20, 2007

The latest bug fix release of 1.1.15.5134 is ready [1]. Check out the
changelog[2] to see what was fixed. The latest release of
1.2.0.5137alpha [3] moves closer to beta with the addition of the new
Cache Engines, Console Shells and the Translation behavior.
You may notice fewer queries in both 1.1.15.5134 and 1.2.0.5137alpha
the use IN() in hasMany queries. This should help to speed up some of
the bigger associations.

The Cache Engines include apc, memcache, xcache, file, and model. When
you are ready to speed up your application, you can have a look at one
of these engines. Special thanks to Jiri Kupiainen aka aakour for his
hard work.

The Console Shells are a new way to access Cake based scripts from the
command line. A 'cake' bash and dos wrapper have been provided to make
the commands shorter and these can be placed in your PATH to be
accessed from anywhere. All the scripts previously located in
/cake/scripts have been refactored to use this new functionality. The
Bake shell provides a good example of how you can separate your code
to make it more maintainable and extensible.

The Translation behavior also made it into the core in this release.
This allows you to maintain copies of dynamic information in multiple
languages. Special thanks to Jitka Koukalova aka poLK for her hard
work. With the addition of this behavior i18N should be complete in
1.2.

Security: If you add the Security component to your AppController or
any other controller, all your forms will expect a token and maintain
a hash of the hidden values so they can not be changed. If you have
difficulty with your ajax forms refer to you can disable security[4].

Deprecated methods[5] were removed from 1.2. So, this means if you may
need to use an earlier version of 1.2 if you have not been keeping up
with the latest releases.

Read the 1.2 changelog for complete details[6].

We have some more changes before we can release the Beta. The most
major change will be the transition from defines in core.php to the
$config array used by the Configure class. This will be the last
release that allows for defines inside of core.php. Moving away from
defines provides much greater flexibility. The next release will also
see the inclusion of a Session model, which will allow easier access
to the database session handling.

We hope everyone enjoys this latest release. Though some people in the
community don't like polls, we still want to thank everyone who voted
in the latest one. The results pretty much speak for themselves.

[1] Download 1.1.15.5134:
`http://cakeforge.org/frs/?group_id=23_id=232`_ [2] 1.1.x.x branch
change log: `https://trac.cakephp.org/wiki/changelog/1.1.x.x`_ [3]
Download 1.2.0.5137alpha:
`http://cakeforge.org/frs/?group_id=23_id=234`_ [4] Disabling
Security: `https://trac.cakephp.org/changeset/4978`_ [5] Removed
methods: `https://trac.cakephp.org/changeset/4981`_ [6] 1.2.x.x branch
change log: `https://trac.cakephp.org/wiki/changelog/1.2.x.x`_

.. __id=234: http://cakeforge.org/frs/?group_id=23&release_id=234
.. __id=232: http://cakeforge.org/frs/?group_id=23&release_id=232
.. _https://trac.cakephp.org/wiki/changelog/1.2.x.x: https://trac.cakephp.org/wiki/changelog/1.2.x.x
.. _https://trac.cakephp.org/changeset/4981: https://trac.cakephp.org/changeset/4981
.. _https://trac.cakephp.org/changeset/4978: https://trac.cakephp.org/changeset/4978
.. _https://trac.cakephp.org/wiki/changelog/1.1.x.x: https://trac.cakephp.org/wiki/changelog/1.1.x.x
.. meta::
    :title: The Cake is Rising
    :description: CakePHP Article related to ,News
    :keywords: ,News
    :copyright: Copyright 2007 
    :category: news

