AliasBehavior
=============

The behavior allows you to create pseudo field names for actual
fields, and allows you to use those fields as if they were actual
fields in the database table.


What is it?
```````````
This behavior allows you to use pseudo field names that correspond to
actual fields in your model's find*/save*/deleteAll operations.

I've actually find this kind of thing to be useful when migrating non-
standard Cake database tables from other applications to something
more comfortable to use in Cake. But I'm sure it has many more uses :)



Download
````````

The behavior class is as follows:


Behavior Class:
```````````````

::

    <?php 
    /**
     * The alias behavior allows you to use column aliases to refer to a particular
     * field in your model using another name.
     *
     * The behavior will figure out in your find/save operations which fields
     * correspond to which aliases and vice-versa.
     *
     * For find operations, string-based conditions are not supported. Only array
     * based conditions will work.
     *
     * Interestingly, findBy* and findAllBy* methods work for aliases :)
     *
     * @author Matthew Harris <shugotenshi@gmail.com>
     */
    class AliasBehavior extends ModelBehavior {
        /**
         * Field aliases.
         *
         * @var array
         * @access private
         */
        var $__aliases = array();
       
        /**
         * Setup aliases.
         *
         * @param Model model
         * @param array $config
         */
        function setup(&$model, $config = array())
        {
            if (is_array($config)) {
                foreach ($config as $field => $aliases) {
                    if (!is_array($aliases)) {
                        $aliases = array($aliases);
                    }
                    if ($model->hasField($field)) {
                        $this->__aliases[$field] = $aliases;
                    }
                }
            }
        }
        
        /**
         * Get the field->alias mapping.
         *
         * @return array
         * @access public
         */
        function getAliases()
        {
            return $this->__aliases;
        }
        
        /**
         * Replace field values with the actual values in their aliases.
         * This only works when an array is used for conditions, instead of a
         * string partial.
         *
         * @param Model $model
         * @param array $queryData
         * @return array
         * @access public
         */
        function beforeFind(&$model, $queryData)
        {
            if (isset($queryData['conditions']) && is_array($queryData['conditions'])) {
                foreach ($this->__aliases as $field => $aliases) {
                    foreach ($aliases as $alias) {
                        if (isset($queryData['conditions'][$alias])) {
                            $queryData['conditions'][$model->alias.'.'.$field] = $queryData['conditions'][$alias];
                            unset($queryData['conditions'][$alias]);
                        }
                        
                        if (isset($queryData['conditions'][$model->alias.'.'.$alias])) {
                            $queryData['conditions'][$model->alias.'.'.$field] = $queryData['conditions'][$model->alias.'.'.$alias];
                            unset($queryData['conditions'][$model->alias.'.'.$alias]);
                        }
                    }
                }
            }
            return $queryData;
        }
        
        /**
         * Replace field values with the value stored in their alias fields.
         * The actual value will be the one stored in the last alias for a given
         * field.
         *
         * @param Model $model
         * @return boolean
         * @access public
         */
        function beforeSave(&$model)
        {
            if (isset($model->data[$model->alias]) && is_array($model->data[$model->alias])) {
                foreach ($this->__aliases as $field => $aliases) {
                    foreach ($aliases as $alias) {
                        if (isset($model->data[$model->alias][$alias])) {
                            $model->data[$model->alias][$field] = $model->data[$model->alias][$alias];
                            unset($model->data[$model->alias][$alias]);
                        }
                    }
                }
            }
            return true;
        }
        
        /**
         * Set aliases to the value of corresponding field.
         *
         * @param Model $model
         * @param array $results
         * @return array
         * @access public
         */
        function afterFind(&$model, $results)
        {
            foreach ($results as $key => $result) {
                if (isset($results[$key][$model->alias]) && is_array($results[$key][$model->alias])) {
                    foreach ($this->__aliases as $field => $aliases) {
                        if (isset($results[$key][$model->alias][$field])) {
                            foreach ($aliases as $alias) {
                                $results[$key][$model->alias][$alias] = $results[$key][$model->alias][$field];
                            }
                        }
                    }
                }
            }
            return $results;
        }
        
        /**
         * Set aliases to the value of corresponding field.
         *
         * @param Model $model
         * @return boolean
         */
        function afterSave(&$model)
        {
            if (isset($model->data[$model->alias]) && is_array($model->data[$model->alias])) {
                foreach ($this->__aliases as $field => $aliases) {
                    if (isset($model->data[$model->alias][$field])) {
                        foreach ($aliases as $alias) {
                            $model->data[$model->alias][$alias] = $model->data[$model->alias][$field];
                        }
                    }
                }
            }
            return true;
        }
    }
    ?>

You can obtain snapshots of the behavior and components (and my other
stuff) from my public snapshots directory on the web, where I keep
snapshots of working branches.

Check it out here:
`http://ariworks.co.kr/~kuja/files/snapshots/cake/`_


How to use the behavior
```````````````````````
There's not much involved in actually using the behavior. Simply
download it, place the alias.php into your APP/models/behaviors
directory and add the correct $actsAs line to your model you'd like to
apply the behavior to. I'll also demonstrate in one swift move how you
can define your aliases for that model.

Let's say you have a User model, then this is how you'd apply the
Alias behavior:


Model Class:
````````````

::

    <?php 
    class User extends AppModel {
    	var $actsAs = array('Alias' => array(
    		'username'   => 'nickname',
    		'gender'     => 'sex',
    		'ip_address' => array('client_ip', 'remote_addr')
    	));
    }
    ?>

It's that simple :)

The format is 'actualField' => 'pseudoField', or if you want multiple
aliases to the same field, it's 'actualField' => array('pseudoField',
'pseudoField2', 'pseudoField3', ...) and so on.

After you've set up your model's aliases, you can proceed to use your
model normally. You can find and save from and to pseudo fields just
as if they were normal fields, with the exception that in find
operations, you *must* use array-based conditions or else pseudo-field
handling will be disabled.

For example: $users = $this->User->findAll(array('sex' => 'm'))
Do *not* do: $users = $this->User->findAll("sex = 'm'")

And that's all there is to it.



Feedback and support
````````````````````
If you have any comments or questions, feel free to contact me, as
usual I'm kuja at #cakephp on irc.freenode.net!
Don't hesitate to catch me by e-mail either: shugotenshi at gmail dot
com

Or just leave comments on this article and I'll get to it as soon as I
see it :)

Thanks.

.. _http://ariworks.co.kr/~kuja/files/snapshots/cake/: http://ariworks.co.kr/~kuja/files/snapshots/cake/

.. author:: kuja
.. categories:: articles, behaviors
.. tags:: ,Behaviors

