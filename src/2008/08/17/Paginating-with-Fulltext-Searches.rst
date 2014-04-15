Paginating with Fulltext Searches
=================================

by britg on August 17, 2008

This a short tutorial on how to implement pagination with MYSQL full
text searches. Requirements to use this implementation are CakePHP 1.2
and Mysql 3.23.

Let's say you want to do a full-text search on a Mysql (MyISAM) table
called `listings` with a fulltext index on the fields `title` and
`description`. Your search term comes from a search form that an end-
user fills out. You also want to paginate the search results with the
incredibly powerful and easy-to-use CakePHP paginator.


Controller Class:
`````````````````

::

    <?php 
    # query comes from GET request parameter 'q'
    $input = $this->params['url']['q'];
    
    # sanitize the query
    App::import('Sanitize');
    $q = Sanitize::escape($input);
    
    # we are searching a table called 'listings'
    $options['conditions'] = array(
       "MATCH(Listing.title,Listing.description) 
              AGAINST('$q' IN BOOLEAN MODE)"
    );
    
    $this->set(array('results' => $this->paginate('Listing', $options)));
    ?>


Voila! You can now handle the $results in your view just as you would
normally handle a paginated variable.



.. author:: britg
.. categories:: articles, tutorials
.. tags:: ,Tutorials

