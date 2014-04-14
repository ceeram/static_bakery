Search feature to CakePHP blog example
======================================

by calin on September 18, 2008

Using searchable behavior, exemplified on CakePHP's blog example from
documentation.

Hello everybody!

First of all I assume that you have read and tried the blog example
form documentation `http://book.cakephp.org/view/219/blog`_).

Setup
`````

So to get things started, we need to download seachable behavior from
(`http://code.google.com/p/searchable-behaviour-for-cakephp`_). Then
we copy the archive contents to the /app folder. To complete the
installation process there is one more thing to do: create the search
table. So we run the following sql code:

::

    
    CREATE TABLE `search_index` (
    	`id` int(11) NOT NULL auto_increment,
    	`association_key` int(11) NOT NULL,
    	`model` varchar(128) collate utf8_unicode_ci NOT NULL,
    	`data` longtext collate utf8_unicode_ci NOT NULL,
    	`created` datetime NOT NULL,
    	`modified` datetime NOT NULL,
    	PRIMARY KEY  (`id`),
    	KEY `association_key` (`association_key`,`model`),
    	FULLTEXT KEY `data` (`data`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

So we are set up now, ready to add our nice search feature!

The actual search
`````````````````

First we need to add the searchable behavior to Post model.

Model Class:
````````````

::

    <?php 
    <?php
    // app/models/post.php
    class Post extends AppModel
    {
    	var $name = 'Post';
    	var $actsAs = array ('Searchable');
    
    	var $validate = array(
    		'title' => array(
    			'rule' => array('minLength', 1)
    		),
    		'body' => array(
    			'rule' => array('minLength', 1)
    		)
    	);
    }
    ?>
    ?>

To be able to search, we need to place a search box somewhere. Add the
following snippet of code just after the page heading.

::

    
    <?php 
    	echo $form->create("Post",array('action' => 'search'));
    	echo $form->input("q", array('label' => 'Search for'));
    	echo $form->end("Search");
    ?>

We need to define the search action in our post controller.

::

    
    <?php
    	function search() {
    		$this->set('results',$this->Post->search($this->data['Post']['q']));
    	}
    ?>

Finally we create the view for search results (which is just a
slightly modified version of posts index):

View Template:
``````````````

::

    
    <?php // app/views/posts/search.ctp ?>
    <h1>Blog posts</h1>
    <?php 
    	echo $form->create("Post",array('action' => 'search'));
    	echo $form->input("q", array('label' => 'Search for'));
    	echo $form->end("Search");
    ?>
    <p><?php echo $html->link("Add Post", "/posts/add"); ?>
    <table>
    	<tr>
    		<th>Id</th>
    		<th>Title</th>
                    <th>Action</th>
    		<th>Created</th>
    	</tr>
    
    <!-- Here's where we loop through our $results array, printing out post info -->
    
    <?php foreach ($results as $post): ?>
    	<tr>
    		<td><?php echo $post['Post']['id']; ?></td>
    		<td>
    			<?php echo $html->link($post['Post']['title'],'/posts/view/'.$post['Post']['id']);?>
                    </td>
                    <td>
    			<?php echo $html->link(
    				'Delete', 
    				"/posts/delete/{$post['Post']['id']}", 
    				null, 
    				'Are you sure?'
    			)?>
    			<?php echo $html->link('Edit', '/posts/edit/'.$post['Post']['id']);?>
    		</td>
    		<td><?php echo $post['Post']['created']; ?></td>
    	</tr>
    <?php endforeach; ?>
    </table>

That's it! You now have a blog with a fully featured search engine.

For other ways of using the searchable behavior, check the project
page on Google Code (`http://code.google.com/p/searchable-behaviour-
for-cakephp`_).


.. _http://book.cakephp.org/view/219/blog: http://book.cakephp.org/view/219/blog
.. _http://code.google.com/p/searchable-behaviour-for-cakephp: http://code.google.com/p/searchable-behaviour-for-cakephp
.. meta::
    :title: Search feature to CakePHP blog example
    :description: CakePHP Article related to behavior,Tutorials
    :keywords: behavior,Tutorials
    :copyright: Copyright 2008 calin
    :category: tutorials

