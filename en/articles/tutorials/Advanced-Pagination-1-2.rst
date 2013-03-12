

Advanced Pagination (1.2)
=========================

by %s on June 29, 2007

This tutorial will attempt to cover some advanced techniques of
pagination. In large this will cover Ajax pagination. Hopefully we can
also uncover some of the better practices and techniques to use with
pagination.
Please be sure you are familiar with the basics of Cake pagination.
This information can be found in the `basic CakePHP pagination
tutorial`_.

This tutorial is going to be a work in progress though. There are
likely some "best practices" I am not familiar with. I would like to
compile a fairly good amount of "best practices" into this tutorial. I
am sure the many smart people in the Cake community will help out as
they find opportunity to give input.

Ok let's get started. First we will go through an Ajax pagination
example. Then after that there will just be a discussion of abstract
ideas on pagination techniques.


Ajax Pagination
---------------
In reality CakePHP makes pagination with Ajax fairly simple. I'm going
to just work with the Customer list from the basic tutorial. Again,
your Model does not need to do anything special. So we'll just look at
the View and Controller code you will need to implement.

First things first, make sure prototype.js is included by your layout.
You can't do any Ajax without the javascript Ajax library. All of my
layouts usually looks like this.

::

    <?php if(!empty($ajax)): ?>
    	<?php echo $javascript->link('prototype');?>
    <?php endif; ?>

You'll have to get your own ajax loader, but your view can look like
this.

View Template:
``````````````

::

    This is the customer listing page. There are many things I might like to put here. Below is a list of all of the customers in the database.
    <br /><br />
    
    <div id="LoadingDiv" style="display: none;">
    	<?php echo $html->image('ajax-loader.gif'); ?>
    </div>
    
    <div id="CustomerPaging">
    	<?php echo $this->renderElement('customers/paging'); ?>
    </div>

Now the actual paging is happening within an element, you need to make
an element. I put my elements within a folder of the name of the
controller that uses it. This one is elements/customers/paging.ctp.

::

    <?php
    	$paginator->options(
    			array('update'=>'CustomerPaging', 
    					'url'=>array('controller'=>'Customers', 'action'=>'display'), 
    					'indicator' => 'LoadingDiv'));
    ?>
    
    Showing Page <?php echo $paginator->counter(); ?>
    <table>
    	<tr>
    		<th><?php echo $paginator->sort('Name', 'name');?></th>
    		<th><?php echo $paginator->sort('Store', 'store');?></th>
    	</tr>
    <?php foreach($customers as $customer): ?>
        <tr>
        	<td><?php echo $customer['Customer']['name']; ?></td>
        	<td><?php echo $customer['Customer']['store']; ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
    <?php echo $paginator->prev(); ?> -
    <?php echo $paginator->numbers(array('separator'=>' - ')); ?>
    <?php echo $paginator->next('Next Page'); ?>

The controller now needs to know when its just a page load, and when
it an Ajax call. We use Cake's RequestHandler to accomplish this.

Controller Class:
`````````````````

::

    <?php 
    class CustomersController extends Controller {
    
        var $name = 'Customers';
    
        var $components = array('RequestHandler');
        
        var $paginate = array('limit' => 15, 'page' => 1, 'order'=>array('name'=>'asc'));
    
        function display() {
        	if(!$this->RequestHandler->isAjax()) {
        		// things you want to do on initial page load go here
        		$this->pageTitle = "Customer List";	
        	}
        	
            $this->set('customers', $this->paginate('Customer'));
            
            if($this->RequestHandler->isAjax()) {
    			$this->viewPath = 'elements'.DS.'customers';
    			$this->render('paging');			
        	}
        }
    }
    ?>



Searching
---------
A very common use for pagination is probably search pages. Lets try to
find a good technique we can use to implement a paginated search.

The view is going to change just a little. We'll add a form and a text
field for the user to enter a search term into.

View Template:
``````````````

::

    <?php echo $form->create('Customer', array('action'=>'display'))?>
    
    <?php echo $form->text('Customer.search'); ?>
    <div id="CustomerPaging">
    	<?php echo $this->renderElement('customers/paging'); ?>
    </div>
    
    </form>

Now the controller is where things get a little tricky. The paginator
won't hold on to the search term for page 2 or page 3 and so forth. So
we have to hold onto the search term ourselves and pass it along to
the paginator manually. I have used sessions to implement this
functionality. Here is how my controller looks.

Controller Class:
`````````````````

::

    <?php 
    ...
    function display() {
    	if(!$this->RequestHandler->isAjax()) {
    		$this->pageTitle = "Customer List";	
    		// clear the session on first page visit
    		$this->Session->del($this->name.'.search');
    	}
    	
    	if(!empty($this->data))
    		$search = $this->data['Customer']['search'];
    	elseif($this->Session->check($this->name.'.search'))
    		$search = $this->Session->read($this->name.'.search');
    
    	$filters = array();
    	if(isset($search)) {
    		$filters = array("lower(Customer.name) like '%".low($search)."%'");
    		$this->Session->write($this->name.'.search', $search);		
    	}
    		
        $this->set('customers', $this->paginate('Customer', $filters));
        
        if($this->RequestHandler->isAjax()) {
    		$this->viewPath = 'elements'.DS.'customers';
    		$this->render('paging');			
    	}
    }
    
    ...
    ?>



Other Techniques
----------------
Of course there are other things you might want to with pagination.
Here are a few other little techniques I've picked up as I've gone
along.


Action parameters 1
+++++++++++++++++++
Lets say you have an action that looks like this..

::

    <?php
    ...
    function display($one=null, $two=null) {
    	if(!($one && $two))
    		$this->cakeError('error404', array($this->params['url']));
    ...
    ?>

You can get the paginator to hold onto parameters $one and $two by
using 'url'=>$this->params['pass'] in your options array. Possibly you
might want to make it standard to do something along this lines in
your views:

::

    <?php
    	$paginator->options(
    			array('update'=>'CustomerPaging', 
    					'url'=>$this->params['pass'],
    					'model'=>'Customer', 
    					'indicator' => 'LoadingDiv'));
    ?>


Empty Pagination 2
++++++++++++++++++
There will likey be a sitution where it would be convenient to get an
empty pagination set. That can be accomplished using something like
this:

::

    <?php
    	$this->set('customers', $this->paginate('Customer', array('id'=>null)));
    ?>



Known Weaknesses
----------------
Cake 1.2 is still in development. I feel obligated to inform the
reader of known weaknesses in pagination, before they dive in and
start using it. There are only two things I know of which have been
any hinderance to anybody.


#. [li] Sorting by another model - Perhaps your model you want to
   paginate with has a belongsTo relationship to another model. You may
   want to sort by that other model. Currently this is not possible for
   security reasons. I'm told it will be done before Cake 1.2 becomes
   official. For now though, I am sorry you can only sort by the Model
   you are paginating with. [li] Paginating with Javascript - Perhaps you
   want to move to page two of your list or sort using some sort of
   javascript command. Unfortunaltely this is difficult to do right now
   because the paginator only returns full anchor tags. This is not super
   hard to hack and get around. Below is a little function I've used to
   extract the url from a link.

::

    <?php $url = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\1', $link); ?>




Credits
+++++++
Quick thanks to 1 Andy Dawson (AD7six), Jitka Koukalova (poLK), and 2
Jared Hoyt. The sum of mutiple brains is better than mine is.

.. _basic CakePHP pagination tutorial: http://bakery.cakephp.org/articles/view/basic-pagination-overview-3
.. meta::
    :title: Advanced Pagination (1.2)
    :description: CakePHP Article related to pagination,paginate,1.2,Tutorials
    :keywords: pagination,paginate,1.2,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

