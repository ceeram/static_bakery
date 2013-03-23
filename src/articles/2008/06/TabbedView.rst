TabbedView
==========

by %s on June 04, 2008

This is a helper to generate tabbed views. This is my first helper and
I've just started coding in CakePHP, so... any comments are welcomw


Helper Class:
`````````````

::

    <?php 
    
    /* /app/views/helpers/tabbed_view.php */
    
    class TabbedViewHelper extends AppHelper {
        var $helpers = array('Html', 'Javascript');
        
    
        function getTabs($tabbedData, $controller_action, $id, $init_tab=NULL) {
        	echo $this->Javascript->includeScript("tabManager");
        	
    		$tabs="\n<ul class=\"tabManager\" id=\"tabManager\" >";
        	foreach($tabbedData as $tab=>$info){
        		if($tab==$init_tab)
        			$class="active";
        		else
        			$class="";
        		
        		$tabs .= "<li id=\"$tab\" class=\"$tab\" ><a href=\"/".$controller_action."/$tab/".$id."\" class=\"$class\" >$tab</a></li>\n";
        	}
    		$tabs .= "</ul>";
    		
    		       
    
            return $this->output($tabs);
        }
    
    	
    }
    
    
    ?>



::

    
    /* /app/webroot/js/tabManager.js */
    
    function switchTab(tab){
    
    	tabs=document.getElementById('tabManager');
    	
    	tabs=tabs.getElementsByTagName('li');
    	for(i=0; i<tabs.length; i++){
    		tabs[i].getElementsByTagName('a')[0].className="";
    		
    	}
    		
    	document.getElementById(tab).getElementsByTagName('a')[0].className="active";
    	//alert(document.getElementById(tab).getElementsByTagName('a')[0].innerHTML);
    }


.. meta::
    :title: TabbedView
    :description: CakePHP Article related to tabs tab tabbed pest,Helpers
    :keywords: tabs tab tabbed pest,Helpers
    :copyright: Copyright 2008 
    :category: helpers

