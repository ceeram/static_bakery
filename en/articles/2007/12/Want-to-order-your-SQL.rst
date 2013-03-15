Want to order your SQL
======================

by %s on December 28, 2007

If you don't like to have your sqls scattered around your code maybe
this component is for you
Some time ago I discovered that trying to find an error in sql querys
when you create the query on the fly and you application is not so
small can be a headache, so I think on organizing all my sql querys in
one folder one by file (I think of this as an imitation of the Store
Procedures). Basically this files contains an SQL with certain
"placeholders" and Variables so I can use them in more than one place
if I need to.

Ok, lets see the code. First the controller I came up when I ported
what I already have to Cake (I used the name of StoreProcedure because
it's what it remembers me by the structure).

::

    
    <?php
    //Restrict direct access to the file
    if (basename($_SERVER['PHP_SELF'])==basename(__FILE__))
    {
    	die('<h1>403 Forbidden</h1> You do not have permission to view this file.');
    }
    
    class StoreProcedureComponent extends Object {
    	
    	var $controller = true;
    	
    	var $__data=array();
    	
    	var $__placeHolders=array();
    	
    	function addData($pValue)
    	{
    		$this->__data[]=$pValue;
    	}
    	
    	function setPlaceHolder($pName,$pValue)
    	{
    		$this->__placeHolders[$pName]=$pValue;
    	}
    	
    	function getPlaceHolder($pName)
    	{
    		if(isset($this->__placeHolders[$pName]))
    		{
    			return $this->__placeHolders[$pName];
    		}else{
    			return '';
    		}
    	}
    	
    	function cleanPlanceHolders()
    	{
    		$this->__placeHolders=array();
    	}
    	
    	function getSQLfrom($pStoreFile,$pEmptyData=true)
    	{
    		if(is_file(_STORE_PROCEDURE_PATH."/".$pStoreFile.".php"))
    		{
    			require(_STORE_PROCEDURE_PATH."/".$pStoreFile.".php");
    			$rstring=$this->__spvfsprintf($sql,$this->__data);
    			foreach ($this->__placeHolders as $vstrName => $vstrValue)
    			{
    				$rstring=str_replace('[['.$vstrName.']]',$vstrValue,$rstring);
    			}
    			if($pEmptyData)
    			{
    				$this->__data=array();
    			}
    			return $rstring;
    		}else{
    			return '';
    		}
    	}
    
        function __spvfsprintf($str,$args=array(),$sepr="%s")
        {
            if(!is_array($args))
    	{
    		$args=array($args);
    	}
    	$str_parts=explode($sepr,$str);
    	$numberOfParts=count($str_parts);
    	$numberOfArgs=count($args);
    	$finalStr=array();
    	if(($numberOfParts-1)==$numberOfArgs)
    	{
    		for($i=0;$i<$numberOfParts;$i++)
    		{
    			$finalStr[]=$str_parts[$i];
    			if($i<$numberOfParts-1)
    			{
    				$finalStr[]=$args[$i];
    			}
    		}
    	}else{
    		trigger_error("Number of Arguments differ", E_USER_ERROR);
    	}
    	return implode(null,$finalStr);
        }
    }
    ?>

The _STORE_PROCEDURE_PATH constant you can define wherever you like
but it must point to the folder where the files that contains the SQL
are.
Something like this:

::

    
    define(_STORE_PROCEDURE_PATH,"/path/to/my/sql/files/folder");

if you want to be able to change the path on the fly just add the
following:

::

    
     var $__stPath = "";
     
     var function setPath($pPath)
     {
        if(is_dir($pPath))
        {
             $this->__stPath=$pPath;
        }  
     }

and then replace _STORE_PROCEDURE_PATH with $this->stPath

How the sql files look like, let's see one

::

    
    <?php
    //Restrict direct access to the file
    if (basename($_SERVER['PHP_SELF'])==basename(__FILE__))
    {
    	die('<h1>403 Forbidden</h1> You do not have permission to view this file.');
    }
    
    
    //SENTENCE
    $sql="
    	select %s
    	from 
    		[[ TABLE_PREFIX ]]SOME_TABLE AS `Table` 
    	where
    		Id=%s
    ";
    ?>

Note: the [[ TABLE_PREFIX ]] must be with no spaces but the bbcode
gets weird if I put here without them Let's assume that the file is
called selSomething.php, in your controller now you can do the
following:

::

    
    
    function getSomething($pId)
    {
       $this->StoreProcedure->setPlaceHolder('TABLE_PREFIX','PRE_');
       $this->StoreProcedure->addData('Name');     
       $this->StoreProcedure->addData($pId);
       $this->set("someVariable",$this->Table->query($this->StoreProcedure->getSQLfrom('selSomething')));
    }

The values assigned by addData must be in order of the %s placed in
the query and in the same quantity. The placeholder just needs to be
assigned once and remembers it's value until the end of the script or
you assign them a new value.

Hope this helps someone as it helped me.

Any suggestions, comments or doubts just drop an email to jescribens
at urbangolem dot com

.. meta::
    :title: Want to order your SQL
    :description: CakePHP Article related to sql,component,StoreProcedure,Components
    :keywords: sql,component,StoreProcedure,Components
    :copyright: Copyright 2007 
    :category: components

