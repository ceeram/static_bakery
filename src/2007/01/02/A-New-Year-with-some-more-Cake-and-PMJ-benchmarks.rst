A New Year with some more Cake and PMJ benchmarks
=================================================

We are hoping 2007 will be another great year for CakePHP. We started
things off right with the release of version 1.2. Many feature
improvements keep us moving forward in our quest to make developing
with Cake even easier.
The first news of the New Year relates to the benchmarks by Paul M.
Jones. You may recall in a previous ariticle that Paul published some
results for a basic responsiveness test on CakePHP 1.1.10. We were not
happy with the numbers, so we spent some time to optimize the
dispatcher and routing, which is now much more efficient with the lazy
loading of models.

CakePHP 1.1.11 was the first version to see the changes and it made
nice sized improvements in speed. Check out Paul's new benchmarks.
`http://paul-m-jones.com/blog/?p=238`_. You can see that Cake performs
pretty well considering how much it does for the developer out of the
box. Cake does not just dispatch on the response, it loads common
classes like HtmlHelper and Session. The Session class takes the
current session and processes it for easy use and security.

In any case, we are very pleased with how well Cake responds. We thank
Paul for setting up the comparison. We spend time profiling Cake and
optimizing it, but having something to compare against is very useful.
Everyone should respect that these comparisons in an objective
environment are not easy to create and they stand as just one factor
in the overall aspect of using a framework. We think Cake does the
most for developers, making code easier to create, extend and maintain
and along with some pretty decent speed, things are looking good for
2007.

Bake on.

.. _http://paul-m-jones.com/blog/?p=238: http://paul-m-jones.com/blog/?p=238

.. author:: gwoo
.. categories:: news
.. tags:: benchmark,News

