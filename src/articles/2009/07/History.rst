History
=======

by %s on July 14, 2009

For a recent project I needed Wikipedia style history for all of my
content. Therefore all major content models (articles, pages, forum
posts etc) aggregate a base 'content' model. I wrote a behaviour to
attach to this content model to maintain a complete history. As yet I
have not written any of the rollback functions to allow one to give a
date/revision to roll back to. There is also no way to list history.
When I write these I will add them to the article. [b]Note:[/b] This
uses Unix commands, and so is not for Windows machines. It was
important for my project to retain Unix diff/patch friendly history -
and I found just executing shell commands on written files to be far
simpler than a bespoke PHP function (the options out there are weak
and I needed something fast).
You use the behaviour by stating which 'field' you wish to keep track
of, which model and field in that model you are storing your history
in ('historyModel' and 'historyField' respectively), which
'foreignKey' links the two and any other 'mappedValues' you wish to be
mapped between the two tables (below user_id is mapped across so that
diffs can be linked to a user where ContentHistory hasone User - at
the moment, fields are identically named across both tables).

The model you are tracking the history of must have a hasmany
relationship to its content history on the foreignKey indicated in the
behaviour settings (if anyone knows how I can pull this key out of the
model descriptions automagically, let me know).

models/content.php

::

    <?php
    class Content extends AppModel {
    
    	var $name = 'Content';
    	var $actsAs = array('History' => 
    	    array(
    	        'field' => 'body',
    	        'historyModel' => 'ContentHistory',
    	        'historyField' => 'changes',
    	        'foreignKey' => 'content_id',
    	        'mappedValues' => array('Content' => 'user_id')
    	    )
    	);
    
    	var $hasMany = array(
    		'ContentHistory' => array(
    			'className' => 'ContentHistory',
    			'foreignKey' => 'content_id',
    			'dependent' => false
    		)
    	);
    ?>

The code for the behaviour:

models/behaviors/history.php

::

    <?php
    class HistoryBehavior extends ModelBehavior {
        
         function setup(&$model, $settings) {
              if (!isset($this->settings[$model->alias])) {
                   $this->settings[$model->alias] = array(
                        // any default settings; none yet
                   );
              }
              $this->settings[$model->alias] = array_merge(
                   $this->settings[$model->alias], (array)$settings);
        }
        
        function beforeSave(&$model) {
            $field = $this->settings[$model->alias]['field'];
            $historyModel = $this->settings[$model->alias]['historyModel'];
            $historyField = $this->settings[$model->alias]['historyField'];
            $foreignKey = $this->settings[$model->alias]['foreignKey'];
            if (!empty($model->id)) {
               $old = $model->find('first');
               $history = $this->_diff($old[$model->alias][$field], $model->data[$model->alias][$field]);
                if (!empty($history)) {
                    $model->data[$historyModel][$historyField] = $history;
                    $model->data[$historyModel][$foreignKey] = $model->id;
                    foreach ($this->settings[$model->alias]['mappedValues'] as $mapModel => $mapField) {
                        $model->data[$historyModel][$mapField] = $model->data[$mapModel][$mapField];
                    }
                    $model->$historyModel->save($model->data);
                }
            }
            return true;
        }
    	
        /**
         * Currently a reliance on unix commands History and Patch, not sure how long the stream can be 
         * before PHP gives up but I am sure it is ample for some quite serious bits of content.
         */
        function _diff($old, $new) {
    
            $hash = md5(mt_rand(1,1000000));
    
            $oldHandle = fopen($oldFile = TMP . "$hash.old","w");
            $newHandle = fopen($newFile = TMP . "$hash.new","w");
    
            fwrite($oldHandle, $old);
            fwrite($newHandle, $new);
    
            fclose($oldHandle);
            fclose($newHandle);
    
            $diff = shell_exec("diff $newFile $oldFile");
    
            unlink($oldFile);
            unlink($newFile);
    
            return $diff;
    
        }
    
        function _patch($diff, $new) {
    
            $hash = md5(mt_rand(1,1000000));
    
            $diffHandle = fopen($diffFile = TMP . "$hash.diff","w");
            $newHandle = fopen($newFile = TMP . "$hash.new","w");
    
            fwrite($diffHandle ,$res);
            fwrite($newHandle, $new);
    
            fclose($diffHandle);
            fclose($newHandle);
    
            shell_exec("patch $newFile $diffFile");
    
            $patched = file_get_contents($newFile);
    
            unlink($diffFile);
            unlink($newFile);
    
            return $patched;
    
        }
    }
    ?>

Like I said, this is a first iteration - so don't expect it to be
anywhere near perfect. Also, if anyone knows of Unix diff/patch
compatible PHP functions that aren't ridiculous let me know.

.. meta::
    :title: History
    :description: CakePHP Article related to history,diff,patch,Behaviors
    :keywords: history,diff,patch,Behaviors
    :copyright: Copyright 2009 
    :category: behaviors

