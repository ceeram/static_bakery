Advanced Search
===============

by bmuller on September 25, 2009

This project provides the easy ability to autogenerate a search form
that allows a user to search not only on the fields of a given object,
but on the fields of all related (and recursively related) objects.
For instance, say you have People in your database, with a many to
many relationship to Addresses, and each address has a State, and each
State has an abbreviation. This module produces a search form that
generates a list of fields that allows you to choose to search for all
people that have addresses that have a state that has an abbreviation
of MD. The search is performed for you, and the results displayed in a
meaningful manner.
Here are some simple examples. For the full documentation, as well as
the code and instructions for installation, see
`https://sneezy.cybergroup.com/trac/public/CakeAdvancedSearch`_

In your view (using "business" as the model we want to search for):

::

    
    <?php echo $searchForm->form("business"); ?>
    

or, with an optional URL parameter:

::

    
    <?php echo $searchForm->form("business", array("action" => "dosearch", "controller" => "pages")); ?>
    

This will create a search form with a number of parameter pull downs
and the ability to make complicated queries (containing less than /
greater than for dates / numbers, like/equals/doesn't equal for
strings, etc) as well as the ability to have multiple constraints
(search by multiple fields).

You can also create a view to display results.

::

    
    <?php
    
    if(count($results) == 0) {
    
      echo "<b>No results</b>";
    
    } else {
    
      // pass optional attributes in for the table element, th element, and td element
    
      $tableAttrs = array(
    
            'table' => array("cellspacing" => "0", "border" => "0", "style" => "width: 100%;", "class" => "styledtable"),
    
            'th' => array("scope" => "col")
    
            );
    
      // Using the ObjectList helper, display a table of business results 
    
      echo $objectList->makeTable("business", $results, $tableAttrs);
    
    }
    
    ?>
    




.. _https://sneezy.cybergroup.com/trac/public/CakeAdvancedSearch: https://sneezy.cybergroup.com/trac/public/CakeAdvancedSearch
.. meta::
    :title: Advanced Search 
    :description: CakePHP Article related to search,Components
    :keywords: search,Components
    :copyright: Copyright 2009 bmuller
    :category: components

