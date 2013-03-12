

Performance comparision CakePHP and symfony
===========================================

by %s on January 14, 2009

We can see some benchmark. But that's only "hello world" or something
simple codes. I compared CakePHP and symfony in real application.
that's well known symfony sample "askeet!!".


comparision target
~~~~~~~~~~~~~~~~~~

+ "askeet!!" is a sample application for symfony1.0
+ the frontpage ported to CakePHP and share the database
+ the data was registered in symfony application.



test environment
~~~~~~~~~~~~~~~~

+ AMD Opteron1210(1800x2)
+ memory 4GB
+ FreeBSD7.0-STABLE
+ apache 2.2.9
+ PHP 5.2.6
+ MySQL 5.0.67



result
~~~~~~

+ CakePHP1.1
+ 3.27 request per second.
+ CakePHP1.2final
+ 2.26 request per second.
+ symfony
+ 2.11 request per second.
+ CakePHP1.2RC2
+ 1.92 request per second.



more detail and codes
~~~~~~~~~~~~~~~~~~~~~

+ `http://spreadsheets.google.com/pub?key=pMM1_aVbjL2GYdYNRh46odA=2`_
+ `http://coderepos.org/share/browser/websites/cakeet`_
+ `http://puyo2.upper.jp/cake/download/confirm/20080929_CakePHP_perfor
  mance(en).pdf`_



.. _http://puyo2.upper.jp/cake/download/confirm/20080929_CakePHP_performance(en).pdf: http://puyo2.upper.jp/cake/download/confirm/20080929_CakePHP_performance(en).pdf
.. _http://coderepos.org/share/browser/websites/cakeet: http://coderepos.org/share/browser/websites/cakeet
.. _=2: http://spreadsheets.google.com/pub?key=pMM1_aVbjL2GYdYNRh46odA&gid=2
.. meta::
    :title: Performance comparision CakePHP and symfony
    :description: CakePHP Article related to use jobeet and sf lu,Case Studies
    :keywords: use jobeet and sf lu,Case Studies
    :copyright: Copyright 2009 
    :category: case_studies

