

Tidy Output Filtering
=====================

by %s on May 16, 2008

If you'd like to filter all output from your application (layouts and
views) through Tidy for cleaner markup, then this article is for you!
I recently stumbled across an urge to output clean HTML/XHTML on all
my pages without having to worry much about writing the actual markup.
Being able to do this would allow me to generate clean markup in all
cases (at the cost of performance, of course).

Now, before starting, please note that you will need the php-tidy
extension. You can read more at `http://php.net/tidy`_.

Now, onto the good stuff. The solution I came to (thanks to AD7six)
was creating a helper. I called this the TidyFilterHelper (I thought
that TidyHelper may have been a bit misleading in this case).

You simply create the tidy_filter.php helper file, drop it in
APP/views/helpers/.

The code is as follows:

Helper Class:
`````````````

::

    <?php 
    class TidyFilterHelper extends AppHelper {
        function __construct()
        {
            ob_start();
        }
        
        function __destruct()
        {
            $output = ob_get_clean();
            $config = array('indent' => true, 'output-xhtml' => true);
            $output = tidy_repair_string($output, $config, 'utf8');
            
            ob_start();
            echo $output;
        }
    }
    ?>

Now that is pretty cool. It would've been cooler if
Helper::afterLayout() was a working callback (it's defined in Helper,
it's documented, just not implemented as a callback yet.. but it will
be soon!)

Now that you've got the helper in place, all you need to do is add the
appropriate entry into your AppController::$helpers array, if you want
to apply it to absolutely all output.

If you still don't understand, try this:


Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
        var $helpers = array('TidyFilter', '...some of your other helpers...');
    }
    ?>

Everything will now work automatically.

Happy baking!

.. _http://php.net/tidy: http://php.net/tidy
.. meta::
    :title: Tidy Output Filtering
    :description: CakePHP Article related to tidy,Tutorials
    :keywords: tidy,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

