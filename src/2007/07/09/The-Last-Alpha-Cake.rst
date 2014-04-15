The Last Alpha Cake
===================

by PhpNut on July 09, 2007

We have some new versions ready. As usual, 1.1.x.x branch gets a bug-
fix update, while 1.2.x.x branch sees some new features along with the
bug-fixes.
Major Changes:

AuthComponent:
The authorization methods have changed. To tell Auth what to do, set
$this->Auth->authorize in your beforeFilter. Possible values include,
false, controller, model, actions, crud. False will turn off
authorization all together and you will just be using the provided
Authentication. This is useful if you want to give every user the same
access level. Setting authorize to controller requires you controller
to have an isAuthroized method that returns true or false. If you set
authorize to 'model' you will set $this->Auth->objectModel to the
object you wish to authorize against. This object should have an
isAuthorized method. You can also set authorize to
array('model'=>'objectToAuthorize') where objectToAuthorize is the
name of the object. These three methods do not require the use of an
Acl component. The default setting for authorize is false.

If you set authorize to actions or crud, Auth will requires you have
the AclComponent set in your controller's components array. You can
use the core Acl, with DB_ACL or INI_ACL, by setting the proper
classname in core.php or setting $this->Acl->name in the beforeFilter.
DB_ACL is used by default. You can also have your own component by
setting Acl->name equal to the name of you custom component or
defining it in core.php. You custom component should extend AclBase
and have a check method that will receive an aro, aco, and action.

EmailComponent:
SMTP has been added and you can now set the template and layout in the
send method.

Bake Console:
Templates have been added for views. You can create your own and place
them in /vendors/shells/templates/views. Check out the core ones to
see what is possible.
You can also quickly bake models with 'bake model Post'. For now this
will just output the class without validation or associations.

HtmlHelper:
radio method is deprecated in favor of FormHelper::radio

The rest of the changes to 1.2.x.x come in the form of bug fixes. The
1.2.x.x line of code is stabilizing and the next release will be beta.
We are going to have to work hard to get through all enhancements, and
how soon we can release beta will be based on how many tickets come in
and the complexity of the enhancements. This is where you can really
help us out. We are asking that any tickets for 1.2.x.x be submitted
with tests. This especially holds true for a class in the core that
already has tests written for it. Creating a test will help us find
the problem faster and we can be sure we duplicate your error. The
test will also help you debug and create a patch.

If you plan on upgrading to 1.2.0.5427alpha, please review all the
past release announcements. There is a lot of information in there to
help you understand the new features and changes that were made.

Download from CakeForge
1.1.16.5421: `http://cakeforge.org/frs/?group_id=23_id=294`_
1.2.0.5427alpha: `http://cakeforge.org/frs/?group_id=23_id=295`_
Read the Changelogs on Trac
1.1.16.5421: `https://trac.cakephp.org/wiki/changelog/1.1.x.x`_
1.2.0.5427alpha: `https://trac.cakephp.org/wiki/changelog/1.2.x.x`_
Happy Baking.

.. _https://trac.cakephp.org/wiki/changelog/1.2.x.x: https://trac.cakephp.org/wiki/changelog/1.2.x.x
.. __id=295: http://cakeforge.org/frs/?group_id=23&release_id=295
.. __id=294: http://cakeforge.org/frs/?group_id=23&release_id=294
.. _https://trac.cakephp.org/wiki/changelog/1.1.x.x: https://trac.cakephp.org/wiki/changelog/1.1.x.x

.. author:: PhpNut
.. categories:: news
.. tags:: ,News

