CakePHP 1.1.11.4064 thawed and redelivered better than before.
==============================================================

Well, its not often that you can take something out of the freezer and
have it taste better than before, but that's what happened. We
received a few more bugs and as Gwoo said in a message last week,
speed was an issue. So, with the bugs that came in, we spent some time
making things work a bit faster. While speed differences may vary
based on your system, we noticed a nice increase[1].
The main increase comes from the lazy loading of models. Previous
versions of CakePHP loaded all the models in the models directory.
This functionality made access to models easier, but came at the
expense of valuable processing time. The change results in a need to
make sure you loadModel('ModelName'); if you use $ModelName = new
ModelName() somewhere in your code. We have seen several examples,
particularly in reference to components creating model instances. This
was not recommended practice, so remember that you need to load the
model now if you plan on creating an instance.

At the same time we tried to clean up the doc blocs and found a few
things to deprecate moving forward. The main change is to _viewVars,
now we made it public as viewVars. For 1.1.11.4064 _viewVars is still
there, but 1.2.x.x will no longer have them, so you may need to keep
this in mind when upgrading once 1.2 is out of the oven.

The other nice addition in 1.1.11.4064 is updated functionality of the
Configure class. All core configuration is now handle by this class,
so inside your methods you can access Configure::read('debug'); for
the current setting. Also, Configure::write('debug', '0'); to change
the debug level for a specific method. Also, we added
Configure::version(); to get the latest version number.

Enjoy.

For more info on check out the changelog[2].
Release notes[3] To download head over to CakeForge [4]
[1] `http://cakephp.org/profile.png`_ [2]
`https://trac.cakephp.org/wiki/changelog/1.1.x.x`_ [3]
`https://trac.cakephp.org/wiki/notes/1.1.x.x`_ [4]
`http://cakeforge.org/frs/?group_id=23_id=164`_

.. _http://cakephp.org/profile.png: http://cakephp.org/profile.png
.. __id=164: http://cakeforge.org/frs/?group_id=23&release_id=164
.. _https://trac.cakephp.org/wiki/notes/1.1.x.x: https://trac.cakephp.org/wiki/notes/1.1.x.x
.. _https://trac.cakephp.org/wiki/changelog/1.1.x.x: https://trac.cakephp.org/wiki/changelog/1.1.x.x

.. author:: PhpNut
.. categories:: news
.. tags:: release,News

