Clearing Up Some Confusion on the Release Versions of CakePHP
=============================================================

by %s on August 31, 2009

There seems to be a bit of confusion as to what version of PHP will be
supported in what CakePHP releases, and where to find which projects
on [url]http://code.cakephp.org/projects[/url]. Hopefully, this post
will help answer any questions you may have.
If you only read one line of this entire post, read this: CakePHP 1.3
!= Cake3.

Now, while this may seem obvious to many, there are quite a few people
who seem to be confused about the naming of the different versions of
CakePHP.

CakePHP 1.2 supports PHP 4 and PHP 5.2, inclusively. PHP 5.3
introduced a few changes to the error handler flags, which we have
accounted for in CakePHP 1.3-dev and will be accounting for in all
subsequent releases.

The reason we are not including PHP 5.3 compatibility in CakePHP 1.2.x
is that we want people to move forward to CakePHP 1.3 & up when they
become stable. If you're willing to update the php *language* to a new
point release and not willing to update your application to a new
framework point release, I think you have a slight disconnect. If you
want, you could always apply the patches that we have committed to
1.3-dev to a local git branch, and continue on your merry way by
merging in upstream changes from 1.2.x into your local branch.

CakePHP 1.2.x and 1.3.x are in the same project, at
`http://code.cakephp.org/cakephp`_. The 1.2 and 1.3-dev releases are
simply different branches.

Note: As of now (1251729076 seconds since Unix Epoch), CakePHP 1.3 is
not considered stable. We encourage everyone to checkout the 1.3-dev
branch, play around with some of the new features, and file bug
reports and/or enhancement requests, since now's the best time to get
any new desired features worked on. For notes on the minimal amount of
backwards incompatible changes occuring in CakePHP 1.3, please consult
the migration guide: `http://code.cakephp.org/wiki/1.3/migration-
guide`_

CakePHP 2.0, a different project , is also being worked on, which will
be PHP 5.2 and up only, meaning we're dropping support for PHP4. See
`http://code.cakephp.org/cakephp2`_ to follow along the development of
this project. The goal of this release is to be a nearly transparent
upgrade for anyone running CakePHP 1.3 stable (whenever that is
released), meaning that the two should be pretty much 100% API
compatible.

Finally Cake3 is a different project, found at
`http://code.cakephp.org/cake3`_. This is an experimental project
which is a complete rewrite of the CakePHP framework from the ground
up, and will only support PHP 5.3 and up.

I hope this clears up some of the confusion.
-jperras.


.. _http://code.cakephp.org/cakephp2: http://code.cakephp.org/cakephp2
.. _http://code.cakephp.org/wiki/1.3/migration-guide: http://code.cakephp.org/wiki/1.3/migration-guide
.. _http://code.cakephp.org/cake3: http://code.cakephp.org/cake3
.. _http://code.cakephp.org/cakephp: http://code.cakephp.org/cakephp
.. meta::
    :title: Clearing Up Some Confusion on the Release Versions of CakePHP
    :description: CakePHP Article related to CakePHP,cake,releases,git,News
    :keywords: CakePHP,cake,releases,git,News
    :copyright: Copyright 2009 
    :category: news

