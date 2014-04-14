Clean your HTML output
======================

by T0aD on June 11, 2008

Sometimes you'd like to clean your HTML output to remove useless stuff
that may slow down the loading of your page. Here is a little trick to
slim your website output ;)
Let's say we want to remove comments, lines separator (that can affect
the loading of your page) and blank spaces. All we have to do is to
collect all the output returned by cake, modify it and return it in
the afterFilter hook in the app_controller.php file:


Controller Class:
`````````````````

::

    <?php 
    
    class AppController extends Controller
    {
    	function beforeRender()
    	{
    		if (Configure::read('debug') == 0) {
    			ob_start();
    		}
    	}
    
    	function afterFilter()
    	{
    		if (Configure::read('debug') == 0) {
    			$output = ob_get_contents();
    			ob_end_clean();
    			echo $this->_clean($output);
    		}
    	}
    
    	function _clean($string)
    	{
    		$string = str_replace("\n", '', $string);
    		$string = str_replace("\t", '', $string);
    		$string = preg_replace('/[ ]+/', ' ', $string);
    		$string = preg_replace('/<!--[^-]*-->/', '', $string);
    		return $string;
    	}
    }
    ?>

You can check out the results on my website
`http://www.lescigales.org`_, enjoy ;)


.. _http://www.lescigales.org: http://www.lescigales.org/
.. meta::
    :title: Clean your HTML output
    :description: CakePHP Article related to html output,Tutorials
    :keywords: html output,Tutorials
    :copyright: Copyright 2008 T0aD
    :category: tutorials

