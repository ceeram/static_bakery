Creating a custom flash.thtml page
==================================

by jburns131 on September 26, 2006

This tutorial will show you how to create a custom flash.thtml page,
instead of just having a blank page for your flash messages.
This is quite a simple process. This is the minimum code that you'll
need to have in you custom 'flash.thtml' page:


View Template:
``````````````

::

    
    
    
    <!-- cake_install_path/app/views/layouts/flash.thtml -->
    
    <html>
        <head>
            <title><?php echo $page_title?></title>
    
            <?php if(DEBUG == 0) { ?>
                <meta http-equiv="Refresh" content="<?php echo $pause?>;url=<?php echo $url?>"/>
            <?php } ?>
        </head>
    
        <body>
            <p><a href="<?php echo $url?>"><?php echo $message?></a></p>
        </body>
    </html>

First of all, you need to save your custom 'flash.html' page in the
following location: /cake_install_path/app/views/layouts/

Now I'll go through the important lines of this code and explain what
they do.


View Template:
``````````````

::

    
    <?php if(DEBUG == 0) { ?>
        <meta http-equiv="Refresh" content="<?php echo $pause?>;url=<?php echo $url?>"/>
    <?php } ?>

The first line here checks to see if the 'DEBUG' level is 0 (zero). If
so, then it will execute the next line of code, which will pause for
the amount of time that you specified in your 'flash()' message
method, then automaticaly forward the user to the url that you
specified in your 'flash()' message method.

If the 'DEBUG' level is greater than 0 (zero), then the flash message
will not automaticaly forward the user to the specified url.


View Template:
``````````````

::

    
    <p><a href="<?php echo $url?>"><?php echo $message?></a></p>

This will display the message that you specified in your 'flash()'
message method. Remember that in a default cake installation, the
flash message is also a link to the specified url.

That is it. Now all you have to do is add your own formatting to make
the rest of the page look the way you'd like it to.

What I did was made a copy of my 'default.html' page and renamed it to
'flash.thtml', added the


View Template:
``````````````

::

    
    <?php if(DEBUG == 0) { ?>
        <meta http-equiv="Refresh" content="<?php echo $pause?>;url=<?php echo $url?>"/>
    <?php } ?>

lines of code to my header, then replaced the default.thtml's
'$content_for_layout' variable with the flash.thtml's $message
variable.

I got this information from cake's dafault 'flash.html'. file. That's
a good place to get a working example to start out with. You can find
it here:
'/cake_install_path/cake/libs/view/templates/layouts/flash.html'


.. meta::
    :title: Creating a custom flash.thtml page
    :description: CakePHP Article related to Layouts,Tutorials
    :keywords: Layouts,Tutorials
    :copyright: Copyright 2006 jburns131
    :category: tutorials

