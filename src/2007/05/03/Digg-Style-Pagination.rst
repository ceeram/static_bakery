Digg Style Pagination
=====================

Looking for a fairly simple way to create pagination in a style
similar to Digg? Check this out.


First Grab the Code
~~~~~~~~~~~~~~~~~~~

Copy the code below into a file called pagination.php and place it
into the /app/view/helpers/ folder in your Cake application.


Helper Class:
`````````````

::

    <?php 
    
    class PaginationHelper extends HtmlHelper
    {
    	
    	function paginate( $link, $page, $total, $show=5, $skip='…' )
    	{
    		/*
    			$link	string of what link is to be (utilizes $html->link helper)... page numbers will be appended to its end
    			$page	int current page you're on
    			$total	int total number of pages
    			$show	int how many page numbers / "skips" to show between first and last numbers
    			$skip	string text to be displayed for "skips"... inside <span>
    		*/
    
    		// Get out early if there's no total pages
    		if ( $total < 1 ) return false;
    
    		// Init
    		if ( $show < 1 ) $show = 1;						// make sure you're showing at least 1
    		$show_mid = ceil( $show / 2 );					// find the midpoint of the shown page numbers / skips
    		$skip = '<span class="skip">'.$skip.'</span>';	// add spans around skip text
    		$out = "\n";
    
    		// Figure out start point for In-between numbers
    		if ( $page <= $show_mid ) $start = 2;
    		elseif ( $page > ($total-$show) ) $start = $total - $show;
    		else $start = $page - $show_mid + 1;
    
    		// Previous link
    		$out .= ( ($page-1) > 0 )
    					? $this->link( 'Prev', $link.($page-1), array('title'=>'View the previous index page', 'class'=>'prev') ) 
    					: '<span class="prev">Prev</span>';
    		$out .= "\n";
    
    		// First number
    		$out .= ( $page == 1 )
    					? '<span class="current">1</span>'
    					: $this->link( '1', $link.'1', array('title'=>'View index page 1') );
    		$out .= "\n";
    
    		// In-between numbers
    		for ( $i=0; $i<( ($total<$show+2) ? $total-2 : $show ); $i++ )
    		{
    			// First in-between number...
    			if ( $i == 0 )
    			{
    				$out .= ( $start == 2 ) 
    							? ( $page == 2 )
    								? '<span class="current">2</span>'
    								: $this->link( '2', $link.'2', array('title'=>'View index page 2') )
    							: $skip;
    			}
    
    			// Last in-between number...
    			elseif ( $i == ($show-1) )
    			{
    				$out .= ( $start >= ($total-$show) ) 
    							? ( $page == ($total-1) )
    								? '<span class="current">'.($total-1).'</span>'
    								: $this->link( ($total-1), $link.($total-1), array('title'=>'View index page '.($total-1)) )
    							: $skip;
    			}
    
    			// Else...
    			else 
    			{
    				$out .= ( $page == ($start+$i) )
    							? '<span class="current">'.($start+$i).'</span>'
    							: $this->link( ($start+$i), $link.($start+$i), array('title'=>'View index page '.($start+$i)) );
    			}
    
    			$out .= "\n";
    		}
    
    		// Last number
    		if ( $total > 1 )
    		{
    			$out .= ( $page == $total )
    						? '<span class="current">'.$total.'</span>'
    						: $this->link( $total, $link.$total, array('title'=>'View index page '.$total) );
    			$out .= "\n";
    		}
    
    		// Next link
    		$out .= ( ($page+1) <= $total )
    					? $this->link( 'Next', $link.($page+1), array('title'=>'View the next index page', 'class'=>'next') )
    					: '<span class="next">Next</span>';
    		$out .= "\n";
    
    		// Return
    		return $out;
    	}
    
    }
    
    ?>



Enable It For Use
~~~~~~~~~~~~~~~~~

As with all helpers, you have to tell Cake to include it before it
becomes available. Therefore, open up the controller you want it to
use it in (or your app_controller.php if you'd like it available to
every controller), and add it to the $helpers array property.


Another thing to note is that this helper extends the functionality of
the default HtmlHelper, so you have to make sure to include it in the
$helpers array as well.


Controller Class:
`````````````````

::

    <?php 
    
    class EntriesController extends AppController
    {
    
    	var $helpers = array( 'Html', 'Pagination' );
    
    	function index($page=1)
    	{
    		// blah blah blah
    
    		$this->set( 'pag_link', '/entries/view/' );
    		$this->set( 'pag_page', $page );
    		$this->set( 'pag_total', $this->Entry->findCount() );
    	}
    
    }
    
    ?>



Get It Working in the View
~~~~~~~~~~~~~~~~~~~~~~~~~~

Now the only thing left to do is to call it in the view. As you can
see from the example controller above, we made an index method that
sets some variables to be available in that method's associated view
file. We will use this information when making the call to our
Pagination helper, as shown below.


View Template:
``````````````

::

    
    <h2>Additional Pages</h2>
    
    <?php $pagination->$paginate($pag_link, $pag_page, $pag_total); ?>


That's pretty much all there is to it. You can also supply the last
two parameters if you'd like, so that you can change the amount of
"number links" in-between the first and last numbers, or the text to
be printed in the "skips" (what exactly these things are will become
apparent when using the helper).

I'm sure there might be a better way to do this, so if you have any
suggestions, comments are always welcome.



.. author:: mattpuchlerz
.. categories:: articles, helpers
.. tags:: digg,web2.0,Helpers

