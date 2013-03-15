Changes in CakePHP and new plugins
==================================

by %s on December 09, 2009

With the development of 1.3 and 2.0 well underway the CakePHP team has
taken some time to re-evaluate and reconsider some of the tools we use
as a team and as a community. Recently, CakePHP has undergone several
changes, such as switching from Subversion to Git, and switching from
trac to the current code.cakephp.org issue tracker. Each of these
changes has brought change helping the community and project grow.
We feel at this time that the change to code.cakephp.org (using
thechaw.com software) has not been an entirely beneficial change. The
code.cakephp.org code base has a number of issues, and the present
core team lacks the interest to improve this code as there are
numerous existing solutions that fit our needs. After hours of
discussion and considering many different options and avenues, we've
decided that the best course of action is to utilise one of the many
existing open source supporting project management web applications.
We will be using github[1] for source control. In addition we will be
using lighthouse[2][3] for issue tracking and temporary documentation
in the form of wiki pages.

As before there are separate repositories for CakePHP1.x[4] and
CakePHP2.x[5], as well as separate lighthouse projects. We feel these
changes will provide the community with the tools and resources they
need and have requested. In addition these changes will lighten the
load on the core team allowing us to focus on we do best, create the
best framework and related tools we can. We hope you - the community -
can understand our motivations for wanting to switch and sincerely
apologize for any inconvenience it will cause you.

Once the transition is complete trac and code.cakephp.org will be
deactivated and all activity related to CakePHP will occur on github
and lighthouse. We expect this transition to take no more than a
couple of days. The repository on github has had some of the history
rewritten to fix irregularities and errors that occurred in the past.
If you have local changes in your clones you should be able to migrate
and maintain those changes by rebasing your changes.

At this time we'd also like to announce the introduction of two new
projects, named localized[6] and datasources[7]. Localized is a
project designed to contain all the country specific validators for
1.3. At the time of writing we have 13 countries already partially or
fully implemented. If your country is not in the repository please
fork the project, add your country and send a pull request.

Datasources is a project to contain community powered datasource
classes. Initially this repository will contain the datasources that
have been deprecated and removed from 1.3. Over time we hope it grows
into a useful repository for datasources of all shapes and sizes. By
keeping these two projects separate from CakePHP we can provide a more
flexible release schedule that does not conform to that of CakePHP
itself.

Both datasources and localized have lighthouse projects for issue
tracking so please file any issues with these plugins on their
respective lighthouses[8][9]. If you are interested in getting
involved with either project, please fork the project on github and
send any pull requests.

[1] `http://github.com/cakephp`_
[2] `http://cakephp.lighthouseapp.com/projects/42648-cakephp-
1x/overview`_
[3] `http://cakephp.lighthouseapp.com/projects/42649-cakephp-
2x/overview`_
[4] `http://github.com/cakephp/cakephp1x`_
[5] `http://github.com/cakephp/cakephp2x`_
[6] `http://github.com/cakephp/localized`_
[7] `http://github.com/cakephp/datasources`_
[8] `http://cakephp.lighthouseapp.com/projects/42658-localized/`_
[9] `http://cakephp.lighthouseapp.com/projects/42657-datasources`_

.. _http://cakephp.lighthouseapp.com/projects/42649-cakephp-2x/overview: http://cakephp.lighthouseapp.com/projects/42649-cakephp-2x/overview
.. _http://github.com/cakephp/cakephp2x: http://github.com/cakephp/cakephp2x
.. _http://github.com/cakephp/datasources: http://github.com/cakephp/datasources
.. _http://github.com/cakephp: http://github.com/cakephp
.. _http://github.com/cakephp/localized: http://github.com/cakephp/localized
.. _http://cakephp.lighthouseapp.com/projects/42658-localized/: http://cakephp.lighthouseapp.com/projects/42658-localized/
.. _http://github.com/cakephp/cakephp1x: http://github.com/cakephp/cakephp1x
.. _http://cakephp.lighthouseapp.com/projects/42657-datasources: http://cakephp.lighthouseapp.com/projects/42657-datasources
.. _http://cakephp.lighthouseapp.com/projects/42648-cakephp-1x/overview: http://cakephp.lighthouseapp.com/projects/42648-cakephp-1x/overview
.. meta::
    :title: Changes in CakePHP and new plugins
    :description: CakePHP Article related to CakePHP,news,development,trac,News
    :keywords: CakePHP,news,development,trac,News
    :copyright: Copyright 2009 
    :category: news

