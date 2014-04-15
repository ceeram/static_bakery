Introducing the cakephp-instaweb webserver
==========================================

by lamby on January 14, 2008

Save time and effort developing with this zero-configuration CakePHP-
oriented webserver.
cakephp-instaweb is tiny Python-based webserver suitable for quick
development of CakePHP applications, similar to Django's 'runserver'
and the "script/server" script in Ruby on Rails.

It requires zero configuration for running CakePHP applications,
allowing you to:


+ delay webserver configuration to deployment time
+ develop multiple applications simultaneously with ease
+ just get on building your application

Usage is simple. Simply type:

::

     % cakephp-instaweb

somewhere inside a CakePHP application. The server will then start and
start serving requests at http://localhost:3000/. It will additionally
respond with some logging information:

::

          CakePHP development server is running at http://localhost:3000/
          Quit the server with CONTROL-C.
            
    [04/Jan/2008 11:58:06] "GET /"
    ...

You do not need be in the root directory of the CakePHP application -
cakephp-instaweb will perform a fuzzy search both up and down the
filesystem to locate your application. Alternatively, you can request
a specific directory as a command-line parameter.

Multiple applications can be developed simultaneously by running
additional instances of cakephp-instaweb - just specify a specific
port to run the additional servers on:

::

     % cakephp-instaweb -p 3001

cakephp-instaweb has some other options, including disabling rewrites
and specifying the listening interface.

::

    $ cakephp-instaweb --help
    usage: cakephp-instaweb [webroot]
    
    options:
      -h, --help            show this help message and exit
      -p PORT, --port=PORT  serve on port PORT (default: 3000)
      -i INTERFACE, --interface=INTERFACE
                            interface to serve from (default: 127.0.0.1)
      -r, --disable-rewrite
                            disable URL rewriting
      -q, --quiet           quiet mode

cakephp-instaweb does not ship with CakePHP. However, it is available
in Debian and Ubuntu, or can be downloaded straight from Git:
`http://git.chris-lamb.co.uk/?p=cakephp-instaweb.git`_. It is released
under the terms of the MIT license, the same license as CakePHP
itself.

.. _http://git.chris-lamb.co.uk/?p=cakephp-instaweb.git: http://git.chris-lamb.co.uk/?p=cakephp-instaweb.git

.. author:: lamby
.. categories:: articles, tutorials
.. tags:: apache,Webserver,hosting,Tutorials

