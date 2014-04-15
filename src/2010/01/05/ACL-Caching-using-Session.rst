ACL Caching using Session
=========================

by macduy on January 05, 2010

ACL checks can be costly and would benefit from caching. One approach
would be to use Cake's Caching system, as implemented in
http://bakery.cakephp.org/articles/view/caching-acl-permissions-with-
cachedaclcomponent. Presented here is a different approach, where the
cache is stored using Session.


How it works
~~~~~~~~~~~~
Results of ACL checks are stored in the Session variable 'Acl'. Every
time an ACL check is performed, the component looks into this variable
to see if the result has been stored, if not, it performs the usual
check using the original AclComponent and then stores the result in
the variable.

The limitations of this method is its reliance on the Session
component. Also, the cache remains active until the user destroys the
session, either by logging out or manually. This will cause problems
if ACL rights tend to change often while the Session remains active.


Getting the code
````````````````
To use the component, download the code on the next page and store it
in /app/controllers/components/ as session_acl.php .


How to use
~~~~~~~~~~

To use the component, include it in the $component variable, along
with Session. Do not include the 'Acl' component.

::

    <?php
    
    var $components = array('Session','SessionAcl');?>

The component stores itself as $this->Acl and so you can continue
using e.g.

::

    <?php
    $this->Acl->check($user_alias, "Posts", "read");
    ?>

as usual. The only difference is that now SessionAcl is working for
you. All standard functions deny, allow etc. work as well.

If you need to destroy the cache, call

::

    <?php
    $this->Acl->flushCache();
    ?>

or delete the 'Acl' variable from the Session.


Extra functions
~~~~~~~~~~~~~~~

all()
`````
SessionAcl comes with extra functions to simplify certain checks. Say
you would like to allow a user to access something only if he has read
access to A and create access to B. Normally, you would write:

::

    <?php
    if ($this->Acl->check($user_alias, "A", "read") && $this->Acl->check($user_alias, "B", "create"))
    ?>

Using SessionAcl, you can do:

::

    <?php
    if ($this->Acl->all($user_alias, array("A" => "read", "B" => "create")))
    ?>



one()
`````
Similarly, if you would like to allow the user only if he has read
access to at least one of A, B, C or D, instead of writing

::

    <?php
    if ($this->Acl->check($user_alias, "A", "read") || $this->Acl->check($user_alias, "B", "read") || $this->Acl->check($user_alias, "C", "read") || $this->Acl->check($user_alias, "D", "read"))
    ?>

you now can write

::

    <?php
    if ($this->Acl->one($user_alias, array("A" => "read", "B" => "read", "C" => "read", "D" => "read")))
    ?>



can()
`````
Sometimes you want to perform several checks at once. Say you'd like
to know if a user has read,create and delete access to A,B,C
respectively, at once. You can do

::

    <?php
    $result = $this->Acl->can($user_alias, array("A" => "read", "B" => "create", "C" => "delete"))
    ?>

$result now contains, say:

::

    <?php
    [true, false, true]
    ?>

Use it with array_combine to simplify work:

::

    <?php
    $can = array_combine(array("read_A","create_B","delete_C"), $this->Acl->can($user_alias, array("A" => "read", "B" => "create", "C" => "delete"));
    
    if ($can["create_B"]) {...



Component Class:
````````````````

::

    <?php 
    /**
     * ACL Caching.
     *
     * Yet another take at Caching ACL queries, now using Session.
     * Adapted from http://www.nabble.com/ACL-Auth-Speed-Issues-td21386047.html
     * and bits and pieces taken from cached_acl.php
     *
     * It also extends ACL with some nifty functions for easier and simpler code.
     *
     * Cake's ACL doesn't cache anything. For better performance, we
     * put results of check into session. Only ::check() is wrapped,
     * other functions are simply piped to the parent Acl object,
     * though it can be handy to wrap these too in future.
     *
     * @author macduy
     */
    App::import('Component', 'Acl');
    App::import('component', 'Session');
    class SessionAclComponent extends AclComponent
    {
    
        function initialize(&$controller)
        {
            $this->master =& $controller;
            $controller->Acl =& $this;
            $this->Session = new SessionComponent();
        }
        
        function check($aro, $aco, $action = "*")
        {
            $path = $this->__cachePath($aro, $aco, $action);
            if ($this->Session->check($path))
            {
                return $this->Session->read($path);
            } else
            {
                $check = parent::check($aro, $aco, $action);
                $this->Session->write($path, $check);
                return $check;
            }
        }
    
        /**
         * Allow
         */
        function allow($aro, $aco, $action = "*")
        {
            parent::allow($aro, $aco, $action);
            $this->__delete($aro, $aco, $action);
        }
    
        /**
         * Deny method.
         */
        function deny($aro, $aco, $action = "*")
        {
            parent::deny($aro, $aco, $action);
            $this->__delete($aro, $aco, $action);
        }
    
        /**
         * Inherit method.
         *
         * This method overrides and uses the original
         * method. It only adds cache to it.
         *
         * @param string $aro ARO
         * @param string $aco ACO
         * @param string $action Action (defaults to *)
         * @access public
         */
        function inherit($aro, $aco, $action = "*")
        {
            parent::inherit($aro, $aco, $action);
            $this->__delete($aro, $aco, $action);
        }
    
        /**
         * Grant method.
         *
         * This method overrides and uses the original
         * method. It only adds cache to it.
         *
         * @param string $aro ARO
         * @param string $aco ACO
         * @param string $action Action (defaults to *)
         * @access public
         */
        function grant($aro, $aco, $action = "*")
        {
            parent::grant($aro, $aco, $action);
            $this->__delete($aro, $aco, $action);
        }
    
        /**
         * Revoke method.
         *
         * This method overrides and uses the original
         * method. It only adds cache to it.
         *
         * @param string $aro ARO
         * @param string $aco ACO
         * @param string $action Action (defaults to *)
         * @access public
         */
        function revoke($aro, $aco, $action = "*")
        {
            parent::revoke($aro, $aco, $action);
            $this->__delete($aro, $aco, $action);
        }
    
        /**
         * Returns a unique, dot separated path to use as the cache key. Copied from CachedAcl.
         *
         * @param string $aro ARO
         * @param string $aco ACO
         * @param boolean $acoPath Boolean to return only the path to the ACO or the full path to the permission.
         * @access private
         */
        function __cachePath($aro, $aco, $action, $acoPath = false)
        {
            if ($action != "*")
            {
                $aco .= '/' . $action;
            }
            $path = Inflector::slug($aco);
    
            if (!$acoPath)
            {
                if (!is_array($aro))
                {
                    $_aro = explode(':', $aro);
                } elseif (Set::countDim($aro) > 1)
                {
                    $_aro = array(key($aro), current(current($aro)));
                } else
                {
                    $_aro = array_values($aro);
                }
                $path .= '.' . Inflector::slug(implode('.', $_aro));
            }
    
            return "Acl.".$path;
        }
    
        /**
         * Deletes the cache reference in Session, if found
         */
         function __delete($aro, $aco, $action) {
             $key = $this->__cachePath($aro, $aco, $action, true);
             if ($this->Session->check($key))
             {
                 $this->Session->delete($key);
             }
         }
    
         /**
          * Deletes the whole cache from the Session variable
          */
         function flushCache() {
             $this->Session->delete('Acl');
         }
    
         /**
          * Checks that ALL of given pairs of aco-action are satisfied
          */
         function all($aro, $pairs) {
             foreach ($pairs as $aco => $action)
             {
                 if (!$this->check($aro,$aco,$action))
                 {
                     return false;
                 }
             }
             return true;
         }
    
    
         /**
          * Checks that AT LEAST ONE of given pairs of aco-action is satisfied
          */
         function one($aro, $pairs) {
             foreach ($pairs as $aco => $action)
             {
                 if ($this->check($aro,$aco,$action))
                 {
                     return true;
                 }
             }
             return false;
         }
         
         /**
          * Returns an array of booleans for each $aco-$aro pair
          */
         function can($aro, $pairs) {
             $can = array();
             $i = 0;
             foreach ($pairs as $aco => $action)
             {
                 $can[$i] = $this->check($aro,$aco,$action);
                 $i++;
             }
             return $can;
         }
    }
    ?>

`1`_|`2`_


More
````

+ `Page 1`_
+ `Page 2`_

.. _Page 1: :///articles/view/4caea0e5-d720-4bd8-902d-4fa882f0cb67/lang:eng#page-1
.. _Page 2: :///articles/view/4caea0e5-d720-4bd8-902d-4fa882f0cb67/lang:eng#page-2

.. author:: macduy
.. categories:: articles, components
.. tags:: acl,session,component,cache,Components

