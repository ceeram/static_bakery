CakePHP Big Data Behavior
=========================

by %s on February 18, 2012

An easy way to efficiently insert, update, and work with very large
amounts of data using CakePHP and MySQL. The most recent version of
the code can be downloaded `here`_.


Background
----------

> <p> My company uses CakePHP for most of our applications. However,
we were running into efficiency issues when working with large amounts
of data. It’s not uncommon for us to insert (or update) hundreds of
thousands of rows (now millions) with a single process. Additionally,
we needed an efficient way to work with those hundreds of thousands of
pieces of data. < p>
So, after some investigation I narrowed our efficiency problem to
CakePHP sending data to the database, one row at a time. This works
great out of the box, but will really slow things down once large
amounts of data come into play. I remedied this by creating this
behavior that allows a model to have a “bundle” of objects. This
bundle is stored in memory. Upon saving the bundle, all of the model
objects are inserted into the database as a bulk insert, 100,000 items
per insert by default.

Additionally, this behavior allows CakePHP find results to be returned
in the form of a hashed array. The user can specify a ‘key’, which
will serve as the key of the returned associative array.

> <h2>Download< h2>
> The most recent version of the code can be downloaded here - <a
href="https: /github.com/jarriett/CakePHP_Big_Data">CakePHP BigData
><br >

Requirements
------------

> <ul><li> CakePHP 1.3 < li>
