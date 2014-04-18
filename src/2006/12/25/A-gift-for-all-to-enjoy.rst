A gift for all to enjoy
=======================

Everyone loves gifts. So, we have CakePHP 1.1.12.4205[1] and
1.2.0.4206_dev[2] packaged and available on CakeForge. CakePHP
1.1.12.4205 is a bug fix release for the stable branch. Read the
changelog[3] for more information on what has been updated. CakePHP
1.2.0.4206_dev is the next major version with several new features.
This is a development release so not all the features are finalized
and additional changes are almost guaranteed. We have been running the
Bakery and most of the CakePHP sites on the 1.2 branch, and have also
heard of some people using it with some success.
As for CakePHP 1.2.0.4206_dev, there is not much in the way of
documentation yet. We will have some updates as soon as we can. The
best we can do for now is the API [4]. We appreciate any help you can
lend to help us get the docs up to speed.

Here are the new features in CakePHP 1.2

Validation: There is a new Validation class that replaces the old
Validators.

FormHelper: Is much improved and extended. Bake yourself some new
views using the latest code.

EmailComponent: send some emails in plain text, html, or both.

SecurityComponent: now supports HTTP_AUTH through var $requireLogin.

CTP: We are deprecating the ole ".thtml" in favor of ".ctp". This will
serve as the template for any type of content, whether it be xhtml,
xml, rss, etc.

Pagination: check out the paginate method in the Controller class and
the PaginatorHelper. Its pretty simple. try using $this->paginate();
instead of $this->Model->findAll();, then $paginator->next() in the
view. The PaginatorHelper is automatically added when you use
paginate().

Url extensions: specifying the content type of the request is easy
with Router::parseExtensions() added to /app/config/routes.php and the
RequestHandler. Things like XML and RSS can be added without changing
your controller code. Views are mapped to /app/views/ / / .ctp

Model Behaviors: A behavior is something that can help you handle your
data. Similar to a component helps out the controller, now you can use
behaviors to extend the functionality of the model layer. Inside,
/app/models/behaviors create your file. For example, lets take the new
ListBehavior (coming soon). we have a class ListBehavior extends
ModelBehavior in /app/models/behaviors.php. This class has a
setup(&$model, $config) method that will pass a instance of the model
and some config data.

Datasources: The database is not the only place you might store data.
So, having multiple datasources will allow you to access them through
model methods. You can build custom datasources. We will have a
skeleton class up shortly of how to do this, or take a look at
dbo_source to get an idea of whats needed.

i18N and l10N: Some of you have contributed to helping us translate
the core, so you already have a leg up in this regard. I need to write
more on this but I dont know what to write. Its mostly automagic, just
use the __() method to wrap your static text. Then put some po in the
locale and get jiggy with it. You can see it in action now[5], the
site should display in the language your browser sends through
HTTP_ACCEPT_LANGUAGE if we have the translation. You can see the
current languages we have[6]. If you would like to translate to a
language we do not currently have you can use the default file[7]
These are most of the major features. There are many other
improvements to make you life easier. The best thing to do is start
playing around.

[1] Stable 1.1.12.4205:
`http://cakeforge.org/frs/?group_id=23_id=170`_ [2] Development
1.2.0.4206: `http://cakeforge.org/frs/?group_id=23_id=171`_ you will
need to scroll to find the highlighted release
[3] 1.1.12.4205 changelog
`https://trac.cakephp.org/wiki/changelog/1.1.x.x`_ [4] 1.2.0.4206 API
`http://api.cakephp.org/1.2/`_ [5] `http://translation.cakephp.org/`_
[6] `http://translation.cakephp.org/languages`_ [7] `https://svn.cakep
hp.org/repo/branches/1.2.x.x/cake/locale/default.pot`_
Merry Christmas,
CakePHP Development Team.

.. __id=170: http://cakeforge.org/frs/?group_id=23&release_id=170
.. __id=171: http://cakeforge.org/frs/?group_id=23&release_id=171
.. _http://api.cakephp.org/1.2/: http://api.cakephp.org/1.2/
.. _http://translation.cakephp.org/: http://translation.cakephp.org/
.. _http://translation.cakephp.org/languages: http://translation.cakephp.org/languages
.. _https://svn.cakephp.org/repo/branches/1.2.x.x/cake/locale/default.pot: https://svn.cakephp.org/repo/branches/1.2.x.x/cake/locale/default.pot
.. _https://trac.cakephp.org/wiki/changelog/1.1.x.x: https://trac.cakephp.org/wiki/changelog/1.1.x.x

.. author:: PhpNut
.. categories:: news
.. tags:: release,news,cake gift,News

