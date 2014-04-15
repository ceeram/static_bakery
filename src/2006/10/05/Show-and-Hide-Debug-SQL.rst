Show and Hide Debug SQL
=======================

by cgmartin on October 05, 2006

Place an Element on your page or layout to toggle view of the SQL
messages (DEBUG >= 2).
Helps alleviate some of the layout/rendering issues that occur with
the sql debug messages, while still having quick access to the
information.

File: app/views/elements/sqldebugtoggle.thtml

View Template:
``````````````

::

    
    <?php if (DEBUG >= 2) { ?>
    <div id='sqldebugtoggle'>
    	<style type="text/css">
    	#cakeSqlLog { display: none; }
    	</style>
    	<script language="javascript"><!--
    	function sqldebugtoggle_toggleLayer(whichLayer) {
    		if (document.getElementById)
    		{
    			// this is the way the standards work
    			var style2 = document.getElementById(whichLayer).style;
    			style2.display = style2.display? "":"block";
    		}
    		else if (document.all)
    		{
    			// this is the way old msie versions work
    			var style2 = document.all[whichLayer].style;
    			style2.display = style2.display? "":"block";
    		}
    		else if (document.layers)
    		{
    			// this is the way nn4 works
    			var style2 = document.layers[whichLayer].style;
    			style2.display = style2.display? "":"block";
    		}
    	}
    	--></script>
    	<a onclick="sqldebugtoggle_toggleLayer('cakeSqlLog')">[Expand/Collapse SQL]</a>
    </div>
    <?php } ?>

A good spot to use is near the bottom of
app/views/layouts/default.thtml :

::

    
        ...
        <?=$this->renderElement('sqldebugtoggle')?>
    </body>
    </html>



.. author:: cgmartin
.. categories:: articles, snippets
.. tags:: Debugging,cakeSqlLog,Snippets

