Text Formatting
===============

So I created a component awhile back for Textile. Turns out, it would
be a little friendlier to just dump it in a behavior and let the model
define fields that need to be saved with a textile'd version. It
assumes that the textile'd fieldname has html_ added to it. This also
has options for
In your model:

::

    
    var $actsAs = array('Textile' => array('fields' => array('body'=>'textile', 'excerpt'=>'markdown', 'text'=>'plain'), 'restricted'=>true));

What this means is that if you have a field called "body" it assumes
there is a field called 'html_body".

If restricted is set (it's optional) Textile-formatted fields will be
processed using the more restrictive Textile formatting
(rel="nofollow", no images, etc). This is best for content coming from
"unknown" sources.

If you're using this for a content management system, let the user
edit the non-textile'd version, and then, whenever they save a record
for that model, it Textile's the columns defined in the actsAs var.

When you're outputting for viewers, echo the html_ version for easy
and more efficient rendering.

You can download the code here:
`http://code.google.com/p/caketextbehavior/`_

.. _http://code.google.com/p/caketextbehavior/: http://code.google.com/p/caketextbehavior/

.. author:: walker
.. categories:: articles, behaviors
.. tags:: ,Behaviors

