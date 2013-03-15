Pagination Recall
=================

by %s on February 04, 2008

Does it drive you nuts when you edit an item on page 4 of a list, then
when you save you're dropped back to page 1 with the list back in the
default order? The PaginationRecall component automatically remembers
the page you were on and the sorting so you're returned to the same
spot on the list.
I’ve been using the built in pagination in CakePHP 1.2-beta a lot
lately and I must say it’s freaking awesome. My one oh so minor
grievance is that it doesn’t "remember" where you were if you navigate
off the page. For example many times I'll be paging through a sorted
list of records and want to edit a particular one. After I edit and
save I'm dropped back to the index with the default sorting on page 1.

Enter the PaginationRecall component. It will automatically retain
your pagination settings so that you can edit/delete/surf around and
return to the same spot on list.

Just include the component in your controller:

::

    var $components = array('PaginationRecall');



Component Class:
````````````````

::

    <?php 
    /*
     * Pagination Recall CakePHP Component
     * Copyright (c) 2008 Matt Curry
     * www.PseudoCoder.com
     *
     * @author      mattc <matt@pseudocoder.com>
     * @version     1.0
     * @license     MIT
     *
     */
    
    class PaginationRecallComponent extends Object {
      var $components = array('Session');
      var $Controller = null;
    
      function startup(&$controller) {
        $this->Controller = & $controller;
    
        $options = array_merge($this->Controller->params,
                               $this->Controller->params['url'],
                               $this->Controller->passedArgs
                              );
    
        $vars = array('page', 'sort', 'direction');
        $keys = array_keys($options);
        $count = count($keys);
        
        for ($i = 0; $i < $count; $i++) {
          if (!in_array($keys[$i], $vars)) {
            unset($options[$keys[$i]]);
          }
        }
        
        //save the options into the session
        if ($options) {
          if ($this->Session->check("Pagination.{$this->Controller->modelClass}.options")) {
            $options = array_merge($this->Session->read("Pagination.{$this->Controller->modelClass}.options"), $options);
          }
          
          $this->Session->write("Pagination.{$this->Controller->modelClass}.options", $options);
        }
    
        //recall previous options
        if ($this->Session->check("Pagination.{$this->Controller->modelClass}.options")) {
          $options = $this->Session->read("Pagination.{$this->Controller->modelClass}.options");
          $this->Controller->passedArgs = array_merge($this->Controller->passedArgs, $options);
        }
      }
    }
    ?>


.. meta::
    :title: Pagination Recall
    :description: CakePHP Article related to ,Components
    :keywords: ,Components
    :copyright: Copyright 2008 
    :category: components

