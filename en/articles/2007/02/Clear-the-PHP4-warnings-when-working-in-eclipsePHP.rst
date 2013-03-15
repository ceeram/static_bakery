

Clear the PHP4 warnings when working in eclipsePHP
==================================================

by %s on February 16, 2007

A series of search-and-replace that you can run on the latest alpha
release to clear the majority of EclipsePHP's warnings, without
(hopefully!) breaking anything.

CakePHP - a great framework, but sometimes surprising to those who are
used to programming with PHP 5. EclipsePHP spits out pages and pages
of warnings when compiling towards a PHP 5 base, scaring new CakePHP
converts.

Run the following replacements in eclipse across the entire workspace
as case-sensitive matches. This follows the
`https://trac.cakephp.org/wiki/Developement/CodingStandards`_


#. &$ -> $
#. var $__ -> private $__
#. var $_ -> protected $_
#. var $ -> public $
#. function -> public function
#. public function _ -> protected function _
#. protected function __ -> private function __



.. _https://trac.cakephp.org/wiki/Developement/CodingStandards: https://trac.cakephp.org/wiki/Developement/CodingStandards
.. meta::
    :title: Clear the PHP4 warnings when working in eclipsePHP
    :description: CakePHP Article related to php5,eclipsephp,alpha,ide,General Interest
    :keywords: php5,eclipsephp,alpha,ide,General Interest
    :copyright: Copyright 2007 
    :category: general_interest

