Calling controller actions from cron and the command line
=========================================================

by mathew_attlee on December 05, 2006

This is a very simple tutorial that shows how you can modify the
dispatcher to call controller actions from the command line and cron.
On the Google Group there has been quite a few threads about the best
way to run cron jobs when using CakePHP. This is something I had do
for my one of projects and I figured out a very simple way to do it by
modifying the dispatcher so controller actions - such as an action to
send out a batch of emails - can be invoked from the command line.

It's very simple. What you do is make a copy of index.php in
/app/webroot/, call it cron_dispatcher.php and place it in /app.

Scroll down in the code till you come to the line:-

::

    
    require CORE_PATH.'cake'.DS.'bootstrap.php';

Now replaces everything below it with the following code:-

::

    
    // Dispatch the controller action given to it
    // eg php cron_dispatcher.php /controller/action
    define('CRON_DISPATCHER',true);
    if($argc == 2) {
            $Dispatcher= new Dispatcher();
            $Dispatcher->dispatch($argv[1]);
    }

What this means is you can call controller actions from the command
line, so for example to call the send action in your mailouts
controller you just create a cron job like this:

php cron_dispatcher.php /mailouts/send

As you can see it's a very simple approach that doesn't break the MVC
framework.

Since you are invoking your cron jobs as controller actions it is
possible for users to invoke the action from the site proper. If there
are certain actions that you don't want users to invoke add a line to
the action to check that the constant CRON_DISPATCHER is defined. For
example

::

    
    function send() 
    {
        // Check the action is being invoked by the cron dispatcher
        if (!defined('CRON_DISPATCHER')) { $this->redirect('/'); exit(); }
    
        $this->layout = null; // turn off the layout
        
        // do something here
    }



.. author:: mathew_attlee
.. categories:: articles, tutorials
.. tags:: command_line,cron,automate,Tutorials

