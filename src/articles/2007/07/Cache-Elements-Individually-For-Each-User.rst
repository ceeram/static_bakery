Cache Elements Individually For Each User.
==========================================

by ketan on July 19, 2007

Caching elements in general has been discussed before on bakery and
this article takes caching of an element to a higher level. This
article explains how to cache elements individually for each user.
An article on how to cache an element has been published earlier on
bakery at `http://bakery.cakephp.org/articles/view/optimizing-your-
cakephp-elements-and-views-with-caching`_. This article is an
enhancement to the above mentioned article. So make sure you are
through with the other one before getting on this.

There are many situations where you would want to cache an element
individually for each user. Cases like having separate tagClouds,
todolists, etc for each user. Remember, if you are calling
'requestAction' in your elements then it may be a good idea to cache
the element as sometimes 'requestAction' could get expensive with lots
of them.

For this article, I will assume that we are having a todo element as
follows:

::

    
    // Todo Element
    
    $todoLists = $this->requestAction('todoLists/getList');
    
    
    echo '<h2>ToDo List</h2>';
    echo '<ul>';
    
    foreach($todoLists as $todoList)
    {
       echo '<li>'.$todoList['name'].'</li>';
    }
    echo '</ul>';

And in one of my views I call this element as follows:

::

    
    // Generic way to cache element
    $this->element('todo', array('cache'=>'+1 day'));

If I call element using the above call, what happens is that Cake will
cache the element 'todo' as app/tmp/cache/views/element__todo for 1
day. But this cache will be same for all users as the name of the
element and cache file generated is the same.

First you must understand how the cache file for element is named. It
is as 'element_{plugin}_{name of element}'. In our case, as we didnot
specify the plugin, it was considered null, and you see two
consecutive 'underscores' in the name of the cache file
(element__todo).

Taking the naming convention to our advantage, we trick Cake to do our
job. Take a look at following code:

::

    
    // Individually cache the element for each user.
    $userId = 3; // For example
    $this->element('todo', array('cache'=>'+1 day', 'plugin'=>$userId));

Woila, you are done. You just tricked Cake to create a separate cache
file for each user. By specifying the plugin to be userId, Cake
creates a separate cache file with the name 'element_3_todo'.

So now for each user, cake will create a separate cache file. Hang on,
don't you want to know how to delete this cache file if the user
updated his todo list?? In your TodoList Model, update the afterSave
method as follows

::

    
    function afterSave()
    {
      $userId = $this->data['TodoList']['user_id'];
      $this->clearCache('element_'.$userId.'_todo', 'views', '');
      parent::afterSave();
    }

That's it, whenever user updates the todo list, cake will now
automatically clear the cache for that user.

This is my first article and I would really want people to post some
constructive feedback and comments. Let me know how you feel about
this article.

Ketan Patel


.. _http://bakery.cakephp.org/articles/view/optimizing-your-cakephp-elements-and-views-with-caching: http://bakery.cakephp.org/articles/view/optimizing-your-cakephp-elements-and-views-with-caching
.. meta::
    :title: Cache Elements Individually For Each User.
    :description: CakePHP Article related to elements,cache,todo,Tutorials
    :keywords: elements,cache,todo,Tutorials
    :copyright: Copyright 2007 ketan
    :category: tutorials

