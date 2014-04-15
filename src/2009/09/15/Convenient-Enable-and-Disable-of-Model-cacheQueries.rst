Convenient Enable and Disable of Model cacheQueries
===================================================

by dennis.hennen on September 15, 2009

Often we need to disable Cake's query caching for a single find
statement or a sequence of related finds. This behavior provides a way
to do that quickly.
Cake provides a convenient way to automatically cache the results of a
query. The mechanism is simple, but effective.

This is briefly documented in
`http://book.cakephp.org/view/445/cacheQueries`_ but I'll explain in a
little more detail below.

If Model->cacheQueries == true, Cake will store the result of queries
in memory. If it later sees an identical query, the result will be
pulled from memory instead of hitting the database. This is only
cached for the duration of a single page request. However, if a record
is updated, the cache is not cleared. This is what gets most people
unfamiliar with the cache.

Consider the following simplistic example model. We want to look up a
record by name and return that record. If the name doesn't exist,
we'll create a new one and return that. But normally we want queries
against the model cached, so we set cacheQueries = true by default.


Model Class:
````````````

::

    <?php 
    class MyModel extends AppModel {
    
        var $cacheQueries = true;
    
        function lookupName($name) {
            $record = $this->find('first', array(
                'conditions' => array('name' => $name)
            ));
            if (!$record) {
                $this->create();
                $this->save(array('name' => $name));
                $record = $this->find('first', array(
                    'conditions' => array('name' => $name)
                ));
            }
            return $record;
        }
    }
    ?>

However, since we are caching queries, the first find query is cached
and even if the record is successfully created, it will return false
from the cache.

To avoid this one would normally write code such as the following:


Model Class:
````````````

::

    <?php 
    $cacheQueries = $this->cacheQueries;
    $this->cacheQueries = false;
    $record = $this->find('first', ...);
    $this->cacheQueries = $cacheQueries;
    ?>

However, this code is ugly and cumbersome. Our first step is to create
a behavior to manipulate the model's cacheQueries, while keeping track
of previous states in a stack. In our behavior we use the following
functions:


Model Class:
````````````

::

    <?php 
    function setCacheQueries(&$Model, $cacheQueries) {
        $this->__cacheStack[$Model->name][] = $Model->cacheQueries;
        $Model->cacheQueries = $cacheQueries;
    }
    
    function resetCacheQueries(&$Model) {
        $Model->cacheQueries = array_pop($this->__cacheStack[$Model->name]);
    }
    ?>

So our example code would now become:


Model Class:
````````````

::

    <?php 
    $this->setCacheQueries(false);
    $response = $this->find('first', ...);
    $this->resetCacheQueries(false);
    ?>


This is much more concise and understandable, but we can do better by
adding a cacheQueries option to our beforeFind() callback and reset to
the last value in our afterFind() callback.


Model Class:
````````````

::

    <?php 
    function beforeFind(&$Model, $query) {
        $cacheQueries = $Model->cacheQueries;
        if (isset($query['cacheQueries'])) {
            $cacheQueries = $query['cacheQueries'];
        }
        $Model->setCacheQueries($cacheQueries);
        unset($query['cacheQueries']);
        return $query;
    }
    
    public function afterFind(&$Model, $results, $primary) {
        $Model->resetCacheQueries();
    }
    ?>

Now our example code becomes:


Model Class:
````````````

::

    <?php 
    $result = $this->find('first', array('cacheQueries' => false, 'conditions' => ...));
    ?>

A plugin providing this functionality is available
`http://github.com/dsh/cache_queries`_. To use, place the plugin in
your plugins directory and add 'CacheQueries.CacheQueries' to your
Model's actsAs fied.

Our final code model would like like this:


Model Class:
````````````

::

    <?php 
    class MyModel extends AppModel {
    
        var $cacheQueries = true;
        var $actsAs = array('CacheQueries.CacheQueries');
    
        function lookupName($name) {
            $record = $this->find('first', array(
                'cacheQueries' => false,
                'conditions' => array('name' => $name)
            ));
            if (!$record) {
                $this->create();
                $this->save(array('name' => $name));
                $record = $this->find('first', array(
                    'cacheQueries' => false,
                    'conditions' => array('name' => $name)
                ));
            }
            return $record;
        }
    }
    ?>

We also have the option of using setCacheQueries() and
resetCacheQueries() at the start and of the method.

Hopefully you will find this behavior will make your code a little
cleaner and a little easier to avoid this common mistake.


.. _http://github.com/dsh/cache_queries: http://github.com/dsh/cache_queries
.. _http://book.cakephp.org/view/445/cacheQueries: http://book.cakephp.org/view/445/cacheQueries

.. author:: dennis.hennen
.. categories:: articles, behaviors
.. tags:: model,behavior,cache,query cache,cachequeries,Behaviors

