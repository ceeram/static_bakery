Make Your Visitors Use HTTPS With the Security Component
========================================================

You can easily force visitors to use an SSL connection to your Cake
site with the Security Component. Here's how.
In whichever controller you want to always use HTTPS (or for all of
them, using app_controller.php), call requireSecure() like so:

Controller Class:
`````````````````

::

    <?php 
    function beforeFilter()
    	{
    		$this->Security->requireSecure();
    		$this->Security->blackHoleCallback = '_blackHole';
        }
    ?>

The blackHoleCallback value tells the Security component what function
to call when the user is not using HTTPS, in this case _blackHole():

Controller Class:
`````````````````

::

    <?php 
    function _blackHole()
        {
    		$this->log("Redirecting user from HTTP to HTTPS");
    		$this->redirect(str_replace('http://', 'https://', Router::url($this->params['url']['url'], true)), null, true);
        }
    ?>

Now whenever a user requests a HTTP address (e.g.
`http://www.myserver.com/posts/view/1`_) they are sent to the HTTPS
version (e.g. `https://www.myserver.com/posts/view/1`_).

NOTE: I'm having some issues getting this to work correctly with the
Auth component's login page. Any advice would be appreciated.

.. _http://www.myserver.com/posts/view/1: http://www.myserver.com/posts/view/1
.. _https://www.myserver.com/posts/view/1: https://www.myserver.com/posts/view/1

.. author:: pr1001
.. categories:: articles, tutorials
.. tags:: redirect,security,HTTP,component,https,ssl,Tutorials

