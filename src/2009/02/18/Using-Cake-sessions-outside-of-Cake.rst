Using Cake sessions outside of Cake
===================================

While recently working on a CMS tool, I needed to pass some in-session
information. I was using Cake's database sessions and it wasn't
playing nice with outside applications so I set this up to allow my
outside application to use Cake's session handlers.
The short version is that you need to make sure all of the path's are
setup correctly, which happens in index.php.

Copy your index.php file into another file (I called it
cake_session.php). This file needs to be in the webroot because
index.php initializes the paths based on the location of webroot.

In your cake_sessions.php file find this line (should be line 86):

::

    
    if (isset($_GET['url']) && $_GET['url'] === 'favicon.ico') {

And delete everything from there down. Now just add this code:

::

    
    if(App::import('Core','Session')) {
       $session = new CakeSession();
       $session->start();
    }

Check your $_SESSION variable to make sure everything works. You
should be able to just include this file anywhere that you want to use
your cake session.


.. author:: brightball
.. categories:: articles, tutorials
.. tags:: database,session,placeniceoutsideofca,brightball,Tutorials

