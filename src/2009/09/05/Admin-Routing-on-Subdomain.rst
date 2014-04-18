Admin Routing on Subdomain
==========================

I've read several howtos on this topic, but never found a solution i
liked. Until now.

From my first CakePHP application I've been using admin routing, and I
always felt that it would be better to access the admin actions from a
subdomain. I tried to google for some advice on this, but all
solutions i found felt like ugly hacks.

This is my sollution
~~~~~~~~~~~~~~~~~~~~

You just add a prefix to the $params['action'] variable in your
AppController when the request is from the subdomain.

Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
      function beforeFilter() {
        if (ADMIN) {
          $this->params['action'] = 'admin_'.$this->params['action'];
        }
      }
    }
    ?>

For this to work, you'll also need this in your
app/config/bootstrap.php file

::

    if (preg_match('/^admin\./i',$_SERVER['HTTP_HOST']))
      define('ADMIN',true);
    else
      define('ADMIN',false);

NOTE: If you add this to your app, I think it's best to disable admin
routing in the app/config/core.php file, or you might get some strange
results



.. author:: jonarne
.. categories:: articles, tutorials
.. tags:: admin subdomain,Tutorials

