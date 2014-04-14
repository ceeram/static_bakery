Redirects with Ajax
===================

by sunertl on December 17, 2006

Redirects with Ajax are not really simple to do. When you redirect
within the same controller, you can use setAction() instead but what
if you want to redirect to another controller? I figured out a quite
simple way using sessions, postet it - and got back the IMHO perfect
solution by gwoo.
Cakebaker already wrote two times about redirecting with Ajax:
`http://cakebaker.42dh.com/2006/03/15/redirect-with-
ajax/`_`http://cakebaker.42dh.com/2006/03/28/a-simple-redirect-
component/`_ He does it using a client-side redirect with javascript.
I don't like client-side solutions as it is something I cannot
controll. I figured out a way using session (storing the desired
layout in a session, then redirect and load the layout according to
the stored layout) but gwoo showed me a much more efficient way:

Simply override the default redirect-method in the appcontroller by
using:


Controller Class:
`````````````````

::

    <?php 
    function redirect($url, $status = null){
        $ajax = ($this->RequestHandler->isAjax())
        ? ($url{0} != '/') ? '/ajax/' : '/ajax' : null;
        parent::redirect($ajax.$url, $status);
    }
    ?>

you are done!

Be careful if this solution conflicts in any way with your custom
routings. But in most cases it will work perfektly.

using this way does not even require you to change any code written
already :-).

.. _http://cakebaker.42dh.com/2006/03/15/redirect-with-ajax/: http://cakebaker.42dh.com/2006/03/15/redirect-with-ajax/
.. _http://cakebaker.42dh.com/2006/03/28/a-simple-redirect-component/: http://cakebaker.42dh.com/2006/03/28/a-simple-redirect-component/
.. meta::
    :title: Redirects with Ajax
    :description: CakePHP Article related to redirect,Snippets
    :keywords: redirect,Snippets
    :copyright: Copyright 2006 sunertl
    :category: snippets

