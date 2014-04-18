PMJ publishes some benchmarks
=============================

Anytime new information comes out about CakePHP, we do our best to
respond and share our knowledge for the betterment of the community.
Recently some new benchmarks were created to help guide us on our way.
Thanks to Paul M. Jones for the effort to put them together.
Hello loyal bakers,

Yesterday, Paul M. Jones published some interesting benchmarks using
apache ab to test the performance of a simple hello word echo
statement in several of the most recent frameworks [1]. Paul explains
how the test was run and compares relative speed. Paul is very
respectful of each framework and published the benchmarks as a guide
more than anything else. As it turns out we have some more work to do.
This is a great opportunity for us to work with Paul to speed up the
dispatcher to achieve some better results in his scenario.

As most of you know and Paul admits, CakePHP is not just about simple
hello world examples. CakePHP is mainly concerned with data-driven
rapid development. We feel Cake is a strong performer as the
application is fully realized partly because of some of the things
done in the dispatcher, partly because we think our model layer is
pretty quick. What we do not want to do is fine tune CakePHP for one
specific benchmark scenario. We need to look at the application
development as a whole and try to get the fastest, most secure
framework possible. As is often said, development and maintenance
costs are much higher than hardware costs. We plan to stay focused on
making development faster, but it can't hurt to speed up the
processing at the same time. We also want to provide more tools to
compliment the full page caching, persistent models, and other things
used to optimize applications.

We will continue to work with Paul on improving Cake's speed in his
scnario and we also want to produce some benchmarks of a more complete
application. We remain focused on the three things we believe make
frameworks great...security, speed, and simplicity. We are confident
that with a little more work, CakePHP will come out on top. As always,
if you would like to help out, feel free to send an email or talk to
one of us on IRC.

Bake on.

[1] `http://paul-m-jones.com/blog/?p=236`_

.. _http://paul-m-jones.com/blog/?p=236: http://paul-m-jones.com/blog/?p=236

.. author:: gwoo
.. categories:: news
.. tags:: benchmark,News

