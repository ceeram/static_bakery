

The Gift of 1.2 final
=====================

by %s on December 25, 2008

History does not happen, it is made. Warning: this message is long and
full of goodies.
Today, the history of the CakePHP grows stronger. December 25, 2008
will be remembered as one of the most important points in this
history. After exactly 2 yrs from the first development release, we
can happily say we have the most stable and powerful web framework
available. Please enjoy our big present to you, CakePHP 1.2 stable
[1]. For this release, we have removed the test files from the build,
and created a tag in SVN.

Through the last two years, we have been blessed by a dedicated,
talented, and opinionated community[2]. We have shared
disagreements[3] and triumphs. We have won popularity contests[4] and
been hated on. We have seen CakePHP grow into a truly international
community[5]. All of these events have generated an immense amount of
passion for CakePHP.

No one is more passionate about CakePHP than the developers[6] who
close tickets and fix bugs. We started out two years ago with a small
team that dedicated countless hours to implementing new features into
1.2 and maintaining 1.1 stable. This team ensured the integrity of
code and vision of the project. When we needed to grow, we found
members of the community who showed the same amount of dedication and
passion for CakePHP. And with the launch of CakeBOOK, on
`http://book.cakephp.org`_, we have seen the dedication and passion
further extend to all the contributors and translators[7] of the
fantastic documentation that makes learning about the power of CakePHP
a bit easier.

We have seen CakePHP adopted by large projects[8] and the growth of
dedicated service companies[9]. We have held a workshop[10] to spread
the knowledge and passion of CakePHP. And ultimately, we implemented a
huge list of features...

+ - Tests!
  +

    + - All classes are test-covered, with good code coverage
    + - Test suite now integrated into the framework
    + - test generation
    + - support for coverage analysis

+ - Command-line infrastructure
  +

    + - with more shell scripts and ability to write custom ones easily

+ - Plugin architecture
  +

    + - Plugins are now distributable as packaged collections of files
    + - Can be loaded from your main app with a dot syntax

+ - Internationalization and Localization support
  +

    + - i18n and l10n classes
    + - Support for unicode strings

+ - Auth component
  +

    + - automatically handles sessions for authenticated users
    + - ties into ACL for automatic denial of protected content or actions

+ - Email component
  +

    + - for generation of text and html email

+ - Security component
  +

    + - HTTP auth support, great for web services
    + - CSRF protection

+ - Cookie component
  +

    + - for secure cookie handling

+ - Custom model finders
  +

    + - simplified syntax

+ - powerful and extensible


+ - Join models
  +

    + - for modeling and accessing HABTM join tables

+ - Behaviors, new way to extend models
  +

    + - Supports "mixing in" new functionality

+ - Containable behavior
  +

    + - simplified query optimization

+ - Validation system extended
  +

    + - with new Validation class, lots of rules

+ - multiple rules and messages


+ - Database drivers
  +

    + - support for many more databases including DB2 and Oracle

+ - Caching
  +

    + - Adapter-driven caching, with support for APC/XCache/Memcache

+ - Set class,
  +

    + - for magical array hacking

+ - Socket and HttpSocket classes
  +

    + - for dealing with remote data and services

+ - Debugger class, for detailed introspection of errors
  +

    + - Get stack traces anywhere in your code
    + - Introspected help on errors, with context information

+ - Pagination
  +

    + - one of the first additions to the new version
    + - one of the simplest systems known

+ - Proper Routing
  +

    + - mapResources() method for exposing controllers via REST
    + - Reverse routing support
    + - Named arguments
    + - Magic variables for common regexes
    + - Support for file extensions with custom content type mappings

+ - View stuff
  +

    + - Separate templates for different content types
    + - automatic switching with RequestHandler
    + - New helper callbacks
    + - renderElement() replaced with element(), added built-in caching
      support

+ - FormHelper
  +

    + - All form-related methods moved here
    + - New dot notation
    + - Support for associations and multiple records
    + - Huge automation and introspection support; form creation requires
      very little code

+ - Configure and App classes
  +

    + - for configuration and loading

+ - Replaces defines and global functions

We hope that was a fun read. The changes since 1.1 have been dramatic,
but to us this was the minimum set of features needed to a truly
powerful framework and realize our vision for maintainability,
flexibility, and extensibility.

Almost as dramatic as the feature set, was the growth of the community
over this time, especially with its adoption of testing. We are proud
of the fact that Cake is one of the most test covered frameworks. Test
coverage allows us to fix more bugs and produce the most stable
framework available. We believe that a feature is not truly a feature
if there is even one known bug. With that in mind, each release comes
with the expectation that no bugs are known at the time.

Many of you may remember the first release of 1.2. Back on Dec 25,
2006 we released at revision 4206. Many features had not been
implemented or finalized, but we had a taste of what was to come. With
this release at [7958], we have come a long way. But possibly the most
exciting aspect of being where we are on Dec 25, 2008, is what we
expect to see in the future.

CakePHP helps build amazingly powerful applications. We have a running
list of examples[11]. Many of these applications were built with
earlier versions of 1.2. With the release of CakePHP 1.2 stable, we
expect these applications to enjoy a long history, just like the
CakePHP project itself.

If you have made it this far, we would like to pass on a few extra
goodies we have been working on. First is the all new Cake 1.2
cheatsheet. The old CakeSheet has proved to be a simple, quick
reference to some of the power of CakePHP. This new version is the
start of several more to come. Second, the gorgeous DebugKit plugin.
This plugin helps you develop your application faster by providing
quick, easy access to a lot of valuable debugging information.
DebugKit also provides and excellent example of how you can build
plugins to extend the functionality of you application. Finally, for
all the TextMate users out there we have an updated CakePHP TextMate
bundle. Joel Perras has put in a great amount of work and coding
CakePHP in TextMate just got a lot easier thanks to him. For all these
great resources and more, head on over to the downloads[12] page.

We hope you enjoy the big present and the few goodies. Have a great
holiday season.
- Gwoo, Nate, PhpNut and the rest of the CakePHP team

[1] `http://cakeforge.org/frs/?group_id=23_id=433`_
[2] `http://groups.google.com/group/cake-php/`_,
`http://www.ohloh.net/p/cakephp`_
[3] `http://ajbrown.org/blog/2008/12/22/four-reasons-to-hate-
cakephp.html`_
[4] `http://php-
mag.net/magphpde/magphpde_news/psecom,id,26752,nodeid,5.html`_,
`http://www.brownphp.com/2008/12/popular-php-frameworks-whats-your-
fav/`_
[5] `http://archive.fosdem.org/2008/`_,
`http://conference.cakephp.jp/`_, `http://cakefest.org/`_
[6] `https://trac.cakephp.org/wiki/Contributors`_
[7] `http://book.cakephp.org/stats`_
[8] `https://addons.mozilla.org/`_, `http://www.livemocha.com/`_,
`http://www.zeadoo.com/`_
[9] `http://cakedc.com`_, `http://debuggable.com`_,
`http://sanisoft.com`_
[10] `http://debuggable.com/posts/workshop-day-2:48c51df7-4fd4-4906
-8b1f-6ed64834cda3`_
[11] `http://book.cakephp.org/view/510/Sites-in-the-wild`_
[12] `http://cakephp.org/downloads`_



.. _http://www.brownphp.com/2008/12/popular-php-frameworks-whats-your-fav/: http://www.brownphp.com/2008/12/popular-php-frameworks-whats-your-fav/
.. _http://cakedc.com: http://cakedc.com/
.. _http://conference.cakephp.jp/: http://conference.cakephp.jp/
.. _http://php-mag.net/magphpde/magphpde_news/psecom,id,26752,nodeid,5.html: http://php-mag.net/magphpde/magphpde_news/psecom,id,26752,nodeid,5.html
.. _http://www.ohloh.net/p/cakephp: http://www.ohloh.net/p/cakephp
.. _http://debuggable.com: http://debuggable.com
.. _http://book.cakephp.org: http://book.cakephp.org/
.. _http://debuggable.com/posts/workshop-day-2:48c51df7-4fd4-4906-8b1f-6ed64834cda3: http://debuggable.com/posts/workshop-day-2:48c51df7-4fd4-4906-8b1f-6ed64834cda3
.. _http://www.livemocha.com/: http://www.livemocha.com/
.. __id=433: http://cakeforge.org/frs/?group_id=23&release_id=433
.. _http://cakephp.org/downloads: http://cakephp.org/downloads
.. _http://book.cakephp.org/stats: http://book.cakephp.org/stats
.. _http://groups.google.com/group/cake-php/: http://groups.google.com/group/cake-php/
.. _http://ajbrown.org/blog/2008/12/22/four-reasons-to-hate-cakephp.html: http://ajbrown.org/blog/2008/12/22/four-reasons-to-hate-cakephp.html
.. _https://trac.cakephp.org/wiki/Contributors: https://trac.cakephp.org/wiki/Contributors
.. _http://book.cakephp.org/view/510/Sites-in-the-wild: http://book.cakephp.org/view/510/Sites-in-the-wild
.. _http://www.zeadoo.com/: http://www.zeadoo.com/
.. _http://cakefest.org/: http://cakefest.org/
.. _https://addons.mozilla.org/: https://addons.mozilla.org/
.. _http://archive.fosdem.org/2008/: http://archive.fosdem.org/2008/
.. _http://sanisoft.com: http://sanisoft.com
.. meta::
    :title: The Gift of 1.2 final 
    :description: CakePHP Article related to release,spam comments,News
    :keywords: release,spam comments,News
    :copyright: Copyright 2008 
    :category: news

