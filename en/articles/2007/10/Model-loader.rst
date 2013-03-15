

Model loader
============

by %s on October 23, 2007

This component helps you to load your models on the fly ( I mean in
your actions, whenever).
If you just add the model names to $uses, Cake will autoload for you.
But when you have large controllers, you find out that you will need
to use difference models in different actions. The Cake autoloading
will make your application slow down and need more memory usage by
loading models that you don't need (in the current action).

This component helps you to solve that problem. It gives you freedom
to load the Model on the fly. You can also name the model any name you
want.

This is the cod. It's simple because I just do the things that
Controller::constructClasses do with some modifications.

You will save it to app/controllers/components/model_loader.php

Component Class:
````````````````

::

    <?php 
    <?php
    
    /**
     * @author Huy <www.imhuy.com>
     * @license http://www.opensource.org/licenses/mit-license.php The MIT License
     */
    
    
    class ModelLoaderComponent extends Object
    {
        
        var $controller = null;
        
        function startup(&$controller)
        {
            $this->setController($controller);
        }
        /**
         * Load the model and assign it to the controller
         *
         * @access public
         * @param string $modelClass
         * @return bool | mixed
         */
        function load($modelClass, $objectName = null)
        {
            if (!$this->controller)
            {
                return false;
            }
            
            
            $id = false;
            $object = null;
            $cached = false;
            
            $plugin = '';
            
            if ($this->controller->plugin) 
            {
                $plugin = $this->controller->plugin . '.';
            }
            
            if (!class_exists($modelClass))
            {
                loadModel($plugin . $modelClass);
            }
            
            if (class_exists($modelClass))
            {
                if (!$objectName)
                {
                    $objectName = $modelClass;
                }
                
                if ($this->controller->persistModel)
                {
                    $cached = $this->_persist($modelClass, null, $object);
                }
                
                if (false === $cached)
                {
                    $model =& new $modelClass($id);
                    $this->controller->modelNames[] = $modelClass;
                    $this->controller->{$objectName} =& $model;
                    
                    if ($this->controller->persistModel === true) 
                    {
                        $this->_persist($modelClass, true, $model);
                        $registry = ClassRegistry::getInstance();
                        $this->_persist($modelClass . 'registry', true, $registry->__objects, 'registry');
                    }
                    
                }
                else
                {
                    $this->_persist($modelClass . 'registry', true, $object, 'registry');
                    $this->_persist($modelClass, true, $object);
                    $this->controller->{$objectName} = $this->{$modelClass};
                    
                    // unset the temp model, for PHP4 
                    unset($this->{$modelClass});
                    $this->controller->modelNames[] = $modelClass;
                }
            }
            else
            {
                return $this->controller->cakeError('missingModel', array(array('className' => $modelClass, 'webroot' => '', 'base' => $this->controller->base)));
            }
            return true;
        }
        
        /**
         * Set the controller to work
         *
         * @access public
         * @param AppController $controller
         * @return void
         */
        function setController(&$controller)
        {
            $this->controller = $controller;
        }
    }
    ?>
    ?>



Usage
`````

::

    
      class FooController extends AppController
      {
          var $components = array('ModelLoader');
          
          function index($id)
          {
              $this->ModelLoader->setController($this);
              $this->ModelLoader->load('Product');
              $this->Product->read(null, $id);
      
              $this->ModelLoader->load('Solution', 'Solu');
              $this->Solu->read(null, $id);
          }
      }

Have fun !

.. meta::
    :title: Model loader
    :description: CakePHP Article related to component,model_loader,Components
    :keywords: component,model_loader,Components
    :copyright: Copyright 2007 
    :category: components

