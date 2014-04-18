Pagination Helper for Long Content
==================================

In one of my CMS projects, I ran across a case where the user created
a post of very long content that scrolled endlessly down the page. In
an effort to make the content more easily readable, I created a
Pagination helper that breaks that content into discrete blocks of
content with "next" and "prev" links.
In one of my CMS projects, I ran across a case where the user created
a post of very long content that scrolled endlessly down the page. In
an effort to make the content more easily readable, I created a
Pagination helper that breaks that content into discrete blocks of
content with "next" and "prev" links.

The visibility of the content is controlled through script.aculo.us
Effects.

The benefit of using this helper is that all of the content is still
on the page, for SEO.

To use this helper, all you have to do is add "[page]" markers in your
content, which indicate places where you want the content to break
into a new "page".

For example, you might have:

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam
nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
sed diam voluptua.
[page] At vero eos et accusam et justo duo dolores et ea rebum. Stet
clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor
sit amet.

Enable the helper in your controller:


Controller Class:
`````````````````

::

    <?php 
    var $helpers = array("Html", "Pagination");
    ?>

In your view, render the paginated content:


View Template:
``````````````

::

    
    <?php echo $pagination->paginateContent($content_for_layout); ?>



Helper Class:
`````````````

::

    <?php 
    class PaginationHelper extends Helper {
      /**
       * Parses a string of content for [page] blocks, and replaces them 
       * with div tags for dynamic control over content section 
       * visibility.
       * 
       * @access public
       * @since 1.0.0
       * @param string $strContent The content to parse and paginate
       * @return string The paginated content
       * 
       **/
      function paginateContent($strContent) {
        $strPaginated = '';
        $arrSections = explode('[page]', $strContent);
        if(count($arrSections) > 0) {
          for($i=0;$i<count($arrSections);$i++) {
            $arrSections[$i] = preg_replace("/<br([^>]+)>/i", 
                                            "", 
                                            $arrSections[$i], 1);
    	$curIndex = $i + 1;
    	$prevIndex = $i == 0 ? 1 : $curIndex - 1;
    	$nextIndex = $i >= count($arrSections) ? count($arrSections) : $curIndex + 1;
    				
    	$pageID = 'page'.$curIndex;
    	$nextPage = 'page'.$nextIndex;
    	$prevPage = 'page'.$prevIndex;
    			
    	// Handle first block
    	if($i == 0) {
    	  $strPaginated .= '<div id="'.
                               $pageID.'" class="contentblock">'.
                               $arrSections[$i];
    	  // Only show the "More" link if there is more than 1 section
              if(count($arrSections) > 1) {
                $strPaginated .= '<a class="pagelink" href=javascript:void(0); onclick="switchPage(\''.$nextPage.'\');">More ></a>';
    	  }
              $strPaginated .= '</div>';
    	}
    	else {
    	  // Hide the other blocks
    	  $strPaginated .= '<div id="'.$pageID.'" class="contentblock" style="display:none;">'.$arrSections[$i];
    					
    	  if($curIndex < count($arrSections)){
    	    $strPaginated .= '<a class="pagelink" href=javascript:void(0); onclick="switchPage(\''.$nextPage.'\');">More ></a>';
    	  }
    					
    	  $strPaginated .= '<a class="pagelink" href=javascript:void(0); onclick="switchPage(\''.$prevPage.'\');">< Prev</a>';
    	  $strPaginated .= '</div>';
    	}
          }
    			
          return<<<PAGE_CODE
    $strPaginated			
    <script type="text/javascript">
    var currentPage = 'page1';
    </script>
    PAGE_CODE;
    			
        }
        return $strContent;
    	
      } // End Function
    
    } // End Class
    ?>

You'll also need to add some javascript code to handle the switching
of visible content blocks:

::

    
    <script language="javascript">
    function switchPage(thePage) {
      if(window.currentPage) {
        if(thePage != window.currentPage) {
          new Effect.Fade(window.currentPage);
          window.currentPage = thePage;
          new Effect.Appear(thePage, {delay:0.5});	
        }
      }
    }
    </script>

Please note: visibility of content blocks is controlled via
javascript. You need to verify that the client has javascript enabled
before calling the paginateContent() method, or else none of the
content will be visible.


.. author:: ebeyrent
.. categories:: articles, helpers
.. tags:: seo,scriptaculous,Helpers

