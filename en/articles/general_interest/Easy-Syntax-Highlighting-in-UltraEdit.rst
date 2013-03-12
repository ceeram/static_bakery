

Easy Syntax Highlighting in UltraEdit
=====================================

by %s on September 15, 2007

UltraEdit was my answer for enabling .thtml syntax highlighting
without hassle. Change two lines in the UltraEdit config. file, and
.thtml files rely on previous settings for html and PHP highlighting.
Sweet!


Requirements
~~~~~~~~~~~~
All you need is Windows and UltraEdit - `http://www.ultraedit.com/`_

Enjoy THTML syntax highlighting in minutes!
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
To edit the configuration file:
1) open UltraEdit and go to Advanced > Configuration.
2) Under Editor Display , select syntax highlighting
3) Next to "Full Path Name for word list" click open.
4) Click "Ok" (The config. file will open in the background)

Now edit two lines, save, and enjoy!

*Please note that I have broken up the code for easy reading; the code
is on one line in the live config. file.
Around line 225, add THTML as an extension that will use html syntax
highlighting (code to add is bold ):

::

    
    /L3"HTML" HTML_LANG Nocase Noquote Block Comment On = 
    <!-- Block Comment Off = --> File Extensions = 
    HTM HTML SHTML HTT HTA HTX CFM JSP PHP PHTML ASP THTML

Around line 830, add THTML as an extension that will use PHP syntax
highlighting (code to add is bold ):

::

    
    /L8"PHP" PHP_LANG Nocase EnableMLS Line Comment = 
    // Line Comment Alt = # Block Comment On = /* Block Comment Off = */
    Escape Char = \ String Chars = "' File Extensions = INC PHP3 PHP4 THTML



Follow Up
~~~~~~~~~
Go back to the configuration settings and change default syntax
highlighting for HTML and PHP if you like. I don't care for some of
the default highlighting in UltraEdit.

Also note that PHP code inside HTML attributes will highlight as the
default attribute value color for HTML (grey by default)


.. _http://www.ultraedit.com/: http://www.ultraedit.com/
.. meta::
    :title: Easy Syntax Highlighting in UltraEdit
    :description: CakePHP Article related to thtml,syntax highlighting,UltraEdit,General Interest
    :keywords: thtml,syntax highlighting,UltraEdit,General Interest
    :copyright: Copyright 2007 
    :category: general_interest

