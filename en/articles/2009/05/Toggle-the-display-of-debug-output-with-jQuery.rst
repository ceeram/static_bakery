Toggle the display of debug output with jQuery
==============================================

by %s on May 04, 2009

The problem:
I love having the debug output enabled, but I don't always need it. I
also find that with pages with a lot of queries, my CSS and design
gets all messed up when debug output is displayed. What I really would
like is to have the debug always on but hidden by default. I would
like a link at the bottom of each page allowing me to toggle the
display of the debug section on or off.
My solution:
I accomplished this very quickly using jQuery and I will show you how
below. It should only take you a few minutes, and really makes a
difference.

First you need to install jQuery. You can download the latest version
on the `jQuery website`_. You will want to include it in your
layout.ctp of your app.

Edit your layout.ctp file as show below.

::

    
    <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <script type="text/javascript" src="/webroot/js/jquery-1.3.2.min.js"></script>
        <style type="text/css">
    
           /* Hide debug statements */
           table.cake-sql-log {
        	display:none;
           }
    
        </style>
    <script type="text/javascript">
    $(document).ready(function() {
    
        // Toggle debug
        $(".cake-sql-log").before("<a href='#' class='toggle-cake-sql-log'>Debug</a>");
        $(".toggle-cake-sql-log").click( function() {
               $(".cake-sql-log").toggle();
               return false;
        });
    
    });
    </script>
        </head>
    ...

Notes:


+ Make sure you have debug set to a 2 or higher in your core.php
  config file.



.. _jQuery website: http://jquery.com/
.. meta::
    :title: Toggle the display of debug output with jQuery
    :description: CakePHP Article related to toggle,debug,hide,jquery,queries,dump,Snippets
    :keywords: toggle,debug,hide,jquery,queries,dump,Snippets
    :copyright: Copyright 2009 
    :category: snippets

