Passing Named Parameters
========================

by TommyO on November 01, 2006

This simple tutorial will add the ability to pass named parameters to
your actions. Named Parameters are beneficial because they: * Make for
much prettier URLs * Allow passing of a variable number of parameters
easier * Allow passing of parameters in any order * Remove the need to
pass placeholders in URLs A URL containing named parameters may look
like:
http://example.com/controller/action/param1:value1/param2:value2/

Note: This is a feature planned for CakePHP 1.2. Because of this, I
have tried to stick to the planned conventions as much as possible.
The latest version presented here is in fact based on research done on
the proposed functionality for CakePHP 1.2. I have included only a
portion of the future functionality set, but enough to start using
named params immediately.

This is 100% my code - not taken from the official source. Therefore I
cannot guarantee that it will match exactly the functionality of the
final release. I will say that if the final release matches what the
proposal presents, then your code will migrate flawlessly simply by
removing my code from the app_controller.

You are of course welcome to modify this code, but I ask that you
stick as closely as possible to the planned conventions. Making it
even more compliant is better still.


The Code
````````

Modify your app_controller.php in the following way:

Set up the necessary members and their default values

::

    
        var $namedArgs = FALSE;
        var $argSeparator = ":";


Define the callback function

::

    
       /**
       * A callback function to populate the namedArgs array if activated
       * This should be triggered in the beforeFilter
       * @return TRUE always
       *
       **/
        function getNamedArgs() {
            if ($this->namedArgs)
            {
                $this->namedArgs = array();
                if (!empty($this->params['pass']))
                {
                    foreach ($this->params['pass'] as $param)
                    {
                        if (strpos($param, $this->argSeparator))
                        {
                            list($name, $val) = split( this->argSeparator,
                                                       $param );
                            $this->namedArgs[$name] = $val;
                        }
                    }
                }
            }
            return TRUE;
        }


Make sure the callback is called before every controller/action. This
can be place in the AppController or your individual controllers, as
needed.

::

    
        function beforeFilter() {
            $this->getNamedArgs();
        }



Using named params in your controller
`````````````````````````````````````

Now it is as simple as activating it in your controller. Once
activated, named parameters will be available through $namedArgs
array. An example:

Controller Class:
`````````````````

::

    <?php 
    class MyExamplesController extends AppController
    {
        var $namedArgs = TRUE;
     
        function myAction()
        {
            if (isset($this->namedArgs['param1']))
            {
                $myParam1 = $this->namedArgs['param1'];
            }
        }
    }
    ?>

In this example, $myParam1 will have a value of 'value' on the
following request:
`http://example.com/myexamples/myAction/param1:value/`_

There you have it. Simple and clean. Input welcome.


Options
```````

There is one parameter, $argSeparator, that can be set to whatever you
would like as a separator. The default is ":". Redefine in your
controller like:

::

    
    var $argSeparator = "|";



.. _http://example.com/myexamples/myAction/param1:value/: http://example.com/myexamples/myAction/param1:value/

.. author:: TommyO
.. categories:: articles, tutorials
.. tags:: Named Parameters,Tutorials

