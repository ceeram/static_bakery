Paginating Data with CakePHP and Yahoo! User Interface DataTable
================================================================

The aim is to pull data from your database and output it in a nice
shiny data table.
For this tutorial I have used CakePHP 1.2 RC3 and YUI 2.6.0. I am
using a "Products" controller, model and corresponding views.

Thanks should go to this post `http://www.ntatd.org/mark/?p=32`_ by
Mark Buckner (aka hydra12) describing how to use cakePHP with ExtJS
DataGrid, for the inspiration and the some of the cakePHP code for
this tutorial.

The aim is to pull data from your database and output it in a nice
shiny data table. Although the cakePHP paginator does this perfectly
well, I have been using YUI and I am not a fan of the prototype
library.

The YUI Paginator control is able to send requests to the server with
parameters for the page required, the DataTable control can then
format that data into our shiny table.

The first step is to extract the data and return it to the view. To do
this we need to create a new action in the controller.


Controller Class:
`````````````````

::

    <?php function results($page=0,$limit=10) {
    $this->layout = "ajax";  //make cake use the ajax layout
    $productArray = array();  //this will hold our data from the database.
    $count = $this->Product->findCount(); //counts the number of records in Message.
    $productA = $this->Product->find('all', array('limit'=>$limit,'page'=>$page,'order' => 'Product.created DESC')); //gets all the Product records and sorts them by date created.
    $productArray = Set::extract($productA, '{n}.Product');  //convert $productArray into a json-friendly format
    $this->set('total', $count);  //send total to the view
    $this->set('page_size',$limit);
    $this->set('product_list',$productArray);  //send messages to the view
    }?>

Next, create a view file called results.ctp in /views/products/. In
this file we will set out the data given to us by the controller in to
JSON format. YUI DataTable will be able to use this to input the data
into our shiny table later.


View Template:
``````````````

::

    <?php
     echo '{"totalRecords":'.$total.',
            "recordsReturned":'.$page_size.',
            "records":'.$javascript->Object($product_list).'}';
    ?>

Now if all is well and you go to
`http://www.example.com/products/results`_, you should be presented
with your JSON array.

To keep things simple I have put the javascript needed in a seperate
file, products.js. This will be imported into the view with the
javascript helper.

::

    YAHOO.example.DynamicData = function() {
      // Column definitions
      var myColumnDefs = [
          {key:"id", label:"Id"},
          {key:"code", label:"Code"},
          {key:"name", label:"Name"},
          {key:"quantity", label:"Qty"}
      ];
    
      // DataSource instance
      var myDataSource = new YAHOO.util.DataSource("http://www.example.com/products/results/");
     // This is where the DataTable gets its data from.
     myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
     myDataSource.responseSchema = {
         resultsList: "records",
         fields: [
           {key:"id"},
           {key:"code",parser:"number"},
           {key:"name"},
           {key:"quantity",parser:"number"}
         ],
         metaFields: {
             totalRecords: "totalRecords" // Access to value in the server response
         }
     };
    
    // This tells the DataTable how to pass variables to the URL, for this example all we need is the current page.
     var myRequestBuilder = function(oState, oSelf) {
     // Get states or use defaults
     oState = oState || {pagination:null};
     var page = oState.pagination.page;
     // Build custom request
     return  page;
     );
    
     // DataTable configuration
     var myConfigs = {
         initialRequest: "1", // Initial request for first page of data
         dynamicData: true, // Enables dynamic server-driven data
         paginator: new YAHOO.widget.Paginator({ rowsPerPage:10 }),
         generateRequest : myRequestBuilder}; // Enables pagination 
    
     // DataTable instance
     var myDataTable = new YAHOO.widget.DataTable("dynamicdata", myColumnDefs, myDataSource, myConfigs);
     myDataTable.hideColumn("id");//this hides the id column, so it is not visible but we can use the data at a later date.
    
    // Update totalRecords on the fly with value from server
     myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
         oPayload.totalRecords = oResponse.meta.totalRecords;
         return oPayload;
     }
    
     return {
         ds: myDataSource,
         dt: myDataTable
     };
    
    }();

Now, all we need to add to the view of the page that we want the
DataTable to appear on is this,


View Template:
``````````````

::

    <div id="dynamicdata"></div>
    
    <?php echo $javascript->includeScript('products'); //this is our js file with all the YUI goodness ?>

In the layout file we need to add the YUI files between the head tags,


View Template:
``````````````

::

    <!-- Combo-handled YUI CSS files: -->
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.6.0/build/datatable/assets/skins/sam/datatable.css">
    <!-- Combo-handled YUI JS files: -->
    <script type="text/javascript" src="http://yui.yahooapis.com/combo?2.6.0/build/yahoo-dom-event/yahoo-dom-event.js&2.6.0/build/connection/connection-min.js&2.6.0/build/datasource/datasource-min.js&2.6.0/build/element/element-beta-min.js&2.6.0/build/datatable/datatable-min.js"></script>

Of course you can use the html and javascript helpers for this if you
want. You can see how the file requests are configured here,
`http://developer.yahoo.com/yui/articles/hosting/?connection`_. To use
the built-in "skin" you need to give your body tag a class of yui-
skin-sam so,

::

    <body class="yui-skin-sam">

The skin can easily be modified as explained here,
`http://developer.yahoo.com/yui/articles/skinning/`_.

Now when you visit the page you have put the DataTable on to, you
should see your nice shiny table and your data retrieved, also YUI
puts navigation above and below the table, which can also be
customised with skins.

Happy Baking and I hope this was useful.
Duncan Brown
`http://www.duncanbrown.me.uk`_

.. _http://www.ntatd.org/mark/?p=32: http://www.ntatd.org/mark/?p=32
.. _http://www.example.com/products/results: http://www.example.com/products/results
.. _http://developer.yahoo.com/yui/articles/hosting/?connection: http://developer.yahoo.com/yui/articles/hosting/?connection&datasource&datatable&MIN
.. _http://www.duncanbrown.me.uk: http://www.duncanbrown.me.uk/
.. _http://developer.yahoo.com/yui/articles/skinning/: http://developer.yahoo.com/yui/articles/skinning/

.. author:: duncanbrown
.. categories:: articles, tutorials
.. tags:: yui,datatable,Tutorials

