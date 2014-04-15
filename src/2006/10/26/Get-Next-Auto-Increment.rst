Get Next Auto Increment
=======================

by jsconnell on October 26, 2006

A small hack that returns the auto increment id for the current model.
I wanted to know before hand what the next auto_increment id for a
model was, but cake didn't provide any obvious way to do so. The kind
folks at `http://blog.jamiedoris.com/geek/560/`_ showed me a way to do
so, which I adaped for cake. If someone with experience in the cake
core could give me any tips on cleaning this up, let me know.

Model Class:
````````````

::

    <?php 
    class AppModel extends Model {
    	function getNextAutoIncrement(){
    
    		$next_increment = 0;
    
    		$table = Inflector::tableize($this->name);
    
    		$query = "SHOW TABLE STATUS LIKE '$table'";
    
    		$db =& ConnectionManager::getDataSource($this->useDbConfig);
    
    		$result = $db->rawQuery($query);
    
    
    
    		while ($row = mysql_fetch_assoc($result)) {
    
    			$next_increment = $row['Auto_increment'];
    
    		}
    
    
    
    		return $next_increment;
    
    	}
    
    }
    ?>



.. _http://blog.jamiedoris.com/geek/560/: http://blog.jamiedoris.com/geek/560/

.. author:: jsconnell
.. categories:: articles, snippets
.. tags:: auto_increment model,Snippets

