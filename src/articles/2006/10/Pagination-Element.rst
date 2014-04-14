Pagination Element
==================

by AD7six on October 09, 2006

The simple pagination element. For instruction on use see:
http://bakery.cakephp.org/articles/view/65

::

    
    <div id='pagination'>
    <?php
        if($pagination->setPaging($paging)):
        $leftArrow = $html->image("nav/arrowleft.gif", Array('height'=>15));
        $rightArrow = $html->image("nav/arrowright.gif", Array('height'=>15));
    	
        $prev = $pagination->prevPage($leftArrow,false);
        $prev = $prev?$prev:$leftArrow;
        $next = $pagination->nextPage($rightArrow,false);
        $next = $next?$next:$rightArrow;
    
        $pages = $pagination->pageNumbers(" | ");
    
        echo $pagination->result()."<br>";
        echo $prev." ".$pages." ".$next."<br>";
        echo $pagination->resultsPerPage(NULL, ' ');
        endif;
    ?>
    </div>


.. meta::
    :title: Pagination Element
    :description: CakePHP Article related to ,Snippets
    :keywords: ,Snippets
    :copyright: Copyright 2006 AD7six
    :category: snippets

