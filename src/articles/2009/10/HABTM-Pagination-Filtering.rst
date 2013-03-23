HABTM Pagination & Filtering
============================

by %s on October 21, 2009

HABTM relationships can be hard to work with as the builtin methods in
Cake don't work like in other types of associations. This often leads
to ugly hacks and non-cakeish tricks. Here, in the first of a series
of articles about HABTM in Cake, I'll show you how to achieve
pagination [with optional conditons] while keeping your controllers
thin and clean.
Imagine the following schema:
users ---HABTM---> groups <---HABTM--- projects
A user can belong to more than one group and some projects can be
shared between some groups of users.

After baking, your Groups Controller will look like this:

Controller Class:
`````````````````

::

    <?php 
    class GroupsController extends AppController
       var $name = 'Groups';
       var $helpers = array('Html', 'Form'); 
    
       function index() {
          $this->Group->recursive = 0;
          $this->set('groups', $this->paginate());
       }
    ?>

This is not what we want! Every user can see a full list of groups he
doesn't belong to. However if we try to add conditions to paginate, as
in hasOne or hasMany associations, it won't work.

Some people (me too) have tried to used Containable behaviour (with
conditions) and model binding to solve this, but the result is often a
mess, and hard to maintain/extend, like this:


Model Class:
````````````

::

    <?php 
    class GroupModel extends AppModel
       var $recursive = -1;
       var $actsAs = array('Containable');
    ?>



Controller Class:
`````````````````

::

    <?php 
    class GroupsController extends AppController
       var $name = 'Groups';
       var $helpers = array('Html', 'Form'); 
    
       function index() {
          $pagination['Group']['contain'][] = 'GroupsUser';
          $pagination['Group']['conditions'][] = array('GroupsUser.user_id' => $this->Auth->user('id'));
          $this->Group->bindModel(array('hasOne' => array('GroupsUser' => array('foreignKey' => 'group_id'))), false);
          $this->paginate = $pagination;
          $groups = $this->paginate();
          $this->set('groups', $groups);
       }
    ?>

Not very beautiful and cumbersome if you have many actions like this.
Also, I think this code shouldn't be here. Remember:
Fat Models, Skinny Controllers
TODO...

.. meta::
    :title: HABTM Pagination & Filtering
    :description: CakePHP Article related to habtm pagination con,Tutorials
    :keywords: habtm pagination con,Tutorials
    :copyright: Copyright 2009 
    :category: tutorials

