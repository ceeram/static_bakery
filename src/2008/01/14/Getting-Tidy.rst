Getting Tidy.
=============

5 minutes job to get a fair markup validation implant.

"Beauty shall save the World", Fyodor said once. It's that simple and
one can hardly disagree after thinking it over. That is true
apparently for one reason only - you let it in right at the moment you
sense a beautiful thing because you just know that whoever the creator
was he knew what he'd been doing.

Every time I find myself researching a certain web site the next thing
I do right after getting visual and clicking back and forth is getting
to the source. And, gees, it can tell you quite a lot on the
underlying "extreme programming" used. Those ugly chunks of code
brought together by rotten tables, those unquoted attributes,
capitalized tag names and unescaped chars ennobled by a smart ass
xhtml DOCTYPE at the top of the page. The power of mighty cut-n-paste,
and it feels like the server merely spits out every response in
disgust.

They need some tidy medicine, well, at least.

Where is that point we should mount our Tidy? Obviously, it's right
before all the mess is sent to the browser. So, we get into
app/webroot/index.php as it is in the standard Cake distro. At the end
of the script there is the following:

::

    
    } else {
        $Dispatcher=new Dispatcher();
        $Dispatcher->dispatch($url);
    } 
    
    if (DEBUG) {
        echo "<!-- " . round(getMicrotime() - $TIME_START, 4) . "s -->";
    }


The Dispatcher::dispatch() method sends the final markup back to my
Firefox. So, to get our job done we have to intercept, tidyfy and
serve the result, in that order.

We collect the final output in a buffer then process the collected
stuff, then, yes, just echo it.

::

    
    } else {
        $Dispatcher=new Dispatcher();
    		 
        ob_start();
        $Dispatcher->dispatch($url);
        $output = ob_get_clean();
    		 
        if (function_exists('tidy_parse_string') && TIDY_OUTPUT)
        {
            $tidy_config = array('indent' => true, 'indent-spaces' => 2, 'output-xhtml' => true, 'wrap' => 200);
    		 		    
            $tidy = tidy_parse_string($output, $tidy_config, 'utf8');
            $tidy->diagnose();
            $output = tidy_get_output($tidy);
            if (tidy_get_error_buffer($tidy))
            {
                $output .= "<div style=\"border:2px solid red;\">".htmlspecialchars(tidy_get_error_buffer($tidy))."</div>";
            }
    		    
        }		 
    
        echo $output;
    }
    
    if (DEBUG) {
        echo "<!-- " . round(getMicrotime() - $TIME_START, 4) . "s -->";
    }



Comments.
`````````

#. We have to have a switch to be able to turn Tidy on/off. You may
   implement that the way you see fit, as for me I'm gonna use the
   TIDY_OUTPUT constant which I declare in app/config/core.php, at the
   end of the script like this:


Conclusion.
```````````

There are powerful and accurate validators and beautyfiers on the
market out there, I know, as well as that some people report that Tidy
doesn't follow the official DTDs thorougly, but the fact is that it is
simple and very useful.

1. I use tidy basically for validation, to instantly make sure that
the markup is well-formed and valid to a certain degree. It's xhtml
that is being served, after all.

2. The use of online validators does no good for pages that require
authorization that you have planted in your web application.

3. Tidy extension is available in most php builds, so make sure to
check phpinfo() for Tidy entries, and if it is not there try to enable
it in php.ini

4. As per shared hosting, it might not be available on your host, so
ask the support staff.

5. It happened to me once that Tidy extension was available at my
hosting provider TextDrive, but was somehow misconfigured, so I was
getting 500 headers. In that case I had to forget about using Tidy
there, because they were unable to resolve the matter. But at the
localhost, hey, I do what I need.

6. There are limits, of course, just remember one thing - I've been
talking about serving xhtml as a final document. Therefore, if an
action of some controller of yours is intended for ajax requests, you
should probably use different configuration for tidy parser, and not
use tidy at all for responses for which you serve something like a
dynamic binary content (pictures, archives etc.). Implementing some
kind of a dynamic switching based on the content served is left to
you.

Now, get Tidy on and you'll find out a bunch more about the meal you
serve to your visitors.

Later.


.. author:: zeRUS
.. categories:: articles, tutorials
.. tags:: markup,xhtml,tidy,Tutorials

