Adding friendly URLs to The Cake Blog Tutorial
==============================================

On this tutorial we'll learn how to modify The Cake Blog Tutorial to
allow friendly URLs when accessing posts.


Introduction
~~~~~~~~~~~~
How many times you wondered how great it would be if your URLs didn't
look so much like:

`http://www.server.com/posts/view/1058`_
But more like:

`http://www.server.com/posts/view/my_first_post`_
Jeez even the bakery could use something like this :)

On this article I'm going to show you how easy it is to make your
model records be accessed by friendly URLs. In fact, we're going to
modify CakePHP's popular `The Cake Blog Tutorial`_ to allow friendly
URLs.


Modify your Table
~~~~~~~~~~~~~~~~~
The first thing we'll need to do is to add a field called url that
will hold a unique value for each post. On the blog tutorial you can
see that we have a table called posts with several fields. Run your
database administration and issue the following command:

::

    ALTER TABLE `posts` ADD `url` VARCHAR(255) NOT NULL AFTER `id`;

We just added a field called `url` after field `id`.


Create your AppModel
~~~~~~~~~~~~~~~~~~~~
The next step will be to add the logic on the model to allow the
automatic creation of a unique value for the field url based on the
post title.

Since we may want to add friendly URLs to other models we're going to
insert the appropiate methods to our AppModel, rather than adding it
to the Posts model. So create a file named app_model.php on your /app
directory. It should look like this:


Model Class:
````````````

::

    <?php 
    class AppModel extends Model
    {
    	function getUniqueUrl($string, $field)
    	{
    		// Build URL
    		
    		$currentUrl = $this->_getStringAsURL($string);
    		
    		// Look for same URL, if so try until we find a unique one
    		
    		$conditions = array($this->name . '.' . $field => 'LIKE ' . $currentUrl . '%');
    		
    		$result = $this->findAll($conditions, $this->name . '.*', null);
    		
    		if ($result !== false && count($result) > 0)
    		{
    			$sameUrls = array();
    			
    			foreach($result as $record)
    			{
    				$sameUrls[] = $record[$this->name][$field];
    			}
    		}
    	
    		if (isset($sameUrls) && count($sameUrls) > 0)
    		{
    			$currentBegginingUrl = $currentUrl;
    	
    			$currentIndex = 1;
    	
    			while($currentIndex > 0)
    			{
    				if (!in_array($currentBegginingUrl . '_' . $currentIndex, $sameUrls))
    				{
    					$currentUrl = $currentBegginingUrl . '_' . $currentIndex;
    	
    					$currentIndex = -1;
    				}
    	
    				$currentIndex++;
    			}
    		}
    		
    		return $currentUrl;
    	}
    	
    	function _getStringAsURL($string)
    	{
    		// Define the maximum number of characters allowed as part of the URL
    		
    		$currentMaximumURLLength = 100;
    		
    		$string = strtolower($string);
    		
    		// Any non valid characters will be treated as _, also remove duplicate _
    		
    		$string = preg_replace('/[^a-z0-9_]/i', '_', $string);
    		$string = preg_replace('/_[_]*/i', '_', $string);
    		
    		// Cut at a specified length
    		
    		if (strlen($string) > $currentMaximumURLLength)
    		{
    			$string = substr($string, 0, $currentMaximumURLLength);
    		}
    		
    		// Remove beggining and ending signs
    		
    		$string = preg_replace('/_$/i', '', $string);
    		$string = preg_replace('/^_/i', '', $string);
    		
    		return $string;
    	}
    }
    ?>

The method _getStringAsURL() converts a string to a friendly URL form.
For example, running:

::

    _getStringAsURL('Hello CakePHP baker, baking hard?');

Will be transformed into:

::

    hello_cakephp_baker_baking_hard

The method getUniqueUrl takes two parameters:


#. $string : the string that will be used to generate the URL. On our
   case this is the post title.
#. $field : the field that will hold the generated URL. On our case
   this is url.

It will start by generating the friendly URL version of the post title
and then look over the table to see if the generated URL was assigned
to another record. If so, it will add _1, _2, _3, etc. until it finds
a unique version.

It is important to know that we will only generate a friendly URL when
the post is being inserted to the database, not when it is being
modified. This is a common procedure on friendly URL generation since
you never know if you already have incoming links to the generated
URL.


Modify your Model
~~~~~~~~~~~~~~~~~
Now we are ready to modify the Post model to allow the creation of a
friendly URL when inserting a new post. As `The Cake Blog Tutorial`_
shows the latest version of the file /app/models/post.php looked like
this:


Model Class:
````````````

::

    <?php 
    class Post extends AppModel
    {
    	var $name = 'Post';
    	
    	var $validate = array(
    		'title'  => VALID_NOT_EMPTY,
    		'body'   => VALID_NOT_EMPTY
    	);
    }
    ?>

Change it so we can add the URL generation. It should now look like
this:


Model Class:
````````````

::

    <?php 
    class Post extends AppModel
    {
    	var $name = 'Post';
    	
    	var $validate = array(
    		'title'  => VALID_NOT_EMPTY,
    		'body'   => VALID_NOT_EMPTY
    	);
    	
    	function beforeSave()
    	{
    		if (empty($this->id))
    		{
    			$this->data[$this->name]['url'] = $this->getUniqueUrl($this->data[$this->name]['title'], 'url');
    		}
    		
    		return true;
    	}
    }
    ?>

As you can see we just added a method called beforeSave() , which is a
function that CakePHP automatically calls before saving a model
instance to the database. There, we start by checking that the ID for
the record has not been set. This is the case when inserting a new
post. We then set the value of the url field to be the friendly URL
version of the value of the field title.

Now, every time a new post is being inserted to your database a unique
friendly URL will be generated.


Modify your View
~~~~~~~~~~~~~~~~
The next step is to modify the way we are building the links to each
post. Edit your file /app/views/posts/index.thtml and look for the
following expression:

::

    echo $html->link($post['Post']['title'], "/posts/view/".$post['Post']['id']);

Change it to:

::

    echo $html->link($post['Post']['title'], "/posts/view/".$post['Post']['url']);



Modify your Controller
~~~~~~~~~~~~~~~~~~~~~~
Last but not least we need to change our controller so it will receive
the URL rather than the ID of the post the user is trying to access.
Edit your file /app/controllers/posts_controller.php and look for the
following block of code:

::

    function view($id = null)
    {
    	$this->Post->id = $id;
    	$this->set('post', $this->Post->read());
    }

Change this code to look like this:

::

    function view($url)
    {
    	$post = $this->Post->findByUrl($url);
    	
    	$this->set('post', $post);
    }



Feedback
~~~~~~~~
If you have any comments / questions try to add them (if you think
they'll add value to other bakers) as comments on this page. If you
want to contact me directly try:

email: `mariano@cricava.com`_ blog:
`http://www.marianoiglesias.com.ar`_
Otherwise just drop a question on `Cake's Google Group`_ mentioning
this tutorial on the subject since I am constantly reading/writing on
the group.

Got your `CakeSchwag`_? I bought myself the `Baseball Jersey`_ and the
`Khaki Cap`_. I have to wait till December 29 for them to arrive (I
asked a friend from the states to buy them and bring it to me down
here... Argentina is a long way from the US.) What are you waiting
for?

Remember, smart coders answer ten questions for every question they
ask. So be smart, be cool, and share your knowledge.
BAKE ON

.. _mariano@cricava.com: mailto:mariano@cricava.com
.. _CakeSchwag: http://www.cafepress.com/cakefoundation
.. _http://www.server.com/posts/view/my_first_post: http://www.server.com/posts/view/my_first_post
.. _http://www.marianoiglesias.com.ar: http://www.marianoiglesias.com.ar/
.. _Cake's Google Group: http://groups.google.com/group/cake-php
.. _Baseball Jersey: http://www.cafepress.com/cakefoundation.45920086
.. _Khaki Cap: http://www.cafepress.com/cakefoundation.45920090
.. _The Cake Blog Tutorial: http://manual.cakephp.org/appendix/blog_tutorial
.. _http://www.server.com/posts/view/1058: http://www.server.com/posts/view/1058

.. author:: mariano
.. categories:: articles, tutorials
.. tags:: model,url,blog,post,friendly,seo,beforeSave,Tutorials

