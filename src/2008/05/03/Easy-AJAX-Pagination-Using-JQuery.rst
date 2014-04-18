Easy AJAX Pagination Using JQuery
=================================

This simple little JQuery function allows you to drop paginated data
into your views with ease.
I chose to implement ajax pagination in this manner because I wasn't
able to successfully follow Rob Conner's technique in his super-useful
Advanced Pagination article (`http://bakery.cakephp.org/articles/view
/advanced-pagination-1-2`_).

I assume in this article that you are familiar with basic pagination
techniques using Cake's built-in functions. If you are not, make sure
to read and understand those first.

I use the following to display a paginated list of related items in a
sidebar element, though I imagine it can easily generalize whatever
you'd like it to do.

Let's begin.

Things you will need:

#. CakePHP 1.2.6311 or later
#. JQuery 1.2.3 (`http://docs.jquery.com/Downloading_jQuery`_)
#. Data you want to paginate


Download JQuery, and put it in your /app/webroot/js/ folder. Include
it in the header of your view, i.e.:

::

    
    echo $javascript->link(array('jquery.js'));

In the controller providing paginated data, be sure to include the
RequestHandler Component. For the purposes of this example, I'm going
to be paginating Images in an ImageController. So my basic controller
looks something like this:


Controller Class:
`````````````````

::

    <?php 
    class ImagesController extends AppController
    {
    	var $components = array('RequestHandler');
    	var $helpers = array('Html','Form','Javascript');
    	var $paginate = array('order'=>array('Image.title'),'limit'=>'15');
    
            function list()
            {
                    $data = $this->paginate();
                    $this->set('images',$data);
            }
    
    }
    ?>

And the view is equally simple (/app/views/images/list.ctp):


View Template:
``````````````

::

    
    <?php
    if (count($images)>0) {
         /* Display paging info */
    ?>
    <div id="pagination">
    <?php
          echo $paginator->prev(); 
          echo $paginator->numbers(array('separator'=>' - ')); 
          echo $paginator->next();
    ?>
    </div>
    
    <table>
    <?php
          foreach ($images as $image) {
    ?>
          <tr>
                 <td>
    <?php
               echo $html->image($image['Image']['filename']);
    ?>
                </td>
                <td>
                          <?php echo $image['Image']['title'];?>
                </td>
         </tr>
    <?php
          }
    ?>
    </table>
    <?php
    }
    ?>

This would be your basic level of pagination. Note the Div around the
pagination-specific links.

Now let's say that I'm in another controller, and I'd like to see a
paginated list of all my Images.

So here's where the JQuery magic comes in. First, designate a part of
your view you want to use to display the image pagination with a div.

Say I'm in a view where I'm adding content, ie:
/app/views/content/add.ctp:


View Template:
``````````````

::

    
    
    /* bunch of related content view code, etc, etc, then.... */
    
    <div id="imageList">
    
    </div>
    
    

Now include this snippet of Javascript before the div:


View Template:
``````````````

::

    
    
    /* bunch of related content view code, etc, etc, then.... */
    	<script type="text/javascript">
    		  $(document).ready(function() {
    			loadPiece("<?php echo $html->url(array('controller'=>'images','action'=>'list'));?>","#imageList");
       		  });
    	</script>
    <div id="imageList">
    
    </div>

The $(document).ready() function is from JQuery. The Javascript
function, loadPiece, needs to be included either in the head of your
view, or in a reference file. Here it is (it also requires JQuery):

::

    
    /**
     * Loads in a URL into a specified divName, and applies the function to
     * all the links inside the pagination div of that page (to preserve the ajax-request)
     * @param string href The URL of the page to load
     * @param string divName The name of the DOM-element to load the data into
     * @return boolean False To prevent the links from doing anything on their own.
     */
    function loadPiece(href,divName) {	
    	$(divName).load(href, {}, function(){
    		var divPaginationLinks = divName+" #pagination a";
    		$(divPaginationLinks).click(function() { 	
    			var thisHref = $(this).attr("href");
    			loadPiece(thisHref,divName);
    			return false;
    		});
    	});
    }

This function loads a particular URL using an Ajax-request (in this
case, the Image Controller's list() function), and writes it to the
specified DIV. The RequestHandler component makes sure that Cake only
renders the view, sans any layout templating.

For all links within the pagination div the function applies an event
listener so that, when clicked, the link URL is loaded through
JQuery's Ajax loader and back into the specified DIV. Any links not in
this div will load in a normal way, though, as you can see, it's
pretty easy to change the behavior.

I like this method quite a bit, as it just makes more sense to me, and
it's also quite clean.


.. _http://docs.jquery.com/Downloading_jQuery: http://docs.jquery.com/Downloading_jQuery
.. _http://bakery.cakephp.org/articles/view/advanced-pagination-1-2: http://bakery.cakephp.org/articles/view/advanced-pagination-1-2

.. author:: daphonz
.. categories:: articles, snippets
.. tags:: pagination,jquery,paginate,Snippets

