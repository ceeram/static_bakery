Model-based code insight and completion in NetBeans
===================================================

by SymenTimmermans on January 21, 2009

As a lazy programmer I really like code completion and code insight.
It's the only thing I do like about Visual Studio. It somehow knows
all your classes, the ones they extend, all functions and properties
and their types...etc. PHP developers have also had this functionality
for a few years now, with Aptana, PHPEclipse or Zend Studio (I know
there's more). Unfortunately CakePHP binds models to controllers in a
way IDE's don't understand, without helping them a little. Let me show
you how to approach this in NetBeans.
I started using Netbeans (`http://www.netbeans.org/features/php/`_) a
while ago, because I was sick of the big footprint eclipse-based IDE's
have. Aptana was slowing me down.

So I started using NetBeans PHP and discovered that it has some great
features.

The only thing it doesn't do is complete your code in the controller
with model-based methods and properties. On the blog for Netbeans PHP
I yakked a bit about this (`http://blogs.sun.com/netbeansphp/entry/def
ining_a_variable_type_in#comment-1232115150000`_) and was directed to
this screencast: `http://blogs.sun.com/netbeansphp/entry/screencat_abo
ut_class_property_variables`_. Petr tells us how to achieve the wanted
functionality (he did forget a '$' in the definition, but hey, we all
make mistakes).

We can make this work for Cake. Let's say we have the following model:


Model Class:
````````````

::

    <?php 
    class Post extends AppModel {
        var $name = 'Post';
        var $belongsTo = array('Author' => array('className' => 'Author', 'foreignKey' => 'author_id'));
    }
    ?>

And this controller:


Controller Class:
`````````````````

::

    <?php 
    class PostsController extends AppController {
        var $name = 'Posts';
        function index() {
            $posts = $this->Post->find('all');
            $this->set('posts', $posts);
        }
    }
    ?>

When you type " $this->Post-> ", normally, you will not get a list of
functions we can call for the Post model or the parent AppModel.
We do want this.

We can do this by 'helping' NetBeans a little with 'Class property
variables':


Controller Class:
`````````````````

::

    <?php 
    /**
     * @property Post $Post
     */
    class PostsController extends AppController {
        var $name = 'Posts';
        function index() {
            $posts = $this->Post->find('all');
            $this->set('posts', $posts);
        }
    }
    ?>

See the new comment above the PostsController? This is telling
NetBeans our class has a property $Post with class Post (upon typing,
NetBeans automatically lists all classes found in your code base,
along with the filename where it is defined), which is used for code
completion and code hinting.

This works for controllers, but even better, it also works for models,
so you can link all relations in your model to the relevant model
class. If I would do this to the model in my example, you'd get this:



Model Class:
````````````

::

    <?php 
    /**
     * @property Author $Author
     */
    class Post extends AppModel {
        var $name = 'Post';
        var $belongsTo = array('Author' => array('className' => 'Author', 'foreignKey' => 'author_id'));
    }
    ?>

This allows me to get code completion and code hinting in the Posts
controller for the Author model.

::

    
    $this->Post->Author->...

And NetBeans would provide me with a list of all methods and
properties of the Author model and of course it's parent, AppModel.

You can go as deep as you want.
Check this screenshot of the app I'm working on:

`http://livesurvey.nl/files/NetBeansModelCompletion.jpg`_
I hope I have been clear enough... otherwise, let me know in the
comments.

I don't have enough experience with other IDE's to be able to tell you
if such a thing is possible in your preferred IDE, but I wouldn't be
surprised! In any way, discovering this has made my life a little
easier, so I thought I'd share!

Have Fun,

Symen Timmermans

.. _http://www.netbeans.org/features/php/: http://www.netbeans.org/features/php/
.. _http://blogs.sun.com/netbeansphp/entry/screencat_about_class_property_variables: http://blogs.sun.com/netbeansphp/entry/screencat_about_class_property_variables
.. _http://livesurvey.nl/files/NetBeansModelCompletion.jpg: http://livesurvey.nl/files/NetBeansModelCompletion.jpg
.. _http://blogs.sun.com/netbeansphp/entry/defining_a_variable_type_in#comment-1232115150000: http://blogs.sun.com/netbeansphp/entry/defining_a_variable_type_in#comment-1232115150000
.. meta::
    :title: Model-based code insight and completion in NetBeans
    :description: CakePHP Article related to ide,netbeans,code hinting,code completion,code insight,Tutorials
    :keywords: ide,netbeans,code hinting,code completion,code insight,Tutorials
    :copyright: Copyright 2009 SymenTimmermans
    :category: tutorials

