Basic Pagination Overview (1.2)
===============================

Just a basic introduction to the wonderful abilities of pagination in
Cake 1.2. Gives an overview of how to use pagination to do some very
complex things with very little code.
Pagination in Cake 1.2 is very simple to set up and use. There is not
much to it. I'll just walk you through step by step getting a basic
table with pagination going. From there its on you to explore the more
advanced options that exist.


A Quick Walk Through
--------------------
For this example we'll use a Customer Model. So we'll be displaying a
list of customers and sorting them and paging through them.

You do not need to do anything special in your Model

Model Class:
````````````

::

    <?php 
    class Customer extends AppModel {
      
    	var $name = "Customer";
    }
    ?>

In your controller just add the $paginate variable. It tells the
system to start and page 1, and show 15 entries per page. You will
call the paginate function from the controller to generate the data
list.

Controller Class:
`````````````````

::

    <?php 
    class CustomersController extends AppController {
    
    	var $name = 'Customers';
    
    	var $paginate = array('limit' => 15, 'page' => 1);
    
    	function display() {
    		$this->set('customers', $this->paginate('Customer'));
    	}
    }
    ?>

And lastly in your view you will wants to use the $paginator to
display your data. You view will look something like this. This will
be the file /views/customers/display.ctp.

View Template:
``````````````

::

    Showing Page <?php echo $paginator->counter(); ?>
    <table>
    	<tr>
    		<th><?php echo $paginator->sort('Customer Name', 'name');?></th>
    		<th><?php echo $paginator->sort('Store Location', 'store');?></th>
    	</tr>
    <?php foreach($customers as $customer): ?>
        <tr>
        	<td><?php echo $customer['Customer']['name']; ?></td>
        	<td><?php echo $customer['Customer']['store']; ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
    <?php echo $paginator->prev(); ?>
    <?php echo $paginator->numbers(); ?>
    <?php echo $paginator->next(); ?>
    
    

Yes, it is that simple. You might say that was "Cake".


Further Reading
---------------
There are two parts to pagination with CakePHP. The paginate function
within the controller and the $paginator helper within the view. All
of the options for these can be found in the `1.2 API documentation`_.

The paginate function is used to define your data set. You can pass in
parameters to screen out certain data, perhaps only display customers
from a certain store. This function is assisted by the $paginate
variable in the controller which has a few options such as rows per
page and default sort order.

The paginator helper does a great job of supporting Ajax pagination.
Information is available in the API, but more information should come
in a later tutorial on advanced pagination abilities. For now, the
only real good tip I have is that you have to always use arrays when
passing in custom urls.

You paginator Ajax links might look like this:

::

    <?php echo 
    $paginator->sort('Article Name', 'name', array('url'=>
    			array('controller'=>'Articles', 'action'=>'index'),
    			'update'=>'ArticleListTable')); ?>

But like I said there is a lot to this. Ajax and Url handling will
need a tutorial of its own.


Tips & Hints
------------
To set a default sort order use the $paginate variable like this:

::

    // in your Controller
    var $paginate = array('order'=>array('name' => 'desc'));

You can use images for your prev and next links. You have to use the
escape option.

::

    <?php echo $paginator->link('<img src="myimage.jpg">', array('escape'=>false))?>

It is possible to have more than one paginator within a controller and
view. You just have to specify the Model that you are going to use
everywhere.

::

    // this goes in the Controller
    var $paginate = array('Article' => array('limit'=>25), 'Customer'=>array('limit'=>10));
    
    <!-- these will go in the view -->
    <?php echo $paginator->sort('Article Sort', 'id', array('model'=>'Article')); ?>
    <?php echo $paginator->sort('Customer Name', 'name', array('model'=>'Customer')); ?>

The counter function of the $paginator helper is very powerful, giving
you access to all kinds of data that you might want to display. Thanks
to Gwoo for this lovely little piece of code.

::

    <?php 
    echo $paginator->counter(array(
    		'format' => 'Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%'
    )); 
    ?>



.. _1.2 API documentation: http://api.cakephp.org/1.2/

.. author:: rtconner
.. categories:: articles, tutorials
.. tags:: pagination,helpers,1.2,beginners,Tutorials

