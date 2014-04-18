Google Search using CakePhP
===========================

There comes a time when everyone needs to search the Internet. This
search can be done on Yahoo or Google or MSN or any other search
engine. This app utilizes Web Services to make a call against the
Google Servers and returns the results to be displayed. This is my
first release of this app (based on an older cake ver.) and hopefully
with the help of the community we can make changes and improvements to
make this app a better and faster utility. Thanks.
nuSOAP -> `http://dietrich.ganx4.com/nusoap/`_ Google API Developers
key -> `https://www.google.com/accounts/NewAccount?continue=http://api
.google.com/createkey=http://api.google.com/createkey`_
Installation Instructions:: Prerequisites

1. Download the latest version of nuSOAP and upload it to
/cake/app/vendors/nusoap/

2. Open searchs_controller.php file and insert your Google API
Developers key (if you don't have one, you need to create a Google
Account) wher it says: 'PLACE KEY HERE'

3. Upload the Controller and View to your app.

4. Perform a search.


Controller Class:
`````````````````

::

    <?php 
    //////////////////////////////////////////////////////////// 
    // This is example code of how to query the Google API using 
    // Web Services, SOAP, and PHP. 
    // 
    // Author: Salim Kapadia, November 24,2006.   
    // Updated by Salim Kapadia to work on the CakePhp Framework.
    
    //I have copied this code from the following and adapted it to work with CakePHP.
    // Original Code and comment sources: 
    		//	http://www.dankarran.com/googleapi-phpsitesearch/ 
    		//  http://www.devarticles.com/c/a/PHP/Create-Your-Own-Search-Engine-with-PHP-and-Google-Web-Services
    
    vendor('lib/nusoap'); 	
    	
    class SearchsController extends AppController
     {
    	var $name = 'Searchs';
        var $helpers = array('Html');
    	var $key = 'PLACE KEY HERE'; 
    	var $soapclient;
    		
    	function search()
    	{
    		$results="";
    		if (empty($this->params['data']))
    		{
    			//render empty.
    		}
    		else
    		{
    		$this->soapclient = new soapclient("http://api.google.com/search/beta2");
    		$this->soapclient->debug_flag = 1; 
    			$params = array( 'Googlekey' => $this->key, // Google license 
    			  // key 
    			 'queryStr' => $this->params['data']['Search']['q'],  // search term that was being typed 
    			 'startFrom' => 0,               // start from result n 
    			 'maxResults' => 10,              // show a total of 10 results 
    			 'filter' => true,               // remove similar results 
    			 'restrict' => '',               // restrict by topic 
    			 'adultContent' => true,        // remove adult links from search result 
    			 'language' => '',              // restrict by language 
    			 'iencoding' => '',             // input encoding 
    			 'oencoding' => ''             // output encoding 
    			  ); 	
    		$results = $this->soapclient->call("doGoogleSearch", $params, "urn:GoogleSearch", "urn:GoogleSearch"); 
    		}
    		$this->set('results',$results);		
    		$this->render();
    	}//end function search		
     }//end class
    ?>


View Template:
``````````````

::

    
    	echo $html->formTag('/searchs/search');
    	echo $html->input('Search/q', array('type' => 'text', 'size' => '25'));
    	echo $html->submit('Submit');	
    	echo "</form>"; 		 
    	
    	if ($results)
    	{	
    		if (isset($results['faultstring'])) 
    		{	 
    			echo "<h2>Error Report</h2>";
    			echo $results['faultstring']; 
    		}
    		else
    		{
    		echo "<h2>Google Search Results using CakePhP</h2>";
    		echo "Your search for <b>" . $results['searchQuery'] . "</b> produced " . $results['estimatedTotalResultsCount'] . " hits." ; 
    		$i=1; 
    		
    			if (is_array($results['resultElements'])) 
    			{ 
    				echo "<table border=0 cellspacing=2 cellpadding=2>"; 
    					foreach ($results['resultElements'] as $r) 
    					  { 
    						echo "<tr><td>[$i] <a href=" . $r['URL'] . ">" . $r['title'] . "</a>"; 
    						echo $r['snippet'] . "(" . $r['cachedSize'] . ")</td></tr>"; 
    						$i++; 
    					  }
    				echo "</table>";
    			}
    		}
    	}	
    	echo $html->formTag('/searchs/search');
    	echo $html->input('Search/q', array('type' => 'text', 'size' => '25'));
    	echo $html->submit('Submit');	
    	echo "</form>"; 		 



.. _=http://api.google.com/createkey: https://www.google.com/accounts/NewAccount?continue=http://api.google.com/createkey&followup=http://api.google.com/createkey
.. _http://dietrich.ganx4.com/nusoap/: http://dietrich.ganx4.com/nusoap/

.. author:: salimk786
.. categories:: articles, snippets
.. tags:: ,Snippets

