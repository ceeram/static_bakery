Simple access control via AuthComponent
=======================================

by SiggiSmara on October 22, 2008

Sometimes all you want is a simple action based access control. You
can achieve that with minimal amount of effort with the AuthComponent.
This article explains how for newbie bakers like me. It took a while
for everything to sink in so I just wanted to spare someone else the
headache.

If you are not planning on using the ACL component for access control,
or would like a simpler form of access control, you can use the
allow() and deny() functions of the AuthComponent in a beforeFilter()
function of your controller. The way this works is that by default
everything is disallowed. You add actions to a list of allowed actions
by using the allow() function and remove items from the list by using
the deny() function.

First, setup your AuthComponent to work with your App as explained in
the Docs: `http://book.cakephp.org/view/172/Authentication`_

That's it!(almost) Now you are ready for some simple access control.

To allow access to all static pages (including the lovely 'home.cpt'
page) you can use allow('display') in the beforeFilter() of your
AppController like this:

Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
        var $component = array('Auth');
    
        function beforeFilter() {
            $this->Auth->allow('display');
        }
    }
    ?>

The deny() function is used to remove previously allowed actions
rather than force the denial of the action.

Example, lets say you allowed the following actions in your
AppController:

Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
        var $component = array('Auth');
    
        function beforeFilter() {
            $this->Auth->allow('display','index','share');
        }
    }
    ?>

And that in a particular controller you would want to disallow the
'index' action. You would do that like this:

Controller Class:
`````````````````

::

    <?php 
    class myAppController extends AppController {
        var $component = array('Auth');
    
        function beforeFilter() {
            $this->Auth->deny('index');
        }
    }
    ?>

The result would be that 'index' action in myAppController would no
longer be allowed.

And that is all there is to it.

Things to look out for
----------------------

The caveat of this approach is that you should be very careful of
allowing actions in your AppController as these will be global
allowances. Try to set allowances per controller as much as you can
since that minimizes any any potential action name overlap problems.

Also be aware that the deny() function will not add actions to a list
of denied actions, only remove it from the list of allowed actions if
present. You can not change the basic principle of the component that
'everything is disallowed except this list of allowed actions' to
'everything is allowed except this list of disallowed actions'.

Example: allow everything in your AppController

Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
        var $component = array('Auth');
    
        function beforeFilter() {
    
            $this->Auth->allow('*');  //allow every action of every controller
        }
    }
    ?>

And then try to disallow specific actions

Controller Class:
`````````````````

::

    <?php 
    class myAppController extends AppController {
        var $component = array('Auth');
    
        function beforeFilter() {
            //will not work as these actions are not on the list of allowed actions
            $this->Auth->deny('add','update','delete');  
        }
    }
    ?>

This fails due to the false assumption that there is a list of
disallowed actions. There is only one list and it is of allowed
actions, if the action is not on it, then it is disallowed. If you
give the AutComponent broad allowances (like '*') then expect
unreliable results or at best that everything will be allowed.


.. _http://book.cakephp.org/view/172/Authentication: http://book.cakephp.org/view/172/Authentication

.. author:: SiggiSmara
.. categories:: articles, tutorials
.. tags:: access control,authcomponent,Tutorials

