

History Navigation Bar
======================

by %s on May 06, 2009

This component is intended to be used in cases where as we go sailing,
go deeper into the pages, starting from a home page.It show the path
to the current page, also allow direct access to any of the pages
visited.
Store the component code in:

controllers/components/history_bar.php.
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;


Component Class:
````````````````

::

    <?php 
    
    /*
     * HistoryComponent: Navigation History Bar
     * @author: Claudio Juan BÃ¶hm
     * @license: MIT
     * @version: 0.1
     * */ 
     
    class HistoryBarComponent extends Object
    {
        var $controller = null;
        var $components = array( "Session" );
        
        
        var $settings = array(
            "viewVariable" => "hisBar",
            "sessionVariable" => "hisBar",
            "normalSeparator" => "|",
            "initialSeparator" => "",
            "className" => "hisBar",
            "actualPage" => "true"
            );
    
    	//called before Controller::beforeFilter()
    	function initialize(&$controller, $settings = array()) {
    		// saving the controller reference for later use
    		$this->controller =& $controller;
            foreach($settings as $key => $data)
                $this->settings[$key] = $data;
    	}
    
    	//called after Controller::beforeRender()
    	function beforeRender(&$controller) {
            
            $historyBarLnk = $controller->here;
            
            $historyBarTit = $controller->pageTitle;
            if(isset($controller->HistoryBarSettings) && array_key_exists('label',$controller->HistoryBarSettings))
                $historyBarTit = $controller->HistoryBarSettings['label'];
            
            $historyBarData = array();
            $newHistoryBarData = array();
            $barItems = array();
            
            $actualPage = $this->settings["actualPage"];
            
            if(isset($controller->HistoryBarSettings) && array_key_exists('actualPage',$controller->HistoryBarSettings))
                $actualPage = (bool)($controller->HistoryBarSettings['actualPage']) ;
    
            if($controller->Session->check($this->settings["sessionVariable"]))
            {
                $historyBarData = $controller->Session->read($this->settings["sessionVariable"]);
                $controller->Session->delete($this->settings["sessionVariable"]);
            }
             
            $add = true;
             
            foreach($historyBarData as $tit=>$lnk)
            {
                $newHistoryBarData[$tit] = $lnk;            
            
                if($lnk == $controller->here)
                {
                    if($actualPage)
                        $barItems[] = $tit;
                    $add = false;
                    break;
                }
                else
                {
                    $barItems[] = '<a href="'.$lnk.'">'.$tit.'</a>';
                }
                
            }
            if($add)
            {
                $newHistoryBarData = $newHistoryBarData + array($historyBarTit=>$historyBarLnk);
                if($actualPage)
                    $barItems[] = $historyBarTit;
            }
            
            $this->Session->write($this->settings['sessionVariable'],$newHistoryBarData);
    
            $normalSeparator = $this->settings['normalSeparator'];
            if(isset($controller->HistoryBarSettings['normalSeparator']))
                $normalSeparator = $controller->HistoryBarSettings['normalSeparator'];
    
            $strHisBar = '<div class="'.$this->settings['className'].'">';
            if(isset($controller->HistoryBarSettings['initialSeparator']))
                $strHisBar .= $controller->HistoryBarSettings['initialSeparator'];
            else
                $strHisBar .= $this->settings['initialSeparator'];
            
            $strHisBar .= join(' '.$normalSeparator.' ',$barItems);
            
            $strHisBar .= '</div>';
            
            $controller->set($this->settings['viewVariable'], $strHisBar);
          
    	}  
       
    }
    ?>



Usage
+++++
The component will automatically be loaded by the Controller.
I recommend placing it directly in App_Controller

::

    
    class AppController extends Controller
    {
        var $components = array('Auth','RequestHandler','HistoryBar');
    
    }



Settings
++++++++
The settings are optional.
They can be set different settings in every action of the controller.
These settings are configured using the array "HistoryBarSettings" on
the controller.

::

    
    var $HistoryBarSettings = array(
        "viewVariable" => "hisBar",
        "sessionVariable" => "hisBar",
        "normalSeparator" => "|",
        "initialSeparator" => "",
        "className" => "hisBar",
        "actualPage" => "true"
        );



+ viewVariable: Is the name of the variable to be used in the view.
+ sessionVariable: Is the name of the variable to be used in the
  session.
+ normalSeparator: Is the string used to separate each item in the
  navigation bar.
+ initialSeparator: Is the string used to start the navigation bar.
+ className: The navigation bar is placed inside a div tag. This
  setting is the name of the style class to use.
+ actualPage: Finally, this setting indicates if the name of the
  current page will be displayed in the bar or not. By default is set to
  true, and is displayed without the link tag.



Expansion
+++++++++
I'm developing a Helper to add more flexibility to display the bar.
Comments regarding more functionality and bugs are welcome.


.. meta::
    :title: History Navigation Bar
    :description: CakePHP Article related to component,history,navigator,Components
    :keywords: component,history,navigator,Components
    :copyright: Copyright 2009 
    :category: components

