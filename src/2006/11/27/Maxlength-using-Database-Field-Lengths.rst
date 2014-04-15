Maxlength using Database Field Lengths
======================================

by cornernote on November 27, 2006

I wanted to have a maxlength="something" in my forms, but I needed the
value to come from the database at least during development. When it
goes live the data will cache so it will still be fast.
1. Create a cache folder in app/tmp/cache/fieldlengths

2. Create in app/controllers/components/fieldlength.php

Component Class:
````````````````

::

    <?php 
    class FieldlengthComponent extends Object
    {
      var $controller = true;
    
      function startup(&$controller)
      {
        $this->controller = &$controller;
        $controllerClass = $this->controller->name;
        $modelClass = $this->controller->modelClass;
    
        if (!$fieldLengths = cache('fieldlengths'.DS.$modelClass))
        {
          $cols = $this->controller->$modelClass->query('DESC ' . $this->controller->$modelClass->table);
      		foreach ($cols as $column) {
      			if (isset($column['COLUMNS'])) {
          		if (preg_match('/^(?!.*int).*(?:[(]([0-9]+(?:,[0-9]+)?)[)])$/', $column['COLUMNS']['Type'], $regs))
          		{
          		  $fieldLengths[$column['COLUMNS']['Field']] = $regs[1];
              }
              elseif (strpos($column['COLUMNS']['Type'],'date')!==false)
              {
          		  $fieldLengths[$column['COLUMNS']['Field']] = 'date';
              }
      			}
      		}
          $fieldLengths = CACHE_CHECK ? cache('fieldlengths'.DS.$table, serialize($fieldLengths)) : serialize($fieldLengths);
        }
        $this->controller->set('field_lengths', unserialize($fieldLengths));
      }
    }
    ?>


3. Add this to app/views/[-tablename-]/form.thtml

View Template:
``````````````

::

    
    <?php echo $html->input('[-Model-]/[-field-]', array('size' => '50', 'maxlength' => $field_lengths['[-field-]'])); ?>



.. author:: cornernote
.. categories:: articles, components
.. tags:: ,Components

