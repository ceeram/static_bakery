

Creating Reusable Elements with requestAction
=============================================

by %s on April 12, 2007

This is a brief tutorial on using requestAction to produce reusable
elements. Code samples for 1.1 and 1.2 are provided.
Creating reusable elements with requestAction is very simple. At the
end, we can even cache the element using the new feature in 1.2.

Start of with a simple controller.

Controller Class:
`````````````````

::

    <?php 
    class PostsController extends AppController {
        var $name = 'Posts';
    
        function index() {
            $posts = $this->Post->findAll();
            if(isset($this->params['requested'])) {
                 return $posts;
            }
            $this->set('posts', $posts);
        }
    }
    ?>

So, we created the Posts controller and we gave it an index action.
Then we found all the posts, then if requestAction is used it will
return the posts, otherwise it will set the posts for the view.

Now we can create a reusable element that will use requestAction. We
create a file in /app/elements/posts.thtml, then add the code below.

View Template:
``````````````

::

    
    $posts = $this->requestAction('posts/index');
    foreach($posts as $post):
        echo $post['Post']['title'];
    endforeach;

Now we have the element we can include it in the layout, another
element or a view template.

View Template:
``````````````

::

    
    <?php echo $this->renderElement('posts');?>

The code above is for 1.1. If you want to get into the goodness of
Cake 1.2 you can look at the updated code below.


Controller Class:
`````````````````

::

    <?php 
    class PostsController extends AppController {
        var $name = 'Posts';
    
        function index() {
            $posts = $this->paginate();
            if(isset($this->params['requested'])) {
                 return $posts;
            }
            $this->set('posts', $posts);
        }
    }
    ?>

Notice we changed the index action to use paginate instead.
In the view we can use paginate params to control what is returned.

View Template:
``````````````

::

    
    $posts = $this->requestAction('posts/index/sort:created/direction:desc/limit:10');
    foreach($posts as $post):
        echo $post['Post']['title'];
    endforeach;

Now we are only grabbing the latest 10 posts. Whoa, we just created a
latest posts element without having to change our index action or
create another action.

Lets call this one latest posts and cache it for an hour.
create the file /app/elements/latest_posts.ctp.

View Template:
``````````````

::

    
    <?php echo $this->element('latest_posts', array('cache'=>'+1 hour');?>

We can still use this element in any other view file.

There are other ways to use requestAction, but if you are looking to
create reusable elements for your site, then this is the way to go.




.. meta::
    :title: Creating Reusable Elements with requestAction
    :description: CakePHP Article related to requestAction,elements,intabox,Tutorials
    :keywords: requestAction,elements,intabox,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

