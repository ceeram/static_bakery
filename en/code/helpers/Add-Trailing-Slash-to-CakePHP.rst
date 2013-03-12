

Add Trailing Slash to CakePHP
=============================

by %s on June 17, 2008

How to add a trailing slash to URLs in CakePHP using .htaccess and app
helper.
I wanted to add a trailing slash to URLs in CakePHP. Why?

+ There is speculation that it matters for SEO to have the trailing
  slash. For instance, Wordpress still has a trailing slash on URLs,
  which that community is obsessive with SEO practices so I look to them
  as a sort of de-facto standard.
+ I wanted CakePHP to do my bidding and learn in the process about URL
  handling

In my app_helper.php, I over wrote the url function to check to see if
there already is a trailing slash, or there is a file extension and
then added the trailing slash if not.

Helper Class:
`````````````

::

    <?php <?php
    class AppHelper extends Helper {
    	function url($url = null, $full = false) {
    		$routerUrl = Router::url($url, $full);
    		if (!preg_match('/\\.(rss|html|js|css|jpeg|jpg|gif|png|xml?)$/', strtolower($routerUrl)) && substr($routerUrl, -1) != '/') {
    			$routerUrl .= '/';
    		}
    		return $routerUrl;
    	}
    }
    ?>?>

You may need to add more file extensions if you use them.

I also added 301 redirects to include the trailing slash on URL
requests in my app level .htaccess

::

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} !(\.[a-zA-Z0-9]{1,5}|/)$
    #RewriteRule ^(.*)$ http://domain.com/$1/ [L,R=301]
    RewriteRule ^(.*)$ http://domain.com/$1/ [L,R=301]

You will need to change domain.com to your domain.

Whether or not it matters to add the trailing slash is speculative,
however if you want to this is how I made it work.

.. meta::
    :title: Add Trailing Slash to CakePHP
    :description: CakePHP Article related to ,Helpers
    :keywords: ,Helpers
    :copyright: Copyright 2008 
    :category: helpers

