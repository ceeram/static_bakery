Component to display more flash messages
========================================

by T0aD on June 18, 2007

I always wondered why CakePHP didn't come right away with a method to
store several session flash messages to be displayed later to the user
(or at least I didn't find the quick and easy way to do so), hence
this tiny piece of code, enjoy..
This functionnality with rely on a component (to store messages from
controllers' side) and on a helper (to eventually display our messages
to the user).

This is a replacement for $this->Session->setFlash() and
$this->Session->flash() methods.


Demo:
`````
In your thtml template file:

::

    
    <?php
    $message->display(); // will display every flash messages stored
    ?>

In your controller file:

::

    
    <?php
    ...
    var $components = array('Message');
    function anyFunction()
    {
    $this->Message->add("My first flash message");
    ..
    $this->Message->add("Oh ! a second flash message !");
    }
    ...
    ?>



Helper Class:
`````````````

::

    <?php 
    class MessageHelper extends Helper
    {
      var $helpers = array('Session');
      
      function display()
      {
        for ($i = 0, $lastID = 0; $this->Session->check('Message.' . $i); $i++) {
          $lastID = $i;
        }
        while ($lastID != 0)
        {
          e('<div class="flash">' . $this->Session->read('Message.' . $lastID) . '</div>');
          $this->Session->del('Message.' . $lastID);
          $lastID--;
        }
      }
      
      /* If we want to get message one by one.. interest ? */
      function get()
      {
      }
    }
    ?>

With this helper, you would like to change the cake.generic.css
definition of #flashMessage style to become .flash



Component Class:
````````````````

::

    <?php    
    class MessageComponent extends Object
    {
      var $components = array('Session');
      
      function add($message)
      {
        for ($i = 0; $this->Session->check('Message.' . $i); $i++)
          ;
        $this->Session->write('Message.' . $i, $message);
      }
    }
    ?>

I wish CakePHP will come with something so easy to use ;)



.. author:: T0aD
.. categories:: articles, components
.. tags:: flash,session,Components

