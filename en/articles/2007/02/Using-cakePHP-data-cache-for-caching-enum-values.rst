Using cakePHP data cache for caching enum values
================================================

by %s on February 25, 2007

This example will show you how you can cache the enum values that you
obtain from a database using cakePHP's data cache...
cakePHP has view caching built in which when used cleverly will really
speed up your applications.

However, this doesn't work with requestAction.

Let's consider this example:

1. You store all your categories in a database.

2. While displaying your views, you want to pull these categories from
the DB.

3. You don't want that every time you display a page, you should pull
the categories from DB. Since they are not going to change (till you
change them :) it's an unnecessary hit on the DB.

4. You want to load these categories at startup and then just keep
pulling the data from the cache rather than the DB.

There can be many ways of doing this, but one of them could be that
whenever the category data is first requested, we hit the DB and get
the list of categories, then cache them (in a file) and subsequent
requests would be served from reading the file (it could be stored in
the session too).

So, I decided to give this a shot. cakePHP already has a cache()
function that can be used for achieving something like this.

Here is what I came up with:

::

    
    function __getCategories()
    {
      $cache_name = "views".DS."categories-list.php";
      $cache_expires = '+24 hours';
    
      $cache_data = cache($cache_name, null, $cache_expires);
      if (empty($cache_data))
      {
        $list = array_merge(array('categories'=>'All Categories'),
                $this->Contest->getEnumValues("contest_category"));
        cache($cache_name, serialize($list), $cache_expires);
      } else {
        $list = unserialize($cache_data);
      }
      return $list;
    }

getEnumValues is something I got as a snippet from cakeforge.
`http://cakeforge.org/snippet/detail.php?type=snippet=112`_
So, that's it, you can now do the following in your controller:

::

    
      function test()
      {
        $categories = $this->__getCategories();
        debug($categories);
      }

-Mandy.

.. _=112: http://cakeforge.org/snippet/detail.php?type=snippet&id=112
.. meta::
    :title: Using cakePHP data cache for caching enum values
    :description: CakePHP Article related to enum,caching,Snippets
    :keywords: enum,caching,Snippets
    :copyright: Copyright 2007 
    :category: snippets

