

Counter Cache behavior for HABTM relations
==========================================

by %s on May 29, 2009

CounterCache is a cool feature introduced in CakePHP 1.2, but I found
that it doesn't work for HABTM (hasAndBelongsToMany) relations and
AFAIK only planned in 1.3 (See ticket #5214). Using this bakery
article: [url]http://bakery.cakephp.org/articles/view/countercache-or-
counter_cache-behavior[/url]), I wrote my own CounterCache behavior
for HABTM relations. This is my first article for Bakery and English
isn't my mother tongue, so please be indulgent to my mistakes.
For those who is new to CakePHP or doesn't know about what
CounterCache is, please read this Cookbook chapter first:
`http://book.cakephp.org/view/816/counterCache-Cache-your-count`_.

I will explain how to use my CounterCacheHabtmBehavior on a simple
example. Imagine we want to build a blog with tags assigned to each
article. There can be many tags assigned to many articles and each tag
is unique. The database definition will look as follows:

::

    
    CREATE TABLE articles (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, title VARCHAR(100), body TEXT, tag_count INT DEFAULT 0);
    CREATE TABLE tags (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, name VARCHAR(20), article_count INT DEFAULT 0);
    CREATE TABLE articles_tags (article_id INT, tag_id INT);

As you can see, articles and tags are linked via HABTM association,
and articles_tags is the join table. Notice also that article has the
column called tag_count and tags has article_count , those we will
count how many tags each article has and to how many articles each tag
belongs.

On this step you should bake your blog application, but I assume that
you've done it already.

Download the code below and save as
models/behaviors/counter_cache_habtm.php


Model Class:
````````````

::

    <?php 
    /**
     * CounterCacheHabtmBehavior - add counter cache support for HABTM relations
     *
     * Based on CounterCacheBehavior by Derick Ng aka dericknwq
     *
     * @see http://bakery.cakephp.org/articles/view/counter-cache-behavior-for-habtm-relations
     * @author Yuri Pimenov aka Danaki (http://blog.gate.lv)
     * @version 2009-05-28
     */
    class CounterCacheHabtmBehavior extends ModelBehavior {
        /**
         * Array to store intermediate results
         *
         * @var array
         * @access public
         */
        var $foreignTableIDs = array();
    
        /**
         * For each HABTM association using given id, find related foreign ids
         * that represent in the join table. Save results to $foreignTableIDs array.
         *
         * @param mixed $model
         * @access private
         * @return void
         */
        function findForeignIDs(&$model) {
            foreach ($model->hasAndBelongsToMany as $assocKey => $assocData) {
                $assocModel =& $model->{$assocData['className']};
                $field = Inflector::underscore($model->name).'_count';
    
                if ($assocModel->hasField($field)) {       
                    $joinModel =& $model->{$assocData['with']};
                   
                    $joinIDs = $joinModel->find('all', array(
                        'fields' => array($assocData['associationForeignKey']),
                        'conditions' => array($assocData['foreignKey'] => $model->id),
                        'group' => $assocData['associationForeignKey']
                    ));
    
                    $this->foreignTableIDs[$assocData['className']] = array_keys(
                        Set::combine($joinIDs, '{n}.'.$assocData['with'].'.'.$assocData['associationForeignKey'])
                    );
                }
            }
        }
    
        /**
         * For each HABTM association, using ids from $foreignTableIDs array find
         * counts and update counter cache field in the associated table
         *
         * @param mixed $model
         * @access private
         * @return void
         */
        function updateCounters(&$model) {
            foreach ($model->hasAndBelongsToMany as $assocKey => $assocData)
                if (isset($this->foreignTableIDs[$assocData['className']])
                    && $this->foreignTableIDs[$assocData['className']]) {
    
                    $assocModel =& $model->{$assocData['className']};
                    $joinModel =& $model->{$assocData['with']};
    
                    $field = Inflector::underscore($model->name).'_count';
    
                    if ($assocModel->hasField($field)) {               
                        $saveArr = array();
       
                        // in case of delete $rawCounts array may be empty -- update associated model anyway
                        foreach ($this->foreignTableIDs[$assocData['className']] as $assocId)
                            $saveArr[$assocId] = array('id' => $assocId, $field => 0);
    
                        // if 'unique' set to false - update counter cache with the number of only unique pairs
                        $rawCounts = $joinModel->find('all', array(
                            'fields' => array(
                                $assocData['associationForeignKey'],
                                ($assocData['unique'] ? 'COUNT(*)' : 'COUNT(DISTINCT '.$assocData['associationForeignKey'].','.$assocData['foreignKey'].')')
                                .' AS count'),
                            'conditions' => array(
                                $assocData['associationForeignKey'] => $this->foreignTableIDs[$assocData['className']]
                            ),
                            'group' => $assocData['associationForeignKey']
                        ));
                                           
                        $counts = Set::combine($rawCounts, '{n}.'.$assocData['with'].'.'.$assocData['associationForeignKey'], '{n}.0.count');
       
                        // override $saveArr with count() data
                        foreach ($counts as $assocId => $count)
                            $saveArr[$assocId] = array('id' => $assocId, $field => $count);
           
                        $assocModel->saveAll($saveArr, array(
                            'validate' => false,
                            'fieldList' => array($field),
                            'callbacks' => false
                        ));
                    }
                }       
        }
    
        /**
         * On update fill $foreignTableIDs for each HABTM association from user form data
         *
         * @param mixed $model
         * @access public
         * @return boolean
         */   
        function beforeSave(&$model) { 
            if (! empty($model->id)) {
                // this is an update, we handle creates in afterSave(), this saves us some CPU cycles           
                $this->findForeignIDs($model);
    
                foreach ($model->hasAndBelongsToMany as $assocKey => $assocData)
                    if (isset($model->data[$assocData['className']])
                        && isset($model->data[$assocData['className']][$assocData['className']])
                        && is_array($model->data[$assocData['className']][$assocData['className']])) {
    
                        $this->foreignTableIDs[$assocData['className']] = Set::merge(
                            isset($this->foreignTableIDs[$assocData['className']]) ? $this->foreignTableIDs[$assocData['className']] : array(),
                            $model->data[$assocData['className']][$assocData['className']]
                        );
                    }
            }
    
            return true;       
        }
       
        /**
         * Update counter cache after all data saved
         *
         * @param mixed $model
         * @param boolean $created
         * @access public
         * @return void
         */       
        function afterSave(&$model, $created) {
            if ($created) {
                foreach ($model->hasAndBelongsToMany as $assocKey => $assocData) {
                    $assocModel =& $model->{$assocData['className']};
                    $field = Inflector::underscore($model->name).'_count';     
    
                    if ($assocModel->hasField($field))
                        $this->foreignTableIDs[$assocData['className']] = $model->data[$assocData['className']][$assocData['className']];
                }
            }
    
            $this->updateCounters($model);
           
            foreach ($model->hasAndBelongsToMany as $assocKey => $assocData) {
                $field = Inflector::underscore($assocKey).'_count';
               
                if ($model->hasField($field)) {
                    $joinModel =& $model->{$assocData['with']};
    
                    // if 'unique' set to false - update counter cache with the number of only unique pairs
                    $count = $joinModel->field(
                        ($assocData['unique'] ? 'COUNT(*)' : 'COUNT(DISTINCT '.$assocData['associationForeignKey'].')').' AS count',
                        array($assocData['foreignKey'] => $model->id)
                    );
    
                    $model->saveField($field, $count, array(
                        'validate' => false,
                        'callbacks' => false
                    ));
                }
            }
           
            $this->foreignTableIDs = array();
        }
    
        /**
         * Fill $foreignTableIDs array just before deletion
         *
         * @param mixed $model
         * @access public
         * @return boolean
         */   
        function beforeDelete(&$model) {
            $this->findForeignIDs($model);
    
            return true;
        }
    
        /**
         * Update counter cache after deletion
         *
         * @param mixed $model
         * @access public
         * @return void
         */     
        function afterDelete(&$model) {
            $this->updateCounters($model);
           
            $this->foreignTableIDs = array();       
        }
    }
    ?>

Unlike hasMany association, in HABTM both tables are linked to each
other and none of them are master or slave. So you have to add the
following line

::

        var $actsAs = array('CounterCacheHabtm');

to both article and tag models. Models must now look like:

Model Class:
````````````

::

    <?php 
    class Article extends AppModel {
        var $name = 'Article';
        var $actsAs = array('CounterCacheHabtm');
    
        var $hasAndBelongsToMany = array(
            'Tag' => array('className' => 'Tag',
                'joinTable' => 'articles_tags',
                'foreignKey' => 'article_id',
                'associationForeignKey' => 'tag_id',
                'unique' => true
            )
        );
    }
    ?>



Model Class:
````````````

::

    <?php 
    class Tag extends AppModel {
        var $name = 'Tag';
        var $actsAs = array('CounterCacheHabtm');
    
        var $hasAndBelongsToMany = array(
            'Article' => array('className' => 'Article',
                'joinTable' => 'articles_tags',
                'foreignKey' => 'tag_id',
                'associationForeignKey' => 'article_id',
                'unique' => true
            )
        );
    }
    ?>

That's all, so simple. Now you can create/update/delete articles and
tags and behavior will count related rows and update each model.

Important notice : if you want to have CounterCache enabled for only
one of the models, say you don't want to cache number of articles in
the tag model, just don't create the appropriate _count column (in our
example you can safely drop article_count in tags table) and the
behavior won't try to update it. But even in this case remember that
you must have $actsAs in both models in order the code to work
properly.

.. _http://book.cakephp.org/view/816/counterCache-Cache-your-count: http://book.cakephp.org/view/816/counterCache-Cache-your-count
.. meta::
    :title: Counter Cache behavior for HABTM relations
    :description: CakePHP Article related to countercache,HABTM,behavior,Behaviors
    :keywords: countercache,HABTM,behavior,Behaviors
    :copyright: Copyright 2009 
    :category: behaviors

