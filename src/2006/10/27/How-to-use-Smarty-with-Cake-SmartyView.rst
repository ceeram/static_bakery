How to use Smarty with Cake (SmartyView)
========================================

The SmartyView package sits here:
[url]http://cakeforge.org/snippet/detail.php?type=snippet=6[/url] But
without some significant baking-knowledge, new users may not know how
to incorporte SmartyView. Here's how.


Steps
~~~~~
1. Download a version of SmartyView from here:
`http://cakeforge.org/snippet/detail.php?type=snippet=6`_

#. Save SmartyView as /(app)/views/smarty.php



2. Download Smarty
``````````````````
`http://smarty.php.net/download.php`_ Extract tarball so
Smarty.class.php sits at /vendor/smarty/Smarty.class.php


3. Create "smarty/compile" and "smarty/cache" within your tmp dir
`````````````````````````````````````````````````````````````````
(/(app)/tmp/ by default) and make them writable by the apache user


4. Include in your controller
`````````````````````````````
(in app_controller.php to do it app-wide) to use Smarty templates and
helpers.
You now have SmartyView installed!

Notes:
~~~~~~

#. /views/smarty_templates is parsed for smarty plugins :
   `http://smarty.incutio.com/?page=SmartyPlugins`_
#. SmartyView will fall back to .thtml templates for views.
#. [li]By default Smarty views must reside in 'smarty' subdirectories
   -- see addendum for information regarding removing this requirement.



Examples:
~~~~~~~~~
Without Smarty Helpers all helpers are available through:

::

    {$helper->method()}

ex:

::

    {$html->input('User/username')}

Smarty doesn't allow for 'array(' in the templates so to use calls
requiring them you have to use a plugin to assign arrays as well (such
as `http://cakeforge.org/snippet/detail.php?type=snippet=13`_ or
`http://smarty.incutio.com/?page=AdvancedAssignPlugin`_).
This ends up looking like this (using array_assoc from cakeforge):

::

    {assign_assoc var='ArrayName' value='size=>40,class=>classname'}
    <p>Title: {$html->input('Post/title', $ArrayName)}</p>

Alternatively, you can use the Smarty Helpers
`http://bakery.cakephp.org/articles/view/138`_. The Smarty Helpers are
Smarty wrappers for existing helpers so you can call helper methods
'natively' in smarty. Note that many helper methods have not been
wrapped yet - the current code serves as an example.

With Smarty Helpers all helpers are available in native Smarty style:

::

    	{html func=css path="cake.generic"}
    	{javascript func=link url="lib/prototype"}

::

    	{html func=url url="/admin/logout"}
    	{html func=input fieldName=User/username class="test" size=30 }

For html func=input above, fieldName is required while all other
parameters are passed as $htmlAttributes.

Enjoy!


Addendum:
`````````

note:

This line within the constructor ( __construct ) :

`$this->subDir = 'smarty'.DS;` requires view templates to be within a
subdirectory (i.e. /app/views/posts/smarty/index.tpl)

Commenting this line removes this requirement.

This will not cause problems unless you are using another inherited
view class that uses '.tpl' as its extension.


.. _=13: http://cakeforge.org/snippet/detail.php?type=snippet&id=13
.. _=6: http://cakeforge.org/snippet/detail.php?type=snippet&id=6
.. _http://smarty.php.net/download.php: http://smarty.php.net/download.php
.. _http://bakery.cakephp.org/articles/view/138: http://bakery.cakephp.org/articles/view/138
.. _http://smarty.incutio.com/?page=AdvancedAssignPlugin: http://smarty.incutio.com/?page=AdvancedAssignPlugin
.. _http://smarty.incutio.com/?page=SmartyPlugins: http://smarty.incutio.com/?page=SmartyPlugins

.. author:: tclineks
.. categories:: articles, tutorials
.. tags:: smartyview,smarty,Tutorials

