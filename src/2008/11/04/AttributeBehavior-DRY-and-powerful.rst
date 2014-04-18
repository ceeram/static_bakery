AttributeBehavior - DRY and powerful
====================================

Another piece taken inspiration from a somewhat similar AliasBehavior,
but most of the time u to do more than aliasing. What about a simple
callback mechanism to build aliases, virtual or derivative attributes
the simple way, Results is pretty neat. :)


Background
----------
There are some cases you want to modified the result returned by model
find methods, We have been told that we should overwrite the
afterFind() do any post-processing to support aliasing of attributes,
derivative values or simply doing some filtering.

I found my afterFilter on some of my bigger application endup to be
very messy and possibly break other find results if you are not
careful. And sadly after you wrested to get correct behavior to alter
$results array for both direct model find and associated find. All the
logic stuck there and not reusable.

Here is a familiar pattern of implementing some post-processing to
implement virtual attribute in Modle::afterFind correctly.

Model Class:
````````````

::

    <?php 
    class Person extends AppModel {
      function afterFind($results, $primary) {
        # Primary find 
        if ($primary && $results[0][$this->alias]) {
          foreach ($results as $i => $result) {
            # build another property
            $full_name = "{$result[$this->alias]['first_name']} {$result[$this->alias]['last_name']}";
            $results[$i][$this->alias]['full_name'] = $full_name;
          
            # query is adult 
            $results[$i][$this->alias]['is_adult'] = (int)$result[$this->alias]['age'] > 18
          }
        }# Associated find
        elseif (isset($results[$this->alias])) {
            # build another property
            $full_name = "{$result[$this->alias]['first_name']} {$result[$this->alias]['last_name']}";
            $results[$this->alias]['full_name'] = $full_name;
          
            # query is adult 
            $results[$this->alias]['is_adult'] = (int)$result[$this->alias]['age'] > 18
    
        }
      }
    }
    ?>

That is some trivial logic and pretty "WET" code,


Introducing Virtual Attribute
-----------------------------

This behavior is largely Inspirated by AliasBehavior and nifty trick
Felix GeisendÃ¶rfer uses static model methods for url generation..
`http://debuggable.com/posts/new-router-goodies:480f4dd6-4d40-4405
-908d-4cd7cbdd56cb`_
Features

#. Idea for any kind of data manipulation that usually ended in
   Model::afterFind()
#. There is a lot of potential application for this such as Attribute
   aliasing, Derivative attribute and value filtering
#. Following the pattern you will endup with some very useful static
   methods can be used in controller or view and be Mr. DRY
#.



Example
-------
Same person model is suddenly much sexier now, now returned find
result will include all virtual attribute that we desired and all
virtual attribute are build from respective model methods and Best yet
you can reuse them in a view or controller.

Sexy Person model

Model Class:
````````````

::

    <?php 
    class Person extends AppModel {
      $actsAs = array('Attribute' => array('full_name', 'is_adult'));
     
      function full_name ($person) {
        $person = $person['Person'];
        $middleInitial = $person['middle_name'] ? strtoupper($person['middle_name'][0]).'.' :'';
        return "{$person['Person']['first_name']} {$middleInitial} {$person['Person']['last_name']}" 
      }
      
      function is_adult($person) {
        return int_val($person['Person']['age']) >= 18
      }
    
      function url($person) {
        $slug = Inflecter::slugify($this->full_name());
        return "/people/{$person['Person']['id'};{$slug}";
      }
    }
    ?>

Result

::

    
      $this->Person->find('all');

Sweet, Result will automattically include our custom attributes,

::

    
    Array (
     0 => Array(
        'Person' => Array(
            'id' => 1,
            'first_name' => 'Peter',
            'last_name' => 'Black'
            'middle_name' => 'Joanna'
            'age' => 21,
            'full_name' => 'Peter J. Black',
            'is_adult' => 1,
            'url' => '/people/1;peter-j-black'
        )  
     1 => Array(
      ......  
       
     )  

Here is a example how you can reuse those static methods in view,
let's assume for now those additional attribute doesn't exist yet,
since there are cases you may want to reuse those logic in view or
controller

View Template:
``````````````

::

    
    ...
      <ul>
      <? foreach ($people as $p) : ?>
         <li>
            <? if Person::is_adult($p) : ?>
              He seems old enough 
            <? endif ?>
          
             <?= $html->link(Person::fullname($p), Person::url($p)) ?>
          </li>
      <? endforeach ?>
      </ul>
    ...



More example
````````````

Here is a Article model for a blogging application, but you want to
provide Aliasing, filtering or derived attribute

Model Class:
````````````

::

    <?php 
    class Article extends AppModel {
      $actsAs = array(
        'Attribute' => array('body', 'slug', 'url', 'is_commentable')
      );
     
      function slug($article) {
         return $article['Article']['permalink'];
      }
    
      function is_commentable($article) {
         return $article['Article']['allow_comment'] === 'yes';
      }
      function is_published($article) {
         return $article['Article']['status'] === 'published' ;
      }  
      
      function url($article) {
        $article = $article['Article'];
        return date('/Y/m/d/', strtotime($article['published_at']) . $article['permalink'];
      }
    }
    ?>



Limitation and Work around
--------------------------
Currently CakePHP model doesn't propagate afterFind callback to
behavior in associated model, for example: Site hasMany Article.
When u find your site, All article will be find except
AttributeBehavior::afterFind won't be triggered. Here is a example
work around but use with care.

Model Class:
````````````

::

    <?php 
    class Site extends AppModel {
      var $hasMany = array('Article');
    }
    
    //work around
    class Article extends AppModel {
         ....
         function afterFind($results, $primary = false) {
              if (!$primary) {
                  return $this->Behaviors->Attribute->afterFind($this, $results, true);
              }
         }
    }
    ?>



Code
----

Save as app/models/behaviors/attribute.php


Model Class:
````````````

::

    <?php 
    class AttributeBehavior extends ModelBehavior {
        function setup(&$model, $config = array()) {
            if (is_string($config))
                $config = array($config);
    
            $this->settings[$model->alias] = $config;   
        }
        
        function afterFind(&$model, $results = array(), $primary = false) {
            $attributes = $this->settings[$model->alias];
            
            if ($primary && isset($results[0][$model->alias])) {
                foreach($results as $i => $result) {
                    foreach ($attributes as $attr) {
                        if (method_exists($model, $attr) && !is_null($tmp = $model->$attr($result))) {
                            $results[$i][$model->alias][$attr] = $tmp;
                        } 
                    }
                }
            } 
            elseif (isset($results[$model->alias])) {
                foreach ($attributes as $attr) {
                    if (method_exists($model, $attr) &&  !is_null($tmp = $model->$attr($result))) {
                        $results[$model->alias][$attr] = $tmp; 
                    }
                }
            }
            return $results;
        }
    } 
    ?>
    ?>



.. _http://debuggable.com/posts/new-router-goodies:480f4dd6-4d40-4405-908d-4cd7cbdd56cb: http://debuggable.com/posts/new-router-goodies:480f4dd6-4d40-4405-908d-4cd7cbdd56cb

.. author:: taylor.luk
.. categories:: articles, behaviors
.. tags:: model,behavior,attribute,dry,attributes,callback,virtual,com
puted,Behaviors

