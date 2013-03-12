

New CakePHP Releases
====================

by %s on October 22, 2007

We have some new releases available for download. Version 1.1.18.5850
is a bug fix update to the current stable release. Version 1.2.0.5875
is the pre beta release of the current development version.
We decided to have pre beta since a lot of new features have been
added since the last alpha release. There are still some new features
to implement, so we are holding the beta release until all the
enhancement tickets are closed. This release took a little bit longer
than expected, but we say it was worth the wait.

As usual, this release includes hundreds of minor fixes, enhancements,
and speed improvements. This release also includes several major
feature additions, which we've been working hard on to implement so
CakePHP remains the best framework for web application development in
PHP.

A number of important features were added to improve support for
implementing web services, including simplified REST routing, i.e.:

PHP Snippet:
````````````

::

    <?php 
    Router::mapResources('posts');
    ?>

This will map each HTTP request to it's corresponding controller
method (index, view, add, edit or delete). For example, a PUT request
to /posts/1 maps to the edit() method of PostsController. In addition,
the Router now supports a whole range of HTTP header detection
options. Controlling access to your web services is easier than ever,
too. The Security component now supports HTTP basic and digest
authentication, which is easily configurable in your controller's
beforeFilter() method.

The Auth component has also undergone extensive refactoring, making it
possible for you to plug in your own custom login authentication
method or access control system.

In the first of several major changes to the Model, findCount() and
findAll() are being replaced by the new find() syntax. This new syntax
allows you to specify parameters as an array, like the following:

PHP Snippet:
````````````

::

    <?php 
    Post->find('count', array('conditions' => array('Post.comments_count' => '< 20'));
    Post->find('first', array('conditions' => array('Post.comments_count' => '< 20'), 'order' => 'Post.date ASC'));
    Post->find('all', array('limit' => 10, 'order' => 'Post.date ASC'));
    ?>

Pagination flexibility has also been improved with the addition of
Model::paginate(), and Model::paginateCount(), which you can define in
your models to handle custom pagination. Each method takes the same
parameters as findAll() and findCount(), respectively.

Also in the database department, schema generation has been
implemented for several databases. Schema generation allows you to
create and edit your database schemas with your favorite tool, and
Cake's schema tools will manage the changes for you.

In an ongoing effort to improve the flexibility of Cake's
(minimalistic) configuration, most of the constants in core.php have
been migrated to settings in the Configure class. Check out the latest
version of core.php and update your app's configuration accordingly.
To help you identify the settings that should be changed, friendly
warning messages have been added to point you in the right direction.

In addition to code, the docs team has been hard at work over the past
few months improving the documentation, and along with this release,
we're now ready to unveil the pre-beta version of the CakePHP 1.2
manual, at `http://tempdocs.cakephp.org/`_. There are still many
things missing, but if you find any errors, please submit
documentation tickets.

And last but certainly not least, significant work has done to lay the
foundation for full, PHP-native Unicode support in CakePHP. This means
we do not have to wait for PHP6 to make it possible to fully
internationalize your PHP application on any platform, independent of
installed extensions.

With all these new features, we hope you agree that CakePHP 1.2 pre-
beta was worth the wait. This will be the last release before the 1.2
API is fully stabilized, and we can't wait for you to try it.

[1] Download 1.1.18.5850:
`http://cakeforge.org/frs/?group_id=23_id=343`_ 1.1.x.x change long:
`https://trac.cakephp.org/wiki/changelog/1.1.x.x`_
[2] Download 1.2.0.5875 pre-beta: [2] Download 1.2.0.5875 pre-beta:
`http://cakeforge.org/frs/?group_id=23_id=344`_ 1.2.x.x change log:
`https://trac.cakephp.org/wiki/changelog/1.2.x.x`_

.. __id=343: http://cakeforge.org/frs/?group_id=23&release_id=343
.. _http://tempdocs.cakephp.org/: http://tempdocs.cakephp.org/
.. __id=344: http://cakeforge.org/frs/?group_id=23&release_id=344
.. _https://trac.cakephp.org/wiki/changelog/1.2.x.x: https://trac.cakephp.org/wiki/changelog/1.2.x.x
.. _https://trac.cakephp.org/wiki/changelog/1.1.x.x: https://trac.cakephp.org/wiki/changelog/1.1.x.x
.. meta::
    :title: New CakePHP Releases
    :description: CakePHP Article related to release,News
    :keywords: release,News
    :copyright: Copyright 2007 
    :category: news

