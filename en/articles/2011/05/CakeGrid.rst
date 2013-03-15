CakeGrid
========

by %s on May 02, 2011

CakeGrid is an easy way to easily generate tables from results. It
uses elements so changing the design is easy.

To get cakegrid, go to `https://github.com/rross0227/CakeGrid`_ to
clone it. Use issues if you find any bugs!

::

    $this->Grid->addColumn('Order Id', '/Order/id');
    $this->Grid->addColumn('Order Date', '/Order/created', array('type' => 'date'));
    $this->Grid->addColumn('Order Amount', '/Order/amount', array('type' => 'money'));
    
    $this->Grid->addAction('Edit', array('controller' => 'orders', 'action' => 'edit'), array('/Order/id'));
    echo $this->Grid->generate($results);

The README in the repo has everything you should need to know.


.. _https://github.com/rross0227/CakeGrid: https://github.com/rross0227/CakeGrid
.. meta::
    :title: CakeGrid
    :description: CakePHP Article related to tables,tabular,Helpers
    :keywords: tables,tabular,Helpers
    :copyright: Copyright 2011 
    :category: helpers

