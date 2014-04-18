Automagic RSS Feed Generator Plugin
===================================

Automatically create RSS feeds for your CakePHP app.
A .zip of the plugin is available at
`http://sandbox.siteamonth.com/demo/rss`_
1. Download FeedCreator
1.7.2-ppt(`http://sourceforge.net/projects/feedcreator/`_) and put the
feedcreator.class.php file in /app/vendors/feedcreator.

2. Download the plugin and unzip the contents to app/plugins. The
plugin is called "rss", so make sure there is no conflict with any
other controllers/plugins

3. Add this to your /app/config/routes.php:

::

    $Route->connect('/rss/*', array('controller' => 'rss', 'action' => 'feed'));

4. You have to allow permission for the plugin to generate a feed for
a particular model. Edit your model to include:

::

    var $feed = true;

5. The plugin will attempt to find default values for the feed.
If your model includes a field 'title' or 'name' that will be used
automatically for the item title.
The fields 'desc', 'description', 'text', 'content' or 'body' will be
used for the item description.
The link will be '/{$model}/view/{$model->primaryKey}' by default.
The results will be ordered by 'created' => 'DESC' by default.
The limit is set to 10 by default.
You can override any of these in the model, as well as set conditions
for which rows are returned, by replacing the line used in step #5
with:

::

    var $feed = array(
        'conditions' => array('Post.active' => true),
        'titleField' => 'Post.name',
        'descField' => 'Post.desc',
        'link' => '/post/view/%s',
        'orderby' => array('Post.created' => 'DESC'),
        'limit' => 10
    );

6. You reach the feed using both the singular and plural model name
(post or posts).

::

    http://yourapp/rss/modelname

or

::

    http://yourapp/rss/modelnames



.. _http://sandbox.siteamonth.com/demo/rss: http://sandbox.siteamonth.com/demo/rss
.. _http://sourceforge.net/projects/feedcreator/: http://sourceforge.net/projects/feedcreator/

.. author:: mattc
.. categories:: articles, plugins
.. tags:: Rss,rss feed generator,Plugins

