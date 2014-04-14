How to Add AutoComplete to Eclipse/Aptana
=========================================

by gravyface on July 23, 2009

In this article I'll show you how to add autocomplete to
Eclipse/Aptana for models, components, and helpers. Thanks goes out to
schneimi and voidstate's on Google Groups for figuring this trick out.
If you haven't already, create an AppController in your application
folder. By default, the file app_controller.php should be created in
/your/cake/install/app/


Adding AutoComplete in Controllers for Models and Components
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Copy/paste the following lines somewhere in your app_controller.php;
when finished, it should look like this:

Controller Class:
`````````````````

::

    <?php 
    /**
     * Post Model
     *
     * @var Post
     */
     var $Post;
    
    /**
     * User Model
     *
     * @var User
     */
     var $User;
    
     /**
     * Group Model
     *
     * @var Group
     */
     var $Group;
     
     /**
     * AuthComponent
     *
     * @var AuthComponent
     */
     var $Auth;
     
     /**
     * SessionComponent
     *
     * @var SessionComponent
     */
     var $Session;
     
      /**
     * RequestHandlerComponent
     *
     * @var RequestHandlerComponent
     */
     var $RequestHandler;
    
    ?>

Substitute my models for your own, obviously.

Now in Eclipse, you should be able to hit CTRL-SPACE after entering
$this-> to bring up a selection of model and/or components; ditto for
model and/or component methods, properties, etc. Note: make sure you
use the window resizer (bottom-right) to stretch out the autocomplete
window so you see the full object docs (you'll notice that
"parameters" will be cut-off and show "...").


Adding Helper AutoComplete to Views
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Create a file called "eclipse_helper.php" (or whatever, it doesn't
matter) and put it somewhere in your /app path. Copy/paste the
following:


Helper Class:
`````````````

::

    <?php 
    exit();
    // auto-complete in views for Helpers
    if(false) {
        $ajax = new AjaxHelper();
        $cache = new CacheHelper();
        $form = new FormHelper();
        $html = new HtmlHelper();
        $javascript = new JavascriptHelper();
        $number = new NumberHelper();
        $session = new SessionHelper();
        $text = new TextHelper();
        $time = new TimeHelper();
    }
    ?>

NOTE: if (like me) you have a bad habit of creating files outside of
Eclipse, make sure you hit F5 and refresh your project or the view
helpers won't work.

Not working? Try closing/re-opening Eclipse.

You're good to go. Have fun!



.. meta::
    :title: How to Add AutoComplete to Eclipse/Aptana
    :description: CakePHP Article related to autocomplete,Eclipse,syntax highlighting,code complete,intellisense,General Interest
    :keywords: autocomplete,Eclipse,syntax highlighting,code complete,intellisense,General Interest
    :copyright: Copyright 2009 gravyface
    :category: general_interest

