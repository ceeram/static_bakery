How to bend CakePHP's session handling to your needs
====================================================

by ADmad on September 02, 2009

This article is an attempt to break the myth that Cakephp's current
session handling is not easily customizable.
There are various tickets on `https://trac.cakephp.org`_ stating that
current session handling by CakePHP is not configurable enough and
asking for enhancements to it. Here are a few examples.
`https://trac.cakephp.org/ticket/830`_
`https://trac.cakephp.org/ticket/5985`_
`https://trac.cakephp.org/ticket/6324`_

It seems most people don't release that you can specify your own
config file using which you can set session handling as per your needs
and don't have to resort to hacking core files. If you check your
app/config/core.php there is a comment which states:
To define a custom session handler, save it at /app/config/(name).php.
Set the value of 'Session.save' to (name) to utilize it in CakePHP.
So we are going to do just that.

In your core.php put

::

    Configure::write('Session.save', 'my_session_handler');

Then make a file app/config/my_session_handler.php and put your stuff
there. Here's an example file

::

    
    <?php
    // You can copy the ini_set statements from the switch block here
    // http://code.cakephp.org/source/branches/1.2/cake/libs/session.php#484
    // for case 'php' (around line 484) and modify to your needs.
    
    // Lets assume our config value for Security.level is 'medium'
    
    //Get rid of the referrer check even when Security.level is medium
    ini_set('session.referer_check', '');
    // or you can use this to restore to previous value
    // ini_restore('session.referer_check');
    
    //Cookie lifetime set to 0, so session is destroyed when browser is closed and doesn't persist for days as it does by default when Security.level is 'low' or 'medium'
    ini_set('session.cookie_lifetime', 0);
    
    //Now this feels a bit hacky so it would surely be nice to have a config variable for cookie path instead.
    //Cookie path is now '/' even if your app is within a sub directory on the domain
    $this->path = '/';
    ini_set('session.cookie_path', $this->path);
    
    //This sets the cookie domain to ".yourdomain.com" thereby making session persists across all sub-domains
    ini_set('session.cookie_domain', env('HTTP_BASE'));
    
    //Comment out/remove this line if you want to keep using the default session cookie name 'PHPSESSID'
    //Useful when you want to share session vars with another non-cake app.
    ini_set('session.name', Configure::read('Session.cookie'));
    
    //Makes sure PHPSESSID doesn't tag along in all your urls
    ini_set('session.use_trans_sid', 0);
    ?>


So this example file above shows how you can customize session
handling, at least for php based session handling. I personally
haven't used database based session handling so not sure how things
would work out it that case but i don't think it would be much of a
problem either. Please feel to point out any mistakes on my part.

.. _https://trac.cakephp.org/ticket/5985: https://trac.cakephp.org/ticket/5985
.. _https://trac.cakephp.org/ticket/6324: https://trac.cakephp.org/ticket/6324
.. _https://trac.cakephp.org: https://trac.cakephp.org/
.. _https://trac.cakephp.org/ticket/830: https://trac.cakephp.org/ticket/830

.. author:: ADmad
.. categories:: articles, tutorials
.. tags:: session,session handling,admad,session
customizatio,Tutorials

