The CakePHP Blog Tutorial with SMARTY
=====================================

by HyperCas on June 11, 2008

The official cakePHP Blog tutorial converted to use smarty. I hope
this would help people quickly setup smarty and have an example to
refer to for cakePHP + smarty. I assume you've already done the blog
tutorial at http://manual.cakephp.org/view/219/the-cakephp-blog-
tutorial
Using smarty in cakePHP is pretty simple, though there are some quirks
which popup when switching to smarty. I am not an expert on cakePHP.
The way shown here might not be the best, rather just how I got the
blog running on smarty (and no, it's not sloppy either)
You can refer to the following tutorials to setup smarty and it's
helpers:

How to use Smarty with Cake (SmartyView)
`http://bakery.cakephp.org/articles/view/how-to-use-smarty-with-cake-
smartyview`_
How to use SmartyHelpers with SmartyView
`http://bakery.cakephp.org/articles/view/how-to-use-smartyhelpers-
with-smartyview`_


To setup smarty (quick overview)
````````````````````````````````
1. Download the smarty template engine
2. Extract it to /app/vendors/smarty
3. Create /app/temp/smarty/cache
4. Create /app/temp/smarty/compile
5. Above 2 directories should be writable
6. Refer to the SmartyView tutorial to download SmartyView
7. Create a file app/views/helpers/smarty_html.php and paste the
SmartyView class in that file
8. Get the SmartyHelpers (form & session)
9. class SmartyFormHelper goes to app/views/helpers/smarty_form.php
10. class SmartySessionHelper goes to
app/views/helpers/smarty_session.php


Create your own AppController
+++++++++++++++++++++++++++++
The SmartyView tutorial recommends you edit your app_controller.php to
make SmartyView available. Do not edit app_controller.php in
cake/libs/controller/ but copy that file to app/controllers/ and edit
there

Your app_controller.php should look like:

::

    
    class AppController extends Controller {
    	var $view = 'Smarty';
    	var $helpers = array('SmartyHtml','SmartySession','form');
    }

Open your blog's /posts url and you should see everything as it is.
This is because smartyview defaults to .ctp files if no .tpl files are
found.



First order of business default.ctp
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Now you want to check if smarty works. I went made a modified version
of default.ctp. Why?
Just to make sure smarty was working globally.
1. Copy the file cake/libs/view/layouts/default.ctp
2. Paste in app/view/layouts/default.ctp
3. Put some random text to ensure your new default.ctp file is being
loaded by cakePHP
4. Copy & paste the file in same directory as default.tpl
5. Open the your page /posts in the browser and see the errors
6. Replace the code in default.tpl with the following

::

    
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    	<title>
    		Smarty + cakePHP: {$title_for_layout}
    	</title>
    	{$html->charset()}
    	{$html->meta('icon')}
    	{html func=css path="cake.generic"}
    	{$scripts_for_layout} 
    	
    </head>
    <body>
    	<div id="container">
    		<div id="header">
    			<h1>{html func=link title='CakePHP: the rapid development php framework' url='http://cakephp.org'}</h1>
    		</div>
    		<div id="content">
    			{if $session->check('Message.flash')}
    						{$session->flash()}
    			{/if}
    			<!-- content -->
    			{$content_for_layout}
    		</div>
    		<div id="footer">
    			<a href="{html func=url url='/posts/'}">{$html->image("cake.power.gif")}</a> 
    		</div>
    	</div>
    	{$cakeDebug}
    </body>
    </html>

It is just the default.ctp file converted to smarty. People with some
idea of both cakePHP and smarty will see that I am using the
SmartyHtmlHelper and also calling the html helper by using the object.

Using the SmartyHtmlHelper

::

    
     {html func=css path="cake.generic"}
     

Using the html helper object

::

    
    {$html->charset()}
    {$html->meta('icon')}

Code in the previous "Using the SmartyHtmlHelper" as html object

::

    
    {$html->css('cake.generic')}

Reffer to the tutorial "How to use SmartyHelpers with SmartyView" for
some more examples. How we call the html helper is not really
important, just a matter of convinence at the moment.

Uptill now we have created our own app_controller.php and default.ctp
( + default.tpl ). If everything goes well, your page will look
exactly as it did before, except the title of the page will be "Smarty
+ cakePHP: Posts" and the CAKEPHP | POWER button will link to the
/posts page


Second order of business index.ctp
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
1. Go to app/views/posts. This is where we made index.ctp for the blog
tutorial
2. Copy & past index.ctp as index.tpl
3. Replace the text with the following

::

    
    <h1>Blog posts</h1>
    <h1>{$html->link('Add New Post',"/posts/add")}</h1>
    <table>
    <tr>
    	<th>Id</th>
    	<th>Title</th>
    	<th>Actions</th>
    	<th>Created</th>
    </tr>
    <!-- Here's where we loop through our $posts array, printing out post info -->
    	
    	{section name=item loop=$posts}
    	{assign var=bpost value=$posts[item].Post}
    	<tr>
    		<td>{$bpost.id}</td>
    		<td>
    			{html func=link title=$bpost.title url=/posts/view/`$bpost.id`}
    		</td>
    		<td>
    			{html func=link  title=Edit url=/posts/edit/`$bpost.id`}
    		</td>
    	</tr>
    	{/section}
    </table>

I was a bit lazy and left out the delete link. I made a few additions
to make using {section} in smarty a bit easier.


::

    
    {assign var=bpost value=$posts[item].Post}

The above code creates a variable $bpost to easily access the current
post. To get a post id use {$bpost.id}. Without this variable to
access the id or any other info

::

    
    $posts[item].Post.id
    $posts[item].Post.some_other_var


To create proper urls

::

    
    {html func=link  title=Edit url=/posts/edit/`$bpost.id`}

I went through some trouble to arrive at this since calling
$html->link() via smarty does not allow for string
concatination. Having the smarty html helper is quite handy. Googling
helped me find the ` character could be used for concatination.
Confession: I am not an expert in smarty either.
`http://smarty.incutio.com/?page=SmartyFrequentlyAskedQuestions`_

We should now try viewing /posts can clicking the links. Everything
should work. Adding/Editing pages will use the old .ctp files.



Third Order of business flash()
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Not Adobe flash, but rather the flash() call used to show a message to
users. In the posts_controller.php file calling $this->flash('Your
post has been updated','/posts'); will only lead to a blank page with
SmartyView enabled.

I tried creating a flash.tpl in app/views/layouts/ which lead to
nothing. I assumed it would be like replacing default.ctp, the same
would work for flash(). Since I have no idea how the view are handled
in this instance I would love it if someone could enlighten me or
point out where to look. Also I am not sure why the flash.tpl layout
does not work with SmartyView.

`http://manual.cakephp.org/view/96/layouts`_
However I did arrive a convenient alternative. While I was searching
around for solutions I found a couple of articles which provided an
improved flashing system.

`http://shabadeehoob.com/2007/03/17/rails-like-flash-messages-in-
cakephp/`_ `http://google.com`_ - dont have the other links handy atm

So based on the information I found, I changed my app_controller.php
to the following:

::

    
     class AppController extends Controller {
    	var $view = 'Smarty';
    	var $helpers = array('SmartyHtml','SmartySession','form');
    
    	function flash($msg,$url=null,$pause=1)
    	{
    	  	$this->Session->setFlash($msg);
    	  	if($url)
    	  	{
    	  		$this->redirect($url,$pause);
    	  		exit;
    	  	}
    		//This is the code found the Controller:flash()
    		//$this->autoRender = false;
    		//$this->set('url', Router::url($url));
    		//$this->set('message', $message);
    		//$this->set('pause', $pause);
    		//$this->set('page_title', $message);
    		//$this->SmartySession->render(false, 'flash');
    	}
    }
     

What does it do?
It flashes the message in the new page your controller redirects to or
if no url is specified, it flashes the message on the same page.

The API pages on cakephp.org helped me look up the code for the
various message flash related

The code that allows this in the default.tpl file

::

    
    			{if $session->check('Message.flash')}
    				{$session->flash()}
    			{/if}
     

The code is not fully functional since the $pause variable is
meaningless. But it accomplishes the objective of flashing messages.

Note: For some reason, there will be a 1 displayed in a new line after
the flashed message. I tracked it down to flash() in
cake\libs\view\helpers\session.php. I replaced return true;
with return null;
It's not the proper way to do something phpCake but I've no idea how
the session->flash() gets called while rendering so this patching is
just a convinent solution. If anyone know how prevent the return value
from flash() being displayed please let me know.


Other Notes
```````````
1. I've not enabled the SmartyFormHelper, and am using the default
form helper
2. The entire blog tutorial has not been replaced to render with
smarty. Just the main layout and the /posts page
3. The flash() system needs a slight patching of the sessions.php file
4. The blog is my first attempt at cakePHP coding, I spent only a few
hours doing the blog and converting it and writing the tut
5. cakePHP is really easy to use. I spent only 2-3 days reading the
manual (couple of hours each day)
6. Read the manual first. My tinkering would not have been possible
otherwise.


Fun stuff
+++++++++
I crashed my apache server a couple times while trying to include
helpers in app_controller.php. It crashed when I tried to include
'flash' in the var $helpers = array(). Why did I do that? Simply
thought there was a flash helper :-P. It also crashed when I put
'SmartyHtml','SmartySession' without setting up their files properly.

I used Eclipse PDT as my IDE. If you want to use it with cakePHP check
out the following link:
`http://bakery.cakephp.org/articles/view/setting-up-eclipse-to-work-
with-cake`_


My heartfelt thanks to the guys how made the Smarty classes for
cakePHP, the ones who wrote the tutorials I referred to.



.. _http://google.com: http://google.com/
.. _http://shabadeehoob.com/2007/03/17/rails-like-flash-messages-in-cakephp/: http://shabadeehoob.com/2007/03/17/rails-like-flash-messages-in-cakephp/
.. _http://bakery.cakephp.org/articles/view/how-to-use-smarty-with-cake-smartyview: http://bakery.cakephp.org/articles/view/how-to-use-smarty-with-cake-smartyview
.. _http://manual.cakephp.org/view/96/layouts: http://manual.cakephp.org/view/96/layouts
.. _http://bakery.cakephp.org/articles/view/how-to-use-smartyhelpers-with-smartyview: http://bakery.cakephp.org/articles/view/how-to-use-smartyhelpers-with-smartyview
.. _http://smarty.incutio.com/?page=SmartyFrequentlyAskedQuestions: http://smarty.incutio.com/?page=SmartyFrequentlyAskedQuestions
.. _http://bakery.cakephp.org/articles/view/setting-up-eclipse-to-work-with-cake: http://bakery.cakephp.org/articles/view/setting-up-eclipse-to-work-with-cake
.. meta::
    :title: The CakePHP Blog Tutorial with SMARTY
    :description: CakePHP Article related to blog,smartyview,smarty,beginner,Tutorials
    :keywords: blog,smartyview,smarty,beginner,Tutorials
    :copyright: Copyright 2008 HyperCas
    :category: tutorials

