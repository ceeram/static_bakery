

Request Information Component
=============================

by %s on June 07, 2007

Need more insight into how a controller structures a CakePHP request?
This little component will log important information and variables for
each request to your application's debug log.
This is a simple little component that helped me better understand how
the controller was setting up variables and data during requests. You
can control the amount of information contained in the log based on
your DEBUG level found in app/config/core.php

This might be particularly useful to beginning bakers. To use, simply
download the component class below to
app/controllers/componenents/request_info.php , and include the
component in your controller


Controller Class:
`````````````````

::

    <?php $components = array('RequestInfo');?>

And here is the component:


Component Class:
````````````````

::

    <?php 
    class RequestInfoComponent extends Object {
        
        function startup( &$controller ){
            $this->log('---C O N T R O L L E R ------ I N F O R M A T I O N ----',LOG_DEBUG);
            $this->log('---Controller:  '.$controller->name.' ('.get_class($this).'/'.get_parent_class($this).')',LOG_DEBUG);
            $this->log('---Action:  '.$controller->action,LOG_DEBUG);
            switch(DEBUG){
                case 3:
                case 2:
                    $this->log('---Parameters:  ',LOG_DEBUG);
                    $i = 1;
                    foreach($controller->params as $param => $value){
                        if (is_array($value)){
                            $string = str_replace(" ", "", print_r(str_replace("\n"," ",$value), TRUE));
                        } else {
                            $string = $value;
                        }
                        $this->log('--------------'.$i.') '.$param.': '.$string,LOG_DEBUG);
                        $i++;
                    }
                    break;
                case 1:
                    $this->log('Loaded '.$controller->name.'/'.$controller->action,LOG_DEBUG);
                    $this->log('',LOG_DEBUG);
                    break;
                case 0:
                default:
                    // quiet
            } // end switch
        } // end function
    
    } // end class
    ?>


.. meta::
    :title: Request Information Component
    :description: CakePHP Article related to ,Components
    :keywords: ,Components
    :copyright: Copyright 2007 
    :category: components

