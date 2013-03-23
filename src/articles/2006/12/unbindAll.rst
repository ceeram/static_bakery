unbindAll
=========

by %s on December 10, 2006

Here is a simple way to unbind all models. Just put this function into
your app_model.php


Model Class:
````````````

::

    <?php 
      function unbindModelAll()
      {
        $unbind = array();
        foreach ($this->belongsTo as $model=>$info)
        {
          $unbind['belongsTo'][] = $model;
        }
        foreach ($this->hasOne as $model=>$info)
        {
          $unbind['hasOne'][] = $model;
        }
        foreach ($this->hasMany as $model=>$info)
        {
          $unbind['hasMany'][] = $model;
        }
        foreach ($this->hasAndBelongsToMany as $model=>$info)
        {
          $unbind['hasAndBelongsToMany'][] = $model;
        }
        parent::unbindModel($unbind);
      }
    ?>


.. meta::
    :title: unbindAll
    :description: CakePHP Article related to unbindAll,unbind,Snippets
    :keywords: unbindAll,unbind,Snippets
    :copyright: Copyright 2006 
    :category: snippets

