BakeMe - Customized cakephp bake templates & application skeleton
=================================================================

by wouter on January 13, 2009

Bakeme is a cakephp application skeleton with customized bake scripts,
which enables you to create advanced, data-driven websites. Some of
the features are: - Pasword protected backend, based on users in the
database - Customized form helpers to add fckeditor input fields,
server side file browser fields, datepickers for date(time) fields,
autocompletion functionality instead of comboboxes (if you set
autocomplete to true using the extendedForm helper). - SoftDeletable
behavior, so database records arenâ€™t deleted instantly, but flagged
as deleted - More advanced code generation, to generate nicer looking
backends, based on your models - Option to bake all controllers
instantly (using cake bake controller all (admin)) - Option to bake
all views instantly (using cake bake view all) - View code generation,
to ajax sort records of models which have a field â€œorderâ€. -
Integration of cakeswxphp, which enables remoting on your website
through swx, amf or json - â€¦
Bakeme works with cakephp 1.2, you will need cakephp aswell:
`http://www.cakephp.org/`_.

Extract cakephp to your webhost. After that, you will need to extract
the files from bakeme to the correct directories. You can download
bakeme from `http://code.google.com/p/bakeme/`_
Extract the â€œtemplateâ€ and â€œvendorsâ€ folders from bakeme, to
the directory of you cakephp installation (the directory where you
have the folders app, cake and vendors).

You will need a basic database structure aswell. You can find an sql
dump of the database in the folder template/config/sql/database.sql.
This will create a database user â€œtemplateâ€, together with a
database and basic tables for bakeme (tables for users & groups to
enable authentication).

If everything goes well, you should be able to surf to the template
project: http://URL_OF_YOUR_CAKEPHP_ROOT/template/ and login to the
password protected section:
http://URL_OF_YOUR_CAKEPHP_ROOT/template/admin/ (username:
`wouter@aboutme.be`_, password: T3mpl@t3).

You can use the customized bake scripts from the template project. If
you run bake (the code generation utility from cakephp) from within
the template folder, it will use the scripts from
template/vendors/shells/â€¦ to generate code.

.. _wouter@aboutme.be: mailto:wouter@aboutme.be=wouter@aboutme.be
.. _http://www.cakephp.org/: http://www.cakephp.org/
.. _http://code.google.com/p/bakeme/: http://code.google.com/p/bakeme/

.. author:: wouter
.. categories:: articles, plugins
.. tags:: bake template fckedi,Plugins

