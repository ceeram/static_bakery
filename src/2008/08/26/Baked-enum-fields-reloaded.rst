Baked enum fields reloaded
==========================

I had the need to convert (mysql) enum fields into form selects once
baked with the cake console. This method worked for me with minimal
changes to the infrastructure.
Place this into your app/app_controller.php and you will notice that
all the enum(s) are converted into select fields in baked views.


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
    
      function beforeRender() {
        foreach($this->modelNames as $model) {
          foreach($this->$model->_schema as $var => $field) {
            if(strpos($field['type'], 'enum') === FALSE)
              continue;
    
            preg_match_all("/\'([^\']+)\'/", $field['type'], $strEnum);
    
            if(is_array($strEnum[1])) {
              $varName = Inflector::camelize(Inflector::pluralize($var));
              $varName[0] = strtolower($varName[0]);
              $this->set($varName, array_combine($strEnum[1], $strEnum[1]));
            }
          }
        }
      }
    
    }
    
    ?>


This works for mysql (don't know about other dbms) and it's based on
the assumption that
setting a variable array with the same name of the enum field in the
view will convert the free text
input into a select.



.. author:: gunzip
.. categories:: articles, snippets
.. tags:: bake enum mysql form,Snippets

